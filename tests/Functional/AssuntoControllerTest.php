<?php

namespace App\Tests\Functional;

use App\Entity\Assunto;
use App\Entity\Autor;
use App\Entity\Livro;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AssuntoControllerTest extends WebTestCase
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
        $this->client->request('GET', '/assunto/');
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorTextContains('h1', 'Assuntos');
    }

    #[Test]
    public function testNewAssuntoFormAndSubmission(): void
    {
        $crawler = $this->client->request('GET', '/assunto/novo');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Salvar Assunto')->form([
            'assunto[descricao]' => 'Terror Psicológico',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/assunto/');

        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Assunto cadastrado com sucesso!');

        $assunto = $this->getEntityManager()->getRepository(Assunto::class)->findOneBy(['descricao' => 'Terror Psicológico']);
        $this->assertNotNull($assunto);
    }

    #[Test]
    public function testEditAssunto(): void
    {
        $em = $this->getEntityManager();
        $assunto = (new Assunto())->setDescricao('Original');
        $em->persist($assunto);
        $em->flush();
        $assuntoId = $assunto->getId();

        $crawler = $this->client->request('GET', sprintf('/assunto/%d/editar', $assuntoId));
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Atualizar Assunto')->form([
            'assunto[descricao]' => 'Editado',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/assunto/');

        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Assunto atualizado com sucesso!');

        $assuntoAtualizado = $this->getEntityManager()->getRepository(Assunto::class)->find($assuntoId);
        $this->assertSame('Editado', $assuntoAtualizado->getDescricao());
    }

    #[Test]
    public function testDeleteAssuntoSemLivros(): void
    {
        $em = $this->getEntityManager();
        $assunto = (new Assunto())->setDescricao('Sem Livros');
        $em->persist($assunto);
        $em->flush();
        $assuntoId = $assunto->getId();

        $crawler = $this->client->request('GET', '/assunto/');
        $form = $crawler->filter(sprintf('form[action="/assunto/%d"]', $assuntoId))->form();

        $this->client->submit($form);

        $this->assertResponseRedirects('/assunto/');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Assunto excluído com sucesso!');

        $deletedAssunto = $this->getEntityManager()->getRepository(Assunto::class)->find($assuntoId);
        $this->assertNull($deletedAssunto);
    }

    #[Test]
    public function testDeleteAssuntoComLivroVinculadoExibeMensagemEspecifica(): void
    {
        $em = $this->getEntityManager();
        $autor = (new Autor())->setNome('Autor Auxiliar');
        $assunto = (new Assunto())->setDescricao('Assunto Em Uso');
        $em->persist($autor);
        $em->persist($assunto);

        $livro = (new Livro())
            ->setTitulo('Livro Com Assunto Em Uso')
            ->setEditora('Editora Teste')
            ->setEdicao(1)
            ->setAnoPublicacao('2024')
            ->setValor('45.00')
            ->addAutor($autor)
            ->addAssunto($assunto);

        $em->persist($livro);
        $em->flush();
        $assuntoId = $assunto->getId();

        $crawler = $this->client->request('GET', '/assunto/');
        $form = $crawler->filter(sprintf('form[action="/assunto/%d"]', $assuntoId))->form();

        $this->client->submit($form);

        $this->assertResponseRedirects('/assunto/');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-danger');

        $assuntoAindaExiste = $this->getEntityManager()->getRepository(Assunto::class)->find($assuntoId);
        $this->assertNotNull($assuntoAindaExiste);
    }

    #[Test]
    public function testPagination(): void
    {
        $em = $this->getEntityManager();
        for ($i = 1; $i <= 7; ++$i) {
            $assunto = (new Assunto())->setDescricao(sprintf('Assunto %02d', $i));
            $em->persist($assunto);
        }
        $em->flush();

        // Página 1 deve conter 5 itens (padrão KnpPaginator)
        $crawler = $this->client->request('GET', '/assunto/');
        $this->assertResponseIsSuccessful();
        $this->assertCount(5, $crawler->filter('tbody tr'));
        $this->assertSelectorExists('.pagination');

        // Página 2 deve conter os 2 itens restantes
        $crawlerPage2 = $this->client->request('GET', '/assunto/?page=2');
        $this->assertResponseIsSuccessful();
        $this->assertCount(2, $crawlerPage2->filter('tbody tr'));
    }
}
