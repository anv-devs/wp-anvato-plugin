<?php

/**
 * Anvato Settings
 */

if ( !class_exists( 'Anvato_Settings' ) ) :

class Anvato_Settings 
{
	// Option storage key names
	const AUTOMATIC_SETUP_KEY = 'anvato_plugin_setup';
	const GENERAL_SETTINGS_KEY = 'anvato_mcp';
	const PLAYER_SETTINGS_KEY = 'anvato_player';
	const ANALYTICS_SETTINGS_KEY = 'anvato_analytics';
	const MONETIZATION_SETTINGS_KEY = 'anvato_monetization';
        
	public $options = array();
	
        private $remote_setup = false;
	// Plugin Fields
	private $plugin_fields = array (
		self::AUTOMATIC_SETUP_KEY => array (
			array( 'id' => 'section_setup', 'title' => 'Welcome to Automatic Setup', 'callback' => array("Anvato_Callbacks", "__automatic_setup_desc"),
			       'fields' => array(
				       array( 'id' => 'mcp_config_automatic_key', 'title' => 'Auto Configuration Key:' , 'no_value' => 1 )
			       )
		       )
		),
		self::PLAYER_SETTINGS_KEY => array (
			array( 'id' => 'section_player', 'title' => 'Player Settings', 'callback' => array("Anvato_Callbacks", "__html_line") ,
				'fields' => array(
					array( 'id' => 'player_url', 'title' => 'Player URL*:'),
					array( 'id' => 'height', 'title' => 'Height:', 'args' => array('size' => 4, 'after_field' => ' px') ),
					array( 'id' => 'width', 'title' => 'Width:', 'args' => array('size' => 4, 'after_field' => ' px') )
				)
			)
		),
		self::ANALYTICS_SETTINGS_KEY => array (
			// Anvato Analytics
			array( 'id' => 'section_anvato_analytics', 'title' => 'Anvato Analytics Settings', 'callback' => array("Anvato_Callbacks", "__html_line"),
				'fields' => array(
					array( 'id' => 'tracker_id', 'title' => 'Tracker ID:'),
				)
			),
			// Adobe Analytics
			array( 'id' => 'section_adobe_analytics', 'title' => 'Adobe Analytics Settings', 'callback' => array("Anvato_Callbacks", "__html_line"),
				'fields' => array(
					array( 'id' => 'adobe_account', 'title' => 'Account:'),
					array( 'id' => 'adobe_trackingserver', 'title' => 'Tracking Server:'),
				)
			),
			// Heartbeat Analytics Block
			array( 'id' => 'section_heartbeet_analytics', 'title' => 'Heartbeat Analytics Settings', 'callback' => array("Anvato_Callbacks", "__html_line"),
				'fields' => array(
					array( 'id' => 'heartbeat_account_id', 'title' => 'Account ID:'),
					array( 'id' => 'heartbeat_publisher_id', 'title' => 'Publisher ID:'),
					array( 'id' => 'heartbeat_job_id', 'title' => 'Job ID:'),
					array( 'id' => 'heartbeat_marketing_id', 'title' => 'Cloud ID:'),
					array( 'id' => 'heartbeat_tracking_server', 'title' => 'Tracking Server:'),
				)
			),
			// Comscore Analytics
			array( 'id' => 'section_comscore_analytics', 'title' => 'Comscore Analytics Settings', 'callback' => array("Anvato_Callbacks", "__html_line"),
				'fields' => array(
					array( 'id' => 'comscore_client_id', 'title' => 'Client ID:'),
				)
			),
		),
		self::MONETIZATION_SETTINGS_KEY => array (
			array( 'id' => 'section_monetization', 'title' => 'Monetization Settings', 'callback' => array("Anvato_Callbacks", "__html_line") ,
				'fields' => array(
					array( 'id' => 'adtag', 'title' => 'DFP Premium Ad Tag:' ),
					array( 'id' => 'advanced_targeting', 'title' => 'Advanced Targeting:', 'callback' => array( 'Anvato_Form_Fields', 'textarea' ) ),
				)
			)
		),
		self::GENERAL_SETTINGS_KEY => array (
			array( 'id' => 'section_mcp', 'title' => 'MCP Settings', 'callback' => array("Anvato_Callbacks", "__html_line") ,
				'fields' => array(
					array( 'id' => 'mcp_config', 'title' => 'API Configuration:', 'callback' => array( 'Anvato_Form_Fields', 'textarea' ) ),
					array( 'id' => 'reset_settings', 'title' => 'Reset settings:', 'callback' => array( 'Anvato_Form_Fields', 'reset_check' ), 'no_value' => 1 ),
				)
			)
		),
	);
	
