class Focusable {
  constructor() {
    this.focusSource = ally.style.focusSource();
    this.focusableElements = ally.query.focusable();
    this.activeElement = ally.event.activeElement();
    this.activeElementBoundingRect = null;
    this.focusRingElement = document.createElement("span");
    this.focusRingElement.classList.add("focusable__ring");
    this.outlineColor = this.invertColor(
      window
        .getComputedStyle(document.body, null)
        .getPropertyValue("background-color"),
      true
    );

    document.body.insertAdjacentElement("beforeend", this.focusRingElement);

    this.addClass();
    this.addEvents();
  }

  addClass() {
    for (const element of this.focusableElements) {
      element.classList.add("focusable");
    }
  }

  addEvents() {
    document.addEventListener(
      "active-element",
      (event) => {
        if (this.focusSource.used("key") === false) {
          return;
        }
        // event.detail.focus: element that received focus
        // event.detail.blur: element that lost focus

        this.activeElement = event.detail.focus;

        this.updateRingStyle();
      },
      false
    );

    document.addEventListener("scroll", () => {
      if (this.focusSource.used("key") === false) {
        return;
      }
      this.updateRingStyle();
    });
  }

  updateRingStyle() {
    if (!this.isElement(this.activeElement)) {
      return;
    }

    this.activeElementBoundingRect = this.activeElement.getBoundingClientRect();

    let outlineColor = this.outlineColor;
    let activeOutlineColor = window
      .getComputedStyle(this.activeElement, null)
      .getPropertyValue("outline-color");

    if (activeOutlineColor === "rgb(0, 0, 0)") {
      outlineColor = this.outlineColor;
    } else {
      outlineColor = activeOutlineColor;
    }

    const values = {
      transform: `translate(${this.activeElementBoundingRect.left}px, ${this.activeElementBoundingRect.top}px)`,
      width: this.activeElementBoundingRect.width + "px",
      height: this.activeElementBoundingRect.height + "px",
      outlineColor: outlineColor,
    };

    for (const property in values) {
      if (Object.hasOwnProperty.call(values, property)) {
        const value = values[property];
        this.focusRingElement.style[property] = value;
      }
    }
  }

  invertColor(rgb) {
    let RGB = rgb
      .substring(5, rgb.length - 1)
      .replace(/ /g, "")
      .split(",");

    // invert color components
    let r = RGB[0],
      g = RGB[1],
      b = RGB[2];

    return r * 0.299 + g * 0.587 + b * 0.114 > 186 ? "#000000" : "#FFFFFF";
  }

  isElement(element) {
    return element instanceof Element || element instanceof HTMLDocument;
  }
}

jQuery(document).ready(function () {
  new Focusable();
});
