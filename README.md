# 📚 Sistema de Gestão e Relatórios de Livros

[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Symfony](https://img.shields.io/badge/Symfony-8.1-000000?logo=symfony&logoColor=white)](https://symfony.com/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?logo=postgresql&logoColor=white)](https://www.postgresql.org/)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker&logoColor=white)](https://www.docker.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![PHPUnit](https://img.shields.io/badge/Tests-55%20Passed-3776AB?logo=pytest&logoColor=white)](https://phpunit.de/)

Sistema corporativo para cadastro e controle de acervo de livros, autores e assuntos com dashboard executivo, gráficos analíticos interativos e exportação de relatórios em PDF, desenvolvido como solução para o Desafio Técnico de Engenharia de Software.

---

## 🎯 Destaques e Decisões de Arquitetura

O projeto foi concebido seguindo os princípios de **Clean Code**, **SOLID**, **Service Layer** e as melhores práticas do ecossistema Symfony/PHP moderno:

### 1. Service Layer & Dependency Inversion (SOLID)
- **Controllers Enxutos (Thin Controllers):** Os controllers são responsáveis apenas por receber as requisições HTTP, delegar as regras para os serviços de aplicação e retornar as respostas correspondentes.
- **Interfaces & Contratos:** Os controllers dependem exclusivamente de interfaces (`LivroServiceInterface`, `AutorServiceInterface`, `AssuntoServiceInterface`, `RelatorioServiceInterface`, `PdfServiceInterface`), permitindo fácil substituição e desacoplamento para testes.
- **Classe Base Genérica:** Implementação de `AbstractEntityService` com encapsulamento de persistência e tratamento cirúrgico de exceções.

### 2. Modelagem Relacional Fiel ao Legado
- **Fidelidade Integral ao Esquema:** Mapeamento exato dos nomes físicos de tabelas e colunas exigidos no modelo relacional do desafio (`Livro`, `Autor`, `Assunto`, `Codl`, `CodAu`, `codAs`, `Livro_Autor`, `Livro_Assunto`).
- **PHP 8.4 & Doctrine ORM 3.x:** Mapeamento moderno via atributos nativos (`#[ORM\Table]`, `#[ORM\Column(name: '`Codl`')]`), mantendo o código PHP com propriedades em camelCase (`$id`, `$titulo`, `$anoPublicacao`, `$valor`).
- **Requisito do Valor Monetário (R$):** Armazenado no banco como `DECIMAL(10,2)` no PostgreSQL, com conversão bidirecional via `BrazilianMoneyTransformer` no formulário e filtro Twig `|money_br` na apresentação.

### 3. Paginação Inteligente com KnpPaginatorBundle
- **Padrão de 5 Itens por Página:** Configurado centralmente em `config/packages/knp_paginator.yaml` com template deslizante do Bootstrap 5 (`@KnpPaginator/Pagination/bootstrap_v5_pagination.html.twig`).
- **Otimização em QueryBuilders:** Repositórios expõem métodos geradores de queries paginadas (`createAllWithAutoresAndAssuntosQueryBuilder()`, etc.) prevenindo queries N+1 e garantindo alta performance mesmo em grandes massas de dados.
- **Integração na Service Layer:** Métodos `listPaginated(int $page = 1, int $limit = 5): PaginationInterface` padronizados em todos os contratos de serviço.

### 4. Relatório Gerencial Obrigatório com VIEW SQL Nativa
- **Versionamento via Migrations:** Criação da view `vw_relatorio_livros` diretamente no PostgreSQL via Doctrine Migrations.
- **Agrupamento por Autor com Co-autorias:** A consulta é executada via Doctrine DBAL pelo `RelatorioService`, agrupando as obras por autor e tratando cenários onde um livro possui múltiplos autores e múltiplos assuntos sem redundância incorreta.
- **Gráficos Nativos Symfony UX Chart.js:** Configurados no PHP via `ChartBuilderInterface` e renderizados de forma reativa pelo Symfony UX / Stimulus.
- **Exportação em PDF:** Geração de documento corporativo em formato A4 utilizando **Dompdf**, com sumário executivo, KPIs e detalhamento das obras agrupadas por autor.

### 5. Componentes Reutilizáveis com Symfony UX Twig Component
- Componentização declarativa oficial do Symfony UX (`<twig:PageHeader>`, `<twig:Card>`, `<twig:Table>`, `<twig:Button>`, `<twig:Navbar>`, `<twig:Alert>`, `<twig:Pagination>`).
- Redução drástica de repetição de HTML nos templates com parametrização tipada no PHP (`#[AsTwigComponent]`).

### 6. Resiliência e Integridade de Dados
- **Prevenção de Consultas N+1:** Método customizado no `LivroRepository` (`createAllWithAutoresAndAssuntosQueryBuilder()`) utilizando `LEFT JOIN` e `addSelect` para carregar livros, autores e assuntos em uma única consulta otimizada.
- **Tratamento Cirúrgico de Exclusões:** Bloqueio de exclusão para autores ou assuntos que possuam livros vinculados com lançamento de `EntityInUseException`, emitindo feedback visual amigável (`alert-danger`) em vez de páginas de erro genéricas.

---

## 🛠️ Tecnologias Utilizadas

- **PHP 8.4-FPM** (com `opcache`, `intl`, `pdo_pgsql`)
- **Symfony 8.1** (Framework completo com autowiring e atributos nativos)
- **PostgreSQL 16** (com VIEW SQL versionada)
- **Nginx (Alpine)**
- **Doctrine ORM 3.x & Doctrine Migrations**
- **KnpPaginatorBundle 6.x** (Paginação configurada para 5 itens por página com Bootstrap 5)
- **Symfony UX Twig Components & Symfony UX Chart.js**
- **Dompdf 3.x**
- **PHPUnit 13**
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

### 2. Subir os Containers Docker
```bash
docker compose up -d --build
```

### 3. Executar as Migrations (Tabelas + VIEW SQL)
```bash
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```

### 4. Popular Dados de Demonstração (Seed)
Execute o comando de carga rápida para semear o banco com autores clássicos, livros com co-autorias e múltiplos assuntos:
```bash
docker compose exec app php bin/console app:seed --clean --no-interaction
```

### 5. Acessar a Aplicação
Abra no seu navegador:
👉 **[http://localhost:8080](http://localhost:8080)**

---

## 🧪 Suíte de Testes Automatizados (TDD)

O projeto conta com **55 testes e 275 asserções** cobrindo testes unitários e testes funcionais HTTP de ponta a ponta:

```bash
docker compose exec app bin/phpunit
```

### Cobertura da Suíte:
1. **Testes Funcionais / HTTP (`WebTestCase`):**
   - [`HomeControllerTest`](file:///home/workspace/livros/tests/Functional/HomeControllerTest.php): Requisições na raiz `/`, verificação dos cards de KPI e links rápidos.
   - [`LivroControllerTest`](file:///home/workspace/livros/tests/Functional/LivroControllerTest.php): Listagem paginada (5 itens/página), formulário de cadastro com conversão de moeda (`BrazilianMoneyTransformer`), edição e exclusão.
   - [`RelatorioControllerTest`](file:///home/workspace/livros/tests/Functional/RelatorioControllerTest.php): Renderização da página analítica e validação do download de PDF (`Content-Type: application/pdf`, cabeçalho `%PDF-`).
2. **Testes Unitários:**
   - Entidades e regras de domínio ([`LivroTest`](file:///home/workspace/livros/tests/Unit/Entity/LivroTest.php)).
   - Serviços de negócio e paginação ([`LivroServiceTest`](file:///home/workspace/livros/tests/Unit/Service/LivroServiceTest.php), [`AutorServiceTest`](file:///home/workspace/livros/tests/Unit/Service/AutorServiceTest.php), [`AssuntoServiceTest`](file:///home/workspace/livros/tests/Unit/Service/AssuntoServiceTest.php), [`RelatorioServiceTest`](file:///home/workspace/livros/tests/Unit/Service/RelatorioServiceTest.php)).
   - Extensões Twig e formatação monetária ([`MoneyExtensionTest`](file:///home/workspace/livros/tests/Unit/Twig/MoneyExtensionTest.php)).
   - Componentes visuais do Symfony UX ([`ComponentsTest`](file:///home/workspace/livros/tests/Unit/Twig/ComponentsTest.php)).

---

## 🎤 Roteiro para Apresentação Técnica (Entrevista)

Ao apresentar o projeto para a banca avaliadora, sugerimos seguir o seguinte roteiro:

1. **Visão Geral e Arquitetura:**
   - Explicar a separação em **Service Layer** (`src/Service/`) com **Dependency Inversion** através de interfaces (`src/Contract/`).
   - Apresentar a fidelidade do banco ao modelo físico do desafio (`Codl`, `CodAu`, `codAs`), demonstrando o mapeamento limpo com atributos do Doctrine ORM.
2. **Execução e Seed:**
   - Demonstrar o comando `php bin/console app:seed --clean` que popula o catálogo com dados reais e co-autorias (*"Belas Maldições"* com Neil Gaiman e Terry Pratchett).
3. **Tela Inicial e Navegação (`/`):**
   - Mostrar a tela inicial com os indicadores em tempo real e atalhos diretos para os módulos.
4. **CRUDs, Validação e Paginação:**
   - Demonstrar a paginação nativa com `KnpPaginatorBundle` limitada a 5 itens por página via componente `<twig:Pagination>`.
   - Demonstrar a máscara e validação de moeda no cadastro de livro (aceitando formato brasileiro `150,50` ou `R$ 150,50`).
   - Tentar excluir um autor vinculado a livros (ex: Machado de Assis) e mostrar o feedback de proteção (`EntityInUseException`).
5. **Relatório Gerencial com VIEW SQL:**
   - Exibir a VIEW `vw_relatorio_livros` no PostgreSQL via migration.
   - Demonstrar o encapsulamento das consultas no `RelatorioRepository` e a agregação no `RelatorioService`.
   - Demonstrar a tela web do relatório com gráficos interativos do Symfony UX Chart.js e agrupamento por autor.
   - Gerar o relatório corporativo em PDF com um clique.
6. **Qualidade de Código & Testes:**
   - Executar `docker compose exec app bin/phpunit` na frente dos avaliadores mostrando **100% de aprovação em 55 testes**.
   - Rodar os linters do Symfony (`lint:container`, `lint:twig`, `lint:yaml`).

---

## 📋 Estrutura de Diretórios

```text
livros/
├── assets/                  # Assets gerenciados pelo AssetMapper e controllers Stimulus
├── config/                  # Configurações do framework, bundles e rotas
├── docker/                  # Configuração do servidor Web Nginx
├── migrations/              # Migrations do Doctrine (incluindo a VIEW SQL)
├── src/
│   ├── Command/             # Comandos CLI (app:seed)
│   ├── Contract/            # Interfaces de serviços e repositórios (DIP / SOLID)
│   ├── Controller/          # Thin Controllers (Home, Livro, Autor, Assunto, Relatorio)
│   ├── Entity/              # Entidades Doctrine mapeadas para o esquema físico
│   ├── Exception/           # Exceções de domínio (EntityInUseException)
│   ├── Form/                # FormTypes e DataTransformers (BrazilianMoneyTransformer)
│   ├── Repository/          # Repositórios Doctrine e DBAL (RelatorioRepository, LivroRepository, etc.)
│   ├── Service/             # Camada de serviços (RelatorioService, LivroService, etc.)
│   └── Twig/                # Componentes PHP do UX Twig e extensões de formatação
├── templates/
│   ├── components/          # Templates dos componentes Twig reutilizáveis
│   ├── home/                # Painel de boas-vindas com métricas e atalhos
│   ├── livro/               # CRUD do acervo de livros
│   ├── autor/               # CRUD de autores
│   ├── assunto/             # CRUD de assuntos
│   └── relatorio/           # Dashboard com gráficos Chart.js e layout do PDF
├── tests/
│   ├── Functional/          # Testes HTTP de ponta a ponta com WebTestCase
│   └── Unit/                # Testes unitários (Services, Entities, Twig, Form)
├── docker-compose.yml       # Orquestração multicontêiner (PHP 8.4 + Postgres 16 + Nginx)
└── Dockerfile               # Build da imagem PHP 8.4-FPM
```
