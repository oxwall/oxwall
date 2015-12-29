var ThemesSelect = function(themeList, addData)
{
    var $msgNode = $('.delete_confirm_node .message'),
            $fbNode = $('.delete_confirm_node'),
            $delBtn = $('.delete_confirm_node input.theme_select_delete'),
            $btn = $('input.theme_select_delete_btn'),
            activeThemeMsg = function() {
                new OW_FloatBox({$contents: '<span>' + addData.deleteActiveThemeMsg + '</span>', width: '400px'});
            },
            inactiveThemeMsg = function() {
                new OW_FloatBox({$contents: $fbNode, width: '400px'});
            };

    $btn.click(activeThemeMsg);

    $.each(themeList, function() {
        $('.themes_select a.' + this.key).bind('click', {data: this},
        function(e) {
            $('.theme_info .theme_control_button').show();
            $('.themes_select a').removeClass('clicked');
            $(this).addClass('clicked');

            $('.themes_select .theme_item').removeClass('theme_clicked');
            $(this).parent().addClass('theme_clicked');

            $context = $('.selected_theme_info');
            $('.theme_icon', $context).css({backgroundImage: 'url(' + e.data.data.previewUrl + ')'});
            $('.theme_title', $context).empty().append(e.data.data.title);
            $('.theme_desc', $context).empty().append(e.data.data.description);
            $('.theme_preview img', $context).attr('src', e.data.data.previewUrl);
            $('.author', $context).empty().append(e.data.data.author);
            $('.author_url', $context).empty().append('<a href="' + e.data.data.authorUrl + '">' + e.data.data.authorUrl + '</a>');

            $btn.unbind('click');

            if (e.data.data.delete_url) {
                $btn.closest('.dlt_btn').show();
            } else {
                $btn.closest('.dlt_btn').hide();
            }
            
            if (e.data.data.license_url) {
                $('.lsn_btn input').click(function(){window.location.replace(e.data.data.license_url);});
                $('.lsn_btn').show();
            } else {
                $('.lsn_btn').hide();
            }

            if (e.data.data.active) {
                $btn.click(activeThemeMsg);
            } else {
                $msgNode.html(addData.deleteConfirmMsg.replace('#theme#', e.data.data.name));
                $btn.click(inactiveThemeMsg);
                $delBtn.click(function() {
                    window.location.replace(e.data.data.delete_url);
                });
            }

            var url = e.data.data.changeUrl;
            $('.selected_theme_info input.theme_select_submit').unbind('click').click(
                    function() {
                        window.location = url;
                    });
        }
        );
    });


}