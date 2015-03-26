<?php

/**
 * Anvato Settings
 */

if ( !class_exists( 'Anvato_Settings' ) ) :


class Anvato_Settings {

	// Option storage key names
	const general_settings_key = 'anvato_mcp';
	const player_settings_key = 'anvato_player';
	const analytics_settings_key = 'anvato_analytics';
	const monetization_settings_key = 'anvato_monetization';

	// Storing options placeholders
	public $options = array();

	private $remote_setup = false;

	// Instance Management
	protected static $instance;
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Anvato_Settings;
			//self::$instance->setup_actions(); // !! This can't be here
		}
		return self::$instance;
	}

	protected function __construct() {

		$this->admin_settings();

	}

	/**
	 * Initiate all the functions and actions related to the admin panel
	 */
	private function admin_settings() {

		if (!is_admin()) return;

		// Add the main Anvato Settings page in "Settings"
		add_action( 'admin_menu', function(){
			add_options_page( __( 'Anvato', ANVATO_DOMAIN_SLUG ), 
				__( 'Anvato', ANVATO_DOMAIN_SLUG ), 
				'manage_options',
				ANVATO_DOMAIN_SLUG, 
				array( Anvato_Settings(), 'admin_settings_page_view' ) 
			);
		} );

		// initiate the fields for the form and saving of the items
		add_action( 'admin_init', array( $this, 'admin_settings_page_setup' ) );

		// add settings link in the plugin activation panel, if available
		if (has_action('plugin_action_links')) {

			add_filter( 'plugin_action_links', function ( $links, $file ) {
				if( $file === 'wp-anvato-plugin/anvato.php' && function_exists( "admin_url" ) ) {
					// Insert option for "Settings" before other links for Anvato Plugin
					array_unshift(
						$links, 
						'<a href="' . esc_url(admin_url( 'options-general.php?page=' . ANVATO_DOMAIN_SLUG )) . '">' . __( 'Settings', ANVATO_DOMAIN_SLUG ) . '</a>'
					);
				}
				return $links;
			}, 10, 2 );

		}
	}

	/**
	 * Setup all the tabs, links and fields for the admin panel
	 */
	public function admin_settings_page_setup() {

		if (!is_admin()) return;

		// Player Options

		$this->plugin_settings_tabs[self::player_settings_key] = "Player";
		register_setting( 
			self::player_settings_key, 
			self::player_settings_key, 
			array( self::$instance, 'sanitize_options', )
		);
			add_settings_section( 
				'section_player', 
				__( 'Player Settings', ANVATO_DOMAIN_SLUG ), 
				function() {
					echo '<hr/>';
				}, 
				self::player_settings_key 
			);
			add_settings_field( 
				'player_url', 
				__( 'Player URL*:', ANVATO_DOMAIN_SLUG ), 
				array( 'Anvato_Form_Fields', 'field' ), 
				self::player_settings_key, 
				'section_player', 
				array( 
					'name' => self::player_settings_key . '[player_url]',
					'value' => $this->get_option( self::player_settings_key, 'player_url' ),
				)
			);
			add_settings_field( 
				'height', 
				__( 'Height:', ANVATO_DOMAIN_SLUG ), 
				array( 'Anvato_Form_Fields', 'field' ), 
				self::player_settings_key, 
				'section_player', 
				array(
					'name' => self::player_settings_key . '[height]',
					'value' => $this->get_option( self::player_settings_key, 'height' ),
					'size' => 4,
					'after_field' => ' px',
				) 
			);
			add_settings_field( 
				'width', 
				__( 'Width:', ANVATO_DOMAIN_SLUG ), 
				array( 'Anvato_Form_Fields', 'field' ), 
				self::player_settings_key, 
				'section_player', 
				array(
					'name' => self::player_settings_key . '[width]',
					'value' => $this->get_option( self::player_settings_key, 'width' ),
					'size' => 4,
					'after_field' => ' px',
				) 
			);


		// Analytics

		$this->plugin_settings_tabs[self::analytics_settings_key] = "Analytics";
		register_setting( 
			self::analytics_settings_key, 
			self::analytics_settings_key, 
			array( self::$instance, 'sanitize_options', )
		);
			// Anvato Analytics Block
			add_settings_section(
				'section_anvato_analytics', 
				__( 'Anvato Analytics Settings', ANVATO_DOMAIN_SLUG ), 
				function() {
					echo '<hr/>';
				}, 
				self::analytics_settings_key
			);
				add_settings_field(
					'tracker_id', 
					__( 'Tracker ID:', ANVATO_DOMAIN_SLUG ), 
					array( 'Anvato_Form_Fields', 'field' ),
					self::analytics_settings_key, 
					'section_anvato_analytics', 
					array( 
						'name' => self::analytics_settings_key . '[tracker_id]',
						'value' => $this->get_option( self::analytics_settings_key, 'tracker_id' ),
					)
				);
			// Adobe Analytics Block
			add_settings_section( 
				'section_adobe_analytics', 
				__( 'Adobe Analytics Settings', ANVATO_DOMAIN_SLUG ), 
				function() {
					echo '<hr/>';
				}, 
				self::analytics_settings_key
			);
				add_settings_field( 
					'adobe_profile', 
					__( 'Profile:', ANVATO_DOMAIN_SLUG ), 
					array( 'Anvato_Form_Fields', 'field' ),
					self::analytics_settings_key, 
					'section_adobe_analytics', 
					array( 
						'name' => self::analytics_settings_key . '[adobe_profile]',
						'value' => $this->get_option( self::analytics_settings_key, 'adobe_profile' ),
					)
				);
				add_settings_field( 
					'adobe_account', 
					__( 'Account:', ANVATO_DOMAIN_SLUG ), 
					array( 'Anvato_Form_Fields', 'field' ),
					self::analytics_settings_key, 
					'section_adobe_analytics', 
					array( 
						'name' => self::analytics_settings_key . '[adobe_account]',
						'value' => $this->get_option( self::analytics_settings_key, 'adobe_account' ),
					)
				);
				add_settings_field( 
					'adobe_trackingserver', 
					__( 'Tracking Server:', ANVATO_DOMAIN_SLUG ), 
					array( 'Anvato_Form_Fields', 'field' ),
					self::analytics_settings_key, 
					'section_adobe_analytics', 
					array( 
						'name' => self::analytics_settings_key . '[adobe_trackingserver]',
						'value' => $this->get_option( self::analytics_settings_key, 'adobe_trackingserver' ),
					)
				);
			// Heartbeet Analytics Block
			add_settings_section( 
				'section_heartbeet_analytics', 
				__( 'Heartbeat Analytics Settings', ANVATO_DOMAIN_SLUG ), 
				function() {
					echo '<hr/>';
				}, 
				self::analytics_settings_key
			);
				add_settings_field( 
					'heartbeat_account_id', 
					__( 'Account ID:', ANVATO_DOMAIN_SLUG ), 
					array( 'Anvato_Form_Fields', 'field' ),
					self::analytics_settings_key, 
					'section_heartbeet_analytics', 
					array( 
						'name' => self::analytics_settings_key . '[heartbeat_account_id]',
						'value' => $this->get_option( self::analytics_settings_key, 'heartbeat_account_id' ),
					)
				);
				add_settings_field( 
					'heartbeat_publisher_id', 
					__( 'Publisher ID:', ANVATO_DOMAIN_SLUG ), 
					array( 'Anvato_Form_Fields', 'field' ),
					self::analytics_settings_key, 
					'section_heartbeet_analytics', 
					array( 
						'name' => self::analytics_settings_key . '[heartbeat_publisher_id]',
						'value' => $this->get_option( self::analytics_settings_key, 'heartbeat_publisher_id' ),
					)
				);
				add_settings_field( 
					'heartbeat_job_id', 
					__( 'Job ID:', ANVATO_DOMAIN_SLUG ), 
					array( 'Anvato_Form_Fields', 'field' ),
					self::analytics_settings_key, 
					'section_heartbeet_analytics', 
					array( 
						'name' => self::analytics_settings_key . '[heartbeat_job_id]',
						'value' => $this->get_option( self::analytics_settings_key, 'heartbeat_job_id' ),
					)
				);
				add_settings_field( 
					'heartbeat_marketing_id', 
					__( 'Cloud ID:', ANVATO_DOMAIN_SLUG ), 
					array( 'Anvato_Form_Fields', 'field' ),
					self::analytics_settings_key, 
					'section_heartbeet_analytics', 
					array( 
						'name' => self::analytics_settings_key . '[heartbeat_marketing_id]',
						'value' => $this->get_option( self::analytics_settings_key, 'heartbeat_marketing_id' ),
					)
				);
				add_settings_field( 
					'heartbeat_tracking_server', 
					__( 'Traking Server:', ANVATO_DOMAIN_SLUG ), 
					array( 'Anvato_Form_Fields', 'field' ),
					self::analytics_settings_key, 
					'section_heartbeet_analytics', 
					array( 
						'name' => self::analytics_settings_key . '[heartbeat_tracking_server]',
						'value' => $this->get_option( self::analytics_settings_key, 'heartbeat_tracking_server' ),
					)
				);
			// Comscore Analytics Block
			add_settings_section( 
				'section_comscore_analytics', 
				__( 'Comscore Analytics Settings', ANVATO_DOMAIN_SLUG ), 
				function() {
					echo '<hr/>';
				}, 
				self::analytics_settings_key
			);
				add_settings_field( 
					'comscore_client_id',
					__( 'Client ID:', ANVATO_DOMAIN_SLUG ), 
					array( 'Anvato_Form_Fields', 'field' ),
					self::analytics_settings_key, 
					'section_comscore_analytics', 
					array( 
						'name' => self::analytics_settings_key . '[comscore_client_id]',
						'value' => $this->get_option( self::analytics_settings_key, 'comscore_client_id' ),
					)
				);


		// Monetization options

		$this->plugin_settings_tabs[self::monetization_settings_key] = "Monetization";
		register_setting( 
			self::monetization_settings_key, 
			self::monetization_settings_key, 
			array( self::$instance, 'sanitize_options', )
		);
			add_settings_section( 
				'section_monetization', 
				__( 'Monetization Settings', ANVATO_DOMAIN_SLUG ), 
				function() {
					echo '<hr/>';
				}, 
				self::monetization_settings_key 
			);
				add_settings_field( 
					'adtag', 
					__( 'DFP Premium Ad Tag:', ANVATO_DOMAIN_SLUG ), 
					array( 'Anvato_Form_Fields', 'field' ),
					self::monetization_settings_key, 
					'section_monetization', 
					array( 
						'name' => self::monetization_settings_key . '[adtag]',
						'value' => $this->get_option( self::monetization_settings_key, 'adtag' ),
					)
				);
				add_settings_field( 
					'advanced_targeting', 
					__( 'Advanced Targeting:', ANVATO_DOMAIN_SLUG ), 
					array( 'Anvato_Form_Fields', 'field' ),
					self::monetization_settings_key, 
					'section_monetization', 
					array( 
						'name' => self::monetization_settings_key . '[advanced_targeting]',
						'value' => $this->get_option( self::monetization_settings_key, 'advanced_targeting' ),
					)
				);


		// Access Options

		$mcp_settings_value = $this->get_option ( self::general_settings_key, 'mcp_config' );

		/*
			If there are no core settings for the plugin, 
			we should not show ANY other tabs, until the core settings a setup.

			The fields are still decalred and can be used through WordPress Settings API,
			but the tabs are not supposed to show
		*/
		if (empty($mcp_settings_value)) {
			$this->plugin_settings_tabs = array();
		}

		$this->plugin_settings_tabs[self::general_settings_key] = "Access";
		register_setting( 
			self::general_settings_key, 
			self::general_settings_key, 
			array( self::$instance, 'sanitize_options', ) 
		);
			add_settings_section(
				'section_mcp', 
				__( 'MCP Settings', ANVATO_DOMAIN_SLUG ), 
				function(){
					echo '<hr/>';
				}, 
				self::general_settings_key
			);
			if ( empty ( $mcp_settings_value ) ) { // only use this field when there is no "mcp_config" setup
				add_settings_field( 
					'mcp_config_automatic_key', 
					__( 'Auto Configuration Key:', ANVATO_DOMAIN_SLUG ), 
					array( 'Anvato_Form_Fields', 'field' ),
					self::general_settings_key, 
					'section_mcp', 
					array( 
						'name' => self::general_settings_key . '[mcp_config_automatic_key]',
						'value' => '', // value for this key shoudl always be blank!
					)
				);
			}
				add_settings_field( 
					'mcp_config', 
					__( 'API Configuration:', ANVATO_DOMAIN_SLUG ), 
					array( 'Anvato_Form_Fields', 'textarea' ),
					self::general_settings_key, 
					'section_mcp',
					array( 
						'name' => self::general_settings_key . '[mcp_config]',
						'value' => $mcp_settings_value,
					)
				);
		
	} // admin options setup

	/**
	 * Function to display and manage settings on the admin page
	 */
	public function admin_settings_page_view() {

		if (!is_admin()) return;

		?>

		<?php
			reset( $this->plugin_settings_tabs ); // reset array pointer
			$active_tab = key( $this->plugin_settings_tabs );
			if ( !empty( $_GET['tab'] ) ) {
				$active_tab = $_GET['tab'];
			}
		?>
		<div class="wrap">
			<h2><img src="<?php echo esc_url( ANVATO_URL . 'img/logo.png' ) ?>" alt="<?php esc_attr_e( 'Anvato Video Plugin Settings', ANVATO_DOMAIN_SLUG ); ?>" /></h2>

			<?php if( $this->remote_setup_state === TRUE) : ?>
				<div id="message" class="updated">
					<p>
						<strong>Your plugin is successfully setup.</strong>
					</p>
				</div>
			<?php endif; ?>

			<p>Anvato Wordpress Plugin allows Anvato Media Content Platform customers to easily insert players into posts that play video on demand clips as well as live channels.</p>

			<?php screen_icon(); ?>
			<h2 class="nav-tab-wrapper">
				<?php foreach ( $this->plugin_settings_tabs as $key => $name ) : ?>
					<?php
						$tab_class = array('nav-tab');
						if ($active_tab == $key) $tab_class[] = 'nav-tab-active';
					?>
					<a class="<?php echo esc_attr(implode(' ', $tab_class)); ?>" href="<?php echo esc_url(admin_url('options-general.php?page=' . ANVATO_DOMAIN_SLUG . '&tab=' . $key)); ?>"><?php echo esc_html($name); ?></a>
				<?php endforeach; ?>
			</h2>

			<form method="post" action="options.php">
				<?php wp_nonce_field( 'anvato-update-options' ); ?>
				<?php settings_fields( $active_tab ); ?>
				<?php do_settings_sections( $active_tab ); ?>

				<hr/>

				<?php submit_button(); ?>
			</form>

		</div>

		<?php
	}
	
	/**
	 * General "Sanitize" function.
	 * Uses wp's sanitize_text_field https://codex.wordpress.org/Function_Reference/sanitize_text_field
	 *
	 * @param string $dirty
	 * @return string
	 */
	function sanitize_options( $dirty ) {
		$clean = array();

		// if its not array, make it into one
		if ( !is_array($dirty) ) {
			$dirty = (array) $dirty;
		}
		$clean = array_map('sanitize_text_field', $dirty);
		
		/*
			Special case for clearing and managing MCP settings.

			The general idea is to check if the "mcp_config" key is present, but empty.
			If that is the case
				- clear the key
				- delete the database refference
				- redirect to the clean version of the same page
					- Redirect is done to prevent the automatic WP's setup from creating a default empty DB line

			This way if you ever decide to disable the plugin, 
			just delete all the data from the "MCP Settings -> API Configuration" block
			and the rest should be handled here.
		*/
		if ( array_key_exists( 'mcp_config', $clean ) ) {
			
			if ( !empty( $clean['mcp_config_automatic_key'] ) ) {
				// For when we can do automatic setup from the 64bit key

				$autoconfigkey_decoded = json_decode( base64_decode( $clean['mcp_config_automatic_key'] ) , true );
				unset($clean['mcp_config_automatic_key']); // remove the auto-key. Don't store it

				if ( !empty( $autoconfigkey_decoded['b'] ) && !empty( $autoconfigkey_decoded['k'] ) ) {
					$result_response = wp_remote_get(
						'https://' . $autoconfigkey_decoded['b'] . '.s3.amazonaws.com/wordpress/conf/' . $autoconfigkey_decoded['k']
					);

					// We don't need transient, since this will not be used often.

					if ( !is_wp_error( $result_response ) ) {
						$result = wp_remote_retrieve_body( $result_response );

						if ( !empty( $result ) ) {
							$result = json_decode( $result, TRUE );

							// Set automatic Main settings
							if ( !empty( $result['mcp'] ) ) {

								// Set automatic Player settings
								if ( !empty( $result['player'] ) ) {
									// this will not overwrite existing options!
									add_option( self::player_settings_key,  $result['player'], '', 'no' );
									unset( $result['player'] );
								}

								// set MCP main settings to everything without the player settings
								// should include "mcp" and "owners" settings
								$clean['mcp_config'] = sanitize_text_field( json_encode($result) );

							} // if not empty mcp_config
						}

					} // end if erro with retrieval of auto settings

				} // end if the key had proper parts

			} else if ( empty( $clean['mcp_config'] ) ) {
				// for when the mcp_config is empty

				delete_option( self::general_settings_key ); // delete the database item

				// and then we want to get out of the process altogether and not create a DB item
				wp_redirect(admin_url(
					'options-general.php?page=' . ANVATO_DOMAIN_SLUG . 
					'&tab=' . self::general_settings_key 
				));
				exit; // terminate advancing further, so the redirect works proper

			}

		} // end of special case for MCP settings

		return $clean;
	}
	







	public function get_options( $key = null ) {

		$this->general_settings = (array) get_option( self::general_settings_key );
		$this->player_settings = (array) get_option( self::player_settings_key );
		$this->analytics_settings = (array) get_option( self::analytics_settings_key );
		$this->monetization_settings = (array) get_option( self::monetization_settings_key );
		
		// Merge with defaults
		$this->options[self::player_settings_key] = array_merge( array( 'section_player' => 'Player' ), $this->player_settings );
		$this->options[self::analytics_settings_key] = array_merge( array( 'section_analytics' => 'Analytics' ), $this->analytics_settings );
		$this->options[self::monetization_settings_key] = array_merge( array( 'section_analytics' => 'Monetization' ), $this->monetization_settings );
		$this->options[self::general_settings_key ] = array_merge( array( 'section_mcp' => 'Access' ), $this->general_settings );

		return $key == null ? $this->options : $this->options[$key];

	}

	public function get_option( $key , $field) 
	{
		if(sizeof($this->options) === 0 )
			$this->get_options();
		
		return isset( $this->options[ $key ][ $field ] ) ? $this->options[ $key ][ $field ] : null;
	}

	static function get_mcp_options() {
		$settings = (array) get_option( self::general_settings_key );
		$json = json_decode($settings['mcp_config'], TRUE);
		return $json;
	}


} // end of Anvato_Settings class



