<?php

class TextSearchManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tear down
     */
    protected function tearDown()
    {
        OW::getTextSearchManager()->deleteAllEntities();
    }

    /**
     * Test delete entities
     */
    public function testDeleteEntities()
    {
        $entities = array(
            array(
                'entity_type' => 'forum_post',
                'entity_id' => 1,
                'text' => 'forum post title',
                'tags' => array(
                    'forum_post'
                )
            ),
            array(
                'entity_type' => 'forum_post',
                'entity_id' => 1,
                'text' => 'forum post body',
                'tags' => array(
                    'forum_post'
                )
            )
        );

        // add and delete test entities
        foreach ($entities as $entitiy)
        {
            OW::getTextSearchManager()->
                    addEntity($entitiy['entity_type'], $entitiy['entity_id'], $entitiy['text'], $entitiy['tags']);

            OW::getTextSearchManager()->deleteEntity($entitiy['entity_type'], $entitiy['entity_id']);
        }

        // do we have entities?
        $entities = OW::getTextSearchManager()->getAllEntities(0, 2);
        $this->assertInternalType('array', $entities);
        $this->assertEquals(0, count($entities));
    }

    /**
     * Test deactivate all entities
     */
    public function testDeactivateAllEntities()
    {
        $entities = array(
            array(
                'entity_type' => 'forum_post',
                'entity_id' => 1,
                'text' => 'forum post title',
                'tags' => array(
                    'forum_post'
                )
            ),
            array(
                'entity_type' => 'forum_post',
                'entity_id' => 1,
                'text' => 'forum post body',
                'tags' => array(
                    'forum_post'
                )
            ),
            array(
                'entity_type' => 'forum_topic',
                'entity_id' => 1,
                'text' => 'forum topic title',
                'tags' => array(
                    'forum_topic'
                )
            )
        );

        // add test entities
        foreach ($entities as $entitiy)
        {
            OW::getTextSearchManager()->
                    addEntity($entitiy['entity_type'], $entitiy['entity_id'], $entitiy['text'], $entitiy['tags']);
        }

        // deactivate all forum post entities
        OW::getTextSearchManager()->deactivateAllEntities('forum_post');

        // get all entities
        $entities = OW::getTextSearchManager()->getAllEntities(0, 3);

        //  check entities status
        foreach ($entities as $entity)
        {
            switch ($entity['entityType'])
            {
                // all forum posts entities should be deactivated
                case 'forum_post' :
                    $this->assertEquals(BASE_CLASS_AbstractSearchStorage::ENTITY_STATUS_NOT_ACTIVE, $entity['status']);
                    break;

                default :
                    $this->assertEquals(BASE_CLASS_AbstractSearchStorage::ENTITY_STATUS_ACTIVE, $entity['status']);
            }
        }
    }

    /**
     * Test activate all entities
     */
    public function testActivateAllEntities()
    {
        $entities = array(
            array(
                'entity_type' => 'forum_post',
                'entity_id' => 1,
                'text' => 'forum post title',
                'tags' => array(
                    'forum_post'
                )
            ),
            array(
                'entity_type' => 'forum_post',
                'entity_id' => 1,
                'text' => 'forum post body',
                'tags' => array(
                    'forum_post'
                )
            ),
            array(
                'entity_type' => 'forum_topic',
                'entity_id' => 1,
                'text' => 'forum topic title',
                'tags' => array(
                    'forum_topic'
                )
            )
        );

        // add test entities
        foreach ($entities as $entitiy)
        {
            OW::getTextSearchManager()->
                    addEntity($entitiy['entity_type'], $entitiy['entity_id'], $entitiy['text'], $entitiy['tags']);
        }

        // deactivate all entities
        OW::getTextSearchManager()->deactivateAllEntities();

        // activate all entities
        OW::getTextSearchManager()->activateAllEntities();

        // get all entities
        $entities = OW::getTextSearchManager()->getAllEntities(0, 3);

        //  check entities status
        foreach ($entities as $entity)
        {
            $this->assertEquals(BASE_CLASS_AbstractSearchStorage::ENTITY_STATUS_ACTIVE, $entity['status']);
        }
    }

    /**
     * Test search entities
     */
    public function testSearchEntities()
    {
        $entities = array(
            array(
                'entity_type' => 'forum_post',
                'entity_id' => 1,
                'text' => 'forum post title',
                'tags' => array(
                    'forum_post'
                ),
                'active' => false
            ),
            array(
                'entity_type' => 'forum_post',
                'entity_id' => 1,
                'text' => 'forum post body',
                'tags' => array(
                    'forum_post'
                ),
                'active' => false
            ),
            array(
                'entity_type' => 'forum_topic',
                'entity_id' => 1,
                'text' => 'forum topic title',
                'tags' => array(
                    'forum_topic'
                ),
                'active' => true
            )
        );

        // add test entities
        foreach ($entities as $entitiy)
        {
            OW::getTextSearchManager()->
                    addEntity($entitiy['entity_type'], $entitiy['entity_id'], $entitiy['text'], $entitiy['tags']);

            // deactivate an entity
            if (!$entitiy['active']) {
                OW::getTextSearchManager()->
                        setEntityStatus($entitiy['entity_type'], $entitiy['entity_id'], BASE_CLASS_AbstractSearchStorage::ENTITY_STATUS_NOT_ACTIVE);
            }
        }

        // search only active entities
        $this->assertEquals(1, OW::getTextSearchManager()->searchEntitiesCount('forum'));
        $entities = OW::getTextSearchManager()->searchEntities('forum', 0, 100);

        // did we get forum topic?
        $this->assertInternalType('array', $entities);
        $this->assertEquals(1, count($entities));

        $currentEntity = array_shift($entities);
        $this->assertEquals('forum_topic', $currentEntity['entityType']);
        $this->assertEquals('1', $currentEntity['entityId']);

        // search an non existing entity
        $this->assertEquals(0, OW::getTextSearchManager()->searchEntitiesCount('non existing entity'));
        $entities = OW::getTextSearchManager()->searchEntities('non existing entity', 0, 100);
        $this->assertInternalType('array', $entities);
        $this->assertEquals(0, count($entities));
    }

    /**
     * Test search entities by tags
     */
    public function testSearchEntitiesByTags()
    {
        $entities = array(
            array(
                'entity_type' => 'forum_post',
                'entity_id' => 1,
                'text' => 'forum post title',
                'tags' => array(
                    'forum_post'
                )
            ),
            array(
                'entity_type' => 'forum_post',
                'entity_id' => 1,
                'text' => 'forum post body',
                'tags' => array(
                    'forum_post'
                )
            ),
            array(
                'entity_type' => 'forum_post',
                'entity_id' => 2,
                'text' => 'forum post title',
                'tags' => array(
                    'forum_post'
                )
            ),
            array(
                'entity_type' => 'forum_post',
                'entity_id' => 2,
                'text' => 'forum post body',
                'tags' => array(
                    'forum_post'
                )
            ),
            array(
                'entity_type' => 'forum_topic',
                'entity_id' => 1,
                'text' => 'forum topic title',
                'tags' => array(
                    'forum_topic'
                )
            ),
            array(
                'entity_type' => 'forum_category',
                'entity_id' => 1,
                'text' => 'forum category title',
                'tags' => array(
                    'forum_category'
                )
            )
        );

        // add test entities
        foreach ($entities as $entitiy)
        {
            OW::getTextSearchManager()->
                    addEntity($entitiy['entity_type'], $entitiy['entity_id'], $entitiy['text'], $entitiy['tags']);
        }

        // search entities by tags
        $entities = OW::getTextSearchManager()->searchEntities('forum', 0, 100, array(
            'forum_post'
        ));

        // did we get only forum posts?
        $this->assertInternalType('array', $entities);
        $this->assertEquals(2, count($entities));

        foreach ($entities as $entity) 
        {
            $this->assertEquals('forum_post', $entity['entityType']);
        }
    }

    /**
     * Test set entity status
     */
    public function testSetEntityStatus()
    {
        $entities = array(
            array(
                'entity_type' => 'forum_post',
                'entity_id' => 1,
                'text' => 'forum post title',
                'tags' => array(
                    'forum_post'
                )
            ),
            array(
                'entity_type' => 'forum_post',
                'entity_id' => 1,
                'text' => 'forum post body',
                'tags' => array(
                    'forum_post'
                )
            )
        );

        // add and deactivate test entities 
        foreach ($entities as $entitiy)
        {
            OW::getTextSearchManager()->
                    addEntity($entitiy['entity_type'], $entitiy['entity_id'], $entitiy['text'], $entitiy['tags']);

            // deactivate entities
            OW::getTextSearchManager()->
                    setEntityStatus($entitiy['entity_type'], $entitiy['entity_id'], BASE_CLASS_AbstractSearchStorage::ENTITY_STATUS_NOT_ACTIVE);
        }

        // get all entities
        $entities = OW::getTextSearchManager()->getAllEntities(0, 2);

        $this->assertInternalType('array', $entities);
        $this->assertEquals(2, count($entities));

        //  check entities status
        foreach ($entities as $entity)
        {
            $this->assertEquals(BASE_CLASS_AbstractSearchStorage::ENTITY_STATUS_NOT_ACTIVE, $entity['status']);
        }
    }
}