<?php
get_header();
$mehs_itinerary = isset($_GET["itinerary"]) ? sanitize_title(wp_unslash($_GET["itinerary"])) : "";
if ($mehs_itinerary !== "") {
    $mehs_tour = mehs_find_tour($mehs_itinerary);
    if ($mehs_tour) {
        mehs_render_package($mehs_tour);
        get_footer();
        return;
    }
}
if (!mehs_render_saved_page("packages")) {
    mehs_render_packages_list();
}
get_footer();
