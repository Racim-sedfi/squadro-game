<?php

declare(strict_types=1);

namespace Squadro\Http;

/**
 * Accès typé à la session : joueur connecté, jeton CSRF et messages flash.
 * Le stockage est injecté (référence vers $_SESSION en production, tableau en test).
 */
final class Session
{
    private const CLE_JOUEUR = 'joueur_id';
    private const CLE_CSRF = 'csrf';
    private const CLE_FLASH = 'flash';

    /** @param array<string, mixed> $stockage */
    public function __construct(private array &$stockage)
    {
    }

    public static function demarrer(): self
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start([
                'cookie_httponly' => true,
                'cookie_samesite' => 'Lax',
                'cookie_secure' => ($_SERVER['HTTPS'] ?? '') === 'on',
                'use_strict_mode' => true,
            ]);
        }

        return new self($_SESSION);
    }

    public function joueurId(): ?int
    {
        $id = $this->stockage[self::CLE_JOUEUR] ?? null;

        return is_int($id) ? $id : null;
    }

    public function connecter(int $joueurId): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true); // protection contre la fixation de session
        }
        $this->stockage[self::CLE_JOUEUR] = $joueurId;
        unset($this->stockage[self::CLE_CSRF]);
    }

    public function deconnecter(): void
    {
        $this->stockage = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public function jetonCsrf(): string
    {
        if (!isset($this->stockage[self::CLE_CSRF]) || !is_string($this->stockage[self::CLE_CSRF])) {
            $this->stockage[self::CLE_CSRF] = bin2hex(random_bytes(32));
        }

        return $this->stockage[self::CLE_CSRF];
    }

    public function verifierCsrf(string $jeton): bool
    {
        return $jeton !== '' && hash_equals($this->jetonCsrf(), $jeton);
    }

    public function flash(string $type, string $message): void
    {
        $this->stockage[self::CLE_FLASH][] = ['type' => $type, 'message' => $message];
    }

    /** @return list<array{type: string, message: string}> */
    public function consommerFlashs(): array
    {
        $flashs = $this->stockage[self::CLE_FLASH] ?? [];
        unset($this->stockage[self::CLE_FLASH]);

        return is_array($flashs) ? array_values($flashs) : [];
    }
}
