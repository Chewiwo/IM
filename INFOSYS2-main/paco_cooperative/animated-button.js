function applyUIEnhancements() {
  // 1. Inject CSS for Buttons, Inputs, and Nav Hover Effects
  if (!document.getElementById("custom-ui-styles")) {
    const style = document.createElement("style");
    style.id = "custom-ui-styles";
    style.textContent = `
      /* --- NAV LINKS & HOVER-ONLY SLIDING UNDERLINE --- */
      header nav,
      .site-header nav,
      .header-wrap nav,
      nav {
        position: relative !important;
      }

      /* Single sliding line controlled by CSS variables */
      header nav::after,
      .site-header nav::after,
      .header-wrap nav::after,
      nav::after {
        content: '' !important;
        position: absolute !important;
        bottom: -2px !important;
        left: var(--underline-left, 0px) !important;
        width: var(--underline-width, 0px) !important;
        height: 3px !important;
        background-color: #e7b84b !important; /* PASCCO Gold */
        border-radius: 2px !important;
        transition: left 0.35s cubic-bezier(0.25, 1, 0.5, 1), width 0.35s cubic-bezier(0.25, 1, 0.5, 1), opacity 0.25s ease !important;
        opacity: var(--underline-opacity, 0) !important; /* Hidden by default */
        pointer-events: none !important;
        box-shadow: 0 2px 8px rgba(231, 184, 75, 0.6) !important;
      }

      /* Navigation Links Zoom & Color Effect */
      header nav a,
      .site-header nav a,
      .header-wrap nav a,
      nav a {
        display: inline-block !important;
        transition: transform 0.3s cubic-bezier(0.25, 1, 0.5, 1), color 0.3s ease !important;
        will-change: transform;
        text-decoration: none !important;
        border-bottom: none !important; /* Removes any default border underlines */
      }

      header nav a:hover,
      .site-header nav a:hover,
      .header-wrap nav a:hover,
      nav a:hover {
        color: #e7b84b !important;
        transform: translateY(-3px) scale(1.12) !important;
        text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
      }

      /* Hide all original static underlines completely */
      header nav a::after,
      .site-header nav a::after,
      nav a::after,
      header nav a.active,
      .site-header nav a.active,
      nav a.active {
        border-bottom: none !important;
      }

      header nav a::after,
      .site-header nav a::after,
      nav a::after {
        display: none !important;
      }

      /* --- BUTTON ANIMATION CSS --- */
      @keyframes buttonPulse {
        0% { box-shadow: 0 0 0 0 rgba(231, 184, 75, 0.6); }
        70% { box-shadow: 0 0 0 10px rgba(231, 184, 75, 0); }
        100% { box-shadow: 0 0 0 0 rgba(231, 184, 75, 0); }
      }

      .animated-button {
        position: relative;
        display: inline-flex !important;
        align-items: center;
        gap: 4px;
        padding: 8px 22px !important;
        border: 2px solid transparent !important;
        font-size: 14px !important;
        background-color: transparent !important;
        border-radius: 100px !important;
        font-weight: 600 !important;
        color: #e7b84b !important;
        box-shadow: 0 0 0 2px #e7b84b !important;
        cursor: pointer;
        overflow: hidden;
        transition: all 0.6s cubic-bezier(0.23, 1, 0.32, 1) !important;
        text-decoration: none !important;
        box-sizing: border-box;
        animation: buttonPulse 2.5s infinite;
      }

      .animated-button::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -60%;
        width: 25%;
        height: 200%;
        background: rgba(255, 255, 255, 0.4);
        transform: rotate(30deg);
        transition: all 0.6s ease;
        z-index: 2;
        pointer-events: none;
      }

      .animated-button:hover::after {
        left: 130%;
      }

      .animated-button svg {
        position: absolute;
        width: 16px;
        height: 16px;
        fill: #e7b84b;
        z-index: 9;
        transition: all 0.8s cubic-bezier(0.23, 1, 0.32, 1);
      }

      .animated-button .arr-1 { right: 12px; }
      .animated-button .arr-2 { left: -25%; }

      .animated-button .circle {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 20px;
        height: 20px;
        background-color: #e7b84b;
        border-radius: 50%;
        opacity: 0;
        transition: all 0.8s cubic-bezier(0.23, 1, 0.32, 1);
      }

      .animated-button .text {
        position: relative;
        z-index: 1;
        transform: translateX(-8px);
        transition: all 0.8s cubic-bezier(0.23, 1, 0.32, 1);
        color: #e7b84b !important;
      }

      .animated-button:hover {
        animation: none;
        box-shadow: 0 0 0 12px transparent !important;
        color: #212121 !important;
        border-radius: 12px !important;
      }

      .animated-button:hover .arr-1 { right: -25%; }
      .animated-button:hover .arr-2 { left: 12px; }
      .animated-button:hover .text { 
        transform: translateX(8px);
        color: #212121 !important;
      }
      .animated-button:hover svg { fill: #212121; }

      .animated-button:active {
        transform: scale(0.95);
        box-shadow: 0 0 0 4px #e7b84b !important;
      }

      .animated-button:hover .circle {
        width: 220px;
        height: 220px;
        opacity: 1;
      }

      /* --- INPUT FLOATING LABEL CSS --- */
      .ui-input-group {
        position: relative;
        margin-bottom: 22px;
        width: 100%;
      }

      .ui-input {
        border: solid 1.5px #9e9e9e !important;
        border-radius: 1rem !important;
        background: transparent !important;
        padding: 1rem !important;
        font-size: 1rem !important;
        color: #ffffff !important;
        width: 100% !important;
        box-sizing: border-box !important;
        transition: border 150ms cubic-bezier(0.4,0,0.2,1);
      }

      .ui-user-label {
        position: absolute;
        left: 15px;
        top: 0;
        color: #ffffff !important;
        pointer-events: none;
        transform: translateY(1rem);
        transition: 150ms cubic-bezier(0.4,0,0.2,1);
      }

      .ui-input:focus, 
      .ui-input:not(:placeholder-shown),
      .ui-input:-webkit-autofill,
      .ui-input[data-has-value="true"] {
        outline: none;
        border: 1.5px solid #1456a0 !important;
      }

      .ui-input:focus ~ .ui-user-label, 
      .ui-input:not(:placeholder-shown) ~ .ui-user-label,
      .ui-input:-webkit-autofill ~ .ui-user-label,
      .ui-input[data-has-value="true"] ~ .ui-user-label {
        transform: translateY(-50%) scale(0.85);
        background-color: #0d1b3a !important;
        padding: 0 .4em;
        color: #ffffff !important;
        border-radius: 4px;
      }
    `;
    document.head.appendChild(style);
  }

  // 2. Hover-Only Sliding Navigation Line Logic
  const navContainer = document.querySelector("header nav, .site-header nav, .header-wrap nav, nav");
  if (navContainer) {
    const navLinks = Array.from(navContainer.querySelectorAll("a"));

    const updateLinePosition = (targetEl) => {
      if (!targetEl) return;
      const navRect = navContainer.getBoundingClientRect();
      const targetRect = targetEl.getBoundingClientRect();

      const leftOffset = targetRect.left - navRect.left;
      const width = targetRect.width;

      navContainer.style.setProperty("--underline-left", `${leftOffset}px`);
      navContainer.style.setProperty("--underline-width", `${width}px`);
      navContainer.style.setProperty("--underline-opacity", "1"); // Show line on hover
    };

    // Bind hover movement
    navLinks.forEach((link) => {
      link.addEventListener("mouseenter", () => updateLinePosition(link));
    });

    // Hide underline completely when mouse leaves nav area
    navContainer.addEventListener("mouseleave", () => {
      navContainer.style.setProperty("--underline-opacity", "0");
    });
  }

  // 3. Transform Top Header Action Button (Sign Up or Login)
  const headerCandidates = Array.from(document.querySelectorAll("a, button, .header-action"));
  const targetBtn = headerCandidates.find((el) => {
    const text = el.textContent.trim().toLowerCase();
    return el.classList.contains("header-action") || text === "sign up" || text === "login" || text === "log in";
  });

  if (targetBtn && !targetBtn.classList.contains("animated-button")) {
    const textContent = targetBtn.textContent.trim();
    targetBtn.classList.add("animated-button");
    targetBtn.innerHTML = `
      <div class="circle"></div>
      <span class="text">${textContent}</span>
      <svg class="arr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path d="M16.172 11l-5.364-5.364 1.414-1.414L20 12l-7.778 7.778-1.414-1.414L16.172 13H4v-2z"></path>
      </svg>
      <svg class="arr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path d="M16.172 11l-5.364-5.364 1.414-1.414L20 12l-7.778 7.778-1.414-1.414L16.172 13H4v-2z"></path>
      </svg>
    `;
  }

  // 4. Transform Form Input Fields
  const inputs = document.querySelectorAll(
    "input[type='text'], input[type='password'], input[type='email'], input[type='tel']"
  );

  inputs.forEach((input) => {
    if (input.classList.contains("ui-input")) return;

    input.setAttribute("placeholder", " ");
    input.classList.add("ui-input");

    let label = document.querySelector(`label[for='${input.id}']`) || input.previousElementSibling;
    
    const wrapper = document.createElement("div");
    wrapper.classList.add("ui-input-group");

    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);

    if (label && label.tagName.toLowerCase() === "label") {
      label.classList.add("ui-user-label");
      wrapper.appendChild(label);
    } else {
      const newLabel = document.createElement("label");
      newLabel.classList.add("ui-user-label");
      newLabel.textContent = input.name || input.id || "Input";
      wrapper.appendChild(newLabel);
    }

    input.addEventListener("input", () => {
      if (input.value !== "") {
        input.setAttribute("data-has-value", "true");
      } else {
        input.removeAttribute("data-has-value");
      }
    });
  });

  // Auto-fill check
  function checkFilledInputs() {
    document.querySelectorAll(".ui-input").forEach((input) => {
      if (input.value !== "" || input.matches(":-webkit-autofill")) {
        input.setAttribute("data-has-value", "true");
      }
    });
  }

  checkFilledInputs();
  setTimeout(checkFilledInputs, 200);
  setTimeout(checkFilledInputs, 500);
}

// Run script
applyUIEnhancements();

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", applyUIEnhancements);
}