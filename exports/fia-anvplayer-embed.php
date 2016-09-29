<?php

// deregister original shortcode
remove_shortcode( 'anvplayer' );

// add new facebook shortcode for anvplayer
add_shortcode( 'anvplayer', function( $args ) {
	
	$parameters = anvato_shortcode_get_parameters( $attr );
	$json = $parameters['json'];
	
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
	
	$iframe_src = 'https://w3.cdn.anvato.net/player/prod/anvload.html?key='.base64_encode( json_encode( $json ) );
	
	$iframe_width = 640;
	if ( !empty( $parameters['player']['width'] ) && 'px' === $parameters['player']['width_type'] ) {
		$iframe_width = $parameters['player']['width'];
	}
	
	$iframe_height = 360;
	if ( !empty( $parameters['player']['height'] ) && 'px' === $parameters['player']['height_type'] ) {
		$iframe_height = $parameters['player']['height'];
	}
	
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
	'></iframe>';
	/*
		Adding "figure" tag deletes the shortcode processing.
		Escaping will be done automatically and should not be included.
		*/
	return $iframe_html;
} );