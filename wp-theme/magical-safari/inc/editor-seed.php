<?php
/**
 * Puts the current public design into Elementor once, so Edit with Elementor
 * opens that design instead of the older Elementor layout.
 */

const MEHS_EDITOR_SEED = "1.2.11";

function mehs_seed_id() {
    return substr(bin2hex(random_bytes(4)), 0, 7);
}

function mehs_seed_widget($type, $settings) {
    return [
        "id" => mehs_seed_id(),
        "elType" => "widget",
        "widgetType" => $type,
        "settings" => $settings,
        "elements" => [],
    ];
}

function mehs_seed_box($elements, $settings = [], $inner = false) {
    return [
        "id" => mehs_seed_id(),
        "elType" => "container",
        "isInner" => $inner,
        "settings" => $settings,
        "elements" => $elements,
    ];
}

function mehs_seed_pad($top, $right, $bottom, $left) {
    return [
        "unit" => "px",
        "top" => (string) $top,
        "right" => (string) $right,
        "bottom" => (string) $bottom,
        "left" => (string) $left,
        "isLinked" => false,
    ];
}

function mehs_seed_url($file) {
    return mehs_asset("assets/images/" . $file);
}

function mehs_seed_heading($text, $tag = "h2", $color = "#17352c", $size = 36) {
    return mehs_seed_widget("heading", [
        "title" => $text,
        "header_size" => $tag,
        "align" => "left",
        "title_color" => $color,
        "typography_typography" => "custom",
        "typography_font_family" => "Roboto",
        "typography_font_weight" => "700",
        "typography_font_size" => ["unit" => "px", "size" => $size, "sizes" => []],
    ]);
}

function mehs_seed_text($html, $color = "#243028") {
    return mehs_seed_widget("text-editor", [
        "editor" => $html,
        "align" => "left",
        "text_color" => $color,
        "typography_typography" => "custom",
        "typography_font_family" => "Roboto",
        "typography_font_size" => ["unit" => "px", "size" => 18, "sizes" => []],
    ]);
}

function mehs_seed_image($file, $alt) {
    return mehs_seed_widget("image", [
        "image" => ["url" => mehs_seed_url($file), "id" => "", "alt" => $alt, "source" => "url"],
        "image_size" => "full",
        "align" => "center",
        "width" => ["unit" => "%", "size" => 100, "sizes" => []],
        "height" => ["unit" => "px", "size" => 190, "sizes" => []],
        "object-fit" => "cover",
    ]);
}

function mehs_seed_button($label, $url, $fill = "#b4532a") {
    return mehs_seed_widget("button", [
        "text" => $label,
        "link" => ["url" => $url, "is_external" => "", "nofollow" => "", "custom_attributes" => ""],
        "align" => "left",
        "size" => "md",
        "background_color" => $fill,
        "button_text_color" => "#ffffff",
        "border_radius" => ["unit" => "px", "top" => "28", "right" => "28", "bottom" => "28", "left" => "28", "isLinked" => true],
        "typography_typography" => "custom",
        "typography_font_family" => "Roboto",
        "typography_font_weight" => "700",
    ]);
}

function mehs_seed_section($elements, $settings = []) {
    $settings = array_merge([
        "content_width" => "boxed",
        "flex_direction" => "column",
        "flex_gap" => ["unit" => "px", "size" => 14, "column" => "14", "row" => "14"],
        "padding" => mehs_seed_pad(48, 24, 24, 24),
        "background_background" => "classic",
        "background_color" => "#ffffff",
    ], $settings);
    return mehs_seed_box($elements, $settings, false);
}

