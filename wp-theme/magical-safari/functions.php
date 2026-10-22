<?php
/**
 * Magical Safari theme.
 */

define("MEHS_WHATSAPP", "254719087388");
define("MEHS_EMAIL", "info@magicaleastafricaheritagesafaris.com");
define("MEHS_PHONE", "+254 722 326 845");
define("MEHS_PHONE_HREF", "+254722326845");

function mehs_extra_lines() {
    return [
        ["+254 719 087 388", "+254719087388", "254719087388"],
        ["+254 755 823 337", "+254755823337", "254755823337"],
    ];
}

function mehs_extra_contact_item() {
    $html = '<li><span aria-hidden="true">☎</span><div><small>Telephone, WhatsApp, emergency</small>';
    foreach (mehs_extra_lines() as $line) {
        $html .= '<p><a href="tel:' . esc_attr($line[1]) . '">' . esc_html($line[0]) . '</a> · <a href="https://wa.me/' . esc_attr($line[2]) . '" target="_blank" rel="noopener">WhatsApp</a></p>';
    }
    return $html . "</div></li>";
}

require get_template_directory() . "/inc/design.php";
require get_template_directory() . "/inc/editor-seed.php";

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

function mehs_view_id() {
    if (isset($_GET["elementor-preview"])) {
        $preview_id = (int) $_GET["elementor-preview"];
        if ($preview_id > 0) {
            return $preview_id;
        }
    }
    $queried = (int) get_queried_object_id();
    if ($queried > 0) {
        return $queried;
    }
    return (int) get_the_ID();
}

function mehs_prepare_view_post() {
    if (!mehs_elementor_request()) {
        return;
    }
    $post = get_post(mehs_view_id());
    if (!$post instanceof WP_Post) {
        return;
    }
    $GLOBALS["post"] = $post;
    setup_postdata($post);
}
add_action("wp", "mehs_prepare_view_post", 1);

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

function mehs_mark_elementor_public($post_id) {
    $post_id = (int) $post_id;
    if ($post_id < 1 || !empty($GLOBALS["mehs_seeding"]) || wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }
    update_post_meta($post_id, "mehs_elementor_public", "1");
    clean_post_cache($post_id);
    do_action("litespeed_purge_post", $post_id);
    $url = get_permalink($post_id);
    if ($url) {
        do_action("litespeed_purge_url", $url);
    }
}

function mehs_elementor_is_public($post_id = 0) {
    $post_id = $post_id ? (int) $post_id : (int) get_the_ID();
    return $post_id > 0 && get_post_meta($post_id, "mehs_elementor_public", true) === "1";
}

function mehs_note_elementor_save($document) {
    $post_id = 0;
    if (is_object($document) && method_exists($document, "get_main_id")) {
        $post_id = (int) $document->get_main_id();
    }
    mehs_mark_elementor_public($post_id);
}
add_action("elementor/document/after_save", "mehs_note_elementor_save");

function mehs_note_elementor_ajax_save($post_id) {
    if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
        return;
    }
    $action = isset($_REQUEST["action"]) ? sanitize_key(wp_unslash($_REQUEST["action"])) : "";
    if ($action === "elementor_ajax") {
        mehs_mark_elementor_public($post_id);
    }
}
add_action("save_post", "mehs_note_elementor_ajax_save", 20);

function mehs_note_elementor_meta($meta_id, $post_id, $meta_key) {
    if ($meta_key !== "_elementor_data") {
        return;
    }
    mehs_mark_elementor_public($post_id);
}
add_action("added_post_meta", "mehs_note_elementor_meta", 10, 3);
add_action("updated_post_meta", "mehs_note_elementor_meta", 10, 3);

function mehs_elementor_image_url($settings) {
    $image = isset($settings["image"]) && is_array($settings["image"]) ? $settings["image"] : [];
    if (!empty($image["url"])) {
        return $image["url"];
    }
    $id = (int) ($image["id"] ?? 0);
    if ($id > 0) {
        $url = wp_get_attachment_image_url($id, "large");
        if ($url) {
            return $url;
        }
    }
    return "";
}

function mehs_page_photo($post_id) {
    $post_id = (int) $post_id;
    $thumb = get_the_post_thumbnail_url($post_id, "large");
    if ($thumb) {
        return $thumb;
    }
    foreach (mehs_elementor_blocks($post_id) as $block) {
        if ($block["kind"] === "img" && $block["url"] !== "") {
            return $block["url"];
        }
    }
    return "";
}

function mehs_elementor_blocks($post_id) {
    $raw = get_post_meta($post_id, "_elementor_data", true);
    $data = is_array($raw) ? $raw : json_decode((string) $raw, true);
    if (!is_array($data)) {
        return [];
    }
    $blocks = [];
    $walk = function ($elements) use (&$walk, &$blocks) {
        foreach ($elements as $element) {
            if (!is_array($element)) {
                continue;
            }
            $type = $element["widgetType"] ?? "";
            $settings = isset($element["settings"]) && is_array($element["settings"]) ? $element["settings"] : [];
            if ($type === "heading") {
                $text = trim(wp_strip_all_tags($settings["title"] ?? ""));
                if ($text !== "") {
                    $blocks[] = ["kind" => "h", "tag" => $settings["header_size"] ?? "h2", "text" => $text];
                }
            } elseif ($type === "text-editor") {
                $text = trim(preg_replace("/\s+/", " ", wp_strip_all_tags($settings["editor"] ?? "")));
                if ($text !== "") {
                    $blocks[] = ["kind" => "p", "text" => $text];
                }
            } elseif ($type === "image" || $type === "image-box") {
                $url = mehs_elementor_image_url($settings);
                if ($url !== "") {
                    $blocks[] = ["kind" => "img", "url" => $url];
                }
            }
            if (!empty($element["elements"]) && is_array($element["elements"])) {
                $walk($element["elements"]);
            }
        }
    };
    $walk($data);
    return $blocks;
}

