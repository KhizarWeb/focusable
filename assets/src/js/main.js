class Focusable {
  constructor() {
    if (!ally.hasOwnProperty("style") || !ally.hasOwnProperty("query")) {
      return;
    }

    this.focusSource = ally.style.focusSource();
    this.focusableElements = ally.query.focusable();
    this.activeElement = ally.event.activeElement();

    this.activeElementBoundingRect = {
      left: 0,
      top: 0,
      width: 0,
      height: 0,
    };

    this.focusRingElement = document.createElement("span");
    this.settings = focusableData.settings;
    this.outlineColor = window
      .getComputedStyle(document.body, null)
      .getPropertyValue("color");

    document.body.insertAdjacentElement("beforeend", this.focusRingElement);

    this.addClasses();
    this.addAttributes();
    this.addEvents();
  }

  addClasses() {
    this.focusRingElement.classList.add("focusable__ring");

    for (const element of this.focusableElements) {
      element.classList.add("focusable");
    }
  }

  addAttributes() {
    this.focusRingElement.setAttribute(
      "data-focusable-transition",
      this.settings.transition
    );
  }

  addEvents() {
    document.addEventListener(
      "active-element",
      (event) => {
        if (this.focusSource.used("key") === false) {
          return;
        }

        this.activeElement = event.detail.focus;

        this.updateRingStyle();
      },
      false
    );

    document.addEventListener("scroll", () => {
      if (this.focusSource.used("key") === false) {
        return;
      }

      this.updateRingPosition();
    });
  }

  updateRingStyle() {
    if (!this.isElement(this.activeElement)) {
      return;
    }

    this.activeElementBoundingRect = this.activeElement.getBoundingClientRect();

    this.updateRingColor(this.activeElement, "color");

    const style = {
      transform: `translate(${this.activeElementBoundingRect.left}px, ${this.activeElementBoundingRect.top}px)`,
      width: this.activeElementBoundingRect.width + "px",
      height: this.activeElementBoundingRect.height + "px",
      outlineColor: this.outlineColor,
    };

    this.applyStyle(style);
  }

  updateRingPosition() {
    if (!this.isElement(this.activeElement)) {
      return;
    }

    this.activeElementBoundingRect = this.activeElement.getBoundingClientRect();

    const style = {
      transform: `translate(${this.activeElementBoundingRect.left}px, ${this.activeElementBoundingRect.top}px)`,
    };

    this.applyStyle(style);
  }

  updateRingColor(element, property) {
    if (!this.isElement(element)) {
      return;
    }

    if (!this.isElement(element.parentElement)) {
      return;
    }

    let color = window
      .getComputedStyle(element.parentElement, null)
      .getPropertyValue(property);

    if (color === "rgba(0, 0, 0, 0)" && element !== document.body) {
      this.updateRingColor(element.parentElement, "color");
    } else {
      this.outlineColor = color;
    }

    return;
  }

  applyStyle(style) {
    for (const property in style) {
      if (Object.hasOwnProperty.call(style, property)) {
        const value = style[property];
        this.focusRingElement.style[property] = value;
      }
    }
  }

  invertColor(rgb) {
    let RGB = rgb
      .substring(5, rgb.length - 1)
      .replace(/ /g, "")
      .split(",");

    return RGB[0] * 0.299 + RGB[1] * 0.587 + RGB[2] * 0.114 > 125
      ? "#000000"
      : "#FFFFFF";
  }

  isElement(element) {
    return element instanceof Element || element instanceof HTMLDocument;
  }
}

if (typeof jQuery === "function") {
  jQuery(document).ready(function () {
    new Focusable();
  });

  console.log(typeof Focusable);
} else {
  document.addEventListener("DOMContentLoaded", function () {
    new Focusable();
  });
}
