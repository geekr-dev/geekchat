<?php

declare(strict_types=1);

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Client\OpenAI withToken($token = '')
 * @method static string chat()
 * @method static string completion()
 * @method static string embeddings()
 * @method static string createEdit()
 * @method static string uploadFile()
 * @method static string listFiles()
 * @method static string retrieveFile()
 * @method static string retrieveFileContent()
 * @method static string deleteFile()
 * @method static string createFineTune()
 * @method static string listFineTunes()
 * @method static string retrieveFineTune()
 * @method static string cancelFineTune()
 * @method static string listFineTuneEvents()
 * @method static string deleteFineTune()
 * @method static string image()
 * @method static string imageEdit()
 * @method static string createImageVariation()
 * @method static string listModels()
 * @method static string retrieveModel()
 * @method static string moderation()
 * @method static string transcribe()
 * @method static string translate()
 */
class OpenAI extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'openai';
    }
}
