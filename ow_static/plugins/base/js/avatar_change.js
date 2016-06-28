var avatarChange = function( params )
{
    $.event.props.push('dataTransfer');
    
    this.params = params;

    this.$dropArea = $('#avatar-drop-area');
    this.$dropAreaLabel = $('#avatar-drop-area-label');
    this.$fileInput = $('#avatar-change-file-input');

    this.$preloader = $('#avatar-change-preloader');
    this.$step1 = $("#avatar-change-step-1");
    this.$step2 = $("#avatar-change-step-2");

    this.$backButton = $('#avatar-crop-back-btn');
    this.$cropButton = $('#avatar-crop-btn');
    this.$cropPreview = $('#avatar-crop-preview');
    this.$cropTarget = $('#avatar-crop-target');

    this.fileTypes = ['image/jpeg', 'image/png', 'image/gif'];
    this.coords = {};
    this.jcropApi = {};

    // selected image params
    this.url = '';
    this.entityType = '';
    this.entityId = '';
    this.id = '';

    var self = this;

    this.cropPreview = function showPreview(coords) {
        var rx = self.params.minCropSize / coords.w;
        var ry = self.params.minCropSize / coords.h;

        self.coords = coords;
        self.$cropPreview.css({
            width: Math.round(rx * self.$cropTarget.width()) + 'px',
            height: Math.round(ry * self.$cropTarget.height()) + 'px',
            marginLeft: '-' + Math.round(rx * coords.x) + 'px',
            marginTop: '-' + Math.round(ry * coords.y) + 'px'
        });
    };

    this.initCrop = function( callback ){

        var ts = new Date().getTime();
        var url = self.url + "?" + ts;

        if (!$.isEmptyObject(self.jcropApi)) { // reset jcrop api
            self.jcropApi.destroy();
        }

        var image = new Image();
        image.onload = function(e) {
            var width = this.width;
            var height = this.height;

            self.$cropTarget.attr('src', url);
            self.$cropPreview.attr('src', url);

            document.avatarFloatBox.$header.find(".floatbox_title span").html(OW.getLanguageText('base', 'avatar_crop'));
            var cropWidth = document.avatarFloatBox.$body.width() - self.params.minCropSize - 16;

            $(".ow_avatar_crop_area").css({ width: cropWidth });

            $(".ow_avatar_crop_result").css({ width: self.params.minCropSize });
            $(".avatar_crop_preview_wrap").css({ height: self.params.minCropSize });
            self.$cropPreview.css({ width: self.params.minCropSize, height: self.params.minCropSize });

            if (height < self.params.minCropSize) {
                self.$cropTarget.css({ height: self.params.minCropSize });
            }

            self.$cropTarget.Jcrop({
                onChange: self.cropPreview,
                onSelect: self.cropPreview,
                boxWidth: cropWidth,
                minSize: [ self.params.minCropSize, self.params.minCropSize ],
                aspectRatio: 1
            },function(){
                self.jcropApi = this;
                if (width >= self.params.minCropSize && height >= self.params.minCropSize) {
                    self.jcropApi.setSelect(self.getCropInitialSelection(width, height));
                }
                self.$step1.hide();
                self.$step2.show();
                
                if ( callback && typeof(callback) === "function" )
                {
                    callback();
                }
            });
        };
        image.src = url;
    };

    if ( self.params.step == 2 )
    {
        self.$backButton.closest(".ow_button").hide();

        // join form
        if ( self.params.inputId ) {

            self.$step2.hide();
            OW.inProgressNode(self.$preloader);
            self.$preloader.show();

            var file = document.getElementById(self.params.inputId).files[0];

            if ( file ) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var image = new Image();
                    image.onload = function(e) {
                        var width = this.width;
                        var height = this.height;
                        if (width < self.params.minCropSize || height < self.params.minCropSize) {
                            OW.error(OW.getLanguageText('base', 'avatar_image_too_small', { width : self.params.minCropSize, height : self.params.minCropSize }));
                            document.avatarFloatBox.close();
                            $("#" + self.params.inputId).val("");
                        }
                        else {
                            var fd = new FormData();
                            fd.append('file', file);
                            self.uploadFile(fd);
                        }
                    };
                    image.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }
        // pre-selected item from library
        else if ( self.params.url && self.params.entityType && self.params.id ) {
            self.url = self.params.url;
            self.entityType = self.params.entityType;
            self.entityId = self.params.entityId;
            self.id = self.params.id;

            self.initCrop(function(){ self.$preloader.hide(); });
        }
    }

    this.checkFileType = function(fileType) {
        return self.fileTypes.indexOf(fileType.toLowerCase()) !== -1;
    };

    this.getCropInitialSelection = function(width, height){
        var xC = width / 2, yC = height / 2;
        var halfSize = width < height ? width / 2 : height / 2;
        var padding = width < height ? width * 0.05 : height * 0.05;
        halfSize = halfSize - padding;

        var x1 = xC - halfSize, y1 = yC - halfSize;
        var x2 = xC + halfSize, y2 = yC + halfSize;

        return [x1, y1, x2, y2];
    };

    this.$cropButton.click(function() {
        var button = $(this);
        OW.inProgressNode(button);
        
        var params = { ajaxFunc: 'ajaxCropPhoto', coords: self.coords, view_size: self.$cropTarget.width() };

        if ( self.entityType && self.id ) {
            params.entityType = self.entityType;
            params.entityId = self.entityId;
            params.id = self.id;
        }
        else {
            params.url = self.url;
        }

        // do we need change avatar?
        params.changeUserAvatar = self.params.changeUserAvatar ? 1 : 0;

        $.ajax({
            url: self.params.ajaxResponder,
            type: 'POST',
            data: params,
            dataType: 'json',
            success: function(data) {
                if (data.result) 
                {
                    if ( !self.params.inputId ) 
                    {
                        OW.info(OW.getLanguageText('base', 'avatar_changed'));
                    }

                    OW.trigger('base.avatar_cropped', data); // Listen this event to update interface
                    document.avatarFloatBox.close();
                }
                else 
                {
                    if ( data.error )
                    {
                        OW.error(data.error);
                    }
                    else
                    {
                        OW.error(OW.getLanguageText('base', 'crop_avatar_failed'));
                    }
                }
                
                OW.activateNode(button);
            }
        });
    });

    this.$backButton.click(function(){
        self.$step2.hide();
        self.$step1.show();
        document.avatarFloatBox.$header.find(".floatbox_title span").html(OW.getLanguageText('base', 'avatar_change'));
    });

    this.$dropArea.click(function(){
        self.$fileInput.trigger('click');
    });

    self.$fileInput.change(function(e){
        var files = e.target.files || (e.dataTransfer && e.dataTransfer.files);

        if ( !files || !self.checkFileType(files[0].type) ) {
            OW.error(OW.getLanguageText('base', 'not_valid_image'));

            return;
        }

        var height = self.$step1.height();
        self.$preloader.height(height);

        self.$step1.hide();
        OW.inProgressNode(self.$preloader);
        self.$preloader.show();

        var reader = new FileReader();
        reader.onload = function(e) {
            var image = new Image();
            image.onload = function(e) {
                var width = this.width;
                var height = this.height;
                if (width < self.params.minCropSize || height < self.params.minCropSize) {
                    OW.error(OW.getLanguageText('base', 'avatar_image_too_small', { width : self.params.minCropSize, height : self.params.minCropSize }));
                    
                    self.$step1.show();
                    OW.activateNode(self.$preloader);
                    self.$preloader.hide();
                }
                else {
                    var fd = new FormData();
                    fd.append('file', files[0]);
                    self.uploadFile(fd);
                }
            };
            image.src = e.target.result;
        };
        reader.readAsDataURL(files[0]);
    });

    this.$dropArea.on('dragenter', function(e) {
        e.stopPropagation();
        e.preventDefault();
        self.$dropAreaLabel.html(OW.getLanguageText('base', 'drop_image_here'));
    });
    this.$dropArea.on('dragleave', function(e) {
        self.$dropAreaLabel.html(OW.getLanguageText('base', 'drag_image_or_browse'));
    });
    this.$dropArea.on('drop', function(e){
        e.preventDefault();

        var files = e.target.files || (e.dataTransfer && e.dataTransfer.files);
        
        if (files && files.length > 1) {
            OW.error(OW.getLanguageText('base', 'avatar_drop_single_image'));
        }
        else {
            if ( !files || !self.checkFileType(files[0].type) ) {
                OW.error(OW.getLanguageText('base', 'not_valid_image'));
            }
            else {
                var height = self.$step1.height();
                self.$preloader.height(height);
                
                OW.inProgressNode(self.$preloader);
                self.$preloader.show();
                
                self.$step1.hide();
                
                var fd = new FormData();
                fd.append('file', files[0]);
                self.uploadFile(fd);
            }
        }

        self.$dropAreaLabel.html(OW.getLanguageText('base', 'drag_image_or_browse'));
    });

    this.uploadFile = function(formData)
    {
        formData.append('ajaxFunc', 'ajaxUploadImage');

        var jqXHR = $.ajax({
            xhr: function() {
                var xhrobj = $.ajaxSettings.xhr();
                if (xhrobj.upload) {
                    xhrobj.upload.addEventListener('progress', function(event) {
                        var percent = 0;
                        var position = event.loaded || event.position;
                        var total = event.total;
                        if (event.lengthComputable) {
                            percent = Math.ceil(position / total * 100);
                        }
                    }, false);
                }
                return xhrobj;
            },
            url: self.params.ajaxResponder,
            type: "POST",
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            data: formData,
            success: function(data){
                if (data.result) {
                    var ts = new Date().getTime();
                    self.url = data.url;
                    
                    self.initCrop(function(){ self.$preloader.hide(); });
                }
                else
                {
                    if ( data.error )
                    {
                        OW.error(data.error);
                    }
                    else
                    {
                        OW.error('Undefined error!');
                    }
                    document.avatarFloatBox.close();
                }
            }
        });
    };

    $("body").on("click", ".ow_photo_avatar_hover", function(){
        var image = new Image();
        var $btn = $(this).find(".avatar_select");
        var node = $(this).parents(".ow_photo_item:eq(0)");
        
        $btn.hide();
        node.addClass("ow_avatar_preloader");
        var timeout;
        
        function hidePreolader () {
            node.removeClass("ow_avatar_preloader");
            $btn.show();
            timeout && window.clearTimeout(timeout);
        }
        
        var load = function(e) {                           
            
            if (this.width < self.params.minCropSize || this.height < self.params.minCropSize) {
                OW.error(OW.getLanguageText('base', 'avatar_image_too_small', { width : self.params.minCropSize, height : self.params.minCropSize }));
                hidePreolader();
            }
            else {
                self.id = $btn.data("id");
                self.url = $btn.data("url");
                self.entityId = $btn.data("eid");
                self.entityType = $btn.data("type");
                
                self.initCrop( hidePreolader );
            }
        };
        
        image.onload = load;
        
        timeout = window.setTimeout(hidePreolader, 30000);
        
        image.src = $btn.data("url");
    });

//    document.avatarFloatBox.bind('close', function(){
//        $("body").off("click", ".avatar_select");
//
//        if (self.params.step == 2 && self.params.inputId){
//            $("#" + self.params.inputId).val("");
//        }
//    });

    $(".avatar_load_more").click(function(){
        var $moreBtn = $(this);
        var $parent = $moreBtn.parent();
        var offset = $moreBtn.data("offset");

        $moreBtn.data("offset", offset + self.params.limit);

        $parent.addClass("ow_preloader");
        $.ajax({
            url: self.params.ajaxResponder,
            type: 'POST',
            data: { ajaxFunc: 'ajaxLoadMore', entityType : $moreBtn.data("type"), entityId : $moreBtn.data("id"), offset : offset },
            dataType: 'json',
            success: function(data){
                if (data.result) {
                    var $item = $parent.closest(".ow_photo_item_wrap");

                    $item.prev().show();
                    $item.before(data.markup);
                    OW.updateScroll($('.ow_photo_library_wrap'));
                    $parent.removeClass("ow_preloader");

                    if ( $moreBtn.data("offset") >= data.count )
                    {
                        $item.hide();
                    }
                }
            }
        });
    });
};