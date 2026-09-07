<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Alert
{
    public string $type = 'info';
    public string $message = '';
    public bool $dismissible = true;

    public function getTypeClass(): string
    {
        return match ($this->type) {
            'error' => 'danger',
            default => $this->type,
        };
    }
}
