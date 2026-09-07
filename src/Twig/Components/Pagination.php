<?php

namespace App\Twig\Components;

use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Pagination
{
    public ?PaginationInterface $pagination = null;
    public string $itemName = 'itens';
    public bool $borderTop = true;
    public string $class = '';

    public function getFromItem(): int
    {
        if (!$this->pagination || $this->getTotal() === 0) {
            return 0;
        }

        return ($this->pagination->getCurrentPageNumber() - 1) * $this->pagination->getItemNumberPerPage() + 1;
    }

    public function getToItem(): int
    {
        if (!$this->pagination || $this->getTotal() === 0) {
            return 0;
        }

        return min(
            $this->getTotal(),
            ($this->pagination->getCurrentPageNumber() - 1) * $this->pagination->getItemNumberPerPage() + count($this->pagination)
        );
    }

    public function getTotal(): int
    {
        return $this->pagination ? $this->pagination->getTotalItemCount() : 0;
    }

    public function hasItems(): bool
    {
        return $this->getTotal() > 0;
    }
}
