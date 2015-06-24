<?php

/**
 * EXHIBIT A. Common Public Attribution License Version 1.0
 * The contents of this file are subject to the Common Public Attribution License Version 1.0 (the “License”);
 * you may not use this file except in compliance with the License. You may obtain a copy of the License at
 * http://www.oxwall.org/license. The License is based on the Mozilla Public License Version 1.1
 * but Sections 14 and 15 have been added to cover use of software over a computer network and provide for
 * limited attribution for the Original Developer. In addition, Exhibit A has been modified to be consistent
 * with Exhibit B. Software distributed under the License is distributed on an “AS IS” basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for the specific language
 * governing rights and limitations under the License. The Original Code is Oxwall software.
 * The Initial Developer of the Original Code is Oxwall Foundation (http://www.oxwall.org/foundation).
 * All portions of the code written by Oxwall Foundation are Copyright (c) 2011. All Rights Reserved.

 * EXHIBIT B. Attribution Information
 * Attribution Copyright Notice: Copyright 2011 Oxwall Foundation. All rights reserved.
 * Attribution Phrase (not exceeding 10 words): Powered by Oxwall community software
 * Attribution URL: http://www.oxwall.org/
 * Graphic Image as provided in the Covered Code.
 * Display of Attribution Information is required in Larger Works which are defined in the CPAL as a work
 * which combines Covered Code or portions thereof with code not governed by the terms of the CPAL.
 */

/**
 * @author Aybat Duyshokov <duyshokov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_MediaPanelFileDao extends OW_BaseDao
{

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var BOL_MediapFileDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_MediaPanelFileDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @see BOL_MediaPanelFileDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_MediaPanelFile';
    }

    /**
     * @see BOL_MediaPanelFileDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_media_panel_file';
    }

    public function findImages( $plugin, $userId=null, $first, $count )
    {
        $ex = new OW_Example();
            $ex->andFieldEqual('plugin', $plugin);

        if ( $userId !== null && intval($userId) > 0 )
        {
            $ex->andFieldEqual('userId', $userId);
        }

        $ex->setLimitClause($first, $count)->setOrder('stamp DESC');

        return $this->findListByExample($ex);
    }

    public function findImage( $imageId )
    {
        return $this->findById($imageId);
    }

    public function countGalleryImages( $plugin, $userId=null )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('plugin', $plugin)
            ->andFieldEqual('type', 'image');

        if ( $userId !== null && intval($userId) > 0 )
        {
            $ex->andFieldEqual('userId', $userId);
        }

        return $this->countByExample($ex);
    }

    public function deleteImages( $plugin, $count )
    {
        $images = $this->findImages($plugin, null, 0, $count);

        foreach ( $images as $image )
        {
            $data = $image->getData();

            $this->deleteById($image->id);

            $storage = OW::getStorage();

            $storage->removeFile(OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . $image->id . '-' . $data->name);
        }
    }

    public function deleteImagesByUserId( $userId )
    {
        $ex = new OW_Example();

        $ex->andFieldEqual('userId', (int)$userId);

        $images = $this->findListByExample($ex);

        foreach ( $images as $image )
        {
            $data = $image->getData();

            $storage = OW::getStorage();

            $storage->removeFile(OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . $image->id . '-' . $data->name);

            $this->deleteById($image->id);
        }
    }

    public function deleteImageById( $id )
    {
        $image = $this->findById((int)$id);

        $data = $image->getData();

        $storage = OW::getStorage();

        $storage->removeFile(OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . $image->id . '-' . $data->name);

        $this->deleteById($image->id);
    }
}