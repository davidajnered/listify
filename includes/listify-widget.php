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
  function listify_widget() {
		$widget_ops = array('classname' => 'listify_widget',
                        'description' => __("Create a list of posts"));

    $control_ops = array('width' => 100, 'height' => 100);
    $this->WP_Widget('listify_widget', __('Listify'), $widget_ops, $control_ops);
  }

 /**
  * Displays the widget
  */
  function widget($args, $instance) {
    print '<li>Listify</li>';
  }

  /**
   * Saves the widget settings
   */
  function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['widget_title']   = strip_tags(stripslashes($new_instance['widget_title']));
    return $instance;
  }

  /**
   * GUI for backend
   */
  function form($instance) {
    /* Print interface */
    print 'form';
    // show list of lists
  }

} /* End of class */
?>