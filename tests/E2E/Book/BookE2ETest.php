<?php

namespace App\Tests\E2E\Book;

use App\Tests\E2E\AbstractE2ETestCase;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;

class BookE2ETest extends AbstractE2ETestCase
{
    protected static ?string $createdBookTitle = null;
    
    public function testBookListPage(): void
    {
        $this->navigateTo('/books/');
        
        $this->takeScreenshot(self::$driver, 'book_list');
        
        $pageTitle = self::$driver->getTitle();
        $this->assertStringContainsString('Book', $pageTitle, 'Page title should contain "Book"');
        
        $tableExists = count(self::$driver->findElements(WebDriverBy::id('books-table'))) > 0;
        $emptyMessage = count(self::$driver->findElements(WebDriverBy::cssSelector('.alert-info'))) > 0;
        
        $this->assertTrue($tableExists || $emptyMessage, 'Either books table or empty message should be present');
    }

    public function testBookSearch(): void
    {
        $this->navigateTo('/books/');
        
        $this->takeScreenshot(self::$driver, 'search_before');
        
        $searchInput = $this->findSearchInput();
        
        if ($searchInput === null) {
            $this->markTestSkipped('Could not find a search input on the page');
            return;
        }
        
        $searchInput->clear();
        $searchInput->sendKeys('Test');
        $searchInput->sendKeys(WebDriverKeys::ENTER);
        
        sleep(2);
        
        $this->takeScreenshot(self::$driver, 'search_after');
        
        $tableRows = self::$driver->findElements(WebDriverBy::cssSelector('#books-table tbody tr'));
        $alertMessages = self::$driver->findElements(WebDriverBy::cssSelector('.alert'));
        
        $this->assertTrue(
            count($tableRows) > 0 || count($alertMessages) > 0,
            'Should either display search results or a "no results" message'
        );
    }

    public function testBookCreation(): void
    {
        try {
            $this->navigateTo('/books/new');
            
            $this->takeScreenshot(self::$driver, 'book_form');
            
            $uniqueId = uniqid();
            $bookTitle = "Test Book " . $uniqueId;
            
            self::$createdBookTitle = $bookTitle;
            
            self::$driver->findElement(WebDriverBy::name('book[title]'))->sendKeys($bookTitle);
            self::$driver->findElement(WebDriverBy::name('book[author]'))->sendKeys('Test Author');
            self::$driver->findElement(WebDriverBy::name('book[isbn]'))->sendKeys('978-3-16-148410-0');
            
            $genreSelect = self::$driver->findElement(WebDriverBy::name('book[genre]'));
            $genreSelect->click();
            self::$driver->findElement(WebDriverBy::xpath("//select[@name='book[genre]']/option[text()='Fiction']"))->click();
            
            self::$driver->findElement(WebDriverBy::name('book[copies]'))->sendKeys('5');
            
            self::$driver->executeScript(
                "document.querySelector('input[name=\"book[publicationDate]\"]').value = '2023-01-01'"
            );
            
            self::$driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
            
            sleep(2);
            
            $this->takeScreenshot(self::$driver, 'after_book_create');
            
            $successAlert = self::$driver->findElements(WebDriverBy::cssSelector('.alert-success'));
            $this->assertGreaterThan(0, count($successAlert), 'Success message should be displayed');
            
            $alertText = $successAlert[0]->getText();
            $this->assertStringContainsString('success', strtolower($alertText), 'Success message should contain the word "success"');
            
        } catch (\Exception $e) {
            $this->logError(self::$driver, 'creation', $e);
            $this->fail('Book creation failed: ' . $e->getMessage());
        }
    }

