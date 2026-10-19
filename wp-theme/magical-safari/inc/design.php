<?php
/**
 * Designed public pages. These match the local preview.
 */

function mehs_banner($file, $kicker, $title) {
    echo '<section class="banner page-banner">';
    echo mehs_image($file, $title, "banner-media");
    echo '<div class="banner-copy wrap"><p class="kicker">' . esc_html($kicker) . '</p><h1>' . esc_html($title) . '</h1>';
    echo '<p class="crumbs"><a href="' . esc_url(home_url("/")) . '">Home</a> · ' . esc_html($title) . '</p></div></section>';
}

function mehs_tours() {
    static $tours = null;
    if ($tours !== null) {
        return $tours;
    }
    $file = get_template_directory() . "/data/tours.json";
    $raw = is_readable($file) ? json_decode(file_get_contents($file), true) : [];
    $tours = [];
    foreach (is_array($raw) ? $raw : [] as $tour) {
        if (empty($tour["slug"]) || empty($tour["section"])) {
            continue;
        }
        $days = isset($tour["days"]) && is_array($tour["days"]) ? $tour["days"] : [];
        $count = count($days);
        if ($count < 1 && preg_match("/(\d+)-days?/", $tour["slug"], $match)) {
            $count = (int) $match[1];
        }
        $summary = "";
        foreach ($tour["intro"] ?? [] as $line) {
            $line = trim(preg_replace("/\s+/", " ", (string) $line));
            if (strlen($line) > 45 && strpos($line, "@") === false) {
                $summary = $line;
                break;
            }
        }
        $section = $tour["section"];
        $country = $section === "tanzania-safaris" ? "Tanzania" : ($section === "uganda-safaris" ? "Uganda" : ($section === "rwanda-safaris" ? "Rwanda" : "Kenya"));
        $tours[] = [
            "slug" => $tour["slug"],
            "section" => $section,
            "country" => $country,
            "title" => ucwords(str_replace("-", " ", $tour["slug"])),
            "days" => $days,
            "dayCount" => max(1, $count),
            "summary" => $summary,
            "highlights" => array_slice($tour["highlights"] ?? [], 0, 8),
            "includes" => $tour["includes"] ?? [],
            "excludes" => $tour["excludes"] ?? [],
            "image" => mehs_image_for($tour["slug"] . " " . $summary),
        ];
    }
    return $tours;
}

function mehs_image_for($text) {
    $map = [
        "gorilla" => "gorilla.webp",
        "bwindi" => "gorilla.webp",
        "volcano" => "rwanda.webp",
        "rwanda" => "rwanda.webp",
        "zanzibar" => "zanzibar.webp",
        "ngorongoro" => "ngorongoro.webp",
        "serengeti" => "serengeti.webp",
        "nakuru" => "flamingo.webp",
        "flamingo" => "flamingo.webp",
        "samburu" => "samburu.webp",
        "amboseli" => "amboseli.webp",
        "kilimanjaro" => "kilimanjaro.webp",
        "tsavo" => "tsavo.webp",
        "rhino" => "rhino.webp",
        "pejeta" => "rhino.webp",
        "queen" => "queen.webp",
        "lion" => "lions.webp",
        "migration" => "migration.webp",
        "mara" => "zebra.webp",
        "naivasha" => "acacia-sunset.webp",
    ];
    $hay = strtolower($text);
    foreach ($map as $needle => $file) {
        if (strpos($hay, $needle) !== false) {
            return $file;
        }
    }
    return "elephant-savanna.webp";
}

function mehs_find_tour($slug) {
    foreach (mehs_tours() as $tour) {
        if ($tour["slug"] === $slug) {
            return $tour;
        }
    }
    return null;
}

function mehs_find_tour_by_title($title) {
    $wanted = sanitize_title($title);
    if ($wanted === "") {
        return null;
    }
    foreach (mehs_tours() as $tour) {
        if (sanitize_title($tour["title"]) === $wanted || $tour["slug"] === $wanted) {
            return $tour;
        }
    }
    return null;
}

function mehs_book_url($title) {
    return add_query_arg([
        "book" => "1",
        "trip" => $title,
    ], home_url("/book/"));
}

function mehs_tour_from_page($page, $section) {
    $count = 1;
    if (preg_match("/(\d+)-days?/", $page->post_name, $match)) {
        $count = (int) $match[1];
    }
    $countries = [
        "tanzania-safaris" => "Tanzania",
        "uganda-safaris" => "Uganda",
        "rwanda-safaris" => "Rwanda",
        "international-destinations" => "International",
    ];
    $country = $countries[$section] ?? "Kenya";
    $summary = trim(wp_strip_all_tags(get_the_excerpt($page)));
    return [
        "slug" => $page->post_name,
        "section" => $section,
        "country" => $country,
        "title" => get_the_title($page),
        "days" => [],
        "dayCount" => max(1, $count),
        "summary" => $summary,
        "highlights" => [],
        "includes" => [],
        "excludes" => [],
        "image" => mehs_image_for($page->post_name . " " . $summary),
    ];
}

