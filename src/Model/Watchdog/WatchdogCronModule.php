<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Watchdog;

use Monolog\Logger;
use Shopsys\FrameworkBundle\Model\Watchdog\Mail\WatchdogMailFacade;
use Shopsys\Plugin\Cron\IteratedCronModuleInterface;

class WatchdogCronModule implements IteratedCronModuleInterface
{
    protected Logger $logger;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Watchdog\WatchdogFacade $watchdogFacade
     * @param \Shopsys\FrameworkBundle\Model\Watchdog\Mail\WatchdogMailFacade $watchdogMailFacade
     */
    public function __construct(
        protected readonly WatchdogFacade $watchdogFacade,
        protected readonly WatchdogMailFacade $watchdogMailFacade,
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
        $watchdog = $this->watchdogFacade->findNextWatchdogToSend();

        if ($watchdog === null) {
            return false;
        }

        $this->watchdogMailFacade->sendMail($watchdog);

        $this->logger->info('Sending watchdog email.', [
            'watchdogId' => $watchdog->getId(),
            'watchdogProductId' => $watchdog->getProduct()->getId(),
        ]);

        $this->watchdogFacade->deleteById($watchdog->getId());

        return true;
    }

    public function sleep()
    {
    }
}
