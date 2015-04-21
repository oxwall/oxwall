<?php

class ADMIN_CLASS_AddAccountTypeForm extends Form {

    /**
     *
     * Constructor
     * @param $prefix
     * @param $key
     * @param BASE_CMP_LanguageValueEdit $parent
     */
    public function __construct(BOL_QuestionAccountType $accountType, $formName = '') {
        if (empty($formName)) {
            $formName = 'account_type_' . sha1(rand(0, 99999999));
        }

        parent::__construct($formName);

        $key = BOL_QuestionService::getInstance()->getQuestionLangKeyName(BOL_QuestionService::LANG_KEY_TYPE_ACCOUNT_TYPE, $accountType->name);
        $prefix = 'base';

        $this->setAjax(true);
        $this->setAction(OW::getRouter()->urlFor("ADMIN_CTRL_Questions", "ajaxResponder"));

        $hidden = new HiddenField('command');
        $hidden->setValue('AddAccountType');

        $this->addElement($hidden);

        $hidden = new HiddenField('key');
        $hidden->setValue($key);

        $this->addElement($hidden);

        $hidden = new HiddenField('prefix');
        $hidden->setValue($prefix);

        $this->addElement($hidden);

        $hidden = new HiddenField('accountTypeName');
        $hidden->setValue($accountType->name);

        $this->addElement($hidden);

        $languageService = BOL_LanguageService::getInstance();
        $list = $languageService->findActiveList();

        foreach ($list as $item) {
            $textArea = new Textarea("lang[{$item->getId()}][{$prefix}][{$key}]");
            $dto = $languageService->getValue($item->getId(), $prefix, $key);

            $value = ($dto !== null) ? $dto->getValue() : '';

            $textArea->setValue($value);
            $textArea->addAttribute('style', 'height: 32px;');

            $this->addElement($textArea);
        }

        $roleList = BOL_AuthorizationService::getInstance()->findNonGuestRoleList();

        $defaultRole = null;
        if (!empty($accountType->roleId)) {
            $defaultRole = BOL_AuthorizationService::getInstance()->getRoleById($accountType->roleId);
        }

        if (empty($defaultRole)) {
            $defaultRole = BOL_AuthorizationService::getInstance()->getDefaultRole();
        }

        $options = array();

        foreach ($roleList as $role) {
            $options[$role->id] = BOL_AuthorizationService::getInstance()->getRoleLabel($role->name);
        }

        $roleFormElement = new Selectbox('role');
        $roleFormElement->setOptions($options);
        $roleFormElement->setValue($defaultRole->id);
        $roleFormElement->setHasInvitation(false);
        $this->addElement($roleFormElement);

        if (!empty($accountType->id)) {
            $accountTypes = BOL_QuestionService::getInstance()->findAllAccountTypes();

            if (count($accountTypes) > 1) {
                $options = array();
                $i = 1;

                foreach ($accountTypes as $dto) {
                    /* @var $dto BOL_QuestionAccountType  */
                    $options[$dto->sortOrder] = $i;
                    $i++;
                }

                $orderFormElement = new Selectbox('order');
                $orderFormElement->setOptions($options);
                $orderFormElement->setValue($accountType->sortOrder);
                $orderFormElement->setHasInvitation(false);
                $this->addElement($orderFormElement);
            }
        }

        $submit = new Submit('submit');
        $submit->setValue(OW::getLanguage()->text('admin', 'questions_add_new_account_type'));

        $jsString = ' owForms[{$formName}].bind("success", function(json){
            if ( json.result.add == true) {
                OW.registerLanguageKey("base", ' . json_encode($key) . ', json.accountTypeName);
                OW.trigger("admin.add_account_type", [json], this);
            }
        }); ';

        OW::getLanguage()->addKeyForJs($prefix, $key);
        if (!empty($accountType->id)) {
            $jsString = ' owForms[{$formName}].bind("success", function(json){
                if ( json.result.update == true) {
                    OW.registerLanguageKey("base", ' . json_encode($key) . ', json.accountTypeName);
                    OW.trigger("admin.update_account_type", [json], this);
                }
            }); ';
        }

        $script = UTIL_JsGenerator::composeJsString($jsString, array(
                    'formName' => $this->getName()
        ));

        OW::getDocument()->addOnloadScript($script);

        if ($accountType->id) {
            $submit->setValue(OW::getLanguage()->text('admin', 'questions_save_account_type'));
        }
        $this->addElement($submit);
    }

    public function process($data) {
        $order = isset($data['order']) ? $data['order'] : null;
        $result = $this->saveOrUpdateAccountType($data['accountTypeName'], $data['role'], $order);
        $this->saveLangValues($data['prefix'], $data['key']);

        return $result;
    }

    protected function saveOrUpdateAccountType($accountTypeName, $roleId, $order) 
    {
        $result = array(
            'add' => false,
            'update' => false,
            'reorder' => false,
            'orderList' => array(),
        );

        $list = array();
        
        $accountType = BOL_QuestionService::getInstance()->findAccountTypeByName($accountTypeName);

        if (empty($accountType)) {
            $accountType = BOL_QuestionService::getInstance()->createAccountType($accountTypeName, '', $roleId);
            $result['add'] = true;
        } else {
            
            if ( $accountType->roleId != $roleId )
            {
                BOL_QuestionService::getInstance()->deleteUsersRoleByAccountType($accountType);
                $accountType->roleId = $roleId;
                BOL_QuestionService::getInstance()->addUsersRoleByAccountType($accountType);
            }            
            
            $accountType->roleId = $roleId;
            BOL_QuestionService::getInstance()->saveOrUpdateAccountType($accountType);
            $result['update'] = true;
        }

        if (isset($order)) {
            $direction = 0;

            if ($accountType->sortOrder > $order) {
                $direction = -1;
            } else if ($accountType->sortOrder < $order) {
                $direction = 1;
            }
            
            if ( !empty($direction) )
            {
                // sorted account type list
                $accountTypes = BOL_QuestionService::getInstance()->findAllAccountTypes();

                $orderNumber = 0;
                $flag = false;

                foreach ($accountTypes as $account) {
                    if ($account->name == $accountType->name) {
                        continue;
                    }

                    $list[$account->name] = $orderNumber;

                    //
                    if ($account->sortOrder >= $order) {
                        if (!$flag) {
                            if ($direction < 0) {
                                $list[$accountTypeName] = $orderNumber;
                                $orderNumber++;
                                $list[$account->name] = $orderNumber;
                            } else if ($direction > 0) {
                                $list[$account->name] = $orderNumber;
                                $orderNumber++;
                                $list[$accountTypeName] = $orderNumber;
                            }

                            $flag = true;
                        }
                    }

                    $orderNumber++;
                }


                if (!isset($list[$accountTypeName])) {
                    $list[$accountTypeName] = $orderNumber;
                }

                BOL_QuestionService::getInstance()->reOrderAccountType($list);
                $result['reorder'] = true;
                $result['orderList'] = $list;
                        
                $event = new OW_Event(BOL_QuestionService::EVENT_ON_ACCOUNT_TYPE_REORDER, array('dto' => $accountType, 'id' => $accountType->id, 'orderList' => $list));
                OW::getEventManager()->trigger($event);
            }
        }

                
        $event = new OW_Event(BOL_QuestionService::EVENT_ON_ACCOUNT_TYPE_REORDER, array('dto' => $accountType, 'id' => $accountType->id, 'orderList' => $list));
        OW::getEventManager()->trigger($event);
        
        return $result;
    }

    protected function saveLangValues($prefix, $key) {
        $languageService = BOL_LanguageService::getInstance();
        $list = $languageService->findActiveList();
        $currentLanguageId = OW::getLanguage()->getCurrentId();
        $currentLangValue = "";

        $keyDto = $languageService->findKey($prefix, $key);

        if (empty($keyDto)) {
            $prefixDto = $languageService->findPrefix($prefix);
            $keyDto = $languageService->addKey($prefixDto->id, $key);
        }

        foreach ($list as $item) {
            $value = trim($_POST['lang'][$item->getId()][$prefix][$key]);


            if (mb_strlen(trim($value)) == 0 || $value == json_decode('"\u00a0"')) { // stupid hack
                $value = '&nbsp;';
            }

            $dto = $languageService->findValue($item->getId(), $keyDto->getId());

            if ($dto !== null) {
                if ($dto->getValue() !== $value) {
                    $languageService->saveValue($dto->setValue($value, false));
                }
            } else {
                $dto = $languageService->addValue($item->getId(), $prefix, $key, $value, false);
            }

            if ((int) $currentLanguageId === (int) $item->getId()) {
                $currentLangValue = $value;
            }
        }
        
        $languageService->generateCache(OW::getLanguage()->getCurrentId());
    }

}
