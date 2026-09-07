<?php

namespace App\Contract;

interface PdfServiceInterface
{
    public function generatePdf(string $html): string;
}