function mehs_tours_in($section) {
    $tours = array_values(array_filter(mehs_tours(), function ($tour) use ($section) {
        return $tour["section"] === $section;
    }));
    $parent = mehs_section_page($section);
    if (!$parent instanceof WP_Post) {
        return $tours;
    }
    $seen = [];
    foreach ($tours as $tour) {
        $seen[$tour["slug"]] = true;
    }
    $pages = get_pages([
        "parent" => $parent->ID,
        "post_status" => "publish",
        "sort_column" => "menu_order,post_title",
    ]);
    foreach ($pages as $page) {
        if (isset($seen[$page->post_name])) {
            continue;
        }
        $tours[] = mehs_tour_from_page($page, $section);
    }
    return $tours;
}

function mehs_section_page($section) {
    foreach ([$section, "destinations/" . $section] as $path) {
        $page = get_page_by_path($path);
        if ($page instanceof WP_Post && $page->post_status === "publish") {
            return $page;
        }
    }
    return null;
}

function mehs_tour_url($tour) {
    $paths = [];
    if (!empty($tour["section"])) {
        $paths[] = $tour["section"] . "/" . $tour["slug"];
        $paths[] = "destinations/" . $tour["section"] . "/" . $tour["slug"];
    }
    $paths[] = $tour["slug"];
    foreach ($paths as $path) {
        $page = get_page_by_path($path);
        if ($page instanceof WP_Post && $page->post_status === "publish") {
            return get_permalink($page);
        }
    }
    return add_query_arg("itinerary", sanitize_title($tour["slug"]), home_url("/packages/"));
}

function mehs_offer_card($tour) {
    $nights = $tour["dayCount"] > 1 ? " / " . ($tour["dayCount"] - 1) . " nights" : "";
    $name = preg_replace("/^\d+\s+days?\s+/i", "", $tour["title"]);
    echo '<a class="offer" href="' . esc_url(mehs_tour_url($tour)) . '">';
    echo '<div class="offer-photo">' . mehs_image($tour["image"], $tour["title"]) . '<span class="offer-tag">' . esc_html($tour["country"]) . '</span></div>';
    echo '<div class="offer-body"><h3>' . esc_html($tour["dayCount"] . " " . ($tour["dayCount"] === 1 ? "day" : "days") . " | " . $name) . "</h3>";
    if ($tour["summary"] !== "") {
        echo "<p>" . esc_html($tour["summary"]) . "</p>";
    }
    echo '<div class="offer-foot"><span>' . esc_html($tour["dayCount"] . " " . ($tour["dayCount"] === 1 ? "day" : "days") . $nights) . '</span><span class="offer-go">View itinerary</span></div></div></a>';
}

function mehs_offer_grid($tours) {
    echo '<div class="offers">';
    foreach ($tours as $tour) {
        mehs_offer_card($tour);
    }
    echo "</div>";
}

function mehs_plain($text) {
    $text = html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, "UTF-8");
    return trim(preg_replace("/\s+/", " ", $text));
}

function mehs_parse_itinerary($html) {
    $parsed = ["overview" => "", "note" => "", "table" => [], "days" => [], "highlights" => []];
    if (!is_string($html) || trim($html) === "") {
        return $parsed;
    }
    $doc = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    $xpath = new DOMXPath($doc);
    foreach ($xpath->query("//p") as $paragraph) {
        $text = mehs_plain($paragraph->textContent);
        if ($text === "") {
            continue;
        }
        if (stripos($text, "Safari Price") !== false || (stripos($text, "per person") !== false && stripos($text, "USD") !== false)) {
            if ($parsed["note"] === "") {
                $parsed["note"] = $text;
            }
            continue;
        }
        if ($parsed["overview"] === "" && strlen($text) > 80) {
            $parsed["overview"] = $text;
        }
    }
    $tables = $doc->getElementsByTagName("table");
    if ($tables->length) {
        foreach ($tables->item(0)->getElementsByTagName("tr") as $row) {
            $cells = [];
            foreach ($row->childNodes as $cell) {
                if ($cell->nodeType !== XML_ELEMENT_NODE) {
                    continue;
                }
                $name = strtolower($cell->nodeName);
                if ($name !== "td" && $name !== "th") {
                    continue;
                }
                $cells[] = mehs_plain($cell->textContent);
            }
            if ($cells) {
                $parsed["table"][] = $cells;
            }
        }
    }
    foreach ($xpath->query("//details[contains(@class,'e-n-accordion-item')]") as $details) {
        $title_node = $xpath->query(".//*[contains(@class,'e-n-accordion-item-title-text')]", $details)->item(0);
        $title = $title_node ? mehs_plain($title_node->textContent) : "";
        $lines = [];
        foreach ($xpath->query(".//p", $details) as $paragraph) {
            $line = mehs_plain($paragraph->textContent);
            if ($line !== "") {
                $lines[] = $line;
            }
        }
        if ($title !== "") {
            $parsed["days"][] = ["title" => $title, "text" => implode("\n", $lines)];
        }
    }
    foreach ($xpath->query("//li") as $item) {
        $text = mehs_plain($item->textContent);
        if (strlen($text) > 24 && strlen($text) < 240) {
            $parsed["highlights"][] = $text;
        }
        if (count($parsed["highlights"]) >= 6) {
            break;
        }
    }
    return $parsed;
}

