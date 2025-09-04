<?php

namespace App\Domain\Board\Exceptions;

use Exception;

class BoardNotFoundException extends Exception
{
    public function __construct(
        string $message = "Board not found",
        int $code = 404,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function withId(int $boardId): self
    {
        return new self("Board with ID {$boardId} not found");
    }
}
