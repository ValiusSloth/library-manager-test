<?php

namespace App\Tests\Integration\Controller;

use App\Controller\BookController;
use App\Entity\Book;
use App\Repository\BookRepository;
use App\Service\BookService;
use App\Tests\Support\DatabaseTestTrait;
use App\Tests\Support\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BookControllerTest extends KernelTestCase
{
    use DatabaseTestTrait;
    use FixturesTrait;
    
    private BookRepository $repository;
    private BookController $controller;
    private BookService $bookService;

    protected function setUp(): void
    {
        self::bootKernel();
        
        $this->getEntityManager();
        $this->repository = $this->entityManager->getRepository(Book::class);
        
        $csrfTokenManager = static::getContainer()->get('security.csrf.token_manager');
        
        $this->bookService = new BookService(
            $this->entityManager,
            $this->repository,
            $csrfTokenManager
        );
        
        $this->controller = new BookController($this->bookService);
        
        $this->clearDatabase('book');
        $this->createTestBooks(2);
    }

    public function testIndex(): void
    {
        $request = new Request();
        $request->query->set('page', 1);
        $request->query->set('limit', 10);
        
        $container = $this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class);
        $container->method('has')->willReturn(true);
        $container->method('get')->willReturn($this->createMock(\Twig\Environment::class));
        
        $this->controller->setContainer($container);
        
        $response = $this->controller->index($request);
        
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testList(): void
    {
        $request = new Request();
        $request->query->set('page', 1);
        $request->query->set('limit', 10);
        $request->query->set('ajax', 1);
        
        $container = $this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class);
        $container->method('has')->willReturn(true);
        
        $container->method('get')->willReturn(
            $this->getMockBuilder(\Twig\Environment::class)
                ->disableOriginalConstructor()
                ->getMock()
        );
        
        $this->controller->setContainer($container);
        
        $controllerMock = $this->getMockBuilder(BookController::class)
            ->setConstructorArgs([$this->bookService])
            ->onlyMethods(['renderView'])
            ->getMock();
        
        $controllerMock->method('renderView')
            ->willReturn('<div>Mocked HTML</div>');
        
        $controllerMock->setContainer($container);
        
        $response = $controllerMock->list($request);
        
        $this->assertInstanceOf(Response::class, $response);
        
        $this->assertStringContainsString('<div', $response->getContent());
    }

    public function testSearch(): void
    {
        $request = new Request();
        $request->query->set('q', 'Test');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        
        $container = $this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class);
        $container->method('has')->willReturn(true);
        $container->method('get')->willReturn($this->createMock(\Twig\Environment::class));
        
        $this->controller->setContainer($container);
        
        $response = $this->controller->search($request);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        
        $data = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('results', $data);
        $this->assertArrayHasKey('count', $data);
        $this->assertArrayHasKey('searchTerm', $data);
        $this->assertEquals('Test', $data['searchTerm']);
    }

    public function testAlert(): void
    {
        $request = new Request();
        $request->query->set('type', 'success');
        $request->query->set('message', 'Test alert message');
        
        $container = $this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class);
        $container->method('has')->willReturn(true);
        $container->method('get')->willReturn($this->createMock(\Twig\Environment::class));
        
        $this->controller->setContainer($container);
        
        $response = $this->controller->alert($request);
        
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testShow(): void
    {
        $book = $this->createTestBook();
        
        $container = $this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class);
        $container->method('has')->willReturn(true);
        $container->method('get')->willReturn($this->createMock(\Twig\Environment::class));
        
        $this->controller->setContainer($container);
        
        $response = $this->controller->show($book);
        
        $this->assertInstanceOf(Response::class, $response);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->closeEntityManager();
    }
}