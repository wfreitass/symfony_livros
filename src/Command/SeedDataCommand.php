<?php

namespace App\Command;

use App\Entity\Assunto;
use App\Entity\Autor;
use App\Entity\Livro;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed',
    description: 'Popula o banco com dados de teste e demonstração (livros, autores, assuntos e co-autorias)'
)]
class SeedDataCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('clean', null, InputOption::VALUE_NONE, 'Remove dados existentes antes de popular');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Semeando o banco de dados com dados de demonstração');

        $clean = $input->getOption('clean');

        if ($clean) {
            $io->section('Limpando registros existentes...');
            $connection = $this->em->getConnection();
            $connection->executeStatement('TRUNCATE TABLE "Livro_Autor", "Livro_Assunto", "Livro", "Autor", "Assunto" RESTART IDENTITY CASCADE');
            $io->success('Tabelas limpas com sucesso.');
        }

        // 1. Assuntos
        $assuntoRepo = $this->em->getRepository(Assunto::class);
        $assuntosData = [
            'Romance',
            'Ficção Científica',
            'Realismo',
            'Fantasia',
            'Tecnologia',
            'Filosofia',
        ];

        $assuntosMap = [];
        foreach ($assuntosData as $desc) {
            $assunto = $assuntoRepo->findOneBy(['descricao' => $desc]);
            if (!$assunto) {
                $assunto = (new Assunto())->setDescricao($desc);
                $this->em->persist($assunto);
            }
            $assuntosMap[$desc] = $assunto;
        }

        // 2. Autores
        $autorRepo = $this->em->getRepository(Autor::class);
        $autoresData = [
            'Machado de Assis',
            'Clarice Lispector',
            'Guimarães Rosa',
            'George Orwell',
            'Neil Gaiman',
            'Terry Pratchett',
        ];

        $autoresMap = [];
        foreach ($autoresData as $nome) {
            $autor = $autorRepo->findOneBy(['nome' => $nome]);
            if (!$autor) {
                $autor = (new Autor())->setNome($nome);
                $this->em->persist($autor);
            }
            $autoresMap[$nome] = $autor;
        }

        $this->em->flush();

        // 3. Livros com Co-autorias e Múltiplos Assuntos
        $livroRepo = $this->em->getRepository(Livro::class);
        $livrosData = [
            [
                'titulo' => 'Dom Casmurro',
                'editora' => 'Garnier',
                'edicao' => 1,
                'ano' => '1899',
                'valor' => '49.90',
                'autores' => ['Machado de Assis'],
                'assuntos' => ['Romance', 'Realismo'],
            ],
            [
                'titulo' => 'Memórias Póstumas de Brás Cubas',
                'editora' => 'Tipografia Nacional',
                'edicao' => 1,
                'ano' => '1881',
                'valor' => '54.50',
                'autores' => ['Machado de Assis'],
                'assuntos' => ['Romance', 'Realismo', 'Filosofia'],
            ],
            [
                'titulo' => 'A Hora da Estrela',
                'editora' => 'José Olympio',
                'edicao' => 1,
                'ano' => '1977',
                'valor' => '39.90',
                'autores' => ['Clarice Lispector'],
                'assuntos' => ['Romance', 'Filosofia'],
            ],
            [
                'titulo' => 'Grande Sertão: Veredas',
                'editora' => 'José Olympio',
                'edicao' => 1,
                'ano' => '1956',
                'valor' => '89.90',
                'autores' => ['Guimarães Rosa'],
                'assuntos' => ['Romance'],
            ],
            [
                'titulo' => '1984',
                'editora' => 'Companhia das Letras',
                'edicao' => 3,
                'ano' => '1949',
                'valor' => '44.90',
                'autores' => ['George Orwell'],
                'assuntos' => ['Ficção Científica', 'Filosofia'],
            ],
            [
                'titulo' => 'Belas Maldições',
                'editora' => 'Bertrand Brasil',
                'edicao' => 2,
                'ano' => '1990',
                'valor' => '64.90',
                // Livro com múltiplos autores conforme destacado no desafio!
                'autores' => ['Neil Gaiman', 'Terry Pratchett'],
                'assuntos' => ['Fantasia', 'Romance'],
            ],
        ];

        $criados = 0;
        foreach ($livrosData as $lData) {
            $livroExistente = $livroRepo->findOneBy(['titulo' => $lData['titulo']]);
            if ($livroExistente) {
                continue;
            }

            $livro = (new Livro())
                ->setTitulo($lData['titulo'])
                ->setEditora($lData['editora'])
                ->setEdicao($lData['edicao'])
                ->setAnoPublicacao($lData['ano'])
                ->setValor($lData['valor']);

            foreach ($lData['autores'] as $autorNome) {
                if (isset($autoresMap[$autorNome])) {
                    $livro->addAutore($autoresMap[$autorNome]);
                }
            }

            foreach ($lData['assuntos'] as $assuntoDesc) {
                if (isset($assuntosMap[$assuntoDesc])) {
                    $livro->addAssunto($assuntosMap[$assuntoDesc]);
                }
            }

            $this->em->persist($livro);
            $criados++;
        }

        $this->em->flush();

        $io->success(sprintf('Base populada com sucesso! (%d livros novos inseridos)', $criados));

        return Command::SUCCESS;
    }
}
