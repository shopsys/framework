<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Customer\Mail\CustomerMailFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;

class CustomerUserFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\UserRepository
     */
    protected $userRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerUserDataFactoryInterface
     */
    protected $customerUserDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerFactoryInterface
     */
    protected $customerFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\Mail\CustomerMailFacade
     */
    protected $customerMailFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\BillingAddressFactoryInterface
     */
    protected $billingAddressFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFactoryInterface
     */
    protected $deliveryAddressFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactoryInterface
     */
    protected $billingAddressDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\UserFactoryInterface
     */
    protected $userFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerUserPasswordFacade
     */
    protected $customerUserPasswordFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Customer\UserRepository $userRepository
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerUserDataFactoryInterface $customerUserDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerFactoryInterface $customerFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\Mail\CustomerMailFacade $customerMailFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressFactoryInterface $billingAddressFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFactoryInterface $deliveryAddressFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactoryInterface $billingAddressDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\UserFactoryInterface $userFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerUserPasswordFacade $customerUserPasswordFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        UserRepository $userRepository,
        CustomerUserDataFactoryInterface $customerUserDataFactory,
        CustomerFactoryInterface $customerFactory,
        CustomerMailFacade $customerMailFacade,
        BillingAddressFactoryInterface $billingAddressFactory,
        DeliveryAddressFactoryInterface $deliveryAddressFactory,
        BillingAddressDataFactoryInterface $billingAddressDataFactory,
        UserFactoryInterface $userFactory,
        CustomerUserPasswordFacade $customerUserPasswordFacade
    ) {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->customerUserDataFactory = $customerUserDataFactory;
        $this->customerFactory = $customerFactory;
        $this->customerMailFacade = $customerMailFacade;
        $this->billingAddressFactory = $billingAddressFactory;
        $this->deliveryAddressFactory = $deliveryAddressFactory;
        $this->billingAddressDataFactory = $billingAddressDataFactory;
        $this->userFactory = $userFactory;
        $this->customerUserPasswordFacade = $customerUserPasswordFacade;
    }

    /**
     * @param int $userId
     * @return \Shopsys\FrameworkBundle\Model\Customer\User
     */
    public function getUserById($userId)
    {
        return $this->userRepository->getUserById($userId);
    }

    /**
     * @param string $email
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Customer\User|null
     */
    public function findUserByEmailAndDomain($email, $domainId)
    {
        return $this->userRepository->findUserByEmailAndDomain($email, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\UserData $userData
     * @return \Shopsys\FrameworkBundle\Model\Customer\User
     */
    public function register(UserData $userData)
    {
        $customer = $this->customerFactory->create();
        $billingAddressData = $this->billingAddressDataFactory->create();

        $billingAddressData->customer = $customer;
        $userData->customer = $customer;

        $customer->addBillingAddress($this->billingAddressFactory->create($billingAddressData));

        $this->em->persist($customer);
        $this->em->flush($customer);

        $user = $this->userFactory->create($userData, null);

        $this->setEmail($userData->email, $user);

        $this->em->persist($user);
        $this->em->flush();

        $this->customerMailFacade->sendRegistrationMail($user);

        return $user;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerUserData $customerData
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\User
     */
    public function create(CustomerUserData $customerData)
    {
        $customer = $this->customerFactory->create();

        $customerData->billingAddressData->customer = $customer;
        $customerData->userData->customer = $customer;

        $customer->addBillingAddress($this->billingAddressFactory->create($customerData->billingAddressData));
        $this->em->persist($customer);
        $this->em->flush($customer);

        $deliveryAddress = $this->deliveryAddressFactory->create($customerData->deliveryAddressData);

        $user = $this->userFactory->create($customerData->userData, $deliveryAddress);

        $this->setEmail($customerData->userData->email, $user);

        $this->em->persist($user);
        $this->em->flush($user);

        if ($customerData->sendRegistrationMail) {
            $this->customerMailFacade->sendRegistrationMail($user);
        }

        return $user;
    }

    /**
     * @param int $userId
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerUserData $customerUserData
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\User
     */
    protected function edit($userId, CustomerUserData $customerUserData)
    {
        $user = $this->getUserById($userId);

        $user->edit($customerUserData->userData);

        if ($customerUserData->userData->password !== null) {
            $this->customerUserPasswordFacade->changePassword($user, $customerUserData->userData->password);
        }

        $user->getCustomer()->getBillingAddress()->edit($customerUserData->billingAddressData);

        $this->editDeliveryAddress($user, $customerUserData->deliveryAddressData);

        return $user;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User $user
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData $deliveryAddressData
     */
    protected function editDeliveryAddress(User $user, DeliveryAddressData $deliveryAddressData): void
    {
        if (!$deliveryAddressData->addressFilled) {
            $user->setDeliveryAddress(null);
            return;
        }

        $deliveryAddress = $user->getDeliveryAddress();
        if ($deliveryAddress instanceof DeliveryAddress) {
            $deliveryAddress->edit($deliveryAddressData);
        } else {
            $user->setDeliveryAddress($this->deliveryAddressFactory->create($deliveryAddressData));
        }
    }

    /**
     * @param int $userId
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerUserData $customerUserData
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\User
     */
    public function editByAdmin($userId, CustomerUserData $customerUserData)
    {
        $user = $this->edit($userId, $customerUserData);

        $this->setEmail($customerUserData->userData->email, $user);

        $this->em->flush();

        return $user;
    }

    /**
     * @param int $userId
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerUserData $customerUserData
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\User
     */
    public function editByCustomer($userId, CustomerUserData $customerUserData)
    {
        $user = $this->edit($userId, $customerUserData);

        $this->em->flush();

        return $user;
    }

    /**
     * @param int $userId
     */
    public function delete($userId)
    {
        $user = $this->getUserById($userId);

        $this->em->remove($user);
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User $user
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     */
    public function amendCustomerDataFromOrder(User $user, Order $order)
    {
        $this->edit(
            $user->getId(),
            $this->customerUserDataFactory->createAmendedByOrder($user, $order)
        );

        $this->em->flush();
    }

    /**
     * @param string $email
     * @param \Shopsys\FrameworkBundle\Model\Customer\User $user
     */
    protected function setEmail(string $email, User $user): void
    {
        $userByEmailAndDomain = $this->findUserByEmailAndDomain(
            $email,
            $user->getDomainId()
        );

        if ($userByEmailAndDomain !== null && $user->getId() !== $userByEmailAndDomain->getId()) {
            throw new \Shopsys\FrameworkBundle\Model\Customer\Exception\DuplicateEmailUserException($email);
        }

        $user->setEmail($email);
    }
}
