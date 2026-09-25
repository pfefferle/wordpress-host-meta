# host-meta

- Contributors: pfefferle
- Donate link: https://notiz.blog/donate/
- Tags: host-meta, discovery, well-known, webfinger, fediverse
- Requires at least: 6.4
- Tested up to: 7.1
- Stable tag: 1.4.0
- Requires PHP: 7.4
- License: GPL-2.0-or-later
- License URI: https://www.gnu.org/licenses/gpl-2.0.html

Helps other apps and services find out what your site offers.

## Description

When an app or service wants to connect to your site, it first has to find out what your site supports. Where are the feeds? Is there an API? Can it look up the people who write here?

host-meta is a small file that answers these questions. It always lives at the same address, `/.well-known/host-meta`, so apps know where to look. The format is an [internet standard](https://www.rfc-editor.org/rfc/rfc6415).

This plugin adds that file to your WordPress site. You don't have to do anything else, there are no settings.

### Why would I need it?

Most visitors never see it. It is for other software. You need it if a plugin or service asks for it, for example:

* The [WebFinger](https://wordpress.org/plugins/webfinger/) plugin can add a link to it, so older apps can still find the profiles of your authors.
* The [Open Search Document](https://wordpress.org/plugins/open-search-document/) plugin adds your site search to it.
* Feed readers and other tools can use it to find your feeds and the WordPress API.

### What is in the file?

* Links to your feeds (Atom, RSS and RDF)
* A link to the WordPress REST API
* A link for blogging apps (RSD)
* Your site icon, if you set one under *Appearance > Customize > Site Identity*
* Your privacy policy, if you published one under *Settings > Privacy*
* Everything other plugins add to it

The file comes in two versions: XML at `/.well-known/host-meta` and JSON at `/.well-known/host-meta.json`. Both have the same content.

Logo by [Eran Hammer](https://web.archive.org/web/20091130230307/http://hueniverse.com/2009/11/host-meta-aka-site-meta-and-well-known-uris/)

## Frequently Asked Questions

### How do I check that it works?

Open `https://yoursite.com/.well-known/host-meta` in your browser (with your own domain). You should see a short XML file with some links.

### I get a "Page not found" error

WordPress probably did not pick up the new address yet. Go to *Settings > Permalinks* and click *Save Changes*. Then try again.

If your permalinks are set to "Plain" (addresses like `?p=123`), the file can't work. Pick any other option there, for example "Post name".

If it still does not work, your web server might block addresses that start with a dot (like `/.well-known/`). Your hosting provider can help with that.

### Does it change anything on my site?

No. Your pages, posts and theme stay the same. The plugin only adds the file.

### I am a developer, can I add my own links?

Yes, with the `host_meta` filter. The data uses the JSON format (JRD), the plugin creates the XML version from it.

    function custom_host_meta( $host_meta ) {
        $host_meta['links'][] = array(
            'rel'      => 'lrdd',
            'type'     => 'application/jrd+json',
            'template' => 'https://example.com/.well-known/webfinger?resource={uri}',
        );

        return $host_meta;
    }
    add_filter( 'host_meta', 'custom_host_meta' );

For the XML version only, there are two more actions: `host_meta_ns` adds namespaces to the root element and `host_meta_xrd` adds elements right before the closing tag. Both expect you to echo your output:

    function custom_host_meta_ns() {
        echo ' xmlns:foo="https://example.com/ns"';
    }
    add_action( 'host_meta_ns', 'custom_host_meta_ns' );

## Changelog

### 1.4.0

* Moved the code into the `Host_Meta` namespace, the global `Host_Meta` class still works but is deprecated
* Namespaces added with `host_meta_ns` no longer end up inside the `xmlns` attribute
* Aliases like `acct:` URIs and property values are no longer dropped by the URL escaping
* Properties with a `null` value are rendered with `xsi:nil`
* URLs in the JSON version are no longer HTML encoded
* The rewrite rules only match the exact well-known URLs
* Requires WordPress 6.4 and PHP 7.4
* Added links to the site icon and the privacy policy
* Removed the outdated translation template, WordPress.org provides the translations
* Added tests and a new readme

### 1.3.2

* update requirements

### 1.3.1

* fixed "flush rewrite rules"

### 1.3.0

* complete refactoring
* updated dependencies

### 1.2.2

* updated escaping methods
* small changes

### 1.2.1

* WordPress coding stye
* added missing „static“ to init function

### 1.2.0

* added WP-API discovery
* added RSD discovery

### 1.1.0

* removed deprecated `hm` namespace and items
* WordPress coding standard

### 1.0.4

* some small bug-fixes

### 1.0.3

* better compatibility with other plugins

### 1.0.2

* bug fix

### 1.0.1

* bug fix

### 1.0.0

* refactoring
* deprecated well-known plugin

### 0.4.3

* implemented new well-known hooks

### 0.4.2

* some changes to support http://unhosted.org

### 0.4.1

* fixed ostatus compatibility issue: http://status.net/open-source/issues/3235

### 0.4

* added jrd support

### 0.3

* implements the new well-known hook

### 0.2

* Initial release

## Installation

Follow the normal instructions for [installing WordPress plugins](https://wordpress.org/documentation/article/manage-plugins/#installing-plugins-1).

### Automatic installation

1. In your WordPress admin, go to *Plugins > Add New Plugin*.
2. Search for "host-meta".
3. Click *Install Now* and then *Activate*.

That's it, there are no settings.

### Manual installation

You only need this if your site can't install plugins on its own, or if you want to try the [latest development version](https://github.com/pfefferle/wordpress-host-meta).

1. Download the plugin from [WordPress.org](https://wordpress.org/plugins/host-meta/) or from the [GitHub releases](https://github.com/pfefferle/wordpress-host-meta/releases).
2. Unzip it. You should get a folder called `host-meta`.
3. Upload that folder to `wp-content/plugins/` on your server, for example with an FTP program.
4. In your WordPress admin, go to *Plugins* and click *Activate* below "host-meta".

Please make a backup of your site before you install plugins by hand.

### Installation with Composer

If you manage your site with Composer, you can install the plugin from [Packagist](https://packagist.org/packages/pfefferle/wordpress-host-meta):

    composer require pfefferle/wordpress-host-meta

Or from [WPackagist](https://wpackagist.org/search?q=host-meta), which mirrors the WordPress.org version:

    composer require wpackagist-plugin/host-meta

Composer puts the plugin into your plugins folder, you still have to activate it.

### After the installation

The plugin needs "pretty" permalinks. If your addresses look like `?p=123`, go to *Settings > Permalinks* and pick any other option, for example "Post name".

Then open `https://yoursite.com/.well-known/host-meta` (with your own domain) to check that it works.
