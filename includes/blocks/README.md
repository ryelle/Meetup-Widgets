Blocks
======

Meetup-Widgets currently supports two blocks: a list of upcoming events by group, and a list of upcoming events for the API key owner.

## Project Setup

To work on the meetup-widgets blocks, you need node, npm & composer installed. After making sure those are installed, install the project's dependencies:

	composer install
	npm install

If you have `WP_DEBUG` set to true, the plugin looks for the files on the webpack dev server, so you'll need to start it:

	npm start

If you have `WP_DEBUG` set to false, you can just build the files directly:

	npm run build

## Development

The editing interface for both blocks lives in `blocks/src/components`. The content returned from the component's `render` function is presented in the editor. This content is _not_ saved to the database. Instead, WordPress calls the PHP render functions in `includes/class-meetup-widgets-blocks.php` for each block. This guarantees up-to-date event results, because the data is queried every page load (with a 2hr API cache).

### Handlebars

Since the data is displayed in both JS (when editing) and PHP (when viewing), I've extracted the templates out into handlebars.

The javascript side uses [handlebars-loader](https://www.npmjs.com/package/handlebars-loader) to pre-compile the template into a function that accepts the event & attribute data & returns HTML. This needs to be "dangerously set" into the react component.

The PHP side uses [handlebars.php](https://github.com/XaminProject/handlebars.php), which is installed via composer. There are quirks with handlebars when used in this way, some nested `if/each` sections don't behave as expected. This is why the if/else sections are structured as they are.

### CSS

The CSS is built from `style.css`, and is currently very minimal. This is loaded in the gutenberg editor, and on the frontend of the site.
