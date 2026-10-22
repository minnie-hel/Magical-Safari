<?php
if (mehs_elementor_request()) {
    $mehs_view = get_post(mehs_view_id());
    if ($mehs_view instanceof WP_Post) {
        $GLOBALS["post"] = $mehs_view;
        setup_postdata($mehs_view);
    }
    get_header();
    if (!mehs_elementor_canvas()) {
        the_content();
    }
    get_footer();
    return;
}
?>
<?php get_header(); ?>
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
<?php
$mehs_package = null;
if (is_page("packages") && !empty($_GET["itinerary"])) {
    $mehs_package = mehs_find_tour(sanitize_title(wp_unslash($_GET["itinerary"])));
}
?>
<?php if ($mehs_package) : ?>
<?php mehs_render_package($mehs_package); ?>
<?php elseif (mehs_elementor_canvas()) : ?>
<?php elseif (mehs_elementor_request()) : ?>
<?php the_content(); ?>
<?php elseif (($mehs_live = mehs_live_markup()) !== "") : ?>
<?php echo $mehs_live; ?>
<?php elseif (is_page("packages")) : ?>
<?php mehs_render_packages_list(); ?>
<?php elseif (is_page("book")) : ?>
<?php mehs_render_book_form(); ?>
<?php elseif (is_page("gallery")) : ?>
<?php mehs_gallery_wall(); ?>
<?php elseif (is_page("about-us")) : ?>
<?php mehs_render_about(); ?>
<?php elseif (mehs_render_known_page()) : ?>
<?php elseif (mehs_try_saved_itinerary()) : ?>
<?php else : ?>
<section class="banner page-banner">
  <?php if (has_post_thumbnail()) : ?>
    <?php the_post_thumbnail("large", ["class" => "banner-media"]); ?>
  <?php else : ?>
    <?php echo mehs_image("safari-vehicle.webp", get_the_title(), "banner-media"); ?>
  <?php endif; ?>
  <div class="banner-copy wrap">
    <p class="crumbs"><a href="<?php echo esc_url(home_url("/")); ?>">Home</a></p>
    <h1><?php the_title(); ?></h1>
  </div>
</section>
<div class="wrap page-block entry">
  <?php if (is_page("blog")) : ?>
    <div class="posts">
      <?php
      $posts = get_posts(["numberposts" => 12]);
      foreach ($posts as $post) :
          setup_postdata($post); ?>
        <article class="card">
          <?php if (has_post_thumbnail($post)) {
              echo get_the_post_thumbnail($post, "mehs-card");
          } ?>
          <div class="safari-body">
            <p class="fine"><?php echo esc_html(get_the_date("", $post)); ?></p>
            <h2><a href="<?php echo esc_url(get_permalink($post)); ?>"><?php echo esc_html(get_the_title($post)); ?></a></h2>
            <p><?php echo esc_html(wp_trim_words(get_the_excerpt($post), 32)); ?></p>
          </div>
        </article>
      <?php endforeach;
      wp_reset_postdata(); ?>
    </div>
  <?php endif; ?>
  <?php the_content(); ?>
  <?php if (is_page("contact")) : ?>
    <form class="contact-card message-form" data-enquiry data-whatsapp-number="<?php echo esc_attr(MEHS_WHATSAPP); ?>" data-email-address="<?php echo esc_attr(MEHS_EMAIL); ?>">
      <h2>Send a message</h2>
      <div class="form-grid">
        <label>Full name<input name="name" autocomplete="name" required></label>
        <label>Phone<input name="phone" autocomplete="tel" required></label>
        <label>Email address<input name="email" type="email" autocomplete="email" required></label>
        <label>Message<textarea name="note" rows="5" required></textarea></label>
        <input type="hidden" name="trip" value="Contact message">
        <div class="hp"><label>Leave this blank<input name="mehs_leave_blank" tabindex="-1" autocomplete="off"></label></div>
        <div class="send-row">
          <button class="btn btn-green" type="submit" name="channel" value="whatsapp">Send on WhatsApp</button>
          <button class="btn btn-line" type="submit" name="channel" value="email">Send by email</button>
        </div>
        <div class="result" data-result><p data-status></p></div>
      </div>
    </form>
  <?php endif; ?>
</div>
<?php endif; ?>
<!-- <?php endwhile; endif; ?> -->
<?php get_footer(); ?>
