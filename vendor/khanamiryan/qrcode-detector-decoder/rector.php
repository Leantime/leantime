<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Nette\Set\NetteSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Core\Configuration\Option;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Symfony\Set\SensiolabsSetList;
use Rector\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector;


return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/lib'
    ]);

    $parameters = $rectorConfig->parameters();
    $parameters->set(
        Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER,
        __DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml'
    );

    $rectorConfig->sets([
        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
        NetteSetList::ANNOTATIONS_TO_ATTRIBUTES,
        SensiolabsSetList::FRAMEWORK_EXTRA_61,
        SymfonySetList::SYMFONY_60,
        LevelSetList::UP_TO_PHP_81
    ]);

    // register a single rule
    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);
    $rectorConfig->rule(AddReturnTypeDeclarationRector::class);
    $rectorConfig->rules([
        AddVoidReturnTypeWhereNoReturnRector::class,
        ArrayShapeFromConstantArrayReturnRector::class,
        ParamTypeByMethodCallTypeRector::class,
        ParamTypeByParentCallTypeRector::class,
        PropertyTypeDeclarationRector::class,
        ReturnTypeFromReturnNewRector::class,
        // ReturnTypeFromStrictBoolReturnExprRector::class,
        // ReturnTypeFromStrictNativeFuncCallRector::class,
        // ReturnTypeFromStrictNewArrayRector::class,
        TypedPropertyFromAssignsRector::class
    ]);

    // define sets of rules
    //    $rectorConfig->sets([
    //        LevelSetList::UP_TO_PHP_80
    //    ]);
};
