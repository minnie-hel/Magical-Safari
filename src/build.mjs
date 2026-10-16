import { cpSync, mkdirSync, readFileSync, rmSync, writeFileSync } from "node:fs";
import { dirname, join } from "node:path";
import { fileURLToPath } from "node:url";
import {
  destinations,
  excludedBase,
  faqs,
  includedBase,
  months,
  posts,
  priceNote,
  reviews,
  safaris,
  site,
  steps,
  styles,
} from "./content.mjs";

const root = join(dirname(fileURLToPath(import.meta.url)), "..");
const dist = join(root, "dist");
const images = Object.fromEntries(
  JSON.parse(readFileSync(join(root, "src/image-credits.json"), "utf8")).map((item) => [item.key, item]),
);
const credits = Object.values(images);

rmSync(dist, { recursive: true, force: true });
mkdirSync(join(dist, "css"), { recursive: true });
mkdirSync(join(dist, "js"), { recursive: true });
mkdirSync(join(dist, "images"), { recursive: true });
cpSync(join(root, "src/styles.css"), join(dist, "css/styles.css"));
cpSync(join(root, "src/main.js"), join(dist, "js/main.js"));
for (const item of credits) cpSync(join(root, "src/images", item.file), join(dist, "images", item.file));
cpSync(join(root, "src/images/logo.svg"), join(dist, "images/logo.svg"));

const routes = [];

