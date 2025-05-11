<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Service\BookService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/books')]
#[IsGranted('ROLE_USER')]
class BookController extends AbstractController
{
    public function __construct(
        private BookService $bookService
    ) {
    }

    #[Route('/', name: 'app_book_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        
        $paginationData = $this->bookService->getPaginatedBooks($page, $limit);
        
        return $this->render('book/index.html.twig', $paginationData);
    }

    #[Route('/list', name: 'app_book_list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        
        $paginationData = $this->bookService->getPaginatedBooks($page, $limit);
        
        if ($request->query->has('ajax')) {
            $bookListHtml = $this->renderView('components/book/_table.html.twig', [
                'books' => $paginationData['books'],
                'show_delete' => true
            ]);
            
            $paginationHtml = $this->renderView('components/pagination/_pagination.html.twig', [
                'currentPage' => $paginationData['currentPage'],
                'lastPage' => $paginationData['lastPage'],
                'totalBooks' => $paginationData['totalBooks']
            ]);
            
            $html = '<div id="books-container">' . $bookListHtml . '</div>';
            $html .= '<div id="pagination-container">' . $paginationHtml . '</div>';
            
            return new Response($html);
        }
        
        return $this->redirectToRoute('app_book_index', [
            'page' => $paginationData['currentPage'],
            'limit' => $limit
        ]);
    }

    #[Route('/search', name: 'app_book_search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        $searchTerm = $request->query->get('q', '');
        
        try {
            $books = $this->bookService->searchBooks($searchTerm);
            
            if ($request->isXmlHttpRequest()) {
                $bookData = $this->bookService->createBookDataArray($books);
                
                return new JsonResponse([
                    'success' => true,
                    'results' => $bookData,
                    'count' => count($bookData),
                    'searchTerm' => $searchTerm
                ]);
            }
            
            return $this->render('book/search.html.twig', [
                'books' => $books,
                'searchTerm' => $searchTerm,
            ]);
        } catch (\Exception $e) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'An error occurred during search: ' . $e->getMessage(),
                    'searchTerm' => $searchTerm
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            
            $this->addFlash('error', 'An error occurred during search: ' . $e->getMessage());
            return $this->redirectToRoute('app_book_index');
        }
    }

    #[Route('/alert', name: 'app_book_alert', methods: ['GET'])]
    public function alert(Request $request): Response
    {
        $type = $request->query->get('type', 'info');
        $message = $request->query->get('message', '');
        
        return $this->render('components/alert/_alert.html.twig', [
            'type' => $type,
            'message' => $message
        ]);
    }

    #[Route('/new', name: 'app_book_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->bookService->saveBook($book);

            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'success' => true,
                    'id' => $book->getId(),
                    'message' => 'Book has been successfully registered'
                ]);
            }

            $this->addFlash('success', 'Book has been successfully registered');
            return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($form->isSubmitted() && !$form->isValid() && $request->isXmlHttpRequest()) {
            $errors = $this->bookService->getFormErrors($form);
            
            return $this->json([
                'success' => false,
                'errors' => $errors,
            ]);
        }

        return $this->render('book/new.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_book_show', methods: ['GET'])]
    public function show(Book $book): Response
    {
        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_book_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Book $book): Response
    {
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->bookService->updateBook($book);

            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'success' => true,
                    'id' => $book->getId(),
                    'message' => 'Book has been successfully updated'
                ]);
            }

            $this->addFlash('success', 'Book has been successfully updated');
            return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($form->isSubmitted() && !$form->isValid() && $request->isXmlHttpRequest()) {
            $errors = $this->bookService->getFormErrors($form);
            
            return $this->json([
                'success' => false,
                'errors' => $errors,
            ]);
        }

        return $this->render('book/edit.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_book_delete', methods: ['POST', 'DELETE'])]
    public function delete(Request $request, Book $book): Response
    {
        $token = $request->request->get('_token');
        
        if ($this->bookService->validateDeleteToken($book, $token)) {
            $this->bookService->deleteBook($book);
            
            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'success' => true,
                    'message' => 'Book has been successfully deleted'
                ]);
            }
            
            $this->addFlash('success', 'Book has been successfully deleted');
        } else if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => false,
                'message' => 'Invalid CSRF token'
            ]);
        }

        return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
    }
}