function mehs_saved_copy($post_id = 0) {
    static $cache = [];
    $empty = ["h1" => "", "sections" => []];
    $post_id = $post_id ? (int) $post_id : (int) get_the_ID();
    if (isset($cache[$post_id])) {
        return $cache[$post_id];
    }
    $use_saved = mehs_elementor_is_public($post_id) || (mehs_elementor_request() && empty($GLOBALS["mehs_seeding"]));
    if (!$use_saved) {
        $cache[$post_id] = $empty;
        return $empty;
    }
    $current = null;
    $pending_image = "";
    foreach (mehs_elementor_blocks($post_id) as $block) {
        if ($block["kind"] === "img") {
            if ($current && ($current["image"] ?? "") === "") {
                $current["image"] = $block["url"];
            } else {
                $pending_image = $block["url"];
            }
            continue;
        }
        if ($block["kind"] === "h") {
            if ($current) {
                $empty["sections"][] = $current;
            }
            if ($block["tag"] === "h1" && $empty["h1"] === "") {
                $empty["h1"] = $block["text"];
            }
            $current = ["tag" => $block["tag"], "title" => $block["text"], "paras" => [], "image" => $pending_image];
            $pending_image = "";
            continue;
        }
        if (!$current) {
            $current = ["tag" => "p", "title" => "", "paras" => [$block["text"]], "image" => $pending_image];
            $pending_image = "";
            continue;
        }
        $current["paras"][] = $block["text"];
    }
    if ($current) {
        $empty["sections"][] = $current;
    }
    $cache[$post_id] = $empty;
    return $empty;
}

function mehs_saved_paragraphs($title, $tag = "") {
    $copy = mehs_saved_copy();
    $wanted = strtolower(trim(preg_replace("/\s+/", " ", wp_strip_all_tags($title))));
    foreach ($copy["sections"] as $section) {
        $label = strtolower(trim(preg_replace("/\s+/", " ", $section["title"])));
        if ($label !== $wanted) {
            continue;
        }
        if ($tag !== "" && $section["tag"] !== $tag) {
            continue;
        }
        return $section["paras"];
    }
    return [];
}

function mehs_saved_image($title, $tag = "h3") {
    $copy = mehs_saved_copy();
    $wanted = strtolower(trim(preg_replace("/\s+/", " ", wp_strip_all_tags($title))));
    foreach ($copy["sections"] as $section) {
        $label = strtolower(trim(preg_replace("/\s+/", " ", $section["title"])));
        if ($label !== $wanted || $section["tag"] !== $tag) {
            continue;
        }
        return $section["image"] ?? "";
    }
    return "";
}

function mehs_plain_start($text) {
    $text = trim(preg_replace("/\s+/u", " ", wp_strip_all_tags((string) $text)));
    $text = preg_replace('/^[\s,;:.!?"\'“”‘’`´\-–—·•\/\\\\()\[\]]+/u', "", $text);
    $text = trim((string) $text);
    if ($text === "") {
        return "";
    }
    if (preg_match("/^\p{Ll}/u", $text)) {
        $text = mb_strtoupper(mb_substr($text, 0, 1, "UTF-8"), "UTF-8") . mb_substr($text, 1, null, "UTF-8");
    }
    return $text;
}

function mehs_saved_line($title, $fallback, $tag = "") {
    $paras = mehs_saved_paragraphs($title, $tag);
    $best = "";
    foreach ($paras as $paragraph) {
        $raw = trim((string) $paragraph);
        if ($raw === "" || !preg_match('/^[\p{L}\p{N}]/u', $raw)) {
            continue;
        }
        $clean = mehs_plain_start($raw);
        if (strlen($clean) > strlen($best)) {
            $best = $clean;
        }
    }
    $fallback = mehs_plain_start($fallback);
    return $best !== "" ? $best : $fallback;
}

function mehs_elementor_background_url($settings) {
    $image = $settings["background_image"] ?? null;
    if (is_string($image) && $image !== "") {
        return $image;
    }
    if (!is_array($image)) {
        return "";
    }
    if (!empty($image["url"])) {
        return $image["url"];
    }
    $id = (int) ($image["id"] ?? 0);
    if ($id > 0) {
        $url = wp_get_attachment_image_url($id, "large");
        if ($url) {
            return $url;
        }
    }
    return "";
}

function mehs_saved_banner_image($post_id = 0) {
    $post_id = $post_id ? (int) $post_id : (int) get_queried_object_id();
    if (!mehs_elementor_is_public($post_id)) {
        return "";
    }
    $raw = get_post_meta($post_id, "_elementor_data", true);
    $data = is_array($raw) ? $raw : json_decode((string) $raw, true);
    if (!is_array($data)) {
        return "";
    }
    foreach ($data as $element) {
        if (!is_array($element)) {
            continue;
        }
        $settings = isset($element["settings"]) && is_array($element["settings"]) ? $element["settings"] : [];
        $url = mehs_elementor_background_url($settings);
        if ($url !== "") {
            return $url;
        }
    }
    return "";
}

function mehs_elementor_live_html($post_id) {
    $post_id = (int) $post_id;
    $raw = get_post_meta($post_id, "_elementor_data", true);
    $data = is_array($raw) ? $raw : json_decode((string) $raw, true);
    if (!is_array($data)) {
        return "";
    }
    $html = "";
    $walk = function ($elements) use (&$walk, &$html) {
        foreach ($elements as $element) {
            if (!is_array($element)) {
                continue;
            }
            if (($element["widgetType"] ?? "") === "html") {
                $chunk = $element["settings"]["html"] ?? "";
                if (is_string($chunk) && strpos($chunk, 'data-mehs-live="1"') !== false) {
                    $html .= $chunk;
                }
            }
            if (!empty($element["elements"]) && is_array($element["elements"])) {
                $walk($element["elements"]);
            }
        }
    };
    $walk($data);
    return $html;
}

function mehs_published_live_html($post_id = 0) {
    $post_id = $post_id ? (int) $post_id : (int) get_the_ID();
    if (!mehs_elementor_is_public($post_id)) {
        return "";
    }
    return mehs_elementor_live_html($post_id);
}

