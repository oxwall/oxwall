var QuestionUtils = function()
{
   this.in_array = function (what, where)
   {

        var a=false;

        for(var i=0; i<where.length; i++)
        {
            if(what == where[i])
            {
                a=true;
                break;
            }
        }
        return a;
    }
}

var qUtils = new QuestionUtils();

var questionValuesField = function( params )
{
    var self = this;
    
    self.dataFieldId;
    self.dataField;

    self.tagFieldId;
    self.tagField;

    self.tr;

    this.template;
    this.value = {};
    this.order = [];
    this.possibleValuesList = [];

    this.addButton;
    this.valuesArea;

    this.construct(params);
};

questionValuesField.prototype = {
    
    construct : function ( params )
    {
        var self = this;

        self.dataFieldId = params['dataFieldId'];
        self.dataField = $('input#' + this.dataFieldId);

        self.tagFieldId = params['tagFieldId'];
        self.tagField = $('input#' + this.tagFieldId);

        self.template = $(params['template']);

        self.tr = self.dataField .parents('tr:eq(0)');

        self.addButton = self.tr.find('input[name=qst_possible_values_add_button]');
        self.valuesArea = self.tr.find('.values_list');

        self.addButton.click(
            function()
            {
                var value = self.tagField.val();
                self.tagField.val('');
                self.tr.find('.tagsinput span.tag').remove();
                
                if ( value )
                {
                    self.setValue(value);
                }
            }
        );

        var id = 0;
        for ( var i = 0; i < 32; i++ )
        {
            id = Math.pow(2, i);
            self.possibleValuesList.push(id);
        }

        if ( params['value'] && !$.isEmptyObject(params['value']) )
        {
            //self.value = params['value'];
            
            $.each(params['value'], function( key, item )
            {
                if ( $.inArray(key, self.possibleValuesList) )
                {
                    self.order.push(key);
                    self.value[key] = item;
                }
            });
        }
        
        if ( params['order'] && !$.isEmptyObject(params['order']) )
        {
            self.order = params['order'];
        }

        self.valuesArea.on( 'click', '.remove', function() {
            if( confirm(OW.getLanguageText('admin', 'questions_edit_delete_value_confirm_message')) )
            {
                self.deleteValue($(this));
            }
        } );

        self.renderValues();
        self.sortable();
    },

    setValue : function ( values )
    {
        var self = this;

        var list = values.split(',');
        var valuesList = [];
        var first = 0;

        if ( list )
        {
            var addValues = {};

            $.each(list, function( key, item )
            {
                if ( item != undefined )
                {
                    valuesList.push(item);
                }
            });
            
            $.each(self.possibleValuesList, function( key, item )
            {
                if ( valuesList.lenght >= first )
                {
                    return false;
                }

                if ( self.value[item] == undefined && valuesList[first] != undefined )
                {
                    self.value[item] = valuesList[first];
                    addValues[item] = valuesList[first];
                    self.order.push(item);
                    first++;
                }
            });
            
            self.renderValues();
            OW.trigger('question.value.add', {values:addValues, node:self.tr});
            self.updateDataField();
        }
    },

    renderValues : function ()
    {
        var self = this;

        self.valuesArea.empty();

        $.each(self.order, function( key, item )
        {
            if ( item != undefined && self.value[item] != undefined )
            {
                self.renderValue(item);
            }
        });
    },

    renderValue : function ( item )
    {
        var self = this;

        var template = self.template.clone();
        var label = template.find('.label');
        var value = template.find('input[type=hidden]');

        label.html(self.value[item]);
        value.val(item);
        
        self.valuesArea.append(template);
    },

    deleteValue : function ( element )
    {
        var self = this;
        var div = element.parents("div.question_value_block:eq(0)");
        var item = div.find( 'input[type=hidden]' ).val();

        if ( item )
        {
            self.value[item] = undefined;

            $.each(self.order, function ( key, value ) {
                if ( value == item )
                {
                    self.order[key] = undefined;
                }
            });
        }

        div.remove();
        OW.trigger('question.value.delete', {value:item, node:self.tr});
        self.updateDataField();
    },
    
    updateDataField : function ()
    {
        var self = this;

        var values = [];
        
        $.each(self.order, function( key, item )
        {
            var obj = {};
            if ( self.value[item] != undefined )
            {
                obj[item] = self.value[item];
                values.push(obj);
            }
        });
        self.dataField.val( JSON.stringify(values).replace(/"/g,'\"') );
    },

    sortable : function ()
    {
        var self = this;

        self.valuesArea.sortable({
               items: '.question_value_block',
               cursor: 'move',
               tolerance: 'pointer',
               
               update: function(event, ui)
               {
                    self.order = [];

                    $.each( self.valuesArea.find('.tag'), function( key, item ) {
                        var value = $(item).find( 'input[type=hidden]' ).val();
                        self.order.push(value);
                    } );

                    self.updateDataField();
               }
        });
    }
}

var QuestionFormModel = function( params )
{
    this.presentationToQuestion = {};
    //this.fieldParams = {};
    this.presentationField
    this.questionBlockList = {};
    this.formName;

    this.construct( params )
}

QuestionFormModel.prototype = {
    construct : function ( params )
    {
        var self = this;
        self.presentationToQuestion = params['presentations2FormElements'];
        self.formName = params['formName'];

        self.init();

        self.presentationField = $('form[name='+self.formName+'] select[name=qst_answer_type]');
        self.changePresentation(self.presentationField);

        self.presentationField.on( 'change', function() {self.changePresentation(this)} )
    },

    init : function()
    {
        var self = this;

        $.each( self.presentationToQuestion['text'], function( name, value ) {
            self.questionBlockList[name] = $('form[name="'+self.formName+'"] [name^="'+name+'"]').parents('tr:eq(0)');

            //self.fieldParams[name] = value;
        } );
    },

    changePresentation : function( element )
    {
        var self = this;
        var value = $(element).val();

        $.each( self.presentationToQuestion[value], function( name, value ) {
            if ( value == true )
            {
                self.questionBlockList[name].show();
            }
            else
            {
                self.questionBlockList[name].hide();
            }
        });
    }
}

var questionAccountTypesModel = function( params, accountTypes )
{
    var self = this;
    
    self.queue = {};
    
    this.name;    
    this.accountTypes = {};
    this.nodes = {};
    this.data = {};

    this.construct(params, accountTypes);
}


questionAccountTypesModel.prototype = {

    construct : function ( params, accountTypes )
    {
        var self = this;
        self.name = params.name;

        self.question_node = $("tr[question_name="+self.name+"]");

        if ( accountTypes && !params.disable_account_types )
        {
            $.each(accountTypes, function( key, item ) {

                self.accountTypes[item] = item;
                self.nodes[item] = self.question_node.find('.'+item);
                self.nodes[item].on( 'click', function() {self.change(this);});

            } );
        }

        self.loadDataFromView();
    },

    setResponderUrl : function ( responderUrl )
    {
        this.responderUrl = responderUrl;
    },

    loadDataFromView : function ()
    {
        var self = this;

        $.each(self.accountTypes, function( key, item ) {
            self.data[item] = self.nodes[item].hasClass('ow_checkbox_cell_marked');
        } );
    },

    setData : function ( value )
    {
        this.required = value;
    },
    
    saveQueue : function()
    {
        var self = this;
        
        if ( !self.sendRequest && !$.isEmptyObject(self.queue) )
        {
            self.sendRequest = true;
                   
            var data = self.queue;
            
            self.queue = {};

            $.ajax( {
                url: self.responderUrl,
                type: 'POST',
                data: data
,
                success: function( result ) {
                    self.sendRequest = false;
                    self.saveQueue();
                },
                
                dataType: 'json'
            } );
        }
    },
    
    save : function()
    {
        var self = this;
        
        var data = {
                        command: 'questionAccountTypes',
                        question: self.name,
                        data: self.data,
                        commandId: self.data
                   };
               
        if ( !self.sendRequest )
        {
            self.sendRequest = true;

            $.ajax( {
                url: self.responderUrl,
                type: 'POST',
                data: data,
                success: function( result ) {
                    self.sendRequest = false;
                    self.saveQueue();
                },
                dataType: 'json'
            } );
        }
        else
        {
            self.queue = data;
        }
    },

    change : function( element )
    {
        var self = this;

        var element = $(element);
        //element.toggleClass('ow_checkbox_cell_marked');

        if( element.hasClass('ow_checkbox_cell_marked') )
        {
            element.removeClass('ow_checkbox_cell_marked');
            element.addClass('ow_checkbox_cell');
        }
        else
        {
            element.addClass('ow_checkbox_cell_marked');
            element.removeClass('ow_checkbox_cell');
        }

        // -- animation --
        var td = element.parents('td:eq(0)');
        td.addClass('selected');
        td.removeClass('not_selected');

        setTimeout( function(){
                td.removeClass('selected');
                td.addClass('not_selected');
            }, 200);
        // --------------
        
        var accountType = undefined;

        $.each(self.accountTypes, function( key, item ) {
            if ( element.hasClass(item) )
            {
               accountType = item;
               return false;
            }
        } );

        self.data[accountType] = self.nodes[accountType].hasClass('ow_checkbox_cell_marked');

        self.save();
    }
}

var questionPagesModel = function( params )
{
    var self = this;
    
    self.queue = {};

    this.name;
    this.required;
    this.onJoin;
    this.onEdit;
    this.onView;
    this.onSearch;

    this.question_node;
    this.required_node;
    this.join_node;
    this.edit_node;
    this.view_node;
    this.search_node;

    this.sendRequest = false;

    this.construct(params);
}

questionPagesModel.prototype = {

    construct : function ( params )
    {
        var self = this;
        self.name = params.name;
        
        self.question_node = $("tr[question_name="+self.name+"]");
        self.required_node = self.question_node.find(".required");
        self.join_node = self.question_node.find(".on_join");
        self.edit_node = self.question_node.find(".on_edit");
        self.view_node = self.question_node.find(".on_view");
        self.search_node = self.question_node.find(".on_search");
        
        self.loadDataFromView();

        if ( params.disable_required != 1 )
        {
            self.required_node.on( 'click', function() {self.change(this);});
        }

        if ( params.disable_on_join != 1 )
        {
            self.join_node.on( 'click', function() {self.change(this);});
        }

        if ( params.disable_on_edit != 1 )
        {
            self.edit_node.on( 'click', function() {self.change(this);});
        }

        if ( params.disable_on_view != 1 )
        {
            self.view_node.on( 'click', function() {self.change(this);});
        }

        if ( params.disable_on_search != 1 )
        {
            self.search_node.on( 'click', function() {self.change(this);});
        }
        
        
    },

    setResponderUrl : function ( responderUrl )
    {
        this.responderUrl = responderUrl;
    },

    loadDataFromView : function ()
    {
        var self = this;
        self.setRequired( self.required_node.hasClass('ow_checkbox_cell_marked') );
        self.setJoin( self.join_node.hasClass('ow_checkbox_cell_marked') );
        self.setEdit( self.edit_node.hasClass('ow_checkbox_cell_marked') );
        self.setView( self.view_node.hasClass('ow_checkbox_cell_marked') );
        self.setSearch( self.search_node.hasClass('ow_checkbox_cell_marked') );
    },

    setRequired : function ( value )
    {
        this.required = value;
    },

    setJoin : function ( value )
    {
        this.onJoin = value;
    },

    setEdit : function ( value )
    {
        this.onEdit = value;
    },

    setView : function ( value )
    {
        this.onView = value;
    },

    setSearch : function ( value )
    {
        this.onSearch = value;
    },
    
    saveQueue : function()
    {
        var self = this;
        
        if ( !self.sendRequest && !$.isEmptyObject(self.queue) )
        {
            self.sendRequest = true;
                   
            var data = self.queue;
            
            self.queue = {};

            $.ajax( {
                url: self.responderUrl,
                type: 'POST',
                data: data
,
                success: function( result ) {
                    self.sendRequest = false;
                    self.saveQueue();
                },
                
                dataType: 'json'
            } );
        }
    },
    
    save : function( changes )
    {
        var self = this;

        var data = {
                    command: 'questionPages',
                    question: self.name,
                    required: self.required,
                    onJoin: self.onJoin,
                    onEdit: self.onEdit,
                    onView: self.onView,
                    onSearch: self.onSearch, 
               };


        if ( !self.sendRequest )
        {
            self.sendRequest = true;
                   
//            if ( changes )
//            {
//                data.changed = changes;
//            }

            $.ajax( {
                url: self.responderUrl,
                type: 'POST',
                data: data
,
                success: function( result ) {
                    self.sendRequest = false;
                    self.saveQueue();
                },
                dataType: 'json'
            } );
        }
        else
        {
            self.queue = data;
        }
    },

    change : function( element )
    {
        var self = this;

        var element = $(element);
        //element.toggleClass('ow_checkbox_cell_marked');

        if( element.hasClass('ow_checkbox_cell_marked') )
        {
            element.removeClass('ow_checkbox_cell_marked');
            element.addClass('ow_checkbox_cell');
        }
        else
        {
            element.addClass('ow_checkbox_cell_marked');
            element.removeClass('ow_checkbox_cell');
        }

        // -- animation --
        var td = element.parents('td:eq(0)');
        td.addClass('selected');
        td.removeClass('not_selected');
        setTimeout( function(){
                td.removeClass('selected');
                td.addClass('not_selected');
            }, 200);
        // --------------
        
        var changes = '';
        
        switch( true )
        {
            case  ( element.hasClass('required') ) :
                self.setRequired( element.hasClass('ow_checkbox_cell_marked') );
                changes = 'required';
            break;

            case ( element.hasClass('on_join') ) :
                self.setJoin( element.hasClass('ow_checkbox_cell_marked') );
                changes = 'onJoin';
            break;

            case ( element.hasClass('on_edit') ) :
                self.setEdit( element.hasClass('ow_checkbox_cell_marked') );
                changes = 'onEdit';
            break;

            case ( element.hasClass('on_search') ) :
                self.setSearch( element.hasClass('ow_checkbox_cell_marked') );
                changes = 'onSearch';
            break;

            case ( element.hasClass('on_view') ) :
                self.setView( element.hasClass('ow_checkbox_cell_marked') );
                changes = 'onView';
            break;
        }

        self.save(changes);
    }
}

var indexQuestions = function( $params )
{
    var self = this;
    var $questionAddUrl = $params.questionAddUrl;
    this.responderUrl = $params.ajaxResponderUrl;

    var $questionDiv = $('.ow_admin_profile_questions_list_div');
    var $questionTable = $('.ow_admin_profile_questions_list');
    var $questionTr = $questionTable.find('.question_tr');
    this.oldSection = undefined;

    var $questionPagesMoldels = [];
    var $questionAccountTypesMoldels = [];

    if ( $params.questions )
    {
        $.each( $params.questions, function( key, item ) {
            var model = new questionPagesModel( item );
            model.setResponderUrl(self.responderUrl);
            $questionPagesMoldels.push(model);

            if ( $params.accountTypes && !$.isEmptyObject($params.accountTypes) && !item.disable_account_type )
            {
                var accountModel = new questionAccountTypesModel( item, $params.accountTypes );
                accountModel.setResponderUrl(self.responderUrl);
                $questionAccountTypesMoldels.push(accountModel);
            }
        } );
    }

    $('.account_type_th').bind( "mouseover", function(){ $(this).find('.account_type_menu').show();} )
    .bind( "mouseout", function(){ $(this).parent('tr:eq(0)').find('.account_type_menu').hide();} );
    
    $('td[data-accounttype]').bind( "mouseover", function(){ 
        var accountType = $(this).data('accounttype');
        $("th[data-accounttype="+accountType+"]").find('.account_type_menu').show();
    } ).bind( "mouseout", function(){ 
        var accountType = $(this).data('accounttype');
        $("th[data-accounttype="+accountType+"]").find('.account_type_menu').hide();
    } );
    
    OW.bind( "admin.add_account_type", function( params ) {
            
            if ( params && params.result.add == true && params.accountTypeName )
            {
                var floatbox = OW.getActiveFloatBox();
                
                if ( floatbox )
                {
                    floatbox.close();
                }
                
                OW.info(OW.getLanguageText('admin', 'questions_account_type_was_added'));
                
                window.location.reload();
            }
            else
            {
                OW.error(OW.getLanguageText('admin', 'questions_account_type_added_error'));
            }
        });

    OW.bind( "admin.update_account_type", function( params ) {
            if ( params.result.reorder && params.result.orderList )
            {
                var temp = [];

                $.each(params.result.orderList, function(key, value) {
                    temp.push({v:value, k: key});
                });

                temp.sort(function(a,b){
                   if(a.v > b.v){ return 1}
                    if(a.v < b.v){ return -1}
                      return 0;
                });

                $.each(temp,
                    function( key, object )
                    {
                        $.each( $("."+object.k), function( key, item ) {
                            $(item).parents("tr:eq(0)").append($(item).parents("td:eq(0)"));
                        })

                        var th = $('th input[value=' + object.k + ']').parents('th:eq(0)');
                        th.parents('tr:eq(0)').append(th);
                    }
                );

                $.each( $(".account_type_empty"), function( key, item ) {
                            $(item).parents("tr:eq(0)").append($(item).parents("td:eq(0)"));
                })

                var th = $('th .add_account_type').parents('th:eq(0)');
                th.parents('tr:eq(0)').append(th);
            }

            if ( params && params.result.update == true && params.accountTypeName )
            {
                var input = $("form[name=editAccountType] textarea:eq(0)");
                var label = $("th input[type=hidden][value="+params.accountTypeName+"]").parents("th:eq(0)").find("div.table_content_block");
                label.html(input.val());

                var floatbox = OW.getActiveFloatBox();
                
                if ( floatbox )
                {
                    floatbox.close();
                }

                OW.info(OW.getLanguageText('admin', 'questions_account_type_was_updated'));
            }
        });

    $('.question_values').click( function()  {$(this).parents('center:eq(0)').next('div').toggle();} );

    $('a.question_edit_button').click( function()  {

        var questionId = $(this).parents("tr:eq(0)").find("input[type='hidden']").val();

        if ( questionId )
        {
            OW.ajaxFloatBox('ADMIN_CMP_EditQuestion', [questionId], {
                width: '700px',
                title: OW.getLanguageText('admin', 'questions_edit_profile_question_title')
            } );
        }
    } );
    
    $('a.parent_question_link').click( function()  {

        var questionId = $(this).attr('parentId');

        if ( questionId )
        {
            OW.ajaxFloatBox('ADMIN_CMP_EditQuestion', [questionId], {
                width: '700px',
                title: OW.getLanguageText('admin', 'questions_edit_profile_question_title')
            } );
        }
    } );
    
    
    $('a.add_account_type').click( function()  {
        //window.location = $questionAddUrl
        OW.ajaxFloatBox('ADMIN_CMP_AddAccountType', [], {
            width: '500px',
            title: OW.getLanguageText('admin', 'questions_add_account_type_title')
        } );
    } );
    
    $('a.question_edit_account_type_button').click( function()  {
        var th = $(this).parents("th:eq(0)");
        var input = th.find("input[type=hidden]");
        
        if ( input.length > 0 && input.val() )
        {
            OW.ajaxFloatBox('ADMIN_CMP_EditAccountType', [input.val()], {
                width: '500px',
                title: OW.getLanguageText('admin', 'questions_edit_account_type_title')
            } );
        }
    } );

   $('a.question_delete_account_type_button').click(
        function(event) {

            var th = $(this).parents("th:eq(0)");
            var input = th.find("input[type=hidden]");

            if( confirm(OW.getLanguageText('admin', 'questions_delete_account_type_confirmation')) )
            {
                self.sendRequest = true;
                $.ajax( {
                    url: self.responderUrl,
                    type: 'POST',
                    data:
                       {
                            command: 'deleteAccountType',
                            accountType: input.val()
                       },
                    success: function( result ) {
                        self.sendRequest = false;

                        if ( result && result.result == 'success' )
                        {
                            OW.info(result['message']);
                            window.location.reload();
                        }
                        else if ( result['message'] )
                        {
                            OW.error(result['message']);
                        }
                    },
                    dataType: 'json'
                } );
            }
            
            event.preventDefault();
            event.stopPropagation();
            
        } );
            
    //$('a.question_edit_account_type_button').parents('th').bind( "mouseover", function(){ $(this).find('a.question_edit_account_type_button').css('visibility', 'visible');} )
    //.bind( "mouseout", function(){$(this).find('a.question_edit_account_type_button').css('visibility', 'hidden');} );
    
    $('input.add_new_question_button').click( function()  {
        //window.location = $questionAddUrl
        OW.ajaxFloatBox('ADMIN_CMP_AddQuestion', [], {
            width: '700px',
            title: OW.getLanguageText('admin', 'questions_add_profile_question_title')
        } );
    } );
    
    $('input.add_new_section_button').on( 'click', function()  {
        OW.ajaxFloatBox('ADMIN_CMP_AddQuestionSection', [], {
            width: '600px',
            title: OW.getLanguageText('admin', 'questions_profile_question_sections_title')
        } );
    } );

    $('.question_delete_button').click(
        function()
        {
            var input = $(this).parents(".quest_buttons:eq(0)").find("input[type='hidden']");
            var $questionId = input.val();
            
            if( confirm(OW.getLanguageText('admin', 'questions_delete_question_confirmation_' + $questionId)) )
            {
                self.sendRequest = true;
                $.ajax( {
                    url: self.responderUrl,
                    type: 'POST',
                    data:
                       {
                            command: 'deleteQuestion',
                            questionId: $questionId
                       },
                    success: function( result ) {
                        self.sendRequest = false;
                        
                        if ( result['result'] == 'success' )
                        {
                            input.parents('tr:eq(0)').remove();
                            
                            if ( result['deleteList'] )
                            {
                                $.each(result['deleteList'], function( key, item ) { 
                                    $('tr.question_tr[question_name='+item+']').remove();
                                } );
                            }
                            
                            var $question_tr = $questionTable.find(".question_tr:not(.no_question)");
                            $question_tr.removeClass('ow_alt1');
                            $question_tr.removeClass('ow_alt2');

                            $questionTable.find('.question_tr:not(.no_question):odd').addClass('ow_alt2');
                            $questionTable.find('.question_tr:not(.no_question):even').addClass('ow_alt1');
                            
                            OW.info(result['message']);
                        }
                    },
                    dataType: 'json'
                } );
            }
        } );

    $('.section_delete_button').click(
        function() {
    
            var th = $(this).parents("table:eq(0)");
            var name = th.attr('sectionName');
            
            if ( self.sendRequest == true )
            {
                return;
            }
            
            
            $.ajax( {
                    url: self.responderUrl,
                    type: 'POST',
                    dataType: 'json',
                    data:
                       {
                            command: 'findNearestSection',
                            sectionName: name
                       },
                   })
            .done(
                function ( result ) {
              
                    self.sendRequest == false;
              
                    var message = OW.getLanguageText('admin', 'questions_delete_section_confirmation');

                    if ( result.message )
                    {
                        message = result.message;
                    }

                    if( confirm( message ) )
                    {
                        self.sendRequest = true;
                        $.ajax( {
                            url: self.responderUrl,
                            type: 'POST',
                            data:
                               {
                                    command: 'deleteSection',
                                    sectionName: name
                               },
                            success: function( result ) {
                                self.sendRequest = false;

                                if ( result && result.result == 'success' )
                                {
                                    if ( result.moveTo )
                                    {
                                        var moveToSection = $questionDiv.find("table[sectionName="+result.moveTo+"]");
                                        var questions = th.find("tr.question_tr:not(.no_question)");
                                        moveToSection.append(questions);
                                    }

                                    th.remove();
                                    OW.info(result['message']);
                                }
                            },
                            dataType: 'json'
                        } );
                    }
                }        
            )
            .always(function() { self.sendRequest == false }); 
            
        } );

    $questionTable.find("tr.question_section_tr, tr.question_tr").bind( "mouseover", function(){$(this).find(".delete_edit_buttons a").css('visibility', 'visible');} )
	   				 .bind( "mouseout", function(){$(this).find(".delete_edit_buttons a").css('visibility', 'hidden');} );

    $(".edit_sectionNameLang").click( function() {
         var $tr = $(this).parents("tr:eq(0)");
         var $element = $tr.find(".section_value .ow_section_label");
         var $name = $(this).parents(".ow_admin_profile_questions_list:eq(0)").attr("sectionName");
         var $lang_key = 'questions_section_' + $name + '_label';

         window.editLangValue('base', $lang_key, function($data)
         {
             var $value = $.trim($data.value);

             $($element).text($value)
         } );

     } );

     $questionDiv.sortable({

       items: '.ow_admin_profile_questions_list',
       cancel: 'no_section',
       cursor: 'move',
       tolerance: 'pointer',
       handle: '.question_section_tr',
       placeholder: 'section_placeholder ow_table_2 ow_smallmargin ow_admin_content',
       forcePlaceholderSize: true,

       update: function(event, ui)
       {
            var order = {};

            $questionDiv.find('.ow_admin_profile_questions_list:not(.no_section)').each(function(ord, o){
                order[$(o).attr('sectionName')] = ord;
            });

            $.ajax( {
                    url: self.responderUrl,
                    type: 'POST',
                    data: {
                               command: 'sortSection',
                               sectionOrder:JSON.stringify(order)
                           },
                    dataType: 'json'
                } );
        },

        start: function(event, ui)
        {
            //$(ui.placeholder).append('<table class="ow_table_2 ow_smallmargin"><tr><td colspan="9"><div style="width:869px;"></div></td></tr></table>');
            $questionDiv.sortable( 'refreshPositions' );
        },

        stop: function(event, ui) {
        },

        helper: function(event, ui)
        {
            var itemWidth = ui.outerWidth();
            if (itemWidth > 160)
            {
                var k = 160 / ui.outerWidth();
                var offset = k * (event.pageX - ui.position().left);
                $(this).sortable( 'option', 'cursorAt', {left: offset} );
            }

            return $('<div class="ow_dnd_helper" style="width: 180px;height: 30px; text-align:center; vertical-align:middle;"></div>');
        }

     });

     $questionTable.not(".about_my_match").sortable(
     {
       items: '.question_tr',
       cursor: 'move',
       placeholder: 'question_placeholder',
       snap: true,
       snapToleranse: 50,
       forcePlaceholderSize: true,
       connectWith: '.ow_admin_profile_questions_list:not(.no_section):not(.about_my_match)',

        update: function(event, ui) {

             var newSection = ui.item.parents(".ow_admin_profile_questions_list:eq(0)");

             if( ui.sender )
             {
                  var orderOld = {};

                  ui.sender.find('.question_tr:not(.no_question)').each(function(order, o){
                            orderOld[$(o).attr('question_name')] = order;
                            });

                  var $oldSectionName = ui.sender.attr("sectionName");

                  $.ajax( {
                            url: self.responderUrl,
                            type: 'POST',
                            data: {
                                       command: 'sortQuestions',
                                       sectionName: $oldSectionName,
                                       questionOrder:JSON.stringify(orderOld)

                                   },
                            dataType: 'json'
                        } );
               }

               var orderNew = {};

               newSection.find('.question_tr:not(.no_question)').each(function(order, o){
                    orderNew[$(o).attr('question_name')] = order;
                });

               var $question_tr = $questionTable.find(".question_tr:not(.no_question)");
               $question_tr.removeClass('ow_alt1');
               $question_tr.removeClass('ow_alt2');

               $questionTable.find('.question_tr:not(.no_question):odd').addClass('ow_alt2');
               $questionTable.find('.question_tr:not(.no_question):even').addClass('ow_alt1');

               var $newSectionName =  newSection.attr("sectionName");

               $.ajax( {
                    url: self.responderUrl,
                    type: 'POST',
                    data: {
                               command: 'sortQuestions',
                               sectionName: $newSectionName,
                               questionOrder:JSON.stringify(orderNew)

                           },
                    dataType: 'json'
                } );

        },

        start: function(event, ui)
        {
            
        },

        helper: function(event, ui)
        {
            this.oldSection = ui.parents(".ow_admin_profile_questions_list:eq(0)");
            var itemWidth = ui.outerWidth();
            if (itemWidth > 160)
            {
                var k = 160 / ui.outerWidth();
                var offset = k * (event.pageX - ui.position().left);
                $(this).sortable( 'option', 'cursorAt', {left: offset} );
            }

            return $('<div class="ow_dnd_helper" style="width: 180px; height: 30px; text-align:center; vertical-align:middle;"></div>');
        }

    });

     $questionDiv.find(".about_my_match").sortable(
     {
       items: '.question_tr',
       cursor: 'move',
       placeholder: 'question_placeholder',
       snap: true,
       snapToleranse: 50,
       forcePlaceholderSize: true,

        update: function(event, ui) {

             var newSection = ui.item.parents(".ow_admin_profile_questions_list:eq(0)");

             if( ui.sender )
             {
                  var orderOld = {};

                  ui.sender.find('.question_tr:not(.no_question)').each(function(order, o){
                            orderOld[$(o).attr('question_name')] = order;
                            });

                  var $oldSectionName = ui.sender.attr("sectionName");

                  $.ajax( {
                            url: self.responderUrl,
                            type: 'POST',
                            data: {
                                       command: 'sortQuestions',
                                       sectionName: $oldSectionName,
                                       questionOrder:JSON.stringify(orderOld)

                                   },
                            dataType: 'json'
                        } );
               }

               var orderNew = {};

               newSection.find('.question_tr:not(.no_question)').each(function(order, o){
                    orderNew[$(o).attr('question_name')] = order;
                });

               var $question_tr = $questionTable.find(".question_tr:not(.no_question)");
               $question_tr.removeClass('ow_alt1');
               $question_tr.removeClass('ow_alt2');

               $questionTable.find('.question_tr:not(.no_question):odd').addClass('ow_alt2');
               $questionTable.find('.question_tr:not(.no_question):even').addClass('ow_alt1');

               var $newSectionName =  newSection.attr("sectionName");

               $.ajax( {
                    url: self.responderUrl,
                    type: 'POST',
                    data: {
                               command: 'sortQuestions',
                               sectionName: $newSectionName,
                               questionOrder:JSON.stringify(orderNew)

                           },
                    dataType: 'json'
                } );

        },

        start: function(event, ui)
        {

        },

        helper: function(event, ui)
        {
            this.oldSection = ui.parents(".ow_admin_profile_questions_list:eq(0)");
            var itemWidth = ui.outerWidth();
            if (itemWidth > 160)
            {
                var k = 160 / ui.outerWidth();
                var offset = k * (event.pageX - ui.position().left);
                $(this).sortable( 'option', 'cursorAt', {left: offset} );
            }

            return $('<div class="ow_dnd_helper" style="width: 180px; height: 30px; text-align:center; vertical-align:middle;"></div>');
        }

    }); 
}