function mehs_seed_hero($file, $title, $text, $buttons = [], $height = null) {
    if ($height === null) {
        $height = ["unit" => "rem", "size" => 20, "sizes" => []];
    }
    $elements = [
        mehs_seed_heading($title, "h1", "#ffffff", 58),
        mehs_seed_text("<p>" . $text . "</p>", "#f6f1ea"),
    ];
    if ($buttons) {
        $elements[] = mehs_seed_box($buttons, [
            "content_width" => "full",
            "flex_direction" => "row",
            "flex_wrap" => "wrap",
            "flex_gap" => ["unit" => "px", "size" => 12, "column" => "12", "row" => "12"],
        ], true);
    }
    return mehs_seed_box($elements, [
        "content_width" => "boxed",
        "flex_direction" => "column",
        "flex_justify_content" => "flex-end",
        "flex_gap" => ["unit" => "px", "size" => 12, "column" => "12", "row" => "12"],
        "min_height" => $height,
        "background_background" => "classic",
        "background_image" => ["url" => mehs_seed_url($file), "id" => "", "size" => "", "alt" => "", "source" => "url"],
        "background_position" => "center center",
        "background_repeat" => "no-repeat",
        "background_size" => "cover",
        "background_overlay_background" => "classic",
        "background_overlay_color" => "#000000",
        "background_overlay_opacity" => ["unit" => "px", "size" => 0.4, "sizes" => []],
        "padding" => mehs_seed_pad(80, 28, 56, 28),
    ], false);
}

function mehs_seed_tour_card($tour) {
    $label = mehs_trip_label($tour);
    $bits = [
        mehs_seed_image($tour["image"], $tour["title"]),
        mehs_seed_heading($label, "h3", "#17352c", 22),
    ];
    if ($tour["summary"] !== "") {
        $bits[] = mehs_seed_text("<p>" . esc_html($tour["summary"]) . "</p>");
    }
    $bits[] = mehs_seed_button("View itinerary", mehs_tour_url($tour), "#738c4a");
    return mehs_seed_box($bits, [
        "content_width" => "full",
        "flex_direction" => "column",
        "flex_gap" => ["unit" => "px", "size" => 8, "column" => "8", "row" => "8"],
        "padding" => mehs_seed_pad(12, 12, 16, 12),
        "background_background" => "classic",
        "background_color" => "#f6f1ea",
        "border_radius" => ["unit" => "px", "top" => "12", "right" => "12", "bottom" => "12", "left" => "12", "isLinked" => true],
        "width" => ["unit" => "%", "size" => 31, "sizes" => []],
        "width_mobile" => ["unit" => "%", "size" => 100, "sizes" => []],
    ], true);
}

function mehs_seed_tours($tours) {
    $cards = [];
    foreach ($tours as $tour) {
        $cards[] = mehs_seed_tour_card($tour);
    }
    return mehs_seed_box($cards, [
        "content_width" => "full",
        "flex_direction" => "row",
        "flex_wrap" => "wrap",
        "flex_gap" => ["unit" => "px", "size" => 16, "column" => "16", "row" => "16"],
    ], true);
}

function mehs_seed_home() {
    $tours = array_slice(mehs_tours_in("short-kenya-safaris"), 0, 6);
    return [
        mehs_seed_hero("elephant-savanna.webp", "Discover the magic of East Africa.", "Private safaris in Kenya, Tanzania, Uganda and Rwanda, planned in Nairobi.", [
            mehs_seed_button("Explore packages", home_url("/packages/")),
            mehs_seed_button("Book a safari", home_url("/book/"), "#17352c"),
        ], ["unit" => "vh", "size" => 78, "sizes" => []]),
        mehs_seed_section([
            mehs_seed_heading("Featured tour packages"),
            mehs_seed_text("<p>Open a package for the day-by-day plan, then book it with your travel date.</p>"),
            mehs_seed_tours($tours),
            mehs_seed_button("View all packages", home_url("/packages/"), "#738c4a"),
        ]),
    ];
}

