<?php

/**
 * Backbone templates for various views for the Anvato service.
 */
class MEXP_Anvato_Template extends MEXP_Template {

	private $anv_settings = array();

	public function __construct() {
		$this->anv_settings = Anvato_Settings()->get_options( Anvato_Settings::general_settings_key );
	}

	/**
	 * Outputs the Backbone template for an item within search results.
	 *
	 * @param string $id  The template ID.
	 * @param string $tab The tab ID.
	 */
	public function item($id, $tab) {
		?>
		<div id="mexp-item-<?php echo esc_attr($tab); ?>-{{ data.id }}" class="mexp-item-area" data-id="{{ data.id }}">
			<div class="mexp-item-container clearfix">
				<div class="mexp-item-thumb" style="background-image: url({{ data.thumbnail }})" class="thickbox">
					<img src="<?php echo esc_url(ANVATO_URL . 'img/play.png') ?>" onclick="anv_preview('<?php echo $this->anv_settings['mcp_id'] ?>', '{{ data.id }}');" />
					<span>{{ data.meta.duration }}</span>
				</div>
				<div class="mexp-item-main">
					<div class="mexp-item-content">
						<span class="anv-title">{{ data.content }}</span>
						<span class="anv-desc">{{ data.meta.description }}</span>
					</div>
					<div class="mexp-item-meta">
						<span>Category:</span>{{ data.meta.category }}
					</div>
					<div class="mexp-item-meta">
						<span>Published:</span>{{ data.date }}
					</div>
				</div>
			</div>
		</div>

		<a href="#" id="mexp-check-{{ data.id }}" data-id="{{ data.embed_id }}" class="check" title="<?php esc_attr_e('Deselect', 'mexp'); ?>">
			<div class="media-modal-icon"></div>
		</a>
		<?php
	}

	/**
	 * Outputs the Backbone template for a select item's thumbnail in the footer toolbar.
	 *
	 * @param string $id The template ID.
	 */
	public function thumbnail($id) {
		?>
		<div class="mexp-item-thumb">
			<img src="{{ data.thumbnail }}">
		</div>
		<?php
	}

	/**
	 * Outputs the Backbone template for a tab's search fields.
	 *
	 * @param string $id  The template ID.
	 * @param string $tab The tab ID.
	 */
	public function search($id, $tab) {
		?>
		<form action="#" class="mexp-toolbar-container clearfix tab-all" id='anv_search_form'>
			<input type="text" name="q" value="{{ data.params.q }}" class="mexp-input-text mexp-input-search" 
				   size="40" placeholder="<?php esc_attr_e('Search for videos', 'mexp'); ?>" >
			<input class="button button-large" type="submit" value="<?php esc_attr_e('Search', 'mexp'); ?>">
			<select onchange="anv_type_select(this)" name='type'>
				<option value='vod'>Video clips</option>
				<option value='live'>Live channel</option>
			</select>
			<div class="spinner"></div>
			<div class="anv-logo">
				<img src="<?php echo esc_url(ANVATO_URL . 'img/logo_small.png') ?>" alt="<?php esc_attr_e('Anvato Video Plugin', ANVATO_DOMAIN_SLUG); ?>" />
			</div>
		</form>
		<?php
	}

}