<?php
/*
Plugin Name: host-meta
Plugin URI: http://notizblog.org/
Description: Host Metadata for WordPress (IETF-Draft: http://tools.ietf.org/html/draft-nottingham-site-meta-01)
Version: 0.1
Author: Matthias Pfefferle
Author URI: http://notizblog.org/
*/

//
add_filter('well-known', array('HostMetaPlugin', 'hostMetaUri'));

/**
 * the host-meta class
 *
 * @author Matthias Pfefferle
 */
class HostMetaPlugin {
  /**
   * add the the well-known uri
   *
   * @param array $wellKnown
   * @return array
   * @link @todo link to the well known plugin
   */
  function hostMetaUri($wellKnown) {
    return $wellKnown[] = array('host-meta' => array('HostMetaPlugin', 'printHostMeta'));
  }
  
  /**
   * prints the host-meta xrd file
   */
  function printHostMeta() {
    header('Content-Type: application/xrd+xml; charset=' . get_option('blog_charset'), true);
    
    echo "<?xml version='1.0' encoding='".get_option('blog_charset')."'?>\n";
    echo "<XRD xmlns='http://docs.oasis-open.org/ns/xri/xrd-1.0'\n";
    echo "     xmlns:hm='http://host-meta.net/xrd/1.0'";
      do_action('host_meta_ns');
    echo ">\n";
    echo "  <hm:Host>".parse_url(get_option('siteurl'), PHP_URL_HOST)."</hm:Host>\n";
      do_action('host_meta_xrd');
    echo "\n</XRD>";
  }
}
?>