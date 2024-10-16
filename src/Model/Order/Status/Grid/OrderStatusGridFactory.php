<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Status\Grid;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;

class OrderStatusGridFactory implements GridFactoryInterface
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
            ->select('os, ost')
            ->from(OrderStatus::class, 'os')
            ->join('os.translations', 'ost', Join::WITH, 'ost.locale = :locale')
            ->setParameter('locale', $this->localization->getAdminLocale());
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'os.id');

        $grid = $this->gridFactory->create('orderStatusList', $dataSource);
        $grid->setDefaultOrder('name');

        $grid->addColumn('name', 'ost.name', t('Name'), true);

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addDeleteActionColumn('admin_orderstatus_deleteconfirm', ['id' => 'os.id'])
            ->setAjaxConfirm();

        $grid->setTheme('@ShopsysFramework/Admin/Content/OrderStatus/listGrid.html.twig', [
            'TYPE_NEW' => OrderStatusTypeEnum::TYPE_NEW,
            'TYPE_DONE' => OrderStatusTypeEnum::TYPE_DONE,
            'TYPE_CANCELED' => OrderStatusTypeEnum::TYPE_CANCELED,
        ]);

        return $grid;
    }
}
