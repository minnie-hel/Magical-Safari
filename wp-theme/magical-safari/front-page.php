<?php get_header(); ?>
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
<?php if (mehs_use_page_content()) : ?>
<?php the_content(); ?>
<?php else : ?>
<section class="banner hero">
  <?php echo mehs_image("elephant-savanna.webp", "Elephants on the savannah", "banner-media"); ?>
  <div class="banner-copy wrap">
    <h1>Discover the magic of East Africa.</h1>
    <p class="lede">Private safaris in Kenya, Tanzania, Uganda and Rwanda, planned in Nairobi.</p>
    <div class="hero-actions">
      <a class="btn btn-clay" href="<?php echo esc_url(home_url("/packages/")); ?>">Explore packages</a>
      <a class="btn btn-ghost" href="<?php echo esc_url(home_url("/book/")); ?>">Book a safari</a>
    </div>
  </div>
</section>
<section><div class="wrap">
  <div class="section-intro">
    <p class="kicker">Our offerings</p>
    <h2>Featured tour packages</h2>
        <p>Open a package for the day-by-day plan, then book it with your travel date.</p>
      </div>
      <div class="offers">
        <?php foreach (array_slice(mehs_tours_in("short-kenya-safaris"), 0, 6) as $tour) {
            mehs_offer_card($tour);
        } ?>
      </div>
  <p class="center"><a class="btn btn-green" href="<?php echo esc_url(home_url("/packages/")); ?>">View all packages</a></p>
</div></section>
<?php endif; ?>
<?php endwhile; endif; ?>
<?php get_footer(); ?>
