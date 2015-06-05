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
        // remove all wrappers
        var $clonedWrapper = $(suitUpBlock).clone(false);
 
        $clonedWrapper.find(".owm_attach_preview").each(function() {
            $(this).replaceWith($(this).find(".owm_preview_content").html());
        });

        textarea.value = $clonedWrapper.html();
        console.log(textarea.value);
    };

    /**
     * Get media wrapper
     * 
     * @param string wrapperId
     * @param string type
     * @param string content
     * @return string
     */
    var getMediaWrapper = function(wrapperId, type, content)
    {
        content = content || '';
        var preloader = !content  ? 'owm_preloader' : '';

        return '<div id="attach_preview_' + wrapperId + '" contenteditable="false" style="min-height:50px;margin:0;padding:0;background-color:#c0c0c0;position:relative" class="owm_attach_preview owm_std_margin_bottom ' + type + ' ' + preloader + '">' 
                    + '<a class="owm_attach_preview_close" href="#"></a>' 
                    + '<div class="owm_preview_content" style="overflow:hidden">' + content + '</div>' + 
                '</div>' + (!content ? '<br />' : '');
    }

    /**
     * Generate media wrapper id
     * 
     * @return integer
     */
    var generateMediaWrapperId = function()
    {
        return Math.random().toString(36).slice(2);
    }

    /**
     * Init media wrappers close buttons
     * 
     * @param object suitUpBlock
     * @param object textarea
     * @return void
     */
    var initMediaWrappersCloseButtons = function(suitUpBlock, textarea)
    {
       $(suitUpBlock).find(".owm_attach_preview_close").unbind().on("click", function(e){
           e.preventDefault();
           $(this).parent().remove();
           saveHtml(textarea, suitUpBlock);
       }); 
    }

    /**
     * Apply media wrappers
     * 
     * @param object suitUpBlock
     * @param object textarea
     * @return void
     */
    var applyMediaWrappers = function(suitUpBlock, textarea)
    {
        var content = '';
        var mediaWrapper = '';

        // find and wrapp all images
        $(suitUpBlock).find("img").each(function() {
            var $parent = $(this).parent();

            if ($parent.is("a")) {
                content = $("<div />").append( $parent.clone() ).html();
                mediaWrapper = getMediaWrapper(generateMediaWrapperId(), "owm_photo_attach_preview", content);

                $parent.replaceWith(mediaWrapper);
            }
            else {
                content = $("<div />").append( $(this).clone() ).html();
                mediaWrapper = getMediaWrapper(generateMediaWrapperId(), "owm_photo_attach_preview", content);

                $(this).replaceWith(mediaWrapper);
            }
        });

        initMediaWrappersCloseButtons(suitUpBlock, textarea);
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
        controls: [ 'italic', 'bold', 'undeline', 'link', 'image' ],

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
                var wrapperId = '';
 
                // create the upload image input
                var $uploadImage = $('<input type="file" class="suitup_upload_image"  style="display:none" />' ).html5_upload({
                    url: $.suitUp.getImageUploadUrl(),
                    sendBoundary: window.FormData || $.browser.mozilla,
                    fieldName : 'file',
                    extraFields : {
                        'command' : 'image-upload'
                    },
                    onStartOne: function(event, name, number, total) 
                    {
                        wrapperId = generateMediaWrapperId();

                        // add a new media wrapper
                        suitUpBlock.focus();
                        document.execCommand('insertHTML', false, getMediaWrapper(wrapperId, "owm_photo_attach_preview"));                       
                        initMediaWrappersCloseButtons(suitUpBlock, textarea);

                        return true;
                    },
                    onFinishOne: function(event, response, name, number, total) 
                    {
                        var result = jQuery.parseJSON(response);
                        var $wrapper = $(suitUpBlock).find("#attach_preview_" + wrapperId);
 
                        // show an error message
                        if ( typeof result.error_message != "undefined" && result.error_message )
                        {
                           OWM.message(result.error_message, 'error');
                           $wrapper.remove();
                           return;
                        }
 
                        // show the image
                        var content = '<a href="' + result.file_url + '" target="_blank"><img src="' + result.file_url + '"></a>';
                        $wrapper.removeClass("owm_preloader").find(".owm_preview_content").html(content);

                        saveHtml(textarea, suitUpBlock);
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
                        var oldSelection = $.suitUp.getSelection();

                        var floatBox = OWM.ajaxFloatBox("BASE_MCMP_LinkSelect", [{ "linkText" : $.suitUp.getSelectedText() }], {
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
 */
$.fn.suitUp = function( controls, imageUploadUrl ) 
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

                    controlsBlock = create( 'div', {
                            className: 'owm_suitup-controls'
                    }).appendTo( mainBlock ),

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

            applyMediaWrappers(containerBlock, that);
            mainBlock.insertBefore(that);

    });
};
})( window, document, jQuery );
