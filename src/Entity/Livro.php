<?php

namespace App\Entity;

use App\Repository\LivroRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LivroRepository::class)]
#[ORM\Table(name: '`Livro`')]
class Livro
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: '`Codl`', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: '`Titulo`', type: 'string', length: 40)]
    #[Assert\NotBlank(message: 'O título do livro é obrigatório.')]
    #[Assert\Length(max: 40, maxMessage: 'O título não pode ter mais de {{ limit }} caracteres.')]
    private ?string $titulo = null;

    #[ORM\Column(name: '`Editora`', type: 'string', length: 40)]
    #[Assert\NotBlank(message: 'A editora é obrigatória.')]
    #[Assert\Length(max: 40, maxMessage: 'A editora não pode ter mais de {{ limit }} caracteres.')]
    private ?string $editora = null;

    #[ORM\Column(name: '`Edicao`', type: 'integer')]
    #[Assert\NotNull(message: 'A edição é obrigatória.')]
    #[Assert\Positive(message: 'A edição deve ser um número positivo.')]
    private ?int $edicao = null;

    #[ORM\Column(name: '`AnoPublicacao`', type: 'string', length: 4)]
    #[Assert\NotBlank(message: 'O ano de publicação é obrigatório.')]
    #[Assert\Regex(pattern: '/^\d{4}$/', message: 'O ano deve conter 4 dígitos numéricos.')]
    private ?string $anoPublicacao = null;

    // Requisito 21: Campo Valor (R$)
    #[ORM\Column(name: '`Valor`', type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotNull(message: 'O valor do livro é obrigatório.')]
    #[Assert\PositiveOrZero(message: 'O valor não pode ser negativo.')]
    private ?string $valor = null;

    /**
     * @var Collection<int, Autor>
     */
    #[ORM\ManyToMany(targetEntity: Autor::class, inversedBy: 'livros')]
    #[ORM\JoinTable(
        name: '`Livro_Autor`',
        joinColumns: [new ORM\JoinColumn(name: '`Livro_Codl`', referencedColumnName: '`Codl`')],
        inverseJoinColumns: [new ORM\JoinColumn(name: '`Autor_CodAu`', referencedColumnName: '`CodAu`')]
    )]
    #[Assert\Count(min: 1, minMessage: 'Selecione ao menos um autor para o livro.')]
    private Collection $autores;

    /**
     * @var Collection<int, Assunto>
     */
    #[ORM\ManyToMany(targetEntity: Assunto::class, inversedBy: 'livros')]
    #[ORM\JoinTable(
        name: '`Livro_Assunto`',
        joinColumns: [new ORM\JoinColumn(name: '`Livro_Codl`', referencedColumnName: '`Codl`')],
        inverseJoinColumns: [new ORM\JoinColumn(name: '`Assunto_codAs`', referencedColumnName: '`codAs`')]
    )]
    #[Assert\Count(min: 1, minMessage: 'Selecione ao menos um assunto para o livro.')]
    private Collection $assuntos;

    public function __construct()
    {
        $this->autores = new ArrayCollection();
        $this->assuntos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): static
    {
        $this->titulo = $titulo;
        return $this;
    }

    public function getEditora(): ?string
    {
        return $this->editora;
    }

    public function setEditora(string $editora): static
    {
        $this->editora = $editora;
        return $this;
    }

    public function getEdicao(): ?int
    {
        return $this->edicao;
    }

    public function setEdicao(int $edicao): static
    {
        $this->edicao = $edicao;
        return $this;
    }

    public function getAnoPublicacao(): ?string
    {
        return $this->anoPublicacao;
    }

    public function setAnoPublicacao(string $anoPublicacao): static
    {
        $this->anoPublicacao = $anoPublicacao;
        return $this;
    }

    public function getValor(): ?string
    {
        return $this->valor;
    }

    public function setValor(string $valor): static
    {
        $this->valor = $valor;
        return $this;
    }

    /**
     * @return Collection<int, Autor>
     */
    public function getAutores(): Collection
    {
        return $this->autores;
    }

    public function addAutore(Autor $autore): static
    {
        if (!$this->autores->contains($autore)) {
            $this->autores->add($autore);
        }
        return $this;
    }

    public function removeAutore(Autor $autore): static
    {
        $this->autores->removeElement($autore);
        return $this;
    }

    /**
     * @return Collection<int, Assunto>
     */
    public function getAssuntos(): Collection
    {
        return $this->assuntos;
    }

    public function addAssunto(Assunto $assunto): static
    {
        if (!$this->assuntos->contains($assunto)) {
            $this->assuntos->add($assunto);
        }
        return $this;
    }

    public function removeAssunto(Assunto $assunto): static
    {
        $this->assuntos->removeElement($assunto);
        return $this;
    }
}
