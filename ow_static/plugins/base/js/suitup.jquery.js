"use strict";
( function( win, doc, $ ) 
{ 
    //-- protected functions -- //

    /**
     * Create a tag
     * 
     * @return object
     */
    var create = function(tagName, props) 
    {
        return $($.extend( doc.createElement( tagName ), props ));
    };

    /**
     * Save html
     * 
     * @param object suitUpBlock
     * @param string type
     * @return void
     */
    var saveHtml = function(textarea, suitUpBlock)
    {
        textarea.value = suitUpBlock.html();
    };

    /**
     * Wyswyg
     */
    $.suitUp = {
        /**
         * Default controls
         * 
         * @var array
         */
        controls: [ 'italic', 'bold', 'undeline', 'link', 'image', 'video' ],

        /**
         * Image upload url
         * 
         * @var string
         */
        imageUploadUrl: '',

        /**
         * Commands
         * 
         * @var object
         */
        commands: {
        },

        /**
         * Custom buttons
         * 
         * @var object
         */
        custom: {
            /**
             * Image uploader
             * 
             * @param object textarea
             * @param object suitUpBlock
             * @return void
             */
            image: function(textarea, suitUpBlock) 
            {
                var oldSelection = '';
 
                // create the upload image input
                var $uploadImage = $('<input type="file" class="suitup_upload_image" style="display:none" accept="image/*" />' ).html5_upload({
                    url: $.suitUp.getImageUploadUrl(),
                    sendBoundary: window.FormData || $.browser.mozilla,
                    fieldName : 'file',
                    extraFields : {
                        'command' : 'image-upload'
                    },
                    onStartOne: function(event, name, number, total) 
                    {
                        suitUpBlock.focus();
                        oldSelection = $.suitUp.getSelection();
                        suitUpBlock.addClass("owm_preloader");
                        return true;
                    },
                    onFinishOne: function(event, response, name, number, total) 
                    {
                        suitUpBlock.removeClass("owm_preloader");
                        var result = jQuery.parseJSON(response);
 
                        // show an error message
                        if ( typeof result.error_message != "undefined" && result.error_message )
                        {
                           OWM.message(result.error_message, 'error');
                           return;
                        }
 
                        suitUpBlock.focus();
                        $.suitUp.restoreSelection(oldSelection);
 
                        // show the image
                        var content = '<br /><a href="' + result.file_url + 
                                '" target="_blank"><img src="' + result.file_url + '"></a><br /><br />';
 
                        document.execCommand('insertHTML', false, content);
                        saveHtml(textarea, suitUpBlock);
                    },
                    onError: function(event, name, error) {
                        suitUpBlock.removeClass("owm_preloader");
                    }
                });

                $($uploadImage).insertAfter(suitUpBlock);

                // create a new button
                return create("a", {
                    className: "owm_suitup-control",
                    href: "javascript://"
                }).attr({
                    "data-command": "insertImage"
                }).on("click", function() {
                    // show the file choose window
                    $uploadImage.trigger("click");
                });
            },
            link: function(textarea, suitUpBlock) 
            {
                return create("a", {
                    className: "owm_suitup-control",
                    href: "javascript://"
                }).attr({
                    "data-command": "createlink"
                }).on("click", function() {
                    if (!$.suitUp.hasSelectedNodeParent("a")) {
                        suitUpBlock.focus();
                        var oldSelection = $.suitUp.getSelection();

                        var floatBox = OWM.ajaxFloatBox("BASE_MCMP_InsertLink", [{ "linkText" : $.suitUp.getSelectedText() }], {
                            "title" : OW.getLanguageText('base', 'ws_button_label_link'),
                            "scope" : {
                                "success" : function(data) {
                                    floatBox.close();
                                    suitUpBlock.focus();
                                    $.suitUp.restoreSelection(oldSelection);

                                    document.execCommand('insertHTML', false, '<a href="' + data.link + '" rel="nofollow" target="_blank">' + data.title + '</a>');
                                    saveHtml(textarea, suitUpBlock);
                                }
                            }
                        });
                    } else {
                        doc.execCommand('unlink', false, null);
                        saveHtml(textarea, suitUpBlock);
                    }
                });
            },
            video: function(textarea, suitUpBlock) 
            {
                return create("a", {
                    className: "owm_suitup-control",
                    href: "javascript://"
                }).attr({
                    "data-command": "insertVideo"
                }).on("click", function() {
                    suitUpBlock.focus();
                    var oldSelection = $.suitUp.getSelection();

                    var floatBox = OWM.ajaxFloatBox("BASE_MCMP_InsertVideo", [], {
                         "title" : OW.getLanguageText('base', 'ws_button_label_video'),
                         "scope" : {
                            "success" : function(data) {
                                $.ajax({
                                    url: $.suitUp.embedUrl,
                                    data: { url: data.link },
                                    cache: false,
                                    success: function(response) {
                                        var data = jQuery.parseJSON(response);

                                        if (typeof data.type == "undefined" 
                                            || typeof data.html == "undefined" 
                                            || data.type != "video") {

                                            OWM.message(OW.getLanguageText('base', 'ws_error_video'), 'error');
                                            return;
                                        }

                                        floatBox.close();
                                        suitUpBlock.focus();
                                        $.suitUp.restoreSelection(oldSelection);

                                        document.execCommand('insertHTML', false, '<br />' + data.html + '<br /><br />');
                                        saveHtml(textarea, suitUpBlock);
                                    },
                                    'error' : function() {
                                        OWM.message(OW.getLanguageText('base', 'ws_error_video'), 'error');
                                    }
                                });
                            }
                         }
                     });
                });
            }
        },

        /**
         * Get selected text in editable area
         *
         * @return string
         */
        getSelectedText: function() 
        {
            return this.getSelection().toString();
        },

        /**
         * Get image upload url
         * 
         * @return string
         */
        getImageUploadUrl: function() 
        {
            return this.imageUploadUrl;
        },

        /**
         * Get current selection
         * 
         * @return object
         */
        getSelection: function() 
        {
            var range;

            if (win.getSelection) {
                try {
                    range = win.getSelection().getRangeAt( 0 );
                } catch(e) {
                  
                }
            } else if (doc.selection) { 
                range = doc.selection.createRange();  
            }

            return range;
        },

        /**
         * Restore selection
         * 
         * @param object range
         * @retrun void
         */
        restoreSelection: function(range) 
        {
            var s;

            if (range) {
                if (win.getSelection) {
                    s = win.getSelection();
                    if (s.rangeCount > 0) {
                        s.removeAllRanges();
                    }

                    s.addRange(range);
                } else if (doc.createRange) {
                    win.getSelection().addRange(range);
                } else if (doc.selection) {
                    range.select();
                }
            }
        },

        /**
         * Get selected node
         * 
         * @return object
         */
        getSelectedNode: function() 
        {
            if (doc.selection) {
                return doc.selection.createRange().parentElement();
            } else {
                var selection = win.getSelection();
 
                if (selection.rangeCount > 0) {
                    return selection.getRangeAt(0).endContainer;
                }
            }
        },

        /**
         * Has selected node parent
         * 
         * @param strign tagName
         * @return boolean
         */
        hasSelectedNodeParent: function(tagName) 
        {
            var node = this.getSelectedNode(), has = false;
            tagName = tagName.toUpperCase();
 
            while (node && node.tagName !== 'BODY') {
                if (node.tagName === tagName) {
                    has = true;
                    break;
                }
 
                node = node.parentNode;
            }

            return has;
        }
    };

/**
 * Suitup jquery plugin
 * 
 * @param array controls
 * @param string imageUploadUrl
 * @param string embedUrl
 */
$.fn.suitUp = function(controls, imageUploadUrl, embedUrl) 
{
    var suitUp = $.suitUp,
            lastSelectionRange,
            lastSelectionElement,
            commands = $.suitUp.commands,
            custom   = $.suitUp.custom,
            restoreSelection = suitUp.restoreSelection;

    controls = controls || $.suitUp.controls;
    controls = controls instanceof Array ? controls : Array.prototype.slice.call( arguments ); // IE changes the arguments object when one of the arguments is redefined

    $.suitUp.imageUploadUrl = imageUploadUrl || '';
    $.suitUp.embedUrl = embedUrl || '';
 
    return this.each( function() {
            var that = this,
                    self = $( this ).hide(),
                    buttonControls,
                    selectControls,
                    typeofCommandValue,
                    commandValue,
                    select,

                    mainBlock = create( 'div', {
                            className: 'owm_suitup'
                    }),

                    containerBlock = create( 'div', {
                            className: 'owm_suitup-editor',
                            contentEditable: true
                    }).keyup( function(){ 
                            updateTextarea();
                            highlightActiveControls();
                    }).focus( function(){
                            lastSelectionElement = this;
                            //document.execCommand('styleWithCSS', null, false);
                    }).mouseup( function(){
                            highlightActiveControls();
                    })
                    .html( that.value )
                    .appendTo( mainBlock ),

                    controlsBlock = create( 'div', {
                            className: 'owm_suitup-controls'
                    }).appendTo( mainBlock ),

                    updateTextarea = function() {
                         saveHtml(that, containerBlock);
                    },

                    highlightActiveControls = function() {
                            buttonControls = buttonControls || $( 'a.owm_suitup-control', controlsBlock );
                            buttonControls
                             .removeClass( 'active' )
                             .each( function(){
                                    var self = $( this ),
                                            command = self.data( 'command' ),
                                            value = self.data( 'value' );

                                    try {
                                            value = value ? value.replace( '<', '' ).replace( '>', '' ) : value; // for formatBlock
                                            doc.queryCommandValue( command ) === ( value || 'true' ) && self.addClass( 'active' );
                                    } catch( e ) {}
                                    try {
                                            doc.queryCommandState( command ) && self.addClass( 'active' );
                                    } catch( e ) {}
                            });

                            selectControls = selectControls || $( 'select.owm_suitup-control', controlsBlock );
                            selectControls.each( function(){
                                    var self = $( this ),
                                            command = self.data( 'command' ),
                                            value = doc.queryCommandValue( command ),
                                            option = self.children( 'option' ).filter( function() {
                                                    return value && this.value.toLowerCase() === value.toLowerCase();
                                            });

                                    if( option.length ) {
                                            this.value = option.val();
                                    }
                            });
                    }

            for( var splittedControl, i = 0, control = controls[ 0 ]; i < controls.length; control = controls[ ++i ] ) {
                    splittedControl = control.split( '#' );
                    control = splittedControl[ 0 ];
                    commandValue = splittedControl[ 1 ];

                    if( control === '|' ) {
                            create( 'span', {
                                    className: 'owm_suitup-separator'
                            }).appendTo( controlsBlock );

                    } else if( control in custom ) {
                        custom[ control ]( that, containerBlock ).appendTo( controlsBlock );
                    } else {
                            commandValue = commandValue || commands[ control ] || null;
                            typeofCommandValue = typeof commandValue;

                            if( commandValue && typeofCommandValue === 'object' ) {
                                    select = create( 'select', {
                                            className: 'owm_suitup-control'
                                    })
                                    .attr( 'data-command', control )
                                    .appendTo( controlsBlock )
                                    .on( 'change', { command: control }, function( event ) {
                                            var command = event.data.command;
                                            doc.execCommand( command, null, this.value );
                                            updateTextarea();
                                    });

                                    $.each( commandValue, function( displayName, commandValue ) {
                                            create( 'option', {
                                                    value: commandValue
                                            }).html( displayName )
                                            .appendTo( select );
                                    });
                            } else {
                                    create( 'a', {
                                            href: '#',
                                            className: 'owm_suitup-control'
                                    })
                                    .attr({
                                            'data-command': control,
                                            'data-value': typeofCommandValue === 'function' ? '_DYNAMIC_' : commandValue
                                    })
                                    .appendTo( controlsBlock )
                                    .on( 'click', { command: control, value: commandValue, typeofValue: typeofCommandValue }, function( event ){
                                            var command = event.data.command,
                                                    value = event.data.value,
                                                    typeofValue = event.data.typeofValue,
                                                    resultValue;

                                            if( lastSelectionElement !== containerBlock[ 0 ] || !lastSelectionRange ) {
                                                containerBlock.focus();
                                            }

                                            if( typeofValue === 'function' ) {                                               
                                                    lastSelectionRange = getSelection();
                                                    value( function( resultValue ) {
                                                            lastSelectionElement.focus();
                                                            restoreSelection( lastSelectionRange );
                                                            doc.execCommand( command, null, resultValue );
                                                            updateTextarea();
                                                    });
                                            } else {
                                                    resultValue = value;
                                                    doc.execCommand( command, null, resultValue );
                                                    updateTextarea();
                                                    highlightActiveControls();
                                            }

                                            return false;
                                    });
                            }

                    } 
            }

            mainBlock.insertBefore(that);
    });
};
})( window, document, jQuery );