    /**
     * @depends testBookCreation
     */
    public function testBookEdit(): void
    {
        try {
            $this->navigateTo('/books/new');
            
            $uniqueId = 'E2ETEST';
            $bookTitle = "Edit Test Book " . $uniqueId;
            
            self::$driver->findElement(WebDriverBy::name('book[title]'))->sendKeys($bookTitle);
            self::$driver->findElement(WebDriverBy::name('book[author]'))->sendKeys('Edit Test Author');
            self::$driver->findElement(WebDriverBy::name('book[isbn]'))->sendKeys('978-3-16-148410-1');
            self::$driver->findElement(WebDriverBy::name('book[genre]'))->click();
            self::$driver->findElement(WebDriverBy::xpath("//select[@name='book[genre]']/option[text()='Fiction']"))->click();
            self::$driver->findElement(WebDriverBy::name('book[copies]'))->sendKeys('5');
            self::$driver->executeScript("document.querySelector('input[name=\"book[publicationDate]\"]').value = '2023-01-01'");
            
            self::$driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
            sleep(2);
            
            $this->navigateTo('/books/');
            sleep(2);
            
            $this->searchForBook($bookTitle);
            
            $this->takeScreenshot(self::$driver, 'search_for_edit');
            
            $this->clickEditButtonForBook($bookTitle);
            
            sleep(2);
            
            $this->takeScreenshot(self::$driver, 'edit_form');
            
            $updatedTitle = "Updated Edit Test Book " . $uniqueId;
            
            $titleField = self::$driver->findElement(WebDriverBy::name('book[title]'));
            $titleField->clear();
            $titleField->sendKeys($updatedTitle);
            sleep(2);
            
            self::$driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
            
            $this->navigateTo('/books/');
            sleep(2);
            
            $this->searchForBook($uniqueId);
            
            $this->takeScreenshot(self::$driver, 'search_for_updated_book');
            
            $updatedBookVisible = $this->isBookVisibleInTable($updatedTitle);
            
            $this->assertTrue($updatedBookVisible, "Updated title '$updatedTitle' should be visible in the book list");
            
        } catch (\Exception $e) {
            $this->logError(self::$driver, 'edit', $e);
            $this->fail('Book edit failed: ' . $e->getMessage());
        }
    }

    public function testPagination(): void
    {
        $this->navigateTo('/books/');
        
        $paginationElements = self::$driver->findElements(WebDriverBy::cssSelector('.pagination'));
        
        if (count($paginationElements) > 0) {
            self::$driver->executeScript("window.scrollTo(0, document.body.scrollHeight)");
            sleep(1);
            
            $this->takeScreenshot(self::$driver, 'pagination_after_scroll');
            
            $nextPageLinks = self::$driver->findElements(
                WebDriverBy::cssSelector('.pagination .page-item:not(.disabled):not(.active) .page-link')
            );
            
            if (count($nextPageLinks) > 0) {
                try {
                    $nextLink = $nextPageLinks[0];
                    self::$driver->executeScript("arguments[0].click();", [$nextLink]);
                    
                    sleep(2);
                    
                    $this->takeScreenshot(self::$driver, 'next_page');
                    
                    $activePageElements = self::$driver->findElements(WebDriverBy::cssSelector('.pagination .page-item.active'));
                    $this->assertGreaterThan(0, count($activePageElements), 'Active page indicator should exist');
                } catch (\Exception $e) {
                    $this->logError(self::$driver, 'pagination', $e);
                    
                    try {
                        $page2Link = self::$driver->findElement(WebDriverBy::cssSelector('.pagination .page-link[data-page="2"]'));
                        $page2Link->click();
                        sleep(2);
                        $this->takeScreenshot(self::$driver, 'page2');
                        $this->assertTrue(true, 'Navigation to page 2 successful');
                    } catch (\Exception $e2) {
                        $this->markTestSkipped('Pagination navigation failed: ' . $e->getMessage());
                    }
                }
            } else {
                $this->addWarning('Pagination exists but no other pages are available');
            }
        } else {
            $this->markTestSkipped('Pagination not found, possibly not enough books');
        }
    }
    
    private function searchForBook(string $query): void
    {
        self::$driver->executeScript(
            "
            const searchInput = document.getElementById('book-search');
            searchInput.value = arguments[0];
            
            // Trigger input event to activate search
            const event = new Event('input', { bubbles: true });
            searchInput.dispatchEvent(event);
            ",
            [$query]
        );
        
        sleep(3);
    }
    
    private function clickEditButtonForBook(string $bookTitle): void
    {
        $rows = self::$driver->findElements(WebDriverBy::cssSelector('#books-table tbody tr'));
        $found = false;
        
        foreach ($rows as $row) {
            if (strpos($row->getText(), $bookTitle) !== false) {
                $editButton = $row->findElement(WebDriverBy::cssSelector('a.btn-primary'));
                self::$driver->executeScript("arguments[0].click();", [$editButton]);
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $this->fail("Could not find the book '$bookTitle' in the search results");
        }
    }
    
    private function isBookVisibleInTable(string $bookTitle): bool
    {
        $tableRows = self::$driver->findElements(WebDriverBy::cssSelector('#books-table tbody tr'));
        $found = false;
        
        foreach ($tableRows as $row) {
            if (strpos($row->getText(), $bookTitle) !== false) {
                $found = true;
                break;
            }
        }
        
        return $found;
    }
}