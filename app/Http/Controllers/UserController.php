<?php

namespace App\Http\Controllers;

use App\Http\Repository\UserRepository;
use App\Jobs\ProcessImportJob;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use JsonMachine\JsonDecoder\PassThruDecoder;
use JsonMachine\JsonMachine;

class UserController extends Controller
{
    public UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function importData(Request $request)
    {
        // validate file
        $request->validate([
           'file' => 'nullable|file|mimes:json,txt,csv,xml',
        ]);


        $this->userRepository->processImportRequest($request);
    }
}
