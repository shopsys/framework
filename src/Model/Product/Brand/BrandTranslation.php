<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;
use Shopsys\FrameworkBundle\Component\String\TransformString;

/**
 * @ORM\Table(name="brand_translations")
 * @ORM\Entity
 */
class BrandTranslation extends AbstractTranslation
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     * @Prezent\Translatable(targetEntity="Shopsys\FrameworkBundle\Model\Product\Brand\Brand")
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $translatable;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription($description)
    {
        $this->description = TransformString::getTrimmedStringOrNullOnEmpty($description);
    }
}
