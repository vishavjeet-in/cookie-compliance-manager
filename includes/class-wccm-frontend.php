<?php
/**
 * Frontend functionality.
 *
 * @package Cookie_Compliance_Manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Frontend class.
 */
class WCCM_Frontend {
	/**
	 * The single instance of the class.
	 *
	 * @var WCCM_Frontend|null
	 */
	private static $instance = null;

	/**
	 * Text domain constant.
	 *
	 * @var string
	 */
	const TEXT_DOMAIN = 'cookie-compliance-manager';

	/**
	 * Get the singleton instance.
	 *
	 * @return WCCM_Frontend
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Options array.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->options = get_option( 'wccm_settings', array() );

		if ( $this->is_banner_enabled() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
			add_action( 'wp_footer', array( $this, 'render_cookie_banner' ) );
			add_action( 'wp_ajax_nopriv_wccm_store_consent', array( $this, 'ajax_store_consent' ) );
			add_action( 'wp_ajax_wccm_store_consent', array( $this, 'ajax_store_consent' ) );

			// GTM output if enabled
			if ( $this->is_gtm_enabled() ) {
				add_action( 'wp_head', array( $this, 'output_gtm_head' ), 1 );
			}
		}
	}

	/**
	 * Check if banner is enabled.
	 *
	 * @return bool
	 */
	private function is_banner_enabled() {
		return isset( $this->options['enabled'] ) && '1' === $this->options['enabled'];
	}

	/**
	 * Check if GTM is enabled.
	 *
	 * @return bool
	 */
	private function is_gtm_enabled() {
		return isset( $this->options['gtm_enable'] ) 
			&& '1' === $this->options['gtm_enable'] 
			&& ! empty( $this->options['gtm_id'] );
	}

	/**
	 * Get sanitized GTM ID.
	 *
	 * @return string
	 */
	private function get_gtm_id() {
		return isset( $this->options['gtm_id'] ) ? sanitize_text_field( $this->options['gtm_id'] ) : '';
	}

	/**
	 * Check if user has accepted cookies.
	 *
	 * @return bool
	 */
	private function has_consent() {
		return isset( $_COOKIE['wccm_cookie_consent'] ) && 'accepted' === $_COOKIE['wccm_cookie_consent'];
	}

	/**
	 * Output GTM script in head with consent defaults.
	 */
	public function output_gtm_head() {
		$gtm_id = $this->get_gtm_id();
		
		if ( empty( $gtm_id ) ) {
			return;
		}

		if ( $this->has_consent() ) {
			$this->inject_gtm_full_code();
		} else {
			$this->output_gtm_consent_defaults();
		}
	}

	/**
	 * Output GTM consent defaults (denied state).
	 */
	private function output_gtm_consent_defaults() {
		?>
		<script>
			window.dataLayer = window.dataLayer || [];
			function gtag(){dataLayer.push(arguments);}
			gtag("consent", "default", {
				ad_storage: "denied",
				analytics_storage: "denied",
				ad_user_data: "denied",
				ad_personalization: "denied",
				functionality_storage: "denied",
				security_storage: "denied",
				personalization_storage: "denied"
			});
		</script>
		<?php
	}

	/**
	 * Enqueue frontend assets.
	 */
	public function enqueue_frontend_assets() {
		// Enqueue CSS.
		wp_enqueue_style(
			'wccm-frontend',
			WCCM_PLUGIN_URL . 'assets/css/frontend.min.css',
			array(),
			WCCM_VERSION
		);

		// Enqueue JS.
		wp_enqueue_script(
			'wccm-frontend',
			WCCM_PLUGIN_URL . 'assets/js/frontend.js',
			array( 'jquery' ),
			WCCM_VERSION,
			true
		);

		// Pass settings to JavaScript.
		$gtm_id       = $this->get_gtm_id();
		$cookie_expiry = isset( $this->options['cookieExpiry'] ) ? absint( $this->options['cookieExpiry'] ) : 90;
		
		wp_localize_script(
			'wccm-frontend',
			'wccmSettings',
			array(
				'cookieName'   => 'wccm_cookie_consent',
				'cookieExpiry' => $cookie_expiry,
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( 'wccm_cookie_consent' ),
				'gtmId'        => $gtm_id,
			)
		);

		// Add inline styles for customization.
		$custom_css = $this->get_custom_css();
		wp_add_inline_style( 'wccm-frontend', $custom_css );
	}

