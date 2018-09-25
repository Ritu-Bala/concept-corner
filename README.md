# Concept Corner Theme and Site-specific Custom Plugins

Primary Developer: Roger Los roger@rogerlos.com

## Branches

* Master: Current "development" version.
* v3archive: Previous theme and plugins

## Concept Corner Struture, v4

This version of CC separates the program logic, as much as possible, from the presentation layer. Though this divorce is not perfect, it's close.

* The theme, concept-corner-v4, handles the templating system, CSS, JS, etc.
* The MU-Plugin "concept-corner" handles the program logic.

In this way, changing the visuals is considerably easier. In theory. Note that everything in the "/explore/*" part of the site is fed from a single template, explore.php, which is completely reliant on the content objects returned by the mu-plugin.

## Dependencies

CC relies on "pods" to handle the creation and administration of custom post types and taxonomies, though due to the sluggish performance of that plugin, we do not use their shortcodes or native display functions on the front end (we completely remove all filters on wordpress content types).

CC also uses a mildly customized version of S3 Media Storage, which can be found in this repository.

## Development

Please fork the master repository and make a pull request when you're finished working on it so it can be merged. As always (and I'm as guilty as anyone else), commenting changes is most welcome.

- RL