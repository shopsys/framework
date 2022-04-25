<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension;

use phpDocumentor\Reflection\DocBlock\Tags\TagWithType;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Type;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use Shopsys\FrameworkBundle\Component\ClassExtension\Exception\DocBlockParserAmbiguousTagException;
use Shopsys\FrameworkBundle\Component\ClassExtension\Exception\DocBlockParserEmptyDocBlockException;
use Shopsys\FrameworkBundle\Component\ClassExtension\Exception\DocBlockParserMissingTagException;

class DocBlockParser
{
    /**
     * @var \phpDocumentor\Reflection\DocBlockFactory
     */
    protected DocBlockFactory $docBlockFactory;

    public function __construct()
    {
        $this->docBlockFactory = DocBlockFactory::createInstance();
    }

    /**
     * @param string $docBlock
     * @return \phpDocumentor\Reflection\Type[]
     */
    public function getReturnTypes(string $docBlock): array
    {
        if ($docBlock === '') {
            return [];
        }

        /** @var \phpDocumentor\Reflection\DocBlock\Tags\TagWithType[] $tags */
        $tags = $this->docBlockFactory->create($docBlock)->getTagsByName('return');

        return array_map(static fn (TagWithType $tag) => $tag->getType(), $tags);
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionParameter $reflectionParameter
     * @return \phpDocumentor\Reflection\Type
     */
    public function getParameterType(ReflectionParameter $reflectionParameter): Type
    {
        $docBlock = $reflectionParameter->getDeclaringFunction()->getDocComment();

        if ($docBlock === '') {
            throw new DocBlockParserEmptyDocBlockException();
        }

        /** @var \phpDocumentor\Reflection\DocBlock\Tags\Param[] $functionParamTags */
        $functionParamTags = $this->docBlockFactory
            ->create($docBlock)
            ->getTagsByName('param');

        /** @var \phpDocumentor\Reflection\Type|null $paramType */
        $paramType = null;

        foreach ($functionParamTags as $tag) {
            if ($tag->getVariableName() === $reflectionParameter->getName()) {
                $paramType = $tag->getType();
            }
        }

        if ($paramType === null) {
            throw new DocBlockParserMissingTagException('@param', $reflectionParameter->getName());
        }

        return $paramType;
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionProperty $reflectionProperty
     * @return \phpDocumentor\Reflection\Type
     */
    public function getPropertyType(ReflectionProperty $reflectionProperty): Type
    {
        $docBlock = $reflectionProperty->getDocComment();

        if ($docBlock === '') {
            throw new DocBlockParserEmptyDocBlockException();
        }

        /** @var \phpDocumentor\Reflection\DocBlock\Tags\Var_[] $propertyVarTags */
        $propertyVarTags = $this->docBlockFactory
            ->create($docBlock)
            ->getTagsByName('var');

        if (count($propertyVarTags) > 1) {
            throw new DocBlockParserAmbiguousTagException('@var');
        }

        if (!isset($propertyVarTags[0])) {
            throw new DocBlockParserMissingTagException('@var', $reflectionProperty->getName());
        }

        return $propertyVarTags[0]->getType();
    }
}
