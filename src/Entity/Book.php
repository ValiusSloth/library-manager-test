<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BookRepository::class)]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Please enter the book title')]
    #[Assert\Length(max: 255, maxMessage: 'The title cannot be longer than {{ limit }} characters')]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Please enter the author name')]
    #[Assert\Length(max: 255, maxMessage: 'The author name cannot be longer than {{ limit }} characters')]
    private ?string $author = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Please enter the ISBN')]
    #[Assert\Regex(pattern: '/^(?:\d[- ]?){9}[\dXx]$|^(?:\d[- ]?){13}$/', message: 'Please enter a valid 10 or 13-digit ISBN')]
    private ?string $isbn = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'Please select a publication date')]
    private ?\DateTime $publicationDate = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Please select a genre')]
    private ?string $genre = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Please enter the number of copies')]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'The number of copies cannot be negative')]
    private ?int $copies = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    public function setIsbn(string $isbn): static
    {
        $this->isbn = $isbn;

        return $this;
    }

    public function getPublicationDate(): ?\DateTime
    {
        return $this->publicationDate;
    }

    public function setPublicationDate(\DateTime $publicationDate): static
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(string $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    public function getCopies(): ?int
    {
        return $this->copies;
    }

    public function setCopies(int $copies): static
    {
        $this->copies = $copies;

        return $this;
    }
}
