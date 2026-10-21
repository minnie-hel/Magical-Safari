<?php
/**
 * Magical Safari theme.
 */

define("MEHS_WHATSAPP", "254722326845");
define("MEHS_EMAIL", "info@magicaleastafricaheritagesafaris.com");
define("MEHS_PHONE", "+254 722 326 845");
define("MEHS_PHONE_HREF", "+254722326845");

require get_template_directory() . "/inc/design.php";

function mehs_post_has_elementor($post_id) {
    $post_id = (int) $post_id;
    if (!$post_id) {
        return false;
    }
    if (get_post_meta($post_id, "_elementor_edit_mode", true) === "builder") {
        return true;
    }
    $data = get_post_meta($post_id, "_elementor_data", true);
    if (is_array($data)) {
        return $data !== [];
    }
    return is_string($data) && strlen($data) > 20;
}

function mehs_elementor_request() {
    if (isset($_GET["elementor-preview"]) || isset($_GET["elementor_library"])) {
        return true;
    }
    if (!class_exists("\\Elementor\\Plugin")) {
        return false;
    }
    $plugin = \Elementor\Plugin::$instance;
    if (!empty($plugin->preview) && method_exists($plugin->preview, "is_preview_mode") && $plugin->preview->is_preview_mode()) {
        return true;
    }
    return !empty($plugin->editor) && method_exists($plugin->editor, "is_edit_mode") && $plugin->editor->is_edit_mode();
}

function mehs_use_page_content() {
    return is_singular() && mehs_elementor_request();
}

function mehs_render_saved_page($slug) {
    if (!mehs_elementor_request()) {
        return false;
    }
    $page = get_page_by_path($slug);
    if (!$page instanceof WP_Post || $page->post_status !== "publish") {
        return false;
    }
    $preview_id = isset($_GET["elementor-preview"]) ? (int) $_GET["elementor-preview"] : 0;
    if ($preview_id && $preview_id !== (int) $page->ID) {
        return false;
    }
    $GLOBALS["post"] = $page;
    setup_postdata($page);
    the_content();
    wp_reset_postdata();
    return true;
}

function mehs_setup() {
    add_theme_support("title-tag");
    add_theme_support("post-thumbnails");
    add_theme_support("html5", ["search-form", "comment-form", "comment-list", "gallery", "caption", "style", "script"]);
    add_image_size("mehs-card", 960, 600, true);
}
add_action("after_setup_theme", "mehs_setup");

function mehs_assets() {
    $dir = get_template_directory_uri();
    wp_enqueue_style("mehs-font", "https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap", [], null);
    wp_enqueue_style("mehs", $dir . "/assets/css/styles.css", ["mehs-font"], "1.1.8");
    wp_enqueue_style("mehs-theme", $dir . "/assets/css/theme.css", ["mehs"], "1.1.8");
    wp_enqueue_script("mehs", $dir . "/assets/js/main.js", [], "1.1.8", true);
    wp_localize_script("mehs", "MEHS_BOOK", [
        "url" => admin_url("admin-ajax.php?action=mehs_booking"),
        "nonce" => wp_create_nonce("mehs_booking"),
    ]);
}
add_action("wp_enqueue_scripts", "mehs_assets");

function mehs_asset($path) {
    return get_template_directory_uri() . "/" . ltrim($path, "/");
}

function mehs_image($file, $alt, $class = "") {
    $src = esc_url(mehs_asset("assets/images/" . $file));
    $class_attr = $class !== "" ? ' class="' . esc_attr($class) . '"' : "";
    return '<img src="' . $src . '" alt="' . esc_attr($alt) . '"' . $class_attr . ">";
}

function mehs_page_url($slug) {
    $page = get_page_by_path($slug);
    return ($page instanceof WP_Post) ? get_permalink($page) : "";
}

function mehs_skipped_slugs() {
    return [
        "my-account",
        "my-account-2",
        "wp-travel-engine-checkout",
        "wp-travel-engine-cart",
        "checkout",
        "thank-you",
        "enquiry-thank-you-page",
        "wishlist",
        "trip-search-result",
        "travellers-information",
        "terms-and-conditions",
        "activities",
        "destination",
        "trip-types",
        "blog",
        "east-africa-safaris",
    ];
}

function mehs_public_pages($limit = 0) {
    $pages = get_pages([
        "sort_column" => "post_modified",
        "sort_order" => "DESC",
        "post_status" => "publish",
    ]);
    $skip = mehs_skipped_slugs();
    $chosen = [];
    foreach ($pages as $page) {
        if (in_array($page->post_name, $skip, true)) {
            continue;
        }
        $chosen[] = $page;
        if ($limit && count($chosen) >= $limit) {
            break;
        }
    }
    return $chosen;
}

function mehs_offer($page) {
    $title = get_the_title($page);
    $image = get_the_post_thumbnail_url($page, "mehs-card");
    if (!$image) {
        $image = mehs_asset("assets/images/elephant-savanna.webp");
    }
    $excerpt = wp_trim_words(wp_strip_all_tags(get_the_excerpt($page)), 28);
    ?>
    <a class="offer" href="<?php echo esc_url(get_permalink($page)); ?>">
      <div class="offer-photo">
        <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>">
      </div>
      <div class="offer-body">
        <h3><?php echo esc_html($title); ?></h3>
        <?php if ($excerpt !== "") : ?><p><?php echo esc_html($excerpt); ?></p><?php endif; ?>
        <div class="offer-foot"><span>View</span><span class="offer-go">View itinerary</span></div>
      </div>
    </a>
    <?php
}

