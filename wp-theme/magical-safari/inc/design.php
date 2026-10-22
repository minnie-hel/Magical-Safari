<?php
/**
 * Designed public pages. These match the local preview.
 */

function mehs_render_front() {
    echo '<section class="banner hero">';
    $hero = mehs_saved_banner_image();
    if ($hero === "") {
        foreach (mehs_saved_copy()["sections"] as $section) {
            if (($section["tag"] ?? "") === "h1" && ($section["image"] ?? "") !== "") {
                $hero = $section["image"];
                break;
            }
        }
    }
    echo mehs_picture($hero !== "" ? $hero : "elephant-savanna.webp", "Elephants on the savannah", "banner-media");
    echo '<div class="banner-copy wrap">';
    $title = mehs_saved_copy()["h1"] !== "" ? mehs_saved_copy()["h1"] : "Discover the magic of East Africa.";
    echo "<h1>" . esc_html($title) . "</h1>";
    echo '<p class="lede">' . esc_html(mehs_saved_line($title, "Private safaris in Kenya, Tanzania, Uganda and Rwanda, planned in Nairobi.", "h1")) . "</p>";
    echo '<div class="hero-actions">';
    echo '<a class="btn btn-clay" href="' . esc_url(home_url("/packages/")) . '">Explore packages</a>';
    echo '<a class="btn btn-ghost" href="' . esc_url(home_url("/book/")) . '">Book a safari</a>';
    echo "</div></div></section>";
    mehs_home_destinations();
    mehs_home_why();
    echo '<section class="home-featured"><div class="wrap"><div class="section-intro"><p class="kicker">Our offerings</p><h2>Featured tour packages</h2></div>';
    mehs_offer_grid(array_slice(mehs_tours_in("short-kenya-safaris"), 0, 6));
    echo '<p class="center"><a class="btn btn-green" href="' . esc_url(home_url("/packages/")) . '">View all packages</a></p></div></section>';
    mehs_home_welcome();
    mehs_home_services();
    mehs_home_about();
    mehs_home_reviews();
    mehs_home_stories();
    mehs_home_enquiry();
}

function mehs_home_destinations() {
    $places = [
        ["Maasai Mara", "Kenya", "zebra.webp", "short-kenya-safaris", "Open grassland, big cats, and the river crossings in a good July to October."],
        ["Amboseli", "Kenya", "amboseli.webp", "short-kenya-safaris", "Elephants on the open plain, with Kilimanjaro behind them when the sky is clear."],
        ["Samburu", "Kenya", "samburu.webp", "short-kenya-safaris", "Grevy’s zebra, reticulated giraffe, and the Ewaso Nyiro."],
        ["Serengeti", "Tanzania", "serengeti.webp", "tanzania-safaris", "The plains, and the migration herds when the month matches the route."],
        ["Ngorongoro", "Tanzania", "ngorongoro.webp", "tanzania-safaris", "A day on the crater floor for rhino, lion, and the herds."],
        ["Zanzibar", "Tanzania", "zanzibar.webp", "tanzania-safaris", "A beach stay of three or four nights after the parks."],
        ["Bwindi", "Uganda", "gorilla.webp", "uganda-safaris", "Gorilla trekking with a permit booked in your name."],
        ["Volcanoes", "Rwanda", "rwanda.webp", "rwanda-safaris", "Gorilla trekking on the Rwanda side, planned with the permit days."],
    ];
    echo '<section class="home-destinations"><div class="wrap">';
    echo '<div class="section-intro"><p class="kicker">Popular destinations</p><h2>Parks we plan around</h2></div><div class="dest-grid">';
    foreach ($places as $place) {
        $url = mehs_page_url($place[3]) ?: home_url("/" . $place[3] . "/");
        echo '<a class="dest-card" href="' . esc_url($url) . '">';
        echo mehs_picture($place[2], $place[0]);
        echo "<div><p>" . esc_html($place[1]) . "</p><h3>" . esc_html($place[0]) . "</h3><p>" . esc_html($place[4]) . "</p></div></a>";
    }
    echo "</div></div></section>";
}

function mehs_home_why() {
    $points = [
        ["A written price", "The lodges, park fees and the vehicle are named before anyone pays."],
        ["One desk", "The same office in Nairobi stays on the phone from the first message until you are home."],
        ["Your vehicle", "A private safari is your vehicle and your guide. A group safari is a set departure."],
        ["The days decide the route", "Kenya is the flagship. Tanzania, Uganda and Rwanda are added when the days, and a permit where one is needed, are real."],
    ];
    echo '<section class="home-why"><div class="wrap">';
    echo '<div class="section-intro"><p class="kicker">Why choose us</p><h2>What you can count on</h2></div><div class="values">';
    foreach ($points as $point) {
        echo '<article class="value"><span aria-hidden="true">✓</span><div><strong>' . esc_html($point[0]) . "</strong><p>" . esc_html($point[1]) . "</p></div></article>";
    }
    echo "</div></div></section>";
}

function mehs_home_welcome() {
    echo '<section class="home-welcome"><div class="wrap">';
    echo '<p class="kicker">How a safari is planned</p><h2>Tell us the month. We write the route.</h2>';
    echo "<p>You send the month, who is travelling, and the parks you want to see. We answer from Nairobi with the days in order, the lodges named, and the price in writing before anyone pays. Kenya is where most trips start: the Mara, Amboseli, Tsavo, Samburu, Nakuru and Ol Pejeta. Tanzania, Uganda and Rwanda are added when the days, and a permit where one is needed, are real.</p>";
    echo "<p>A private safari is your vehicle and your guide. A group safari is a set departure, shared with the other seats on that vehicle. The same desk stays with the booking from the first message until you are back. Park fees, the vehicle and the nights are on the quote. A gorilla permit, a balloon flight, or nights at the coast are written on their own line.</p>";
    echo "<p>People book here because the route follows their dates, not a timetable we already printed, and the price is the one they agreed before a deposit. Call " . esc_html(MEHS_PHONE) . ", write to " . esc_html(MEHS_EMAIL) . ", or use the form. We reply with the plan.</p>";
    echo "</div></section>";
}