function mehs_price_cell($text) {
    $text = trim((string) $text);
    if ($text === "" || $text === "$" || strcasecmp($text, "usd") === 0) {
        return "On request";
    }
    return $text;
}

function mehs_lowest_price($table) {
    $amounts = [];
    foreach ($table as $index => $row) {
        if ($index === 0) {
            continue;
        }
        foreach ($row as $cell_index => $cell) {
            if ($cell_index === 0 || preg_match("/discount|free|%|child/i", (string) $cell)) {
                continue;
            }
            if (!preg_match_all("/\d[\d,]*/", (string) $cell, $matches)) {
                continue;
            }
            foreach ($matches[0] as $number) {
                $value = (int) str_replace(",", "", $number);
                if ($value >= 100) {
                    $amounts[] = $value;
                }
            }
        }
    }
    return $amounts ? min($amounts) : 0;
}

function mehs_saved_itinerary() {
    $post = get_post();
    if (!$post instanceof WP_Post) {
        return null;
    }
    $parsed = mehs_parse_itinerary(apply_filters("the_content", $post->post_content));
    if (!$parsed["days"] && count($parsed["table"]) < 2) {
        return null;
    }
    return $parsed;
}

function mehs_try_saved_itinerary() {
    $parsed = mehs_saved_itinerary();
    if (!$parsed) {
        return false;
    }
    $post = get_post();
    $parent = $post->post_parent ? get_post($post->post_parent) : null;
    $section = $parent instanceof WP_Post ? $parent->post_name : "";
    $tour = mehs_find_tour($post->post_name) ?: mehs_tour_from_page($post, $section);
    mehs_render_package($tour, $parsed);
    return true;
}

