<?php

namespace App\Tests\Factory;

use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;

class BookFactory
{
    private EntityManagerInterface $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    public function create(array $attributes = []): Book
    {
        $defaultAttributes = [
            'title' => 'Test Book ' . uniqid(),
            'author' => 'Test Author',
            'isbn' => '978-3-16-148410-' . rand(0, 9),
            'publicationDate' => new \DateTime('2020-01-01'),
            'genre' => 'Fiction',
            'copies' => 5
        ];
        
        $mergedAttributes = array_merge($defaultAttributes, $attributes);
        
        if (is_string($mergedAttributes['publicationDate'])) {
            $mergedAttributes['publicationDate'] = new \DateTime($mergedAttributes['publicationDate']);
        }
        
        $book = new Book();
        $book->setTitle($mergedAttributes['title']);
        $book->setAuthor($mergedAttributes['author']);
        $book->setIsbn($mergedAttributes['isbn']);
        $book->setPublicationDate($mergedAttributes['publicationDate']);
        $book->setGenre($mergedAttributes['genre']);
        $book->setCopies($mergedAttributes['copies']);
        
        return $book;
    }
    
    public function createAndPersist(array $attributes = []): Book
    {
        $book = $this->create($attributes);
        
        $this->entityManager->persist($book);
        $this->entityManager->flush();
        
        return $book;
    }
    
    public function createMany(int $count, array $attributes = []): array
    {
        $books = [];
        
        for ($i = 0; $i < $count; $i++) {
            $books[] = $this->create($attributes);
        }
        
        return $books;
    }
    
    public function createManyAndPersist(int $count, array $attributes = []): array
    {
        $books = $this->createMany($count, $attributes);
        
        foreach ($books as $book) {
            $this->entityManager->persist($book);
        }
        
        $this->entityManager->flush();
        
        return $books;
    }
}