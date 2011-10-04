<?php
/*
Plugin Name: Listify
Description: List posts from a multisite installation
Version: 0001
Author: David Ajnered
Author URI: http://davidajnered.com
*/

require_once('includes/listify-admin-page.php');

/**
 * Init function. This is where it all starts 
 */
function listify_init() {
  listify_form_handler();
  define(LISTIFY_PATH, get_bloginfo('wpurl') . '/wp-content/plugins/listify');
  listify_blogs(); // update index
  add_action('admin_head', 'listify_css_and_script');
}
add_action('init', 'listify_init');

/**
 * Adds an option page to the admin menu
 */
function listify_menu() {
	add_options_page('Listify', 'Listify', 'manage_options', 'listify', 'listify_page_router');
}
add_action('admin_menu', 'listify_menu');

/**
 * Handle form submits and saves valid data to the database.
 */
function listify_form_handler() {
  $valid = listify_validate_submit($_POST);
  if($valid === FALSE) {
    return FALSE;
  }

  $lists = get_site_option('listify_lists', array());
  
  // add list
  if($valid && $_POST['form_action'] == 'add-list') {
    $blogs = ($_POST['check-all-blogs'] == '0') ? 0 : $_POST['blogs'];
    $list = array(
      'type'  => $_POST['list_type'],
      'blogs'  => $blogs,
    );
    $lists[$_POST['list_name']] = $list;
    update_site_option('listify_lists', $lists);
  }

  // delete list
  if($valid && $_POST['form_action'] == 'delete-list') {
    foreach($_POST['lists'] as $key => $list) {
      // delete options too -----------------------------------------------------------------
      unset($lists[$list]);
    }
    update_site_option('listify_lists', $lists);
  }
  
  // update list option
  if($valid && $_POST['form_action'] == 'update-list-option') {
    $list = $_POST['list-name'];
    $options = listify_load_options();

    foreach($_POST as $name => $option) {
      if($option != '') {
        $updated_options[$name] = $option;
      }
    }

    // unset the things we don't need
    unset($updated_options['submit']);
    unset($updated_options['form_action']);
    unset($updated_options['list-name']);

    $options[$list] = $updated_options;
    update_site_option('listify_list_options', $options);
  }
  listify_url(TRUE);
}

/**
 * Form submit validation checks the submitted values and sets an error if needed
 */
function listify_validate_submit($data) {
  if(isset($_POST) && isset($_POST['form_action'])) {
    if($_POST['form_action'] == 'add-list' && $data['list_name'] != '' && isset($data['blogs'])) {
      return TRUE;
    }
    if($_POST['form_action'] == 'delete-list' && isset($data['lists'])) {
      return TRUE;
    }
    if($_POST['form_action'] == 'update-list-option') {
      return TRUE;
    }
  }
  return FALSE;
}

function listify_url($redirect = FALSE, $params = FALSE, $url = NULL) {
  if($url == NULL) {
    $url = get_bloginfo('wpurl');
    $url .= '/wp-admin/options-general.php?page=listify';
    // add errors here
  }
  if($params != FALSE) {
    foreach($params as $key => $value) {
      $url .= '&' . $key . '=' . $value;
    }
  }
  if($redirect != FALSE) {
    $url = 'Location: ' . $url;
    header($url);
  }
  return $url;
}

/**
 * Fetches all the blogs from the database
 * @param $id if true, we only get the blog id's in an array
 */
function listify_blogs($id = FALSE) {
  global $wpdb;
  $blogs = array();
  $blog_index = array();
  $update_index = FALSE;
  $query = "SELECT blog_id FROM $wpdb->blogs;";
  foreach($wpdb->get_results($query) as $key => $value) {
    if($id) {
      $blogs[] = $value->blog_id;
    }
    else {
      $update_index = TRUE;
      switch_to_blog($value->blog_id);
      $blogs[] = array(
        'id' => $value->blog_id,
        'name' => get_bloginfo('name'),
      );
      $blog_index[$value->blog_id] = get_bloginfo('name');
      restore_current_blog();
    }
  }
  if($update_index) {
    update_site_option('listify_blog_index', $blog_index);
  }
  return $blogs;
}

function listify_blog_information($id) {
  $blogs = listify_blogs();
  foreach($blogs as $key => $blog) {
    if($blog['id'] == $id) {
      return $blog;
    }
  }
}

/**
 * This is the function to call from your theme. It starts the process of collecting data to list.
 * @param $list the name of the lists
 */
function listify($list_name, $return = FALSE) {
  if($list_name == '' || listify_load_list($list_name) == FALSE) {
    error_log(var_export('List error!', TRUE));
    return FALSE;
  }
  // print or return data ---------------------------------------------------------------------------------------------
  $list = listify_load_list($list_name);
  $data = listify_list($list);
  if($return) {
    return $data;
  }

  if(function_exists('listify_output_' . $list['type'])) {
    call_user_func('listify_output_' . $list['type'], $data);
  } else {
    call_user_func('listify_output_' . $list['type'] . '_default', $data);
  }
}

/**
 * Return an list from the database
 * @param $list_name the name of the list
 */
