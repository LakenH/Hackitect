<?php
/*********
* Style the visual editor to match the theme styles
*/
add_filter('mce_css', 'my_editor_style');
function my_editor_style($url) {
  if ( !empty($url) ) {
    $url .= ',';
  }
  $url .= trailingslashit( get_stylesheet_directory_uri() ) . '/css/ed-content.css';
  return $url;
}

/*********
* Remove WP version from head (helps us evade spammers/hackers who search for default metadata)
*/
remove_action('wp_head', 'wp_generator');


/**
* Replace the default title separator.
*/
function mozhacks_document_title_separator() {
  $sep = '–'; // this is en dash, not a hyphen
  return $sep;
}
add_filter('document_title_separator', 'mozhacks_document_title_separator');


/**
* Returns the page number currently being browsed.
*/
function mozhacks_page_number() {
  global $paged; // Contains page number.
  $sep = ' ' . mozhacks_document_title_separator() . ' ';
  if ($paged >= 2) {
    return $sep . sprintf(__('Page %s', 'mozhacks'), $paged);
  }
}

/**
* Use auto-excerpts for meta description if hand-crafted exerpt is missing
*/
function mozhacks_meta_desc() {
  $post_desc_length = 30; // auto-excerpt length in number of words

  global $cat, $cache_categories, $wp_query, $wp_version;
  if(is_single() || is_page()) {
    $post = $wp_query->post;
    $post_custom = get_post_custom($post->ID);

    if(!empty($post->post_excerpt)) {
      $text = $post->post_excerpt;
    } else {
      $text = $post->post_content;
    }
    $text = do_shortcode($text);
    $text = str_replace(array("\r\n", "\r", "\n", "  "), " ", $text);
    $text = str_replace(array("\""), "", $text);
    $text = trim(strip_tags($text));
    $text = explode(' ', $text);
    if(count($text) > $post_desc_length) {
      $l = $post_desc_length;
      $ellipsis = '...';
    } else {
      $l = count($text);
      $ellipsis = '';
    }
    $description = '';
    for ($i=0; $i<$l; $i++)
      $description .= $text[$i] . ' ';

    $description .= $ellipsis;
  }
  elseif(is_category()) {
    $category = $wp_query->get_queried_object();
    if (!empty($category->category_description)) {
      $description = trim(strip_tags($category->category_description));
    } else {
      $description = single_cat_title('Articles posted in ', 'mozhacks');
    }
  }
  else {
    $description = trim(strip_tags(get_bloginfo('description')));
  }

  if($description) {
    echo $description;
  }
}


/*********
* Register sidebars
*/
if ( function_exists('register_sidebars') ) :
  register_sidebar(array(
    'name' => 'Home Page Sidebar',
    'id' => 'home',
    'description' => 'Displayed on the Home page',
    'before_widget' => '<li id="%1$s" class="widget %2$s">',
    'after_widget' => '</li>',
    'before_title' => '<h3 class="widgettitle">',
    'after_title' => '</h3>',
  ));

  register_sidebar(array(
    'name' => 'Articles Page Sidebar',
    'id' => 'articles',
    'description' => 'Displayed on the main Articles page',
    'before_widget' => '<li id="%1$s" class="widget %2$s">',
    'after_widget' => '</li>',
    'before_title' => '<h3 class="widgettitle">',
    'after_title' => '</h3>',
  ));

  register_sidebar(array(
    'name' => 'Demos Page Sidebar',
    'id' => 'demos',
    'description' => 'Displayed on the main Demos page',
    'before_widget' => '<li id="%1$s" class="widget %2$s">',
    'after_widget' => '</li>',
    'before_title' => '<h3 class="widgettitle">',
    'after_title' => '</h3>',
  ));

  register_sidebar(array(
    'name' => 'About Page Sidebar',
    'id' => 'about',
    'description' => 'Displayed on the About page',
    'before_widget' => '<li id="%1$s" class="widget %2$s">',
    'after_widget' => '</li>',
    'before_title' => '<h3 class="widgettitle">',
    'after_title' => '</h3>',
  ));

endif;


/*********
* Add theme support
*/
if ( function_exists( 'add_theme_support' ) ) {
  // This theme uses Featured Images (also known as post thumbnails)
  add_theme_support('post-thumbnails');

  // Let WordPress generate document titles
  add_theme_support('title-tag');

  // Let WordPress generate feeds
  add_theme_support('automatic-feed-links');
}

