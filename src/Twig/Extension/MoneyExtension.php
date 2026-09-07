<?php

namespace App\Twig\Extension;

use Twig\Attribute\AsTwigFilter;

class MoneyExtension
{
    #[AsTwigFilter('money_br')]
    public function formatMoneyBr(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'R$ 0,00';
        }

        $numeric = (float) str_replace(',', '.', (string) $value);

        return 'R$ ' . number_format($numeric, 2, ',', '.');
    }
}
