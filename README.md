# 📚 Sistema de Gestão e Relatórios de Livros

[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Symfony](https://img.shields.io/badge/Symfony-8.1-000000?logo=symfony&logoColor=white)](https://symfony.com/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?logo=postgresql&logoColor=white)](https://www.postgresql.org/)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker&logoColor=white)](https://www.docker.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![PHPUnit](https://img.shields.io/badge/Tests-PHPUnit-3776AB?logo=pytest&logoColor=white)](https://phpunit.de/)

Sistema completo para cadastro de acervo de livros, autores e assuntos com dashboard executivo, gráficos analíticos e exportação em PDF, desenvolvido como solução para o Desafio Técnico de Engenharia de Software.

---

## 🎯 Destaques e Decisões de Arquitetura

O projeto foi concebido seguindo os princípios de **Clean Code**, **SOLID** e as melhores práticas do ecossistema PHP moderno:

### 1. Modelagem Fiel ao Legado com Código Moderno
- **Fidelidade ao Esquema:** Mapeamento integral dos nomes de tabelas e colunas exigidos no modelo relacional original (`Autor`, `Livro`, `Assunto`, `CodAu`, `Codl`, `codAs`, `Livro_Autor`, `Livro_Assunto`).
- **PSR-12 & Clean Code:** No código PHP foram mantidas as convenções modernas (`$id`, `$titulo`, `$anoPublicacao`, métodos camelCase), mapeadas via atributos nativos do Doctrine ORM (`#[ORM\Column(name: '`Codl`')]`).
- **Requisito 21 (Valor do Livro):** Campo de valor monetário adicionado como `DECIMAL(10,2)` no PostgreSQL, com validação de formato e integridade.

### 2. Relatório Gerencial Obrigatório com VIEW SQL Nativa (Requisito 17)
- **Consulta via VIEW:** Criação e versionamento da view `vw_relatorio_livros` no PostgreSQL via **Doctrine Migrations**.
- **Desacoplamento e Agrupamento:** A consulta à view é executada pelo `RelatorioService` via Doctrine DBAL, agrupando as informações por Autor e consolidando múltiplos autores e assuntos sem duplicação inconsistente de dados.
- **Gráficos com Symfony UX Chart.js:** Em vez de scripts soltos de CDN, os gráficos são configurados orientados a objetos no PHP (`ChartBuilderInterface`) e renderizados de forma nativa pelo Symfony UX com AssetMapper/Stimulus.
- **Exportação em PDF:** Geração de documento corporativo em formato A4 utilizando o **Dompdf**.

### 3. Camada de Apresentação com Twig Moderno e Bootstrap 5
- **Componentização:** Utilização do `symfony/ux-twig-component` para criação de componentes reutilizáveis (`<twig:PageHeader>`, `<twig:Card>`, `<twig:Alert>`), eliminando repetição de HTML.
- **Filtro de Moeda com Atributos PHP 8.4:** Utilização do atributo `#[AsTwigFilter('money_br')]` na classe `MoneyExtension` para formatação em padrão brasileiro (`R$ 1.500,00`).
- **DataTransformer do Symfony Forms:** Implementação do `BrazilianMoneyTransformer` no formulário de Livro, realizando a conversão bidirecional transparente entre a entrada do usuário (`150,50` ou `R$ 1.250,00`) e o armazenamento decimal no banco.
- **Validação Integrada:** Configuração com `novalidate` para acionar a validação do Symfony Validator com feedback visual elegante do Bootstrap (`is-invalid` e `.invalid-feedback`).

### 4. Otimização de Performance e Resiliência
- **Prevenção do Problema N+1:** Método customizado no `LivroRepository` (`findAllWithAutoresAndAssuntos()`) utilizando `LEFT JOIN` e `addSelect` para carregar livros, autores e assuntos em uma única consulta otimizada.
- **Tratamento Específico de Erros:** Captura cirúrgica de `ForeignKeyConstraintViolationException` para impedir exclusões que violem a integridade referencial, exibindo mensagens amigáveis ao invés de páginas de erro genéricas.

---

## 🛠️ Tecnologias Utilizadas

- **PHP 8.4-FPM**
- **Symfony 8.1**
- **PostgreSQL 16**
- **Nginx (Alpine)**
- **Doctrine ORM 3.x & Migrations**
- **Symfony UX Twig Components & Symfony UX Chart.js**
- **Dompdf**
- **PHPUnit 10+**
- **Bootstrap 5.3 & Bootstrap Icons**

---

## 🚀 Como Executar o Projeto

### Pré-requisitos
- [Docker](https://docs.docker.com/get-docker/) e [Docker Compose](https://docs.docker.com/compose/) instalados.

### 1. Clonar e Acessar o Diretório
```bash
git clone https://github.com/wfreitass/symfony_livros
cd livros
```

### 2. Subir o Ambiente Docker
O Dockerfile irá compilar a imagem PHP 8.4 com todas as extensões necessárias (`pdo_pgsql`, `intl`, `zip`, `opcache`):
```bash
docker compose up -d --build
```

### 3. Executar as Migrations (Tabelas + VIEW SQL)
Execute as migrations para criar as tabelas do modelo de dados e a VIEW do relatório no PostgreSQL:
```bash
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```

### 4. Acessar a Aplicação
Abra no navegador:
👉 **[http://localhost:8080](http://localhost:8080)**

---

## 🧪 Execução dos Testes Automatizados (TDD)

A suíte de testes contempla testes unitários (entidades, extensões de template e transformers) e testes funcionais (`WebTestCase` para rotas, CRUDs e exportação de PDF):

```bash
docker compose exec app bin/phpunit
```

---

## 📋 Estrutura de Pastas do Projeto

```text
livros/
├── assets/                  # Controladores Stimulus e assets do AssetMapper
├── config/                  # Configurações do framework e pacotes
├── docker/                  # Configuração do Nginx
├── migrations/              # Migrations versionadas (incluindo a VIEW SQL)
├── src/
│   ├── Controller/          # Controllers (Autor, Assunto, Livro, Relatorio)
│   ├── Entity/              # Entidades Doctrine (Autor, Assunto, Livro)
│   ├── Form/                # FormTypes e DataTransformers
│   ├── Repository/          # Repositórios otimizados (evitando N+1)
│   ├── Service/             # Serviços de negócio (RelatorioService, PdfService)
│   └── Twig/                # Extensões e filtros com atributos PHP 8.4
├── templates/
│   ├── components/          # Componentes Twig reutilizáveis
│   ├── autor/               # Telas do CRUD de Autores
│   ├── assunto/             # Telas do CRUD de Assuntos
│   ├── livro/               # Telas do CRUD de Livros
│   └── relatorio/           # Dashboard com gráficos Chart.js e template PDF
├── tests/
│   ├── Functional/          # Testes funcionais com WebTestCase
│   └── Unit/                # Testes de unidade (Entities, Form, Twig)
├── docker-compose.yml       # Orquestração (App, Web, Database)
└── Dockerfile               # Imagem PHP 8.4-FPM
```



