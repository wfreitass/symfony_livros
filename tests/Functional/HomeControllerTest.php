<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PHPUnit\Framework\Attributes\Test;

class HomeControllerTest extends WebTestCase
{
    #[Test]
    public function testHomePageLoadsSuccessfully(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorTextContains('h1', 'Painel de Gestão do Acervo');
    }

    #[Test]
    public function testHomePageContainsQuickLinks(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();

        $this->assertGreaterThan(0, $crawler->filter('a[href="/livro/"]')->count());
        $this->assertGreaterThan(0, $crawler->filter('a[href="/autor/"]')->count());
        $this->assertGreaterThan(0, $crawler->filter('a[href="/assunto/"]')->count());
        $this->assertGreaterThan(0, $crawler->filter('a[href="/relatorio/"]')->count());
    }
}
