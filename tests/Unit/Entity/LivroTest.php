<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Assunto;
use App\Entity\Autor;
use App\Entity\Livro;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LivroTest extends TestCase
{
    #[Test]
    public function testInstanciacaoECamposBasicos(): void
    {
        $livro = new Livro();
        $livro->setTitulo('Clean Architecture');
        $livro->setEditora('Prentice Hall');
        $livro->setEdicao(1);
        $livro->setAnoPublicacao('2017');
        $livro->setValor('150.50');
        $this->assertSame('Clean Architecture', $livro->getTitulo());
        $this->assertSame('Prentice Hall', $livro->getEditora());
        $this->assertSame(1, $livro->getEdicao());
        $this->assertSame('2017', $livro->getAnoPublicacao());
        $this->assertSame('150.50', $livro->getValor());
    }

    #[Test]
    public function testRelacionamentoComAutores(): void
    {
        $livro = new Livro();
        $autor1 = (new Autor())->setNome('Robert C. Martin');
        $autor2 = (new Autor())->setNome('Martin Fowler');
        $livro->addAutore($autor1);
        $livro->addAutore($autor2);
        $this->assertCount(2, $livro->getAutores());
        $this->assertTrue($livro->getAutores()->contains($autor1));
        $livro->removeAutore($autor1);
        $this->assertCount(1, $livro->getAutores());
    }

    #[Test]
    public function testRelacionamentoComAssuntos(): void
    {
        $livro = new Livro();
        $assunto = (new Assunto())->setDescricao('Engenharia');
        $livro->addAssunto($assunto);
        $this->assertCount(1, $livro->getAssuntos());
        $this->assertTrue($livro->getAssuntos()->contains($assunto));
    }
}
