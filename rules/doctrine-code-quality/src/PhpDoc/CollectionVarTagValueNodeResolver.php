<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\PhpDoc;

use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\DoctrineCodeQuality\NodeAnalyzer\DoctrinePropertyAnalyzer;

final class CollectionVarTagValueNodeResolver
{
    /**
     * @var DoctrinePropertyAnalyzer
     */
    private $doctrinePropertyAnalyzer;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    public function __construct(
        DoctrinePropertyAnalyzer $doctrinePropertyAnalyzer,
        PhpDocInfoFactory $phpDocInfoFactory
    ) {
        $this->doctrinePropertyAnalyzer = $doctrinePropertyAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }

    public function resolve(Property $property): ?VarTagValueNode
    {
        $doctrineOneToManyTagValueNode = $this->doctrinePropertyAnalyzer->matchDoctrineOneToManyTagValueNode($property);
        if ($doctrineOneToManyTagValueNode === null) {
            return null;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        if (! $phpDocInfo instanceof PhpDocInfo) {
            return null;
        }

        return $phpDocInfo->getVarTagValueNode();
    }
}