function mehs_capture_public($post_id) {
    $post = get_post($post_id);
    if (!$post instanceof WP_Post) {
        return "";
    }
    $previous = $GLOBALS["post"] ?? null;
    $GLOBALS["post"] = $post;
    setup_postdata($post);
    $slug = $post->post_name;
    $front = (int) get_option("page_on_front");
    ob_start();
    $matched = true;
    if ((int) $post->ID === $front || $slug === "east-africa-safaris") {
        mehs_render_front();
    } elseif ($slug === "about-us") {
        mehs_render_about();
    } elseif ($slug === "packages") {
        mehs_render_packages_list();
    } elseif ($slug === "book") {
        mehs_render_book_form();
    } elseif ($slug === "gallery") {
        mehs_gallery_wall();
    } elseif (!mehs_render_known_page()) {
        $photo = has_post_thumbnail($post) ? get_the_post_thumbnail_url($post, "large") : "";
        mehs_banner($photo !== "" ? $photo : "safari-vehicle.webp", "", get_the_title($post));
        echo '<div class="wrap page-block entry">';
        echo apply_filters("the_content", $post->post_content);
        echo "</div>";
    }
    $html = ob_get_clean();
    if ($previous instanceof WP_Post) {
        $GLOBALS["post"] = $previous;
        setup_postdata($previous);
    } else {
        wp_reset_postdata();
    }
    if (!$matched || trim(wp_strip_all_tags($html)) === "") {
        return "";
    }
    return '<div data-mehs-live="1">' . $html . "</div>";
}

function mehs_use_page_content() {
    return is_singular() && mehs_elementor_request();
}

function mehs_listing_page($post_id = 0) {
    $post_id = $post_id ? (int) $post_id : (int) get_queried_object_id();
    if ($post_id > 0 && $post_id === (int) get_option("page_on_front")) {
        return true;
    }
    $post = get_post($post_id);
    if (!$post instanceof WP_Post) {
        return false;
    }
    return in_array($post->post_name, ["group-safaris", "excursions", "packages", "short-kenya-safaris", "tanzania-safaris", "uganda-safaris", "rwanda-safaris", "international-destinations", "destinations"], true);
}

function mehs_strip_pasted_styles($html) {
    if (!is_string($html) || $html === "") {
        return $html;
    }
    $clean = preg_replace_callback('/<(span|font|p|div|li|h1|h2|h3|h4|strong|em|b|i)\b([^>]*)>/i', function ($match) {
        $tag = strtolower($match[1]);
        $attrs = $match[2];
        if ($tag === "div" && preg_match('/\bclass="/i', $attrs) && !preg_match('/\bMso/i', $attrs)) {
            return $match[0];
        }
        $attrs = preg_replace("/\\sstyle=([\"']).*?\\1/i", "", $attrs);
        if ($tag === "span" || $tag === "font") {
            $attrs = preg_replace_callback('/\sclass=(["\'])(.*?)\1/i', function ($class) {
                $keep = [];
                foreach (preg_split('/\s+/', trim($class[2])) as $name) {
                    if ($name === "" || preg_match('/^Mso/i', $name)) {
                        continue;
                    }
                    $keep[] = $name;
                }
                return $keep ? ' class="' . implode(" ", $keep) . '"' : "";
            }, $attrs);
        }
        return "<" . $match[1] . $attrs . ">";
    }, $html);
    if (!is_string($clean)) {
        return $html;
    }
    $clean = preg_replace('#</?font\b[^>]*>#i', "", $clean);
    if (!is_string($clean)) {
        return $html;
    }
    $clean = str_replace(
        ["+254 717087388", "+254717087388", "717087388"],
        ["+254 719 087 388", "+254719087388", "719087388"],
        $clean
    );
    $clean = preg_replace('/<h[1-6]\b[^>]*>\s*<\/h[1-6]>/i', "", $clean);
    $clean = preg_replace('/<p\b(?![^>]*\bdata-status\b)[^>]*>(?:\s|&nbsp;|&#0*160;|&#x0*a0;|<br\s*\/?>)*<\/p>/iu', "", $clean);
    if (!is_string($clean)) {
        return $html;
    }
    return mehs_restore_button_classes($clean);
}

function mehs_class_attr($attrs, $class) {
    if (preg_match('/\bclass=(["\'])([^"\']*)\1/i', $attrs, $found)) {
        $names = preg_split('/\s+/', trim($found[2]));
        if (!in_array($class, $names, true)) {
            $names[] = $class;
        }
        $names = array_values(array_filter($names, function ($name) {
            return $name !== "";
        }));
        return preg_replace('/\s*\bclass=(["\'])[^"\']*\1/i', ' class="' . implode(" ", $names) . '"', $attrs, 1);
    }
    return $attrs . ' class="' . $class . '"';
}

function mehs_restore_button_classes($html) {
    $html = preg_replace_callback('/(<div class="offer-foot"[^>]*>)(.*?)(<\/div>)/is', function ($match) {
        $last = -1;
        $count = 0;
        $inner = preg_replace_callback('/<span\b([^>]*)>(.*?)<\/span>/is', function ($span) use (&$last, &$count) {
            $last = $count;
            $count++;
            return $span[0];
        }, $match[2]);
        if (!is_string($inner) || $last < 0) {
            return $match[0];
        }
        $index = 0;
        $inner = preg_replace_callback('/<span\b([^>]*)>(.*?)<\/span>/is', function ($span) use (&$index, $last) {
            $current = $index;
            $index++;
            if ($current !== $last) {
                return $span[0];
            }
            return "<span" . mehs_class_attr($span[1], "offer-go") . ">" . $span[2] . "</span>";
        }, $inner);
        return is_string($inner) ? $match[1] . $inner . $match[3] : $match[0];
    }, $html);
    $html = preg_replace_callback('/(<div class="offer-photo"[^>]*>)(.*?)(<\/div>)/is', function ($match) {
        $inner = preg_replace_callback('/<span\b([^>]*)>(.*?)<\/span>/is', function ($span) {
            $text = trim(html_entity_decode(strip_tags($span[2]), ENT_QUOTES));
            if ($text === "") {
                return $span[0];
            }
            return "<span" . mehs_class_attr($span[1], "offer-tag") . ">" . $span[2] . "</span>";
        }, $match[2]);
        return is_string($inner) ? $match[1] . $inner . $match[3] : $match[0];
    }, $html);
    $html = preg_replace_callback('/(<summary\b[^>]*>)(.*?)(<\/summary>)/is', function ($match) {
        $index = 0;
        $inner = preg_replace_callback('/<span\b([^>]*)>(.*?)<\/span>/is', function ($span) use (&$index) {
            $index++;
            $text = trim(html_entity_decode(strip_tags($span[2]), ENT_QUOTES));
            $class = "";
            if ($index === 1 && preg_match('/^\d+$/', $text)) {
                $class = "day-num";
            } elseif (preg_match('/^Day\s+\d+$/i', $text)) {
                $class = "day-label";
            } elseif ($text !== "") {
                $class = "day-title";
            }
            if ($class === "") {
                return $span[0];
            }
            return "<span" . mehs_class_attr($span[1], $class) . ">" . $span[2] . "</span>";
        }, $match[2]);
        return is_string($inner) ? $match[1] . $inner . $match[3] : $match[0];
    }, $html);
    return is_string($html) ? $html : "";
}

