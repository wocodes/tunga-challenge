<?php

namespace App\Http\Repository;

use App\Jobs\ProcessImportJob;
use Illuminate\Http\Request;

class UserRepository
{
    public function processImportRequest(Request $request)
    {
        ProcessImportJob::dispatch($request);
    }
}
