<?php

namespace Shopsys\FrameworkBundle\Component\DataFixture;

use Doctrine\Common\DataFixtures\FixtureInterface;

abstract class AbstractReferenceFixture implements FixtureInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade
     */
    private $persistentReferenceFacade;

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade $persistentReferenceFacade
     */
    public function autowirePersistentReferenceFacade(PersistentReferenceFacade $persistentReferenceFacade)
    {
        $this->persistentReferenceFacade = $persistentReferenceFacade;
    }

    /**
     * @param string $name
     * @param object $object
     */
    public function addReference($name, $object)
    {
        $this->persistentReferenceFacade->persistReference($name, $object);
    }

    /**
     * @param string $name
     * @return object
     */
    public function getReference($name)
    {
        return $this->persistentReferenceFacade->getReference($name);
    }
}
