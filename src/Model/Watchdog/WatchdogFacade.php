<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Watchdog;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Product\Product;

class WatchdogFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Watchdog\WatchdogRepository $watchdogRepository
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Watchdog\WatchdogFactory $watchdogFactory
     */
    public function __construct(
        protected readonly WatchdogRepository $watchdogRepository,
        protected readonly EntityManagerInterface $em,
        protected readonly WatchdogFactory $watchdogFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Watchdog\WatchdogData $watchdogData
     * @return \Shopsys\FrameworkBundle\Model\Watchdog\Watchdog
     */
    public function create(WatchdogData $watchdogData): Watchdog
    {
        $watchdog = $this->watchdogFactory->create($watchdogData);

        $this->em->persist($watchdog);
        $this->em->flush();

        return $watchdog;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Watchdog\Watchdog $watchdog
     * @return \Shopsys\FrameworkBundle\Model\Watchdog\Watchdog
     */
    public function updateValidity(Watchdog $watchdog): Watchdog
    {
        $watchdog->updateValidity();

        $this->em->flush();

        return $watchdog;
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Watchdog\Watchdog
     */
    public function getById(int $id): Watchdog
    {
        return $this->watchdogRepository->getById($id);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param string $email
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Watchdog\Watchdog|null
     */
    public function findByProductEmailAndDomainId(Product $product, string $email, int $domainId): ?Watchdog
    {
        return $this->watchdogRepository->findByProductEmailAndDomainId($product, $email, $domainId);
    }
}
