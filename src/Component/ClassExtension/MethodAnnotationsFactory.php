<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension;

use OutOfBoundsException;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Shopsys\FrameworkBundle\Component\ClassExtension\Exception\DocBlockParserException;

class MethodAnnotationsFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacementsMap
     */
    protected AnnotationsReplacementsMap $annotationsReplacementsMap;

    /**
     * @var \Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacer
     */
    protected AnnotationsReplacer $annotationsReplacer;

    /**
     * @var \Shopsys\FrameworkBundle\Component\ClassExtension\DocBlockParser
     */
    protected DocBlockParser $docBlockParser;

    /**
     * @param \Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacementsMap $annotationsReplacementsMap
     * @param \Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacer $annotationsReplacer
     * @param \Shopsys\FrameworkBundle\Component\ClassExtension\DocBlockParser $docBlockParser
     */
    public function __construct(
        AnnotationsReplacementsMap $annotationsReplacementsMap,
        AnnotationsReplacer $annotationsReplacer,
        DocBlockParser $docBlockParser
    ) {
        $this->annotationsReplacementsMap = $annotationsReplacementsMap;
        $this->annotationsReplacer = $annotationsReplacer;
        $this->docBlockParser = $docBlockParser;
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionClass $frameworkClassBetterReflection
     * @param \Roave\BetterReflection\Reflection\ReflectionClass $projectClassBetterReflection
     * @return string
     */
    public function getProjectClassNecessaryMethodAnnotationsLines(
        ReflectionClass $frameworkClassBetterReflection,
        ReflectionClass $projectClassBetterReflection
    ): string {
        $projectClassDocBlock = $projectClassBetterReflection->getDocComment();
        $methodAnnotationsLines = '';
        foreach ($frameworkClassBetterReflection->getMethods() as $method) {
            $methodAnnotationLine = $this->getMethodAnnotationLine($method, $projectClassBetterReflection);
            if ($methodAnnotationLine !== '' && strpos($projectClassDocBlock, $methodAnnotationLine) === false) {
                $methodAnnotationsLines .= $methodAnnotationLine;
            }
        }

        return $methodAnnotationsLines;
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionMethod $reflectionMethodFromFrameworkClass
     * @param \Roave\BetterReflection\Reflection\ReflectionClass $projectClassBetterReflection
     * @return string
     */
    public function getMethodAnnotationLine(
        ReflectionMethod $reflectionMethodFromFrameworkClass,
        ReflectionClass $projectClassBetterReflection
    ): string {
        foreach ($this->annotationsReplacementsMap->getPatterns() as $frameworkClassPattern) {
            $methodName = $reflectionMethodFromFrameworkClass->getName();

            if ($this->isMethodImplementedInClass($methodName, $projectClassBetterReflection)) {
                continue;
            }

            $methodReturnTypeIsExtended = $this->methodReturningTypeIsExtendedInProject(
                $frameworkClassPattern,
                $this->docBlockParser->getReturnTypes($reflectionMethodFromFrameworkClass->getDocComment())
            );

            $methodParameterTypeIsExtended = $this->methodParameterTypeIsExtendedInProject(
                $frameworkClassPattern,
                $reflectionMethodFromFrameworkClass->getParameters()
            );

            if ($methodReturnTypeIsExtended || $methodParameterTypeIsExtended) {
                $optionalStaticKeyword = $reflectionMethodFromFrameworkClass->isStatic() ? 'static ' : '';

                $replaceReturnType = $this->annotationsReplacer->replaceInMethodReturnType(
                    $reflectionMethodFromFrameworkClass
                );

                $returnType = $replaceReturnType !== '' ? $replaceReturnType . ' ' : '';
                $parameterNamesWithTypes = $this->getMethodParameterNamesWithTypes(
                    $reflectionMethodFromFrameworkClass
                );

                return sprintf(
                    " * @method %s%s%s(%s)\n",
                    $optionalStaticKeyword,
                    $returnType,
                    $methodName,
                    $parameterNamesWithTypes
                );
            }
        }

        return '';
    }

    /**
     * @param string $methodName
     * @param \Roave\BetterReflection\Reflection\ReflectionClass $reflectionClass
     * @return bool
     */
    protected function isMethodImplementedInClass(string $methodName, ReflectionClass $reflectionClass): bool
    {
        try {
            $reflectionMethod = $reflectionClass->getMethod($methodName);
            return $reflectionMethod->getDeclaringClass()->getName() === $reflectionClass->getName();
        } catch (OutOfBoundsException $ex) {
            return false;
        }
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionMethod $reflectionMethod
     * @return string
     */
    protected function getMethodParameterNamesWithTypes(ReflectionMethod $reflectionMethod): string
    {
        $methodParameterNamesWithTypes = [];
        foreach ($reflectionMethod->getParameters() as $methodParameter) {
            $methodParameterNamesWithTypes[] = sprintf(
                '%s $%s',
                $this->annotationsReplacer->replaceInParameterType($methodParameter),
                $methodParameter->getName()
            );
        }

        return implode(', ', $methodParameterNamesWithTypes);
    }

    /**
     * @param string $frameworkClassPattern
     * @param \phpDocumentor\Reflection\Type[] $docBlockReturnTypes
     * @return bool
     */
    protected function methodReturningTypeIsExtendedInProject(
        string $frameworkClassPattern,
        array $docBlockReturnTypes
    ): bool {
        foreach ($docBlockReturnTypes as $docBlockReturnType) {
            if (preg_match($frameworkClassPattern, (string)$docBlockReturnType)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $frameworkClassPattern
     * @param \Roave\BetterReflection\Reflection\ReflectionParameter[] $methodParameters
     * @return bool
     */
    protected function methodParameterTypeIsExtendedInProject(
        string $frameworkClassPattern,
        array $methodParameters
    ): bool {
        foreach ($methodParameters as $methodParameter) {
            try {
                $paramTypeString = (string)$this->docBlockParser->getParameterType($methodParameter);
            } catch (DocBlockParserException $exception) {
                $paramTypeString = '';
            }

            if (preg_match($frameworkClassPattern, $paramTypeString)) {
                return true;
            }
        }

        return false;
    }
}
