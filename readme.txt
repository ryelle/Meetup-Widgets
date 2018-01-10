=== Meetup Widgets ===
Contributors: ryelle
Tags: meetup, meetups, meetup.com, widget, gutenberg
Requires at least: 4.8
Requires PHP: 5.6
Tested up to: 4.9
Stable tag: 3.0.0

Adds widgets displaying information from a meetup.com group.

== Description ==

For use with a [Meetup.com](http://meetup.com) group.

This plugin creates three widgets:

1. A list of events from a meetup group (by ID or URL name, for multiple groups use IDs)
2. A list of upcoming events for the API key's owner.
3. Details of a single event (by ID) with a link to RSVP.

== Installation ==

1. Extract `meetup-widgets.zip` to `meetup-widgets` & Upload that folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Set up your API key in your General Settings screen.
4. Use your new widgets!

== Frequently Asked Questions ==

= How do I find my API key? =

Log in to your Meetup.com account, then visit the [Getting an API Key](http://www.meetup.com/meetup_api/key/) page.

= How do I find my event ID? =

It's in the event page's URL: http://www.meetup.com/`[group ID]`/events/`[event ID]`/

= How do I find my group ID? =

If your meetup group is set up at meetup.com/`[group URL name]`, the part after `meetup.com/` is one of your group identifiers (the URL name). The other possible identifier is your group's ID number.

= What happened to the OAuth feature? =

Previous to version 3.0, this plugin had a feature where you could RSVP to an event using OAuth to transparently log in to Meetup.com, without leaving the host website. This was using PHP's core OAuth implementation, which used OAuth 1, an outdated approach. Since there is no core support for OAuth 2, and it also requires sites to have HTTPS enabled, I've decided it's better to remove the feature. **Potential attendees can still RSVP from the widget**, it will now take them to the event on Meetup.com.

== Screenshots ==

1. Example of the single event detail widget, shows title, date, an excerpt of the description, number of currently-rsvp'd attendees, a link to RSVP, and the location (linking to a google map).
2. Example of the upcoming event list widget. Lists a set number of events from the group you specify, title & date.

== Upgrade Notice ==

= 3.0.0 =

* NEW: Gutenberg support: 2 new blocks for listing events
* NEW: API endpoints for fetching Meetup.com data
* BREAKING: Removal of OAuth RSVP feature
* BREAKING: Set minimum PHP version to 5.6
* UPDATE: Refactor basically the entire plugin
* UPDATE: Refactor widgets into new `includes/widgets/*` files
* UPDATE: Move templates into `includes/templates/` folder
* DEVELOPER: Add PHP CodeSniffer, clean up flagged issues
* DEVELOPER: Add webpack, babel, eslint for building gutenberg blocks
* DEVELOPER: Add Handlebars for the gutenberg block templates (widgets still use themeable php templates)

= 2.2.1 =

* Code cleanup
* Use `__construct` for the widget's parent constructor (gets rid of the deprecation warning)

= 2.2 =
* Fix bug where widgets could only be used once per page, due to `load_template` calling `require_once` by default.
* Add clases to `<p>`s in meetup-single template, so you can style the title/date/summary/location without jumping through CSS hoops.
* Add 2 filters, `vsm_no_date_text` and `vsm_no_location_text`, for filtering the text displayed if there is no date or location set.
* Some escaping of API data on the meetup templates.

= 2.1 =
* Stray semicolon! The list template was causing a PHP syntax error. Thanks to Jordan Wagnon for letting me know.

= 2.1 =
* At the suggestion of [Harlan Harris](http://datacommunitydc.org/), I investigated using multiple groups in one widget - happily, it worked by default. I've adjusted the validation in the widget to allow multiple group IDs (does need to be the ID numbers, not URL name).
* Add a new widget! Meetup User Events displays all events for a user, specifically the user who created the API key.
* Use `get_template_part` to allow theme developers to create their own displays. Documentation will be available on [my website](http://redradar.net/category/plugins/meetup-widgets/) shortly.

= 2.0.2 =
* Validation function was not actually working. Now we're correctly only saving valid keys - valid meaning 0-40 char alphanumeric strings.

= 2.0.1 =
* Add minutes to list event widget

= 2.0 =
* Change to using admin-ajax to process OAuth requests, rather than custom file.
* Change basic code structure to work with other (in-development) meetup plugins.
* Add warning message if the server does not have OAuth.
* use `wp_trim_words` rather than writing something custom
* pull apart a translated string somewhat
