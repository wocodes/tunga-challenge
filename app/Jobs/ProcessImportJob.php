<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JsonMachine\JsonMachine;

class ProcessImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public JsonMachine $users;
    public int $filesize = 0;
    public string $filename = "";
    private int $skippedRecordsCount = 0;
    private int $importedRecordsCount = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(JsonMachine $users, string $filename, int $filesize)
    {
        $this->users = $users;
        $this->filesize = $filesize;
        $this->filename = $filename;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $uploadProgressBuilder = DB::table('upload_progress');

        foreach ($this->users as $key => $user) {
            $dataMeetsRequirement = $this->hasValidatedRequirements($user);

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



    /**
     * Process data validations
     *
     * @param array $data
     * @return bool
     */
    private function hasValidatedRequirements(array $data): bool
    {
        return $this->isAgeValid($data['date_of_birth']) ||
            $this->isCreditCardValid($data['credit_card']); // add other validation methods here
    }


    /**
     * Check if Date of Birth reach age requirements
     *
     * @param string|null $dateOfBirth
     * @return bool
     */
    private function isAgeValid(?string $dateOfBirth): bool
    {
        // parse date of birth to age value
        $age = $dateOfBirth ? Carbon::parse(strtotime($dateOfBirth))->age : null;

        // if date of birth doesn't meet criteria return false
        if ($age && ($age < 18 || $age > 65)) {
            // skip data
            return false;
        }

        return true;
    }


    /**
     * Check if Credit Card meets requirements
     *
     * @param string $creditCardNumber
     * @return bool
     */
    private function isCreditCardValid(array $creditCardNumber): bool
    {
        // check if credit card meets criteria
        return (bool) preg_match('/(.)\\1{2}/', $creditCardNumber['number']);
    }
}