	/**
	 * AJAX handler to store consent log.
	 */
	public function ajax_store_consent() {
		// Verify nonce.
		check_ajax_referer( 'wccm_cookie_consent', 'nonce' );

		// Validate required fields.
		if ( empty( $_POST['session_id'] ) ) {
			wp_send_json_error( array( 'message' => 'Missing session ID' ), 400 );
		}

		if ( empty( $_POST['status'] ) ) {
			wp_send_json_error( array( 'message' => 'Missing status' ), 400 );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'wccm_cookie_logs';

		// Sanitize all inputs.
		$session_id = sanitize_text_field( wp_unslash( $_POST['session_id'] ) );
		$status     = sanitize_text_field( wp_unslash( $_POST['status'] ) );
		$ip_address = $this->get_client_ip();
		$landing    = isset( $_POST['landing_page'] ) ? esc_url_raw( wp_unslash( $_POST['landing_page'] ) ) : '';
		$source     = isset( $_POST['source'] ) ? sanitize_text_field( wp_unslash( $_POST['source'] ) ) : '';
		$medium     = isset( $_POST['medium'] ) ? sanitize_text_field( wp_unslash( $_POST['medium'] ) ) : '';
		$campaign   = isset( $_POST['campaign'] ) ? sanitize_text_field( wp_unslash( $_POST['campaign'] ) ) : '';
		$referrer   = isset( $_POST['referrer'] ) ? esc_url_raw( wp_unslash( $_POST['referrer'] ) ) : '';
		$device     = isset( $_POST['device'] ) ? sanitize_text_field( wp_unslash( $_POST['device'] ) ) : '';

		// Insert into database.
		$result = $wpdb->insert(
			$table,
			array(
				'date'         => current_time( 'mysql' ),
				'session_id'   => $session_id,
				'status'       => $status,
				'ip_address'   => $ip_address,
				'landing_page' => $landing,
				'source'       => $source,
				'medium'       => $medium,
				'campaign'     => $campaign,
				'referrer'     => $referrer,
				'device'       => $device,
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( false === $result ) {
			wp_send_json_error( array( 'message' => 'Failed to store consent log' ), 500 );
		}

		wp_send_json_success( array( 'message' => 'Consent stored successfully' ) );
	}

	/**
	 * Get client IP address safely.
	 *
	 * @return string
	 */
	private function get_client_ip() {
		$ip_address = '';

		if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip_address = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		// Validate IP address.
		if ( filter_var( $ip_address, FILTER_VALIDATE_IP ) ) {
			return $ip_address;
		}

		return '';
	}

	/**
	 * Get custom CSS based on settings.
	 *
	 * @return string
	 */
	public function get_custom_css() {
		$bg_color     = isset( $this->options['bg_color'] ) ? sanitize_hex_color( $this->options['bg_color'] ) : '#2c3e50';
		$button_color = isset( $this->options['button_color'] ) ? sanitize_hex_color( $this->options['button_color'] ) : '#27ae60';

		// Fallback if sanitization fails.
		$bg_color     = $bg_color ? $bg_color : '#2c3e50';
		$button_color = $button_color ? $button_color : '#27ae60';

		$css = "
			.wccm-cookie-banner {
				background-color: {$bg_color};
			}
			.wccm-accept-btn {
				background-color: {$button_color};
			}
			.wccm-accept-btn:hover {
				background-color: {$button_color};
				opacity: 0.9;
			}
		";

		return $css;
	}

	/**
	 * Injects the full GTM code into the head.
	 */
	public function inject_gtm_full_code() {
		$gtm_id = $this->get_gtm_id();
		
		if ( empty( $gtm_id ) ) {
			return;
		}
		
		// Enqueue the GTM script with unique handle.
		wp_enqueue_script(
			'wccm-gtm-script',
			'https://www.googletagmanager.com/gtag/js?id=' . esc_attr( $gtm_id ),
			array(),
			WCCM_VERSION,
			false
		);
		
		// Add inline initialization code.
		$inline_script = sprintf(
			'window.dataLayer = window.dataLayer || [];
			function gtag(){dataLayer.push(arguments);}
			gtag("js", new Date());
			gtag("config", "%s");
			gtag("consent", "update", {
				ad_storage: "granted",
				analytics_storage: "granted",
				ad_user_data: "granted",
				ad_personalization: "granted",
				functionality_storage: "granted",
				security_storage: "granted",
				personalization_storage: "granted"
			});',
			esc_js( $gtm_id )
		);
		
		wp_add_inline_script( 'wccm-gtm-script', $inline_script );
	}

	/**
	 * Render cookie banner.
	 */
	public function render_cookie_banner() {
		$message     = isset( $this->options['message'] ) ? wp_kses_post( $this->options['message'] ) : __( 'We use cookies to ensure you get the best experience on our website.', self::TEXT_DOMAIN );
		$accept_text = isset( $this->options['accept_text'] ) ? sanitize_text_field( $this->options['accept_text'] ) : __( 'Accept', self::TEXT_DOMAIN );
		$reject_text = isset( $this->options['reject_text'] ) ? sanitize_text_field( $this->options['reject_text'] ) : __( 'Reject', self::TEXT_DOMAIN );
		$position    = isset( $this->options['position'] ) ? sanitize_text_field( $this->options['position'] ) : 'bottom';

		// Validate position value.
		$allowed_positions = array( 'top', 'bottom', 'left', 'right' );
		if ( ! in_array( $position, $allowed_positions, true ) ) {
			$position = 'bottom';
		}

		$position_class = 'wccm-position-' . esc_attr( $position );
		?>
		<div id="wccm-cookie-banner" class="wccm-cookie-banner <?php echo esc_attr( $position_class ); ?>" style="display: none;">
			<div class="wccm-banner-content">
				<div class="wccm-banner-message">
					<p><?php echo $message; // Already escaped with wp_kses_post above. ?></p>
				</div>
				<div class="wccm-banner-buttons">
					<button type="button" class="wccm-accept-btn" id="wccm-accept-btn">
						<?php echo esc_html( $accept_text ); ?>
					</button>
					<button type="button" class="wccm-reject-btn" id="wccm-reject-btn">
						<?php echo esc_html( $reject_text ); ?>
					</button>
				</div>
			</div>
		</div>
		<?php
	}
}