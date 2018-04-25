<?php

namespace Shopsys\FrameworkBundle\DataFixtures\DemoMultidomain;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Country\CountryData;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;

class CountryDataFixture extends AbstractReferenceFixture
{
    /** @var \Shopsys\FrameworkBundle\Model\Country\CountryFacade */
    private $countryFacade;

    const COUNTRY_CZECH_REPUBLIC_2 = 'country_czech_republic_2';
    const COUNTRY_SLOVAKIA_2 = 'country_slovakia_2';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade
     */
    public function __construct(CountryFacade $countryFacade)
    {
        $this->countryFacade = $countryFacade;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $domainId = 2;
        $countryData = new CountryData();
        $countryData->name = 'Česká republika';
        $countryData->code = 'CZ';
        $this->createCountry($countryData, $domainId, self::COUNTRY_CZECH_REPUBLIC_2);

        $domainId = 2;
        $countryData = new CountryData();
        $countryData->name = 'Slovenská republika';
        $countryData->code = 'SK';
        $this->createCountry($countryData, $domainId, self::COUNTRY_SLOVAKIA_2);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryData $countryData
     * @param int $domainId
     * @param string $referenceName
     */
    private function createCountry(CountryData $countryData, $domainId, $referenceName)
    {
        $country = $this->countryFacade->create($countryData, $domainId);
        $this->addReference($referenceName, $country);
    }
}
