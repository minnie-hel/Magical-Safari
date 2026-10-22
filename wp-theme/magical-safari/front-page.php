<?php get_header(); ?>
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
<?php if (mehs_elementor_canvas()) : ?>
<?php elseif (($mehs_live = mehs_live_markup()) !== "") : ?>
<?php echo $mehs_live; ?>
<?php else : ?>
<?php mehs_render_front(); ?>
<?php endif; ?>
<?php endwhile; endif; ?>
<?php get_footer(); ?>