function esc(value) {
  return String(value ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;");
}

function writePage(urlPath, html) {
  const rel = urlPath === "/" ? "index.html" : `${urlPath.replace(/^\//, "").replace(/\/$/, "")}/index.html`;
  const file = join(dist, rel);
  mkdirSync(dirname(file), { recursive: true });
  writeFileSync(file, html);
  routes.push(urlPath);
}

function img(key, { eager = false, className = "media" } = {}) {
  const item = images[key] || images["acacia-sunset"];
  const loading = eager ? 'fetchpriority="high"' : 'loading="lazy"';
  return `<img src="/images/${item.file}" alt="${esc(item.alt)}" width="${item.width}" height="${item.height}" ${loading} decoding="async"${className ? ` class="${className}"` : ""}>`;
}

function money(usd) {
  return `<span data-usd="${usd}">USD ${usd.toLocaleString("en-US")}</span>`;
}

function logo() {
  return `<img src="/images/logo.svg" alt="Magical Africa Safaris" width="280" height="85">`;
}

function currencySwitch() {
  return `<div class="filters" aria-label="Display currency">
    <button class="btn btn-line" type="button" data-currency="USD" aria-pressed="true">USD</button>
    <button class="btn btn-line" type="button" data-currency="EUR" aria-pressed="false">EUR</button>
    <button class="btn btn-line" type="button" data-currency="GBP" aria-pressed="false">GBP</button>
  </div>`;
}

function formFields({ compact = false, trip = "" } = {}) {
  const monthsAhead = Array.from({ length: 18 }, (_, index) => {
    const date = new Date(2026, 9 + index, 1);
    return date.toLocaleString("en-US", { month: "long", year: "numeric" });
  });
  return `<div class="form-grid">
    <div class="form-grid form-2">
      <label>Name<input name="name" autocomplete="name" required></label>
      <label>Email<input name="email" type="email" autocomplete="email" required></label>
    </div>
    ${compact ? "" : `<label>Phone or WhatsApp<input name="phone" autocomplete="tel"></label>`}
    <div class="form-grid form-2">
      <label>Travel month<select name="month" required><option value="">Choose</option>${monthsAhead.map((month) => `<option>${esc(month)}</option>`).join("")}</select></label>
      <label>Budget<select name="budget"><option>Not sure yet</option><option>Budget</option><option>Mid-range</option><option>Luxury</option></select></label>
    </div>
    <div class="form-grid form-2">
      <label>Adults<input name="adults" type="number" min="1" max="12" value="2" required></label>
      <label>Children<input name="children" type="number" min="0" max="10" value="0"></label>
    </div>
    <fieldset>
      <legend class="fine">Interests</legend>
      <div class="chips">
        ${["Wildlife", "Great Migration", "Gorillas", "Beach", "Family", "Photography"].map((item) => `<label><input type="checkbox" name="interest" value="${esc(item)}"> ${esc(item)}</label>`).join("")}
      </div>
    </fieldset>
    ${trip ? `<input type="hidden" name="trip" value="${esc(trip)}">` : `<label>Trip, if you have one in mind<input name="trip" placeholder="Optional"></label>`}
    ${compact ? "" : `<label>Anything we should know?<textarea name="note" rows="4"></textarea></label>`}
    <button class="btn btn-clay" type="submit">Send my safari plan</button>
    <p class="fine">We use this only to reply. No payment is taken on this form.</p>
    <div class="result" data-result>
      <strong>Your enquiry is ready to send.</strong>
      <p class="fine">This site has no inbox of its own yet. Use WhatsApp or email and the message below is filled in for you.</p>
      <a class="btn btn-green" data-whatsapp href="https://wa.me/${site.whatsapp}" target="_blank" rel="noopener">Send on WhatsApp</a>
      <a class="btn btn-line" data-email href="mailto:${site.email}">Send by email</a>
    </div>
  </div>`;
}

function enquiryForm({ compact = false, trip = "", title = "Plan my safari" } = {}) {
  return `<form class="plan-card" data-enquiry data-whatsapp-number="${site.whatsapp}" data-email-address="${site.email}" action="/contact/">
    <h2>${esc(title)}</h2>
    ${formFields({ compact, trip })}
  </form>`;
}

const bookCountries = ["Kenya", "Tanzania", "Uganda", "Rwanda", "United Kingdom", "United States", "Canada", "Germany", "France", "Spain", "Italy", "Netherlands", "Belgium", "Switzerland", "Australia", "India", "China", "South Africa", "United Arab Emirates", "Other"];

function bookingForm({ trip = "", usd = "" } = {}) {
  const rate = Number(usd);
  const priced = Number.isFinite(rate) && rate > 0;
  return `<form class="book-form" id="book" data-enquiry data-book data-whatsapp-number="${site.whatsapp}" data-email-address="${site.email}">
    <div class="book-layout">
      <div class="plan-card">
        <h2>Personal details</h2>
        <div class="form-grid">
          <label>Full name<input name="name" autocomplete="name" required></label>
          <label>Email address<input name="email" type="email" autocomplete="email" required></label>
          <div class="form-grid form-2">
            <label>Phone number<input name="phone" autocomplete="tel" required></label>
            <label>WhatsApp number<input name="whatsapp" autocomplete="tel"></label>
          </div>
          <label>Country<select name="country" required><option value="">Select country</option>${bookCountries.map((country) => `<option>${esc(country)}</option>`).join("")}</select></label>
          <h2>Travel details</h2>
          <div class="form-grid form-2">
            <label>Travel date<input name="date" type="date" required></label>
            <label>Number of people<input name="adults" type="number" min="1" max="20" value="2" required data-people-input></label>
          </div>
          ${trip ? `<input type="hidden" name="trip" value="${esc(trip)}">` : `<label>Package<input name="trip" placeholder="Name a safari, or leave this open"></label>`}
          <label>Anything we should know?<textarea name="note" rows="3"></textarea></label>
          <label class="check"><input type="checkbox" name="terms" required> I agree to the <a href="/booking-policy/">booking policy</a> and confirm these details are correct.</label>
          <button class="btn btn-clay" type="submit">Book now</button>
          <p class="fine">We confirm availability and send the price in writing. No payment is taken on this form.</p>
          <div class="result" data-result>
            <strong>Your booking request is ready to send.</strong>
            <p class="fine">Use WhatsApp or email. The message below is filled in for you.</p>
            <a class="btn btn-green" data-whatsapp href="https://wa.me/${site.whatsapp}" target="_blank" rel="noopener">Send on WhatsApp</a>
            <a class="btn btn-line" data-email href="mailto:${site.email}">Send by email</a>
          </div>
        </div>
      </div>
      <aside class="summary-card" data-summary data-usd-rate="${priced ? rate : ""}">
        <h2>Booking summary</h2>
        <p class="summary-name">${esc(trip || "Safari to be confirmed")}</p>
        <p>Per person <strong data-per>${priced ? money(rate) : "On request"}</strong></p>
        <p>People <strong data-people-count>2</strong></p>
        <p class="summary-total">Estimated total <strong data-total${priced ? ` data-usd="${rate * 2}"` : ""}>${priced ? money(rate * 2) : "On request"}</strong></p>
        <p class="fine">The written quote is the price. Nothing is charged on this page.</p>
        <p class="fine"><a href="tel:${site.phoneHref}">${esc(site.phone)}</a><br><a href="mailto:${site.email}">${esc(site.email)}</a></p>
      </aside>
    </div>
  </form>`;
}

function bookLink(title, usd) {
  const query = new URLSearchParams({ trip: title });
  if (usd) query.set("usd", String(usd));
  return `/book/?${query.toString()}`;
}

function offerCard({ href, image, country, title, summary, days }) {
  const nights = days > 1 ? ` / ${days - 1} nights` : "";
  return `<a class="offer" href="${href}">
    <div class="offer-photo">${img(image)}<span class="offer-tag">${esc(country)}</span></div>
    <div class="offer-body">
      <h3>${esc(title)}</h3>
      ${summary ? `<p>${esc(summary)}</p>` : ""}
      <div class="offer-foot"><span>${days} ${days === 1 ? "day" : "days"}${nights}</span><span class="offer-go">View details</span></div>
    </div>
  </a>`;
}

function safariOffer(safari) {
  const name = safari.title.replace(/^\d+\s+days?\s+/i, "");
  return offerCard({
    href: `/safaris/${safari.slug}/`,
    image: safari.image,
    country: safari.country,
    title: `${safari.days} days | ${name}`,
    summary: safari.summary,
    days: safari.days,
  });
}

function catalogOffer(tour) {
  const name = tour.title.replace(/^\d+\s+days?\s+/i, "");
  return offerCard({
    href: `/safaris/${tour.slug}/`,
    image: tour.image,
    country: tour.country,
    title: `${tour.dayCount} ${tour.dayCount === 1 ? "day" : "days"} | ${name}`,
    summary: tour.intro[0] || "",
    days: tour.dayCount,
  });
}

function header(path) {
  const item = (href, label) => `<a href="${href}"${path === href ? ' aria-current="page"' : ""}>${label}</a>`;
  const destOn = ["/kenya-safaris/", "/tanzania-safaris/", "/uganda-safaris/", "/rwanda-safaris/", "/destinations/local/", "/destinations/international/"].includes(path);
  return `<a class="skip" href="#main">Skip to content</a>
  <div class="site-head">
  <header class="site-header"><div class="header-row">
    <a class="logo" href="/">${logo()}</a>
    <nav class="nav-bar" aria-label="Primary">
      ${item("/", "Home")}
      ${item("/about/", "About")}
      ${item("/safaris/", "Packages")}
      ${item("/group-safaris/", "Group safaris")}
      <div class="nav-drop">
        <button type="button" data-drop-toggle aria-expanded="false"${destOn ? ' aria-current="page"' : ""}>Destinations <span class="caret" aria-hidden="true"></span></button>
        <div class="nav-drop-menu" data-drop-menu hidden>
          ${item("/kenya-safaris/", "Kenya")}
          ${item("/tanzania-safaris/", "Tanzania")}
          ${item("/uganda-safaris/", "Uganda")}
          ${item("/rwanda-safaris/", "Rwanda")}
          ${item("/destinations/local/", "Local")}
          ${item("/destinations/international/", "International")}
        </div>
      </div>
      ${item("/gallery/", "Gallery")}
      ${item("/blog/", "Blog")}
      ${item("/faqs/", "FAQs")}
      ${item("/contact/", "Contact")}
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
      <a class="quote-btn" href="/book/">Book now</a>
    </div>
  </div></header>
  <div id="google_translate_element" class="gt-hidden"></div>
  </div>`;
}

function footer() {
  return `<footer class="site-footer"><div class="wrap">
    <div class="foot-grid">
      <div>
        <h2>${esc(site.name)}</h2>
        <p>Private journeys in Kenya, Tanzania, Uganda and Rwanda, planned from Nairobi. One name, on this website, on our email address and on your invoice.</p>
        <p><a href="tel:${site.phoneHref}">${esc(site.phone)}</a><br><a href="mailto:${site.email}">${esc(site.email)}</a><br>${esc(site.city)}</p>
      </div>
      <div>
        <h2>Plan</h2>
        <p><a href="/book/">Book a safari</a><br><a href="/safaris/">Packages</a><br><a href="/group-safaris/">Group safaris</a><br><a href="/excursions/">Day trips</a><br><a href="/booking-policy/">Booking policy</a></p>
      </div>
      <div>
        <h2>Learn</h2>
        <p><a href="/best-time-to-visit/">Best time to visit</a><br><a href="/travel-info/">Visas, packing, tipping</a><br><a href="/gallery/">Gallery</a><br><a href="/faqs/">FAQs</a><br><a href="/blog/">Blog</a><br><a href="/about/">About</a></p>
      </div>
      <div>
        <h2>Notes</h2>
        <form data-enquiry data-whatsapp-number="${site.whatsapp}" data-email-address="${site.email}">
          <label>Newsletter<input type="email" name="email" placeholder="Email address" required></label>
          <input type="hidden" name="trip" value="Newsletter">
          <input type="hidden" name="name" value="Newsletter reader">
          <input type="hidden" name="month" value="Not travelling yet">
          <input type="hidden" name="adults" value="1">
          <button class="btn btn-clay" type="submit">Request the list</button>
          <div class="result" data-result><a data-email href="mailto:${site.email}">Email me the signup</a></div>
        </form>
      </div>
    </div>
    <div class="legal">
      <p><a href="/">Home</a> · <a href="/privacy/">Privacy</a> · <a href="/image-credits/">Photograph credits</a></p>
      <p>© ${new Date().getFullYear()} ${esc(site.name)}. Display prices in EUR and GBP are approximate. Quotes are issued in US dollars.</p>
    </div>
  </div></footer>
  <a class="wa-float" href="https://wa.me/${site.whatsapp}" target="_blank" rel="noopener">WhatsApp</a>`;
}

function layout({ title, description, path, image = "kilimanjaro", jsonld = [], body, bodyClass = "" }) {
  const canonical = site.domain + (path === "/" ? "/" : path);
  const photo = images[image] || images.kilimanjaro;
  const graph = [
    {
      "@context": "https://schema.org",
      "@type": "TravelAgency",
      name: site.name,
      url: site.domain,
      email: site.email,
      telephone: site.phone,
      image: `${site.domain}/images/${photo.file}`,
      address: { "@type": "PostalAddress", addressLocality: "Nairobi", addressCountry: "KE" },
      areaServed: ["KE", "TZ", "UG", "RW"],
    },
    ...jsonld,
  ];
  return `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>${esc(title)}</title>
  <meta name="description" content="${esc(description)}">
  <link rel="canonical" href="${canonical}">
  <meta property="og:title" content="${esc(title)}">
  <meta property="og:description" content="${esc(description)}">
  <meta property="og:type" content="website">
  <meta property="og:url" content="${canonical}">
  <meta property="og:image" content="${site.domain}/images/${photo.file}">
  <link rel="icon" href="/favicon.svg" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/styles.css">
  <script type="application/ld+json">${JSON.stringify(graph)}</script>
</head>
<body class="${bodyClass}">
${header(path)}
<main id="main">${body}</main>
${footer()}
<script>window.MEHS_GA_ID = "";</script>
<script src="/js/main.js" defer></script>
</body>
</html>`;
}

function safariCard(safari, { compact = false } = {}) {
  const tags = [safari.country, ...safari.tags, "budget", "luxury"];
  return `<article class="card safari-card-wrap" data-card data-tags="${esc(tags.join(" "))}">
    <a class="safari-card" href="/safaris/${safari.slug}/">
      ${img(safari.image)}
      <div class="safari-body">
        <div class="badges"><span class="badge">${safari.days} days</span><span class="badge">${esc(safari.style)}</span><span class="badge">${esc(safari.country)}</span></div>
        <h3>${esc(safari.title)}</h3>
        ${compact ? "" : `<p class="fine">${esc(safari.bestFor)}</p><ul class="highlights">${safari.highlights.map((item) => `<li>${esc(item)}</li>`).join("")}</ul>`}
        <p class="price">From ${money(safari.from)} pp</p>
      </div>
    </a>
  </article>`;
}

function destCard(dest) {
  return `<a class="dest-card" href="/destinations/${dest.slug}/">${img(dest.image)}<div><p class="fine">${esc(dest.country)}</p><h3>${esc(dest.name)}</h3><p>${esc(dest.tagline)}</p></div></a>`;
}

const imagePools = {
  Kenya: ["migration", "amboseli", "tsavo", "flamingo", "samburu", "rhino", "zebra", "elephant-savanna", "elephant-walk", "giraffe-dusk", "lions", "red-savanna", "kilimanjaro", "acacia-sunset", "safari-vehicle"],
  Tanzania: ["serengeti", "ngorongoro", "zanzibar", "flamingo"],
  Uganda: ["gorilla", "queen", "lions"],
  Rwanda: ["rwanda", "zebra", "giraffe-dusk"],
};

function takeImage(used, preferred, country) {
  const pool = [preferred, ...(imagePools[country] || [])].filter(Boolean);
  const key = pool.find((item) => item && !used.has(item));
  if (!key) return "";
  used.add(key);
  return key;
}

function placeCard(dest, imageKey) {
  return `<article class="place${imageKey ? "" : " place-text"}">${imageKey ? img(imageKey) : ""}<div><h3><a href="/destinations/${dest.slug}/">${esc(dest.name)}</a></h3><p>${esc(dest.summary)}</p><p class="fine">${esc(dest.best)} ${esc(dest.stay)}</p></div></article>`;
}

function tripRow({ href, title, meta }) {
  return `<a class="trip-row" href="${href}"><span><strong>${esc(title)}</strong><span>${esc(meta)}</span></span><span>View</span></a>`;
}

function uniqueGallery(keys, bannerKey) {
  const photos = [...new Set([bannerKey, ...(keys || [])])].filter(Boolean).slice(0, 6);
  if (!photos.length) return "";
  return `<h2 class="ruled">Photo gallery</h2><div class="gallery">${photos.map((key) => img(key, { className: "media square" })).join("")}</div>`;
}

function frontMenu() {
  const items = [
    ["/kenya-safaris/", "Kenya Safaris", "lions"],
    ["/tanzania-safaris/", "Tanzania Safaris", "serengeti"],
    ["/uganda-safaris/", "Uganda Safaris", "gorilla"],
    ["/rwanda-safaris/", "Rwanda Safaris", "rwanda"],
    ["/group-safaris/", "Group Safaris", "zebra"],
    ["/excursions/", "Excursions", "zanzibar"],
  ];
  return `<nav class="front-menu" aria-label="Safari menus">${items.map(([href, label, image]) => `<a href="${href}">${img(image)}<span>${label}</span></a>`).join("")}</nav>`;
}

const skipImport = new Set([
  "5-days-amboseli-salt-lick-tsavo-west-tsavo-east-safari",
  "7-days-ol-pejeta-samburu-lake-nakuru-masai-mara-safari",
]);

const imageWords = [
  ["gorilla", "gorilla"], ["bwindi", "gorilla"], ["mgahinga", "gorilla"],
  ["rwanda", "rwanda"], ["volcano", "rwanda"], ["kigali", "rwanda"],
  ["serengeti", "serengeti"], ["ngorongoro", "ngorongoro"], ["manyara", "flamingo"],
  ["zanzibar", "zanzibar"], ["mombasa", "zanzibar"], ["wasini", "zanzibar"],
  ["watamu", "zanzibar"], ["beach", "zanzibar"], ["amboseli", "amboseli"],
  ["tsavo", "tsavo"], ["mara", "migration"], ["nakuru", "flamingo"],
  ["samburu", "samburu"], ["pejeta", "rhino"], ["naivasha", "flamingo"],
  ["shimba", "elephant-savanna"], ["nairobi", "rhino"], ["aberdare", "acacia-sunset"],
];

function wordsIn(text) {
  const hay = text.toLowerCase();
  const keys = [];
  for (const [word, key] of imageWords) {
    if (hay.includes(word) && !keys.includes(key)) keys.push(key);
  }
  return keys.length ? keys : ["kilimanjaro"];
}

function prettyTitle(slug) {
  const small = new Set(["and", "of", "to", "in", "with", "a", "the", "or"]);
  return slug.split("-").map((word, index) => {
    const lower = word.toLowerCase();
    if (lower === "masai" || lower === "maasai") return "Maasai";
    if (index > 0 && small.has(lower)) return lower;
    return lower.charAt(0).toUpperCase() + lower.slice(1);
  }).join(" ");
}

function joinFragments(lines) {
  const parts = (lines || [])
    .map((line) => line.replace(/\s+/g, " ").trim())
    .filter((line) => line && !line.includes("@") && line.toLowerCase() !== "magical africa safaris");
  const paras = [];
  let buf = "";
  for (const part of parts) {
    if (!buf) buf = part;
    else if (/[.!?]$/.test(buf) && /^[A-Z0-9]/.test(part) && part.length > 70) {
      paras.push(buf);
      buf = part;
    } else buf += `${buf.endsWith("-") ? "" : " "}${part}`;
  }
  if (buf) paras.push(buf);
  return paras.filter((paragraph) => paragraph.length > 45).slice(0, 3);
}

const catalog = JSON.parse(readFileSync(join(root, "src/imported-tours.json"), "utf8"))
  .filter((tour) => !skipImport.has(tour.slug))
  .map((tour) => {
    const title = prettyTitle(tour.slug);
    const days = (tour.days || []).map((day) => ({
      title: day.title,
      text: (day.text || "").replace(/\s+/g, " ").trim(),
      stay: (day.stay || "").replace(/\s+/g, " ").trim(),
    })).filter((day) => day.title);
    const notes = {
      "1-day-amboseli-safari": "This is a very long day from Nairobi and the drive back is after dark. One night in the park is the better version.",
      "1-day-ol-pejeta-tour": "It is a lot of road for a single day. Staying the night is the better version of this trip.",
      "1-day-tsavo-east-safari": "Tsavo East is a long way from Nairobi for a day trip. Ask us for a night in the park if the day would be mostly driving.",
      "1-day-shimba-hills-tour": "This one starts from the coast, not from Nairobi.",
      "watamu-safari-blue": "A marine day for guests already in Watamu or Malindi. It is not a Nairobi excursion.",
      "wasini-dolphin-tour": "This starts from the south coast, not from Nairobi.",
    };
    return {
      ...tour,
      title,
      days,
      dayCount: days.length || Number((tour.slug.match(/(\d+)-days?/) || (tour.intro || []).join(" ").match(/(\d+)\s+days/i) || [])[1] || 1),
      intro: joinFragments(tour.intro),
      highlights: (tour.highlights || []).map((item) => item.replace(/\s+/g, " ").trim()).filter((item) => item.length > 2 && !item.includes("@")).slice(0, 8),
      includes: (tour.includes || []).map((item) => item.replace(/\s+/g, " ").trim()).filter(Boolean),
      excludes: (tour.excludes || []).map((item) => item.replace(/\s+/g, " ").trim()).filter(Boolean),
      image: wordsIn(`${tour.slug} ${tour.intro?.join(" ") || ""}`)[0],
      gallery: wordsIn(`${tour.slug} ${(tour.intro || []).join(" ")} ${(tour.days || []).map((day) => day.title).join(" ")}`).slice(0, 3),
      note: notes[tour.slug] || "",
      country: tour.section === "tanzania-safaris" ? "Tanzania"
        : tour.section === "uganda-safaris" ? "Uganda"
        : tour.section === "rwanda-safaris" ? "Rwanda"
        : "Kenya",
    };
  });

function catalogCard(tour) {
  const kind = tour.section === "group-safaris" ? "Group" : tour.section === "excursions" ? "Day trip" : "Private";
  const tags = [tour.country, kind.toLowerCase(), tour.section];
  return `<article class="card safari-card-wrap" data-card data-tags="${esc(tags.join(" "))}">
    <a class="safari-card" href="/safaris/${tour.slug}/">
      ${img(tour.image)}
      <div class="safari-body">
        <div class="badges"><span class="badge">${tour.dayCount} ${tour.dayCount === 1 ? "day" : "days"}</span><span class="badge">${esc(kind)}</span><span class="badge">${esc(tour.country)}</span></div>
        <h3>${esc(tour.title)}</h3>
        <p class="price">Price on request</p>
      </div>
    </a>
  </article>`;
}

function catalogDetail(tour) {
  const includes = tour.includes.length ? tour.includes : includedBase;
  const excludes = tour.excludes.length ? tour.excludes : excludedBase;
  const days = tour.days.length ? tour.days : [{ title: tour.title, text: tour.intro.join(" "), stay: "" }];
  return layout({
    title: `${tour.title} | ${site.name}`,
    description: tour.intro[0] || tour.title,
    path: `/safaris/${tour.slug}/`,
    image: tour.image,
    body: `<section class="banner package-banner">
      ${img(tour.image, { eager: true, className: "banner-media" })}
      <div class="banner-copy wrap">
        <p class="crumbs"><a href="/">Home</a> · <a href="/safaris/">Safaris</a> · ${esc(tour.country)}</p>
        <h1>${esc(tour.title)}</h1>
      </div>
    </section>
    <div class="wrap package">
      <div class="package-main">
        <h2 class="ruled">Package overview</h2>
        ${tour.intro.map((paragraph) => `<p>${esc(paragraph)}</p>`).join("") || `<p>${esc(tour.title)}</p>`}
        ${tour.highlights.length ? `<ul class="highlights">${tour.highlights.map((item) => `<li>${esc(item)}</li>`).join("")}</ul>` : ""}
        ${tour.note ? `<p class="note">${esc(tour.note)}</p>` : ""}
      </div>
      <aside class="price-card book-card">
        <div class="book-card-head">
          <p class="kicker">Package price from</p>
          <p class="big">On request</p>
          <p>Per person</p>
        </div>
        <div class="book-card-body">
          <p>Select your travel date and the number of people on the next step.</p>
          <a class="btn btn-clay" href="${bookLink(tour.title)}">Book now</a>
        </div>
      </aside>
      <div class="package-rest">
        ${uniqueGallery(tour.gallery, tour.image)}
        <h2 class="ruled">Day-by-day itinerary</h2>
        ${days.map((day, index) => `<details class="day-acc"${index === 0 ? " open" : ""}><summary><span class="day-num">${String(index + 1).padStart(2, "0")}</span><span>Day ${index + 1}${day.title ? ` | ${esc(day.title)}` : ""}</span></summary><p>${esc(day.text)}</p>${day.stay ? `<p class="fine">Accommodation: ${esc(day.stay)}</p>` : ""}</details>`).join("")}
        <div class="ie">
          <div><h2 class="ruled">What's included</h2><ul class="yes">${includes.map((item) => `<li>${esc(item)}</li>`).join("")}</ul></div>
          <div><h2 class="ruled">What's excluded</h2><ul class="no">${excludes.map((item) => `<li>${esc(item)}</li>`).join("")}</ul></div>
        </div>
        <p class="fine">${esc(priceNote)}</p>
      </div>
    </div>`,
  });
}

function home() {
  const featured = safaris.filter((safari) => safari.featured);
  return layout({
    title: "Kenya Safari Tours | Magical East Africa Heritage Safaris",
    description: "Private Kenya safari tours and trips to Tanzania, Uganda and Rwanda, planned in Nairobi. Book with a written price before you pay.",
    path: "/",
    bodyClass: "home",
    image: "elephant-savanna",
    body: `<section class="banner hero">
      ${img("elephant-savanna", { eager: true, className: "banner-media" })}
      <div class="banner-copy wrap">
        <h1>Discover the magic of East Africa.</h1>
        <p class="lede">Private safaris in Kenya, Tanzania, Uganda and Rwanda, planned in Nairobi.</p>
        <div class="hero-actions">
          <a class="btn btn-clay" href="/safaris/">Explore packages</a>
          <a class="btn btn-ghost" href="/book/">Book a safari</a>
        </div>
      </div>
    </section>
    <section><div class="wrap">
      <div class="section-intro">
        <p class="kicker">Our offerings</p>
        <h2>Featured tour packages</h2>
        <p>Open a package for the day-by-day plan, then book it with your travel date.</p>
      </div>
      <div class="offers">${featured.map(safariOffer).join("")}</div>
      <p class="center"><a class="btn btn-green" href="/safaris/">View all packages</a></p>
    </div></section>`,
  });
}

function safariDetail(safari) {
  const related = destinations.filter((dest) => safari.route.includes(dest.name) || safari.title.includes(dest.name)).slice(0, 3);
  return layout({
    title: `${safari.title} | ${site.name}`,
    description: safari.summary,
    path: `/safaris/${safari.slug}/`,
    image: safari.image,
    jsonld: [{
      "@context": "https://schema.org",
      "@type": "TouristTrip",
      name: safari.title,
      description: safari.summary,
      touristType: safari.bestFor,
      itinerary: {
        "@type": "ItemList",
        itemListElement: safari.itinerary.map(([title, text], index) => ({
          "@type": "ListItem",
          position: index + 1,
          name: `Day ${index + 1}: ${title}`,
          description: text,
        })),
      },
    }],
    body: `<section class="banner package-banner">
      ${img(safari.image, { eager: true, className: "banner-media" })}
      <div class="banner-copy wrap">
        <p class="crumbs"><a href="/">Home</a> · <a href="/safaris/">Safaris</a> · ${esc(safari.country)}</p>
        <h1>${safari.days} days | ${esc(safari.title.replace(/^\d+\s+days\s+/i, ""))}</h1>
      </div>
    </section>
    <div class="wrap package">
      <div class="package-main">
        <h2 class="ruled">Package overview</h2>
        <p>${esc(safari.summary)}</p>
        <p>${esc(safari.bestFor)}. Highlights: ${safari.highlights.map(esc).join(", ")}.</p>
        <p class="chip"><span>Route</span> ${safari.route.map(esc).join(" → ")}</p>
        ${safari.extraNote ? `<p class="note">${esc(safari.extraNote)}</p>` : ""}
        ${safari.permit ? `<p class="note">${esc(safari.permit)}</p>` : ""}
      </div>
      <aside class="price-card book-card">
        <div class="book-card-head">
          <p class="kicker">Package price from</p>
          <p class="big">${money(safari.from)}</p>
          <p>Per person</p>
          ${currencySwitch()}
        </div>
        <div class="book-card-body">
          <p>Select your travel date and the number of people on the next step.</p>
          <a class="btn btn-clay" href="${bookLink(safari.title, safari.from)}">Book now</a>
        </div>
      </aside>
      <div class="package-rest">
        ${uniqueGallery(safari.gallery, safari.image)}
        <h2 class="ruled">Day-by-day itinerary</h2>
        ${safari.itinerary.map(([title, text, stay], index) => `<details class="day-acc"${index === 0 ? " open" : ""}><summary><span class="day-num">${String(index + 1).padStart(2, "0")}</span><span>Day ${index + 1} | ${esc(title)}</span></summary><p>${esc(text)}</p><p class="fine">Accommodation: ${esc(stay)}</p></details>`).join("")}
        <h2 class="ruled">Where you sleep</h2>
        <p>Budget is a permanent tent or a simple lodge. Mid-range is an en-suite lodge or tented camp. Luxury is a smaller camp and, where it helps, a flight between parks. The camp names are written on your quote.</p>
        <div class="ie">
          <div><h2 class="ruled">What's included</h2><ul class="yes">${includedBase.map((item) => `<li>${esc(item)}</li>`).join("")}</ul></div>
          <div><h2 class="ruled">What's excluded</h2><ul class="no">${excludedBase.map((item) => `<li>${esc(item)}</li>`).join("")}</ul></div>
        </div>
        <h2 class="ruled">Rates by season</h2>
        ${currencySwitch()}
        <table class="rate-table"><thead><tr><th>Season</th><th>Budget</th><th>Mid-range</th><th>Luxury</th></tr></thead><tbody>
          <tr><td>Peak, July–October and late December</td><td>${money(safari.rates.budget)}</td><td>${money(safari.rates.mid)}</td><td>${money(safari.rates.luxury)}</td></tr>
          <tr><td>Shoulder, June, November and January–February</td><td>${money(Math.round(safari.rates.budget * 0.9 / 10) * 10)}</td><td>${money(Math.round(safari.rates.mid * 0.9 / 10) * 10)}</td><td>${money(Math.round(safari.rates.luxury * 0.9 / 10) * 10)}</td></tr>
          <tr><td>Green, April–May, where camps stay open</td><td>${money(Math.round(safari.rates.budget * 0.82 / 10) * 10)}</td><td>${money(Math.round(safari.rates.mid * 0.82 / 10) * 10)}</td><td>${money(Math.round(safari.rates.luxury * 0.82 / 10) * 10)}</td></tr>
        </tbody></table>
        <p class="fine">${esc(priceNote)}</p>
        ${related.length ? `<h2 class="ruled">Places on this route</h2><div class="dest-grid">${related.map(destCard).join("")}</div>` : ""}
      </div>
    </div>`,
  });
}

function countryPage(name, path, intro, section) {
  const parks = destinations.filter((dest) => dest.country === name);
  const used = new Set();
  const places = parks.map((dest) => placeCard(dest, takeImage(used, dest.image, name))).join("");
  return layout({
    title: `${name} Safaris | ${site.name}`,
    description: intro,
    path,
    image: parks[0]?.image || "kilimanjaro",
    body: `<section class="banner package-banner">
      ${img(parks[0]?.image || "kilimanjaro", { eager: true, className: "banner-media" })}
      <div class="banner-copy wrap">
        <p class="crumbs"><a href="/">Home</a> · Destinations</p>
        <h1>${esc(name)}</h1>
      </div>
    </section>
    <div class="wrap page-block">
      <p>${esc(intro)}</p>
      <h2 class="ruled">Packages</h2>
      <div class="offers">${safaris.filter((safari) => safari.country === name || safari.tags.includes(name)).map(safariOffer).join("")}${catalog.filter((tour) => tour.section === section).map(catalogOffer).join("")}</div>
      <h2 class="ruled">Parks and places</h2>
      <div class="place-grid">${places}</div>
    </div>`,
  });
}

const pages = [
  ["/", home],
  ["/safaris/", () => layout({
    title: `Safari packages | ${site.name}`,
    description: "Private safari packages in Kenya, Tanzania, Uganda and Rwanda, with duration and indicative from-prices.",
    path: "/safaris/",
    body: `<div class="wrap page-hero"><p class="crumbs"><a href="/">Home</a></p><h1>Packages</h1><p>Open a package for the day-by-day plan, then book it with your travel date. Prices on the private routes are planning figures.</p></div>
      <div class="wrap page-block"><div class="offers">${safaris.map(safariOffer).join("")}${catalog.map(catalogOffer).join("")}</div>
      <h2 id="styles" class="ruled">Ways to travel</h2>
      <div class="offers offers-4">${styles.map((style) => `<a class="offer" href="/safari-styles/${style.slug}/"><div class="offer-photo">${img(style.image)}</div><div class="offer-body"><h3>${esc(style.name)}</h3><div class="offer-foot"><span>View</span><span class="offer-go">Open</span></div></div></a>`).join("")}</div>
      </div>`,
  })],
  ["/kenya-safaris/", () => countryPage("Kenya", "/kenya-safaris/", "Kenya is where most of our guests start. Short safaris of one to three days sit beside the longer Mara, Amboseli, Tsavo, Samburu, Nakuru and Ol Pejeta routes. Lake Naivasha, Hell’s Gate, Nairobi National Park, Meru, Mount Kenya, Shimba Hills and the Aberdares are on the same list when the days fit.", "short-kenya-safaris")],
  ["/tanzania-safaris/", () => countryPage("Tanzania", "/tanzania-safaris/", "Tanzania trips start in Arusha. The five-day Serengeti and Ngorongoro route and the seven-day route that adds Tarangire and Lake Manyara are both here, with Zanzibar when you want a beach at the end.", "tanzania-safaris")],
  ["/uganda-safaris/", () => countryPage("Uganda", "/uganda-safaris/", "Uganda safaris are built around gorilla trekking in Bwindi Impenetrable National Park and, when a permit is free, Mgahinga. You spend one hour with a habituated family. Queen Elizabeth adds a savannah drive and the Kazinga boat when you have the days. Permits are quoted on their own line.", "uganda-safaris")],
  ["/rwanda-safaris/", () => countryPage("Rwanda", "/rwanda-safaris/", "Rwanda is the land of a thousand hills: gorilla trekking in Volcanoes National Park, savannah in Akagera, chimpanzees and a canopy walk in Nyungwe, and a lake night on Kivu. The gorilla permit is the main cost and it is shown on its own line.", "rwanda-safaris")],
  ["/best-time-to-visit/", () => layout({
    title: `Best time to visit East Africa | ${site.name}`,
    description: "A month-by-month wildlife calendar for Kenya, Tanzania, Uganda and Rwanda, including the Great Migration.",
    path: "/best-time-to-visit/",
    image: "migration",
    body: `<div class="wrap page-hero"><h1>Best time to visit</h1><p>Wildlife does not follow a single high season across four countries. Use this as a planning calendar, then tell us the month you can actually travel.</p></div>
    <div class="wrap calendar">${months.map(([month, text, tag]) => `<article class="month"><b>${esc(month)}</b><span class="badge">${esc(tag)}</span><p>${esc(text)}</p></article>`).join("")}</div>
    <div class="wrap prose"><h2>The migration, without the slogan</h2>
    <p>The herds calve on the southern Serengeti around January and February. They move west toward the Grumeti around May and June, then north. Mara River crossings, on the Kenya or Tanzania bank, are most often looked for from July to October. By November the movement is south again. None of those sentences is a promise for a given week.</p>
    <p><a class="btn btn-green" href="/contact/">Tell us your month</a></p></div>`,
  })],
  ["/about/", () => layout({
    bodyClass: "about-page",
    title: `About us and our guides | ${site.name}`,
    description: "Magical East Africa Heritage Safaris is a Nairobi safari planner for private trips in Kenya, Tanzania, Uganda and Rwanda.",
    path: "/about/",
    image: "acacia-sunset",
    body: `<section class="banner page-banner">
      ${img("acacia-sunset", { eager: true, className: "banner-media" })}
      <div class="banner-copy wrap">
        <p class="kicker">Our story</p>
        <h1>About us</h1>
        <p class="crumbs"><a href="/">Home</a> · About us</p>
      </div>
    </section>
    <section><div class="wrap about-grid">
      <div>
        <p class="kicker">Who we are</p>
        <h2>About us</h2>
        <p><strong>${esc(site.name)}</strong> is a safari company based in Nairobi. Kenya is the flagship: the Mara, Amboseli, Tsavo, Samburu, Nakuru and Ol Pejeta. We also plan Tanzania, Uganda and Rwanda when the days, and the permit budget, are real.</p>
        <p>The journeys are private, or a shared vehicle when you choose a group safari. The same desk writes the itinerary, names the lodges on the quote, and stays on the phone from the first message until you are home. On the vehicle, guests write about guide James Ngigi. Nicole helps shape the plan before you book. Grace is the person guests have reached after the deposit.</p>
        <p>The name on this website is the name on our email address and the name that should appear on your invoice. Some reviews shorten it to Magical Africa Safaris. That is the same desk.</p>
        <p>We are listed on SafariBookings and SafariOptions. Ask us for the Tourism Regulatory Authority licence before you pay a deposit.</p>
      </div>
      <figure class="about-figure">${img("safari-vehicle", { className: "about-photo" })}</figure>
    </div></section>
    <section class="about-band"><div class="wrap">
      <div class="mission-grid">
        <article><p class="kicker">Our mission</p><p>Personal safari plans that connect travellers with East Africa’s wildlife, parks and cultures, with the price written down before anyone pays.</p></article>
        <article><p class="kicker">Our vision</p><p>To be a trusted desk for authentic safaris in Kenya, Tanzania, Uganda and Rwanda.</p></article>
      </div>
      <div class="section-intro">
        <p class="kicker">What drives us</p>
        <h2>Our core values</h2>
      </div>
      <div class="values">
        ${[
          ["Customer satisfaction", "The route is built around your month, your group and the way you like to travel."],
          ["Professionalism", "One name, one phone number, and a written quote before you pay."],
          ["Sustainability", "Park tracks, conservancies where they fit, and community visits paid to that community."],
          ["Safety", "A licensed operator, a known vehicle, and a meeting point confirmed with your flight."],
          ["Authenticity", "The parks, the wildlife and the people on the route, not a staged version of them."],
        ].map(([title, text]) => `<article class="value"><span aria-hidden="true">✓</span><div><strong>${title}</strong><p>${text}</p></div></article>`).join("")}
      </div>
    </div></section>
    <section class="cta-band">
      <h2>Ready to explore East Africa?</h2>
      <p>Tell us the month and who is travelling. We reply with a plan and a written price.</p>
      <div class="hero-actions" style="justify-content:center">
        <a class="btn btn-clay" href="/safaris/">View packages</a>
        <a class="btn btn-ghost" href="/contact/">Get in touch</a>
      </div>
    </section>`,
  })],
  ["/travel-info/", () => layout({
    title: `Kenya and East Africa travel information | ${site.name}`,
    description: "Visas, health, packing, tipping and safety notes for safaris in Kenya, Tanzania, Uganda and Rwanda.",
    path: "/travel-info/",
    jsonld: [{
      "@context": "https://schema.org",
      "@type": "FAQPage",
      mainEntity: faqs.map((item) => ({ "@type": "Question", name: item.q, acceptedAnswer: { "@type": "Answer", text: item.a } })),
    }],
    body: `<div class="wrap page-hero"><h1>Travel information</h1><p>Practical notes for a safari. Health decisions belong to your doctor. Visa decisions belong to the official government site for your passport.</p></div>
    <div class="wrap prose">
      <h2>Visas</h2>
      <p>Kenya asks most visitors to apply for an electronic travel authorisation before they fly. Tanzania, Uganda and Rwanda each publish their own eVisa or visa-on-arrival rules. Some passports can use an East Africa tourist visa covering Kenya, Uganda and Rwanda. We send the official link that matches your nationality when we quote. Do not buy a visa from a random third-party advert.</p>
      <h2>Health</h2>
      <p>See a travel clinic four to six weeks before departure. Much of the safari circuit is a malaria area, and prophylaxis is a medical decision, not one we make for you. Yellow fever certificates are required on some routes, especially if you enter from a country where the disease is present. Carry the certificate if your clinic or the destination says you need it.</p>
      <h2>Flights</h2>
      <p>Kenya trips use Jomo Kenyatta International Airport in Nairobi, code NBO. Tanzania trips use Kilimanjaro or Arusha, and we can book the hop from Nairobi. Uganda uses Entebbe, code EBB. Rwanda uses Kigali, code KGL. Tell us the flight number and we confirm who will meet you.</p>
      <h2>Packing</h2>
      <ul>
        <li>Neutral colours: khaki, olive, brown. Leave the bright blue at home.</li>
        <li>A warm layer for dawn drives. The Mara and the crater rim are cold before sunrise.</li>
        <li>A scarf or buff against dust, a hat, and sunscreen.</li>
        <li>Binoculars. A phone camera is enough for most people. A dust cover helps if you bring a longer lens.</li>
        <li>Soft bag rather than a hard suitcase. Light aircraft have a weight limit, often 15 kilograms in soft luggage.</li>
        <li>Any prescription medicine in original packaging, plus a basic personal kit. Lodges are not pharmacies.</li>
      </ul>
      <h2>Tipping</h2>
      <p>Tips are not included and they are not a fee. Guests often give the guide about USD 10 to 20 per guest per day, and a similar pooled amount for camp staff, shared across the group. Give it at the end, in US dollars or local currency, and only if the service earned it.</p>
      <h2>Money and safety</h2>
      <p>Cards work in Nairobi and in larger lodges. Small shops and tips are easier with cash. Use ATMs in the city, not a promise of one in the park. Book through a company that will show you a licence, meet you in arrivals, and keep the same phone number from the first email to the last day.</p>
      <p>On game drives, stay seated in the vehicle unless the guide says the park allows a walk. Do not ask the driver to leave the road for a photograph.</p>
      <h2>Common questions</h2>
      <div class="faq">${faqs.map((item) => `<details><summary>${esc(item.q)}</summary><p>${esc(item.a)}</p></details>`).join("")}</div>
    </div>`,
  })],
  ["/booking-policy/", () => layout({
    title: `Booking and cancellation policy | ${site.name}`,
    description: "Deposits, final payment, cancellation terms and how to pay Magical East Africa Heritage Safaris.",
    path: "/booking-policy/",
    body: `<div class="wrap page-hero"><h1>Booking policy</h1><p>These are our standard terms. Your invoice repeats the ones that apply to your trip. Supplier rules for a particular camp or a gorilla permit can be stricter, and those rules are written on the invoice before you pay.</p></div>
    <div class="wrap prose">
      <h2>Deposit and balance</h2>
      <p>A deposit of 30 percent of the quoted price confirms the booking. The balance is due 60 days before arrival. If you book inside 60 days, the full amount is due when you confirm.</p>
      <h2>How to pay</h2>
      <p>We invoice in US dollars and accept international bank transfer. If a card payment is available for your booking, the invoice will say so. Account details appear on the invoice and are confirmed by phone if you ask. We will not ask you to switch to a new account by WhatsApp alone. If that message arrives, call ${esc(site.phone)} before you send money.</p>
      <h2>Cancellation</h2>
      <ul>
        <li>More than 60 days before arrival: the deposit is retained. If suppliers release the money, we can move the trip to a new date within 12 months.</li>
        <li>59 to 31 days before arrival: 50 percent of the trip cost.</li>
        <li>30 days or fewer: 100 percent of the trip cost.</li>
      </ul>
      <p>Gorilla permits, once issued, are often non-refundable at the park. That cost is lost even if the rest of the trip is moved. Travel insurance that covers cancellation is your responsibility, and we recommend you buy it when you pay the deposit.</p>
      <h2>Changes</h2>
      <p>You can ask for a change of date or lodge. We will tell you the supplier’s fee before we make the change. We may also change a lodge if a camp closes a season. You get the alternative in writing, at the same level, or a refund of the difference.</p>
      <h2>What the price assumes</h2>
      <p>Quotes assume two people sharing a room unless they say otherwise. A single room, extra nights, flights and permits are listed as their own lines. Park fees are included for the parks named on the itinerary.</p>
    </div>`,
  })],
  ["/excursions/", () => layout({
    title: `Day trips and excursions | ${site.name}`,
    description: "Private day trips from Nairobi and from the Kenya coast, with honest notes on which ones are too long.",
    path: "/excursions/",
    body: `<div class="wrap page-hero"><h1>Excursions and day trips</h1><p>These sit beside a longer safari or a beach stay. A day in Amboseli, Ol Pejeta or Tsavo from Nairobi is mostly driving, and the notes on those pages say so. Cultural days in Nairobi and Mombasa, Wasini, Watamu, the coast chama stay and the student beach package are here as well.</p></div>
    <div class="wrap page-block"><div class="offers">${catalog.filter((tour) => tour.section === "excursions").map(catalogOffer).join("")}</div></div>`,
  })],
  ["/group-safaris/", () => layout({
    title: `Small group safaris | ${site.name}`,
    description: "Small group safari departures for up to six guests, arranged when several enquiries share a month.",
    path: "/group-safaris/",
    body: `<div class="wrap page-hero"><h1>Group safaris</h1>
      <p>These are shared-vehicle departures for solo travellers, couples and anyone who wants the Mara without paying for a private car. The migration departures are planned for July to October. A date is only a departure once the vehicle has the guests. If your month does not match, the same route can still be done privately.</p></div>
      <div class="wrap page-block"><div class="offers">${catalog.filter((tour) => tour.section === "group-safaris").map(catalogOffer).join("")}</div></div>`,
  })],
  ["/blog/", () => layout({
    title: `Safari journal | ${site.name}`,
    description: "Notes on choosing between the Masai Mara and the Serengeti, and on what a Kenya safari costs.",
    path: "/blog/",
    body: `<div class="wrap page-hero"><h1>Journal</h1><p>Two articles to read before you ask for a quote. More will go here when we have something useful to add, not on a schedule for its own sake.</p></div>
    <div class="wrap posts">${posts.map((post) => `<article class="card">${img(post.image)}<div class="safari-body"><p class="fine">${esc(post.date)}</p><h2><a href="/blog/${post.slug}/">${esc(post.title)}</a></h2><p>${esc(post.excerpt)}</p></div></article>`).join("")}</div>`,
  })],
  ["/book/", () => layout({
    title: `Book your safari | ${site.name}`,
    description: "Reservation form for a Magical East Africa Heritage Safaris package. Choose a date and the number of travellers.",
    path: "/book/",
    body: `<div class="wrap page-hero"><p class="crumbs"><a href="/">Home</a> · <a href="/safaris/">Packages</a> · Book now</p><h1>Book your safari</h1><p>Reservation form. We confirm availability and send the price in writing.</p></div>
    <div class="wrap page-block">${bookingForm()}</div>`,
  })],
  ["/gallery/", () => layout({
    title: `Gallery | ${site.name}`,
    description: "Photographs from the parks and coasts on our East African safari routes.",
    path: "/gallery/",
    image: "serengeti",
    body: `<div class="wrap page-hero"><p class="crumbs"><a href="/">Home</a></p><h1>Gallery</h1><p>Parks, wildlife and coast on the routes we plan.</p></div>
    <div class="wrap gallery page-block">${Object.keys(images).map((key) => img(key, { className: "media square" })).join("")}</div>`,
  })],
  ["/faqs/", () => layout({
    title: `FAQs | ${site.name}`,
    description: "Answers before you book a safari with Magical East Africa Heritage Safaris.",
    path: "/faqs/",
    body: `<div class="wrap page-hero"><p class="crumbs"><a href="/">Home</a></p><h1>FAQs</h1><p>The questions guests ask before they book.</p></div>
    <div class="wrap page-block faq">${faqs.map((item) => `<details><summary>${esc(item.q)}</summary><p>${esc(item.a)}</p></details>`).join("")}
    <p><a class="btn btn-clay" href="/book/">Book a safari</a></p></div>`,
  })],
  ["/contact/", () => layout({
    title: `Contact | ${site.name}`,
    description: "Contact Magical East Africa Heritage Safaris in Nairobi by phone, email, WhatsApp or the reservation form.",
    path: "/contact/",
    image: "safari-vehicle",
    body: `<section class="banner page-banner">
      ${img("safari-vehicle", { eager: true, className: "banner-media" })}
      <div class="banner-copy wrap">
        <p class="kicker">Get in touch</p>
        <h1>Contact us</h1>
        <p class="crumbs"><a href="/">Home</a> · Contact us</p>
      </div>
    </section>
    <section><div class="wrap contact-grid">
      <div>
        <p class="kicker">Reach out</p>
        <h2>We would like to hear from you</h2>
        <p>Whether you are ready to book or you want a question answered, write or call. The same desk replies by email or WhatsApp.</p>
        <ul class="reach">
          <li><span aria-hidden="true">⌖</span><div><small>Address</small><strong>${esc(site.city)}</strong><p class="fine">We meet you at the airport. The point is confirmed with your flight number.</p></div></li>
          <li><span aria-hidden="true">✉</span><div><small>Email</small><a href="mailto:${site.email}">${esc(site.email)}</a></div></li>
          <li><span aria-hidden="true">☎</span><div><small>Phone</small><a href="tel:${site.phoneHref}">${esc(site.phone)}</a></div></li>
          <li><span aria-hidden="true">✆</span><div><small>WhatsApp</small><a href="https://wa.me/${site.whatsapp}" target="_blank" rel="noopener">Chat on WhatsApp</a></div></li>
        </ul>
        <p><a href="https://www.google.com/maps/search/?api=1&query=Nairobi%2C%20Kenya" target="_blank" rel="noopener">Open Nairobi in Maps</a></p>
      </div>
      <form class="contact-card message-form" data-enquiry data-whatsapp-number="${site.whatsapp}" data-email-address="${site.email}">
        <h2>Send a message</h2>
        <p class="fine">We reply by email or WhatsApp. Nothing is charged on this form. To book a package, use <a href="/book/">Book now</a>.</p>
        <div class="form-grid">
          <div class="form-grid form-2">
            <label>Full name<input name="name" autocomplete="name" required></label>
            <label>Phone<input name="phone" autocomplete="tel" required></label>
          </div>
          <div class="form-grid form-2">
            <label>Email address<input name="email" type="email" autocomplete="email" required></label>
            <label>Country<select name="country"><option value="">Your country</option>${bookCountries.map((country) => `<option>${esc(country)}</option>`).join("")}</select></label>
          </div>
          <label>Message<textarea name="note" rows="5" placeholder="Tell us about your travel plans" required></textarea></label>
          <input type="hidden" name="trip" value="Contact message">
          <button class="btn btn-green" type="submit">Send message</button>
          <div class="result" data-result>
            <strong>Your message is ready to send.</strong>
            <a class="btn btn-green" data-whatsapp href="https://wa.me/${site.whatsapp}" target="_blank" rel="noopener">Send on WhatsApp</a>
            <a class="btn btn-line" data-email href="mailto:${site.email}">Send by email</a>
          </div>
        </div>
      </form>
    </div></section>`,
  })],
  ["/privacy/", () => layout({
    title: `Privacy | ${site.name}`,
    description: "How Magical East Africa Heritage Safaris uses enquiry details.",
    path: "/privacy/",
    body: `<div class="wrap prose page-hero"><h1>Privacy</h1>
      <p>The enquiry form on this site does not store your message on a server. It prepares a WhatsApp or email message that you choose to send. If you email or message us, we use your name, contact details and trip notes to reply and, if you book, to run the safari.</p>
      <p>We do not sell mailing lists. A newsletter request is a request for us to write to you. You can ask us to delete an enquiry by emailing ${esc(site.email)}.</p>
      <p>If a Google Analytics ID is later added to the site, page views and form or WhatsApp clicks can be measured. Until that ID is set, the analytics script is not loaded.</p></div>`,
  })],
  ["/image-credits/", () => layout({
    title: `Photograph credits | ${site.name}`,
    description: "Licences and authors for the photographs on this website.",
    path: "/image-credits/",
    body: `<div class="wrap page-hero"><h1>Photograph credits</h1><p>These pictures are used under the licence named below. They replace photographs that previously carried other lodge brands. We have resized them for the web.</p></div>
    <div class="wrap"><table><thead><tr><th>Photograph</th><th>Author</th><th>Licence</th></tr></thead><tbody>
      ${credits.map((item) => `<tr><td>${esc(item.alt)}</td><td>${esc(item.author)}</td><td><a href="${esc(item.source)}">${esc(item.license)}</a></td></tr>`).join("")}
    </tbody></table></div>`,
  })],
];

for (const [path, render] of pages) writePage(path, render());
for (const safari of safaris) writePage(`/safaris/${safari.slug}/`, safariDetail(safari));
for (const tour of catalog) writePage(`/safaris/${tour.slug}/`, catalogDetail(tour));
writePage("/destinations/local/", layout({
  title: `Local destinations | ${site.name}`,
  description: "Kenya safari destinations, from the Maasai Mara and Amboseli to Naivasha, Meru, Shimba Hills and the Aberdares.",
  path: "/destinations/local/",
  image: "migration",
  body: `<div class="wrap page-hero"><p class="kicker">Destinations</p><h1>Local destinations</h1><p>Kenya is the local circuit: the Mara, Amboseli, both Tsavos, Samburu, Nakuru, Ol Pejeta, Naivasha, Hell’s Gate, Nairobi National Park, Meru, Mount Kenya, Shimba Hills and the Aberdares.</p></div>
  <div class="wrap page-block place-grid">${(() => { const used = new Set(); return destinations.filter((dest) => dest.country === "Kenya").map((dest) => placeCard(dest, takeImage(used, dest.image, "Kenya"))).join(""); })()}</div>`,
}));
writePage("/destinations/international/", layout({
  title: `International destinations | ${site.name}`,
  description: "Safari destinations in Tanzania, Uganda and Rwanda.",
  path: "/destinations/international/",
  image: "serengeti",
  body: `<div class="wrap page-hero"><p class="kicker">Destinations</p><h1>International destinations</h1><p>Beyond Kenya: the Serengeti, Ngorongoro and Zanzibar; Bwindi, Mgahinga and Queen Elizabeth; Volcanoes, Akagera, Nyungwe and Lake Kivu.</p></div>
  <div class="wrap page-block">${["Tanzania", "Uganda", "Rwanda"].map((country) => {
    const used = new Set();
    const parks = destinations.filter((dest) => dest.country === country);
    return `<h2>${country}</h2><div class="place-grid">${parks.map((dest) => placeCard(dest, takeImage(used, dest.image, country))).join("")}</div>`;
  }).join("")}</div>`,
}));
for (const dest of destinations) {
  const token = dest.name.replace(" East and West", "").replace(" National Park", "").toLowerCase();
  const trips = safaris.filter((safari) => `${safari.title} ${safari.route.join(" ")}`.toLowerCase().includes(token));
  writePage(`/destinations/${dest.slug}/`, layout({
    title: `${dest.name} safari | ${site.name}`,
    description: dest.summary,
    path: `/destinations/${dest.slug}/`,
    image: dest.image,
    body: `<div class="wrap page-hero"><p class="kicker">${esc(dest.country)}</p><h1>${esc(dest.name)}</h1><p>${esc(dest.tagline)}</p></div>
    <div class="wrap split"><div class="prose">${img(dest.image, { eager: true })}<p>${esc(dest.summary)}</p><p><strong>Best months.</strong> ${esc(dest.best)}</p><p><strong>How long to stay.</strong> ${esc(dest.stay)}</p></div>
    <div class="plan-card"><h2>Book this place</h2><p>Tell us the date and who is travelling. The reservation form carries ${esc(dest.name)} into the summary.</p><a class="btn btn-clay" href="${bookLink(dest.name)}">Book now</a></div></div>
    ${trips.length ? `<div class="wrap page-block"><h2>Trips that include this place</h2><div class="trip-list">${trips.map((safari) => tripRow({ href: `/safaris/${safari.slug}/`, title: safari.title, meta: `${safari.days} days · ${safari.country}` })).join("")}</div></div>` : ""}`,
  }));
}
for (const style of styles) {
  const trips = style.tag === "budget" || style.tag === "luxury"
    ? safaris
    : safaris.filter((safari) => safari.tags.includes(style.tag));
  writePage(`/safari-styles/${style.slug}/`, layout({
    title: `${style.name} | ${site.name}`,
    description: style.summary,
    path: `/safari-styles/${style.slug}/`,
    image: style.image,
    body: `<div class="wrap page-hero"><h1>${esc(style.name)}</h1><p>${esc(style.summary)}</p></div>
    <div class="wrap page-block trip-list">${trips.map((safari) => tripRow({ href: `/safaris/${safari.slug}/`, title: safari.title, meta: `${safari.days} days · ${safari.country}` })).join("")}</div>`,
  }));
}
for (const post of posts) {
  writePage(`/blog/${post.slug}/`, layout({
    title: `${post.title} | ${site.name}`,
    description: post.excerpt,
    path: `/blog/${post.slug}/`,
    image: post.image,
    jsonld: [{
      "@context": "https://schema.org",
      "@type": "Article",
      headline: post.title,
      datePublished: post.date,
      author: { "@type": "Organization", name: site.name },
    }],
    body: `<div class="wrap page-hero"><p class="kicker"><a href="/blog/">Journal</a> · ${esc(post.date)}</p><h1>${esc(post.title)}</h1><p>${esc(post.excerpt)}</p></div>
    <div class="wrap prose">${img(post.image, { eager: true })}${post.paragraphs.map((paragraph) => `<p>${esc(paragraph)}</p>`).join("")}
    <p><a class="btn btn-green" href="/contact/">Plan from this</a></p></div>`,
  }));
}

writeFileSync(join(dist, "404.html"), layout({
  title: `Page not found | ${site.name}`,
  description: "That page is not on this site.",
  path: "/404.html",
  body: `<div class="wrap page-hero"><h1>That page is not here.</h1><p><a href="/">Back to the homepage</a> or <a href="/safaris/">browse the safaris</a>.</p></div>`,
}));

writeFileSync(join(dist, "favicon.svg"), `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" rx="16" fill="#1b3d32"/><circle cx="32" cy="24" r="7" fill="#f0d3b4"/><path d="M32 34c-8 2-14 8-16 14h32c-2-6-8-12-16-14z" fill="#c4a574"/></svg>`);
writeFileSync(join(dist, "robots.txt"), `User-agent: *\nAllow: /\nSitemap: ${site.domain}/sitemap.xml\n`);
writeFileSync(join(dist, "sitemap.xml"), `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n${routes.map((route) => `  <url><loc>${site.domain}${route === "/" ? "/" : route}</loc></url>`).join("\n")}\n</urlset>\n`);

const files = new Set(routes);
const missing = [];
for (const route of routes) {
  const rel = route === "/" ? "index.html" : `${route.replace(/^\//, "").replace(/\/$/, "")}/index.html`;
  const html = readFileSync(join(dist, rel), "utf8");
  for (const match of html.matchAll(/href="(\/[^"]*)"/g)) {
    const href = match[1].split("?")[0].split("#")[0];
    if (href.startsWith("/images/") || href.startsWith("/css/") || href.startsWith("/js/") || href === "/favicon.svg") continue;
    const normalized = href.endsWith("/") ? href : `${href}/`;
    if (!files.has(normalized) && href !== "/404.html") missing.push(`${route} -> ${href}`);
  }
}
if (missing.length) {
  console.error(missing.join("\n"));
  process.exit(1);
}
console.log(`Built ${routes.length} pages`);
