<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

interface ProductFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $data
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function create(ProductData $data): Product;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $data
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $mainProduct
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $variants
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function createMainVariant(ProductData $data, Product $mainProduct, array $variants): Product;
}