function mehs_home_services() {
    $services = [
        ["Wildlife safaris", "Private game drives in Kenya, and in Tanzania, Uganda and Rwanda when those parks belong on the route.", "safari-vehicle.webp"],
        ["Trekking", "Gorilla permits in Uganda or Rwanda, and Kilimanjaro only when the climb has its own days.", "gorilla.webp"],
        ["Beach stays", "Diani, Watamu or Zanzibar after the parks. Plan three or four nights. One night is mostly transfers.", "zanzibar.webp"],
        ["Cultural visits", "A community visit that is paid to that community, not a roadside stop.", "giraffe-dusk.webp"],
    ];
    echo '<section class="home-services"><div class="wrap">';
    echo '<div class="section-intro"><p class="kicker">Our services</p><h2>Safaris, trekking, beaches and visits</h2></div><div class="do-grid">';
    foreach ($services as $service) {
        echo '<article class="do-card">' . mehs_picture($service[2], $service[0]);
        echo "<div><h3>" . esc_html($service[0]) . "</h3><p>" . esc_html($service[1]) . "</p></div></article>";
    }
    echo "</div></div></section>";
}

function mehs_home_about() {
    $about = mehs_page_url("about-us") ?: home_url("/about-us/");
    echo '<section class="home-about"><div class="wrap about-grid"><div>';
    echo '<p class="kicker">About us</p><h2>A Nairobi desk for East Africa</h2>';
    echo "<p><strong>Magical East Africa Heritage Safaris</strong> is a safari company based in Nairobi. Kenya is the flagship: the Mara, Amboseli, Tsavo, Samburu, Nakuru and Ol Pejeta. We also plan Tanzania, Uganda and Rwanda when the days, and the permit budget, are real.</p>";
    echo "<p>The mission is personal safari plans that connect travellers with East Africa’s wildlife, parks and cultures, with the price written down before anyone pays.</p>";
    echo '<p><a class="btn btn-green" href="' . esc_url($about) . '">Read about us</a></p>';
    echo "</div>" . '<figure class="about-figure">' . mehs_picture("safari-vehicle.webp", "Safari vehicle", "about-photo") . "</figure></div></section>";
}

function mehs_home_reviews() {
    $notes = [
        ["The guide", "On the vehicle, guests write about guide James Ngigi."],
        ["Before you book", "Nicole helps shape the plan before you book."],
        ["After the deposit", "Grace is the person guests have reached after the deposit."],
    ];
    echo '<section class="home-reviews"><div class="wrap">';
    echo '<div class="section-intro"><p class="kicker">Testimonials</p><h2>What guests mention</h2></div><div class="values">';
    foreach ($notes as $note) {
        echo '<article class="value"><span aria-hidden="true">✓</span><div><strong>' . esc_html($note[0]) . "</strong><p>" . esc_html($note[1]) . "</p></div></article>";
    }
    echo "</div>";
    echo "<p>We are listed on SafariBookings and SafariOptions. Ask us for the Tourism Regulatory Authority licence before you pay a deposit.</p>";
    echo "</div></section>";
}

function mehs_home_stories() {
    $photos = [
        ["zebra.webp", "Zebras"],
        ["flamingo.webp", "Flamingos"],
        ["lions.webp", "Lions"],
        ["kilimanjaro.webp", "Kilimanjaro"],
        ["zanzibar.webp", "The coast"],
        ["migration.webp", "The migration"],
    ];
    $gallery = home_url("/gallery/");
    $blog = mehs_page_url("blog") ?: home_url("/blog/");
    echo '<section class="home-stories"><div class="wrap">';
    echo '<div class="section-intro"><p class="kicker">Gallery and blog</p><h2>Photographs and planning notes</h2></div><div class="gallery">';
    foreach ($photos as $photo) {
        echo '<a href="' . esc_url($gallery) . '">' . mehs_picture($photo[0], $photo[1]) . "</a>";
    }
    echo '</div><p class="home-notes">The gallery is the field photographs. The blog is where the season and park notes go. Seasons, what to pack, and which parks fit the month are written there.</p>';
    if (function_exists("get_posts")) {
        $posts = get_posts(["numberposts" => 3, "post_status" => "publish"]);
        if ($posts) {
            echo '<div class="home-post-list">';
            foreach ($posts as $post) {
                echo '<a href="' . esc_url(get_permalink($post)) . '"><strong>' . esc_html(get_the_title($post)) . "</strong></a>";
            }
            echo "</div>";
        }
    }
    echo '<div class="home-story-actions"><a class="btn btn-line" href="' . esc_url($gallery) . '">Open the gallery</a>';
    echo '<a class="btn btn-green" href="' . esc_url($blog) . '">Read the blog</a></div></div></section>';
}

function mehs_home_enquiry() {
    echo '<section class="home-enquiry"><div class="wrap">';
    echo '<div class="section-intro"><p class="kicker">Booking inquiry</p><h2>Tell us the dates</h2></div>';
    echo "<p>Travel dates, who is coming, and how to reach you. We confirm availability and send the price in writing. Nothing is charged on this form.</p>";
    echo '<form class="plan-card" data-enquiry data-whatsapp-number="' . esc_attr(MEHS_WHATSAPP) . '" data-email-address="' . esc_attr(MEHS_EMAIL) . '"><div class="form-grid">';
    echo '<div class="form-grid form-2"><label>Full name<input name="name" autocomplete="name" required></label><label>Email address<input name="email" type="email" autocomplete="email" required></label></div>';
    echo '<div class="form-grid form-2"><label>Phone<input name="phone" autocomplete="tel" required></label><label>Travel date<input name="date" type="date" required></label></div>';
    echo '<div class="form-grid form-2"><label>Number of people<input name="adults" type="number" min="1" max="20" value="2" required></label><label>Where you want to go<input name="trip" placeholder="Kenya, Tanzania, or a park"></label></div>';
    echo '<label>Anything we should know?<textarea name="note" rows="4"></textarea></label>';
    echo '<div class="hp"><label>Leave this blank<input name="mehs_leave_blank" tabindex="-1" autocomplete="off"></label></div>';
    echo '<div class="send-row"><button class="btn btn-green" type="submit" name="channel" value="whatsapp">Send on WhatsApp</button>';
    echo '<button class="btn btn-line" type="submit" name="channel" value="email">Send by email</button></div>';
    echo '<div class="result" data-result><p data-status></p></div></div></form></div></section>';
}

