<?php

namespace Shopsys\FrameworkBundle\Model\Order\Item;

use Shopsys\FrameworkBundle\Model\Product\Product;

class QuantifiedProduct
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     */
    protected $product;

    /**
     * @var int
     */
    protected $quantity;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $quantity
     */
    public function __construct(Product $product, $quantity)
    {
        $this->product = $product;
        $this->quantity = $quantity;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }
}
