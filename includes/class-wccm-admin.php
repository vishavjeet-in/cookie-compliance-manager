<?php
/**
 * Admin functionality.
 *
 * @package Cookie_Compliance_Manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin class.
 */
class WCCM_Admin {

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	private static $instance = null;

	/**
	 * Option name.
	 *
	 * @var string
	 */
	private $option_name = 'wccm_settings';

	/**
	 * Get instance of this class.
	 *
	 * @return object Single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}


	/**
	 * Add top-level admin menu and submenus.
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'Cookie Compliance Manager', 'cookie-compliance-manager' ),
			__( 'Cookie Compliance', 'cookie-compliance-manager' ),
			'manage_options',
			'wccm-main',
			array( $this, 'render_settings_page' ),
			'dashicons-shield-alt',
			60
		);
		add_submenu_page(
			'wccm-main',
			__( 'Settings', 'cookie-compliance-manager' ),
			__( 'Settings', 'cookie-compliance-manager' ),
			'manage_options',
			'wccm-main',
			array( $this, 'render_settings_page' )
		);
		add_submenu_page(
			'wccm-main',
			__( 'Cookie Manager Logs', 'cookie-compliance-manager' ),
			__( 'Logs', 'cookie-compliance-manager' ),
			'manage_options',
			'wccm-logs',
			array( $this, 'render_logs_page' )
		);
	}

	/**
	 * Render the logs admin page.
	 */
	public function render_logs_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'cookie-compliance-manager' ) );
		}
			global $wpdb;
			$table = $wpdb->prefix . 'wccm_cookie_logs';
			$paged = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
			$per_page = 20;
			$offset = ( $paged - 1 ) * $per_page;
			$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
			$logs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table ORDER BY date DESC LIMIT %d OFFSET %d", $per_page, $offset ) );

			// Summary cards with icons and time filters
			$accepted_count = 0;
			$rejected_count = 0;
			$week_count = 0;
			$month_count = 0;
			$year_count = 0;
			$now = current_time('timestamp');
			$start_of_week = strtotime('monday this week', $now);
			$start_of_month = strtotime(date('Y-m-01', $now));
			$start_of_year = strtotime(date('Y-01-01', $now));
			if ( $logs ) {
				foreach ( $logs as $log ) {
					$log_time = strtotime($log->date);
					if ( strtolower( $log->status ) === 'accepted' ) $accepted_count++;
					elseif ( strtolower( $log->status ) === 'rejected' ) $rejected_count++;
					if ( $log_time >= $start_of_week ) $week_count++;
					if ( $log_time >= $start_of_month ) $month_count++;
					if ( $log_time >= $start_of_year ) $year_count++;
				}
			}
			$total_count = $total;
			echo '<div class="wrap">';
			echo '<h1>' . esc_html__( 'Cookie Manager Logs', 'cookie-compliance-manager' ) . '</h1>';
			echo '<div class="wccm-cards">';
			// Total Logs
			echo '<div class="wccm-card wccm-card-total">'
. '<div class="wccm-card-row">'
	. '<div class="wccm-card-icon">'
		. '<svg width="32" height="32" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="#0073aa" opacity="0.1"></circle><path d="M12 7v5l3 3" stroke="#0073aa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>'
	. '</div>'
. '</div>'
. '<div class="wccm-card-text">'
	. '<div class="wccm-card-title">' . esc_html__( 'Total Logs', 'cookie-compliance-manager' ) . '</div>'
	. '<div class="wccm-card-value">' . esc_html( $total_count ) . '</div>'
. '</div>'
. '</div>';
// Accepted
echo '<div class="wccm-card wccm-card-accepted">'
. '<div class="wccm-card-row">'
	. '<div class="wccm-card-icon">'
		. '<svg width="32" height="32" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="#27ae60" opacity="0.1"></circle><path d="M7 13l3 3 7-7" stroke="#27ae60" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>'
	. '</div>'
