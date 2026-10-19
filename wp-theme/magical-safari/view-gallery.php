<?php get_header(); ?>
<div class="wrap page-hero">
  <p class="crumbs"><a href="<?php echo esc_url(home_url("/")); ?>">Home</a></p>
  <h1>Gallery</h1>
  <p>Photos added in WP Admin, Media, appear here.</p>
</div>
<div class="wrap gallery page-block">
  <?php
  $images = get_posts([
      "post_type" => "attachment",
      "post_mime_type" => "image",
      "post_status" => "inherit",
      "posts_per_page" => 24,
  ]);
  foreach ($images as $image) {
      $src = wp_get_attachment_image_url($image->ID, "mehs-card");
      if (!$src) {
          continue;
      }
      echo '<img src="' . esc_url($src) . '" alt="' . esc_attr(get_the_title($image)) . '">';
  }
  ?>
</div>
<?php get_footer(); ?>
