<?php
get_header();
?>
<div class="wrap page-hero">
  <p class="kicker">Packages</p>
  <h1>Tour packages</h1>
  <p>Add or edit a page in WP Admin and it appears in this list.</p>
</div>
<div class="wrap page-block">
  <div class="offers">
    <?php foreach (mehs_public_pages() as $page) {
        mehs_offer($page);
    } ?>
  </div>
</div>
<?php get_footer(); ?>
