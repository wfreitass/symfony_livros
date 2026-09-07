<?php

namespace App\Tests\Unit\Twig;

use App\Twig\Components\Alert;
use App\Twig\Components\Button;
use App\Twig\Components\Card;
use App\Twig\Components\Navbar;
use App\Twig\Components\PageHeader;
use App\Twig\Components\Pagination;
use App\Twig\Components\Table;
use Knp\Component\Pager\Pagination\PaginationInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ComponentsTest extends TestCase
{
    #[Test]
    public function testButtonComponentDefaultsAndCustomValues(): void
    {
        $btn = new Button();
        $this->assertNull($btn->label);
        $this->assertSame('button', $btn->type);
        $this->assertSame('primary', $btn->variant);
        $this->assertNull($btn->size);
        $this->assertNull($btn->icon);
        $this->assertNull($btn->href);
        $this->assertFalse($btn->disabled);
        $this->assertSame('btn btn-primary', $btn->getClasses());

        $btn->variant = 'light';
        $this->assertSame('btn btn-light border', $btn->getClasses());

        $btn->variant = 'outline-danger';
        $btn->size = 'sm';
        $btn->icon = 'bi-trash';
        $btn->type = 'submit';
        $btn->disabled = true;
        $this->assertSame('btn btn-outline-danger btn-sm', $btn->getClasses());
    }

    #[Test]
    public function testAlertComponentDefaultsAndCustomValues(): void
    {
        $alert = new Alert();
        $this->assertSame('info', $alert->type);
        $this->assertSame('', $alert->message);
        $this->assertTrue($alert->dismissible);
        $this->assertSame('info', $alert->getTypeClass());

        $alert->type = 'error';
        $alert->message = 'Erro ao salvar';
        $alert->dismissible = false;
        $this->assertSame('danger', $alert->getTypeClass());
        $this->assertSame('Erro ao salvar', $alert->message);
        $this->assertFalse($alert->dismissible);

        $alert->type = 'success';
        $this->assertSame('success', $alert->getTypeClass());
    }

    #[Test]
    public function testCardComponentDefaultsAndCustomValues(): void
    {
        $card = new Card();
        $this->assertNull($card->title);
        $this->assertNull($card->icon);
        $this->assertNull($card->variant);
        $this->assertTrue($card->shadow);
        $this->assertFalse($card->border);
        $this->assertSame('mb-4', $card->margin);
        $this->assertSame('card border-0 shadow-sm mb-4', $card->getClasses());

        $card->title = 'Meus Livros';
        $card->icon = 'bi-book';
        $this->assertSame('Meus Livros', $card->title);
        $this->assertSame('bi-book', $card->icon);

        $card->variant = 'primary';
        $card->margin = '';
        $this->assertSame('card border-0 shadow-sm bg-primary text-white', $card->getClasses());

        $card->variant = 'info';
        $this->assertSame('card border-0 shadow-sm bg-info text-dark', $card->getClasses());
    }

    #[Test]
    public function testPageHeaderComponentDefaultsAndCustomValues(): void
    {
        $header = new PageHeader();
        $this->assertSame('', $header->title);
        $this->assertNull($header->subtitle);
        $this->assertSame([], $header->actions);

        $header->title = 'Cadastro de Livro';
        $header->subtitle = 'Preencha os campos';
        $header->actions = [
            ['label' => 'Voltar', 'url' => '/livro/', 'class' => 'btn-secondary', 'icon' => 'bi-arrow-left'],
        ];

        $this->assertSame('Cadastro de Livro', $header->title);
        $this->assertSame('Preencha os campos', $header->subtitle);
        $this->assertCount(1, $header->actions);
        $this->assertSame('Voltar', $header->actions[0]['label']);
    }

    #[Test]
    public function testNavbarComponentDefaultsAndActiveRoute(): void
    {
        $requestStack = new RequestStack();
        $navbar = new Navbar($requestStack);

        $this->assertSame('Gestão de Livros', $navbar->brand);
        $this->assertSame('app_home', $navbar->brandRoute);
        $this->assertFalse($navbar->isRouteActive('app_livro'));

        $request = new Request([], [], ['_route' => 'app_livro_index']);
        $requestStack->push($request);

        $this->assertSame('app_livro_index', $navbar->getCurrentRoute());
        $this->assertTrue($navbar->isRouteActive('app_livro'));
        $this->assertFalse($navbar->isRouteActive('app_autor'));

        $homeRequest = new Request([], [], ['_route' => 'app_home']);
        $requestStack->pop();
        $requestStack->push($homeRequest);
        $this->assertFalse($navbar->isRouteActive('app_livro'));
    }

    #[Test]
    public function testTableComponentDefaultsAndClasses(): void
    {
        $table = new Table();
        $this->assertSame([], $table->columns);
        $this->assertNull($table->items);
        $this->assertTrue($table->hover);
        $this->assertFalse($table->striped);
        $this->assertFalse($table->bordered);
        $this->assertTrue($table->alignMiddle);
        $this->assertNull($table->size);
        $this->assertSame('table table-hover align-middle', $table->getClasses());
        $this->assertSame(1, $table->getEffectiveColspan());

        $table->striped = true;
        $table->bordered = true;
        $table->size = 'sm';
        $table->columns = ['Cód.', 'Nome', 'Ações'];
        $this->assertSame('table table-hover table-striped table-bordered align-middle table-sm', $table->getClasses());
        $this->assertSame(3, $table->getEffectiveColspan());

        $table->colspan = 5;
        $this->assertSame(5, $table->getEffectiveColspan());
    }

    #[Test]
    public function testPaginationComponentDefaultsAndCalculations(): void
    {
        $comp = new Pagination();
        $this->assertNull($comp->pagination);
        $this->assertSame('itens', $comp->itemName);
        $this->assertTrue($comp->borderTop);
        $this->assertSame('', $comp->class);
        $this->assertFalse($comp->hasItems());
        $this->assertSame(0, $comp->getFromItem());
        $this->assertSame(0, $comp->getToItem());
        $this->assertSame(0, $comp->getTotal());

        // Simula página 2 com 5 itens de um total de 12
        $pagination = $this->createStub(PaginationInterface::class);
        $pagination->method('getCurrentPageNumber')->willReturn(2);
        $pagination->method('getItemNumberPerPage')->willReturn(5);
        $pagination->method('getTotalItemCount')->willReturn(12);
        $pagination->method('count')->willReturn(5);

        $comp->pagination = $pagination;
        $comp->itemName = 'livros';
        $this->assertTrue($comp->hasItems());
        $this->assertSame(6, $comp->getFromItem());
        $this->assertSame(10, $comp->getToItem());
        $this->assertSame(12, $comp->getTotal());

        // Simula última página (página 3 com 2 itens)
        $paginationLast = $this->createStub(PaginationInterface::class);
        $paginationLast->method('getCurrentPageNumber')->willReturn(3);
        $paginationLast->method('getItemNumberPerPage')->willReturn(5);
        $paginationLast->method('getTotalItemCount')->willReturn(12);
        $paginationLast->method('count')->willReturn(2);

        $comp->pagination = $paginationLast;
        $this->assertSame(11, $comp->getFromItem());
        $this->assertSame(12, $comp->getToItem());
        $this->assertSame(12, $comp->getTotal());
    }
}
