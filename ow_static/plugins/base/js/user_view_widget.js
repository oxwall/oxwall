var UserViewWidget = function()
{
    var self = this;
    var $li = $(".user_view_menu li");
    var $table = $(".data_table");

    this.showSection = function(section){
        $li.removeClass("active");
        $table.hide();

        $(".section_" + section ).show();
        $(".menu_" + section ).addClass("active");
    }
}