function mehs_seed_about() {
    return [
        mehs_seed_hero("acacia-sunset.webp", "About us", "Our story"),
        mehs_seed_section([
            mehs_seed_heading("About us"),
            mehs_seed_text("<p><strong>Magical East Africa Heritage Safaris</strong> is a safari company based in Nairobi. Kenya is the flagship: the Mara, Amboseli, Tsavo, Samburu, Nakuru and Ol Pejeta. We also plan Tanzania, Uganda and Rwanda when the days, and the permit budget, are real.</p>"),
            mehs_seed_text("<p>The journeys are private, or a shared vehicle when you choose a group safari. The same desk writes the itinerary, names the lodges on the quote, and stays on the phone from the first message until you are home. On the vehicle, guests write about guide James Ngigi. Nicole helps shape the plan before you book. Grace is the person guests have reached after the deposit.</p>"),
            mehs_seed_text("<p>The name on this website is the name on our email address and the name that should appear on your invoice. Some reviews shorten it to Magical Africa Safaris. That is the same desk.</p>"),
            mehs_seed_text("<p>We are listed on SafariBookings and SafariOptions. Ask us for the Tourism Regulatory Authority licence before you pay a deposit.</p>"),
            mehs_seed_image("safari-vehicle.webp", "Safari vehicle at sunset"),
        ]),
        mehs_seed_section([
            mehs_seed_heading("Our mission", "h3", "#17352c", 26),
            mehs_seed_text("<p>Personal safari plans that connect travellers with East Africa’s wildlife, parks and cultures, with the price written down before anyone pays.</p>"),
            mehs_seed_heading("Our vision", "h3", "#17352c", 26),
            mehs_seed_text("<p>To be a trusted desk for authentic safaris in Kenya, Tanzania, Uganda and Rwanda.</p>"),
            mehs_seed_heading("Our core values", "h2"),
            mehs_seed_text("<p><strong>Customer satisfaction.</strong> The route is built around your month, your group and the way you like to travel.</p><p><strong>Professionalism.</strong> One name, one phone number, and a written quote before you pay.</p><p><strong>Sustainability.</strong> Park tracks, conservancies where they fit, and community visits paid to that community.</p><p><strong>Safety.</strong> A licensed operator, a known vehicle, and a meeting point confirmed with your flight.</p><p><strong>Authenticity.</strong> The parks, the wildlife and the people on the route, not a staged version of them.</p>"),
            mehs_seed_button("View packages", home_url("/packages/")),
            mehs_seed_button("Get in touch", mehs_page_url("contact") ?: home_url("/contact/"), "#17352c"),
        ], ["background_color" => "#f6f1ea"]),
    ];
}

function mehs_seed_listing($file, $kicker, $title, $lead, $groups) {
    $elements = [mehs_seed_hero($file, $title, $kicker . " " . $lead)];
    $blocks = [mehs_seed_text("<p>" . $lead . "</p>")];
    foreach ($groups as $heading => $tours) {
        if (!$tours) {
            continue;
        }
        $blocks[] = mehs_seed_heading($heading);
        $blocks[] = mehs_seed_tours($tours);
    }
    $elements[] = mehs_seed_section($blocks);
    return $elements;
}

function mehs_seed_group() {
    return mehs_seed_listing(
        "zebra.webp",
        "Shared departures.",
        "Group safaris",
        "These are shared-vehicle departures for solo travellers, couples and anyone who wants the Mara without paying for a private car. The migration departures are planned for July to October. A date is only a departure once the vehicle has the guests.",
        ["Shared departures" => mehs_tours_in("group-safaris")]
    );
}

function mehs_seed_packages() {
    $groups = [
        "Kenya" => mehs_tours_in("short-kenya-safaris"),
        "Tanzania" => mehs_tours_in("tanzania-safaris"),
        "Uganda" => mehs_tours_in("uganda-safaris"),
        "Rwanda" => mehs_tours_in("rwanda-safaris"),
        "International" => mehs_tours_in("international-destinations"),
    ];
    return mehs_seed_listing(
        "elephant-savanna.webp",
        "Safaris.",
        "Packages",
        "Open a package for the day-by-day plan, then book it with your travel date. Shared departures are on the group page, and single days are with the excursions.",
        $groups
    );
}

