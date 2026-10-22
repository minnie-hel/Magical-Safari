const RATES = { USD: 1, EUR: 0.92, GBP: 0.78 };

function money(usd, currency) {
  const value = Math.round((usd * RATES[currency]) / 10) * 10;
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency,
    maximumFractionDigits: 0,
  }).format(value);
}

function applyCurrency(currency) {
  document.querySelectorAll("[data-usd]").forEach((node) => {
    const usd = Number(node.getAttribute("data-usd"));
    if (!Number.isFinite(usd)) return;
    node.textContent = money(usd, currency);
  });
  document.querySelectorAll("[data-currency]").forEach((button) => {
    button.setAttribute("aria-pressed", button.getAttribute("data-currency") === currency ? "true" : "false");
  });
  localStorage.setItem("mehs-currency", currency);
}

function track(name, params) {
  if (typeof window.gtag === "function") window.gtag("event", name, params || {});
}

function loadAnalytics() {
  const id = window.MEHS_GA_ID;
  if (!id) return;
  const script = document.createElement("script");
  script.async = true;
  script.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(id)}`;
  document.head.appendChild(script);
  window.dataLayer = window.dataLayer || [];
  window.gtag = function gtag() { window.dataLayer.push(arguments); };
  window.gtag("js", new Date());
  window.gtag("config", id);
}

function formPayload(form) {
  const data = new FormData(form);
  const payload = {};
  for (const [key, value] of data.entries()) {
    if (key === "channel" || key === "terms" || key === "mehs_leave_blank") continue;
    if (key === "interest") {
      payload.interest = payload.interest || [];
      payload.interest.push(String(value));
    } else if (payload[key] === undefined) {
      payload[key] = String(value);
    }
  }
  const total = form.querySelector("[data-total]")?.textContent?.trim();
  if (total) payload.total = total;
  return payload;
}

function formSummary(form) {
  const payload = formPayload(form);
  const rows = [
    ["Name", payload.name],
    ["Email", payload.email],
    ["Phone", payload.phone],
    ["WhatsApp", payload.whatsapp],
    ["Country", payload.country],
    ["Travel date", payload.date],
    ["Travel month", payload.month],
    ["People", payload.adults],
    ["Children", payload.children],
    ["Budget", payload.budget],
    ["Interests", Array.isArray(payload.interest) ? payload.interest.join(", ") : ""],
    ["Trip", payload.trip || "Not chosen yet"],
    ["Estimated total", payload.total],
    ["Note", payload.note],
  ];
  const lines = ["Booking for Magical East Africa Heritage Safaris"];
  rows.forEach(([label, value]) => {
    const text = String(value ?? "").trim();
    if (!text || (label === "Children" && text === "0")) return;
    lines.push(`${label}: ${text}`);
  });
  return lines.join("\n").slice(0, 1400);
}

function showStatus(result, message, isError) {
  if (!result) return;
  result.classList.add("is-on");
  result.classList.toggle("is-error", Boolean(isError));
  const status = result.querySelector("[data-status]");
  if (status) status.textContent = message;
  result.querySelectorAll("a").forEach((link) => link.remove());
}

function bindForms() {
  document.querySelectorAll("form[data-enquiry]").forEach((form) => {
    form.addEventListener("submit", (event) => {
      event.preventDefault();
      if (!form.reportValidity()) return;
      const channel = event.submitter?.value || "email";
      const result = form.querySelector("[data-result]");
      if (channel === "whatsapp") {
        const phone = (form.getAttribute("data-whatsapp-number") || "").replace(/\D/g, "");
        const url = `https://wa.me/${phone}?text=${encodeURIComponent(formSummary(form))}`;
        const opened = window.open(url, "_blank");
        if (opened) {
          try { opened.opener = null; } catch (error) { /* The chat can still open. */ }
        }
        showStatus(
          result,
          opened
            ? "WhatsApp is open with your booking. Press send so the office receives it."
            : "WhatsApp was blocked by the browser. Use the button below.",
          !opened,
        );
        if (!opened && result) {
          const link = document.createElement("a");
          link.className = "btn btn-green";
          link.href = url;
          link.target = "_blank";
          link.rel = "noopener";
          link.textContent = "Open WhatsApp";
          result.appendChild(link);
        }
        track("generate_lead", { method: "whatsapp" });
        return;
      }
      sendEmail(form, result);
    });
  });
}

