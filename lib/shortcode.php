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
			'width' => $player['width'] . $player['height_type'],
			'height' => $player['height'] . $player['width_type'],
			'video' => null,
			'station'=>null,
			'ext_id' => null,
			'sharelink' => null,
			'autoplay' => false,
			'thumbnail_image' => '',
		),
		$attr, 
		'anvplayer'
	);

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
	
	if ( !empty($json['sharelink']) ) {
		$json['shareLink'] = $json['sharelink'];
	} else if ( !empty($player['default_share_link']) ) {
		$json['shareLink'] = $player['default_share_link'];
	}

	unset($json['sharelink']);

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
			'chapterTracking'=>'chapter_tracking',
			'version' => 'heartbeat_version'
		),
		"comscore" => array(
			'clientId' => 'comscore_client_id',
			'c3' => 'comscore_c3'
		)
	);

	foreach ( $plugin_map as $name => $plugin ) {
		foreach ( $plugin as $field => $var ) {
			if ( !empty( $analytics[$var] ) ) {
				$json['plugins'][$name][$field] = $analytics[$var];
				if($field == 'chapterTracking')
					$json['plugins'][$name][$field] = ($analytics[$var] == 'true' ? true : false);
			}
		}
	}
	
	foreach($mcp['owners'] as $owner) {
		if($owner['id'] == $json['station'] && !empty($owner['access_key']))
		{
			$json['accessKey'] = $owner['access_key'];
			$json['token'] = 'default';
		}
	}
	
	unset($json['station']);
	
	//Special consideration for heartbeat analytics
	$account_obj = json_decode($analytics['heartbeat_account_id']);
	if(is_object($account_obj))
	{
		$json['plugins']['heartbeat']['account'] = $account_obj;
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
	
	// this is an amp experience
	if ((function_exists('is_amp_endpoint') && is_amp_endpoint()) || has_action('simple_fb_reformat_post_content')) {
		
		if(isset($json['video']))
		{
			$json['v'] = $json['video'];
			unset($json['video']);
		}
		
		if(isset($json['playlist']))
		{
			$json['pl'] = $json['playlist'];
			unset($json['playlist']);
		}
		
		$json['m'] = $json['mcp'];
		
		unset($json['mcp']);
		unset($json['width']);
		unset($json['height']);
		unset($json['pInstance']);
		
		$json['p'] = 'default';
		$json['html5'] = true;
		
	}

	// $json['html5'] = true; // Removed because it breaks live streams

	# Allow theme/plugins to filter the JSON before outputting
	$json = apply_filters( 'anvato_anvp_json', $json, $attr );

	$is_instant_articles_enabled = false;
	// Detect support for Google AMP
	if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
		$is_instant_articles_enabled = true;
		$is_amp_articles_enabled = true;
	}
	// Detect support for Facebook Instant Articles
	if ( has_action('simple_fb_reformat_post_content') || ( defined('INSTANT_ARTICLES_SLUG') && INSTANT_ARTICLES_SLUG ) ) {
		$is_instant_articles_enabled = true;
		$is_fia_articles_enabled = true;
	}

	// InstantArticles handle (AMP, Facebook Instant Articles etc)
	if ( $is_instant_articles_enabled ) {

		$iframe_src = 'https://w3.cdn.anvato.net/player/prod/anvload.html?key=' . 
			base64_encode( json_encode( $json ) );
		$iframe_width = 640;
		if ( !empty( $player['width'] ) && 'px' === $player['width_type'] ) {
			$iframe_width = $player['width'];
		}
		$iframe_height = 360;
		if ( !empty( $player['height'] ) && 'px' === $player['height_type'] ) {
			$iframe_height = $player['height'];
		}

		$iframe_inner = ''; // placeholder for inner content of the iframe - placeholder etc

		// Construct the element
		$iframe_html =
			'<iframe ' .
				'src="' . esc_url( $iframe_src ) . '" ' .
				'width="' . esc_attr( $iframe_width ) . '" height="' . esc_attr( $iframe_height ) . '" ' . 
				'sandbox="allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox" ' .
				'allowfullscreen ' .
				'layout="responsive" ' .
				'scrolling="no" ' .
				'frameborder="0" ' .
			'>' .
				$iframe_inner .
			'</iframe>';

		return $iframe_html;

	}

	// Regular player
	$format = "<div id='%s'></div><script data-anvp='%s' src='%s'></script>";
	return sprintf(
		$format, 
		esc_attr( $json['pInstance'] ), 
		esc_attr( json_encode( $json ) ), 
		esc_url( $player_url )
	);
}

add_shortcode( 'anvplayer', 'anvato_shortcode' );