/*********
* Load various JavaScripts
*/
function mozhacks_load_scripts() {
  // Load the default jQuery
  wp_enqueue_script('jquery');

  // Load the threaded comment reply script
  if ( is_singular() && comments_open() && get_option('thread_comments') ) {
    wp_enqueue_script( 'comment-reply' );
  }
}
add_action( 'wp_enqueue_scripts', 'mozhacks_load_scripts' );


/*********
* Make cleaner excerpts of any length
*/
function fc_excerpt($num) {
  $limit = $num+1;
  $excerpt = explode(' ', get_the_excerpt(), $limit);
  array_pop($excerpt);
  $excerpt = implode(" ",$excerpt);
  echo $excerpt;
}

/**********
* Determine if the page is paged and should show posts navigation
*/
function fc_show_posts_nav() {
  global $wp_query;
  return ($wp_query->max_num_pages > 1) ? TRUE : FALSE;
}


/*********
* Determines if the current page is the result of paged comments.
* This lets us prevent search engines from indexing lots of duplicate pages (since the post is repeated on every paged comment page).
*/
function is_comments_paged_url() {
  $pos = strpos($_SERVER['REQUEST_URI'], "comment-page");
  if ($pos === false) { return false; }
  else { return true; }
}

/*********
* Catch spambots with a honeypot field in the comment form.
* It's hidden from view with CSS so most humans will leave it blank, but robots will kindly fill it in to alert us to their presence.
* The field has an innucuous name -- 'age' in this case -- likely to be autofilled by a robot.
*/
function fc_honeypot( array $data ){
  if( !isset($_POST['comment']) && !isset($_POST['content'])) { die("No Direct Access"); }  // Make sure the form has actually been submitted

  if($_POST['age']) {  // If the Honeypot field has been filled in
    $message = _e('Sorry, you appear to be a spamming robot because you filled in the hidden spam trap field. To show you are not a spammer, submit your comment again and leave the field blank.', 'mozhacks');
    $title = 'Spam Prevention';
    $args = array('response' => 200);
    wp_die( $message, $title, $args );
    exit(0);
  } else {
	   return $data;
	}
}
add_filter('preprocess_comment','fc_honeypot');


/*********
* Comment Template for Mozilla Hacks theme
*/
function hacks_comment($comment, $args, $depth) {
  $GLOBALS['comment'] = $comment;
  $comment_type = get_comment_type();
?>

 <li id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
  <?php if ( $comment_type == 'trackback' ) : ?>
  <?php elseif ( $comment_type == 'pingback' ) : ?>
  <?php else : ?>
    <b class="comment__title vcard">
      <cite class="author fn"><?php comment_author(); ?></cite>
    </b>
  <?php endif; ?>

    <?php if ($comment->comment_approved == '0') : ?>
      <p class="mod"><strong><?php _e('Your comment is awaiting moderation.'); ?></strong></p>
    <?php endif; ?>

    <blockquote class="comment__body">
      <?php comment_text(); ?>
    </blockquote>

    <a class="comment__meta" href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ); ?>" rel="bookmark" title="Permanent link to this comment by <?php comment_author(); ?>"><abbr class="published" title="<?php comment_date('Y-m-d'); ?>"><?php comment_date('F jS, Y'); ?></abbr> at <?php comment_time(); ?></a>

  <?php if (get_option('thread_comments') == true) : ?>
    <p class="comment__util"><?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?> <?php if ( current_user_can('edit_post', $comment->comment_post_ID) ) : ?><span class="edit"><?php edit_comment_link('Edit Comment','',''); ?></span><?php endif; ?></p>
  <?php endif; ?>
<?php
} /* end comment template */


/*********
* Render debug information nicely
*/
function pr($obj) {
  echo '<pre style="white-space: pre-wrap; background-color: black; color: white">';
  print_r($obj);
  echo '</pre>';
}

/*********
* Hook which renders any SQL query to the screen when it happens.
* Uncomment the pr to have these displayed
*/
function tmh_display_query_sql($query) {
  global $wp_query;
  // pr(array_filter($wp_query->query_vars));
  // pr($query);
  return $query;
}
add_filter('posts_request', 'tmh_display_query_sql'); // use query for all db activity

