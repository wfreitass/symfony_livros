<?php

namespace App\Tests\Functional;

use App\Entity\Assunto;
use App\Entity\Autor;
use App\Entity\Livro;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RelatorioControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    #[Test]
    public function testRelatorioIndexPageLoadsSuccessfully(): void
    {
        $this->client->request('GET', '/relatorio/');
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorTextContains('h1', 'Relatório Gerencial & Estatísticas');
        $this->assertSelectorExists('a[href="/relatorio/pdf"]');
    }

    #[Test]
    public function testRelatorioIndexWithGroupedData(): void
    {
        $em = $this->getEntityManager();
        $autor = (new Autor())->setNome('Autor Relatorio Teste');
        $assunto = (new Assunto())->setDescricao('Genero Teste');
        $livro = (new Livro())
            ->setTitulo('Livro Para Relatorio View')
            ->setEditora('Editora View')
            ->setEdicao(1)
            ->setAnoPublicacao('2024')
            ->setValor('125.00')
            ->addAutor($autor)
            ->addAssunto($assunto);

        $em->persist($autor);
        $em->persist($assunto);
        $em->persist($livro);
        $em->flush();

        $crawler = $this->client->request('GET', '/relatorio/');
        $this->assertResponseIsSuccessful();

        $this->assertStringContainsString('Autor Relatorio Teste', $crawler->text());
        $this->assertStringContainsString('Livro Para Relatorio View', $crawler->text());
        $this->assertStringContainsString('R$ 125,00', $crawler->text());
    }

    #[Test]
    public function testPdfExportReturnsValidPdfDocument(): void
    {
        $this->client->request('GET', '/relatorio/pdf');
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('Content-Type', 'application/pdf');
        $this->assertResponseHeaderSame('Content-Disposition', 'inline; filename="relatorio_gerencial_livros.pdf"');

        $content = $this->client->getResponse()->getContent();
        $this->assertStringStartsWith('%PDF-', $content);
    }
}
