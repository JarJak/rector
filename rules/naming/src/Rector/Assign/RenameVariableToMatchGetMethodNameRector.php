<?php

declare(strict_types=1);

namespace Rector\Naming\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeTraverser;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Naming\Naming\ExpectedNameResolver;
use Rector\Naming\VariableRenamer;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\RenameVariableToMatchGetMethodNameRectorTest
 */
final class RenameVariableToMatchGetMethodNameRector extends AbstractRector
{
    /**
     * @var ExpectedNameResolver
     */
    private $expectedNameResolver;

    /**
     * @var VariableRenamer
     */
    private $variableRenamer;

    public function __construct(ExpectedNameResolver $expectedNameResolver, VariableRenamer $variableRenamer)
    {
        $this->expectedNameResolver = $expectedNameResolver;
        $this->variableRenamer = $variableRenamer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Rename variable to match get method name', [
            new CodeSample(
                <<<'PHP'
class SomeClass {
    public function run()
    {
        $a = $this->getRunner();
    }
}
PHP
,
                <<<'PHP'
class SomeClass {
    public function run()
    {
        $runner = $this->getRunner();
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        if (
            $node->expr instanceof ArrowFunction || ! $node->var instanceof Variable
        ) {
            return null;
        }

        $newName = $this->expectedNameResolver->resolveForGetCallExpr($node->expr);
        if ($newName === null || $this->isName($node, $newName)) {
            return null;
        }

        if ($this->shouldSkipForNameConflict($node, $newName)) {
            return null;
        }

        $this->renameVariable($node, $newName);

        return $node;
    }

    private function renameVariable(Assign $assign, string $newName): void
    {
        /** @var Variable $variableNode */
        $variableNode = $assign->var;

        /** @var string $originalName */
        $originalName = $variableNode->name;

        $this->renameInDocComment($assign, $originalName, $newName);

        $functionLike = $this->getCurrentFunctionLike($assign);
        if ($functionLike === null) {
            return;
        }

        $this->variableRenamer->renameVariableInClassMethodOrFunction(
            $functionLike,
            $assign,
            $originalName,
            $newName
        );
    }

    /**
     * @return Param[]
     */
    private function getParentNodeParams(Node $node): array
    {
        /** @var FunctionLike|null $parentNode */
        $parentNode = $this->getCurrentFunctionLike($node);
        if ($parentNode === null) {
            return [];
        }

        return $parentNode->getParams() ?? [];
    }

    /**
     * @return ClosureUse[]
     */
    private function getParentNodeUses(Node $node): array
    {
        /** @var FunctionLike|null $parentNode */
        $parentNode = $this->getCurrentFunctionLike($node);

        if ($parentNode === null || ! $parentNode instanceof Closure) {
            return [];
        }

        return $parentNode->uses ?? [];
    }

    /**
     * @todo decouple to standalone service
     */
    private function shouldSkipForNameConflict(Assign $assign, string $newName): bool
    {
        if ($this->skipOnConflictParamName($assign, $newName)) {
            return true;
        }

        if ($this->skipOnConflictClosureUsesName($assign, $newName)) {
            return true;
        }

        return $this->skipOnConflictOtherVariable($assign, $newName);
    }

    /**
     * @return ClassMethod|Function_|null
     */
    private function getCurrentFunctionLike(Node $node): ?FunctionLike
    {
        return $node->getAttribute(AttributeKey::METHOD_NODE) ?? $node->getAttribute(AttributeKey::FUNCTION_NODE);
    }

    private function skipOnConflictParamName(Assign $assign, string $newName): bool
    {
        $parentNodeParams = $this->getParentNodeParams($assign);
        if ($parentNodeParams === []) {
            return false;
        }

        $originalName = $this->getName($assign->var);

        $skip = false;
        $this->traverseNodesWithCallable($parentNodeParams, function (Node $node) use (
            $originalName,
            $newName,
            &$skip
        ): void {
            if (
                $node instanceof Param &&
                ($this->isVariableName($node->var, $newName) || $this->isVariableName($node->var, $originalName))
            ) {
                $skip = true;
            }
        });
        return $skip;
    }

    private function skipOnConflictClosureUsesName(Assign $assign, string $newName): bool
    {
        $skip = false;

        $parentNodeUses = $this->getParentNodeUses($assign);
        $this->traverseNodesWithCallable($parentNodeUses, function (Node $node) use ($newName, &$skip) {
            if ($this->isVariableName($node, $newName)) {
                $skip = true;
                return NodeTraverser::STOP_TRAVERSAL;
            }

            return null;
        });

        return $skip;
    }

    private function skipOnConflictOtherVariable(Assign $assign, string $newName): bool
    {
        $skip = false;
        $functionLike = $this->getCurrentFunctionLike($assign);
        if ($functionLike === null) {
            return false;
        }

        $this->traverseNodesWithCallable((array) $functionLike->stmts, function (Node $node) use ($newName, &$skip) {
            if (! $node instanceof Variable) {
                return null;
            }

            if ($this->isVariableName($node, $newName)) {
                $skip = true;
                return NodeTraverser::STOP_TRAVERSAL;
            }

            return null;
        });

        return $skip;
    }

    private function renameInDocComment(Node $node, string $originalName, string $newName): void
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return;
        }

        $varTagValueNode = $phpDocInfo->getByType(VarTagValueNode::class);
        if ($varTagValueNode === null) {
            return;
        }

        if ($varTagValueNode->variableName !== '$' . $originalName) {
            return;
        }

        $varTagValueNode->variableName = '$' . $newName;
    }
}
