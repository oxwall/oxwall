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
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
final class BOL_UserService
{
    const CREATE_USER_INVALID_USERNAME = -1;
    const CREATE_USER_INVALID_EMAIL = -2;
    const CREATE_USER_INVALID_PASSWORD = -3;
    const CREATE_USER_DUPLICATE_USERNAME = -4;
    const CREATE_USER_DUPLICATE_EMAIL = -5;
    const PERMISSIONS_ANYONE_CAN_JOIN = 1;
    const PERMISSIONS_JOIN_BY_INVITATIONS = 2;
    const PERMISSIONS_MEMBERS_CAN_INVITE = 1;
    const PERMISSIONS_ADMIN_CAN_INVITE = 2;
    const PERMISSIONS_GUESTS_CAN_VIEW = 1;
    const PERMISSIONS_GUESTS_CANT_VIEW = 2;
    const PERMISSIONS_GUESTS_PASSWORD_VIEW = 3;
    const CONFIG_JOIN_DISPLAY_PHOTO_UPLOAD = 'display';
    const CONFIG_JOIN_DISPLAY_AND_SET_REQUIRED_PHOTO_UPLOAD = 'display_and_required';
    const CONFIG_JOIN_NOT_DISPLAY_PHOTO_UPLOAD = 'not_display';
    const USER_CONTEXT_DESKTOP = BOL_UserOnlineDao::CONTEXT_VAL_DESKTOP;
    const USER_CONTEXT_MOBILE = BOL_UserOnlineDao::CONTEXT_VAL_MOBILE;
    const USER_CONTEXT_API = BOL_UserOnlineDao::CONTEXT_VAL_API;
    const USER_CONTEXT_CLI = BOL_UserOnlineDao::CONTEXT_VAL_CLI;
    const PASSWORD_RESET_CODE_EXPIRATION_TIME = 3600;
    const PASSWORD_RESET_CODE_UPDATE_TIME = 600;
    
    const EVENT_USER_QUERY_FILTER = BOL_UserDao::EVENT_QUERY_FILTER;

    /**
     * @var BOL_UserDao
     */
    private $userDao;

    /**
     * @var BOL_LoginCookieDao
     */
    private $loginCookieDao;

    /**
     *
     * @var BOL_UserFeaturedDao
     */
    private $userFeaturedDao;

    /**
     * @var BOL_UserOnlineDao
     */
    private $userOnlineDao;

    /**
     * @var BOL_UserSuspendDao
     */
    private $userSuspendDao;

    /**
     * @var BOL_UserApproveDao
     */
    private $userApproveDao;

    /**
     * @var BOL_RestrictedUsernamesDao
     */
    private $restrictedUsernamesDao;

    /**
     * @var BOL_InviteCodeDao
     */
    private $inviteCodeDao;

    /**
     * @var BOL_UserApproveDao
     */
    private $approveDao;

    /**
     * @var BOL_UserResetPasswordDao
     */
    private $resetPasswordDao;

    /**
     * @var BOL_UserBlockDao
     */
    private $userBlockDao;

    /**
     * @var BOL_AuthTokenDao
     */
    private $tokenDao;

    /**
     * @var BOL_UserService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_UserService
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
     * Constructor.
     */
    private function __construct()
    {
        $this->userDao = BOL_UserDao::getInstance();
        $this->loginCookieDao = BOL_LoginCookieDao::getInstance();
        $this->userFeaturedDao = BOL_UserFeaturedDao::getInstance();
        $this->userOnlineDao = BOL_UserOnlineDao::getInstance();
        $this->userSuspendDao = BOL_UserSuspendDao::getInstance();
        $this->userApproveDao = BOL_UserApproveDao::getInstance();
        $this->restrictedUsernamesDao = BOL_RestrictedUsernamesDao::getInstance();
        $this->inviteCodeDao = BOL_InviteCodeDao::getInstance();
        $this->approveDao = BOL_UserApproveDao::getInstance();
        $this->resetPasswordDao = BOL_UserResetPasswordDao::getInstance();
        $this->userBlockDao = BOL_UserBlockDao::getInstance();
        $this->tokenDao = BOL_AuthTokenDao::getInstance();
    }

    /**
     * @param string $var
     * @param string $password
     * @return BOL_User
     */
    public function findUserForStandardAuth( $var )
    {
        return $this->userDao->findUserByUsernameOrEmail($var);
    }

    /**
     * Finds user by id.
     *
     * @param integer $id
     * @return BOL_User
     */
    public function findUserById( $id )
    {
        return $this->userDao->findById((int) $id);
    }

    /**
     * Finds user by id.
     *
     * @param integer $id
     * @return BOL_User
     */
    public function findByIdWithoutCache( $id )
    {
        return $this->userDao->findByIdWithoutCache((int) $id);
    }

    /**
     * Returns display name for provided user id.
     *
     * @param integer $userId
     * @return string
     */
    public function getDisplayName( $userId )
    {
        $questionName = OW::getConfig()->getValue('base', 'display_name_question');

        $questionValue = BOL_QuestionService::getInstance()->getQuestionData(array($userId), array($questionName));

        $displayName = isset($questionValue[$userId]) ? ( isset($questionValue[$userId][$questionName]) ? $questionValue[$userId][$questionName] : '' ) : OW::getLanguage()->text('base', 'deleted_user');

        return strip_tags($displayName);
    }

    /**
     * Returns display names for provided list of user ids.
     *
     * @param array $userIdList
     * @return array
     */
    public function getDisplayNamesForList( array $userIdList )
    {
        $userIdList = array_unique($userIdList);

        $questionName = OW::getConfig()->getValue('base', 'display_name_question');

        $questionValues = BOL_QuestionService::getInstance()->getQuestionData($userIdList, array($questionName));

        $resultArray = array();

        foreach ( $userIdList as $value )
        {
            $resultArray[$value] = OW::getLanguage()->text('base', 'deleted_user');

            if ( isset($questionValues[$value]) )
            {
                $resultArray[$value] = isset($questionValues[$value][$questionName]) ? htmlspecialchars($questionValues[$value][$questionName]) : '';
            }
        }

        return $resultArray;
    }

    public function getUserName( $userId )
    {
        $user = $this->findUserById($userId);

        return ( $user === null ? null : $user->getUsername() );
    }

