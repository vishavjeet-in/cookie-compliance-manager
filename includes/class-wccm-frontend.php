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
	 * Instance of this class.
	 *
	 * @var object
	 */
	private static $instance = null;

	/**
	 * Plugin options.
	 *
	 * @var array
	 */
	private $options;

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
		$this->options = get_option( 'wccm_settings' );

		if ( $this->is_banner_enabled() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
			add_action( 'wp_footer', array( $this, 'render_cookie_banner' ) );
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
			WCCM_PLUGIN_URL . 'assets/js/frontend.min.js',
			array( 'jquery' ),
			WCCM_VERSION,
			true
		);

		// Pass settings to JavaScript.
		wp_localize_script(
			'wccm-frontend',
			'wccmSettings',
			array(
				'cookieName'   => 'wccm_cookie_consent',
				'cookieExpiry' => 365,
			)
		);

		// Add inline styles for customization.
		$custom_css = $this->get_custom_css();
		wp_add_inline_style( 'wccm-frontend', $custom_css );
	}

	/**
	 * Get custom CSS based on settings.
	 *
	 * @return string
	 */
	private function get_custom_css() {
		$bg_color     = isset( $this->options['bg_color'] ) ? $this->options['bg_color'] : '#2c3e50';
		$button_color = isset( $this->options['button_color'] ) ? $this->options['button_color'] : '#27ae60';

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
	 * Render cookie banner.
	 */
	public function render_cookie_banner() {
		$message     = isset( $this->options['message'] ) ? $this->options['message'] : __( 'We use cookies to ensure you get the best experience on our website.', 'cookie-compliance-manager' );
		$accept_text = isset( $this->options['accept_text'] ) ? $this->options['accept_text'] : __( 'Accept', 'cookie-compliance-manager' );
		$reject_text = isset( $this->options['reject_text'] ) ? $this->options['reject_text'] : __( 'Reject', 'cookie-compliance-manager' );
		$position    = isset( $this->options['position'] ) ? $this->options['position'] : 'bottom';

		$position_class = 'wccm-position-' . esc_attr( $position );
		?>
		<div id="wccm-cookie-banner" class="wccm-cookie-banner <?php echo esc_attr( $position_class ); ?>" style="display: none;">
			<div class="wccm-banner-content">
				<div class="wccm-banner-message">
					<p><?php echo esc_html( $message ); ?></p>
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