function mehs_render_about() {
    echo '<section class="banner page-banner">';
    echo mehs_image("acacia-sunset.webp", "Acacia at sunset", "banner-media");
    echo '<div class="banner-copy wrap"><p class="kicker">Our story</p><h1>About us</h1>';
    echo '<p class="crumbs"><a href="' . esc_url(home_url("/")) . '">Home</a> · About us</p></div></section>';
    echo '<section><div class="wrap about-grid"><div><p class="kicker">Who we are</p><h2>About us</h2>';
    $about = array_values(array_filter(mehs_saved_paragraphs("About us", "h2"), function ($paragraph) {
        return strlen($paragraph) > 40;
    }));
    if ($about) {
        foreach ($about as $paragraph) {
            $paragraph = mehs_plain_start($paragraph);
            if ($paragraph !== "") {
                echo "<p>" . esc_html($paragraph) . "</p>";
            }
        }
    } else {
        echo "<p><strong>Magical East Africa Heritage Safaris</strong> is a safari company based in Nairobi. Kenya is the flagship: the Mara, Amboseli, Tsavo, Samburu, Nakuru and Ol Pejeta. We also plan Tanzania, Uganda and Rwanda when the days, and the permit budget, are real.</p>";
        echo "<p>The journeys are private, or a shared vehicle when you choose a group safari. The same desk writes the itinerary, names the lodges on the quote, and stays on the phone from the first message until you are home. On the vehicle, guests write about guide James Ngigi. Nicole helps shape the plan before you book. Grace is the person guests have reached after the deposit.</p>";
        echo "<p>The name on this website is the name on our email address and the name that should appear on your invoice. Some reviews shorten it to Magical Africa Safaris. That is the same desk.</p>";
        echo "<p>We are listed on SafariBookings and SafariOptions. Ask us for the Tourism Regulatory Authority licence before you pay a deposit.</p>";
    }
    $photo = mehs_saved_image("About us", "h2");
    echo '</div><figure class="about-figure">' . mehs_picture($photo !== "" ? $photo : "safari-vehicle.webp", "Safari vehicle at sunset", "about-photo") . "</figure></div></section>";
    echo '<section class="about-band"><div class="wrap"><div class="mission-grid">';
    echo "<article><p class=\"kicker\">Our mission</p><p>Personal safari plans that connect travellers with East Africa’s wildlife, parks and cultures, with the price written down before anyone pays.</p></article>";
    echo "<article><p class=\"kicker\">Our vision</p><p>To be a trusted desk for authentic safaris in Kenya, Tanzania, Uganda and Rwanda.</p></article>";
    echo '</div><div class="section-intro"><p class="kicker">What drives us</p><h2>Our core values</h2></div><div class="values">';
    $values = [
        ["Customer satisfaction", "The route is built around your month, your group and the way you like to travel."],
        ["Professionalism", "One name, one phone number, and a written quote before you pay."],
        ["Sustainability", "Park tracks, conservancies where they fit, and community visits paid to that community."],
        ["Safety", "A licensed operator, a known vehicle, and a meeting point confirmed with your flight."],
        ["Authenticity", "The parks, the wildlife and the people on the route, not a staged version of them."],
    ];
    foreach ($values as $value) {
        echo '<article class="value"><span aria-hidden="true">✓</span><div><strong>' . esc_html($value[0]) . "</strong><p>" . esc_html($value[1]) . "</p></div></article>";
    }
    echo '</div></div></section>';
    mehs_about_extra();
    mehs_about_trust();
    mehs_about_partners();
    echo '<section class="cta-band"><h2>Ready to explore East Africa?</h2>';
    echo "<p>Tell us the month and who is travelling. We reply with a plan and a written price.</p>";
    echo '<div class="hero-actions" style="justify-content:center">';
    echo '<a class="btn btn-clay" href="' . esc_url(home_url("/packages/")) . '">View packages</a>';
    echo '<a class="btn btn-ghost" href="' . esc_url(mehs_page_url("contact") ?: home_url("/contact/")) . '">Get in touch</a>';
    echo "</div></section>";
}

function mehs_about_extra() {
    echo '<section class="about-extra"><div class="wrap">';
    echo '<p class="kicker">How we plan</p><h2>From the first message to the vehicle</h2>';
    echo "<p>The trip is written around your dates. We do not drop you onto a departure that was already full, unless you asked for a group safari. The order of the parks follows the driving days, so a short trip is not asked to cover four parks.</p>";
    echo '<div class="steps">';
    $steps = [
        ["1", "Your dates", "Tell us the month, how many of you are travelling, and what you want to see."],
        ["2", "The route", "We put the parks in an order the days can carry, and we name the lodges."],
        ["3", "The quote", "You read the price in writing. Nothing is paid until you agree to that page."],
        ["4", "The meeting", "We confirm where the vehicle meets you, against your flight. The guide is the one on the plan."],
    ];
    foreach ($steps as $step) {
        echo '<article class="step"><b>' . esc_html($step[0]) . "</b><strong>" . esc_html($step[1]) . "</strong><p>" . esc_html($step[2]) . "</p></article>";
    }
    echo "</div>";
    echo "<p>Kenya is the flagship, and Tanzania, Uganda and Rwanda are planned from the same desk when they belong on the route. A private vehicle stays with your group. A group safari uses the seats on that departure. Either way you are speaking to the office in Nairobi, on " . esc_html(MEHS_PHONE) . " or " . esc_html(MEHS_EMAIL) . ".</p>";
    echo "</div></section>";
}