    public function getUserNamesForList( array $userIdList )
    {
        $userIdList = array_unique($userIdList);

        $userList = $this->userDao->findByIdList($userIdList);

        $resultArray = array();

        /* @var $user BOL_User */
        foreach ( $userList as $user )
        {
            $resultArray[$user->getId()] = $user->getUsername();
        }

        $returnArray = array();

        foreach ( $userIdList as $id )
        {
            $returnArray[$id] = isset($resultArray[$id]) ? $resultArray[$id] : null; //todo check and replace with lang value
        }

        return $returnArray;
    }

    public function getUserUrl( $id )
    {
        $user = $this->findUserById($id);

        return $this->getUserUrlForUsername(($user === null ? 'deleted-user' : $user->getUsername()));
    }

    public function getUserUrlForUsername( $username )
    {
        if ( empty($username) )
        {
            $username = 'deleted-user';
        }

        return OW::getRouter()->urlForRoute('base_user_profile', array('username' => $username));
    }

    public function getUserUrlsForList( array $userIdList )
    {
        $userIdList = array_unique($userIdList);

        $userList = $this->userDao->findByIdList($userIdList);

        $resultArray = array();

        /* @var $user BOL_User */
        foreach ( $userList as $user )
        {
            $resultArray[$user->getId()] = $this->getUserUrlForUsername($user->getUsername());
        }

        $returnArray = array();

        foreach ( $userIdList as $id )
        {
            $returnArray[$id] = isset($resultArray[$id]) ? $resultArray[$id] : $this->getUserUrlForUsername('deleted-user');
        }

        return $returnArray;
    }

    public function getUserUrlsListForUsernames( array $usernamesList )
    {
        $usernamesList = array_unique($usernamesList);

        $returnArray = array();

        foreach ( $usernamesList as $key => $value )
        {
            $returnArray[$key] = $this->getUserUrlForUsername($value);
        }

        return $returnArray;
    }

    /**
     * @param string $username
     * @return BOL_User
     */
    public function findByUsername( $username )
    {
        return $this->userDao->findByUsername($username);
    }

    public function findRestrictedUsername( $username )
    {
        return $this->restrictedUsernamesDao->findRestrictedUsername($username);
    }

    /**
     *
     * @param string $email
     * @return BOL_User
     */
    public function findByEmail( $email )
    {
        return $this->userDao->findByUseEmail($email);
    }

    /**
     * Creates and saves login cookie.
     *
     * @param integer $userId
     * @return BOL_LoginCookie
     */
    public function saveLoginCookie( $userId )
    {
        $loginCookie = $this->loginCookieDao->findByUserId($userId);

        if ( $loginCookie === null )
        {
            $loginCookie = new BOL_LoginCookie();
        }

        $loginCookie->setUserId($userId);
        $loginCookie->setCookie(hash_hmac('md5', time(), $userId));

        $this->loginCookieDao->save($loginCookie);

        return $loginCookie;
    }

    public function findUserIdByCookie( $cookie )
    {
        $obj = $this->loginCookieDao->findByCookie($cookie);

        return ( $obj === null ? null : $obj->getUserId() );
    }

    /**
     *
     * @param integer $userId
     * @return BOL_LoginCookie
     */
    public function findLoginCookieByUserId( $userId )
    {
        return $this->loginCookieDao->findByUserId($userId);
    }

    /**
     * Find latest user ids list
     *
     * @param integer $offset
     * @param integer $count
     * @return array
     */
    public function findLatestUserIdsList( $offset, $count )
    {
        return $this->userDao->findLatestUserIdsList($offset, $count);
    }

    public function findList( $first, $count, $isAdmin = false )
    {
        return $this->userDao->findList($first, $count, $isAdmin);
    }

    public function findRecentlyActiveList( $first, $count, $isAdmin = false )
    {
        return $this->userDao->findRecentlyActiveList($first, $count, $isAdmin);
    }

    public function getRecentlyActiveOrderedIdList( $userIdList )
    {
        return $this->userDao->getRecentlyActiveOrderedIdList($userIdList);
    }

    public function findOnlineList( $first, $count )
    {
        $onlineList = $this->userDao->findOnlineList($first, $count);
        $list = array();

        $userIdList = array();

        foreach ( $onlineList as $id => $user )
        {
            $userIdList[] = $user->id;
        }
        // Check privacy permissions
        $eventParams = array(
            'action' => 'base_view_my_presence_on_site',
            'ownerIdList' => $userIdList,
            'viewerId' => OW::getUser()->getId()
        );

        $permission = OW::getEventManager()->getInstance()->call('privacy_check_permission_for_user_list', $eventParams);

        foreach ( $onlineList as $user )
        {
            $show = true;
            if ( isset($permission[$user->id]['blocked']) && $permission[$user->id]['blocked'] == true )
            {
                $show = false;
                continue;
            }

            if ( $show )
            {
                $list[] = $user;
            }
        }
        return $list;
    }

    public function findOnlineUserById( $userId )
    {
        // Check privacy permissions
        $eventParams = array(
            'action' => 'base_view_my_presence_on_site',
            'ownerId' => $userId,
            'viewerId' => OW::getUser()->getId()
        );
        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch ( RedirectException $e )
        {
            return null;
        }
        return $this->userOnlineDao->findByUserId($userId);
    }

    public function countOnline()
    {
        return (int) $this->userDao->countOnline();
    }

    public function count( $isAdmin = false )
    {
        return (int) $this->userDao->count($isAdmin);
    }

    public function findSuspendedList( $first, $count )
    {
        return $this->userDao->findSuspendedList($first, $count);
    }

    public function countSuspended()
    {
        return $this->userDao->countSuspended();
    }

    public function findUnverifiedList( $first, $count )
    {
        return $this->userDao->findUnverifiedList($first, $count);
    }

    public function countUnverified()
    {
        return $this->userDao->countUnverified();
    }

    public function findUnapprovedList( $first, $count )
    {
        return $this->userDao->findUnapprovedList($first, $count);
    }

    public function countUnapproved()
    {
        return $this->userDao->countUnapproved();
    }

    public function saveOrUpdate( BOL_User $user )
    {
        $event = new OW_Event('base.before_save_user', array('dto' => $user));
        OW::getEventManager()->trigger($event);

        $this->userDao->save($user);

        if ( !empty($this->cachedUsers[$user->getId()]) )
        {
            unset($this->cachedUsers[$user->getId()]);
        }
    }

    public function isExistUserName( $value )
    {
        if ( $value === null )
        {
            return false;
        }

        $user = $this->findByUsername(trim($value));

        if ( isset($user) )
        {
            return true;
        }

        return false;
    }

