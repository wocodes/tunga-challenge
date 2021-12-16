<?php

namespace App\Actions;

use App\Actions\FileTypes\ReadCsvFile;
use App\Actions\FileTypes\ReadJsonFile;
use App\Actions\FileTypes\ReadTextFile;
use App\Actions\FileTypes\ReadXmlFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;

class ReadFileContent
{
    use AsAction;

    public function handle($filepath)
    {
        $extension = File::extension($filepath);
        $file = Storage::get($filepath);
        switch ($extension) {
            case "txt":
                return ReadTextFile::run($file);

            case "csv":
                return ReadCsvFile::run($file);

            case "json":
                return ReadJsonFile::run($file);

            case "xml":
                return ReadXmlFile::run($file);

            default:
                throw new \Exception("The file format ($extension) isn't yet supported.");
        }
    }
}
