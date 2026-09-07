<?php

namespace App\Entity;

use App\Repository\AutorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AutorRepository::class)]
#[ORM\Table(name: '`Autor`')]
class Autor
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: '`CodAu`', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: '`Nome`', type: 'string', length: 40)]
    #[Assert\NotBlank(message: 'O nome do autor é obrigatório.')]
    #[Assert\Length(max: 40, maxMessage: 'O nome não pode ter mais de {{ limit }} caracteres.')]
    private ?string $nome = null;


    #[ORM\ManyToMany(targetEntity: Livro::class, mappedBy: 'autores')]
    private Collection $livros;

    public function __construct()
    {
        $this->livros = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setNome(string $nome): static
    {
        $this->nome = $nome;
        return $this;
    }


    public function getLivros(): Collection
    {
        return $this->livros;
    }

    public function __toString(): string
    {
        return (string) $this->nome;
    }
}
