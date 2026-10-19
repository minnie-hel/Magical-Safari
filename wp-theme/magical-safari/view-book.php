<?php
get_header();
mehs_banner("safari-vehicle.webp", "Reservation", "Book your safari");
$trip = isset($_GET["trip"]) ? sanitize_text_field(wp_unslash($_GET["trip"])) : "";
?>
<div class="wrap page-block">
  <p class="page-lead">Reservation form. We confirm availability and send the price in writing.</p>
  <form class="book-form" data-enquiry data-whatsapp-number="<?php echo esc_attr(MEHS_WHATSAPP); ?>" data-email-address="<?php echo esc_attr(MEHS_EMAIL); ?>">
    <div class="plan-card">
      <h2>Personal details</h2>
      <div class="form-grid">
        <label>Full name<input name="name" autocomplete="name" required></label>
        <label>Email address<input name="email" type="email" autocomplete="email" required></label>
        <div class="form-grid form-2">
          <label>Phone number<input name="phone" autocomplete="tel" required></label>
          <label>WhatsApp number<input name="whatsapp" autocomplete="tel"></label>
        </div>
        <label>Travel date<input name="date" type="date" required></label>
        <label>Number of people<input name="adults" type="number" min="1" max="20" value="2" required></label>
        <label>Package<input name="trip" value="<?php echo esc_attr($trip); ?>" placeholder="Name a safari, or leave this open"></label>
        <label>Anything we should know?<textarea name="note" rows="4"></textarea></label>
        <p class="fine">We confirm availability and send the price in writing. No payment is taken on this form.</p>
        <div class="hp"><label>Leave this blank<input name="mehs_leave_blank" tabindex="-1" autocomplete="off"></label></div>
        <div class="send-row">
          <button class="btn btn-green" type="submit" name="channel" value="whatsapp">Send on WhatsApp</button>
          <button class="btn btn-line" type="submit" name="channel" value="email">Send by email</button>
        </div>
        <div class="result" data-result><p data-status></p></div>
      </div>
    </div>
  </form>
</div>
<?php get_footer(); ?>
