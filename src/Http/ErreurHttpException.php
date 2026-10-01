<?php

declare(strict_types=1);

namespace Squadro\Http;

use RuntimeException;

final class ErreurHttpException extends RuntimeException
{
    public function __construct(public readonly int $statut, string $message)
    {
        parent::__construct($message, $statut);
    }
}
