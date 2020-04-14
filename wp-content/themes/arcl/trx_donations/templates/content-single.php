<?php
/**
 * The template for displaying single donation's content
 *
 * @package ThemeREX Donations
 * @since ThemeREX Donations 1.0
 */

$plugin = TRX_DONATIONS::get_instance();
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'post_item_single post_type_'.esc_attr(get_post_type()) ); ?>>

	<div class="post_sidebar">
		<?php
		// Post thumbnail
		if ( has_post_thumbnail() ) {
			?>
			<div class="post_featured">
				<?php the_post_thumbnail( 'masonry', array( 'alt' => get_the_title() ) ); ?>
			</div><!-- .post_featured -->
			<?php
		}

		// Post meta
		$goal = get_post_meta( get_the_ID(), 'trx_donations_goal', true );
		$raised = get_post_meta( get_the_ID(), 'trx_donations_raised', true );
		if (empty($raised)) $raised = 0;
		$manual = get_post_meta( get_the_ID(), 'trx_donations_manual', true );
		$supporters = get_post_meta( get_the_ID(), 'trx_donations_supporters', true );
		?>

		<div class="post_goal">
			<h5 class="post_goal_title"><?php esc_html_e('Group goal:', 'lighthouseschool'); ?> <span class="post_goal_amount"><?php echo trim($plugin->get_money($goal)); ?></span></h5>
		</div>

		<div class="post_raised">
			<h5 class="post_raised_title"><?php esc_html_e('Amount raised:', 'lighthouseschool'); ?> <span class="post_raised_amount"><?php echo trim($plugin->get_money($raised+$manual)); ?></span></h5>
		</div>

		<?php
		if (isset($_REQUEST['trx_donations_pp_answer']) && substr($_REQUEST['trx_donations_pp_answer'], 0, 7)=='success'
				&& !empty($_POST['payment_status']) && $_POST['payment_status']=='Completed' && !empty($_POST['item_number']) && (int) $_POST['item_number'] == get_the_ID()) {
			?><div class="post_thanks"><?php esc_html_e('Thank you', 'lighthouseschool'); ?></div><?php
		} else {
			?><div class="post_help"><?php esc_html_e('Help us to attain our goal', 'lighthouseschool'); ?></div><?php
		}
		?>

		<div class="post_supporters">
	
			<h6 class="post_supporters_title"><?php esc_html_e('Group\'s supporters to date', 'lighthouseschool'); ?></h6>
			<?php
			if (is_array($supporters) && count($supporters) > 0) {
				$i = 0;
				$max = max(0, (int) $plugin->get_option('max_supporters_to_show'));
				?><ol><?php
				foreach ($supporters as $v) {
					if ( (int) $v['show_in_rating'] == 0) continue;
					$i++;
					if ($i > $max) break;
					?><li class="post_supporters_item"><span class="post_supporters_name"><?php echo esc_html($v['name']); ?></span><span class="post_supporters_amount"><?php echo esc_html($plugin->get_money($v['amount']));  ?></span><?php 
						if ($v['site']) { 
							?><a href="<?php echo esc_url($v['site']); ?>" class="post_supporters_site" title="<?php esc_attr_e("Go to the supporter's site", 'lighthouseschool'); ?>"><?php echo trim($v['site']); ?></a><?php
						}
						if (!empty($v['message'])) {
							?><div class="post_supporters_message"><?php echo trim($v['message']); ?></div><?php
						}
					?></li><?php
				}
				?>
				</ol>
				<div class="post_supporters_count"><?php printf(esc_html__('Supporters number: %s', 'lighthouseschool'), !empty($supporters) ? count($supporters) : 0); ?></div>
			<?php } else { ?>
				<div class="post_supporters_count"><?php esc_html_e('No supporters yet', 'lighthouseschool'); ?></div>
			<?php } ?>
		</div>
	</div>

	<div class="post_body">
		<div class="post_header entry-header">
			<div class="post_info">
				<span class="post_info_item post_date"><?php printf(esc_html__('%s', 'lighthouseschool'), get_the_date()); ?></span>
			</div>
			<?php the_title( '<h6 class="post_title entry-title">', '</h6>' ); ?>
		</div><!-- .entry-header -->
	
	
		<div class="post_content entry-content">
			<?php
				the_content( );
	
				wp_link_pages( array(
					'before'      => '<div class="page_links"><span class="page_links_title">' . esc_html__( 'Pages:', 'lighthouseschool' ) . '</span>',
					'after'       => '</div>',
					'link_before' => '<span>',
					'link_after'  => '</span>',
					'pagelink'    => '<span class="screen-reader-text">' . esc_html__( 'Page', 'lighthouseschool' ) . ' </span>%',
					'separator'   => '<span class="screen-reader-text">, </span>',
				) );
			?>
		</div><!-- .entry-content -->
	
		<div class="post_footer entry-footer">
			<div class="post_categories"><?php printf(esc_html__('Categories: %s', 'lighthouseschool'), get_the_term_list( get_the_ID(), TRX_DONATIONS::TAXONOMY, '', ', ', '' )); ?></div>
			<?php $plugin->show_share_links(); ?>
		</div><!-- .entry-footer -->

	</div><!-- .post_body -->


</article>
