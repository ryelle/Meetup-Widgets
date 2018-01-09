API
===

This folder contains controllers for Meetup.com proxy endpoints, using the API key set in wp-admin. These endpoints are each accessible by `GET` requests only, and only by logged-in users. 

### Event Lists

	meetup/v1/events/self

This lists upcoming events for the user who created the API key. 

	meetup/v1/events/(?P<group_urlname>[^/]+)

This lists upcoming events for the group passed through. This is the group `urlname`, for example, if the URL for your meetup is `https://www.meetup.com/boston-wordpress-meetup/`, the `urlname` is `boston-wordpress-meetup`.

### Single Event

	meetup/v1/events/(?P<group_urlname>[^/]+)/(?P<event_id>[^/]+)

This gets information about a single event in a group. See above for the `group_urlname` description. The event ID is the part after `/events/` in a URL for a single event.

### Groups List

	meetup/v1/groups/self

This lists the groups that the API key owner is a member of.
