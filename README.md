[![WordPress](https://img.shields.io/wordpress/v/host-meta.svg?style=flat-square)](https://wordpress.org/plugins/host-meta/) [![WordPress plugin](https://img.shields.io/wordpress/plugin/v/host-meta.svg?style=flat-square)](https://wordpress.org/plugins/host-meta/changelog/) [![WordPress](https://img.shields.io/wordpress/plugin/dt/host-meta.svg?style=flat-square)](https://wordpress.org/plugins/host-meta/) 

# host-meta #

**Contributors:** [pfefferle](https://profiles.wordpress.org/pfefferle/)  
**Donate link:** https://notiz.blog/donate/  
**Tags:** discovery, host-meta, xrd, jrd, ostatus  
**Requires at least:** 3.0.5  
**Tested up to:** 6.6  
**Stable tag:** 1.3.2  
**Requires PHP:** 5.2  
**License:** GPL-2.0-or-later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

host-meta for WordPress!

## Description ##

This plugin provides a host-meta - file for WordPress (RFC: http://tools.ietf.org/html/rfc6415).

From the RFC:

> Web-based protocols often require the discovery of host policy or metadata, where host is not a single resource but the entity controlling the collection of resources identified by URIs with a common host as defined.  While these protocols have a wide range of metadata needs, they often define metadata that is concise, has simple syntax requirements, and can benefit from storing its metadata in a common location used by other related protocols.

> Because there is no URI or a resource available to describe a host, many of the methods used for associating per-resource metadata (such as HTTP headers) are not available.  This often leads to the overloading of the root HTTP resource (e.g. 'http://example.com/') with host metadata that is not specific to the root resource (e.g. a home page or web application), and which often has nothing to do it.

> This memo registers the "well-known" URI suffix 'host-meta' in the Well-Known URI Registry established by, and specifies a simple, general-purpose metadata document for hosts, to be used by multiple Web-based protocols.

Logo by [Eran Hammer](http://hueniverse.com/2009/11/23/host-meta-aka-site-meta-and-well-known-uris/)

## Changelog ##

### 1.3.2 ###

* update requirements

### 1.3.1 ###

* fixed "flush rewrite rules"

### 1.3.0 ###

* complete refactoring
* updated dependencies

### 1.2.2 ###

* updated escaping methods
* small changes

### 1.2.1 ###

* WordPress coding stye
* added missing „static“ to init function

### 1.2.0 ###

* added WP-API discovery
* added RSD discovery

### 1.1.0 ###

* removed deprecated `hm` namespace and items
* WordPress coding standard

### 1.0.4 ###

* some small bug-fixes

### 1.0.3 ###

* better compatibility with other plugins

### 1.0.2 ###

* bug fix

### 1.0.1 ###

* bug fix

### 1.0.0 ###

* refactoring
* deprecated well-known plugin

### 0.4.3 ###

* implemented new well-known hooks

### 0.4.2 ###

* some changes to support http://unhosted.org

### 0.4.1 ###

* fixed ostatus compatibility issue: http://status.net/open-source/issues/3235

### 0.4 ###

* added jrd support

### 0.3 ###

* implements the new well-known hook

### 0.2 ###

* Initial release

## Installation ##

Follow the normal instructions for [installing WordPress plugins](https://codex.wordpress.org/Managing_Plugins#Installing_Plugins).

### Automatic Plugin Installation ###

To add a WordPress Plugin using the [built-in plugin installer](https://codex.wordpress.org/Administration_Screens#Add_New_Plugins):

1. Go to [Plugins](https://codex.wordpress.org/Administration_Screens#Plugins) > [Add New](https://codex.wordpress.org/Plugins_Add_New_Screen).
1. Type "`host-meta`" into the **Search Plugins** box.
1. Find the WordPress Plugin you wish to install.
    1. Click **Details** for more information about the Plugin and instructions you may wish to print or save to help setup the Plugin.
    1. Click **Install Now** to install the WordPress Plugin.
1. The resulting installation screen will list the installation as successful or note any problems during the install.
1. If successful, click **Activate Plugin** to activate it, or **Return to Plugin Installer** for further actions.

### Manual Plugin Installation ###

There are a few cases when manually installing a WordPress Plugin is appropriate.

* If you wish to control the placement and the process of installing a WordPress Plugin.
* If your server does not permit automatic installation of a WordPress Plugin.
* If you want to try the [latest development version](https://github.com/pfefferle/wordpress-host-meta).

Installation of a WordPress Plugin manually requires FTP familiarity and the awareness that you may put your site at risk if you install a WordPress Plugin incompatible with the current version or from an unreliable source.

Backup your site completely before proceeding.

To install a WordPress Plugin manually:

* Download your WordPress Plugin to your desktop.
    * Download from [the WordPress directory](https://wordpress.org/plugins/host-meta/)
    * Download from [GitHub](https://github.com/pfefferle/wordpress-host-meta/releases)
* If downloaded as a zip archive, extract the Plugin folder to your desktop.
* With your FTP program, upload the Plugin folder to the `wp-content/plugins` folder in your WordPress directory online.
* Go to [Plugins screen](https://codex.wordpress.org/Administration_Screens#Plugins) and find the newly uploaded Plugin in the list.
* Click **Activate** to activate it.
