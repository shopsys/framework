<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class AdministratorLocalizationFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly Localization $localization,
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @param string $locale
     */
    public function setSelectedLocale(Administrator $administrator, string $locale): void
    {
        $this->localization->checkLocaleIsSupported($locale);
        $administrator->setSelectedLocale($locale);

        $this->em->flush();
    }
}
