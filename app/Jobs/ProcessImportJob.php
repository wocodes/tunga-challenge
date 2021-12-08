<?php

namespace App\Jobs;

use App\Http\Traits\CustomDataValidation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use JsonMachine\JsonMachine;

class ProcessImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, CustomDataValidation;

    public JsonMachine $users;
    public ?Request $request;
    public int $filesize = 0;
    private int $skippedRecordsCount = 0;
    private int $importedRecordsCount = 0;
    private string $filename = "uploaded_users.json";


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(?Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $uploadProgressBuilder = DB::table('upload_progress');

        // check if there's a file to upload
        if (Storage::exists("public/{$this->filename}")) {
            $file = Storage::get("public/{$this->filename}");
            $this->filesize = strlen($file);

            // I could simply have used an array_chunk to chunk the json data
            // if they would never be more than say 10,000 * 500 or say over 200MB
            // But based on requirements, if the expected file will be large e.g over 200MB,
            // Using this package; JsonMachine, helps to  process large sizes of json files with less memory
            $this->users = JsonMachine::fromString($file);

            foreach ($this->users as $key => $user) {
                $dataMeetsRequirement = CustomDataValidation::hasValidatedRequirements($user);

                // if data doesn't meet requirements then skip processing to next record
                // else if last inserted index is greater than current key, then skip, cos it has been inserted
                if (!$dataMeetsRequirement ||
                    ($uploadProgressBuilder->exists() && ($uploadProgressBuilder->first())->last_inserted_index > $key)
                ) {
                    $this->skippedRecordsCount++;
                    continue;
                }

                // insert record
                $this->insertRecord($user, $key);
            }

            Log::info("Total imported records: {$this->importedRecordsCount}");
            Log::info("Total skipped records: {$this->skippedRecordsCount}");
        } elseif (!Storage::exists("public/{$this->filename}") && $this->request && $this->request->file) {
            // save the data to local storage in streams
            $disk = Storage::disk('local');
            $disk->put("public/{$this->filename}", fopen($this->request->file, 'r+'));
        } else {
            // no file and no request to upload file, so do nothing
            return null;
        }
    }



    private function insertRecord(array $user, int $key): void
    {
        try {
            // all good...
            DB::table('users')->insert([
                "name" => $user['name'],
                "address" => $user['address'],
                "checked" => $user['checked'],
                "description" => $user['description'],
                "interest" => $user['interest'],
                "date_of_birth" => $user['date_of_birth'],
                "email" => $user['email'],
                "account" => $user['account'],
                "credit_card" => json_encode($user['credit_card']),
            ]);

            DB::commit();

            // update last inserted index value
            DB::table('upload_progress')->updateOrInsert(
                ['filename' => $this->filename],
                ['last_inserted_index' => $key]
            );

            // update importedRecords count
            $this->importedRecordsCount++;

            // file upload is complete, then delete file
            $percentProcessed = intval($this->users->getPosition() / $this->filesize * 100);
            Log::info($percentProcessed . "% processed");

            if ($percentProcessed >= 99) {
                // delete file
                unset($file);
            }
        } catch (\Exception $exception) {
            Log::info('something went wrong`', [$exception->getMessage()]);

            // something went wrong, rollback
            DB::rollBack();
        }
    }
}
