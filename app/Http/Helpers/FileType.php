<?php

namespace App\Http\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use JsonMachine\JsonMachine;

trait FileType
{
    public static function extractContent($filepath)
    {
        $extension = File::extension($filepath);
        $file = Storage::get($filepath);
        switch ($extension) {
            case "txt":
                return self::fromText($file);

            case "csv":
                return self::fromCsv($file);

            case "json":
                return self::fromJson($file);

            case "xml":
                return self::fromXml($file);

            default:
                return null;
        }
    }

    /**
     * Extract content from Text File
     *
     * @param $file
     * @return JsonMachine
     */
    private static function fromText($file)
    {
        // TODO: Extract content from text file logic here
    }


    /**
     * Extract content from Json File
     *
     * @param $file
     */
    private static function fromJson($file)
    {
        // I could simply have used an array_chunk to chunk the json data
        // if they would never be more than say 10,000 * 500 or say over 200MB
        // But based on requirements, if the expected file will be large e.g over 200MB,
        // Using this package; JsonMachine, helps to  process large sizes of json files with less memory

        return JsonMachine::fromString($file);
    }


    /**
     * Extract content from Csv File
     *
     * @param $file
     */
    private static function fromCsv($file)
    {
        // TODO: Extract content from Csv file logic here
    }


    /**
     * Extract content from Xml File
     *
     * @param $file
     */
    private static function fromXml($file)
    {
        // TODO: Extract content from Xml file logic here
    }

}
