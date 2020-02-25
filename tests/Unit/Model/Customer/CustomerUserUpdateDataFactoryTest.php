<?php

namespace Tests\FrameworkBundle\Unit\Model\Customer;

use DateTime;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Country\CountryData;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddress;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressData;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\CustomerData;
use Shopsys\FrameworkBundle\Model\Customer\CustomerFactory;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentData;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportData;

class CustomerUserUpdateDataFactoryTest extends TestCase
{
    private const DOMAIN_ID = 1;

    public function testGetAmendedCustomerUserUpdateDataByOrderWithoutChanges()
    {
        $customerUserUpdateUpdateDataFactory = $this->getCustomerUserUpdateDataFactory();

        $customerData = new CustomerData();
        $customer = new Customer($customerData);

        $customerUserData = new CustomerUserData();
        $customerUserData->firstName = 'firstName';
        $customerUserData->lastName = 'lastName';
        $customerUserData->createdAt = new DateTime();
        $customerUserData->telephone = 'telephone';
        $customerUserData->email = 'no-reply@shopsys.com';
        $customerUserData->domainId = Domain::FIRST_DOMAIN_ID;
        $customerUserData->customer = $customer;

        $billingCountryData = new CountryData();
        $billingCountryData->names = ['cs' => 'Česká republika'];
        $billingCountry = new Country($billingCountryData);
        $billingAddressData = new BillingAddressData();
        $billingAddressData->street = 'street';
        $billingAddressData->city = 'city';
        $billingAddressData->postcode = 'postcode';
        $billingAddressData->companyCustomer = true;
        $billingAddressData->companyName = 'companyName';
        $billingAddressData->companyNumber = 'companyNumber';
        $billingAddressData->companyTaxNumber = 'companyTaxNumber';
        $billingAddressData->country = $billingCountry;
        $billingAddressData->customer = $customer;
        $billingAddress = $this->createBillingAddress($billingAddressData);

        $deliveryCountryData = new CountryData();
        $deliveryCountryData->names = ['cs' => 'Slovenská republika'];
        $deliveryCountry = new Country($deliveryCountryData);
        $deliveryAddressData = new DeliveryAddressData();
        $deliveryAddressData->addressFilled = false;
        $deliveryAddressData->street = 'deliveryStreet';
        $deliveryAddressData->city = 'deliveryCity';
        $deliveryAddressData->postcode = 'deliveryPostcode';
        $deliveryAddressData->companyName = 'deliveryCompanyName';
        $deliveryAddressData->firstName = 'deliveryFirstName';
        $deliveryAddressData->lastName = 'deliveryLastName';
        $deliveryAddressData->telephone = 'deliveryTelephone';
        $deliveryAddressData->country = $deliveryCountry;
        $deliveryAddressData->customer = $customer;
        $deliveryAddress = $this->createDeliveryAddress($deliveryAddressData);

        $customerData->billingAddress = $billingAddress;
        $customerData->deliveryAddresses[] = $deliveryAddress;
        $customer->edit($customerData);

        $customerUser = new CustomerUser($customerUserData);

        $transportData = new TransportData();
        $transportData->name = ['cs' => 'transportName'];
        $transport = new Transport($transportData);
        $paymentData = new PaymentData();
        $paymentData->name = ['cs' => 'paymentName'];
        $payment = new Payment($paymentData);
        $orderStatusData = new OrderStatusData();
        $orderStatusData->name = ['en' => 'orderStatusName'];
        $orderStatus = new OrderStatus($orderStatusData, OrderStatus::TYPE_NEW);
        $orderData = new OrderData();
        $orderData->transport = $transport;
        $orderData->payment = $payment;
        $orderData->status = $orderStatus;
        $orderData->firstName = 'orderFirstName';
        $orderData->lastName = 'orderLastName';
        $orderData->email = 'order@email.com';
        $orderData->telephone = 'orderTelephone';
        $orderData->street = 'orderStreet';
        $orderData->city = 'orderCity';
        $orderData->postcode = 'orderPostcode';
        $orderData->country = $billingCountry;
        $orderData->deliveryAddressSameAsBillingAddress = false;
        $orderData->deliveryFirstName = 'orderDeliveryFirstName';
        $orderData->deliveryLastName = 'orderDeliveryLastName';
        $orderData->deliveryCompanyName = 'orderDeliveryCompanyName';
        $orderData->deliveryTelephone = 'orderDeliveryTelephone';
        $orderData->deliveryStreet = 'orderDeliveryStreet';
        $orderData->deliveryCity = 'orderDeliveryCity';
        $orderData->deliveryPostcode = 'orderDeliveryPostcode';
        $orderData->deliveryCountry = $deliveryCountry;
        $orderData->domainId = self::DOMAIN_ID;
        $order = new Order(
            $orderData,
            '123456',
            '7ebafe9fe'
        );
        $order->setCompanyInfo(
            'companyName',
            'companyNumber',
            'companyTaxNumber'
        );

        $customerUserUpdateData = $customerUserUpdateUpdateDataFactory->createAmendedByOrder($customerUser, $order, $deliveryAddress);

        $this->assertEquals($customerUserData, $customerUserUpdateData->customerUserData);
        $this->assertEquals($billingAddressData, $customerUserUpdateData->billingAddressData);
        $this->assertEquals($deliveryAddressData, $customerUserUpdateData->deliveryAddressData);
    }

