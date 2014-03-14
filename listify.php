<?php
/*
Plugin Name: Listify
Description: List posts from a site or multisite installation
Version: 1.0
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

  if (is_multisite()) {
    listify_blogs(); // update index
  }

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

  if ($valid === false) {
    return false;
  }

  $lists = get_site_option('listify_lists', array());

  // add list
  if ($valid && $_POST['form_action'] == 'add-list') {
    $blogs = ($_POST['check-all-blogs'] == '0') ? 0 : $_POST['blogs'];
    $list = array(
      'type'  => $_POST['list_type'],
      'blogs'  => $blogs,
    );
    $lists[$_POST['list_name']] = $list;
    update_site_option('listify_lists', $lists);
  }

  // delete list
  if ($valid && $_POST['form_action'] == 'delete-list') {
    foreach ($_POST['lists'] as $key => $list) {
      // delete options too -----------------------------------------------------------------
      unset($lists[$list]);
    }
    update_site_option('listify_lists', $lists);
  }

  // update list option
  if ($valid && $_POST['form_action'] == 'update-list-option') {
    $list = $_POST['list-name'];
    $options = listify_load_options();

    foreach ($_POST as $name => $option) {
      if ($option != '') {
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
  listify_url(true);
}

/**
 * Form submit validation checks the submitted values and sets an error if needed
 */
function listify_validate_submit($data) {
  global $wpdb;

  if(isset($_POST) && isset($_POST['form_action'])) {
    if ($_POST['form_action'] == 'add-list' && $data['list_name'] != '') {
      if (is_multisite() && !isset($data['blogs'])) {
        return false;
      }
      return true;
    }

    if ($_POST['form_action'] == 'delete-list' && isset($data['lists'])) {
      return true;
    }

    if ($_POST['form_action'] == 'update-list-option') {
      return true;
    }
  }

  return false;
}

/**
 * @param $redirect
 * @param $params
 * @param $url
 */
function listify_url($redirect = false, $params = false, $url = null) {
  if ($url == NULL) {
    $url = get_bloginfo('wpurl');
    $url .= '/wp-admin/options-general.php?page=listify';
    // add errors here
  }

  if ($params != false) {
    foreach ($params as $key => $value) {
      $url .= '&' . $key . '=' . $value;
    }
  }

  if ($redirect != false) {
    $url = 'Location: ' . $url;
    header($url);
  }

  return $url;
}

/**
 * Fetches all the blogs from the database.
 *
 * @param $id if true, we only get the blog id's in an array
 */
function listify_blogs($id = FALSE) {
  global $wpdb;

  $blogs = array();
  $blog_index = array();
  $update_index = FALSE;

  if (!($blogs = $wpdb->blogs)) {
    $blogs = $wpdb->dbname;
  }

  if ($wpdb->blogs) {
    $query = "SELECT `blog_id` FROM $wpdb->blogs;";
    $blogs = $wpdb->get_results($query);

    foreach ($blogs as $key => $value) {
      if ($id) {
        $blogs[] = $value->blog_id;
      } else {
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

    if ($update_index) {
      update_site_option('listify_blog_index', $blog_index);
    }

    return $blogs;
  }
}

/**
 * Blog information
 *
 * @param $id
 */
function listify_blog_information($id) {
  $blogs = listify_blogs();
  foreach ($blogs as $key => $blog) {
    if ($blog['id'] == $id) {
      return $blog;
    }
  }
}

/**
 * This is the function to call from your theme. It starts the process of collecting data to list.
 *
 * @param $list the name of the lists
 */
function listify($list_name, $return = false) {
  if ($list_name == '' || listify_load_list($list_name) == false) {
    trigger_error('List could not be loaded');

    return false;
  }
  // print or return data ---------------------------------------------------------------------------------------------
  $list = listify_load_list($list_name);
  $data = listify_list($list);

  // Return data instead of output
  if ($return) {
    return $data;
  }

  // Render output
  if (function_exists('listify_output_' . $list['type'])) {
    call_user_func('listify_output_' . $list['type'], $data);
  } else {
    call_user_func('listify_output_' . $list['type'] . '_default', $data);
  }
}

/**
 * Shorttag handler
 *
 * @param $attr shorttag attributes
 */
function listify_shorttag($attr) {
  extract(shortcode_atts(array(
    'list' => null,
  ), $attr));
  listify($attr['list']);
}
add_shortcode('listify', 'listify_shorttag');

/**
 * Return a list from the database
 *
 * @param $list_name the name of the list
 */
function listify_load_list($list_name = null) {
  $lists = get_site_option('listify_lists', array());
  if ($list_name == null) {
    return $lists;
  }

  if (isset($lists[$list_name])) {
    $list = $lists[$list_name];
    $list['list_name'] = $list_name;

    // to make it easier later on, if we want data from all blogs add an array with blog id's here
    if ($list['blogs'] == '0') {
      $list['blogs'] = listify_blogs(true);
    }

    return $list;
  } else {
    return false;
  }
}

/**
 * Load list stored in the database
 *
 * @param $list_name the name of the list
 */
function listify_load_options($list_name = false) {
  $options = get_site_option('listify_list_options', array());
  if ($list_name != false) {
    if (!isset($options[$list_name])) {
      return array();
    }
    if ($option[$list_name]['blogs'] == '0') {

    }
    return $options[$list_name];
  }
  return array();
}

/**
 * This is there the data is collected
 *
 * @param $list an array with list data
 */
function listify_list($list) {
  $data = array();
  $args = listify_load_options($list['list_name']);

  // Multisite
  if (is_multisite()) {
    $blogs = $list['blogs'];

    if (!is_array($blogs)) {
      $blogs = array($blogs);
    }

    foreach ($blogs as $blog) {
      switch_to_blog($blog);
      switch ($list['type']) {
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
  }

  // Single site
  if (!is_multisite()) {
    if (!$blogs) {
      switch ($list['type']) {
        case 'posts':
          $data[0] = get_posts($args);
          break;
        case 'pages':
          $data[0] = get_pages($args);
          break;
        case 'comments':
          $data[0] = get_comments($args);
          break;
      }
    }
  }

  return listify_normalize_blog_data($data);
}

/**
 * Remove blogs without posts and add the blog id to the post object for future use.
 *
 * @param $data array with post, page or comment data
 */
function listify_normalize_blog_data($data) {
  $data_array = array();
  foreach ($data as $id => $blog) {
    if (!empty($blog)) {
      foreach ($blog as $post) {
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
 *
 * @param $post post data
 * @param $size thumbnail size
 */
function listify_the_thumbnail($post, $size) {
  $blog_id = $post->belongs_to_blog;
  switch_to_blog($blog_id);
  if (has_post_thumbnail($post->ID)) {
    $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), $size);
    print '<img src="' . $image[0] . '">';
  }
  restore_current_blog();
}

/**
 * Default output for posts
 *
 * @param $data array with post data
 */
function listify_output_posts_default($data) {
  foreach ($data as $d): ?>
    <div class="listify-post">
      <h2><?php print $d->post_title; ?></h2>
      <?php if ($d->post_excerpt == ''): ?>
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

/**
 * Default output for pages
 *
 * @param $data array with page data
 */
function listify_output_pages_default($data) {
  foreach ($data as $d): ?>
    <div class="listify-page">
      <h2><?php print $d->post_title; ?></h2>
      <?php if ($d->post_excerpt == ''): ?>
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

/**
 * Default output for comments
 *
 * @param $data array with comment data
 */
function listify_output_comments_default($data) {
  foreach ($data as $d): ?>
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
