<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\PropertyInfo\PhpStan;

/**
 * NameScope class adapted from PHPStan code.
 *
 * @copyright Copyright (c) 2016, PHPStan https://github.com/phpstan/phpstan-src
 * @copyright Copyright (c) 2016, Ondřej Mirtes
 * @author Baptiste Leduc <baptiste.leduc@gmail.com>
 *
 * @internal
 */
final class NameScope
{
    private string $calledClassName;
    private string $namespace;
    /** @var array<string, string> alias(string) => fullName(string) */
    private array $uses;

    public function __construct(string $calledClassName, string $namespace, array $uses = [])
    {
        $this->calledClassName = $calledClassName;
        $this->namespace = $namespace;
        $this->uses = $uses;
    }

    public function resolveStringName(string $name): string
    {
        if (str_starts_with($name, '\\')) {
            return ltrim($name, '\\');
        }

        $nameParts = explode('\\', $name);
        $firstNamePart = $nameParts[0];
        if (isset($this->uses[$firstNamePart])) {
            if (1 === \count($nameParts)) {
                return $this->uses[$firstNamePart];
            }
            array_shift($nameParts);

            return sprintf('%s\\%s', $this->uses[$firstNamePart], implode('\\', $nameParts));
        }

        if (null !== $this->namespace) {
            return sprintf('%s\\%s', $this->namespace, $name);
        }

        return $name;
    }

    public function resolveRootClass(): string
    {
        return $this->resolveStringName($this->calledClassName);
    }
}
