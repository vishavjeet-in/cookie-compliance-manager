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
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Add settings page to admin menu.
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'Cookie Compliance Manager', 'cookie-compliance-manager' ),
			__( 'Cookie Compliance Manager', 'cookie-compliance-manager' ),
			'manage_options',
			'wccm-settings',
			array( $this, 'render_settings_page' )
		);
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
	}

	/**
	 * Sanitize settings.
	 *
	 * @param array $input Input values.
	 * @return array Sanitized values.
	 */
	public function sanitize_settings( $input ) {
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

		return $sanitized;
	}

	/**
	 * General section callback.
	 */
	public function general_section_callback() {
		echo '<p>' . esc_html__( 'Configure your cookie compliance banner settings below.', 'cookie-compliance-manager' ) . '</p>';
	}

	/**
	 * Enabled field callback.
	 */
	public function enabled_field_callback() {
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
	public function message_field_callback() {
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
	 * Enqueue admin assets.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( 'settings_page_wccm-settings' !== $hook ) {
			return;
		}

		// Enqueue color picker.
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		// Enqueue custom admin script.
		wp_enqueue_script(
			'wccm-admin',
			WCCM_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery', 'wp-color-picker' ),
			WCCM_VERSION,
			true
		);
	}
}