function mehs_render_package($tour, $parsed = null) {
    if ($parsed === null) {
        $parsed = mehs_saved_itinerary() ?: ["overview" => "", "note" => "", "table" => [], "days" => [], "highlights" => []];
    }
    $summary = $parsed["overview"] !== "" ? $parsed["overview"] : $tour["summary"];
    $highlights = $parsed["highlights"] ?: $tour["highlights"];
    $days = $parsed["days"] ?: $tour["days"];
    $from = mehs_lowest_price($parsed["table"]);
    mehs_banner($tour["image"], $tour["country"], $tour["title"]);
    echo '<div class="wrap package"><div class="package-main"><h2 class="ruled">Package overview</h2>';
    if ($summary !== "") {
        echo "<p>" . esc_html($summary) . "</p>";
    }
    if ($highlights) {
        echo "<ul>";
        foreach ($highlights as $item) {
            echo "<li>" . esc_html($item) . "</li>";
        }
        echo "</ul>";
    }
    echo '</div><aside class="price-card book-card"><div class="book-card-head"><p class="kicker">Package price from</p><p class="big">';
    echo $from > 0 ? esc_html("$" . number_format($from)) : "On request";
    echo '</p><p>Per person</p></div><div class="book-card-body">';
    if ($parsed["note"] !== "") {
        echo '<p class="rate-note">' . esc_html($parsed["note"]) . "</p>";
    }
    if (count($parsed["table"]) > 1) {
        echo '<table class="rate-table">';
        foreach ($parsed["table"] as $index => $row) {
            echo "<tr>";
            foreach ($row as $cell) {
                $tag = $index === 0 ? "th" : "td";
                $value = $index === 0 ? $cell : mehs_price_cell($cell);
                echo "<" . $tag . ">" . esc_html($value) . "</" . $tag . ">";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>The written quote confirms the price for your dates and group size.</p>";
    }
    echo '<a class="btn btn-clay" href="' . esc_url(mehs_book_url($tour["title"])) . '">Book now</a></div></aside>';
    echo '<div class="package-rest"><h2 class="ruled">Day-by-day itinerary</h2>';
    foreach ($days as $index => $day) {
        $open = $index === 0 ? " open" : "";
        $num = str_pad((string) ($index + 1), 2, "0", STR_PAD_LEFT);
        $label = trim((string) ($day["title"] ?? ""));
        $label = preg_replace("/^day\s*\d+\s*[:|\-–—]\s*/i", "", $label);
        echo '<details class="day-acc"' . $open . '><summary><span class="day-num">' . esc_html($num) . "</span><span>Day " . esc_html((string) ($index + 1));
        if ($label !== "") {
            echo " | " . esc_html($label);
        }
        echo "</span></summary>";
        $lines = preg_split("/\n+/", trim((string) ($day["text"] ?? "")));
        foreach ($lines as $line) {
            if (trim($line) !== "") {
                echo "<p>" . esc_html($line) . "</p>";
            }
        }
        echo "</details>";
    }
    if ($tour["includes"] || $tour["excludes"]) {
        echo '<div class="ie">';
        if ($tour["includes"]) {
            echo '<div><h2 class="ruled">What\'s included</h2><ul class="yes">';
            foreach ($tour["includes"] as $item) {
                echo "<li>" . esc_html($item) . "</li>";
            }
            echo "</ul></div>";
        }
        if ($tour["excludes"]) {
            echo '<div><h2 class="ruled">What\'s excluded</h2><ul class="no">';
            foreach ($tour["excludes"] as $item) {
                echo "<li>" . esc_html($item) . "</li>";
            }
            echo "</ul></div>";
        }
        echo "</div>";
    }
    echo "</div></div>";
}

function mehs_page_contact() {
    mehs_banner("safari-vehicle.webp", "Get in touch", "Contact us");
    $countries = ["Kenya", "Tanzania", "Uganda", "Rwanda", "United Kingdom", "United States", "Canada", "Germany", "France", "Spain", "Italy", "Netherlands", "Australia", "India", "South Africa", "United Arab Emirates", "Other"];
    echo '<section><div class="wrap contact-grid"><div><p class="kicker">Reach out</p><h2>We would like to hear from you</h2>';
    echo "<p>Whether you are ready to book or you want a question answered, write or call. The same desk replies by email or WhatsApp.</p><ul class=\"reach\">";
    echo '<li><span aria-hidden="true">⌖</span><div><small>Address</small><strong>Nairobi, Kenya</strong><p class="fine">We meet you at the airport. The point is confirmed with your flight number.</p></div></li>';
    echo '<li><span aria-hidden="true">✉</span><div><small>Email</small><a href="mailto:' . esc_attr(MEHS_EMAIL) . '">' . esc_html(MEHS_EMAIL) . "</a></div></li>";
    echo '<li><span aria-hidden="true">☎</span><div><small>Phone</small><a href="tel:' . esc_attr(MEHS_PHONE_HREF) . '">' . esc_html(MEHS_PHONE) . "</a></div></li>";
    echo '<li><span aria-hidden="true">✆</span><div><small>WhatsApp</small><a href="https://wa.me/' . esc_attr(MEHS_WHATSAPP) . '" target="_blank" rel="noopener">Chat on WhatsApp</a></div></li></ul>';
    echo '<p><a href="https://www.google.com/maps/search/?api=1&query=Nairobi%2C%20Kenya" target="_blank" rel="noopener">Open Nairobi in Maps</a></p></div>';
    echo '<form class="contact-card message-form" data-enquiry data-whatsapp-number="' . esc_attr(MEHS_WHATSAPP) . '" data-email-address="' . esc_attr(MEHS_EMAIL) . '">';
    echo '<h2>Send a message</h2><p class="fine">We reply by email or WhatsApp. Nothing is charged on this form. To book a package, use <a href="' . esc_url(home_url("/book/")) . '">Book now</a>.</p><div class="form-grid">';
    echo '<div class="form-grid form-2"><label>Full name<input name="name" autocomplete="name" required></label><label>Phone<input name="phone" autocomplete="tel" required></label></div>';
    echo '<div class="form-grid form-2"><label>Email address<input name="email" type="email" autocomplete="email" required></label><label>Country<select name="country"><option value="">Your country</option>';
    foreach ($countries as $country) {
        echo "<option>" . esc_html($country) . "</option>";
    }
    echo '</select></label></div><label>Message<textarea name="note" rows="5" placeholder="Tell us about your travel plans" required></textarea></label>';
    echo '<input type="hidden" name="trip" value="Contact message"><p class="fine">WhatsApp opens a chat for you to send. Email goes straight to the office.</p>';
    echo '<div class="hp"><label>Leave this blank<input name="mehs_leave_blank" tabindex="-1" autocomplete="off"></label></div><div class="send-row">';
    echo '<button class="btn btn-green" type="submit" name="channel" value="whatsapp">Send on WhatsApp</button>';
    echo '<button class="btn btn-line" type="submit" name="channel" value="email">Send by email</button></div>';
    echo '<div class="result" data-result><p data-status></p></div></div></form></div></section>';
}

function mehs_page_faqs() {
    $groups = [
        "When to go" => [
            ["What is the best time to visit Kenya?", "June to October is the dry season and the usual window for the Mara River crossings. January to March is hot, drier, and quieter, with clear Kilimanjaro mornings. April and May are the long rains: some camps close, the parks are green, and prices drop."],
            ["Is the Mara River crossing guaranteed?", "No. Crossings usually fall between July and October and they happen when the herds decide. We plan those months if a crossing is the reason for the trip, and we say clearly that a particular morning cannot be promised."],
        ],
        "Your trip" => [
            ["Are the safaris private?", "Yes. The itineraries on this site are private unless a page says otherwise. You get your own vehicle and guide. Group departures are arranged when several parties want the same month."],
            ["Which safari is best for a first visit?", "For Kenya, Amboseli plus the Mara, with Nakuru if you have five or more days. For Tanzania, the Serengeti and Ngorongoro. Add gorillas only if you have the extra days and the permit budget."],
            ["Can I add a beach?", "Yes. Diani, Watamu and Mombasa are the Kenya coast. Zanzibar is the usual add-on after a Tanzania safari. Plan three or four nights. One night is an airport exercise."],
        ],
        "Before you pay" => [
            ["Do I need a visa?", "Most visitors to Kenya apply for an electronic travel authorisation before flying, on the official government site. Tanzania, Uganda and Rwanda have their own rules. We send the current official link for your nationality before you pay a deposit."],
            ["How much does a safari cost?", "A private mid-range Kenya safari is often in the range shown on each trip, per person sharing. Two people in a private vehicle cost more per person than four. The quote we email is the price."],
            ["How do I pay, and what if I cancel?", "A 30 percent deposit confirms the trip. The balance is due 60 days before arrival. Cancellation terms, and the stricter rules on gorilla permits, are repeated on your invoice."],
        ],
    ];
    mehs_banner("acacia-sunset.webp", "Before you book", "FAQs");
    echo '<div class="wrap page-block faq-layout"><aside class="faq-side"><h2>Ask us the rest</h2><p>If your question is not here, write or call. The same desk replies.</p>';
    echo '<p><a class="btn btn-clay" href="' . esc_url(mehs_page_url("contact") ?: home_url("/contact/")) . '">Contact us</a> <a class="btn btn-line" href="' . esc_url(home_url("/book/")) . '">Book a safari</a></p></aside><div class="faq">';
    $first = true;
    foreach ($groups as $title => $items) {
        echo '<section class="faq-group"><h2>' . esc_html($title) . "</h2>";
        foreach ($items as $item) {
            echo "<details" . ($first ? " open" : "") . "><summary>" . esc_html($item[0]) . "</summary><p>" . esc_html($item[1]) . "</p></details>";
            $first = false;
        }
        echo "</section>";
    }
    echo "</div></div>";
}

function mehs_page_group() {
    mehs_banner("zebra.webp", "Shared departures", "Group safaris");
    echo '<div class="wrap page-block"><p class="page-lead">These are shared-vehicle departures for solo travellers, couples and anyone who wants the Mara without paying for a private car. The migration departures are planned for July to October. A date is only a departure once the vehicle has the guests.</p>';
    mehs_offer_grid(mehs_tours_in("group-safaris"));
    echo "</div>";
}

function mehs_page_excursions() {
    mehs_banner("safari-vehicle.webp", "Day trips", "Excursions and day trips");
    echo '<div class="wrap page-block"><p class="page-lead">These sit beside a longer safari or a beach stay. A day in Amboseli, Ol Pejeta or Tsavo from Nairobi is mostly driving, and the notes on those pages say so.</p>';
    mehs_offer_grid(mehs_tours_in("excursions"));
    echo "</div>";
}

function mehs_guide_section($heading, $items) {
    $with_photos = isset($items[0][2]);
    echo '<section class="guide-block"><h2 class="ruled">' . esc_html($heading) . '</h2><div class="' . ($with_photos ? "do-grid" : "values") . '">';
    foreach ($items as $item) {
        if ($with_photos) {
            echo '<article class="do-card">' . mehs_image($item[2], $item[0]);
            echo '<div><h3>' . esc_html($item[0]) . '</h3><p>' . esc_html($item[1]) . '</p></div></article>';
            continue;
        }
        echo '<article class="value"><span aria-hidden="true">✓</span><div><h3>' . esc_html($item[0]) . '</h3><p>' . esc_html($item[1]) . '</p></div></article>';
    }
    echo "</div></section>";
}

function mehs_page_country($file, $title, $intro, $sections) {
    mehs_banner($file, "Destinations", $title);
    echo '<div class="wrap page-block"><p class="page-lead">' . esc_html($intro) . "</p>";
    foreach ($sections as $heading => $items) {
        mehs_guide_section($heading, $items);
    }
    echo '<p class="center"><a href="' . esc_url(home_url("/packages/")) . '">Browse safari packages</a></p></div>';
    echo '<section class="cta-band"><h2>Plan a tailor-made ' . esc_html($title) . ' safari</h2>';
    echo "<p>Tell us the month, who is travelling, and what you want to see. We reply with a route and a written price.</p>";
    echo '<div class="hero-actions" style="justify-content:center">';
    echo '<a class="btn btn-clay" href="' . esc_url(mehs_page_url("contact") ?: home_url("/contact/")) . '">Enquire now</a>';
    echo '<a class="btn btn-ghost" href="' . esc_url(home_url("/book/")) . '">Book a safari</a>';
    echo "</div></section>";
}

function mehs_country_guides() {
    return [
        "kenya" => [
            "Kenya is the usual starting point: the Mara, Amboseli, and the coast, planned from Nairobi around the month you can travel.",
            [
                "Things to do in Kenya" => [
                    ["Wildlife safaris", "Private game drives in the Mara, Amboseli, Tsavo, Samburu, and Nakuru, with the vehicle and guide to yourselves.", "safari-vehicle.webp"],
                    ["Hot-air balloon safaris", "A dawn flight over the Mara, when the month and the camp can arrange it. It is an extra, named on the quote.", "acacia-sunset.webp"],
                    ["Cultural visits", "A Maasai or other community visit that is paid to that community, not a roadside stop.", "giraffe-dusk.webp"],
                    ["Hiking", "Walks where the park allows them, including shorter conservancy walks. We say which days are driving and which are on foot.", "red-savanna.webp"],
                    ["Beach holidays", "Diani, Watamu, or the north coast after the safari. Plan three or four nights. One night is mostly transfers.", "zanzibar.webp"],
                ],
                "Top destinations to explore" => [
                    ["Maasai Mara", "Open grassland, big cats, and the river crossings in a good July to October."],
                    ["Amboseli", "Elephants on the open plain, with Kilimanjaro behind them when the sky is clear."],
                    ["Tsavo East and Tsavo West", "Red soil, large elephant herds, and long views between the two parks."],
                    ["Samburu", "The northern specials: Grevy’s zebra, reticulated giraffe, and the Ewaso Ng’iro."],
                    ["Lake Nakuru and Ol Pejeta", "Rhino country, with flamingos at Nakuru when the lake is holding them."],
                ],
                "Travel guides" => [
                    ["Best time to visit", "June to October is the dry season and the usual Mara window. January to March is hot and quieter. April and May are the long rains: green, fewer vehicles, and some camps closed."],
                    ["What to expect", "Most days are two game drives from a lodge or camp. Distances between parks are real. A Nairobi day trip to Amboseli is mostly driving."],
                    ["Planning notes", "Park fees, the vehicle, and the lodges are named on the quote before you pay. Ask for the Tourism Regulatory Authority licence with that quote."],
                ],
                "Wildlife and safari experiences" => [
                    ["The Great Migration", "River crossings are a July to October plan. A particular morning cannot be promised."],
                    ["Big Five safaris", "The Mara is the usual Big Five route. Nakuru and Ol Pejeta are the stronger rhino stops."],
                    ["Photography safaris", "We can bias the hours toward morning and evening light, and book a vehicle with a window seat for each person."],
                ],
            ],
        ],
        "tanzania" => [
            "Tanzania is the Serengeti, Ngorongoro, and the northern parks, usually starting in Arusha, with Zanzibar when you want a beach at the end.",
            [
                "Things to do in Tanzania" => [
                    ["Wildlife safaris", "Game drives on the Serengeti plains and in Tarangire, with a crater descent at Ngorongoro when that day is in the plan.", "serengeti.webp"],
                    ["Hot-air balloon safaris", "A sunrise balloon over the Serengeti in the months the operators fly. It is quoted on its own line.", "elephant-savanna.webp"],
                    ["Cultural experiences", "A visit with a Maasai community near the crater highlands or a walk in a village that is set up for guests.", "ngorongoro.webp"],
                    ["Hiking", "Kilimanjaro and the shorter highland walks are separate from a game-drive safari. We plan them only with the days they need.", "kilimanjaro.webp"],
                    ["Beach holidays", "Zanzibar after the northern circuit. Three or four nights is the useful length.", "zanzibar.webp"],
                ],
                "Top destinations to explore" => [
                    ["Serengeti", "The long-grass and short-grass plains, and the migration herds when the month matches the route."],
                    ["Ngorongoro Crater", "A day on the crater floor for rhino, lion, and the large herds on the floor."],
                    ["Tarangire", "Baobabs and elephants in the dry season, when the river holds the animals."],
                    ["Lake Manyara", "A compact park on the way, known for forest, the lake, and tree-climbing lions when they are up."],
                    ["Mount Kilimanjaro", "The mountain is its own trek, not an afternoon added to a safari."],
                    ["Zanzibar", "Stone Town and the north or east coast after the wildlife days."],
                ],
                "Travel guides" => [
                    ["Best time to visit", "June to October is dry and the usual time for the Serengeti river area. January to March is the calving season on the southern plains. April and May are the long rains."],
                    ["What to expect", "Trips start in Arusha. Internal flights save a long drive when the route is Serengeti to Zanzibar. The quote says which transfers are by road and which are by air."],
                    ["Planning notes", "Park fees and the crater service fee are part of the written price. Tanzania’s own entry rules are sent before you pay a deposit."],
                ],
                "Wildlife and safari experiences" => [
                    ["The Great Migration", "The herds move. We place the Serengeti nights where the month usually finds them, and we do not promise a crossing."],
                    ["Big Five safaris", "Ngorongoro and the Serengeti are the usual Big Five pairing. Tarangire adds elephants."],
                    ["Photography safaris", "Dawn drives and a vehicle that can stay with a sighting. Balloon flights are optional."],
                ],
            ],
        ],
        "uganda" => [
            "Uganda safaris are built around gorilla trekking in Bwindi and, when a permit is free, Mgahinga. The permit is quoted on its own line.",
            [
                "Things to do in Uganda" => [
                    ["Gorilla trekking", "A morning in Bwindi or Mgahinga with a permit booked in your name. The trek can be short or most of the day.", "gorilla.webp"],
                    ["Chimpanzee tracking", "Kibale is the usual forest for chimpanzees, on a different day from the gorillas.", "rwanda.webp"],
                    ["Boat safaris", "A launch on the Kazinga Channel in Queen Elizabeth, for hippos, buffalo, and elephants along the bank.", "queen.webp"],
                    ["Game drives", "Queen Elizabeth and Murchison Falls for savannah animals between the forest days.", "lions.webp"],
                    ["Community visits", "A stop with a community near the park, paid to that community.", "acacia-sunset.webp"],
                ],
                "Top destinations to explore" => [
                    ["Bwindi Impenetrable National Park", "The main gorilla forest, with several sectors. The sector depends on which permits are free."],
                    ["Mgahinga", "The smaller gorilla park on the volcanoes, used when a Bwindi permit is not open."],
                    ["Queen Elizabeth National Park", "The Kazinga Channel, the Ishasha tree-climbing lions when they are in the figs, and crater lakes."],
                    ["Murchison Falls", "The Nile squeezed through the falls, with game drives on the north bank."],
                    ["Kibale", "Chimpanzee tracking in the forest west of the crater lakes."],
                ],
                "Travel guides" => [
                    ["Best time to visit", "June to September and December to February are the drier months. The forests are trekable in the rains, and the paths are steeper and muddier."],
                    ["What to expect", "A gorilla day starts early. You walk with rangers to where the family is that morning. Fitness and a porter are worth deciding before you book."],
                    ["Planning notes", "The gorilla permit is a separate line on the quote, and the cancellation rule on a permit is stricter than on the lodges."],
                ],
                "Wildlife and safari experiences" => [
                    ["Mountain gorillas", "One hour with a habituated family after the trek in. A second trek is a second permit."],
                    ["Chimpanzees", "Kibale tracking is the usual add, not a substitute for the gorilla permit."],
                    ["Tree-climbing lions", "Ishasha, in the southern part of Queen Elizabeth, when the lions are using the fig trees."],
                    ["Shoebill storks", "Mabamba and the Nile delta are the places named for shoebill, as an extra day from Entebbe or Murchison."],
                ],
            ],
        ],
        "rwanda" => [
            "Rwanda is gorilla trekking in Volcanoes National Park, savannah in Akagera, and forest in Nyungwe. The gorilla permit is shown on its own line.",
            [
                "Things to do in Rwanda" => [
                    ["Gorilla trekking", "A permit in Volcanoes National Park. The group size on the mountain is small, and the start is early.", "gorilla.webp"],
                    ["Golden monkey tracking", "A shorter forest walk in the same park, often the day before or after the gorillas.", "rwanda.webp"],
                    ["Canopy walk", "The suspension bridge in Nyungwe, with colobus and a chance of chimpanzees on a separate tracking permit.", "ngorongoro.webp"],
                    ["Game drives", "Akagera for savannah animals on the eastern lakes.", "zebra.webp"],
                    ["Kigali and Lake Kivu", "A city day, then the lake if you want a night off the parks.", "flamingo.webp"],
                ],
                "Top destinations to explore" => [
                    ["Volcanoes National Park", "Gorillas and golden monkeys on the Virunga slopes."],
                    ["Nyungwe", "Montane forest, the canopy walk, and chimpanzee tracking."],
                    ["Akagera", "Savannah, lakes, and the reintroduced lions and rhinos."],
                    ["Lake Kivu", "A rest night after the forest, with the Congo Nile trail if you want a walk."],
                    ["Kigali", "The usual arrival city, and the genocide memorial if you want that visit in the plan."],
                ],
                "Travel guides" => [
                    ["Best time to visit", "June to September and December to February are drier. The forest treks run all year. Paths are harder in the long rains."],
                    ["What to expect", "Roads between Kigali, Musanze, and the forest gates are good by regional standards. The trek itself is the variable: uphill, and as long as the gorillas’ location that morning."],
                    ["Planning notes", "The gorilla permit is quoted alone. Rwanda’s own entry rules are sent before a deposit."],
                ],
                "Wildlife and safari experiences" => [
                    ["Mountain gorillas", "One hour with the family. Permits are limited, so the month has to be agreed before we request them."],
                    ["Big Five in Akagera", "Lion, leopard, elephant, buffalo, and rhino are the reason for the savannah day. Sightings are wildlife, not a guarantee of all five in one drive."],
                    ["Chimpanzees and colobus", "Nyungwe is the forest for both. Chimpanzee tracking uses its own permit."],
                    ["Golden monkeys", "Bamboo forest in Volcanoes, a different morning from the gorillas."],
                ],
            ],
        ],
    ];
}

function mehs_render_known_page() {
    $slug = get_post_field("post_name");
    if ($slug === "contact") {
        mehs_page_contact();
        return true;
    }
    if ($slug === "blog") {
        mehs_page_blog();
        return true;
    }
    if ($slug === "faqs") {
        mehs_page_faqs();
        return true;
    }
    if ($slug === "group-safaris") {
        mehs_page_group();
        return true;
    }
    if ($slug === "excursions") {
        mehs_page_excursions();
        return true;
    }
    $guides = mehs_country_guides();
    $countries = [
        "short-kenya-safaris" => ["zebra.webp", "Kenya", "kenya"],
        "tanzania-safaris" => ["serengeti.webp", "Tanzania", "tanzania"],
        "uganda-safaris" => ["gorilla.webp", "Uganda", "uganda"],
        "rwanda-safaris" => ["rwanda.webp", "Rwanda", "rwanda"],
    ];
    if (isset($countries[$slug])) {
        [$file, $name, $key] = $countries[$slug];
        mehs_page_country($file, $name, $guides[$key][0], $guides[$key][1]);
        return true;
    }
    $tour = mehs_find_tour($slug);
    if ($tour) {
        mehs_render_package($tour);
        return true;
    }
    return false;
}

function mehs_gallery_wall() {
    $groups = [
        "Kenya" => [
            ["amboseli.webp", "Elephants in Amboseli National Park with Mount Kilimanjaro behind them"],
            ["kilimanjaro.webp", "Snow-capped Mount Kilimanjaro above the Amboseli plains"],
            ["tsavo.webp", "A dust-red elephant in Tsavo East National Park, Kenya"],
            ["zebra.webp", "Three plains zebras in the Masai Mara, Kenya"],
            ["migration.webp", "Wildebeest crossing the Mara River in the Masai Mara"],
            ["lions.webp", "Lions resting in long grass"],
            ["flamingo.webp", "Flamingos on Lake Nakuru, Kenya"],
            ["samburu.webp", "A reticulated giraffe in Samburu National Reserve"],
            ["rhino.webp", "A rhino in the grass"],
            ["safari-vehicle.webp", "A safari vehicle at sunset"],
        ],
        "Tanzania and the coast" => [
            ["serengeti.webp", "The Serengeti plains"],
            ["ngorongoro.webp", "The Ngorongoro highlands"],
            ["zanzibar.webp", "The shoreline at a Zanzibar beach"],
        ],
        "Uganda and Rwanda" => [
            ["gorilla.webp", "A mountain gorilla in Bwindi Impenetrable National Park"],
            ["queen.webp", "Queen Elizabeth National Park"],
            ["rwanda.webp", "The hills of Rwanda"],
        ],
    ];
    mehs_banner("migration.webp", "Photographs", "Gallery");
    echo '<div class="wrap page-block">';
    foreach ($groups as $title => $photos) {
        echo '<section class="gallery-block"><h2>' . esc_html($title) . '</h2><div class="photo-wall">';
        foreach ($photos as $photo) {
            echo '<figure class="photo-card">' . mehs_image($photo[0], $photo[1]) . "<figcaption>" . esc_html($photo[1]) . "</figcaption></figure>";
        }
        echo "</div></section>";
    }
    $uploads = mehs_library_photos();
    if ($uploads) {
        echo '<section class="gallery-block"><h2>Photographs added</h2><div class="photo-wall">';
        foreach ($uploads as $photo) {
            echo '<figure class="photo-card"><img src="' . esc_url($photo["src"]) . '" alt="' . esc_attr($photo["caption"]) . '"><figcaption>' . esc_html($photo["caption"]) . "</figcaption></figure>";
        }
        echo "</div></section>";
    }
    echo "</div>";
}

function mehs_library_photos() {
    $images = get_posts([
        "post_type" => "attachment",
        "post_mime_type" => "image",
        "post_status" => "inherit",
        "posts_per_page" => 120,
        "orderby" => "date",
        "order" => "DESC",
    ]);
    $photos = [];
    foreach ($images as $image) {
        $file = get_attached_file($image->ID);
        $name = strtolower($file ? basename($file) : "");
        if ($name !== "" && preg_match("/logo|favicon|icon|sprite|emoji/", $name)) {
            continue;
        }
        $meta = wp_get_attachment_metadata($image->ID);
        $width = (int) ($meta["width"] ?? 0);
        $height = (int) ($meta["height"] ?? 0);
        if ($width && $height && max($width, $height) < 500) {
            continue;
        }
        $src = wp_get_attachment_image_url($image->ID, "large");
        if (!$src) {
            continue;
        }
        $caption = trim(wp_strip_all_tags(get_post_meta($image->ID, "_wp_attachment_image_alt", true)));
        if ($caption === "") {
            $caption = trim(wp_strip_all_tags(get_the_title($image)));
        }
        $photos[] = ["src" => $src, "caption" => $caption !== "" ? $caption : "Safari photograph"];
    }
    return $photos;
}

function mehs_page_blog() {
    mehs_banner("safari-vehicle.webp", "Journal", "Blog");
    echo '<div class="wrap page-block"><div class="posts">';
    $posts = get_posts(["numberposts" => 24, "post_status" => "publish"]);
    if (!$posts) {
        echo "<p>New stories will appear here.</p>";
    }
    foreach ($posts as $post) {
        echo '<article class="card">';
        if (has_post_thumbnail($post)) {
            echo '<a href="' . esc_url(get_permalink($post)) . '">' . get_the_post_thumbnail($post, "mehs-card") . "</a>";
        }
        echo '<div class="safari-body"><p class="fine">' . esc_html(get_the_date("", $post)) . "</p>";
        echo '<h2><a href="' . esc_url(get_permalink($post)) . '">' . esc_html(get_the_title($post)) . "</a></h2>";
        echo "<p>" . esc_html(wp_trim_words(wp_strip_all_tags(get_the_excerpt($post)), 32)) . "</p></div></article>";
    }
    echo "</div></div>";
}
