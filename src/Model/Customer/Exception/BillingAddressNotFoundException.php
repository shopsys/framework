<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BillingAddressNotFoundException extends NotFoundHttpException implements BillingAddressException
{
}