. '</div>'
. '<div class="wccm-card-text">'
	. '<div class="wccm-card-title">' . esc_html__( 'Accepted', 'cookie-compliance-manager' ) . '</div>'
	. '<div class="wccm-card-value">' . esc_html( $accepted_count ) . '</div>'
. '</div>'
. '</div>';
// Rejected
echo '<div class="wccm-card wccm-card-rejected">'
. '<div class="wccm-card-row">'
	. '<div class="wccm-card-icon">'
		. '<svg width="32" height="32" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="#e74c3c" opacity="0.1"></circle><path d="M15 9l-6 6M9 9l6 6" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>'
	. '</div>'
. '</div>'
. '<div class="wccm-card-text">'
	. '<div class="wccm-card-title">' . esc_html__( 'Rejected', 'cookie-compliance-manager' ) . '</div>'
	. '<div class="wccm-card-value">' . esc_html( $rejected_count ) . '</div>'
. '</div>'
. '</div>';
// This Week
echo '<div class="wccm-card wccm-card-week">'
. '<div class="wccm-card-row">'
	. '<div class="wccm-card-icon">'
		. '<svg width="32" height="32" fill="none" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="4" fill="#6c63ff" opacity="0.1"></rect><path d="M8 12h8M8 16h4" stroke="#6c63ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>'
	. '</div>'
. '</div>'
. '<div class="wccm-card-text">'
	. '<div class="wccm-card-title">' . esc_html__( 'This Week', 'cookie-compliance-manager' ) . '</div>'
	. '<div class="wccm-card-value">' . esc_html( $week_count ) . '</div>'
. '</div>'
. '</div>';
// This Month
echo '<div class="wccm-card wccm-card-month">'
. '<div class="wccm-card-row">'
	. '<div class="wccm-card-icon">'
		. '<svg width="32" height="32" fill="none" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="4" fill="#f39c12" opacity="0.1"></rect><path d="M8 8h8v8H8z" stroke="#f39c12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>'
	. '</div>'
. '</div>'
. '<div class="wccm-card-text">'
	. '<div class="wccm-card-title">' . esc_html__( 'This Month', 'cookie-compliance-manager' ) . '</div>'
	. '<div class="wccm-card-value">' . esc_html( $month_count ) . '</div>'
. '</div>'
. '</div>';
// This Year
echo '<div class="wccm-card wccm-card-year">'
. '<div class="wccm-card-row">'
	. '<div class="wccm-card-icon">'
		. '<svg width="32" height="32" fill="none" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="4" fill="#00bcd4" opacity="0.1"></rect><path d="M12 8v8M8 12h8" stroke="#00bcd4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>'
	. '</div>'
. '</div>'
. '<div class="wccm-card-text">'
	. '<div class="wccm-card-title">' . esc_html__( 'This Year', 'cookie-compliance-manager' ) . '</div>'
	. '<div class="wccm-card-value">' . esc_html( $year_count ) . '</div>'
