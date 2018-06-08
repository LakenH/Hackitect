<?php if (( is_single() || is_page() ) && (!is_front_page()) ) : $pageClass = 'home'; ?>

<?php endif ?>

<!doctype html>
<html lang="en-US">
<head data-template-path="<?php echo get_template_directory_uri(); ?>">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <?php get_template_part('includes/metadata'); ?>

  <link href='//fonts.googleapis.com/css?family=Open+Sans:400,400italic,700,700italic' rel='stylesheet' type='text/css'>
  <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/style.css">
  <link rel="stylesheet" href="//cdn.jsdelivr.net/highlight.js/8.6.0/styles/solarized_light.min.css">

  <?php wp_head(); ?>
</head>
<body>
  <!-- <div class="outer-wrapper"> -->
    <header class="section section--fullwidth header">
      <div class="masthead row">
        <div class="branding block block--3">
          <h1>
            <a href="<?php bloginfo('url'); ?>">
           
              <img class="branding__wordmark" src="<?php echo get_template_directory_uri(); ?>/img/logo.svg" alt="Worlditect">

            </a>
          </h1>
        </div>
      </div>
      <?php wp_nav_menu( array( 'theme_location' => 'header-menu' ) ); ?>
    </header>
