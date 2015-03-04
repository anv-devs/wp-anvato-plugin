/*
 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 
 */

// CONTROLLER OVERIDE:

media.controller.MEXP = media.controller.State.extend({

	initialize: function(options) {
		this.props = new Backbone.Collection();
		for (var tab in options.tabs) {
			this.props.add(new Backbone.Model({
				id: tab,
				params: {},
				page: null,
				min_id: null,
				max_id: null,
				fetchOnRender: options.tabs[ tab ].fetchOnRender
			}));
		}

		this.props.add(new Backbone.Model({
			id: '_all',
			selection: new Backbone.Collection()
		}));

		this.props.on('change:selection', this.refresh, this);
	},
	refresh: function() {
		this.frame.toolbar.get().refresh();
                this.attachEvents();
	},
	mexpInsert: function() {
		var shortcode = "";
		if ( window.anv_playlist_enabled )
			shortcode = this.createPlaylist();
		else
			shortcode = this.attachVideo();

		media.editor.insert(shortcode);

		this.frame.close();
	},
	attachVideo: function()
	{
		var selection = this.frame.content.get().getSelection(),
				urls = [];

		selection.each(function(model) {
			urls.push(model.get('url'));
		}, this);
		selection.reset();

		if (typeof(tinymce) === 'undefined' || tinymce.activeEditor === null || tinymce.activeEditor.isHidden()) {
			return _.toArray(urls).join("\n\n");
		}

		return "<p>" + _.toArray(urls).join("</p><p>") + "</p>";

	},
	createPlaylist: function()
	{
		var selection = this.frame.content.get().getSelection(),
				video_ids = [];

		selection.each(function(model) {
			video_ids.push(model.get('id'));
		}, this);
		selection.reset();

		_shortcode = '[anvplayer video="' + video_ids.join(',') + '"]';

		if (typeof(tinymce) === 'undefined' || tinymce.activeEditor === null || tinymce.activeEditor.isHidden()) {
			return _shortcode + "\n\n";
		} else {
			return "<p>" + _shortcode + "</p>";
		}
	}
        
});

function anv_preview(mcp_id, video_id, type)
{
        var ptype = type === 'video' || type === 'live' ? 'video' : 'playlist';
	var player_js_url = "http://qa.up.anv.bz/dev/scripts/anvload.js";
	var script = jQuery("<script src='"+player_js_url+"'></script>");
	script.attr("data-anvp", '{\"mcp\":\"' + mcp_id + '\", \"pInstance\": \"anv_preview_cont\", \"'+ptype+'\":\"' + video_id + '\", \"autoplay\": \"true\"}');

	var div = jQuery("<div class='anv_preview'><div id=\"anv_preview_cont\"></div><a class=\"anv_preview_close\" href='Javascript://' onclick=\"anv_preview_close()\"></a></div>");
	div.append(script).insertBefore('.mexp-content-wp_anvato > .mexp-items');
	jQuery('.mexp-content-wp_anvato > .mexp-items').css('opacity', 0.25);
}

function anv_preview_close()
{
	jQuery('.anv_preview').remove();
	jQuery('.mexp-content-wp_anvato > .mexp-items').css('opacity', 1);
}

function anv_type_select(el)
{
	if ( el.value === 'vod') {
		window.anv_playlist_enabled = true;
	}
	else
	{
		window.anv_playlist_enabled = false;
	}
	
	jQuery('#anv_search_form').submit();
}

window.anv_playlist_enabled = true;