<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Monolog\Logger;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class RemoveOldCustomerUserRefreshTokenChainsCronModule implements SimpleCronModuleInterface
{
    protected Logger $logger;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainRepository $customerUserRefreshTokenChainRepository
     */
    public function __construct(
        protected readonly CustomerUserRefreshTokenChainRepository $customerUserRefreshTokenChainRepository,
    ) {
    }

    /**
     * @param \Monolog\Logger $logger
     */
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    public function run(): void
    {
        $this->logger->info('Removing expired customer refresh token chains');
        $this->customerUserRefreshTokenChainRepository->removeOldCustomerRefreshTokenChains();
        $this->logger->info('Customer refresh token chains removed');
    }
}
