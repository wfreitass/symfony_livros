<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Card
{
    public ?string $title = null;
    public ?string $icon = null;
}
