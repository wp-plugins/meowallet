(function($){

"use strict";

var wc_plugin_meowallet = {
  el: {
    $form: undefined,
    $payment_box: undefined
  },
  order_id: '',

  /**
   * Event Binding
   */
  event_binding: function() {
    this.el.$form.on('checkout_place_order', $.proxy( this.submit_form, this ));
    $('body').on('updated_checkout', $.proxy( this.update_form_value, this ));
  },

  validate_checkout_form: function( meowallet_error ) {
    var $form = this.el.$form,
        validate_message = '';

    // Remove old errors
    $('.woocommerce-error, .woocommerce-message').remove();

    // Show validate message
    $form.find('.woocommerce-invalid').each(function(){
      var $this = $(this),
          label = $this.find('label').text().replace('*', '');
    });
    
    if( meowallet_error ) {
      if( veritrans_error.status == 'FAIL' ) {
        meowallet_error.message = meowallet_error.message.replace('[','').replace(']','');
        validate_message += '<li><strong>MEO WALLET Error: </strong> '+ meowallet_error.message +'</li>';
      }
    }

    // Show error message if form is not valid
    if( $form.find('.woocommerce-invalid').length > 0 || meowallet_error ) {
      $('<ul class="woocommerce-error">'+ validate_message +'</ul>').prependTo( $form );
    }
  },

  /**
   * Callback when response from Wallet is success
   */
  _success: function(res) {
    var token_id = res.data.id,
        order_id = this.order_id,
        $form = this.el.$form;
  
    // Fill the token id field
    $('[name="meowallet_trans_id"]').val( res.data.id );

    // Validate checkout form
    this.validate_checkout_form();

    // Request token succeed, process the form submission
    this.submit_form_ajax();
  },

  /** 
   * Callback when response from Veritrans is error
   */
  _error: function(res) {
    var message = res.message.replace('[','').replace(']',''),
        $form = this.el.$form;

    // Remove old errors
    $('.woocommerce-error, .woocommerce-message').remove();

    // Add new errors
    if ( res.message ) $form.prepend( res.messages );

    // Cancel processing
    $form.removeClass('processing').unblock();

    // Lose focus for all fields
    $form.find( '.input-text, select' ).blur();

    this.validate_checkout_form( res );

    // Scroll to top
    $('html, body').animate({
        scrollTop: ($('form.checkout').offset().top - 100)
    }, 1000);
  },

  /**
   * Submit Checkout Form
   */
  submit_form: function( e ) {
    var _this = this,
        $form = this.el.$form,
        form_data = $form.data();
    
    $form.addClass('processing');

    if ( form_data["blockUI.isBlocked"] != 1 )
      $form.block({message: null, overlayCSS: {background: '#fff url(' + woocommerce_params.ajax_loader_url + ') no-repeat center', backgroundSize: '16px 16px', opacity: 0.6}});

    return false;
  },

  /**
   * Submit Form Ajax
   */
  submit_form_ajax: function() {
    var _this = this,
        $form = this.el.$form,
        form_data = $form.data(),
        serialized_data = $form.serialize();
        
    $.ajax({
      type:     'POST',
      url:      wc_checkout_params.checkout_url,
      data:     serialized_data,
      success:  function( code ) {
        var result = '';
        
        try {
          // Get the valid JSON only from the returned string
          if ( code.indexOf("<!--WC_START-->") >= 0 )
            code = code.split("<!--WC_START-->")[1]; // Strip off before after WC_START

          if ( code.indexOf("<!--WC_END-->") >= 0 )
            code = code.split("<!--WC_END-->")[0]; // Strip off anything after WC_END
         // debugger;
          // Parse
          result = $.parseJSON( code );

          if ( result.result == '200' ) {

            window.location = decodeURI(result.redirect);

          } else if ( result.result == 'FAIL' ) {
            throw "Result failure";
          } else {
            throw "Invalid response";
          }
        }
        catch( err ) {
          // Remove old errors
          $('.woocommerce-error, .woocommerce-message').remove();

          // Add new errors
          if ( result.messages )
            $form.prepend( result.messages );
          else
            $form.prepend( code );

          // Cancel processing
          $form.removeClass('processing').unblock();

          // Lose focus for all fields
          $form.find( '.input-text, select' ).blur();

          // Scroll to top
          $('html, body').animate({
              scrollTop: ($('form.checkout').offset().top - 100)
          }, 1000);

          // Trigger update in case we need a fresh nonce
          if ( result.refresh == 'true' )
            $('body').trigger('update_checkout');
        }
      },
    });
  },

  /**
   * Initialize function
   */
  init: function() {
    meowallet.apikey = wc_mw_apikey;

    this.el.$form = $('form.checkout');
    //this.el.$payment_box = $('.payment_methods_list .payment_method_veritrans');

    this.event_binding();
  }
};

$(document).ready(function(){
  wc_plugin_meowallet.init();
});

})(jQuery);