function mehs_seed_excursions() {
    return mehs_seed_listing(
        "safari-vehicle.webp",
        "Day trips.",
        "Excursions and day trips",
        "These sit beside a longer safari or a beach stay. A day in Amboseli, Ol Pejeta or Tsavo from Nairobi is mostly driving, and the notes on those pages say so.",
        ["Day trips" => mehs_tours_in("excursions")]
    );
}

function mehs_seed_contact() {
    return [
        mehs_seed_hero("safari-vehicle.webp", "Contact us", "Get in touch"),
        mehs_seed_section([
            mehs_seed_heading("We would like to hear from you"),
            mehs_seed_text("<p>Whether you are ready to book or you want a question answered, write or call. The same desk replies by email or WhatsApp.</p>"),
            mehs_seed_text("<p><strong>Address.</strong> Nairobi, Kenya. We meet you at the airport. The point is confirmed with your flight number.</p><p><strong>Email.</strong> " . esc_html(MEHS_EMAIL) . "</p><p><strong>Phone.</strong> " . esc_html(MEHS_PHONE) . "</p><p><strong>WhatsApp.</strong> +" . esc_html(MEHS_WHATSAPP) . "</p>"),
            mehs_seed_button("Chat on WhatsApp", "https://wa.me/" . MEHS_WHATSAPP, "#738c4a"),
            mehs_seed_button("Book a safari", home_url("/book/")),
        ]),
    ];
}

function mehs_seed_faqs() {
    $blocks = [
        mehs_seed_text("<p>If your question is not here, write or call. The same desk replies.</p>"),
        mehs_seed_button("Contact us", mehs_page_url("contact") ?: home_url("/contact/")),
    ];
    $groups = [
        "When to go" => [
            ["What is the best time to visit Kenya?", "June to October is the dry season and the usual window for the Mara River crossings. January to March is hot, drier, and quieter. April and May are the long rains: some camps close, the parks are green, and prices drop."],
            ["Is the Mara River crossing guaranteed?", "No. Crossings usually fall between July and October and they happen when the herds decide. A particular morning cannot be promised."],
        ],
        "Your trip" => [
            ["Are the safaris private?", "Yes. The itineraries on this site are private unless a page says otherwise. You get your own vehicle and guide. Group departures are arranged when several parties want the same month."],
            ["Which safari is best for a first visit?", "For Kenya, Amboseli plus the Mara, with Nakuru if you have five or more days. For Tanzania, the Serengeti and Ngorongoro. Add gorillas only if you have the extra days and the permit budget."],
            ["Can I add a beach?", "Yes. Diani, Watamu and Mombasa are the Kenya coast. Zanzibar is the usual add-on after a Tanzania safari. Plan three or four nights."],
        ],
        "Before you pay" => [
            ["Do I need a visa?", "Most visitors to Kenya apply for an electronic travel authorisation before flying, on the official government site. Tanzania, Uganda and Rwanda have their own rules. We send the current official link before you pay a deposit."],
            ["How much does a safari cost?", "A private mid-range Kenya safari is often in the range shown on each trip, per person sharing. The quote we email is the price."],
            ["How do I pay, and what if I cancel?", "A 30 percent deposit confirms the trip. The balance is due 60 days before arrival. Cancellation terms, and the stricter rules on gorilla permits, are repeated on your invoice."],
        ],
    ];
    foreach ($groups as $title => $items) {
        $blocks[] = mehs_seed_heading($title);
        foreach ($items as $item) {
            $blocks[] = mehs_seed_heading($item[0], "h3", "#17352c", 22);
            $blocks[] = mehs_seed_text("<p>" . esc_html($item[1]) . "</p>");
        }
    }
    return [
        mehs_seed_hero("acacia-sunset.webp", "FAQs", "Before you book"),
        mehs_seed_section($blocks),
    ];
}