function mehs_about_trust() {
    echo '<section class="about-trust"><div class="wrap">';
    echo '<p class="kicker">Our story</p><h2>A desk that stays with the trip</h2>';
    echo "<p>Magical East Africa Heritage Safaris plans the journey from Nairobi. The work is the day-by-day route, the lodges named on the quote, and the same phone number from the first message until you are home. Kenya is where most trips start. Tanzania, Uganda and Rwanda are added when the days, and a permit where one is needed, are real. The name on this website is the name on the email and the name that should be on the invoice.</p>";
    echo '<p class="kicker">Why choose us</p><h2>What you can check before you pay</h2>';
    echo '<div class="values">';
    $reasons = [
        ["Your dates", "The route follows the month you can travel. A group safari is only used when you ask for a shared departure."],
        ["A written price", "Park fees, the vehicle and the lodges are named before anyone pays. A gorilla permit, a balloon flight, or nights at the coast are on their own line."],
        ["One office", "You are speaking to the desk in Nairobi, on " . MEHS_PHONE . " or " . MEHS_EMAIL . ", before the trip and while you are away."],
        ["The licence", "We are listed on SafariBookings and SafariOptions. Ask for the Tourism Regulatory Authority licence before you pay a deposit."],
    ];
    foreach ($reasons as $reason) {
        echo '<article class="value"><span aria-hidden="true">✓</span><div><strong>' . esc_html($reason[0]) . "</strong><p>" . esc_html($reason[1]) . "</p></div></article>";
    }
    echo "</div>";
    echo '<p class="kicker">The people you deal with</p><h2>The desk and the guide</h2>';
    echo '<div class="about-team">';
    $people = [
        ["James Ngigi", "Guide", "On the vehicle, guests write about James Ngigi. He is the guide named on the plan."],
        ["Nicole", "Before you book", "Nicole helps shape the plan before you book, while the route and the price are still being written."],
        ["Grace", "After the deposit", "Grace is the person guests have reached after the deposit, once the trip is confirmed."],
    ];
    foreach ($people as $person) {
        echo '<article><strong>' . esc_html($person[0]) . '</strong><span>' . esc_html($person[1]) . "</span><p>" . esc_html($person[2]) . "</p></article>";
    }
    echo "</div>";
    echo '<p class="kicker">Responsible tourism</p><h2>The parks and the people on the route</h2>';
    echo "<p>A community visit is paid to that community, not taken as a roadside stop. Game drives stay on the park tracks, and a conservancy is used when it belongs on the route. Gorilla permits are booked in the guest’s name and quoted on their own line. We do not add a park the days cannot carry.</p>";
    echo "</div></section>";
}

function mehs_about_partners() {
    echo '<section class="about-partners"><div class="wrap">';
    echo '<div class="section-intro"><p class="kicker">Our partners</p><h2>Partners</h2></div>';
    echo '<div class="partner-row">';
    for ($slot = 1; $slot <= 8; $slot++) {
        echo '<figure class="partner">' . mehs_image("partner.svg", "Add a partner logo", "partner-logo") . "</figure>";
    }
    echo "</div></div></section>";
}

function mehs_banner($file, $kicker, $title) {
    $saved = mehs_saved_banner_image();
    echo '<section class="banner page-banner">';
    echo mehs_picture($saved !== "" ? $saved : $file, $title, "banner-media");
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
            $line = mehs_plain_start($line);
            if (strlen($line) > 45 && strpos($line, "@") === false && preg_match('/^[\p{L}\p{N}]/u', $line)) {
                $summary = $line;
                break;
            }
        }
        foreach ($days as $index => $day) {
            if (!is_array($day)) {
                continue;
            }
            $days[$index]["title"] = mehs_plain_start($day["title"] ?? "");
            $days[$index]["text"] = mehs_plain_start($day["text"] ?? "");
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
            "highlights" => array_values(array_filter(array_map("mehs_plain_start", array_slice($tour["highlights"] ?? [], 0, 8)))),
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

function mehs_same_trip($page, $tour) {
    if (!$page instanceof WP_Post || !is_array($tour)) {
        return false;
    }
    $page_title = sanitize_title(wp_strip_all_tags(get_the_title($page)));
    $tour_title = sanitize_title(wp_strip_all_tags((string) ($tour["title"] ?? "")));
    $tour_slug = sanitize_title((string) ($tour["slug"] ?? ""));
    if ($page_title === "") {
        return true;
    }
    if ($page_title === $tour_title || ($tour_slug !== "" && $page_title === $tour_slug)) {
        return true;
    }
    if ($tour_title !== "" && (strpos($page_title, $tour_title) !== false || strpos($tour_title, $page_title) !== false)) {
        return true;
    }
    return false;
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
    if (preg_match("/(\d+)\s*-?\s*days?/i", $page->post_title . " " . $page->post_name, $match)) {
        $count = (int) $match[1];
    }
    $countries = [
        "tanzania-safaris" => "Tanzania",
        "uganda-safaris" => "Uganda",
        "rwanda-safaris" => "Rwanda",
        "international-destinations" => "International",
    ];
    $country = $countries[$section] ?? "Kenya";
    $summary = mehs_plain_start(get_the_excerpt($page));
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
        "photo" => mehs_page_photo($page->ID),
        "pageId" => (int) $page->ID,
    ];
}

function mehs_with_page_photo($tour) {
    $paths = [];
    if (!empty($tour["section"])) {
        $paths[] = $tour["section"] . "/" . $tour["slug"];
        $paths[] = "destinations/" . $tour["section"] . "/" . $tour["slug"];
    }
    $paths[] = $tour["slug"];
    foreach ($paths as $path) {
        $page = get_page_by_path($path);
        if (!$page instanceof WP_Post || $page->post_status !== "publish" || !mehs_same_trip($page, $tour)) {
            continue;
        }
        $photo = mehs_page_photo($page->ID);
        if ($photo !== "") {
            $tour["photo"] = $photo;
        }
        break;
    }
    return $tour;
}

