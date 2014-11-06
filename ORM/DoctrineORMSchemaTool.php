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

    /**
     * {@inheritDoc}
     */
    public function dropSchema(array $classes = null)
    {
        $this->foreachObjectManagers(function(ObjectManager $objectManager) use ($classes) {
            $schemaTool = new DoctrineSchemaTool($objectManager);

            if ($classes) {
                $schemaTool->dropSchema($classes);
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
            if ($classes) {
                $schemaTool->createSchema($classes);
            } else {
                $metadata = $objectManager->getMetadataFactory()->getAllMetadata();

                $schemaTool = new DoctrineSchemaTool($objectManager);
                $schemaTool->createSchema($metadata);
            }
        });
    }

    private function foreachObjectManagers($callback)
    {
        array_map($callback, $this->managerRegistry->getManagers());
    }
}
