<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Book;
use App\Tests\Support\DatabaseTestTrait;
use App\Tests\Support\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BookRepositoryTest extends KernelTestCase
{
    use DatabaseTestTrait;
    use FixturesTrait;

    protected function setUp(): void
    {
        self::bootKernel();
        
        $this->getEntityManager();
        
        $this->clearDatabase('book');
        
        $this->createSpecificTestBooks();
    }
    
    private function createSpecificTestBooks(): void
    {
        $books = [
            [
                'title' => 'Test Book 1',
                'author' => 'Author 1',
                'isbn' => '978-1-11111-111-1',
                'publicationDate' => new \DateTime('2020-01-01'),
                'genre' => 'Fiction',
                'copies' => 3
            ],
            [
                'title' => 'Test Book 2',
                'author' => 'Author 2',
                'isbn' => '978-2-22222-222-2',
                'publicationDate' => new \DateTime('2021-02-02'),
                'genre' => 'Science Fiction',
                'copies' => 5
            ],
            [
                'title' => 'Another Book',
                'author' => 'Author 3',
                'isbn' => '978-3-33333-333-3',
                'publicationDate' => new \DateTime('2022-03-03'),
                'genre' => 'Fantasy',
                'copies' => 2
            ],
            [
                'title' => 'Final Test',
                'author' => 'Author 1',
                'isbn' => '978-4-44444-444-4',
                'publicationDate' => new \DateTime('2019-04-04'),
                'genre' => 'Fiction',
                'copies' => 1
            ],
        ];

        $factory = $this->createBookFactory();
        
        foreach ($books as $bookData) {
            $factory->createAndPersist($bookData);
        }
    }

    public function testFindAll(): void
    {
        $repository = $this->entityManager->getRepository(Book::class);
        $books = $repository->findAll();
        $this->assertCount(4, $books);
    }

    public function testFindByPage(): void
    {
        $repository = $this->entityManager->getRepository(Book::class);
        
        $books = $repository->findByPage(1, 2);
        $this->assertCount(2, $books);
        
        $books = $repository->findByPage(2, 2);
        $this->assertCount(2, $books);
        
        $books = $repository->findByPage(3, 2);
        $this->assertCount(0, $books);
    }

    public function testSearchByTerms(): void
    {
        $repository = $this->entityManager->getRepository(Book::class);
        
        $books = $repository->searchByTerms('Test');
        $this->assertCount(3, $books);
        
        $books = $repository->searchByTerms('Author 1');
        $this->assertCount(2, $books);
        
        $books = $repository->searchByTerms('978-1');
        $this->assertCount(1, $books);
        
        $books = $repository->searchByTerms('NonExistentTerm');
        $this->assertCount(0, $books);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->closeEntityManager();
    }
}