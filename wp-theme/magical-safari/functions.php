<?php
/**
 * Magical Safari theme.
 */

define("MEHS_WHATSAPP", "254722326845");
define("MEHS_EMAIL", "info@magicaleastafricaheritagesafaris.com");
define("MEHS_PHONE", "+254 722 326 845");
define("MEHS_PHONE_HREF", "+254722326845");

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
    wp_enqueue_style("mehs", $dir . "/assets/css/styles.css", ["mehs-font"], "1.0.2");
    wp_enqueue_style("mehs-theme", $dir . "/assets/css/theme.css", ["mehs"], "1.0.2");
    wp_enqueue_script("mehs", $dir . "/assets/js/main.js", [], "1.0.2", true);
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
        <div class="offer-foot"><span>View</span><span class="offer-go">View details</span></div>
      </div>
    </a>
    <?php
}

function mehs_routes() {
    add_rewrite_rule('^packages/?$', "index.php?mehs_view=packages", "top");
    add_rewrite_rule('^book/?$', "index.php?mehs_view=book", "top");
    add_rewrite_rule('^gallery/?$', "index.php?mehs_view=gallery", "top");
}
add_action("init", "mehs_routes");

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
    mehs_routes();
    flush_rewrite_rules();
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

add_filter("hfe_header_enabled", "__return_false");
add_filter("hfe_footer_enabled", "__return_false");
add_filter("hfe_before_footer_enabled", "__return_false");
