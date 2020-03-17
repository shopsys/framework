<?php

namespace Shopsys\FrameworkBundle\Model\Security;

use Shopsys\FrameworkBundle\Model\Order\OrderFlowFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class FrontLogoutHandler implements LogoutSuccessHandlerInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderFlowFacade
     */
    protected $orderFlowFacade;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFlowFacade $orderFlowFacade
     */
    public function __construct(RouterInterface $router, OrderFlowFacade $orderFlowFacade)
    {
        $this->router = $router;
        $this->orderFlowFacade = $orderFlowFacade;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function onLogoutSuccess(Request $request)
    {
        $this->orderFlowFacade->resetOrderForm();
        $url = $this->router->generate('front_homepage');
        $request->getSession()->remove(LoginAsUserFacade::SESSION_LOGIN_AS);
        $request->getSession()->migrate();

        return new RedirectResponse($url);
    }
}
