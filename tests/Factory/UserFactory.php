<?php

namespace App\Tests\Factory;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFactory
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    
    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }
    
    public function create(array $attributes = []): User
    {
        $defaultAttributes = [
            'email' => 'test' . uniqid() . '@example.com',
            'roles' => ['ROLE_USER'],
            'password' => 'password'
        ];
        
        $mergedAttributes = array_merge($defaultAttributes, $attributes);
        
        $user = new User();
        $user->setEmail($mergedAttributes['email']);
        $user->setRoles($mergedAttributes['roles']);
        
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $mergedAttributes['password']
        );
        $user->setPassword($hashedPassword);
        
        return $user;
    }
    
    public function createAndPersist(array $attributes = []): User
    {
        $user = $this->create($attributes);
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        return $user;
    }
    
    public function getOrCreate(string $email, string $password = 'password'): User
    {
        $repository = $this->entityManager->getRepository(User::class);
        $user = $repository->findOneBy(['email' => $email]);
        
        if (!$user) {
            $user = $this->createAndPersist([
                'email' => $email,
                'password' => $password
            ]);
        }
        
        return $user;
    }
}