function mehs_seed_country($key, $title, $file) {
    $guides = mehs_country_guides();
    if (!isset($guides[$key])) {
        return [];
    }
    $blocks = [mehs_seed_text("<p>" . esc_html($guides[$key][0]) . "</p>")];
    foreach ($guides[$key][1] as $heading => $items) {
        $blocks[] = mehs_seed_heading($heading);
        foreach ($items as $item) {
            if (!empty($item[2])) {
                $blocks[] = mehs_seed_image($item[2], $item[0]);
            }
            $blocks[] = mehs_seed_heading($item[0], "h3", "#17352c", 22);
            $blocks[] = mehs_seed_text("<p>" . esc_html($item[1]) . "</p>");
        }
    }
    $blocks[] = mehs_seed_button("Browse safari packages", home_url("/packages/"), "#738c4a");
    $blocks[] = mehs_seed_button("Enquire now", mehs_page_url("contact") ?: home_url("/contact/"));
    return [
        mehs_seed_hero($file, $title, "Destinations"),
        mehs_seed_section($blocks),
    ];
}

function mehs_seed_destinations() {
    $cards = [];
    $places = [
        ["short-kenya-safaris", "Kenya", "The Mara, Amboseli, Tsavo, Samburu and the coast.", "zebra.webp"],
        ["tanzania-safaris", "Tanzania", "The Serengeti, Ngorongoro and Zanzibar.", "serengeti.webp"],
        ["uganda-safaris", "Uganda", "Gorilla trekking, with Queen Elizabeth and Kibale.", "gorilla.webp"],
        ["rwanda-safaris", "Rwanda", "Volcanoes, Nyungwe and Akagera.", "rwanda.webp"],
    ];
    foreach ($places as $place) {
        $url = mehs_page_url($place[0]);
        if (!$url) {
            continue;
        }
        $cards[] = mehs_seed_box([
            mehs_seed_image($place[3], $place[1]),
            mehs_seed_heading($place[1], "h3", "#17352c", 24),
            mehs_seed_text("<p>" . esc_html($place[2]) . "</p>"),
            mehs_seed_button("Open", $url, "#738c4a"),
        ], [
            "content_width" => "full",
            "flex_direction" => "column",
            "background_background" => "classic",
            "background_color" => "#f6f1ea",
            "padding" => mehs_seed_pad(12, 12, 16, 12),
            "width" => ["unit" => "%", "size" => 31, "sizes" => []],
            "width_mobile" => ["unit" => "%", "size" => 100, "sizes" => []],
        ], true);
    }
    return [
        mehs_seed_hero("elephant-savanna.webp", "Destinations", "Where we travel"),
        mehs_seed_section([
            mehs_seed_text("<p>Kenya, Tanzania, Uganda and Rwanda, planned from Nairobi. Open a country for what to do there, then choose a package when the route is clear.</p>"),
            mehs_seed_box($cards, [
                "content_width" => "full",
                "flex_direction" => "row",
                "flex_wrap" => "wrap",
                "flex_gap" => ["unit" => "px", "size" => 16, "column" => "16", "row" => "16"],
            ], true),
        ]),
    ];
}

function mehs_seed_blog() {
    $blocks = [mehs_seed_text("<p>Stories from the desk in Nairobi.</p>")];
    $posts = get_posts(["numberposts" => 12, "post_status" => "publish"]);
    if (!$posts) {
        $blocks[] = mehs_seed_text("<p>New stories will appear here.</p>");
    }
    foreach ($posts as $post) {
        $blocks[] = mehs_seed_heading(get_the_title($post), "h3", "#17352c", 24);
        $blocks[] = mehs_seed_text("<p>" . esc_html(wp_trim_words(wp_strip_all_tags(get_the_excerpt($post)), 32)) . "</p>");
        $blocks[] = mehs_seed_button("Read", get_permalink($post), "#738c4a");
    }
    return [
        mehs_seed_hero("safari-vehicle.webp", "Blog", "Journal"),
        mehs_seed_section($blocks),
    ];
}

