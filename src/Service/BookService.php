<?php

namespace App\Service;

use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class BookService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BookRepository $bookRepository,
        private CsrfTokenManagerInterface $csrfTokenManager
    ) {
    }
    
    public function getPaginatedBooks(int $page, int $limit): array
    {
        $page = max(1, $page);
        $limit = max(1, $limit);
        
        $totalBooks = $this->bookRepository->count([]);
        $lastPage = max(1, ceil($totalBooks / $limit));
        $page = min($page, $lastPage);
        
        $books = $this->bookRepository->findByPage($page, $limit);
        
        return [
            'books' => $books,
            'currentPage' => $page,
            'lastPage' => $lastPage,
            'totalBooks' => $totalBooks
        ];
    }
    
    public function searchBooks(string $searchTerm): array
    {
        return $this->bookRepository->searchByTerms($searchTerm);
    }
    
    public function createBookDataArray(array $books): array
    {
        $bookData = [];
        
        foreach ($books as $book) {
            try {
                $csrfToken = $this->csrfTokenManager->getToken('delete' . $book->getId())->getValue();
                
                $bookData[] = [
                    'id' => $book->getId(),
                    'title' => $book->getTitle(),
                    'author' => $book->getAuthor(),
                    'isbn' => $book->getIsbn(),
                    'publicationDate' => $book->getPublicationDate() ? $book->getPublicationDate()->format('Y-m-d') : null,
                    'genre' => $book->getGenre(),
                    'copies' => $book->getCopies(),
                    'csrfToken' => $csrfToken
                ];
            } catch (\Exception $e) {
            }
        }
        
        return $bookData;
    }
    
    public function saveBook(Book $book): void
    {
        $this->entityManager->persist($book);
        $this->entityManager->flush();
    }
    
    public function updateBook(Book $book): void
    {
        $this->entityManager->flush();
    }
    
    public function deleteBook(Book $book): void
    {
        $this->entityManager->remove($book);
        $this->entityManager->flush();
    }
    
    public function validateDeleteToken(Book $book, string $token): bool
    {
        return $this->csrfTokenManager->isTokenValid(
            $this->csrfTokenManager->getToken('delete' . $book->getId())->fromString($token)
        );
    }
    
    public function getFormErrors(\Symfony\Component\Form\FormInterface $form): array
    {
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $fieldName = $error->getOrigin()->getName();
            $errors[$fieldName] = $error->getMessage();
        }
        
        return $errors;
    }
}