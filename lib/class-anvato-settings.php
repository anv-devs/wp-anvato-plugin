<?php

/**
 * Anvato Settings
 */

if ( ! class_exists( 'Anvato_Settings' ) ) :

	class Anvato_Settings {

		// Option storage key names
		const ANALYTICS_SETTINGS_KEY    = 'anvato_analytics';
		const AUTOMATIC_SETUP_KEY       = 'anvato_plugin_setup';
		const GENERAL_SETTINGS_KEY      = 'anvato_mcp';
		const MONETIZATION_SETTINGS_KEY = 'anvato_monetization';
		const PLAYER_SETTINGS_KEY       = 'anvato_player';

		public $options = array();

		private $remote_setup = false;

		private $translations = array();

		private function register_translations() {
			$this->translations = array(
				'section_setup'                  => __( 'Welcome to Automatic Setup', 'wp_anvato' ),
				'section_player'                 => __( 'Player Settings', 'wp_anvato' ),
				'section_anvato_analytics'       => __( 'Anvato Analytics Settings', 'wp_anvato' ),
				'section_monetization'           => __( 'Monetization Settings', 'wp_anvato' ),
				'section_mcp'                    => __( 'MCP Settings', 'wp_anvato' ),
				'section_adobe_analytics'        => __( 'Adobe Analytics Settings', 'wp_anvato' ),
				'section_heartbeat_analytics'    => __( 'Heartbeat Analytics Setting', 'wp_anvato' ),
				'section_comscore_analytics'     => __( 'Comscore Analytics Settings', 'wp_anvato' ),
				'section_google_analytics'       => __( 'Google Analytics Settings', 'wp_anvato' ),
				'mcp_config_automatic_key'       => __( 'Auto Configuration Key', 'wp_anvato' ),
				'player_url'                     => __( 'Player URL', 'wp_anvato' ),
				'default_share_link'             => __( 'Default Share Link', 'wp_anvato' ),
				'title_visible'                  => __( 'Title Visible', 'wp_anvato' ),
				'player_parameters'              => __( 'Embed Parameters', 'wp_anvato' ),
				'height'                         => __( 'Height', 'wp_anvato' ),
				'width'                          => __( 'Width', 'wp_anvato' ),
				'tracker_id'                     => __( 'Tracker ID', 'wp_anvato' ),
				'adobe_account'                  => __( 'Account', 'wp_anvato' ),
				'adobe_trackingserver'           => __( 'Tracking Server', 'wp_anvato' ),
				'heartbeat_account_id'           => __( 'Account Info', 'wp_anvato' ),
				'heartbeat_publisher_id'         => __( 'Publisher ID', 'wp_anvato' ),
				'heartbeat_job_id'               => __( 'Job ID', 'wp_anvato' ),
				'heartbeat_marketing_id'         => __( 'Cloud ID', 'wp_anvato' ),
				'heartbeat_tracking_server'      => __( 'Tracking Server', 'wp_anvato' ),
				'heartbeat_cstm_tracking_server' => __( 'Custom Tracking Server:', 'wp_anvato' ),
				'heartbeat_version'              => __( 'Version', 'wp_anvato' ),
				'chapter_tracking'               => __( 'Chapter Tracking', 'wp_anvato' ),
				'comscore_client_id'             => __( 'Client ID', 'wp_anvato' ),
				'comscore_c3'                    => __( 'C3 Value', 'wp_anvato' ),
				'google_account_id'              => __( 'Account Info', 'wp_anvato' ),
				'adtag'                          => __( 'GAM Ad Tag', 'wp_anvato' ),
				'advanced_targeting'             => __( 'Advanced Targeting', 'wp_anvato' ),
				'mcp_config'                     => __( 'API Configuration', 'wp_anvato' ),
				'reset_settings'                 => __( 'Reset settings', 'wp_anvato' ),
			);
		}

		// Plugin Fields
		private $plugin_fields = array(
			self::AUTOMATIC_SETUP_KEY       => array(
				array(
					'id'       => 'section_setup',
					'title'    => 'Welcome to Automatic Setup',
					'callback' => array( 'Anvato_Callbacks', 'anvato_automatic_setup_desc' ),
					'fields'   => array(
						array(
							'id'       => 'mcp_config_automatic_key',
							'title'    => 'Auto Configuration Key',
							'no_value' => 1,
						),
					),
				),
			),
			self::PLAYER_SETTINGS_KEY       => array(
				array(
					'id'       => 'section_player',
					'title'    => 'Player Settings',
					'callback' => array( 'Anvato_Callbacks', 'anvato_html_line' ),
					'fields'   => array(
						array(
							'id'           => 'player_url',
							'title'        => 'Player URL',
							'is_mandatory' => true,
						),
						array(
							'id'    => 'default_share_link',
							'title' => 'Default Share Link:',
						),
						array(
							'id'    => 'title_visible',
							'title' => 'Title Visible',
						),
						array(
							'id'       => 'height',
							'title'    => 'Height',
							'args'     => array(
								'size'         => 4,
								'select_field' => 'height_type',
								'parent'       => self::PLAYER_SETTINGS_KEY,
								'after_text'   => false,
							),
							'callback' => array( 'Anvato_Form_Fields', 'hw_field' ),
						),
						array(
							'id'       => 'width',
							'title'    => 'Width',
							'args'     => array(
								'size'         => 4,
								'select_field' => 'width_type',
								'parent'       => self::PLAYER_SETTINGS_KEY,
								'after_text'   => false,
							),
							'callback' => array( 'Anvato_Form_Fields', 'hw_field' ),
						),
						array(
							'id'       => 'player_parameters',
							'title'    => 'Embed Parameters',
							'callback' => array( 'Anvato_Form_Fields', 'textarea' ),
							'args'     => array(
								'rows' => 5,
							),
						),
					),
				),
			),
			self::ANALYTICS_SETTINGS_KEY    => array(
				// Anvato Analytics
				array(
					'id'       => 'section_anvato_analytics',
					'title'    => 'Anvato Analytics Settings',
					'callback' => array( 'Anvato_Callbacks', 'anvato_html_line' ),
					'fields'   => array(
						array(
							'id'    => 'tracker_id',
							'title' => 'Tracker ID',
						),
					),
				),
				// Adobe Analytics
				array(
					'id'       => 'section_adobe_analytics',
					'title'    => 'Adobe Analytics Settings',
					'callback' => array( 'Anvato_Callbacks', 'anvato_html_line' ),
					'fields'   => array(
						array(
							'id'    => 'adobe_account',
							'title' => 'Account',
						),
						array(
							'id'    => 'adobe_trackingserver',
							'title' => 'Tracking Server',
						),
					),
				),
				// Heartbeat Analytics Block
				array(
					'id'       => 'section_heartbeat_analytics',
					'title'    => 'Heartbeat Analytics Settings',
					'callback' => array( 'Anvato_Callbacks', 'anvato_html_line' ),
					'fields'   => array(
						array(
							'id'       => 'heartbeat_account_id',
							'title'    => 'Account Info',
							'callback' => array( 'Anvato_Form_Fields', 'textarea' ),
							'args'     => array(
								'rows'     => 5,
								'cols'     => 48,
								'cs_class' => '',
							),
						),
						array(
							'id'    => 'heartbeat_publisher_id',
							'title' => 'Publisher ID',
						),
						array(
							'id'    => 'heartbeat_job_id',
							'title' => 'Job ID',
						),
						array(
							'id'    => 'heartbeat_marketing_id',
							'title' => 'Cloud ID',
						),
						array(
							'id'    => 'heartbeat_tracking_server',
							'title' => 'Tracking Server',
						),
						array(
							'id'    => 'heartbeat_cstm_tracking_server',
							'title' => 'Custom Tracking Server',
						),
						array(
							'id'    => 'heartbeat_version',
							'title' => 'Version',
						),
						array(
							'id'    => 'chapter_tracking',
							'title' => 'Chapter Tracking',
						),
					),
				),
				// Comscore Analytics
				array(
					'id'       => 'section_comscore_analytics',
					'title'    => 'Comscore Analytics Settings',
					'callback' => array( 'Anvato_Callbacks', 'anvato_html_line' ),
					'fields'   => array(
						array(
							'id'    => 'comscore_client_id',
							'title' => 'Client ID',
						),
						array(
							'id'    => 'comscore_c3',
							'title' => 'C3 Value',
						),
					),
				),
				// Google Analytics
				array(
					'id'       => 'section_google_analytics',
					'title'    => 'Google Analytics Settings',
					'callback' => array( 'Anvato_Callbacks', 'anvato_html_line' ),
					'fields'   => array(
						array(
							'id'       => 'google_account_id',
							'title'    => 'Account Info',
							'callback' => array( 'Anvato_Form_Fields', 'textarea' ),
							'args'     => array(
								'rows'     => 5,
								'cols'     => 48,
								'cs_class' => '',
							),
						),
					),
				),
			),
			self::MONETIZATION_SETTINGS_KEY => array(
				array(
					'id'       => 'section_monetization',
					'title'    => 'Monetization Settings',
					'callback' => array( 'Anvato_Callbacks', 'anvato_html_line' ),
					'fields'   => array(
						array(
							'id'    => 'adtag',
							'title' => 'GAM Ad Tag',
						),
						array(
							'id'       => 'advanced_targeting',
							'title'    => 'Advanced Targeting',
							'callback' => array( 'Anvato_Form_Fields', 'textarea' ),
						),
					),
				),
			),
			self::GENERAL_SETTINGS_KEY      => array(
				array(
					'id'       => 'section_mcp',
					'title'    => 'MCP Settings',
					'callback' => array( 'Anvato_Callbacks', 'anvato_html_line' ),
					'fields'   => array(
						array(
							'id'       => 'mcp_config',
							'title'    => 'API Configuration',
							'callback' => array( 'Anvato_Form_Fields', 'textarea' ),
						),
						array(
							'id'       => 'reset_settings',
							'title'    => 'Reset settings',
							'callback' => array( 'Anvato_Form_Fields', 'reset_check' ),
							'no_value' => 1,
						),
					),
				),
			),
		);

		// Instance Management
		protected static $instance;

		protected function __construct() {
			$this->admin_settings();
		}

		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new Anvato_Settings;
			}
			return self::$instance;
		}

		/**
		 * Initiate all the functions and actions related to the admin panel
		 */
		private function admin_settings() {
			if ( ! is_admin() ) {
				return;
			}

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

			// Add the main Anvato Settings page in "Settings"
			add_action( 'admin_menu', array( 'Anvato_Callbacks', 'anvato_admin_menu' ) );

			// initiate the fields for the form and saving of the items
			add_action( 'admin_init', array( $this, 'admin_settings_page_setup' ) );

			// add settings link in the plugin activation panel, if available
			if ( has_action( 'plugin_action_links' ) ) {
				add_filter( 'plugin_action_links', array( 'Anvato_Callbacks', 'anvato_action_links' ), 10, 2 );
			}
		}

		/**
		 * Enqueue scripts for the admin.
		 */
		public function enqueue_admin_scripts() {
			wp_enqueue_script(
				'anvato-common-js',
				ANVATO_URL . 'lib/common.js',
				array( 'jquery' ),
				'0.1.5'
			);
		}

		/**
		 * Setup all the tabs, links and fields for the admin panel
		 */
		public function admin_settings_page_setup() {
			if ( ! is_admin() ) {
				return;
			}

			$this->register_translations();

			$setup_key_value = $this->get_option( self::AUTOMATIC_SETUP_KEY, 0 );
			/*
				If there are no setup key for the plugin,
				we should not show ANY other tabs, until the core settings is setup.
				When setup finished, setup key will be false
			 */
			if ( empty( $setup_key_value ) ) {
				$this->plugin_settings_tabs                              = array();
				$this->plugin_settings_tabs[ self::AUTOMATIC_SETUP_KEY ] = __( 'Plugin Setup', 'wp_anvato' );
				$this->create_settings_section( self::AUTOMATIC_SETUP_KEY );
				$this->remote_setup = true;
			} else {
				// Player Tab
				$this->plugin_settings_tabs                              = array();
				$this->plugin_settings_tabs[ self::PLAYER_SETTINGS_KEY ] = __( 'Player', 'wp_anvato' );
				$this->create_settings_section( self::PLAYER_SETTINGS_KEY );

				// Analytics Tab
				$this->plugin_settings_tabs[ self::ANALYTICS_SETTINGS_KEY ] = __( 'Analytics', 'wp_anvato' );
				$this->create_settings_section( self::ANALYTICS_SETTINGS_KEY );

				// Monetization Tab
				$this->plugin_settings_tabs[ self::MONETIZATION_SETTINGS_KEY ] = __( 'Monetization', 'wp_anvato' );
				$this->create_settings_section( self::MONETIZATION_SETTINGS_KEY );

				// Access Tab
				$this->plugin_settings_tabs[ self::GENERAL_SETTINGS_KEY ] = __( 'Access', 'wp_anvato' );
				$this->create_settings_section( self::GENERAL_SETTINGS_KEY );
			}
		}

		// admin options setup
		private function create_settings_section( $key ) {

			$plugin_sections = $this->plugin_fields[ $key ];
			foreach ( $plugin_sections as $section ) {
				register_setting( $key, $key, array( $this, 'sanitize_options' ) );
				add_settings_section(
					$section['id'],
					$this->translations[ $section['id'] ],
					$section['callback'],
					$key
				);

				foreach ( $section['fields'] as $field ) {
					if ( empty( $field['args'] ) ) {
						$field['args'] = array();
					}

					$args = array_merge(
						array(
							'name'  => "{$key}[{$field['id']}]",
							'value' => empty( $field['no_value'] ) ? $this->get_option( $key, $field['id'] ) : '',
						),
						$field['args']
					);

					$callback = ! empty( $field['callback'] ) ? $field['callback'] : array( 'Anvato_Form_Fields', 'field' );

					add_settings_field(
						$field['id'],
						$this->translations[ $field['id'] ],
						$callback,
						$key,
						$section['id'],
						$args
					);
				}
			}
		}

		private function do_autosetup( $key ) {

			$autoconfigkey_decoded = json_decode( base64_decode( $key ), true );
			if ( empty( $autoconfigkey_decoded['b'] ) || empty( $autoconfigkey_decoded['k'] ) ) {
				return 0;
			}

			$result_response = wp_safe_remote_get( "http://{$autoconfigkey_decoded['b']}.s3.amazonaws.com/wordpress/conf/{$autoconfigkey_decoded['k']}" );

			// We don't need transient, since this will not be used often.
			if ( is_wp_error( $result_response ) ) {
				return 0;
			}

			$response_data = wp_remote_retrieve_body( $result_response );
			if ( empty( $response_data ) ) {
				return 0;
			}

			$result = json_decode( $response_data, true );
			if ( empty( $result['player'] ) || empty( $result['mcp'] ) || empty( $result['owners'] ) ) {
				return 0;
			}

			// Set automatic Player settings
			// this will not overwrite existing options!
			add_option( self::PLAYER_SETTINGS_KEY, $result['player'], '', 'no' );
			unset( $result['player'] );

			// set MCP main settings to everything without the player settings
			//"mcp" and "owners" should be stored
			// You have to delete before add option because there is no upsert support on option store.
			delete_option( self::GENERAL_SETTINGS_KEY );
			add_option( self::GENERAL_SETTINGS_KEY, array( 'mcp_config' => wp_json_encode( $result ) ), '', 'no' );

			return 1;
		}

		/**
		 * Function to display and manage settings on the admin page
		 */
		public function admin_settings_page_view() {
			if ( isset( $_GET['_wpnonce'] ) && ! wp_verify_nonce( sanitize_text_field( $_GET['_wpnonce'] ), 'anvato-tab-action' ) ) {
				return;
			}

			if ( ! is_admin() ) {
				return;
			}

			reset( $this->plugin_settings_tabs ); // reset array pointer
			$active_tab = key( $this->plugin_settings_tabs );
			if ( ! empty( $_GET['tab'] ) ) {
				$active_tab = sanitize_text_field( $_GET['tab'] );
			}

			?>
		<div class="wrap">
			<h2>
				<img src="<?php echo esc_url( ANVATO_URL . 'img/logo_small.png' ); ?>" alt="<?php esc_attr_e( 'Anvato Video Plugin Settings', 'wp_anvato' ); ?>" />
			</h2>

			<?php if ( get_query_var( 'setup-state', '0' ) ) { ?>
				<div id="message" class="updated">
					<p>
						<strong>Your plugin is successfully setup.</strong>
					</p>
				</div>
			<?php } ?>
			<div id="anv_msg_board" class="error" style="display: none"><p></p></div>
			<?php if ( isset( $_GET['auto_err'] ) && '1' === sanitize_text_field( $_GET['auto_err'] ) ) { ?>
				<div class="error">
					<p>
						<strong>Incorrect key provided, please provide correct key or set up manually.</strong>
					</p>
				</div>
			<?php } ?>

			<p><strong>Anvato WordPress Plugin</strong>  allows <strong>Anvato Media Content Platform</strong> customers to easily insert players into posts that play video on demand clips as well as live channels and feeds.</p>

			<h2 class="nav-tab-wrapper">
				<?php
				foreach ( $this->plugin_settings_tabs as $key => $name ) {
					$tab_class = array( 'nav-tab' );
					if ( $active_tab === $key ) {
						$tab_class[] = 'nav-tab-active';
					}
					?>
					<a class="<?php echo esc_attr( implode( ' ', $tab_class ) ); ?>" href="<?php echo esc_url( wp_nonce_url( admin_url( 'options-general.php?page=' . 'wp_anvato' . '&tab=' . $key ), 'anvato-tab-action' ) ); ?>">
					<?php echo esc_html( $name ); ?>
					</a>
				<?php } ?>
			</h2>
			<form method="post" action="options.php">
				<?php
					wp_nonce_field( 'anvato-update-options' );
					settings_fields( $active_tab );
					do_settings_sections( $active_tab );
				?>
				<hr/>
				<?php
				if ( $this->remote_setup ) {
					$attr = array( 'onClick' => 'return check_auto_setup_key()' );
					submit_button( 'Automated Setup', 'primary large', 'remote_setup', false, $attr );
					submit_button( 'Manual Setup', 'secondary large', 'manual_setup', false );
				} else {
					$attr = array();
					if ( 'anvato_player' === $active_tab ) {
						$attr = array( 'onClick' => 'return validate_hw_fields()' );
					}
					submit_button( 'Save Changes', 'primary', 'submit', false, $attr );
				}
				?>
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
		public function sanitize_options( $dirty ) {
			$clean = array();
			// if its not array, make it into one
			if ( ! is_array( $dirty ) ) {
				$dirty = (array) $dirty;
			}
			$clean = array_map( 'sanitize_text_field', $dirty );

			if ( array_key_exists( 'mcp_config_automatic_key', $clean ) ) {
				$result      = 1;
				$extra_param = '';
				if ( ! empty( $clean['mcp_config_automatic_key'] ) ) {
					// For when we can do automatic setup from the 64bit key
					$result = $this->do_autosetup( $clean['mcp_config_automatic_key'] );

					if ( ! $result ) {
						$extra_param = '&auto_err=1';
					}
				} else {
					$extra_param = '&tab=anvato_mcp';
				}

				delete_option( self::AUTOMATIC_SETUP_KEY );

				if ( $result ) {
					add_option( self::AUTOMATIC_SETUP_KEY, $result, '', 'no' );
					unset( $clean['mcp_config_automatic_key'] );
				}

				// and then we want to get out of the process altogether and not create a DB item
				wp_redirect(
					esc_url_raw(
						admin_url(
							'options-general.php?page=' . 'wp_anvato' .
							'&setup-state=' . $result . $extra_param
						)
					)
				);
				exit; // terminate advancing further, so the redirect works proper
			} elseif ( array_key_exists( 'reset_settings', $clean ) ) {
				/*
				 * Check the reset action was stored. If yes, clean up all stored settings and then
				 * redirect user to automatic setup screen
				 */
				delete_option( self::GENERAL_SETTINGS_KEY );
				delete_option( self::PLAYER_SETTINGS_KEY );
				delete_option( self::MONETIZATION_SETTINGS_KEY );
				delete_option( self::ANALYTICS_SETTINGS_KEY );
				delete_option( self::AUTOMATIC_SETUP_KEY );

				wp_redirect(
					esc_url_raw(
						admin_url(
							'options-general.php?page=' . 'wp_anvato' .
							'&reset-success=true'
						)
					)
				);
				exit; // terminate advancing further, so the redirect works proper
			}

			$hw_limits = array(
				'h_abs_min' => 100,
				'w_abs_min' => 100,
				'h_rel_min' => 1,
				'w_rel_min' => 1,
				'h_abs_max' => 1000,
				'w_abs_max' => 1000,
				'h_rel_max' => 100,
				'w_rel_max' => 100,
				'h_abs_def' => 640,
				'w_abs_def' => 640,
				'h_rel_def' => 100,
				'w_rel_def' => 100,
			);

			//First height...
			$max_val = '%' === $clean['height_type'] ? $hw_limits['h_rel_max'] : $hw_limits['h_abs_max'];
			$min_val = '%' === $clean['height_type'] ? $hw_limits['h_rel_min'] : $hw_limits['h_abs_min'];
			$def_val = '%' === $clean['height_type'] ? $hw_limits['h_rel_def'] : $hw_limits['h_abs_def'];
			$height  = $def_val;
			if ( isset( $clean['height_type'] ) ) {
				if ( ctype_digit( $clean['height'] ) && (int) $clean['height'] >= $min_val && (int) $clean['height'] <= $max_val ) {
					$height = $clean['height'];
				}
			}
			$clean['height'] = $height;

			//Now width...
			$max_val = '%' === $clean['width_type'] ? $hw_limits['w_rel_max'] : $hw_limits['w_abs_max'];
			$min_val = '%' === $clean['width_type'] ? $hw_limits['w_rel_min'] : $hw_limits['w_abs_min'];
			$def_val = '%' === $clean['width_type'] ? $hw_limits['w_rel_def'] : $hw_limits['w_abs_def'];
			$width   = $def_val;
			if ( isset( $clean['width_type'] ) ) {
				if ( ctype_digit( $clean['width'] ) && (int) $clean['width'] >= $min_val && (int) $clean['width'] <= $max_val ) {
					$width = $clean['width'];
				}
			}
			$clean['width'] = $width;

			return $clean;
		}

		public function get_options( $key = null ) {
			$this->general_settings      = (array) get_option( self::GENERAL_SETTINGS_KEY );
			$this->player_settings       = (array) get_option( self::PLAYER_SETTINGS_KEY );
			$this->analytics_settings    = (array) get_option( self::ANALYTICS_SETTINGS_KEY );
			$this->monetization_settings = (array) get_option( self::MONETIZATION_SETTINGS_KEY );

			// Merge with defaults
			$this->options[ self::PLAYER_SETTINGS_KEY ]       = array_merge( array( 'section_player' => __( 'Player', 'wp_anvato' ) ), $this->player_settings );
			$this->options[ self::ANALYTICS_SETTINGS_KEY ]    = array_merge( array( 'section_analytics' => __( 'Analytics', 'wp_anvato' ) ), $this->analytics_settings );
			$this->options[ self::MONETIZATION_SETTINGS_KEY ] = array_merge( array( 'section_analytics' => __( 'Monetization', 'wp_anvato' ) ), $this->monetization_settings );
			$this->options[ self::GENERAL_SETTINGS_KEY ]      = array_merge( array( 'section_mcp' => __( 'Access', 'wp_anvato' ) ), $this->general_settings );
			$this->options[ self::AUTOMATIC_SETUP_KEY ]       = (array) get_option( self::AUTOMATIC_SETUP_KEY );

			return ! isset( $key ) ? $this->options : $this->options[ $key ];
		}

		public function get_option( $key, $field ) {
			if ( sizeof( $this->options ) === 0 ) {
				$this->get_options();
			}

			return isset( $this->options[ $key ][ $field ] ) ? $this->options[ $key ][ $field ] : null;
		}

		static function get_mcp_options() {
			$settings = (array) get_option( self::GENERAL_SETTINGS_KEY );
			if ( empty( $settings['mcp_config'] ) ) {
				return null;
			}

			$json = json_decode( $settings['mcp_config'], true );
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
			if ( empty( $args['name'] ) ) {
				return;
			}

			$args = wp_parse_args(
				$args,
				array(
					'type'        => 'text',
					'size'        => 50,
					'value'       => '',
					'placeholder' => '',
					'after_field' => '',
				)
			);

			printf(
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
		 * Generates html for height and width field with a selecti option next to it for px/%
		 *
		 * @param array $args
		 */
		static function hw_field( $args ) {
			if ( empty( $args['name'] ) ) {
				return;
			}

			$args = wp_parse_args(
				$args,
				array(
					'type'         => 'text',
					'size'         => 50,
					'value'        => '',
					'options'      => array( 'px', '%' ),
					'select_field' => 'select_hw',
					'parent'       => 'anvato_player',
					'placeholder'  => '',
				)
			);

			$sel_val = anvato_settings()->get_option( $args['parent'], $args['select_field'] );
			if ( ! $sel_val ) {
				$sel_val = 'px';
			}

			$args['after_field'] = sprintf(
				'<select name="%s">',
				esc_attr( $args['parent'] . '[' . $args['select_field'] . ']' )
			);

			foreach ( $args['options'] as $option ) {
				$selected             = $sel_val === $option ? ' selected ' : ' ';
				$args['after_field'] .= sprintf( '<option%svalue="%s">%s</option>', $selected, $option, $option );
			}

			$args['after_field'] .= '</select>';
			if ( isset( $args['after_text'] ) && $args['after_text'] ) {
				$args['after_field'] .= '<p class="disabled">&nbsp;&nbsp;' . $args['after_text'] . '</p>';
			}

			printf(
				'<input type="%s" name="%s" placeholder="%s" size="%s" value="%s" />%s',
				esc_attr( $args['type'] ),
				esc_attr( $args['name'] ),
				esc_attr( $args['placeholder'] ),
				esc_attr( $args['size'] ),
				esc_attr( $args['value'] ),
				wp_kses(
					$args['after_field'],
					array(
						'select' => array(
							'name' => array(),
						),
						'option' => array(
							'value'    => array(),
							'selected' => array(),
						),
						'p'      => array(
							'class' => array(),
						),
					)
				)
			);
		}

		/**
		 * Generate TextArea form field
		 */
		static function textarea( $args ) {
			if ( empty( $args['name'] ) ) {
				return;
			}

			$rows = 15;
			if ( isset( $args['rows'] ) && (int) $args['rows'] > 0 ) {
				$rows = (int) $args['rows'];
			}

			$cols = '';
			if ( isset( $args['cols'] ) && (int) $args['cols'] > 0 ) {
				$cols = ' cols="' . (int) $args['cols'] . '"';
			}

			$cs_class = 'large-text code';
			if ( isset( $args['cs_class'] ) ) {
				$cs_class = $args['cs_class'];
			}

			printf(
				'<textarea name="%s" class="' . esc_attr( $cs_class ) . '" rows="' . esc_attr( $rows ) . '"' . esc_attr( $cols ) . '>%s</textarea>',
				esc_attr( $args['name'] ),
				esc_textarea( $args['value'] )
			);
		}

		static function reset_check( $args ) {
			printf(
				'<input type="checkbox" name="%s" value="1" onclick="if(this.checked) return confirm(\'This will erase all plugin settings. Are you sure?\');" />',
				esc_attr( $args['name'] )
			);
		}

	}

	/**
	 * There is no support for anonymous functions on PHP 5.2.4+ hence all anonymous
	 * functions was collected in this class.
	 * see more for WP requirements : https://wordpress.org/about/requirements/
	 */

	class Anvato_Callbacks {

		static function anvato_admin_menu() {
			add_options_page(
				__( 'Anvato', 'wp_anvato' ),
				__( 'Anvato', 'wp_anvato' ),
				'manage_options',
				'wp_anvato',
				array( Anvato_Settings(), 'admin_settings_page_view' )
			);
		}

		static function anvato_action_links( $links, $file ) {
			if ( 'wp-anvato-plugin/anvato.php' === $file && function_exists( 'admin_url' ) ) {
				// Insert option for "Settings" before other links for Anvato Plugin
				array_unshift(
					$links,
					'<a href="' . esc_url( admin_url( 'options-general.php?page=' . 'wp_anvato' ) ) . '">' . esc_html__( 'Settings', 'wp_anvato' ) . '</a>'
				);
			}
			return $links;
		}

		static function anvato_automatic_setup_desc() {
			echo "To setup this plugin automatically, please enter your setup key provided by Anvato. If you don't have a setup key, press Manual Setup.";
		}

		static function anvato_html_line() {
			echo '<hr/>';
		}
	}

	/**
	 * Get handle for Anvato Settings class
	 *
	 * return object
	 */
	function anvato_settings() {
		return Anvato_Settings::instance();
	}

	add_action( 'after_setup_theme', 'Anvato_Settings' );

endif; // if not class "Anvato_Settings" exists
