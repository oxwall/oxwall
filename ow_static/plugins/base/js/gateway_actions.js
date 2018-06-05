var gatewayExtraInfo = function() {

    var billingGatewayKey;
    var billingGatewayExtraInfoUrl;
    var langKey;

    this.init = function (params) {

        billingGatewayExtraInfoUrl = params['billingGatewayExtraInfoUrl'];
        langKey = params['langKey'];

        billingGatewayKey = $("[name='gateway[key]']").val();
        getExtraInfo(billingGatewayKey);

        OW.bind("core.gateway_changed",
            function(data){

                billingGatewayKey = data['gatewayKey'];

                getExtraInfo(billingGatewayKey);

            }
        );

        function getExtraInfo(billingGatewayKey) {
            $.ajax(
            {
                url: billingGatewayExtraInfoUrl,
                type: 'POST',
                dataType: 'json',
                data:
                {
                    billingGatewayKey: billingGatewayKey,
                    langKey: langKey
                },
                success: function( response )
                {
                    $("#billing_gateway_extra_information").html(response.extraInfo);
                }
            });
        }

    }
}