    public function isRestrictedUsername( $value )
    {
        if ( $value === null )
        {
            return false;
        }

        $user = $this->findRestrictedUsername(trim($value));

        if ( isset($user) )
        {
            return true;
        }

        return false;
    }

    public function isExistEmail( $value )
    {
        if ( $value === null )
        {
            return false;
        }

        $email = $this->findByEmail(trim($value));

        if ( isset($email) )
        {
            return true;
        }

        return false;
    }

    public function isValidPassword( $userId, $value )
    {
        $user = $this->findUserById($userId);

        if ( $value === null || $user === null )
        {
            return false;
        }

        $password = $this->hashPassword($value);

        if ( $user->password === $password )
        {
            return true;
        }

        return false;
    }

    public function markAsFeatured( $userId )
    {
        $dto = new BOL_UserFeatured();
        $dto->setUserId($userId);

        $result = $this->userFeaturedDao->save($dto);

        $event = new OW_Event(OW_EventManager::ON_USER_MARK_FEATURED, array('userId' => $userId));
        OW::getEventManager()->trigger($event);

        return $result;
    }

    public function cancelFeatured( $userId )
    {
        $this->userFeaturedDao->deleteByUserId($userId);

        $event = new OW_Event(OW_EventManager::ON_USER_UNMARK_FEATURED, array('userId' => $userId));
        OW::getEventManager()->trigger($event);
    }

    public function isUserFeatured( $id )
    {
        $dto = $this->userFeaturedDao->findByUserId($id);

        return !empty($dto);
    }

    public function findBlockedUserList( $userId, $first, $count )
    {
        $list =  $this->userBlockDao->findBlockedUserList($userId, $first, $count);
        $processedList = [];

        foreach($list as $item)
        {
            $processedList[] = $item->getBlockedUserId();
        }

        return $processedList;
    }

    public function countBlockedUsers($userId)
    {
        return  $this->userBlockDao->countBlockedUsers($userId);
    }

    public function isBlocked( $id, $byUserId = null )
    {
        if ( $byUserId === null )
        {
            $byUserId = OW::getUser()->getId();
        }
        $dto = $this->userBlockDao->findBlockedUser($byUserId, $id);

        return !empty($dto);
    }

    public function findBlockedListByUserIdList( $userId, array $userIdList )
    {
        if ( !$userId )
        {
            return null;
        }

        $list = $this->userBlockDao->findBlockedList($userId, $userIdList);

        $users = array();
        if ( $list )
        {
            foreach ( $list as $user )
            {
                $users[$user->blockedUserId] = $user;
            }
        }

        $blockList = array();
        foreach ( $userIdList as $blockedUserId )
        {
            $blockList[$blockedUserId] = array_key_exists($blockedUserId, $users);
        }

        return $blockList;
    }

    public function findFeaturedList( $first, $count )
    {
        return $this->userDao->findFeaturedList($first, $count);
    }

    public function countFeatured()
    {
        return $this->userDao->countFeatured();
    }

    public function onLogin( $userId, $context )
    {
        $this->updateActivityStamp($userId, $context);
    }

    public function onLogout( $userId )
    {
        if ( (int) $userId < 1 )
        {
            return;
        }

        $user = $this->userDao->findById($userId);
        $userOnline = $this->userOnlineDao->findByUserId($userId);

        if ( $user === null || $userOnline === null )
        {
            return;
        }

        $user->setActivityStamp($userOnline->getActivityStamp());

        $this->userDao->save($user);

        $this->userOnlineDao->deleteById($userOnline->getId());
    }

    public function updateActivityStamp( $userId, $context )
    {
        if ( !$userId )
        {
            return;
        }

        $user = $this->userDao->findById((int) $userId);

        if ( $user === null )
        {
            return;
        }

        $activityStamp = time();
        $userOnline = $this->userOnlineDao->findByUserId($userId);

        if ( $userOnline === null )
        {
            $userOnline = new BOL_UserOnline();
            $userOnline->setUserId($userId);
        }

        $userOnline->setActivityStamp($activityStamp);
        $userOnline->setContext($context);
        $this->userOnlineDao->saveDelayed($userOnline);

        /* @var $user BOL_User */
        $user->setActivityStamp($activityStamp);
        $this->userDao->saveDelayed($user);
    }

    public function findUserListByIdList( array $idList )
    {
        $idList = array_unique($idList);

        return $this->userDao->findByIdList($idList);
    }

    public function findUserIdListByIdList( array $idList )
    {
        $idList = array_unique($idList);

        return $this->userDao->findIdListByIdList($idList);
    }

    public function findOnlineStatusForUserList( $idList )
    {
        $onlineUsers = $this->userOnlineDao->findOnlineUserIdListFromIdList($idList);

        $onlineUsersArr = array();

        foreach ( $onlineUsers as $item )
        {
            $onlineUsersArr[$item['userId']] = $item['context'];
        }

        $resultArray = array();

        foreach ( $idList as $userId )
        {
            $resultArray[$userId] = array_key_exists($userId, $onlineUsersArr) ? $onlineUsersArr[$userId] : false;
        }

        return $resultArray;
    }

    public function deleteUser( $userId, $deleteContent = true )
    {
        $event = new OW_Event(OW_EventManager::ON_USER_UNREGISTER, array('userId' => $userId, 'deleteContent' => $deleteContent));
        OW::getEventManager()->trigger($event);

        BOL_QuestionService::getInstance()->deleteQuestionDataByUserId((int) $userId);
        BOL_AvatarService::getInstance()->deleteUserAvatar($userId);
        
        $this->userSuspendDao->deleteById($userId);
        $this->userBlockDao->deleteByUserId($userId);
        
        $this->userDao->deleteById($userId);

        return true;
    }

    public function addRestrictedUsername( $username )
    {
        $this->restrictedUsernamesDao->addRestrictedUsername($username);
    }

    public function getRestrictedUsername( $username )
    {
        return $this->restrictedUsernamesDao->getRestrictedUsername($username);
    }

    public function getRestrictedUsernameList()
    {
        return $this->restrictedUsernamesDao->getRestrictedUsernameList();
    }

    public function replaceAccountTypeForUsers( $oldType, $newType )
    {
        $this->userDao->replaceAccountTypeForUsers($oldType, $newType);
    }

    public function findMassMailingUsers( $start, $count, $ignoreUnsubscribe = false, $roles = array() )
    {
        return $this->userDao->findMassMailingUsers($start, $count, $ignoreUnsubscribe, $roles);
    }

    public function findMassMailingUserCount( $ignoreUnsubscribe = false, $roles = array() )
    {
        return $this->userDao->findMassMailingUserCount($ignoreUnsubscribe, $roles);
    }

