<?php

namespace App\Tests\Unit\Validator;

use App\Entity\Book;
use App\Tests\Support\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BookValidationTest extends KernelTestCase
{
    use FixturesTrait;
    
    private ?ValidatorInterface $validator = null;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testValidBook(): void
    {
        $book = new Book();
        $book->setTitle('Valid Book');
        $book->setAuthor('Valid Author');
        $book->setIsbn('978-1-56619-909-4');
        $book->setPublicationDate(new \DateTime('2020-01-01'));
        $book->setGenre('Fiction');
        $book->setCopies(5);

        $errors = $this->validator->validate($book);
        $this->assertCount(0, $errors, "Book validation should pass with valid data");
    }

    public function testInvalidBook(): void
    {
        $book = new Book();

        $errors = $this->validator->validate($book);
        $this->assertGreaterThan(0, $errors->count(), "Empty book should fail validation");
    }

    public function testInvalidISBN(): void
    {
        $book = new Book();
        $book->setTitle('Valid Book');
        $book->setAuthor('Valid Author');
        $book->setIsbn('invalid-isbn');
        $book->setPublicationDate(new \DateTime('2020-01-01'));
        $book->setGenre('Fiction');
        $book->setCopies(5);

        $errors = $this->validator->validate($book);
        $this->assertGreaterThan(0, $errors->count(), "Book with invalid ISBN should fail validation");
    }

    public function testNegativeCopies(): void
    {
        $book = new Book();
        $book->setTitle('Valid Book');
        $book->setAuthor('Valid Author');
        $book->setIsbn('978-1-56619-909-4');
        $book->setPublicationDate(new \DateTime('2020-01-01'));
        $book->setGenre('Fiction');
        $book->setCopies(-5);

        $errors = $this->validator->validate($book);
        $this->assertGreaterThan(0, $errors->count(), "Book with negative copies should fail validation");
    }
}