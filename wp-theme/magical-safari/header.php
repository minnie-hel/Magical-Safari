<?php
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo("charset"); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip" href="#main">Skip to content</a>
<div class="site-head">
<header class="site-header"><div class="header-row">
  <a class="logo" href="<?php echo esc_url(home_url("/")); ?>"><?php echo mehs_image("logo.svg", "Magical Africa Safaris"); ?></a>
  <nav class="nav-bar" aria-label="Primary">
    <a href="<?php echo esc_url(home_url("/")); ?>"<?php echo is_front_page() ? ' aria-current="page"' : ""; ?>>Home</a>
    <?php
    $links = [
        ["about-us", "About"],
        ["packages", "Packages"],
        ["group-safaris", "Group safaris"],
    ];
    foreach ($links as [$slug, $label]) {
        $url = $slug === "packages" ? home_url("/packages/") : mehs_page_url($slug);
        if (!$url) {
            continue;
        }
        echo '<a href="' . esc_url($url) . '">' . esc_html($label) . "</a>";
    }
    $destinations = [
        ["short-kenya-safaris", "Kenya"],
        ["tanzania-safaris", "Tanzania"],
        ["uganda-safaris", "Uganda"],
        ["rwanda-safaris", "Rwanda"],
        ["local-destinations", "Local"],
        ["international-destinations", "International"],
    ];
    ?>
    <div class="nav-drop">
      <button type="button" data-drop-toggle aria-expanded="false">Destinations <span class="caret" aria-hidden="true"></span></button>
      <div class="nav-drop-menu" data-drop-menu hidden>
        <?php foreach ($destinations as [$slug, $label]) :
            $url = mehs_page_url($slug);
            if (!$url) {
                continue;
            } ?>
          <a href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a>
        <?php endforeach; ?>
      </div>
    </div>
    <a href="<?php echo esc_url(home_url("/gallery/")); ?>">Gallery</a>
    <?php $blog = mehs_page_url("blog"); ?>
    <?php if ($blog) : ?><a href="<?php echo esc_url($blog); ?>">Blog</a><?php endif; ?>
    <?php $contact = mehs_page_url("contact"); ?>
    <?php if ($contact) : ?><a href="<?php echo esc_url($contact); ?>">Contact</a><?php endif; ?>
  </nav>
  <div class="header-actions">
    <div class="lang" data-lang>
      <button type="button" data-lang-toggle aria-expanded="false" aria-haspopup="listbox">
        <span data-lang-flag aria-hidden="true">🇬🇧</span>
        <span data-lang-code>EN</span>
        <span class="caret" aria-hidden="true"></span>
      </button>
      <ul class="lang-menu" data-lang-menu hidden role="listbox">
        <li><button type="button" data-set-lang="en">🇬🇧 English</button></li>
        <li><button type="button" data-set-lang="fr">🇫🇷 Français</button></li>
        <li><button type="button" data-set-lang="de">🇩🇪 Deutsch</button></li>
        <li><button type="button" data-set-lang="es">🇪🇸 Español</button></li>
        <li><button type="button" data-set-lang="it">🇮🇹 Italiano</button></li>
        <li><button type="button" data-set-lang="pt">🇵🇹 Português</button></li>
        <li><button type="button" data-set-lang="nl">🇳🇱 Nederlands</button></li>
        <li><button type="button" data-set-lang="zh-CN">🇨🇳 中文</button></li>
      </ul>
    </div>
    <a class="quote-btn" href="<?php echo esc_url(home_url("/book/")); ?>">Book now</a>
  </div>
</div></header>
<div id="google_translate_element" class="gt-hidden"></div>
</div>
<main id="main">
