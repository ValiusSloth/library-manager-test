<?php

namespace App\Tests\Integration\ErrorHandling;

use App\Tests\Support\SecurityTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookErrorHandlingTest extends WebTestCase
{
    use SecurityTestTrait;
    
    public function testNotFoundPage(): void
    {
        $client = static::createClient();
        
        $this->createAuthenticatedClient($client);
        
        $client->request('GET', '/books/99999');
        
        $this->assertResponseStatusCodeSame(404);
    }

    public function testInvalidFormSubmission(): void
    {
        $client = static::createClient();
        
        $this->createAuthenticatedClient($client);
        
        $crawler = $client->request('GET', '/books/new');
        
        $form = $crawler->selectButton('Create')->form();
        $form['book[title]'] = '';
        $form['book[author]'] = 'Test Author';
        $form['book[isbn]'] = '978-1-56619-909-4';
        $form['book[publicationDate]'] = '2023-01-01';
        $form['book[genre]'] = 'Fiction';
        $form['book[copies]'] = '5';
        
        $client->submit($form);
        
        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorExists('.is-invalid, .invalid-feedback, .alert-danger, ul li');
    }
    
    public function testInvalidJsonRequest(): void
    {
        $client = static::createClient();
        
        $this->createAuthenticatedClient($client);
        
        $client->request(
            'POST', 
            '/books/new', 
            [], 
            [], 
            [
                'CONTENT_TYPE' => 'application/json', 
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ], 
            '{}'
        );
        
        $this->assertResponseIsSuccessful();
    }
}