function mehs_tours_in($section) {
    $tours = array_values(array_filter(mehs_tours(), function ($tour) use ($section) {
        return $tour["section"] === $section;
    }));
    $parent = mehs_section_page($section);
    if (!$parent instanceof WP_Post) {
        foreach ($tours as $index => $tour) {
            $tours[$index] = mehs_with_page_photo($tour);
        }
        return $tours;
    }
    $pages = get_posts([
        "post_type" => "page",
        "post_status" => "publish",
        "post_parent" => $parent->ID,
        "numberposts" => -1,
        "orderby" => "menu_order title",
        "order" => "ASC",
    ]);
    foreach ($pages as $page) {
        $existing = mehs_find_tour($page->post_name);
        if ($existing && mehs_same_trip($page, $existing)) {
            continue;
        }
        $tours[] = mehs_tour_from_page($page, $section);
    }
    foreach ($tours as $index => $tour) {
        $tours[$index] = mehs_with_page_photo($tour);
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
        if ($page instanceof WP_Post && $page->post_status === "publish" && (!empty($tour["pageId"]) || mehs_same_trip($page, $tour))) {
            return get_permalink($page);
        }
    }
    return add_query_arg("itinerary", sanitize_title($tour["slug"]), home_url("/packages/"));
}

function mehs_trip_label($tour) {
    $count = max(1, (int) ($tour["dayCount"] ?? 1));
    $word = $count === 1 ? "Day" : "Days";
    $name = trim((string) ($tour["title"] ?? ""));
    $name = preg_replace("/^\d+\s+days?\s+/i", "", $name);
    $name = trim(preg_replace("/\s*\|\s*/", " ", (string) $name));
    $name = trim(preg_replace("/\s+/", " ", $name));
    if ($name === "") {
        return $count . " " . $word;
    }
    return $count . " " . $word . " " . $name;
}

function mehs_offer_card($tour) {
    $nights = $tour["dayCount"] > 1 ? " / " . ($tour["dayCount"] - 1) . " nights" : "";
    $name = preg_replace("/^\d+\s+days?\s+/i", "", (string) $tour["title"]);
    $legacy = $tour["dayCount"] . " " . ($tour["dayCount"] === 1 ? "day" : "days") . " | " . trim((string) $name);
    $label = mehs_trip_label($tour);
    $edited = mehs_saved_image($label, "h3");
    if ($edited === "") {
        $edited = mehs_saved_image($legacy, "h3");
    }
    $photo = $edited !== "" ? $edited : (!empty($tour["photo"]) ? $tour["photo"] : $tour["image"]);
    $summary = mehs_plain_start(mehs_saved_line($label, mehs_saved_line($legacy, $tour["summary"], "h3"), "h3"));
    echo '<a class="offer" href="' . esc_url(mehs_tour_url($tour)) . '">';
    echo '<div class="offer-photo">' . mehs_picture($photo, $tour["title"]) . '<span class="offer-tag">' . esc_html($tour["country"]) . '</span></div>';
    echo '<div class="offer-body"><h3>' . esc_html($label) . "</h3>";
    if ($summary !== "") {
        echo "<p>" . esc_html($summary) . "</p>";
    }
    $go = ($tour["section"] ?? "") === "group-safaris" ? "View departure" : "View itinerary";
    echo '<div class="offer-foot"><span>' . esc_html($tour["dayCount"] . " " . ($tour["dayCount"] === 1 ? "day" : "days") . $nights) . '</span><span class="offer-go">' . esc_html($go) . '</span></div></div></a>';
}

function mehs_offer_grid($tours) {
    echo '<div class="offers">';
    foreach ($tours as $tour) {
        mehs_offer_card($tour);
    }
    echo "</div>";
}

function mehs_render_packages_list() {
    $lead = "Open a package for the day-by-day plan, then book it with your travel date. Shared departures are on the group page, and single days are with the excursions.";
    mehs_banner("elephant-savanna.webp", "Safaris", "Packages");
    echo '<div class="wrap page-block"><p class="page-lead">' . esc_html(mehs_saved_line("Packages", $lead, "h1")) . "</p>";
    $groups = [
        "Kenya" => "short-kenya-safaris",
        "Tanzania" => "tanzania-safaris",
        "Uganda" => "uganda-safaris",
        "Rwanda" => "rwanda-safaris",
        "International" => "international-destinations",
    ];
    foreach ($groups as $title => $section) {
        $tours = mehs_tours_in($section);
        if (!$tours) {
            continue;
        }
        echo '<section class="gallery-block"><h2>' . esc_html($title) . "</h2>";
        mehs_offer_grid($tours);
        echo "</section>";
    }
    echo "</div>";
}