        // Instance Management
	protected static $instance;
        
	protected function __construct() 
        {
		$this->admin_settings();
	}
        
	public static function instance() 
        {
		if(!isset(self::$instance)) 
                {
			self::$instance = new Anvato_Settings;
		}
		return self::$instance;
	}

	/**
	 * Initiate all the functions and actions related to the admin panel
	 */
	private function admin_settings() 
        {
		if (!is_admin()) return;

		// Add the main Anvato Settings page in "Settings"
		add_action( 'admin_menu', array( 'Anvato_Callbacks', '__admin_menu' ) );

		// initiate the fields for the form and saving of the items
		add_action( 'admin_init', array( $this, 'admin_settings_page_setup' ) );

		// add settings link in the plugin activation panel, if available
		if (has_action('plugin_action_links')) {
			add_filter( 'plugin_action_links', array('Anvato_Callbacks','__plugin_action_links'), 10, 2 );
		}
	}

	/**
	 * Setup all the tabs, links and fields for the admin panel
	 */
	public function admin_settings_page_setup() 
	{
		if (!is_admin()) return;

		$setup_key_value = $this->get_option(self::AUTOMATIC_SETUP_KEY, 0);
 		/*
			If there are no setup key for the plugin, 
			we should not show ANY other tabs, until the core settings is setup.
			When setup finished, setup key will be false
		*/
		if ( empty($setup_key_value) )
		{
                        $this->plugin_settings_tabs = array();
                        $this->plugin_settings_tabs[self::AUTOMATIC_SETUP_KEY] = "Plugin Setup";
                        $this->create_settings_section(self::AUTOMATIC_SETUP_KEY);
                        $this->remote_setup = true;
		}
		else 
		{
                        // Player Tab
                        $this->plugin_settings_tabs = array();
                        $this->plugin_settings_tabs[self::PLAYER_SETTINGS_KEY] = "Player";
                        $this->create_settings_section(self::PLAYER_SETTINGS_KEY);

                        // Analytics Tab
                        $this->plugin_settings_tabs[self::ANALYTICS_SETTINGS_KEY] = "Analytics";
                        $this->create_settings_section(self::ANALYTICS_SETTINGS_KEY);

                        // Monetization Tab
                        $this->plugin_settings_tabs[self::MONETIZATION_SETTINGS_KEY] = "Monetization";
                        $this->create_settings_section(self::MONETIZATION_SETTINGS_KEY);

                        // Access Tab
                        $this->plugin_settings_tabs[self::GENERAL_SETTINGS_KEY] = "Access";
                        $this->create_settings_section(self::GENERAL_SETTINGS_KEY);
		}

	} // admin options setup

	private function create_settings_section( $key )
	{
		$plugin_sections = $this->plugin_fields[$key];
		foreach ( $plugin_sections as $section )
		{
			register_setting($key, $key, array($this, 'sanitize_options') );

			add_settings_section(
				$section['id'], 
				__( $section['title'] , ANVATO_DOMAIN_SLUG ), 
				$section['callback'],  
				$key
			);

			foreach ( $section['fields'] as $field )
			{
				if( empty($field['args']) ) {
					$field['args'] = [];
				}

				$args = array_merge( array( 
						'name' => "{$key}[{$field['id']}]",
						'value' => empty($field['no_value']) ? $this->get_option($key, $field['id']) : '',
					), $field['args'] );

				$callback = !empty($field['callback']) ? $field['callback'] : array( 'Anvato_Form_Fields', 'field' );

				add_settings_field ( 
					$field['id'], 
					__( $field['title'], ANVATO_DOMAIN_SLUG ), 
					$callback,
					$key, 
					$section['id'], 
					$args
				);
			}
		}
	}
		