function mehs_saved_page_html($post_id = 0) {
    $post_id = $post_id ? (int) $post_id : (int) get_the_ID();
    $html = get_post_meta($post_id, "mehs_live_html", true);
    if (!is_string($html) || strpos($html, 'data-mehs-live="1"') === false) {
        return "";
    }
    $html = str_replace(
        [
            ">Package price from<",
            ">On request<",
            ">The written quote confirms the price for your dates and group size.<",
            ">Tell us the dates and the number of travellers. We send the price.<",
            ">If you want to start in Nairobi and finish at the coast, say so when you book. We will write it into the quote.<",
            ">Day-by-day itinerary<",
            ">Day by day<",
            ">What's included<",
            ">What&#039;s included<",
            ">What's excluded<",
            ">What&#039;s excluded<",
        ],
        [
            ">Per person<",
            ">Quote<",
            ">The price depends on the month and how many of you travel.<",
            ">The price depends on the month and how many of you travel.<",
            ">You can start in Nairobi and finish at the coast. Tell us when you book.<",
            ">Itinerary<",
            ">Itinerary<",
            ">Included<",
            ">Included<",
            ">Not included<",
            ">Not included<",
        ],
        $html
    );
    $replaced = preg_replace(
        '/<p class="kicker">(?:Price|Per person)<\/p>\s*<p class="big">(?:Ask us|On request|Quote)<\/p>\s*<p>Per person<\/p>/i',
        '<p class="kicker">Per person</p>',
        $html
    );
    if (is_string($replaced)) {
        $html = $replaced;
    }
    $html = str_replace(">Ask us<", ">Quote<", $html);
    $html = preg_replace('#<aside\b[^>]*\bmehs-edit-side\b[^>]*>.*?</aside>#is', "", $html);
    $html = preg_replace('#<div class="mehs-hero-frames"[^>]*>.*?</div>#is', "", $html);
    return mehs_strip_pasted_styles(is_string($html) ? $html : "");
}

function mehs_text_key($value) {
    $hash = 2166136261;
    $length = strlen($value);
    for ($index = 0; $index < $length; $index++) {
        $hash ^= ord($value[$index]);
        $hash = mehs_imul($hash, 16777619);
    }
    return str_pad(dechex($hash & 0xFFFFFFFF), 8, "0", STR_PAD_LEFT);
}

function mehs_imul($left, $right) {
    $left &= 0xFFFFFFFF;
    $right &= 0xFFFFFFFF;
    $low = (($left & 0xFFFF) * ($right & 0xFFFF)) & 0xFFFFFFFF;
    $mid = (($left & 0xFFFF) * ($right >> 16) + ($left >> 16) * ($right & 0xFFFF)) & 0xFFFFFFFF;
    return ($low + (($mid << 16) & 0xFFFFFFFF)) & 0xFFFFFFFF;
}

function mehs_prepare_main($main, $post_id) {
    $edits = get_post_meta($post_id, "mehs_text_edits", true);
    if (!is_array($edits)) {
        $edits = [];
    }
    if (!mehs_listing_page($post_id)) {
        $html_time = (int) get_post_meta($post_id, "mehs_live_saved_at", true);
        $edit_time = (int) get_post_meta($post_id, "mehs_text_edits_at", true);
        if ($html_time > $edit_time) {
            $edits = [];
        }
    }
    $document = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $loaded = $document->loadHTML('<?xml encoding="utf-8"><html><body>' . $main . "</body></html>");
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    if (!$loaded) {
        return $main;
    }
    $node = $document->getElementsByTagName("main")->item(0);
    if (!$node instanceof DOMElement) {
        return $main;
    }
    $seen = [];
    $heading = "";
    mehs_mark_editable($node, $seen, $heading, $edits);
    $saved = $document->saveHTML($node);
    return is_string($saved) && $saved !== "" ? $saved : $main;
}

function mehs_mark_editable($node, &$seen, &$heading, $edits) {
    if (!$node instanceof DOMElement) {
        return;
    }
    $tag = strtolower($node->nodeName);
    $class = " " . $node->getAttribute("class") . " ";
    if ($tag === "form" || strpos($class, " mehs-elementor-sink ") !== false || strpos($class, " mehs-edit-side ") !== false || strpos($class, " mehs-edit-tools ") !== false || strpos($class, " mehs-new-trip ") !== false) {
        return;
    }
    if ($node->hasAttribute("data-mehs-add") || $node->hasAttribute("data-mehs-remove") || $node->hasAttribute("data-mehs-create")) {
        return;
    }
    if ($tag === "img") {
        $src = $node->getAttribute("src");
        $key = strtolower($node->getAttribute("data-edit"));
        if ($key === "") {
            $file = strtolower(basename((string) strtok($src, "?")));
            $bucket = "img|" . $file;
            $seen[$bucket] = ($seen[$bucket] ?? 0) + 1;
            $key = mehs_text_key($bucket . "|" . $seen[$bucket]);
            $node->setAttribute("data-edit", $key);
        }
        if (isset($edits[$key]) && is_string($edits[$key]) && preg_match("#^https?://#i", $edits[$key])) {
            $node->setAttribute("src", esc_url($edits[$key]));
        }
        return;
    }
    $children = [];
    foreach ($node->childNodes as $child) {
        if ($child instanceof DOMElement) {
            $children[] = $child;
        }
    }
    $leaf_tags = ["h1", "h2", "h3", "h4", "h5", "h6", "p", "li", "td", "th", "figcaption", "span", "a", "button", "strong", "em", "b", "i", "small"];
    if (!$children && in_array($tag, $leaf_tags, true) && strpos($class, " day-num ") === false) {
        $text = trim(preg_replace("/\s+/", " ", $node->textContent));
        if ($text === "") {
            return;
        }
        if (in_array($tag, ["h1", "h2", "h3", "h4", "h5", "h6"], true)) {
            $heading = $text;
        }
        $key = strtolower($node->getAttribute("data-edit"));
        if ($key === "") {
            $bucket = $tag . "|" . $heading . "|" . $text;
            $seen[$bucket] = ($seen[$bucket] ?? 0) + 1;
            $key = mehs_text_key($bucket . "|" . $seen[$bucket]);
            $node->setAttribute("data-edit", $key);
        }
        if (isset($edits[$key]) && is_string($edits[$key])) {
            $node->textContent = $edits[$key];
        }
        return;
    }
    foreach ($children as $child) {
        mehs_mark_editable($child, $seen, $heading, $edits);
    }
}

