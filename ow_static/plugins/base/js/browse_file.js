/**
 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.

 * ---
 * Copyright (c) 2011, Oxwall Foundation
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
 * following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice, this list of conditions and
 *  the following disclaimer.
 *
 *  - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and
 *  the following disclaimer in the documentation and/or other materials provided with the distribution.
 *
 *  - Neither the name of the Oxwall Foundation nor the names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow_plugins.photo
 * @since 1.6.1
 */
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
                        _elements.content.append($('<div>', {style: 'text-align:center; padding-top: 24px;'}).html(OW.getLanguageText('photo', 'no_items')));
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
            truncate: function( value, limit )
            {
                if ( !value )
                {
                    return '';
                }

                var parts;

                limit = +limit || 50;

                if ( (parts = value.split(/\n/)).length >= 3 )
                {
                    value = parts.slice(0, 3).join('\n') + '...';
                }
                else if ( value.length > limit )
                {
                    value = value.toString().substring(0, limit) + '...';
                }

                return value;
            },
            getHashtags: function( text )
            {
                var result = {};

                text.replace(_vars.hashtagPattern, function( str, offest )
                {
                    result[offest] = str;
                });

                return result;
            },
            descToHashtag: function( description, hashtags )
            {
                var url = '<a href="' + _vars.tagUrl + '">{$tagLabel}</a>';

                return description.replace(_vars.hashtagPattern, function( str, offest )
                {
                    return (url.replace('-tag-', encodeURIComponent(hashtags[offest]))).replace('{$tagLabel}', str);
                }).replace(/\n/g, '<br>');
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
            setAlbumInfo: function( photo, data )
            {
                if ( !data || !data.albumUrl || !data.albumName )
                {
                    return;
                }

                if ( _vars.classicMode )
                {
                    this.find('.ow_photo_item_info_album').show().find('a').attr('href', data.albumUrl).find('b').html(data.albumName);
                }
                else
                {
                    this.find('.ow_photo_item_info_album').show().find('a').attr('href', data.albumUrl).html(data.albumName);
                }
            },
            setUserInfo: function( photo, data)
            {
                if ( !data || !data.userUrl || !data.userName )
                {
                    return;
                }

                if ( _vars.classicMode )
                {
                    this.find('.ow_photo_by_user').show().find('a').attr('href', data.userUrl).find('b').html(data.userName);
                }
                else
                {
                    this.find('.ow_photo_by_user').show().find('a').attr('href', data.userUrl).html(data.userName);
                }
            },
            setDescription: function( entity )
            {
                this.find('.ow_photo_item_info_title span').html(entity.title);
                this.find('.ow_photo_item_info_dimensions span').html(entity.dimensions);
                this.find('.ow_photo_item_info_fileszie span').html(entity.filesize);
                this.find('.ow_photo_item_info_uploaddate span').html(entity.addDatetime);
            },
            setURL: function( photo, data )
            {
                if ( !data || !data.url )
                {
                    return;
                }

                this.data('url', data.url);
                this.on('click', function( event )
                {
                    window.location = data.url;
                });
            },
            setRate: function( photo, data )
            {
                if ( !data || !data.rateInfo || data.userScore === undefined )
                {
                    return;
                }

                var self = $(this), rateItems = $('.rate_item', this);

                if ( +data.userScore > 0 )
                {
                    this.find('.rate_title').html(
                        OW.getLanguageText('photo', 'rating_your', {count: data.rateInfo.rates_count, score: data.userScore})
                    );
                }
                else
                {
                    this.find('.rate_title').html(
                        OW.getLanguageText('photo', 'rating_total', {count: data.rateInfo.rates_count})
                    );
                }

                this.find('.active_rate_list').css('width', (data.rateInfo.avg_score * 20) + '%');

                rateItems.each(function( index )
                {
                    $(this)
                        .on('click', function()
                        {
                            var ownerError;

                            if ( +_vars.rateUserId === 0 || (ownerError = (+photo.userId === +_vars.rateUserId)) )
                            {
                                if ( ownerError === undefined )
                                {
                                    OW.error(OW.getLanguageText('base', 'rate_cmp_auth_error_message'));
                                }
                                else
                                {
                                    OW.error(OW.getLanguageText('base', 'rate_cmp_owner_cant_rate_error_message'));
                                }

                                return false;
                            }

                            $.ajax(
                                {
                                    url: _vars.getPhotoURL,
                                    dataType: 'json',
                                    data: {
                                        ajaxFunc: 'ajaxRate',
                                        entityId: photo.id,
                                        rate: index + 1,
                                        ownerId: photo.userId
                                    },
                                    cache: false,
                                    type: 'POST',
                                    success: function( result, textStatus, jqXHR )
                                    {
                                        if ( result )
                                        {
                                            switch ( result.result )
                                            {
                                                case true:
                                                    OW.info(result.msg);
                                                    self.find('.active_rate_list').css('width', (result.rateInfo.avg_score * 20) + '%');
                                                    self.find('.rate_title').html(
                                                        OW.getLanguageText('photo', 'rating_your', {count: result.rateInfo.rates_count, score: index + 1})
                                                    );
                                                    data.rateInfo.avg_score = result.rateInfo.avg_score;
                                                    break;
                                                case false:
                                                default:
                                                    OW.error(result.error);
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
                        })
                        .hover(function()
                        {
                            rateItems.slice(0, index + 1).addClass('active');
                            self.find('.active_rate_list').css('width', '0px');
                        }, function()
                        {
                            rateItems.slice(0, index + 1).removeClass('active');
                            self.find('.active_rate_list').css('width', (data.rateInfo.avg_score * 20) + '%');
                        });
                });
            },
            updateRate: function( data )
            {
                var diff = ['entityId', 'userScore', 'avgScore', 'ratesCount'].every(function( item )
                {
                    return data.hasOwnProperty(item);
                });

                var photoItem;

                if ( diff && (photoItem = document.getElementById('photo-item-' + data.entityId)) !== null)
                {
                    $('.active_rate_list', photoItem).css('width', (data.avgScore * 20) + '%');
                    $('.rate_title', photoItem).html(
                        OW.getLanguageText('photo', 'rating_your', {count: data.ratesCount, score: data.userScore})
                    );
                }
            },
            setCommentCount: function( photo, data )
            {
                this.find('.ow_photo_comment_count a').html('<b>' + parseInt(data.commentCount) + '</b>').on('click', function()
                {
                    var data = this.data(), _data = {}, img = this.find('img')[0];

                    if ( data.dimension && data.dimension.length )
                    {
                        try
                        {
                            var dimension = JSON.parse(data.dimension);

                            _data.main = dimension.main;
                        }
                        catch( e )
                        {
                            _data.main = [img.naturalWidth, img.naturalHeight];
                        }
                    }
                    else
                    {
                        _data.main = [img.naturalWidth, img.naturalHeight];
                    }

                    _data.mainUrl = data.photoUrl;

                    photoView.setId(data.photoId, _vars.listType, _methods.getMoreData(), _data);
                }.bind(this));
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
            initSearchEngine: function()
            {
                var timerId;

                _elements.searchBox = document.getElementById('photo-list-search');
                _elements.searchResultList = $('.ow_searchbar_ac', _elements.searchBox);
                _elements.hashItemPrototype = $('li.hash-prototype', _elements.searchBox).removeClass('hash-prototype');
                _elements.userItemPrototype = $('li.user-prototype', _elements.searchBox).removeClass('user-prototype');
                _elements.searchInput = $('input:text', _elements.searchBox).on({
                    keyup: function( event )
                    {
                        if ( timerId )
                        {
                            clearTimeout(timerId);
                            _methods.abortSearchRequest();
                        }

                        if ( event.keyCode === 13 )
                        {
                            _methods.destroySearchResultList();
                            _methods.searchAll(this.value);
                        }
                        else
                        {
                            timerId = setTimeout(function()
                            {
                                _methods.search(_elements.searchInput.val());
                            }, 300);
                        }
                    },
                    focus: function()
                    {
                        $(this).removeClass('invitation');

                        if ( _elements.searchResultList.children().length !== 0 )
                        {
                            _elements.searchResultList.show();
                        }

                        if ( this.value.trim() === OW.getLanguageText('photo', 'search_invitation') )
                        {
                            $(this).val('');
                        }
                    },
                    blur: function()
                    {
                        $(this).addClass('invitation');

                        if ( this.value.trim().length === 0 )
                        {
                            $(this).val(OW.getLanguageText('photo', 'search_invitation'));
                        }
                    }
                });

                _elements.listBtns = $('.ow_fw_btns > a').on('click', function( event )
                {
                    _methods.loadList($(this).attr('list-type'));

                    event.preventDefault();
                });

                $('.ow_btn_close_search', _elements.searchBox).on('click', function()
                {
                    _methods.loadList(_vars.serachInitList);
                });

                $('.ow_searchbar_btn', _elements.searchBox).on('click', function()
                {
                    var value = _elements.searchInput.val().trim();

                    if ( value.length === 0 || value === OW.getLanguageText('photo', 'search_invitation') )
                    {
                        return false;
                    }

                    _methods.abortSearchRequest();
                    _methods.searchAll(_elements.searchInput[0].value);
                });

                $(document).on('click', function( event )
                {
                    if ( event.target.id === 'search-photo' )
                    {
                        event.stopPropagation();
                    }
                    else if ( _elements.searchResultList.is(':visible') )
                    {
                        _elements.searchResultList.hide();
                    }
                });

                $.extend(_methods, {
                    createSearchResultItem: function( type )
                    {
                        switch ( type )
                        {
                            case 'user':
                                return _elements.userItemPrototype.clone();
                            default:
                                return _elements.hashItemPrototype.clone();
                        }
                    },
                    buildSearchResultList: function( type, list, searchVal, avatarData )
                    {
                        _methods.hideSearchProcess();

                        var keys;

                        if ( (keys = Object.keys(list)).length === 0 )
                        {
                            $('<li class="browse-photo-search clearfix"><span style="line-height: 20px;">' + OW.getLanguageText('photo', 'search_result_empty') + '</span></li>')
                                .appendTo(_elements.searchResultList);

                            return;
                        }

                        _vars.searchVal = searchVal;
                        _methods.changeListType(type);

                        keys.forEach(function( item )
                        {
                            var listItem = _methods.createSearchResultItem(type);

                            listItem.data('id', list[item].id);
                            listItem.data('searchType', type);
                            listItem.find('.ow_searchbar_ac_count').html(list[item].count);
                            listItem.on('click', function()
                            {
                                _methods.getSearchResultPhotos.call(this);
                            });

                            _methods.setSearchResultItemInfo.call(listItem, searchVal, list[item].label, avatarData !== undefined ? avatarData[list[item].id].src : null);

                            listItem.appendTo(_elements.searchResultList).slideDown(200);
                        });
                    },
                    destroySearchResultList: function()
                    {
                        _elements.searchResultList.hide().empty();
                    },
                    showSearchProcess: function()
                    {
                        _elements.searchResultList.append($('<li>', {class: 'browse-photo-search clearfix ow_preloader'})).show();
                    },
                    hideSearchProcess: function()
                    {
                        _elements.searchResultList.find('.ow_preloader').remove();
                    },
                    search: function( searchVal )
                    {
                        searchVal = searchVal.trim();

                        if ( searchVal.length <= 2 || searchVal === _vars.preSearchVal )
                        {
                            return;
                        }

                        _elements.searchRequest = $.ajax(
                            {
                                url: _vars.getPhotoURL,
                                dataType: 'json',
                                data:
                                {
                                    ajaxFunc: 'getSearchResult',
                                    searchVal: searchVal
                                },
                                cache: false,
                                type: 'POST',
                                beforeSend: function( jqXHR, settings )
                                {
                                    _vars.preSearchVal = searchVal;
                                    _methods.destroySearchResultList();
                                    _methods.showSearchProcess();
                                },
                                success: function( data, textStatus, jqXHR )
                                {
                                    if ( data && data.result )
                                    {
                                        _methods.buildSearchResultList(data.type, data.list, searchVal, data.avatarData);
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
                    searchAll: function( searchVal )
                    {
                        searchVal = searchVal.trim();

                        if ( searchVal.length <= 2 || searchVal === _vars.preAllSearchVal )
                        {
                            return;
                        }

                        _vars.preAllSearchVal = searchVal;
                        _methods.getSearchResultPhotos('all');
                    },
                    getSearchResultPhotos: function( type )
                    {
                        _methods.resetPhotoListData();

                        if ( type )
                        {
                            _methods.changeListType(type);
                        }
                        else
                        {
                            _methods.changeListType($(this).data('searchType'));
                            _vars.preAllSearchVal = '';
                        }

                        if ( _vars.serachInitList == null )
                        {
                            _vars.serachInitList = _vars.listType;
                            $('.ow_searchbar_input', _elements.searchBox).addClass('active');
                        }

                        _vars.listType = _vars.searchType;

                        switch ( _vars.searchType )
                        {
                            case 'desc':
                                _vars.searchVal = $(this).data('searchVal');
                            case 'user':
                            case 'hash':
                                _vars.id = +$(this).data('id');
                                break;
                            case 'all':
                                _vars.searchVal = _elements.searchInput.val().trim();
                                break;
                        }

                        _methods.getPhotos();
                    },
                    changeListType: function( type )
                    {
                        OW.trigger('photo.onChangeListType', [type]);

                        if ( _vars.searchType === type )
                        {
                            return;
                        }

                        _vars.searchType = type;

                        switch ( type )
                        {
                            case 'user':
                                _methods.getMoreData = function()
                                {
                                    return {
                                        id: _vars.id,
                                        searchVal: _vars.searchVal
                                    };
                                };

                                _methods.setSearchResultItemInfo = function( searchVal, info, avatarUrl )
                                {
                                    var reg = new RegExp(searchVal.substring(1), 'i');

                                    this.find('img').attr('src', avatarUrl);
                                    this.find('.ow_searchbar_username').html(info.replace(reg, function( p1 )
                                    {
                                        return '<b>' + p1 + '</b>';
                                    }));
                                };
                                break;
                            case 'hash':
                                _methods.getMoreData = function()
                                {
                                    return {
                                        id: _vars.id,
                                        searchVal: _vars.searchVal
                                    };
                                };

                                _methods.setSearchResultItemInfo = function( searchVal, info )
                                {
                                    var reg = new RegExp(searchVal.substring(1), 'gi');

                                    this.find('.ow_search_result_tag').html(info.replace(reg, function( p1 )
                                    {
                                        return '<b>' + p1 + '</b>';
                                    }));
                                };
                                break;
                            case 'desc':
                                _methods.getMoreData = function()
                                {
                                    return {
                                        id: _vars.id,
                                        searchVal: _vars.searchVal
                                    };
                                };

                                _methods.setSearchResultItemInfo = function( searchVal, info )
                                {
                                    var reg = new RegExp(searchVal, 'gi');

                                    this.find('.ow_search_result_tag').html(info.replace(reg, function( p1 )
                                    {
                                        return '<b>' + p1 + '</b>';
                                    }));
                                    this.data('searchVal', searchVal);
                                };
                                break;
                            case 'all':
                                _methods.getMoreData = function()
                                {
                                    return {
                                        ajaxFunc: 'getSearchAllResult',
                                        searchVal: _vars.searchVal
                                    };
                                };
                                break;
                            default:
                                window.history.replaceState(null, null, type);
                                _elements.searchInput.val(OW.getLanguageText('photo', 'search_invitation'));
                                document.title = OW.getLanguageText('photo', 'meta_title_photo_' + _vars.searchType);

                                _methods.getMoreData = function()
                                {
                                    return {};
                                };
                                break;
                        }
                    },
                    resetPhotoListData: function()
                    {
                        _elements.listBtns.removeClass('active');
                        _vars.uniqueList = null;
                        _vars.offset = 0;
                        _vars.preSearchVal = '';
                        _vars.photoListOrder = _vars.photoListOrder.map(Number.prototype.valueOf, 0);
                        _vars.completed = false;
                        _elements.content.hide().empty().css('height', 'auto').show();
                    },
                    abortSearchRequest: function()
                    {
                        if ( _elements.searchRequest && _elements.searchRequest.readyState !== 4 )
                        {
                            try
                            {
                                _elements.searchRequest.abort();
                            }
                            catch ( e ) { }
                        }
                    },
                    loadList: function( listType )
                    {
                        _methods.resetPhotoListData();
                        $('.ow_searchbar_input', _elements.searchBox).removeClass('active');
                        _elements.listBtns.filter('[list-type="' + listType + '"]').addClass('active');
                        _vars.listType = listType;
                        _vars.serachInitList = null;
                        _methods.changeListType(_vars.listType);
                        _methods.destroySearchResultList();
                        _methods.getPhotos();
                    }
                });
            }
        };

    _methods.setInfo = (function()
    {
        var action = [];

        //action.push('setURL');
        action.push('setDescription');

        //action.push('setRate');
        //action.push('setCommentCount');



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
        switch ( _vars.listType )
        {
            case 'albums':
            case 'userPhotos':
                return function()
                {
                    return $.extend({}, (_vars.modified ? {offset: 1, idList: _vars.idList} : {}), {
                        userId: _vars.userId
                    });
                };
            case 'albumPhotos':
                return function()
                {
                    return $.extend({}, (_vars.modified ? {offset: 1, idList: _vars.idList} : {}), {
                        albumId: _vars.albumId
                    });
                };
            default:
                _methods.initSearchEngine();

                return function()
                {
                    if ( ['user', 'hash', 'desc', 'all', 'tag'].indexOf(_vars.listType) !== -1 )
                    {
                        return {searchVal: _vars.searchVal};
                    }

                    return {};
                };
        }
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

                    var updateCommentCount = function( data )
                    {
                        var photo;

                        if ( !data || data.entityType != 'photo_comments' || (photo = document.getElementById('photo-item-' + data.entityId)) == null )
                        {
                            return;
                        }

                        $('.ow_photo_comment_count a', photo).html('<b>' + parseInt(data.commentCount) + '</b>');
                    };

                    OW.bind('base.comment_delete', updateCommentCount);
                    OW.bind('base.comment_added', updateCommentCount);
                    OW.bind('photo.onBeforeLoadFromCache', function()
                    {
                        OW.bind('base.comment_delete', updateCommentCount);
                        OW.bind('base.comment_added', updateCommentCount);
                    });

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
            if ( !confirm(OW.getLanguageText('photo', 'confirm_delete')) )
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
            if ( !confirm(OW.getLanguageText('photo', 'confirm_delete_images')) )
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
        editPhoto: function( photoId )
        {
            var editFB = OW.ajaxFloatBox('PHOTO_CMP_EditPhoto', {photoId: photoId}, {width: 580, iconClass: 'ow_ic_edit', title: OW.getLanguageText('photo', 'tb_edit_photo'),
                    onLoad: function()
                    {
                        owForms['photo-edit-form'].bind("success", function( data )
                        {
                            editFB.close();

                            if ( data && data.result )
                            {
                                if ( data.photo.status !== 'approved' )
                                {
                                    OW.info(data.msgApproval);
                                    browsePhoto.removePhotoItems(['photo-item-' + photoId]);
                                }
                                else
                                {
                                    OW.info(data.msg);
                                    browsePhoto.updateSlot('photo-item-' + data.id, data);
                                }

                                browsePhoto.reorder();

                                OW.trigger('photo.onAfterEditPhoto', [data.id]);
                            }
                            else if ( data.msg )
                            {
                                OW.error(data.msg);
                            }
                        });
                    }}
            );
        },
        saveAsAvatar: function( photoId )
        {
            document.avatarFloatBox = OW.ajaxFloatBox(
                "BASE_CMP_AvatarChange",
                { params : { step: 2, entityType : 'photo_album', entityId : '', id : photoId } },
                { width : 749, title : OW.getLanguageText('base', 'avatar_change') }
            );
        },
        saveAsCover: function( photoId )
        {
            var img, item = document.getElementById('photo-item-' + photoId), data = $(item).data(), dim;

            if ( _params.isClassic )
            {
                img = $('img.ow_hidden', item)[0];
            }
            else
            {
                img = $('img', item)[0];
            }

            if ( data.dimension && data.dimension.length )
            {
                try
                {
                    var dimension = JSON.parse(data.dimension);

                    dim = dimension.main;
                }
                catch( e )
                {
                    dim = [img.naturalWidth, img.naturalHeight];
                }
            }
            else
            {
                dim = [img.naturalWidth, img.naturalHeight];
            }

            if ( dim[0] < 330 || dim[1] < 330 )
            {
                OW.error(OW.getLanguageText('photo', 'to_small_cover_img'));

                return;
            }

            window.albumCoverMakerFB = OW.ajaxFloatBox('PHOTO_CMP_MakeAlbumCover', [_params.albumId, photoId], {
                title: OW.getLanguageText('photo', 'set_as_album_cover'),
                width: '700',
                onLoad: function()
                {
                    window.albumCoverMaker.init();
                }
            });
        },
        editAlbum: function( event )
        {
            var url = $(this).closest('.ow_photo_item_wrap').data('url') + '#edit';

            window.location = url;
        },
        deleteAlbum: function( albumId )
        {
            if ( !confirm(OW.getLanguageText('photo', 'are_you_sure')) )
            {
                return;
            }

            _methods.sendRequest('ajaxDeletePhotoAlbum', albumId, function( data )
            {
                if ( data.result )
                {
                    OW.info(data.msg);

                    browsePhoto.removePhotoItems(['photo-item-' + albumId]);
                }
                else
                {
                    if ( data.msg )
                    {
                        OW.error(data.msg);
                    }
                    else
                    {
                        alert(OW.getLanguageText('photo', 'no_photo_selected'));
                    }
                }
            });
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
//OW.trigger('photo.browsePhotoInitialized');