	private function do_autosetup( $key )
	{
                $autoconfigkey_decoded = json_decode( base64_decode( $key) , true );
                if ( empty( $autoconfigkey_decoded['b'] ) || empty( $autoconfigkey_decoded['k'] ) ) {
                        return 0;
                }
                 
                $result_response = wp_remote_get("http://{$autoconfigkey_decoded['b']}.s3.amazonaws.com/wordpress/conf/{$autoconfigkey_decoded['k']}");

                // We don't need transient, since this will not be used often.
                if ( is_wp_error($result_response) ) 
                {
                        return 0; 
                }

                $response_data = wp_remote_retrieve_body($result_response);
                if ( empty($response_data) ) 
                {
                        return 0;
                }

                $result = json_decode($response_data, TRUE);

                if ( empty($result['player']) || empty($result['mcp']) || empty($result['owners']) ) 
                {
                        return 0;
                }

                // Set automatic Player settings
                // this will not overwrite existing options!
                add_option( self::PLAYER_SETTINGS_KEY, $result['player'], '', 'no' );
                unset( $result['player'] );
               
                // set MCP main settings to everything without the player settings
                //"mcp" and "owners" should be stored
                // You have to delete before add option because there is no upsert support on option store.
                delete_option(self::GENERAL_SETTINGS_KEY);
                add_option( self::GENERAL_SETTINGS_KEY, array('mcp_config'=> json_encode($result)), '', 'no' );

                return 1; 
	}
	
