<?php
get_header();
if (!mehs_render_saved_page("gallery")) {
    mehs_gallery_wall();
}
get_footer();
