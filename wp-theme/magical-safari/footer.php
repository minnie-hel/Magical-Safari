</main>
<footer class="site-footer"><div class="wrap">
  <div class="foot-grid">
    <div>
      <h2><?php bloginfo("name"); ?></h2>
      <p>Private journeys in Kenya, Tanzania, Uganda and Rwanda, planned from Nairobi.</p>
      <p><a href="tel:<?php echo esc_attr(MEHS_PHONE_HREF); ?>"><?php echo esc_html(MEHS_PHONE); ?></a><br><a href="mailto:<?php echo esc_attr(MEHS_EMAIL); ?>"><?php echo esc_html(MEHS_EMAIL); ?></a><br>Nairobi, Kenya</p>
    </div>
    <div>
      <h2>Plan</h2>
      <p>
        <a href="<?php echo esc_url(home_url("/book/")); ?>">Book a safari</a><br>
        <a href="<?php echo esc_url(home_url("/packages/")); ?>">Packages</a><br>
        <?php if ($group = mehs_page_url("group-safaris")) : ?><a href="<?php echo esc_url($group); ?>">Group safaris</a><br><?php endif; ?>
        <?php if ($trips = mehs_page_url("excursions")) : ?><a href="<?php echo esc_url($trips); ?>">Day trips</a><?php endif; ?>
      </p>
    </div>
    <div>
      <h2>Learn</h2>
      <p>
        <?php if ($about = mehs_page_url("about-us")) : ?><a href="<?php echo esc_url($about); ?>">About</a><br><?php endif; ?>
        <?php if ($blog = mehs_page_url("blog")) : ?><a href="<?php echo esc_url($blog); ?>">Blog</a><br><?php endif; ?>
        <a href="<?php echo esc_url(home_url("/gallery/")); ?>">Gallery</a>
      </p>
    </div>
    <div>
      <h2>Notes</h2>
      <form data-enquiry data-whatsapp-number="<?php echo esc_attr(MEHS_WHATSAPP); ?>" data-email-address="<?php echo esc_attr(MEHS_EMAIL); ?>">
        <label>Newsletter<input type="email" name="email" placeholder="Email address" required></label>
        <input type="hidden" name="trip" value="Newsletter">
        <input type="hidden" name="name" value="Newsletter reader">
        <div class="hp"><label>Leave this blank<input name="mehs_leave_blank" tabindex="-1" autocomplete="off"></label></div>
        <div class="send-row"><button class="btn btn-line" type="submit" name="channel" value="email">Request the list</button></div>
        <div class="result" data-result><p data-status></p></div>
      </form>
    </div>
  </div>
  <div class="legal">
    <p>© <?php echo esc_html(gmdate("Y")); ?> <?php bloginfo("name"); ?>.</p>
  </div>
</div></footer>
<a class="wa-float" href="https://wa.me/<?php echo esc_attr(MEHS_WHATSAPP); ?>" target="_blank" rel="noopener">WhatsApp</a>
<?php wp_footer(); ?>
</body>
</html>
