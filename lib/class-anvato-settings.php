<?php

/**
 * Anvato Settings
 */

if ( !class_exists( 'Anvato_Settings' ) ) :

class Anvato_Settings {

	const general_settings_key = 'anvato_mcp';
	const player_settings_key = 'anvato_player';
	const analytics_settings_key = 'anvato_analytics';
	const monetization_settings_key = 'anvato_monetization';
	
	public $options_capability = 'manage_options';
	public $options = array();

	protected static $instance;

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Anvato_Settings;
			self::$instance->setup_actions();
		}
		return self::$instance;
	}

	protected function __construct() {
		/** Don't do anything **/
	}

	public function setup_actions() 
	{
		add_action( 'admin_init', array( self::$instance, 'mcp_settings_init' ) );
		add_action( 'admin_init', array( self::$instance, 'player_settings_init' ) );
		add_action( 'admin_init', array( self::$instance, 'analytics_settings_init' ) );
		add_action( 'admin_init', array( self::$instance, 'monetization_settings_init' ) );
		add_action( 'admin_menu', array( self::$instance, 'action_admin_menu' ) );
		add_filter( 'plugin_action_links', array( self::$instance, 'wp_plugin_actions' ), 10, 2 );
	}
	 
	public function action_admin_menu() 
	{
		add_options_page( __( 'Anvato', ANVATO_DOMAIN_SLUG ), __( 'Anvato', ANVATO_DOMAIN_SLUG ), $this->options_capability, ANVATO_DOMAIN_SLUG, array( self::$instance, 'view_settings_page' ) );
	}
	
	public function get_options($key=null) 
	{
		$this->general_settings = (array) get_option( self::general_settings_key );
		$this->player_settings = (array) get_option( self::player_settings_key );
		$this->analytics_settings = (array) get_option( self::analytics_settings_key );
		$this->monetization_settings = (array) get_option( self::monetization_settings_key );
		
		// Merge with defaults
		$this->options[self::general_settings_key ] = array_merge( array( 'section_mcp' => 'MCP' ), $this->general_settings );
		$this->options[self::player_settings_key] = array_merge( array( 'section_player' => 'Player' ), $this->player_settings );
		$this->options[self::analytics_settings_key] = array_merge( array( 'section_analytics' => 'Analytics' ), $this->analytics_settings );
		$this->options[self::monetization_settings_key] = array_merge( array( 'section_analytics' => 'Monetization' ), $this->monetization_settings );
		
		return $key == null ? $this->options : $this->options[$key];
	}
	
	public function view_settings_page()
	{
		$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : self::general_settings_key;
		?>
		<div class="wrap">
			<h2><img src="<?php echo esc_url( ANVATO_URL . 'img/logo.png' ) ?>" alt="<?php esc_attr_e( 'Anvato Video Plugin Settings', ANVATO_DOMAIN_SLUG ); ?>" /></h2>
			<p>Anvato Wordpress Plugin allows Anvato Media Content Platform customers to easily insert players into posts that play video on demand clips as well as live channels.</p>
			<?php $this->plugin_options_tabs(); ?>
			<form method="post" action="options.php">
				<?php wp_nonce_field( 'update-options' ); ?>
				<?php settings_fields( $tab ); ?>
				<?php do_settings_sections( $tab ); ?>
				<hr/>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
	
	public function plugin_options_tabs() 
	{
		$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : self::general_settings_key;

		screen_icon();
		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) 
		{
			$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
			echo '<a class="nav-tab ' . $active . '" href="?page=' . ANVATO_DOMAIN_SLUG . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';	
		}
		echo '</h2>';
	}
	
	public function mcp_settings_init()
	{
 
		$this->plugin_settings_tabs[self::general_settings_key] = "MCP";
		
		register_setting( self::general_settings_key, self::general_settings_key, array( self::$instance, 'sanitize_options' ) );
		add_settings_section( 'section_mcp', 'MCP Settings', array( self::$instance, 'section_mcp_desc' ), self::general_settings_key );
		// Fields
		add_settings_field( 'mcp_url', __( 'MCP URL:', ANVATO_DOMAIN_SLUG ), array( self::$instance, 'field' ), self::general_settings_key, 'section_mcp', array( "key" => self::general_settings_key, 'field' => 'mcp_url' ) );
		add_settings_field( 'mcp_id', __( 'MCP ID:', ANVATO_DOMAIN_SLUG ), array( self::$instance, 'field' ), self::general_settings_key, 'section_mcp', array( "key" => self::general_settings_key, 'field' => 'mcp_id' ) );
		add_settings_field( 'station_id', __( 'Station ID:', ANVATO_DOMAIN_SLUG ), array( self::$instance, 'field' ), self::general_settings_key, 'section_mcp', array( "key" => self::general_settings_key, 'field' => 'station_id' ) );
		add_settings_field( 'public_key', __( 'Public Key:', ANVATO_DOMAIN_SLUG ), array( self::$instance, 'field' ), self::general_settings_key, 'section_mcp', array( "key" => self::general_settings_key, 'field' => 'public_key' ) );
		add_settings_field( 'private_key', __( 'Private Key:', ANVATO_DOMAIN_SLUG ), array( self::$instance, 'field' ), self::general_settings_key, 'section_mcp', array( "key" => self::general_settings_key, 'field' => 'private_key') );
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
	
	public function analytics_settings_init()
	{
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

	}
	
	public function monetization_settings_init()
	{
		$this->plugin_settings_tabs[self::monetization_settings_key] = "Monetization";
		
		register_setting( self::monetization_settings_key, self::monetization_settings_key, array( self::$instance, 'sanitize_options' ) );

		add_settings_section( 'section_monetization', 'Monetization Settings', array( self::$instance, 'section_monetization_desc' ), self::monetization_settings_key );
		add_settings_field( 'adtag', __( 'DFP Premium Ad Tag:', ANVATO_DOMAIN_SLUG ), array( self::$instance, 'field' ), self::monetization_settings_key, 'section_monetization',  array( "key" => self::monetization_settings_key, 'field' => 'adtag' ) );

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
		error_log($file);
		if( $file === 'wp-anvato-plugin/anvato.php' && function_exists( "admin_url" ) ) 
		{
			$settings_link = '<a href="' . admin_url( 'options-general.php?page='.ANVATO_DOMAIN_SLUG ) . '">' . __('Settings') . '</a>';
			array_unshift( $links, $settings_link ); // before other links
		}	
		return $links;
	}
}

function Anvato_Settings() {
	return Anvato_Settings::instance();
}
	
add_action( 'after_setup_theme', 'Anvato_Settings' );

endif;