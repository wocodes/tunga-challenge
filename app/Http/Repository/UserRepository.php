<?php

namespace App\Http\Repository;

use App\Jobs\ProcessImportJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use JsonMachine\JsonMachine;

class UserRepository
{

    private string $filename = "uploaded_users.json";

    public function processImportRequest(Request $request)
    {

        // check if there's a file to upload
        if (Storage::exists("public/{$this->filename}")) {
            $file = Storage::get("public/{$this->filename}");
            $fileSize = strlen($file);

            // I could simply have used an array_chunk to chunk the json data
            // if they would never be more than say 10,000 * 500 or say over 200MB
            // But based on requirements, if the expected file will be large e.g over 200MB,
            // Using this package; JsonMachine, helps to  process large sizes of json files with less memory
            $users = JsonMachine::fromString($file);


            ProcessImportJob::dispatch($users, $file, $fileSize);
        } elseif (!Storage::exists("public/{$this->filename}") && $request->file) {
            // save the data to local storage in streams
            $disk = Storage::disk('local');
            $disk->put("public/{$this->filename}", fopen($request->file, 'r+'));
        } else {
            // no file and no request to upload file, so do nothing
            return null;
        }
    }
}
