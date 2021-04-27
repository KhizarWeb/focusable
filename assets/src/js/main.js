class Focusable {
  constructor() {
    this.focusSource = ally.style.focusSource();
    this.focusableElements = ally.query.focusable();
    this.activeElement = ally.event.activeElement();
    this.activeElementBoundingRect = null;
    this.activeElementBackgroundColor = "#000000";

    this.createRing();
  }

  createRing() {
    this.focusRingElement = document.createElement("span");
    this.focusRingElement.classList.add("focusable__ring");

    document.body.insertAdjacentElement("beforeend", this.focusRingElement);

    document.addEventListener(
      "active-element",
      (event) => {
        // event.detail.focus: element that received focus
        // event.detail.blur: element that lost focus

        this.activeElementBoundingRect = event.detail.focus.getBoundingClientRect();

        this.activeElementBackgroundColor = window
          .getComputedStyle(event.detail.focus, null)
          .getPropertyValue("color");

        this.updateRingStyle();
      },
      false
    );

    // document.addEventListener("scroll", () => {
    //   this.updateRingStyle();
    // });
  }

  updateRingStyle() {
    console.log(this.activeElementBackgroundColor);
    this.focusRingElement.style.transform = `translate(${this.activeElementBoundingRect.left}px, ${this.activeElementBoundingRect.top}px)`;
    this.focusRingElement.style.width =
      this.activeElementBoundingRect.width + "px";
    this.focusRingElement.style.height =
      this.activeElementBoundingRect.height + "px";
  }
}

jQuery(document).ready(function () {
  new Focusable();
});
