<?php

namespace App\Client;

use Orhanerday\OpenAi\OpenAi as BaseOpenAi;

class OpenAI extends BaseOpenAi
{
    public function __construct($apiKey)
    {
        parent::__construct($apiKey);
    }

    public function withToken($token = '')
    {
        if ($token) {
            $this->setHeader([1 => 'Authorization: Bearer ' . $token]);
            return $this;
        } else {
            return $this;
        }
    }
}
