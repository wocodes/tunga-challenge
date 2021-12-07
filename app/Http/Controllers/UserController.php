<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessImportJob;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function importData(Request $request)
    {
        // validate file
//        $request->validate([
//           'file' => 'file|mimes:json',
//        ]);

        // cache data
        $data = json_decode($request->file->getContent(), true);

        $chunkedData = array_chunk($data, 1000);

        foreach ($chunkedData as $datum) {
            ProcessImportJob::dispatch($datum);
        }
    }
}
