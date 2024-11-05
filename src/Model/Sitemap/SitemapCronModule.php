<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Sitemap;

use Monolog\Logger;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class SitemapCronModule implements SimpleCronModuleInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Sitemap\SitemapFacade $sitemapFacade
     */
    public function __construct(protected readonly SitemapFacade $sitemapFacade)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(Logger $logger)
    {
    }

    public function run()
    {
        $this->sitemapFacade->generateForAllDomains();
    }
}
