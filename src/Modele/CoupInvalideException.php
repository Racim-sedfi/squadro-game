<?php

declare(strict_types=1);

namespace Squadro\Modele;

use DomainException;

/**
 * Levée lorsqu'un coup ne respecte pas les règles (mauvaise pièce, pas son tour…).
 * Le message est destiné à être affiché au joueur.
 */
final class CoupInvalideException extends DomainException
{
}
