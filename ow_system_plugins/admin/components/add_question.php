<?php

class ADMIN_CMP_AddQuestion extends OW_Component
{
    protected $responderUrl;

    public function __construct()
    {
        parent::__construct();
        /* @var $addForm ADMIN_CLASS_AddQuestionForm */
        $addForm = OW::getClassInstance('ADMIN_CLASS_AddQuestionForm', 'qst_add_form', OW::getRouter()->urlFor("ADMIN_CTRL_Questions", "ajaxResponder"));

        $valuesStorage = new HiddenField('valuesStorage');
        $valuesStorage->setValue('{}');
        $addForm->addElement($valuesStorage);

        $command = new HiddenField('command');
        $command->setValue('addQuestion');
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

        $this->assign('formData', $fields);
        $this->assign('displayedFormElements', $presentations2FormElements[BOL_QuestionService::QUESTION_PRESENTATION_TEXT]);

        $formId = $addForm->getId();

        $script = ' window.addQuest = new QuestionFormModel( ' . json_encode(array(
                'formName' => 'qst_add_form',
                'presentations2FormElements' => $presentations2FormElements
                )) . ' );

       OW.bind("admin.questions_edit_question_value", function(data) {

            var storage = $(\'form' . "#" . $formId . '\').find(\'input[name=valuesStorage]\');
            var form = $(\'form' . "#" . $formId . '\')

            var regexp = /lang\[(\d+)\]\[value\]\[([\w_]+)\]/i;
            
            if ( storage && data )
            {
                var values = {};
                
                $.each(data , function( key, value ) {

                    var match = key.match(regexp);
                    
                    if ( match && match[0] && value )
                    {
                        if ( data.value != match[2] )
                        {
                            return;
                        }

                        var langId = match[1];
                        values[langId.toString()] = value;
                        
                        if ( langId == ' . json_encode(OW::getLanguage()->getCurrentId()) . ' )
                        {
                            var formElement = window.addQuestionValues['.json_encode($addForm->getElement('qst_possible_values')->getId()).'];
                            
                            formElement.value[data.value] = value;
                            
                            formElement.updateDataField();
                            formElement.renderValues();
                        }
                    }
                    
                } );
                
                var storageData = storage.val();
                storageData = $.parseJSON(storageData);
                storageData[data.value] = values;
                storage.val( JSON.stringify(storageData) );

                // close floatbox
                var floatbox = OW.getActiveFloatBox();
                floatbox.close();
            } 
        } );

        OW.bind("question.value.add", function(data) {
            var currentLang = ' . json_encode(OW::getLanguage()->getCurrentId()) . ';

            var values = {};

            var storage = $(\'form' . "#" . $formId . '\').find(\'input[type=hidden][name=valuesStorage]\');
            var storageData = $.parseJSON(storage.val());

            if ( data.values && form )
            {
                $.each(data.values , function( key, value ) {

                    if ( !values[key.toString()] )
                    {
                        values[key.toString()] = {};
                    }

                    values[key.toString()][currentLang.toString()] = value;
                } );

                var val = jQuery.extend(storageData, values);
                
                storage.val( JSON.stringify(val) );
            }
        } );

        OW.bind("question.value.delete", function(data) {

            var storage = $(\'form' . "#" . $formId . '\').find(\'input[type=hidden][name=valuesStorage]\');
            var storageData = $.parseJSON(storage.val());

            if ( data.value && form && storageData && storageData[data.value] )
            {
                var values = {};

                $.each(storageData , function( key, value ) {

                    if ( data.value != key )
                    {
                        if ( !values[key.toString()] )
                        {
                            values[key.toString()] = {};
                        }

                        $.each(storageData[key] , function( langId, value ) {
                            values[key][langId] = value;
                        });
                    }
                } );

                storage.val( JSON.stringify(values) );
            }
        } );


        $("form[name=qst_add_form]").on( "dblclick", ".values_list .tag .label", function() {
            var value = $(this).parents(\'span.tag:eq(0)\').find(\'input[type=hidden]\').val();
            var valueList = $(this).parents(\'form:eq(0)\').find(\'input[type=hidden][name=valuesStorage]\').val();

            var data = $.parseJSON(valueList);

            if ( !data[value] )
            {
                data[value] = {};
            }
            
            OW.ajaxFloatBox("ADMIN_CMP_EditQuestionValueLabel", [value, data[value]], { title: ' . json_encode(OW::getLanguage()->text('base', 'questions_edit_question_value_title')) . ' } );
        } );

        ';

        OW::getDocument()->addOnloadScript($script);

        $jsDir = OW::getPluginManager()->getPlugin("admin")->getStaticJsUrl();
        OW::getDocument()->addScript($jsDir . "questions.js");
    }
}