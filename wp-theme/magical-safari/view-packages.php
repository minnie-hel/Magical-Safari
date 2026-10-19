<?php
get_header();
mehs_banner("elephant-savanna.webp", "Safaris", "Packages");
?>
<div class="wrap page-block">
  <p class="page-lead">Open a package for the day-by-day plan, then book it with your travel date. Shared departures are on the group page, and single days are with the excursions.</p>
  <?php
  $groups = [
      "Kenya" => "short-kenya-safaris",
      "Tanzania" => "tanzania-safaris",
      "Uganda" => "uganda-safaris",
      "Rwanda" => "rwanda-safaris",
      "International" => "international-destinations",
  ];
  foreach ($groups as $title => $section) :
      $tours = mehs_tours_in($section);
      if (!$tours) {
          continue;
      } ?>
    <section class="gallery-block">
      <h2><?php echo esc_html($title); ?></h2>
      <?php mehs_offer_grid($tours); ?>
    </section>
  <?php endforeach; ?>
</div>
<?php get_footer(); ?>
