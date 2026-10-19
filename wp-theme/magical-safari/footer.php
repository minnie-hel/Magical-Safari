</main>
<footer class="site-footer"><div class="wrap">
  <div class="foot-grid">
    <div>
      <h2>Contact</h2>
      <p class="foot-name"><?php bloginfo("name"); ?></p>
      <ul class="foot-contact">
        <li><span>Phone</span><a href="tel:<?php echo esc_attr(MEHS_PHONE_HREF); ?>"><?php echo esc_html(MEHS_PHONE); ?></a></li>
        <li><span>Email</span><a href="mailto:<?php echo esc_attr(MEHS_EMAIL); ?>"><?php echo esc_html(MEHS_EMAIL); ?></a></li>
        <li><span>Office</span><span>Nairobi, Kenya</span></li>
      </ul>
    </div>
    <nav aria-label="Plan">
      <h2>Plan</h2>
      <a href="<?php echo esc_url(home_url("/book/")); ?>">Book a safari</a>
      <a href="<?php echo esc_url(home_url("/packages/")); ?>">Packages</a>
      <?php if ($group = mehs_page_url("group-safaris")) : ?><a href="<?php echo esc_url($group); ?>">Group safaris</a><?php endif; ?>
      <?php if ($trips = mehs_page_url("excursions")) : ?><a href="<?php echo esc_url($trips); ?>">Day trips</a><?php endif; ?>
      <?php if ($policy = mehs_page_url("booking-policy")) : ?><a href="<?php echo esc_url($policy); ?>">Booking policy</a><?php endif; ?>
    </nav>
    <nav aria-label="Learn">
      <h2>Learn</h2>
      <?php if ($about = mehs_page_url("about-us")) : ?><a href="<?php echo esc_url($about); ?>">About</a><?php endif; ?>
      <?php if ($when = mehs_page_url("best-time-to-visit")) : ?><a href="<?php echo esc_url($when); ?>">Best time to visit</a><?php endif; ?>
      <?php if ($info = mehs_page_url("travel-info")) : ?><a href="<?php echo esc_url($info); ?>">Visas, packing, tipping</a><?php endif; ?>
      <?php if ($faqs = mehs_page_url("faqs")) : ?><a href="<?php echo esc_url($faqs); ?>">FAQs</a><?php endif; ?>
      <?php if ($blog = mehs_page_url("blog")) : ?><a href="<?php echo esc_url($blog); ?>">Blog</a><?php endif; ?>
    </nav>
    <div class="foot-news">
      <h2>Newsletter</h2>
      <p>Seasonal notes by email. We write to you. We do not sell the address.</p>
      <form class="foot-form" data-enquiry data-whatsapp-number="<?php echo esc_attr(MEHS_WHATSAPP); ?>" data-email-address="<?php echo esc_attr(MEHS_EMAIL); ?>">
        <label>Email address<input type="email" name="email" placeholder="Email address" required></label>
        <input type="hidden" name="trip" value="Newsletter">
        <input type="hidden" name="name" value="Newsletter reader">
        <input type="hidden" name="month" value="Not travelling yet">
        <input type="hidden" name="adults" value="1">
        <div class="hp"><label>Leave this blank<input name="mehs_leave_blank" tabindex="-1" autocomplete="off"></label></div>
        <button class="btn btn-clay" type="submit" name="channel" value="email">Request the list</button>
        <div class="result" data-result><p data-status></p></div>
      </form>
    </div>
  </div>
  <div class="legal">
    <p>© <?php echo esc_html(gmdate("Y")); ?> <?php bloginfo("name"); ?></p>
    <p class="legal-links">
      <?php if ($privacy = mehs_page_url("privacy")) : ?><a href="<?php echo esc_url($privacy); ?>">Privacy</a><?php endif; ?>
      <?php if ($credits = mehs_page_url("image-credits")) : ?><a href="<?php echo esc_url($credits); ?>">Photograph credits</a><?php endif; ?>
    </p>
  </div>
  <p class="legal-note">Display prices in EUR and GBP are approximate. Quotes are issued in US dollars.</p>
</div></footer>
<a class="wa-float" href="https://wa.me/<?php echo esc_attr(MEHS_WHATSAPP); ?>" target="_blank" rel="noopener">WhatsApp</a>
<?php wp_footer(); ?>
</body>
</html>