. '</div>'
. '</div>';
			echo '</div>';
			echo '<table id="wccm-logs-datatable" class="wccm-logs-table display">';
			echo '<thead><tr>';
			echo '<th>' . esc_html__( 'Date', 'cookie-compliance-manager' ) . '</th>';
			echo '<th>' . esc_html__( 'Session ID', 'cookie-compliance-manager' ) . '</th>';
			echo '<th>' . esc_html__( 'Status', 'cookie-compliance-manager' ) . '</th>';
			echo '<th>' . esc_html__( 'IP Address', 'cookie-compliance-manager' ) . '</th>';
			echo '<th>' . esc_html__( 'Landing Page', 'cookie-compliance-manager' ) . '</th>';
			echo '<th>' . esc_html__( 'Source', 'cookie-compliance-manager' ) . '</th>';
			echo '<th>' . esc_html__( 'Medium', 'cookie-compliance-manager' ) . '</th>';
			echo '<th>' . esc_html__( 'Campaign', 'cookie-compliance-manager' ) . '</th>';
			echo '<th>' . esc_html__( 'Referrer', 'cookie-compliance-manager' ) . '</th>';
			echo '<th>' . esc_html__( 'Device', 'cookie-compliance-manager' ) . '</th>';
			echo '</tr></thead><tbody>';
			if ( $logs ) {
				foreach ( $logs as $log ) {
					$status_class = 'wccm-status-other';
					if ( strtolower($log->status) === 'accepted' ) $status_class = 'wccm-status-accepted';
					elseif ( strtolower($log->status) === 'rejected' ) $status_class = 'wccm-status-rejected';
					echo '<tr>';
					echo '<td>' . esc_html( $log->date ) . '</td>';
					echo '<td>' . esc_html( $log->session_id ) . '</td>';
					echo '<td class="' . esc_attr($status_class) . '"><span>' . esc_html( ucfirst( $log->status ) ) . '</span></td>';
					echo '<td>' . esc_html( $log->ip_address ) . '</td>';
					echo '<td>' . esc_html( $log->landing_page ) . '</td>';
					echo '<td>' . esc_html( $log->source ) . '</td>';
					echo '<td>' . esc_html( $log->medium ) . '</td>';
					echo '<td>' . esc_html( $log->campaign ) . '</td>';
					echo '<td>' . esc_html( $log->referrer ) . '</td>';
					echo '<td>' . esc_html( $log->device ) . '</td>';
					echo '</tr>';
				}
			} else {
				echo '<tr><td colspan="10" class="wccm-logs-empty">' . esc_html__( 'No logs found.', 'cookie-compliance-manager' ) . '</td></tr>';
			}
			echo '</tbody></table>';

			// Pagination.
			$total_pages = ceil( $total / $per_page );
			if ( $total_pages > 1 ) {
				$base_url = esc_url( admin_url( 'options-general.php?page=wccm-logs' ) );
				echo '<div class="tablenav"><div class="tablenav-pages">';
				for ( $i = 1; $i <= $total_pages; $i++ ) {
					if ( $i === $paged ) {
						echo '<span class="tablenav-page-num current">' . $i . '</span> ';
					} else {
						echo '<a class="tablenav-page-num" href="' . $base_url . '&paged=' . $i . '">' . $i . '</a> ';
					}
				}
				echo '</div></div>';
			}
			echo '</div>';
	}

	/**
	 * Add settings page to admin menu.
	 */
	public function add_settings_page() {
		   // No longer needed; settings page is now a submenu of the top-level menu.
	}

	/**
	 * Register plugin settings.
	 */
	public function register_settings() {
		register_setting(
			'wccm_settings_group',
			$this->option_name,
			array( $this, 'sanitize_settings' )
		);

		// General Settings Section.
		add_settings_section(
			'wccm_general_section',
			__( 'General Settings', 'cookie-compliance-manager' ),
			array( $this, 'general_section_callback' ),
			'wccm-settings'
		);

		// Enable/Disable field.
		add_settings_field(
			'wccm_enabled',
			__( 'Enable Cookie Banner', 'cookie-compliance-manager' ),
			array( $this, 'enabled_field_callback' ),
			'wccm-settings',
			'wccm_general_section'
		);

		// Banner message field.
		add_settings_field(
			'wccm_message',
			__( 'Banner Message', 'cookie-compliance-manager' ),
			array( $this, 'message_field_callback' ),
			'wccm-settings',
			'wccm_general_section'
		);

		// Accept button text field.
		add_settings_field(
			'wccm_accept_text',
			__( 'Accept Button Text', 'cookie-compliance-manager' ),
			array( $this, 'accept_text_field_callback' ),
			'wccm-settings',
			'wccm_general_section'
		);

		// Reject button text field.
		add_settings_field(
			'wccm_reject_text',
			__( 'Reject Button Text', 'cookie-compliance-manager' ),
			array( $this, 'reject_text_field_callback' ),
			'wccm-settings',
			'wccm_general_section'
		);

		// Position field.
		add_settings_field(
			'wccm_position',
			__( 'Banner Position', 'cookie-compliance-manager' ),
			array( $this, 'position_field_callback' ),
			'wccm-settings',
			'wccm_general_section'
		);

		// Background color field.
		add_settings_field(
			'wccm_bg_color',
			__( 'Background Color', 'cookie-compliance-manager' ),
			array( $this, 'bg_color_field_callback' ),
			'wccm-settings',
			'wccm_general_section'
		);

		// Button color field.
		add_settings_field(
			'wccm_button_color',
			__( 'Button Color', 'cookie-compliance-manager' ),
			array( $this, 'button_color_field_callback' ),
			'wccm-settings',
			'wccm_general_section'
		);

		// Cookie Expiry field.
		add_settings_field(
			'wccm_cookie_expiry',
			__( 'Cookie Expiry (days)', 'cookie-compliance-manager' ),
			array( $this, 'cookie_expiry_field_callback' ),
			'wccm-settings',
			'wccm_general_section'
		);

		// GTM Enable field.
		add_settings_field(
			'wccm_gtm_enable',
			__( 'Enable Google Tag Manager Tracking', 'cookie-compliance-manager' ),
			array( $this, 'gtm_enable_field_callback' ),
			'wccm-settings',
			'wccm_general_section'
		);

		// GTM Tag ID field.
		add_settings_field(
			'wccm_gtm_id',
			__( 'Google Tag Manager ID', 'cookie-compliance-manager' ),
			array( $this, 'gtm_id_field_callback' ),
			'wccm-settings',
			'wccm_general_section'
		);
	}

	/**
	 * Cookie Expiry field callback.
	 */
	function cookie_expiry_field_callback() {
		$options = get_option( $this->option_name );
		$value = isset( $options['cookieExpiry'] ) ? intval( $options['cookieExpiry'] ) : 365;
		?>
		<input type="number" id="wccm_cookie_expiry" name="<?php echo esc_attr( $this->option_name ); ?>[cookieExpiry]" value="<?php echo esc_attr( $value ); ?>" min="1" class="small-text" />
		<p class="description"><?php esc_html_e( 'Number of days before the cookie expires.', 'cookie-compliance-manager' ); ?></p>
		<?php
	}

	/**
	 * Sanitize settings.
	 *
	 * @param array $input Input values.
	 * @return array Sanitized values.
	 */
	function sanitize_settings( $input ) {
		$sanitized = array();

		if ( isset( $input['enabled'] ) ) {
			$sanitized['enabled'] = '1';
		} else {
			$sanitized['enabled'] = '0';
		}

		if ( isset( $input['message'] ) ) {
			$sanitized['message'] = sanitize_textarea_field( $input['message'] );
		}

		if ( isset( $input['accept_text'] ) ) {
			$sanitized['accept_text'] = sanitize_text_field( $input['accept_text'] );
		}

		if ( isset( $input['reject_text'] ) ) {
			$sanitized['reject_text'] = sanitize_text_field( $input['reject_text'] );
		}

		if ( isset( $input['position'] ) ) {
			$allowed_positions = array( 'bottom', 'left', 'right' );
			$sanitized['position'] = in_array( $input['position'], $allowed_positions, true ) ? $input['position'] : 'bottom';
		}

		if ( isset( $input['bg_color'] ) ) {
			$sanitized['bg_color'] = sanitize_hex_color( $input['bg_color'] );
		}

		if ( isset( $input['button_color'] ) ) {
			$sanitized['button_color'] = sanitize_hex_color( $input['button_color'] );
		}

		// GTM Enable
		$sanitized['gtm_enable'] = isset( $input['gtm_enable'] ) && $input['gtm_enable'] === '1' ? '1' : '0';

		// GTM ID
		if ( isset( $input['gtm_id'] ) ) {
			$sanitized['gtm_id'] = preg_match( '/^GTM-[A-Z0-9]+$/i', $input['gtm_id'] ) ? strtoupper( sanitize_text_field( $input['gtm_id'] ) ) : '';
		}

		// Cookie Expiry
		if ( isset( $input['cookieExpiry'] ) ) {
			$sanitized['cookieExpiry'] = max( 1, intval( $input['cookieExpiry'] ) );
		} else {
			$sanitized['cookieExpiry'] = 90;
		}

		return $sanitized;
	}

	/**
	 * General section callback.
	 */
	function general_section_callback() {
		echo '<p>' . esc_html__( 'Configure your cookie compliance banner settings below.', 'cookie-compliance-manager' ) . '</p>';
	}

	/**
	 * Enabled field callback.
	 */
	function enabled_field_callback() {
		$options = get_option( $this->option_name );
		$checked = isset( $options['enabled'] ) && '1' === $options['enabled'] ? 'checked' : '';
		?>
		<label for="wccm_enabled">
			<input type="checkbox" id="wccm_enabled" name="<?php echo esc_attr( $this->option_name ); ?>[enabled]" value="1" <?php echo esc_attr( $checked ); ?> />
			<?php esc_html_e( 'Enable cookie compliance banner', 'cookie-compliance-manager' ); ?>
		</label>
		<?php
	}

	/**
	 * Message field callback.
	 */
	function message_field_callback() {
		$options = get_option( $this->option_name );
		$value   = isset( $options['message'] ) ? $options['message'] : '';
		?>
		<textarea id="wccm_message" name="<?php echo esc_attr( $this->option_name ); ?>[message]" rows="3" cols="50" class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
		<p class="description"><?php esc_html_e( 'The message displayed in the cookie banner.', 'cookie-compliance-manager' ); ?></p>
		<?php
	}

	/**
	 * Accept text field callback.
	 */
	public function accept_text_field_callback() {
		$options = get_option( $this->option_name );
		$value   = isset( $options['accept_text'] ) ? $options['accept_text'] : '';
		?>
		<input type="text" id="wccm_accept_text" name="<?php echo esc_attr( $this->option_name ); ?>[accept_text]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
		<?php
	}

	/**
	 * Reject text field callback.
	 */
	public function reject_text_field_callback() {
		$options = get_option( $this->option_name );
		$value   = isset( $options['reject_text'] ) ? $options['reject_text'] : '';
		?>
		<input type="text" id="wccm_reject_text" name="<?php echo esc_attr( $this->option_name ); ?>[reject_text]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
		<?php
	}

	/**
	 * Position field callback.
	 */
	public function position_field_callback() {
		$options  = get_option( $this->option_name );
		$value    = isset( $options['position'] ) ? $options['position'] : 'bottom';
		$positions = array(
			'bottom' => __( 'Bottom (Full Width)', 'cookie-compliance-manager' ),
			'left'   => __( 'Bottom Left', 'cookie-compliance-manager' ),
			'right'  => __( 'Bottom Right', 'cookie-compliance-manager' ),
		);
		?>
		<select id="wccm_position" name="<?php echo esc_attr( $this->option_name ); ?>[position]">
			<?php foreach ( $positions as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Background color field callback.
	 */
	public function bg_color_field_callback() {
		$options = get_option( $this->option_name );
		$value   = isset( $options['bg_color'] ) ? $options['bg_color'] : '#2c3e50';
		?>
		<input type="text" id="wccm_bg_color" name="<?php echo esc_attr( $this->option_name ); ?>[bg_color]" value="<?php echo esc_attr( $value ); ?>" class="wccm-color-picker" />
		<?php
	}

	/**
	 * Button color field callback.
	 */
	public function button_color_field_callback() {
		$options = get_option( $this->option_name );
		$value   = isset( $options['button_color'] ) ? $options['button_color'] : '#27ae60';
		?>
		<input type="text" id="wccm_button_color" name="<?php echo esc_attr( $this->option_name ); ?>[button_color]" value="<?php echo esc_attr( $value ); ?>" class="wccm-color-picker" />
		<?php
	}

	/**
	 * Render settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'cookie-compliance-manager' ) );
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'wccm_settings_group' );
				do_settings_sections( 'wccm-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * GTM Enable field callback.
	 */
	public function gtm_enable_field_callback() {
		$options = get_option( $this->option_name );
		$checked = isset( $options['gtm_enable'] ) && '1' === $options['gtm_enable'] ? 'checked' : '';
		?>
		<label for="wccm_gtm_enable">
			<input type="checkbox" id="wccm_gtm_enable" name="<?php echo esc_attr( $this->option_name ); ?>[gtm_enable]" value="1" <?php echo esc_attr( $checked ); ?> />
			<?php esc_html_e( 'Enable Google Tag Manager Tracking', 'cookie-compliance-manager' ); ?>
		</label>
		<script>
		jQuery(document).ready(function($){
			function toggleGTMIdField() {
				if($('#wccm_gtm_enable').is(':checked')) {
					$('#wccm_gtm_id_row').show();
				} else {
					$('#wccm_gtm_id_row').hide();
				}
			}
			$('#wccm_gtm_enable').on('change', toggleGTMIdField);
			toggleGTMIdField();
		});
		</script>
		<?php
	}

	/**
	 * GTM Tag ID field callback.
	 */
	public function gtm_id_field_callback() {
		$options = get_option( $this->option_name );
		$value = isset( $options['gtm_id'] ) ? $options['gtm_id'] : '';
		?>
		<div id="wccm_gtm_id_row">
			<input type="text" id="wccm_gtm_id" name="<?php echo esc_attr( $this->option_name ); ?>[gtm_id]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" placeholder="GTM-XXXXXXX" />
			<p class="description"><?php esc_html_e( 'Enter your Google Tag Manager ID (e.g., GTM-XXXXXXX).', 'cookie-compliance-manager' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		   // Enqueue on all plugin admin pages (settings and logs)
		   $allowed_hooks = array(
			   'toplevel_page_wccm-main',
			   'cookie-compliance_page_wccm-main',
			   'cookie-compliance_page_wccm-logs',
		   );
		   if ( in_array( $hook, $allowed_hooks, true ) ) {
			   wp_enqueue_style( 'wp-color-picker' );
			   wp_enqueue_style(
				   'wccm-admin',
				   WCCM_PLUGIN_URL . 'assets/css/admin.css',
				   array(),
				   WCCM_VERSION
			   );
			   wp_enqueue_style(
				   'wccm-admin-datatables',
				   WCCM_PLUGIN_URL . 'assets/css/admin-datatables.css',
				   array('wccm-admin'),
				   WCCM_VERSION
			   );
			   // DataTables core and Buttons extension from CDN
			   wp_enqueue_style( 'datatables', 'https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css', array(), '1.13.7' );
			   wp_enqueue_style( 'datatables-buttons', 'https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css', array('datatables'), '2.4.2' );
			   wp_enqueue_script( 'wp-color-picker' );
			   wp_enqueue_script( 'datatables', 'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js', array('jquery'), '1.13.7', true );
			   wp_enqueue_script( 'datatables-buttons', 'https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js', array('datatables'), '2.4.2', true );
			   wp_enqueue_script( 'datatables-buttons-html5', 'https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js', array('datatables-buttons'), '2.4.2', true );
			   wp_enqueue_script( 'datatables-buttons-print', 'https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js', array('datatables-buttons'), '2.4.2', true );
			   wp_enqueue_script(
				   'wccm-admin',
				   WCCM_PLUGIN_URL . 'assets/js/admin.js',
				   array( 'jquery', 'wp-color-picker', 'datatables', 'datatables-buttons', 'datatables-buttons-html5', 'datatables-buttons-print' ),
				   WCCM_VERSION,
				   true
			   );
			   wp_enqueue_script(
				   'wccm-admin-datatables',
				   WCCM_PLUGIN_URL . 'assets/js/admin-datatables.js',
				   array( 'wccm-admin', 'datatables', 'datatables-buttons', 'datatables-buttons-html5', 'datatables-buttons-print' ),
				   WCCM_VERSION,
				   true
			   );
		   }
	}
}