function mehs_seed_gallery() {
    $files = ["amboseli.webp", "zebra.webp", "migration.webp", "lions.webp", "serengeti.webp", "gorilla.webp"];
    $images = [];
    foreach ($files as $file) {
        $images[] = mehs_seed_image($file, "Safari photograph");
    }
    return [
        mehs_seed_hero("migration.webp", "Gallery", "Photographs"),
        mehs_seed_section(array_merge([
            mehs_seed_text("<p>Kenya, Tanzania, Uganda and Rwanda. Photographs added in the media library also appear on the public gallery.</p>"),
        ], $images)),
    ];
}

function mehs_seed_book() {
    return [
        mehs_seed_hero("safari-vehicle.webp", "Book your safari", "Reservation"),
        mehs_seed_section([
            mehs_seed_text("<p>Reservation form. We confirm availability and send the price in writing. No payment is taken on this form.</p>"),
            mehs_seed_text("<p>Ask for the full name, email, phone, WhatsApp number, travel date, number of people, and the package.</p>"),
            mehs_seed_button("Open the booking form", home_url("/book/")),
        ]),
    ];
}

function mehs_seed_live_box($html) {
    return mehs_seed_box([
        mehs_seed_widget("html", ["html" => $html]),
    ], [
        "content_width" => "full",
        "flex_direction" => "column",
        "padding" => mehs_seed_pad(0, 0, 0, 0),
        "css_classes" => "mehs-live-canvas",
    ], false);
}

function mehs_seed_drop_autosaves($post_id) {
    unset($post_id);
}

function mehs_seed_page($post_id, $elements) {
    if (!$post_id || !$elements) {
        return;
    }
    if (get_post_meta($post_id, "mehs_editor_seed", true) === MEHS_EDITOR_SEED) {
        return;
    }
    $already = !empty($GLOBALS["mehs_seeding"]);
    $GLOBALS["mehs_seeding"] = true;
    update_post_meta($post_id, "_elementor_edit_mode", "builder");
    update_post_meta($post_id, "_elementor_template_type", "wp-page");
    update_post_meta($post_id, "_elementor_version", "3.24.0");
    update_post_meta($post_id, "_elementor_data", wp_slash(wp_json_encode($elements)));
    delete_post_meta($post_id, "_elementor_css");
    delete_post_meta($post_id, "_elementor_element_cache");
    update_post_meta($post_id, "mehs_editor_seed", MEHS_EDITOR_SEED);
    mehs_seed_drop_autosaves($post_id);
    if (!$already) {
        $GLOBALS["mehs_seeding"] = false;
    }
}

function mehs_seed_by_slug($slug, $elements) {
    foreach ([$slug, "destinations/" . $slug] as $path) {
        $page = get_page_by_path($path);
        if ($page instanceof WP_Post) {
            mehs_seed_page($page->ID, $elements);
            return;
        }
    }
}

function mehs_seed_tour_page($tour) {
    $blocks = [
        mehs_seed_image($tour["image"], $tour["title"]),
        mehs_seed_heading($tour["title"], "h1", "#ffffff", 48),
        mehs_seed_heading("Package overview", "h2"),
    ];
    if (($tour["summary"] ?? "") !== "") {
        $blocks[] = mehs_seed_text("<p>" . esc_html($tour["summary"]) . "</p>");
    }
    $days = isset($tour["days"]) && is_array($tour["days"]) ? $tour["days"] : [];
    foreach ($days as $day) {
        $label = trim((string) ($day["title"] ?? ""));
        if ($label === "") {
            continue;
        }
        $blocks[] = mehs_seed_heading($label, "h3", "#17352c", 22);
        $text = trim((string) ($day["text"] ?? ""));
        if ($text !== "") {
            $blocks[] = mehs_seed_text("<p>" . esc_html($text) . "</p>");
        }
    }
    return [mehs_seed_section($blocks)];
}

