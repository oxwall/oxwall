<?php

class ADMIN_CMP_EditQuestion extends OW_Component
{
    protected $responderUrl;

    public function __construct( $questionId )
    {
        parent::__construct();

        $question = BOL_QuestionService::getInstance()->findQuestionById($questionId);

        $responderUrl = OW::getRouter()->urlFor("ADMIN_CTRL_Questions", "ajaxResponder");
        
        /* @var $addForm ADMIN_CLASS_AddQuestionForm */
        $addForm = OW::getClassInstance('ADMIN_CLASS_EditQuestionForm', 'qst_edit_form', $responderUrl);
        $addForm->loadQuestionData($question);

        $command = new HiddenField('command');
        $command->setValue('editQuestion');
        $addForm->addElement($command);

        $this->addForm($addForm);
        
        $sections = BOL_QuestionService::getInstance()->findAllSections();

        // need to hide sections select box
        if ( empty($sections) )
        {
            $this->assign('no_sections', true);
        }

        $fields = array();
        foreach ( $addForm->getElements() as $element )
        {
            if ( !($element instanceof HiddenField) )
            {
                $fields[$element->getName()] = $element->getName();
            }
        }

        $presentations2FormElements = $addForm->getPresentations2FormElements();

        $noValue = OW::getLanguage()->text('admin', 'questions_empty_lang_value');
        $this->assign('formData', $fields);
        $this->assign('displayedFormElements', $presentations2FormElements[BOL_QuestionService::QUESTION_PRESENTATION_TEXT]);

        $nameLang = BOL_QuestionService::getInstance()->getQuestionLangKeyName(BOL_QuestionService::LANG_KEY_TYPE_QUESTION_LABEL, $question->name);
        $descriptionLang = BOL_QuestionService::getInstance()->getQuestionLangKeyName(BOL_QuestionService::LANG_KEY_TYPE_QUESTION_DESCRIPTION, $question->name);
        $valueLang = BOL_QuestionService::getInstance()->getQuestionLangKeyName(BOL_QuestionService::LANG_KEY_TYPE_QUESTION_VALUE, $question->name, '');
        $nameJoinLang = BOL_QuestionService::getInstance()->getQuestionLangKeyName(BOL_QuestionService::LANG_KEY_TYPE_QUESTION_LABEL_JOIN, $question->name);
        $nameJoinDesc = BOL_QuestionService::getInstance()->getQuestionLangKeyName(BOL_QuestionService::LANG_KEY_TYPE_QUESTION_DESCRIPTION_JOIN, $question->name);
        $nameEditLang = BOL_QuestionService::getInstance()->getQuestionLangKeyName(BOL_QuestionService::LANG_KEY_TYPE_QUESTION_LABEL_EDIT, $question->name);
        $nameEditDesc = BOL_QuestionService::getInstance()->getQuestionLangKeyName(BOL_QuestionService::LANG_KEY_TYPE_QUESTION_DESCRIPTION_EDIT, $question->name);
        $nameSearchLang = BOL_QuestionService::getInstance()->getQuestionLangKeyName(BOL_QuestionService::LANG_KEY_TYPE_QUESTION_LABEL_SEARCH, $question->name);
        $nameSearchDesc = BOL_QuestionService::getInstance()->getQuestionLangKeyName(BOL_QuestionService::LANG_KEY_TYPE_QUESTION_DESCRIPTION_SEARCH, $question->name);

        $script = ' window.addQuest = new QuestionFormModel( ' . json_encode( array(
            'formName' => 'qst_edit_form',
            'presentations2FormElements' => $presentations2FormElements
        ) ) . ' );

        OW.bind( "question.value.delete", function( params ) {
                var questionId = params.node.parents(\'form[name=qst_edit_form]:eq(0)\').find(\'input[name=questionId]\').val();
                var value = params.value;

                if ( questionId && value )
                {
                    $.ajax( {
                        url: ' . json_encode($responderUrl) . ',
                        type: \'POST\',
                        data: {command: \'DeleteQuestionValue\', questionId: questionId, value: value},
                        dataType: \'json\'
                    } );
                }
        } );

        OW.bind( "question.value.add", function( params ) {
                var questionId = params.node.parents(\'form[name=qst_edit_form]:eq(0)\').find(\'input[name=questionId]\').val();
                var values = params.values;

                if ( questionId && values )
                {
                    $.ajax( {
                        url: ' . json_encode($responderUrl) . ',
                        type: \'POST\',
                        data: {command: \'AddQuestionValues\', questionId: questionId, values: values},
                        dataType: \'json\'
                    } );
                }
        } );

        $("form[name=qst_edit_form] a.question_label").on( "click",  function(){
            OW.ajaxFloatBox("BASE_CMP_LanguageValueEdit", [\'base\', '.json_encode($nameLang).', true], { title: '.json_encode(OW::getLanguage()->text('base', 'questions_edit_question_label_title')).' } );
        });

        $("form[name=qst_edit_form] a.question_description").on( "click", function(){
            OW.ajaxFloatBox("BASE_CMP_LanguageValueEdit", [\'base\', '.json_encode($descriptionLang).', true], { title: '.json_encode(OW::getLanguage()->text('base', 'questions_edit_description_label_title')).' } );
        });

        $("form[name=qst_edit_form] a.question_join_label").on( "click", function(){
            OW.ajaxFloatBox("BASE_CMP_LanguageValueEdit", [\'base\', '.json_encode($nameJoinLang).', true], { title: '.json_encode(OW::getLanguage()->text('admin', 'questions_edit_join_label_title')).' } );
        });
        
        $("form[name=qst_edit_form] a.question_join_desc").on( "click", function(){
            OW.ajaxFloatBox("BASE_CMP_LanguageValueEdit", [\'base\', '.json_encode($nameJoinDesc).', true], { title: '.json_encode(OW::getLanguage()->text('admin', 'questions_edit_join_description_title')).' } );
        });

        $("form[name=qst_edit_form] a.question_edit_label").on( "click", function(){
            OW.ajaxFloatBox("BASE_CMP_LanguageValueEdit", [\'base\', '.json_encode($nameEditLang).', true], { title: '.json_encode(OW::getLanguage()->text('admin', 'questions_edit_edit_label_title')).' } );
        });

        $("form[name=qst_edit_form] a.question_edit_desc").on( "click", function(){
            OW.ajaxFloatBox("BASE_CMP_LanguageValueEdit", [\'base\', '.json_encode($nameEditDesc).', true], { title: '.json_encode(OW::getLanguage()->text('admin', 'questions_edit_edit_description_title')).' } );
        });

        $("form[name=qst_edit_form] a.question_search_label").on( "click", function(){
            OW.ajaxFloatBox("BASE_CMP_LanguageValueEdit", [\'base\', '.json_encode($nameSearchLang).', true], { title: '.json_encode(OW::getLanguage()->text('admin', 'questions_edit_search_label_title')).' } );
        });

        $("form[name=qst_edit_form] a.question_search_desc").on( "click", function(){
            OW.ajaxFloatBox("BASE_CMP_LanguageValueEdit", [\'base\', '.json_encode($nameSearchDesc).', true], { title: '.json_encode(OW::getLanguage()->text('admin', 'questions_edit_search_description_title')).' } );
        });

        $("form[name=qst_edit_form]").on( "dblclick", ".values_list .tag .label", function(){
            var value = $(this).parents(\'span.tag:eq(0)\').find(\'input[type=hidden]\').val();
            OW.ajaxFloatBox("BASE_CMP_LanguageValueEdit", [\'base\', '.json_encode($valueLang).'+value, true], { title: '.json_encode(OW::getLanguage()->text('admin', 'questions_edit_question_value_title')).' } );
        } );

        OW.bind( "admin.language_key_edit_success", function( params ) {
            if ( params && params.result == "success" && params.key )
            {
                var translationValue = params.value.trim();
                var translation = translationValue && translationValue != "&nbsp;" ? translationValue : ' . json_encode($noValue) . ';
                var closeFloatbox = false;

                // set value to form element
                if ( /^'.$valueLang.'/g.test(params.key) )
                {
                    var value = params.key.replace(/^'.$valueLang.'/g, "");

                    var input = $("form[name=qst_edit_form] .values_list").find("input[type=hidden][value="+value+"]");
                    var label = input.parents("span.tag:eq(0)").find("span.label");

                    label.html(translation);

                    closeFloatbox = true;
                }
                else if ( /^'.$nameLang.'/g.test(params.key) )
                {
                    $("form[name=qst_edit_form] a.question_label").html(translation);
                    closeFloatbox = true;
                }
                else if ( /^'.$descriptionLang.'/g.test(params.key) )
                {
                    $("form[name=qst_edit_form] a.question_description").html(translation);
                    closeFloatbox = true;
                }
                else if ( /^'.$nameJoinLang.'/g.test(params.key) )
                {
                    $("form[name=qst_edit_form] a.question_join_label").html(translation);
                    closeFloatbox = true;
                }
                else if ( /^'.$nameJoinDesc.'/g.test(params.key) )
                {
                    $("form[name=qst_edit_form] a.question_join_desc").html(translation);
                    closeFloatbox = true;
                }
                else if ( /^'.$nameEditLang.'/g.test(params.key) )
                {
                    $("form[name=qst_edit_form] a.question_edit_label").html(translation);
                    closeFloatbox = true;
                }
                else if ( /^'.$nameEditDesc.'/g.test(params.key) )
                {
                    $("form[name=qst_edit_form] a.question_edit_desc").html(translation);
                    closeFloatbox = true;
                }
                else if ( /^'.$nameSearchLang.'/g.test(params.key) )
                {
                    $("form[name=qst_edit_form] a.question_search_label").html(translation);
                    closeFloatbox = true;
                }
                else if ( /^'.$nameSearchDesc.'/g.test(params.key) )
                {
                    $("form[name=qst_edit_form] a.question_search_desc").html(translation);
                    closeFloatbox = true;
                }

                if ( closeFloatbox )
                {
                    // close floatbox
                    var floatbox = OW.getActiveFloatBox();
                    floatbox.close();
                }
            }
        });

        ';

        OW::getDocument()->addOnloadScript($script);
        
        $jsDir = OW::getPluginManager()->getPlugin("admin")->getStaticJsUrl();
        OW::getDocument()->addScript($jsDir . "questions.js");

        $questionLabel = BOL_QuestionService::getInstance()->getQuestionLang($question->name);
        $questionDescription = BOL_QuestionService::getInstance()->getQuestionDescriptionLang($question->name);
        $questionLabel = ( mb_strlen(trim($questionLabel)) == 0 || $questionLabel == '&nbsp;' ) ? $noValue : $questionLabel;

        $questionDescription = ( mb_strlen(trim($questionDescription)) == 0 || $questionDescription == '&nbsp;' ) ? $noValue : $questionDescription;

        $this->assign('questionLabel', $questionLabel);
        $this->assign('questionDescription', $questionDescription);


        // get extra labels
        $extraLangs = array(
            BOL_QuestionService::LANG_KEY_TYPE_QUESTION_LABEL_JOIN,
            BOL_QuestionService::LANG_KEY_TYPE_QUESTION_DESCRIPTION_JOIN,
            BOL_QuestionService::LANG_KEY_TYPE_QUESTION_LABEL_EDIT,
            BOL_QuestionService::LANG_KEY_TYPE_QUESTION_DESCRIPTION_EDIT,
            BOL_QuestionService::LANG_KEY_TYPE_QUESTION_LABEL_SEARCH,
            BOL_QuestionService::LANG_KEY_TYPE_QUESTION_DESCRIPTION_SEARCH
        );

        foreach ( $extraLangs as $extraLang )
        {
            $questionLabel = BOL_QuestionService::getInstance()->getQuestionLangByType($question->name, $extraLang, '');
            $questionLabel = ( mb_strlen(trim($questionLabel)) == 0 || $questionLabel == '&nbsp;' ) ? $noValue : $questionLabel;

            $this->assign($extraLang, $questionLabel);
        }
    }
}