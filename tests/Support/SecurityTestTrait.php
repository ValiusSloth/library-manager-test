<?php

namespace App\Tests\Support;

use App\Tests\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

trait SecurityTestTrait
{
    protected function createAuthenticatedClient(
        KernelBrowser $client, 
        string $email = 'admin@admin.com', 
        string $password = 'admin'
    ): KernelBrowser {
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);
        
        $userFactory = new UserFactory($entityManager, $passwordHasher);
        $userFactory->getOrCreate($email, $password);
        
        $client->request('GET', '/login');
        
        if ($client->getResponse()->isRedirect()) {
            $client->followRedirect();
            return $client;
        }
        
        $crawler = $client->getCrawler();
        
        $emailField = null;
        $passwordField = null;
        
        foreach (['_username', 'email', 'inputEmail'] as $possibleName) {
            if ($crawler->filter('input[name="' . $possibleName . '"]')->count() > 0) {
                $emailField = $possibleName;
                break;
            }
        }
        
        foreach (['_password', 'password', 'inputPassword'] as $possibleName) {
            if ($crawler->filter('input[name="' . $possibleName . '"]')->count() > 0) {
                $passwordField = $possibleName;
                break;
            }
        }
        
        if ($emailField !== null && $passwordField !== null) {
            $buttonCrawler = $crawler->selectButton('Sign in');
            if ($buttonCrawler->count() === 0) {
                $buttonCrawler = $crawler->selectButton('Login');
            }
            
            if ($buttonCrawler->count() > 0) {
                $form = $buttonCrawler->form();
                $form[$emailField] = $email;
                $form[$passwordField] = $password;
                
                $client->submit($form);
                
                if ($client->getResponse()->isRedirect()) {
                    $client->followRedirect();
                }
            }
        }
        
        return $client;
    }
}