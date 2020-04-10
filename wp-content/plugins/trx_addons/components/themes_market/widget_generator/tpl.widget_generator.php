<?php
/**
 * The template to display the Widget Generator
 *
 * @package WordPress
 * @subpackage ThemeREX Addons
 * @since v1.6.34
 */

/*
Template Name: Widget Generator
*/

get_header();

?>
<article id="trx_addons_widget_generator" <?php post_class( 'widget_generator_page itemscope' ); trx_addons_seo_snippets('', 'Article'); ?>>

	<?php do_action('trx_addons_action_before_article', 'widget_generator_page'); ?>
		
	<section class="widget_generator_page_header">	
		<div class="<?php echo esc_attr(trx_addons_get_columns_wrap_class()); ?>"><?php
			// Banner
			?><div class="<?php echo esc_attr(trx_addons_get_column_class(1, 3)); ?>">
				<div class="widget_generator_banner">
					<img src="<?php echo esc_url(trx_addons_get_file_url(TRX_ADDONS_PLUGIN_THEMES_MARKET.'widget_generator/widget_generator.png')); ?>" alt="<?php esc_attr_e('ThemeREX Widget Generator', 'trx_addons'); ?>">
				</div>
			</div><?php
			// Description
			?><div class="<?php echo esc_attr(trx_addons_get_column_class(2, 3)); ?>">
				<div class="widget_generator_info">
					<h2 class="widget_generator_title"><?php esc_html_e('ThemeREX Tools: Widget Generator', 'trx_addons'); ?></h2>
					<div class="widget_generator_description"><?php
						esc_html_e('With ThemeREX Widget Generator you can easily make a unique and personally-customized widget for your website or blog. Our widget allows you to explore the templates you choose to display. You can adjust all the settings of the widget and even hide the price and ThemeREX logo', 'trx_addons');
					?></div>
				</div>
			</div>
		</div>
	</section>

	<section class="widget_generator_page_content entry-content"<?php trx_addons_seo_snippets('articleBody'); ?>>
		<div class="<?php echo esc_attr(trx_addons_get_columns_wrap_class()); ?>"><?php
			// Form
			$trx_addons_args = array(
				'style' => 'accent'	//trx_addons_get_option('input_hover')
			);
			?><div class="<?php echo esc_attr(trx_addons_get_column_class(1, 3)); ?>">
				<h4 class="widget_generator_form_title"><?php esc_html_e('Widget parameters:', 'trx_addons'); ?></h4>
				<form name="widget_generator_form" class="widget_generator_form sc_form_form sc_form_custom <?php
					if ($trx_addons_args['style'] != 'default') echo 'sc_input_hover_'.esc_attr($trx_addons_args['style']);
				?>"><?php
					// Widget title
					trx_addons_get_template_part(TRX_ADDONS_PLUGIN_SHORTCODES . 'form/tpl.form-field.php',
											'trx_addons_args_sc_form_field',
											array_merge($trx_addons_args, array(
														'labels'      => true,
														'field_name'  => 'widget_title',
														'field_type'  => 'text',
														'field_value' => '',
														'field_req'   => false,
														'field_icon'  => 'trx_addons_icon-wpforms',
														'field_title' => __('Title', 'trx_addons'),
														'field_placeholder' => __('Widget title', 'trx_addons')
														))
										);

					// Market
					$list = trx_addons_get_list_terms(false, TRX_ADDONS_EDD_TAXONOMY_MARKET);
					trx_addons_get_template_part(TRX_ADDONS_PLUGIN_SHORTCODES . 'form/tpl.form-field.php',
													'trx_addons_args_sc_form_field',
													array_merge($trx_addons_args, array(
																'labels'      => true,
																'field_name'  => 'market',
																'field_type'  => 'select2',
																'field_multiple' => true,
																'field_value' => 0,
																'field_req'   => false,
																'field_title' => __('Marketplace', 'trx_addons'),
																'field_options'  => $list,
																))
												);

					// Category
					$list = trx_addons_get_list_terms(false, TRX_ADDONS_EDD_TAXONOMY_CATEGORY);
					trx_addons_get_template_part(TRX_ADDONS_PLUGIN_SHORTCODES . 'form/tpl.form-field.php',
													'trx_addons_args_sc_form_field',
													array_merge($trx_addons_args, array(
																'labels'      => true,
																'field_name'  => 'category',
																'field_type'  => 'select2',
																'field_multiple' => true,
																'field_value' => 0,
																'field_req'   => false,
																'field_title' => __('Category', 'trx_addons'),
																'field_options'  => $list
																))
												);
				
				?></form>
			</div><?php
			// Preview
			?><div class="<?php echo esc_attr(trx_addons_get_column_class(2, 3)); ?>">
				<div class="widget_generator_preview">
				</div>
			</div>
		</div>
	</section>

	<?php do_action('trx_addons_action_after_article', 'widget_generator_page'); ?>

</article>

<?php get_footer(); ?>