function mehs_filter_editable_page($html) {
    try {
        return mehs_filter_editable_page_inner($html);
    } catch (Throwable $error) {
        return $html;
    }
}

function mehs_filter_editable_page_inner($html) {
    if (!is_string($html) || stripos($html, "<main") === false) {
        return $html;
    }
    $post_id = mehs_view_id();
    if ($post_id < 1) {
        return $html;
    }
    $start = stripos($html, "<main");
    $end = strripos($html, "</main>");
    if ($start === false || $end === false || $end <= $start) {
        return $html;
    }
    $end += strlen("</main>");
    $main = substr($html, $start, $end - $start);
    $next = mehs_prepare_main($main, $post_id);
    return substr($html, 0, $start) . $next . substr($html, $end);
}

function mehs_public_nocache() {
    if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
        return;
    }
    if (!is_singular() && !is_front_page()) {
        return;
    }
    if (headers_sent()) {
        return;
    }
    header("Cache-Control: no-cache, must-revalidate, max-age=0");
    header("X-LiteSpeed-Cache-Control: no-cache");
    header("Pragma: no-cache");
}
add_action("send_headers", "mehs_public_nocache");

function mehs_start_edit_buffer() {
    if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
        return;
    }
    if (!is_singular("page") && !is_front_page()) {
        return;
    }
    ob_start("mehs_filter_editable_page");
}
add_action("template_redirect", "mehs_start_edit_buffer", 0);

function mehs_url_key($url) {
    $url = html_entity_decode((string) $url, ENT_QUOTES, "UTF-8");
    $path = wp_parse_url($url, PHP_URL_PATH);
    return trim(strtolower(is_string($path) && $path !== "" ? $path : $url), "/");
}

function mehs_listing_groups($post_id) {
    $post = get_post($post_id);
    if (!$post instanceof WP_Post) {
        return [];
    }
    if ((int) $post->ID === (int) get_option("page_on_front")) {
        return [];
    }
    if ($post->post_name === "packages") {
        return [
            ["tours" => mehs_tours_in("short-kenya-safaris")],
            ["tours" => mehs_tours_in("tanzania-safaris")],
            ["tours" => mehs_tours_in("uganda-safaris")],
            ["tours" => mehs_tours_in("rwanda-safaris")],
            ["tours" => mehs_tours_in("international-destinations")],
        ];
    }
    $sections = [
        "group-safaris" => "group-safaris",
        "excursions" => "excursions",
        "short-kenya-safaris" => "short-kenya-safaris",
        "tanzania-safaris" => "tanzania-safaris",
        "uganda-safaris" => "uganda-safaris",
        "rwanda-safaris" => "rwanda-safaris",
        "international-destinations" => "international-destinations",
    ];
    if (!isset($sections[$post->post_name])) {
        return [];
    }
    return [["tours" => mehs_tours_in($sections[$post->post_name])]];
}

function mehs_append_html($document, $parent, $html) {
    $holder = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $holder->loadHTML('<?xml encoding="utf-8"><html><body><div id="mehs-add">' . $html . "</div></body></html>");
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    $box = $holder->getElementById("mehs-add");
    if (!$box) {
        return;
    }
    foreach (iterator_to_array($box->childNodes) as $child) {
        $parent->appendChild($document->importNode($child, true));
    }
}

function mehs_with_new_offers($html, $post_id) {
    $groups = mehs_listing_groups($post_id);
    if (!$groups) {
        return $html;
    }
    $document = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $loaded = $document->loadHTML('<?xml encoding="utf-8"><html><body>' . $html . "</body></html>");
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    if (!$loaded) {
        return $html;
    }
    $xpath = new DOMXPath($document);
    $offers_list = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' offers ')]");
    if (!$offers_list || $offers_list->length < 1) {
        return $html;
    }
    $present = [];
    foreach ($xpath->query("//a[@href]") as $link) {
        if ($link instanceof DOMElement) {
            $present[mehs_url_key($link->getAttribute("href"))] = true;
        }
    }
    foreach ($groups as $index => $group) {
        $offers = $offers_list->item(min($index, $offers_list->length - 1));
        if (!$offers instanceof DOMElement) {
            continue;
        }
        foreach ($group["tours"] as $tour) {
            $key = mehs_url_key(mehs_tour_url($tour));
            if ($key === "" || isset($present[$key])) {
                continue;
            }
            $present[$key] = true;
            ob_start();
            mehs_offer_card($tour);
            mehs_append_html($document, $offers, ob_get_clean());
        }
    }
    $body = $document->getElementsByTagName("body")->item(0);
    $out = "";
    if ($body) {
        foreach ($body->childNodes as $child) {
            $out .= $document->saveHTML($child);
        }
    }
    return $out !== "" ? $out : $html;
}

