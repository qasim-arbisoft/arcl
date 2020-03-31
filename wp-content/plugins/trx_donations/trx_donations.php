<?php
/*
Plugin Name:	ThemeREX Donations
Plugin URI:		http://themerex.net
Description:	Manage donation causes 
Version:		1.7.2
Author:			ThemeREX
Author URI:		http://themerex.net
*/

if (!function_exists('trx_donations_init')) {
	function trx_donations_init() {
		TRX_DONATIONS::get_instance();
	}
	add_action( 'init', 'trx_donations_init');
}

require_once 'includes/plugin.debug.php';
require_once 'includes/plugin.files.php';
require_once 'includes/plugin.wp.php';
require_once 'includes/plugin.html.php';


if (!class_exists('TRX_DONATIONS')) {
	class TRX_DONATIONS {
	
		const POST_TYPE = 'donation';					// Post type for plugin
		const TAXONOMY = 'donation_category';			// Taxonomy for plugin
	    const OPTIONS_NAME = 'trx_donations_options';	// Name of the wp_options key to store plugin options
	
		static $instance = null;						// Self instance reference
		
		var $options = array(							// Plugin options
			// PayPal settings
			'pp_account' => 'you@youremail.com',		// PayPal account used in production mode
			'pp_account_sandbox' => 'you-facilitator@youremail.com',// PayPal account used in sandbox mode
			'pp_client_id' => '',						// PayPal App Client ID for production
			'pp_client_id_sandbox' => '',				// PayPal App Client ID for sandbox
			'pp_sandbox' => true,						// Use sandbox mode to testing PayPal transactions
			'pp_currency' => 'USD',						// Currency code for PayPal
			'pp_amount' => 5,							// Default amount for PayPal forms
			// Common settings
			'blog_style' => 'excerpt',					// excerpt - Post's style in the donations archive
			'blog_columns' => 1,						// Columns number for the donations archive
			'max_supporters_to_show' => 10,				// Max number of supporters to show in the single donation post
			'share' => array(							// Social share
				'twitter' => '1',
				'facebook' => '1'
			),
			// Theme specific settings
			'columns_wrap_class' => '',					// Class name for columns wrapper, used in the  current theme. If empty - use internal class
			'column_class' => ''						// Class name (mask) for single column, used in the current theme.
														// For example: column-$1_$2, where $1 - column width, $2 - total columns: column-1_4, column-2_3, etc.
														// If empty - use internal class
		);

		private $blog_styles = array(
			'excerpt' => 'Excerpt'
		);

		public $currency_codes = array(
			'USD' => 'U.S. Dollar ($)',
			'EUR' => 'Euro (&euro;)',
			'AUD' => 'Australian Dollar',
			'BRL' => 'Brazilian Real',
			'CAD' => 'Canadian Dollar',
			'CZK' => 'Czech Koruna',
			'DKK' => 'Danish Krone',
			'HKD' => 'Hong Kong Dollar',
			'HUF' => 'Hungarian Forint',
			'ILS' => 'Israeli New Shekel',
			'JPY' => 'Yen (&yen;)',
			'MYR' => 'Malaysian Ringgit',
			'MXN' => 'Mexican Peso',
			'NOK' => 'Norwegian Krone',
			'NZD' => 'New Zealand Dollar',
			'PHP' => 'Philippine Peso',
			'PLN' => 'Polish Zloty',
			'GBP' => 'Pounds Sterling (&pound;)',
			'RUB' => 'Russian Ruble',
			'SGD' => 'Singapore Dollar',
			'SEK' => 'Swedish Krona',
			'CHF' => 'Swiss Franc',
			'TWD' => 'Taiwan New Dollar',
			'THB' => 'Thai Baht',
			'TRY' => 'Turkish Lira'
		);
	
		private $socials_share = array(
			'blogger' =>		'http://www.blogger.com/blog_this.pyra?t&u={link}&n={title}',
			'bobrdobr' =>		'http://bobrdobr.ru/add.html?url={link}&title={title}&desc={descr}',
			'delicious' =>		'http://delicious.com/save?url={link}&title={title}&note={descr}',
			'designbump' =>		'http://designbump.com/node/add/drigg/?url={link}&title={title}',
			'designfloat' =>	'http://www.designfloat.com/submit.php?url={link}',
			'digg' =>			'http://digg.com/submit?url={link}',
			'evernote' =>		'https://www.evernote.com/clip.action?url={link}&title={title}',
			'email' =>			'mailto:{email}?subject={title}&body={link}',
//			'facebook' =>		'http://www.facebook.com/sharer.php?s=100&p[url]={link}&p[title]={title}&p[summary]={descr}&p[images][0]={image}',
			'facebook' =>		'http://www.facebook.com/sharer/sharer.php?u={link}',
			'friendfeed' =>		'http://www.friendfeed.com/share?title={title} - {link}',
			'google' =>			'http://www.google.com/bookmarks/mark?op=edit&output=popup&bkmk={link}&title={title}&annotation={descr}',
			'gplus' => 			'https://plus.google.com/share?url={link}', 
			'identi' => 		'http://identi.ca/notice/new?status_textarea={title} - {link}', 
			'juick' => 			'http://www.juick.com/post?body={title} - {link}',
			'linkedin' => 		'http://www.linkedin.com/shareArticle?mini=true&url={link}&title={title}&summary={descr}', 
			'liveinternet' =>	'http://www.liveinternet.ru/journal_post.php?action=n_add&cnurl={link}&cntitle={title}',
			'livejournal' =>	'http://www.livejournal.com/update.bml?event={link}&subject={title}',
			'mail' =>			'http://connect.mail.ru/share?url={link}&title={title}&description={descr}&imageurl={image}',
			'memori' =>			'http://memori.ru/link/?sm=1&u_data[url]={link}&u_data[name]={title}', 
			'mister-wong' =>	'http://www.mister-wong.ru/index.php?action=addurl&bm_url={link}&bm_description={title}', 
			'mixx' =>			'http://chime.in/chimebutton/compose/?utm_source=bookmarklet&utm_medium=compose&utm_campaign=chime&chime[url]={link}&chime[title]={title}&chime[body]={descr}', 
			'moykrug' =>		'http://share.yandex.ru/go.xml?service=moikrug&url={link}&title={title}&description={descr}',
			'myspace' =>		'http://www.myspace.com/Modules/PostTo/Pages/?u={link}&t={title}&c={descr}', 
			'newsvine' =>		'http://www.newsvine.com/_tools/seed&save?u={link}&h={title}',
			'odnoklassniki' =>	'http://www.odnoklassniki.ru/dk?st.cmd=addShare&st._surl={link}&title={title}', 
			'pikabu' =>			'http://pikabu.ru/add_story.php?story_url={link}',
			'pinterest' =>		'http://pinterest.com/pin/create/button/?url={link}&media={image}&description={title}',
			'posterous' =>		'http://posterous.com/share?linkto={link}&title={title}',
			'postila' =>		'http://postila.ru/publish/?url={link}&agregator=themerex',
			'reddit' =>			'http://reddit.com/submit?url={link}&title={title}', 
			'rutvit' =>			'http://rutvit.ru/tools/widgets/share/popup?url={link}&title={title}', 
			'stumbleupon' =>	'http://www.stumbleupon.com/submit?url={link}&title={title}', 
			'surfingbird' =>	'http://surfingbird.ru/share?url={link}', 
			'technorati' =>		'http://technorati.com/faves?add={link}&title={title}', 
			'tumblr' =>			'http://www.tumblr.com/share?v=3&u={link}&t={title}&s={descr}', 
			'twitter' =>		'https://twitter.com/intent/tweet?text={title}&url={link}',
			'vk' =>				'http://vk.com/share.php?url={link}&title={title}&description={descr}',
			'vk2' =>			'http://vk.com/share.php?url={link}&title={title}&description={descr}',
			'vkontakte' =>		'http://vk.com/share.php?url={link}&title={title}&description={descr}',
			'webdiscover' =>	'http://webdiscover.ru/share.php?url={link}',
			'yahoo' =>			'http://bookmarks.yahoo.com/toolbar/savebm?u={link}&t={title}&d={descr}',
			'yandex' =>			'http://zakladki.yandex.ru/newlink.xml?url={link}&name={title}&descr={descr}',
			'ya' =>				'http://my.ya.ru/posts_add_link.xml?URL={link}&title={title}&body={descr}',
			'yosmi' =>			'http://yosmi.ru/index.php?do=share&url={link}'
		);

		// Create itself and return reference on just created object
		public static function get_instance() {
			if (self::$instance === null) {
				self::$instance = new self();
			}
			return self::$instance;
		}
	
		function __construct() {
			
			// Set image sizes
			add_image_size( 'thumb-med', 480, 270, true );

			// Setup actions handlers
			if (is_admin()) {
	
				// Admin
				add_action('admin_menu',			array($this, 'admin_menu_item'));
				add_action('admin_enqueue_scripts',	array($this, 'admin_load_scripts'));
				add_action('add_meta_boxes',		array($this, 'admin_add_meta_box'));
				add_action('save_post', 			array($this, 'admin_save_meta_box'));

				// Extra columns for donations list
				add_filter('manage_edit-donation_columns',			array($this, 'admin_add_list_columns'), 9);
				add_filter('manage_donation_posts_custom_column',	array($this, 'admin_fill_list_columns'), 9, 2);
	
			} else {
	
				// Frontend
				add_action('wp_enqueue_scripts',	array($this, 'load_scripts'));
				add_action("wp_footer", 			array($this, 'localize_scripts_front'));

				// Replace standard templates
				add_filter('single_template',		array($this, 'get_single_template'));
				add_filter('archive_template',		array($this, 'get_archive_template'));
				add_filter('taxonomy_template',		array($this, 'get_taxonomy_template'));

				// Add plugin shortcodes
				add_shortcode('trx_donations_form',	array($this, 'sc_donations_form'));
				add_shortcode('trx_donations_list',	array($this, 'sc_donations_list'));
				add_shortcode('trx_donations_info',	array($this, 'sc_donations_info'));

			}
			// Store donation data when 'Donation' Button is clicked
			add_action('wp_ajax_donation_start',		array($this, 'ajax_donation_start'));
			add_action('wp_ajax_nopriv_donation_start',	array($this, 'ajax_donation_start'));

			// Create post type 'Donations'
			register_post_type( self::POST_TYPE, array(
				'label'               => esc_html__( 'Donations', 'trx_donations' ),
				'description'         => esc_html__( 'Donation causes', 'trx_donations' ),
				'labels'              => array(
					'name'                => _x( 'Donations', 'Post Type General Name', 'trx_donations' ),
					'singular_name'       => _x( 'Donation', 'Post Type Singular Name', 'trx_donations' ),
					'menu_name'           => esc_html__( 'Donations', 'trx_donations' ),
					'parent_item_colon'   => esc_html__( 'Parent Item:', 'trx_donations' ),
					'all_items'           => esc_html__( 'All Donations', 'trx_donations' ),
					'view_item'           => esc_html__( 'View Donation', 'trx_donations' ),
					'add_new_item'        => esc_html__( 'Add New Donation', 'trx_donations' ),
					'add_new'             => esc_html__( 'Add New', 'trx_donations' ),
					'edit_item'           => esc_html__( 'Edit Donation', 'trx_donations' ),
					'update_item'         => esc_html__( 'Update Donation', 'trx_donations' ),
					'search_items'        => esc_html__( 'Search Donations', 'trx_donations' ),
					'not_found'           => esc_html__( 'Donation not found', 'trx_donations' ),
					'not_found_in_trash'  => esc_html__( 'Donation not found in Trash', 'trx_donations' ),
				),
				'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'),	//, 'custom-fields'
				'public'              => true,
				'hierarchical'        => false,
				'has_archive'         => true,
				'can_export'          => true,
				'show_in_admin_bar'   => true,
				'show_in_menu'        => true,
				'menu_position'       => '51.1',
				'menu_icon'			  => 'dashicons-money',
				'capability_type'     => 'post',
				'rewrite'             => array( 'slug' => self::POST_TYPE )
				)
			);
				
			// Prepare taxonomy for donations
			register_taxonomy( self::TAXONOMY, self::POST_TYPE, array(
					'labels'            => array(
						'name'              => _x( 'Donations Category', 'taxonomy general name', 'trx_donations' ),
						'singular_name'     => _x( 'Category', 'taxonomy singular name', 'trx_donations' ),
						'search_items'      => esc_html__( 'Search Categories', 'trx_donations' ),
						'all_items'         => esc_html__( 'All Categories', 'trx_donations' ),
						'parent_item'       => esc_html__( 'Parent Category', 'trx_donations' ),
						'parent_item_colon' => esc_html__( 'Parent Category:', 'trx_donations' ),
						'edit_item'         => esc_html__( 'Edit Category', 'trx_donations' ),
						'update_item'       => esc_html__( 'Update Category', 'trx_donations' ),
						'add_new_item'      => esc_html__( 'Add New Category', 'trx_donations' ),
						'new_item_name'     => esc_html__( 'New Category Name', 'trx_donations' ),
						'menu_name'         => esc_html__( 'Categories', 'trx_donations' ),
					),
					'hierarchical'      => true,
					'show_ui'           => true,
					'show_admin_column' => true,
					'query_var'         => true,
					'rewrite'           => array( 'slug' => self::TAXONOMY )
					)
				);

			// Init properties
			if ( ($options = get_option('trx_donations_options', false))!==false)
				$this->options = array_merge($this->options, $options);

			// If last donation success
			if (isset($_REQUEST['trx_donations_pp_answer'])
				&& substr($_REQUEST['trx_donations_pp_answer'], 0, 7)=='success'
				&& !empty($_REQUEST['trx_donations_pp_code'])) {
					// Get data from cache
					$supporter_info = get_transient($_REQUEST['trx_donations_pp_code']);
					if (is_array($supporter_info) && !empty($supporter_info['donation_id'])) {
						$supporter_info['time'] = time();
						// Add to supporters list
						if (is_array($supporter_info) && !empty($supporter_info['donation_id']))
							$this->add_to_supporters($supporter_info['donation_id'], $supporter_info);
						delete_transient($_REQUEST['trx_donations_pp_code']);
					}
			}
		}

		// Callback for start donation
		function ajax_donation_start() {
	
			if ( !wp_verify_nonce( trx_donations_get_value_gp('nonce'), admin_url('admin-ajax.php') ) )
				die();
			
			parse_str($_POST['data'], $post_data);

			$donation = !empty($post_data['donation']) ? $post_data['donation'] : 'donation_0_0';
			$donation_parts = explode('_', $donation);

			$supporter_info = array(
				'donation_id' => $donation_parts[0]=='donation' && !empty($donation_parts[1]) ? (int) $donation_parts[1] : 0,
				'email' => !empty($post_data['email']) ? stripslashes($post_data['email']) : '',
				'name' => !empty($post_data['name']) ? stripslashes($post_data['name']) : '',
				'message' => !empty($post_data['message']) ? stripslashes($post_data['message']) : '',
				'amount' => !empty($post_data['amount']) ? (float)$post_data['amount'] : 0,
				'site' => !empty($post_data['site']) ? stripslashes($post_data['site']) : '',
				'show_in_rating' => isset($post_data['show_in_rating']) ? $post_data['show_in_rating'] : 0
			);
		
			$response = array('error' => '');
			
			if ($supporter_info['donation_id'] == 0)
				$response['error'] = esc_html__('Unknown donation ID', 'trx_donations');
			else if (empty($supporter_info['email']))
				$response['error'] = esc_html__("Supporter's email is not specified", 'trx_donations');
			else if (empty($supporter_info['name']))
				$response['error'] = esc_html__("Supporter's name is not specified", 'trx_donations');
			else if (empty($supporter_info['amount']))
				$response['error'] = esc_html__("Donation amount is not specified", 'trx_donations');
			else {
				set_transient($donation, $supporter_info, 60*60);	// Store to the cache for 60 minutes
			}
			
			echo json_encode($response);
			die();
		}

		// Add to supporters list
		function add_to_supporters($post_id, $sup) {
			$supporters = get_post_meta( $post_id, 'trx_donations_supporters', true );
			$raised = max(0, (float) get_post_meta( $post_id, 'trx_donations_raised', true ));
			if (empty($supporters)) $supporters = array();
			array_unshift($supporters, $sup);
			//uasort($supporters, array(&$this, 'sort_supporters'));
			update_post_meta( $post_id, 'trx_donations_supporters', $supporters );
			update_post_meta( $post_id, 'trx_donations_raised', $raised + (float) $sup['amount'] );
		}

		// Sort supporters by amount
		function sort_supporters($s1, $s2) {
			return $s1['amount'] > $s2['amount'] 
				? -1
				: ($s1['amount'] < $s2['amount'] 
					? 1
					: 0);
		}






	
	
		//-----------------------------------------------------------------------------------
		// Frontend Interface
		//-----------------------------------------------------------------------------------
		
		
		// Load required styles and scripts
		function load_scripts($always=false) {
			global $post;
			if ( $always
					|| (!empty($post->post_content) && !empty($post->post_type)
						&& ((is_single() && $post->post_type == self::POST_TYPE) 
							|| is_post_type_archive(self::POST_TYPE) 
							|| is_tax(self::TAXONOMY) 
							|| has_shortcode($post->post_content, 'trx_donations_list') 
							|| has_shortcode($post->post_content, 'trx_donations_form')
							|| has_shortcode($post->post_content, 'trx_donations_info')
							)
						)
				) {
				trx_donations_enqueue_style ('trx-donations-style',  trx_donations_get_file_url( 'trx_donations.css' ), array(), null);
				trx_donations_enqueue_style( 'trx-donations-socials-share-style', trx_donations_get_file_url('css/socials-share/css/socials-share-embedded.css'),  array(), null);
				trx_donations_enqueue_script('trx-donations-script', trx_donations_get_file_url( 'trx_donations.js' ), array('jquery'), null, true);
			}
		}

		// Add variables in the frontend
		function localize_scripts_front() {
			wp_localize_script( 'trx-donations-script', 'TRX_DONATIONS_STORAGE', apply_filters('trx_donations_localize_script', array(
				// AJAX parameters
				'ajax_url'	=> esc_url(admin_url('admin-ajax.php')),
				'ajax_nonce'=> esc_attr(wp_create_nonce(admin_url('admin-ajax.php'))),
				// Site base url
				'site_url'	=> esc_url(get_site_url()),
				// E-mail mask to validate forms
				'email_mask' => '^([a-zA-Z0-9_\\-]+\\.)*[a-zA-Z0-9_\\-]+@[a-z0-9_\\-]+(\\.[a-z0-9_\\-]+)*\\.[a-z]{2,6}$',
				// JS Messages
				'msg_ajax_error'	=> addslashes(esc_html__('Invalid server answer!', 'trx_donations')),
				'msg_fields_error'	=> addslashes(esc_html__('Please, fill required fields!', 'trx_donations')),
				'msg_cancel_payment' => addslashes(esc_html__('The payment is canceled!', 'trx_donations')),
				// PayPal endpoints
				'pp_currency' => $this->get_option('pp_currency'),
				'finish_donation_url' => esc_url(trx_donations_add_to_url(get_permalink(), array('trx_donations_pp_answer' => 'success')))
				) )
			);
		}

		// Change standard single template for donation posts
		function get_single_template($template) {
			global $post;
			if (is_single() && $post->post_type == self::POST_TYPE)
				$template = trx_donations_get_file_dir('templates/single.php');
			return $template;
		}

		// Change standard archive template for donation posts
		function get_archive_template( $template ) {
			 if ( is_post_type_archive(self::POST_TYPE) )
				  $template = trx_donations_get_file_dir('templates/archive.php');
			 return $template;
		}	

		// Change standard category template for donation categories
		function get_taxonomy_template( $template ) {
			 if ( is_tax(self::TAXONOMY) )
				  $template = trx_donations_get_file_dir('templates/archive.php');
			 return $template;
		}	








	
		//-----------------------------------------------------------------------------------
		// Public API Interface
		//-----------------------------------------------------------------------------------

		// Return money with currency
		function get_money($money) {
            return sprintf(esc_html__('%s %.2f', 'trx_donations'), $this->get_option('pp_currency'), $money);
        }

		// Return option value
		function get_option($name) {
			return isset($this->options) ? $this->options[$name] : null;
		}

		// Show share links
		function show_share_links($args=array()) {
	
			$args = array_merge(array(
				'post_id' => 0,						// post ID
				'post_link' => '',					// post link
				'post_title' => '',					// post title
				'post_descr' => '',					// post descr
				'post_thumb' => '',					// post featured image
				'popup' => true,					// open share url in new window or in popup window
				'caption' => esc_html__('Share:', 'trx_donations'),		// share block caption
				'echo' => true						// if true - show on page, else - only return as string
				), $args);
	
			$share = $this->get_option('share');
			
			$output = '';

			if (is_array($share) && count($share) > 0) {
				if (empty($args['post_id']))	$args['post_id'] = get_the_ID();
				if (empty($args['post_link']))	$args['post_link'] = get_permalink($args['post_id']);
				if (empty($args['post_title']))	$args['post_title'] = get_the_title($args['post_id']);
				if (empty($args['post_descr']))	$args['post_descr'] = strip_tags(get_the_excerpt($args['post_id']));
				if (empty($args['post_thumb']))	$args['post_thumb'] = wp_get_attachment_url(get_post_thumbnail_id($args['post_id']));

				foreach ($share as $soc=>$enabled) {
					if ( (int) $enabled == 0 || empty($this->socials_share[$soc]) ) continue;
					$icon = 'icon_share-'.$soc;
					$url = $this->socials_share[$soc];
					$email = strpos($url, 'mailto:')!==false;
					$popup = !empty($args['popup']) && !$email;
					$link = str_replace(
						array('{id}', '{link}', '{title}', '{descr}', '{image}', '{email}'),
						array(
							urlencode($args['post_id']),
							urlencode($args['post_link']),
							urlencode(strip_tags($args['post_title'])),
							urlencode(strip_tags($args['post_descr'])),
							urlencode($args['post_thumb']),
							urlencode(get_bloginfo('admin_email'))
							),
						$url);
					$output .= '<a href="'.esc_url($link).'"'
								. ' class="sc_socials_share_item"'
								. ($popup ? ' onclick="window.open(\'' . esc_url($link) .'\', \'_blank\', \'scrollbars=0, resizable=1, menubar=0, left=100, top=100, width=480, height=400, toolbar=0, status=0\'); return false;"' : ' target="_blank"')
							. '>'
								. '<span class="' . esc_attr($icon) . '"></span>' 
							. '</a>';
				}
			}
			
			if ($output) {
				$output = '<div class="sc_socials_share">'
						. ($args['caption']!='' ? '<span class="sc_socials_share_caption">'.($args['caption']).'</span>' : '')
						. $output
					. '</div>';
				if ($args['echo']) echo ($output);
			}
			return $output;
		}

	




	
	
		//-----------------------------------------------------------------------------------
		// Shortcodes: Donation form
		//-----------------------------------------------------------------------------------
		function sc_donations_form($atts, $content = null) {
			extract(trx_donations_html_decode(shortcode_atts(array(
				// Individual params
				"title" => "",
				"subtitle" => "",
				"description" => "",
				"align" => "",
				"donation" => 0,
				// PayPal settings
				"client_id" => "",
				"sandbox" => "",
				"amount" => "",
				// Common params
				"id" => "",
				"class" => "",
				"css" => "",
				"width" => "",
				"top" => "",
				"bottom" => "",
				"left" => "",
				"right" => ""
			), $atts)));
			
			$this->load_scripts(true);
			
			if (empty($donation) && is_single() && get_post_type()==self::POST_TYPE) $donation = get_the_ID();
			
			if (empty($id)) $id = "sc_donations_form";
			if (!in_array($sandbox, array('on', 'off'))) $sandbox = $this->get_option('pp_sandbox') ? "on" : "off";
			if (empty($client_id)) $client_id = $this->get_option('pp_client_id'.($sandbox=='on' ? '_sandbox' : ''));
			if ((float) $amount <= 0) $amount = $this->get_option('pp_amount');
			
			$css .= trx_donations_get_css_position_from_values($top, $right, $bottom, $left, $width);

			$success = $error = '';
			
			if ($donation > 0) {
				wp_enqueue_script('paypal-checkout', 'https://www.paypalobjects.com/api/checkout.js', array(), null, true);

				// If donation success
				if (isset($_REQUEST['trx_donations_pp_answer'])
					&& substr($_REQUEST['trx_donations_pp_answer'], 0, 7)=='success'
					&& !empty($_REQUEST['trx_donations_pp_code'])) {
						// Show message
						$code = explode('_', $_REQUEST['trx_donations_pp_code']);
						if ($code[1] == $donation)
							$success = esc_html__('Operation success! Thank you for your donation!', 'trx_donations');
				}
			} else {
				$error = esc_html__('Please, specify donation to display form!', 'trx_donations');
			}

			$privacy = apply_filters( 'trx_donations_filter_privacy_text', wp_kses_post(
					__( 'I agree that my submitted data is being collected and stored.', 'trx_donations' )
					. (get_option('wp_page_for_privacy_policy') != ''
						// Translators: Add url to the Privacy Policy page
						? ' ' . sprintf(__('For further details on handling user data, see our %s', 'trx_addons'),
							'<a href="' . esc_url(get_permalink(get_option('wp_page_for_privacy_policy'))) . '" target="_blank">'
							. __('Privacy Policy', 'trx_donations')
							. '</a>')
						: ''
					)
				)
			);
			$output = '<div ' . ($id ? ' id="'.esc_attr($id).'"' : '')
				. ' class="sc_donations_form'
				. (!empty($align) && !in_array($align, array('none', 'default')) ? ' align'.esc_attr($align) : '')
				. (!empty($class) ? ' '.esc_attr($class) : '')
				. '"'
				. ($css!='' ? ' style="'.esc_attr($css).'"' : '')
				. '>'
				. (!empty($subtitle)
					? '<h6 class="sc_donations_form_subtitle sc_item_subtitle">' . trim($subtitle) . '</h6>'
					: '')
				. (!empty($title)
					? '<h2 class="sc_donations_form_title sc_item_title">' . trim($title) . '</h2>'
					: '')
				. (!empty($description)
					? '<div class="sc_donations_form_descr sc_item_descr">' . trim($description) . '</div>'
					: '')
				. (!empty($success)
					? '<div id="sc_donations_result" class="sc_donations_result sc_donations_success">'
					. '<p>' . trim($success) . '</p>'
					. '</div>'
					: ''
				)
				. (!empty($error)
					? '<div id="sc_donations_result" class="sc_donations_result sc_donations_error">'
					. '<p>' . trim($error) . '</p>'
					. '</div>'
					: ''
				)
				. ($donation > 0
					? '<form' . ($id ? ' id="'.esc_attr($id).'_form"' : '') . ' method="post" action="#"'
					. ' data-pp_env="' . ($sandbox == 'on' ? 'sandbox' : 'production') .'"'
					. ' data-pp_client="' . esc_attr($client_id) .'"'
					. '>'
					// Hidden fields
					. '<input type="hidden" id="sc_donations_form_donation_code" name="donation" value="donation_' . $donation . '_' . md5(substr(microtime() . uniqid(), 0, 12)) . '">'

					// User interface fields
					. '<div class="sc_donations_form_field sc_donations_form_field_amount"><label class="required sc_donations_form_label" for="sc_donations_form_amount_5">' . esc_html__('Amount', 'trx_donations') . '</label>'
					. '<input id="sc_donations_form_amount_5" type="radio" name="amount_value" value="5.00"' . ($amount==5 ? ' checked="checked"' : '') . '>'
					. '<label class="sc_donations_form_amount_label" for="sc_donations_form_amount_5">' . $this->get_money(5) . '</label>'
					. '<input id="sc_donations_form_amount_10" type="radio" name="amount_value" value="10.00"' . ($amount==10 ? ' checked="checked"' : '') . '>'
					. '<label class="sc_donations_form_amount_label" for="sc_donations_form_amount_10">' . $this->get_money(10) . '</label>'
					. '<input id="sc_donations_form_amount_20" type="radio" name="amount_value" value="20.00"' . ($amount==20 ? ' checked="checked"' : '') . '>'
					. '<label class="sc_donations_form_amount_label" for="sc_donations_form_amount_20">' . $this->get_money(20) . '</label>'
					. '<input id="sc_donations_form_amount_50" type="radio" name="amount_value" value="50.00"' . ($amount==50 ? ' checked="checked"' : '') . '>'
					. '<label class="sc_donations_form_amount_label" for="sc_donations_form_amount_50">' . $this->get_money(50) . '</label>'
					. '<br>'
					. '<input id="sc_donations_form_amount_0" type="radio" name="amount_value" value="0"' . (!in_array($amount, array(5,10,20,50)) ? ' checked="checked"' : '') . '>'
					. '<label class="sc_donations_form_amount_label" for="sc_donations_form_amount_0">' . esc_html__('Other:', 'trx_donations') . '</label>'
					. '<input id="sc_donations_form_amount" class="sc_donations_form_amount" type="text" name="amount" value="' . esc_attr(number_format($amount, 2)) . '">'
					. ' '. esc_html($this->get_option('pp_currency'))
					. '</div>'
					. '<div class="sc_donations_form_field sc_donations_form_field_name">'
					. '<label class="required sc_donations_form_label" for="sc_donations_form_name">' . esc_html__('Name', 'trx_donations') . '</label>'
					. '<input id="sc_donations_form_name" type="text" name="name" placeholder="' . esc_attr__('Name *', 'trx_donations') . '">'
					. '</div>'
					. '<div class="sc_donations_form_field sc_donations_form_field_email">'
					. '<label class="required sc_donations_form_label" for="sc_donations_form_email">' . esc_html__('E-mail', 'trx_donations') . '</label>'
					. '<input id="sc_donations_form_email" type="text" name="email" placeholder="' . esc_attr__('E-mail *', 'trx_donations') . '">'
					. '</div>'
					. '<div class="sc_donations_form_field sc_donations_form_field_site">'
					. '<label class="sc_donations_form_label" for="sc_donations_form_site">' . esc_html__('Website', 'trx_donations') . '</label>'
					. '<input id="sc_donations_form_site" type="text" name="site" placeholder="' . esc_attr__('Website', 'trx_donations') . '">'
					. '</div>'
					. '<div class="sc_donations_form_field sc_donations_form_field_message">'
					. '<label class="sc_donations_form_label" for="sc_donations_form_message">' . esc_html__('Message (up to 200 characters)', 'trx_donations') . '</label>'
					. '<textarea id="sc_donations_form_message" name="message" maxlength="200" placeholder="' . esc_attr__('Message', 'trx_donations') . '"></textarea>'
					. '</div>'
					. '<div class="sc_donations_form_field sc_donations_form_field_rating">'
					. '<input id="sc_donations_form_rating" type="checkbox" name="show_in_rating" value="1" checked="checked">'
					. '<label class="sc_donations_form_rating_label" for="sc_donations_form_rating">' . esc_html__('Show me in the supporters rating', 'trx_donations') . '</label>'
					. '</div>'
					. ( !empty($privacy)
						? '<div class="sc_donations_form_field sc_donations_form_field_agree">'
						. '<input id="sc_donations_form_agree" type="checkbox" name="i_agree_privacy_policy" value="1">'
						. '<label class="sc_donations_form_agree_label" for="sc_donations_form_agree">' . wp_kses_post($privacy) . '</label>'
						. '</div>'
						: ''
					)
//                            . '<div class="sc_donations_form_field sc_donations_form_field_note icon_share-info-circled">' . sprintf( wp_kses_post( __('<b>Please note!</b> Due to the peculiarities of payment confirmation mechanism in PayPal, in order for your donation to be registered on our website, you must click on the <b><u>"Return to Merchant"</u></b> (or <b><u>"Return to %s"</u></b>) link after the payment on the PayPal payment page. Thank you for your understanding.', 'trx_donations') ), $account ) . '</div>'
					. '<div class="sc_donations_form_field sc_donations_form_field_button' . ( !empty($privacy) ? ' sc_donations_hidden' : '' ) . '">'
//                                . '<input type="button" class="sc_donations_form_submit" value="'.esc_html__('Donate', 'trx_donations').'">'
					. '<div id="paypal-button-container"></div>'
					. '</div>'
					. '</form>'
					: '')
				. '</div>';
			return $output;
		}



	
	
		//-----------------------------------------------------------------------------------
		// Shortcodes: Donations list
		//-----------------------------------------------------------------------------------
		function sc_donations_list($atts, $content = null) {
			extract(trx_donations_html_decode(shortcode_atts(array(
				// Individual params
				"style" => "excerpt",
				"columns" => 3,
				"ids" => "",
				"cat" => "",
				"count" => 3,
				"offset" => "",
				"orderby" => "date",
				"order" => "desc",
				"title" => "",
				"subtitle" => "",
				"description" => "",
				"link_caption" => esc_html__('More donations', 'trx_donations'),
				"link" => '',
				// Common params
				"id" => "",
				"class" => "",
				"css" => "",
				"top" => "",
				"bottom" => ""
			), $atts)));
		
			$this->load_scripts(true);

			$output = '';
			$in_shortcode = true;
			
			if (file_exists($tpl = trx_donations_get_file_dir( 'templates/content-'.$style.'.php' ))) {

				if (empty($id)) $id = "sc_donations_".str_replace('.', '', mt_rand());
				
				$css .= trx_donations_get_css_position_from_values($top, '', $bottom, '');
		
				$count = max(1, (int) $count);
				$columns = max(1, min(12, (int) $columns));
				if ($count < $columns) $columns = $count;
		
				$output = '<div' . ($id ? ' id="'.esc_attr($id).'"' : '') 
							. ' class="sc_donations'
								. ' sc_donations_style_'.esc_attr($style)
								. (!empty($class) ? ' '.esc_attr($class) : '')
								. '"'
								. ($css!='' ? ' style="'.esc_attr($css).'"' : '') 
							. '>'
							. (!empty($subtitle) ? '<h6 class="sc_donations_subtitle sc_item_subtitle">' . trim($subtitle) . '</h6>' : '')
							. (!empty($title) ? '<h2 class="sc_donations_title sc_item_title">' . trim($title) . '</h2>' : '')
							. (!empty($description) ? '<div class="sc_donations_descr sc_item_descr">' . trim($description) . '</div>' : '')
							. ($columns > 1 
								? '<div class="'.($this->get_option('columns_wrap_class')!='' ? esc_attr($this->get_option('columns_wrap_class')) : 'sc_donations_columns_wrap').'">' 
								: '');
	
				if (!empty($ids)) {
					$posts = explode(',', $ids);
					$count = count($posts);
					if ($count < $columns) $columns = $count;
				}
					
				$args = array(
					'post_type' => self::POST_TYPE,
					'post_status' => 'publish',
					'posts_per_page' => $count,
					'ignore_sticky_posts' => true,
					'order' => $order=='asc' ? 'asc' : 'desc'
				);
				
				if ($offset > 0 && empty($ids)) {
					$args['offset'] = $offset;
				}
				
				$args = trx_donations_query_add_sort_order($args, $orderby, $order);
				$args = trx_donations_query_add_posts_and_cats($args, $ids, self::POST_TYPE, $cat, self::TAXONOMY);
	
				$query = new WP_Query( $args );
			
				while ( $query->have_posts() ) { 
					$query->the_post();
					ob_start();
					require $tpl;
					$output .= ob_get_contents();
					ob_end_clean();
				}
				wp_reset_postdata();
			
				if ($columns > 1) {
					$output .= '</div>';
				}
		
				$output .=  (!empty($link) ? '<div class="sc_donations_button sc_item_button"><a href="'.esc_url($link).'">'.esc_html($link_caption).'</a></div>' : '')
							. '</div><!-- /.sc_donations -->';
			}
			return $output;
		}

	




	
	
		//-----------------------------------------------------------------------------------
		// Shortcodes: Donation's supporters
		//-----------------------------------------------------------------------------------
		function sc_donations_info($atts, $content = null) {
			extract(trx_donations_html_decode(shortcode_atts(array(
				// Individual params
				"donation" => 0,
				"show_featured" => 1,
				"show_title" => 1,
				"show_excerpt" => 1,
				"show_goal" => 1,
				"show_raised" => 1,
				"show_scale" => 1,
				"show_supporters" => 5,
				"title" => "",
				"subtitle" => "",
				"description" => "",
				"link_caption" => esc_html__('More donations', 'trx_donations'),
				"link" => '',
				// Common params
				"id" => "",
				"class" => "",
				"css" => "",
				"top" => "",
				"bottom" => ""
			), $atts)));
		
			$this->load_scripts(true);

			if (empty($donation) && is_single() && get_post_type()==self::POST_TYPE) $donation = get_the_ID();

			$output = '';
			
			if ($donation > 0 && file_exists($tpl = trx_donations_get_file_dir( 'templates/content-info.php' ))) {

				if (empty($id)) $id = "sc_donations_".str_replace('.', '', mt_rand());
				
				$css .= trx_donations_get_css_position_from_values($top, '', $bottom, '');
		
				$output = '<div' . ($id ? ' id="'.esc_attr($id).'"' : '') 
							. ' class="sc_donations_info'
								. (!empty($class) ? ' '.esc_attr($class) : '')
								. '"'
								. ($css!='' ? ' style="'.esc_attr($css).'"' : '') 
							. '>'
							. (!empty($subtitle) ? '<h6 class="sc_donations_subtitle sc_item_subtitle">' . trim($subtitle) . '</h6>' : '')
							. (!empty($title) ? '<h2 class="sc_donations_title sc_item_title">' . trim($title) . '</h2>' : '')
							. (!empty($description) ? '<div class="sc_donations_descr sc_item_descr">' . trim($description) . '</div>' : '');
	
				ob_start();
				require $tpl;
				$output .= ob_get_contents();
				ob_end_clean();
		
				$output .=  (!empty($link) 
								? '<div class="sc_donations_button sc_item_button">'
									. '<a href="'.esc_url($link).'">'.esc_html($link_caption).'</a>'
									. '</div>' 
								: '')
							. '</div><!-- /.sc_donations_info -->';
			}
			return $output;
		}



	


	
	
		//-----------------------------------------------------------------------------------
		// Admin Interface
		//-----------------------------------------------------------------------------------
		function admin_menu_item() {
	
			// In this case menu item is add in admin menu
			add_theme_page(
				esc_html__('ThemeREX Donations', 'trx_donations'),	//page_title
				esc_html__('ThemeREX Donations', 'trx_donations'),	//menu_title
				'manage_options',									//capability
				'trx_donations_settings',							//menu_slug
				array($this, 'admin_settings'),						//callback
				'dashicons-awards',									//icon
				''													//menu position
			);
		}
		
		
		// Load required styles and scripts
		function admin_load_scripts() {
			global $post_type;
			if (isset($_REQUEST['page']) && $_REQUEST['page']=='trx_donations_settings' || $post_type==self::POST_TYPE && (strpos($_SERVER['REQUEST_URI'], 'post-new.php')!==false || strpos($_SERVER['REQUEST_URI'], 'post.php')!==false)) {
				trx_donations_enqueue_style ('trx-donations-style',  trx_donations_get_file_url( 'trx_donations_admin.css' ), array(), null);
				trx_donations_enqueue_script('trx-donations-script', trx_donations_get_file_url( 'trx_donations_admin.js' ), array('jquery'), null, true);
			}
		}
		
		
		// Build the Settings page
		function admin_settings() {
			$error = $success = '';
			if (isset($_POST['trx_donations_settings_nonce'])) {
				do {
					if ( !wp_verify_nonce( $_POST['trx_donations_settings_nonce'], get_admin_url() ) ) {
						$error = esc_html__('Invalid security code! Options not saved. Please, refresh page and try again.', 'trx_donations');
						break;
					}
					
					$this->options['pp_account'] = trim(trx_donations_get_value_gp('pp_account'));
					$this->options['pp_account_sandbox'] = trim(trx_donations_get_value_gp('pp_account_sandbox'));
					$this->options['pp_client_id'] = trim(trx_donations_get_value_gp('pp_client_id'));
					$this->options['pp_client_id_sandbox'] = trim(trx_donations_get_value_gp('pp_client_id_sandbox'));
					$this->options['pp_sandbox'] = (int) trx_donations_get_value_gp('pp_sandbox', 0) > 0;
					$this->options['pp_amount'] = (float) trx_donations_get_value_gp('pp_amount');
					$this->options['pp_currency'] = trx_donations_get_value_gp('pp_currency');
					$this->options['blog_style'] = trx_donations_get_value_gp('blog_style');
					$this->options['blog_columns'] = max(1, (int) trx_donations_get_value_gp('blog_columns'));
					$this->options['max_supporters_to_show'] = max(0, (int) trx_donations_get_value_gp('max_supporters_to_show'));
					$this->options['share'] = array();
					foreach ($this->socials_share as $code=>$link) {
						if ( !empty($_POST['share'][$code]) )
							$this->options['share'][$code] = 1;
					}
					$this->options['columns_wrap_class'] = trim(trx_donations_get_value_gp('columns_wrap_class'));
					$this->options['column_class'] = trim(trx_donations_get_value_gp('column_class'));
				} while (false);
				$success = esc_html__('Options saved!', 'trx_donations');
				update_option('trx_donations_options', $this->options);
			}
			?>


			<div class="trx_donations_options">

				<h2 class="trx_donations_options_title"><?php esc_html_e('Donations settings', 'trx_donations'); ?></h2>
				
				<?php if (!empty($error)) { ?>
					<div class="error"><p><?php echo trim($error); ?></p></div>
				<?php } else if (!empty($success)) { ?>
					<div class="updated"><p><?php echo trim($success); ?></p></div>
				<?php } ?>

				<form action="<?php echo esc_url(menu_page_url('trx_donations_settings', false)); ?>" method="post">

					<input type="hidden" name="trx_donations_settings_nonce" value="<?php echo esc_attr(wp_create_nonce(get_admin_url())); ?>">

					<div class="trx_donations_options_section trx_donations_options_section_shortcodes">

						<h3 class="trx_donations_options_section_title"><?php esc_html_e('Shortcodes', 'trx_donations'); ?></h3>
						
						<div class="trx_donations_options_row">
							<h4 class="trx_donations_options_caption"><?php esc_html_e('Insert Donation form into post/page', 'trx_donations'); ?></h4>
							<div class="trx_donations_options_field">
								<input type="text" readonly name="sc_form" value="[trx_donations_form]">
								<div class="trx_donations_options_description"><?php esc_html_e('Use this shortcode to insert donation form into post/page content.', 'trx_donations'); ?></div>
							</div>
						</div>
						
						<div class="trx_donations_options_row">
							<h4 class="trx_donations_options_caption"><?php esc_html_e('Insert list of donations into post/page', 'trx_donations'); ?></h4>
							<div class="trx_donations_options_field">
								<input type="text" readonly name="sc_form" value="[trx_donations_list cat='category_slug_or_id' count='3' columns='3']">
								<div class="trx_donations_options_description"><?php esc_html_e('Use this shortcode to insert donations list into post/page content.', 'trx_donations'); ?></div>
							</div>
						</div>

					</div>
									
					<div class="trx_donations_options_section trx_donations_options_section_pp_account">

						<h3 class="trx_donations_options_section_title"><?php esc_html_e('PayPal options', 'trx_donations'); ?></h3>
						
						<div class="trx_donations_options_row">
							<h4 class="trx_donations_options_caption"><?php esc_html_e('PayPal account', 'trx_donations'); ?></h4>
							<div class="trx_donations_options_field">
								<input type="text" name="pp_account" value="<?php echo esc_attr($this->get_option('pp_account')); ?>">
								<div class="trx_donations_options_description"><?php esc_html_e('E-mail, used for registration PayPal account', 'trx_donations'); ?></div>
							</div>
						</div>
						
						<div class="trx_donations_options_row">
							<h4 class="trx_donations_options_caption"><?php esc_html_e('PayPal App Client ID', 'trx_donations'); ?></h4>
							<div class="trx_donations_options_field">
								<input type="text" name="pp_client_id" value="<?php echo esc_attr($this->get_option('pp_client_id')); ?>">
								<div class="trx_donations_options_description"><?php echo wp_kses_post('Client ID from the PayPal Application. You can <a href="https://developer.paypal.com/developer/applications/create" target="_blank">Create PayPal Application</a> here', 'trx_donations'); ?></div>
							</div>
						</div>
						
						<div class="trx_donations_options_row">
							<h4 class="trx_donations_options_caption"><?php esc_html_e('PayPal account for Sandbox', 'trx_donations'); ?></h4>
							<div class="trx_donations_options_field">
								<input type="text" name="pp_account_sandbox" value="<?php echo esc_attr($this->get_option('pp_account_sandbox')); ?>">
								<div class="trx_donations_options_description"><?php esc_html_e('PayPal account used in the sandbox mode', 'trx_donations'); ?></div>
							</div>
						</div>
						
						<div class="trx_donations_options_row">
							<h4 class="trx_donations_options_caption"><?php esc_html_e('PayPal App Client ID for Sandbox', 'trx_donations'); ?></h4>
							<div class="trx_donations_options_field">
								<input type="text" name="pp_client_id_sandbox" value="<?php echo esc_attr($this->get_option('pp_client_id_sandbox')); ?>">
								<div class="trx_donations_options_description"><?php esc_html_e('Client ID used in the sandbox mode', 'trx_donations'); ?></div>
							</div>
						</div>
						
						<div class="trx_donations_options_row">
							<h4 class="trx_donations_options_caption"><?php esc_html_e('Sandbox', 'trx_donations'); ?></h4>
							<div class="trx_donations_options_field">
								<label><input type="checkbox" name="pp_sandbox" value="1"<?php echo (int) $this->get_option('pp_sandbox') > 0 ? ' checked="checked"' : ''; ?>> <?php esc_html_e('Enable sandbox mode', 'trx_donations'); ?></label>
								<div class="trx_donations_options_description"><?php esc_html_e('Enable sandbox mode to testing your payments without real money transfer.', 'trx_donations'); ?></div>
							</div>
						</div>
						
						<div class="trx_donations_options_row">
							<h4 class="trx_donations_options_caption"><?php esc_html_e('Default amount', 'trx_donations'); ?></h4>
							<div class="trx_donations_options_field">
								<input type="text" name="pp_amount" value="<?php echo esc_attr($this->get_option('pp_amount')); ?>">
								<div class="trx_donations_options_description"><?php esc_html_e('Default value for donation amount.', 'trx_donations'); ?></div>
							</div>
						</div>
						
						<div class="trx_donations_options_row">
							<h4 class="trx_donations_options_caption"><?php esc_html_e('Currency', 'trx_donations'); ?></h4>
							<div class="trx_donations_options_field">
								<select size="1" name="pp_currency">
									<?php
									foreach ($this->currency_codes as $code=>$title) {
										?>
										<option value="<?php echo esc_attr($code); ?>"<?php echo ($this->get_option('pp_currency')==$code ? ' selected="selected"' : ''); ?>><?php echo trim($title); ?></option>
										<?php
									}
									?>
								</select>
								<div class="trx_donations_options_description"><?php esc_html_e('Default currency for donation.', 'trx_donations'); ?></div>
							</div>
						</div>

					</div>

					
					<div class="trx_donations_options_section trx_donations_options_section_common">

						<h3 class="trx_donations_options_section_title"><?php esc_html_e('Common options', 'trx_donations'); ?></h3>
						
						<div class="trx_donations_options_row">
							<h4 class="trx_donations_options_caption"><?php esc_html_e('Blog style', 'trx_donations'); ?></h4>
							<div class="trx_donations_options_field">
								<select size="1" name="blog_style">
									<?php
									foreach ($this->blog_styles as $code=>$title) {
										?>
										<option value="<?php echo esc_attr($code); ?>"<?php echo ($this->get_option('blog_style')==$code ? ' selected="selected"' : ''); ?>><?php echo trim($title); ?></option>
										<?php
									}
									?>
								</select>
								<div class="trx_donations_options_description"><?php esc_html_e('Default articles style for donations archive page.', 'trx_donations'); ?></div>
							</div>
						</div>
						
						<div class="trx_donations_options_row">
							<h4 class="trx_donations_options_caption"><?php esc_html_e('Blog columns', 'trx_donations'); ?></h4>
							<div class="trx_donations_options_field">
								<input type="text" name="blog_columns" value="<?php echo esc_attr($this->get_option('blog_columns')); ?>">
								<div class="trx_donations_options_description"><?php esc_html_e('Columns number in the donations archive.', 'trx_donations'); ?></div>
							</div>
						</div>
						
						<div class="trx_donations_options_row">
							<h4 class="trx_donations_options_caption"><?php esc_html_e('Max supporters showed', 'trx_donations'); ?></h4>
							<div class="trx_donations_options_field">
								<input type="text" name="max_supporters_to_show" value="<?php echo esc_attr($this->get_option('max_supporters_to_show')); ?>">
								<div class="trx_donations_options_description"><?php esc_html_e('Max number of supporters to show in the single donation post.', 'trx_donations'); ?></div>
							</div>
						</div>
						
						<div class="trx_donations_options_row">
							<h4 class="trx_donations_options_caption"><?php esc_html_e('Socials share', 'trx_donations'); ?></h4>
							<div class="trx_donations_options_field trx_donations_options_field_share">
								<?php
								$share = $this->get_option('share');
								foreach ($this->socials_share as $code=>$link) {
									?><label><input type="checkbox" name="share[<?php echo esc_attr($code); ?>]" value="1"<?php echo !empty($share[$code]) && (int) $share[$code] > 0 ? ' checked="checked"' : ''; ?>> <?php echo trim($code); ?></label><?php
								}
								?>
								<div class="trx_donations_options_description"><?php esc_html_e('Select social networks to enable share articles.', 'trx_donations'); ?></div>
							</div>
						</div>

					</div>


					
					<div class="trx_donations_options_section trx_donations_options_section_theme">

						<h3 class="trx_donations_options_section_title"><?php esc_html_e('Theme specific options', 'trx_donations'); ?></h3>
						
						<div class="trx_donations_options_row">
							<h4 class="trx_donations_options_caption"><?php esc_html_e('Columns wrapper class', 'trx_donations'); ?></h4>
							<div class="trx_donations_options_field">
								<input type="text" name="columns_wrap_class" value="<?php echo esc_attr($this->get_option('columns_wrap_class')); ?>">
								<div class="trx_donations_options_description"><?php esc_html_e('Class name for columns wrapper, used in the  current theme. If empty - use internal class.', 'trx_donations'); ?></div>
							</div>
						</div>
						
						<div class="trx_donations_options_row">
							<h4 class="trx_donations_options_caption"><?php esc_html_e('Single column class', 'trx_donations'); ?></h4>
							<div class="trx_donations_options_field">
								<input type="text" name="column_class" value="<?php echo esc_attr($this->get_option('column_class')); ?>">
								<div class="trx_donations_options_description"><?php esc_html_e('Class name (mask) for column item, used in the  current theme. If empty - use internal class.', 'trx_donations'); esc_html_e('For example: column-$1_$2, where $1 - column width, $2 - total columns: column-1_4, column-2_3, etc.', 'trx_donations'); ?></div>
							</div>
						</div>

					</div>

					<input type="submit" value="<?php esc_attr_e('Save options', 'trx_donations'); ?>">

				</form>
				
			</div>
			<?php
		}
		
		
		// Add meta box to the "New/Edit Donation" page
		function admin_add_meta_box($post_type) {
			if ($post_type == self::POST_TYPE) {
				add_meta_box(
					'trx_donations_meta_box',							// id for this section
					esc_html__( 'Donation Options', 'myplugin_textdomain' ),	// Header for this section
					array($this, 'admin_show_meta_box'),				// Method to display meta box fields
					self::POST_TYPE									// Screen name where meta box will be displayed
				);
			}
		}
	
		
		// Create additional columns in the donations list
		function admin_add_list_columns( $columns ){
			if (is_array($columns) && count($columns)>0) {
				$new_columns = array();
				foreach($columns as $k=>$v) {
					if ($k=='comments') {
						$new_columns['trx_donations_goal'] = esc_html__('Goal', 'trx_donations');
						$new_columns['trx_donations_raised'] = esc_html__('Raised', 'trx_donations');
					}
					$new_columns[$k] = $v;
				}
				$columns = $new_columns;
			}
			return $columns;
		}
		
		// Fill columns with data
		function admin_fill_list_columns($column_name='', $post_id=0) {
			if ($post_id == 0) return;
			if ($column_name == 'trx_donations_goal') {
				$goal = get_post_meta( $post_id, 'trx_donations_goal', true );
				echo floatval($goal);
			} else if ($column_name == 'trx_donations_raised') {
				$manual = get_post_meta( $post_id, 'trx_donations_manual', true );
				$raised = get_post_meta( $post_id, 'trx_donations_raised', true );
				if (empty($raised)) $raised = 0;
				echo floatval($manual + $raised);
			}
		}

	
		// Show meta box on the "New/Edit Donation" page
		function admin_show_meta_box( $post ) {
		
			// Add a nonce field so we can check for it later.
			wp_nonce_field( get_admin_url(), 'trx_donations_meta_box_nonce' );
	
			// Show social profiles
			/*
			$meta = get_post_meta( $post->ID, 'trx_donations_socials', true );
			$socials = $this->options['socials'];
			if (!empty($meta)) $socials = array_merge($socials, $meta);
			if (is_array($socials) && count($socials) > 0) {
				?>
				<div class="trx_donations_meta_socials">
					<h4 class="trx_donations_meta_title"><?php esc_html_e('Socials', 'trx_donations'); ?></h4>
					<p class="trx_donations_meta_description"><?php esc_html_e('Specify URLs to this project\'s profiles in the social networks', 'trx_donations'); ?></p>
					<?php foreach ($socials as $soc_name => $soc_link) { ?>
						<div class="trx_donations_meta_socials_item trx_donations_meta_socials_item_<?php echo esc_attr($soc_name); ?>">
							<label for="trx_donations_meta_socials_field_<?php echo esc_attr($soc_name); ?>"><?php echo esc_html(strtoupper($soc_name)); ?></label>
							<input type="text" id="trx_donations_meta_socials_field_<?php echo esc_attr($soc_name); ?>" name="trx_donations_meta_socials_field_<?php echo esc_attr($soc_name); ?>" value="<?php echo esc_attr($soc_link); ?>">
						</div>
					<?php } ?>
				</div>
				<?php
			}
			*/
	
			// Show goal and manually added money
			?>
			<div class="trx_donations_meta_money">
	
				<h4 class="trx_donations_meta_title"><?php esc_html_e('Donations details', 'trx_donations'); ?></h4>
				<p class="trx_donations_meta_description"><?php esc_html_e('Specify dotations goal and start manually added money to this project', 'trx_donations'); ?></p>
	
				<?php
				// Group Goal
				$meta = get_post_meta( $post->ID, 'trx_donations_goal', true );
				?>
				<div class="trx_donations_meta_money_item trx_donations_meta_goal">
					<label for="trx_donations_meta_goal_field"><?php esc_html_e('Group Goal', 'trx_donations'); ?></label>
					<input type="text" id="trx_donations_meta_goal_field" name="trx_donations_meta_goal_field" value="<?php echo esc_attr($meta); ?>">
				</div>
	
				<?php
				// Manually added
				$meta = get_post_meta( $post->ID, 'trx_donations_manual', true );
				?>
				<div class="trx_donations_meta_money_item trx_donations_meta_manual">
					<label for="trx_donations_meta_manual_field"><?php esc_html_e('Manually added', 'trx_donations'); ?></label>
					<input type="text" id="trx_donations_meta_manual_field" name="trx_donations_meta_manual_field" value="<?php echo esc_attr($meta); ?>">
				</div>
	
			</div>
	
			<?php
			// Supporters
			$meta = get_post_meta( $post->ID, 'trx_donations_supporters', true );
			?>
			<div class="trx_donations_meta_supporters">
				<h4 class="trx_donations_meta_title"><?php esc_html_e('Supporters', 'trx_donations'); ?></h4>
				<p class="trx_donations_meta_description"><?php esc_html_e('Supporters list for this project', 'trx_donations'); ?></p>
				<?php if (is_array($meta) && count($meta) > 0) { ?>
					<table>
						<thead>
							<tr>
								<th width="15%"><?php esc_html_e('Date', 'trx_donations'); ?></th>
								<th><?php esc_html_e('Name', 'trx_donations'); ?></th>
								<th width="15%"><?php esc_html_e('Amount', 'trx_donations'); ?></th>
								<th width="15%"><?php esc_html_e('E-mail', 'trx_donations'); ?></th>
								<th width="15%"><?php esc_html_e('Site', 'trx_donations'); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php
						$total = 0;
						foreach ($meta as $v) {
							$total += $v['amount'];
							?>
							<tr>
								<td class="trx_donations_meta_supporters_date"><?php echo date(get_option('date_format'), $v['time']); ?></td>
								<td class="trx_donations_meta_supporters_name">
									<span class="trx_donations_meta_supporters_title"><?php echo esc_html($v['name']); ?></span>
									<?php echo (!empty($v['message']) ? '<span class="trx_donations_meta_supporters_message">' . esc_html($v['message']) . '</span>' : ''); ?>
								</td>
								<td class="trx_donations_meta_supporters_amount" align="right"><?php echo esc_html($this->get_money($v['amount'])); ?></td>
								<td class="trx_donations_meta_supporters_email"><?php echo esc_html($v['email']); ?></td>
								<td class="trx_donations_meta_supporters_site"><?php echo esc_html($v['site']); ?></td>
							</tr>
							<?php 
						}
						?>
						</tbody>
						<tfoot>
							<tr>
								<td align="right" colspan="2"><?php esc_html_e('Total donated', 'trx_donations'); ?></td>
								<td align="right"><?php echo esc_html($this->get_money($total)); ?></td>
								<td colspan="2"><?php echo esc_html($this->get_money($total)); ?></td>
							</tr>
						</tfoot>
					</table>
				<?php } else { ?>
					<p class="trx_donations_meta_error"><?php esc_html_e('No supporters yet', 'trx_donations'); ?></p>
				<?php } ?>			
			</div>
			<?php
		}
	
		// Save meta box on the "New/Edit Donation" page
		function admin_save_meta_box( $post_id ) {
		
			// Check if our nonce is set.
			if ( ! isset( $_POST['trx_donations_meta_box_nonce'] ) ) {
				return;
			}
		
			// Verify that the nonce is valid.
			if ( ! wp_verify_nonce( $_POST['trx_donations_meta_box_nonce'], get_admin_url() ) ) {
				return;
			}
		
			// If this is an autosave, our form has not been submitted, so we don't want to do anything.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}
		
			// Check the user's permissions.
			if ( isset( $_POST['post_type'] ) && $_POST['post_type']==self::POST_TYPE ) {
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}
			}
		
			// OK, it's safe for us to save the data now.
			
			/*
			// Socials
			if (!empty($this->options['socials'])) {
				$socials = $this->options['socials'];
				foreach ($socials as $k=>$v) {
					if (isset($_POST['trx_donations_meta_socials_field_'.$k]))
						$socials[$k] = sanitize_text_field($_POST['trx_donations_meta_socials_field_'.$k]);
				}
				update_post_meta( $post_id, 'trx_donations_socials', $socials );
			}
			*/

			// Group Goal
			if (isset($_POST['trx_donations_meta_goal_field'])) {
				$goal = (float) sanitize_text_field($_POST['trx_donations_meta_goal_field']);
				update_post_meta( $post_id, 'trx_donations_goal', $goal );
			}
	
			// Manually added
			if (isset($_POST['trx_donations_meta_manual_field'])) {
				$manual = (float) sanitize_text_field($_POST['trx_donations_meta_manual_field']);
				update_post_meta( $post_id, 'trx_donations_manual', $manual );
			}
	
		}

	}
}
?>