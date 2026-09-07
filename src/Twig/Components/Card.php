<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Card
{
    public ?string $title = null;
    public ?string $icon = null;
    public ?string $variant = null;
    public bool $shadow = true;
    public bool $border = false;
    public ?string $margin = 'mb-4';
    public ?string $headerClass = null;
    public ?string $bodyClass = null;
    public ?string $footerClass = null;
    public ?string $footer = null;
    public ?string $footerLink = null;
    public ?string $footerLinkLabel = null;
    public ?string $footerLinkIcon = 'bi-arrow-right';

    public function getClasses(): string
    {
        $classes = ['card'];

        if ($this->border) {
            $classes[] = 'border';
        } else {
            $classes[] = 'border-0';
        }

        if ($this->shadow) {
            $classes[] = 'shadow-sm';
        }

        if ($this->margin) {
            $classes[] = $this->margin;
        }

        if ($this->variant) {
            $classes[] = 'bg-' . $this->variant;
            $classes[] = in_array($this->variant, ['light', 'warning', 'info'], true) ? 'text-dark' : 'text-white';
        }

        return implode(' ', $classes);
    }
}