function mehs_insert_after_section($html, $class, $block) {
    $pos = strpos($html, 'class="' . $class . '"');
    if ($pos === false) {
        return $html . $block;
    }
    $start = strrpos(substr($html, 0, $pos), "<section");
    if ($start === false) {
        return $html . $block;
    }
    $close = strpos($html, "</section>", $start);
    if ($close === false) {
        return $html . $block;
    }
    $close += strlen("</section>");
    return substr($html, 0, $close) . $block . substr($html, $close);
}

function mehs_home_saved_sections($html) {
    $sections = [
        ["home-destinations", "mehs_home_destinations", "banner hero"],
        ["home-why", "mehs_home_why", "home-destinations"],
        ["home-welcome", "mehs_home_welcome", "home-featured"],
        ["home-services", "mehs_home_services", "home-welcome"],
        ["home-about", "mehs_home_about", "home-services"],
        ["home-reviews", "mehs_home_reviews", "home-about"],
        ["home-stories", "mehs_home_stories", "home-reviews"],
        ["home-enquiry", "mehs_home_enquiry", "home-stories"],
    ];
    foreach ($sections as $section) {
        if (strpos($html, $section[0]) !== false) {
            continue;
        }
        ob_start();
        call_user_func($section[1]);
        $html = mehs_insert_after_section($html, $section[2], ob_get_clean());
    }
    return $html;
}

function mehs_about_saved_sections($html) {
    $blocks = [
        ["about-extra", "mehs_about_extra"],
        ["about-trust", "mehs_about_trust"],
        ["about-partners", "mehs_about_partners"],
    ];
    foreach ($blocks as $block) {
        if (strpos($html, $block[0]) !== false) {
            continue;
        }
        ob_start();
        call_user_func($block[1]);
        $extra = ob_get_clean();
        $cta = strpos($html, 'class="cta-band"');
        if ($cta !== false) {
            $start = strrpos(substr($html, 0, $cta), "<section");
            if ($start !== false) {
                $html = substr($html, 0, $start) . $extra . substr($html, $start);
                continue;
            }
        }
        $html .= $extra;
    }
    return $html;
}

function mehs_plain_card_titles($html) {
    if (!is_string($html) || strpos($html, "offer-body") === false) {
        return $html;
    }
    $clean = preg_replace_callback('/(<div class="offer-body">)(.*?)(<div class="offer-foot")/s', function ($match) {
        $body = preg_replace('/<h1\b[^>]*>.*?<\/h1>/is', "", $match[2]);
        $body = preg_replace('/<h3\b[^>]*>\s*<\/h3>/i', "", $body);
        if (preg_match_all('/<h3\b[^>]*>.*?<\/h3>/is', $body, $heads) && count($heads[0]) > 1) {
            $last = $heads[0][count($heads[0]) - 1];
            $body = preg_replace('/<h3\b[^>]*>.*?<\/h3>/is', "", $body);
            $body = $last . $body;
        }
        $body = preg_replace_callback('/(<h3\b[^>]*>)(.*?)(<\/h3>)/is', function ($title) {
            $text = preg_replace("/\s*\|\s*/", " ", wp_strip_all_tags($title[2]));
            $text = trim(preg_replace("/\s+/", " ", (string) $text));
            return $title[1] . $text . $title[3];
        }, $body);
        return $match[1] . $body . $match[3];
    }, $html);
    return is_string($clean) ? $clean : $html;
}

function mehs_live_markup($post_id = 0) {
    $post_id = $post_id ? (int) $post_id : mehs_view_id();
    if ($post_id < 1) {
        $post_id = (int) get_the_ID();
    }
    $post = get_post($post_id);
    $saved = mehs_saved_page_html($post_id);
    $front = $post instanceof WP_Post && (int) $post->ID === (int) get_option("page_on_front");
    $group = $post instanceof WP_Post && $post->post_name === "group-safaris";
    $guide = $post instanceof WP_Post && in_array($post->post_name, ["short-kenya-safaris", "tanzania-safaris", "uganda-safaris", "rwanda-safaris"], true);
    if ($front && strpos($saved, "home-featured") === false) {
        $saved = "";
    }
    if ($group && strpos($saved, "View departure") === false) {
        $saved = "";
    }
    if ($guide && strpos($saved, "do-caption") === false) {
        $saved = "";
    }
    if ($saved !== "") {
        if ($front) {
            $saved = mehs_home_saved_sections($saved);
        }
        if ($post instanceof WP_Post && $post->post_name === "about-us") {
            $saved = mehs_about_saved_sections($saved);
        }
        if ($post instanceof WP_Post && $post->post_name === "contact" && strpos($saved, "719087388") === false) {
            $reach = strpos($saved, 'class="reach"');
            $end = $reach === false ? false : strpos($saved, "</ul>", $reach);
            if ($end !== false) {
                $saved = substr($saved, 0, $end) . mehs_extra_contact_item() . substr($saved, $end);
            }
        }
        return mehs_plain_card_titles($front ? $saved : mehs_with_new_offers($saved, $post_id));
    }
    return mehs_plain_card_titles(mehs_capture_public($post_id));
}

function mehs_elementor_canvas() {
    if (!mehs_elementor_request()) {
        return false;
    }
    $post_id = mehs_view_id();
    $view = get_post($post_id);
    if ($view instanceof WP_Post) {
        $GLOBALS["post"] = $view;
        setup_postdata($view);
    }
    $html = mehs_live_markup($post_id);
    if ($html === "") {
        return false;
    }
    echo $html;
    echo '<div class="mehs-elementor-sink">';
    the_content();
    echo "</div>";
    return true;
}

