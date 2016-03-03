(function( $ ) {'use strict';

    var _vars = $.extend({}, (browsePhotoParams || {}), {filterParams: {}, offset: 0, idList: [], modified: false, getListRequest: null, uniqueList: null, hashtagPattern: /#(?:\w|[^\u0000-\u007F])+/g}),
        _elements = {},
        _methods = {
            showPreloader: function()
            {
                _elements.preloader.insertAfter(_elements.content);
            },
            hidePreloader: function()
            {
                _elements.preloader.detach();
            },
            filterPhotos: function(filterParams)
            {
                _vars.filterParams = filterParams;
                _methods.unbindUI();
                _methods.resetPhotoListData();
                _methods.getPhotos();
            },
            getPhotos: function()
            {
                if ( _vars.completed === true )
                {
                    return;
                }

                if ( _elements.getListRequest && _elements.getListRequest.readyState !== 4 )
                {
                    try
                    {
                        _elements.getListRequest.abort();
                    }
                    catch ( e ) { }
                }

                var data = {
                    ajaxFunc: _vars.action || 'getPhotoList',
                    listType: _vars.listType || 'latest',
                    offset: ++_vars.offset
                };

                $.extend(data, _vars.filterParams);
                $.extend(data, _methods.getMoreData());

                _elements.getListRequest = $.ajax(
                    {
                        url: _vars.getPhotoURL,
                        dataType: 'json',
                        data: data,
                        cache: false,
                        type: 'POST',
                        beforeSend: function( jqXHR, settings )
                        {
                            _methods.showPreloader();

                            delete _vars.data;
                        },
                        success: function( data, textStatus, jqXHR )
                        {
                            if ( data && data.status )
                            {
                                switch ( data.status )
                                {
                                    case 'success':
                                        _methods.buildPhotoList(data.data);
                                        break;
                                    case 'error':
                                    default:
                                        OW.error(data.msg);
                                        break;
                                }
                            }
                            else
                            {
                                OW.error('Server error');
                            }
                        },
                        error: function( jqXHR, textStatus, errorThrown )
                        {
                            throw textStatus;
                        }
                    });
            },
            resetPhotoListData: function()
            {
                _vars.uniqueList = null;
                _vars.offset = 0;
                _vars.preSearchVal = '';
                _vars.photoListOrder = _vars.photoListOrder.map(Number.prototype.valueOf, 0);
                _vars.completed = false;
                _elements.content.hide().empty().css('height', 'auto').show();
            },
            createPhotoItem: function( photoObj )
            {
                var photo = _elements.photoItemPrototype.clone();
                var data = {
                    photoId: photoObj.id,
                    listType: _vars.listType,
                    dimension: photoObj.dimension,
                    photoUrl: photoObj.url
                };

                photo.attr('id', 'photo-item-' + photoObj.id);
                photo.data(data);

                return photo;
            },
            removePhotoItems: function( idList )
            {
                if ( !Array.isArray(idList) || idList.length === 0 )
                {
                    return false;
                }

                var result = false, iDs = [];

                idList.forEach(function( item )
                {
                    var photo;

                    if ( (photo = document.getElementById(item)) !== null )
                    {
                        var self = $(photo);

                        iDs.push(+self.data('photoId'));
                        self.remove();
                        result = true;
                    }
                });

                if ( result )
                {
                    _vars.modified = true;
                    _vars.idList = _vars.idList.filter(function( item )
                    {
                        return iDs.indexOf(item) === -1;
                    });

                    _methods.reorder();

                    if ( _methods.isTimeToLoad() )
                    {
                        _methods.getPhotos();
                    }

                    OW.trigger('photo.onRemovePhotoItems', iDs);
                }

                return result;
            },
            buildPhotoList: function( data )
            {
                if ( data.photoList.length === 0 )
                {
                    _methods.hidePreloader();

                    if ( _elements.content[0].childNodes.length === 0 )
                    {
                        _elements.content.append($('<div>', {style: 'text-align:center; padding-top: 24px;'}).html(OW.getLanguageText('admin', 'no_items')));
                    }

                    return;
                }
                else if ( data.photoList.length < 20 )
                {
                    _vars.completed = true;
                }

                _vars.data = data;
                _vars.uniqueList = data.unique;

                for ( var i = 1; i < _vars.data.photoList.length; i++ )
                {
                    _methods.asyncLoadPhoto(_vars.data.photoList[i].url);
                }

                _methods.buildPhotoItem(data.photoList.shift());
            },
            buildPhotoItem: function( photo )
            {
                if ( photo === undefined )
                {
                    _methods.hidePreloader();

                    if ( _methods.isTimeToLoad() )
                    {
                        _methods.unbindUI();
                        _methods.getPhotos();
                    }
                    else
                    {
                        _methods.bindUI();
                    }

                    return;
                }
                else if ( photo.unique != _vars.uniqueList )
                {
                    return;
                }

                var photoItem = _methods.createPhotoItem(photo);

                _methods.setInfo.call(photoItem, photo);
                OW.trigger('photo.onRenderPhotoItem', [$.extend({}, photo)], photoItem);

                _methods.buildPhotoItem.buildByMode(photoItem, photo);
                OW.trigger('photo.photoItemRendered', photoItem);
            },
            isTimeToLoad: function()
            {
                var win = $(window);

                return Math.max(document.body.scrollHeight, document.documentElement.scrollHeight, document.body.offsetHeight, document.documentElement.offsetHeight, document.body.clientHeight, document.documentElement.clientHeight) - (win.scrollTop() + win.height()) <= 200;
            },
            bindUI: function()
            {
                $(window).on('scroll.browse_photo resize.browse_photo', function()
                {
                    if ( _methods.isTimeToLoad() )
                    {
                        _methods.unbindUI();
                        if (_vars.hasOwnProperty('filterParams'))
                        {
                            _methods.getPhotos(_vars.filterParams);
                        }
                        else
                        {
                            _methods.getPhotos();
                        }

                    }
                });
            },
            unbindUI: function()
            {
                $(window).off('scroll.browse_photo resize.browse_photo');
            },
            asyncLoadPhoto: function( url )
            {
                setTimeout(function()
                {
                    new Image().src = url;
                }, 1);
            },
            reorder: function()
            {
                if ( _vars.classicMode )
                {
                    return;
                }

                _vars.photoListOrder = _vars.photoListOrder.map(Number.prototype.valueOf, 0);

                $('.ow_photo_item_wrap', _elements.content).each(function()
                {
                    var self = $(this), top, left;

                    top = Math.min.apply(0, _vars.photoListOrder);
                    left = _vars.photoListOrder.indexOf(top);

                    self.css({top: top + 'px', left: left / (_vars.level || 4) * 100 + '%'});
                    _vars.photoListOrder[left] += self.height() + 16;
                });

                _elements.content.height(Math.max.apply(0, _vars.photoListOrder));
            },
            setDescription: function( entity )
            {
                this.find('.ow_photo_item_info_title span').html(entity.title);
                this.find('.ow_photo_item_info_dimensions span').html(entity.dimensions);
                this.find('.ow_photo_item_info_fileszie span').html(entity.filesize);
                this.find('.ow_photo_item_info_uploaddate span').html(entity.addDatetime);
                var link = $('<a></a>', {
                    href: "javascript://",
                    class: 'clipboard-button',
                    'data-clipboard-text': entity.url,
                    'text': OW.getLanguageText('admin', 'copy_url')

                });
                this.find('.ow_photo_item_info_url').append(link);
            },
            getData: function( keys, entity )
            {
                _vars.idList.push(+entity.id);

                var resultData = {};

                keys.forEach(function( item )
                {
                    switch ( item )
                    {
                        case 'setUserInfo':
                            resultData.userUrl = _vars.data.userUrlList[+entity.userId];
                            resultData.userName = _vars.data.displayNameList[+entity.userId];
                            break;
                        case 'setAlbumInfo':
                            resultData.albumUrl = _vars.data.albumUrlList[+entity.albumId];
                            resultData.albumName = _vars.data.albumNameList[+entity.albumId].name;
                            break;
                        case 'setRate':
                            resultData.rateInfo = _vars.data.rateInfo[+entity.id];
                            resultData.userScore = _vars.data.userScore[+entity.id];
                            break;
                        case 'setURL':
                            resultData.url = _vars.data.photoUrlList[+entity.id];
                            break;
                        case 'setCommentCount':
                            resultData.commentCount = _vars.data.commentCount[+entity.id];
                            break;
                    }
                });

                return resultData;
            },
        };

    _methods.setInfo = (function()
    {
        var action = [];
        action.push('setDescription');
        return function( entity )
        {
            var data = _methods.getData(action, entity);

            action.forEach(function( item )
            {
                _methods[item].call(this, entity, data);
            }, this);
        };
    })();

    _methods.getMoreData = (function()
    {
        return function()
        {
            if ( ['user', 'hash', 'desc', 'all', 'tag'].indexOf(_vars.listType) !== -1 )
            {
                return {searchVal: _vars.searchVal};
            }

            return {};
        };
    })();

    _methods.buildPhotoItem.buildByMode = (function()
    {
        if ( _vars.classicMode )
        {
            return function( photoItem, photo )
            {
                photoItem.find('.ow_photo_item')[0].style.backgroundImage = 'url(' + photo.url + ')';
                photoItem.find('img.ow_hidden').attr('src', photo.url);
                photoItem.appendTo(_elements.content);
                photoItem.fadeIn(100, function()
                {
                    if ( _vars.data && _vars.data.photoList )
                    {
                        _methods.buildPhotoItem(_vars.data.photoList.shift());
                    }
                });
            };
        }
        else
        {
            if ( _vars.listType == 'albums' )
            {
                return function( photoItem, photo )
                {
                    var top = Math.min.apply(0, _vars.photoListOrder);
                    var left = _vars.photoListOrder.indexOf(top);
                    var img = photoItem.find('img')[0];

                    img.onerror = img.onload = function(){_methods.buildPhotoItem.complete(left, photoItem)};
                    img.src = photo.url;
                    photoItem.appendTo(_elements.content);
                };
            }

            return function( photoItem, photo )
            {
                var top = Math.min.apply(0, _vars.photoListOrder);
                var left = _vars.photoListOrder.indexOf(top);

                photoItem.css({top: top + 'px', left: left / (_vars.level || 4) * 100 + '%'});
                photoItem.find('img').attr('src', photo.url);
                photoItem.appendTo(_elements.content);
                _elements.content.height(Math.max.apply(0, _vars.photoListOrder));

                var img = new Image();
                img.onload = img.onerror = function(){_methods.buildPhotoItem.complete(left, photoItem, photo)};
                img.src = photo.url;
            };
        }
    })();

    _methods.buildPhotoItem.complete = function( left, photoItem, photo )
    {
        photoItem.fadeIn(100, function()
        {
            if ( photo && photo.unique != _vars.uniqueList )
            {
                return;
            }

            _vars.photoListOrder[left] += photoItem.height() + 16;
            _elements.content.height(Math.max.apply(0, _vars.photoListOrder));

            if ( _vars.data && _vars.data.photoList )
            {
                _methods.buildPhotoItem(_vars.data.photoList.shift());
            }
        });
    };

    window.browsePhoto = Object.defineProperties({},
        {
            init: {
                value: function()
                {
                    $.extend(_elements, {
                        content: $(document.getElementById('browse-photo')),
                        preloader: $(document.getElementById('browse-photo-preloader')),
                        photoItemPrototype: $(document.getElementById('browse-photo-item-prototype'))
                    });

                    _vars.photoListOrder = Array.apply(0, new Array(_vars.level || 4)).map(Number.prototype.valueOf, 0);

                    OW.bind('photo.onSetRate', _methods.updateRate);

                    _methods.getPhotos();
                    return;
                }
            },
            reorder: {value: _methods.reorder},
            filter: {value: _methods.filterPhotos},
            removePhotoItems: {value: _methods.removePhotoItems},
            updateSlot: {
                value: function( slotId, data )
                {
                    OW.trigger('photo.onUpdateSlot', [slotId, data]);

                    var item;

                    if ( !_vars.isOwner || (item = document.getElementById(slotId)) === null || Object.keys(data).length === 0 )
                    {
                        return false;
                    }

                    switch ( _vars.listType )
                    {
                        case 'userPhotos':
                        case 'albumPhotos':
                            _methods.setAlbumInfo.call($(item), 0, data);
                            _methods.setDescription.call($(item), data);
                            break;
                    }
                }
            },
            getListData: {
                value: function()
                {
                    return {
                        searchVal: _vars.searchVal,
                        id: _vars.id
                    };
                }
            },
            getMoreData: {value: _methods.getMoreData},
            getFilterData: {value: function(){
                return _vars.filterParams;
            }}
        });
})(jQuery);

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Lite Dating Software http://lite.skadate.com/
 * and is licensed under SkaDate Lite License by Skalfa LLC.
 * Full text of this license can be found at http://lite.skadate.com/sll.pdf
 */

/**
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow_plugins.photo
 * @since 1.6.1
 */
(function( root, $ )
{
    var _params = {};
    var _contextList = [];
    var _methods = {
        sendRequest: function( ajaxFunc, entityId, success )
        {
            $.ajax(
                {
                    url: _params.actionUrl,
                    type: 'POST',
                    cache: false,
                    data:
                    {
                        ajaxFunc: ajaxFunc,
                        entityId: entityId
                    },
                    dataType: 'json',
                    success: success,
                    error: function( jqXHR, textStatus, errorThrown )
                    {
                        OW.error(textStatus);

                        throw textStatus;
                    }
                });
        },
        deleteImage: function( imageId )
        {
            if ( !confirm(OW.getLanguageText('admin', 'confirm_delete')) )
            {
                return false;
            }

            _methods.sendRequest('ajaxDeleteImage', imageId, function( data )
            {
                if ( data.result === true )
                {
                    OW.info(data.msg);
                    browsePhoto.removePhotoItems(['photo-item-' + imageId]);
                }
                else if ( data.hasOwnProperty('error') )
                {
                    OW.error(data.error);
                }
            });
        },
        deleteImages: function( imagesIds )
        {
            if (imagesIds.length == 0)
            {
                OW.error(OW.getLanguageText('admin', 'no_photo_selected'));
                return false;
            }
            if ( !confirm(OW.getLanguageText('admin', 'confirm_delete_images')) )
            {
                return false;
            }
            for (var i in imagesIds)
            {
                var imageId = imagesIds[i];
                _methods.sendRequest('ajaxDeleteImage', imageId, function( data )
                {
                    if ( data.result === true )
                    {
                        OW.info(data.msg);
                        browsePhoto.removePhotoItems(['photo-item-' + data.imageId]);
                    }
                    else if ( data.hasOwnProperty('error') )
                    {
                        OW.error(data.error);
                    }
                });
            }
        },
        call: function( event )
        {
            var closest = $(this).closest('.ow_photo_item_wrap');

            _methods[event.data.action].apply(closest, [+closest.data('photoId')]);

            event.stopPropagation();
        },
        createElement: function( action, html, style )
        {
            return $('<li/>', {class: style || ''}).html(
                $('<a/>', {href : 'javascript://'}).on('click', {action: action}, _methods.call).html(html)
            );
        },
        init: function()
        {
            $.extend(_params, (root.photoContextActionParams || {}));


            if ( _params.contextOptions )
            {
                for (var i in _params.contextOptions)
                {
                    var option = _params.contextOptions[i];
                    if (option['action'])
                    {
                        _contextList.push(_methods.createElement(option['action'], option['name']));
                    }
                    else
                    {
                        _contextList.push(
                            $('<li/>', {class: option['liClass']}).html(
                                $('<a/>', {class: option['aClass']}).html(option['name'])
                            )
                        );
                    }

                }
            }

            var event = {buttons:[]};
            OW.trigger('photo.collectMenuItems', [event]);

            if ( _contextList.length === 0 && event.buttons.length === 0 )
            {
                return;
            }

            var list = $('<ul>', {class: 'ow_context_action_list'});

            _contextList.concat(event.buttons).forEach(function(item)
            {
                item.appendTo(list);
            });

            _params.contextAction = $('<div>', {class: 'ow_photo_context_action'}).on('click', function( event )
            {
                event.stopImmediatePropagation();
            });
            _params.contextActionPrototype = $(document.getElementById('context-action-prototype')).removeAttr('id');
            _params.contextActionPrototype.find('.ow_tooltip_body').append(list);

            OW.bind('photo.onRenderPhotoItem', function( album )
            {
                var self = $(this);
                var prototype = _params.contextActionPrototype.clone(true);

                prototype.find('.download').attr('href', _params.downloadUrl.replace(':id', self.data('photoId')));

                if ( _params.listType == 'albums' && album.name.trim() == OW.getLanguageText('photo', 'newsfeed_album').trim() )
                {
                    prototype.find('.delete_album').remove();
                }

                var contextAction = _params.contextAction.clone(true);
                contextAction.append(prototype);

                if ( _params.isClassic )
                {
                    self.find('.ow_photo_item').prepend(contextAction);
                }
                else
                {
                    self.find('.ow_photo_pint_album').append(contextAction);
                }
            });
        }
    };

    root.photoContextAction = Object.defineProperties({},
        {
            init: {value: _methods.init},
            deleteImages: {value: _methods.deleteImages}
        });

})( window, jQuery );