<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\Mail;

use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\ResetPasswordInterface;
use Shopsys\FrameworkBundle\Model\Mail\Exception\ResetPasswordHashNotValidException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NewPasswordUrlProvider
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     */
    public function __construct(
        protected readonly DomainRouterFactory $domainRouterFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\ResetPasswordInterface $customerUser
     * @return string
     */
    public function getNewPasswordUrl(ResetPasswordInterface $customerUser): string
    {
        $router = $this->domainRouterFactory->getRouter($customerUser->getDomainId());

        if (!$customerUser->isResetPasswordHashValid($customerUser->getResetPasswordHash())) {
            throw new ResetPasswordHashNotValidException('
                Reset password mail cannot be sent. Customer user with ID "' . $customerUser->getId() . '" has invalid reset password hash.
            ');
        }

        $routeParameters = [
            'email' => $customerUser->getEmail(),
            'hash' => $customerUser->getResetPasswordHash(),
        ];

        return $router->generate(
            'front_registration_set_new_password',
            $routeParameters,
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }
}
