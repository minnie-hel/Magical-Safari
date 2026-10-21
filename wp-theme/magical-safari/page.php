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
<?php elseif (mehs_use_page_content()) : ?>
<?php the_content(); ?>
<?php elseif (is_page("packages")) : ?>
<?php mehs_render_packages_list(); ?>
<?php elseif (is_page("book")) : ?>
<?php mehs_render_book_form(); ?>
<?php elseif (is_page("gallery")) : ?>
<?php mehs_gallery_wall(); ?>
<?php elseif (is_page("about-us")) : ?>
<section class="banner page-banner">
  <?php echo mehs_image("acacia-sunset.webp", "Acacia at sunset", "banner-media"); ?>
  <div class="banner-copy wrap">
    <p class="kicker">Our story</p>
    <h1>About us</h1>
    <p class="crumbs"><a href="<?php echo esc_url(home_url("/")); ?>">Home</a> · About us</p>
  </div>
</section>
<section><div class="wrap about-grid">
  <div>
    <p class="kicker">Who we are</p>
    <h2>About us</h2>
    <?php
    $mehs_about = array_values(array_filter(mehs_saved_paragraphs("About us", "h2"), function ($paragraph) {
        return strlen($paragraph) > 40;
    }));
    if ($mehs_about) :
        foreach ($mehs_about as $mehs_about_paragraph) : ?>
    <p><?php echo esc_html($mehs_about_paragraph); ?></p>
        <?php endforeach;
    else : ?>
    <p><strong>Magical East Africa Heritage Safaris</strong> is a safari company based in Nairobi. Kenya is the flagship: the Mara, Amboseli, Tsavo, Samburu, Nakuru and Ol Pejeta. We also plan Tanzania, Uganda and Rwanda when the days, and the permit budget, are real.</p>
    <p>The journeys are private, or a shared vehicle when you choose a group safari. The same desk writes the itinerary, names the lodges on the quote, and stays on the phone from the first message until you are home. On the vehicle, guests write about guide James Ngigi. Nicole helps shape the plan before you book. Grace is the person guests have reached after the deposit.</p>
    <p>The name on this website is the name on our email address and the name that should appear on your invoice. Some reviews shorten it to Magical Africa Safaris. That is the same desk.</p>
    <p>We are listed on SafariBookings and SafariOptions. Ask us for the Tourism Regulatory Authority licence before you pay a deposit.</p>
    <?php endif; ?>
  </div>
  <figure class="about-figure"><?php echo mehs_image("safari-vehicle.webp", "Safari vehicle at sunset", "about-photo"); ?></figure>
</div></section>
<section class="about-band"><div class="wrap">
  <div class="mission-grid">
    <article><p class="kicker">Our mission</p><p>Personal safari plans that connect travellers with East Africa’s wildlife, parks and cultures, with the price written down before anyone pays.</p></article>
    <article><p class="kicker">Our vision</p><p>To be a trusted desk for authentic safaris in Kenya, Tanzania, Uganda and Rwanda.</p></article>
  </div>
  <div class="section-intro">
    <p class="kicker">What drives us</p>
    <h2>Our core values</h2>
  </div>
  <div class="values">
    <article class="value"><span aria-hidden="true">✓</span><div><strong>Customer satisfaction</strong><p>The route is built around your month, your group and the way you like to travel.</p></div></article>
    <article class="value"><span aria-hidden="true">✓</span><div><strong>Professionalism</strong><p>One name, one phone number, and a written quote before you pay.</p></div></article>
    <article class="value"><span aria-hidden="true">✓</span><div><strong>Sustainability</strong><p>Park tracks, conservancies where they fit, and community visits paid to that community.</p></div></article>
    <article class="value"><span aria-hidden="true">✓</span><div><strong>Safety</strong><p>A licensed operator, a known vehicle, and a meeting point confirmed with your flight.</p></div></article>
    <article class="value"><span aria-hidden="true">✓</span><div><strong>Authenticity</strong><p>The parks, the wildlife and the people on the route, not a staged version of them.</p></div></article>
  </div>
</div></section>
<section class="cta-band">
  <h2>Ready to explore East Africa?</h2>
  <p>Tell us the month and who is travelling. We reply with a plan and a written price.</p>
  <div class="hero-actions" style="justify-content:center">
    <a class="btn btn-clay" href="<?php echo esc_url(home_url("/packages/")); ?>">View packages</a>
    <a class="btn btn-ghost" href="<?php echo esc_url(mehs_page_url("contact") ?: home_url("/contact/")); ?>">Get in touch</a>
  </div>
</section>
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
