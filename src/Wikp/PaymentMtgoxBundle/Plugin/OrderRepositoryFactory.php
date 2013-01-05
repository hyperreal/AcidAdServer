<?php

namespace Wikp\PaymentMtgoxBundle\Plugin;

use Doctrine\ORM\EntityManager;
use Wikp\PaymentMtgoxBundle\Exception\InvalidArgumentException;

class OrderRepositoryFactory
{
    private $entityManager;
    private $repositoryPath;
    private $repository;

    public function __construct(EntityManager $entityManager, $repositoryPath)
    {
        $this->entityManager = $entityManager;
        $this->repositoryPath = $repositoryPath;
    }

    /**
     * @return \Wikp\PaymentMtgoxBundle\Plugin\OrderRepositoryInterface
     */
    public function getRepository()
    {
        if (!empty($this->repository)) {
            return $this->repository;
        }

        $repository = $this->entityManager->getRepository($this->repositoryPath);
        if (!($repository instanceof OrderRepositoryInterface)) {
            throw new InvalidArgumentException('Repository does not implement OrderRepositoryInterface');
        }
        $this->repository = $repository;

        return $repository;
    }
}
