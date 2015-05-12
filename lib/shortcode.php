<?php
define ('EXT_ID_PREFIX', 'WN');

global $anvato_player_index;
$anvato_player_index = 0;

/**
 * Implement the Anvato shortcode.
 *
 * @param  array $attr Array of shortcode attributes
 * @return string       HTML to replace the shortcode
 */
function anvato_shortcode($attr) {

	global $anvato_player_index;
	$mcp = Anvato_Settings()->get_mcp_options();
	$player = Anvato_Settings()->get_options( Anvato_Settings::PLAYER_SETTINGS_KEY );
	$analytics = Anvato_Settings()->get_options( Anvato_Settings::ANALYTICS_SETTINGS_KEY );
	$monetization = Anvato_Settings()->get_options( Anvato_Settings::MONETIZATION_SETTINGS_KEY );

	// Set the attributes which the shortcode can override
	$json = shortcode_atts(
		array(
			'mcp' => $mcp['mcp']['id'],
			'width' => $player['width'],
			'height' => $player['height'],
			'video' => null,
			'ext_id' => null,
			'autoplay' => false
		),
		$attr, 
		'anvplayer'
	);
	
	$json['height'] = $json['height'] . $player['height_type'];
	$json['width'] = $json['width'] . $player['width_type'];

	$video_ids = explode( ",", $json["video"] );
	if ( sizeof( $video_ids ) > 1 ) {
		unset( $json["video"] );
		$json["playlist"] = $video_ids;
	} else if ( !empty( $attr['playlist'] ) ) {
		unset( $json["video"] );
		$json["playlist"] = $attr['playlist'];
	} else if ( !empty( $json['ext_id'] ) ) {
		$extern_ids = explode( ",", $json["ext_id"] );
		$video_ids = array();
		foreach ( $extern_ids as $extern_id ) {
			$video_ids[] = EXT_ID_PREFIX.$extern_id;
		}
		
		if(sizeof( $video_ids ) > 1) {
			unset( $json["video"] );
			$json["playlist"] = $video_ids;
		} else {
			$json["video"] = $video_ids[0];
		}  	
	}
	
	unset($json["ext_id"]);
	
	if ( !empty( $mcp['mcp']['tkx_key'] ) ) {
		$json['accessKey'] = $mcp['mcp']['tkx_key'];
		$json['accessControl']['preview'] = false;
	}

	$json['autoplay'] = ( 'true' == $json['autoplay'] );

	$json['pInstance'] = 'p' . $anvato_player_index++;

	// Set the player URL, which isn't part of the JSON but can be overridden
	$player_url = !empty( $attr['player_url'] ) ? $attr['player_url'] : $player['player_url'];

	// Avaliable values 
	$plugin_map = array(
		"analytics" => array(
			"pdb" => "tracker_id",
		),
		"omniture" => array(
			"account" => "adobe_account",
			"trackingServer" => "adobe_trackingserver"
		),
		"heartbeat" => array(
			'account' => 'heartbeat_account_id',
			'publisherId' => 'heartbeat_publisher_id',
			'jobId' => 'heartbeat_job_id',
			'marketingCloudId' => 'heartbeat_marketing_id',
			'trackingServer' => 'heartbeat_tracking_server',
			'customTrackingServer' => 'heartbeat_cstm_tracking_server',
		),
		"comscore" => array(
			'clientId' => 'comscore_client_id'
		)
	);

	foreach ( $plugin_map as $name => $plugin ) {
		foreach ( $plugin as $field => $var ) {
			if ( !empty( $analytics[$var] ) ) {
				$json['plugins'][$name][$field] = $analytics[$var];
			}
		}
	}

	// Set the DFP Ad Tag, which can also be overridden
	if ( !empty( $monetization['advanced_targeting'] ) ) {
		$json['plugins']['dfp'] = json_decode( $monetization['advanced_targeting'], true );
	} else {
		// User can close or change own dfp in shortcode
		if ( !empty( $attr['plugin_dfp_adtagurl'] ) && $attr['plugin_dfp_adtagurl'] !== 'false' ) {
			$json['plugins']['dfp']['adTagUrl'] = urldecode( $attr['plugin_dfp_adtagurl'] );
		} elseif ( !empty( $monetization['adtag'] ) ) {
			$json['plugins']['dfp']['adTagUrl'] = $monetization['adtag'];
		}
	}
	
	if( isset( $attr['dfpkeyvalues'] ) ) {
		$dfp_kv = json_decode( $attr['dfpkeyvalues'], true );
		
		$json['plugins']['dfp']['clientSide']['keyValues'] = 
				isset( $json['plugins']['dfp']['clientSide']['keyValues'] ) ?
					array_merge( $json['plugins']['dfp']['clientSide']['keyValues'], $dfp_kv ) : $dfp_kv;
	}
	
	//only in video mode, not in playilst mode
	if( isset( $attr['no_pr'] ) && 'true' == $attr['no_pr'] && isset( $json["video"] ) ) {
		unset( $json['plugins']['dfp'] );
	}
	
	if( isset( $json['video'] ) && is_string( $json['video'] ) && substr( $json['video'], 0, 1 ) === 'c' ) {
		$json['androidIntentPlayer'] = 'true';
	}

	# Allow theme/plugins to filter the JSON before outputting
	$json = apply_filters( 'anvato_anvp_json', $json, $attr );

	$format = "<div id='%s'></div><script data-anvp='%s' src='%s'></script>";

	return sprintf(
		$format, 
		esc_attr( $json['pInstance'] ), 
		esc_attr( json_encode( $json ) ), 
		esc_url( $player_url )
	);
}

add_shortcode( 'anvplayer', 'anvato_shortcode' );