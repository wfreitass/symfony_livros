<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class PageHeader
{
    public string $title = '';
    public ?string $subtitle = null;
    public array $actions = [];
}
