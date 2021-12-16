<?php

namespace App\Jobs;

use App\Actions\CustomDataValidation;
use App\Actions\ReadFileContent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use JsonMachine\JsonMachine;

class ProcessImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public JsonMachine $users;
    public int $filesize = 0;
    private int $skippedRecordsCount = 0;
    private int $importedRecordsCount = 0;
    private string $filename = "uploaded_users.json";

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {

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

            $this->users = ReadFileContent::run("public/{$this->filename}");
            $this->filesize = strlen(Storage::get("public/{$this->filename}"));

            foreach ($this->users as $key => $user) {
                $dataMeetsRequirement = (new CustomDataValidation($user))->checkRequirements()->handle();

                // if data doesn't meet requirements then skip processing to next record
                // else if last inserted index is greater than current key, then skip, cos it has been inserted
                if (!$dataMeetsRequirement ||
                    ($uploadProgressBuilder->exists() && ($uploadProgressBuilder->first())->last_inserted_index > $key)
                ) {
                    $this->skippedRecordsCount++;

                    $this->updateImportProgress($key);
                    continue;
                }

                // insert record
                $this->insertRecord($user, $key);
            }

            Log::info("Total imported records: {$this->importedRecordsCount}");
            Log::info("Total skipped records: {$this->skippedRecordsCount}");
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

            // update importedRecords count
            $this->importedRecordsCount++;

            $this->updateImportProgress($key);

            // file upload is complete, then delete file
            $percentProcessed = intval($this->users->getPosition() / $this->filesize * 100);
            Log::info($percentProcessed . "% processed");

            if ($percentProcessed >= 99) {
                // delete file
                Storage::delete("public/{$this->filename}");

                // delete tracking record
                DB::table('upload_progress')->delete(1);
            }
        } catch (\Exception $exception) {
            Log::error('something went wrong`', [$exception->getMessage()]);
        }
    }

    private function updateImportProgress($key)
    {
        // update last inserted index value
        DB::table('upload_progress')->updateOrInsert(
            ['filename' => $this->filename],
            ['last_inserted_index' => $key]
        );
    }
}
