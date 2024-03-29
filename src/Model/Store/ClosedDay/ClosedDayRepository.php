<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\ClosedDay;

use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\Exception\ClosedDayNotFoundException;
use Shopsys\FrameworkBundle\Model\Store\Store;

class ClosedDayRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface $displayTimeZoneProvider
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly Domain $domain,
        protected readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider,
    ) {
    }

    /**
     * @param int $closedDayId
     * @return \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay
     */
    public function getById(int $closedDayId): ClosedDay
    {
        $closedDay = $this->getClosedDayRepository()->find($closedDayId);

        if ($closedDay === null) {
            throw new ClosedDayNotFoundException(sprintf('Holiday / internal day with ID %s not found.', $closedDay));
        }

        return $closedDay;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     * @return \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay[]
     */
    public function getFollowingWeekClosedDaysNotExcludedForStore(Store $store): array
    {
        $today = new DateTimeImmutable('today', $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($store->getDomainId()));
        $endOfFollowingWeek = $today->add(new DateInterval('P7D'));

        return $this
            ->getClosedDayRepository()
            ->createQueryBuilder('cd')
            ->where('cd.domainId = :domainId')
            ->andWhere(':store NOT MEMBER OF cd.excludedStores')
            ->andWhere('cd.date >= :today')
            ->andWhere('cd.date < :endOfFollowingWeek')
            ->setParameter('domainId', $store->getDomainId())
            ->setParameter('store', $store)
            ->setParameter('today', $today)
            ->setParameter('endOfFollowingWeek', $endOfFollowingWeek)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getClosedDayRepository(): EntityRepository
    {
        return $this->em->getRepository(ClosedDay::class);
    }
}
