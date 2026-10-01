<?php

declare(strict_types=1);

namespace Squadro\Http;

use Closure;

/**
 * Routeur minimaliste : associe une méthode HTTP et un motif d'URL
 * (avec paramètres numériques `{id}`) à une action de contrôleur.
 */
final class Routeur
{
    /** @var list<array{methode: string, regex: string, action: Closure}> */
    private array $routes = [];

    public function get(string $motif, Closure $action): self
    {
        return $this->ajouter('GET', $motif, $action);
    }

    public function post(string $motif, Closure $action): self
    {
        return $this->ajouter('POST', $motif, $action);
    }

    public function ajouter(string $methode, string $motif, Closure $action): self
    {
        $regex = '#^' . preg_replace('/\{(\w+)\}/', '(?P<$1>\d+)', $motif) . '$#';
        $this->routes[] = ['methode' => $methode, 'regex' => $regex, 'action' => $action];

        return $this;
    }

    /**
     * @return array{0: Closure, 1: array<string, int>}
     * @throws ErreurHttpException 404 si aucune route ne correspond, 405 si seule la méthode diffère
     */
    public function resoudre(Requete $requete): array
    {
        $methodeNonAutorisee = false;

        foreach ($this->routes as $route) {
            if (preg_match($route['regex'], $requete->chemin, $correspondances) !== 1) {
                continue;
            }
            if ($route['methode'] !== $requete->methode) {
                $methodeNonAutorisee = true;
                continue;
            }

            $parametres = array_map('intval', array_filter($correspondances, 'is_string', ARRAY_FILTER_USE_KEY));

            return [$route['action'], $parametres];
        }

        throw $methodeNonAutorisee
            ? new ErreurHttpException(405, 'Méthode non autorisée.')
            : new ErreurHttpException(404, 'Cette page n\'existe pas.');
    }
}
