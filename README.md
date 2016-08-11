# WP Nav Helper - Page Sections

This plugin is designed to work with Advanced Custom Fields. It will allow you to select a page and then create links to a section on that page, assuming that page has ACF meta that follows a certain schema:

1. There's a field with a Field Name of `page_sections` that is a Repeater field.
1. `page_sections` Repeater field has any number of fields, one of them having a Field Name of `title`.

The plugin will create a link that includes a sanitized copy of the title. The title is passed through WordPress' `sanitize_title` function. The link will include the url of the page, so you can link to other pages.

## Screenshots

### ACF Field Group
![screenshot4](assets/images/screenshot4.png)

### Nav Helper in action
![screenshot1](assets/images/screenshot1.png)
![screenshot2](assets/images/screenshot2.png)
![screenshot3](assets/images/screenshot3.png)