        /**
	 * Function to display and manage settings on the admin page
	 */
	public function admin_settings_page_view() 
        {
		if (!is_admin()) return;

		reset($this->plugin_settings_tabs); // reset array pointer
		$active_tab = key($this->plugin_settings_tabs);
		if ( !empty($_GET['tab']) ) {
			$active_tab = $_GET['tab'];
		}
		?>
		<div class="wrap">
			<h2><img src="<?php echo esc_url( ANVATO_URL . 'img/logo.png' ) ?>" alt="<?php esc_attr_e( 'Anvato Video Plugin Settings', ANVATO_DOMAIN_SLUG ); ?>" /></h2>

			<?php if(get_query_var('setup-state', '0')) : ?>
				<div id="message" class="updated">
					<p>
						<strong>Your plugin is successfully setup.</strong>
					</p>
				</div>
			<?php endif; ?>

			<p>Anvato Wordpress Plugin allows Anvato Media Content Platform customers to easily insert players into posts that play video on demand clips as well as live channels.</p>

			<?php screen_icon(); ?>
			<h2 class="nav-tab-wrapper">
				<?php 
                                foreach ($this->plugin_settings_tabs as $key => $name) {
                                        $tab_class = array('nav-tab');
                                        if ($active_tab == $key) 
                                            $tab_class[] = 'nav-tab-active';
                                ?>
					<a class="<?php echo esc_attr(implode(' ', $tab_class)); ?>" href="<?php echo esc_url(admin_url('options-general.php?page=' . ANVATO_DOMAIN_SLUG . '&tab=' . $key)); ?>"><?php echo esc_html($name); ?></a>
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
				if( $this->remote_setup ) { // User wants make a manual setup so secondary button was added even though it usesless. ?>
					<?php submit_button( 'Automated Setup', 'primary large', 'remote_setup', false ); ?>
					<?php submit_button( 'Manual Setup', 'secondary large', 'manual_setup', false ); ?>
				<?php 
                                } else {
					submit_button();
                                } ?>
				
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
	public function sanitize_options( $dirty ) 
	{
		$clean = array();
		// if its not array, make it into one
		if ( !is_array($dirty) ) {
			$dirty = (array) $dirty;
		$clean = array_map('sanitize_text_field', $dirty);

		if ( array_key_exists( 'mcp_config_automatic_key', $clean ) ) 
		{
			$result = "";
			if ( !empty($clean['mcp_config_automatic_key']) ) 
                        { 
				// For when we can do automatic setup from the 64bit key
				$result = $this->do_autosetup($clean['mcp_config_automatic_key']);
				unset($clean['mcp_config_automatic_key']);
			} 

			add_option(self::AUTOMATIC_SETUP_KEY, $result, '', 'no');
                        
			// and then we want to get out of the process altogether and not create a DB item
			wp_redirect(admin_url(
				'options-general.php?page=' . ANVATO_DOMAIN_SLUG . 
				'&setup-state='.$result  
			));
			exit; // terminate advancing further, so the redirect works proper
		} 
		elseif ( array_key_exists('reset_settings', $clean ) ) 
		{
                        /*
                         * Check the reset action was stored. If yes, clean up all stored settings and then 
                         * redirect user to automatic setup screen  
                         */
                        delete_option(self::GENERAL_SETTINGS_KEY);
                        delete_option(self::PLAYER_SETTINGS_KEY);
                        delete_option(self::MONETIZATION_SETTINGS_KEY);
                        delete_option(self::ANALYTICS_SETTINGS_KEY);
                        delete_option(self::AUTOMATIC_SETUP_KEY);

                        wp_redirect(admin_url(
                                'options-general.php?page=' . ANVATO_DOMAIN_SLUG . 
                                '&reset-success=true'  
                        ));
                        exit; // terminate advancing further, so the redirect works proper
		}
		
		return $clean;
	}

	public function get_options( $key = null ) 
        {

		$this->general_settings = (array) get_option( self::GENERAL_SETTINGS_KEY );
		$this->player_settings = (array) get_option( self::PLAYER_SETTINGS_KEY );
		$this->analytics_settings = (array) get_option( self::ANALYTICS_SETTINGS_KEY );
		$this->monetization_settings = (array) get_option( self::MONETIZATION_SETTINGS_KEY );
		
		// Merge with defaults
		$this->options[self::PLAYER_SETTINGS_KEY] = array_merge( array( 'section_player' => 'Player' ), $this->player_settings );
		$this->options[self::ANALYTICS_SETTINGS_KEY] = array_merge( array( 'section_analytics' => 'Analytics' ), $this->analytics_settings );
		$this->options[self::MONETIZATION_SETTINGS_KEY] = array_merge( array( 'section_analytics' => 'Monetization' ), $this->monetization_settings );
		$this->options[self::GENERAL_SETTINGS_KEY] = array_merge( array( 'section_mcp' => 'Access' ), $this->general_settings );
		$this->options[self::AUTOMATIC_SETUP_KEY] = (array) get_option( self::AUTOMATIC_SETUP_KEY );
 
		return $key == null ? $this->options : $this->options[$key];
	}

	public function get_option( $key , $field) 
	{
		if(sizeof($this->options) === 0 )
			$this->get_options();

		return isset( $this->options[ $key ][ $field ] ) ? $this->options[ $key ][ $field ] : null;
	}

	static function get_mcp_options() 
        {
		$settings = (array) get_option( self::GENERAL_SETTINGS_KEY );
                if ( empty($settings['mcp_config']) ) 
                    return null;

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
                        'type' => 'text',
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
	
	static function reset_check( $args ) {

		printf (
			'<input type="checkbox" name="%s" value="1" onclick="if(this.checked) return confirm(\'This will erase all plugin settings. Are you sure?\');" />'
		, esc_attr( $args['name'] ));
	}
}

/**
 * There is no support for anonymous functions on PHP 5.2.4+ hence all anonymous 
 * functions was collected in this class.
 * see more for WP requirements : https://wordpress.org/about/requirements/
 */

class Anvato_Callbacks
{
    
    static function __admin_menu()
    {
	    add_options_page( __( 'Anvato', ANVATO_DOMAIN_SLUG ), 
		    __( 'Anvato', ANVATO_DOMAIN_SLUG ), 
		    'manage_options',
		    ANVATO_DOMAIN_SLUG, 
		    array( Anvato_Settings(), 'admin_settings_page_view' ) 
	    );
    }
    
    static function __plugin_action_links( $links, $file ) 
    {
	    if( $file === 'wp-anvato-plugin/anvato.php' && function_exists( "admin_url" ) ) {
		    // Insert option for "Settings" before other links for Anvato Plugin
		    array_unshift(
			    $links, 
			    '<a href="' . esc_url(admin_url( 'options-general.php?page=' . ANVATO_DOMAIN_SLUG )) . '">' . __( 'Settings', ANVATO_DOMAIN_SLUG ) . '</a>'
		    );
	    }
	    return $links;
    }
    
    static function __automatic_setup_desc()
    {
	    echo "To setup this plugin automatically, please enter your setup key provided by Anvato. If you don't have a setup key, press Manual Setup.";
    }
    
    static function __html_line()
    {
	    echo '<hr/>';
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