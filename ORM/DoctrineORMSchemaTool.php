<?php

/*
 * This file is part of the h4cc/AliceFixtureBundle package.
 *
 * (c) Julius Beckmann <github@h4cc.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace h4cc\AliceFixturesBundle\ORM;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Tools\SchemaTool as DoctrineSchemaTool;

/**
 * Helper tool for creating and dropping ORM Schemas.
 *
 * @author Julius Beckmann <github@h4cc.de>
 */
class DoctrineORMSchemaTool implements SchemaToolInterface
{
    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function truncateSchema(array $classes)
    {
        $this->foreachObjectManagers(function(ObjectManager $objectManager) use ($classes) {
            $connection = $objectManager->getConnection();

            $metadatas = $this->getMetadatas($objectManager, $classes);

            $connection->query('SET FOREIGN_KEY_CHECKS = 0;');
            foreach ($metadatas as $metadata) {
                $connection->query('TRUNCATE '. $metadata->getTableName() .';');
            }
            $connection->query('SET FOREIGN_KEY_CHECKS = 1;');
        });
    }

    /**
     * {@inheritDoc}
     */
    public function dropSchema(array $classes = null)
    {
        $this->foreachObjectManagers(function(ObjectManager $objectManager) use ($classes) {
            $schemaTool = new DoctrineSchemaTool($objectManager);

            if ($classes) {
                $schemaTool->dropSchema($this->getMetadatas($objectManager, $classes));
            } else {
                $schemaTool->dropDatabase();
            }
        });
    }

    /**
     * {@inheritDoc}
     */
    public function createSchema(array $classes = null)
    {
        $this->foreachObjectManagers(function(ObjectManager $objectManager) use ($classes) {
            $schemaTool = new DoctrineSchemaTool($objectManager);

            if ($classes) {
                $schemaTool->createSchema($this->getMetadatas($objectManager, $classes));
            } else {
                $metadata = $objectManager->getMetadataFactory()->getAllMetadata();

                $schemaTool->createSchema($metadata);
            }
        });
    }

    private function getMetadatas(ObjectManager $objectManager, array $classes)
    {
        $array = [];

        foreach ($classes as $class) {
            $array[] = $objectManager->getClassMetadata($class);
        }

        return $array;
    }

    private function foreachObjectManagers($callback)
    {
        array_map($callback, $this->managerRegistry->getManagers());
    }
}
