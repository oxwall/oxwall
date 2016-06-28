var RangeField = function( $name, $minValue, $maxValue  )
{
    var self = this;

    var $cont = $('.'+$name);

    this.toValue = $cont.find("select[name='" + $name + "[to]']");
    this.fromValue = $cont.find("select[name='" + $name + "[from]']");

    this.minAge = $minValue;
    this.maxAge = $maxValue;

    this.toValue.change( function()
    {
        self.updateValue();

        if( parseInt(self.fromValue.val()) > parseInt(self.toValue.val()) )
        {
            self.fromValue.val(self.toValue.val());
        }
    } );

    this.fromValue.change( function()
    {
        self.updateValue();

        if( parseInt(self.fromValue.val()) > parseInt(self.toValue.val()) )
        {
            self.toValue.val(self.fromValue.val());
        }
    } );

    this.updateValue = function()
    {
        if( parseInt(self.fromValue.val()) < parseInt(self.minAge) )
        {
            self.fromValue.val(self.minAge);
        }

        if( parseInt(self.toValue.val()) < parseInt(self.minAge) )
        {
            self.toValue.val(self.minAge);
        }

        if( parseInt(self.fromValue.val()) > parseInt(self.maxAge) )
        {
            self.fromValue.val(self.maxAge);
        }

        if( parseInt(self.toValue.val()) > parseInt(self.maxAge) )
        {
            self.toValue.val(self.maxAge);
        }
    }
}