function mehs_seed_elements_for($post_id) {
    $post = get_post($post_id);
    if (!$post instanceof WP_Post) {
        return null;
    }
    $slug = $post->post_name;
    $front = (int) get_option("page_on_front");
    if ((int) $post->ID === $front || $slug === "east-africa-safaris") {
        return mehs_seed_home();
    }
    $pages = [
        "about-us" => "mehs_seed_about",
        "group-safaris" => "mehs_seed_group",
        "packages" => "mehs_seed_packages",
        "contact" => "mehs_seed_contact",
        "faqs" => "mehs_seed_faqs",
        "blog" => "mehs_seed_blog",
        "gallery" => "mehs_seed_gallery",
        "book" => "mehs_seed_book",
        "excursions" => "mehs_seed_excursions",
        "destinations" => "mehs_seed_destinations",
    ];
    if (isset($pages[$slug]) && function_exists($pages[$slug])) {
        return $pages[$slug]();
    }
    $countries = [
        "short-kenya-safaris" => ["kenya", "Kenya", "zebra.webp"],
        "tanzania-safaris" => ["tanzania", "Tanzania", "serengeti.webp"],
        "uganda-safaris" => ["uganda", "Uganda", "gorilla.webp"],
        "rwanda-safaris" => ["rwanda", "Rwanda", "rwanda.webp"],
    ];
    if (isset($countries[$slug])) {
        return mehs_seed_country($countries[$slug][0], $countries[$slug][1], $countries[$slug][2]);
    }
    $tour = mehs_find_tour($slug);
    if ($tour) {
        return mehs_seed_tour_page($tour);
    }
    return null;
}

function mehs_seed_editor_pages() {
    if (get_option("mehs_editor_seeded") === MEHS_EDITOR_SEED) {
        return;
    }
    if (!is_admin() && empty($_GET["elementor-preview"])) {
        return;
    }
    $GLOBALS["mehs_seeding"] = true;
    $priority = 0;
    if (!empty($_GET["post"])) {
        $priority = (int) $_GET["post"];
    } elseif (!empty($_GET["elementor-preview"])) {
        $priority = (int) $_GET["elementor-preview"];
    }
    $ids = [];
    if ($priority > 0) {
        $ids[] = $priority;
    }
    foreach (get_pages(["post_status" => "publish", "number" => 0, "hierarchical" => 0]) as $page) {
        if ((int) $page->ID !== $priority) {
            $ids[] = (int) $page->ID;
        }
    }
    $wrote = 0;
    $pending = false;
    foreach ($ids as $id) {
        if (get_post_meta($id, "mehs_editor_seed", true) === MEHS_EDITOR_SEED) {
            continue;
        }
        try {
            $elements = mehs_seed_elements_for($id);
        } catch (Throwable $e) {
            $pending = true;
            continue;
        }
        if (!$elements) {
            update_post_meta($id, "mehs_editor_seed", MEHS_EDITOR_SEED);
            continue;
        }
        mehs_seed_page($id, $elements);
        $wrote++;
        if ($wrote >= 8) {
            $pending = true;
            break;
        }
    }
    if (class_exists("\\Elementor\\Plugin")) {
        $plugin = \Elementor\Plugin::$instance;
        if (!empty($plugin->files_manager) && method_exists($plugin->files_manager, "clear_cache")) {
            $plugin->files_manager->clear_cache();
        }
    }
    $GLOBALS["mehs_seeding"] = false;
    if (!$pending) {
        update_option("mehs_editor_seeded", MEHS_EDITOR_SEED, false);
    }
}
add_action("init", "mehs_seed_editor_pages", 30);
