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
    <div id="icon-options-general" class="icon-listify"><br></div>
    <h2>Listify</h2>
    <div class="metabox-holder">
      <div id="add-list-container" class="postbox-container">
        <div class="postbox">
          <h3>Add a list</h3>
          <div class="inside">
            <form id="listify-add-list" method="post">
              <div class="list-name element">
                <label for="list_name">Give your list a name:</label>
                <input type="text" name="list_name">
              </div>
              <div class="list-element element">
                <label for="list_type">Select what you want to list:</label>
                <select name="list_type">
                  <option value="posts">Posts</option>
                  <option value="pages">Pages</option>
                  <option value="comments">Comments</option>
                </select>
              </div>
              <div class="list-element element">
                <label>Select blogs:</label>
                <div class="blogs-wrapper">
                  <label><input type="checkbox" name="check-all-blogs" value="0"><span>All Blogs</span></label>
                  <?php foreach($blogs as $id => $name): ?>
                    <label><input type="checkbox" name="blogs[]" value="<?php print $id; ?>"><span><?php print $name; ?></span></label>
                  <?php endforeach; ?>
                </div>
              </div>
              <div class="list-element element">
                <input type="hidden" name="form_action" value="add-list">
                <input type="submit" name="submit" id="submit" class="button-primary" value="Add A New List">
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    
    <div class="metabox-holder">
      <div id="delete-list-container" class="postbox-container">
        <div class="postbox">
          <h3>Lists</h3>
          <form id="listify-delete-list" method="post">
            <table class="listify-list wp-list-table widefat fixed pages">
              <thead>
                <tr>
                  <th class="listify-list-checkbox"><input name="check-all-lists" type="checkbox"></th>
                  <th class="listify-list-name">Name</th>
                  <th class="listify-list-description">Description</th>
                  <th class="listify-list-option"></th>
                </tr>
              </thead>
              <tbody id="list-list">
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
                      <?php if($list['blogs'] == '0' || in_array(0, $list['blogs'])) {
                        print 'all blogs'; 
                      }
                      else {
                        $blogs = listify_blogs();
                        if(is_array($list['blogs'])) {
                          $counter = 0;
                          foreach($list['blogs'] as $id) {
                            $sep = ($counter != 0) ? ', ': '';
                            $sep = ($counter != count($list['blogs']) - 1) ? ', ' : '';
                            $blog_info = listify_blog_information($id);
                            print $blog_info['name'];
                            print $sep;
                            $counter++;
                          }
                        }
                        else {
                          print $blogs[(int)$list['from']];
                        }
                      } ?>
                      </strong>
                    </td>
                    <td class="listify-list-option"><a href="<?php print listify_url(FALSE, array('listify_page' => 'option', 'list_name' => $name)); ?>">options</td>
                  </div>
                <?php $zebra++; endforeach; ?>
              </tbody>
            </table>
            <input type="hidden" name="form_action" value="delete-list">
            <input type="submit" name="submit" id="submit" class="button-primary" value="Delete Selected Lists">
          </form>
        </div>
      </div>
    </div>
  </div>
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
    <div id="icon-options-general" class="icon-listify"><br></div>
    <h2>Options for <?php print $_GET['list_name']; ?></h2>
    <a class="listify-go-back" href="wp-admin/options-general.php?page=listify">&laquo; Back to main page</a>
    <div class="metabox-holder">
      <div id="delete-list-container" class="postbox-container">
        <div class="postbox">
          <h3>Options</h3>
    
          <div class="listify-option-desc">
            <i>The options below are taken from the wordpress codex for the content type you wish to list. Have a look at the codex if you want to know what the fields mean.</i> 
            <?php switch($list['type']) {
              case 'posts': ?>
                <a href="http://codex.wordpress.org/Function_Reference/get_posts">Wordpress Codex</a>
              <?php break;
      
              case 'pages': ?>
                <a href="http://codex.wordpress.org/Function_Reference/get_pages">Wordpress Codex</a>
              <?php break;
      
              case 'comments': ?>
                <a href="http://codex.wordpress.org/Function_Reference/get_comments">Wordpress Codex</a>
              <?php break;
            } ?>
          </div>
    
          <form class="listify-list-options-form" method="post">
            <table class="listify-option-table wp-list-table widefat fixed pages">
              <thead>
                <tr>
                  <th class="listify-option-form-name">Name</th>
                  <th class="listify-option-form-value">Value</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($options as $name => $default): ?>
                  <tr>
                    <td class="listify-option-form-name">
                      <label for="<?php print $name; ?>"><?php print $name; ?></label>
                    </td>
                    <td class="listify-option-form-value">
                      <input type="text" name="<?php print $name; ?>" value="<?php print isset($saved_options[$name]) ? $saved_options[$name] : ''; ?>" />
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            <input type="hidden" name="list-name" value="<?php print $list_name; ?>">
            <input type="hidden" name="form_action" value="update-list-option">
            <input type="submit" name="submit" id="submit" class="button-primary" value="Update Options">
          </form>

        </div>
      </div>
    </div>
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
}
add_action('admin_head', 'listify_admin_css_and_script');
