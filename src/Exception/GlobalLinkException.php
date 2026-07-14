<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Exception;

/**
 * Base exception for all GlobalLink SDK errors.
 */
class GlobalLinkException extends \RuntimeException
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
