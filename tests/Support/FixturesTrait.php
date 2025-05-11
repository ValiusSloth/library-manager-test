<?php

namespace App\Tests\Support;

use App\Tests\Factory\BookFactory;
use App\Tests\Factory\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

trait FixturesTrait
{
    protected function createBookFactory(): BookFactory
    {
        return new BookFactory($this->getEntityManagerForFixtures());
    }
    
    protected function createUserFactory(): UserFactory
    {
        return new UserFactory(
            $this->getEntityManagerForFixtures(),
            static::getContainer()->get(UserPasswordHasherInterface::class)
        );
    }
    
    protected function createTestBooks(int $count = 4): array
    {
        return $this->createBookFactory()->createManyAndPersist($count);
    }
    
    protected function createTestBook(array $attributes = []): object
    {
        return $this->createBookFactory()->createAndPersist($attributes);
    }
    
    private function getEntityManagerForFixtures(): EntityManagerInterface
    {
        if (property_exists($this, 'entityManager') && $this->entityManager instanceof EntityManagerInterface) {
            return $this->entityManager;
        }
        
        if (method_exists($this, 'getContainer')) {
            return static::getContainer()->get('doctrine')->getManager();
        }
        
        throw new \LogicException('This trait requires either DatabaseTestTrait or a method getContainer() to be implemented.');
    }
}