<?php get_header(); ?>
<div class="wrap page-block">
  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <article class="entry">
      <h1><?php the_title(); ?></h1>
      <?php the_content(); ?>
    </article>
  <?php endwhile; else : ?>
    <h1>Nothing is published here yet.</h1>
  <?php endif; ?>
</div>
<?php get_footer(); ?>
