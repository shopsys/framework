<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\MoneyConvertingDataSourceDecorator;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\Customer\User\CustomerUserUpdateFormType;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserListAdminFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Security\Exception\LoginAsRememberedUserException;
use Shopsys\FrameworkBundle\Model\Security\LoginAsUserFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CustomerController extends AdminBaseController
{
    protected const LOGIN_AS_TOKEN_ID_PREFIX = 'loginAs';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactoryInterface $customerUserDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserListAdminFacade $customerUserListAdminFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade $administratorGridFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Security\LoginAsUserFacade $loginAsUserFacade
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly CustomerUserDataFactoryInterface $customerUserDataFactory,
        protected readonly CustomerUserListAdminFacade $customerUserListAdminFacade,
        protected readonly CustomerUserFacade $customerUserFacade,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly AdministratorGridFacade $administratorGridFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly OrderFacade $orderFacade,
        protected readonly LoginAsUserFacade $loginAsUserFacade,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     */
    #[Route(path: '/customer/edit/{id}', requirements: ['id' => '\d+'])]
    public function editAction(Request $request, int $id)
    {
        $customerUser = $this->customerUserFacade->getCustomerUserById($id);
        $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromCustomerUser($customerUser);

        $form = $this->createForm(CustomerUserUpdateFormType::class, $customerUserUpdateData, [
            'customerUser' => $customerUser,
            'domain_id' => $this->adminDomainTabsFacade->getSelectedDomainId(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->customerUserFacade->editByAdmin($id, $customerUserUpdateData);

            $this->addSuccessFlashTwig(
                t('Customer <strong><a href="{{ url }}">{{ name }}</a></strong> modified'),
                [
                    'name' => $customerUser->getFullName(),
                    'url' => $this->generateUrl('admin_customer_edit', ['id' => $customerUser->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_customer_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing customer - %name%', ['%name%' => $customerUser->getFullName()]),
        );

        $orders = $this->orderFacade->getCustomerUserOrderList($customerUser);

        return $this->render('@ShopsysFramework/Admin/Content/Customer/edit.html.twig', [
            'form' => $form->createView(),
            'customerUser' => $customerUser,
            'orders' => $orders,
            'ssoLoginAsUserUrl' => $this->getSsoLoginAsCustomerUserUrl($customerUser),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    #[Route(path: '/customer/list/')]
    public function listAction(Request $request)
    {
        $quickSearchForm = $this->createForm(QuickSearchFormType::class, new QuickSearchFormData());
        $quickSearchForm->handleRequest($request);

        $queryBuilder = $this->customerUserListAdminFacade->getCustomerUserListQueryBuilderByQuickSearchData(
            $this->adminDomainTabsFacade->getSelectedDomainId(),
            $quickSearchForm->getData(),
        );

        $innerDataSource = new QueryBuilderDataSource($queryBuilder, 'u.id');
        $dataSource = new MoneyConvertingDataSourceDecorator($innerDataSource, ['ordersSumPrice']);

        $grid = $this->gridFactory->create('customerList', $dataSource);
        $grid->enablePaging();
        $grid->setDefaultOrder('name');

        $grid->addColumn('name', 'name', t('Full name'), true);
        $grid->addColumn('city', 'city', t('City'), true);
        $grid->addColumn('telephone', 'u.telephone', t('Telephone'), true);
        $grid->addColumn('email', 'u.email', t('Email'), true);
        $grid->addColumn('pricingGroup', 'pricingGroup', t('Pricing group'), true);
        $grid->addColumn('orders_count', 'ordersCount', t('Number of orders'), true)->setClassAttribute('text-right');
        $grid->addColumn('orders_sum_price', 'ordersSumPrice', t('Orders value'), true)
            ->setClassAttribute('text-right');
        $grid->addColumn('last_order_at', 'lastOrderAt', t('Last order'), true)
            ->setClassAttribute('text-right');

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_customer_edit', ['id' => 'id']);
        $grid->addDeleteActionColumn('admin_customer_delete', ['id' => 'id'])
            ->setConfirmMessage(t('Do you really want to remove this customer?'));

        $grid->setTheme('@ShopsysFramework/Admin/Content/Customer/listGrid.html.twig');

        $this->administratorGridFacade->restoreAndRememberGridLimit($this->getCurrentAdministrator(), $grid);

        return $this->render('@ShopsysFramework/Admin/Content/Customer/list.html.twig', [
            'gridView' => $grid->createView(),
            'quickSearchForm' => $quickSearchForm->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    #[Route(path: '/customer/new/')]
    public function newAction(Request $request)
    {
        $customerUserUpdateData = $this->customerUserUpdateDataFactory->create();
        $selectedDomainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $customerUserData = $this->customerUserDataFactory->createForDomainId($selectedDomainId);
        $customerUserUpdateData->customerUserData = $customerUserData;

        $form = $this->createForm(CustomerUserUpdateFormType::class, $customerUserUpdateData, [
            'customerUser' => null,
            'domain_id' => $selectedDomainId,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customerUser = $this->customerUserFacade->create($customerUserUpdateData);

            $this->addSuccessFlashTwig(
                t('Customer <strong><a href="{{ url }}">{{ name }}</a></strong> created'),
                [
                    'name' => $customerUser->getFullName(),
                    'url' => $this->generateUrl('admin_customer_edit', ['id' => $customerUser->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_customer_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/Customer/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @CsrfProtection
     * @param int $id
     */
    #[Route(path: '/customer/delete/{id}', requirements: ['id' => '\d+'])]
    public function deleteAction($id)
    {
        try {
            $fullName = $this->customerUserFacade->getCustomerUserById($id)->getFullName();

            $this->customerUserFacade->delete($id);

            $this->addSuccessFlashTwig(
                t('Customer <strong>{{ name }}</strong> deleted'),
                [
                    'name' => $fullName,
                ],
            );
        } catch (CustomerUserNotFoundException $ex) {
            $this->addErrorFlash(t('Selected customer doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_customer_list');
    }

    /**
     * @param int $customerUserId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/customer/login-as-user/{customerUserId}/')]
    public function loginAsCustomerUserAction(int $customerUserId): Response
    {
        try {
            return $this->render('@ShopsysFramework/Admin/Content/Login/loginAsCustomerUser.html.twig', [
                'tokens' => $this->loginAsUserFacade->loginAdministratorAsCustomerUserAndGetAccessAndRefreshToken($customerUserId),
                'url' => $this->generateUrl('front_homepage', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);
        } catch (CustomerUserNotFoundException $e) {
            $this->addErrorFlash(t('Customer not found.'));

            return $this->redirectToRoute('admin_customer_list');
        } catch (LoginAsRememberedUserException $e) {
            throw $this->createAccessDeniedException('Access denied', $e);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return string
     */
    protected function getSsoLoginAsCustomerUserUrl(CustomerUser $customerUser)
    {
        $customerDomainRouter = $this->domainRouterFactory->getRouter($customerUser->getDomainId());
        $loginAsUserUrl = $customerDomainRouter->generate(
            'admin_customer_loginascustomeruser',
            [
                'customerUserId' => $customerUser->getId(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        $mainAdminDomainRouter = $this->domainRouterFactory->getRouter(Domain::MAIN_ADMIN_DOMAIN_ID);

        return $mainAdminDomainRouter->generate(
            'admin_login_sso',
            [
                LoginController::ORIGINAL_DOMAIN_ID_PARAMETER_NAME => $customerUser->getDomainId(),
                LoginController::ORIGINAL_REFERER_PARAMETER_NAME => $loginAsUserUrl,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }
}
