=== Meetup Widgets ===
Contributors: ryelle
Donate link: https://www.paypal.com/us/cgi-bin/webscr?cmd=_flow&SESSION=7OzM1TYh271yVnb30fI26d7BkYaEcnM9pmQnT42lWbEm7BsLORp4G_2UTRW&dispatch=5885d80a13c0db1f8e263663d3faee8d4026841ac68a446f69dad17fb2afeca3
Tags: meetup, meetups, meetup.com, widget
Requires at least: 3.3
Tested up to: 4.4.1
Stable tag: 2.2.1

Adds widgets displaying information from a meetup.com group.

== Description ==

For use with a [Meetup.com](http://meetup.com) group.

This plugin creates two widgets: one a list of events from a meetup group (by ID or URL name, for multiple groups use IDs); the other shows details about single event (by ID) with a link to RSVP - using OAuth if keys are specified, otherwise just a link to the event on meetup.com. Does require at least an API key (which it asks for on the settings page).

== Installation ==

1. Extract `meetup-widgets.zip` to `meetup-widgets` & Upload that folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Set up your API key (and optional OAuth key & secret) in your General Settings screen.
1. Use your new widgets!

== Frequently Asked Questions ==

= How do I find my API key? =

Log in to your Meetup.com account, then visit the [Getting an API Key](http://www.meetup.com/meetup_api/key/) page.

= How do I find my OAuth key & secret? =

You'll need to create an OAuth consumer for your website. Visit [Your OAuth Consumers](http://www.meetup.com/meetup_api/oauth_consumers/) for more information & to get started.

= How do I find my event ID? =

It's in the event page's URL: http://www.meetup.com/`[group ID]`/events/`[event ID]`/

= How do I find my group ID? =

If your meetup group is set up at meetup.com/`[group URL name]`, the part after `meetup.com/` is one of your group identifiers (the URL name). The other possible identifier is your group's ID number. 

== Screenshots ==

1. Example of the single event detail widget, shows title, date, an excerpt of the description, number of currently-rsvp'd attendees, a link to RSVP (through OAuth if configured), and the location (linking to a google map).
2. Example of the upcoming event list widget. Lists a set number of events from the group you specify, title & date.

== Upgrade Notice ==

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