async function sendEmail(form, result) {
  const buttons = [...form.querySelectorAll("button")];
  buttons.forEach((button) => { button.disabled = true; });
  showStatus(result, "Sending your booking to the office…");
  try {
    const payload = formPayload(form);
    if (window.MEHS_BOOK && window.MEHS_BOOK.nonce) payload.nonce = window.MEHS_BOOK.nonce;
    const response = await fetch((window.MEHS_BOOK && window.MEHS_BOOK.url) || "/api/book.php", {
      method: "POST",
      headers: { Accept: "application/json", "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    const data = await response.json().catch(() => ({}));
    if (response.status === 400) {
      showStatus(result, data.error || "Check the form and try again.", true);
      return;
    }
    if (!response.ok || !data.ok) throw new Error("mail");
    showStatus(result, "Sent. The office has this booking by email and can reply to you.");
    track("generate_lead", { method: "email" });
  } catch (error) {
    const address = form.getAttribute("data-email-address") || "";
    const href = `mailto:${address}?subject=${encodeURIComponent("Safari booking")}&body=${encodeURIComponent(formSummary(form))}`;
    showStatus(result, "The server could not send the email. Open your email app and press send.", true);
    if (result) {
      const link = document.createElement("a");
      link.className = "btn btn-line";
      link.href = href;
      link.textContent = "Open email";
      result.appendChild(link);
    }
  } finally {
    buttons.forEach((button) => { button.disabled = false; });
  }
}

const LANGUAGES = {
  en: ["EN", "🇬🇧"],
  fr: ["FR", "🇫🇷"],
  de: ["DE", "🇩🇪"],
  es: ["ES", "🇪🇸"],
  it: ["IT", "🇮🇹"],
  pt: ["PT", "🇵🇹"],
  nl: ["NL", "🇳🇱"],
  "zh-CN": ["ZH", "🇨🇳"],
};

function currentLanguage() {
  const match = document.cookie.match(/(?:^|; )googtrans=\/en\/([^;]+)/);
  const code = match ? decodeURIComponent(match[1]) : "en";
  return LANGUAGES[code] ? code : "en";
}

function loadTranslator(code) {
  const apply = () => {
    const select = document.querySelector(".goog-te-combo");
    if (!select) return false;
    select.value = code;
    select.dispatchEvent(new Event("change"));
    return true;
  };
  if (apply()) return;
  window.googleTranslateElementInit = () => {
    new window.google.translate.TranslateElement({
      pageLanguage: "en",
      includedLanguages: Object.keys(LANGUAGES).join(","),
      autoDisplay: false,
    }, "google_translate_element");
    const timer = window.setInterval(() => {
      if (apply()) window.clearInterval(timer);
    }, 200);
    window.setTimeout(() => window.clearInterval(timer), 8000);
  };
  if (!document.querySelector("script[data-gt]")) {
    const script = document.createElement("script");
    script.src = "https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit";
    script.dataset.gt = "1";
    document.body.appendChild(script);
  }
}

function bindLanguage() {
  const root = document.querySelector("[data-lang]");
  if (!root) return;
  const button = root.querySelector("[data-lang-toggle]");
  const menu = root.querySelector("[data-lang-menu]");
  const codeNode = root.querySelector("[data-lang-code]");
  const flagNode = root.querySelector("[data-lang-flag]");
  const paint = () => {
    const code = currentLanguage();
    const [label, flag] = LANGUAGES[code];
    codeNode.textContent = label;
    flagNode.textContent = flag;
    menu.querySelectorAll("[data-set-lang]").forEach((item) => {
      item.setAttribute("aria-current", item.getAttribute("data-set-lang") === code ? "true" : "false");
    });
  };
  paint();
  button.addEventListener("click", (event) => {
    event.stopPropagation();
    const open = menu.hidden;
    menu.hidden = !open;
    button.setAttribute("aria-expanded", open ? "true" : "false");
  });
  menu.querySelectorAll("[data-set-lang]").forEach((item) => {
    item.addEventListener("click", () => {
      const code = item.getAttribute("data-set-lang");
      if (code === currentLanguage()) {
        menu.hidden = true;
        button.setAttribute("aria-expanded", "false");
        return;
      }
      if (code === "en") {
        document.cookie = "googtrans=;path=/;max-age=0";
        document.cookie = `googtrans=;path=/;domain=${location.hostname};max-age=0`;
      } else {
        document.cookie = `googtrans=/en/${code};path=/`;
        document.cookie = `googtrans=/en/${code};path=/;domain=${location.hostname}`;
      }
      location.reload();
    });
  });
  document.addEventListener("click", (event) => {
    if (!root.contains(event.target)) {
      menu.hidden = true;
      button.setAttribute("aria-expanded", "false");
    }
  });
  const active = currentLanguage();
  if (active !== "en") loadTranslator(active);
}

function bindDrop() {
  document.querySelectorAll("[data-drop-toggle]").forEach((button) => {
    const menu = button.parentElement.querySelector("[data-drop-menu]");
    if (!menu) return;
    const close = () => {
      menu.hidden = true;
      button.setAttribute("aria-expanded", "false");
    };
    button.addEventListener("click", (event) => {
      event.stopPropagation();
      const open = menu.hidden;
      menu.hidden = !open;
      button.setAttribute("aria-expanded", open ? "true" : "false");
    });
    document.addEventListener("click", (event) => {
      if (!button.parentElement.contains(event.target)) close();
    });
    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape") close();
    });
  });
}

function bindBooking() {
  const params = new URLSearchParams(location.search);
  const trip = params.get("trip");
  const usd = Number(params.get("usd"));
  if (trip) {
    document.querySelectorAll(".summary-name").forEach((node) => { node.textContent = trip; });
    document.querySelectorAll('input[name="trip"]').forEach((input) => { input.value = trip; });
  }
  document.querySelectorAll("[data-book]").forEach((form) => {
    const card = form.querySelector("[data-summary]");
    const input = form.querySelector("[data-people-input]");
    const count = form.querySelector("[data-people-count]");
    const total = form.querySelector("[data-total]");
    const per = form.querySelector("[data-per]");
    if (Number.isFinite(usd) && usd > 0 && card) card.setAttribute("data-usd-rate", String(usd));
    const update = () => {
      const people = Math.max(1, Number(input?.value) || 1);
      if (count) count.textContent = String(people);
      const rate = Number(card?.getAttribute("data-usd-rate"));
      if (!Number.isFinite(rate) || rate <= 0) return;
      if (per) per.setAttribute("data-usd", String(rate));
      if (total) total.setAttribute("data-usd", String(rate * people));
      applyCurrency(localStorage.getItem("mehs-currency") || "USD");
    };
    input?.addEventListener("input", update);
    update();
  });
}

function bindTabs() {
  document.querySelectorAll("[data-tabs]").forEach((root) => {
    const tabs = [...root.querySelectorAll("[data-tab]")];
    const panels = [...root.querySelectorAll("[data-panel]")];
    tabs.forEach((tab) => {
      tab.addEventListener("click", () => {
        const name = tab.getAttribute("data-tab");
        tabs.forEach((item) => item.setAttribute("aria-selected", item === tab ? "true" : "false"));
        panels.forEach((panel) => {
          panel.hidden = panel.getAttribute("data-panel") !== name;
        });
      });
    });
  });
}

function bindFilters() {
  const buttons = document.querySelectorAll("[data-filter]");
  const cards = document.querySelectorAll("[data-card]");
  if (!buttons.length) return;
  buttons.forEach((button) => {
    button.addEventListener("click", () => {
      const filter = button.getAttribute("data-filter");
      buttons.forEach((item) => item.setAttribute("aria-pressed", item === button ? "true" : "false"));
      cards.forEach((card) => {
        const tags = card.getAttribute("data-tags") || "";
        card.hidden = filter !== "all" && !tags.split(" ").includes(filter);
      });
    });
  });
}

const floatingWhatsApp = document.querySelector(".wa-float");
const cover = document.querySelector(".hero, .package-banner");
if (floatingWhatsApp && cover && window.matchMedia("(max-width: 759px)").matches) {
  const observer = new IntersectionObserver(([entry]) => {
    floatingWhatsApp.hidden = entry.isIntersecting;
  });
  observer.observe(cover);
}

document.addEventListener("click", (event) => {
  const link = event.target.closest("a[href*='wa.me']");
  if (link) track("whatsapp_click");
});

const saved = localStorage.getItem("mehs-currency") || "USD";
applyCurrency(saved);
bindBooking();
document.querySelectorAll("[data-currency]").forEach((button) => {
  button.addEventListener("click", () => applyCurrency(button.getAttribute("data-currency")));
});
loadAnalytics();
bindForms();

const navToggle = document.querySelector("[data-nav-toggle]");
const navBar = document.querySelector(".nav-bar");
if (navToggle && navBar) {
  navToggle.addEventListener("click", () => {
    const open = navBar.classList.toggle("is-open");
    navToggle.setAttribute("aria-expanded", open ? "true" : "false");
  });
  navBar.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", () => {
      navBar.classList.remove("is-open");
      navToggle.setAttribute("aria-expanded", "false");
    });
  });
}