function mehs_save_live_html() {
    $post_id = (int) ($_POST["post_id"] ?? 0);
    if ($post_id < 1 || !current_user_can("edit_post", $post_id)) {
        wp_send_json(["ok" => false], 403);
    }
    check_ajax_referer("mehs_live_html", "nonce");
    $html = isset($_POST["html"]) ? wp_unslash($_POST["html"]) : "";
    if (!is_string($html) || strpos($html, 'data-mehs-live="1"') === false) {
        wp_send_json(["ok" => false], 400);
    }
    $html = preg_replace('/\scontenteditable="true"/', "", $html);
    $html = preg_replace('/\sdata-mehs-origin="[^"]*"/', "", $html);
    $html = preg_replace('/\sdata-mehs-bound="1"/', "", $html);
    $html = preg_replace('/\sspellcheck="true"/', "", $html);
    $html = preg_replace('#<script\b[^>]*>.*?</script>#is', "", $html);
    $html = preg_replace('#<aside\b[^>]*\bmehs-edit-side\b[^>]*>.*?</aside>#is', "", $html);
    $html = preg_replace('#<div class="mehs-hero-frames"[^>]*>.*?</div>#is', "", $html);
    $html = preg_replace_callback('#<img\b[^>]*\bbanner-media\b[^>]*>#i', function ($match) {
        return preg_replace('/\sstyle="[^"]*"/i', "", $match[0]);
    }, $html);
    $html = preg_replace('#<button\b[^>]*data-mehs-remove\b[^>]*>.*?</button>#is', "", $html);
    if (preg_match("/<h1\\b[^>]*>(.*?)<\\/h1>/is", $html, $title_match)) {
        $crumb = esc_html(trim(html_entity_decode(wp_strip_all_tags($title_match[1]), ENT_QUOTES, "UTF-8")));
        if ($crumb !== "") {
            $html = preg_replace("/(<p class=\"crumbs\">.*?<\\/a>\\s*(?:·|&#183;|&middot;)\\s*)(.*?)(<\\/p>)/is", '$1' . $crumb . '$3', $html, 1);
        }
    }
    $saved_at = isset($_POST["saved_at"]) ? (int) $_POST["saved_at"] : (int) round(microtime(true) * 1000);
    $previous = (int) get_post_meta($post_id, "mehs_live_saved_at", true);
    if ($saved_at > 0 && $previous > $saved_at) {
        wp_send_json(["ok" => true]);
    }
    $html = mehs_strip_pasted_styles($html);
    update_post_meta($post_id, "mehs_live_html", $html);
    update_post_meta($post_id, "mehs_live_saved_at", $saved_at);
    update_post_meta($post_id, "mehs_elementor_public", "1");
    $edits_raw = isset($_POST["edits"]) ? wp_unslash($_POST["edits"]) : "";
    $edits = json_decode(is_string($edits_raw) ? $edits_raw : "", true);
    if (is_array($edits)) {
        $clean = [];
        foreach ($edits as $key => $value) {
            $key = preg_replace("/[^a-f0-9]/", "", strtolower((string) $key));
            if ($key === "") {
                continue;
            }
            $clean[$key] = trim(preg_replace("/\s+/", " ", wp_strip_all_tags((string) $value)));
        }
        update_post_meta($post_id, "mehs_text_edits", $clean);
        update_post_meta($post_id, "mehs_text_edits_at", $saved_at);
    }
    do_action("litespeed_purge_all");
    if (function_exists("litespeed_purge_all")) {
        litespeed_purge_all();
    }
    if (!mehs_listing_page($post_id) && preg_match("/<h1\\b[^>]*>(.*?)<\\/h1>/is", $html, $match)) {
        $title = trim(html_entity_decode(wp_strip_all_tags($match[1]), ENT_QUOTES, "UTF-8"));
        $current = get_post($post_id);
        if ($title !== "" && $current instanceof WP_Post && $current->post_title !== $title) {
            $GLOBALS["mehs_updating_title"] = true;
            wp_update_post(["ID" => $post_id, "post_title" => $title]);
            unset($GLOBALS["mehs_updating_title"]);
        }
    }
    clean_post_cache($post_id);
    do_action("litespeed_purge_post", $post_id);
    $url = get_permalink($post_id);
    if ($url) {
        do_action("litespeed_purge_url", $url);
    }
    wp_send_json(["ok" => true]);
}
add_action("wp_ajax_mehs_save_live_html", "mehs_save_live_html");

function mehs_itinerary_parent_slugs() {
    return [
        "short-kenya-safaris" => "Kenya",
        "tanzania-safaris" => "Tanzania",
        "uganda-safaris" => "Uganda",
        "rwanda-safaris" => "Rwanda",
        "group-safaris" => "Group safaris",
        "excursions" => "Day trips",
        "international-destinations" => "International",
    ];
}

function mehs_itinerary_parent_id($post_id) {
    $post = get_post($post_id);
    $slugs = mehs_itinerary_parent_slugs();
    while ($post instanceof WP_Post) {
        if (isset($slugs[$post->post_name])) {
            return (int) $post->ID;
        }
        if ((int) $post->post_parent < 1) {
            break;
        }
        $post = get_post((int) $post->post_parent);
    }
    return 0;
}

