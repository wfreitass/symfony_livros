<?php

namespace App\Tests\Unit\Twig;

use App\Twig\Extension\MoneyExtension;
use PHPUnit\Framework\TestCase;

class MoneyExtensionTest extends TestCase
{
    private MoneyExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new MoneyExtension();
    }

    public function testFormatMoneyBrComValoresValidos(): void
    {
        $this->assertSame('R$ 150,50', $this->extension->formatMoneyBr('150.50'));
        $this->assertSame('R$ 1.250,00', $this->extension->formatMoneyBr('1250.00'));
        $this->assertSame('R$ 0,50', $this->extension->formatMoneyBr(0.5));
    }

    public function testFormatMoneyBrComValoresNulosOuVazios(): void
    {
        $this->assertSame('R$ 0,00', $this->extension->formatMoneyBr(null));
        $this->assertSame('R$ 0,00', $this->extension->formatMoneyBr(''));
    }
}
