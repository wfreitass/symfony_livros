<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class BrazilianMoneyTransformer implements DataTransformerInterface
{
    /**
     * Transforma o valor do banco (ex: "150.50") para exibição no form (ex: "150,50")
     */
    public function transform(mixed $value): string
    {
        if (null === $value || '' === $value) {
            return '';
        }

        return number_format((float) $value, 2, ',', '');
    }

    /**
     * Transforma a entrada do usuário (ex: "1.250,50" ou "150,50") para o formato do banco (ex: "1250.50")
     */
    public function reverseTransform(mixed $value): ?string
    {
        if (!$value) {
            return null;
        }

        // Remove espaços e símbolo R$ caso o usuário digite
        $cleaned = trim((string) $value);
        $cleaned = str_replace(['R$', ' '], '', $cleaned);

        // Remove pontos de milhar e substitui vírgula por ponto
        $cleaned = str_replace('.', '', $cleaned);
        $cleaned = str_replace(',', '.', $cleaned);

        if (!is_numeric($cleaned)) {
            throw new TransformationFailedException('O valor informado não é um número válido.');
        }

        return number_format((float) $cleaned, 2, '.', '');
    }
}
