<?php

namespace Shopsys\FrameworkBundle\Component\FlashMessage;

use Twig\Environment;

class FlashMessageSender
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\FlashMessage\Bag
     */
    protected $flashMessageBag;

    /**
     * @var \Twig\Environment
     */
    protected $twigEnvironment;

    /**
     * @param \Shopsys\FrameworkBundle\Component\FlashMessage\Bag $flashMessageBag
     * @param \Twig\Environment $twigEnvironment
     */
    public function __construct(
        Bag $flashMessageBag,
        Environment $twigEnvironment
    ) {
        $this->flashMessageBag = $flashMessageBag;
        $this->twigEnvironment = $twigEnvironment;
    }

    /**
     * @param string $template
     * @param array $parameters
     */
    public function addErrorFlashTwig($template, $parameters = [])
    {
        $message = $this->renderStringTwigTemplate($template, $parameters);
        $this->flashMessageBag->addError($message, false);
    }

    /**
     * @param string $template
     * @param array $parameters
     */
    public function addInfoFlashTwig($template, $parameters = [])
    {
        $message = $this->renderStringTwigTemplate($template, $parameters);
        $this->flashMessageBag->addInfo($message, false);
    }

    /**
     * @param string $template
     * @param array $parameters
     */
    public function addSuccessFlashTwig($template, $parameters = [])
    {
        $message = $this->renderStringTwigTemplate($template, $parameters);
        $this->flashMessageBag->addSuccess($message, false);
    }

    /**
     * @param string $template
     * @param array $parameters
     * @return string
     */
    protected function renderStringTwigTemplate($template, array $parameters)
    {
        $twigTemplate = $this->twigEnvironment->createTemplate($template);

        return $twigTemplate->render($parameters);
    }

    /**
     * @param string|array $message
     */
    public function addErrorFlash($message)
    {
        $this->flashMessageBag->addError($message, true);
    }

    /**
     * @param string|array $message
     */
    public function addInfoFlash($message)
    {
        $this->flashMessageBag->addInfo($message, true);
    }

    /**
     * @param string|array $message
     */
    public function addSuccessFlash($message)
    {
        $this->flashMessageBag->addSuccess($message, true);
    }
}
