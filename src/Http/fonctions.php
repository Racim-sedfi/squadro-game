<?php

declare(strict_types=1);

/**
 * Échappe une valeur pour un affichage sûr dans du HTML (anti-XSS).
 */
function e(string|int|float|null $valeur): string
{
    return htmlspecialchars((string) $valeur, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
