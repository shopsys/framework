<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\Customer\DeliveryAddressFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Customer\CustomerFacade;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade;
use Shopsys\FrameworkBundle\Model\Customer\Exception\DeliveryAddressNotFoundException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeliveryAddressController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactory $deliveryAddressDataFactory
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade $customerFacade
     */
    public function __construct(
        protected readonly DeliveryAddressFacade $deliveryAddressFacade,
        protected readonly DeliveryAddressDataFactory $deliveryAddressDataFactory,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly CustomerFacade $customerFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/delivery-address/edit/{id}', name: 'admin_delivery_address_edit', requirements: ['id' => '\d+'])]
    public function editAction(Request $request, int $id): Response
    {
        $deliveryAddress = $this->deliveryAddressFacade->getById($id);
        $deliveryAddressData = $this->deliveryAddressDataFactory->createFromDeliveryAddress($deliveryAddress);

        $form = $this->createForm(DeliveryAddressFormType::class, $deliveryAddressData, [
            'domain_id' => $deliveryAddress->getCustomer()->getDomainId(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->deliveryAddressFacade->edit($id, $deliveryAddressData);

            $this->addSuccessFlashTwig(
                t('Customer <strong><a href="{{ url }}">{{ name }}</a></strong> modified'),
                [
                    'name' => $deliveryAddress->getCity(),
                    'url' => $this->generateUrl('admin_delivery_address_edit', ['id' => $deliveryAddress->getId()]),
                ],
            );

            $customer = $deliveryAddress->getCustomer();

            if ($this->customerFacade->isB2bFeaturesEnabledByCustomer($customer)) {
                $billingAddress = $customer->getBillingAddress();

                return $this->redirectToRoute('admin_billing_address_edit', ['id' => $billingAddress->getId()]);
            }

            return $this->redirectToRoute('admin_customer_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing delivery address - %name%', ['%name%' => $deliveryAddress->getCity()]),
        );

        return $this->render('@ShopsysFramework/Admin/Content/Customer/DeliveryAddress/edit.html.twig', [
            'form' => $form->createView(),
            'deliveryAddress' => $deliveryAddress,
        ]);
    }

    /**
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    #[Route(path: '/delivery-address/delete/{id}', name: 'admin_delivery_address_delete', requirements: ['id' => '\d+'])]
    public function deleteAction(int $id): RedirectResponse
    {
        try {
            $city = $this->deliveryAddressFacade->getById($id)->getCity();

            $this->deliveryAddressFacade->delete($id);

            $this->addSuccessFlashTwig(
                t('Delivery address <strong>{{ city }}</strong> deleted'),
                [
                    'city' => $city,
                ],
            );
        } catch (DeliveryAddressNotFoundException $ex) {
            $this->addErrorFlash(t('Selected delivery address doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_customer_list');
    }
}
