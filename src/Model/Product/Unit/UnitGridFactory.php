<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Unit;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class UnitGridFactory implements GridFactoryInterface
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GridFactory $gridFactory,
        protected readonly Localization $localization,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create()
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('u, ut')
            ->from(Unit::class, 'u')
            ->join('u.translations', 'ut', Join::WITH, 'ut.locale = :locale')
            ->setParameter('locale', $this->localization->getAdminLocale());
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'u.id');

        $grid = $this->gridFactory->create('unitList', $dataSource);
        $grid->setDefaultOrder('name');

        $grid->addColumn('name', 'ut.name', t('Name'), true);

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addDeleteActionColumn('admin_unit_deleteconfirm', ['id' => 'u.id'])
            ->setAjaxConfirm();

        $grid->setTheme('@ShopsysFramework/Admin/Content/Unit/listGrid.html.twig');

        return $grid;
    }
}
