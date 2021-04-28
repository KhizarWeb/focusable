class Focusable {
  constructor() {
    this.focusSource = ally.style.focusSource();
    this.focusableElements = ally.query.focusable();
    this.activeElement = ally.event.activeElement();
    this.activeElementBoundingRect = {};
    this.focusRingElement = document.createElement("span");
    this.focusRingElement.classList.add("focusable__ring");
    this.outlineColor = window
      .getComputedStyle(document.body, null)
      .getPropertyValue("background-color");

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

    this.getBackgroundColor(this.activeElement);

    // let outlineColor = this.outlineColor;
    // let activeOutlineColor = window
    //   .getComputedStyle(this.activeElement, null)
    //   .getPropertyValue("outline-color");

    // if (activeOutlineColor === "rgb(0, 0, 0)") {
    //   outlineColor = this.outlineColor;
    // } else {
    //   outlineColor = activeOutlineColor;
    // }

    const values = {
      transform: `translate(${this.activeElementBoundingRect.left}px, ${this.activeElementBoundingRect.top}px)`,
      width: this.activeElementBoundingRect.width + "px",
      height: this.activeElementBoundingRect.height + "px",
      outlineColor: this.outlineColor,
    };

    for (const property in values) {
      if (Object.hasOwnProperty.call(values, property)) {
        const value = values[property];
        this.focusRingElement.style[property] = value;
      }
    }
  }

  getBackgroundColor(element) {
    let backgroundColor = window
      .getComputedStyle(element, null)
      .getPropertyValue("background-color");

    if (backgroundColor === "rgba(0, 0, 0, 0)" && element !== document.body) {
      this.getBackgroundColor(element.parentElement);
    } else {
      this.outlineColor = backgroundColor;
      return;
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

jQuery(document).ready(function () {
  new Focusable();
});
