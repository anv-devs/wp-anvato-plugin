Anvato Video Plugin
===================

A WordPress Plugin for integrating the Anvato video player. This plugin lets you add a shortcode for Anvato video into your content. You can easly find your Anvato video by searching with this plugin. 

Setup
-----
In order to get this working in your WordPress installation, you have to follow
the next steps:

* Get the below configuration parameters form Anvato
	* `mcp_id`
	* `station_id`
	* `profile`
	* `player_url`
* Set default video player size & autoplay state
	* `width`
	* `height`
	* `autoplay`
* Set tracking parameters, give empty if you use default 
	* `plugin_dfp_adtagurl`
	* `tracker_id`
	* `adobe_profile`
	* `adobe_account`
	* `adobe_trackingserver`

Usage
-----
# Shortcode

This plugin has a shortcode supports to prepare Anvato video embed code automatically.

## Basic shortcode usage

`[anvplayer video="282411"]`

### Available shortcode attributes
* `video`
* `width`
* `height`
* `autoplay`
* `adobe_analytics` (accepts only `false`, which removes all Adobe settings from the output)

Wordress 5 Support
-----

### Requirements

* Wordpress 5.3.2 must be installed.
* Wordpress Rest service must be enabled.

Anvato Gutenberg block implementation resides in plugin's `gutenberg` directory. In this directory, 

* `src` is the directory where block source code resides
* `build` is the directory where compiled and bundled block code resides, 

### Build steps
If you would like to develop or compile Anvato Block source, you will follow build steps below .

 1. Change current directory to `gutenberg`
 2. Make sure `node` and `npm` are installed on your machine.
 3. `npm install`
 4. `npm run build`
 5. (Optional - live reload) `npm run start:custom`


Linting
-----

We are linting our codebase using [PHP Code Sniffer](https://wpvip.com/documentation/how-to-install-php-code-sniffer-for-wordpress-com-vip/) along with two Wordpress rulesets such as `Wordpress-Core` and `WordpressVIPminimum`.

### Applying rulesets
To lint the codebase, please run below commands in the plugin root directory.

```
phpcs --standard=WordPress-Core ./**/*.php --no-cache
```

```
phpcs --standard=WordPressVIPminimum ./**/*.php -n --no-cache
```

###	Fixing errors

To fix automatically, please run below commands in the plugin root directory.

```
phpcbf --standard=WordPress-Core ./**/*.php --no-cache
```

```
phpcbf --standard=WordPressVIPminimum ./**/*.php -n --no-cache
```


## Plugin Settings

### Player

`Anvato Player` parameters (`player URL, width, height, title and share link`) are used to decorate player instances. We support additional custom player parameters in `Embed Parameters` field as `JSON` object that are passed to Anvato Player without modification.

### Analytics

Adobe, Comscore and Heartbeat settings have as-is attributes. Please see below for exceptional cases.

Heartbeat Analytics

`Account Info` field can be JSON object that includes all attributes for Heartbeat Analytics. It is also can be used an `account Id` as a single field along with other attributes in the settings.

Google Analytics

`Account Info` field can be JSON object that includes all attributes for Google Analytics. We also support shortcode attribute level Google Analytics tracking ID.

Example: [anvplayer video="9876543" plugin_google_trackerid="UA-12345"]

### Monetization

`One` of the following fields is enough to set `Anvato Player's Google Ad Manager Plugin (formerly DFP)` in `Monetization Tab`. Don't set both fields.

- Google Ad Manager(GAM) Premium Ad Tag

	It is used to set GAM Plugin Adtag URL. It can be also set in shortcode as attribute.

	Example: [anvplayer video="9876543" plugin_dfp_adtagurl="[URL]"]

- Advanced Targeting

	`JSON` object that are passed to `Anvato Player` as `GAM Plugin` object without modification.

	To override GAM client side key values, you can use attribute level setup.

	Example: [anvplayer video="9876543" dfpkeyvalues="[keyValues]"]