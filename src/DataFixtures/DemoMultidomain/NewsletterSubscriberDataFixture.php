<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\DataFixtures\DemoMultidomain;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;

class NewsletterSubscriberDataFixture extends AbstractReferenceFixture
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade
     */
    private $newsletterFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    public function __construct(
        NewsletterFacade $newsletterFacade,
        Domain $domain
    ) {
        $this->newsletterFacade = $newsletterFacade;
        $this->domain = $domain;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->domain->getAllIdsExcludingFirstDomain() as $domainId) {
            $this->loadForDomain($domainId);
        }
    }

    /**
     * @param int $domainId
     */
    private function loadForDomain(int $domainId)
    {
        $newsletterSubscribersData = $this->getEmailData();

        foreach ($newsletterSubscribersData as $email) {
            $this->newsletterFacade->addSubscribedEmail($email, $domainId);
        }
    }

    /**
     * @return string[]
     */
    private function getEmailData()
    {
        return [
            'anna.anina@no-reply.com',
            'jonathan.anderson@no-reply.com',
            'peter.parkson@no-reply.com',
        ];
    }
}
