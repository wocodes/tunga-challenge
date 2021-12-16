<?php

namespace App\Actions\FileTypes;

use JsonMachine\JsonMachine;
use Lorisleiva\Actions\Concerns\AsAction;

class ReadJsonFile
{
    use AsAction;

    public function handle($file)
    {
        // I could simply have used an array_chunk to chunk the json data
        // if they would never be more than say 10,000 * 500 or say over 200MB
        // But based on requirements, if the expected file will be large e.g over 200MB,
        // Using this package; JsonMachine, helps to  process large sizes of json files with less memory

        return JsonMachine::fromString($file);
    }
}