function mehs_render_book_form() {
    $trip = isset($_GET["trip"]) ? sanitize_text_field(wp_unslash($_GET["trip"])) : "";
    mehs_banner("safari-vehicle.webp", "Reservation", "Book your safari");
    echo '<div class="wrap page-block"><p class="page-lead">Reservation form. We confirm availability and send the price in writing.</p>';
    echo '<form class="book-form" data-enquiry data-whatsapp-number="' . esc_attr(MEHS_WHATSAPP) . '" data-email-address="' . esc_attr(MEHS_EMAIL) . '"><div class="plan-card"><h2>Personal details</h2><div class="form-grid">';
    echo '<label>Full name<input name="name" autocomplete="name" required></label>';
    echo '<label>Email address<input name="email" type="email" autocomplete="email" required></label>';
    echo '<div class="form-grid form-2"><label>Phone number<input name="phone" autocomplete="tel" required></label><label>WhatsApp number<input name="whatsapp" autocomplete="tel"></label></div>';
    echo '<label>Travel date<input name="date" type="date" required></label>';
    echo '<label>Number of people<input name="adults" type="number" min="1" max="20" value="2" required></label>';
    echo '<label>Package<input name="trip" value="' . esc_attr($trip) . '" placeholder="Name a safari, or leave this open"></label>';
    echo '<label>Anything we should know?<textarea name="note" rows="4"></textarea></label>';
    echo '<p class="fine">We confirm availability and send the price in writing. No payment is taken on this form.</p>';
    echo '<div class="hp"><label>Leave this blank<input name="mehs_leave_blank" tabindex="-1" autocomplete="off"></label></div>';
    echo '<div class="send-row"><button class="btn btn-green" type="submit" name="channel" value="whatsapp">Send on WhatsApp</button>';
    echo '<button class="btn btn-line" type="submit" name="channel" value="email">Send by email</button></div>';
    echo '<div class="result" data-result><p data-status></p></div></div></div></form></div>';
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
        return "Quote";
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
    $tour = mehs_with_page_photo($tour);
    if ($parsed === null) {
        $parsed = mehs_saved_itinerary() ?: ["overview" => "", "note" => "", "table" => [], "days" => [], "highlights" => []];
    }
    $summary = $parsed["overview"] !== "" ? $parsed["overview"] : $tour["summary"];
    $highlights = $parsed["highlights"] ?: $tour["highlights"];
    $days = $parsed["days"] ?: $tour["days"];
    $copy = mehs_saved_copy();
    if ($copy["h1"] !== "") {
        $tour["title"] = $copy["h1"];
    }
    $saved_overview = mehs_saved_line("Package overview", "", "h2");
    if ($saved_overview !== "") {
        $summary = $saved_overview;
    }
    $saved_days = [];
    foreach ($copy["sections"] as $section) {
        if (($section["tag"] ?? "") !== "h3" || ($section["title"] ?? "") === "") {
            continue;
        }
        $saved_days[] = ["title" => $section["title"], "text" => implode("\n", $section["paras"] ?? [])];
    }
    if ($saved_days) {
        $days = $saved_days;
    }
    $from = mehs_lowest_price($parsed["table"]);
    $photo = !empty($tour["photo"]) ? $tour["photo"] : $tour["image"];
    foreach ($copy["sections"] as $section) {
        if (($section["image"] ?? "") !== "") {
            $photo = $section["image"];
            break;
        }
    }
    mehs_banner($photo, $tour["country"], $tour["title"]);
    echo '<div class="wrap package"><div class="package-main"><h2 class="ruled">Package overview</h2>';
    $summary = mehs_plain_start($summary);
    if ($summary !== "") {
        echo "<p>" . esc_html($summary) . "</p>";
    }
    if (($tour["country"] ?? "") === "Kenya") {
        echo "<p>You can start in Nairobi and finish at the coast. Tell us when you book.</p>";
    }
    if ($highlights) {
        echo "<ul>";
        foreach ($highlights as $item) {
            $item = mehs_plain_start($item);
            if ($item === "") {
                continue;
            }
            echo "<li>" . esc_html($item) . "</li>";
        }
        echo "</ul>";
    }
    echo '</div><aside class="price-card book-card"><div class="book-card-head"><p class="kicker">Per person</p>';
    if ($from > 0) {
        echo '<p class="big">' . esc_html("$" . number_format($from)) . "</p>";
    }
    echo "</div><div class=\"book-card-body\">";
    $note = mehs_plain_start($parsed["note"] ?? "");
    if ($note !== "") {
        echo '<p class="rate-note">' . esc_html($note) . "</p>";
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
        echo "<p>The price depends on the month and how many of you travel.</p>";
    }
    echo '<a class="btn btn-clay" href="' . esc_url(mehs_book_url($tour["title"])) . '">Book now</a></div></aside>';
    $editing = function_exists("mehs_elementor_request") && mehs_elementor_request();
    if ($days || !empty($tour["includes"]) || !empty($tour["excludes"]) || $editing) {
        echo '<div class="package-rest">';
        if ($days || $editing) {
            echo '<h2 class="ruled">Itinerary</h2>';
            foreach ($days as $index => $day) {
                $open = $index === 0 ? " open" : "";
                $num = str_pad((string) ($index + 1), 2, "0", STR_PAD_LEFT);
                $label = mehs_plain_start(preg_replace("/^day\s*\d+\s*[:|\-–—]\s*/i", "", (string) ($day["title"] ?? "")));
                echo '<details class="day-acc"' . $open . '><summary><span class="day-num">' . esc_html($num) . '</span><span class="day-label">Day ' . esc_html((string) ($index + 1)) . "</span>";
                if ($label !== "") {
                    echo '<span class="day-title">' . esc_html($label) . "</span>";
                }
                echo "</summary>";
                $lines = preg_split("/\n+/", trim((string) ($day["text"] ?? "")));
                foreach ($lines as $line) {
                    $line = mehs_plain_start($line);
                    if ($line !== "") {
                        echo "<p>" . esc_html($line) . "</p>";
                    }
                }
                echo "</details>";
            }
        }
        if (!empty($tour["includes"]) || !empty($tour["excludes"])) {
            echo '<div class="ie">';
            if (!empty($tour["includes"])) {
                echo '<div><h2 class="ruled">Included</h2><ul class="yes">';
                foreach ($tour["includes"] as $item) {
                    $item = mehs_plain_start($item);
                    if ($item !== "") {
                        echo "<li>" . esc_html($item) . "</li>";
                    }
                }
                echo "</ul></div>";
            }
            if (!empty($tour["excludes"])) {
                echo '<div><h2 class="ruled">Not included</h2><ul class="no">';
                foreach ($tour["excludes"] as $item) {
                    $item = mehs_plain_start($item);
                    if ($item !== "") {
                        echo "<li>" . esc_html($item) . "</li>";
                    }
                }
                echo "</ul></div>";
            }
            echo "</div>";
        }
        echo "</div>";
    }
    echo "</div>";
}

