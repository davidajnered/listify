<?php
/*
 * Plugin Name: multisite_list Widget
 * Version: 1.0
 * Plugin URI: http://www.davidajnered.com/
 * Description: N/A
 * Author: David Ajnered
 */

class listify_widget extends WP_Widget {
  private $length;

  /**
  * Init method
  */
  function multisite_list_widget() {
		$widget_ops = array('classname' => 'multisite_list_widget',
                        'description' => __("Create a list of posts"));

    $control_ops = array('width' => 100, 'height' => 100);
    $this->WP_Widget('multisite_list_widget', __('multisite_list Widget'), $widget_ops, $control_ops);
  }

 /**
  * Displays the widget
  */
  function widget($args, $instance) {
    if(!empty($instance)) {
      // Variables
      $widget_title         = $instance['widget_title'];
      $this->length         = (int)$instance['length'];
      $selection            = $instance['selection'];
      $has_thumbnail        = $instance['has_thumbnail'];
      $thumbnail_size       = $instance['thumbnail_size'];
      $data_to_use          = $instance['data_to_use'];
      $paragraph            = $instance['paragraph'];
      $posts_per_blog       = $instance['posts_per_blog'];
      $link                 = $instance['link'];
      $template             = $instance['template'];
      $limit                = $instance['limit'];
      $ignore               = $instance['ignore'];

      // Set default limit
      $limit = !is_int($limit) ? (int)$limit : $limit;
      $limit = $limit == 0 ? 1 : $limit;

      include_once('includes/db_queries.php');
      if(!empty($selection)) {
        $ex = explode(':', $selection);
        $type = $ex[0];
        $selection = $ex[1];
        $ignore = explode(',', $ignore);
        $data_array = spl_get_posts($selection, $limit, $posts_per_blog, $ignore);
        $inc = $template ? get_template_directory() . '/' . $template : WP_PLUGIN_DIR . '/simple-post-list/' . 'templates/spl_' . $type . '_default_template.php';
      }
      include('includes/output.php');
    }
  }

  /**
   *
   */
  function spl_get_themes() {
    $path = ABSPATH . '/wp-content/themes/' . get_template();
    $template_files = array();
  	if(is_dir($path)) {
  	  if ($folder = opendir($path)) {
        while(($file = readdir($folder)) !== FALSE) {
          if(strpos($file, 'spl_post_') !== FALSE ||
            strpos($file, 'spl_comment_') !== FALSE ||
            strpos($file, 'spl_blog_') !== FALSE) {
            $file_path = $path . '/' . $file;
            $template = $this->spl_fetch_template($file_path);
            $template['Path'] = $file;
            if(!empty($template['Path']) && !empty($template['Name'])) {
              $template_files[] = $template;
            }
          }
        }
      }
    }
  	return $template_files;
  }

  /**
   *
   */
  function spl_fetch_template($file = NULL) {
  	$default_headers = array(
  		'Name'          => 'Style Name',
  		'Class'         => 'Class',
  		'Description'   => 'Description',
  		'Version'       => 'Version',
  		'Author'        => 'Author',
  		'AuthorURI'     => 'Author URI',
  	);
  	$fp = fopen($file, 'r');
  	$file_data = fread($fp, 8192);
  	fclose($fp);
  	
  	foreach($default_headers as $field => $regex) {
  		preg_match('/^[ \t\/*#]*' . preg_quote($regex, '/') . ':(.*)$/mi', $file_data, ${$field});
  		if (!empty(${$field})) {
  			${$field} = _cleanup_header_comment(${$field}[1]);
  		} else {
  			${$field} = '';
			}
  	}
  	$file_data = compact(array_keys($default_headers));
  	return $file_data;
  }

  /**
   *
   */
  function spl_shorten($content) {
    if($this->length <= -1) {
      $content = '';
    }
    else if (strlen($content) > $this->length) {
      if($this->length > 0) {
        $content = substr($content, 0, $this->length) . '&hellip; ';
      }
    }
    return $content;
  }

 /**
  *
  */
  function spl_paragraph($content) {
    $start = strpos($content, '<p>');
    $end = strpos($content, '</p>', $start);
    $paragraph = substr($content, $start, $end - $start + 4);
    if($start == FALSE || $end == FALSE) {
      $paragraph = $this->spl_shorten($content);
    }
    return $paragraph;
  }

 /**
  * Saves the widget settings
  */
  function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['widget_title']   = strip_tags(stripslashes($new_instance['widget_title']));
    $instance['selection']      = strip_tags(stripslashes($new_instance['selection']));
    $instance['limit']          = strip_tags(stripslashes($new_instance['limit']));
    $instance['has_thumbnail']  = strip_tags(stripslashes($new_instance['has_thumbnail'])) != 'checked' ? FALSE : TRUE;
    $instance['thumbnail_size'] = strip_tags(stripslashes($new_instance['thumbnail_size']));
    $instance['data_to_use']    = strip_tags(stripslashes($new_instance['data_to_use']));
    $instance['paragraph']      = strip_tags(stripslashes($new_instance['paragraph'])) != 'checked' ? FALSE : TRUE;
    $instance['posts_per_blog'] = strip_tags(stripslashes($new_instance['posts_per_blog'])) != 'checked' ? TRUE : FALSE;
    $instance['length']         = strip_tags(stripslashes($new_instance['length']));
    $instance['link']           = strip_tags(stripslashes($new_instance['link']));
    $instance['link_to']        = strip_tags(stripslashes($new_instance['link_to']));
    $instance['template']       = strip_tags(stripslashes($new_instance['template']));
    $instance['ignore']         = strip_tags(stripslashes($new_instance['ignore']));
    return $instance;
  }

 /**
  * GUI for backend
  */
  function form($instance) {
    $widget_title   = htmlspecialchars($instance['widget_title']);
    $selection      = htmlspecialchars($instance['selection']);
    $limit          = htmlspecialchars($instance['limit']);
    $has_thumbnail  = htmlspecialchars($instance['has_thumbnail']);
    $thumbnail_size = htmlspecialchars($instance['thumbnail_size']);
    $data_to_use    = htmlspecialchars($instance['data_to_use']);
    $paragraph      = htmlspecialchars($instance['paragraph']);
    $posts_per_blog = htmlspecialchars($instance['posts_per_blog']);
    $length         = htmlspecialchars($instance['length']);
    $link           = htmlspecialchars($instance['link']);
    $link_to        = htmlspecialchars($instance['link_to']);
    $ignore         = htmlspecialchars($instance['ignore']);

    $template_files = $this->spl_get_themes();
    /* Print interface */
    include('includes/interface.php');
  }

} /* End of class */
?>