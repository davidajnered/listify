<?php class listify_widget extends WP_Widget {

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
    listify($instance['listify-widget-list']);
  }

  /**
   * Saves the widget settings
   */
  function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['listify-widget-list'] = strip_tags(stripslashes($new_instance['listify-widget-list']));
    return $instance;
  }

  /**
   * GUI for backend
   */
  function form($instance) { ?>
    <select name="<?php echo $this->get_field_name('listify-widget-list'); ?>" id="<?php echo $this->get_field_id('listify-widget-list'); ?>" class="listify-widget-list">
      <?php $lists = listify_load_list();
      foreach($lists as $name => $list): ?>
        <option value="<?php print $name; ?>" <?php print ($name == $instance['listify-widget-list']) ? 'selected' : ''; ?>>
          <?php print $name; ?>
        </option>
      <?php endforeach; ?>
    </select>
  <?php }

} /* End of class */
?>