var ColorPicker = function($parent, callback, colorC){
	var Fce = function(tag){return document.createElement(tag);}
	var FValidateColor = function(color){if(!color){return false;} if(color.match(new RegExp("^#(([0-9a-f]{3})|([0-9a-f]{6}))$","i"))) return true; else return false};
	var color = FValidateColor(colorC) ? colorC : '#ffffff'; 
	var colors  = new Array(
		new Array('ffffff','ffcccc','ffcc99','ffff99','ffffcc','99ff99','99ffff','ccffff','ccccff','ffccff'),
		new Array('cccccc','ff6666','ff9966','ffff66','ffff33','66ff99','33ffff','66ffff','9999ff','ff99ff'),
		new Array('c0c0c0','ff0000','ff9900','ffcc66','ffff00','33ff33','66cccc','33ccff','6666cc','cc66cc'),
		new Array('999999','cc0000','ff6600','ffcc33','ffcc00','33cc00','00cccc','3366ff','6633ff','cc33cc'),
		new Array('666666','990000','cc6600','cc9933','999900','009900','339999','3333ff','6600cc','993399'),
		new Array('333333','660000','993300','996633','666600','006600','336666','000099','333399','663366'),
		new Array('222222','550000','883300','883300','555500','004400','225555','000077','222288','441144'),
		new Array('000000','330000','663300','663333','333300','003300','003333','000066','330099','330033')
		);
	var $colorTable = $(Fce('tbody'));	
	var $colorTableWB = $(Fce('table')).attr('cellspacing','3').addClass('colorPicker').append($colorTable);
	var $coloredTd = $('<td colspan="5"></td>').css({backgroundColor:color});
	var $colorInput = $(Fce('input')).attr('type', 'text').attr('class', 'colorCode').attr('value', color).bind('keyup', 
		function(){
			if( FValidateColor($colorInput.attr('value')) ){ 
				color = $colorInput.attr('value');
				$coloredTd.css({backgroundColor:$colorInput.attr('value')});
			}
		}
	);
	var $colorInputSubmit = $(Fce('input')).attr('type', 'button').attr('value', 'Ok').attr('class', 'ow_ic_ok').bind('click', function(){callback(color);});

	var $colorInputSubmitWrapper = $('<span class="ow_ic_ok"></span>').append($colorInputSubmit);
	$colorInputSubmitWrapper = $('<span class="ow_button"></span>').append($colorInputSubmitWrapper);

	var $colorInputCont = $('<td colspan="5"></td>').append($colorInput).append($colorInputSubmitWrapper);
	var $inputTr = $(Fce('tr')).append($coloredTd).append($colorInputCont);
	$colorTable.append($inputTr);
	for(i=0;i<colors.length;i++){
		var $colorTr = $(Fce('tr'));
		for(j=0;j<colors[i].length;j++){
			var $colorTd = $(Fce('td')).css({backgroundColor:'#'+colors[i][j],cursor:'pointer'}).bind('click',{color:'#'+colors[i][j]},
				function(e){
					color = e.data.color;
					$coloredTd.css({backgroundColor:color});
					callback(color);
				}
			);
			$colorTr.append($colorTd);
		}
		$colorTable.append($colorTr);
	}

	$midDiv = $(Fce('div')).attr('class', 'special_block_mid').append($colorTableWB);
	$parent.append($(Fce('div')).attr('class', 'special_block_top')).append($midDiv).append($(Fce('div')).attr('class', 'special_block_bot'));
	
	this.setColor = function(colorP){
		if(!FValidateColor(color)){alert('Invalid color code!!!');return;}
		color = colorP;
		$coloredTd.css({backgroundColor:colorP});
		$colorInput.attr('value', colorP);
	}
	this.getColor = function(){return color;}
}
