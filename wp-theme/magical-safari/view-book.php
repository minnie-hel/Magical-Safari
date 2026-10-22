<?php
get_header();
if (!mehs_render_saved_page("book")) {
    $mehs_book = get_page_by_path("book");
    $mehs_book_html = $mehs_book instanceof WP_Post ? mehs_saved_page_html($mehs_book->ID) : "";
    if ($mehs_book_html !== "") {
        echo $mehs_book_html;
    } else {
        mehs_render_book_form();
    }
}
get_footer();
