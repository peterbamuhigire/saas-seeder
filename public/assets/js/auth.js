"use strict";

document.querySelectorAll("[data-password-toggle]").forEach((button) => {
  button.addEventListener("click", () => {
    const inputId = button.getAttribute("data-password-toggle");
    const input = inputId ? document.getElementById(inputId) : null;
    if (!(input instanceof HTMLInputElement)) {
      return;
    }

    const showing = input.type === "text";
    input.type = showing ? "password" : "text";
    button.textContent = showing ? "Show" : "Hide";
    button.setAttribute("aria-pressed", showing ? "false" : "true");
  });
});

const strengthInput = document.querySelector("[data-password-strength-input]");
const strengthOutput = document.querySelector("[data-password-strength]");
const strengthText = document.querySelector("[data-password-strength-text]");

if (strengthInput instanceof HTMLInputElement && strengthOutput instanceof HTMLElement && strengthText instanceof HTMLElement) {
  const labels = ["Very weak", "Weak", "Fair", "Strong", "Very strong"];

  strengthInput.addEventListener("input", () => {
    const value = strengthInput.value;
    if (value === "") {
      strengthOutput.hidden = true;
      strengthOutput.dataset.score = "0";
      strengthText.textContent = "";
      return;
    }

    let score = 0;
    if (value.length >= 12) score += 1;
    if (/[A-Z]/.test(value)) score += 1;
    if (/[a-z]/.test(value)) score += 1;
    if (/[0-9]/.test(value)) score += 1;
    if (/[^A-Za-z0-9]/.test(value)) score += 1;

    strengthOutput.hidden = false;
    strengthOutput.dataset.score = String(Math.max(score, 1));
    strengthText.textContent = labels[Math.max(score - 1, 0)];
  });
}
