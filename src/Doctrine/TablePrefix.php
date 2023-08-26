<?php

declare(strict_types=1);

namespace Blacktrs\WPBundle\Doctrine;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class TablePrefix
{
    private string $prefix;

    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();

        if (!$classMetadata->isInheritanceTypeSingleTable() || $classMetadata->getName() === $classMetadata->rootEntityName) {
            $classMetadata->setPrimaryTable(['name' => $this->prefix.$classMetadata->getTableName()]);
        }

        foreach ($classMetadata->getAssociationMappings() as $fieldName => $associationMapping) {
            if ($associationMapping['type'] === ClassMetadataInfo::MANY_TO_MANY && $associationMapping['isOwningSide']) {
                $mappedTableName = $associationMapping['joinTable']['name'];
                $classMetadata->associationMappings[$fieldName]['joinTable']['name'] = $this->prefix.$mappedTableName;
            }
        }
    }
}
