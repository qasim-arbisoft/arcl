/* global jQuery */
jQuery(document).ready(function () {
	"use strict";
	
	// Donations form handlers
	if (jQuery('.sc_donations_form').length > 0) {

		// Checkbox with "I agree..."
		if (jQuery('input[type="checkbox"][name="i_agree_privacy_policy"]:not(.sc_donations_inited)').length > 0) {
			jQuery('input[type="checkbox"][name="i_agree_privacy_policy"]:not(.sc_donations_inited)')
				.addClass('sc_donations_inited')
				.on('change', function(e) {
					if (jQuery(this).get(0).checked)
						jQuery(this).parents('form').find('.sc_donations_form_field_button').slideDown();
					else
						jQuery(this).parents('form').find('.sc_donations_form_field_button').slideUp();
				});
		}

		// Change amount
		jQuery('.sc_donations_form .sc_donations_form_field_amount input[type="radio"]').click(function(e) {
			"use strict";
			jQuery(this).siblings('.sc_donations_form_amount').val(jQuery(this).val());
		});
		jQuery('.sc_donations_form .sc_donations_form_amount').focus(function() {
			"use strict";
			jQuery(this).siblings('input[type="radio"]').removeAttr('checked');
			jQuery(this).siblings('#sc_donations_form_amount_0').attr('checked', 'checked');
		});

		// Hide result window after 5 sec.
		if (jQuery('.sc_donations_form .sc_donations_result').length > 0) {
			jQuery('body,html').scrollTo('.sc_donations_form', {offsetTop : '100'});
			setTimeout(function() {
				"use strict";
				jQuery('.sc_donations_form .sc_donations_result').fadeOut();
			}, 5000);
		}

		// PayPal Button
		var form = jQuery('.sc_donations_form form'),
			pp_env = form.data('pp_env'),
			pp_client = form.data('pp_client'),
			pp_currency = TRX_DONATIONS_STORAGE['pp_currency'];
		var form_result = form.find('.sc_donations_result');
		if (form_result.length == 0)
			form_result = form.append('<div id="sc_donations_result" class="sc_donations_result sc_donations_error"></div>').find('.sc_donations_result').hide();
		
		function trx_donations_toggle_button(actions) {
			if (form.find('#sc_donations_form_amount').val() <= 0
				|| form.find('#sc_donations_form_email').val() == ''
				|| form.find('#sc_donations_form_name').val() == '')
				actions.disable();
			else
				actions.enable();
		}

		function trx_donations_check_fields() {
			var error = false;
			form.find('*').removeClass('trx_donations_form_error');
			if (form.find('#sc_donations_form_amount').val() <= 0) {
				error = true;
				form.find('#sc_donations_form_amount').addClass('sc_donations_form_error');
			}
			if (form.find('#sc_donations_form_name').val() == '') {
				error = true;
				form.find('#sc_donations_form_name').addClass('sc_donations_form_error');
			}
			if (form.find('#sc_donations_form_email').val() == '') {
				error = true;
				form.find('#sc_donations_form_email').addClass('sc_donations_form_error');
			}
			if (error) {
				form_result.html('<p>' + TRX_DONATIONS_STORAGE['msg_fields_error'] + '</p>').fadeIn();
				setTimeout(function() {
					form_result.fadeOut();
				}, 5000);
			} else
				form_result.hide();
		}
		
		paypal.Button.render({
	
			env: pp_env, // sandbox | production
	
			// PayPal Client IDs - replace with your own
			// Create a PayPal app: https://developer.paypal.com/developer/applications/create
			client: {
				sandbox:    pp_env == 'sandbox' ? pp_client : '',
				production: pp_env != 'sandbox' ? pp_client : ''
			},
			
			// Style of the PayPal button
			style: {
				label: 'paypal',	// checkout | paypal | pay | credit
				size:  'medium',	// small | medium | large | responsive
				shape: 'rect',		// pill | rect
				color: 'blue'		// gold | blue | silver | black
			},	
			// Show the buyer a 'Pay Now' button in the checkout flow
			commit: true,

			// Validate form
			validate: function(actions) {
				trx_donations_toggle_button(actions);
				form.find('#sc_donations_form_amount,#sc_donations_form_email,#sc_donations_form_name').on('change', function() {
					trx_donations_toggle_button(actions);
				});
			},

			// Validate form
			onClick: function() {
				trx_donations_check_fields();
			},

			// Cancel payment
			onCancel: function() {
				form_result.html('<p>' + TRX_DONATIONS_STORAGE['msg_cancel_payment'] + '</p>').fadeIn();
				setTimeout(function() {
					form_result.fadeOut();
				}, 5000);
			},
	
			// payment() is called when the button is clicked
			payment: function(data, actions) {
				// Send payments data to our server
				jQuery.post(TRX_DONATIONS_STORAGE['ajax_url'], {
					action: 'donation_start',
					nonce: TRX_DONATIONS_STORAGE['ajax_nonce'],
					data: form.serialize()
				}).done(function(response) {
					var rez = {};
					if (response=='' || response==0) {
						rez = { error: TRX_DONATIONS_STORAGE['msg_ajax_error'] };
					} else {
						try {
							rez = JSON.parse(response);
						} catch (e) {
							rez = { error: TRX_DONATIONS_STORAGE['msg_ajax_error'] };
							console.log(response);
						}
					}
					if (rez.error !== '') console.error(rez.error);
				});
				// Make a call to the REST api to create the payment
				return actions.payment.create({
					payment: {
						transactions: [
							{
								amount: { total: form.find('input[name="amount"]').val(), currency: pp_currency },
								//description: form.find('textarea[name="message"]').val(),
							}
						]
					}
				});
			},
	
			// onAuthorize() is called when the buyer approves the payment
			onAuthorize: function(data, actions) {
				// Make a call to the REST api to execute the payment
				return actions.payment.execute().then(function() {
					window.location.href = TRX_DONATIONS_STORAGE['finish_donation_url'] + '&trx_donations_pp_code=' + form.find('#sc_donations_form_donation_code').val();
				});
			}
	
		}, '#paypal-button-container');
	}
});

jQuery.fn.scrollTo = function(target, options, callback) {
	"use strict";
	if (typeof options == 'function' && arguments.length == 2) { callback = options; options = target; }
	var settings = jQuery.extend({
		scrollTarget  : target,
		offsetTop     : 50,
		duration      : 500,
		easing        : 'swing'
		}, options);
	return this.each(function() {
		"use strict";
		var scrollPane = jQuery(this);
		var scrollTarget = (typeof settings.scrollTarget == "number") ? settings.scrollTarget : jQuery(settings.scrollTarget);
		var scrollY = (typeof scrollTarget == "number") ? scrollTarget : scrollTarget.offset().top - parseInt(settings.offsetTop);
		scrollPane.animate({scrollTop: scrollY}, parseInt(settings.duration), settings.easing, function() {
			"use strict";
			if (typeof callback == 'function') { callback.call(this); }
		});
	});
}
