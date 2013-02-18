<?php
/*
Plugin Name: host-meta
Plugin URI: http://wordpress.org/extend/plugins/host-meta/
Description: Host Metadata for WordPress (RFC: http://tools.ietf.org/html/rfc6415)
Version: 1.0.0
Author: Matthias Pfefferle
Author URI: http://notizblog.org/
*/

/**
 * the host-meta class
 *
 * @author Matthias Pfefferle
 */
class HostMetaPlugin {
  /**
   * adds some query vars
   *
   * @param array $vars
   * @return array
   */
  function query_vars($vars) {
    $vars[] = 'host-meta';
    $vars[] = 'format';

    return $vars;
  }
  
  /**
   * Add rewrite rules
   *
   * @param WP_Rewrite
   */
  function rewrite_rules($wp_rewrite) {
    $host_meta_rules = array(
      '(.well-known/host-meta.json)' => 'index.php?host-meta=true&format=jrd',
      '(.well-known/host-meta)' => 'index.php?host-meta=true&format=xrd',
    );

    $wp_rewrite->rules = $host_meta_rules + $wp_rewrite->rules;
  }
  
  /**
   * renders the output-file
   *
   * @param array
   */
  function parse_request($wp) {
    // check if "host-meta" param exists
    if (!array_key_exists('host-meta', $wp->query_vars)) {
      return;
    }
    
    $format = "jrd";
    if (array_key_exists('format', $wp->query_vars)) {
      $format = $wp->query_vars["format"];
    }
    
    $host_meta = apply_filters('host_meta', array(), $wp->query_vars);
    
    do_action("host_meta_render", $format, $host_meta, $wp->query_vars);
    do_action("host_meta_render_{$format}", $host_meta, $wp->query_vars);
  }
  
  /**
   * renders the host-meta file in xml
   */
  function render_xrd($host_meta) {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/xrd+xml; charset=" . get_bloginfo('charset'), true);
    
    echo "<?xml version='1.0' encoding='".get_bloginfo('charset')."'?>\n";
    echo "<XRD xmlns='http://docs.oasis-open.org/ns/xri/xrd-1.0'\n";
    echo "     xmlns:hm='http://host-meta.net/xrd/1.0'\n";
      // add xml-only namespaces
      do_action('host_meta_ns');
    echo ">\n";
    echo "  <hm:Host>".parse_url(site_url(), PHP_URL_HOST)."</hm:Host>\n";

    echo self::jrd_to_xrd($host_meta);
      // add xml-only content
      do_action('host_meta_xrd');
    
    echo "\n</XRD>";
    exit;
  }
  
  /**
   * renders the host-meta file in json
   */
  function render_jrd($host_meta) {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=" . get_bloginfo('charset'), true);

    echo json_encode($host_meta);
    exit;
  }
  
  /**
   * generates the host-meta base array (and activate filter)
   *
   * @return array
   */
  function generate_default_content($host_meta) {
    $host_meta = array("subject" => site_url());
    
    return $host_meta;
  }
  
  /**
   * recursive helper to generade the xrd-xml from the jrd array
   *
   * @param string $host_meta
   * @return string
   */
  function jrd_to_xrd($host_meta) {
    $xrd = null;

    foreach ($host_meta as $type => $content) {
      // print subject
      if ($type == "subject") {
        $xrd .= "<Subject>$content</Subject>";
        continue;
      }
      
      // print aliases
      if ($type == "aliases") {
        foreach ($content as $uri) {
          $xrd .= "<Alias>".htmlentities($uri)."</Alias>";
        }
        continue;
      }
      
      // print properties
      if ($type == "properties") {
        foreach ($content as $type => $uri) {
          $xrd .= "<Property type='".htmlentities($type)."'>".htmlentities($uri)."</Property>";
        }
        continue;
      }
      
      // print titles
      if ($type == "titles") {
        foreach ($content as $key => $value) {
          if ($key == "default") {
            $xrd .= "<Title>".htmlentities($value)."</Title>";
          } else {
            $xrd .= "<Title xml:lang='".htmlentities($key)."'>".htmlentities($value)."</Title>";
          }
        }
        continue;
      }
      
      // print links
      if ($type == "links") {
        foreach ($content as $links) {
          $temp = array();
          $cascaded = false;
          $xrd .= "<Link ";

          foreach ($links as $key => $value) {
            if (is_array($value)) {
              $temp[$key] = $value;
              $cascaded = true;
            } else {
              $xrd .= htmlentities($key)."='".htmlentities($value)."' ";
            }
          }
          if ($cascaded) {
            $xrd .= ">";
            $xrd .= self::jrdToXrd($temp);
            $xrd .= "</Link>";
          } else {
            $xrd .= " />";
          }
        }
        
        continue;
      }
    }
    
    return $xrd;
  }
}

add_action('query_vars', array('HostMetaPlugin', 'query_vars'));
add_action('parse_request', array('HostMetaPlugin', 'parse_request'), 1);
add_action('generate_rewrite_rules', array('HostMetaPlugin', 'rewrite_rules'));

add_action('host_meta_render_json', array('HostMetaPlugin', 'render_jrd'), 42, 1);
add_action('host_meta_render_jrd', array('HostMetaPlugin', 'render_jrd'), 42, 1);
add_action('host_meta_render_xrd', array('HostMetaPlugin', 'render_xrd'), 42, 1);

add_filter('host_meta', array('HostMetaPlugin', 'generate_default_content'), 0, 1);

register_activation_hook(__FILE__, 'flush_rewrite_rules');
register_deactivation_hook(__FILE__, 'flush_rewrite_rules');