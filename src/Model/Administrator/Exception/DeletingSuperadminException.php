<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Exception;

use Exception;

class DeletingSuperadminException extends Exception implements AdministratorException
{
}
