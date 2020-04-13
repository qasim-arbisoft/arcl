<?php
/**
 * Default template to display the 'add to cart' block on the single page
 *
 * @package WordPress
 * @subpackage ThemeREX Addons
 * @since v1.6.34
 */

$trx_addons_args = get_query_var('trx_addons_args_sc_edd_add_to_cart');

?><div<?php
		if (!empty($trx_addons_args['id'])) echo ' id="'.esc_attr($trx_addons_args['id']).'"';
		if (!empty($trx_addons_args['class'])) echo ' class="'.esc_attr($trx_addons_args['class']).'"';
		if (!empty($trx_addons_args['css'])) echo ' style="'.esc_attr($trx_addons_args['css']).'"';
?>><?php
	trx_addons_edd_themes_market_add_buttons();
?></div>