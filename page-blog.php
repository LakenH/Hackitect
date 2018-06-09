<?php get_header();
$featured_id = get_cat_ID('Featured Article');
$categories = array(
  "Accessibility",
  "Extensions",
  "Plugins",
  "Themes",
  "WordPress"
);
?>
<?php query_posts('cat='. $featured_id .'&posts_per_page=1'); ?>
<?php if (have_posts()): while ( have_posts() ) : the_post(); ?>
<section
  class="feature section section--fullwidth">
  <div class="feature__pane">
    <h1><a class="feature__link" href="<?php the_permalink() ?>"><?php the_title(); ?>&nbsp;&rarr;</a></h1>
    <h2 class="feature__label">Featured Article</h2>
  </div>
</section>
<?php endwhile; endif; ?>

<section class="recent content section">
  <div class="row">
    <div class="block block--3">
      <h2 class="heading">Recent Articles</h2>
      <ul class="article-list">
        <?php wp_reset_query(); ?>
        <?php if ( have_posts() ) : ?>
          <?php while ( have_posts() ) : the_post(); ?>    
            <li class="list-item row listing">
              <?php echo get_avatar( get_the_author_meta('user_email'), 72 ); ?>
              <div class="block block--1">
                <h3 class="post__title">
                  <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h3>
                <p class="post__tease"><?php echo get_the_excerpt(); ?></p>
                <div class="post__meta">
                  Posted on
                  <abbr class="published" title="<?php the_time('Y-m-d\TH:i:sP'); ?>">
                    <?php the_time(get_option('date_format')); ?>
                  </abbr>
                </div>
              </div>
            </li>
          <?php endwhile; ?>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</section>

<?php get_footer(); ?>
