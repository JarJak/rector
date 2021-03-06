<?php

declare(strict_types=1);

namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\ValueObject\MethodName;

final class PromotedPropertyResolver
{
    /**
     * @return Param[]
     */
    public function resolveFromClass(Class_ $class): array
    {
        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if ($constructClassMethod === null) {
            return [];
        }

        $promotedPropertyParams = [];
        foreach ($constructClassMethod->getParams() as $param) {
            if ($param->flags === 0) {
                continue;
            }

            $promotedPropertyParams[] = $param;
        }

        return $promotedPropertyParams;
    }
}
