<?php

declare(strict_types=1);

namespace Squadro\Service;

use DomainException;

/** Erreur de saisie dont le message peut être affiché tel quel à l'utilisateur. */
final class ErreurValidationException extends DomainException
{
}
