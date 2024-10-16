<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension;

use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Object_;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Roave\BetterReflection\Reflection\ReflectionObject;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use Shopsys\FrameworkBundle\Component\ClassExtension\DocBlockParser;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\DummyClassForAnnotationsReplacer;

class DocBlockParserTest extends TestCase
{
    private DocBlockParser $docBlockParser;

    protected function setUp(): void
    {
        $this->docBlockParser = new DocBlockParser();
    }

    /**
     * @param string $phpDoc
     * @param string[] $returnTypes
     */
    #[DataProvider('methodPhpDocReturnTypeDataProvider')]
    public function testGetReturnTypesFromPhpDoc(string $phpDoc, array $returnTypes): void
    {
        $this->assertEquals($returnTypes, $this->docBlockParser->getReturnTypes($phpDoc));
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionParameter $reflectionParameter
     * @param string $paramTypeString
     */
    #[DataProvider('methodPhpDocParamTypeDataProvider')]
    public function testGetMethodParamTypeFromPhpDoc(
        ReflectionParameter $reflectionParameter,
        string $paramTypeString,
    ): void {
        $this->assertEquals($this->docBlockParser->getParameterType($reflectionParameter), $paramTypeString);
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionProperty $reflectionProperty
     * @param string $paramTypeString
     */
    #[DataProvider('methodPhpDocPropertyTypeDataProvider')]
    public function testGetPropertyTypeFromPhpDoc(
        ReflectionProperty $reflectionProperty,
        string $paramTypeString,
    ): void {
        $this->assertEquals($this->docBlockParser->getPropertyType($reflectionProperty), $paramTypeString);
    }

    /**
     * @return array<string|\phpDocumentor\Reflection\Type[]>[]
     */
    public static function methodPhpDocReturnTypeDataProvider(): array
    {
        $reflectionClass = ReflectionObject::createFromName(DummyClassForAnnotationsReplacer::class);

        return [
            [
                $reflectionClass->getMethod('returnsFrameworkCategoryFacade')->getDocComment(),
                [new Object_(new Fqsen('\Shopsys\FrameworkBundle\Model\Category\CategoryFacade'))],
            ],
            [
                $reflectionClass->getMethod('returnsFrameworkCategoryFacadeOrNull')->getDocComment(),
                [
                    new Compound([
                        new Object_(new Fqsen('\Shopsys\FrameworkBundle\Model\Category\CategoryFacade')),
                        new Null_(),
                    ]),
                ],
            ],
            [
                $reflectionClass->getMethod('returnsFrameworkArticleDataArray')->getDocComment(),
                [new Array_(new Object_(new Fqsen('\Shopsys\FrameworkBundle\Model\Article\ArticleData')))],
            ],
            [
                $reflectionClass->getMethod('returnsFrontendApiProductRepository')->getDocComment(),
                [new Object_(new Fqsen('\Shopsys\FrontendApiBundle\Model\Product\ProductRepository'))],
            ],
            [
                $reflectionClass->getMethod('returnsInt')->getDocComment(),
                [new Integer()],
            ],
            [
                $reflectionClass->getMethod('acceptsVariousParameters')->getDocComment(),
                [],
            ],
            [
                $reflectionClass->getMethod('returnsNotTypedArray')->getDocComment(),
                [new Array_()],
            ],
        ];
    }

    /**
     * @return array<(\Roave\BetterReflection\Reflection\ReflectionParameter|string|null)>[]
     */
    public static function methodPhpDocParamTypeDataProvider(): array
    {
        $reflectionClass = ReflectionObject::createFromName(DummyClassForAnnotationsReplacer::class);
        $reflectionMethod = $reflectionClass->getMethod('acceptsVariousParameters');

        return [
            [
                $reflectionMethod->getParameter('categoryFacade'),
                '\Shopsys\FrameworkBundle\Model\Category\CategoryFacade',
            ],
            [
                $reflectionMethod->getParameter('categoryFacadeOrNull'),
                '\Shopsys\FrameworkBundle\Model\Category\CategoryFacade|null',
            ],
            [
                $reflectionMethod->getParameter('array'),
                '\Shopsys\FrameworkBundle\Model\Article\ArticleData[]',
            ],
            [
                $reflectionMethod->getParameter('integer'),
                'int',
            ],
        ];
    }

    /**
     * @return array<\Roave\BetterReflection\Reflection\ReflectionProperty|string>[]
     */
    public static function methodPhpDocPropertyTypeDataProvider(): array
    {
        $reflectionClass = ReflectionObject::createFromName(DummyClassForAnnotationsReplacer::class);

        return [
            [
                $reflectionClass->getProperty('categoryFacadeOrNull'),
                '\Shopsys\FrameworkBundle\Model\Category\CategoryFacade|null',
            ],
            [
                $reflectionClass->getProperty('integer'),
                'int',
            ],
            [
                $reflectionClass->getProperty('articleDataArray'),
                '\Shopsys\FrameworkBundle\Model\Article\ArticleData[]',
            ],
        ];
    }
}
