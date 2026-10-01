<?php

declare(strict_types=1);

namespace Squadro\Persistance;

use RuntimeException;

/**
 * La partie a été modifiée entre sa lecture et son enregistrement
 * (par exemple deux requêtes simultanées) : l'écriture est refusée.
 */
final class ConflitDeVersionException extends RuntimeException
{
}
