(function () {
  const scriptEl = document.currentScript;
  if (!scriptEl) {
    console.error("contact-popup.js: Script element not found.");
    return;
  }

  const dataset = scriptEl.dataset || {};
  const host = (dataset.ntfyHost || "https://ntfy.sh").replace(/\/$/, "");
  const topic = dataset.ntfyTopic || "easy-stack-web-contact";
  const token = dataset.ntfyToken ? dataset.ntfyToken.trim() : "";
  const title = dataset.ntfyTitle || "New contact request";
  const tags = dataset.ntfyTags || "contact,website";
  const debug = dataset.ntfyDebug === "true";

  const dragEnabledAttr = "data-draggable-enabled";
  const dragHandleSelector = ".modal-header";

  const modalMarkup = `
<div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered contact-modal">
    <div class="modal-content contact-modal__content">
      <div class="modal-header contact-modal__header">
        <h2 class="modal-title fs-5" id="contactModalLabel">Get in touch</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body contact-modal__body">
        <form id="contactForm" novalidate>
          <div class="mb-3">
            <label class="form-label" for="contact-name">Name</label>
            <input class="form-control" id="contact-name" name="name" type="text" autocomplete="name" placeholder="John Doe">
          </div>
          <div class="mb-3">
            <label class="form-label" for="contact-email">Email<span class="text-danger">*</span></label>
            <input class="form-control" id="contact-email" name="email" type="email" autocomplete="email" required placeholder="name@example.com">
            <div class="invalid-feedback">Please enter a valid email address.</div>
          </div>
          <div class="mb-3">
            <label class="form-label" for="contact-phone">Phone</label>
            <input class="form-control" id="contact-phone" name="phone" type="tel" autocomplete="tel" placeholder="Optional">
          </div>
          <div class="mb-3">
            <label class="form-label" for="contact-message">Message<span class="text-danger">*</span></label>
            <textarea class="form-control" id="contact-message" name="message" rows="4" required placeholder="What is your project about?"></textarea>
            <div class="invalid-feedback">Please enter a message.</div>
          </div>
          <div class="alert alert-success d-none" role="status" data-contact-success>Thank you! We will get back to you shortly.</div>
          <div class="alert alert-danger d-none" role="status" data-contact-error>Sending failed. Please try again later.</div>
          <div class="d-flex justify-content-end gap-2 contact-modal__actions">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" data-contact-submit>Send message</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>`;

  function clamp(value, min, max) {
    if (!Number.isFinite(value)) {
      return min;
    }
    if (max < min) {
      return min;
    }
    return Math.min(Math.max(value, min), max);
  }

  function applyStoredPosition(dialog) {
    const rect = dialog.getBoundingClientRect();
    const storedLeft = Number.parseFloat(dialog.dataset.dragLeft);
    const storedTop = Number.parseFloat(dialog.dataset.dragTop);
    const left = Number.isFinite(storedLeft) ? storedLeft : rect.left;
    const top = Number.isFinite(storedTop) ? storedTop : rect.top;

    dialog.style.position = "absolute";
    dialog.style.margin = "0";
    dialog.style.transform = "none";
    dialog.style.width = `${rect.width}px`;
    dialog.style.left = `${left}px`;
    dialog.style.top = `${top}px`;
  }

  function enableDraggableModal(modal) {
    if (!modal || modal.getAttribute(dragEnabledAttr) === "true") {
      return;
    }

    const dialog = modal.querySelector(".modal-dialog");
    const header = modal.querySelector(dragHandleSelector);

    if (!dialog || !header) {
      return;
    }

    const startDrag = (event) => {
      if (event.button && event.button !== 0) {
        return;
      }
      if (!event.currentTarget.setPointerCapture) {
        return;
      }
      event.preventDefault();
      const pointerId = event.pointerId;
      event.currentTarget.setPointerCapture(pointerId);

      const rect = dialog.getBoundingClientRect();
      const startLeft = rect.left;
      const startTop = rect.top;
      const startX = event.clientX;
      const startY = event.clientY;

      const handleMove = (moveEvent) => {
        if (moveEvent.pointerId !== pointerId) {
          return;
        }
        const deltaX = moveEvent.clientX - startX;
        const deltaY = moveEvent.clientY - startY;
        const maxLeft = Math.max(0, window.innerWidth - dialog.offsetWidth);
        const maxTop = Math.max(0, window.innerHeight - dialog.offsetHeight);
        const nextLeft = clamp(startLeft + deltaX, 0, maxLeft || window.innerWidth);
        const nextTop = clamp(startTop + deltaY, 0, maxTop || window.innerHeight);
        dialog.style.left = `${nextLeft}px`;
        dialog.style.top = `${nextTop}px`;
        dialog.dataset.dragLeft = `${nextLeft}`;
        dialog.dataset.dragTop = `${nextTop}`;
      };

      const stopDrag = (endEvent) => {
        if (endEvent.pointerId !== pointerId) {
          return;
        }
        header.removeEventListener("pointermove", handleMove);
        header.removeEventListener("pointerup", stopDrag);
        header.removeEventListener("pointercancel", stopDrag);
        if (header.releasePointerCapture) {
          header.releasePointerCapture(pointerId);
        }
      };

      header.addEventListener("pointermove", handleMove);
      header.addEventListener("pointerup", stopDrag);
      header.addEventListener("pointercancel", stopDrag);
    };

    modal.addEventListener("shown.bs.modal", () => applyStoredPosition(dialog));
    header.addEventListener("pointerdown", startDrag);
    applyStoredPosition(dialog);
    modal.setAttribute(dragEnabledAttr, "true");
  }

  function initialiseDraggableModals() {
    document.querySelectorAll(".modal").forEach(enableDraggableModal);
  }

  document.addEventListener("shown.bs.modal", (event) => {
    const modal = event.target && event.target.classList && event.target.classList.contains("modal")
      ? event.target
      : event.target.closest(".modal");
    enableDraggableModal(modal);
  });

  document.addEventListener("DOMContentLoaded", initialiseDraggableModals);

  function normaliseHost(value) {
    if (!value) {
      return "https://ntfy.sh";
    }
    const trimmed = value.trim();
    if (!trimmed) {
      return "https://ntfy.sh";
    }
    return trimmed.replace(/\/$/, "");
  }

  function buildMessage(formData) {
    const lines = [
      `Page: ${window.location.href}`,
      `Timestamp: ${formData.submittedAt || new Date().toISOString()}`,
      `Name: ${formData.name || "-"}`,
      `Email: ${formData.email || "-"}`,
      `Phone: ${formData.phone || "-"}`,
      "",
      "Message:",
      formData.message || "-",
    ];
    return lines.join("\n");
  }

  function toggleFeedback(successEl, errorEl, type) {
    successEl.classList.toggle("d-none", type !== "success");
    errorEl.classList.toggle("d-none", type !== "error");
  }

  function attachListeners() {
    const modalElement = document.getElementById("contactModal");
    if (!modalElement) {
      console.error("contact-popup.js: Modal element not found.");
      return;
    }

    const bootstrapModal = window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(modalElement) : null;
    if (!bootstrapModal) {
      console.error("contact-popup.js: Bootstrap Modal unavailable.");
      return;
    }

    enableDraggableModal(modalElement);

    const form = modalElement.querySelector("#contactForm");
    const successEl = modalElement.querySelector("[data-contact-success]");
    const errorEl = modalElement.querySelector("[data-contact-error]");
    const submitBtn = modalElement.querySelector("[data-contact-submit]");

    const triggerElements = document.querySelectorAll("[data-contact-trigger]");
    triggerElements.forEach((trigger) => {
      trigger.addEventListener("click", (event) => {
        event.preventDefault();
        toggleFeedback(successEl, errorEl, null);
        form.reset();
        Array.from(form.elements).forEach((el) => {
          if (el.classList) {
            el.classList.remove("is-invalid");
          }
        });
        bootstrapModal.show();
      });
    });

    form.addEventListener("submit", async (event) => {
      event.preventDefault();
      toggleFeedback(successEl, errorEl, null);

      if (!form.checkValidity()) {
        event.stopPropagation();
        form.classList.add("was-validated");
        Array.from(form.elements).forEach((el) => {
          if (el.checkValidity && !el.checkValidity()) {
            el.classList.add("is-invalid");
          } else if (el.classList) {
            el.classList.remove("is-invalid");
          }
        });
        return;
      }

      form.classList.remove("was-validated");

      const formData = {
        name: form.elements.namedItem("name").value.trim(),
        email: form.elements.namedItem("email").value.trim(),
        phone: form.elements.namedItem("phone").value.trim(),
        message: form.elements.namedItem("message").value.trim(),
        submittedAt: new Date().toISOString(),
      };

      const body = buildMessage(formData);
      const headers = {
        "Content-Type": "text/plain; charset=utf-8",
        Title: title,
        Tags: tags,
      };

      if (token) {
        headers.Authorization = `Bearer ${token}`;
      }

      const endpoint = `${normaliseHost(host)}/${topic}`;

      submitBtn.disabled = true;
      submitBtn.setAttribute("data-sending", "true");

      try {
        const response = await fetch(endpoint, {
          method: "POST",
          body,
          headers,
        });

        if (!response.ok) {
          throw new Error(`ntfy request failed with status ${response.status}`);
        }

        toggleFeedback(successEl, errorEl, "success");
        form.reset();
        setTimeout(() => {
          bootstrapModal.hide();
        }, 2000);
      } catch (error) {
        if (debug) {
          console.error("contact-popup.js:", error);
        }
        toggleFeedback(successEl, errorEl, "error");
      } finally {
        submitBtn.disabled = false;
        submitBtn.removeAttribute("data-sending");
      }
    });
  }

  document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById("contactModal")) {
      attachListeners();
      return;
    }

    document.body.insertAdjacentHTML("beforeend", modalMarkup);
    attachListeners();
  });
})();
