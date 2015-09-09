(function( window, $ ) {'use strict';

    $.event.props.push('dataTransfer');

    var _vars = $.extend({}, (window.ajaxFileUploadParams || {}), {
        isHTML5: window.hasOwnProperty('FormData'),
        fileType: ['image/jpeg', 'image/png', 'image/gif'],
        files: [],
        UPLOAD_THREAD_COUNT: 3
    }),
    _elements = {},
    _methods = {
        isAvailableFileSize: function( size )
        {
            return +size <= _vars.maxFileSize;
        },
        isAvailableFileType: function( fileType )
        {
            return fileType.length && _vars.fileType.indexOf(fileType.toLowerCase()) !== -1;
        },
        createSlot: function()
        {
            var slotPrototype = _elements.slotPrototype.clone(true);
            var id = 'slot-' + (++_elements.slotCounter);
            
            slotPrototype.attr('id', id).appendTo(_elements.slotArea);
            _elements.slotData[id] = slotPrototype;
            
            return id;
        },
        destroySlot: function( slotId, id )
        {
            if ( !_methods.isSlotExist(slotId) )
            {
                return;
            }
            
            _methods.afterUploadTask();
            
            _elements.slotData[slotId].animate({opacity: '0'}, 300, function()
            {
                if ( id != null )
                {
                    $.ajax(
                    {
                        url: _vars.deleteAction,
                        data: {id: id},
                        cache: false,
                        type: 'POST'
                    });
                }
                
                _elements.slotData[slotId].remove();

                delete _elements.slotData[slotId];
                delete _elements.descCache[slotId];
                delete _elements.relations[slotId];
            });
        },
        updateSlot: function( slotId, fileUrl, id, filename )
        {
            if ( !slotId || !fileUrl || !id || !_methods.isSlotExist(slotId) )
            {
                return;
            }
            
            _methods.afterUploadTask();
            
            var slot = _elements.slotData[slotId];
            
            var rotateId = 'rotate[' + id + ']';
            slot.find('[name="rotate"]').attr({id: rotateId, name: rotateId});
            
            var descId = 'desc[' + id + ']';
            slot.find('textarea').attr({id: descId, name: descId});

            _elements.relations[slotId] = id;

            owForms['ajax-upload'].addElement(new OwFormElement(rotateId, rotateId));
            var descriptionElement = new OwFormElement(descId, descId);
            descriptionElement.setValue(filename);
            owForms['ajax-upload'].addElement(descriptionElement);

            slot.find('.ow_photo_preview_x').on('click', function()
            {
                _methods.destroySlot(slotId, id);
            });
            slot.find('.ow_photo_preview_rotate').on('click', function()
            {
                var photo = slot.find('.ow_photo_preview_image'), _rotate;
                var rotate = (_rotate = photo.data('rotate')) === undefined ? 90 : _rotate;
                
                photo.rotate(rotate);
                slot.find('[name="' + rotateId + '"]').val(rotate);
                photo.data('rotate', rotate += 90);
            });
            
            var img = new Image();
            
            img.onload = function()
            {
                slot.find('.ow_photo_preview_image')
                    .hide(0, function()
                    {
                        this.style.backgroundImage = 'url(' + img.src + ')';
                        $(this).removeClass('ow_photo_preview_loading').fadeIn(300);
                        
                        OW.trigger('photo.onRenderUploadSlot', [_elements.descEditors[slotId]], slot);
                    });
            };
            img.src = fileUrl;
        },
        initHashtagEditor: function( slotId )
        {
            if ( !_methods.isSlotExist(slotId) )
            {
                return;
            }
            
            var slot = _elements.slotData[slotId];
            var editor = _elements.descEditors[slotId] = CodeMirror.fromTextArea(slot.find('textarea')[0], {mode: "text/hashtag", lineWrapping: true, extraKeys: {Tab: false}});

            editor.setValue(OW.getLanguageText('photo', 'describe_photo'));
            editor.on('blur', function( editor )
            {
                var value = editor.getValue().trim(), lineCount;
                
                if ( value.length === 0 || value === OW.getLanguageText('photo', 'describe_photo') )
                {
                    $(editor.display.wrapper).addClass('invitation');
                    editor.setValue(OW.getLanguageText('photo', 'describe_photo'));
                }
                else if ( (lineCount = editor.lineCount()) > 3 )
                {
                    editor.setLine(2, editor.getLine(2).substring(0, 20) + '...');

                    for ( var i = 3; i < lineCount; i++ )
                    {
                        editor.removeLine(3);
                    }
                }
                else
                {
                    var limit;
                    
                    switch ( lineCount )
                    {
                        case 1: limit = 70; break;
                        case 2: limit = 50; break;
                        case 3: limit = 20; break;
                    }

                    if ( value.length > limit )
                    {
                        editor.setValue(value.substring(0, limit) + '...');
                    }
                }
                
                editor.setSize('100%', 58 + 'px');
                
                _elements.descCache[slotId] = value;
                slot.find('.ow_photo_preview_image').removeClass('ow_photo_preview_image_active');
                
                if ( _elements.slotArea.find('.ow_photo_preview_image_active').length === 0 )
                {
                    _elements.slotArea.removeClass('ow_photo_preview_image_filtered');
                }
            });
            editor.on('focus', function( editor )
            {
                $(editor.display.wrapper).removeClass('invitation');
                
                if ( _elements.descCache.hasOwnProperty(slotId) )
                {
                    editor.setValue(_elements.descCache[slotId]);
                }
                else
                {
                    var value = editor.getValue().trim();
                
                    if ( value === OW.getLanguageText('photo', 'describe_photo') )
                    {
                        editor.setValue('');
                    }
                }
                
                var height = editor.doc.height;
                
                switch ( true )
                {
                    case height <= 42:
                        editor.setSize('100%', 58 + 'px');
                        break;
                    case height > 42 && height < 108:
                        editor.setSize('100%', height + 14 + 'px');
                        editor.scrollTo(0, height + 14);
                        break;
                    default:
                        editor.setSize('100%', '108px');
                        editor.scrollTo(0, 108);
                        break;
                }
                
                setTimeout(function()
                {
                    editor.setCursor(editor.lineCount(), 0);
                }, 1);
                 
                _elements.slotArea.addClass('ow_photo_preview_image_filtered');
                slot.find('.ow_photo_preview_image').addClass('ow_photo_preview_image_active');
            });
            editor.on('change', function( editor )
            {
                var height = editor.doc.height;
                
                switch ( true )
                {
                    case height <= 42:
                        editor.setSize('100%', 58 + 'px');
                        break;
                    case height > 42 && height < 108:
                        editor.setSize('100%', height + 14 + 'px');
                        break;
                    default:
                        editor.setSize('100%', '108px');
                        break;
                }
            });
            editor.setSize('100%', 58 + 'px');
        },
        isSlotExist: function( slotId )
        {
            return slotId && _elements.slotData.hasOwnProperty(slotId);
        },
        pushFileList: function( files )
        {
            if ( !files || !(_vars.isHTML5 && (files instanceof FileList)) )
            {
                return;
            }

            for ( var i = 0; i < files.length; i++ )
            {
                _vars.files.push(files.item(i));
            }

            if ( !_vars.isRuning )
            {
                _methods.setIsRuning();
                _methods.runAsyncUploadFile(_vars.UPLOAD_THREAD_COUNT);
            }
        },
        runAsyncUploadFile: function( count )
        {
            count = isNaN(+count) ? 1 : count;
            
            for ( var i = 0; i < count; i++ )
            {
                var file = _vars.files.shift();
                
                if ( file != null )
                {
                    _methods.uploadFile(file);
                }
            }
        },
        uploadFile: function( file )
        {
            var slotId;
            
            if ( _vars.isHTML5 )
            {
                var typeError;

                if ( _methods.isAvailableFileSize(file.size) && (typeError = _methods.isAvailableFileType(file.type)) )
                {
                    var formData = new FormData();

                    formData.append('file', file);

                    $.ajax(
                    {
                        isPhotoUpload: true,
                        url: _vars.actionUrl,
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        type: 'POST',
                        timeout: 60000,
                        beforeSend: function( jqXHR, settings )
                        {
                            slotId = _methods.createSlot();
                            //_methods.initHashtagEditor(slotId);
                        },
                        success: function( response, textStatus, jqXHR )
                        {
                            _methods.requestSuccess(response, slotId);
                        },
                        error: function( jqXHR, textStatus, errorThrown )
                        {
                            OW.error(textStatus + ': ' + file.name);
                            _methods.destroySlot(slotId);

                            throw textStatus;
                        },
                        complete: function( jqXHR, textStatus )
                        {
                            if ( textStatus === 'success' && jqXHR.responseText.length === 0 )
                            {
                                _methods.destroySlot(slotId);
                            }
                        }
                    });
                }
                else
                {
                    if ( typeError === undefined )
                    {
                        OW.error(OW.getLanguageText('photo', 'size_limit', {name: file.name, size: (_vars.maxFileSize / 1048576)}));
                    }
                    else
                    {
                        OW.error(OW.getLanguageText('photo', 'type_error', {name: file.name}));
                    }
                    
                    _methods.afterUploadTask();
                }
            }
            else
            {
                if ( file.search(/\.(?:jpe?g|png|gif)$/i) !== -1 )
                {
                    slotId = _methods.createSlot();

                    _elements.dropArea.off('click').on('click', function(){alert(OW.getLanguageText('photo', 'please_wait'))});
                    _elements.uploadForm.submit();
                    _elements.iframeForm.off().load(function()
                    {
                        _elements.dropArea.off('click').on('click', function()
                        {
                            $('input:file', _elements.uploadForm).trigger('click');
                        });
                        
                        _methods.requestSuccess($(this).contents().find('body').html(), slotId);
                    });
                }
                else
                {
                    OW.error(OW.getLanguageText('photo', 'type_error', {name: file}));
                    
                    _methods.afterUploadTask();
                }
            }
        },
        requestSuccess: function( jsonStr, slotId )
        {
            if ( !jsonStr || !slotId )
            {
                return false;
            }
            
            var data;
                            
            try
            {
                data = JSON.parse(jsonStr);
            }
            catch( e )
            {
                OW.error(e);
                _methods.destroySlot(slotId);

                return false;
            }

            if ( data && data.status )
            {
                switch ( data.status )
                {
                    case 'success':
                        _methods.updateSlot(slotId, data.fileUrl, data.id, data.filename);
                        break;
                    case 'error':
                    default:
                        _methods.destroySlot(slotId);

                        OW.error(data.msg);
                        break;
                }
            }
            else
            {
                _methods.destroySlot(slotId);
                OW.error(OW.getLanguageText('photo', 'not_all_photos_uploaded'));
            }
        },
        showAlbumList: function()
        {
            _elements.albumList.show();
            $('.upload_photo_spinner', _elements.albumForm).removeClass('ow_dropdown_arrow_down').addClass('ow_dropdown_arrow_up');
        },
        hideAlbumList: function()
        {
            _elements.albumList.hide();
            $('.upload_photo_spinner', _elements.albumForm).removeClass('ow_dropdown_arrow_up').addClass('ow_dropdown_arrow_down');
        },
        setIsRuning: function()
        {
            _vars.isRuning = true;
            OW.inProgressNode($(':submit', owForms['ajax-upload'].form));
        },
        afterUploadTask: function()
        {
            if ( _vars.files.length !== 0 )
            {
                setTimeout(function()
                {
                    _methods.runAsyncUploadFile();
                }, 10);
            }
            else
            {
                _vars.isRuning = false;
                OW.activateNode($(':submit', owForms['ajax-upload'].form));
            }
        }
    };
    
    var _a = $('<a>', {class: 'ow_hidden ow_content a'}).appendTo(document.body);
    OW.addCss('.cm-hashtag{cursor:pointer;color:' + _a.css('color') + '}');
    _a.remove();
    
    window.ajaxFileUploader = Object.defineProperties({}, {
        init: { value: function()
        {
            $.extend(_elements, {
                dropArea: $('#drop-area').off(),
                dropAreaLabel: $('#drop-area-label').off(),

                slotArea: $('#slot-area').off(),
                slotPrototype: $('#slot-prototype').removeAttr('id').off(),
                slotData: {},
                slotCounter: 0,

                descEditors: {},
                descCache: {},
                relations: {},

                uploadForm: $('#upload-form').off(),
                iframeForm: $('#iframe_upload').off()
                //albumForm: $('#photo-album-form').off()
            });
            
            if ( !_vars.isHTML5 )
            {
                _elements.dropAreaLabel.html(OW.getLanguageText('photo', 'dnd_not_support'));
            }
            
            _elements.dropArea.add(_elements.dropAreaLabel).on(
                (function()
                {
                    var eventMap = {
                        click: function()
                        {
                            $('input:file', _elements.uploadForm).trigger('click');
                        }
                    };

                    if ( _vars.isHTML5 )
                    {
                        eventMap.drop = function( event )
                        {
                            _methods.pushFileList(event.dataTransfer.files);

                            _elements.dropArea.css('border', 'none');
                            _elements.dropAreaLabel.html(OW.getLanguageText('photo', 'dnd_support'));

                            return false;
                        };
                        eventMap.dragenter = function()
                        {
                            _elements.dropArea.css('border', '1px dashed #E8E8E8');
                            _elements.dropAreaLabel.html(OW.getLanguageText('photo', 'drop_here'));
                        };
                        eventMap.dragleave = function()
                        {
                            _elements.dropArea.css('border', 'none');
                            _elements.dropAreaLabel.html(OW.getLanguageText('photo', 'dnd_support'));
                        };
                    }

                    return eventMap;
                })()
            );

            $('input:file', _elements.uploadForm).on('change', function()
            {
                if ( _vars.isHTML5 )
                {
                    _methods.pushFileList(this.files);
                }
                else
                {
                    _methods.setIsRuning();
                    _methods.uploadFile(this.value);
                }

                return false;
            });
            
            _elements.albumList = $('.ow_dropdown_list', _elements.albumForm);
            _elements.albumInput = $('input[name="album"]', _elements.albumForm);

            $('.upload_photo_spinner', _elements.albumForm).add(_elements.albumInput).on('click', function( event )
            {
                if ( _elements.albumList.is(':visible') )
                {
                    _methods.hideAlbumList();
                }
                else
                {
                    _methods.showAlbumList();
                }

                event.stopPropagation();
            });

            _elements.albumList.find('li').on('click', function()
            {
                _methods.hideAlbumList();
                owForms['ajax-upload'].removeErrors();
            })
            .eq(0).on('click', function()
            {
                $('.new-album', _elements.albumForm).show();
                _elements.albumInput.val(OW.getLanguageText('photo', 'create_album'));
                $('input[name="album-name"]', _elements.albumForm).val(OW.getLanguageText('photo', 'album_name'));
                $('textarea', _elements.albumForm).val(OW.getLanguageText('photo', 'album_desc'));
            })
            .end().slice(2).on('click', function()
            {
                $('.new-album', _elements.albumForm).hide();
                _elements.albumInput.val($(this).html());
                $('input[name="album-name"]', _elements.albumForm).val(_elements.albumInput.val());
                $('textarea', _elements.albumForm).val('');
            });

            $(document).on('click',':not(#photo-album-list)', function()
            {
                if ( _elements.albumList.is(':visible') )
                {
                    _methods.hideAlbumList();
                }
            });
            
            OW.bind('photo.onCloseUploaderFloatBox', function()
            {
                _vars.files.length = 0;
                _vars.isRuning = false;
            });

            $.ajaxPrefilter(function(options, origOPtions, jqXHR)
            {
                if ( _vars.isRuning && options.isPhotoUpload !== true )
                {
                    jqXHR.abort();

                    typeof origOPtions.success == 'function' && (origOPtions.success.call(options, {}));
                    typeof origOPtions.complete == 'function' && (origOPtions.complete.call(options, {}));

                }
            });
        }},
        isHasData: {value: function()
        {
            return Object.keys(_elements.slotData).length !== 0;
        }}
    });
})( window, window.jQuery );
