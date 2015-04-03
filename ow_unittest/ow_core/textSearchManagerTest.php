<?php

class TextSearchManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Entity active
     */
    CONST ENTITY_ACTIVE = 1;

    /**
     * Entity not active
     */
    CONST ENTITY_NOT_ACTIVE = 0;

    /**
     * Tear down
     */
    protected function tearDown()
    {
        OW::getTextSearchManager()->deleteAllEntities();
    }

    /**
     * Test add entities
     */
    public function testAddEntities()
    {
        $entities = array(
            array(
                'entity_type' => 'forum_post',
                'entity_id' => 1,
                'text' => 'forum post title',
                'tags' => array(
                    'forum_post'
                ),
                'active' => true
            ),
            array(
                'entity_type' => 'forum_post',
                'entity_id' => 1,
                'text' => 'forum post body',
                'tags' => array(
                    'forum_post'
                ),
                'active' => true
            )
        );

        // add entities
        foreach ($entities as $entitiy)
        {
            $this->assertTrue(OW::getTextSearchManager()->
                    addEntity($entitiy['entity_type'], $entitiy['entity_id'], $entitiy['text'], $entitiy['tags'], $entitiy['active']));
        }
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
                ),
                'active' => true
            ),
            array(
                'entity_type' => 'forum_post',
                'entity_id' => 1,
                'text' => 'forum post body',
                'tags' => array(
                    'forum_post'
                ),
                'active' => true
            )
        );

        // add and delete test entities
        foreach ($entities as $entitiy)
        {
            $this->assertTrue(OW::getTextSearchManager()->
                    addEntity($entitiy['entity_type'], $entitiy['entity_id'], $entitiy['text'], $entitiy['tags'], $entitiy['active']));

            $this->assertTrue(OW::getTextSearchManager()->deleteEntity($entitiy['entity_type'], $entitiy['entity_id']));
        }

        // do we have entities?
        $entities = OW::getTextSearchManager()->getAllEntities(0, 2);
        $this->assertInternalType('array', $entities);
        $this->assertEquals(0, count($entities));

        // delete an non existing entity
        $this->assertFalse(OW::getTextSearchManager()->deleteEntity('non _existing_type', 100));
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
                ),
                'active' => true
            ),
            array(
                'entity_type' => 'forum_post',
                'entity_id' => 1,
                'text' => 'forum post body',
                'tags' => array(
                    'forum_post'
                ),
                'active' => true
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
            $this->assertTrue(OW::getTextSearchManager()->
                    addEntity($entitiy['entity_type'], $entitiy['entity_id'], $entitiy['text'], $entitiy['tags'], $entitiy['active']));
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
                // all forum post entities should be deactivated
                case 'forum_post' :
                    $this->assertEquals(self::ENTITY_NOT_ACTIVE, $entity['status']);
                    break;

                default :
                    $this->assertEquals(self::ENTITY_ACTIVE, $entity['status']);
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
            $this->assertTrue(OW::getTextSearchManager()->
                    addEntity($entitiy['entity_type'], $entitiy['entity_id'], $entitiy['text'], $entitiy['tags'], $entitiy['active']));
        }

        // activate all entities
        OW::getTextSearchManager()->activateAllEntities();

        // get all entities
        $entities = OW::getTextSearchManager()->getAllEntities(0, 3);

        //  check entities status
        foreach ($entities as $entity)
        {
            $this->assertEquals(self::ENTITY_ACTIVE, $entity['status']);
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
            $this->assertTrue(OW::getTextSearchManager()->
                    addEntity($entitiy['entity_type'], $entitiy['entity_id'], $entitiy['text'], $entitiy['tags'], $entitiy['active']));
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
                ),
                'active' => true
            ),
            array(
                'entity_type' => 'forum_post',
                'entity_id' => 1,
                'text' => 'forum post body',
                'tags' => array(
                    'forum_post'
                ),
                'active' => true
            ),
            array(
                'entity_type' => 'forum_post',
                'entity_id' => 2,
                'text' => 'forum post title',
                'tags' => array(
                    'forum_post'
                ),
                'active' => true
            ),
            array(
                'entity_type' => 'forum_post',
                'entity_id' => 2,
                'text' => 'forum post body',
                'tags' => array(
                    'forum_post'
                ),
                'active' => true
            ),
            array(
                'entity_type' => 'forum_topic',
                'entity_id' => 1,
                'text' => 'forum topic title',
                'tags' => array(
                    'forum_topic'
                ),
                'active' => true
            ),
            array(
                'entity_type' => 'forum_category',
                'entity_id' => 1,
                'text' => 'forum category title',
                'tags' => array(
                    'forum_category'
                ),
                'active' => true
            )
        );

        // add test entities
        foreach ($entities as $entitiy)
        {
            $this->assertTrue(OW::getTextSearchManager()->
                    addEntity($entitiy['entity_type'], $entitiy['entity_id'], $entitiy['text'], $entitiy['tags'], $entitiy['active']));
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
            )
        );

        // add and activate test entities 
        foreach ($entities as $entitiy)
        {
            $this->assertTrue(OW::getTextSearchManager()->
                    addEntity($entitiy['entity_type'], $entitiy['entity_id'], $entitiy['text'], $entitiy['tags'], $entitiy['active']));

            $this->assertTrue(OW::getTextSearchManager()->
                    setEntityStatus($entitiy['entity_type'], $entitiy['entity_id'], true));
        }

        // get all entities
        $entities = OW::getTextSearchManager()->getAllEntities(0, 2);

        $this->assertInternalType('array', $entities);
        $this->assertEquals(2, count($entities));

        //  check entities status
        foreach ($entities as $entity)
        {
            $this->assertEquals(self::ENTITY_ACTIVE, $entity['status']);
        }
    }
}