function mehs_ensure_theme_pages() {
    $pages = [
        "packages" => "Packages",
        "book" => "Book",
        "gallery" => "Gallery",
    ];
    foreach ($pages as $slug => $title) {
        $existing = get_page_by_path($slug);
        if ($existing instanceof WP_Post) {
            continue;
        }
        wp_insert_post([
            "post_title" => $title,
            "post_name" => $slug,
            "post_status" => "publish",
            "post_type" => "page",
            "post_content" => "",
        ]);
    }
}
add_action("init", "mehs_ensure_theme_pages");

function mehs_redirect_old_book_link() {
    $on_book = is_page("book") || get_query_var("mehs_view") === "book";
    if (!$on_book) {
        return;
    }
    $trip = isset($_GET["trip"]) ? sanitize_text_field(wp_unslash($_GET["trip"])) : "";
    $from_itinerary = $trip !== "" && (!isset($_GET["book"]) || $_GET["book"] !== "1");
    if (!$from_itinerary) {
        return;
    }
    $tour = mehs_find_tour_by_title($trip);
    if (!$tour) {
        return;
    }
    wp_safe_redirect(mehs_tour_url($tour));
    exit;
}
add_action("template_redirect", "mehs_redirect_old_book_link", 1);

function mehs_query_vars($vars) {
    $vars[] = "mehs_view";
    return $vars;
}
add_filter("query_vars", "mehs_query_vars");

function mehs_template($template) {
    $view = get_query_var("mehs_view");
    if ($view) {
        $file = get_template_directory() . "/view-" . $view . ".php";
        if (is_readable($file)) {
            return $file;
        }
    }
    return $template;
}
add_filter("template_include", "mehs_template");

function mehs_flush_routes() {
    flush_rewrite_rules(false);
}
add_action("after_switch_theme", "mehs_flush_routes");

function mehs_clean($value, $max) {
    $text = trim(preg_replace("/[\r\n]+/", " ", (string) $value));
    return function_exists("mb_substr") ? mb_substr($text, 0, $max) : substr($text, 0, $max);
}

function mehs_handle_booking() {
    $raw = file_get_contents("php://input");
    $data = json_decode($raw === false ? "" : $raw, true);
    if (!is_array($data)) {
        $data = $_POST;
    }
    if (mehs_clean($data["mehs_leave_blank"] ?? "", 200) !== "") {
        wp_send_json(["ok" => true]);
    }
    if (empty($data["nonce"]) || !wp_verify_nonce($data["nonce"], "mehs_booking")) {
        wp_send_json(["ok" => false, "error" => "Refresh the page and try again."], 400);
    }
    $name = mehs_clean($data["name"] ?? "", 120);
    $email = mehs_clean($data["email"] ?? "", 160);
    if ($name === "" || !is_email($email)) {
        wp_send_json(["ok" => false, "error" => "Add your name and a valid email address."], 400);
    }
    $rows = [
        "Name" => $name,
        "Email" => $email,
        "Phone" => mehs_clean($data["phone"] ?? "", 40),
        "WhatsApp" => mehs_clean($data["whatsapp"] ?? "", 40),
        "Country" => mehs_clean($data["country"] ?? "", 80),
        "Travel date" => mehs_clean($data["date"] ?? "", 40),
        "Travel month" => mehs_clean($data["month"] ?? "", 40),
        "People" => mehs_clean($data["adults"] ?? "", 10),
        "Trip" => mehs_clean($data["trip"] ?? "", 180),
        "Estimated total" => mehs_clean($data["total"] ?? "", 40),
        "Note" => mehs_clean($data["note"] ?? "", 2000),
    ];
    $lines = ["Booking for Magical East Africa Heritage Safaris", ""];
    foreach ($rows as $label => $value) {
        if ($value !== "") {
            $lines[] = $label . ": " . $value;
        }
    }
    $sent = wp_mail(
        MEHS_EMAIL,
        "Safari booking from " . $name,
        implode("\n", $lines),
        ["Content-Type: text/plain; charset=UTF-8", "Reply-To: " . $name . " <" . $email . ">"]
    );
    if (!$sent) {
        wp_send_json(["ok" => false, "error" => "The office email could not be sent."], 502);
    }
    wp_send_json(["ok" => true]);
}
add_action("wp_ajax_mehs_booking", "mehs_handle_booking");
add_action("wp_ajax_nopriv_mehs_booking", "mehs_handle_booking");

function mehs_purge_saved_pages() {
    if (get_option("mehs_purged") === "1.1.8") {
        return;
    }
    update_option("mehs_purged", "1.1.8", false);
    flush_rewrite_rules(false);
    do_action("litespeed_purge_all");
    if (!headers_sent()) {
        header("X-LiteSpeed-Purge: *");
    }
}
add_action("init", "mehs_purge_saved_pages", 20);

add_filter("hfe_header_enabled", "__return_false");
add_filter("hfe_footer_enabled", "__return_false");
add_filter("hfe_before_footer_enabled", "__return_false");
