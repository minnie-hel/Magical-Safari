<?php
get_header();
if (!mehs_render_saved_page("book")) {
    mehs_render_book_form();
}
get_footer();
