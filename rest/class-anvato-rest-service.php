<?php

class Anvato_Rest_Service {
	public static function init() {
		add_action(
			'rest_api_init',
			function() {
				register_rest_route(
					'anv-rest-service',
					'/search',
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( 'Anvato_Rest_Service', 'get_items' ),
						'permission_callback' => function () {
							return current_user_can( 'edit_posts' );
						},
					)
				);
				register_rest_route(
					'anv-rest-service',
					'/get-settings',
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( 'Anvato_Rest_Service', 'get_settings' ),
						'permission_callback' => function () {
							return current_user_can( 'edit_posts' );
						},
					)
				);
			}
		);
	}

	/**
	 * Get a collection of items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public static function get_items( $request ) {
		$req_params    = $request->get_params();
		$search_params = array();
		if ( ! empty( $req_params['query'] ) ) {
			$search_params['lk'] = sanitize_text_field( $req_params['query'] );
		}

		$search_params['page_no'] =
		isset( $req_params['page'] ) && ( (int) $req_params['page'] > 0 )
		? (int) $req_params['page']
		: 1;

		$search_params['station'] = $req_params['station'];
		$search_params['type']    = $req_params['type'];

		$selected_station = self::anvato_library()->get_sel_station();
		$result           = self::anvato_library()->search( $search_params );

		$response = new WP_REST_Response();

		if ( ! is_wp_error( $result ) ) {
			$content_list = array();

			foreach ( $result as $item ) {
				if ( 'feed' === $search_params['type'] && 'video' !== (string) $item->type ) {
					continue;
				}

				$content_list[] = self::get_content_data( $item, $search_params['type'], $selected_station );

				if ( 'live' === $search_params['type'] && ! empty( $item->stitched_streams ) ) {
					$content_list = array_merge( $content_list, self::get_channels_stitched_streams( $item, $selected_station ) );
				}
			}

			$response->set_data( $content_list );
			$response->set_status( 200 );
		} else {
			$error_list = array();

			foreach ( $result->errors as $value ) {
				$error_list[] = $value;
			}

			$response->set_data(
				array(
					'anv_rest_error' => true,
					'messages'       => $error_list,
				)
			);
			$response->set_status( 400 );
		}

		return rest_ensure_response( $response );
	}

	/**
	* Get a collection of items
	*
	* @param WP_REST_Request $request Full data about the request.
	* @return WP_Error|WP_REST_Response
	*/
	public static function get_settings() {
		$ret = array(
			'logo_url'      => ANVATO_URL . 'img/logo_smallest.png',
			'content_types' => array(
				array(
					'label' => 'Video clips',
					'value' => 'vod',
				),
				array(
					'label' => 'Live channels',
					'value' => 'live',
				),
				array(
					'label' => 'Playlists',
					'value' => 'playlist',
				),
				array(
					'label' => 'Feeds',
					'value' => 'feed',
				),
			),
			'owners'        => array_map(
				function( $owner ) {
					return array(
						'label' => $owner['label'],
						'value' => $owner['id'],
					);
				},
				Anvato_Settings::get_mcp_options()['owners']
			),
		);

		return rest_ensure_response( new WP_REST_Response( $ret, 200 ) );
	}

	/**
	 * Helper function to use the class instance.
	 *
	 * @return object.
	 */
	private static function anvato_library() {
		return Anvato_Library::get_instance();
	}

	/**
	 * Helper function to normalize response acc. to content type
	 *
	 * @return object.
	 */
	private function get_content_data( $content, $type, $station ) {
		$response = array(
			'id'          => null,
			'title'       => null,
			'description' => null,
			'meta'        => array(
				'duration'     => null,
				'category'     => null,
				'publish_date' => null,
				'video_count'  => null,
			),
			'thumbnail'   => null,
			'embed_id'    => null,
			'type'        => $type,
			'station'     => $station['id'],
		);

		switch ( $type ) {
			case 'vod':
				$response['id']                   = (string) $content->upload_id;
				$response['title']                = sanitize_text_field( (string) $content->title );
				$response['description']          = sanitize_text_field( (string) $content->description );
				$response['meta']['duration']     = (string) $content->duration;
				$response['meta']['category']     = sanitize_text_field( (string) $content->categories->primary_category );
				$response['meta']['publish_date'] = (string) $content->ts_added;
				$response['thumbnail']            = (string) $content->src_image_url;
				$response['embed_id']             = $response['id'];
				break;
			case 'live':
				$response['id']               = (string) $content->id;
				$response['title']            = sanitize_text_field( (string) $content->channel_name );
				$response['meta']['category'] = 'Live Stream';
				$response['thumbnail']        = '' === (string) $content->icon_url ? ANVATO_URL . 'img/channel_icon.png' : (string) $content->icon_url;
				$response['embed_id']         = (string) $content->embed_id;
				break;
			case 'playlist':
				$response['id']                  = (string) $content->playlist_id;
				$response['title']               = sanitize_text_field( (string) $content->playlist_title );
				$response['description']         = sanitize_text_field( (string) $content->description );
				$response['meta']['video_count'] = (string) $content->item_count;
				$response['thumbnail']           = ANVATO_URL . 'img/playlist_icon.png';
				$response['embed_id']            = $response['id'];
				break;
			case 'feed':
				$response['id']          = end( explode( '/', (string) $content->feed_url ) );
				$response['title']       = sanitize_text_field( (string) $content->name );
				$response['description'] = sanitize_text_field( (string) $content->description );
				$response['thumbnail']   = ANVATO_URL . 'img/feed_icon.png';
				$response['embed_id']    = $response['id'];
				break;
			default:
				break;
		}

		return $response;
	}

	/**
	 * Returns channels stitched streams
	 *
	 * @param int The video ID
	 * @param int Station Id
	 * @return string The shortcode
	 */
	private function get_channels_stitched_streams( $channel, $station ) {
		$streams = array();

		foreach ( $channel->stitched_streams->stitched_stream as $stream ) {
			$streams [] = array(
				'id'          => (string) $stream->id,
				'title'       => (string) $stream->name,
				'description' => null,
				'meta'        => array(
					'duration'     => null,
					'category'     => 'Stitched Stream',
					'published_on' => null,
					'video_count'  => null,
				),
				'type'        => 'live',
				'thumbnail'   => '' === (string) $channel->icon_url ? ANVATO_URL . 'img/channel_icon.png' : (string) $channel->icon_url,
				'embed_id'    => (string) $stream->embed_id,
			);
		}

		return $streams;
	}
}

Anvato_Rest_Service::init();
