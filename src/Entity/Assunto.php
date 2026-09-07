<?php

namespace App\Entity;

use App\Repository\AssuntoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AssuntoRepository::class)]
#[ORM\Table(name: '`Assunto`')]
class Assunto
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: '`codAs`', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: '`Descricao`', type: 'string', length: 20)]
    #[Assert\NotBlank(message: 'A descrição do assunto é obrigatória.')]
    #[Assert\Length(max: 20, maxMessage: 'A descrição não pode ter mais de {{ limit }} caracteres.')]
    private ?string $descricao = null;


    #[ORM\ManyToMany(targetEntity: Livro::class, mappedBy: 'assuntos')]
    private Collection $livros;

    public function __construct()
    {
        $this->livros = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function setDescricao(string $descricao): static
    {
        $this->descricao = $descricao;
        return $this;
    }


    public function getLivros(): Collection
    {
        return $this->livros;
    }

    public function __toString(): string
    {
        return (string) $this->descricao;
    }
}
