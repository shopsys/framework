<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version20241106123834 extends AbstractMigration implements ContainerAwareInterface
{
    protected ContainerInterface $container;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $adminDefaultLocale = $this->container->getParameter('shopsys.admin_default_locale');

        $this->sql(sprintf('ALTER TABLE administrators ADD selected_locale VARCHAR(10) NOT NULL DEFAULT \'%s\'', $adminDefaultLocale));
        $this->sql('ALTER TABLE administrators ALTER selected_locale DROP DEFAULT');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface|null $container
     */
    public function setContainer(?ContainerInterface $container = null): void
    {
        $this->container = $container;
    }
}
