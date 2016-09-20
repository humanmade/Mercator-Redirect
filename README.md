# Mercator Redirect
Redirect component for [Mercator](https://github.com/humanmade/Mercator).

Redirects to the domain listed in the `wp_blogs` table. With newer versions
of Mercator this should be treated as the primary domain.

The addon also supports redirecting to aliases for legacy data.

## Requirements
Mercator requires WordPress 3.9 or newer for the new sunrise processes. Mercator
also requires PHP 5.3+ due to the use of namespaced code.

## Installation
Include the file `redirect.php` from your `sunrise.php` in the same way you include Mercator itself.

For example:

```php
<?php
// Default mu-plugins directory if you haven't set it
defined( 'WPMU_PLUGIN_DIR' ) or define( 'WPMU_PLUGIN_DIR', WP_CONTENT_DIR . '/mu-plugins' );

require WPMU_PLUGIN_DIR . '/mercator/mercator.php';
require WPMU_PLUGIN_DIR . '/mercator-redirect/redirect.php';
```

## Filters
You can modify SSO behaviour for example in a local environment with the
following filters in `sunrise.php`:

**mercator.redirect.enabled**

Defaults to `true`

```php
// Disable redirects
add_filter( 'mercator.redirect.enabled', '__return_false' );
```

**mercator.redirect.admin.enabled**

Defaults to `false`

```php
// Enable redirecting the admin
add_filter( 'mercator.redirect.admin.enabled', '__return_true' );
```

**mercator.redirect.legacy.enabled**

Defaults to `false`

```php
// Enable legacy redirects
add_filter( 'mercator.redirect.legacy.enabled', '__return_true' );
```


## License
Mercator is licensed under the GPLv3 or later.

## Credits
Created by Human Made for high volume and large-scale sites, such as [Happytables](http://happytables.com/). We run Mercator on sites with millions of monthly page views, and thousands of sites.

Written and maintained by [Ryan McCue](https://github.com/rmccue). Thanks to all our [contributors](https://github.com/humanmade/Mercator-SSO/graphs/contributors).

Mercator builds on concepts from [WPMU Domain Mapping][], written by Donncha O'Caoimh, Ron Rennick, and contributors.

Mercator relies on WordPress core, building on core functionality added in [WP27003][]. Thanks to all involved in the overhaul, including Andrew Nacin and Jeremy Felt.

[WPMU Domain Mapping]: http://wordpress.org/plugins/wordpress-mu-domain-mapping/
[WP27003]: https://core.trac.wordpress.org/ticket/27003

Interested in joining in on the fun? [Join us, and become human!](https://hmn.md/is/hiring/)
