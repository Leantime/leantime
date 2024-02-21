<?php

use Rector\Set\ValueObject\LevelSetList;
use PhpCsFixer\Fixer\Operator\ConcatSpaceFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Option;

use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ECSConfig $configurator): void {

  // alternative to CLI arguments, easier to maintain and extend
  $configurator->paths([__DIR__ . '/lib', __DIR__ . '/tests']);

  // choose 
  $configurator->sets([
    SetList::CLEAN_CODE, SetList::PSR_12//, LevelSetList::UP_TO_PHP_81 //, SymfonySetList::SYMFONY_60
  ]);

  $configurator->ruleWithConfiguration(ConcatSpaceFixer::class, [
    'spacing' => 'one'
  ]);

  // indent and tabs/spaces
  // [default: spaces]. BUT: tabs are superiour due to accessibility reasons
  $configurator->indentation('tab');
};