    public function updateEmail( $userId, $email )
    {
        if ( UTIL_Validator::isEmailValid($email) )
        {
            $this->userDao->updateEmail((int) $userId, $email);
        }
        else
        {
            throw new InvalidArgumentException('Invalid email!');
        }
    }

    public function updatePassword( $userId, $password )
    {
        if ( !empty($password) )
        {
            $this->userDao->updatePassword((int) $userId, $this->hashPassword($password));
        }
        else
        {
            throw new InvalidArgumentException('Invalid password!');
        }
    }

    public function suspend( $userId, $message )
    {
        if ( $this->isSuspended($userId) )
        {
            return;
        }

        $dto = new BOL_UserSuspend();

        $dto->setUserId($userId)
            ->setTimestamp(time())->setMessage($message);

        $this->userSuspendDao->save($dto);

        $event = new OW_Event(OW_EventManager::ON_USER_SUSPEND, array('userId' => $userId, 'message' => $message));
        OW::getEventManager()->trigger($event);
    }

    public function unsuspend( $userId )
    {
        if ( !$this->isSuspended($userId) )
        {
            return;
        }

        $dto = $this->userSuspendDao->findByUserId($userId);

        $this->userSuspendDao->delete($dto);

        $event = new OW_Event(OW_EventManager::ON_USER_UNSUSPEND, array('userId' => $userId));
        OW::getEventManager()->trigger($event);
    }

    public function block( $userId )
    {
        if ( $this->isBlocked($userId) )
        {
            return;
        }

        $dto = new BOL_UserBlock();

        $dto->setUserId(OW::getUser()->getId());
        $dto->setBlockedUserId($userId);

        $this->userBlockDao->save($dto);

        $event = new OW_Event(OW_EventManager::ON_USER_BLOCK, array('userId' => OW::getUser()->getId(), 'blockedUserId' => $userId));
        OW::getEventManager()->trigger($event);
    }

    public function unblock( $userId )
    {
        if ( !$this->isBlocked($userId) )
        {
            return;
        }

        $dto = $this->userBlockDao->findBlockedUser(OW::getUser()->getId(), $userId);

        $this->userBlockDao->delete($dto);

        $event = new OW_Event(OW_EventManager::ON_USER_UNBLOCK, array('userId' => OW::getUser()->getId(), 'blockedUserId' => $userId));
        OW::getEventManager()->trigger($event);
    }

    /**
     * Get suspend reason
     *
     * @param integer $userId
     * @return string
     */
    public function getSuspendReason( $userId )
    {
        return $this->userSuspendDao->getSuspendReason($userId);
    }

    public function isSuspended( $userId )
    {
        return $this->userSuspendDao->findByUserId($userId) !== null;
    }

    public function hashPassword( $password )
    {
        return hash('sha256', OW_PASSWORD_SALT . $password);
    }

    public function findListByRoleId( $roleId, $first, $count )
    {
        return $this->userDao->findListByRoleId($roleId, $first, $count);
    }

    public function countByRoleId( $roleId )
    {
        return $this->userDao->countByRoleId($roleId);
    }

    public function deleteExpiredOnlineUsers()
    {
        $timestamp = OW::getConfig()->configExists('base', 'user_expired_time') ? time() - OW::getConfig()->getValue('base', 'user_expired_time') * 60 : time() - 30 * 60;
        $this->userOnlineDao->deleteExpired($timestamp);
    }

    public function findListByEmailList( $emailList )
    {
        return $this->userDao->findListByEmailList($emailList);
    }

    public function createUser( $username, $password, $email, $accountType = null, $emailVerify = false, $ip = null )
    {
        if ( !UTIL_Validator::isEmailValid($email) )
        {
            throw new InvalidArgumentException('Invalid email!', self::CREATE_USER_INVALID_EMAIL);
        }

        if ( !UTIL_Validator::isUserNameValid($username) )
        {
            throw new InvalidArgumentException('Invalid username!', self::CREATE_USER_INVALID_USERNAME);
        }

        if ( !isset($password) || strlen($password) === 0 )
        {
            throw new InvalidArgumentException('Invalid password!', self::CREATE_USER_INVALID_PASSWORD);
        }

        if ( $this->isExistUserName($username) )
        {
            throw new LogicException('Duplicate username!', self::CREATE_USER_DUPLICATE_USERNAME);
        }

        if ( $this->isExistEmail($email) )
        {
            throw new LogicException('Duplicate email!', self::CREATE_USER_DUPLICATE_EMAIL);
        }

        $userAccountType = $accountType;

        if ( $userAccountType === null )
        {
            $userAccountType = '';
            $accountTypes = BOL_QuestionService::getInstance()->findAllAccountTypes();

            if ( count($accountTypes) === 1 )
            {
                $userAccountType = $accountTypes[0]->name;
            }
        }

        $user = new BOL_User();

        $user->username = trim($username);
        $user->password = BOL_UserService::getInstance()->hashPassword($password);
        $user->email = trim($email);
        $user->joinStamp = time();
        $user->activityStamp = time();
        $user->accountType = $userAccountType;
        $user->joinIp = $ip ? $ip : ip2long(OW::getRequest()->getRemoteAddress());

        if ( $emailVerify === true )
        {
            $user->emailVerify = true;
        }

        $this->saveOrUpdate($user);

        BOL_AuthorizationService::getInstance()->assignDefaultRoleToUser($user->id);

        return $user;
    }

    /**
     *
     * @param string $code
     * @return BOL_InviteCode
     */
    public function findInvitationInfo( $code )
    {
        return $this->inviteCodeDao->findByCode($code);
    }
    
    public function deleteInvitationCode( $code )
    {
        return $this->inviteCodeDao->deleteByCode($code);
    }

    public function sendAdminInvitation( $email )
    {
        $inviteCodeDto = new BOL_InviteCode();
        $inviteCodeDto->setCode(UTIL_String::getRandomString(20));
        $inviteCodeDto->setUserId(0);
        $inviteCodeDto->setExpiration_stamp(time() + 3600 * 24 * 30);
        $this->inviteCodeDao->save($inviteCodeDto);

        $inviteUrl = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('base_join'), array('code' => $inviteCodeDto->getCode()));

        $mail = OW::getMailer()->createMail();
        $mail->addRecipientEmail($email);
        $mail->setSubject(OW::getLanguage()->text('base', 'mail_template_admin_invite_user_subject'));
        $mail->setHtmlContent(OW::getLanguage()->text('base', 'mail_template_admin_invite_user_content_html', array('url' => $inviteUrl)));
        $mail->setTextContent(OW::getLanguage()->text('base', 'mail_template_admin_invite_user_content_text', array('url' => $inviteUrl)));

