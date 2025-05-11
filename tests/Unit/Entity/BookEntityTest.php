<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Book;
use PHPUnit\Framework\TestCase;

class BookTest extends TestCase
{
    private Book $book;

    protected function setUp(): void
    {
        $this->book = new Book();
    }

    public function testGettersAndSetters(): void
    {
        $this->book->setTitle('Test Book');
        $this->assertEquals('Test Book', $this->book->getTitle());

        $this->book->setAuthor('Test Author');
        $this->assertEquals('Test Author', $this->book->getAuthor());

        $this->book->setIsbn('978-3-16-148410-0');
        $this->assertEquals('978-3-16-148410-0', $this->book->getIsbn());

        $date = new \DateTime('2023-01-01');
        $this->book->setPublicationDate($date);
        $this->assertEquals($date, $this->book->getPublicationDate());

        $this->book->setGenre('Fiction');
        $this->assertEquals('Fiction', $this->book->getGenre());

        $this->book->setCopies(5);
        $this->assertEquals(5, $this->book->getCopies());
    }

    public function testInitialValues(): void
    {
        $this->assertNull($this->book->getId());
    }
}