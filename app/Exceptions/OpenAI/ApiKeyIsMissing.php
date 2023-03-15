<?php

declare(strict_types=1);

namespace App\Exceptions\OpenAI;

use InvalidArgumentException;

/**
 * @internal
 */
class ApiKeyIsMissing extends InvalidArgumentException
{
    /**
     * Create a new exception instance.
     */
    public static function create(): self
    {
        return new self(
            'The OpenAI API Key is missing. Please publish the [openai.php] configuration file and set the [api_key].'
        );
    }
}
