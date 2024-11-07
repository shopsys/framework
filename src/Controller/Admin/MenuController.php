<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Symfony\Component\HttpFoundation\Response;

class MenuController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly Localization $localization,
    ) {
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function menuAction(): Response
    {
        return $this->render('@ShopsysFramework/Admin/Inline/Menu/menu.html.twig', [
            'domainConfigs' => $this->domain->getAll(),
            'allowedLocales' => $this->localization->getAllowedAdminLocales(),
        ]);
    }
}
