<?php
global $anvato_player_index;
$anvato_player_index = 0;

/**
 * Implement the Anvato shortcode.
 *
 * @param  array $attr Array of shortcode attributes
 * @return string       HTML to replace the shortcode
 */
function anvato_shortcode( $attr ) 
{
	global $anvato_player_index;
	$mcp = Anvato_Settings()->get_mcp_options();
	$player = Anvato_Settings()->get_options(Anvato_Settings::PLAYER_SETTINGS_KEY);
	$analytics = Anvato_Settings()->get_options(Anvato_Settings::ANALYTICS_SETTINGS_KEY);
	$monetization = Anvato_Settings()->get_options(Anvato_Settings::MONETIZATION_SETTINGS_KEY);

	# Set the attributes which the shortcode can override
	$json = shortcode_atts( array(
		'mcp'        => $mcp['mcp']['id'],
		'width'      => $player['width'],
		'height'     => $player['height'],
		'video'      => null,
		'autoplay'   => false
	), $attr, 'anvplayer' );

	# Set other attributes that can't be overridden
	$json['pInstance'] = 'p' . $anvato_player_index++;

	# Set the player URL, which isn't part of the JSON but can be overridden
	$player_url = !empty( $attr['player_url'] ) ? $attr['player_url'] : $player['player_url'];

	# Set the DFP Ad Tag, which can also be overridden
        $dfp = '';
	if ( !empty($monetization['advanced_targeting']) )
        {
                $dfp = json_decode($monetization['advanced_targeting'], true );
        }
        else
        {
                if( !empty($attr['plugin_dfp_adtagurl']) && $attr['plugin_dfp_adtagurl'] !== 'false' ) // User can close or change own dfp in shortcode
                {
                        $json['plugins']['dfp']['adTagUrl'] = urldecode($attr['plugin_dfp_adtagurl']);
                }
                elseif( !empty($monetization['adtag']) )
                {
                        $json['plugins']['dfp']['adTagUrl'] = $monetization['adtag'];
                }
        }
 
        // Defining analytics in shortcode is closed.
	if ( ! isset( $attr['tracker_id'] ) && ! empty( $analytics['tracker_id'] ) ) 
        {
		$json['plugins']['analytics'] = array( 'pdb' => $analytics['tracker_id'] );
	}
        
        // Adobe Settings
        if ( !empty( $analytics['adobe_account'] ) ) {
                $json['plugins']['omniture']['account'] = $analytics['adobe_account'];
        }
        
	if ( !empty( $analytics['adobe_trackingserver'] ) ) 
        {
                $json['plugins']['omniture']['trackingServer'] = $analytics['adobe_trackingserver'];
        } 

        # Set Heartbeat 
        if ( !empty( $analytics['heartbeat_account_id'] ) ) 
        {
		$json['plugins']['heartbeat']['account'] = $analytics['heartbeat_account_id'];
	}
        
        if ( !empty( $analytics['heartbeat_publisher_id'] ) ) 
        {
		$json['plugins']['heartbeat']['publisherId'] = $analytics['heartbeat_publisher_id'];
	}
        
        if ( ! empty( $analytics['heartbeat_job_id'] ) ) 
        {
		$json['plugins']['heartbeat']['jobId'] = $analytics['heartbeat_job_id'];
	}
        
        if ( ! empty( $analytics['heartbeat_marketing_id'] ) ) 
        {
		$json['plugins']['heartbeat']['marketingCloudId'] = $analytics['heartbeat_marketing_id'];
	}
        
        if ( ! empty( $analytics['heartbeat_tracking_server'] ) ) 
        {
		$json['plugins']['heartbeat']['trackingServer'] = $analytics['heartbeat_tracking_server'];
	}
        
        # Set Comscore Ids
        if ( ! empty( $analytics['comscore_client_id'] ) ) 
        {
		$json['plugins']['comscore']['clientId'] = $analytics['comscore_client_id'];
	}

        $video_ids = explode(",", $json["video"]);
	if ( sizeof($video_ids) > 1 )
	{
		unset($json["video"]);
		$json["playlist"] = $video_ids;
	}
        elseif( !empty($attr['playlist']) )
        {
                unset($json["video"]);
		$json["playlist"] = $attr['playlist'];
        }

        if ( !empty($mcp['mcp']['tkx_key']) )
        {
            $json['config']['accessKey'] = $mcp['mcp']['tkx_key'];
            $json['config']['accessControl']['preview'] = false;
        }
        
	# Clean up attributes as need be
	$json['autoplay'] = ( 'true' == $json['autoplay'] );

	# Allow theme/plugins to filter the JSON before outputting
	$json = apply_filters( 'anvato_anvp_json', $json, $attr );

	return "<div id='" . esc_attr( $json['pInstance'] ) . "'></div><script data-anvp='" . esc_attr( json_encode( $json ) ) . "' src='" . esc_url( $player_url ) . "'></script>";
}

add_shortcode( 'anvplayer', 'anvato_shortcode' );
