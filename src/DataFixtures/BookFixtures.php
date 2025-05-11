<?php

namespace App\DataFixtures;

use App\Entity\Book;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BookFixtures extends Fixture
{
    private const GENRES = [
        'Fiction', 'Non-Fiction', 'Science Fiction', 'Fantasy', 'Mystery', 
        'Thriller', 'Romance', 'Horror', 'Biography', 'History', 
        'Adventure', 'Children\'s', 'Young Adult', 'Dystopian', 'Magical Realism',
        'Gothic Fiction', 'Historical Fiction', 'Psychological Fiction', 'Classic'
    ];
    
    private const POPULAR_AUTHORS = [
        'J.K. Rowling', 'Stephen King', 'George R.R. Martin', 'Dan Brown',
        'John Grisham', 'J.R.R. Tolkien', 'Agatha Christie', 'Jane Austen',
        'Ernest Hemingway', 'Mark Twain', 'Gabriel García Márquez', 'Leo Tolstoy',
        'Fyodor Dostoevsky', 'Virginia Woolf', 'Charles Dickens', 'Harper Lee',
        'F. Scott Fitzgerald', 'George Orwell', 'Aldous Huxley', 'Oscar Wilde'
    ];
    
    private const BOOK_TITLES_PREFIXES = [
        'The', 'A', 'My', 'Our', 'Their', 'Your', 'Her', 'His', 'One', 'Last',
        'First', 'New', 'Old', 'Secret', 'Lost', 'Hidden', 'Forgotten', 'Eternal',
        'Endless', 'Dark', 'Bright', 'Silent', 'Loud', 'Mysterious', 'Magical'
    ];
    
    private const BOOK_TITLES_NOUNS = [
        'Tale', 'Story', 'Chronicles', 'Adventure', 'Journey', 'Quest', 
        'Mystery', 'Secret', 'Legacy', 'History', 'Life', 'Path', 'Road',
        'Garden', 'Forest', 'Mountain', 'River', 'Ocean', 'Sky', 'Star',
        'Moon', 'Sun', 'World', 'Universe', 'Mind', 'Heart', 'Soul', 'Spirit',
        'Dreams', 'Shadows', 'Light', 'Darkness', 'Hope', 'Love', 'Destiny', 'Fate'
    ];
    
    private const BOOK_TITLES_SUFFIXES = [
        'of Time', 'of Love', 'of Destiny', 'of Shadows', 'of Light', 'of Fire',
        'of Ice', 'of the Sea', 'of the Sky', 'of the Forest', 'of the Mountains',
        'of Dreams', 'of Hope', 'of Despair', 'of Life', 'of Death', 'in Bloom',
        'in Winter', 'in Summer', 'in the Dark', 'in the Light', 'Untold',
        'Forgotten', 'Remembered', 'Revisited', 'Found', 'Lost'
    ];
    
    private const POPULAR_BOOKS = [
        ['To Kill a Mockingbird', 'Harper Lee', '978-0-06-112008-4', '1960-07-11', 'Fiction'],
        ['1984', 'George Orwell', '978-0-452-28423-4', '1949-06-08', 'Dystopian'],
        ['The Great Gatsby', 'F. Scott Fitzgerald', '978-0-7432-7356-5', '1925-04-10', 'Fiction'],
        ['Harry Potter and the Philosopher\'s Stone', 'J.K. Rowling', '978-0-7475-3269-6', '1997-06-26', 'Fantasy'],
        ['The Lord of the Rings', 'J.R.R. Tolkien', '978-0-618-64015-7', '1954-07-29', 'Fantasy'],
        ['Pride and Prejudice', 'Jane Austen', '978-0-14-143951-8', '1813-01-28', 'Romance'],
        ['The Hobbit', 'J.R.R. Tolkien', '978-0-618-00221-4', '1937-09-21', 'Fantasy'],
        ['The Hunger Games', 'Suzanne Collins', '978-0-439-02348-1', '2008-09-14', 'Science Fiction'],
        ['The Da Vinci Code', 'Dan Brown', '978-0-385-50420-5', '2003-03-18', 'Mystery Thriller'],
        ['The Shining', 'Stephen King', '978-0-385-12167-5', '1977-01-28', 'Horror'],
    ];

    public function load(ObjectManager $manager): void
    {
        $count = 50;
        $useRealBooks = true;
        
        $booksToCreate = [];
        if ($useRealBooks) {
            $popularBooksToAdd = min(count(self::POPULAR_BOOKS), $count);
            $booksToCreate = array_slice(self::POPULAR_BOOKS, 0, $popularBooksToAdd);
            
            $count -= $popularBooksToAdd;
        }
        
        for ($i = 0; $i < $count; $i++) {
            $title = $this->generateRandomTitle();
            
            $author = self::POPULAR_AUTHORS[array_rand(self::POPULAR_AUTHORS)];
            
            $isbn = $this->generateRandomIsbn();
            
            $year = rand(date('Y') - 200, date('Y'));
            $month = rand(1, 12);
            $day = rand(1, 28); 
            $publicationDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($day, 2, '0', STR_PAD_LEFT);
            
            $genre = self::GENRES[array_rand(self::GENRES)];
            
            $booksToCreate[] = [$title, $author, $isbn, $publicationDate, $genre];
        }
        

        foreach ($booksToCreate as $bookData) {
            $book = new Book();
            $book->setTitle($bookData[0]);
            $book->setAuthor($bookData[1]);
            $book->setIsbn($bookData[2]);
            $book->setPublicationDate(new \DateTime($bookData[3]));
            $book->setGenre($bookData[4]);
            $book->setCopies(rand(1, 20));
            
            $manager->persist($book);
        }
        
        $manager->flush();
    }
    
    private function generateRandomTitle(): string
    {
        $usePattern = rand(1, 10);
        
        if ($usePattern <= 7) {
            return self::BOOK_TITLES_PREFIXES[array_rand(self::BOOK_TITLES_PREFIXES)] . ' ' .
                   self::BOOK_TITLES_NOUNS[array_rand(self::BOOK_TITLES_NOUNS)] . ' ' .
                   self::BOOK_TITLES_SUFFIXES[array_rand(self::BOOK_TITLES_SUFFIXES)];
        } else if ($usePattern <= 9) {
            return self::BOOK_TITLES_PREFIXES[array_rand(self::BOOK_TITLES_PREFIXES)] . ' ' .
                   self::BOOK_TITLES_NOUNS[array_rand(self::BOOK_TITLES_NOUNS)];
        } else {
            return self::BOOK_TITLES_NOUNS[array_rand(self::BOOK_TITLES_NOUNS)] . ' ' .
                   self::BOOK_TITLES_SUFFIXES[array_rand(self::BOOK_TITLES_SUFFIXES)];
        }
    }
    
    private function generateRandomIsbn(): string
    {
        $prefix = '978';
        $group = (string)rand(0, 7);
        if ($group == '6') $group = '0'; 
        
        $publisherLength = rand(2, 6);
        $publisher = '';
        for ($i = 0; $i < $publisherLength; $i++) {
            $publisher .= (string)rand(0, 9);
        }
        
        $titleLength = 9 - strlen($group) - strlen($publisher);
        $title = '';
        for ($i = 0; $i < $titleLength; $i++) {
            $title .= (string)rand(0, 9);
        }
        
        $isbn = $prefix . $group . $publisher . $title;
        
        $formattedIsbn = substr($isbn, 0, 3) . '-' . 
                          $group . '-' . 
                          $publisher . '-' . 
                          $title;
        
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$isbn[$i] * ($i % 2 === 0 ? 1 : 3);
        }
        $checkDigit = (10 - ($sum % 10)) % 10;
        
        return $formattedIsbn . '-' . $checkDigit;
    }
}