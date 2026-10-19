<?php get_header(); ?>
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
<section class="banner page-banner">
  <?php if (has_post_thumbnail()) : ?>
    <?php the_post_thumbnail("large", ["class" => "banner-media"]); ?>
  <?php else : ?>
    <?php echo mehs_image("acacia-sunset.webp", get_the_title(), "banner-media"); ?>
  <?php endif; ?>
  <div class="banner-copy wrap">
    <p class="crumbs"><a href="<?php echo esc_url(home_url("/")); ?>">Home</a> · <a href="<?php echo esc_url(mehs_page_url("blog") ?: home_url("/")); ?>">Blog</a></p>
    <h1><?php the_title(); ?></h1>
  </div>
</section>
<article class="wrap page-block entry">
  <?php the_content(); ?>
</article>
<?php endwhile; endif; ?>
<?php get_footer(); ?>
