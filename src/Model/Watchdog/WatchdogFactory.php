<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Watchdog;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class WatchdogFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Watchdog\WatchdogData $watchdogData
     * @return \Shopsys\FrameworkBundle\Model\Watchdog\Watchdog
     */
    public function create(WatchdogData $watchdogData): Watchdog
    {
        $entityClassName = $this->entityNameResolver->resolve(Watchdog::class);

        return new $entityClassName($watchdogData);
    }
}
