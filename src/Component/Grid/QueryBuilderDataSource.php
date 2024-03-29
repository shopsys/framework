<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\GroupedScalarHydrator;
use Shopsys\FrameworkBundle\Component\Paginator\QueryPaginator;

class QueryBuilderDataSource implements DataSourceInterface
{
    protected string $rowIdSourceColumnName;

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string $rowIdSourceColumnName
     */
    public function __construct(protected readonly QueryBuilder $queryBuilder, $rowIdSourceColumnName)
    {
        $this->rowIdSourceColumnName = $rowIdSourceColumnName;
    }

    /**
     * @param int|null $limit
     * @param int $page
     * @param string|null $orderSourceColumnName
     * @param string $orderDirection
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getPaginatedRows(
        $limit = null,
        $page = 1,
        $orderSourceColumnName = null,
        $orderDirection = self::ORDER_ASC,
    ) {
        $queryBuilder = clone $this->queryBuilder;

        if ($orderSourceColumnName !== null) {
            $this->addQueryOrder($queryBuilder, $orderSourceColumnName, $orderDirection);
        }

        $queryPaginator = new QueryPaginator($queryBuilder, GroupedScalarHydrator::HYDRATION_MODE);

        return $queryPaginator->getResult($page, $limit);
    }

    /**
     * @param int $rowId
     * @return array
     */
    public function getOneRow($rowId)
    {
        $queryBuilder = clone $this->queryBuilder;
        $this->prepareQueryWithOneRow($queryBuilder, $rowId);

        return $queryBuilder->getQuery()->getSingleResult(GroupedScalarHydrator::HYDRATION_MODE);
    }

    /**
     * @return int
     */
    public function getTotalRowsCount()
    {
        $queryPaginator = new QueryPaginator($this->queryBuilder, GroupedScalarHydrator::HYDRATION_MODE);

        return $queryPaginator->getTotalCount();
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string $orderSourceColumnName
     * @param string $orderDirection
     */
    protected function addQueryOrder(QueryBuilder $queryBuilder, $orderSourceColumnName, $orderDirection)
    {
        $queryBuilder->orderBy($orderSourceColumnName, $orderDirection);
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param int $rowId
     */
    protected function prepareQueryWithOneRow(QueryBuilder $queryBuilder, $rowId)
    {
        $queryBuilder
            ->andWhere($this->rowIdSourceColumnName . ' = :rowId')
            ->setParameter('rowId', $rowId)
            ->setFirstResult(null)
            ->setMaxResults(null)
            ->resetDQLPart('orderBy');
    }

    /**
     * @return string
     */
    public function getRowIdSourceColumnName()
    {
        return $this->rowIdSourceColumnName;
    }
}
