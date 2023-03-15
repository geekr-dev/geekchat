<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenAI API Key and Organization
    |--------------------------------------------------------------------------
    |
    | Here you may specify your OpenAI API Key and organization. This will be
    | used to authenticate with the OpenAI API - you can find your API key
    | and organization on your OpenAI dashboard, at https://openai.com.
    */

    'api_key' => env('OPENAI_API_KEY'),
    'base_uri' => env('OPENAI_BASE_URI', 'api.openai.com'),
    'organization' => env('OPENAI_ORGANIZATION'),
    'http_proxy' => env('HTTP_PROXY'),
];
