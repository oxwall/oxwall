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
 * Preference Service
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_PreferenceService
{
    const PREFERENCE_ADD_FORM_ELEMENT_EVENT = 'base.preference_add_form_element';
    const PREFERENCE_SECTION_LABEL_EVENT = 'base.preference_section_label';

    /**
     * @var BOL_QuestionDao
     */
    private $preferenceDao;
    /**
     * @var BOL_PreferenceSectionDao
     */
    private $preferenceSectionDao;
    /**
     * @var BOL_PreferenceDataDao
     */
    private $preferenceDataDao;

    /**
     * @var array
     */
    private $preferenceData = array();
    /**
     * Singleton instance.
     *
     * @var BOL_PreferenceService
     */
    private static $classInstance;

    /**
     * Constructor.
     *
     */
    private function __construct()
    {

        $this->preferenceDao = BOL_PreferenceDao::getInstance();
        /* @var $this->preferenceDao BOL_PreferenceDao */
        $this->preferenceSectionDao = BOL_PreferenceSectionDao::getInstance();
        /* @var $this->preferenceSectionDao BOL_PreferenceSectionDao */
        $this->preferenceDataDao = BOL_PreferenceDataDao::getInstance();
        /* @var $this->preferenceDataDao BOL_PreferenceDataDao */
    }

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_PreferenceService
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
     * @return BOL_Preference
     */
    public function findAllPreference()
    {
        return $this->preferenceDao->findAllPreference();
    }

    /**
     *
     * @param string $key
     * @return BOL_Preference
     */
    public function findPreference( $key )
    {
        return $this->preferenceDao->findPreference( $key );
    }

     /**
     *
     * @param array $keyList
     * @return array <BOL_Preference>
     */
    public function findPreferenceList( $keyList )
    {
        $resultList = array();
        $result = $this->preferenceDao->findPreferenceList( $keyList );

        foreach( $result as $dto )
        {
            /* @var $dto BOL_Preference */
            $resultList[$dto->key] = $dto;
        }

        return $resultList;
    }

   /**
     *
     * @param string $section
     * @return array <BOL_Preference>
     */
    public function findPreferenceListBySectionName( $section )
    {
        return $this->preferenceDao->findPreferenceListBySectionName( $section );
    }

     /**
     *
     * @param string $key
     * @return boolean
     */
    public function deletePreference( $key )
    {
        $result = $this->preferenceDao->deletePreference($key);
        $this->preferenceDataDao->deleteByPreferenceNamesList(array( $key ));
        return $result;
    }

    /**
     *
     * @return BOL_PreferenceSection
     */
    public function findAllSections()
    {
        return $this->preferenceSectionDao->findAllSections();
    }

    /**
     *
     * @return BOL_PreferenceSection
     */
    public function findSection( $name )
    {
        return $this->preferenceSectionDao->findSection($name);
    }

    /**
     *
     * @param string $section
     * @return boolean
     */
    public function deleteSection( $section )
    {
        $list = $this->preferenceDao->findPreferenceListBySectionName($section);

        foreach( $list as $preference )
        {
            $this->deletePreference( $preference->key );
        }

        return $this->preferenceSectionDao->deleteSection( $section );
    }

    /**
     * @param array $preferenceList
     * @param int $userId
     * @return array[userId][preferenceName]
     */
    public function getPreferenceValue( $preferenceKey, $userId )
    {
        $result = $this->getPreferenceValueListByUserIdList( array( $preferenceKey ), array( $userId ) );
        return isset($result[$userId][$preferenceKey]) ? $result[$userId][$preferenceKey] : null;
    }

    /**
     * @param array $preferenceList
     * @param int $userId
     * @return array[userId][preferenceName]
     */
    public function getPreferenceValueList( array $preferenceList, $userId )
    {
        $result = $this->getPreferenceValueListByUserIdList( $preferenceList, array( $userId ) );
        return $result[$userId];
    }

    /**
     * @param array $preferenceList
     * @param array $userIdList
     * @return array[userId][preferenceName]
     */
    public function getPreferenceValueListByUserIdList( array $preferenceList, array $userIdList )
    {
        $resultList = array();

        foreach( $userIdList as $userId )
        {
            $resultList[$userId] = array();
        }

        if ( $userIdList === null || !is_array($userIdList) || count($userIdList) === 0 )
        {
            return $resultList;
        }

        if ( $preferenceList === null || !is_array($preferenceList) || count($preferenceList) === 0 )
        {
            return $resultList;
        }

        $usersBol = BOL_UserService::getInstance()->findUserListByIdList($userIdList);
        
        if ( $usersBol === null || count($usersBol) === 0 )
        {
            return $resultList;
        }

        $issetUserList = array();

        foreach( $usersBol as $user )
        {
            $issetUserList[$user->id] = $user->id;
        }

        $cachedPreferenceList = array();
        $notCachedPreferenceList = array();

        foreach( $usersBol as $user )
        {
            if ( !empty( $this->preferenceData[$userId] ) )
            {
                foreach( $preferenceList as $key )
                {
                    if ( isset( $this->preferenceData[$userId][$key] ) && !isset( $notCachedPreferenceList[$key] ) )
                    {
                       $cachedPreferenceList[$key] = $key;
                    }
                    else
                    {
                       $notCachedPreferenceList[$key] = $key;

                       if( isset ( $cachedPreferenceList[$key] ) )
                       {
                           unset( $cachedPreferenceList[$key] );
                       }
                    }
                }
            }
            else
            {
                $notCachedPreferenceList = $preferenceList;
                $cachedPreferenceList = array();
            }
        }

        $preferenceDtoList = array();
        $preferenceData = array();

        if ( count($notCachedPreferenceList) > 0 )
        {
            /* @var $this->preferenceDataDao BOL_PreferenceDataDao */
            $preferenceDtoList = $this->preferenceDao->findPreferenceList($preferenceList);
            $preferenceData = $this->preferenceDataDao->findByPreferenceListForUserList( $notCachedPreferenceList, $issetUserList );
        }

        foreach( $userIdList as $userId )
        {
            foreach( $preferenceDtoList as $dto )
            {
                $key = $dto->key;
                
                if ( isset( $preferenceData[$userId][$key] ) )
                {
                    $dataDto = $preferenceData[$userId][$key];

                    /* @var $dto BOL_PreferenceData */
                    $this->preferenceData[$userId][$key] = json_decode($dataDto->value);
                    $resultList[$userId][$key] = $this->preferenceData[$userId][$key];
                }
                else
                {
                    $this->preferenceData[$userId][$key] = json_decode($dto->defaultValue);
                    $resultList[$userId][$key] = $this->preferenceData[$userId][$key];
                }
            }

            foreach( $cachedPreferenceList as $key )
            {
                $resultList[$userId][$key] = $this->preferenceData[$userId][$key];
            }
        }

        return $resultList;
    }


    /**
     * @param array $preferenceList <$key, value>
     * @param array $userId
     * @return boolean
     */
    public function savePreferenceValue( $preferenceKey, $value, $userId )
    {
        return $this->savePreferenceValues( array( $preferenceKey => $value ), $userId );
    }

    /**
     * @param array $preferenceList <$key, value>
     * @param array $userId
     * @return boolean
     */
    public function savePreferenceValues( array $preferenceList, $userId )
    {
        if ( $preferenceList === null || !is_array($preferenceList) || count($preferenceList) === 0 )
        {
            return false;
        }

        $userDto = BOL_UserService::getInstance()->findUserById($userId);

        if( empty( $userDto ) )
        {
            return false;
        }

        $preferenceKeyList = array_keys($preferenceList);

        $preferenceDtoList = $this->findPreferenceList( $preferenceKeyList );

        $result = $this->preferenceDataDao->findByPreferenceListForUserList($preferenceKeyList, array( $userId ));
        $preferenceDataDtoList = !empty($result[$userId]) ? $result[$userId] : array();

        $preferenceKeyList = array_keys($preferenceDtoList);

        foreach ( $preferenceList as $key => $value )
        {
            if ( in_array($key, $preferenceKeyList) )
            {
                $preferenceDataDto = new BOL_PreferenceData();

                if ( !empty( $preferenceDataDtoList[$key] ) )
                {
                    $preferenceDataDto = $preferenceDataDtoList[$key];
                }

                $preferenceDataDto->key = $key;
                $preferenceDataDto->userId = $userId;
                $preferenceDataDto->value = json_encode($value);

                $this->preferenceDataDao->save($preferenceDataDto);
            }
        }

        if ( isset($this->preferenceData[$userId][$key]) )
        {
            unset($this->preferenceData[$userId][$key]);
        }

        return OW::getDbo()->getAffectedRows();
    }
    
    public function deletePreferenceDataByUserId( $userId )
    {
        return $this->preferenceDataDao->deleteByUserId( $userId );
    }

    public function savePreference( BOL_Preference $preferenceDto )
    {
        $this->preferenceDao->save($preferenceDto);
    }

    public function savePreferenceSection( BOL_PreferenceSection $preferenceSectionDto )
    {
        $this->preferenceSectionDao->save($preferenceSectionDto);
    }
}