        OW::getMailer()->addToQueue($mail);
    }

    public function saveUserInvitation( $userId, $code )
    {
        $dto = new BOL_InviteCode();
        $dto->setCode($code);
        $dto->setUserId($userId);
        $dto->setExpiration_stamp(time() + 3600 * 24 * 30);
        $this->inviteCodeDao->save($dto);
    }

    /**
     *
     * @param int $userId
     */
    public function disapprove( $userId )
    {
        if ( empty($userId) )
        {
            throw new InvalidArgumentException('invalid $userId param');
        }

        $dto = $this->approveDao->findByUserId($userId);
        if ( !empty($dto) )
        {
            return;
        }

        $dto = new BOL_UserDisapprove();
        $dto->setUserId($userId);

        $this->approveDao->save($dto);

        $event = new OW_Event(OW_EventManager::ON_USER_DISAPPROVE, array('userId' => $userId));
        OW::getEventManager()->trigger($event);
    }

    /**
     *
     * @param int $userId
     */
    public function approve( $userId )
    {
        if ( empty($userId) )
        {
            throw new InvalidArgumentException('invalid $userId param');
        }

        $dto = $this->approveDao->findByUserId($userId);

        if ( empty($dto) )
        {
            throw new Exception('User already approved');
        }

        $this->approveDao->delete($dto);

        $event = new OW_Event(OW_EventManager::ON_USER_APPROVE, array('userId' => $userId));
        OW::getEventManager()->trigger($event);
    }

    public function sendApprovalNotification( $userId )
    {
        if ( !$userId )
        {
            return false;
        }

        $user = $this->findUserById($userId);
        if ( !$user )
        {
            return false;
        }

        $language = OW::getLanguage();

        $mail = OW::getMailer()->createMail();
        $vars = array('user_name' => $this->getDisplayName($userId));
        $mail->addRecipientEmail($user->getEmail());
        $mail->setSubject($language->text('base', 'user_approved_mail_subject', $vars));
        $mail->setTextContent($language->text('base', 'user_approved_mail_txt', $vars));
        $mail->setHtmlContent($language->text('base', 'user_approved_mail_html', $vars));
        OW::getMailer()->send($mail);

        return true;
    }

    public function isApproved( $userId = null )
    {
        if ( $userId == null )
        {
            $userId = OW::getUser()->getId();
        }

        return null === $this->approveDao->findByUserId($userId);
    }

    public function findDisapprovedList( $first, $count )
    {
        return $this->userDao->findDisapprovedList($first, $count);
    }

    public function countDisapproved()
    {
        return $this->userDao->countDisapproved();
    }

    public function deleteDisaproveByUserId( $userId )
    {
        if ( empty($userId) )
        {
            return;
        }

        $this->approveDao->deleteByUserId($userId);
    }

    public function findSupsendStatusForUserList( $idList )
    {
        $onlineUsers = $this->userSuspendDao->findSupsendStatusForUserList($idList);

        $resultArray = array();

        foreach ( $idList as $userId )
        {
            $resultArray[$userId] = in_array($userId, $onlineUsers) ? true : false;
        }

        return $resultArray;
    }

    /**
     *
     * @param int $userId
     * return array<BOL_User>
     *
     */
    public function findUserListByQuestionValues( $questionValues, $first, $count, $isAdmin = false )
    {
        return $this->userDao->findUserListByQuestionValues($questionValues, $first, $count, $isAdmin);
    }

    public function countUsersByQuestionValues( $questionValues, $isAdmin = false )
    {
        return $this->userDao->countUsersByQuestionValues($questionValues, $isAdmin);
    }

    public function findUnverifiedStatusForUserList( $idList )
    {
        $unverifiedUsers = $this->userDao->findUnverifyStatusForUserList($idList);

        $resultArray = array();

        foreach ( $idList as $userId )
        {
            $resultArray[$userId] = in_array($userId, $unverifiedUsers) ? true : false;
        }

        return $resultArray;
    }

    public function findUserIdListByQuestionValues( $questionValues, $first, $count, $isAdmin = false, $aditionalParams = array() )
    {
        $first = (int) $first;
        $count = (int) $count;

        $data = array(
            'data' => $questionValues,
            'first' => $first,
            'count' => $count,
            'isAdmin' => $isAdmin,
            'aditionalParams' => $aditionalParams
        );

        $event = new OW_Event("base.question.before_user_search", $data, $data);

        OW_EventManager::getInstance()->trigger($event);

        $data = $event->getData();

        return $this->userDao->findUserIdListByQuestionValues($data['data'], $data['first'], $data['count'], $data['isAdmin'], $data['aditionalParams']);
    }

    public function findSearchResultList( $listId, $first, $count )
    {
        return $this->userDao->findSearchResultList($listId, $first, $count);
    }

    public function findUnapprovedStatusForUserList( $idList )
    {
        $unapprovedUsers = $this->userApproveDao->findUnapproveStatusForUserList($idList);
        $resultArray = array();

        foreach ( $idList as $userId )
        {
            $resultArray[$userId] = in_array($userId, $unapprovedUsers) ? true : false;
        }

        return $resultArray;
    }

    /**
     * @deprecated
     */
    public function findListByBirthdayPeriod( $start, $end, $first, $count )
    {
        return array();
    }

    /**
     * @deprecated
     */
    public function countByBirthdayPeriod( $start, $end )
    {
        return 0;
    }

    /**
     * @deprecated
     */
    public function findListByBirthdayPeriodAndUserIdList( $start, $end, $first, $count, $idList )
    {
        return array();
    }

    /**
     * @deprecated
     */
    public function countByBirthdayPeriodAndUserIdList( $start, $end, $idList )
    {
        return 0;
    }

    /**
     * @param integer $userId
     * @return BOL_UserResetPassword
     */
    public function findResetPasswordByUserId( $userId )
    {
        return $this->resetPasswordDao->findByUserId($userId);
    }

    /**
     * @param integer $userId
     * @return BOL_UserResetPassword
     */
    public function getNewResetPassword( $userId )
    {
        $resetPassword = new BOL_UserResetPassword();
        $resetPassword->setUserId($userId);
        $resetPassword->setExpirationTimeStamp(( time() + self::PASSWORD_RESET_CODE_EXPIRATION_TIME));
        $resetPassword->setUpdateTimeStamp(time() + self::PASSWORD_RESET_CODE_UPDATE_TIME);
        $resetPassword->setCode(md5(UTIL_String::getRandomString(8, 5)));

        $this->resetPasswordDao->save($resetPassword);

        return $resetPassword;
    }

    /**
     * @param string $code
     * @return BOL_UserResetPassword
     */
    public function findResetPasswordByCode( $code )
    {
        return $this->resetPasswordDao->findByCode($code);
    }

    public function deleteExpiredResetPasswordCodes()
    {
        $this->resetPasswordDao->deleteExpiredEntities();
    }

    public function deleteResetCode( $resetCodeId )
    {
        $this->resetPasswordDao->deleteById($resetCodeId);
    }

    public function sendWellcomeLetter( BOL_User $user )
    {
        if ( $user === null )
        {
            return;
        }

        if ( OW::getConfig()->getValue('base', 'confirm_email') && $user->emailVerify != true )
        {
            return;
        }

        $vars = array(
            'username' => $this->getDisplayName($user->id),
        );

        $language = OW::getLanguage();

        $subject = !($language->text('base', 'welcome_letter_subject', $vars)) ? 'base+welcome_letter_subject' : $language->text('base', 'welcome_letter_subject', $vars);
        $template_html = !($language->text('base', 'welcome_letter_template_html', $vars)) ? 'base+welcome_letter_template_html' : $language->text('base', 'welcome_letter_template_html', $vars);
        $template_text = !($language->text('base', 'welcome_letter_template_text', $vars)) ? 'base+welcome_letter_template_text' : $language->text('base', 'welcome_letter_template_text', $vars);

        $mail = OW::getMailer()->createMail();
        $mail->addRecipientEmail($user->email);
        $mail->setSubject($subject);
        $mail->setHtmlContent($template_html);
        $mail->setTextContent($template_text);

        try
        {
            OW::getMailer()->send($mail);
        }
        catch ( phpmailerException $e )
        {
            $user->emailVerify = false;
            $this->saveOrUpdate($user);
        }


        BOL_PreferenceService::getInstance()->savePreferenceValue('send_wellcome_letter', 0, $user->id);
    }

    public function cronSendWellcomeLetter()
    {
        $preferenceValues = array('send_wellcome_letter' => 1);

        $userIdList = $this->userDao->findUserIdListByPreferenceValues($preferenceValues);

        if ( empty($userIdList) )
        {
            return;
        }

        $users = $this->findUserListByIdList($userIdList);

        foreach ( $users as $user )
        {
            $this->sendWellcomeLetter($user);
        }
    }

    /**
     * @param string $formName
     * @param string $submitDecorator
     * @return Form
     */
    public function getSignInForm( $formName = 'sign-in', $submitDecorator = 'button' )
    {
        $form = new Form($formName);

        $username = new TextField('identity');
        $username->setRequired(true);
        $username->setHasInvitation(true);
        $username->setInvitation(OW::getLanguage()->text('base', 'component_sign_in_login_invitation'));
        $form->addElement($username);

        $password = new PasswordField('password');
        $password->setHasInvitation(true);
        $password->setInvitation(OW::getLanguage()->text('base', 'component_sign_in_password_invitation'));
        $password->setRequired(true);
        $form->addElement($password);

        $remeberMe = new CheckboxField('remember');
        $remeberMe->setLabel(OW::getLanguage()->text('base', 'sign_in_remember_me_label'));
        $remeberMe->setValue(true);
        $form->addElement($remeberMe);

        $submit = new Submit('submit', $submitDecorator);
        $submit->setValue(OW::getLanguage()->text('base', 'sign_in_submit_label'));
        $form->addElement($submit);

        return $form;
    }

    /**
     *
     * @param string $identity
     * @param string $password
     * @param bool $rememberMe
     * @return OW_AuthResult
     */
    public function processSignIn( $identity, $password, $rememberMe = false )
    {
        if ( empty($identity) || empty($password) )
        {
            throw new LogicException("Invalid auth attrs");
        }

        $result = OW::getUser()->authenticate(new BASE_CLASS_StandardAuth($identity, $password));

        if ( $result->isValid() )
        {
            if ( $rememberMe )
            {
                $loginCookie = $this->saveLoginCookie(OW::getUser()->getId());

                setcookie('ow_login', $loginCookie->getCookie(), (time() + 86400 * 7), '/', null, null, true);
            }
        }

        return $result;
    }

    public function getResetForm( $formName = 'forgot-password' )
    {
        $language = OW::getLanguage();
        $form = new Form($formName);

        $email = new TextField('email');
        $email->setRequired(true);
        $email->addValidator(new EmailValidator());
        $email->setHasInvitation(true);
        $email->setInvitation($language->text('base', 'forgot_password_email_invitation_message'));
        $form->addElement($email);

        $submit = new Submit('submit');
        $submit->setValue($language->text('base', 'forgot_password_submit_label'));
        $form->addElement($submit);

        return $form;
    }

    public function processResetForm( $data )
    {
        $language = OW::getLanguage();
        $email = trim($data['email']);
        $user = $this->findByEmail($email);

        if ( $user === null )
        {
            throw new LogicException($language->text('base', 'forgot_password_no_user_error_message'));
        }

        $resetPassword = $this->findResetPasswordByUserId($user->getId());

        if ( $resetPassword !== null )
        {
            if ( $resetPassword->getUpdateTimeStamp() > time() )
            {
                throw new LogicException($language->text('base', 'forgot_password_request_exists_error_message'));
            }
            else
            {
                $resetPassword->setUpdateTimeStamp($resetPassword->getUpdateTimeStamp() + self::PASSWORD_RESET_CODE_UPDATE_TIME);
                $this->resetPasswordDao->save($resetPassword);
            }
        }
        else
        {
            $resetPassword = $this->getNewResetPassword($user->getId());
        }


        $vars = array('code' => $resetPassword->getCode(), 'username' => $user->getUsername(), 'requestUrl' => OW::getRouter()->urlForRoute('base.reset_user_password_request'),
            'resetUrl' => OW::getRouter()->urlForRoute('base.reset_user_password', array('code' => $resetPassword->getCode())));

        $mail = OW::getMailer()->createMail();
        $mail->addRecipientEmail($email);
        $mail->setSubject($language->text('base', 'reset_password_mail_template_subject'));
        $mail->setTextContent($language->text('base', 'reset_password_mail_template_content_txt', $vars));
        $mail->setHtmlContent($language->text('base', 'reset_password_mail_template_content_html', $vars));
        OW::getMailer()->send($mail);
    }

    public function getResetPasswordRequestFrom( $formName = 'reset-password-request' )
    {
        $language = OW::getLanguage();

        $form = new Form($formName);
        $code = new TextField('code');
        $code->setLabel($language->text('base', 'reset_password_request_code_field_label'));
        $code->setRequired();
        $form->addElement($code);
        $submit = new Submit('submit');
        $submit->setValue($language->text('base', 'reset_password_request_submit_label'));
        $form->addElement($submit);

        return $form;
    }

    public function getResetPasswordForm( $formName = 'reset-password' )
    {
        $language = OW::getLanguage();

        $form = new Form($formName);
        $pass = new PasswordField('password');
        $pass->setRequired();
        $pass->setLabel($language->text('base', 'reset_password_field_label'));
        $form->addElement($pass);
        $repeatPass = new PasswordField('repeatPassword');
        $repeatPass->setRequired();
        $repeatPass->setLabel($language->text('base', 'reset_password_repeat_field_label'));
        $form->addElement($repeatPass);
        $submit = new Submit('submit');
        $submit->setValue($language->text('base', 'reset_password_submit_label'));
        $form->addElement($submit);

        return $form;
    }

    public function processResetPasswordForm( $data, BOL_User $user, $resetCode )
    {
        $language = OW::getLanguage();

        if ( trim($data['password']) !== trim($data['repeatPassword']) )
        {
            throw new LogicException($language->text('base', 'reset_password_not_equal_error_message'));
        }

        if ( strlen(trim($data['password'])) > UTIL_Validator::PASSWORD_MAX_LENGTH || strlen(trim($data['password'])) < UTIL_Validator::PASSWORD_MIN_LENGTH )
        {
            throw new LogicException(OW::getLanguage()->text('base', 'reset_password_length_error_message', array('min' => UTIL_Validator::PASSWORD_MIN_LENGTH, 'max' => UTIL_Validator::PASSWORD_MAX_LENGTH)));
        }

        $user->setPassword($this->hashPassword($data['password']));
        $this->saveOrUpdate($user);
        $this->deleteResetCode($resetCode->getId());
    }

    public function getDataForUsersList( $listKey, $first, $count )
    {
        switch ( $listKey )
        {
            case 'latest':
                return array(
                    $this->findList($first, $count),
                    $this->count()
                );

            case 'online':
                return array(
                    $this->findOnlineList($first, $count),
                    $this->countOnline()
                );

            case 'featured':

                return array(
                    $this->findFeaturedList($first, $count),
                    $this->countFeatured()
                );

            case 'waiting-for-approval':
                return array(
                    $this->findDisapprovedList($first, $count),
                    $this->countDisapproved()
                );

            default:
                $event = new BASE_CLASS_EventCollector('base.add_user_list');
                OW::getEventManager()->trigger($event);
                $data = $event->getData();

                foreach ( $data as $value )
                {
                    if ( $value['key'] == $listKey )
                    {
                        return call_user_func_array($value['dataProvider'], array($first, $count));
                    }
                }

                return array(array(), 0);
        }
    }

    /**
     * @param string $token
     * @return int
     */
    public function findUserIdByAuthToken( $token )
    {
        return (int) $this->tokenDao->findUserIdByToken($token);
    }

    /**
     * @param integer $userId
     * @return string
     */
    public function addTokenForUser( $userId )
    {
        $token = new BOL_AuthToken();
        $token->setUserId($userId);
        $token->setTimeStamp(time());
        $token->setToken(uniqid($userId));

        $this->tokenDao->deleteByUserId($userId);
        $this->tokenDao->save($token);

        return $token->getToken();
    }

    /**
     * @param integer $userId
     */
    public function deleteTokenForUser( $userId )
    {
        $this->tokenDao->deleteByUserId($userId);
    }

    public function getUserViewQuestions( $userId, $adminMode = false, $questionNames = array(), $sectionNames = null )
    {
        $questionService = BOL_QuestionService::getInstance();
        $user = BOL_UserService::getInstance()->findUserById($userId);
        $accountType = $user->accountType;
        $language = OW::getLanguage();


        if ( empty($questionNames) )
        {
            if ( $adminMode )
            {
                $questions = $questionService->findAllQuestionsForAccountType($accountType);
            }
            else
            {
                $questions = $questionService->findViewQuestionsForAccountType($accountType);
            }
        }
        else
        {
            $questions = $questionService->findQuestionByNameList($questionNames);
            foreach ( $questions as &$q )
            {
                $q = (array) $q;
            }
        }

        $section = null;
        $questionArray = array();
        $questionNameList = array();

        foreach ( $questions as $sort => $question )
        {
            if ( !empty($sectionNames) && !in_array($question['sectionName'], $sectionNames) )
            {
                continue;
            }

            if ( $section !== $question['sectionName'] )
            {
                $section = $question['sectionName'];
            }

            $questions[$sort]['hidden'] = false;

            if ( !$questions[$sort]['onView'] )
            {
                $questions[$sort]['hidden'] = true;
            }

            $questionArray[$section][$sort] = $questions[$sort];
            $questionNameList[] = $questions[$sort]['name'];
        }

        $questionData = $questionService->getQuestionData(array($userId), $questionNameList);
        $questionLabelList = array();

        // add form fields
        foreach ( $questionArray as $sectionKey => $section )
        {
            foreach ( $section as $questionKey => $question )
            {
                $event = new OW_Event('base.questions_field_get_label', array(
                    'presentation' => $question['presentation'],
                    'fieldName' => $question['name'],
                    'configs' => $question['custom'],
                    'type' => 'view'
                ));

                OW::getEventManager()->trigger($event);

                $label = $event->getData();

                $questionLabelList[$question['name']] = !empty($label) ? $label : BOL_QuestionService::getInstance()->getQuestionLang($question['name']);

                $event = new OW_Event('base.questions_field_get_value', array(
                    'presentation' => $question['presentation'],
                    'fieldName' => $question['name'],
                    'value' => empty($questionData[$userId][$question['name']]) ? null : $questionData[$userId][$question['name']],
                    'questionInfo' => $question,
                    'userId' => $userId
                ));

                OW::getEventManager()->trigger($event);

                $eventValue = $event->getData();

                if ( !empty($eventValue) )
                {
                    $questionData[$userId][$question['name']] = $eventValue;

                    continue;
                }


                if ( !empty($questionData[$userId][$question['name']]) )
                {
                    switch ( $question['presentation'] )
                    {
                        case BOL_QuestionService::QUESTION_PRESENTATION_CHECKBOX:

                            if ( (int) $questionData[$userId][$question['name']] === 1 )
                            {
                                $questionData[$userId][$question['name']] = OW::getLanguage()->text('base', 'yes');
                            }
                            else
                            {
                                unset($questionArray[$sectionKey][$questionKey]);
                            }

                            break;

                        case BOL_QuestionService::QUESTION_PRESENTATION_DATE:

                            $format = OW::getConfig()->getValue('base', 'date_field_format');

                            $value = 0;

                            switch ( $question['type'] )
                            {
                                case BOL_QuestionService::QUESTION_VALUE_TYPE_DATETIME:

                                    $date = UTIL_DateTime::parseDate($questionData[$userId][$question['name']], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);

                                    if ( isset($date) )
                                    {
                                        $format = OW::getConfig()->getValue('base', 'date_field_format');
                                        $value = mktime(0, 0, 0, $date['month'], $date['day'], $date['year']);
                                    }

                                    break;

                                case BOL_QuestionService::QUESTION_VALUE_TYPE_SELECT:

                                    $value = (int) $questionData[$userId][$question['name']];

                                    break;
                            }

                            if ( $format === 'dmy' )
                            {
                                $questionData[$userId][$question['name']] = date("d/m/Y", $value);
                            }
                            else
                            {
                                $questionData[$userId][$question['name']] = date("m/d/Y", $value);
                            }

                            break;

                        case BOL_QuestionService::QUESTION_PRESENTATION_BIRTHDATE:

                            $date = UTIL_DateTime::parseDate($questionData[$userId][$question['name']], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
                            $questionData[$userId][$question['name']] = UTIL_DateTime::formatBirthdate($date['year'], $date['month'], $date['day']);

                            break;

                        case BOL_QuestionService::QUESTION_PRESENTATION_AGE:

                            $date = UTIL_DateTime::parseDate($questionData[$userId][$question['name']], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
                            $questionData[$userId][$question['name']] = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']) . " " . $language->text('base', 'questions_age_year_old');

                            break;

                        case BOL_QuestionService::QUESTION_PRESENTATION_RANGE:

                            $range = explode('-', $questionData[$userId][$question['name']]);
                            $questionData[$userId][$question['name']] = $language->text('base', 'form_element_from') . " " . $range[0] . " " . $language->text('base', 'form_element_to') . " " . $range[1];

                            break;
                        case BOL_QuestionService::QUESTION_PRESENTATION_FSELECT:
                            $questionValue = (int) $questionData[$userId][$question['name']];
                            $parentName = $question['name'];
                            if ( !empty($question['parent']) )
                            {
                                $parent = BOL_QuestionService::getInstance()->findQuestionByName($question['parent']);

                                if ( !empty($parent) )
                                {
                                    $parentName = $parent->name;
                                }
                            }

                            $questionValues = BOL_QuestionService::getInstance()->findQuestionValues($parentName);
                            $value = array();

                            foreach ( $questionValues as $val )
                            {
                                /* @var $val BOL_QuestionValue */
                                if ( ( (int) $val->value ) == $questionValue  )
                                {
                                    $value[$val->value] = BOL_QuestionService::getInstance()->getQuestionValueLang($val->questionName, $val->value);
                                }
                            }

                            if ( !empty($value) )
                            {
                                $questionData[$userId][$question['name']] = $value;
                            }
                            else
                            {
                                unset($questionArray[$sectionKey][$questionKey]);
                            }

                            break;

                        case BOL_QuestionService::QUESTION_PRESENTATION_SELECT:
                        case BOL_QuestionService::QUESTION_PRESENTATION_RADIO:
                        case BOL_QuestionService::QUESTION_PRESENTATION_MULTICHECKBOX:

                            $value = "";
                            $multicheckboxValue = (int) $questionData[$userId][$question['name']];

                            $parentName = $question['name'];

                            if ( !empty($question['parent']) )
                            {
                                $parent = BOL_QuestionService::getInstance()->findQuestionByName($question['parent']);

                                if ( !empty($parent) )
                                {
                                    $parentName = $parent->name;
                                }
                            }

                            $questionValues = BOL_QuestionService::getInstance()->findQuestionValues($parentName);
                            $value = array();

                            foreach ( $questionValues as $val )
                            {
                                /* @var $val BOL_QuestionValue */
                                if ( ( (int) $val->value ) & $multicheckboxValue )
                                {
                                    /* if ( strlen($value) > 0 )
                                      {
                                      $value .= ', ';
                                      }

                                      $value .= $language->text('base', 'questions_question_' . $parentName . '_value_' . ($val->value)); */

                                    $value[$val->value] = BOL_QuestionService::getInstance()->getQuestionValueLang($val->questionName, $val->value);
                                }
                            }

                            if ( !empty($value) )
                            {
                                $questionData[$userId][$question['name']] = $value;
                            }
                            else
                            {
                                unset($questionArray[$sectionKey][$questionKey]);
                            }

                            break;

                        case BOL_QuestionService::QUESTION_PRESENTATION_URL:
                        case BOL_QuestionService::QUESTION_PRESENTATION_TEXT:
                        case BOL_QuestionService::QUESTION_PRESENTATION_TEXTAREA:
                            if ( !is_string($questionData[$userId][$question['name']]) )
                            {
                                break;
                            }

                            $value = trim($questionData[$userId][$question['name']]);

                            if ( strlen($value) > 0 )
                            {
                                $questionData[$userId][$question['name']] = UTIL_HtmlTag::autoLink(nl2br($value));
                            }
                            else
                            {
                                unset($questionArray[$sectionKey]);
                            }

                            break;

                        default:
                            unset($questionArray[$sectionKey][$questionKey]);
                    }
                }
                else
                {
                    unset($questionArray[$sectionKey][$questionKey]);
                }
            }

            if ( isset($questionArray[$sectionKey]) && count($questionArray[$sectionKey]) === 0 )
            {
                unset($questionArray[$sectionKey]);
            }
        }

        return array('questions' => $questionArray, 'data' => $questionData, 'labels' => $questionLabelList);
    }
    
    /**
     * Returns query parts for filtering users ( by default: suspended, not approved, not verified ). 
     * Result array includes strings: join, where, order
     * 
     * @param array $tables
     * @param array $fields
     * @param array $params
     * @return array
     */
    public function getQueryFilter( array $tables, array $fields, $params = array() )
    {
        return $this->userDao->getQueryFilter($tables, $fields, $params);
    }
}
