(function($){
	function envOptions() {
        var environment_type = $('#woocommerce_select_mw_environment').val();
        
        var api_environment_string = environment_type + '_settings';

        $('.sensitive').closest('tr').hide();
        $('.' + api_environment_string).closest('tr').show();
    }

	$(document).ready(function(){
		
        $("#woocommerce_veritrans_select_mw_environment").on('change', function(e, data) {
            envOptions();
        });

        enOptions();
		
	});
})(jQuery);