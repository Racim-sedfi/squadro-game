<?php

declare(strict_types=1);

namespace Squadro\Service;

use RuntimeException;

final class PartieIntrouvableException extends RuntimeException
{
    public function __construct(int $id)
    {
        parent::__construct(sprintf('La partie n°%d n\'existe pas.', $id));
    }
}
