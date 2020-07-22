<?php

namespace Shopsys\FrameworkBundle\Component\EntityExtension;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder as BaseQueryBuilder;

class QueryBuilder extends BaseQueryBuilder
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver
     */
    protected $entityNameResolver;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(EntityManagerInterface $em, EntityNameResolver $entityNameResolver)
    {
        parent::__construct($em);

        $this->entityNameResolver = $entityNameResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getDQL()
    {
        return $this->entityNameResolver->resolveIn(parent::getDQL());
    }
}
