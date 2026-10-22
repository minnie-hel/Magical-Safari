(function () {
  function editor() {
    if (!window.parent || window.parent === window || !window.parent.elementor) {
      return null;
    }
    return window.parent.elementor;
  }

  function walk(nodes, visit) {
    (nodes || []).forEach(function (child) {
      if (!child) {
        return;
      }
      visit(child);
      if (child.children && child.children.length) {
        walk(child.children, visit);
      }
    });
  }

  function collect(app) {
    var found = { headings: [], texts: [], images: [], buttons: [], boxes: [] };
    var doc = app.documents && app.documents.getCurrent && app.documents.getCurrent();
    if (!doc || !doc.container) {
      return found;
    }
    walk(doc.container.children, function (child) {
      var model = child.model;
      if (!model || !model.get) {
        return;
      }
      var type = model.get("widgetType");
      if (type === "heading") found.headings.push(child);
      if (type === "text-editor") found.texts.push(child);
      if (type === "image") found.images.push(child);
      if (type === "button") found.buttons.push(child);
      if (model.get("elType") === "container") found.boxes.push(child);
    });
    return found;
  }

  function tidy(value) {
    return String(value || "").replace(/\s+/g, " ").trim();
  }

  function plain(html) {
    var node = document.createElement("div");
    node.innerHTML = html || "";
    return tidy(node.textContent);
  }

  function markChanged(app) {
    if (app.saver && app.saver.setFlagEditorChange) {
      app.saver.setFlagEditorChange(true);
    }
  }

  function replaceText(app, origin, next) {
    if (!origin || origin === next) {
      return;
    }
    var items = collect(app);
    items.headings.forEach(function (widget) {
      if (tidy(widget.settings.get("title")) === origin) {
        widget.settings.set("title", next);
      }
    });
    items.buttons.forEach(function (widget) {
      if (tidy(widget.settings.get("text")) === origin) {
        widget.settings.set("text", next);
      }
    });
    items.texts.forEach(function (widget) {
      var html = widget.settings.get("editor") || "";
      if (plain(html) === origin || html.indexOf(origin) !== -1) {
        widget.settings.set("editor", html.split(origin).join(next));
      }
    });
    markChanged(app);
  }

  function fileName(url) {
    return String(url || "").split("/").pop().split("?")[0];
  }

  function snapshot(root) {
    var copy = root.cloneNode(true);
    copy.removeAttribute("data-mehs-bound");
    copy.querySelectorAll("p").forEach(function (node) {
      if (node.hasAttribute("data-status") || node.closest("form")) return;
      if (tidy(node.innerText) === "") node.remove();
    });
    copy.querySelectorAll("h1, h2, h3, h4, h5, h6, p, li, td, th, figcaption").forEach(plainField);
    copy.querySelectorAll("[contenteditable], [data-mehs-origin]").forEach(function (node) {
      node.removeAttribute("contenteditable");
      node.removeAttribute("data-mehs-origin");
      node.removeAttribute("spellcheck");
    });
    copy.querySelectorAll("details").forEach(function (item, index) {
      if (index === 0) item.setAttribute("open", "");
      else item.removeAttribute("open");
    });
    copy.querySelectorAll(".mehs-edit-side, .mehs-edit-tools, .mehs-new-trip, .mehs-hero-frames, .mehs-add-partner, .mehs-remove-partner, [data-mehs-remove]").forEach(function (node) {
      node.remove();
    });
    copy.querySelectorAll(".partner-row").forEach(function (row) {
      row.removeAttribute("data-mehs-partners");
    });
    copy.querySelectorAll(".banner-media").forEach(function (img) {
      img.removeAttribute("style");
    });
    return copy.outerHTML;
  }

  var hostTags = { H1: 1, H2: 1, H3: 1, H4: 1, H5: 1, H6: 1, P: 1, LI: 1, TD: 1, TH: 1, FIGCAPTION: 1 };
  var inlineTags = { SPAN: 1, STRONG: 1, EM: 1, B: 1, I: 1, U: 1, FONT: 1, BR: 1, WBR: 1, SUB: 1, SUP: 1, MARK: 1, SMALL: 1, A: 1 };

  function inlineOnly(node) {
    var children = node.children;
    for (var index = 0; index < children.length; index++) {
      var child = children[index];
      if (!inlineTags[child.tagName] || child.classList.contains("btn") || child.querySelector(".btn, img, svg")) {
        return false;
      }
    }
    return true;
  }

  function ownedByHost(node) {
    var parent = node.parentElement;
    while (parent) {
      if (hostTags[parent.tagName] && parent.children.length && inlineOnly(parent)) return true;
      parent = parent.parentElement;
    }
    return false;
  }

  function textBlocks(value) {
    return String(value || "").replace(/\u00a0/g, " ").replace(/\r\n/g, "\n").replace(/\r/g, "\n").split(/\n{2,}/).map(function (part) {
      return part.replace(/\s*\n\s*/g, " ").replace(/[ \t]{2,}/g, " ").trim();
    }).filter(Boolean);
  }

  function messyField(node) {
    return !!node.querySelector("[style], font, div, p, h1, h2, h3, h4, h5, h6, table, ul, ol, section, article, blockquote, pre");
  }

  function plainField(node) {
    if (!node || node.querySelector(".btn, img, svg") || !messyField(node)) return;
    var blocks = textBlocks(node.innerText);
    var multiline = node.tagName === "P" || node.tagName === "LI" || node.tagName === "TD" || node.tagName === "TH";
    if (!blocks.length) {
      node.textContent = "";
      return;
    }
    if (!multiline) {
      node.textContent = blocks.join(" ");
      return;
    }
    node.textContent = blocks[0];
    var after = node;
    blocks.slice(1).forEach(function (part) {
      var next = document.createElement(node.tagName === "LI" ? "li" : "p");
      next.textContent = part;
      after.insertAdjacentElement("afterend", next);
      after = next;
    });
  }

  function leaves(root) {
    var nodes = Array.prototype.filter.call(root.querySelectorAll("h1, h2, h3, h4, h5, h6, p, li, td, th, figcaption, span, a, button, strong, em, b, i, small, blockquote, label"), function (node) {
      if (node.closest("form, .mehs-elementor-sink, .mehs-edit-side, .mehs-edit-tools, .mehs-new-trip, .mehs-hero-frames") || node.classList.contains("day-num") || node.classList.contains("mehs-add-partner") || node.classList.contains("mehs-remove-partner") || node.hasAttribute("data-mehs-remove") || node.hasAttribute("data-mehs-remove-partner")) {
        return false;
      }
      if (node.children.length && !inlineOnly(node)) return false;
      if (ownedByHost(node)) return false;
      return tidy(node.innerText) !== "";
    });
    root.querySelectorAll("div").forEach(function (node) {
      if (node.children.length || node.closest("form, .mehs-elementor-sink, .mehs-edit-side, .mehs-hero-frames")) return;
      if (tidy(node.innerText) === "") return;
      nodes.push(node);
    });
    return nodes;
  }

  function fields(root) {
    return leaves(root);
  }

  var dirty = false;
  var edited = false;
  function persist(force) {
    var root = document.querySelector("[data-mehs-live]");
    if (!root || !window.MEHS_LIVE || !MEHS_LIVE.url) {
      return;
    }
    if (force !== true && !dirty) {
      return;
    }
    dirty = false;
    var edits = {};
    fields(root).forEach(function (node) {
      if (node.tagName === "IMG") return;
      var key = node.getAttribute("data-edit");
      if (key) edits[key] = tidy(node.innerText);
    });
    root.querySelectorAll("img[data-edit]").forEach(function (img) {
      if (img.closest("form, .mehs-elementor-sink")) return;
      var key = img.getAttribute("data-edit");
      var src = img.getAttribute("src");
      if (key && src) edits[key] = src;
    });
    var data = new FormData();
    data.append("action", "mehs_save_live_html");
    data.append("nonce", MEHS_LIVE.nonce);
    data.append("post_id", String(MEHS_LIVE.post));
    data.append("html", snapshot(root));
    data.append("edits", JSON.stringify(edits));
    data.append("saved_at", String(Date.now()));
    return fetch(MEHS_LIVE.url, { method: "POST", body: data, credentials: "same-origin" }).then(function (response) {
      if (!response.ok) {
        edited = true;
        dirty = true;
      }
      return response;
    }).catch(function () {
      edited = true;
      dirty = true;
    });
  }
  window.mehsPersist = persist;
  window.mehsNeedsSave = function () { return edited || dirty; };
  window.mehsClearEdited = function () { edited = false; dirty = false; };

  function previewWindow(parentDoc) {
    var frame = parentDoc.getElementById("elementor-preview-iframe");
    return frame && frame.contentWindow ? frame.contentWindow : window;
  }

  function watchPublish() {
    var parentDoc = window.parent && window.parent.document;
    if (!parentDoc || parentDoc === document || parentDoc.documentElement.getAttribute("data-mehs-publish") === "1") {
      return;
    }
    parentDoc.documentElement.setAttribute("data-mehs-publish", "1");
    var allowPublish = false;
    parentDoc.addEventListener("click", function (event) {
      var button = event.target.closest && event.target.closest("#elementor-panel-saver-button-publish, #elementor-panel-saver-button-save, #elementor-panel-footer-saver-publish, .elementor-panel-footer-saver-options");
      if (!button) return;
      if (allowPublish) {
        allowPublish = false;
        return;
      }
      var preview = previewWindow(parentDoc);
      if (!preview.mehsPersist) return;
      event.preventDefault();
      event.stopPropagation();
      if (event.stopImmediatePropagation) event.stopImmediatePropagation();
      Promise.resolve(preview.mehsPersist(true)).then(function (response) {
        if (response && response.ok && preview.mehsClearEdited) preview.mehsClearEdited();
        allowPublish = true;
        button.click();
      });
    }, true);
    var app = editor();
    if (app && app.saver && !app.saver.mehsWrapped) {
      app.saver.mehsWrapped = true;
      ["save", "publish", "doSave", "saveDraft", "defaultSave"].forEach(function (name) {
        if (typeof app.saver[name] !== "function") return;
        var original = app.saver[name];
        app.saver[name] = function () {
          var context = this;
          var args = arguments;
          var preview = previewWindow(parentDoc);
          var save = preview.mehsNeedsSave && preview.mehsNeedsSave() && preview.mehsPersist ? preview.mehsPersist(true) : null;
          return Promise.resolve(save).then(function (response) {
            if (response && response.ok && preview.mehsClearEdited) preview.mehsClearEdited();
            return original.apply(context, args);
          });
        };
      });
    }
  }

  var saveTimer = 0;
  function schedulePersist() {
    window.clearTimeout(saveTimer);
    saveTimer = window.setTimeout(persist, 250);
  }

  function keepTyping(event) {
    var node = event.target.closest && event.target.closest("[contenteditable='true']");
    if (!node) return;
    event.stopPropagation();
    if (event.type === "mousedown") node.focus();
  }
  document.addEventListener("mousedown", keepTyping, true);
  document.addEventListener("pointerdown", keepTyping, true);

  function replaceHeroPhoto(photo, thumb) {
    if (!photo || !window.parent.wp || !window.parent.wp.media) {
      return;
    }
    var frame = window.parent.wp.media({ title: "Choose photo", multiple: false, library: { type: "image" } });
    frame.on("select", function () {
      var file = frame.state().get("selection").first().toJSON();
      if (!file || !file.url) {
        return;
      }
      var app = editor();
      if (!photo.classList.contains("mehs-hero-slide")) {
        var items = app ? collect(app) : { images: [], boxes: [] };
        items.boxes.some(function (box) {
          var current = box.settings.get("background_image") || {};
          if (!current.url) {
            return false;
          }
          box.settings.set("background_image", { url: file.url, id: file.id || "", source: "library" });
          return true;
        });
      }
      photo.src = file.url;
      if (thumb) thumb.src = file.url;
      if (app) markChanged(app);
      dirty = true;
      edited = true;
      persist(true);
    });
    frame.open();
  }

  function paintHeroFrames() {
    var hero = document.querySelector("body.home .banner.hero");
    if (!hero) {
      return;
    }
    var photos = Array.prototype.slice.call(hero.querySelectorAll(".banner-media"));
    if (photos.length < 2) {
      return;
    }
    var bar = hero.querySelector(".mehs-hero-frames");
    if (!bar) {
      bar = document.createElement("div");
      bar.className = "mehs-hero-frames";
      var note = document.createElement("span");
      note.className = "mehs-frame-note";
      note.textContent = "Home photos. Click one to replace it.";
      bar.appendChild(note);
      hero.appendChild(bar);
    }
    var picks = bar.querySelectorAll(".mehs-frame-pick");
    if (picks.length === photos.length) {
      photos.forEach(function (photo, index) {
        var thumb = picks[index].querySelector("img");
        var src = photo.getAttribute("src") || "";
        if (thumb && thumb.getAttribute("src") !== src) thumb.src = src;
      });
      return;
    }
    bar.querySelectorAll(".mehs-frame-pick").forEach(function (node) {
      node.remove();
    });
    photos.forEach(function (photo, index) {
      var button = document.createElement("button");
      button.type = "button";
      button.className = "mehs-frame-pick";
      button.title = index === 0 ? "Opening photo" : "Photo " + (index + 1);
      var thumb = document.createElement("img");
      thumb.alt = "";
      thumb.src = photo.getAttribute("src") || "";
      button.appendChild(thumb);
      button.addEventListener("click", function (event) {
        event.preventDefault();
        event.stopPropagation();
        replaceHeroPhoto(photo, thumb);
      });
      bar.appendChild(button);
    });
  }

  function bind() {
    var root = document.querySelector("[data-mehs-live]");
    if (!root || !window.MEHS_LIVE || root.getAttribute("data-mehs-bound") === "1") {
      return;
    }
    var app = editor();
    root.setAttribute("data-mehs-bound", "1");
    root.querySelectorAll("p, h1, h2, h3, h4, li").forEach(function (node) {
      if (node.closest("form") || node.hasAttribute("data-status") || node.querySelector("img, a, button, svg")) return;
      if (tidy(node.innerText) === "") node.remove();
    });
    watchPublish();
    root.querySelectorAll("details").forEach(function (item) {
      item.open = true;
    });
    root.querySelectorAll("summary").forEach(function (summary) {
      summary.addEventListener("click", function (event) {
        event.preventDefault();
      });
    });
    function editKey() {
      return Math.floor(Math.random() * 0xffffffff).toString(16).padStart(8, "0");
    }
    function markNode(node) {
      if (!node || node.getAttribute("contenteditable") === "true" || node.classList.contains("day-num")) {
        return;
      }
      if (!node.getAttribute("data-edit")) node.setAttribute("data-edit", editKey());
      node.setAttribute("contenteditable", "true");
      node.setAttribute("spellcheck", "true");
      node.addEventListener("focus", function () {
        node.setAttribute("data-mehs-origin", tidy(node.innerText));
      });
      node.addEventListener("input", function () {
        dirty = true;
        edited = true;
        schedulePersist();
      });
      node.addEventListener("focusout", function () {
        if (app) replaceText(app, node.getAttribute("data-mehs-origin"), tidy(node.innerText));
        persist();
      });
    }
    function insertPlain(node, raw) {
      var multiline = node.tagName === "P" || node.tagName === "LI" || node.tagName === "TD" || node.tagName === "TH";
      var blocks = multiline ? textBlocks(raw) : [String(raw || "").replace(/\s+/g, " ").trim()];
      if (!blocks.length) return;
      var selection = window.getSelection();
      if (!selection || !selection.rangeCount || !node.contains(selection.anchorNode)) {
        node.textContent = blocks[0];
      } else {
        var range = selection.getRangeAt(0);
        range.deleteContents();
        var text = document.createTextNode(blocks[0]);
        range.insertNode(text);
        range.setStartAfter(text);
        range.collapse(true);
        selection.removeAllRanges();
        selection.addRange(range);
      }
      var after = node;
      blocks.slice(1).forEach(function (part) {
        var next = document.createElement("p");
        next.textContent = part;
        after.insertAdjacentElement("afterend", next);
        markNode(next);
        after = next;
      });
    }
    fields(root).forEach(plainField);
    fields(root).forEach(markNode);
    function partnerSlotSrc() {
      var known = root.querySelector('img[src*="partner.svg"]');
      if (known) return known.getAttribute("src");
      return "data:image/svg+xml," + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 168 92"><rect width="168" height="92" fill="#f7f4ef"/><path d="M84 34v24M72 46h24" fill="none" stroke="#738c4a" stroke-width="2.5" stroke-linecap="round"/></svg>');
    }
    function tagPartner(figure) {
      var img = figure.querySelector("img");
      if (img && /partner\.svg|data:image\/svg\+xml/i.test(img.getAttribute("src") || "")) img.classList.add("is-slot");
      if (figure.querySelector(".mehs-remove-partner")) return;
      var remove = document.createElement("button");
      remove.type = "button";
      remove.className = "mehs-remove-partner";
      remove.setAttribute("data-mehs-remove-partner", "");
      remove.setAttribute("aria-label", "Remove partner");
      remove.textContent = "×";
      figure.appendChild(remove);
    }
    var partnerRow = root.querySelector(".partner-row");
    if (partnerRow && partnerRow.getAttribute("data-mehs-partners") !== "1") {
      partnerRow.setAttribute("data-mehs-partners", "1");
      partnerRow.querySelectorAll(".partner").forEach(tagPartner);
      var addPartner = document.createElement("button");
      addPartner.type = "button";
      addPartner.className = "mehs-add-partner";
      addPartner.textContent = "+ Add partner";
      partnerRow.insertAdjacentElement("afterend", addPartner);
      addPartner.addEventListener("click", function (event) {
        event.preventDefault();
        event.stopPropagation();
        var figure = document.createElement("figure");
        figure.className = "partner";
        var img = document.createElement("img");
        img.className = "partner-logo is-slot";
        img.alt = "Add a partner logo";
        img.src = partnerSlotSrc();
        img.setAttribute("data-edit", editKey());
        figure.appendChild(img);
        partnerRow.appendChild(figure);
        tagPartner(figure);
        dirty = true;
        edited = true;
        persist(true);
      });
    }
    root.addEventListener("paste", function (event) {
      var node = event.target.closest && event.target.closest("[contenteditable='true']");
      if (!node || !root.contains(node)) return;
      event.preventDefault();
      event.stopPropagation();
      if (event.stopImmediatePropagation) event.stopImmediatePropagation();
      var raw = event.clipboardData ? event.clipboardData.getData("text/plain") : "";
      insertPlain(node, raw);
      dirty = true;
      edited = true;
      schedulePersist();
    }, true);
    function renumberDays() {
      root.querySelectorAll(".day-acc").forEach(function (item, index) {
        var number = index + 1;
        var badge = item.querySelector(".day-num");
        var label = item.querySelector(".day-label");
        if (badge) badge.textContent = (number < 10 ? "0" : "") + number;
        if (label) label.textContent = "Day " + number;
      });
    }
    function dayBlock(number) {
      var item = document.createElement("details");
      item.className = "day-acc";
      item.open = true;
      item.innerHTML = '<summary><span class="day-num"></span><span class="day-label"></span><span class="day-title">New day</span></summary><p>Write this day.</p>';
      item.querySelector(".day-num").textContent = (number < 10 ? "0" : "") + number;
      item.querySelector(".day-label").textContent = "Day " + number;
      markNode(item.querySelector(".day-label"));
      markNode(item.querySelector(".day-title"));
      markNode(item.querySelector("p"));
      return item;
    }
    root.querySelectorAll(".mehs-edit-side").forEach(function (node) { node.remove(); });
    var canAddDay = !!root.querySelector(".package-rest, .day-acc");
    var canAddTrip = (MEHS_LIVE.addTrip === true || MEHS_LIVE.addTrip === "1") && !canAddDay;
    if (canAddDay || canAddTrip) {
      function shell() {
        try {
          return window.parent && window.parent.document ? window.parent.document : null;
        } catch (error) {
          return null;
        }
      }
      function paintDays() {
        var doc = shell();
        var list = doc && doc.querySelector("#mehs-panel .mehs-panel-rows");
        if (!list) return;
        list.innerHTML = "";
        root.querySelectorAll(".day-acc").forEach(function (item, index) {
          var row = doc.createElement("li");
          var badge = doc.createElement("span");
          badge.className = "n";
          badge.textContent = (index < 9 ? "0" : "") + (index + 1);
          var name = doc.createElement("span");
          name.className = "name";
          var title = item.querySelector(".day-title");
          name.textContent = title && tidy(title.innerText) ? tidy(title.innerText) : "Day " + (index + 1);
          var remove = doc.createElement("button");
          remove.type = "button";
          remove.className = "x";
          remove.setAttribute("data-mehs-remove", String(index));
          remove.setAttribute("aria-label", "Remove day");
          remove.textContent = "×";
          row.appendChild(badge);
          row.appendChild(name);
          row.appendChild(remove);
          list.appendChild(row);
        });
      }
      function addDayNow() {
        var days = root.querySelectorAll(".day-acc");
        var day = dayBlock(days.length + 1);
        var last = days.length ? days[days.length - 1] : null;
        var rest = root.querySelector(".package-rest");
        var included = rest && rest.querySelector(".ie");
        if (last) last.insertAdjacentElement("afterend", day);
        else if (included) included.insertAdjacentElement("beforebegin", day);
        else if (rest) rest.appendChild(day);
        dirty = true;
        edited = true;
        persist(true);
        paintDays();
        var titleNode = day.querySelector(".day-title");
        if (titleNode) titleNode.focus();
      }
      function removeDayNow(index) {
        var target = root.querySelectorAll(".day-acc")[index];
        if (!target) return;
        target.remove();
        renumberDays();
        paintDays();
        dirty = true;
        edited = true;
        persist(true);
      }
      function buildPanel(doc) {
        var box = doc.createElement("div");
        box.id = "mehs-panel";
        var label = doc.createElement("p");
        label.className = "mehs-panel-label";
        label.textContent = canAddDay ? "Itinerary days" : "Itinerary";
        box.appendChild(label);
        if (canAddDay) {
          var addDay = doc.createElement("button");
          addDay.type = "button";
          addDay.className = "mehs-panel-add";
          addDay.textContent = "+ Add day";
          box.appendChild(addDay);
          var list = doc.createElement("ul");
          list.className = "mehs-panel-rows";
          box.appendChild(list);
          addDay.addEventListener("click", function (event) {
            event.preventDefault();
            event.stopPropagation();
            addDayNow();
          });
          box.addEventListener("click", function (event) {
            var button = event.target.closest("[data-mehs-remove]");
            if (!button || !box.contains(button)) return;
            event.preventDefault();
            event.stopPropagation();
            removeDayNow(Number(button.getAttribute("data-mehs-remove")));
          });
        }
        if (canAddTrip) {
          var addTrip = doc.createElement("button");
          addTrip.type = "button";
          addTrip.className = "mehs-panel-add";
          addTrip.textContent = "+ Add itinerary";
          box.appendChild(addTrip);
          var form = doc.createElement("div");
          form.className = "mehs-panel-form";
          form.hidden = true;
          var titleInput = doc.createElement("input");
          titleInput.type = "text";
          titleInput.placeholder = "Itinerary title";
          form.appendChild(titleInput);
          var parentSelect = null;
          if (MEHS_LIVE.askParent && Array.isArray(MEHS_LIVE.parents)) {
            parentSelect = doc.createElement("select");
            MEHS_LIVE.parents.forEach(function (parent) {
              var option = doc.createElement("option");
              option.value = String(parent.id);
              option.textContent = parent.label;
              parentSelect.appendChild(option);
            });
            form.appendChild(parentSelect);
          }
          var create = doc.createElement("button");
          create.type = "button";
          create.className = "mehs-panel-add";
          create.textContent = "Create";
          form.appendChild(create);
          var note = doc.createElement("p");
          note.className = "mehs-panel-note";
          form.appendChild(note);
          box.appendChild(form);
          addTrip.addEventListener("click", function (event) {
            event.preventDefault();
            event.stopPropagation();
            form.hidden = false;
            titleInput.focus();
          });
          create.addEventListener("click", function (event) {
            event.preventDefault();
            event.stopPropagation();
            var title = tidy(titleInput.value);
            var parent = parentSelect ? parentSelect.value : String(MEHS_LIVE.parent || "");
            if (!title) {
              note.textContent = "Write a title for the itinerary.";
              return;
            }
            create.disabled = true;
            note.textContent = "Creating the itinerary…";
            var data = new FormData();
            data.append("action", "mehs_add_itinerary");
            data.append("nonce", MEHS_LIVE.nonce);
            data.append("parent", parent);
            data.append("title", title);
            fetch(MEHS_LIVE.url, { method: "POST", body: data, credentials: "same-origin" }).then(function (response) {
              return response.json();
            }).then(function (result) {
              if (!result || !result.ok || !result.edit) {
                create.disabled = false;
                note.textContent = (result && result.error) || "The itinerary could not be created.";
                return;
              }
              Promise.resolve(persist(true)).then(function () {
                window.parent.location.href = result.edit;
              });
            }).catch(function () {
              create.disabled = false;
              note.textContent = "The itinerary could not be created.";
            });
          });
        }
        return box;
      }
      function place(box) {
        var doc = box.ownerDocument;
        var nav = doc.getElementById("elementor-panel-elements-navigation");
        var page = doc.getElementById("elementor-panel-page-elements");
        if (nav) {
          if (nav.nextElementSibling !== box) nav.insertAdjacentElement("afterend", box);
          box.hidden = false;
          return true;
        }
        if (page) {
          if (page.firstElementChild !== box) page.insertBefore(box, page.firstElementChild);
          box.hidden = false;
          return true;
        }
        return false;
      }
      var opened = Date.now();
      var box = null;
      function tick() {
        var doc = shell();
        if (!doc || !doc.getElementById("elementor-panel")) return;
        if (!doc.getElementById("mehs-panel-css")) {
          var style = doc.createElement("style");
          style.id = "mehs-panel-css";
          style.textContent = "#mehs-panel{margin:0;padding:12px 16px 8px;border-bottom:1px solid rgba(255,255,255,.08);color:#d5d8dc;font:500 13px/1.3 Roboto,sans-serif}#mehs-panel .mehs-panel-label{margin:4px 0 8px;color:#fff;font-weight:600}#mehs-panel .mehs-panel-add{display:flex;align-items:center;justify-content:center;width:100%;height:36px;margin:0 0 8px;border:1px solid #4a4e54;border-radius:3px;background:#2b2e32;color:#fff;cursor:pointer;font:600 13px/1 Roboto,sans-serif}#mehs-panel .mehs-panel-add:hover{background:#3a3e44}#mehs-panel .mehs-panel-rows{list-style:none;max-height:280px;margin:0;padding:0;overflow:auto}#mehs-panel .mehs-panel-rows li{display:grid;grid-template-columns:28px 1fr 28px;align-items:center;gap:6px;padding:8px 0;border-bottom:1px solid #34383c;color:#e8eaed}#mehs-panel .n{color:#a4afb7;font-size:12px;font-weight:700}#mehs-panel .name{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:600}#mehs-panel .x{border:0;background:transparent;color:#a4afb7;cursor:pointer;font:600 16px/1 Roboto,sans-serif}#mehs-panel .x:hover{color:#fff}#mehs-panel .mehs-panel-form{display:grid;gap:8px}#mehs-panel .mehs-panel-form[hidden]{display:none}#mehs-panel input,#mehs-panel select{width:100%;border:1px solid #4a4e54;border-radius:3px;background:#fff;color:#17352c;padding:8px;font:500 13px/1.3 Roboto,sans-serif}#mehs-panel .mehs-panel-note{margin:0;color:#d5d8dc;font-size:12px}";
          (doc.head || doc.documentElement).appendChild(style);
        }
        var fresh = !box || !box.isConnected;
        if (fresh) box = buildPanel(doc);
        var placed = place(box);
        if (!placed && Date.now() - opened > 2500) {
          var header = doc.getElementById("elementor-panel-header-wrapper");
          var panel = doc.getElementById("elementor-panel");
          if (header && header.nextElementSibling !== box) header.insertAdjacentElement("afterend", box);
          else if (panel && !panel.contains(box)) panel.insertBefore(box, panel.firstChild);
          box.hidden = false;
        }
        var list = box.querySelector(".mehs-panel-rows");
        if (canAddDay && box.isConnected && list && !list.childElementCount) paintDays();
      }
      tick();
      window.setInterval(tick, 700);
      if (canAddDay) {
        root.addEventListener("input", function (event) {
          var field = event.target.closest(".day-title, .day-label");
          if (field && root.contains(field)) paintDays();
        });
      }
    }
    root.addEventListener("click", function (event) {
      var removePartner = event.target.closest("[data-mehs-remove-partner]");
      if (removePartner && root.contains(removePartner)) {
        event.preventDefault();
        event.stopPropagation();
        var figure = removePartner.closest(".partner");
        if (figure) figure.remove();
        dirty = true;
        edited = true;
        persist(true);
        return;
      }
      var link = event.target.closest("a");
      if (link && root.contains(link)) {
        event.preventDefault();
      }
      var image = event.target.closest("img");
      if (!image || !root.contains(image) || image.closest(".mehs-hero-frames") || !window.parent.wp || !window.parent.wp.media) {
        return;
      }
      event.preventDefault();
      event.stopPropagation();
      var frame = window.parent.wp.media({ title: "Choose photo", multiple: false, library: { type: "image" } });
      frame.on("select", function () {
        var file = frame.state().get("selection").first().toJSON();
        if (!file || !file.url) {
          return;
        }
        var items = app ? collect(app) : { images: [], boxes: [] };
        if (image.classList.contains("banner-media") && !image.classList.contains("mehs-hero-slide")) {
          items.boxes.some(function (box) {
            var current = box.settings.get("background_image") || {};
            if (!current.url) {
              return false;
            }
            box.settings.set("background_image", { url: file.url, id: file.id || "", source: "library" });
            return true;
          });
        }
        var photos = Array.prototype.slice.call(root.querySelectorAll("img:not(.banner-media)"));
        var index = photos.indexOf(image);
        if (index > -1 && items.images[index]) {
          items.images[index].settings.set("image", { url: file.url, id: file.id || "", alt: image.alt || "", source: "library" });
        } else {
          items.images.forEach(function (widget) {
            var current = widget.settings.get("image") || {};
            if (fileName(current.url) && fileName(current.url) === fileName(image.src)) {
              widget.settings.set("image", { url: file.url, id: file.id || "", alt: image.alt || "", source: "library" });
            }
          });
        }
        image.src = file.url;
        image.classList.remove("is-slot");
        if (app) markChanged(app);
        dirty = true;
        edited = true;
        persist(true);
      });
      frame.open();
    });
  }

  function boot() {
    bind();
    paintHeroFrames();
  }
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
  } else {
    boot();
  }
  window.setTimeout(boot, 700);
  window.setTimeout(boot, 1800);
})();
