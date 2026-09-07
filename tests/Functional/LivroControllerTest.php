<?php

namespace App\Tests\Functional;

use App\Entity\Assunto;
use App\Entity\Autor;
use App\Entity\Livro;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LivroControllerTest extends WebTestCase
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
    public function testIndexPageLoadsSuccessfully(): void
    {
        $this->client->request('GET', '/livro/');
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorTextContains('h1', 'Acervo de Livros');
    }

    #[Test]
    public function testNewLivroFormAndSubmissionWithMoneyTransformer(): void
    {
        $em = $this->getEntityManager();
        $autor = (new Autor())->setNome('Autor Livro Teste');
        $assunto = (new Assunto())->setDescricao('Gênero Livro Teste');
        $em->persist($autor);
        $em->persist($assunto);
        $em->flush();

        $crawler = $this->client->request('GET', '/livro/novo');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Salvar Livro')->form([
            'livro[titulo]' => 'Livro Funcional E2E',
            'livro[editora]' => 'Editora Teste',
            'livro[edicao]' => 3,
            'livro[anoPublicacao]' => '2023',
            'livro[valor]' => '150,50',
            'livro[autores]' => [(string) $autor->getId()],
            'livro[assuntos]' => [(string) $assunto->getId()],
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/livro/');

        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Livro cadastrado com sucesso!');

        // Verifica integridade da gravação no banco e conversão correta do valor
        $livro = $this->getEntityManager()->getRepository(Livro::class)->findOneBy(['titulo' => 'Livro Funcional E2E']);
        $this->assertNotNull($livro);
        $this->assertSame('150.50', $livro->getValor());
        $this->assertSame(3, $livro->getEdicao());
        $this->assertSame('2023', $livro->getAnoPublicacao());
        $this->assertCount(1, $livro->getAutores());
        $this->assertCount(1, $livro->getAssuntos());
    }

    #[Test]
    public function testEditLivro(): void
    {
        $em = $this->getEntityManager();
        $autor = (new Autor())->setNome('Autor Para Edicao');
        $assunto = (new Assunto())->setDescricao('Assunto Edicao');
        $livro = (new Livro())
            ->setTitulo('Livro Antes da Edicao')
            ->setEditora('Editora Antiga')
            ->setEdicao(1)
            ->setAnoPublicacao('2020')
            ->setValor('40.00')
            ->addAutor($autor)
            ->addAssunto($assunto);

        $em->persist($autor);
        $em->persist($assunto);
        $em->persist($livro);
        $em->flush();
        $livroId = $livro->getId();

        $crawler = $this->client->request('GET', sprintf('/livro/%d/editar', $livroId));
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Atualizar Livro')->form([
            'livro[titulo]' => 'Livro Com Titulo Alterado',
            'livro[valor]' => '99,90',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/livro/');

        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Livro atualizado com sucesso!');

        $livroAtualizado = $this->getEntityManager()->getRepository(Livro::class)->find($livroId);
        $this->assertSame('Livro Com Titulo Alterado', $livroAtualizado->getTitulo());
        $this->assertSame('99.90', $livroAtualizado->getValor());
    }

    #[Test]
    public function testDeleteLivro(): void
    {
        $em = $this->getEntityManager();
        $autor = (new Autor())->setNome('Autor Livro Deletar');
        $assunto = (new Assunto())->setDescricao('Assunto Deletar');
        $livro = (new Livro())
            ->setTitulo('Livro Para Excluir')
            ->setEditora('Editora Excluir')
            ->setEdicao(1)
            ->setAnoPublicacao('2021')
            ->setValor('30.00')
            ->addAutor($autor)
            ->addAssunto($assunto);

        $em->persist($autor);
        $em->persist($assunto);
        $em->persist($livro);
        $em->flush();
        $livroId = $livro->getId();

        $crawler = $this->client->request('GET', '/livro/');
        $form = $crawler->filter(sprintf('form[action="/livro/%d"]', $livroId))->form();

        $this->client->submit($form);

        $this->assertResponseRedirects('/livro/');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Livro excluído com sucesso!');

        $deletedLivro = $this->getEntityManager()->getRepository(Livro::class)->find($livroId);
        $this->assertNull($deletedLivro);
    }
}
