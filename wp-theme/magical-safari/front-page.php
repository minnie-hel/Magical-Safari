<?php get_header(); ?>
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
    <p>These are the pages saved in WP Admin. Edit a page there and it updates here.</p>
  </div>
  <div class="offers">
    <?php foreach (mehs_public_pages(6) as $page) {
        mehs_offer($page);
    } ?>
  </div>
  <p class="center"><a class="btn btn-green" href="<?php echo esc_url(home_url("/packages/")); ?>">View all packages</a></p>
</div></section>
<?php get_footer(); ?>
