<?php

namespace App\Http\Repository;

use App\Jobs\ProcessImportJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use JsonMachine\JsonMachine;

class UserRepository
{
    private string $filename = "uploaded_users.json";

    public function processImportRequest(Request $request)
    {
        try {

            if (!Storage::exists("public/{$this->filename}") && $request->file) {
                // save the data to local storage in streams
                $disk = Storage::disk('local');
                $disk->put("public/{$this->filename}", fopen($request->file, 'r+'));
            }

            ProcessImportJob::dispatch();
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }
    }
}