function mehs_page_contact() {
    mehs_banner("safari-vehicle.webp", "Get in touch", "Contact us");
    $countries = ["Kenya", "Tanzania", "Uganda", "Rwanda", "United Kingdom", "United States", "Canada", "Germany", "France", "Spain", "Italy", "Netherlands", "Australia", "India", "South Africa", "United Arab Emirates", "Other"];
    echo '<section><div class="wrap contact-grid"><div><p class="kicker">Reach out</p><h2>We would like to hear from you</h2>';
    echo "<p>Whether you are ready to book or you want a question answered, write or call. The same desk replies by email or WhatsApp.</p><ul class=\"reach\">";
    echo '<li><span aria-hidden="true">⌖</span><div><small>Address</small><strong>Nairobi, Kenya</strong><p class="fine">We meet you at the airport. The point is confirmed with your flight number.</p></div></li>';
    echo '<li><span aria-hidden="true">✉</span><div><small>Email</small><a href="mailto:' . esc_attr(MEHS_EMAIL) . '">' . esc_html(MEHS_EMAIL) . "</a></div></li>";
    echo '<li><span aria-hidden="true">☎</span><div><small>Phone</small><a href="tel:' . esc_attr(MEHS_PHONE_HREF) . '">' . esc_html(MEHS_PHONE) . "</a></div></li>";
    echo mehs_extra_contact_item();
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
    mehs_banner("acacia-sunset.webp", "Before you book", "Frequently asked questions");
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
    $lead = "These are shared-vehicle departures for solo travellers, couples and anyone who wants the Mara without paying for a private car. The migration departures are planned for July to October. A date is only a departure once the vehicle has the guests.";
    mehs_banner("zebra.webp", "Shared departures", "Group safaris");
    echo '<div class="wrap page-block"><p class="page-lead">' . esc_html(mehs_saved_line("Group safaris", $lead, "h1")) . "</p>";
    mehs_offer_grid(mehs_tours_in("group-safaris"));
    echo "</div>";
}

function mehs_page_excursions() {
    $lead = "These sit beside a longer safari or a beach stay. A day in Amboseli, Ol Pejeta or Tsavo from Nairobi is mostly driving, and the notes on those pages say so.";
    mehs_banner("safari-vehicle.webp", "Day trips", "Excursions and day trips");
    echo '<div class="wrap page-block"><p class="page-lead">' . esc_html(mehs_saved_line("Excursions and day trips", $lead, "h1")) . "</p>";
    mehs_offer_grid(mehs_tours_in("excursions"));
    echo "</div>";
}

function mehs_guide_section($heading, $items) {
    $with_photos = isset($items[0][2]);
    echo '<section class="guide-block"><h2 class="ruled">' . esc_html($heading) . '</h2><div class="' . ($with_photos ? "do-grid" : "values") . '">';
    foreach ($items as $item) {
        if ($with_photos) {
            $photo = mehs_saved_image($item[0]);
            $file = $photo !== "" ? $photo : $item[2];
            $caption = mehs_plain_start($item[3] ?? "");
            echo '<article class="do-card"><figure>' . mehs_picture($file, $caption !== "" ? $caption : $item[0]);
            if ($caption !== "") {
                echo '<figcaption class="do-caption">' . esc_html($caption) . "</figcaption>";
            }
            echo '</figure><div><h3>' . esc_html(mehs_plain_start($item[0])) . '</h3><p>' . esc_html(mehs_plain_start($item[1])) . '</p></div></article>';
            continue;
        }
        echo '<article class="value"><span aria-hidden="true">✓</span><div><h3>' . esc_html(mehs_plain_start($item[0])) . '</h3><p>' . esc_html(mehs_plain_start($item[1])) . '</p></div></article>';
    }
    echo "</div></section>";
}

function mehs_page_country($file, $title, $intro, $sections) {
    $saved_title = mehs_saved_copy();
    if ($saved_title["h1"] !== "") {
        $title = $saved_title["h1"];
    }
    $intro = mehs_saved_line($title, $intro, "h1");
    foreach ($sections as $heading => $items) {
        foreach ($items as $index => $item) {
            $sections[$heading][$index][1] = mehs_saved_line($item[0], $item[1], "h3");
        }
    }
    mehs_banner($file, "Destinations", $title);
    echo '<div class="wrap page-block"><p class="page-lead">' . esc_html($intro) . "</p>";
    foreach ($sections as $heading => $items) {
        mehs_guide_section($heading, $items);
    }
    echo '<p class="center"><a class="btn btn-green" href="' . esc_url(home_url("/packages/")) . '">Browse safari packages</a></p></div>';
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
                    ["Wildlife safaris", "Private game drives in the Mara, Amboseli, Tsavo, Samburu, and Nakuru, with the vehicle and guide to yourselves.", "safari-vehicle.webp", "The picture shows a safari vehicle with the roof up, the way a game drive is done."],
                    ["Hot-air balloon safaris", "A dawn flight over the Mara, when the month and the camp can arrange it. It is an extra, named on the quote.", "acacia-sunset.webp", "The picture shows acacia trees at sunset, the light a dawn flight is booked for."],
                    ["Cultural visits", "A Maasai or other community visit that is paid to that community, not a roadside stop.", "giraffe-dusk.webp", "The picture shows a giraffe in the evening light. The visit itself is with the community, not a posed animal stop."],
                    ["Hiking", "Walks where the park allows them, including shorter conservancy walks. We say which days are driving and which are on foot.", "red-savanna.webp", "The picture shows open red savanna. Walks are only where people are allowed on foot."],
                    ["Beach holidays", "Diani, Watamu, or the north coast after the safari. Plan three or four nights. One night is mostly transfers.", "zanzibar.webp", "The picture shows the coast, the kind of beach stay added after the parks."],
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
                "Game drives" => [
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
                    ["Wildlife safaris", "Game drives on the Serengeti plains and in Tarangire, with a crater descent at Ngorongoro when that day is in the plan.", "serengeti.webp", "The picture shows the open Serengeti plain, where those game drives are."],
                    ["Hot-air balloon safaris", "A sunrise balloon over the Serengeti in the months the operators fly. It is quoted on its own line.", "elephant-savanna.webp", "The picture shows elephants on the plain. A balloon flight is a separate line on the quote."],
                    ["Village visits", "A visit with a Maasai community near the crater highlands or a walk in a village that is set up for guests.", "ngorongoro.webp", "The picture shows the Ngorongoro highlands, near the villages and the crater."],
                    ["Hiking", "Kilimanjaro and the shorter highland walks are separate from a game-drive safari. We plan them only with the days they need.", "kilimanjaro.webp", "The picture shows Kilimanjaro. A climb uses its own days, not a game-drive morning."],
                    ["Beach holidays", "Zanzibar after the northern circuit. Three or four nights is the useful length.", "zanzibar.webp", "The picture shows a beach. Zanzibar is the usual coast after the northern parks."],
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
                "Game drives" => [
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
                    ["Gorilla trekking", "A morning in Bwindi or Mgahinga with a permit booked in your name. The trek can be short or most of the day.", "gorilla.webp", "The picture shows a mountain gorilla, the reason for the Bwindi or Mgahinga permit."],
                    ["Chimpanzee tracking", "Kibale is the usual forest for chimpanzees, on a different day from the gorillas.", "rwanda.webp", "The picture shows forested hills, the kind of cover chimpanzee tracking is done in."],
                    ["Boat safaris", "A launch on the Kazinga Channel in Queen Elizabeth, for hippos, buffalo, and elephants along the bank.", "queen.webp", "The picture shows a rift-valley lake, the setting for the Kazinga boat."],
                    ["Game drives", "Queen Elizabeth and Murchison Falls for savannah animals between the forest days.", "lions.webp", "The picture shows lions resting, the savannah days between the forest treks."],
                    ["Community visits", "A stop with a community near the park, paid to that community.", "acacia-sunset.webp", "The picture shows savanna trees at sunset, near the parks where those visits are arranged."],
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
                "Game drives" => [
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
                    ["Gorilla trekking", "A permit in Volcanoes National Park. The group size on the mountain is small, and the start is early.", "gorilla.webp", "The picture shows a mountain gorilla in forest, the Volcanoes permit."],
                    ["Golden monkey tracking", "A shorter forest walk in the same park, often the day before or after the gorillas.", "rwanda.webp", "The picture shows green hills, the forest used for the golden monkey walk."],
                    ["Canopy walk", "The suspension bridge in Nyungwe, with colobus and a chance of chimpanzees on a separate tracking permit.", "ngorongoro.webp", "The picture shows highland forest, close to the look of the Nyungwe canopy."],
                    ["Game drives", "Akagera for savannah animals on the eastern lakes.", "zebra.webp", "The picture shows plains animals, the kind Akagera is visited for."],
                    ["Kigali and Lake Kivu", "A city day, then the lake if you want a night off the parks.", "flamingo.webp", "The picture shows a lake shore. Lake Kivu is the rest night, not a game park."],
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
                "Game drives" => [
                    ["Mountain gorillas", "One hour with the family. Permits are limited, so the month has to be agreed before we request them."],
                    ["Big Five in Akagera", "Lion, leopard, elephant, buffalo, and rhino are the reason for the savannah day. Sightings are wildlife, not a guarantee of all five in one drive."],
                    ["Chimpanzees and colobus", "Nyungwe is the forest for both. Chimpanzee tracking uses its own permit."],
                    ["Golden monkeys", "Bamboo forest in Volcanoes, a different morning from the gorillas."],
                ],
            ],
        ],
    ];
}

