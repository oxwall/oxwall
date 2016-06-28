(function($) {
    $.fn.htmlarea = function(opts) {
        return this.each(function() {
            jHtmlArea(this, opts);
        });
    };
    var jHtmlArea = window.jHtmlArea = function(elem, options) {
        if (elem.jquery) {
            return jHtmlArea(elem[0]);
        }
        if (elem.jhtmlareaObject) {
            return elem.jhtmlareaObject;
        } else {
            return new jHtmlArea.fn.init(elem, options);
        }
    };
    jHtmlArea.fn = jHtmlArea.prototype = {

        isMsie: function(){
            return $.browser.msie || ( $.browser.mozilla && parseInt($.browser.version) < 12 );
        },
        init: function(elem, options) {
            var self = this;
            if (elem.nodeName.toLowerCase() === "textarea") {
                var buttons = [['bold', 'italic', 'underline', 'link'], ['more', 'image', 'video', 'html']];
                var toolbar = [];

                for( var i = 0; i < buttons.length; i++ ){
                    var tempEl = {};
                    for( var j = 0; j < buttons[i].length; j++ ){
                        if( $.inArray(buttons[i][j], options.toolbar) !== -1 ){
                            tempEl[buttons[i][j]] = window.htmlAreaData.labels.buttons[buttons[i][j]];
                        }
                    }
                    if( !$.isEmptyObject(tempEl) ){
                        toolbar.push(tempEl);
                    }
                }

                var opts = {
                    toolbar: toolbar
                };

                elem.jhtmlareaObject = this;

                var textarea = this.textarea = $(elem);
                var container = this.container = $("<div/>").addClass("jhtmlarea").width('95%').addClass('ow_smallmargin').insertAfter(textarea);
                var toolbar = this.toolbar = $("<div/>").addClass("toolbar clearfix").appendTo(container);
                priv.initToolBar.call(this, opts);
                var iframe = this.iframe = $("<iframe/>").height(parseInt(options.size));
                iframe.width('99%');
                
                var htmlarea = this.htmlarea = $('<div class="input_ws_cont"/>').append(iframe);

                var interval;
                var tempStyles = function(){

                    if( !window.htmlAreaData.tempStyles ){
                        var baseCss = false;
                        
                        $.each(document.styleSheets, function(index, data){
                            if( data.href && data.href.match(/base\.css/) ){
                                baseCss = data;
                            }
                        });

                        if( self.isMsie() ){
                            if( !baseCss || !baseCss.rules ){
                                return;
                            }

                            var crules = baseCss.rules;
                        }
                        else{
                            if( !baseCss || !baseCss.cssRules ){
                                return;
                            }

                            var crules = baseCss.cssRules;
                        }

                        window.htmlAreaData.tempStyles = '';
                        $.each( crules,
                            function(key, data){
                                if( data.selectorText && data.selectorText.match(/.htmlarea_styles/i) ){
                                    window.htmlAreaData.tempStyles += ( self.isMsie() ? data.selectorText + '{' + data.style.cssText + '}' : data.cssText );
                                }
                            }
                            );
                    }


                    var icontents = iframe.get(0).contentDocument;

                    if ( icontents )
                    {
                        $('body', icontents).addClass('htmlarea_styles');
                        if( options.customClass ){
                            $('body', icontents).addClass(options.customClass);
                        }
                        $('head', icontents).html('<style>'+window.htmlAreaData.tempStyles+'</style>');
                        if( window.htmlAreaData.rtl ){
                            $('html', icontents).attr('dir', 'rtl');
                        }
                    }

                    clearInterval(interval);
                }

                interval = setInterval(tempStyles, 50);

                container.append(htmlarea).append(textarea.hide());

                priv.initEditor.call(this, opts);
                priv.attachEditorEvents.call(this);

                // Fix total height to match TextArea
                iframe.height(iframe.height() - toolbar.height());
                //toolbar.width(textarea.width() - 2);

                if (opts.loaded) {
                    opts.loaded.call(this);
                }

                OW.trigger('base.initjHtmlArea', [], this);
            }
        },
        dispose: function() {
            this.textarea.show().insertAfter(this.container);
            this.container.remove();
            this.textarea[0].jhtmlareaObject = null;
        },
        ec: function(a, b, c) {
            this.iframe[0].contentWindow.focus();
            this.editor.execCommand(a, b || false, c || null);
            this.updateTextArea();
        },
        qcv: function(a) {
            this.iframe[0].contentWindow.focus();
            return this.editor.queryCommandState(a);
        },
        getSelectedHTML: function() {
            if (this.isMsie()) {
                return this.getRange().htmlText;
            } else {
                var elem = this.getRange().cloneContents();
                return $("<p/>").append($(elem)).html();
            }
        },
        getSelection: function() {
            if ( this.isMsie() ) {
                return this.editor.selection;
            } else {
                return this.iframe[0].contentDocument.defaultView.getSelection();
            }
        },
        getRange: function() {
            var s = this.getSelection();
            if (!s) {
                return null;
            }
            return (s.getRangeAt) ? s.getRangeAt(0) : s.createRange();
        },

        //        getInternalRange: function () {
        //			var selection = this.getInternalSelection();
        //
        //			if (!selection) {
        //				return null;
        //			}
        //
        //			if (selection.rangeCount && selection.rangeCount > 0) { // w3c
        //				return selection.getRangeAt(0);
        //			} else if (selection.createRange) { // ie
        //				return selection.createRange();
        //			}
        //
        //			return null;
        //		},
        //		getInternalSelection: function () {
        //			// firefox: document.getSelection is deprecated
        //            var editor = this.iframe.get(0);
        //			if (editor.contentWindow) {
        //				if (editor.contentWindow.getSelection) {
        //					return editor.contentWindow.getSelection();
        //				}
        //				if (editor.contentWindow.selection) {
        //					return editor.contentWindow.selection;
        //				}
        //			}
        //			if (this.editor.getSelection) {
        //				return this.editor.getSelection();
        //			}
        //			if (this.editor.selection) {
        //				return this.editor.selection;
        //			}
        //
        //			return null;
        //		},
        html: function(v) {
            if (v) {
                this.pastHTML(v);
            } else {
                return toHtmlString();
            }
        },
        pasteHTMLSpec: function(html) {
            this.iframe[0].contentWindow.focus();
            var r = this.getRange();
            var s = this.getSelection();
            if ( $.browser.msie ) {
                r.pasteHTML(html);
                r.select();
            } else if ( $.browser.mozilla && parseInt($.browser.version) < 12  ){

            }else if ($.browser.mozilla) {
                r.deleteContents();
                var node = $((html.indexOf("<") != 0) ? $("<span/>").append(html) : html)[0];
                r.insertNode(node);
                r.setStartAfter(node);
                r.setEndAfter(node);
                r.collapse(false);
                s.removeAllRanges();
                s.addRange(r);
                  //this.ec("insertHTML", false, html);
            } else {
                r.deleteContents();
                var node = $(this.iframe[0].contentWindow.document.createElement("span")).append($((html.indexOf("<") != 0) ? "<span>" + html + "</span>" : html))[0];
                r.insertNode(node);
                r.setStartAfter(node);
                r.setEndAfter(node);
                r.collapse(false);
                s.removeAllRanges();
                s.addRange(r);
            }
            this.updateTextArea();

        },
        pasteHTML: function(html) {
            if( !this.isMsie() ){
                this.pasteHTMLSpec(html);
                return;
            }
            
            var window = this.iframe[0].contentWindow, document = window.document;
            var sel, range;
            if (window.getSelection) {
                // IE9 and non-IE
                sel = window.getSelection();
                if (sel.getRangeAt && sel.rangeCount) {
                    range = sel.getRangeAt(0);
                    range.deleteContents();

                    // Range.createContextualFragment() would be useful here but is
                    // only relatively recently standardized and is not supported in
                    // some browsers (IE9, for one)
                    var el = document.createElement("div");
                    el.innerHTML = html;
                    var frag = document.createDocumentFragment(), node, lastNode;
                    while ( (node = el.firstChild) ) {
                        lastNode = frag.appendChild(node);
                    }
                    
                    range.insertNode(frag);

                    // Preserve the selection
                    if (lastNode) {
                        range = range.cloneRange();
                        range.setStartAfter(lastNode);
                        range.collapse(true);
                        sel.removeAllRanges();
                        sel.addRange(range);
                    }
                }
            } else if (document.selection && document.selection.type != "Control") {
                // IE < 9
                document.selection.createRange().pasteHTML(html);
            }
        },
        saveCaret: function(){
            if( $.browser.mozilla || this.isMsie() ){
                this.pasteHTML('<span id="caret" />');
            }
        },
        restoreCaret: function(){
            var iDocument = this.iframe[0].contentWindow.document;
            if( this.isMsie() ){
                var referenceNode = iDocument.getElementById("caret");
                var rng = iDocument.body.createTextRange();
                if( referenceNode ){
                    rng.moveToElementText(referenceNode);
                    $(referenceNode).removeAttr('id');
                }
                rng.select();
            }
            else if ( $.browser.mozilla ){
                var referenceNode = iDocument.getElementById("caret");
                if( referenceNode ){
                    var r = this.getRange();
                    r.selectNode(referenceNode);
                    r.deleteContents();
                }
            }
            
        },


        //        pasteHTML: function (szHTML) {
        //			var img, range;
        //
        //			if (!szHTML || szHTML.length === 0) {
        //				return this;
        //			}
        //
        //			if ($.browser.msie) {
        //				this.iframe[0].contentWindow.focus();
        //				this.editor.execCommand("insertImage", false, "#jwysiwyg#");
        //				img = this.getElementByAttributeValue("img", "src", "#jwysiwyg#");
        //				if (img) {
        //					$(img).replaceWith(szHTML);
        //				}
        //			} else {
        //				if ($.browser.mozilla) {
        //					if (1 === $(szHTML).length) {
        //						range = this.getInternalRange();
        //						range.deleteContents();
        //						range.insertNode($(szHTML).get(0));
        //					} else {
        //						this.editor.execCommand("insertHTML", false, szHTML);
        //					}
        //				} else {
        //					if (!this.editor.execCommand("insertHTML", false, szHTML)) {
        //						this.iframe[0].contentWindow.focus();
        //						/* :TODO: place caret at the end
        //						if (window.getSelection) {
        //						} else {
        //						}
        //						this.editor.focus();
        //						*/
        //						this.editor.execCommand("insertHTML", false, szHTML);
        //					}
        //				}
        //			}
        //
        //			this.updateTextArea();
        //		},

        bold: function() {
            this.ec('bold');
        },
        italic: function() {
            this.ec("italic");
        },
        underline: function() {
            this.ec("underline");
        },

        insertImage: function(params){
            this.restoreCaret();
            if( params.preview ){
                $html = $('<div><a href="'+params.src+'" target="_blank"><img style="padding:5px;max-width:100%" src="'+params.src+'" /></a></div>');
            }else{
                $html = $('<div><img style="padding:5px;max-width:100%" src="'+params.src+'" /></div>');
            }

            $img = $('img', $html);
            if( params.align ){
                
                if( params.align == 'center' ){
                    $img.css({
                        display:'block',
                        margin: '0 auto'
                    });
                }else{
                    $img.css({
                        'float':params.align
                        });
                }
            }
            if( params.resize ){
                $img.css({
                    'width':params.resize
                });
            }
            
            this.pasteHTML($html.html());
            this.tempFB.close();
            this.updateTextArea();
        },

        image: function(){
            this.saveCaret();
            this.tempFB = new OW_FloatBox({
                $title: window.htmlAreaData.labels.buttons.image,
                width: '600px',
                height: '600px',
                $contents: '<center><iframe style="min-width: 550px; min-height: 500px;" src="'+window.htmlAreaData.imagesUrl.replace('__id__', this.textarea.attr('id'))+'"></iframe></center>'
            });
        },
        video: function(){
            this.saveCaret();
            var self = this;
            var $contents = $('<div>'+window.htmlAreaData.labels.common.videoTextareaLabel+'<br /><textarea name="code" style="height:200px;"></textarea><br /><br /></div>');
            var buttonCode = window.htmlAreaData.buttonCode;
            $contents.append('<div style="text-align:center;">'+buttonCode.replace('#label#', window.htmlAreaData.labels.common.buttonInsert)+'</div>');
            $('input[type=button].mn_submit', $contents).click(function(){
                self.insertVideo({
                    code:$('textarea[name=code]', $contents).val()
                })
            });

            this.tempFB = new OW_FloatBox({
                $title: window.htmlAreaData.labels.common.videoHeadLabel,
                width: '600px',
                height: '400px',
                $contents: $contents
            });
            
            setInterval(function(){$('textarea[name=code]', $contents).focus()}, 100);
        },

        insertVideo: function(params){
            this.restoreCaret();
            if( !params || !params.code ){
                OW.error(window.htmlAreaData.labels.messages.videoEmptyField);
                return;
            }
            $html = $('<div><span class="ow_ws_video"></span></div>');
            $('span', $html).append(params.code);
            this.pasteHTML($html.html());
            this.tempFB.close();
        },
        html: function(){
            this.saveCaret();
            var self = this;
            var $contents = $('<div>'+window.htmlAreaData.labels.common.htmlTextareaLabel+'<br /><textarea name="code" style="height:200px;"></textarea><br /><br /></div>');
            var buttonCode = window.htmlAreaData.buttonCode;
            $contents.append('<div style="text-align:center;">'+buttonCode.replace('#label#', window.htmlAreaData.labels.common.buttonInsert)+'</div>');
            $('input[type=button].mn_submit', $contents).click(function(){
                self.addHtml({
                    code:$('textarea[name=code]', $contents).val()
                })
            });

            this.tempFB = new OW_FloatBox({
                $title: window.htmlAreaData.labels.common.htmlHeadLabel,
                width: '600px',
                height: '400px',
                $contents: $contents
            });
        },

        addHtml: function(params){
            this.restoreCaret();
            if( !params || !params.code ){
                OW.error(window.htmlAreaData.labels.messages.videoEmptyField);
                return;
            }
            $html = $('<div><span class="ow_ws_html"></span></div>');
            $('span', $html).append(params.code);
            this.pasteHTML($html.html());
            this.tempFB.close();
        },

        insertLink: function(params){
            this.restoreCaret();
            if( !params || !params.url || !params.label ){
                OW.error(window.htmlAreaData.labels.messages.linkEmptyFields);
                return;
            }

            this.pasteHTML('<span class="ow_ws_link"><a rel="nofollow" href="'+params.url+'"'+ ( params.newWindow ? ' target="_blank"' : '') +'>'+params.label+'</a></span>');
            this.tempFB.close();
        },

        link: function() {
            this.saveCaret();
            var self = this;
            var $contents = $('<div style="padding:10px 35px;">'+window.htmlAreaData.labels.common.linkTextLabel+'<br /><input name="wLabel" type="text" style="width:400px;" /><br /><br />'+window.htmlAreaData.labels.common.linkUrlLabel+'<br /><input type="text" value="http://" name="url" style="width:400px;" /><br /><br /><label><input type="checkbox" name="new_window" checked="checked" /> '+window.htmlAreaData.labels.common.linkNewWindowLabel+'</label><br /><br /><br /></div>');
            var buttonCode = window.htmlAreaData.buttonCode;
            $contents.append('<div style="width:400px;text-align:center;">'+buttonCode.replace('#label#', window.htmlAreaData.labels.common.buttonInsert)+'</div>');
            $('input[type=button].mn_submit', $contents).click(function(){
                self.insertLink({
                    label:$('input[name=wLabel]', $contents).val(),
                    url:$('input[name=url]', $contents).val(),
                    newWindow:$('input[name=new_window]', $contents)[0].checked
                })
            });

            var fbInput = $('input[name=wLabel]', $contents);
            
            this.tempFB = new OW_FloatBox({
                $title: window.htmlAreaData.labels.buttons.link,
                width: '500px',
                height: '300px',
                $contents: $contents
            });
            
            setTimeout(function(){fbInput.focus()}, 100);
            
            
        },
        quote: function(){

        },
        more: function(){
            $html = $('<div></div>');
            $html.append(document.createTextNode('<!--more-->'));
            this.pasteHTML($html.html());
        },
        formatBlock: function(v) {
            this.ec("formatblock", false, v || null);
        },
        orderedList: function() {
            this.ec("insertorderedlist");
        },
        unorderedList: function() {
            this.ec("insertunorderedlist");
        },

        showHTMLView: function() {
            this.updateTextArea();
            this.textarea.show();
            this.htmlarea.hide();
            $("ul li:not(li:has(a.switchHtml))", this.toolbar).hide();
            $("ul:not(:has(:visible))", this.toolbar).hide();
            $("ul li a.html", this.toolbar).addClass("highlighted");
        },
        hideHTMLView: function() {
            this.updateHtmlArea();
            this.textarea.hide();
            this.htmlarea.show();
            $("ul", this.toolbar).show();
            $("ul li", this.toolbar).show().find("a.html").removeClass("highlighted");
        },
        toggleHTMLView: function() {
            (this.textarea.is(":hidden")) ? this.showHTMLView() : this.hideHTMLView();
        },

        toHtmlString: function() {
            return this.editor.body.innerHTML;
        },
        toString: function() {
            return this.editor.body.innerText;
        },

        updateTextArea: function() {
            var newContent = $("<div/>").addClass("temp").append(this.toHtmlString());
            newContent.children("div").each(function () {
                var element = $(this), p = element.find("p"), i;

                if (0 === p.length) {
                    p = $('<p style="margin:0;"></p>');

                    if (this.attributes.length > 0) {
                        for (i = 0; i < this.attributes.length; i += 1) {
                            p.attr(this.attributes[i].name, element.attr(this.attributes[i].name));
                        }
                    }

                    p.append(element.html());

                    element.replaceWith(p);
                }
            });
            
            this.textarea.val(newContent.html());
        },

        updateToolbar: function(){
            var self = this;
            $.each(this.toolbarArray,
                function( key, $item ){
                    if( $item.toolbarHandler.call(self) ){
                        $item.addClass('ow_ws_active');
                    }
                    else{
                        $item.removeClass('ow_ws_active');
                    }
                }
                );
        },

        updateHtmlArea: function() {
            this.editor.body.innerHTML = this.textarea.val();
        }
    };

    jHtmlArea.fn.init.prototype = jHtmlArea.fn;

    var priv = {
        toolbarButtons: {
            orderedlist: function(btn){
                this.orderedList(btn);
            },
            unorderedlist: function(btn){
                this.unorderedList(btn)
            },
            bold: function(btn){
                this.bold(btn);
            },
            italic: function(btn){
                this.italic(btn);
            },
            underline: function(btn){
                this.underline(btn);
            },
            image: function(btn){
                this.image(btn)
            },
            link: function(btn){
                this.link(btn)
            },
            switchHtml: function(btn){
                this.toggleHTMLView(btn);
            },
            video: function(btn){
                this.video(btn);
            },
            html: function(btn){
                this.html(btn);
            },
            more: function(btn){
                this.more(btn);
            }
        },
        toolbarHandlers:{
            bold: function(){
                return this.qcv('bold');
            },
            italic: function(){
                return this.qcv('italic');
            },
            underline: function(){
                return this.qcv('underline');
            },
            orderedlist: function(){
                return this.qcv('insertorderedlist');
            },
            unorderedlist: function(){
                return this.qcv('insertunorderedlist');
            }
        },
        initEditor: function(options) {
            var self = this;
            var edit = this.editor = this.iframe[0].contentWindow.document;
            edit.designMode = 'on';
            edit.open();
            edit.write(this.textarea.val());
            edit.close();
        },
        initToolBar: function(options) {
            this.toolbarArray ={};
            var self = this;
            var menuItem = function(className, altText, action) {
                self.toolbarArray[className] = $("<li></li>");
                self.toolbarArray[className].append($('<a href="javascript://"></a>').addClass(className).attr("title", altText).click(
                    function(){
                        action.call(self, $(this));
                        self.updateToolbar();
                    }
                    ));
                self.toolbarArray[className].toolbarHandler = priv.toolbarHandlers[className] ? priv.toolbarHandlers[className] : function(){};
                return self.toolbarArray[className];
            };

            function addButtons(arr) {
                var ul = $("<ul></ul>").appendTo(self.toolbar);

                if( arr.switchHtml ){
                    ul.addClass('switch_html');
                }

                $.each(arr, function( bkey, blabel ){
                    if( bkey === '|' ){
                        ul.append($('<li class="separator"></li>'));
                    }else{
                        ul.append(menuItem(bkey, blabel, function(btn){
                            priv.toolbarButtons[bkey].call(this, btn);
                        }));
                    }
                });
            };

            for (var i = 0; i < options.toolbar.length; i++){
                addButtons(options.toolbar[i]);
            }
        },
        attachEditorEvents: function() {
            var self = this;

            // need to delete textarea edit -> delete update htmlarea
            var fnHA = function() {
                self.updateHtmlArea();
            };

            this.textarea.click(fnHA).
            keyup(fnHA).
            keydown(fnHA).
            mousedown(fnHA).
            blur(fnHA);

            var fnTA = function() {
                self.updateTextArea();
            };
            var fnTb = function(){
                self.updateToolbar();
            }
            $(this.editor).keyup(fnTb).mouseup(fnTb);
            $(this.editor).blur(fnTA).keyup(fnTA).mouseup(fnTA);
            $('form').submit(function() {
                self.toggleHTMLView();
                self.toggleHTMLView();
            });
        }
    };
})(jQuery);