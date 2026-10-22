<?php
get_header();
if (!mehs_render_saved_page("gallery")) {
    $mehs_gallery = get_page_by_path("gallery");
    $mehs_gallery_html = $mehs_gallery instanceof WP_Post ? mehs_saved_page_html($mehs_gallery->ID) : "";
    if ($mehs_gallery_html !== "") {
        echo $mehs_gallery_html;
    } else {
        mehs_gallery_wall();
    }
}
get_footer();