function mehs_add_itinerary() {
    if (!current_user_can("edit_pages") || !current_user_can("publish_pages")) {
        wp_send_json(["ok" => false], 403);
    }
    check_ajax_referer("mehs_live_html", "nonce");
    $parent_id = (int) ($_POST["parent"] ?? 0);
    $title = trim(sanitize_text_field(wp_unslash($_POST["title"] ?? "")));
    $parent = get_post($parent_id);
    $slugs = mehs_itinerary_parent_slugs();
    if (!$parent instanceof WP_Post || $parent->post_type !== "page" || !isset($slugs[$parent->post_name])) {
        wp_send_json(["ok" => false, "error" => "Choose Kenya, Tanzania, Uganda, Rwanda, Group safaris, Day trips, or International."], 400);
    }
    if ($title === "") {
        wp_send_json(["ok" => false, "error" => "Write a title for the itinerary."], 400);
    }
    $page_id = wp_insert_post([
        "post_type" => "page",
        "post_status" => "publish",
        "post_title" => $title,
        "post_parent" => $parent_id,
        "post_content" => " ",
    ], true);
    if (is_wp_error($page_id) || !$page_id) {
        wp_send_json(["ok" => false, "error" => "The itinerary could not be created."], 500);
    }
    update_post_meta($page_id, "_elementor_edit_mode", "builder");
    update_post_meta($page_id, "_elementor_template_type", "wp-page");
    if (defined("ELEMENTOR_VERSION")) {
        update_post_meta($page_id, "_elementor_version", ELEMENTOR_VERSION);
    }
    $canvas = [mehs_seed_box([mehs_seed_heading($title, "h1")])];
    update_post_meta($page_id, "_elementor_data", wp_slash(wp_json_encode($canvas)));
    clean_post_cache($page_id);
    do_action("litespeed_purge_all");
    wp_send_json([
        "ok" => true,
        "id" => (int) $page_id,
        "edit" => admin_url("post.php?post=" . (int) $page_id . "&action=elementor"),
    ]);
}
add_action("wp_ajax_mehs_add_itinerary", "mehs_add_itinerary");

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
    wp_enqueue_style("mehs", $dir . "/assets/css/styles.css", ["mehs-font"], "1.2.44");
    wp_enqueue_style("mehs-theme", $dir . "/assets/css/theme.css", ["mehs"], "1.2.44");
    wp_enqueue_script("mehs", $dir . "/assets/js/main.js", [], "1.2.44", true);
    if (mehs_elementor_request()) {
        $mehs_edit_id = isset($_GET["elementor-preview"]) ? (int) $_GET["elementor-preview"] : (int) get_queried_object_id();
        wp_enqueue_script("mehs-live-edit", $dir . "/assets/js/live-edit.js", [], "1.2.44", true);
        $mehs_parents = [];
        foreach (mehs_itinerary_parent_slugs() as $mehs_slug => $mehs_label) {
            $mehs_parent_page = get_page_by_path($mehs_slug);
            if ($mehs_parent_page instanceof WP_Post) {
                $mehs_parents[] = ["id" => (int) $mehs_parent_page->ID, "label" => $mehs_label];
            }
        }
        $mehs_edit_post = get_post($mehs_edit_id);
        $mehs_slug = $mehs_edit_post instanceof WP_Post ? $mehs_edit_post->post_name : "";
        $mehs_add_trip = in_array($mehs_slug, ["packages", "group-safaris"], true);
        $mehs_ask_parent = $mehs_slug === "packages";
        $mehs_trip_parent = 0;
        if ($mehs_slug === "group-safaris") {
            $mehs_trip_parent = $mehs_edit_id;
        }
        wp_localize_script("mehs-live-edit", "MEHS_LIVE", [
            "url" => admin_url("admin-ajax.php"),
            "nonce" => wp_create_nonce("mehs_live_html"),
            "post" => $mehs_edit_id,
            "parent" => $mehs_trip_parent,
            "askParent" => $mehs_ask_parent,
            "addTrip" => $mehs_add_trip,
            "parents" => $mehs_parents,
        ]);
    }
    wp_localize_script("mehs", "MEHS_BOOK", [
        "url" => admin_url("admin-ajax.php?action=mehs_booking"),
        "nonce" => wp_create_nonce("mehs_booking"),
    ]);
}
add_action("wp_enqueue_scripts", "mehs_assets");

function mehs_silence_elementor_frontend() {
    if (is_admin() || mehs_elementor_request()) {
        return;
    }
    foreach (["elementor-frontend", "elementor-frontend-modules", "elementor-waypoints", "elementor-dialog", "elementor-gallery", "elementor-sticky"] as $handle) {
        wp_dequeue_script($handle);
        wp_deregister_script($handle);
    }
}
add_action("wp_enqueue_scripts", "mehs_silence_elementor_frontend", 999);
add_action("wp_print_scripts", "mehs_silence_elementor_frontend", 1);

function mehs_asset($path) {
    return get_template_directory_uri() . "/" . ltrim($path, "/");
}

function mehs_picture($file, $alt, $class = "") {
    $src = (is_string($file) && preg_match("#^https?://#", $file)) ? $file : mehs_asset("assets/images/" . $file);
    $class_attr = $class !== "" ? ' class="' . esc_attr($class) . '"' : "";
    return '<img src="' . esc_url($src) . '" alt="' . esc_attr($alt) . '"' . $class_attr . ">";
}

function mehs_image($file, $alt, $class = "") {
    return mehs_picture($file, $alt, $class);
}

function mehs_show_admin_bar($show) {
    if (is_user_logged_in() && current_user_can("edit_pages")) {
        return true;
    }
    return $show;
}
add_filter("show_admin_bar", "mehs_show_admin_bar", 1001);

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
        "faqs" => "Frequently asked questions",
    ];
    foreach ($pages as $slug => $title) {
        $existing = get_page_by_path($slug);
        if ($existing instanceof WP_Post) {
            if ($slug === "faqs" && $existing->post_status !== "publish") {
                wp_update_post(["ID" => $existing->ID, "post_status" => "publish"]);
            }
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
    if (mehs_elementor_request()) {
        return;
    }
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
    mehs_ensure_theme_pages();
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
    $kind = $rows["Trip"] === "Newsletter" ? "Newsletter" : ($rows["Trip"] !== "" ? $rows["Trip"] : "Enquiry");
    wp_insert_post([
        "post_type" => "mehs_enquiry",
        "post_status" => "private",
        "post_title" => $kind . " — " . $name,
        "post_content" => implode("\n", $lines),
    ]);
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

function mehs_register_enquiries() {
    register_post_type("mehs_enquiry", [
        "labels" => [
            "name" => "Enquiries",
            "singular_name" => "Enquiry",
            "menu_name" => "Enquiries",
            "all_items" => "Enquiries",
        ],
        "public" => false,
        "show_ui" => true,
        "show_in_menu" => true,
        "show_in_rest" => false,
        "exclude_from_search" => true,
        "publicly_queryable" => false,
        "menu_position" => 26,
        "menu_icon" => "dashicons-email-alt",
        "supports" => ["title", "editor"],
        "capability_type" => "post",
        "map_meta_cap" => true,
    ]);
}
add_action("init", "mehs_register_enquiries");

function mehs_fix_phone_typo($text) {
    if (!is_string($text)) {
        return $text;
    }
    return str_replace(
        ["+254 717087388", "+254717087388", "717087388"],
        ["+254 719 087 388", "+254719087388", "719087388"],
        $text
    );
}
add_filter("wpseo_metadesc", "mehs_fix_phone_typo");
add_filter("wpseo_opengraph_desc", "mehs_fix_phone_typo");
add_filter("rank_math/frontend/description", "mehs_fix_phone_typo");

function mehs_purge_saved_pages() {
    if (get_option("mehs_purged") === "1.2.44") {
        return;
    }
    update_option("mehs_purged", "1.2.44", false);
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