function mehs_page_destinations() {
    mehs_banner("elephant-savanna.webp", "Where we travel", "Destinations");
    echo '<div class="wrap page-block"><p class="page-lead">Kenya, Tanzania, Uganda and Rwanda, planned from Nairobi. Open a country for what to do there, then choose a package when the route is clear.</p><div class="offers">';
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
        echo '<a class="offer" href="' . esc_url($url) . '"><div class="offer-photo">' . mehs_image($place[3], $place[1]) . '</div>';
        echo '<div class="offer-body"><h3>' . esc_html($place[1]) . "</h3><p>" . esc_html($place[2]) . '</p><div class="offer-foot"><span>Guide</span><span class="offer-go">Open</span></div></div></a>';
    }
    echo "</div></div>";
}

function mehs_page_place_index($file, $kicker, $title, $lead) {
    mehs_banner($file, $kicker, $title);
    echo '<div class="wrap page-block"><p class="page-lead">' . esc_html($lead) . '</p><div class="offers">';
    $pages = get_pages([
        "parent" => get_the_ID(),
        "post_status" => "publish",
        "sort_column" => "menu_order,post_title",
    ]);
    foreach ($pages as $page) {
        $image = get_the_post_thumbnail_url($page, "mehs-card");
        echo '<a class="offer" href="' . esc_url(get_permalink($page)) . '"><div class="offer-photo">';
        if ($image) {
            echo '<img src="' . esc_url($image) . '" alt="' . esc_attr(get_the_title($page)) . '">';
        } else {
            echo mehs_image("safari-vehicle.webp", get_the_title($page));
        }
        echo '</div><div class="offer-body"><h3>' . esc_html(get_the_title($page)) . '</h3><div class="offer-foot"><span>Destination</span><span class="offer-go">Open</span></div></div></a>';
    }
    echo "</div></div>";
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
    if ($slug === "destinations") {
        mehs_page_destinations();
        return true;
    }
    if ($slug === "local-destinations") {
        mehs_page_place_index("acacia-sunset.webp", "Kenya parks", "Local destinations", "The parks and reserves these safaris are built around.");
        return true;
    }
    if ($slug === "international-destinations") {
        mehs_banner("zanzibar.webp", "Further afield", "International destinations");
        echo '<div class="wrap page-block"><p class="page-lead">Trips outside East Africa are listed with the other day-by-day plans.</p>';
        echo '<p class="center"><a class="btn btn-green" href="' . esc_url(home_url("/packages/")) . '">View packages</a></p></div>';
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
    $current = get_post();
    if ($tour && (!$current instanceof WP_Post || mehs_same_trip($current, $tour))) {
        mehs_render_package($tour);
        return true;
    }
    $parent_id = (int) get_post_field("post_parent");
    while ($parent_id > 0) {
        $parent = get_post($parent_id);
        if (!$parent instanceof WP_Post) {
            break;
        }
        $sections = ["group-safaris", "short-kenya-safaris", "tanzania-safaris", "uganda-safaris", "rwanda-safaris", "excursions", "international-destinations"];
        if (in_array($parent->post_name, $sections, true)) {
            mehs_render_package(mehs_tour_from_page(get_post(), $parent->post_name));
            return true;
        }
        $parent_id = (int) $parent->post_parent;
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
        echo "<p>" . esc_html(mehs_plain_start(wp_trim_words(wp_strip_all_tags(get_the_excerpt($post)), 32))) . "</p></div></article>";
    }
    echo "</div></div>";
}