function listify_load_list($list_name = NULL) {
  $lists = get_site_option('listify_lists', array());
  if($list_name == NULL) {
    return $lists;
  }

  if(isset($lists[$list_name])) {
    $list = $lists[$list_name];
    $list['list_name'] = $list_name;
    // to make it easier later on, if we want data from all blogs add an array with blog id's here
    if($list['blogs'] == '0') {
      $list['blogs'] = listify_blogs(TRUE);
    }
    return $list;
  }
  else {
    return FALSE;
  }
}

/**
 *
 */
function listify_load_options($list_name = FALSE) {
  $options = get_site_option('listify_list_options', array());
  if($list_name != FALSE) {
    if(!isset($options[$list_name])) {
      return array();
    }
    if($option[$list_name]['blogs'] == '0') {
      
    }
    return $options[$list_name];
  }
  return array();
}

/**
 * This is there the data is collected
 */
function listify_list($list) {
  $blogs = $list['blogs'];
  $data = array();
  $args = listify_load_options($list['list_name']);
  if(!is_array($blogs)) {
    $blogs = array($blogs);
  }
  foreach($blogs as $blog) {
    switch_to_blog($blog);
    switch($list['type']) {
      case 'posts':
        $data[$blog] = get_posts($args);
        break;
      case 'pages':
        $data[$blog] = get_pages($args);
        break;
      case 'comments':
        $data[$blog] = get_comments($args);
        break;
    }
  }
  return listify_normalize_blog_data($data);
}

/**
 * Remove blogs without posts and add the blog id to the post object for future use.
 */
function listify_normalize_blog_data($data) {
  $data_array = array();
  foreach($data as $id => $blog) {
    if(!empty($blog)) {
      foreach($blog as $post) {
        $post->belongs_to_blog = $id;
        $data_array[] = $post;
      }
    }
  }
  return $data_array;
}

/**
 * Prints thumbnail for posts. Have to loop through all the blogs to get the image.
 * There's probably a better way to do this.
 */
function listify_the_thumbnail($post, $size) {
  $blog_id = $post->belongs_to_blog;
  switch_to_blog($blog_id);
  if(has_post_thumbnail($post->ID)) {
    $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), $size);
    print '<img src="' . $image[0] . '">';
  }
  restore_current_blog();
}

function listify_output_posts_default($data) {
  foreach($data as $d): ?>
    <div class="listify-post">
      <h2><?php print $d->post_title; ?></h2>
      <?php if($d->post_excerpt == ''): ?>
        <div class="listify-post-content"><?php print $d->post_content; ?></div>
      <?php else: ?>
        <div class="listify-post-excerpt"><?php print $d->post_excerpt; ?></div>
      <?php endif; ?>
      <div class="listify-post-meta">
        <div class="listify-post-author"><?php print get_the_author_meta('user_nicename', $d->post_author); ?></div>
        <div class="listify-post-date"><?php print $d->post_date; ?></div>
        <div class="listify-post-comments">Comments: <?php print $d->comment_count; ?></div>
      </div>
    </div>
  <?php endforeach;
}

function listify_output_pages_default($data) {
  foreach($data as $d): ?>
    <div class="listify-page">
      <h2><?php print $d->post_title; ?></h2>
      <?php if($d->post_excerpt == ''): ?>
        <div class="listify-page-content"><?php print $d->post_content; ?></div>
      <?php else: ?>
        <div class="listify-page-excerpt"><?php print $d->post_excerpt; ?></div>
      <?php endif; ?>
      <div class="listify-page-meta">
        <div class="listify-page-author"><?php print get_the_author_meta('user_nicename', $d->post_author); ?></div>
        <div class="listify-page-date"><?php print $d->post_date; ?></div>
        <div class="listify-page-comments">Comments: <?php print $d->comment_count; ?></div>
      </div>
    </div>
  <?php endforeach;
}

function listify_output_comments_default($data) {
  foreach($data as $d): ?>
    <div class="listify-comments">
      <div class="listify-comment-author"><?php print $d->comment_author; ?></div>
      <div class="listify-date"><?php print $d->comment_date; ?></div>
      <div class="listify-comment"><?php print $d->comment_content; ?></div>
    </div>
  <?php endforeach;
}

/**
 * Register Widget
 */
function listify_widget_init() {
  require_once('includes/listify-widget.php');
  register_widget('listify_widget');
}
add_action('widgets_init', 'listify_widget_init');

/**
 * Add stylesheets and javascript to head
 */
function listify_css_and_script() {
  echo '<link rel="stylesheet" type="text/css" href="' . LISTIFY_PATH . '/css/listify.css" />';
  echo '<script type="text/javascript" src="' . LISTIFY_PATH . '/js/listify.js"></script>';
}

/**
 * TODO: register the plugin as active on all the blogs without using network activate
 * Q: is this a good idea?
 */
function listify_activation() {
}
register_activation_hook(__FILE__, 'listify_activation');

/**
 * TODO: deactivation hook
 */
function listify_deactivation() {
}
register_deactivation_hook(__FILE__, 'listify_deactivation');

function listify_credit() { ?>
  <div class="listify-credit">
    Plugin created by <a href="http://davidajnered.com">David Ajnered</a>. Design by <a href="http://onetonnegraphic.com">One Tonne Graphic</a>.
  </div>
<?php }