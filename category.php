<?php get_header(); ?>

<main id="content-main" class="section">
    <h1 class="page-title"><?php single_cat_title(); ?></h1>

  <?php if (have_posts()) : ?>
  <ul class="article-list">
    <?php while ( have_posts() ) : the_post(); ?>
    <li class="list-item row listing">
      <?php $authors = get_coauthors($post->ID); ?>
      <?php echo get_avatar($authors[0], 72) ?>
      <div class="block block--1">
        <h3 class="post__title">
          <a href="<?php the_permalink() ?>"><?php the_title(); ?></a>
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
    <?php endwhile;?>
  </ul>
  <?php else: ?>
    <p><?php esc_html_e( 'Sorry, no posts matched your criteria.' ); ?></p>
  <?php endif; ?>
  <hr class="dino">
</main><!-- /#content-main -->

<?php get_footer(); ?>