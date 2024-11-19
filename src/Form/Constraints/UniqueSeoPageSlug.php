<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage;
use Symfony\Component\Validator\Constraint;

class UniqueSeoPageSlug extends Constraint
{
    public string $message = 'Seo page with slug {{ pageSlug }} already exists';

    public ?SeoPage $ignoredSeoPage = null;

    public ?int $domainId = null;
}
