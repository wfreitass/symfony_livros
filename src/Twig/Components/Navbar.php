<?php

namespace App\Twig\Components;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Navbar
{
    public string $brand = 'Gestão de Livros';
    public string $brandRoute = 'app_livro_index';
    public string $brandIcon = 'bi-book-half text-warning';

    public function __construct(
        private readonly RequestStack $requestStack
    ) {}

    public function getCurrentRoute(): ?string
    {
        return $this->requestStack->getCurrentRequest()?->attributes->get('_route');
    }

    public function isRouteActive(string $prefixOrRoute): bool
    {
        $current = $this->getCurrentRoute();
        if (!$current) {
            return false;
        }

        if ($prefixOrRoute === 'app_livro' && $current === 'app_home') {
            return true;
        }

        return str_starts_with($current, $prefixOrRoute);
    }
}
