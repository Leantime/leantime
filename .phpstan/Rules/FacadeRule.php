<?php

namespace Leanstan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

class FacadeRule implements Rule
{
    private const ALLOWED_FACADES = ['Cache', 'Log', 'parent', 'self'];

    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof StaticCall) {
            return [];
        }

        if (!$node->class instanceof \PhpParser\Node\Name) {
            return [];
        }

        $className = $node->class->toString();

        // Check if it's likely a facade
        $parts = $node->class->getParts();
        if (strpos($className, 'Illuminate\\Support\\Facades\\') === 0 || count($parts) === 1) {
            $facadeName = count($parts) === 1 ? $parts[0] : end($parts);

            if (!in_array($facadeName, self::ALLOWED_FACADES)) {
                return [
                    "Only Cache:: and Log:: facades are allowed. Consider using dependency injection or helpers instead of {$facadeName}::."
                ];
            }
        }

        return [];
    }
}