const backButton = document.querySelector("[data-go-back]");
if (backButton) {
  backButton.addEventListener("click", () => {
    if (window.history.length > 1) {
      window.history.back();
      return;
    }
    window.location.href = "/";
  });
}
const scrollButton = document.querySelector("[data-scroll-page]");
if (scrollButton) {
  const scrollLabel = scrollButton.querySelector("[data-scroll-label]");
  const paintScroll = () => {
    const down = window.scrollY < 280;
    scrollButton.classList.toggle("is-up", !down);
    const label = down ? "Next" : "Top";
    if (scrollLabel) scrollLabel.textContent = label;
    scrollButton.setAttribute("aria-label", down ? "Next section" : "Back to the top");
  };
  scrollButton.addEventListener("click", () => {
    if (window.scrollY < 280) {
      window.scrollBy({ top: Math.round(window.innerHeight * 0.85), behavior: "smooth" });
      return;
    }
    window.scrollTo({ top: 0, behavior: "smooth" });
  });
  paintScroll();
  window.addEventListener("scroll", paintScroll, { passive: true });
}
bindDrop();
bindLanguage();
bindTabs();
bindFilters();

const homeHero = document.querySelector("body.home .banner.hero");
const homePhoto = homeHero && homeHero.querySelector(".banner-media:not(.mehs-hero-slide)");
if (homeHero && homePhoto && !homeHero.querySelector(".mehs-hero-slide")) {
  const src = homePhoto.getAttribute("src") || "";
  const slash = src.lastIndexOf("/");
  const dir = slash >= 0 ? src.slice(0, slash + 1) : "";
  const frames = ["zebra.webp", "flamingo.webp", "lions.webp", "migration.webp", "gorilla.webp", "kilimanjaro.webp", "samburu.webp", "serengeti.webp", "rhino.webp", "amboseli.webp"];
  if (dir) {
    frames.forEach((file) => {
      if (src.endsWith("/" + file) || src.endsWith(file)) return;
      const slide = document.createElement("img");
      slide.className = "banner-media mehs-hero-slide";
      slide.alt = "";
      slide.setAttribute("aria-hidden", "true");
      slide.src = dir + file;
      homePhoto.insertAdjacentElement("afterend", slide);
    });
  }
}
if (homeHero && homePhoto) {
  const photos = Array.prototype.slice.call(homeHero.querySelectorAll(".banner-media"));
  photos.forEach((photo, index) => {
    photo.style.animation = "none";
    photo.style.transform = "none";
    photo.style.opacity = index === 0 ? "1" : "0";
    photo.style.zIndex = index === 0 ? "-2" : "-3";
  });
  if (photos.length > 1 && !window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
    let index = 0;
    window.setInterval(() => {
      const current = photos[index];
      const nextIndex = (index + 1) % photos.length;
      const next = photos[nextIndex];
      current.style.zIndex = "-3";
      next.style.zIndex = "-2";
      next.style.transition = "none";
      next.style.opacity = "0";
      next.getBoundingClientRect();
      next.style.transition = "opacity 0.9s ease";
      next.style.opacity = "1";
      window.setTimeout(() => {
        current.style.transition = "none";
        current.style.opacity = "0";
      }, 940);
      index = nextIndex;
    }, 5200);
  }
}
