<?php

namespace Shopsys\FrameworkBundle\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategoryFacade;

class TopCategoryDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategoryFacade
     */
    private $topCategoryFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    public function __construct(TopCategoryFacade $topCategoryFacade, Domain $domain)
    {
        $this->topCategoryFacade = $topCategoryFacade;
        $this->domain = $domain;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $categories = [
            $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS),
            $this->getReference(CategoryDataFixture::CATEGORY_BOOKS),
            $this->getReference(CategoryDataFixture::CATEGORY_TOYS),
        ];

        foreach ($this->domain->getAllIds() as $domainId) {
            $this->topCategoryFacade->saveTopCategoriesForDomain($domainId, $categories);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            CategoryDataFixture::class,
        ];
    }
}
