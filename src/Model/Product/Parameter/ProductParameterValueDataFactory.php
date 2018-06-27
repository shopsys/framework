<?php

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

class ProductParameterValueDataFactory implements ProductParameterValueDataFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData
     */
    public function create(): ProductParameterValueData
    {
        return new ProductParameterValueData();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue $productParameterValue
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData
     */
    public function createFromProductParameterValue(ProductParameterValue $productParameterValue): ProductParameterValueData
    {
        $productParameterValueData = new ProductParameterValueData();
        $this->fillFromProductParameterValue($productParameterValueData, $productParameterValue);

        return $productParameterValueData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData $productParameterValueData
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue $productParameterValue
     */
    protected function fillFromProductParameterValue(ProductParameterValueData $productParameterValueData, ProductParameterValue $productParameterValue)
    {
        $productParameterValueData->parameter = $productParameterValue->getParameter();
        $productParameterValueData->parameterValueData = new ParameterValueData();
        $productParameterValueData->parameterValueData->setFromEntity($productParameterValue->getValue());
    }
}
