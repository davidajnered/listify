=== Listify ===
Contributors: davidajnered
Donate link: http://davidajnered.com/
Tags: list, lists, multisite, site, multi, simple, cross, widget, posts, post, pages, page, comments, comment
Requires at least:
Tested up to: 3.2
Stable tag: beta1

Listify is the easy way to create lists for multisite blogs.

== Description ==

Listify is the easy way to list posts, pages and comments. It's main purpose is to make it easy to list things from other blogs in a multisite installation, but it works equally well for listing things on a single blog.

== Installation ==

Unzip the plugin, put it in the plugin folder and activate it. Go to the settings page and add a list. There are three ways to add a list to your site. Your can use the widget, short tags or you can call the listify function from your theme.

= Widget =
Easy! Just drag the widget to the sidebar where you want to use it and select your list from the drop down. Done.

= Short Tag =
Also easy. The short tag looks like `[listify list="your-lists-name"]`.

= Theme =
Call lustily from your theme using `<?php listify($list_name, $return)?>`. $list_name is the name of your list. $return is a boolean value. If true it will return the list instead of printing it.

== Frequently Asked Questions ==

== Screenshots ==

1. The plugin settings page

== Changelog ==

= 0001 =

== Upgrade Notice ==

= 0001 =