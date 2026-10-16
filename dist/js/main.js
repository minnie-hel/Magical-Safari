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

function formSummary(form) {
  const data = new FormData(form);
  const interests = data.getAll("interest");
  const lines = [
    "Safari enquiry for Magical East Africa Heritage Safaris",
    `Name: ${data.get("name") || ""}`,
    `Email: ${data.get("email") || ""}`,
    `Phone: ${data.get("phone") || ""}`,
    `WhatsApp: ${data.get("whatsapp") || ""}`,
    `Country: ${data.get("country") || ""}`,
    `Travel date: ${data.get("date") || ""}`,
    `Travel month: ${data.get("month") || ""}`,
    `People: ${data.get("adults") || ""}`,
    `Children: ${data.get("children") || ""}`,
    `Budget: ${data.get("budget") || ""}`,
    `Interests: ${interests.join(", ") || ""}`,
    `Trip: ${data.get("trip") || "Not chosen yet"}`,
    `Note: ${data.get("note") || ""}`,
  ];
  return lines.filter((line) => !line.endsWith(": ")).join("\n");
}

function bindForms() {
  document.querySelectorAll("form[data-enquiry]").forEach((form) => {
    form.addEventListener("submit", (event) => {
      event.preventDefault();
      if (!form.reportValidity()) return;
      const summary = formSummary(form);
      const result = form.querySelector("[data-result]");
      const whatsapp = form.querySelector("[data-whatsapp]");
      const email = form.querySelector("[data-email]");
      const phone = form.getAttribute("data-whatsapp-number");
      const address = form.getAttribute("data-email-address");
      if (whatsapp) {
        whatsapp.href = `https://wa.me/${phone}?text=${encodeURIComponent(summary)}`;
      }
      if (email) {
        email.href = `mailto:${address}?subject=${encodeURIComponent("Safari enquiry")}&body=${encodeURIComponent(summary)}`;
      }
      result?.classList.add("is-on");
      track("generate_lead", { method: "enquiry_form" });
    });
  });
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
bindDrop();
bindLanguage();
bindTabs();
bindFilters();
