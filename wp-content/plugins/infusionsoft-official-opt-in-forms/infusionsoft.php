<?php
/*
 * Plugin Name: Infusionsoft Official Opt-in Forms
 * Plugin URI: https://www.infusionsoft.com
 * Version: 1.0.4
 * Description: Infusionsoft Official Opt-in Forms
 * Author: Infusionsoft
 * Author URI: https://www.infusionsoft.com
 * Text Domain: infusionsoft
 * Domain Path: /languages/
 * License: GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'INF_INFUSIONSOFT_PLUGIN_DIR', trailingslashit( dirname( __FILE__ ) ) );
define( 'INF_INFUSIONSOFT_PLUGIN_URI', plugins_url( '', __FILE__ ) );

if ( ! class_exists( 'INF_Dashboard' ) ) {
	require_once( INF_INFUSIONSOFT_PLUGIN_DIR . 'dashboard/dashboard.php' );
}

if ( ! class_exists( 'infusionsoft_infusionbar' ) ) {
	require_once( INF_INFUSIONSOFT_PLUGIN_DIR . 'includes/ext/infusionsoft_infusionbar/class.infusionsoft_infusionbar.php' );
}

require_once( 'includes/infusionsoft_functions.php' );

class INF_Infusionsoft extends INF_Dashboard {
	var $plugin_version = '1.0.0';
	var $db_version = '1.0';
	var $_options_pagename = 'inf_infusionsoft_options';
	var $menu_page;
	var $protocol;

	private static $_this;

	function __construct() {
		// Don't allow more than one instance of the class
		/*if ( isset( self::$_this ) ) {
			wp_die( sprintf( __( '%s is a singleton class and you cannot create a second instance.', 'infusionsoft' ),
					get_class( $this ) )
			);
		}*/
		global $pagenow;
		self::$_this = $this;

		$this->protocol = is_ssl() ? 'https' : 'http';

		add_action( 'admin_menu', array( $this, 'add_menu_link' ) );

		add_action( 'plugins_loaded', array( $this, 'add_localization' ) );

		add_action( 'admin_init', array( $this, 'execute_footer_text' ) );

		add_filter( 'inf_infusionsoft_import_sub_array', array( $this, 'import_settings' ) );
		add_filter( 'inf_infusionsoft_import_array', array( $this, 'import_filter' ) );
		add_filter( 'inf_infusionsoft_export_exclude', array( $this, 'filter_export_settings' ) );
		add_filter( 'inf_infusionsoft_save_button_class', array( $this, 'save_btn_class' ) );

		// generate home tab in dashboard
		add_action( 'inf_infusionsoft_after_header_options', array( $this, 'generate_home_tab' ) );

		add_action( 'inf_infusionsoft_after_main_options', array( $this, 'generate_premade_templates' ) );

		add_action( 'inf_infusionsoft_after_save_button', array( $this, 'add_next_button' ) );
		do_action( 'infusionsoft_ext_init' );
		$plugin_file = plugin_basename( __FILE__ );
		add_filter( "plugin_action_links_{$plugin_file}", array( $this, 'add_settings_link' ) );


		$dashboard_args = array(
			'inf_dashboard_options_pagename'  => $this->_options_pagename,
			'inf_dashboard_plugin_name'       => 'infusionsoft',
			'inf_dashboard_save_button_text'  => __( 'Save & Exit', 'infusionsoft' ),
			'inf_dashboard_plugin_class_name' => 'inf_infusionsoft',
			'inf_dashboard_options_path'      => INF_INFUSIONSOFT_PLUGIN_DIR . 'dashboard/includes/options.php',
			'inf_dashboard_options_page'      => 'toplevel_page',
		);

		parent::__construct( $dashboard_args );

		// Register save settings function for ajax request
		add_action( 'wp_ajax_inf_infusionsoft_save_settings', array( $this, 'infusionsoft_save_settings' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts_styles' ) );

		add_action( 'wp_ajax_infusionsoft_reset_options_page', array( $this, 'infusionsoft_reset_options_page' ) );

		add_action( 'wp_ajax_infusionsoft_remove_optin', array( $this, 'remove_optin' ) );

		add_action( 'wp_ajax_infusionsoft_duplicate_optin', array( $this, 'duplicate_optin' ) );

		add_action( 'wp_ajax_infusionsoft_add_variant', array( $this, 'add_variant' ) );

		add_action( 'wp_ajax_infusionsoft_home_tab_tables', array( $this, 'home_tab_tables' ) );

		add_action( 'wp_ajax_infusionsoft_refresh_accounts_header', array( $this, 'refresh_accounts_header' ) );

		add_action( 'wp_ajax_infusionsoft_toggle_optin_status', array( $this, 'toggle_optin_status' ) );

		add_action( 'wp_ajax_infusionsoft_authorize_account', array( $this, 'authorize_account' ) );

		add_action( 'wp_ajax_infusionsoft_reset_accounts_table', array( $this, 'reset_accounts_table' ) );

		add_action( 'wp_ajax_infusionsoft_generate_mailing_lists', array( $this, 'generate_mailing_lists' ) );

		add_action( 'wp_ajax_infusionsoft_generate_new_account_fields', array( $this, 'generate_new_account_fields' ) );

		add_action( 'wp_ajax_infusionsoft_generate_accounts_list', array( $this, 'generate_accounts_list' ) );

		add_action( 'wp_ajax_infusionsoft_generate_edit_account_page', array( $this, 'generate_edit_account_page' ) );

		add_action( 'wp_ajax_infusionsoft_save_account_tab', array( $this, 'save_account_tab' ) );

		add_action( 'wp_ajax_infusionsoft_ab_test_actions', array( $this, 'ab_test_actions' ) );

		add_action( 'wp_ajax_infusionsoft_get_stats_graph_ajax', array( $this, 'get_stats_graph_ajax' ) );

		add_action( 'wp_ajax_infusionsoft_refresh_optins_stats_table', array( $this, 'refresh_optins_stats_table' ) );

		add_action( 'wp_ajax_infusionsoft_reset_stats', array( $this, 'reset_stats' ) );

		add_action( 'wp_ajax_infusionsoft_pick_winner_optin', array( $this, 'pick_winner_optin' ) );

		add_action( 'wp_ajax_infusionsoft_clear_stats', array( $this, 'clear_stats' ) );
		add_action( 'wp_ajax_infusionsoft_clear_stats_single_optin', array( $this, 'clear_stats_single_optin' ) );


		add_action( 'wp_ajax_infusionsoft_get_premade_values', array( $this, 'get_premade_values' ) );
		add_action( 'wp_ajax_infusionsoft_generate_template_filter', array( $this, 'generate_template_filter' ) );
		add_action( 'wp_ajax_infusionsoft_generate_premade_grid', array( $this, 'generate_premade_grid' ) );

		add_action( 'wp_ajax_infusionsoft_display_preview', array( $this, 'display_preview' ) );

		add_action( 'wp_ajax_infusionsoft_handle_stats_adding', array( $this, 'handle_stats_adding' ) );
		add_action( 'wp_ajax_nopriv_infusionsoft_handle_stats_adding', array( $this, 'handle_stats_adding' ) );

		add_action( 'wp_ajax_infusionsoft_subscribe', array( $this, 'subscribe' ) );
		add_action( 'wp_ajax_nopriv_infusionsoft_subscribe', array( $this, 'subscribe' ) );

		add_action( 'widgets_init', array( $this, 'register_widget' ) );

		add_action( 'after_setup_theme', array( $this, 'register_image_sizes' ) );

		add_shortcode( 'inf_infusionsoft_inline', array( $this, 'display_inline_shortcode' ) );
		add_shortcode( 'inf_infusionsoft_locked', array( $this, 'display_locked_shortcode' ) );

		add_filter( 'body_class', array( $this, 'add_body_class' ) );
		register_activation_hook( __FILE__, 'infusion_version_check' );

		if ( $pagenow == 'plugins.php' || isset( $_GET['page'] ) && $_GET['page'] == 'inf_infusionsoft_options' ) {
			add_action( 'admin_notices', 'infusion_version_check' );
		}

		register_activation_hook( __FILE__, array( $this, 'activate_plugin' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate_plugin' ) );
		add_action( 'infusionsoft_stats_auto_refresh', array( $this, 'perform_stats_refresh' ) );

		add_action( 'options_saved', array( $this, 'sync_optin_forms' ) );

		$this->frontend_register_locations();

		foreach ( array( 'post.php', 'post-new.php' ) as $hook ) {
			add_action( "admin_head-$hook", array( $this, 'tiny_mce_vars' ) );
			add_action( "admin_head-$hook", array( $this, 'add_mce_button_filters' ) );
		}

		add_action( 'wp_head', array( $this, 'add_web_tracking' ) );
	}

	function activate_plugin() {
		// schedule lists auto update daily
		wp_schedule_event( time(), 'daily', 'infusionsoft_lists_auto_refresh' );

		//install the db for stats
		$this->db_install();
	}

	function deactivate_plugin() {
		// remove lists auto updates from wp cron if plugin deactivated
		wp_clear_scheduled_hook( 'infusionsoft_lists_auto_refresh' );
		wp_clear_scheduled_hook( 'infusionsoft_stats_auto_refresh' );
	}

	function define_page_name() {
		return $this->_options_pagename;
	}


	function add_web_tracking() {
		$options_array = INF_Infusionsoft::get_infusionsoft_options();

		if ( isset( $options_array['accounts'] ) && isset( $options_array['accounts']['infusionsoft'] ) ) {
			$accounts = $options_array['accounts']['infusionsoft'];
			reset( $accounts );
			$account = key( $accounts );

			if ( isset( $options_array['accounts']['infusionsoft'][ $account ]['client_id'] ) ) {
				$output = "<script src=\"https://" . $options_array['accounts']['infusionsoft'][ $account ]['client_id'] . ".infusionsoft.com/app/webTracking/getTrackingCode\"></script >";
				echo $output;
			}
		}
	}


	/**
	 * Returns an instance of the object
	 *
	 * @return object
	 */
	static function get_this() {
		return self::$_this;
	}

	function add_menu_link() {
		$menu_page = add_menu_page( __( 'Infusionsoft', 'infusionsoft' ), __( 'Infusionsoft', 'infusionsoft' ),
			'manage_options', 'inf_infusionsoft_options', array( $this, 'options_page' ) );
		add_submenu_page( 'inf_infusionsoft_options', __( 'Infusionsoft', 'infusionsoft' ),
			__( 'Opt-in Forms', 'infusionsoft' ), 'manage_options', 'inf_infusionsoft_options' );
		add_submenu_page( 'inf_infusionsoft_options', __( 'Settings', 'infusionsoft' ),
			__( 'Settings', 'infusionsoft' ), 'manage_options',
			'admin.php?page=inf_infusionsoft_options#tab_inf_dashboard_tab_content_header_accounts' );
		add_submenu_page( 'inf_infusionsoft_options', __( 'Statistics', 'infusionsoft' ),
			__( 'Statistics', 'infusionsoft' ), 'manage_options',
			'admin.php?page=inf_infusionsoft_options#tab_inf_dashboard_tab_content_header_stats' );
		add_submenu_page( 'inf_infusionsoft_options', __( 'Import & Export', 'infusionsoft' ),
			__( 'Import & Export', 'infusionsoft' ), 'manage_options',
			'admin.php?page=inf_infusionsoft_options#tab_inf_dashboard_tab_content_header_importexport' );
	}

	function add_body_class( $body_class ) {
		$body_class[] = 'inf_infusionsoft';

		return $body_class;
	}

	function save_btn_class() {
		return 'inf_dashboard_custom_save';
	}

	/**
	 * Adds plugin localization
	 * Domain: infusionsoft
	 *
	 * @return void
	 */
	function add_localization() {
		load_plugin_textdomain( 'infusionsoft', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	// Add settings link on plugin page
	function add_settings_link( $links ) {
		$settings_link = sprintf( '<a href="admin.php?page=inf_infusionsoft_options">%1$s</a>',
			__( 'Settings', 'infusionsoft' ) );
		array_unshift( $links, $settings_link );

		return $links;
	}

	function options_page() {
		INF_Infusionsoft::generate_options_page( $this->generate_optin_id() );
	}

	function import_settings() {
		return true;
	}


	function infusionsoft_save_settings() {
		INF_Infusionsoft::dashboard_save_settings();
	}

	function filter_export_settings( $options ) {
		$updated_array = array_merge( $options, array( 'accounts' ) );

		return $updated_array;
	}

	/**
	 *
	 * Adds the "Next" button into the Infusionsoft dashboard via INF_Dashboard action.
	 * @return prints the data on screen
	 *
	 */
	function add_next_button() {
		printf( '
			<div class="inf_dashboard_row inf_dashboard_next_design">
				<button class="inf_dashboard_icon">%1$s</button>
			</div>', __( 'Next: Design', 'infusionsoft' ) );

		printf( '
			<div class="inf_dashboard_row inf_dashboard_next_display">
				<button class="inf_dashboard_icon">%1$s</button>
			</div>', __( 'Next: Display Settings', 'infusionsoft' ) );

		printf( '
			<div class="inf_dashboard_row inf_dashboard_next_customize">
				<button class="inf_dashboard_icon" data-selected_layout="layout_1">%1$s</button>
			</div>', __( 'Next: Customize', 'infusionsoft' ) );

		printf( '
			<div class="inf_dashboard_row inf_dashboard_next_shortcode">
				<button class="inf_dashboard_icon">%1$s</button>
			</div>', __( 'Generate Shortcode', 'infusionsoft' ) );
	}

	/**
	 * Retrieves the Infusionsoft options from DB and makes it available outside the class
	 * @return array
	 */
	public static function get_infusionsoft_options() {
		return get_option( 'inf_infusionsoft_options' ) ? get_option( 'inf_infusionsoft_options' ) : array();
	}

	/**
	 * Updates the Infusionsoft options outside the class
	 * @return void
	 */
	public static function update_infusionsoft_options( $update_array ) {
		$dashboard_options = INF_Infusionsoft::get_infusionsoft_options();

		$updated_options = array_merge( $dashboard_options, $update_array );
		update_option( 'inf_infusionsoft_options', $updated_options );
	}

	/**
	 * Filters the options_array before importing data. Function generates new IDs for imported options to avoid
	 * replacement of existing ones. Filter is used in INF_Dashboard class
	 * @return array
	 */
	function import_filter( $options_array ) {
		$updated_array = array();
		$new_id        = $this->generate_optin_id( false );

		foreach ( $options_array as $key => $value ) {
			$updated_array[ 'optin_' . $new_id ] = $options_array[ $key ];

			//reset accounts settings and make all new optins inactive
			$updated_array[ 'optin_' . $new_id ]['email_provider'] = 'empty';
			$updated_array[ 'optin_' . $new_id ]['account_name']   = 'empty';
			$updated_array[ 'optin_' . $new_id ]['email_list']     = 'empty';
			$updated_array[ 'optin_' . $new_id ]['optin_status']   = 'inactive';
			$new_id ++;
		}

		return $updated_array;
	}

	function add_mce_button_filters() {
		add_filter( 'mce_external_plugins', array( $this, 'add_mce_button' ) );
		add_filter( 'mce_buttons', array( $this, 'register_mce_button' ) );
	}

	function add_mce_button( $plugin_array ) {
		global $typenow;

		wp_enqueue_style( 'infusionsoft-shortcodes', INF_INFUSIONSOFT_PLUGIN_URI . '/css/tinymcebutton.css', array(),
			$this->plugin_version );
		$plugin_array['infusionsoft'] = INF_INFUSIONSOFT_PLUGIN_URI . '/js/infusionsoft-mce-buttons.js';


		return $plugin_array;
	}

	function register_mce_button( $buttons ) {
		global $typenow;

		array_push( $buttons, 'infusionsoft_button' );

		return $buttons;
	}


	/**
	 * Pass locked_optins and inline_optins lists to tiny-MCE script
	 */
	function tiny_mce_vars() {
		$options_array = INF_Infusionsoft::get_infusionsoft_options();
		$locked_array  = array();
		$inline_array  = array();
		$onclick_array = array();
		if ( ! empty( $options_array ) ) {
			foreach ( $options_array as $optin_id => $details ) {
				if ( 'accounts' !== $optin_id ) {
					if ( isset( $details['optin_status'] ) && 'active' === $details['optin_status'] && empty( $details['child_of'] ) ) {
						if ( '1' == $details['click_trigger'] ) {
							$onclick_array = array_merge( $onclick_array,
								array( $optin_id => preg_replace( '/[^A-Za-z0-9 _-]/', '', $details['optin_name'] ) ) );
						}

						if ( 'inline' == $details['optin_type'] ) {
							$inline_array = array_merge( $inline_array, array( $optin_id => $details['optin_name'] ) );
						}

						if ( 'locked' == $details['optin_type'] ) {
							$locked_array = array_merge( $locked_array, array( $optin_id => $details['optin_name'] ) );
						}
					}
				}
			}
		}

		if ( empty( $locked_array ) ) {
			$locked_array = array(
				'empty' => __( 'No optins available', 'infusionsoft' ),
			);
		}

		if ( empty( $inline_array ) ) {
			$inline_array = array(
				'empty' => __( 'No optins available', 'infusionsoft' ),
			);
		}
		if ( empty( $onclick_array ) ) {
			$onclick_array = array(
				'empty' => __( 'No optins available', 'infusionsoft' ),
			);
		}
		?>

        <!-- TinyMCE Shortcode Plugin -->
        <script type='text/javascript'>
            var infusionsoft = {
                'onclick_optins': '<?php echo json_encode( $onclick_array ); ?>',
                'locked_optins': '<?php echo json_encode( $locked_array ); ?>',
                'inline_optins': '<?php echo json_encode( $inline_array ); ?>',
                'infusionsoft_tooltip': '<?php _e( "insert Infusionsoft Opt-In", "infusionsoft" ); ?>',
                'inline_text': '<?php _e( "Inline Opt-In", "infusionsoft" ); ?>',
                'locked_text': '<?php _e( "Locked Content Opt-In", "infusionsoft" ); ?>'
            }
        </script>
        <!-- TinyMCE Shortcode Plugin -->
		<?php
	}

	function db_install() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'inf_infusionsoft_stats';

		/*
		 * We'll set the default character set and collation for this table.
		 * If we don't do this, some characters could end up being converted
		 * to just ?'s when saved in our table.
		 */
		$charset_collate = '';

		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		}

		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE {$wpdb->collate}";
		}

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			record_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			record_type varchar(3) NOT NULL,
			optin_id varchar(20) NOT NULL,
			list_id varchar(100) NOT NULL,
			ip_address varchar(45) NOT NULL,
			page_id varchar(20) NOT NULL,
			removed_flag boolean NOT NULL,
			UNIQUE KEY id (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		$db_version = array(
			'db_version' => $this->db_version,
		);
		INF_Infusionsoft::update_option( $db_version );
	}

	function register_image_sizes() {
		add_image_size( 'infusionsoft_image', 610 );
	}

	/**
	 * Generates the Infusionsoft's Home, Stats, Accounts tabs. Hooked to Dashboard class
	 */
	function generate_home_tab( $option, $dashboard_settings = array() ) {
		$options_array = INF_Infusionsoft::get_infusionsoft_options();

		switch ( $option['type'] ) {
			case 'home' :
				printf( '
					<div class="inf_dashboard_row inf_dashboard_new_optin">
						<h1>%2$s</h1>
						<button class="inf_dashboard_icon">%1$s</button>
						<input type="hidden" name="action" value="new_optin" />
					</div>', esc_html__( 'New Opt-in Form', 'infusionsoft' ),
					esc_html__( 'Active Opt-In Forms', 'infusionsoft' ) );
				printf( '
					<div class="inf_dashboard_row inf_dashboard_optin_select">
						<h3>%1$s</h3>
						<span class="inf_dashboard_icon inf_dashboard_close_button"></span>
						<ul>
							<li class="inf_dashboard_optin_type inf_dashboard_optin_add inf_dashboard_optin_type_popup" data-type="pop_up">
								<h6>%2$s</h6>
								<div class="optin_select_grey">
									<div class="optin_select_light_grey">
									</div>
								</div>
							</li>
							<li class="inf_dashboard_optin_type inf_dashboard_optin_add inf_dashboard_optin_type_flyin" data-type="flyin">
								<h6>%3$s</h6>
								<div class="optin_select_grey"></div>
								<div class="optin_select_light_grey"></div>
							</li>
							<li class="inf_dashboard_optin_type inf_dashboard_optin_add inf_dashboard_optin_type_below" data-type="below_post">
								<h6>%4$s</h6>
								<div class="optin_select_grey"></div>
								<div class="optin_select_light_grey"></div>
							</li>
							<li class="inf_dashboard_optin_type inf_dashboard_optin_add inf_dashboard_optin_type_inline" data-type="inline">
								<h6>%5$s</h6>
								<div class="optin_select_grey"></div>
								<div class="optin_select_light_grey"></div>
								<div class="optin_select_grey"></div>
							</li>

						</ul>
						<ul>
						<li class="inf_dashboard_optin_type inf_dashboard_optin_add inf_dashboard_optin_type_locked" data-type="locked">
								<h6>%6$s</h6>
								<div class="optin_select_grey"></div>
								<div class="optin_select_light_grey"></div>
								<div class="optin_select_grey"></div>
							</li>
							<li class="inf_dashboard_optin_type inf_dashboard_optin_add inf_dashboard_optin_type_widget" data-type="widget">
								<h6>%7$s</h6>
								<div class="optin_select_grey"></div>
								<div class="optin_select_light_grey"></div>
								<div class="optin_select_grey_small"></div>
								<div class="optin_select_grey_small last"></div>
							</li>
						<li class="inf_dashboard_optin_type inf_dashboard_optin_add inf_dashboard_optin_type_infusionbar" data-type="infusion">
								<h6>%8$s</h6>
								<div class="optin_select_light_grey"></div>
								<div class="optin_select_grey"></div>
							</li>
						</ul>
					</div>', esc_html__( 'Choose an opt-in form type', 'infusionsoft' ),
					esc_html__( 'pop up', 'infusionsoft' ), esc_html__( 'fly in', 'infusionsoft' ),
					esc_html__( 'below post', 'infusionsoft' ), esc_html__( 'inline', 'infusionsoft' ),
					esc_html__( 'locked content', 'infusionsoft' ), esc_html__( 'widget', 'infusionsoft' ),
					esc_html__( 'bar', 'infusionsoft' ) );

				$this->display_home_tab_tables();
				break;

			case 'account' :
				printf( '
						<div class="inf_dashboard_row inf_dashboard_new_account_row">
						<h1>%1$s</h1>', esc_html__( 'My Account', 'infusionsoft' ) );

				$accountDoesntExist = ( isset( $options_array['accounts'] ) && count( $options_array['accounts']['infusionsoft'] ) == 0 ) || ! isset( $options_array['accounts'] );

				if ( $accountDoesntExist ) {
					printf( '
						<button class="inf_dashboard_icon account_button">%1$s</button>
						<input type="hidden" name="action" value="new_account" />
					',

						esc_html__( 'New Account', 'infusionsoft' ) );
				}
				printf( '</div>' );

				$this->display_accounts_table();
				break;

			case 'edit_account' :
				echo '<div id="inf_dashboard_edit_account_tab"></div>';
				break;

			case 'stats' :
				printf( '
					<div class="inf_dashboard_row inf_dashboard_stats_row">
						<h1>%1$s</h1>
						<div class="inf_infusionsoft_stats_controls">
							<button class="inf_dashboard_icon inf_infusionsoft_clear_stats">%2$s</button>
							<span class="inf_dashboard_confirmation">%4$s</span>
							<button class="inf_dashboard_icon inf_infusionsoft_refresh_stats">%3$s</button>
						</div>
					</div>
					<span class="inf_infusionsoft_stats_spinner"></span>
					<div class="inf_dashboard_stats_contents"></div>', esc_html( $option['title'] ),
					esc_html__( 'Clear Stats', 'infusionsoft' ), esc_html__( 'Refresh Stats', 'infusionsoft' ),
					sprintf( '%1$s<span class="inf_dashboard_confirm_stats">%2$s</span><span class="inf_dashboard_cancel_delete">%3$s</span>',
						esc_html__( 'Remove all the stats data?', 'infusionsoft' ), esc_html__( 'Yes', 'infusionsoft' ),
						esc_html__( 'No', 'infusionsoft' ) ) );
				break;

			case 'support'    :
				break;
		}
	}

	function refresh_accounts_header() {
		$options_array = INF_Infusionsoft::get_infusionsoft_options();

		printf( '
				<div class="inf_dashboard_row inf_dashboard_new_account_row">
				<h1>%1$s</h1>', esc_html__( 'My Account', 'infusionsoft' ) );

		if ( isset( $options_array['accounts'] ) && count( $options_array['accounts']['infusionsoft'] ) == 0 ) {
			printf( '
				<button class="inf_dashboard_icon account_button">%1$s</button>
				<input type="hidden" name="action" value="new_account" />
			',

				esc_html__( 'New Account', 'infusionsoft' ) );
		}
		printf( '</div>' );
		die();
	}


	/**
	 * Generates tab for the premade layouts selection
	 */
	function generate_premade_templates( $option ) {
		switch ( $option['type'] ) {
			case 'premade_templates' :
				echo '<div class="inf_infusionsoft_premade_grid"><span class="spinner inf_infusionsoft_premade_spinner"></span></div>';
				break;
			case 'preview_optin' :
				printf( '
					<div class="inf_dashboard_row inf_dashboard_preview">
						<button class="inf_dashboard_icon">%1$s</button>
					</div>', esc_html__( 'Preview', 'infusionsoft' ) );
				break;
		}
	}


	function generate_template_filter() {
		if ( ! wp_verify_nonce( $_POST['infusionsoft_premade_nonce'], 'infusionsoft_premade' ) ) {
			die( - 1 );
		}
		$filter_path = INF_INFUSIONSOFT_PLUGIN_URI . '/images';
		$isRapidBar  = '';
		$isRedirect  = '';
		$isRapidBar  = sanitize_text_field( $_POST['isRapidBar'] );
		$isRedirect  = sanitize_text_field( $_POST['isRedirect'] );
		if ( $isRapidBar == 'true' ) {
			$this->generate_premade_grid();
			die();
		}
		$output = '';
		$output .= <<<SOL
		<div class="layout_filter_wrapper">
			<p>Filter by Layout</p>
			<div class="layout_filter">
				<img src="$filter_path/layout_bottomform_sidepic.svg" data-form="bottom" data-img="left"/>
				<img src="$filter_path/layout_bottomform_toppic.svg" data-form="bottom" data-img="above"/>
				<img src="$filter_path/layout_sideform.svg" data-form="right" data-img="side"/>
			</div>
		</div>
		<div class="templates_loading">
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="-5 -15 120 120" width="150" height="150" fill="#14283a">
  <circle transform="translate(14 0)" cx="0" cy="14" r="0">
    <animate attributeName="r" values="0; 20; 0; 0" dur="1.2s" repeatCount="indefinite" begin="0"
      keytimes="0;0.2;0.7;1" keySplines="0.2 0.2 0.4 0.8;0.2 0.6 0.4 0.8;0.2 0.6 0.4 0.8" calcMode="spline" />
  </circle>
  <circle transform="translate(50 0)" cx="0" cy="14" r="0">
    <animate attributeName="r" values="0; 20; 0; 0" dur="1.2s" repeatCount="indefinite" begin="0.3"
      keytimes="0;0.2;0.7;1" keySplines="0.2 0.2 0.4 0.8;0.2 0.6 0.4 0.8;0.2 0.6 0.4 0.8" calcMode="spline" />
  </circle>
  <circle transform="translate(80 0)" cx="0" cy="14" r="0">
    <animate attributeName="r" values="0; 20; 0; 0" dur="1.2s" repeatCount="indefinite" begin="0.6"
      keytimes="0;0.2;0.7;1" keySplines="0.2 0.2 0.4 0.8;0.2 0.6 0.4 0.8;0.2 0.6 0.4 0.8" calcMode="spline" />
  </circle>
</svg>
		</div>
		<div class="inf_infusionsoft_premade_grid"></div>
SOL;
		die( $output );
	}

	function generate_premade_grid() {
		$isRapidBar    = '';
		$isRedirect    = '';
		$formLocation  = '';
		$imgLocation   = '';
		$rapidBarClass = '';
		if ( ! wp_verify_nonce( $_POST['infusionsoft_premade_nonce'], 'infusionsoft_premade' ) ) {
			die( - 1 );
		}
		$isRapidBar   = sanitize_text_field( $_POST['isRapidBar'] );
		$isRedirect   = sanitize_text_field( $_POST['isRedirect'] );
		$formLocation = ( $_POST['formLocation'] != '' ? sanitize_text_field( $_POST['formLocation'] ) : '' );
		$imgLocation  = sanitize_text_field( $_POST['imgLocation'] );
		$layoutFolder = $isRedirect == 'true' ? 'redirect' : 'form';
		$filter_path  = INF_INFUSIONSOFT_PLUGIN_URI . '/images';
		if ( $isRapidBar == 'true' ) {
			require_once( INF_INFUSIONSOFT_PLUGIN_DIR . 'includes/ext/infusionsoft_infusionbar/layouts/' . $layoutFolder . '/premade-layouts.php' );
			$imgpath       = INF_INFUSIONSOFT_PLUGIN_URI . '/includes/ext/infusionsoft_infusionbar/layouts/' . $layoutFolder . '/images/thumb_';
			$rapidBarClass = ' infusion_layouts';
		} else {
			require_once( INF_INFUSIONSOFT_PLUGIN_DIR . 'includes/premade-layouts.php' );
			$imgpath = INF_INFUSIONSOFT_PLUGIN_URI . '/images/thumb_';
		}


		$select_layouts = array();


		if ( $isRapidBar == 'true' ) {
			$select_layouts = $all_layouts;
		} else {
			if ( isset( $all_layouts ) ) {
				foreach ( $all_layouts as $id => $array ) {
					foreach ( $array as $key => $value ) {
						if ( $key == 'inf_dashboard_form_orientation' ) {
							if ( $value == $formLocation ) {
								$select_layouts[ $id ] = $array;
							}
						}
					}
				}
			}

			//now filter based on img location
			if ( $formLocation != 'right' ) {
				if ( isset( $select_layouts ) ) {
					foreach ( $select_layouts as $id => $array ) {
						foreach ( $array as $key => $value ) {
							if ( $key == 'inf_dashboard_image_orientation' ) {
								if ( $value != $imgLocation ) {
									unset( $select_layouts[ $id ] );
								}
							}
						}
					}
				}
			}
		}

		$output = '';
		if ( isset( $select_layouts ) ) {
			$i = 0;

			$output .= '<div class="inf_infusionsoft_premade_grid' . $rapidBarClass . '">';

			foreach ( $select_layouts as $layout_id => $layout_options ) {
				$output .= sprintf( '
					<div class="inf_infusionsoft_premade_item%2$s inf_infusionsoft_premade_id_%1$s" data-layout="%1$s">
						<div class="inf_infusionsoft_premade_item_inner">
							<img src="%3$s" alt="" />
						</div>
					</div>', esc_attr( $layout_id ), 0 == $i ? ' inf_infusionsoft_layout_selected' : '',
					esc_attr( $imgpath . $layout_id . '.svg' ) );
				$i ++;
			}

			$output .= '</div>';
		}

		die( $output );
	}

	/**
	 * Gets the layouts data, converts it to json string and passes back to js script to fill the form with predefined
	 * values
	 */
	function get_premade_values() {
		$this->permissionsCheck();
		$isRapidBar = '';
		$isRedirect = '';
		if ( ! wp_verify_nonce( $_POST['infusionsoft_premade_nonce'], 'infusionsoft_premade' ) ) {
			die( - 1 );
		}
		$premade_data_json = str_replace( '\\', '', $_POST['premade_data_array'] );
		$premade_data      = json_decode( $premade_data_json, true );
		$layout_id         = $premade_data['id'];
		$isRapidBar        = sanitize_text_field( $_POST['isRapidBar'] );
		$isRedirect        = sanitize_text_field( $_POST['isRedirect'] );
		$layoutFolder      = $isRedirect == 'true' ? 'redirect' : 'form';
		if ( $isRapidBar == 'true' ) {
			require_once( INF_INFUSIONSOFT_PLUGIN_DIR . 'includes/ext/infusionsoft_infusionbar/layouts/' . $layoutFolder . '/premade-layouts.php' );
		} else {
			require_once( INF_INFUSIONSOFT_PLUGIN_DIR . 'includes/premade-layouts.php' );
		}
		if ( isset( $all_layouts[ $layout_id ] ) ) {
			$options_set = json_encode( $all_layouts[ $layout_id ] );
		}

		die( $options_set );
	}

	/**
	 * Generates output for the Stats tab
	 */
	function generate_stats_tab() {

		$this->permissionsCheck();
		$options_array = INF_Infusionsoft::get_infusionsoft_options();

		$output = sprintf( '
			<div class="inf_dashboard_stats_contents inf_dashboard_stats_ready">
				<div class="inf_dashboard_optins_stats inf_dashboard_lists_stats_graph">
					<div class="inf_infusionsoft_graph_header">
						<h3>%5$s</h3>
						<div class="inf_infusionsoft_graph_controls">
							<a href="#" class="inf_infusionsoft_graph_button inf_infusionsoft_active_button" data-period="30">%6$s</a>
							<a href="#" class="inf_infusionsoft_graph_button" data-period="12">%7$s</a>
							<select class="inf_infusionsoft_graph_select_list">%8$s</select>
						</div>
					</div>
					%4$s
				</div>
				<div class="inf_dashboard_optins_stats inf_dashboard_optins_all_table">
				<div class="stats-collapse"><h2 style="display:inline">View Opt-In Stats</h2><span class="dashicons dashicons-arrow-down-alt2 inf_dashboard_show_hide show-hide-icon"></span></div>
					<div class="inf_dashboard_optins_list">
						%3$s
					</div>
				</div>
				<div class="stats-collapse page-stats"><h2 style="display:inline">View Page Stats</h2><span class="dashicons dashicons-arrow-down-alt2 inf_dashboard_show_hide show-hide-icon"></span></div>

				%9$s
			</div>', '', '', $this->generate_optins_stats_table( 'conversion_rate', true ),
			$this->generate_lists_stats_graph( 30, 'day', '' ), // #4
			esc_html__( 'New sign ups', 'infusionsoft' ), esc_html__( 'Last 30 days', 'infusionsoft' ),
			esc_html__( 'Last 12 month', 'infusionsoft' ), $this->generate_all_lists_select(),
			$this->generate_pages_stats() // #9
		);

		return $output;
	}

	/**
	 * Generates the stats tab and passes it to jQuery
	 * @return string
	 */
	function reset_stats() {
		$this->permissionsCheck();
		if ( ! check_ajax_referer( 'infusionsoft_stats_nonce', 'infusionsoft_stats_nonce' ) ) {
			die( - 1 );
		}

		$output = $this->generate_stats_tab();
		update_option( 'inf_infusionsoft_stats_cache', $output );


		if ( ! wp_get_schedule( 'infusionsoft_stats_auto_refresh' ) ) {
			wp_schedule_event( time(), 'daily', 'infusionsoft_stats_auto_refresh' );
		}

		die( $output );
	}

	/**
	 * Update Stats and save it into WP DB
	 * @return void
	 */
	function perform_stats_refresh() {
		$fresh_stats = $output = $this->generate_stats_tab();
		update_option( 'inf_infusionsoft_stats_cache', $fresh_stats );
	}

	/**
	 * Removes all the stats data from DB
	 * @return void
	 */
	function clear_stats() {
		$this->permissionsCheck();
		if ( ! check_ajax_referer( 'infusionsoft_stats_nonce', 'infusionsoft_stats_nonce' ) ) {
			die( - 1 );
		}

		global $wpdb;

		$table_name = $wpdb->prefix . 'inf_infusionsoft_stats';

		// construct sql query to mark removed options as removed in stats DB
		$sql = "TRUNCATE TABLE $table_name";

		$wpdb->query( $sql );
	}

	/**
	 * Removes stats data from DB for single optin
	 * @return void
	 */
	function clear_stats_single_optin() {
		if ( ! check_ajax_referer( 'infusionsoft_stats_nonce', 'infusionsoft_stats_nonce' ) ) {
			die( - 1 );
		}
		delete_option( 'inf_infusionsoft_stats_cache' );
		global $wpdb;
		$optin_id   = sanitize_text_field( $_POST['optin_id'] );
		$table_name = $wpdb->prefix . 'inf_infusionsoft_stats';

		// construct sql query to mark removed options as removed in stats DB
		$sql = "DELETE FROM $table_name WHERE optin_id = '$optin_id'";

		$wpdb->query( $sql );
	}

	/**
	 * Generates the Lists menu for Lists stats graph
	 * @return string
	 */
	function generate_all_lists_select() {
		$options_array = INF_Infusionsoft::get_infusionsoft_options();
		$output        = sprintf( '<option value="all">%1$s</option>', __( 'All lists', 'infusionsoft' ) );

		if ( ! empty( $options_array['accounts'] ) ) {
			foreach ( $options_array['accounts'] as $service => $accounts ) {
				foreach ( $accounts as $name => $details ) {
					if ( ! empty( $details['lists'] ) ) {
						foreach ( $details['lists'] as $id => $list_data ) {
							$output .= sprintf( '<option value="%2$s">%1$s</option>',
								esc_html( $service . ' - ' . $list_data['name'] ), esc_attr( $service . '_' . $id ) );
						}
					}
				}
			}
		}

		return $output;
	}

	/**
	 * Generates the stats table with optins
	 * @return string
	 */
	function generate_optins_stats_table( $orderby = 'conversion_rate', $include_header = false ) {
		$this->permissionsCheck();
		$options_array     = INF_Infusionsoft::get_infusionsoft_options();
		$optins_count      = 0;
		$output            = '';
		$total_impressions = 0;
		$total_conversions = 0;

		foreach ( $options_array as $optin_id => $value ) {
			if ( 'accounts' !== $optin_id && 'db_version' !== $optin_id ) {
				if ( 0 === $optins_count ) {
					if ( true == $include_header ) {
						$output .= sprintf( '<ul>
								<li data-table="optins">
									<div class="inf_dashboard_table_name inf_dashboard_table_column inf_table_header">%1$s</div>
									<div class="inf_dashboard_table_impressions inf_dashboard_table_column inf_dashboard_icon inf_dashboard_sort_button" data-order_by="impressions">%2$s</div>
									<div class="inf_dashboard_table_conversions inf_dashboard_table_column inf_dashboard_icon inf_dashboard_sort_button" data-order_by="conversions">%3$s</div>
									<div class="inf_dashboard_table_rate inf_dashboard_table_column inf_dashboard_icon inf_dashboard_sort_button active_sorting" data-order_by="conversion_rate">%4$s</div>
									<div class="inf_dashboard_table_name inf_dashboard_table_column">%5$s</div>
									<div style="clear: both;"></div>
								</li>
							</ul>', __( 'Opt-In Form', 'infusionsoft' ), __( 'Views', 'infusionsoft' ),
							__( 'Opt-Ins', 'infusionsoft' ), __( 'Conversion Rate', 'infusionsoft' ),
							__( 'Clear Stats', 'infusionsoft' ) );
					}

					$output .= '<ul class="inf_dashboard_table_contents">';
				}

				$total_impressions += $impressions = $this->stats_count( $optin_id, 'imp' );
				$total_conversions += $conversions = $this->stats_count( $optin_id, 'con' );

				$unsorted_optins[ $optin_id ] = array(
					'name'            => $value['optin_name'],
					'impressions'     => $impressions,
					'conversions'     => $conversions,
					'conversion_rate' => $this->conversion_rate( $optin_id, $conversions, $impressions ),
					'type'            => $value['optin_type'],
					'status'          => $value['optin_status'],
					'child_of'        => $value['child_of'],
				);
				$optins_count ++;

			}
		}

		if ( ! empty( $unsorted_optins ) ) {
			$sorted_optins = $this->sort_array( $unsorted_optins, $orderby );

			foreach ( $sorted_optins as $id => $details ) {
				if ( '' !== $details['child_of'] ) {
					$status = $options_array[ $details['child_of'] ]['optin_status'];
				} else {
					$status = $details['status'];
				}

				$output .= sprintf( '<li class="inf_dashboard_optins_item inf_dashboard_parent_item">
						<div class="inf_dashboard_table_name inf_dashboard_table_column inf_dashboard_icon inf_dashboard_type_%5$s inf_dashboard_status_%6$s">%1$s</div>
						<div class="inf_dashboard_table_impressions inf_dashboard_table_column">%2$s</div>
						<div class="inf_dashboard_table_conversions inf_dashboard_table_column">%3$s</div>
						<div class="inf_dashboard_table_rate inf_dashboard_table_column">%4$s</div>
						<div class="inf_dashboard_table_column"><span data-optin_id="%7$s" class="dashicons dashicons-no clear_individual_stat"></span></div>
						<div style="clear: both;"></div>
					</li>', esc_html( $details['name'] ), esc_html( $details['impressions'] ),
					esc_html( $details['conversions'] ), esc_html( $details['conversion_rate'] ) . '%',
					esc_attr( $details['type'] ), esc_attr( $status ), esc_html( $id ) );
			}
		}

		if ( 0 < $optins_count ) {
			$output .= sprintf( '<li class="inf_dashboard_optins_item_bottom_row">
					<div class="inf_dashboard_table_name inf_dashboard_table_column"></div>
					<div class="inf_dashboard_table_impressions inf_dashboard_table_column">%1$s</div>
					<div class="inf_dashboard_table_conversions inf_dashboard_table_column">%2$s</div>
					<div class="inf_dashboard_table_rate inf_dashboard_table_column">%3$s</div>
				</li>', $this->get_compact_number( $total_impressions ),
				$this->get_compact_number( $total_conversions ),
				( 0 !== $total_impressions ) ? round( ( $total_conversions * 100 ) / $total_impressions,
						1 ) . '%' : '0%' );
			$output .= '</ul>';
		}

		return $output;
	}


	/**
	 * Changes the order of rows in array based on input parameters
	 * @return array
	 */
	function sort_array( $unsorted_array, $orderby, $order = SORT_DESC ) {
		$temp_array = array();
		foreach ( $unsorted_array as $ma ) {
			$temp_array[] = $ma[ $orderby ];
		}

		array_multisort( $temp_array, $order, $unsorted_array );

		return $unsorted_array;
	}

	/**
	 * Generates the highest converting pages table
	 * @return string
	 */
	function generate_pages_stats() {
		$all_pages_id = $this->get_all_stats_pages();
		$con_by_pages = array();
		$output       = '';

		if ( empty( $all_pages_id ) ) {
			return;
		}

		foreach ( $all_pages_id as $page ) {
			$con_by_pages[ $page['page_id'] ] = $this->get_unique_optins_by_page( $page['page_id'] );
		}

		if ( ! empty( $con_by_pages ) ) {
			foreach ( $con_by_pages as $page_id => $optins ) {
				$unique_optins = array();
				foreach ( $optins as $optin_id ) {
					if ( ! in_array( $optin_id, $unique_optins ) ) {
						$unique_optins[]             = $optin_id;
						$rate_by_pages[ $page_id ][] = array(
							$optin_id => $this->conversion_rate( $optin_id, '0', '0', $page_id ),
						);
					}
				}
			}

			$i = 0;

			foreach ( $rate_by_pages as $page_id => $rate ) {
				$page_rate   = 0;
				$rates_count = 0;
				$optins_data = array();
				$j           = 0;

				foreach ( $rate as $current_optin ) {
					foreach ( $current_optin as $optin_id => $current_rate ) {
						$page_rate = $page_rate + $current_rate;
						$rates_count ++;

						$optins_data[ $j ] = array(
							'optin_id'   => $optin_id,
							'optin_rate' => $current_rate,
						);

					}
					$j ++;
				}

				$average_rate                                = 0 != $rates_count ? round( $page_rate / $rates_count,
					1 ) : 0;
				$rate_by_pages_unsorted[ $i ]['page_id']     = $page_id;
				$rate_by_pages_unsorted[ $i ]['page_rate']   = $average_rate;
				$rate_by_pages_unsorted[ $i ]['optins_data'] = $this->sort_array( $optins_data, 'optin_rate',
					$order = SORT_DESC );

				$i ++;
			}

			$rate_by_pages_sorted = $this->sort_array( $rate_by_pages_unsorted, 'page_rate', $order = SORT_DESC );
			$output               = '';

			if ( ! empty( $rate_by_pages_sorted ) ) {
				$options_array  = INF_Infusionsoft::get_infusionsoft_options();
				$table_contents = '<ul>';

				for ( $i = 0; $i < 5; $i ++ ) {
					if ( ! empty( $rate_by_pages_sorted[ $i ] ) ) {
						$table_contents .= sprintf( '<li class="inf_table_page_row">
								<div class="inf_dashboard_table_name inf_dashboard_table_column inf_table_page_row">%1$s</div>
								<div class="inf_dashboard_table_pages_rate inf_dashboard_table_column">%2$s</div>
								<div style="clear: both;"></div>
							</li>', - 1 == $rate_by_pages_sorted[ $i ]['page_id'] ? __( 'Homepage',
							'infusionsoft' ) : esc_html( get_the_title( $rate_by_pages_sorted[ $i ]['page_id'] ) ),
							esc_html( $rate_by_pages_sorted[ $i ]['page_rate'] ) . '%' );
						foreach ( $rate_by_pages_sorted[ $i ]['optins_data'] as $optin_details ) {
							if ( isset( $options_array[ $optin_details['optin_id'] ]['child_of'] ) && '' !== $options_array[ $optin_details['optin_id'] ]['child_of'] ) {
								$status = $options_array[ $options_array[ $optin_details['optin_id'] ]['child_of'] ]['optin_status'];
							} else {
								$status = isset( $options_array[ $optin_details['optin_id'] ]['optin_status'] ) ? $options_array[ $optin_details['optin_id'] ]['optin_status'] : 'inactive';
							}

							$table_contents .= sprintf( '<li class="inf_table_optin_row inf_dashboard_optins_item">
									<div class="inf_dashboard_table_name inf_dashboard_table_column inf_dashboard_icon inf_dashboard_type_%3$s inf_dashboard_status_%4$s">%1$s</div>
									<div class="inf_dashboard_table_pages_rate inf_dashboard_table_column">%2$s</div>
									<div style="clear: both;"></div>
								</li>',
								( isset( $options_array[ $optin_details['optin_id'] ]['optin_name'] ) ) ? esc_html( $options_array[ $optin_details['optin_id'] ]['optin_name'] ) : '',
								esc_html( $optin_details['optin_rate'] ) . '%',
								( isset( $options_array[ $optin_details['optin_id'] ]['optin_type'] ) ) ? esc_attr( $options_array[ $optin_details['optin_id'] ]['optin_type'] ) : '',
								esc_attr( $status ) );
						}
					}
				}

				$table_contents .= '</ul>';

				$output = sprintf( '<div class="inf_dashboard_optins_stats inf_dashboard_pages_stats">
						<div class="inf_dashboard_optins_list">
							<ul>
								<li>
									<div class="inf_dashboard_table_name inf_dashboard_table_column inf_table_header">%1$s</div>
									<div class="inf_dashboard_table_pages_rate inf_dashboard_table_column inf_table_header">%2$s</div>
									<div style="clear: both;"></div>
								</li>
							</ul>
							%3$s
						</div>
					</div>', __( 'Highest converting pages', 'infusionsoft' ), __( 'Conversion rate', 'infusionsoft' ),
					$table_contents );
			}
		}

		return $output;
	}

	/**
	 * Calculates the conversion rate for the optin
	 * Can calculate rate for removed/existing optins and for particular pages.
	 * @return int
	 */
	function conversion_rate( $optin_id, $con_data = '0', $imp_data = '0', $page_id = 'all' ) {
		$conversion_rate = 0;

		$current_conversion = '0' === $con_data ? $this->stats_count( $optin_id, 'con', $page_id ) : $con_data;
		$current_impression = '0' === $imp_data ? $this->stats_count( $optin_id, 'imp', $page_id ) : $imp_data;

		if ( 0 < $current_impression ) {
			$conversion_rate = ( $current_conversion * 100 ) / $current_impression;
		}

		$conversion_rate_output = round( $conversion_rate, 1 );

		return $conversion_rate_output;
	}

	/**
	 * Calculates the conversions/impressions count for the optin
	 * Can calculate conversions for particular pages.
	 * @return int
	 */
	function stats_count( $optin_id, $type = 'imp', $page_id = 'all' ) {
		global $wpdb;

		$stats_count = 0;
		$optin_id    = 'all' == $optin_id ? '*' : $optin_id;

		$table_name = $wpdb->prefix . 'inf_infusionsoft_stats';

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
			// construct sql query to get all the conversions from db
			$sql      = "SELECT COUNT(*) FROM $table_name WHERE record_type = %s AND optin_id = %s";
			$sql_args = array(
				sanitize_text_field( $type ),
				sanitize_text_field( $optin_id )
			);

			if ( 'all' !== $page_id ) {
				$sql        .= " AND page_id = %s";
				$sql_args[] = sanitize_text_field( $page_id );
			}

			// cache the data from conversions table
			$stats_count = $wpdb->get_var( $wpdb->prepare( $sql, $sql_args ) );
		}

		return $stats_count;
	}

	function get_conversions() {
		global $wpdb;
		$conversions = array();

		$table_name = $wpdb->prefix . 'inf_infusionsoft_stats';

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
			// construct sql query to get all the conversions from db
			$sql = "SELECT * FROM $table_name WHERE record_type = 'con' ORDER BY record_date DESC";

			// cache the data from conversions table
			$conversions = $wpdb->get_results( $sql, ARRAY_A );
		}

		return $conversions;
	}

	function get_all_stats_pages() {
		$this->permissionsCheck();
		global $wpdb;

		$all_pages = array();

		$table_name = $wpdb->prefix . 'inf_infusionsoft_stats';

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
			// construct sql query to get all the conversions from db
			$sql = "SELECT DISTINCT page_id FROM $table_name";

			// cache the data from conversions table
			$all_pages = $wpdb->get_results( $sql, ARRAY_A );
		}

		return $all_pages;
	}

	function get_unique_optins_by_page( $page_id ) {
		global $wpdb;

		$all_optins       = array();
		$all_optins_final = array();

		$table_name = $wpdb->prefix . 'inf_infusionsoft_stats';

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
			// construct sql query to get all the conversions from db
			$sql      = "SELECT DISTINCT optin_id FROM $table_name where page_id = %s";
			$sql_args = array( sanitize_text_field( $page_id ) );

			// cache the data from conversions table
			$all_optins = $wpdb->get_results( $wpdb->prepare( $sql, $sql_args ), ARRAY_A );
		}
		if ( ! empty( $all_optins ) ) {
			foreach ( $all_optins as $optin ) {
				$all_optins_final[] = $optin['optin_id'];
			}
		}

		return $all_optins_final;
	}

	/**
	 * Calculates growth rate of the list. list_id should be provided in following format: <service>_<list_id>
	 * @return int
	 */
	function calculate_growth_rate( $list_id ) {
		$list_id = 'all' == $list_id ? '' : $list_id;

		$stats             = $this->generate_stats_by_period( 28, 'day', $this->get_conversions(), $list_id );
		$total_subscribers = $stats['total_subscribers_28'];
		$oldest_record     = - 1;

		for ( $i = 28; $i > 0; $i -- ) {
			if ( ! empty( $stats[ $i ] ) ) {
				if ( - 1 === $oldest_record ) {
					$oldest_record = $i;
				}
			}
		}

		if ( - 1 === $oldest_record ) {
			$growth_rate = 0;
		} else {
			$weeks_count = round( ( $oldest_record ) / 7, 0 );
			$weeks_count = 0 == $weeks_count ? 1 : $weeks_count;
			$growth_rate = round( $total_subscribers / $weeks_count, 0 );
		}

		return $growth_rate;
	}

	/**
	 * Generates output for the lists stats graph.
	 */
	function generate_lists_stats_graph( $period, $day_or_month, $list_id = '' ) {
		$this->permissionsCheck();
		$all_stats_rows = $this->get_conversions();
		$stats          = $this->generate_stats_by_period( $period, $day_or_month, $all_stats_rows, $list_id );
		$output         = $this->generate_stats_graph_output( $period, $day_or_month, $stats );

		return $output;
	}

	/**
	 * Generates stats array by specified period and using provided data.
	 * @return array
	 */
	function generate_stats_by_period( $period, $day_or_month, $input_data, $list_id = '' ) {
		$subscribers = array();

		$j                 = 0;
		$count_subscribers = 0;
		for ( $i = 1; $i <= $period; $i ++ ) {
			if ( array_key_exists( $j, $input_data ) ) {
				$count_subtotal = 1;

				while ( array_key_exists( $j, $input_data ) && strtotime( 'now' ) <= strtotime( sprintf( '+ %d %s', $i,
						'day' == $day_or_month ? 'days' : 'month' ), strtotime( $input_data[ $j ]['record_date'] ) ) ) {

					if ( '' === $list_id || ( '' !== $list_id && $list_id === $input_data[ $j ]['list_id'] ) ) {
						$subscribers[ $i ]['subtotal'] = $count_subtotal ++;

						$count_subscribers ++;

						if ( array_key_exists( $i, $subscribers ) && array_key_exists( $input_data[ $j ]['list_id'],
								$subscribers[ $i ] )
						) {
							$subscribers[ $i ][ $input_data[ $j ]['list_id'] ]['count'] ++;
						} else {
							$subscribers[ $i ][ $input_data[ $j ]['list_id'] ]['count'] = 1;
						}
					}

					$j ++;
				}
			}

			// Add total counts for each period into array
			if ( 'day' == $day_or_month ) {
				if ( $i == $period ) {
					$subscribers[ 'total_subscribers_' . $period ] = $count_subscribers;
				}
			} else {
				if ( $i == 12 ) {
					$subscribers['total_subscribers_12'] = $count_subscribers;
				}
			}
		}

		return $subscribers;
	}

	/**
	 * Generated the output for lists graph. Period and data array are required
	 * @return string
	 */
	function generate_stats_graph_output( $period, $day_or_month, $data ) {
		$this->permissionsCheck();
		$result     = '<div id="infusionsoft_line_chart" class="inf_dashboard_lists_stats_graph_container"></div>';
		$bars_count = 0;
		for ( $i = 1; $i <= $period; $i ++ ) {
			if ( array_key_exists( $i, $data ) ) {
				$bars_count ++;
			}
		}
		if ( 0 < $bars_count ) {
			$per_day = round( $data[ 'total_subscribers_' . $period ] / $bars_count, 0 );
		} else {
			$per_day = 0;
		}

		$totalSubscribers = isset( $data[ 'total_subscribers_' . $period ] ) ? $data[ 'total_subscribers_' . $period ] : '';

		$result .= sprintf( '<div class="inf_infusionsoft_overall">
				<span class="total_signups">%1$s | </span>
				<span class="signups_period">%2$s</span>
			</div>', sprintf( '%1$s %2$s', esc_html( $totalSubscribers ), esc_html__( 'New Signups', 'infusionsoft' ) ),
			sprintf( '%1$s %2$s %3$s', esc_html( $per_day ), esc_html__( 'Per', 'infusionsoft' ),
				'day' == $day_or_month ? esc_html__( 'Day', 'infusionsoft' ) : esc_html__( 'Month',
					'infusionsoft' ) ) );

		$result .= '</div>';

		return $result;
	}

	/**
	 * Generates the lists stats graph and passes it to jQuery
	 */
	function get_stats_graph_ajax() {
		$this->permissionsCheck();
		if ( ! check_ajax_referer( 'infusionsoft_stats_nonce', 'infusionsoft_stats_nonce' ) ) {
			die( - 1 );
		}
		$list_id = ! empty( $_POST['infusionsoft_list'] ) ? sanitize_text_field( $_POST['infusionsoft_list'] ) : '';
		$period  = ! empty( $_POST['infusionsoft_period'] ) ? sanitize_text_field( $_POST['infusionsoft_period'] ) : '';

		$day_or_month = '30' == $period ? 'day' : 'month';
		$list_id      = 'all' == $list_id ? '' : $list_id;

		$output = $this->generate_lists_stats_graph( $period, $day_or_month, $list_id );

		die( $output );
	}

	/**
	 * Generates the optins stats table and passes it to jQuery
	 */
	function refresh_optins_stats_table() {
		$this->permissionsCheck();
		if ( ! check_ajax_referer( 'infusionsoft_stats_nonce', 'infusionsoft_stats_nonce' ) ) {
			die( - 1 );
		}
		$orderby = ! empty( $_POST['infusionsoft_orderby'] ) ? sanitize_text_field( $_POST['infusionsoft_orderby'] ) : '';
		$table   = ! empty( $_POST['infusionsoft_stats_table'] ) ? sanitize_text_field( $_POST['infusionsoft_stats_table'] ) : '';

		if ( 'optins' === $table ) {
			$output = $this->generate_optins_stats_table( $orderby );
		}

		die( $output );
	}

	/**
	 * Converts number >1000 into compact numbers like 1k
	 */
	public static function get_compact_number( $full_number ) {
		if ( 1000000 <= $full_number ) {
			$full_number = floor( $full_number / 100000 ) / 10;
			$full_number .= 'Mil';
		} elseif ( 1000 < $full_number ) {
			$full_number = floor( $full_number / 100 ) / 10;
			$full_number .= 'k';
		}

		return $full_number;
	}

	/**
	 * Converts compact numbers like 1k into full numbers like 1000
	 */
	public static function get_full_number( $compact_number ) {
		if ( false !== strrpos( $compact_number, 'k' ) ) {
			$compact_number = floatval( str_replace( 'k', '', $compact_number ) ) * 1000;
		}
		if ( false !== strrpos( $compact_number, 'Mil' ) ) {
			$compact_number = floatval( str_replace( 'Mil', '', $compact_number ) ) * 1000000;
		}

		return $compact_number;
	}

	/**
	 * Generates the fields set for new account based on service and passes it to jQuery
	 */
	function generate_new_account_fields() {
		if ( ! check_ajax_referer( 'accounts_tab', 'accounts_tab_nonce' ) ) {
			die( - 1 );
		}

		$service = ! empty( $_POST['infusionsoft_service'] ) ? sanitize_text_field( $_POST['infusionsoft_service'] ) : '';

		if ( 'empty' == $service ) {
			echo '<ul class="inf_dashboard_new_account_fields"><li></li></ul>';
		} else {
			$form_fields = $this->generate_new_account_form( $service );

			printf( '<ul class="inf_dashboard_new_account_fields">
					<li class="select inf_dashboard_select_account">
						%3$s
						<button class="inf_dashboard_icon authorize_service new_account_tab" data-service="%2$s">%1$s</button>
						<span class="spinner"></span>
					</li>
				</ul>', esc_html__( 'Authorize', 'infusionsoft' ), esc_attr( $service ), $form_fields );
		}

		die();
	}

	/**
	 * Generates the fields set for account editing form based on service and account name and passes it to jQuery
	 */
	function generate_edit_account_page() {
		$this->permissionsCheck();
		if ( ! check_ajax_referer( 'accounts_tab', 'accounts_tab_nonce' ) ) {
			die( - 1 );
		}
		$edit_account = ! empty( $_POST['infusionsoft_edit_account'] ) ? sanitize_text_field( $_POST['infusionsoft_edit_account'] ) : '';
		$account_name = ! empty( $_POST['infusionsoft_account_name'] ) ? sanitize_text_field( $_POST['infusionsoft_account_name'] ) : '';
		$service      = ! empty( $_POST['infusionsoft_service'] ) ? sanitize_text_field( $_POST['infusionsoft_service'] ) : '';

		echo '<div id="inf_dashboard_edit_account_tab">';

		printf( '<div class="inf_dashboard_row inf_dashboard_new_account_row">
				<h1>%1$s</h1>
				<p>%2$s</p>
			</div>',
			( 'true' == $edit_account ) ? esc_html( $account_name ) : esc_html__( 'New Account Setup', 'infusionsoft' ),
			( 'true' == $edit_account ) ? esc_html__( 'You can view and re-authorize this account’s settings below',
				'infusionsoft' ) : esc_html__( 'Setup a new email marketing service account below', 'infusionsoft' ) );

		if ( 'true' == $edit_account ) {
			$form_fields = $this->generate_new_account_form( $service, $account_name, false );

			printf( '<div class="inf_dashboard_form inf_dashboard_row">
					<h2>%1$s</h2>
					<div style="clear:both;"></div>
					<ul class="inf_dashboard_new_account_fields inf_dashboard_edit_account_fields">
						<li class="select inf_dashboard_select_account">
							%2$s
							<button class="inf_dashboard_icon authorize_service new_account_tab" data-service="%7$s" data-account_name="%4$s">%3$s</button>
							<span class="spinner"></span>
						</li>
					</ul>
					<button class="inf_dashboard_icon save_account_tab" style="visibility: hidden;" data-service="%7$s">%6$s</button>
				</div>', esc_html( $service ), $form_fields, esc_html__( 'Re-Authorize', 'infusionsoft' ),
				esc_attr( $account_name ), '', esc_html__( 'Save &amp; Exit', 'infusionsoft' ), esc_attr( $service ) );
		} else {
			//dropdown is in alphabetical order, please add the new optin here in the right spot. Add to replacement values at the bottom to keep #'s in order
			//new account dropdown
			printf( '<div class="inf_dashboard_form inf_dashboard_row">
					<h2>New Account Settings</h2>
					<div style="clear:both;"></div>
					<ul class="inf_dashboard_new_account_fields">
      					<li class="select inf_dashboard_select_account">
         					<div class=“account_settings_fields inf_visible_settings" data-service="infusionsoft">
								  <input type="hidden" value="" id="name_infusionsoft">
							  <div class="inf_dashboard_account_row">
								 <label for="api_key_infusionsoft">API Key</label>
								 <input type="password" value="" id="api_key_infusionsoft"><span class="inf_dashboard_more_info inf_dashboard_icon ">
						   <span class="inf_dashboard_more_text"><a href="http://help.infusionsoft.com/userguides/get-started/tips-and-tricks/api-key" target="_blank">Click here for more information.</a></span>
						</span>
							  </div>
						  <div class="inf_dashboard_account_row">
							 <label for="client_id_infusionsoft">Application name</label>
							 <input type="text" value="" id="client_id_infusionsoft"><span class="inf_dashboard_more_info inf_dashboard_icon ">
					   <span class="inf_dashboard_more_text">The subdomain of your Infusionsoft application.</span>
						</span>
						  </div></div>
						 <button class="inf_dashboard_icon authorize_service new_account_tab" data-service="infusionsoft">Authorize</button>
						 <span class="spinner"></span>
					  </li>
				    </ul>
					<button class="inf_dashboard_icon save_account_tab" style="visibility: hidden;">Save &amp; Exit</button>
				</div>' );
		}

		echo '</div>';

		die();
	}

	/**
	 * Saves the account data during editing/creating account
	 */
	function save_account_tab() {
		$this->permissionsCheck();
		if ( ! wp_verify_nonce( $_POST['accounts_tab_nonce'], 'accounts_tab' ) ) {
			die( - 1 );
		}
		$service = ! empty( $_POST['infusionsoft_service'] ) ? sanitize_text_field( $_POST['infusionsoft_service'] ) : '';
		$name    = ! empty( $_POST['infusionsoft_account_name'] ) ? sanitize_text_field( $_POST['infusionsoft_account_name'] ) : '';

		$options_array = INF_Infusionsoft::get_infusionsoft_options();

		INF_Infusionsoft::updateEmailProviderForInactiveOptins( $options_array, $service, $name );

		if ( count( $options_array['accounts']['infusionsoft'] ) == 0 ) {

			if ( ! isset( $options_array['accounts'][ $service ][ $name ] ) ) {
				$this->update_account( $service, $name, array(
					'lists'         => array(),
					'is_authorized' => 'false',
				) );
			}

			die();

		} else {
			die();
		}
	}

	function updateEmailProviderForInactiveOptins( &$options_array, $service, $name ) {
		$emailProviderExists = isset( $options_array['accounts'][ $service ][ $name ]['api_key'] ) && isset( $options_array['accounts'][ $service ][ $name ]['client_id'] );

		if ( $emailProviderExists ) {
			$emailProviderChanged = false;
			foreach ( $options_array as $key => &$value ) {
				if ( isset( $value['email_provider'] ) && $value['email_provider'] == 'empty' ) {
					$value['email_provider'] = $name;
					$emailProviderChanged    = true;
				}
			}

			if ( $emailProviderChanged ) {
				INF_Infusionsoft::update_infusionsoft_options( $options_array );
			}
		}

	}

	/**
	 * Generates and displays the table with all accounts for Accounts tab
	 */
	function display_accounts_table() {
		$this->permissionsCheck();
		$options_array = INF_Infusionsoft::get_infusionsoft_options();

		echo '<div class="inf_dashboard_accounts_content">';
		if ( ! empty( $options_array['accounts'] ) ) {
			foreach ( $options_array['accounts'] as $service => $details ) {
				if ( ! empty( $details ) ) {
					$optins_count = 0;
					$output       = '';
					printf( '<div class="inf_dashboard_row inf_dashboard_accounts_title"></div>' );
					foreach ( $details as $account_name => $value ) {
						if ( 0 === $optins_count ) {
							$output .= sprintf( '<div class="inf_dashboard_optins_list">
									<ul>
										<li>
											<div class="inf_dashboard_table_acc_name inf_dashboard_table_column inf_dashboard_table_header">%1$s</div>
											<div class="inf_dashboard_table_subscribers inf_dashboard_table_column inf_dashboard_table_header">%2$s</div>
											<div class="inf_dashboard_table_growth_rate inf_dashboard_table_column inf_dashboard_table_header">%3$s</div>
											<div class="inf_dashboard_table_actions inf_dashboard_table_column"></div>
											<div style="clear: both;"></div>
										</li>', esc_html__( 'Account name', 'infusionsoft' ),
								esc_html__( 'Subscribers', 'infusionsoft' ),
								esc_html__( 'Growth rate', 'infusionsoft' ) );
						}
						$output .= sprintf( '<li class="inf_dashboard_optins_item inf_dashboard_optins_item" data-account_name="%1$s" data-service="%2$s">
								<div class="inf_dashboard_table_acc_name inf_dashboard_table_column">%3$s</div>
								<div class="inf_dashboard_table_subscribers inf_dashboard_table_column"></div>
								<div class="inf_dashboard_table_growth_rate inf_dashboard_table_column"></div>',
							esc_attr( $account_name ), esc_attr( $service ), esc_html( $account_name ) );

						$output .= sprintf( '
								<div class="inf_dashboard_table_actions inf_dashboard_table_column">
									<span class="inf_dashboard_icon_edit_account inf_optin_buttonoptin_button inf_dashboard_icon" title="%8$s" data-account_name="%1$s" data-service="%2$s"></span>
									<span class="inf_dashboard_icon_delete inf_optin_button inf_dashboard_icon" title="%4$s"><span class="inf_dashboard_confirmation">%5$s</span></span>
									%3$s
									<span class="inf_dashboard_icon_indicator_%7$s inf_optin_button inf_dashboard_icon" title="%6$s"></span>
								</div>
								<div style="clear: both;"></div>
							</li>', esc_attr( $account_name ), esc_attr( $service ), '',
							__( 'Remove account', 'infusionsoft' ),
							sprintf( '%1$s<span class="inf_dashboard_confirm_delete" data-optin_id="%4$s" data-remove_account="true">%2$s</span><span class="inf_dashboard_cancel_delete">%3$s</span>',
								esc_html__( 'Remove this account from list?', 'infusionsoft' ),
								esc_html__( 'Yes', 'infusionsoft' ), esc_html__( 'No', 'infusionsoft' ),
								esc_attr( $account_name ) ), //#5
							( isset( $value['is_authorized'] ) && 'true' == $value['is_authorized'] ) ? esc_html__( 'Authorized',
								'infusionsoft' ) : esc_html__( 'Not Authorized', 'infusionsoft' ),
							( isset( $value['is_authorized'] ) && 'true' == $value['is_authorized'] ) ? 'check' : 'dot',
							esc_html__( 'Edit account', 'infusionsoft' ) );

						$optins_count ++;
					}

					echo $output;
					echo '
						</ul>
					</div>';
				}
			}
		}
		echo '</div>';
	}

	/**
	 * Displays tables of Active and Inactive optins on homepage
	 */
	function display_home_tab_tables() {
		$this->permissionsCheck();
		$options_array = INF_Infusionsoft::get_infusionsoft_options();

		echo '<div class="inf_dashboard_home_tab_content">';

		$this->generate_optins_list( $options_array, 'active' );

		$this->generate_optins_list( $options_array, 'inactive' );

		echo '</div>';

	}

	/**
	 * Generates tables of Active and Inactive optins on homepage and passes it to jQuery
	 */
	function home_tab_tables() {
		$this->permissionsCheck();
		if ( ! wp_verify_nonce( $_POST['home_tab_nonce'], 'home_tab' ) ) {
			die( - 1 );
		}
		$this->display_home_tab_tables();
		die();
	}

	/**
	 * Generates accounts tables and passes it to jQuery
	 */
	function reset_accounts_table() {
		$this->permissionsCheck();
		if ( ! wp_verify_nonce( $_POST['accounts_tab_nonce'], 'accounts_tab' ) ) {
			die( - 1 );
		}
		$this->display_accounts_table();
		die();
	}

	/**
	 * Generates optins table for homepage. Can generate table for active or inactive optins
	 */
	function generate_optins_list( $options_array = array(), $status = 'active' ) {
		$optins_count      = 0;
		$output            = '';
		$total_impressions = 0;
		$total_conversions = 0;
		foreach ( $options_array as $optin_id => $value ) {
			if ( isset( $value['optin_status'] ) && $status === $value['optin_status'] && empty( $value['child_of'] ) ) {
				$child_row = '';

				if ( 0 === $optins_count ) {

					$output .= sprintf( '<div class="inf_dashboard_optins_list">
							<ul>
								<li>
									<div class="inf_dashboard_table_name inf_dashboard_table_column">%1$s</div>
									<div class="inf_dashboard_table_impressions inf_dashboard_table_column">%2$s</div>
									<div class="inf_dashboard_table_conversions inf_dashboard_table_column">%3$s</div>
									<div class="inf_dashboard_table_rate inf_dashboard_table_column">%4$s</div>
									<div class="inf_dashboard_table_actions inf_dashboard_table_column"></div>
									<div style="clear: both;"></div>
								</li>', esc_html__( 'Opt-In Form', 'infusionsoft' ),
						esc_html__( 'Views', 'infusionsoft' ), esc_html__( 'Opt-Ins', 'infusionsoft' ),
						esc_html__( 'Conversion Rate', 'infusionsoft' ) );
				}

				if ( ! empty( $value['child_optins'] ) && 'active' == $status ) {
					$optins_data = array();

					foreach ( $value['child_optins'] as $id ) {
						$total_impressions += $impressions = $this->stats_count( $id, 'imp' );
						$total_conversions += $conversions = $this->stats_count( $id, 'con' );

						$optins_data[] = array(
							'name'        => $options_array[ $id ]['optin_name'],
							'id'          => $id,
							'rate'        => $this->conversion_rate( $id, $conversions, $impressions ),
							'impressions' => $impressions,
							'conversions' => $conversions,
						);
					}

					$child_optins_data = $this->sort_array( $optins_data, 'rate', SORT_DESC );

					$child_row = '<ul class="inf_dashboard_child_row">';

					foreach ( $child_optins_data as $child_details ) {
						$child_row .= sprintf( '<li class="inf_dashboard_optins_item inf_dashboard_child_item" data-optin_id="%1$s">
								<div class="inf_dashboard_table_name inf_dashboard_table_column">%2$s</div>
								<div class="inf_dashboard_table_impressions inf_dashboard_table_column">%3$s</div>
								<div class="inf_dashboard_table_conversions inf_dashboard_table_column">%4$s</div>
								<div class="inf_dashboard_table_rate inf_dashboard_table_column">%5$s</div>
								<div class="inf_dashboard_table_actions inf_dashboard_table_column">
									<span class="inf_dashboard_icon_edit inf_optin_button inf_dashboard_icon" title="%8$s" data-parent_id="%9$s"><span class="spinner"></span></span>
									<span class="inf_dashboard_icon_delete inf_optin_button inf_dashboard_icon" title="%6$s"><span class="inf_dashboard_confirmation">%7$s</span></span>
								</div>
								<div style="clear: both;"></div>
							</li>', esc_attr( $child_details['id'] ), esc_html( $child_details['name'] ),
							esc_html( $child_details['impressions'] ), esc_html( $child_details['conversions'] ),
							esc_html( $child_details['rate'] . '%' ), // #5
							esc_attr__( 'Delete Optin', 'infusionsoft' ), sprintf( '%1$s<span class="inf_dashboard_confirm_delete" data-optin_id="%4$s" data-parent_id="%5$s">%2$s</span>
								<span class="inf_dashboard_cancel_delete">%3$s</span>',
								esc_html__( 'Delete this optin?', 'infusionsoft' ), esc_html__( 'Yes', 'infusionsoft' ),
								esc_html__( 'No', 'infusionsoft' ), esc_attr( $child_details['id'] ),
								esc_attr( $optin_id ) ), esc_attr__( 'Edit Optin', 'infusionsoft' ),
							esc_attr( $optin_id ) // #9
						);
					}

					$child_row .= sprintf( '<li class="inf_dashboard_add_variant inf_dashboard_optins_item">
							<a href="#" class="inf_dashboard_add_var_button">%1$s</a>
							<div class="child_buttons_right">
								<a href="#" class="inf_dashboard_start_test%5$s" data-parent_id="%4$s">%2$s</a>
								<a href="#" class="inf_dashboard_end_test" data-parent_id="%4$s">%3$s</a>
							</div>
						</li>', esc_html__( 'Add variant', 'infusionsoft' ),
						( isset( $value['test_status'] ) && 'active' == $value['test_status'] ) ? esc_html__( 'Pause test',
							'infusionsoft' ) : esc_html__( 'Start test', 'infusionsoft' ),
						esc_html__( 'End & pick winner', 'infusionsoft' ), esc_attr( $optin_id ),
						( isset( $value['test_status'] ) && 'active' == $value['test_status'] ) ? ' inf_dashboard_pause_test' : '' );

					$child_row .= '</ul>';
				}

				$total_impressions += $impressions = $this->stats_count( $optin_id, 'imp' );
				$total_conversions += $conversions = $this->stats_count( $optin_id, 'con' );
				$noAccount         = ( 'empty' == $value['email_provider'] ) ? ' inf_infusionsoft_no_account' : '';

				$output .= sprintf( '<li class="inf_dashboard_optins_item inf_dashboard_parent_item" data-optin_id="%1$s">
						<div class="inf_dashboard_table_name inf_dashboard_table_column inf_dashboard_icon inf_dashboard_type_%13$s">%2$s</div>
						<div class="inf_dashboard_table_impressions inf_dashboard_table_column">%3$s</div>
						<div class="inf_dashboard_table_conversions inf_dashboard_table_column">%4$s</div>
						<div class="inf_dashboard_table_rate inf_dashboard_table_column">%5$s</div>
						<div class="inf_dashboard_table_actions inf_dashboard_table_column">
							<span class="inf_dashboard_icon_edit inf_optin_button inf_dashboard_icon" title="%10$s"><span class="spinner"></span></span>
							<span class="inf_dashboard_icon_delete inf_optin_button inf_dashboard_icon" title="%9$s"><span class="inf_dashboard_confirmation">%12$s</span></span>
							<span class="inf_dashboard_icon_duplicate duplicate_id_%1$s inf_optin_button inf_dashboard_icon" title="%8$s"><span class="spinner"></span></span>
							<span class="inf_dashboard_icon_%11$s inf_dashboard_toggle_status inf_optin_button inf_dashboard_icon%16$s" data-toggle_to="%11$s" data-optin_id="%1$s" title="%7$s"><span class="spinner"></span></span>
							%14$s
							%6$s
						</div>
						<div style="clear: both;"></div>
						%15$s
					</li>', esc_attr( $optin_id ), esc_html( $value['optin_name'] ), esc_html( $impressions ),
					esc_html( $conversions ),
					esc_html( $this->conversion_rate( $optin_id, $conversions, $impressions ) . '%' ), // #5
					( 'locked' === $value['optin_type'] || 'inline' === $value['optin_type'] || $value['click_trigger'] == '1' ) ? sprintf( '<span class="inf_dashboard_icon_shortcode inf_optin_button inf_dashboard_icon" title="%1$s" data-type="%2$s" data-click_trigger="%3$s"></span>',
						esc_attr__( 'Generate shortcode', 'infusionsoft' ), esc_attr( $value['optin_type'] ),
						$value['click_trigger'] == '1' ? 'true' : 'false' ) : '',
					'active' === $status ? esc_html__( 'Make Inactive', 'infusionsoft' ) : esc_html__( 'Make Active',
						'infusionsoft' ), esc_attr__( 'Duplicate', 'infusionsoft' ),
					esc_attr__( 'Delete Optin', 'infusionsoft' ), esc_attr__( 'Edit Optin', 'infusionsoft' ), //#10
					'active' === $status ? 'inactive' : 'active', sprintf( '%1$s<span class="inf_dashboard_confirm_delete" data-optin_id="%4$s">%2$s</span>
						<span class="inf_dashboard_cancel_delete">%3$s</span>',
						esc_html__( 'Delete this optin?', 'infusionsoft' ), esc_html__( 'Yes', 'infusionsoft' ),
						esc_html__( 'No', 'infusionsoft' ), esc_attr( $optin_id ) ), esc_attr( $value['optin_type'] ),
					( 'active' === $status ) ? sprintf( '<span class="inf_dashboard_icon_abtest inf_optin_button inf_dashboard_icon%2$s" title="%1$s"></span>',
						esc_attr__( 'A/B Testing', 'infusionsoft' ),
						( '' != $child_row ) ? ' active_child_optins' : '' ) : '', $child_row, //#15
					$noAccount //#16
				);
				$optins_count ++;
			}
		}

		if ( 'active' === $status && 0 < $optins_count ) {
			$output .= sprintf( '<li class="inf_dashboard_optins_item_bottom_row">
					<div class="inf_dashboard_table_name inf_dashboard_table_column"></div>
					<div class="inf_dashboard_table_impressions inf_dashboard_table_column">%1$s</div>
					<div class="inf_dashboard_table_conversions inf_dashboard_table_column">%2$s</div>
					<div class="inf_dashboard_table_rate inf_dashboard_table_column">%3$s</div>
					<div class="inf_dashboard_table_actions inf_dashboard_table_column"></div>
				</li>', esc_html( $this->get_compact_number( $total_impressions ) ),
				esc_html( $this->get_compact_number( $total_conversions ) ),
				( 0 !== $total_impressions ) ? esc_html( round( ( $total_conversions * 100 ) / $total_impressions,
						1 ) . '%' ) : '0%' );
		}

		if ( 0 < $optins_count ) {
			if ( 'inactive' === $status ) {
				printf( '
					<div class="inf_dashboard_row">
						<h1 class="inactive-optins">%1$s</h1>
					</div>', esc_html__( 'Inactive Opt-In Forms', 'infusionsoft' ) );
			}

			echo $output . '</ul></div>';
		}
	}

	function add_admin_body_class( $classes ) {
		return "$classes inf_infusionsoft";
	}

	function register_scripts( $hook ) {

		wp_enqueue_style( 'rad-infusionsoft-menu-icon', INF_INFUSIONSOFT_PLUGIN_URI . '/css/infusionsoft-menu.css',
			array(), $this->plugin_version );

		if ( "toplevel_page_{$this->_options_pagename}" !== $hook ) {
			return;
		}

		add_filter( 'admin_body_class', array( $this, 'add_admin_body_class' ) );
		wp_enqueue_script( 'jquery-ui-dialog', '', '', '', true );
		wp_enqueue_script( ' jquery-ui-position', '', '', '', true );
		wp_enqueue_style( "wp-jquery-ui-dialog" );
		wp_enqueue_script( 'infusionsoft-chart-base', '//www.google.com/jsapi', array(), $this->plugin_version, true );
		wp_enqueue_script( 'infusionsoft-chart-js', INF_INFUSIONSOFT_PLUGIN_URI . '/js/chart.js', array(),
			$this->plugin_version, true );
		wp_enqueue_script( 'inf_infusionsoft-uniform-js', INF_INFUSIONSOFT_PLUGIN_URI . '/js/jquery.uniform.min.js',
			array( 'jquery' ), $this->plugin_version, true );
		wp_enqueue_style( 'rad-open-sans-700', "{$this->protocol}://fonts.googleapis.com/css?family=Open+Sans:700",
			array(), $this->plugin_version );
		wp_enqueue_style( 'rad-montserrat-700',
			"{$this->protocol}://fonts.googleapis.com/css?family=Montserrat:400,700", array(), $this->plugin_version );
		wp_enqueue_style( 'rad-infusionsoft-css', INF_INFUSIONSOFT_PLUGIN_URI . '/css/admin.css', array(),
			$this->plugin_version );
		wp_enqueue_style( 'inf_infusionsoft-preview-css', INF_INFUSIONSOFT_PLUGIN_URI . '/css/style.css', array(),
			$this->plugin_version );
		wp_enqueue_script( 'rad-infusionsoft-js', INF_INFUSIONSOFT_PLUGIN_URI . '/js/admin.js',
			array( 'jquery', 'infusionsoft-chart-base-init' ), $this->plugin_version, true );
		wp_localize_script( 'rad-infusionsoft-js', 'infusionsoft_settings', array(
			'infusionsoft_nonce'         => wp_create_nonce( 'infusionsoft_nonce' ),
			'ajaxurl'                    => admin_url( 'admin-ajax.php', $this->protocol ),
			'reset_options'              => wp_create_nonce( 'reset_options' ),
			'remove_option'              => wp_create_nonce( 'remove_option' ),
			'duplicate_option'           => wp_create_nonce( 'duplicate_option' ),
			'home_tab'                   => wp_create_nonce( 'home_tab' ),
			'toggle_status'              => wp_create_nonce( 'toggle_status' ),
			'optin_type_title'           => __( 'select optin type to begin', 'infusionsoft' ),
			'shortcode_text'             => __( 'Shortcode for this optin:', 'infusionsoft' ),
			'get_lists'                  => wp_create_nonce( 'get_lists' ),
			'add_account'                => wp_create_nonce( 'add_account' ),
			'accounts_tab'               => wp_create_nonce( 'accounts_tab' ),
			'retrieve_lists'             => wp_create_nonce( 'retrieve_lists' ),
			'ab_test'                    => wp_create_nonce( 'ab_test' ),
			'infusionsoft_stats'         => wp_create_nonce( 'infusionsoft_stats_nonce' ),
			'redirect_url'               => rawurlencode( admin_url( 'admin.php?page=' . $this->_options_pagename,
				$this->protocol ) ),
			'authorize_text'             => __( 'Authorize', 'infusionsoft' ),
			'reauthorize_text'           => __( 'Re-Authorize', 'infusionsoft' ),
			'no_account_name_text'       => __( 'Account name is not defined', 'infusionsoft' ),
			'ab_test_pause_text'         => __( 'Pause test', 'infusionsoft' ),
			'ab_test_start_text'         => __( 'Start test', 'infusionsoft' ),
			'infusionsoft_premade_nonce' => wp_create_nonce( 'infusionsoft_premade' ),
			'preview_nonce'              => wp_create_nonce( 'infusionsoft_preview' ),
			'no_account_text'            => __( 'You Have Not Added An Email List. Before your opt-in can be activated, you must first add an account and select an email list. You can save and exit, but the opt-in will remain inactive until an account is added.',
				'infusionsoft' ),
			'add_account_button'         => __( 'Add An Account', 'infusionsoft' ),
			'save_inactive_button'       => __( 'Save As Inactive', 'infusionsoft' ),
			'cannot_activate_text'       => __( 'You Have Not Added An Account. Before your opt-in can be activated, you must first add an account.',
				'infusionsoft' ),
			'save_settings'              => wp_create_nonce( 'save_settings' ),
			'chart_stats'                => $this->get_conversions(),
		) );
		wp_enqueue_script( 'infusionsoft-chart-base-init', INF_INFUSIONSOFT_PLUGIN_URI . '/js/googlechart.js', array(),
			$this->plugin_version, true );
	}

	/**
	 * Generates unique ID for new set of options
	 * @return string or int
	 */
	function generate_optin_id( $full_id = true ) {

		$options_array = INF_Infusionsoft::get_infusionsoft_options();
		$form_id       = (int) 0;

		if ( ! empty( $options_array ) ) {
			foreach ( $options_array as $key => $value ) {
				$keys_array[] = (int) str_replace( 'optin_', '', $key );
			}

			$form_id = max( $keys_array ) + 1;
		}

		$result = true === $full_id ? (string) 'optin_' . $form_id : (int) $form_id;

		return $result;

	}

	/**
	 * Generates options page for specific optin ID
	 * @return string
	 */
	function infusionsoft_reset_options_page() {
		$this->permissionsCheck();
		if ( ! wp_verify_nonce( $_POST['reset_options_nonce'], 'reset_options' ) ) {
			die( - 1 );
		}

		$optin_id           = ! empty( $_POST['reset_optin_id'] ) ? sanitize_text_field( $_POST['reset_optin_id'] ) : $this->generate_optin_id();
		$additional_options = '';

		INF_Infusionsoft::generate_options_page( $optin_id );

		die();
	}

	/**
	 * Handles "Duplicate" button action
	 * @return string
	 */
	function duplicate_optin() {
		if ( ! wp_verify_nonce( $_POST['duplicate_option_nonce'], 'duplicate_option' ) ) {
			die( - 1 );
		}
		$duplicate_optin_id   = ! empty( $_POST['duplicate_optin_id'] ) ? sanitize_text_field( $_POST['duplicate_optin_id'] ) : '';
		$duplicate_optin_type = ! empty( $_POST['duplicate_optin_type'] ) ? sanitize_text_field( $_POST['duplicate_optin_type'] ) : '';

		$this->perform_option_duplicate( $duplicate_optin_id, $duplicate_optin_type, false );

		die();
	}

	/**
	 * Handles "Add Variant" button action
	 * @return string
	 */
	function add_variant() {
		$this->permissionsCheck();
		if ( ! wp_verify_nonce( $_POST['duplicate_option_nonce'], 'duplicate_option' ) ) {
			die( - 1 );
		}
		$duplicate_optin_id = ! empty( $_POST['duplicate_optin_id'] ) ? sanitize_text_field( $_POST['duplicate_optin_id'] ) : '';

		$variant_id = $this->perform_option_duplicate( $duplicate_optin_id, '', true );

		die( $variant_id );
	}

	/**
	 * Toggles testing status
	 * @return void
	 */
	function ab_test_actions() {
		$this->permissionsCheck();
		if ( ! wp_verify_nonce( $_POST['ab_test_nonce'], 'ab_test' ) ) {
			die( - 1 );
		}
		$parent_id                        = ! empty( $_POST['parent_id'] ) ? sanitize_text_field( $_POST['parent_id'] ) : '';
		$action                           = ! empty( $_POST['test_action'] ) ? sanitize_text_field( $_POST['test_action'] ) : '';
		$options_array                    = INF_Infusionsoft::get_infusionsoft_options();
		$update_test_status[ $parent_id ] = $options_array[ $parent_id ];

		switch ( $action ) {
			case 'start' :
				$update_test_status[ $parent_id ]['test_status'] = 'active';
				$result                                          = 'ok';
				break;
			case 'pause' :
				$update_test_status[ $parent_id ]['test_status'] = 'inactive';
				$result                                          = 'ok';
				break;

			case 'end' :
				$result = $this->generate_end_test_modal( $parent_id );
				break;
		}

		INF_Infusionsoft::update_option( $update_test_status );

		die( $result );
	}

	/**
	 * Generates modal window for the pick winner option
	 * @return string
	 */
	function generate_end_test_modal( $parent_id ) {
		$this->permissionsCheck();
		$options_array = INF_Infusionsoft::get_infusionsoft_options();
		$test_optins   = $options_array[ $parent_id ]['child_optins'];
		$test_optins[] = $parent_id;
		$output        = '';

		if ( ! empty( $test_optins ) ) {
			foreach ( $test_optins as $id ) {
				$optins_data[] = array(
					'name' => $options_array[ $id ]['optin_name'],
					'id'   => $id,
					'rate' => $this->conversion_rate( $id ),
				);
			}

			$optins_data = $this->sort_array( $optins_data, 'rate', SORT_DESC );

			$table = sprintf( '<div class="end_test_table">
					<ul data-optins_set="%3$s" data-parent_id="%4$s">
						<li class="inf_test_table_header">
							<div class="inf_dashboard_table_column">%1$s</div>
							<div class="inf_dashboard_table_column inf_test_conversion">%2$s</div>
						</li>', esc_html__( 'Optin name', 'infusionsoft' ),
				esc_html__( 'Conversion rate', 'infusionsoft' ), esc_attr( implode( '#', $test_optins ) ),
				esc_attr( $parent_id ) );

			foreach ( $optins_data as $single ) {
				$table .= sprintf( '<li class="inf_dashboard_content_row" data-optin_id="%1$s">
						<div class="inf_dashboard_table_column">%2$s</div>
						<div class="inf_dashboard_table_column et_test_conversion">%3$s</div>
					</li>', esc_attr( $single['id'] ), esc_html( $single['name'] ), esc_html( $single['rate'] . '%' ) );
			}

			$table .= '</ul></div>';

			$output = sprintf( '<div class="inf_dashboard_networks_modal inf_dashboard_end_test">
					<div class="inf_dashboard_inner_container">
						<div class="inf_dashboard_modal_header">
							<span class="modal_title">%1$s</span>
							<span class="inf_dashboard_close"></span>
						</div>
						<div class="dashboard_icons_container">
							%3$s
						</div>
						<div class="inf_dashboard_modal_footer">
							<a href="#" class="inf_dashboard_ok inf_dashboard_warning_button">%2$s</a>
						</div>
					</div>
				</div>', esc_html__( 'Choose an optin', 'infusionsoft' ), esc_html__( 'cancel', 'infusionsoft' ),
				$table );
		}

		return $output;
	}

	/**
	 * Handles "Pick winner" function. Replaces the content of parent optin with the content of "winning" optin.
	 * Updates options and stats accordingly.
	 * @return void
	 */
	function pick_winner_optin() {
		$this->permissionsCheck();
		if ( ! wp_verify_nonce( $_POST['remove_option_nonce'], 'remove_option' ) ) {
			die( - 1 );
		}

		$winner_id  = ! empty( $_POST['winner_id'] ) ? sanitize_text_field( $_POST['winner_id'] ) : '';
		$optins_set = ! empty( $_POST['optins_set'] ) ? sanitize_text_field( $_POST['optins_set'] ) : '';
		$parent_id  = ! empty( $_POST['parent_id'] ) ? sanitize_text_field( $_POST['parent_id'] ) : '';

		$options_array = INF_Infusionsoft::get_infusionsoft_options();
		$temp_array    = $options_array[ $winner_id ];

		$temp_array['test_status']     = 'inactive';
		$temp_array['child_optins']    = array();
		$temp_array['child_of']        = '';
		$temp_array['next_optin']      = '-1';
		$temp_array['display_on']      = $options_array[ $parent_id ]['display_on'];
		$temp_array['post_types']      = $options_array[ $parent_id ]['post_types'];
		$temp_array['post_categories'] = $options_array[ $parent_id ]['post_categories'];
		$temp_array['pages_exclude']   = $options_array[ $parent_id ]['pages_exclude'];
		$temp_array['pages_include']   = $options_array[ $parent_id ]['pages_include'];
		$temp_array['posts_exclude']   = $options_array[ $parent_id ]['posts_exclude'];
		$temp_array['posts_include']   = $options_array[ $parent_id ]['posts_include'];
		$temp_array['email_provider']  = $options_array[ $parent_id ]['email_provider'];
		$temp_array['account_name']    = $options_array[ $parent_id ]['account_name'];
		$temp_array['email_list']      = $options_array[ $parent_id ]['email_list'];
		$temp_array['custom_html']     = $options_array[ $parent_id ]['custom_html'];

		$updated_array[ $parent_id ] = $temp_array;

		if ( $parent_id != $winner_id ) {
			$this->update_stats_for_winner( $parent_id, $winner_id );
		}

		$optins_set = explode( '#', $optins_set );
		foreach ( $optins_set as $optin_id ) {
			if ( $parent_id != $optin_id ) {
				$this->perform_optin_removal( $optin_id, false, '', '', false );
			}
		}

		INF_Infusionsoft::update_option( $updated_array );
	}

	/**
	 * Updates stats table when A/B testing finished winner optin selected
	 * @return void
	 */
	function update_stats_for_winner( $optin_id, $winner_id ) {
		$this->permissionsCheck();
		global $wpdb;

		$table_name = $wpdb->prefix . 'inf_infusionsoft_stats';

		$this->remove_optin_from_db( $optin_id );

		$sql = "UPDATE $table_name SET optin_id = %s WHERE optin_id = %s AND removed_flag <> 1";

		$sql_args = array(
			$optin_id,
			$winner_id
		);

		$wpdb->query( $wpdb->prepare( $sql, $sql_args ) );
	}

	/**
	 * Performs duplicating of optin. Can duplicate parent optin as well as child optin based on $is_child parameter
	 * @return string
	 */
	function perform_option_duplicate( $duplicate_optin_id, $duplicate_optin_type = '', $is_child = false ) {
		$this->permissionsCheck();
		$new_optin_id = $this->generate_optin_id();
		$suffix       = true == $is_child ? '_child' : '_copy';

		if ( '' !== $duplicate_optin_id ) {
			$options_array                               = INF_Infusionsoft::get_infusionsoft_options();
			$new_option[ $new_optin_id ]                 = $options_array[ $duplicate_optin_id ];
			$new_option[ $new_optin_id ]['optin_name']   = $new_option[ $new_optin_id ]['optin_name'] . $suffix;
			$new_option[ $new_optin_id ]['optin_status'] = 'active';

			if ( true == $is_child ) {
				$new_option[ $new_optin_id ]['child_of'] = $duplicate_optin_id;
				$updated_optin[ $duplicate_optin_id ]    = $options_array[ $duplicate_optin_id ];
				unset( $new_option[ $new_optin_id ]['child_optins'] );
				$updated_optin[ $duplicate_optin_id ]['child_optins'] = isset( $options_array[ $duplicate_optin_id ]['child_optins'] ) ? array_merge( $options_array[ $duplicate_optin_id ]['child_optins'],
					array( $new_optin_id ) ) : array( $new_optin_id );
				INF_Infusionsoft::update_option( $updated_optin );
			} else {
				$new_option[ $new_optin_id ]['optin_type'] = $duplicate_optin_type;
				unset( $new_option[ $new_optin_id ]['child_optins'] );
			}

			if ( 'breakout_edge' === $new_option[ $new_optin_id ]['edge_style'] && 'pop_up' !== $duplicate_optin_type ) {
				$new_option[ $new_optin_id ]['edge_style'] = 'basic_edge';
			}

			if ( ! ( 'flyin' === $duplicate_optin_type || 'pop_up' === $duplicate_optin_type ) ) {
				unset( $new_option[ $new_optin_id ]['display_on'] );
			}

			INF_Infusionsoft::update_option( $new_option );

			return $new_optin_id;
		}
	}

	function get_optin_uniqid( $optin_id ) {
		$options_array = INF_Infusionsoft::get_infusionsoft_options();
		$optin_uniqid  = '';

		if ( isset( $options_array[ $optin_id ] ) ) {
			if ( isset( $options_array[ $optin_id ]['optin_id'] ) ) {
				$optin_uniqid = $options_array[ $optin_id ]['optin_id'];
			}
		}

		return $optin_uniqid;
	}

	/**
	 * Handles optin/account removal function called via jQuery
	 */
	function remove_optin() {
		$this->permissionsCheck();
		if ( ! wp_verify_nonce( $_POST['remove_option_nonce'], 'remove_option' ) ) {
			die( - 1 );
		}

		$optin_id     = ! empty( $_POST['remove_optin_id'] ) ? sanitize_text_field( $_POST['remove_optin_id'] ) : '';
		$is_account   = ! empty( $_POST['is_account'] ) ? sanitize_text_field( $_POST['is_account'] ) : '';
		$service      = ! empty( $_POST['service'] ) ? sanitize_text_field( $_POST['service'] ) : '';
		$parent_id    = ! empty( $_POST['parent_id'] ) ? sanitize_text_field( $_POST['parent_id'] ) : '';
		$optin_uniqid = str_replace( ".", "", $this->get_optin_uniqid( $optin_id ) );

		$this->perform_optin_removal( $optin_id, $is_account, $service, $parent_id );
		$this->delete_optin( $optin_uniqid );

		die();
	}

	/**
	 * Performs removal of optin or account. Can remove parent optin, child optin or account
	 * @return void
	 */
	function perform_optin_removal(
		$optin_id,
		$is_account = false,
		$service = '',
		$parent_id = '',
		$remove_child = true
	) {
		$this->permissionsCheck();
		$options_array = INF_Infusionsoft::get_infusionsoft_options();

		if ( '' !== $optin_id ) {
			if ( 'true' == $is_account ) {
				if ( '' !== $service ) {
					if ( isset( $options_array['accounts'][ $service ][ $optin_id ] ) ) {
						unset( $options_array['accounts'][ $service ][ $optin_id ] );

						foreach ( $options_array as $id => $details ) {
							if ( 'accounts' !== $id ) {
								if ( isset( $details['account_name'] ) && $optin_id == $details['account_name'] ) {
									$options_array[ $id ]['email_provider'] = 'empty';
									$options_array[ $id ]['account_name']   = 'empty';
									$options_array[ $id ]['email_list']     = 'empty';
									$options_array[ $id ]['optin_status']   = 'inactive';
								}
							}
						}

						INF_Infusionsoft::update_option( $options_array );
					}
				}
			} else {
				if ( '' != $parent_id ) {
					$updated_array[ $parent_id ] = $options_array[ $parent_id ];
					$new_child_optins            = array();

					foreach ( $updated_array[ $parent_id ]['child_optins'] as $child ) {
						if ( $child != $optin_id ) {
							$new_child_optins[] = $child;
						}
					}

					$updated_array[ $parent_id ]['child_optins'] = $new_child_optins;

					// change test status to 'inactive' if there is no child options after removal.
					if ( empty( $new_child_optins ) ) {
						$updated_array[ $parent_id ]['test_status'] = 'inactive';
					}

					INF_Infusionsoft::update_option( $updated_array );
				} else {
					if ( ! empty( $options_array[ $optin_id ]['child_optins'] ) && true == $remove_child ) {
						foreach ( $options_array[ $optin_id ]['child_optins'] as $single_optin ) {
							INF_Infusionsoft::remove_option( $single_optin );
							$this->remove_optin_from_db( $single_optin );
						}
					}
				}

				INF_Infusionsoft::remove_option( $optin_id );
				$this->remove_optin_from_db( $optin_id );
			}
		}
	}

	/**
	 * Remove the optin data from stats tabel.
	 */
	function remove_optin_from_db( $optin_id ) {
		if ( '' !== $optin_id ) {
			global $wpdb;

			$table_name = $wpdb->prefix . 'inf_infusionsoft_stats';

			// construct sql query to mark removed options as removed in stats DB
			$sql = "DELETE FROM $table_name WHERE optin_id = %s";

			$sql_args = array(
				$optin_id,
			);

			$wpdb->query( $wpdb->prepare( $sql, $sql_args ) );
		}
	}

	/**
	 * Toggles status of optin from active to inactive and vice versa
	 * @return void
	 */
	function toggle_optin_status() {
		$this->permissionsCheck();
		if ( ! wp_verify_nonce( $_POST['toggle_status_nonce'], 'toggle_status' ) ) {
			die( - 1 );
		}
		$optin_id  = ! empty( $_POST['status_optin_id'] ) ? sanitize_text_field( $_POST['status_optin_id'] ) : '';
		$toggle_to = ! empty( $_POST['status_new'] ) ? sanitize_text_field( $_POST['status_new'] ) : '';

		if ( '' !== $optin_id ) {
			$options_array                              = INF_Infusionsoft::get_infusionsoft_options();
			$update_option[ $optin_id ]                 = $options_array[ $optin_id ];
			$update_option[ $optin_id ]['optin_status'] = 'active' === $toggle_to ? 'active' : 'inactive';

			INF_Infusionsoft::update_option( $update_option );
		}

		die();
	}

	/**
	 * Adds new account into DB.
	 * @return void
	 */
	function add_new_account() {
		if ( ! wp_verify_nonce( $_POST['add_account_nonce'], 'add_account' ) ) {
			die( - 1 );
		}

		$service     = ! empty( $_POST['infusionsoft_service'] ) ? sanitize_text_field( $_POST['infusionsoft_service'] ) : '';
		$name        = ! empty( $_POST['infusionsoft_account_name'] ) ? sanitize_text_field( strtolower( $_POST['infusionsoft_account_name'] ) ) : '';
		$new_account = array();

		if ( '' !== $service && '' !== $name ) {
			$options_array                                = INF_Infusionsoft::get_infusionsoft_options();
			$new_account['accounts']                      = isset( $options_array['accounts'] ) ? $options_array['accounts'] : array();
			$new_account['accounts'][ $service ][ $name ] = array();
			INF_Infusionsoft::update_option( $new_account );
		}
	}

	/**
	 * Updates the account details in DB.
	 * @return void
	 */
	function update_account( $service, $name, $data_array = array() ) {
		if ( '' !== $service && '' !== $name ) {
			$name                                         = str_replace( array( '"', "'" ), '', stripslashes( $name ) );
			$options_array                                = INF_Infusionsoft::get_infusionsoft_options();
			$new_account['accounts']                      = isset( $options_array['accounts'] ) ? $options_array['accounts'] : array();
			$new_account['accounts'][ $service ][ $name ] = isset( $new_account['accounts'][ $service ][ $name ] ) ? array_merge( $new_account['accounts'][ $service ][ $name ],
				$data_array ) : $data_array;

			INF_Infusionsoft::update_option( $new_account );
		}
	}

	/**
	 * Handles accounts authorization. Basically just executes specific function based on service and returns error
	 * message. Supports authorization of new accounts and re-authorization of existing accounts.
	 * @return string
	 */
	function authorize_account() {
		$this->permissionsCheck();
		if ( ! wp_verify_nonce( $_POST['get_lists_nonce'], 'get_lists' ) ) {
			die( - 1 );
		}
		$service         = ! empty( $_POST['infusionsoft_upd_service'] ) ? sanitize_text_field( $_POST['infusionsoft_upd_service'] ) : '';
		$name            = ! empty( $_POST['infusionsoft_upd_name'] ) ? sanitize_text_field( strtolower( $_POST['infusionsoft_upd_name'] ) ) : '';
		$update_existing = ! empty( $_POST['infusionsoft_account_exists'] ) ? sanitize_text_field( $_POST['infusionsoft_account_exists'] ) : '';

		//include class to get functions below
		if ( ! class_exists( 'infusionsoft_' . $service ) ) {
			require_once( INF_INFUSIONSOFT_PLUGIN_DIR . 'includes/classes/integrations/class.infusionsoft-' . $service . '.php' );
		}

		if ( 'true' == $update_existing ) {
			$options_array = INF_Infusionsoft::get_infusionsoft_options();
			$accounts_data = $options_array['accounts'];

			$api_key = ! empty( $accounts_data[ $service ][ $name ]['api_key'] ) ? $accounts_data[ $service ][ $name ]['api_key'] : '';
			$app_id  = ! empty( $accounts_data[ $service ][ $name ]['client_id'] ) ? strtolower( $accounts_data[ $service ][ $name ]['client_id'] ) : '';
		} else {
			$api_key = ! empty( $_POST['infusionsoft_api_key'] ) ? sanitize_text_field( $_POST['infusionsoft_api_key'] ) : '';
			$app_id  = ! empty( $_POST['infusionsoft_client_id'] ) ? sanitize_text_field( strtolower( $_POST['infusionsoft_client_id'] ) ) : '';
		}

		$error_message = '';

		if ( 'infusionsoft' == $service ) {
			$infusionsoft  = new infusionsoft_infusionsoft();
			$error_message = $infusionsoft->testConnection( $app_id, $api_key );
		}

		$result = 'success' == $error_message ? $error_message : __( 'Unable to connect: Please check your API Key and Application name.',
			'infusionsoft' );

		error_log( 'Infusionsoft API Connection Error: ' . $error_message );

		if ( $result == 'success' ) {
			$options_array = self::get_infusionsoft_options();
			if ( sizeof( $options_array['accounts']['infusionsoft'] ) == 0 ) {
				$this->update_account( 'infusionsoft', sanitize_text_field( $name ), array(
					'api_key'       => sanitize_text_field( $api_key ),
					'client_id'     => sanitize_text_field( $app_id ),
					'is_authorized' => 'true',
				) );
			}
		}

		die( $result );
	}

	/**
	 * Handles subscribe action and sends the success or error message to jQuery.
	 */
	function subscribe() {
		$this->permissionsCheck();
		if ( ! wp_verify_nonce( $_POST['subscribe_nonce'], 'subscribe' ) ) {
			die( - 1 );
		}

		$subscribe_data_json  = str_replace( '\\', '', $_POST['subscribe_data_array'] );
		$subscribe_data_array = json_decode( $subscribe_data_json, true );

		$service      = sanitize_text_field( $subscribe_data_array['service'] );
		$account_name = sanitize_text_field( $subscribe_data_array['account_name'] );
		$name         = isset( $subscribe_data_array['name'] ) ? sanitize_text_field( $subscribe_data_array['name'] ) : '';
		$last_name    = isset( $subscribe_data_array['last_name'] ) ? sanitize_text_field( $subscribe_data_array['last_name'] ) : '';
		$email        = sanitize_email( $subscribe_data_array['email'] );
		$list_id      = sanitize_text_field( $subscribe_data_array['list_id'] );
		$page_id      = sanitize_text_field( $subscribe_data_array['page_id'] );
		$optin_id     = sanitize_text_field( $subscribe_data_array['optin_id'] );

		//include class to get functions below
		if ( ! class_exists( 'infusionsoft_' . $service ) ) {
			require_once( INF_INFUSIONSOFT_PLUGIN_DIR . 'includes/classes/integrations/class.infusionsoft-' . $service . '.php' );
		}

		if ( is_email( $email ) ) {
			$options_array = INF_Infusionsoft::get_infusionsoft_options();
			$optin_uniqid  = $this->get_optin_uniqid( $optin_id );


			if ( 'infusionsoft' == $service ) {
				$api_key       = $options_array['accounts'][ $service ][ $account_name ]['api_key'];
				$app_id        = $options_array['accounts'][ $service ][ $account_name ]['client_id'];
				$infusionsoft  = new infusionsoft_infusionsoft();
				$error_message = $infusionsoft->subscribe_infusionsoft( $api_key, $app_id, $email, $name, $last_name,
					$optin_uniqid );
			}
		} else {
			$error_message = __( 'Invalid email', 'infusionsoft' );
		}

		if ( 'success' == $error_message ) {
			INF_Infusionsoft::add_stats_record( 'con', $optin_id, $page_id, $service . '_' . $list_id );
			$result = json_encode( array( 'success' => $error_message ) );
		} else {
			$result = json_encode( array( 'error' => $error_message ) );
		}

		die( $result );
	}

	/**
	 * Converts xml data to array
	 * @return array
	 */
	function xml_to_array( $xml_data ) {
		$xml   = simplexml_load_string( $xml_data );
		$json  = json_encode( $xml );
		$array = json_decode( $json, true );

		return $array;
	}

	/**
	 * Generates output for the "Form Integration" options.
	 * @return string
	 */
	function generate_accounts_list() {
		$this->permissionsCheck();
		if ( ! wp_verify_nonce( $_POST['retrieve_lists_nonce'], 'retrieve_lists' ) ) {
			die( - 1 );
		}
		$service     = ! empty( $_POST['infusionsoft_service'] ) ? sanitize_text_field( $_POST['infusionsoft_service'] ) : '';
		$optin_id    = ! empty( $_POST['infusionsoft_optin_id'] ) ? sanitize_text_field( $_POST['infusionsoft_optin_id'] ) : '';
		$new_account = ! empty( $_POST['infusionsoft_add_account'] ) ? sanitize_text_field( $_POST['infusionsoft_add_account'] ) : '';

		$options_array   = INF_Infusionsoft::get_infusionsoft_options();
		$current_account = isset( $options_array[ $optin_id ]['account_name'] ) ? $options_array[ $optin_id ]['account_name'] : 'empty';

		$available_accounts = array();

		if ( isset( $options_array['accounts'] ) ) {
			if ( isset( $options_array['accounts'][ $service ] ) ) {
				foreach ( $options_array['accounts'][ $service ] as $account_name => $details ) {
					$available_accounts[] = $account_name;
				}
			}
		}

		if ( ! empty( $available_accounts ) && '' === $new_account ) {
			printf( '<li class="select inf_dashboard_select_account">
					<p>%1$s</p>
					<select name="inf_dashboard[account_name]" data-service="%3$s">
						<option value="empty"%2$s</option>', __( 'Select Account', 'infusionsoft' ),
				__( 'Select One...', 'infusionsoft' ), esc_attr( $service ) );

			if ( ! empty( $available_accounts ) ) {
				printf( '<option value="%1$s" selected="selected">%2$s</option>', esc_attr( $available_accounts[0] ),
					esc_html( $available_accounts[0] ) );
			}

			printf( '</select></li>' );
		} else {
			$form_fields = $this->generate_new_account_form( $service, '', false );

			printf( '<li class="select inf_dashboard_select_account inf_dashboard_new_account ">
					%3$s
					<button class="inf_dashboard_icon authorize_service %4$s" data-service="%2$s">%1$s</button>
					<span class="spinner"></span>
				</li>', __( 'Add Account', 'infusionsoft' ), esc_attr( $service ), $form_fields,
				( $service == 'redirect' ) ? ' hidden_item' : '' );
		}

		die();
	}

	/**
	 * Generates fields for the account authorization form based on the service
	 * @return string
	 */
	function generate_new_account_form( $service, $account_name = '', $display_name = true ) {
		$this->permissionsCheck();
		$field_values = '';

		if ( '' !== $account_name ) {
			$options_array = INF_Infusionsoft::get_infusionsoft_options();
			$field_values  = $options_array['accounts'][ $service ][ $account_name ];
		}

		$form_fields = sprintf( '<div class="account_settings_fields" data-service="%1$s">', esc_attr( $service ) );

		if ( true === $display_name ) {
			$form_fields .= sprintf( '
				<div class="inf_dashboard_account_row">
					<label for="%1$s">%2$s</label>
					<input type="text" value="%3$s" id="%1$s">%4$s
				</div>', esc_attr( 'name_' . $service ), __( 'Account Name', 'infusionsoft' ),
				esc_attr( $account_name ),
				INF_Infusionsoft::generate_hint( __( 'Enter the name for your account', 'infusionsoft' ), true ) );
		}

		//include class to get functions below
		if ( ! class_exists( 'infusionsoft_' . $service ) ) {
			require_once( INF_INFUSIONSOFT_PLUGIN_DIR . 'includes/classes/integrations/class.infusionsoft-' . $service . '.php' );
		}

		$infusionsoft = new infusionsoft_infusionsoft();
		$form_fields  = $infusionsoft->draw_infusionsoft_form( $form_fields, $service, $field_values );

		$form_fields .= '</div>';

		return $form_fields;
	}

	/**
	 * Retrieves lists for specific account from Plugin options.
	 * @return string
	 */
	function retrieve_accounts_list( $service, $accounts_list = array() ) {
		$this->permissionsCheck();
		$options_array = INF_Infusionsoft::get_infusionsoft_options();
		if ( isset( $options_array['accounts'] ) ) {
			if ( isset( $options_array['accounts'][ $service ] ) ) {
				foreach ( $options_array['accounts'][ $service ] as $account_name => $details ) {
					$accounts_list[ $account_name ] = $account_name;
				}
			}
		}

		return $accounts_list;
	}

	/**
	 * Generates the list of "Lists" for selected account in the Dashboard. Returns the generated form to jQuery.
	 */
	function generate_mailing_lists( $service = '', $account_name = '' ) {
		if ( ! wp_verify_nonce( $_POST['retrieve_lists_nonce'], 'retrieve_lists' ) ) {
			die( - 1 );
		}
		$account_for = ! empty( $_POST['infusionsoft_account_name'] ) ? sanitize_text_field( $_POST['infusionsoft_account_name'] ) : '';
		$service     = ! empty( $_POST['infusionsoft_service'] ) ? sanitize_text_field( $_POST['infusionsoft_service'] ) : '';
		$optin_id    = ! empty( $_POST['infusionsoft_optin_id'] ) ? sanitize_text_field( $_POST['infusionsoft_optin_id'] ) : '';

		$options_array      = INF_Infusionsoft::get_infusionsoft_options();
		$current_email_list = isset( $options_array[ $optin_id ] ) ? $options_array[ $optin_id ]['email_list'] : 'empty';

		$available_lists = array();

		if ( isset( $options_array['accounts'] ) ) {
			if ( isset( $options_array['accounts'][ $service ] ) ) {
				foreach ( $options_array['accounts'][ $service ] as $account_name => $details ) {
					if ( $account_for == $account_name ) {
						if ( isset( $details['lists'] ) ) {
							$available_lists = $details['lists'];
						}
					}
				}
			}
		}
		printf( '
			<li class="select inf_dashboard_select_list">
				<p>%1$s</p>
				<select name="inf_dashboard[email_list]">
					<option value="empty" %3$s>%2$s</option>', __( 'Select A Tag', 'infusionsoft' ),
			__( 'Select One...', 'infusionsoft' ), selected( 'empty', $current_email_list, false ) );

		if ( ! empty( $available_lists ) ) {
			foreach ( $available_lists as $list_id => $list_details ) {
				printf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr( $list_id ),
					esc_html( $list_details['name'] ), selected( $list_id, $current_email_list, false ) );
			}
		}

		printf( '
				</select>
			</li>' );

		die();
	}

	/**-------------------------**/
	/**        Front end        **/
	/**-------------------------**/

	function load_scripts_styles() {
		wp_enqueue_script( 'inf_infusionsoft-uniform-js', INF_INFUSIONSOFT_PLUGIN_URI . '/js/jquery.uniform.min.js',
			array( 'jquery' ), $this->plugin_version, true );
		wp_enqueue_script( 'inf_infusionsoft-custom-js', INF_INFUSIONSOFT_PLUGIN_URI . '/js/custom.js',
			array( 'jquery' ), $this->plugin_version, true );
		wp_enqueue_script( 'inf_infusionsoft-idle-timer-js', INF_INFUSIONSOFT_PLUGIN_URI . '/js/idle-timer.min.js',
			array( 'jquery' ), $this->plugin_version, true );
		wp_enqueue_style( 'inf_infusionsoft-open-sans',
			esc_url_raw( "{$this->protocol}://fonts.googleapis.com/css?family=Open+Sans:400,700" ), array(), null );
		wp_enqueue_style( 'inf_infusionsoft-css', INF_INFUSIONSOFT_PLUGIN_URI . '/css/style.css', array(),
			$this->plugin_version );
		wp_localize_script( 'inf_infusionsoft-custom-js', 'infusionsoftSettings', array(
			'ajaxurl'         => admin_url( 'admin-ajax.php', $this->protocol ),
			'pageurl'         => ( is_singular( get_post_types() ) ? get_permalink() : '' ),
			'stats_nonce'     => wp_create_nonce( 'update_stats' ),
			'subscribe_nonce' => wp_create_nonce( 'subscribe' ),
		) );


	}

	/**
	 * Generates the array of all taxonomies supported by Infusionsoft.
	 * Infusionsoft fully supports only taxonomies from ET themes.
	 * @return array
	 */
	function get_supported_taxonomies( $post_types ) {
		$taxonomies = array();

		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $single_type ) {
				if ( 'post' != $single_type ) {
					$taxonomies[] = $this->get_tax_slug( $single_type );
				}
			}
		}

		return $taxonomies;
	}

	/**
	 * Returns the slug for supported taxonomy based on post type.
	 * Returns empty string if taxonomy is not supported
	 * Infusionsoft fully supports only taxonomies from ET themes.
	 * @return string
	 */
	function get_tax_slug( $post_type ) {
		$theme_name = wp_get_theme();
		$taxonomy   = '';

		switch ( $post_type ) {
			case 'project' :
				$taxonomy = 'project_category';
				break;

			case 'product' :
				$taxonomy = 'product_cat';
				break;

			case 'listing' :
				if ( 'Explorable' == $theme_name ) {
					$taxonomy = 'listing_type';
				} else {
					$taxonomy = 'listing_category';
				}
				break;

			case 'event' :
				$taxonomy = 'event_category';
				break;

			case 'gallery' :
				$taxonomy = 'gallery_category';
				break;

			case 'post' :
				$taxonomy = 'category';
				break;
		}

		return $taxonomy;
	}

	/**
	 * Returns true if form should be displayed on particular page depending on user settings.
	 * @return bool
	 */
	function check_applicability( $optin_id ) {

		$options_array = INF_Infusionsoft::get_infusionsoft_options();
		$display_there = false;

		$optin_type = $options_array[ $optin_id ]['optin_type'];

		$current_optin_limits = array(
			'post_types'        => $options_array[ $optin_id ]['post_types'],
			'categories'        => $options_array[ $optin_id ]['post_categories'],
			'on_cat_select'     => isset( $options_array[ $optin_id ]['display_on'] ) && in_array( 'category',
				$options_array[ $optin_id ]['display_on'] ) ? true : false,
			'pages_exclude'     => is_array( $options_array[ $optin_id ]['pages_exclude'] ) ? $options_array[ $optin_id ]['pages_exclude'] : explode( ',',
				$options_array[ $optin_id ]['pages_exclude'] ),
			'pages_include'     => is_array( $options_array[ $optin_id ]['pages_include'] ) ? $options_array[ $optin_id ]['pages_include'] : explode( ',',
				$options_array[ $optin_id ]['pages_include'] ),
			'posts_exclude'     => is_array( $options_array[ $optin_id ]['posts_exclude'] ) ? $options_array[ $optin_id ]['posts_exclude'] : explode( ',',
				$options_array[ $optin_id ]['posts_exclude'] ),
			'posts_include'     => is_array( $options_array[ $optin_id ]['posts_include'] ) ? $options_array[ $optin_id ]['posts_include'] : explode( ',',
				$options_array[ $optin_id ]['posts_include'] ),
			'on_tag_select'     => isset( $options_array[ $optin_id ]['display_on'] ) && in_array( 'tags',
				$options_array[ $optin_id ]['display_on'] ) ? true : false,
			'on_archive_select' => isset( $options_array[ $optin_id ]['display_on'] ) && in_array( 'archive',
				$options_array[ $optin_id ]['display_on'] ) ? true : false,
			'homepage_select'   => isset( $options_array[ $optin_id ]['display_on'] ) && in_array( 'home',
				$options_array[ $optin_id ]['display_on'] ) ? true : false,
			'everything_select' => isset( $options_array[ $optin_id ]['display_on'] ) && in_array( 'everything',
				$options_array[ $optin_id ]['display_on'] ) ? true : false,
			'auto_select'       => isset( $options_array[ $optin_id ]['post_categories']['auto_select'] ) ? $options_array[ $optin_id ]['post_categories']['auto_select'] : false,
			'previously_saved'  => isset( $options_array[ $optin_id ]['post_categories']['previously_saved'] ) ? explode( ',',
				$options_array[ $optin_id ]['post_categories']['previously_saved'] ) : false,
		);

		unset( $current_optin_limits['categories']['previously_saved'] );

		$tax_to_check = $this->get_supported_taxonomies( $current_optin_limits['post_types'] );
		if ( ( 'flyin' == $optin_type || 'pop_up' == $optin_type || 'infusion' == $optin_type ) && true == $current_optin_limits['everything_select'] ) {
			if ( is_singular() ) {
				if ( ( is_singular( 'page' ) && ! in_array( get_the_ID(),
							$current_optin_limits['pages_exclude'] ) ) || ( ! is_singular( 'page' ) && ! in_array( get_the_ID(),
							$current_optin_limits['posts_exclude'] ) )
				) {
					$display_there = true;
				}
			} else {
				$display_there = true;
			}
		} else {
			if ( is_archive() && ( 'flyin' == $optin_type || 'pop_up' == $optin_type ) ) {
				if ( true == $current_optin_limits['on_archive_select'] ) {
					$display_there = true;
				} else {
					if ( ( ( is_category( $current_optin_limits['categories'] ) || ( ! empty( $tax_to_check ) && is_tax( $tax_to_check,
										$current_optin_limits['categories'] ) ) ) && true == $current_optin_limits['on_cat_select'] ) || ( is_tag() && true == $current_optin_limits['on_tag_select'] )
					) {
						$display_there = true;
					}
				}
			} else {
				$page_id           = ( is_front_page() && ! is_page() ) ? 'homepage' : get_the_ID();
				$current_post_type = 'homepage' == $page_id ? 'home' : get_post_type( $page_id );

				if ( is_singular() || ( 'home' == $current_post_type && ( 'flyin' == $optin_type || 'pop_up' == $optin_type || 'infusion' == $optin_type ) ) ) {
					if ( in_array( $page_id, $current_optin_limits['pages_include'] ) || in_array( (int) $page_id,
							$current_optin_limits['posts_include'] )
					) {
						$display_there = true;
					}

					if ( true == $current_optin_limits['homepage_select'] && is_front_page() ) {
						$display_there = true;
					}
				}

				if ( ! empty( $current_optin_limits['post_types'] ) && is_singular( $current_optin_limits['post_types'] ) ) {

					switch ( $current_post_type ) {
						case 'page' :
						case 'home' :
							if ( ( 'home' == $current_post_type && ( 'flyin' == $optin_type || 'pop_up' == $optin_type ) ) || 'home' != $current_post_type ) {
								if ( ! in_array( $page_id, $current_optin_limits['pages_exclude'] ) ) {
									$display_there = true;
								}
							}
							break;

						default :
							$taxonomy_slug = $this->get_tax_slug( $current_post_type );

							if ( ! in_array( $page_id, $current_optin_limits['posts_exclude'] ) ) {
								if ( '' != $taxonomy_slug ) {
									$categories = get_the_terms( $page_id, $taxonomy_slug );
									$post_cats  = array();
									if ( $categories ) {
										foreach ( $categories as $category ) {
											$post_cats[] = $category->term_id;
										}
									}

									foreach ( $post_cats as $single_cat ) {
										if ( in_array( $single_cat, $current_optin_limits['categories'] ) ) {
											$display_there = true;
										}
									}

									if ( false === $display_there && 1 == $current_optin_limits['auto_select'] ) {
										foreach ( $post_cats as $single_cat ) {
											if ( ! in_array( $single_cat,
												$current_optin_limits['previously_saved'] )
											) {
												$display_there = true;
											}
										}
									}
								} else {
									$display_there = true;
								}
							}

							break;
					}
				}
			}
		}

		return $display_there;
	}

	/**
	 * Calculates and returns the ID of optin which should be displayed if A/B testing is enabled
	 * @return string
	 */
	public static function choose_form_ab_test( $optin_id, $optins_set, $update_option = true ) {
		$chosen_form = $optin_id;

		if ( ! empty( $optins_set[ $optin_id ]['child_optins'] ) && 'active' == $optins_set[ $optin_id ]['test_status'] ) {
			$chosen_form = ( '-1' != $optins_set[ $optin_id ]['next_optin'] || empty( $optins_set[ $optin_id ]['next_optin'] ) ) ? $optins_set[ $optin_id ]['next_optin'] : $optin_id;

			if ( '-1' == $optins_set[ $optin_id ]['next_optin'] ) {
				$next_optin = $optins_set[ $optin_id ]['child_optins'][0];
			} else {
				$child_forms_count = count( $optins_set[ $optin_id ]['child_optins'] );

				for ( $i = 0; $i < $child_forms_count; $i ++ ) {
					if ( $optins_set[ $optin_id ]['next_optin'] == $optins_set[ $optin_id ]['child_optins'][ $i ] ) {
						$current_optin_number = $i;
					}
				}

				if ( ( $child_forms_count - 1 ) == $current_optin_number ) {
					$next_optin = '-1';
				} else {
					$next_optin = $optins_set[ $optin_id ]['child_optins'][ $current_optin_number + 1 ];
				}

			}
			if ( true === $update_option ) {
				$update_test_optin[ $optin_id ]               = $optins_set[ $optin_id ];
				$update_test_optin[ $optin_id ]['next_optin'] = $next_optin;
				INF_Infusionsoft::update_infusionsoft_options( $update_test_optin );
			}
		}

		return $chosen_form;
	}

	/**
	 * Handles the stats adding request via jQuery
	 * @return void
	 */
	function handle_stats_adding() {
		if ( ! wp_verify_nonce( $_POST['update_stats_nonce'], 'update_stats' ) ) {
			die( - 1 );
		}
		$stats_data_json  = str_replace( '\\', '', $_POST['stats_data_array'] );
		$stats_data_array = json_decode( $stats_data_json, true );
		INF_Infusionsoft::add_stats_record( $stats_data_array['type'], $stats_data_array['optin_id'],
			$stats_data_array['page_id'], $stats_data_array['list_id'] );

		die();

	}

	/**
	 * Adds the record to stats table. Either conversion or impression for specific list on specific form on specific
	 * page.
	 * @return void
	 */
	public static function add_stats_record( $type, $optin_id, $page_id, $list_id ) {
		global $wpdb;

		$row_added = false;

		$table_name = $wpdb->prefix . 'inf_infusionsoft_stats';

		$record_date = current_time( 'mysql' );
		$ip_address  = $_SERVER['REMOTE_ADDR'];

		$wpdb->insert( $table_name, array(
			'record_date'  => sanitize_text_field( $record_date ),
			'optin_id'     => sanitize_text_field( $optin_id ),
			'record_type'  => sanitize_text_field( $type ),
			'page_id'      => (int) $page_id,
			'list_id'      => sanitize_text_field( $list_id ),
			'ip_address'   => sanitize_text_field( $ip_address ),
			'removed_flag' => (int) 0,
		), array(
			'%s', // record_date
			'%s', // optin_id
			'%s', // record_type
			'%d', // page_id
			'%s', // list_id
			'%s', // ip_address
			'%d', // removed_flag
		) );

		$row_added = true;

		return $row_added;
	}

	// add marker at the bottom of the_content() for the "Trigger at bottom of post" option.
	function trigger_bottom_mark( $content ) {
		$content .= '<span class="inf_infusionsoft_bottom_trigger"></span>';

		return $content;
	}

	/**
	 * Generates the content for the optin.
	 * @return string
	 */
	public static function generate_form_content( $optin_id, $page_id, $pagename = '', $details = array() ) {
		if ( empty( $details ) ) {
			$all_optins = INF_Infusionsoft::get_infusionsoft_options();
			$details    = $all_optins[ $optin_id ];
		}
		if ( isset( $_COOKIE['hubspotutk'] ) ) {
			$hubspot_cookie = $_COOKIE['hubspotutk'];
		} else {
			$hubspot_cookie = '';
		}

		$hide_img_mobile_class = isset( $details['hide_mobile'] ) && '1' == $details['hide_mobile'] ? 'inf_infusionsoft_hide_mobile' : '';
		$image_animation_class = isset( $details['image_animation'] ) ? esc_attr( ' inf_infusionsoft_image_' . $details['image_animation'] ) : 'inf_infusionsoft_image_no_animation';
		$image_class           = $hide_img_mobile_class . $image_animation_class . ' inf_infusionsoft_image';

		// Translate all strings if WPML is enabled
		if ( function_exists( 'icl_translate' ) ) {
			$optin_title      = icl_translate( 'infusionsoft', 'optin_title_' . $optin_id, $details['optin_title'] );
			$optin_message    = icl_translate( 'infusionsoft', 'optin_message_' . $optin_id,
				$details['optin_message'] );
			$email_text       = icl_translate( 'infusionsoft', 'email_text_' . $optin_id, $details['email_text'] );
			$first_name_text  = icl_translate( 'infusionsoft', 'name_text_' . $optin_id, $details['name_text'] );
			$single_name_text = icl_translate( 'infusionsoft', 'single_name_text_' . $optin_id,
				$details['single_name_text'] );
			$last_name_text   = icl_translate( 'infusionsoft', 'last_name_' . $optin_id, $details['last_name'] );
			$button_text      = icl_translate( 'infusionsoft', 'button_text_' . $optin_id, $details['button_text'] );
			$success_text     = icl_translate( 'infusionsoft', 'success_message_' . $optin_id,
				$details['success_message'] );
			$footer_text      = icl_translate( 'infusionsoft', 'footer_text_' . $optin_id, $details['footer_text'] );
		} else {
			$optin_title      = $details['optin_title'];
			$optin_message    = $details['optin_message'];
			$email_text       = $details['email_text'];
			$first_name_text  = $details['name_text'];
			$single_name_text = $details['single_name_text'];
			$last_name_text   = $details['last_name'];
			$button_text      = $details['button_text'];
			$success_text     = $details['success_message'];
			$footer_text      = $details['footer_text'];
		}

		$formatted_title   = '&lt;h2&gt;&nbsp;&lt;/h2&gt;' != $details['optin_title'] ? str_replace( '&nbsp;', '',
			$optin_title ) : '';
		$formatted_message = '' != $details['optin_message'] ? $optin_message : '';
		$formatted_footer  = '' != $details['footer_text'] ? sprintf( '<div class="inf_infusionsoft_form_footer">
					<p>%1$s</p>
				</div>', stripslashes( esc_html( $footer_text ) ) ) : '';

		$is_single_name = ( isset( $details['display_name'] ) && '1' == $details['display_name'] ) ? false : true;

		$output = sprintf( '
			<div class="inf_infusionsoft_form_container_wrapper clearfix">
				<div class="inf_infusionsoft_header_outer">
					<div class="inf_infusionsoft_form_header%1$s%13$s">
						%2$s
						%3$s
						%4$s
					</div>
				</div>
				<div class="inf_infusionsoft_form_content%5$s%6$s%7$s%12$s"%11$s>
					%8$s
					<div class="inf_infusionsoft_success_container">
						<span class="inf_infusionsoft_success_checkmark"></span>
					</div>
					<h2 class="inf_infusionsoft_success_message">%9$s</h2>
					%10$s
				</div>
			</div>
			<span class="inf_infusionsoft_close_button"></span>',
			( 'right' == $details['image_orientation'] || 'left' == $details['image_orientation'] ) && 'widget' !== $details['optin_type'] ? sprintf( ' split%1$s',
				'right' == $details['image_orientation'] ? ' image_right' : '' ) : '',
			( ( 'above' == $details['image_orientation'] || 'right' == $details['image_orientation'] || 'left' == $details['image_orientation'] ) && 'widget' !== $details['optin_type'] ) || ( 'above' == $details['image_orientation_widget'] && 'widget' == $details['optin_type'] ) ? sprintf( '%1$s',
				empty( $details['image_url']['id'] ) ? sprintf( '<img src="%1$s" alt="%2$s" %3$s>',
					esc_attr( $details['image_url']['url'] ),
					esc_attr( wp_strip_all_tags( html_entity_decode( $formatted_title ) ) ),
					'' !== $image_class ? sprintf( 'class="%1$s"',
						esc_attr( $image_class ) ) : '' ) : wp_get_attachment_image( $details['image_url']['id'],
					'infusionsoft_image', false, array( 'class' => $image_class ) ) ) : '',
			( '' !== $formatted_title || '' !== $formatted_message ) ? sprintf( '<div class="inf_infusionsoft_form_text">
						%1$s%2$s
					</div>', stripslashes( html_entity_decode( $formatted_title, ENT_QUOTES, 'UTF-8' ) ),
				stripslashes( html_entity_decode( $formatted_message, ENT_QUOTES, 'UTF-8' ) ) ) : '',
			( 'below' == $details['image_orientation'] && 'widget' !== $details['optin_type'] ) || ( isset( $details['image_orientation_widget'] ) && 'below' == $details['image_orientation_widget'] && 'widget' == $details['optin_type'] ) ? sprintf( '%1$s',
				empty( $details['image_url']['id'] ) ? sprintf( '<img src="%1$s" alt="%2$s" %3$s>',
					esc_attr( $details['image_url']['url'] ),
					esc_attr( wp_strip_all_tags( html_entity_decode( $formatted_title ) ) ),
					'' !== $image_class ? sprintf( 'class="%1$s"',
						esc_attr( $image_class ) ) : '' ) : wp_get_attachment_image( $details['image_url']['id'],
					'infusionsoft_image', false, array( 'class' => $image_class ) ) ) : '', //#5
			( 'no_name' == $details['name_fields'] && ! INF_Infusionsoft::is_only_name_support( $details['email_provider'] ) ) || ( INF_Infusionsoft::is_only_name_support( $details['email_provider'] ) && $is_single_name ) ? ' inf_infusionsoft_1_field' : sprintf( ' inf_infusionsoft_%1$s_fields',
				'first_last_name' == $details['name_fields'] && ! INF_Infusionsoft::is_only_name_support( $details['email_provider'] ) ? '3' : '2' ),
			'inline' == $details['field_orientation'] && 'bottom' == $details['form_orientation'] && 'widget' !== $details['optin_type'] ? ' inf_infusionsoft_bottom_inline' : '',
			( 'stacked' == $details['field_orientation'] && 'bottom' == $details['form_orientation'] ) || 'widget' == $details['optin_type'] ? ' inf_infusionsoft_bottom_stacked' : '',
			'custom_html' == $details['email_provider'] ? stripslashes( html_entity_decode( $details['custom_html'] ) ) : sprintf( '
					%1$s
					<form method="post" class="clearfix">
						%3$s
						<p class="inf_infusionsoft_popup_input inf_infusionsoft_subscribe_email %16$s">
							<input placeholder="%2$s">
						</p>

						<button data-optin_id="%4$s" data-service="%5$s" data-list_id="%6$s" data-page_id="%7$s" data-post_name="%12$s" data-cookie="%13$s" data-account="%8$s" data-disable_dbl_optin="%11$s" data-redirect_url="%15$s%17$s" data-redirect="%19$s" data-success_delay="%18$s" data-center_webhook_url="%23$s" class="%14$s%22$s" %20$s>
							<span class="inf_infusionsoft_subscribe_loader"></span>
							<span class="inf_infusionsoft_button_text inf_infusionsoft_button_text_color_%10$s">%9$s</span>
						</button>
						<div class="consent_wrapper" style="margin-top:10px;">%21$s</div>
					</form>

					',
				'basic_edge' == $details['edge_style'] || '' == $details['edge_style'] ? '' : INF_Infusionsoft::get_the_edge_code( $details['edge_style'],
					'widget' == $details['optin_type'] ? 'bottom' : $details['form_orientation'] ),
				'' != $email_text ? stripslashes( esc_attr( $email_text ) ) : esc_html__( 'Email', 'infusionsoft' ),
				( 'no_name' == $details['name_fields'] && ! INF_Infusionsoft::is_only_name_support( $details['email_provider'] ) ) || ( INF_Infusionsoft::is_only_name_support( $details['email_provider'] ) && $is_single_name ) ? '' : sprintf( '<p class="inf_infusionsoft_popup_input inf_infusionsoft_subscribe_name">
								<input placeholder="%1$s%2$s" maxlength="50">
							</p>%3$s', 'first_last_name' == $details['name_fields'] ? sprintf( '%1$s',
					'' != $first_name_text ? stripslashes( esc_attr( $first_name_text ) ) : esc_html__( 'First Name',
						'infusionsoft' ) ) : '', ( 'first_last_name' != $details['name_fields'] ) ? sprintf( '%1$s',
					'' != $single_name_text ? stripslashes( esc_attr( $single_name_text ) ) : esc_html__( 'Name',
						'infusionsoft' ) ) : '',
					'first_last_name' == $details['name_fields'] && ! INF_Infusionsoft::is_only_name_support( $details['email_provider'] ) ? sprintf( '
									<p class="inf_infusionsoft_popup_input inf_infusionsoft_subscribe_last">
										<input placeholder="%1$s" maxlength="50">
									</p>',
						'' != $last_name_text ? stripslashes( esc_attr( $last_name_text ) ) : esc_html__( 'Last Name',
							'infusionsoft' ) ) : '' ), esc_attr( $optin_id ), esc_attr( $details['email_provider'] ),
				//#5
				esc_attr( $details['email_list'] ), esc_attr( $page_id ), esc_attr( $details['account_name'] ),
				'' != $button_text ? stripslashes( esc_html( $button_text ) ) : esc_html__( 'SUBSCRIBE!',
					'infusionsoft' ),
				isset( $details['button_text_color'] ) ? esc_attr( $details['button_text_color'] ) : '', // #10
				isset( $details['disable_dbl_optin'] ) && '1' === $details['disable_dbl_optin'] ? 'disable' : '',#11
				esc_attr( $pagename ),#12
				esc_attr( $hubspot_cookie ),#13
				( isset( $details['enable_redirect_form'] ) && $details['enable_redirect_form'] == true ) ? 'inf_infusionsoft_redirect_page' : 'inf_infusionsoft_submit_subscription',
				#14
				( isset( $details['enable_redirect_form'] ) && $details['enable_redirect_form'] == true ) ? esc_url( $details['redirect_url'] ) : '',
				#15
				( isset( $details['enable_redirect_form'] ) && $details['enable_redirect_form'] == true ) ? 'hidden_item' : '',
				#16
				isset( $details['success_url'] ) ? esc_url( $details['success_url'] ) : '',
				#17 //you will notice both 15 and 17 exist in the dat-redirect_url attribute. This is because both should never be set at the same time.
				isset( $details['success_load_delay'] ) ? esc_attr( $details['success_load_delay'] ) : '', #18
				esc_attr( $details['redirect_standard'] ),#19
				( isset( $details['enable_consent'] ) && $details['enable_consent'] == true ) ? '' : '',
				#20 placeholder so we dont have to renumber items
				( isset( $details['enable_consent'] ) && $details['enable_consent'] == true ) ? '<div class="consent_error" style="background-color:' . $details['form_bg_color'] . '">' . $details['consent_error'] . '</div>' . '<div class="consent"><input type="checkbox" name="accept_consent" class="accept_consent">' . '<span class="consent_text" style="margin-bottom:0 !important; color:' . $details['consent_color'] . '; font-weight:400 !important;">' . $details['consent_text'] . '</span></div>' : '',
				#21
				( isset( $details['enable_consent'] ) && $details['enable_consent'] == true ) ? ' cursor-not-allowed' : '',
				#22
				( isset( $details['center_webhook_url'] ) && ! empty( $details['center_webhook_url'] ) ) ? $details['center_webhook_url'] : ''#23

			), '' != $success_text ? html_entity_decode( wp_kses( stripslashes( $success_text ), array(
				'a'      => array(),
				'br'     => array(),
				'span'   => array(),
				'strong' => array(),
			) ) ) : esc_html__( 'You have Successfully Subscribed!', 'infusionsoft' ), //#10
			$formatted_footer,
			'custom_html' == $details['email_provider'] ? sprintf( ' data-optin_id="%1$s" data-service="%2$s" data-list_id="%3$s" data-page_id="%4$s" data-account="%5$s"',
				esc_attr( $optin_id ), 'custom_form', 'custom_form', esc_attr( $page_id ), 'custom_form' ) : '',
			'custom_html' == $details['email_provider'] ? ' inf_infusionsoft_custom_html_form' : '',
			isset( $details['header_text_color'] ) ? sprintf( ' inf_infusionsoft_header_text_%1$s',
				esc_attr( $details['header_text_color'] ) ) : ' inf_infusionsoft_header_text_dark' //#14
		);

		return $output;
	}

	/**
	 * Checks whether network supports only First Name
	 * @return string
	 */
	public static function is_only_name_support( $service ) {
		$single_name_networks = array(
			'aweber',
			'getresponse'
		);
		$result               = in_array( $service, $single_name_networks );

		return $result;
	}

	/**
	 * Generates the svg code for edges
	 * @return bool
	 */
	public static function get_the_edge_code( $style, $orientation ) {
		$output = '';
		switch ( $style ) {
			case 'wedge_edge' :
				$output = sprintf( '<svg class="triangle inf_infusionsoft_default_edge" xmlns="http://www.w3.org/2000/svg" version="1.1" width="%2$s" height="%3$s" viewBox="0 0 100 100" preserveAspectRatio="none">
						<path d="%1$s" fill=""></path>
					</svg>', 'bottom' == $orientation ? 'M0 0 L50 100 L100 0 Z' : 'M0 0 L0 100 L100 50 Z',
					'bottom' == $orientation ? '100%' : '20', 'bottom' == $orientation ? '20' : '100%' );

				//if right or left orientation selected we still need to generate bottom edge to support responsive design
				if ( 'bottom' !== $orientation ) {
					$output .= sprintf( '<svg class="triangle inf_infusionsoft_responsive_edge" xmlns="http://www.w3.org/2000/svg" version="1.1" width="%2$s" height="%3$s" viewBox="0 0 100 100" preserveAspectRatio="none">
							<path d="%1$s" fill=""></path>
						</svg>', 'M0 0 L50 100 L100 0 Z', '100%', '20' );
				}

				break;
			case 'curve_edge' :
				$output = sprintf( '<svg class="curve inf_infusionsoft_default_edge" xmlns="http://www.w3.org/2000/svg" version="1.1" width="%2$s" height="%3$s" viewBox="0 0 100 100" preserveAspectRatio="none">
						<path d="%1$s"></path>
					</svg>', 'bottom' == $orientation ? 'M0 0 C40 100 60 100 100 0 Z' : 'M0 0 C0 0 100 50 0 100 z',
					'bottom' == $orientation ? '100%' : '20', 'bottom' == $orientation ? '20' : '100%' );

				//if right or left orientation selected we still need to generate bottom edge to support responsive design
				if ( 'bottom' !== $orientation ) {
					$output .= sprintf( '<svg class="curve inf_infusionsoft_responsive_edge" xmlns="http://www.w3.org/2000/svg" version="1.1" width="%2$s" height="%3$s" viewBox="0 0 100 100" preserveAspectRatio="none">
							<path d="%1$s"></path>
						</svg>', 'M0 0 C40 100 60 100 100 0 Z', '100%', '20' );
				}

				break;
		}

		return $output;
	}

	/**
	 * Displays the Flyin content on front-end.
	 */
	function display_flyin() {
		$optins_set = $this->flyin_optins;

		if ( ! empty( $optins_set ) ) {
			foreach ( $optins_set as $optin_id => $details ) {
				if ( $this->check_applicability( $optin_id ) ) {
					$display_optin_id = INF_Infusionsoft::choose_form_ab_test( $optin_id, $optins_set );

					if ( $display_optin_id != $optin_id ) {
						$all_optins = INF_Infusionsoft::get_infusionsoft_options();
						$optin_id   = $display_optin_id;
						$details    = $all_optins[ $optin_id ];
					}

					if ( is_singular() || is_front_page() ) {
						$page_id   = is_front_page() ? - 1 : get_the_ID();
						$post      = get_post();
						$post_name = $post->post_name;
					} else {
						$page_id   = 0;
						$post_name = '';
					}

					printf( '<div class="inf_infusionsoft_flyin inf_infusionsoft_optin inf_infusionsoft_resize inf_infusionsoft_flyin_%6$s inf_infusionsoft_%5$s%17$s%1$s%2$s%18$s%19$s%20$s%21$s%28$s"%22$s%3$s%4$s%16$s%27$s>
							<div class="inf_infusionsoft_form_container%7$s%8$s%9$s%10$s%12$s%13$s%14$s%15$s%23$s%24$s%25$s">

								%11$s

							</div>
						</div>', true == $details['post_bottom'] ? ' inf_infusionsoft_trigger_bottom' : '',
						isset( $details['trigger_idle'] ) && true == $details['trigger_idle'] ? ' inf_infusionsoft_trigger_idle' : '',
						isset( $details['trigger_auto'] ) && true == $details['trigger_auto'] ? sprintf( ' data-delay="%1$s"',
							esc_attr( $details['load_delay'] ) ) : '',
						true == $details['session'] ? ' data-cookie_duration="' . esc_attr( $details['session_duration'] ) . '"' : '',
						esc_attr( $optin_id ), // #5
						esc_attr( $details['flyin_orientation'] ),
						'bottom' !== $details['form_orientation'] && 'custom_html' !== $details['email_provider'] ? sprintf( ' inf_infusionsoft_form_%1$s',
							esc_attr( $details['form_orientation'] ) ) : ' inf_infusionsoft_form_bottom',
						'basic_edge' == $details['edge_style'] || '' == $details['edge_style'] ? '' : sprintf( ' with_edge %1$s',
							esc_attr( $details['edge_style'] ) ),
						( 'no_border' !== $details['border_orientation'] ) ? sprintf( ' inf_infusionsoft_with_border inf_infusionsoft_border_%1$s%2$s',
							esc_attr( $details['border_style'] ),
							esc_attr( ' inf_infusionsoft_border_position_' . $details['border_orientation'] ) ) : '',
						( 'rounded' == $details['corner_style'] ) ? ' inf_infusionsoft_rounded_corners' : '', //#10
						INF_Infusionsoft::generate_form_content( $optin_id, $page_id, $post_name, $details ),
						'bottom' == $details['form_orientation'] && ( 'no_image' == $details['image_orientation'] || 'above' == $details['image_orientation'] || 'below' == $details['image_orientation'] ) && 'stacked' == $details['field_orientation'] ? ' inf_infusionsoft_stacked_flyin' : '',
						( 'rounded' == $details['field_corner'] ) ? ' inf_infusionsoft_rounded' : '',
						'light' == $details['text_color'] ? ' inf_infusionsoft_form_text_light' : ' inf_infusionsoft_form_text_dark',
						isset( $details['load_animation'] ) ? sprintf( ' inf_infusionsoft_animation_%1$s',
							esc_attr( $details['load_animation'] ) ) : ' inf_infusionsoft_animation_no_animation', //#15
						isset( $details['trigger_idle'] ) && true == $details['trigger_idle'] ? sprintf( ' data-idle_timeout="%1$s"',
							esc_attr( $details['idle_timeout'] ) ) : '',
						isset( $details['trigger_auto'] ) && true == $details['trigger_auto'] ? ' inf_infusionsoft_auto_popup' : '',
						isset( $details['exit_trigger'] ) && true == $details['exit_trigger'] ? ' inf_infusionsoft_before_exit' : '',
						isset( $details['comment_trigger'] ) && true == $details['comment_trigger'] ? ' inf_infusionsoft_after_comment' : '',
						isset( $details['purchase_trigger'] ) && true == $details['purchase_trigger'] ? ' inf_infusionsoft_after_purchase' : '',
						//#20
						isset( $details['trigger_scroll'] ) && true == $details['trigger_scroll'] ? ' inf_infusionsoft_scroll' : '',
						isset( $details['trigger_scroll'] ) && true == $details['trigger_scroll'] ? sprintf( ' data-scroll_pos="%1$s"',
							esc_attr( $details['scroll_pos'] ) ) : '',
						isset( $details['hide_mobile_optin'] ) && true == $details['hide_mobile_optin'] ? ' inf_infusionsoft_hide_mobile_optin' : '',
						( 'no_name' == $details['name_fields'] && ! INF_Infusionsoft::is_only_name_support( $details['email_provider'] ) ) || ( INF_Infusionsoft::is_only_name_support( $details['email_provider'] ) && $is_single_name ) ? ' inf_flyin_1_field' : sprintf( ' inf_flyin_%1$s_fields',
							'first_last_name' == $details['name_fields'] && ! INF_Infusionsoft::is_only_name_support( $details['email_provider'] ) ? '3' : '2' ),
						'inline' == $details['field_orientation'] && 'bottom' == $details['form_orientation'] ? ' inf_infusionsoft_flyin_bottom_inline' : '',
						//#25
						'stacked' == $details['field_orientation'] && 'bottom' == $details['form_orientation'] && ( 'right' == $details['image_orientation'] || 'left' == $details['image_orientation'] ) ? ' inf_infusionsoft_flyin_bottom_stacked' : '',
						true == $details['click_trigger'] ? ' data-click_trigger="' . esc_attr( $details['click_trigger_selector'] ) . '"' : '',
						#28
						isset( $details['click_trigger'] ) && true == $details['click_trigger'] ? ' inf_infusionsoft_click_trigger' : ''#29
					);
				}
			}
		}
	}

	/**
	 * Displays the PopUp content on front-end.
	 */
	function display_popup() {
		$optins_set = $this->popup_optins;

		if ( ! empty( $optins_set ) ) {
			foreach ( $optins_set as $optin_id => $details ) {
				if ( $this->check_applicability( $optin_id ) ) {
					$display_optin_id = INF_Infusionsoft::choose_form_ab_test( $optin_id, $optins_set );

					if ( $display_optin_id != $optin_id ) {
						$all_optins = INF_Infusionsoft::get_infusionsoft_options();
						$optin_id   = $display_optin_id;
						$details    = $all_optins[ $optin_id ];
					}

					if ( is_singular() || is_front_page() ) {
						$page_id   = is_front_page() ? - 1 : get_the_ID();
						$post      = get_post();
						$post_name = $post->post_name;
					} else {
						$post_name = '';
						$page_id   = 0;
					}

					printf( '<div class="inf_infusionsoft_popup inf_infusionsoft_optin inf_infusionsoft_resize inf_infusionsoft_%5$s%15$s%21$s%1$s%2$s%16$s%17$s%18$s%20$s%22$s"%3$s%4$s%14$s%19$s%22$s>
							<div class="inf_infusionsoft_form_container inf_infusionsoft_popup_container%6$s%7$s%8$s%9$s%11$s%12$s%13$s">
								%10$s
							</div>
						</div>', true == $details['post_bottom'] ? ' inf_infusionsoft_trigger_bottom' : '',
						isset( $details['trigger_idle'] ) && true == $details['trigger_idle'] ? ' inf_infusionsoft_trigger_idle' : '',
						isset( $details['trigger_auto'] ) && true == $details['trigger_auto'] ? sprintf( ' data-delay="%1$s"',
							esc_attr( $details['load_delay'] ) ) : '',
						true == $details['session'] ? ' data-cookie_duration="' . esc_attr( $details['session_duration'] ) . '"' : '',
						esc_attr( $optin_id ), // #5
						'bottom' !== $details['form_orientation'] && 'custom_html' !== $details['email_provider'] ? sprintf( ' inf_infusionsoft_form_%1$s',
							esc_attr( $details['form_orientation'] ) ) : ' inf_infusionsoft_form_bottom',
						'basic_edge' == $details['edge_style'] || '' == $details['edge_style'] ? '' : sprintf( ' with_edge %1$s',
							esc_attr( $details['edge_style'] ) ),
						( 'no_border' !== $details['border_orientation'] ) ? sprintf( ' inf_infusionsoft_with_border inf_infusionsoft_border_%1$s%2$s',
							esc_attr( $details['border_style'] ),
							esc_attr( ' inf_infusionsoft_border_position_' . $details['border_orientation'] ) ) : '',
						( 'rounded' == $details['corner_style'] ) ? ' inf_infusionsoft_rounded_corners' : '',
						INF_Infusionsoft::generate_form_content( $optin_id, $page_id, $post_name, $details ), //#10
						( 'rounded' == $details['field_corner'] ) ? ' inf_infusionsoft_rounded' : '',
						'light' == $details['text_color'] ? ' inf_infusionsoft_form_text_light' : ' inf_infusionsoft_form_text_dark',
						isset( $details['load_animation'] ) ? sprintf( ' inf_infusionsoft_animation_%1$s',
							esc_attr( $details['load_animation'] ) ) : ' inf_infusionsoft_animation_no_animation',
						isset( $details['trigger_idle'] ) && true == $details['trigger_idle'] ? sprintf( ' data-idle_timeout="%1$s"',
							esc_attr( $details['idle_timeout'] ) ) : '',
						isset( $details['trigger_auto'] ) && true == $details['trigger_auto'] ? ' inf_infusionsoft_auto_popup' : '',
						//#15
						isset( $details['comment_trigger'] ) && true == $details['comment_trigger'] ? ' inf_infusionsoft_after_comment' : '',
						isset( $details['purchase_trigger'] ) && true == $details['purchase_trigger'] ? ' inf_infusionsoft_after_purchase' : '',
						isset( $details['trigger_scroll'] ) && true == $details['trigger_scroll'] ? ' inf_infusionsoft_scroll' : '',
						isset( $details['trigger_scroll'] ) && true == $details['trigger_scroll'] ? sprintf( ' data-scroll_pos="%1$s"',
							esc_attr( $details['scroll_pos'] ) ) : '',
						( isset( $details['hide_mobile_optin'] ) && true == $details['hide_mobile_optin'] ) ? ' inf_infusionsoft_hide_mobile_optin' : '',
						//#20
						isset( $details['exit_trigger'] ) && true == $details['exit_trigger'] ? ' inf_infusionsoft_before_exit' : '',
						#21

						isset( $details['click_trigger'] ) && true == $details['click_trigger'] ? ' inf_infusionsoft_click_trigger' : '' );
				}
			}
		}
	}

	function sanitize_options_meta( $options ) {
		return strip_tags( $options );
	}

	function display_preview() {
		$this->permissionsCheck();
		if ( ! wp_verify_nonce( $_POST['infusionsoft_preview_nonce'], 'infusionsoft_preview' ) ) {
			die( - 1 );
		}

		$options          = sanitize_meta( 'options', $_POST['preview_options'], 'optin' );
		$processed_string = str_replace( array( '%5B', '%5D' ), array( '[', ']' ), $options );
		parse_str( $processed_string, $processed_array );
		$details     = $processed_array['inf_dashboard'];
		$fonts_array = array();
		if ( ! isset( $fonts_array[ $details['header_font'] ] ) && isset( $details['header_font'] ) ) {
			$fonts_array[] = $details['header_font'];
		}
		if ( ! isset( $fonts_array[ $details['body_font'] ] ) && isset( $details['body_font'] ) ) {
			$fonts_array[] = $details['body_font'];
		}


		if ( $details['optin_type'] != 'infusion' ) {
			$popup_array['popup_code'] = $this->generate_preview_popup( $details );
			$popup_array['popup_css']  = '<style id="inf_infusionsoft_preview_css">' . INF_Infusionsoft::generate_custom_css( '.inf_infusionsoft .inf_infusionsoft_preview_popup',
					$details ) . '</style>';
		} else {
			$popup_array['popup_code'] = infusionsoft_infusionbar::generate_preview_popup( $details );
			$popup_array['popup_css']  = '<style id="inf_infusionsoft_preview_css">' . infusionsoft_infusionbar::generate_custom_css( '.inf_infusionsoft .inf_infusionsoft_preview_infusionbar',
					$details ) . '</style>';
		}
		$popup_array['fonts'] = $fonts_array;

		die( json_encode( $popup_array ) );
	}

	/**
	 * Displays the PopUp preview in dashboard.
	 */
	function generate_preview_popup( $details ) {
		$this->permissionsCheck();
		$output = '';
		$output = sprintf( '<div class="inf_infusionsoft_popup inf_infusionsoft_animated inf_infusionsoft_preview_popup inf_infusionsoft_optin">
				<div class="inf_infusionsoft_form_container inf_infusionsoft_animation_fadein inf_infusionsoft_popup_container%1$s%2$s%3$s%4$s%5$s%6$s">
					%7$s
				</div>
			</div>',
			'bottom' !== $details['form_orientation'] && 'custom_html' !== $details['email_provider'] && 'widget' !== $details['optin_type'] ? sprintf( ' inf_infusionsoft_form_%1$s',
				esc_attr( $details['form_orientation'] ) ) : ' inf_infusionsoft_form_bottom',
			'basic_edge' == $details['edge_style'] || '' == $details['edge_style'] ? '' : sprintf( ' with_edge %1$s',
				esc_attr( $details['edge_style'] ) ),
			( 'no_border' !== $details['border_orientation'] ) ? sprintf( ' inf_infusionsoft_with_border inf_infusionsoft_border_%1$s%2$s',
				esc_attr( $details['border_style'] ),
				esc_attr( ' inf_infusionsoft_border_position_' . $details['border_orientation'] ) ) : '',
			( 'rounded' == $details['corner_style'] ) ? ' inf_infusionsoft_rounded_corners' : '',
			( 'rounded' == $details['field_corner'] ) ? ' inf_infusionsoft_rounded' : '',
			'light' == $details['text_color'] ? ' inf_infusionsoft_form_text_light' : ' inf_infusionsoft_form_text_dark',
			INF_Infusionsoft::generate_form_content( 0, 0, '', $details ) );

		return $output;
	}

	/**
	 * Modifies the_content to add the form below content.
	 */
	function display_below_post( $content ) {
		$optins_set = $this->below_post_optins;

		if ( ! empty( $optins_set ) && ! is_singular( 'product' ) ) {
			foreach ( $optins_set as $optin_id => $details ) {
				if ( $this->check_applicability( $optin_id ) ) {
					$content .= '<div class="inf_infusionsoft_below_post">' . $this->generate_inline_form( $optin_id,
							$details ) . '</div>';
				}
			}
		}

		return $content;
	}

	function display_infusionbar() {
		$optins_set = $this->infusion_optins;
		if ( ! empty( $optins_set ) ) {
			foreach ( $optins_set as $optin_id => $details ) {
				if ( $this->check_applicability( $optin_id ) ) {
					$displayCookie = 'inf_infusionsoft_subscribed_to_' . $optin_id . $details['email_list'];
					if ( ! isset( $_COOKIE[ $displayCookie ] ) ) {
						$content = sprintf( '<div class="inf_infusionsoft_infusionbar %1$s%3$s %4$s" %2$s>' . $this->generate_infusionbar_form( $optin_id,
								$details ) . '
							%5$s
							</div>',
							isset( $details['trigger_auto'] ) && true == $details['trigger_auto'] ? 'inf_infusionsoft_infusionbar_trigger_auto' : '',
							#1
							isset( $details['trigger_auto'] ) && true == $details['trigger_auto'] ? sprintf( 'data-delay="%1$s"',
								esc_attr( $details['load_delay'] ) ) : '',#2
							( 'no_border' !== $details['border_orientation'] ) ? sprintf( ' inf_infusionsoft_border_%1$s%2$s',
								esc_attr( $details['border_style'] ),
								'full' !== $details['border_orientation'] ? ' inf_infusionsoft_border_position_' . $details['border_orientation'] : '' ) : '',
							#3
							esc_attr( $details['infusion_position'] ),#4
							( isset( $details['enable_consent'] ) && $details['enable_consent'] == true ) ? '
							<div class="consent_error" style="background-color:' . $details['form_bg_color'] . '">' . $details['consent_error'] . '</div>
							<div class="infusion_consent_closed infusion_consent_form" style="background-color:' . $details['form_bg_color'] . '"><input type="checkbox" name="accept_consent" class="accept_consent">' . '<span class="consent_text" style="margin-bottom:0 !important; color:' . $details['consent_color'] . '; font-weight:400 !important;">' . $details['consent_text'] . '</span>
							</div>' : ''#5
						);


					}
					echo $content;
				}
			}
		}
	}

	/**
	 * Display the form on woocommerce product page.
	 */
	function display_on_wc_page() {
		$optins_set = $this->below_post_optins;

		if ( ! empty( $optins_set ) ) {
			foreach ( $optins_set as $optin_id => $details ) {
				if ( $this->check_applicability( $optin_id ) ) {
					echo $this->generate_inline_form( $optin_id, $details );
				}
			}
		}
	}


	/**
	 * Generates the content for infusion form. Used to generate
	 */
	function generate_infusionbar_form( $optin_id, $details, $update_stats = true ) {
		$output = '';

		$page_id           = get_the_ID();
		$list_id           = $details['email_provider'] . '_' . $details['email_list'];
		$custom_css_output = '';

		$all_optins       = INF_Infusionsoft::get_infusionsoft_options();
		$display_optin_id = INF_Infusionsoft::choose_form_ab_test( $optin_id, $all_optins );

		if ( $display_optin_id != $optin_id ) {
			$optin_id = $display_optin_id;
			$details  = $all_optins[ $optin_id ];
		}
		if ( true === $update_stats ) {
			INF_Infusionsoft::add_stats_record( 'imp', $optin_id, $page_id, $list_id );
		}

		$custom_css        = infusionsoft_infusionbar::generate_custom_css( '.inf_infusionsoft .inf_infusionsoft_' . $display_optin_id,
			$details );
		$custom_css_output = '' !== $custom_css ? sprintf( '<style type="text/css">%1$s</style>', $custom_css ) : '';


		$output .= sprintf( '<div class="inf_infusionsoft_infusionbar_form inf_infusionsoft_optin inf_infusionsoft_%1$s%9$s">
				%10$s
				<div class="inf_infusionsoft_form_container inf_infusionsoft_infusionbar_container%3$s%5$s%6$s%7$s%8$s%11$s">
					%2$s
				</div>

			</div>', esc_attr( $optin_id ), infusionsoft_infusionbar::generate_form_content( $optin_id, $page_id ),
			'basic_edge' == $details['edge_style'] || '' == $details['edge_style'] ? '' : sprintf( ' with_edge %1$s',
				esc_attr( $details['edge_style'] ) ),
			( 'no_border' !== $details['border_orientation'] ) ? sprintf( ' inf_infusionsoft_border_%1$s%2$s',
				esc_attr( $details['border_style'] ),
				'full' !== $details['border_orientation'] ? ' inf_infusionsoft_border_position_' . $details['border_orientation'] : '' ) : '',
			( 'rounded' == $details['corner_style'] ) ? ' inf_infusionsoft_rounded_corners' : '', //#5
			( 'rounded' == $details['field_corner'] ) ? ' inf_infusionsoft_rounded' : '',
			'light' == $details['text_color'] ? ' inf_infusionsoft_form_text_light' : ' inf_infusionsoft_form_text_dark',
			'bottom' !== $details['form_orientation'] && 'custom_html' !== $details['email_provider'] ? sprintf( ' inf_infusionsoft_form_%1$s',
				esc_html( $details['form_orientation'] ) ) : ' inf_infusionsoft_form_bottom',
			( isset( $details['hide_mobile_optin'] ) && true == $details['hide_mobile_optin'] ) ? ' inf_infusionsoft_hide_mobile_optin' : '',
			$custom_css_output, //#10
			( 'no_name' == $details['name_fields'] && ! INF_Infusionsoft::is_only_name_support( $details['email_provider'] ) ) || ( INF_Infusionsoft::is_only_name_support( $details['email_provider'] ) && $is_single_name ) ? ' inf_infusionsoft_inline_1_field' : sprintf( ' inf_infusionsoft_inline_%1$s_fields',
				'first_last_name' == $details['name_fields'] && ! INF_Infusionsoft::is_only_name_support( $details['email_provider'] ) ? '3' : '2' ) );

		return $output;
	}

	/**
	 * Generates the content for inline form. Used to generate "Below content", "Inilne" and "Locked content" forms.
	 */
	function generate_inline_form( $optin_id, $details, $update_stats = true ) {
		$output = '';

		$page_id           = get_the_ID();
		$list_id           = $details['email_provider'] . '_' . $details['email_list'];
		$custom_css_output = '';

		$all_optins       = INF_Infusionsoft::get_infusionsoft_options();
		$display_optin_id = INF_Infusionsoft::choose_form_ab_test( $optin_id, $all_optins );

		if ( $display_optin_id != $optin_id ) {
			$optin_id = $display_optin_id;
			$details  = $all_optins[ $optin_id ];
		}
		if ( true === $update_stats ) {
			INF_Infusionsoft::add_stats_record( 'imp', $optin_id, $page_id, $list_id );
		}
		if ( 'below_post' !== $details['optin_type'] ) {
			$custom_css        = INF_Infusionsoft::generate_custom_css( '.inf_infusionsoft .inf_infusionsoft_' . $display_optin_id,
				$details );
			$custom_css_output = '' !== $custom_css ? sprintf( '<style type="text/css">%1$s</style>',
				$custom_css ) : '';
		}

		$output .= sprintf( '<div class="inf_infusionsoft_inline_form inf_infusionsoft_optin inf_infusionsoft_%1$s%9$s">
				%10$s
				<div class="inf_infusionsoft_form_container inf_infusionsoft_popup_container%3$s%4$s%5$s%6$s%7$s%8$s%11$s">
					%2$s
				</div>
			</div>', esc_attr( $optin_id ), INF_Infusionsoft::generate_form_content( $optin_id, $page_id ),
			'basic_edge' == $details['edge_style'] || '' == $details['edge_style'] ? '' : sprintf( ' with_edge %1$s',
				esc_attr( $details['edge_style'] ) ),
			( 'no_border' !== $details['border_orientation'] ) ? sprintf( ' inf_infusionsoft_border_%1$s%2$s',
				esc_attr( $details['border_style'] ),
				'full' !== $details['border_orientation'] ? ' inf_infusionsoft_border_position_' . $details['border_orientation'] : '' ) : '',
			( 'rounded' == $details['corner_style'] ) ? ' inf_infusionsoft_rounded_corners' : '', //#5
			( 'rounded' == $details['field_corner'] ) ? ' inf_infusionsoft_rounded' : '',
			'light' == $details['text_color'] ? ' inf_infusionsoft_form_text_light' : ' inf_infusionsoft_form_text_dark',
			'bottom' !== $details['form_orientation'] && 'custom_html' !== $details['email_provider'] ? sprintf( ' inf_infusionsoft_form_%1$s',
				esc_html( $details['form_orientation'] ) ) : ' inf_infusionsoft_form_bottom',
			( isset( $details['hide_mobile_optin'] ) && true == $details['hide_mobile_optin'] ) ? ' inf_infusionsoft_hide_mobile_optin' : '',
			$custom_css_output, //#10
			( 'no_name' == $details['name_fields'] && ! INF_Infusionsoft::is_only_name_support( $details['email_provider'] ) ) || ( INF_Infusionsoft::is_only_name_support( $details['email_provider'] ) && $is_single_name ) ? ' inf_infusionsoft_inline_1_field' : sprintf( ' inf_infusionsoft_inline_%1$s_fields',
				'first_last_name' == $details['name_fields'] && ! INF_Infusionsoft::is_only_name_support( $details['email_provider'] ) ? '3' : '2' ) );

		return $output;
	}

	/**
	 * Displays the Inline shortcode on front-end.
	 */
	function display_inline_shortcode( $atts ) {
		$atts     = shortcode_atts( array(
			'optin_id' => '',
		), $atts );
		$optin_id = $atts['optin_id'];

		$optins_set     = INF_Infusionsoft::get_infusionsoft_options();
		$selected_optin = isset( $optins_set[ $optin_id ] ) ? $optins_set[ $optin_id ] : '';
		$output         = '';

		if ( '' !== $selected_optin && 'active' == $selected_optin['optin_status'] && 'inline' == $selected_optin['optin_type'] && empty( $selected_optin['child_of'] ) ) {
			$output = $this->generate_inline_form( $optin_id, $selected_optin );
		}

		return $output;
	}

	/**
	 * Displays the "locked content" shortcode on front-end.
	 */
	function display_locked_shortcode( $atts, $content = null ) {
		$atts           = shortcode_atts( array(
			'optin_id' => '',
		), $atts );
		$optin_id       = $atts['optin_id'];
		$optins_set     = INF_Infusionsoft::get_infusionsoft_options();
		$selected_optin = isset( $optins_set[ $optin_id ] ) ? $optins_set[ $optin_id ] : '';
		if ( '' == $selected_optin ) {
			$output = $content;
		} else {
			$form    = '';
			$page_id = get_the_ID();
			$list_id = 'custom_html' == $selected_optin['email_provider'] ? 'custom_html' : $selected_optin['email_provider'] . '_' . $selected_optin['email_list'];

			if ( '' !== $selected_optin && 'active' == $selected_optin['optin_status'] && 'locked' == $selected_optin['optin_type'] && empty( $selected_optin['child_of'] ) ) {
				$form = $this->generate_inline_form( $optin_id, $selected_optin, false );
			}

			$output = sprintf( '<div class="inf_infusionsoft_locked_container inf_infusionsoft_%4$s" data-page_id="%3$s" data-optin_id="%4$s" data-list_id="%5$s">
					<div class="inf_infusionsoft_locked_content" style="display: none;">
						%1$s
					</div>
					<div class="inf_infusionsoft_locked_form">
						%2$s
					</div>
				</div>', $content, $form, esc_attr( $page_id ), esc_attr( $optin_id ), esc_attr( $list_id ) );
		}

		return $output;
	}

	function register_widget() {
		require_once( INF_INFUSIONSOFT_PLUGIN_DIR . 'includes/infusionsoft-widget.php' );
		register_widget( 'InfusionsoftWidget' );
	}

	/**
	 * Displays the Widget content on front-end.
	 */
	public static function display_widget( $optin_id ) {
		$optins_set     = INF_Infusionsoft::get_infusionsoft_options();
		$selected_optin = isset( $optins_set[ $optin_id ] ) ? $optins_set[ $optin_id ] : '';
		$output         = '';

		if ( '' !== $selected_optin && 'active' == $optins_set[ $optin_id ]['optin_status'] && empty( $optins_set[ $optin_id ]['child_of'] ) ) {

			$display_optin_id = INF_Infusionsoft::choose_form_ab_test( $optin_id, $optins_set );

			if ( $display_optin_id != $optin_id ) {
				$optin_id       = $display_optin_id;
				$selected_optin = $optins_set[ $optin_id ];
			}

			if ( is_singular() || is_front_page() ) {
				$page_id = is_front_page() ? - 1 : get_the_ID();
			} else {
				$page_id = 0;
			}

			$list_id = $selected_optin['email_provider'] . '_' . $selected_optin['email_list'];

			$custom_css        = INF_Infusionsoft::generate_custom_css( '.inf_infusionsoft .inf_infusionsoft_' . $display_optin_id,
				$selected_optin );
			$custom_css_output = '' !== $custom_css ? sprintf( '<style type="text/css">%1$s</style>',
				$custom_css ) : '';

			INF_Infusionsoft::add_stats_record( 'imp', $optin_id, $page_id, $list_id );

			$output = sprintf( '<div class="inf_infusionsoft_widget_content inf_infusionsoft_optin inf_infusionsoft_%7$s">
					%8$s
					<div class="inf_infusionsoft_form_container inf_infusionsoft_popup_container%2$s%3$s%4$s%5$s%6$s">
						%1$s
					</div>
				</div>', INF_Infusionsoft::generate_form_content( $optin_id, $page_id ),
				'basic_edge' == $selected_optin['edge_style'] || '' == $selected_optin['edge_style'] ? '' : sprintf( ' with_edge %1$s',
					esc_attr( $selected_optin['edge_style'] ) ),
				( 'no_border' !== $selected_optin['border_orientation'] ) ? sprintf( ' inf_infusionsoft_border_%1$s%2$s',
					$selected_optin['border_style'],
					'full' !== $selected_optin['border_orientation'] ? ' inf_infusionsoft_border_position_' . $selected_optin['border_orientation'] : '' ) : '',
				( 'rounded' == $selected_optin['corner_style'] ) ? ' inf_infusionsoft_rounded_corners' : '', //#5
				( 'rounded' == $selected_optin['field_corner'] ) ? ' inf_infusionsoft_rounded' : '',
				'light' == $selected_optin['text_color'] ? ' inf_infusionsoft_form_text_light' : ' inf_infusionsoft_form_text_dark',
				esc_attr( $optin_id ), $custom_css_output //#8
			);
		}

		return $output;
	}

	/**
	 * Returns list of widget optins to generate select option in widget settings
	 * @return array
	 */
	public static function widget_optins_list() {
		$optins_set = INF_Infusionsoft::get_infusionsoft_options();
		$output     = array(
			'empty' => __( 'Select optin', 'infusionsoft' ),
		);

		if ( ! empty( $optins_set ) ) {
			foreach ( $optins_set as $optin_id => $details ) {
				if ( isset( $details['optin_status'] ) && 'active' === $details['optin_status'] && empty( $details['child_of'] ) ) {
					if ( 'widget' == $details['optin_type'] ) {
						$output = array_merge( $output, array( $optin_id => $details['optin_name'] ) );
					}
				}
			}
		} else {
			$output = array(
				'empty' => __( 'No Widget optins created yet', 'infusionsoft' ),
			);
		}

		return $output;
	}

	function set_custom_css() {
		$options_array  = INF_Infusionsoft::get_infusionsoft_options();
		$custom_css     = '';
		$font_functions = INF_Infusionsoft::load_fonts_class();
		$fonts_array    = array();

		foreach ( $options_array as $id => $single_optin ) {
			if ( 'accounts' != $id && 'db_version' != $id && isset( $single_optin['optin_type'] ) ) {
				if ( 'inactive' !== $single_optin['optin_status'] ) {
					$current_optin_id = INF_Infusionsoft::choose_form_ab_test( $id, $options_array, false );
					$single_optin     = $options_array[ $current_optin_id ];

					if ( ( ( 'flyin' == $single_optin['optin_type'] || 'pop_up' == $single_optin['optin_type'] || 'below_post' == $single_optin['optin_type'] ) && $this->check_applicability( $id ) ) && ( isset( $single_optin['custom_css'] ) || isset( $single_optin['form_bg_color'] ) || isset( $single_optin['header_bg_color'] ) || isset( $single_optin['form_button_color'] ) || isset( $single_optin['border_color'] ) ) ) {
						$form_class = '.inf_infusionsoft .inf_infusionsoft_' . $current_optin_id;

						$custom_css .= INF_Infusionsoft::generate_custom_css( $form_class, $single_optin );
					}

					if ( ! isset( $fonts_array[ $single_optin['header_font'] ] ) && isset( $single_optin['header_font'] ) ) {
						$fonts_array[] = $single_optin['header_font'];
					}

					if ( ! isset( $fonts_array[ $single_optin['body_font'] ] ) && isset( $single_optin['body_font'] ) ) {
						$fonts_array[] = $single_optin['body_font'];
					}
				}
			}
		}

		if ( ! empty( $fonts_array ) ) {
			$font_functions->et_gf_enqueue_fonts( $fonts_array );
		}

		if ( '' != $custom_css ) {
			printf( '<style type="text/css" id="rad-infusionsoft-custom-css">
					%1$s
				</style>', stripslashes( $custom_css ) );
		}
	}

	/**
	 * Generated the output for custom css with specified class based on input option
	 * @return string
	 */
	public static function generate_custom_css( $form_class, $single_optin = array() ) {
		$font_functions = INF_Infusionsoft::load_fonts_class();
		$custom_css     = '';

		if ( isset( $single_optin['form_bg_color'] ) && '' !== $single_optin['form_bg_color'] ) {
			$custom_css .= $form_class . ' .inf_infusionsoft_form_content { background-color: ' . $single_optin['form_bg_color'] . ' !important; } ';

			if ( 'zigzag_edge' === $single_optin['edge_style'] ) {
				$custom_css .= $form_class . ' .zigzag_edge .inf_infusionsoft_form_content:before { background: linear-gradient(45deg, transparent 33.33%, ' . $single_optin['form_bg_color'] . ' 33.333%, ' . $single_optin['form_bg_color'] . ' 66.66%, transparent 66.66%), linear-gradient(-45deg, transparent 33.33%, ' . $single_optin['form_bg_color'] . ' 33.33%, ' . $single_optin['form_bg_color'] . ' 66.66%, transparent 66.66%) !important; background-size: 20px 40px !important; } ' . $form_class . ' .zigzag_edge.inf_infusionsoft_form_right .inf_infusionsoft_form_content:before, ' . $form_class . ' .zigzag_edge.inf_infusionsoft_form_left .inf_infusionsoft_form_content:before { background-size: 40px 20px !important; }
					@media only screen and ( max-width: 767px ) {' . $form_class . ' .zigzag_edge.inf_infusionsoft_form_right .inf_infusionsoft_form_content:before, ' . $form_class . ' .zigzag_edge.inf_infusionsoft_form_left .inf_infusionsoft_form_content:before { background: linear-gradient(45deg, transparent 33.33%, ' . $single_optin['form_bg_color'] . ' 33.333%, ' . $single_optin['form_bg_color'] . ' 66.66%, transparent 66.66%), linear-gradient(-45deg, transparent 33.33%, ' . $single_optin['form_bg_color'] . ' 33.33%, ' . $single_optin['form_bg_color'] . ' 66.66%, transparent 66.66%) !important; background-size: 20px 40px !important; } ' . '}';
			}
		}

		if ( isset( $single_optin['header_bg_color'] ) && '' !== $single_optin['header_bg_color'] ) {
			$custom_css .= $form_class . ' .inf_infusionsoft_form_container .inf_infusionsoft_form_header { background-color: ' . $single_optin['header_bg_color'] . ' !important; } ';

			switch ( $single_optin['edge_style'] ) {
				case 'curve_edge' :
					$custom_css .= $form_class . ' .curve_edge .curve { fill: ' . $single_optin['header_bg_color'] . '} ';
					break;

				case 'wedge_edge' :
					$custom_css .= $form_class . ' .wedge_edge .triangle { fill: ' . $single_optin['header_bg_color'] . '} ';
					break;

				case 'carrot_edge' :
					$custom_css .= $form_class . ' .carrot_edge .inf_infusionsoft_form_content:before { border-top-color: ' . $single_optin['header_bg_color'] . ' !important; } ' . $form_class . ' .carrot_edge.inf_infusionsoft_form_right .inf_infusionsoft_form_content:before, ' . $form_class . ' .carrot_edge.inf_infusionsoft_form_left .inf_infusionsoft_form_content:before { border-top-color: transparent !important; border-left-color: ' . $single_optin['header_bg_color'] . ' !important; }
						@media only screen and ( max-width: 767px ) {' . $form_class . ' .carrot_edge.inf_infusionsoft_form_right .inf_infusionsoft_form_content:before, ' . $form_class . ' .carrot_edge.inf_infusionsoft_form_left .inf_infusionsoft_form_content:before { border-top-color: ' . $single_optin['header_bg_color'] . ' !important; border-left-color: transparent !important; }
						}';
					break;
			}

			if ( 'dashed' === $single_optin['border_style'] ) {
				if ( 'breakout_edge' !== $single_optin['edge_style'] ) {
					$custom_css .= $form_class . ' .inf_infusionsoft_form_container { background-color: ' . $single_optin['header_bg_color'] . ' !important; } ';
				} else {
					$custom_css .= $form_class . ' .inf_infusionsoft_header_outer { background-color: ' . $single_optin['header_bg_color'] . ' !important; } ';
				}
			}
		}

		if ( isset( $single_optin['form_button_color'] ) && '' !== $single_optin['form_button_color'] ) {
			$custom_css .= $form_class . ' .inf_infusionsoft_form_content button { background-color: ' . $single_optin['form_button_color'] . ' !important; } ';
		}

		if ( isset( $single_optin['border_color'] ) && '' !== $single_optin['border_color'] && 'no_border' !== $single_optin['border_orientation'] ) {
			if ( 'breakout_edge' === $single_optin['edge_style'] ) {
				switch ( $single_optin['border_style'] ) {
					case 'letter' :
						$custom_css .= $form_class . ' .breakout_edge.inf_infusionsoft_border_letter .inf_infusionsoft_header_outer { background: repeating-linear-gradient( 135deg, ' . $single_optin['border_color'] . ', ' . $single_optin['border_color'] . ' 10px, #fff 10px, #fff 20px, #f84d3b 20px, #f84d3b 30px, #fff 30px, #fff 40px ) !important; } ';
						break;

					case 'double' :
						$custom_css .= $form_class . ' .breakout_edge.inf_infusionsoft_border_double .inf_infusionsoft_form_header { -moz-box-shadow: inset 0 0 0 6px ' . $single_optin['header_bg_color'] . ', inset 0 0 0 8px ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 0 0 0 6px ' . $single_optin['header_bg_color'] . ', inset 0 0 0 8px ' . $single_optin['border_color'] . '; box-shadow: inset 0 0 0 6px ' . $single_optin['header_bg_color'] . ', inset 0 0 0 8px ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['border_color'] . '; } ';

						switch ( $single_optin['border_orientation'] ) {
							case 'top' :
								$custom_css .= $form_class . ' .breakout_edge.inf_infusionsoft_border_double.inf_infusionsoft_border_position_top .inf_infusionsoft_form_header { -moz-box-shadow: inset 0 6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 8px 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 0 6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 8px 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 0 6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 8px 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['border_color'] . '; } ';
								break;

							case 'right' :
								$custom_css .= $form_class . ' .breakout_edge.inf_infusionsoft_border_double.inf_infusionsoft_border_position_right .inf_infusionsoft_form_header { -moz-box-shadow: inset -6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset -8px 0 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset -6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset -8px 0 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset -6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset -8px 0 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['border_color'] . '; } ';
								break;

							case 'bottom' :
								$custom_css .= $form_class . ' .breakout_edge.inf_infusionsoft_border_double.inf_infusionsoft_border_position_bottom .inf_infusionsoft_form_header { -moz-box-shadow: inset 0 -6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 -8px 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 0 -6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 -8px 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 0 -6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 -8px 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['border_color'] . '; } ';
								break;

							case 'left' :
								$custom_css .= $form_class . ' .breakout_edge.inf_infusionsoft_border_double.inf_infusionsoft_border_position_left .inf_infusionsoft_form_header { -moz-box-shadow: inset 6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset 8px 0 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset 8px 0 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset 8px 0 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['border_color'] . '; } ';
								break;

							case 'top_bottom' :
								$custom_css .= $form_class . ' .breakout_edge.inf_infusionsoft_border_double.inf_infusionsoft_border_position_top_bottom .inf_infusionsoft_form_header { -moz-box-shadow: inset 0 6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 8px 0 0 ' . $single_optin['border_color'] . ', inset 0 -6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 -8px 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 0 6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 8px 0 0 ' . $single_optin['border_color'] . ', inset 0 -6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 -8px 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 0 6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 8px 0 0 ' . $single_optin['border_color'] . ', inset 0 -6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 -8px 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['border_color'] . '; } ';
								break;

							case 'left_right' :
								$custom_css .= $form_class . ' .breakout_edge.inf_infusionsoft_border_double.inf_infusionsoft_border_position_left_right .inf_infusionsoft_form_header { -moz-box-shadow: inset 6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset 8px 0 0 0 ' . $single_optin['border_color'] . ', inset -6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset -8px 0 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset 8px 0 0 0 ' . $single_optin['border_color'] . ', inset -6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset -8px 0 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset 8px 0 0 0 ' . $single_optin['border_color'] . ', inset -6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset -8px 0 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['border_color'] . '; } ';
						}
						break;

					case 'inset' :
						$custom_css .= $form_class . ' .breakout_edge.inf_infusionsoft_border_inset .inf_infusionsoft_form_header { -moz-box-shadow: inset 0 0 0 3px ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 0 0 0 3px ' . $single_optin['border_color'] . '; box-shadow: inset 0 0 0 3px ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['header_bg_color'] . '; } ';

						switch ( $single_optin['border_orientation'] ) {
							case 'top' :
								$custom_css .= $form_class . ' .breakout_edge.inf_infusionsoft_border_inset.inf_infusionsoft_border_position_top .inf_infusionsoft_form_header { -moz-box-shadow: inset 0 3px 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 0 3px 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 0 3px 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['header_bg_color'] . '; } ';
								break;

							case 'right' :
								$custom_css .= $form_class . ' .breakout_edge.inf_infusionsoft_border_inset.inf_infusionsoft_border_position_right .inf_infusionsoft_form_header { -moz-box-shadow: inset -3px 0 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset -3px 0 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset -3px 0 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['header_bg_color'] . '; } ';
								break;

							case 'bottom' :
								$custom_css .= $form_class . ' .breakout_edge.inf_infusionsoft_border_inset.inf_infusionsoft_border_position_bottom .inf_infusionsoft_form_header { -moz-box-shadow: inset 0 -3px 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 0 -3px 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 0 -3px 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['header_bg_color'] . '; } ';
								break;

							case 'left' :
								$custom_css .= $form_class . ' .breakout_edge.inf_infusionsoft_border_inset.inf_infusionsoft_border_position_left .inf_infusionsoft_form_header { -moz-box-shadow: inset 3px 0 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 3px 0 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 3px 0 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['header_bg_color'] . '; } ';
								break;

							case 'top_bottom' :
								$custom_css .= $form_class . ' .breakout_edge.inf_infusionsoft_border_inset.inf_infusionsoft_border_position_top_bottom .inf_infusionsoft_form_header { -moz-box-shadow: inset 0 3px 0 0 ' . $single_optin['border_color'] . ', inset 0 -3px 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 0 3px 0 0 ' . $single_optin['border_color'] . ', inset 0 -3px 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 0 3px 0 0 ' . $single_optin['border_color'] . ', inset 0 -3px 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['header_bg_color'] . '; } ';
								break;

							case 'left_right' :
								$custom_css .= $form_class . ' .breakout_edge.inf_infusionsoft_border_inset.inf_infusionsoft_border_position_left_right .inf_infusionsoft_form_header { -moz-box-shadow: inset 3px 0 0 0 ' . $single_optin['border_color'] . ', inset -3px 0 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 3px 0 0 0 ' . $single_optin['border_color'] . ', inset -3px 0 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 3px 0 0 0 ' . $single_optin['border_color'] . ', inset -3px 0 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['header_bg_color'] . '; } ';
						}
						break;

					case 'solid' :
						$custom_css .= $form_class . ' .breakout_edge.inf_infusionsoft_border_solid .inf_infusionsoft_form_header { border-color: ' . $single_optin['border_color'] . ' !important } ';
						break;

					case 'dashed' :
						$custom_css .= $form_class . ' .breakout_edge.inf_infusionsoft_border_dashed .inf_infusionsoft_form_header { border-color: ' . $single_optin['border_color'] . ' !important } ';
						break;
				}
			} else {
				switch ( $single_optin['border_style'] ) {
					case 'letter' :
						$custom_css .= $form_class . ' .inf_infusionsoft_border_letter { background: repeating-linear-gradient( 135deg, ' . $single_optin['border_color'] . ', ' . $single_optin['border_color'] . ' 10px, #fff 10px, #fff 20px, #f84d3b 20px, #f84d3b 30px, #fff 30px, #fff 40px ) !important; } ';
						break;

					case 'double' :
						$custom_css .= $form_class . ' .inf_infusionsoft_border_double { -moz-box-shadow: inset 0 0 0 6px ' . $single_optin['header_bg_color'] . ', inset 0 0 0 8px ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 0 0 0 6px ' . $single_optin['header_bg_color'] . ', inset 0 0 0 8px ' . $single_optin['border_color'] . '; box-shadow: inset 0 0 0 6px ' . $single_optin['header_bg_color'] . ', inset 0 0 0 8px ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['border_color'] . '; } ';

						switch ( $single_optin['border_orientation'] ) {
							case 'top' :
								$custom_css .= $form_class . ' .inf_infusionsoft_border_double.inf_infusionsoft_border_position_top { -moz-box-shadow: inset 0 6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 8px 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 0 6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 8px 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 0 6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 8px 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['border_color'] . '; } ';
								break;

							case 'right' :
								$custom_css .= $form_class . ' .inf_infusionsoft_border_double.inf_infusionsoft_border_position_right { -moz-box-shadow: inset -6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset -8px 0 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset -6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset -8px 0 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset -6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset -8px 0 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['border_color'] . '; } ';
								break;

							case 'bottom' :
								$custom_css .= $form_class . ' .inf_infusionsoft_border_double.inf_infusionsoft_border_position_bottom { -moz-box-shadow: inset 0 -6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 -8px 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 0 -6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 -8px 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 0 -6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 -8px 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['border_color'] . '; } ';
								break;

							case 'left' :
								$custom_css .= $form_class . ' .inf_infusionsoft_border_double.inf_infusionsoft_border_position_left { -moz-box-shadow: inset 6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset 8px 0 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset 8px 0 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset 8px 0 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['border_color'] . '; } ';
								break;

							case 'top_bottom' :
								$custom_css .= $form_class . ' .inf_infusionsoft_border_double.inf_infusionsoft_border_position_top_bottom { -moz-box-shadow: inset 0 6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 8px 0 0 ' . $single_optin['border_color'] . ', inset 0 -6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 -8px 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 0 6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 8px 0 0 ' . $single_optin['border_color'] . ', inset 0 -6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 -8px 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 0 6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 8px 0 0 ' . $single_optin['border_color'] . ', inset 0 -6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 -8px 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['border_color'] . '; } ';
								break;

							case 'left_right' :
								$custom_css .= $form_class . ' .inf_infusionsoft_border_double.inf_infusionsoft_border_position_left_right { -moz-box-shadow: inset 6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset 8px 0 0 0 ' . $single_optin['border_color'] . ', inset -6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset -8px 0 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset 8px 0 0 0 ' . $single_optin['border_color'] . ', inset -6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset -8px 0 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset 8px 0 0 0 ' . $single_optin['border_color'] . ', inset -6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset -8px 0 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['border_color'] . '; } ';
						}
						break;

					case 'inset' :
						$custom_css .= $form_class . ' .inf_infusionsoft_border_inset { -moz-box-shadow: inset 0 0 0 3px ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 0 0 0 3px ' . $single_optin['border_color'] . '; box-shadow: inset 0 0 0 3px ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['header_bg_color'] . '; } ';

						switch ( $single_optin['border_orientation'] ) {
							case 'top' :
								$custom_css .= $form_class . ' .inf_infusionsoft_border_inset.inf_infusionsoft_border_position_top { -moz-box-shadow: inset 0 3px 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 0 3px 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 0 3px 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['header_bg_color'] . '; } ';
								break;

							case 'right' :
								$custom_css .= $form_class . ' .inf_infusionsoft_border_inset.inf_infusionsoft_border_position_right { -moz-box-shadow: inset -3px 0 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset -3px 0 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset -3px 0 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['header_bg_color'] . '; } ';
								break;

							case 'bottom' :
								$custom_css .= $form_class . ' .inf_infusionsoft_border_inset.inf_infusionsoft_border_position_bottom { -moz-box-shadow: inset 0 -3px 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 0 -3px 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 0 -3px 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['header_bg_color'] . '; } ';
								break;

							case 'left' :
								$custom_css .= $form_class . ' .inf_infusionsoft_border_inset.inf_infusionsoft_border_position_left { -moz-box-shadow: inset 3px 0 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 3px 0 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 3px 0 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['header_bg_color'] . '; } ';
								break;

							case 'top_bottom' :
								$custom_css .= $form_class . ' .inf_infusionsoft_border_inset.inf_infusionsoft_border_position_top_bottom { -moz-box-shadow: inset 0 3px 0 0 ' . $single_optin['border_color'] . ', inset 0 -3px 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 0 3px 0 0 ' . $single_optin['border_color'] . ', inset 0 -3px 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 0 3px 0 0 ' . $single_optin['border_color'] . ', inset 0 -3px 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['header_bg_color'] . '; } ';
								break;

							case 'left_right' :
								$custom_css .= $form_class . ' .inf_infusionsoft_border_inset.inf_infusionsoft_border_position_left_right { -moz-box-shadow: inset 3px 0 0 0 ' . $single_optin['border_color'] . ', inset -3px 0 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 3px 0 0 0 ' . $single_optin['border_color'] . ', inset -3px 0 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 3px 0 0 0 ' . $single_optin['border_color'] . ', inset -3px 0 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['header_bg_color'] . '; } ';
						}
						break;

					case 'solid' :
						$custom_css .= $form_class . ' .inf_infusionsoft_border_solid { border-color: ' . $single_optin['border_color'] . ' !important } ';
						break;

					case 'dashed' :
						$custom_css .= $form_class . ' .inf_infusionsoft_border_dashed .inf_infusionsoft_form_container_wrapper { border-color: ' . $single_optin['border_color'] . ' !important } ';
						break;
				}
			}
		}

		$custom_css .= isset( $single_optin['form_button_color'] ) && '' !== $single_optin['form_button_color'] ? $form_class . ' .inf_infusionsoft_form_content button { background-color: ' . $single_optin['form_button_color'] . ' !important; } ' : '';
		$custom_css .= isset( $single_optin['header_font'] ) ? $font_functions->et_gf_attach_font( $single_optin['header_font'],
			$form_class . ' h2, ' . $form_class . ' h2 span, ' . $form_class . ' h2 strong' ) : '';
		$custom_css .= isset( $single_optin['body_font'] ) ? $font_functions->et_gf_attach_font( $single_optin['body_font'],
			$form_class . ' p, ' . $form_class . ' p span, ' . $form_class . ' p strong, ' . $form_class . ' form input, ' . $form_class . ' form button span' ) : '';

		$custom_css .= isset( $single_optin['custom_css'] ) ? ' ' . $single_optin['custom_css'] : '';

		return $custom_css;
	}

	/**
	 * Modifies the URL of post after commenting to trigger the popup after comment
	 * @return string
	 */
	function after_comment_trigger( $location ) {
		$newurl    = $location;
		$newurl    = substr( $location, 0, strpos( $location, '#comment' ) );
		$delimeter = false === strpos( $location, '?' ) ? '?' : '&';
		$params    = 'inf_infusionsoft_popup=true';

		$newurl .= $delimeter . $params;

		return $newurl;
	}

	/**
	 * Generated content for purchase trigger
	 * @return string
	 */
	function add_purchase_trigger() {
		echo '<div class="inf_infusionsoft_after_order"></div>';
	}

	function sync_optin_forms() {
		$optins        = array();
		$options_array = INF_Infusionsoft::get_infusionsoft_options();
		$api_key       = '';
		$app_name      = '';

		if ( isset( $options_array['accounts']['infusionsoft'] ) ) {
			foreach ( $options_array['accounts']['infusionsoft'] as $key => $value ) {
				if ( isset( $value['api_key'] ) ) {
					$api_key = $value['api_key'];
				}
				if ( isset( $value['client_id'] ) ) {
					$app_name = $value['client_id'];
				}
				break;
			}
		}

		if ( ! empty( $api_key ) && ! empty( $app_name ) ) {
			foreach ( $options_array as $key => $value ) {
				if ( 'optin_' == substr( $key, 0, 6 ) && isset( $value['optin_id'] ) ) {
					$optin_id            = str_replace( ".", "", $value['optin_id'] );
					$optins[ $optin_id ] = empty( $value['optin_name'] ) ? '' : $value['optin_name'];
				}
			}

			//include class to get functions below
			if ( ! class_exists( 'infusionsoft_infusionsoft' ) ) {
				require_once( INF_INFUSIONSOFT_PLUGIN_DIR . 'includes/classes/integrations/class.infusionsoft-infusionsoft.php' );
			}

			$infusionsoft = new infusionsoft_infusionsoft();
			$infusionsoft->sync_optins( $app_name, $api_key, $optins );
		}
	}

	function delete_optin( $optin_id ) {
		$options_array = INF_Infusionsoft::get_infusionsoft_options();
		$api_key       = '';
		$app_name      = '';

		if ( isset( $options_array['accounts']['infusionsoft'] ) ) {
			foreach ( $options_array['accounts']['infusionsoft'] as $key => $value ) {
				$api_key  = $value['api_key'];
				$app_name = $value['client_id'];
				break;
			}
		}

		//include class to get functions below
		if ( ! class_exists( 'infusionsoft_infusionsoft' ) ) {
			require_once( INF_INFUSIONSOFT_PLUGIN_DIR . 'includes/classes/integrations/class.infusionsoft-infusionsoft.php' );
		}

		$infusionsoft = new infusionsoft_infusionsoft();
		$infusionsoft->optin_delete( $app_name, $api_key, $optin_id );
	}

	/**
	 * Adds appropriate actions for Flyin, Popup, Below Content to wp_footer,
	 * Adds custom_css function to wp_head
	 * Adds trigger_bottom_mark to the_content filter for Flyin and Popup
	 * Creates arrays with optins for for Flyin, Popup, Below Content to improve the performance during forms displaying
	 */
	function frontend_register_locations() {
		$options_array = INF_Infusionsoft::get_infusionsoft_options();

		if ( ! is_admin() && ! empty( $options_array ) ) {
			add_action( 'wp_head', array( $this, 'set_custom_css' ) );

			$flyin_count    = 0;
			$popup_count    = 0;
			$below_count    = 0;
			$after_comment  = 0;
			$after_purchase = 0;
			$infusion_count = 0;

			foreach ( $options_array as $optin_id => $details ) {
				if ( 'accounts' !== $optin_id ) {
					if ( isset( $details['optin_status'] ) && 'active' === $details['optin_status'] && empty( $details['child_of'] ) ) {
						switch ( $details['optin_type'] ) {
							case 'flyin' :
								if ( 0 === $flyin_count ) {
									add_action( 'wp_footer', array( $this, "display_flyin" ) );
									$flyin_count ++;
								}

								if ( 0 === $after_comment && isset( $details['comment_trigger'] ) && true == $details['comment_trigger'] ) {
									add_filter( 'comment_post_redirect', array( $this, 'after_comment_trigger' ) );
									$after_comment ++;
								}

								if ( 0 === $after_purchase && isset( $details['purchase_trigger'] ) && true == $details['purchase_trigger'] ) {
									add_action( 'woocommerce_thankyou', array( $this, 'add_purchase_trigger' ) );
									$after_purchase ++;
								}

								$this->flyin_optins[ $optin_id ] = $details;
								break;

							case 'pop_up' :
								if ( 0 === $popup_count ) {
									add_action( 'wp_footer', array( $this, "display_popup" ) );
									$popup_count ++;
								}

								if ( 0 === $after_comment && isset( $details['comment_trigger'] ) && true == $details['comment_trigger'] ) {
									add_filter( 'comment_post_redirect', array( $this, 'after_comment_trigger' ) );
									$after_comment ++;
								}

								if ( 0 === $after_purchase && isset( $details['purchase_trigger'] ) && true == $details['purchase_trigger'] ) {
									add_action( 'woocommerce_thankyou', array( $this, 'add_purchase_trigger' ) );
									$after_purchase ++;
								}

								$this->popup_optins[ $optin_id ] = $details;
								break;

							case 'below_post' :
								if ( 0 === $below_count ) {
									add_filter( 'the_content', array( $this, 'display_below_post' ), 9999 );
									add_action( 'woocommerce_after_single_product_summary',
										array( $this, 'display_on_wc_page' ) );
									$below_count ++;
								}

								$this->below_post_optins[ $optin_id ] = $details;
								break;
							case 'infusion' :
								if ( 0 === $infusion_count ) {
									add_action( 'wp_head', array( $this, 'display_infusionbar' ), 9999 );
									$infusion_count ++;
								}

								$this->infusion_optins[ $optin_id ] = $details;
								break;
						}
					}
				}
			}

			if ( 0 < $flyin_count || 0 < $popup_count ) {
				add_filter( 'the_content', array( $this, 'trigger_bottom_mark' ), 9999 );
			}
		}
	}

	function inf_add_footer_text( $text ) {
		return sprintf( __( $text . ' Infusionsoft<sup>&reg;</sup>' ) );
	}

	function execute_footer_text() {
		if ( isset( $_GET['page'] ) ) {
			if ( $_GET['page'] == 'inf_infusionsoft_options' && isset( $_GET['page'] ) ) {
				add_filter( 'admin_footer_text', array( $this, 'inf_add_footer_text' ) );
			}
		}
	}

	/**
	 * Get appropriate error message from API request/response.
	 *
	 * @param $theme_request
	 * @param $response_code
	 *
	 * @param $message_map
	 *
	 * @return string|void
	 */
	public function get_error_message( $theme_request, $response_code, $message_map ) {
		if ( null === $message_map ) {
			$message_map = array(
				"401" => 'Invalid Username or API key'
			);
		}
		if ( is_wp_error( $theme_request ) ) {
			$error_message = $theme_request->get_error_message();

			return $error_message;
		} else {
			switch ( $response_code ) {
				case '401' :
					$error_message = __( $message_map['401'], 'infusionsoft' );

					return $error_message;
				default :
					$error_message = $response_code;

					return $error_message;
			}
		}
	}


}

new INF_Infusionsoft();