<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Watchdog;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Watchdog\Exception\WatchdogNotFoundException;

class WatchdogRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getWatchdogRepository(): EntityRepository
    {
        return $this->em->getRepository(Watchdog::class);
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Watchdog\Watchdog
     */
    public function getById(int $id): Watchdog
    {
        $watchdog = $this->getWatchdogRepository()->find($id);

        if ($watchdog === null) {
            throw new WatchdogNotFoundException($id);
        }

        return $watchdog;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('w')
            ->from(Watchdog::class, 'w');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param string $email
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Watchdog\Watchdog|null
     */
    public function findByProductUuidEmailAndDomainId(Product $product, string $email, int $domainId): ?Watchdog
    {
        return $this->getQueryBuilder()
            ->where('w.product = :product')
            ->andWhere('w.email = :email')
            ->andWhere('w.domainId = :domainId')
            ->setParameter('product', $product)
            ->setParameter('email', $email)
            ->setParameter('domainId', $domainId)
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @param string $locale
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getWatchdogProductsQueryBuilder(string $locale): QueryBuilder
    {
        return $this->getWatchdogRepository()
            ->createQueryBuilder('w')
            ->select('IDENTITY(w.product) as productId')
            ->addSelect('pt.name as productName')
            ->addSelect('p.catnum as productCatnum')
            ->addSelect('COUNT(w.product) as watchdogCount')
            ->leftJoin('w.product', 'p')
            ->leftJoin('p.translations', 'pt', Join::WITH, 'pt.locale = :locale')
            ->setParameter('locale', $locale)
            ->groupBy('w.product, pt.name, p.catnum')
            ->orderBy('MAX(w.createdAt)', 'DESC');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getWatchdogsByProductQueryBuilder(Product $product): QueryBuilder
    {
        return $this->getWatchdogRepository()
            ->createQueryBuilder('w')
            ->where('w.product = :product')
            ->setParameter('product', $product);
    }
}
