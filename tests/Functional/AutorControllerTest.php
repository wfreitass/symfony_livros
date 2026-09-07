<?php

namespace App\Tests\Functional;

use App\Entity\Autor;
use App\Entity\Livro;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AutorControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->getEntityManager()->getConnection()->executeStatement(
            'TRUNCATE TABLE "Livro_Autor", "Livro_Assunto", "Livro", "Autor", "Assunto" RESTART IDENTITY CASCADE'
        );
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    #[Test]
    public function testIndexPageLoadsSuccessfully(): void
    {
        $this->client->request('GET', '/autor/');
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorTextContains('h1', 'Autores');
    }

    #[Test]
    public function testNewAutorFormAndSubmission(): void
    {
        $crawler = $this->client->request('GET', '/autor/novo');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Salvar Autor')->form([
            'autor[nome]' => 'Autor Teste Automatizado',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/autor/');

        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Autor cadastrado com sucesso!');

        $autor = $this->getEntityManager()->getRepository(Autor::class)->findOneBy(['nome' => 'Autor Teste Automatizado']);
        $this->assertNotNull($autor);
    }

    #[Test]
    public function testEditAutor(): void
    {
        $em = $this->getEntityManager();
        $autor = (new Autor())->setNome('Autor Original');
        $em->persist($autor);
        $em->flush();
        $autorId = $autor->getId();

        $crawler = $this->client->request('GET', sprintf('/autor/%d/editar', $autorId));
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Atualizar Autor')->form([
            'autor[nome]' => 'Autor Nome Editado',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/autor/');

        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Autor atualizado com sucesso!');

        $autorAtualizado = $this->getEntityManager()->getRepository(Autor::class)->find($autorId);
        $this->assertSame('Autor Nome Editado', $autorAtualizado->getNome());
    }

    #[Test]
    public function testDeleteAutorSemLivrosVinculados(): void
    {
        $em = $this->getEntityManager();
        $autor = (new Autor())->setNome('Autor Sem Obras');
        $em->persist($autor);
        $em->flush();
        $autorId = $autor->getId();

        $crawler = $this->client->request('GET', '/autor/');
        $form = $crawler->filter(sprintf('form[action="/autor/%d"]', $autorId))->form();

        $this->client->submit($form);

        $this->assertResponseRedirects('/autor/');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Autor excluído com sucesso!');

        $deletedAutor = $this->getEntityManager()->getRepository(Autor::class)->find($autorId);
        $this->assertNull($deletedAutor);
    }

    #[Test]
    public function testDeleteAutorComLivroVinculadoExibeMensagemEspecifica(): void
    {
        $em = $this->getEntityManager();
        $autor = (new Autor())->setNome('Autor Com Obra Para Bloqueio');
        $em->persist($autor);

        $livro = (new Livro())
            ->setTitulo('Livro Para Bloqueio de Exclusao')
            ->setEditora('Editora Teste')
            ->setEdicao(1)
            ->setAnoPublicacao('2024')
            ->setValor('50.00')
            ->addAutor($autor);

        $em->persist($livro);
        $em->flush();
        $autorId = $autor->getId();

        $crawler = $this->client->request('GET', '/autor/');
        $form = $crawler->filter(sprintf('form[action="/autor/%d"]', $autorId))->form();

        $this->client->submit($form);

        $this->assertResponseRedirects('/autor/');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-danger');

        // Garante que o autor NÃO foi excluído
        $autorAindaExiste = $this->getEntityManager()->getRepository(Autor::class)->find($autorId);
        $this->assertNotNull($autorAindaExiste);
    }

    #[Test]
    public function testPagination(): void
    {
        $em = $this->getEntityManager();
        for ($i = 1; $i <= 7; ++$i) {
            $autor = (new Autor())->setNome(sprintf('Autor %02d', $i));
            $em->persist($autor);
        }
        $em->flush();

        // Página 1 deve conter 5 itens (padrão KnpPaginator)
        $crawler = $this->client->request('GET', '/autor/');
        $this->assertResponseIsSuccessful();
        $this->assertCount(5, $crawler->filter('tbody tr'));
        $this->assertSelectorExists('.pagination');

        // Página 2 deve conter os 2 itens restantes
        $crawlerPage2 = $this->client->request('GET', '/autor/?page=2');
        $this->assertResponseIsSuccessful();
        $this->assertCount(2, $crawlerPage2->filter('tbody tr'));
    }
}
