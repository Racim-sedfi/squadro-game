<?php

declare(strict_types=1);

namespace Squadro\Persistance;

use RuntimeException;
use Throwable;

final class PseudoDejaPrisException extends RuntimeException
{
    public function __construct(string $pseudo, ?Throwable $precedente = null)
    {
        parent::__construct(sprintf('Le pseudo « %s » est déjà utilisé.', $pseudo), 0, $precedente);
    }
}
