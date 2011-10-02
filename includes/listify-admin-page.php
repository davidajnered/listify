<?php

function listify_page_router() {
  if(isset($_GET) && isset($_GET['listify_page'])) {
    $page = $_GET['listify_page'];
    switch($page) {
      case 'option':
        listify_list_option_page();
        break;
    }
  }
  else {
    listify_admin_page();
  }
}

/**
 * Callback for option page. Creates the forms.
 */
function listify_admin_page() { ?>
  <?php $blogs = get_site_option('listify_blog_index', array()); ?>
  <div class="wrap listify-wrap">
    <div id="icon-options-general" class="icon32"><br></div>
    <h2>Listify</h2>
    <h3>Add a list</h3>
    <form id="listify-add-list" method="POST">
      <div class="list-name element">
        <label for="list_name">Give your list a name</label>
        <input type="text" name="list_name">
      </div>
      <div class="list-element element">
        <label for="list_type">Select what you want to list</label>
        <select name="list_type">
          <option value="posts">Posts</option>
          <option value="pages">Pages</option>
          <option value="comments">Comments</option>
        </select>
      </div>
      <div class="list-element element">
        <label for="list_from">Select the blogs to collect data from</label>
        <select class="chosen" name="list_from" multiple="true">
          <option value="0">All blogs</option>
          <?php foreach($blogs as $id => $name): ?>
            <option name="blogs[]" value="<?php print $id; ?>"><?php print $name; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="list-element element">
        <input type="hidden" name="form_action" value="add_list">
        <input type="submit" name="submit" id="submit" class="button-primary" value="Add List">
      </div>
    </form>

    <h3>Lists</h3>
    <form id="listify-delete-list" method="post">
    <table class="listify-list wp-list-table">
      <thead>
        <tr>
          <th class="listify-list-checkbox"><input name="check-all" type="checkbox"> All</th>
          <th class="listify-list-name">Name</th>
          <th class="listify-list-description">Description</th>
          <th class="listify-list-option">Option</th>
        </tr>
      </thead>
      <tbody id="the-list">
        <?php
        $lists = get_site_option('listify_lists', array());
        $zebra = 1;
        foreach($lists as $name => $list): ?>
          <tr class="list<?php print ($zebra % 2 == 0) ? ' even' : ' odd'; ?>">
            <td class="listify-list-checkbox"><input type="checkbox" name="lists[]" value="<?php print $name; ?>"></td>
            <td class="listify-list-name"><?php print $name; ?></td>
            <td class="listify-list-description">
              List <strong><?php print $list['type']; ?></strong>
              from <strong>
              <?php if($list['from'] == '0') {
                print 'all blogs'; 
              }
              else {
                print $blogs[(int)$list['from']];
              } ?>
              </strong>
              and order them by <strong><?php print $list['order']; ?></strong>
            </td>
            <td><a href="<?php print listify_url(FALSE, array('listify_page' => 'option', 'list_name' => $name)); ?>">options</td>
          </div>
        <?php $zebra++; endforeach; ?>
      </tbody>
    </table>
    <input type="hidden" name="form_action" value="delete_list">
    <input type="submit" name="submit" id="submit" class="button-primary" value="Delete List">
  </form>

<?php
}

function listify_list_option_page() { ?>
  <?php
    $list_name = $_GET['list_name'];
    $list = listify_load_list($list_name);
    $options = multilist_get_list_options($list['type']);
    $saved_options = listify_load_options($list_name);
  ?>
  <div class="wrap listify-list-wrap">
    <div id="icon-options-general" class="icon32"><br></div>
    <h2>Options for <?php print $_GET['list_name']; ?></h2>
    <div class="desc">
    <?php if($list['list_type'] == 'posts'): ?>
      <a href="http://codex.wordpress.org/Function_Reference/get_posts">Wordpress Codex</a>
    <?php elseif($list['list_type'] == 'pages'): ?>
      <a href="http://codex.wordpress.org/Function_Reference/get_pages">Wordpress Codex</a>
    <?php elseif($list['list_type'] == 'comments'): ?>
      <a href="http://codex.wordpress.org/Function_Reference/get_comments">Wordpress Codex</a>
    <?php endif; ?>
    </div>
    <form class="listify-list-options-form" method="post">
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Value</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($options as $name => $default): ?>
            <tr>
              <td><label for="<?php print $name; ?>"><?php print $name; ?></label></td>
              <td><input type="text" name="<?php print $name; ?>" value="<?php print isset($saved_options[$name]) ? $saved_options[$name] : ''; ?>" /></td>
            </tr>
          <?php endforeach; ?>
          <tr><td>
          <input type="hidden" name="list_name" value="<?php print $list_name; ?>">
          <input type="hidden" name="form_action" value="update_list_option">
          <input type="submit" name="submit" id="submit" class="button-primary" value="Save">
          </td></tr>
        </tbody>
      </table>
    </form>
  </div>
<?php }

/**
 * options are copied from wordpress codex
 */
function multilist_get_list_options($type) {
  switch($type) {
    case 'posts':
      $args = array(
        'numberposts' => 5,
        'offset' => 0,
        'category' => '',
        'orderby' => 'post_date',
        'order' => 'DESC',
        'include' => '',
        'exclude' => '',
        'meta_key' => '',
        'meta_value' => '',
        'post_type' => 'post',
        'post_mime_type' => '',
        'post_parent' => '',
        'post_status' => 'publish'
      );
      break;
    case 'pages':
      $args = array(
        'child_of' => 0,
        'sort_order' => 'ASC',
        'sort_column' => 'post_title',
        'hierarchical' => 1,
        'exclude' => '',
        'include' => '',
        'meta_key' => '',
        'meta_value' => '',
        'authors' => '',
        'parent' => -1,
        'exclude_tree' => '',
        'number' => '',
        'offset' => 0,
        'post_type' => 'page',
        'post_status' => 'publish',
      );
      break;
    case 'comments':
      $args = array(
        'author_email' => '',
        'ID' => '',
        'karma' => '',
        'number' => '',
        'offset' => '',
        'orderby' => '',
        'order' => 'DESC',
        'parent' => '',
        'post_id' => '',
        'status' => '',
        'type' => '',
        'user_id' => '',
      );
      break;
  }
  return $args;
}

/**
 * Add stylesheets and javascript to admin_head
 */
function listify_admin_css_and_script(){
  echo '<link rel="stylesheet" type="text/css" href="' . LISTIFY_PATH . '/css/listify-admin.css" />';
  echo '<script type="text/javascript" src="' . LISTIFY_PATH . '/js/chosen.jquery.min.js"></script>';
}
add_action('admin_head', 'listify_admin_css_and_script');
