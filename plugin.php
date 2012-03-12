<?php
/*
Plugin Name: host-meta
Plugin URI: http://wordpress.org/extend/plugins/host-meta/
Description: Host Metadata for WordPress (RFC: http://tools.ietf.org/html/rfc6415)
Version: 0.4.3
Author: Matthias Pfefferle
Author URI: http://notizblog.org/
*/

add_action('well_known_host-meta', array('HostMetaPlugin', 'renderXrd'), 2);
add_action('well_known_host-meta.json', array('HostMetaPlugin', 'renderJrd'), 2);

/**
 * the host-meta class
 *
 * @author Matthias Pfefferle
 */
class HostMetaPlugin {
  /**
   * renders the host-meta file in xml
   */
  public static function renderXrd() {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/xrd+xml; charset=" . get_option('blog_charset'), true);
    $host_meta = self::generateContent();
    
    echo "<?xml version='1.0' encoding='".get_option('blog_charset')."'?>\n";
    echo "<XRD xmlns='http://docs.oasis-open.org/ns/xri/xrd-1.0'\n";
    echo "     xmlns:hm='http://host-meta.net/xrd/1.0'\n";
      // add xml-only namespaces
      do_action('host_meta_ns');
    echo ">\n";
    echo "  <hm:Host>".parse_url(get_option('siteurl'), PHP_URL_HOST)."</hm:Host>\n";

    echo self::jrdToXrd($host_meta);
      // add xml-only content
      do_action('host_meta_xrd');
    
    echo "\n</XRD>";
    exit;
  }
  
  /**
   * renders the host-meta file in json
   */
  public static function renderJrd() {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=" . get_option('blog_charset'), true);
    $host_meta = self::generateContent();

    echo json_encode($host_meta);
    exit;
  }
  
  /**
   * generates the host-meta base array (and activate filter)
   *
   * @return array
   */
  public static function generateContent() {
    $host_meta = array("subject" => get_option('siteurl'));
    $host_meta = apply_filters('host_meta', $host_meta);
    
    return $host_meta;
  }
  
  /**
   * recursive helper to generade the xrd-xml from the jrd array
   *
   * @param string $host_meta
   * @return string
   */
  public static function jrdToXrd($host_meta) {
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
?>