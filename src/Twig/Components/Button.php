<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Button
{
    public ?string $label = null;
    public string $type = 'button';
    public string $variant = 'primary';
    public ?string $size = null;
    public ?string $icon = null;
    public ?string $href = null;
    public bool $disabled = false;

    public function getClasses(): string
    {
        $classes = ['btn'];

        if ($this->variant === 'light') {
            $classes[] = 'btn-light border';
        } elseif ($this->variant) {
            $classes[] = 'btn-' . $this->variant;
        }

        if ($this->size) {
            $classes[] = 'btn-' . $this->size;
        }

        return implode(' ', $classes);
    }
}
