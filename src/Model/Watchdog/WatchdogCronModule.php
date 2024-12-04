<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Watchdog;

use Monolog\Logger;
use Shopsys\Plugin\Cron\IteratedCronModuleInterface;

class WatchdogCronModule implements IteratedCronModuleInterface
{
    protected Logger $logger;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Watchdog\WatchdogFacade $watchdogFacade
     */
    public function __construct(
        protected readonly WatchdogFacade $watchdogFacade,
    ) {
    }

    /**
     * @param \Monolog\Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function wakeUp()
    {
    }

    public function iterate()
    {
        $watchdog = null;

        if ($watchdog === null) {
            return false;
        }
    }

    public function sleep()
    {
    }
}
