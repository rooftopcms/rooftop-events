=== Rooftop Events ===
Contributors: rooftopcms
Tags: rooftop, api, headless, content, events
Requires at least: 4.7
Tested up to: 4.8.1
Stable tag: 4.8
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

rooftop-events is an API-first events plugin for Rooftop CMS

== Description ==

rooftop-events is an API-first events plugin and admin interface for managing events. The data structure is flexible
enough to allow you to use this on websites or in apps.

Either through the API, or WP admin, you can create & manage:
events & associated event instances, price lists, prices, price bands, and ticket types

Track progress, raise issues and contribute at http://github.com/rooftopcms/rooftop-events


== Installation ==

rooftop-events is a Composer plugin, so you can include it in your Composer.json.

Otherwise you can install manually:

1. Upload the `rooftop-events` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. There is no step 3 :-)


== Frequently Asked Questions ==

= Can this be used without Rooftop CMS? =

Yes, it's a Wordpress plugin you're welcome to use outside the context of Rooftop CMS. We haven't tested it, though.


== Changelog ==

= 1.2.1 =
* Tweak readme for packaging

= 1.2.0 =
* Fixes for updating event metadata when event instances are trashed
* Update event metadata when restoring instances
* Unit tests & request specs


== What's Rooftop CMS? ==

Rooftop CMS is a hosted, API-first WordPress CMS for developers and content creators. Use WordPress as your content management system, and build your website or application in the language best suited to the job.

https://www.rooftopcms.com
