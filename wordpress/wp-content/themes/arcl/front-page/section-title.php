<?php
if (($lighthouseschool_slider_sc = lighthouseschool_get_theme_option('front_page_title_shortcode')) != '' && strpos($lighthouseschool_slider_sc, '[')!==false && strpos($lighthouseschool_slider_sc, ']')!==false) {

	?><div class="front_page_section front_page_section_title front_page_section_slider front_page_section_title_slider"><?php
		// Add anchor
		$lighthouseschool_anchor_icon = lighthouseschool_get_theme_option('front_page_title_anchor_icon');	
		$lighthouseschool_anchor_text = lighthouseschool_get_theme_option('front_page_title_anchor_text');	
		if ((!empty($lighthouseschool_anchor_icon) || !empty($lighthouseschool_anchor_text)) && shortcode_exists('trx_sc_anchor')) {
			echo do_shortcode('[trx_sc_anchor id="front_page_section_title"'
											. (!empty($lighthouseschool_anchor_icon) ? ' icon="'.esc_attr($lighthouseschool_anchor_icon).'"' : '')
											. (!empty($lighthouseschool_anchor_text) ? ' title="'.esc_attr($lighthouseschool_anchor_text).'"' : '')
											. ']');
		}
		// Show slider (or any other content, generated by shortcode)
		echo do_shortcode($lighthouseschool_slider_sc);
	?></div><?php

} else {

	?><div class="front_page_section front_page_section_title<?php
				$lighthouseschool_scheme = lighthouseschool_get_theme_option('front_page_title_scheme');
				if (!lighthouseschool_is_inherit($lighthouseschool_scheme)) echo ' scheme_'.esc_attr($lighthouseschool_scheme);
				echo ' front_page_section_paddings_'.esc_attr(lighthouseschool_get_theme_option('front_page_title_paddings'));
			?>"<?php
			$lighthouseschool_css = '';
			$lighthouseschool_bg_image = lighthouseschool_get_theme_option('front_page_title_bg_image');
			if (!empty($lighthouseschool_bg_image)) 
				$lighthouseschool_css .= 'background-image: url('.esc_url(lighthouseschool_get_attachment_url($lighthouseschool_bg_image)).');';
			if (!empty($lighthouseschool_css))
				echo " style=\"{$lighthouseschool_css}\"";
	?>><?php
		// Add anchor
		$lighthouseschool_anchor_icon = lighthouseschool_get_theme_option('front_page_title_anchor_icon');	
		$lighthouseschool_anchor_text = lighthouseschool_get_theme_option('front_page_title_anchor_text');	
		if ((!empty($lighthouseschool_anchor_icon) || !empty($lighthouseschool_anchor_text)) && shortcode_exists('trx_sc_anchor')) {
			echo do_shortcode('[trx_sc_anchor id="front_page_section_title"'
											. (!empty($lighthouseschool_anchor_icon) ? ' icon="'.esc_attr($lighthouseschool_anchor_icon).'"' : '')
											. (!empty($lighthouseschool_anchor_text) ? ' title="'.esc_attr($lighthouseschool_anchor_text).'"' : '')
											. ']');
		}
		?>
		<div class="front_page_section_inner front_page_section_title_inner<?php
			if (lighthouseschool_get_theme_option('front_page_title_fullheight'))
				echo ' lighthouseschool-full-height sc_layouts_flex sc_layouts_columns_middle';
			?>"<?php
				$lighthouseschool_css = '';
				$lighthouseschool_bg_mask = lighthouseschool_get_theme_option('front_page_title_bg_mask');
				$lighthouseschool_bg_color = lighthouseschool_get_theme_option('front_page_title_bg_color');
				if (!empty($lighthouseschool_bg_color) && $lighthouseschool_bg_mask > 0)
					$lighthouseschool_css .= 'background-color: '.esc_attr($lighthouseschool_bg_mask==1
																		? $lighthouseschool_bg_color
																		: lighthouseschool_hex2rgba($lighthouseschool_bg_color, $lighthouseschool_bg_mask)
																	).';';
				if (!empty($lighthouseschool_css))
					echo " style=\"{$lighthouseschool_css}\"";
		?>>
			<div class="front_page_section_content_wrap front_page_section_title_content_wrap content_wrap">
				<?php
				// Caption
				$lighthouseschool_caption = lighthouseschool_get_theme_option('front_page_title_caption');
				if (!empty($lighthouseschool_caption) || (current_user_can('edit_theme_options') && is_customize_preview())) {
					?><h1 class="front_page_section_caption front_page_section_title_caption front_page_block_<?php echo !empty($lighthouseschool_caption) ? 'filled' : 'empty'; ?>"><?php echo wp_kses_post($lighthouseschool_caption); ?></h1><?php
				}
			
				// Description (text)
				$lighthouseschool_description = lighthouseschool_get_theme_option('front_page_title_description');
				if (!empty($lighthouseschool_description) || (current_user_can('edit_theme_options') && is_customize_preview())) {
					?><div class="front_page_section_description front_page_section_title_description front_page_block_<?php echo !empty($lighthouseschool_description) ? 'filled' : 'empty'; ?>"><?php echo wpautop(wp_kses_post($lighthouseschool_description)); ?></div><?php
				}
				
				// Buttons
				if (lighthouseschool_get_theme_option('front_page_title_button1_link')!='' || lighthouseschool_get_theme_option('front_page_title_button2_link')!='') {
					?><div class="front_page_section_buttons front_page_section_title_buttons"><?php
						lighthouseschool_show_layout(lighthouseschool_customizer_partial_refresh_front_page_title_button1_link());
						lighthouseschool_show_layout(lighthouseschool_customizer_partial_refresh_front_page_title_button2_link());
					?></div><?php
				}
				?>
			</div>
		</div>
	</div>
	<?php
}