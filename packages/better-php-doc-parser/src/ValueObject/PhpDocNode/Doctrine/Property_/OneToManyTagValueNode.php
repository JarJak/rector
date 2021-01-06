<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\Contract\Doctrine\MappedByNodeInterface;
use Rector\BetterPhpDocParser\Contract\Doctrine\ToManyTagNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TypeAwareTagValueNodeInterface;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class OneToManyTagValueNode extends AbstractDoctrineTagValueNode implements ToManyTagNodeInterface, MappedByNodeInterface, TypeAwareTagValueNodeInterface
{
    /**
     * @var string|null
     */
    private $fullyQualifiedTargetEntity;

    /**
     * @param mixed[] $items
     */
    public function __construct(array $items, ?string $fullyQualifiedTargetEntity = null) {
        $this->fullyQualifiedTargetEntity = $fullyQualifiedTargetEntity;

        parent::__construct($items);
    }

    public function getTargetEntity(): string
    {
        return $this->items['targetEntity'];
    }

    public function getMappedBy(): ?string
    {
        return $this->items['mappedBy'];
    }

    public function removeMappedBy(): void
    {
        $this->items['mappedBy'] = null;
    }

    public function changeTargetEntity(string $targetEntity): void
    {
        $this->items['targetEntity'] = $targetEntity;
    }

    public function getFullyQualifiedTargetEntity(): ?string
    {
        return $this->fullyQualifiedTargetEntity;
    }

    public function getShortName(): string
    {
        return '@ORM\OneToMany';
    }
}
