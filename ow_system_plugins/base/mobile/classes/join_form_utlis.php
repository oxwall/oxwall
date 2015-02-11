<?php

final class BASE_MCLASS_JoinFormUtlis
{
    public static function presentationToCssClass()
    {
        $result = array();
        $presentations = BOL_QuestionService::getInstance()->getPresentations();

        foreach ( $presentations as $presentation => $dataType )
        {
            switch($presentation)
            {
                case BOL_QuestionService::QUESTION_PRESENTATION_CHECKBOX:
                    $result[$presentation] = 'owm_checkbox_wrap owm_simple_checkbox';
                    break;
                case BOL_QuestionService::QUESTION_PRESENTATION_MULTICHECKBOX:
                    $result[$presentation] = 'owm_checkbox_wrap';
                    break;
                case BOL_QuestionService::QUESTION_PRESENTATION_SELECT:
                    $result[$presentation] = 'owm_field_wrap owm_select_wrap';
                    break;
                case BOL_QuestionService::QUESTION_PRESENTATION_RADIO:
                    $result[$presentation] = 'owm_radio_wrap';
                    break;
                case BOL_QuestionService::QUESTION_PRESENTATION_BIRTHDATE:
                case BOL_QuestionService::QUESTION_PRESENTATION_DATE:
                case BOL_QuestionService::QUESTION_PRESENTATION_AGE:
                    $result[$presentation] = 'owm_field_wrap owm_select_wrap';
                    break;
                case BOL_QuestionService::QUESTION_PRESENTATION_TEXTAREA:
                    $result[$presentation] = 'owm_field_wrap owm_box_padding';
                    break;
                default:
                    $result[$presentation] = 'owm_field_wrap';
                    break;
            }
        }

        return $result;
    }

    public static function setInvitations( Form $form, array $questions )
    {
        foreach( $questions as $question )
        {            
            if( in_array($question['presentation'], array(BOL_QuestionService::QUESTION_PRESENTATION_TEXT, BOL_QuestionService::QUESTION_PRESENTATION_PASSWORD)) )
            {
                /* @var $element FormElement */
                $element = $form->getElement($question['name']);

                if( empty($element) )
                {
                    continue;
                }

                if ( method_exists($element, 'setInvitation') && method_exists($element, 'setHasInvitation') )
                {
                    $element->setHasInvitation(true);
                    $element->setInvitation(strip_tags($element->getLabel()));
                }

                if ( $question['name'] == 'password' )
                {
                    $label = '';

                    if ( !empty($element) && $element->getName() == 'password' )
                    {
                        $label = $element->getLabel();
                    }

                    $element = $form->getElement('repeatPassword');

                    if( empty($element) )
                    {
                        continue;
                    }

                    if ( method_exists($element, 'setInvitation') && method_exists($element, 'setHasInvitation') )
                    {
                        $element->setHasInvitation(true);
                        $element->setInvitation(strip_tags($label));
                    }
                }
            }
        }
    }
    
    public static function setLabels( Form $form, array $questions )
    {
        foreach( $questions as $question )
        {            
            if( !empty($question['required']) )
            {
                /* @var $element FormElement */
                $element = $form->getElement($question['name']);

                if( empty($element) )
                {
                    continue;
                }
                
                $label = $element->getLabel();
                $label .= '<span class="owm_required_star">*<span>';
                
                $element->setLabel($label);
            }
        }
    }

    public static function setColumnCount( Form $form )
    {
        $elements = $form->getElements();

        foreach( $elements as $element )
        {
            if ( method_exists($element, 'getColumnsCount') && method_exists($element, 'setColumnCount') )
            {
                if( $element->getColumnsCount() > 2 )
                {
                    $element->setColumnCount(2);
                }
            }
        }
    }
    
    public static function addOnloadJs( $formName )
    {
        /* OW::getDocument()->addOnloadScript('window.owForms['.  json_encode($formName).'].validate = function(){

        var error = false;
        var element = null;
        var errorMessage;

        $.each( this.elements,
            function(index, data){
                try{
                    data.validate();
                }catch (e){
                    error = true;

                    if( element == null ){
                        element = data;
                        errorMessage = e;
                    }
                }
            }
        );

        if( error ){
            element.input.focus();
            $(element.input).parents(".owm_field_container:eq(0)").addClass("owm_field_error");

            if( this.validateErrorMessage ){
                throw this.validateErrorMessage;
            }else{
                throw errorMessage;
            }
        }
    }

    window.owForms['.  json_encode($formName).'].removeErrors = function(){
        $.each( this.elements,
            function( index, data ){
                data.removeErrors();
                $(data.input).parents(".owm_field_container:eq(0)").removeClass("owm_field_error");
            }
            );
    }
', 99999999); */
    }
}
?>
