var DateField = function( $name )
{
    var self = this;

    var $cont = $("div[class='" + $name + "']");

    var $hidden = $cont.find("input[name='" + $name + "']");

    this.day = $cont.find("select[name='day_" + $name + "']");
    this.month = $cont.find("select[name='month_" + $name + "']");
    this.year = $cont.find("select[name='year_" + $name + "']");

    this.dayFirstOption = self.day.find('option[value=\'\']');

    this.day.change( function()
    {
        self.updateValue();
    });

    this.year.change( function()
    {
        self.changeMonth();
    });

    this.month.change( function()
    {
        self.changeMonth();
    });

    this.changeMonth = function()
    {
        if( self.month.val() != ''  & self.year.val() != ''  )
        {
            var $dayVal = 0;

            if ( self.day.val() != '');
            {
                $dayVal = self.day.val();
            }

            var $monthVal = self.month.val(); // parse date into variables
            var $yearVal = self.year.val();

            if ( $monthVal < 1 )
            {
                self.month.val(1);
            }

            if( $monthVal > 12 )
            {
                self.month.val(12);
            }

            if ( $monthVal==4 || $monthVal==6 || $monthVal==9 || $monthVal==11 )
            {
                self.updateDays($dayVal, 30);
            }
            else if ( $monthVal == 2 )
            {
                var isleap = ($yearVal % 4 == 0 && ($yearVal % 100 != 0 || $yearVal % 400 == 0));

                if ( isleap )
                {
                    self.updateDays($dayVal, 29);
                }
                else
                {
                    self.updateDays($dayVal, 28);
                }
            }
            else
            {
                self.updateDays($dayVal, 31);
            }


       }
       self.updateValue();
    }

    this.updateDays = function( $dayValue, $daysCount )
    {
        if( self.day.find('option[value!=""]').size() == $daysCount )
        {
            return;
        }

        self.day.find('option[value!=\'\']').remove();

        if( !$dayValue )
        {
            $dayValue = '';
        }
        else if ( $dayValue > $daysCount )
        {
            $dayValue = $daysCount;
        }

        for ( var i=1; i<= $daysCount; i++ )
        {
            var $option = this.dayFirstOption.clone();

            $option.text(i);
            $option.val(i);

            if ( $dayValue == i )
            {
                $option.attr('selected', 'selected');
            }

            self.day.append($option);
        }
    }

    this.updateValue = function()
    {
        if( self.day.val() != '' && self.month.val() != ''  && self.year.val() != ''  )
        {
            $hidden.val( self.year.val() + '/' + self.month.val() + '/' + self.day.val() );
        }
        else
        {
            $hidden.val('');
        }
    }
}