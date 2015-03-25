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

	private $settings = array(
		'general' => false,
	);

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

		/*
			Do a one-time (hopefully) retrieval of all the settings for the constructor.
			Since we store and re-use instances, the settings should carry-through.
			Setup to return "false" as a default item, if no data is available
		*/
		//$this->$settings['general'] = get_option( self::general_settings_key, false );

		if (is_admin()) {
			// Manage Settings
			$this->admin_setup_page_management();
		}

	}

	private function admin_setup_page_management() {
		
		/*if( isset($_GET['reset-settings']) ) {
			delete_option( self::general_settings_key );
			delete_option( self::player_settings_key );
			delete_option( self::monetization_settings_key );
			delete_option( self::analytics_settings_key );
			wp_redirect('?page=' . ANVATO_DOMAIN_SLUG . '#autosetup');
		} else {
			$this->remote_setup = get_option( self::general_settings_key ) === FALSE ? TRUE : FALSE;
		}*/

		/*
		if( isset($_POST['manual_setup']) ) {

			//add_option( self::general_settings_key,  array(), null , 'no' );
			//$this->remote_setup = FALSE;

		} elseif (isset($_POST['remote_setup']) && $_POST['remote_setup_key'] !== '' ) {

			$conf = json_decode(base64_decode($_POST['remote_setup_key']) , true);

			// check is valid?
			if( !is_array($conf) ) {
				$this->remote_setup_state = FALSE;
			} else {
				$raw_content = file_get_contents("https://{$conf['b']}.s3.amazonaws.com/wordpress/conf/{$conf['k']}");
				// Where's the failsafe?
				// Where is the transient?
				$setup = json_decode($raw_content, TRUE);

				if ( isset($setup['mcp']) && isset($setup['player']) ) {
					add_option( self::player_settings_key,  $setup['player'], null ,'no' );

					unset($setup['player']);
					add_option( self::general_settings_key, array("mcp_config"=> json_encode($setup) ), null ,'no' );

					$this->remote_setup = FALSE;
					$this->remote_setup_state = TRUE;
				} else {
					$this->remote_setup_state = FALSE;
				}
			}

		}
		*/

		// Add the main Anvato Settings page in "Settings"
		add_action( 'admin_menu', function(){
			add_options_page( __( 'Anvato', ANVATO_DOMAIN_SLUG ), 
				__( 'Anvato', ANVATO_DOMAIN_SLUG ), 
				'manage_options',
				ANVATO_DOMAIN_SLUG, 
				array( Anvato_Settings(), 'view_settings_page' ) 
			);
		} );

		add_action( 'admin_init', array( $this, 'player_settings_init' ) );
		add_action( 'admin_init', array( $this, 'analytics_settings_init' ) );
		add_action( 'admin_init', array( $this, 'monetization_settings_init' ) );
		add_action( 'admin_init', array( $this, 'mcp_settings_init' ) );
		add_filter( 'plugin_action_links', array( $this, 'wp_plugin_actions' ), 10, 2 );
		
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
	

	/**
	 * Function to display and manage settings on the admin page
	 */
	public function view_settings_page() {
		?>
		
		<?php if ( $this->remote_setup ) : ?>

			<div class="wrap">
				<h2><img src="<?php echo esc_url( ANVATO_URL . 'img/logo.png' ) ?>" alt="<?php esc_attr_e( 'Anvato Video Plugin Settings', ANVATO_DOMAIN_SLUG ); ?>" /></h2>

				<?php if( $this->remote_setup_state === FALSE) : ?>
					<div id="message" class="error">
						<p>
							<strong>Sorry, we encountered an error during setup. Please check your setup key and try again.</strong>
						</p>
					</div>';    
				<?php endif; ?>

				<h3>Welcome to Anvato Wordpress Plugin</h3>
				<p>To setup this plugin automatically, please enter your setup key provided by Anvato. If you don't have a setup key, press Manual Setup.</p>
				<form method="POST" action="">
					<input name="remote_setup_key" type="text" value="" style="width: 100%; max-width: 80%" />
					<hr/>
					<?php submit_button( 'Automated Setup', 'primary large', 'remote_setup', false ); ?>
					<?php submit_button( 'Manual Setup', 'secondary large', 'manual_setup', false ); ?>
				</form>
			</div>

		<?php else : ?>

			<?php
				$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : self::player_settings_key;
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
						<a class="<?php echo esc_attr(implode(' ', $tab_class)); ?>" href="<?php echo admin_url('options-general.php?page=' . ANVATO_DOMAIN_SLUG . '&tab=' . $key); ?>"><?php echo esc_html($name); ?></a>
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

		<?php endif; ?>

		<?php
	}

	public function mcp_settings_init() {
 
		$this->plugin_settings_tabs[self::general_settings_key] = "Access";

		register_setting( self::general_settings_key, self::general_settings_key, array( self::$instance, 'sanitize_options' ) );
		add_settings_section(
			'section_mcp', 
			'MCP Settings', 
			function(){
				echo '<hr/>';
			}, 
			self::general_settings_key
		);
		// Fields
		add_settings_field( 'mcp_config', __( 'API Configuration:', ANVATO_DOMAIN_SLUG ), array( self::$instance, 'textbox' ), self::general_settings_key, 'section_mcp', array( "key" => self::general_settings_key, 'field' => 'mcp_config' ) );
		
	}

	public function player_settings_init()
	{
		$this->plugin_settings_tabs[self::player_settings_key] = "Player";

		register_setting( self::player_settings_key, self::player_settings_key, array( self::$instance, 'sanitize_options' ) );
		add_settings_section( 'section_player', 'Player Settings', array( self::$instance, 'section_player_desc' ), self::player_settings_key );
		// Fields
		add_settings_field( 'player_url', __( 'Player URL*:', ANVATO_DOMAIN_SLUG ), array( self::$instance, 'field' ), self::player_settings_key, 'section_player', array( "key" => self::player_settings_key, 'field' => 'player_url' ) );
		add_settings_field( 'height', __( 'Height:', ANVATO_DOMAIN_SLUG ), array( self::$instance, 'field_px' ), self::player_settings_key, 'section_player', array( "key" => self::player_settings_key, 'field' => 'height' ) );
		add_settings_field( 'width', __( 'Width:', ANVATO_DOMAIN_SLUG ), array( self::$instance, 'field_px' ), self::player_settings_key, 'section_player', array( "key" => self::player_settings_key, 'field' => 'width' ) );
	}
	
	public function analytics_settings_init() {

		$this->plugin_settings_tabs[self::analytics_settings_key] = "Analytics";
		
		register_setting( self::analytics_settings_key, self::analytics_settings_key, array( self::$instance, 'sanitize_options' ) );
		// Fields
		add_settings_section( 'section_anvato', 'Anvato Analytics', array( self::$instance, 'section_analytics_desc' ), self::analytics_settings_key );
		add_settings_field( 'tracker_id', __( 'Tracker ID:', ANVATO_DOMAIN_SLUG ), array( self::$instance, 'field' ), self::analytics_settings_key, 'section_anvato', array( "key" => self::analytics_settings_key, 'field' => 'tracker_id' ) );
		// Fields
		add_settings_section( 'section_analytics', 'Adobe Settings', array( self::$instance, 'section_analytics_desc' ), self::analytics_settings_key );
		add_settings_field( 'adobe_profile', __( 'Profile:', ANVATO_DOMAIN_SLUG ), array( self::$instance, 'field' ), self::analytics_settings_key, 'section_analytics', array( "key" => self::analytics_settings_key,'field' => 'adobe_profile' ) );
		add_settings_field( 'adobe_account', __( 'Account:', ANVATO_DOMAIN_SLUG ), array( self::$instance, 'field' ), self::analytics_settings_key, 'section_analytics', array( "key" => self::analytics_settings_key,'field' => 'adobe_account' ) );
		add_settings_field( 'adobe_trackingserver', __( 'Tracking Server:', ANVATO_DOMAIN_SLUG ), array( self::$instance, 'field' ), self::analytics_settings_key, 'section_analytics', array( "key" => self::analytics_settings_key, 'field' => 'adobe_trackingserver' ) );

		add_settings_field( 'heartbeat_account_id', __( 'Heartbeat Account ID:', ANVATO_DOMAIN_SLUG ), array( self::$instance, 'field' ), self::analytics_settings_key, 'section_analytics', array( "key" => self::analytics_settings_key, 'field' => 'heartbeat_account_id' ) );
		add_settings_field( 'heartbeat_publisher_id', __( 'Heartbeat Publisher ID:', ANVATO_DOMAIN_SLUG ), array( self::$instance, 'field' ), self::analytics_settings_key, 'section_analytics', array( "key" => self::analytics_settings_key, 'field' => 'heartbeat_publisher_id' ) );
		add_settings_field( 'heartbeat_job_id', __( 'Heartbeat Job ID:', ANVATO_DOMAIN_SLUG ), array( self::$instance, 'field' ), self::analytics_settings_key, 'section_analytics', array( "key" => self::analytics_settings_key, 'field' => 'heartbeat_job_id' ) );
		add_settings_field( 'heartbeat_marketing_id', __( 'Heartbeat Cloud ID:', ANVATO_DOMAIN_SLUG ), array( self::$instance, 'field' ), self::analytics_settings_key, 'section_analytics', array( "key" => self::analytics_settings_key, 'field' => 'heartbeat_marketing_id' ) );
		add_settings_field( 'heartbeat_tracking_server', __( 'Heartbeat Traking Server:', ANVATO_DOMAIN_SLUG ), array( self::$instance, 'field' ), self::analytics_settings_key, 'section_analytics', array( "key" => self::analytics_settings_key, 'field' => 'heartbeat_tracking_server' ) );


		// Fields
		add_settings_section( 'section_comscore', 'Comscore Analytics', array( self::$instance, 'section_analytics_desc' ), self::analytics_settings_key );
		add_settings_field( 'comscore_client_id', __( 'Client ID:', ANVATO_DOMAIN_SLUG ), array( self::$instance, 'field' ), self::analytics_settings_key, 'section_comscore', array( "key" => self::analytics_settings_key, 'field' => 'comscore_client_id' ) );

	}
	
	public function monetization_settings_init()
	{
		$this->plugin_settings_tabs[self::monetization_settings_key] = "Monetization";
		
		register_setting( self::monetization_settings_key, self::monetization_settings_key, array( self::$instance, 'sanitize_options' ) );

		add_settings_section( 'section_monetization', 'Monetization Settings', array( self::$instance, 'section_monetization_desc' ), self::monetization_settings_key );
		add_settings_field( 'adtag', __( 'DFP Premium Ad Tag:', ANVATO_DOMAIN_SLUG ), array( self::$instance, 'field' ), self::monetization_settings_key, 'section_monetization',  array( "key" => self::monetization_settings_key, 'field' => 'adtag' ) );
		add_settings_field( 'advanced_targeting', __( 'Advanced Targeting:', ANVATO_DOMAIN_SLUG ), array( self::$instance, 'textbox' ), self::monetization_settings_key, 'section_monetization',  array( "key" => self::monetization_settings_key, 'field' => 'advanced_targeting' ) );

	}
	
	function sanitize_options($in) 
	{
		return $in;
	}
	
	public function yes_no( $args ) 
	{
		$args = wp_parse_args( $args, array(
			'type' => 'text'
		) );

		if ( empty( $args['field'] ) ) {
			return;
		}

		printf( '<select name="%s[%s]"  class="meta required valid">', esc_attr( ANVATO_DOMAIN_SLUG ), esc_attr( $args['field'] ) );
		echo '<option value="false" '.(esc_attr( $this->get_option( $args['field'] ) ) === 'false' ? 'selected="selected"' : '' ).'>No</option>'
			.'<option value="true" '.(esc_attr( $this->get_option( $args['field'] ) ) === 'true' ? 'selected="selected"' : '' ).' >Yes</option>'
			.'</select>';
		 
	} 
	
	public function field( $args ) 
	{
		$args = wp_parse_args( $args, array(
			'type' => 'text'
		) );

		if ( empty( $args['field'] ) ) {
			return;
		}

		printf( '<input type="%s" name="%s[%s]" value="%s" size="50" />', esc_attr( $args['type'] ), esc_attr( $args['key'] ), esc_attr( $args['field'] ), esc_attr( $this->get_option( $args['key'], $args['field'] ) ) );
	} 
        
	public function textbox( $args ) 
	{
		if ( empty( $args['field'] ) ) {
			return;
		}

                printf( '<textarea name="%s[%s]" class="large-text code" rows="15">%s</textarea>', esc_attr( $args['key'] ), esc_attr( $args['field'] ), esc_textarea( $this->get_option( $args['key'], $args['field'] ) ) );
                 
	} 
	
	public function field_px( $args ) 
	{
		$args = wp_parse_args( $args, array(
			'type' => 'text'
		) );

		if ( empty( $args['field'] ) ) {
			return;
		}

		printf( '<input type="%s" name="%s[%s]" value="%s" size="4" /> px', esc_attr( $args['type'] ), esc_attr( $args['key'] ), esc_attr( $args['field'] ), esc_attr( $this->get_option( $args['key'], $args['field'] ) ) );
	} 
	
	public function get_option( $key , $field) 
	{
		if(sizeof($this->options) === 0 )
			$this->get_options();
		
		return isset( $this->options[ $key ][ $field ] ) ? $this->options[ $key ][ $field ] : null;
	}

	function section_mcp_desc()		{ echo '<hr/>'; }
	function section_player_desc()	{ echo '<hr/>'; }
	function section_analytics_desc()	{ echo '<hr/>'; }
	function section_monetization_desc()		{ echo '<hr/>'; }
	
	function wp_plugin_actions( $links, $file ) 
	{
		if( $file === 'wp-anvato-plugin/anvato.php' && function_exists( "admin_url" ) ) 
		{
			$settings_link = '<a href="' . admin_url( 'options-general.php?page='.ANVATO_DOMAIN_SLUG ) . '">' . __('Settings') . '</a>';
			array_unshift( $links, $settings_link ); // before other links
		}	
		return $links;
	}

	static function get_mcp_options() {
		$settings = (array) get_option( self::general_settings_key );
		$json = json_decode($settings['mcp_config'], TRUE);
		return $json;
	}


} // end of Anvato_Settings class

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