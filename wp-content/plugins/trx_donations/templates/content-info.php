<?php
/**
 * The template for displaying single donation's info
 *
 * @package ThemeREX Donations
 * @since ThemeREX Donations 1.6
 */

$plugin = TRX_DONATIONS::get_instance();
$GLOBALS['post'] = get_post($donation);
setup_postdata($GLOBALS['post']);

// Featured
if ( !empty($show_featured) && has_post_thumbnail() ) {
	?><div class="sc_donations_featured"><?php
		the_post_thumbnail( 'thumb_med', array( 'alt' => get_the_title() ) );
	?></div><?php
}

// Title
if ( !empty($show_title) ) {
	the_title( sprintf( '<h4 class="sc_donations_title entry-title"><a href="%s" rel="bookmark">', esc_url(get_permalink()) ), '</a></h4>' );
}

// Goal and raised
if ( !empty($show_goal) || !empty($show_raised) || !empty($show_scale) ) {
	$goal = get_post_meta( get_the_ID(), 'trx_donations_goal', true );
	$raised = get_post_meta( get_the_ID(), 'trx_donations_raised', true );
	if (empty($raised)) $raised = 0;
	$manual = get_post_meta( get_the_ID(), 'trx_donations_manual', true );
	?><div class="sc_donations_data"><?php
		if ( !empty($show_goal) ) {
			?><span class="sc_donations_data_item sc_donations_goal">
				<span class="sc_donations_data_label"><?php esc_html_e('Group goal:', 'trx_donations'); ?></span>
				<span class="sc_donations_data_number"><?php echo trim($plugin->get_money($goal)); ?></span>
			</span><?php
		}
		if ( !empty($show_raised) ) {
			?><span class="sc_donations_data_item sc_donations_raised">
				<span class="sc_donations_data_label"><?php esc_html_e('Raised:', 'trx_donations'); ?></span>
				<span class="sc_donations_data_number">
					<span class="sc_donations_data_money"><?php echo trim($plugin->get_money($raised+$manual)); ?></span>
					<span class="sc_donations_data_percent"><?php echo round(($raised+$manual)*100/$goal, 2) . '%'; ?></span>
				</span>
			</span><?php
		}
		if ( !empty($show_scale) ) {
			$percent = round(($raised+$manual)*100/$goal, 2);
			?><span class="sc_donations_data_item sc_donations_scale">
				<span class="sc_donations_data_item sc_donations_scale_raised" style="width:<?php echo esc_attr($percent); ?>%;">
					<span class="sc_donations_data_label"><?php esc_html_e('Raised:', 'trx_donations'); ?></span>
					<span class="sc_donations_data_number">
						<span class="sc_donations_data_money"><?php echo trim($plugin->get_money($raised+$manual)); ?></span>
						<span class="sc_donations_data_percent"><?php echo esc_html($percent) . '%'; ?></span>
					</span>
				</span>
			</span><?php
		}
	?></div><?php
}

// Excerpt
if ( !empty($show_excerpt) && has_excerpt() ) {
	?><div class="sc_donations_excerpt"><?php the_excerpt(); ?></div><?php
}

// Supporters
if ( !empty($show_supporters) ) {
	$supporters = get_post_meta( get_the_ID(), 'trx_donations_supporters', true );
	?><div class="sc_donations_supporters"><?php
		if (is_array($supporters) && count($supporters) > 0) {
			$i = 0;
			$max = max(1, (int) $show_supporters);
			foreach ($supporters as $v) {
				if ( (int) $v['show_in_rating'] == 0) continue;
				$i++;
				if ($i > $max) break;
				?><div class="sc_donations_supporters_item"><?php
					// Amount and date
					?><span class="sc_donations_supporters_item_amount">
						<span class="sc_donations_supporters_item_amount_inner">
							<span class="sc_donations_supporters_item_amount_value"><?php
								echo esc_html($plugin->get_money($v['amount']));
							?></span><?php
							if (!empty($v['time'])) {
								?><span class="sc_donations_supporters_item_amount_date"><?php
									echo time() - (int) $v['time'] > 10 * 24 * 3600
											? date(get_option('date_format'), $v['time'])
											: sprintf(esc_html__('%s ago', 'trx_donations'), human_time_diff($v['time']));
								?></span><?php
							}
						?></span>
					</span><?php 
					
					// Name and Message
					?><span class="sc_donations_supporters_item_info">
						<span class="sc_donations_supporters_item_info_inner">
							<span class="sc_donations_supporters_item_name"><?php echo esc_html($v['name']); ?></span><?php
							if ($v['site']) { 
								?><a href="<?php echo esc_url($v['site']); ?>" class="sc_donations_supporters_item_site" title="<?php esc_attr_e("Go to the supporter's site", 'trx_donations'); ?>"><?php echo trim($v['site']); ?></a><?php
							}
							if (!empty($v['message'])) {
								?><span class="sc_donations_supporters_item_message"><?php echo trim($v['message']); ?></span><?php
							}
						?></span>
					</span>
				</div><?php
			}
			?><div class="sc_donations_supporters_total"><?php printf(esc_html__('Total supporters: %s', 'trx_donations'), !empty($supporters) ? count($supporters) : 0); ?></div><?php
		} else {
			?><div class="sc_donations_supporters_total"><?php esc_html_e('No supporters yet', 'trx_donations'); ?></div><?php
		}
	?></div><?php
}

wp_reset_postdata();
?>