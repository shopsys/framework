<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security;

use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
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
     * @param \Shopsys\FrameworkBundle\Component\Security\ResetPasswordInterface $user
     * @param int $domainId
     * @param string $routeName
     * @return string
     */
    public function getNewPasswordUrl(ResetPasswordInterface $user, int $domainId, string $routeName): string
    {
        $router = $this->domainRouterFactory->getRouter($domainId);

        if (!$user->isResetPasswordHashValid($user->getResetPasswordHash())) {
            throw new ResetPasswordHashNotValidException(sprintf('Reset password mail cannot be sent. %s entity with ID "%d" has invalid reset password hash.', get_class($user), $user->getId()));
        }

        $routeParameters = [
            'email' => $user->getEmail(),
            'hash' => $user->getResetPasswordHash(),
        ];

        return $router->generate(
            $routeName,
            $routeParameters,
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }
}
