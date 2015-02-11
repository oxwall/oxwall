<?php

/**
 * Oxwall: Open Source Community Software
 * @copyright Skalfa LLC Copyright (C) 2009. All rights reserved.
 * @license CPAL 1.0 License - http://www.oxwall.org/license
 */

/**
 * Data Transfer Object for `base_remote_auth` table.
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow.base.bol
 * @since 1.0
 */

class BOL_RemoteAuth extends OW_Entity
{
    /**
     * @var string
     */
    public $remoteId;

    /**
     * @var int
     */
    public $userId;

    /**
     * @var string
     */
    public $type;
    
    /**
     * @var string
     */
    public $timeStamp;
    
    /**
     * @var string
     */
    public $custom;
}
