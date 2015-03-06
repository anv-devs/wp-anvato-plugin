<?php
global $anvato_player_index;
$anvato_player_index = 0;

/**
 * Implement the Anvato shortcode.
 *
 * @param  array $attr Array of shortcode attributes
 * @return string       HTML to replace the shortcode
 */
function anvato_shortcode( $attr ) {
	global $anvato_player_index;
	$mcp = Anvato_Settings()->get_mcp_options();
	$player = Anvato_Settings()->get_options(Anvato_Settings::player_settings_key);
	$analytics = Anvato_Settings()->get_options(Anvato_Settings::analytics_settings_key);
	$monetization = Anvato_Settings()->get_options(Anvato_Settings::monetization_settings_key);

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
	$player_url = ! empty( $attr['player_url'] ) ? $attr['player_url'] : $player['player_url'];

	# Set the DFP Ad Tag, which can also be overridden
	if ( empty($monetization['advanced_targeting']) && ! empty( $monetization['adtag'] ) && ( ! isset( $attr['plugin_dfp_adtagurl'] ) || ( empty( $attr['plugin_dfp_adtagurl'] ) && $attr['plugin_dfp_adtagurl'] !== 'false' ) ) ) {
		$json['plugins']['dfp'] = array( 'adTagUrl' => $monetization['adtag'] );
	} elseif ( ! empty( $attr['plugin_dfp_adtagurl'] ) && $attr['plugin_dfp_adtagurl'] !== 'false' ) {
		$json['plugins']['dfp'] = array( 'adTagUrl' => urldecode($attr['plugin_dfp_adtagurl']) );
	} elseif ( !empty($monetization['advanced_targeting']) ) {
                $json['plugins']['dfp'] = json_decode($monetization['advanced_targeting'], true );
        }
	
	# Set the Tracker ID, which can be overridden
	if ( ! isset( $attr['tracker_id'] ) && ! empty( $analytics['tracker_id'] ) ) {
		$json['plugins']['analytics'] = array( 'pdb' => $analytics['tracker_id'] );
	} elseif ( isset( $attr['tracker_id'] ) && 'false' !== $attr['tracker_id'] ) {
		$json['plugins']['analytics'] = array( 'pdb' => $attr['tracker_id'] );
	}

	# Set the Adobe Analytics information, which can be overridden or canceled
	if ( ! isset( $attr['adobe_analytics'] ) || ( isset( $attr['adobe_analytics'] ) && 'false' != $attr['adobe_analytics'] ) ) {
		if ( ! isset( $attr['adobe_profile'] ) && ! empty( $analytics['adobe_profile'] ) ) {
			$json['plugins']['omniture']['profile'] = $analytics['adobe_profile'];
		} elseif ( isset( $attr['adobe_profile'] ) && 'false' !== $attr['adobe_profile'] ) {
			$json['plugins']['omniture']['profile'] = $attr['adobe_profile'];
		}

		if ( ! isset( $attr['adobe_account'] ) && ! empty( $analytics['adobe_account'] ) ) {
			$json['plugins']['omniture']['account'] = $analytics['adobe_account'];
		} elseif ( isset( $attr['adobe_account'] ) && 'false' !== $attr['adobe_account'] ) {
			$json['plugins']['omniture']['account'] = $attr['adobe_account'];
		}

		if ( ! isset( $attr['adobe_trackingserver'] ) && ! empty( $analytics['adobe_trackingserver'] ) ) {
			$json['plugins']['omniture']['trackingServer'] = $analytics['adobe_trackingserver'];
		} elseif ( isset( $attr['adobe_trackingserver'] ) && 'false' !== $attr['adobe_trackingserver'] ) {
			$json['plugins']['omniture']['trackingServer'] = $attr['adobe_trackingserver'];
		}
	}
        
        # Set Comscore Ids
        if ( ! isset( $attr['comscore_client_id'] ) && ! empty( $analytics['comscore_client_id'] ) ) {
		$json['plugins']['comscore'] = array( 'clientId' => $analytics['comscore_client_id'] );
	} elseif ( isset( $attr['comscore_client_id'] ) && 'false' !== $attr['comscore_client_id'] ) {
		$json['plugins']['comscore'] = array( 'clientId' => $attr['comscore_client_id'] );
	}
        
        
        $video_ids = explode(",", $json["video"]);
	if ( sizeof($video_ids) > 1 )
	{
		unset($json["video"]);
		$json["playlist"] = $video_ids;
	}
        elseif( isset($attr['playlist']) )
        {
                unset($json["video"]);
		$json["playlist"] = $attr['playlist'];
        }

        if ( isset($mcp['mcp']['tkx_key']) )
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
