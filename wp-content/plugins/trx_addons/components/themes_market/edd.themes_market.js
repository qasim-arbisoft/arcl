/**
 * Themes market: Admin utils
 *
 * @package WordPress
 * @subpackage ThemeREX Addons
 * @since v1.6.34
 */

jQuery(document).ready(function() {
	"use strict";
	
	// Recalc subtotals on options change
	jQuery('.edd_price_options.edd_multi_mode input[type="checkbox"]').on('change', function() {
		var opt_list = jQuery(this).parents('.edd_price_options');
		var total = 0;
		var curr = opt_list.find('.edd_price_option_price').html().replace(/[0-9\.,]/g, '');
		// Disable uncheck all elements
		if (opt_list.find('input[type="checkbox"]:checked').length == 0) {
			opt_list.find('li:first-child input[type="checkbox"]').get(0).checked = true;
		}
		// Calc subtotals
		opt_list.find('input[type="checkbox"]:checked').each(function() {
			var price = jQuery(this).data('price');
			price = isNaN(price) ? 0 : Number(price);
			total += price;
		});
		opt_list.find('.trx_addons_edd_purchase_subtotal_value').html(curr+total.formatMoney());
	});
});