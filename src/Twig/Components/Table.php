<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Table
{
    public array $columns = [];
    public mixed $items = null;
    public ?string $emptyMessage = null;
    public string $emptyIcon = 'bi-inbox';
    public int $colspan = 1;
    public bool $hover = true;
    public bool $striped = false;
    public bool $bordered = false;
    public bool $alignMiddle = true;
    public ?string $size = null;
    public string $theadClass = 'table-light';
    public bool $responsive = true;

    public function getClasses(): string
    {
        $classes = ['table'];

        if ($this->hover) {
            $classes[] = 'table-hover';
        }
        if ($this->striped) {
            $classes[] = 'table-striped';
        }
        if ($this->bordered) {
            $classes[] = 'table-bordered';
        }
        if ($this->alignMiddle) {
            $classes[] = 'align-middle';
        }
        if ($this->size) {
            $classes[] = 'table-' . $this->size;
        }

        return implode(' ', $classes);
    }

    public function getEffectiveColspan(): int
    {
        if ($this->colspan > 1) {
            return $this->colspan;
        }

        return max(1, count($this->columns));
    }
}