/**
 * Object to store and manage form-field items for Anvato Settings page
 */
class Anvato_Form_Fields {
	
	/**
	 * Generate Input form field
	 */
	static function field( $args ) {
		if ( empty( $args['name'] ) ) return;

		$args = wp_parse_args( $args, array(
			'size' => 50,
			'value' => '',
			'placeholder' => '',
			'after_field' => '',
		) );

		printf ( 
			'<input type="%s" name="%s" placeholder="%s" size="%s" value="%s" />%s',
			esc_attr( $args['type'] ),
			esc_attr( $args['name'] ),
			esc_attr( $args['placeholder'] ),
			esc_attr( $args['size'] ),
			esc_attr( $args['value'] ),
			esc_attr( $args['after_field'] )
		);
	} 

	/**
	 * Generate TextArea form field
	 */
	static function textarea( $args ) {
		if ( empty( $args['name'] ) ) return;

		printf (
			'<textarea name="%s" class="large-text code" rows="15">%s</textarea>', 
			esc_attr( $args['name'] ),
			esc_attr( $args['value'] )
		);
	}

}

/**
 * Get handle for Anvato Settings class
 *
 * return object
 */
function Anvato_Settings() {
	return Anvato_Settings::instance();
}
add_action( 'after_setup_theme', 'Anvato_Settings' );


endif; // if not class "Anvato_Settings" exists