/*********
* To use when a loop is needed in a page. Use $args to call query_posts and then
* use the template indicated to render the posts. The template gets included once
* per post.
*
* Orginally by Lyza Gardner; see http://www.cloudfour.com/533/wordpress-taking-the-hack-out-of-multiple-custom-loops/
*
* @param Array $args               Wordpress-style arguments; passed on to query_posts
*                                  'template' => name of post template to use for posts
* @return Array of WP $post objs   Matching posts, if you should need them.
*/
function fc_custom_loop($args) {
    global $wp_query;
    global $post;
    $post_template_dir = 'views';
    /* The 'template' element should be the name of the PHP template file
       to use for rendering the matching posts. It should be the name of file,
       without path and without '.php' extension. e.g. the default value 'default'
       is $post_template_dir/default.php
    */
    $defaults = Array('template' => 'article-brief' );

    $opts = wp_parse_args($args, $defaults);

    // Bring arguments into local scope, vars prefixed with $loop_
    extract($opts, EXTR_PREFIX_ALL, 'loop');

    // Preserve the current query object and the current global post before messing around.
    $temp_query = clone $wp_query;
    $temp_post  = clone $post;

    // Wildcard substitution
    if (isset($wp_query->query_vars['tmh_view_as'])) {
      $view_as = $wp_query->query_vars['tmh_view_as'];
    } else {
      $view_as = 'brief';
    }

    $tpl = explode('-', $loop_template);

    // Article => Demo mappings. For demo templates we have to convert this across
    $mappings = tmh_article_to_demo_mappings();

    switch ($tpl[0]) {
      case 'demo':
        $view_as = $mappings[$view_as];
        break;
    }

    $loop_template = str_replace(
      array('%view%'),
      array($view_as),
      $loop_template
    );

    $template_path = sprintf('%s/%s/%s.php', dirname(__FILE__), $post_template_dir, $loop_template);

    if(!file_exists($template_path)) {
        printf ('<p class="fail">Sorry, the template you are trying to use ("%s")
            in %s() does not exist (%s).',
            $template,
            __FUNCTION__,
            __FILE__);
        return false;
    }
    /* Allow for display of posts in order passed in post__in array
       [as the 'orderby' arg doesn't seem to work consistently without giving it some help]
       If 'post__in' is in args and 'orderby' is set to 'none', just grab those posts,
       in the order provided in the 'post__in' array.
    */
    if($loop_orderby && $loop_orderby == 'none' && $loop_post__in)
    {
        foreach($loop_post__in as $post_id)
            $loop_posts[] = get_post($post_id);
    }
    else
        $loop_posts = query_posts($args);

    /* Utility vars for the loop; in scope in included template */
    $loop_count             = 0;
    $loop_odd               = false;
    $loop_even              = false;
    $loop_first             = true;
    $loop_last              = false;
    $loop_css_class         = '';   // For convenience
    $loop_size = sizeof($loop_posts);
    $loop_owner = $temp_post;       /* The context from within this loop is called
                                       the global $post before we query */

    foreach($loop_posts as $post)
    {
        $loop_count += 1;
        ($loop_count % 2 == 0) ? $loop_even = true : $loop_even = false;
        ($loop_count % 2 == 1) ? $loop_odd  = true : $loop_odd  = false;
        ($loop_count == 1) ?     $loop_first = true : $loop_first = false;
        ($loop_count == $loop_size) ? $loop_last = true : $loop_last = false;
        ($loop_even) ? $loop_css_class = 'even' : $loop_class = 'odd';
        setup_postdata($post);
        include($template_path);
    }
    $wp_query = clone $temp_query;  // Put the displaced query and post back into global scope
    $post = clone $temp_post;       // And set up the post for use.
    setup_postdata($post);
    return $loop_posts;
}

/*********
* Returns the number of posts contained within a category, including sub-categories.
* Posts are only counted once even if they are posted to multiple sub-categories.
*/
function tmh_unique_posts_in_category($cat_id) {
  $wp_query = new WP_Query();
  $posts = $wp_query->query('cat='.$cat_id.'&posts_per_page=-1');
  $posts = $wp_query->post_count;
  unset($wp_query);
  return $posts;
}

