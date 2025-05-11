<?php

namespace App\Tests\Support;

use Doctrine\ORM\EntityManagerInterface;

trait DatabaseTestTrait
{
    protected ?EntityManagerInterface $entityManager = null;
    
    protected function getEntityManager(): EntityManagerInterface
    {
        if ($this->entityManager === null) {
            $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        }
        
        return $this->entityManager;
    }
    
    protected function clearDatabase(string $tableName): void
    {
        $connection = $this->getEntityManager()->getConnection();
        
        try {
            $connection->executeStatement("DELETE FROM {$tableName}");
            
            try {
                $connection->executeStatement("ALTER SEQUENCE {$tableName}_id_seq RESTART WITH 1");
            } catch (\Exception $e) {
            }
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to clear database table {$tableName}: " . $e->getMessage());
        }
    }
    
    protected function beginTransaction(): void
    {
        $this->getEntityManager()->beginTransaction();
    }
    
    protected function rollbackTransaction(): void
    {
        if ($this->entityManager && $this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->rollback();
        }
    }
    
    protected function closeEntityManager(): void
    {
        if ($this->entityManager) {
            $this->entityManager->close();
            $this->entityManager = null;
        }
    }
}