<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Brand\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BrandNotFoundException extends NotFoundHttpException implements BrandException
{
}