/*********
* Returns author listing HTML
* Only authors who have active posts and a bio are included
*/
function dw_list_authors() {
  global $wpdb;

  $users = get_users(array());

  // Do a custom query to get post counts for everyone
  // This will save hundreds of queries over "WordPress-style" code
  $postsByUsersQuery = 'SELECT post_author, COUNT(*) as count, meta_value AS description FROM '.$wpdb->posts.' p, '.$wpdb->usermeta.' um WHERE post_status="publish" AND um.meta_key = "description" AND um.user_id = p.post_author AND meta_value != "" AND post_type = "post" GROUP BY post_author';
  $postsByUsersResult = $wpdb->get_results($postsByUsersQuery, ARRAY_A);
  $postsByUsersIndex = array();
  foreach($postsByUsersResult as $result) {
    $postsByUsersIndex[$result['post_author']] = array('count'=>$result['count'], 'description'=>$result['description']);
  }

  // Sort by number of posts
  foreach($users as $user) {
    $count = $postsByUsersIndex[$user->ID]['count'];
    if($count == '') { $count = 0; }
    $user->total_posts = $count;
    $user->description = $postsByUsersIndex[$user->ID]['description'];
  }
  usort($users, 'sort_objects_by_total_posts');
  $users = array_reverse($users);

  // Prep column output
  $column1 = $column2 = array();
  $which = true;

  // Generate output for authors
  foreach($users as $index=>$user) {
    if($user->total_posts > 1 && $user->description) {
      $item = '<li class="vcard" id="author-'.$user->user_login.'">';
      $item.= '<h3><a class="url" href="'.get_author_posts_url($user->ID).'">';
      if (function_exists('get_avatar')) {
        $item.= get_avatar($user->user_email, 48);
      }
      $item.= '<cite class="fn">'.$user->display_name.'</cite> <span class="post-count">'.$user->total_posts.' post'.($user->total_posts > 1 ? 's' : '').'</span></a></h3>';
      $item.= '<p class="desc">'.$user->description.'</p>';
      $item.= dw_get_author_meta($user->ID);
      $item.= '</li>';

      if($which) {
        array_push($column1, $item);
      }
      else {
        array_push($column2, $item);
      }
      $which = !$which;
    }
  }

  $return = '<ul class="author-list">'.implode('', $column1).'</ul>';
  $return.= '<ul class="author-list">'.implode('', $column2).'</ul>';

  return $return;
}

/*********
* Sorts WordPress users by Object key (total posts)
*/
function sort_objects_by_total_posts($a, $b) {
  if($a->total_posts == $b->total_posts){ return 0 ; }
  return ($a->total_posts < $b->total_posts) ? -1 : 1;
}

/*
* Try to clean up email addresses a bit.
*/
function hacks_author($name) {
  return preg_replace('/@.+/', '', $name);
}

/*********
* Returns author list, but not HTML which is gross.
* Only authors who have active posts and a bio are included
*/
function hacks_list_authors() {
  global $wpdb;

  $users = get_users(array());

  // Do a custom query to get post counts for everyone
  // This will save hundreds of queries over "WordPress-style" code
  $postsByUsersQuery = 'SELECT post_author, COUNT(*) as count, meta_value AS description FROM '.$wpdb->posts.' p, '.$wpdb->usermeta.' um WHERE post_status="publish" AND um.meta_key = "description" AND um.user_id = p.post_author AND meta_value != "" AND post_type = "post" GROUP BY post_author';
  $postsByUsersResult = $wpdb->get_results($postsByUsersQuery, ARRAY_A);
  $postsByUsersIndex = array();
  foreach($postsByUsersResult as $result) {
    $postsByUsersIndex[$result['post_author']] = array('count'=>$result['count'], 'description'=>$result['description']);
  }

  // Sort by number of posts
  foreach($users as $user) {
    $count = $postsByUsersIndex[$user->ID]['count'];
    if($count == '') { $count = 0; }
    $user->total_posts = $count;
    $user->description = $postsByUsersIndex[$user->ID]['description'];
  }
  usort($users, 'sort_objects_by_total_posts');
  $users = array_reverse($users);

  // Prep column output
  $return = array();

  // Generate output for authors
  foreach($users as $index=>$user) {
    if($user->total_posts > 1 && $user->description) {
      array_push($return, $user);
    }
  }

  return $return;
}

function hacks_category_link($category) {
  echo '<a href="' . get_category_link( $category->term_id ) . '" rel="category tag" title="' . sprintf( __( "View all posts in %s" ), $category->name ) . '" ' . '>'  . $category->name.'</a>';
}

/*********
* Display the_category(), excluding Featured
*/
function hacks_category_list() {
  $idx = 0;
  $categories = get_the_category();
  $num = count($categories);
  foreach($categories as $category) :
    $idx++;
    hacks_category_link($category);
    if ($idx < $num) :
      if ($num > 2):
        echo ', ';
      endif;
      if ($num == 2 || ($num > 2 && $num - $idx == 1)):
        echo ' and ';
      endif;
    endif;
  endforeach;
}


function register_my_menu() {
  register_nav_menu('header-menu',__( 'Header Menu' ));
}
add_action( 'init', 'register_my_menu' );

?>