    public function testGetAmendedCustomerUserUpdateDataByOrder()
    {
        $customerUserUpdateDataFactory = $this->getCustomerUserUpdateDataFactory();

        $billingCountryData = new CountryData();
        $billingCountryData->names = ['cs' => 'Česká republika'];

        $deliveryCountryData = new CountryData();
        $deliveryCountryData->names = ['cs' => 'Slovenská republika'];

        $billingCountry = new Country($billingCountryData);
        $deliveryCountry = new Country($deliveryCountryData);

        $customerData = new CustomerData();

        $customer = new Customer($customerData);
        $customerUserData = new CustomerUserData();
        $customerUserData->firstName = 'firstName';
        $customerUserData->lastName = 'lastName';
        $customerUserData->email = 'no-reply@shopsys.com';
        $customerUserData->createdAt = new DateTime();
        $customerUserData->domainId = Domain::FIRST_DOMAIN_ID;
        $customerUserData->customer = $customer;

        $billingAddressData = new BillingAddressData();
        $billingAddressData->customer = $customer;
        $billingAddress = $this->createBillingAddress($billingAddressData);

        $customerData->billingAddress = $billingAddress;
        $customer->edit($customerData);

        $customerUser = new CustomerUser($customerUserData);

        $transportData = new TransportData();
        $transportData->name = ['cs' => 'transportName'];
        $transport = new Transport($transportData);
        $paymentData = new PaymentData();
        $paymentData->name = ['cs' => 'paymentName'];
        $payment = new Payment($paymentData);
        $orderStatusData = new OrderStatusData();
        $orderStatusData->name = ['en' => 'orderStatusName'];
        $orderStatus = new OrderStatus($orderStatusData, OrderStatus::TYPE_NEW);
        $orderData = new OrderData();
        $orderData->transport = $transport;
        $orderData->payment = $payment;
        $orderData->status = $orderStatus;
        $orderData->firstName = 'orderFirstName';
        $orderData->lastName = 'orderLastName';
        $orderData->email = 'order@email.com';
        $orderData->telephone = 'orderTelephone';
        $orderData->street = 'orderStreet';
        $orderData->city = 'orderCity';
        $orderData->postcode = 'orderPostcode';
        $orderData->country = $billingCountry;
        $orderData->deliveryAddressSameAsBillingAddress = false;
        $orderData->deliveryFirstName = 'orderDeliveryFirstName';
        $orderData->deliveryLastName = 'orderDeliveryLastName';
        $orderData->deliveryCompanyName = 'orderDeliveryCompanyName';
        $orderData->deliveryTelephone = 'orderDeliveryTelephone';
        $orderData->deliveryStreet = 'orderDeliveryStreet';
        $orderData->deliveryCity = 'orderDeliveryCity';
        $orderData->deliveryPostcode = 'orderDeliveryPostcode';
        $orderData->deliveryCountry = $deliveryCountry;
        $orderData->domainId = self::DOMAIN_ID;
        $order = new Order(
            $orderData,
            '123456',
            '7eba123456fe9fe'
        );
        $order->setCompanyInfo(
            'companyName',
            'companyNumber',
            'companyTaxNumber'
        );

        $deliveryAddressData = new DeliveryAddressData();
        $deliveryAddressData->addressFilled = true;
        $deliveryAddressData->street = $order->getDeliveryStreet();
        $deliveryAddressData->city = $order->getDeliveryCity();
        $deliveryAddressData->postcode = $order->getDeliveryPostcode();
        $deliveryAddressData->companyName = $order->getDeliveryCompanyName();
        $deliveryAddressData->firstName = $order->getDeliveryFirstName();
        $deliveryAddressData->lastName = $order->getDeliveryLastName();
        $deliveryAddressData->telephone = $order->getDeliveryTelephone();
        $deliveryAddressData->country = $deliveryCountry;

        $customerUserUpdateData = $customerUserUpdateDataFactory->createAmendedByOrder($customerUser, $order, null);

        $this->assertEquals($customerUserData, $customerUserUpdateData->customerUserData);
        $this->assertEquals($deliveryAddressData, $customerUserUpdateData->deliveryAddressData);
        $this->assertTrue($customerUserUpdateData->billingAddressData->companyCustomer);
        $this->assertSame($order->getCompanyName(), $customerUserUpdateData->billingAddressData->companyName);
        $this->assertSame($order->getCompanyNumber(), $customerUserUpdateData->billingAddressData->companyNumber);
        $this->assertSame($order->getCompanyTaxNumber(), $customerUserUpdateData->billingAddressData->companyTaxNumber);
        $this->assertSame($order->getStreet(), $customerUserUpdateData->billingAddressData->street);
        $this->assertSame($order->getCity(), $customerUserUpdateData->billingAddressData->city);
        $this->assertSame($order->getPostcode(), $customerUserUpdateData->billingAddressData->postcode);
        $this->assertSame($order->getCountry(), $customerUserUpdateData->billingAddressData->country);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactory
     */
    private function getCustomerUserUpdateDataFactory(): CustomerUserUpdateDataFactory
    {
        return new CustomerUserUpdateDataFactory(
            new BillingAddressDataFactory(),
            new DeliveryAddressDataFactory(),
            new CustomerUserDataFactory($this->createMock(PricingGroupSettingFacade::class)),
            new CustomerFactory($this->createMock(EntityNameResolver::class))
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData|null $billingAddressData
     * @return \Shopsys\FrameworkBundle\Model\Customer\BillingAddress
     */
    private function createBillingAddress(?BillingAddressData $billingAddressData = null)
    {
        if ($billingAddressData === null) {
            $billingAddressData = new BillingAddressData();
        }

        return new BillingAddress($billingAddressData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData|null $deliveryAddressData
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress
     */
    private function createDeliveryAddress(?DeliveryAddressData $deliveryAddressData = null)
    {
        if ($deliveryAddressData === null) {
            $deliveryAddressData = new DeliveryAddressData();
        }

        return new DeliveryAddress($deliveryAddressData);
    }
}
