import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["menu"];

    toggle(event) {
        // this.menuTarget.classList.toggle("hidden");

        const isOpen = this.menuTarget.classList.contains("opacity-100");

        this.menuTarget.classList.toggle("opacity-100");
        this.menuTarget.classList.toggle("scale-100");
        this.menuTarget.classList.toggle("translate-y-0");
        this.menuTarget.classList.toggle("pointer-events-auto");

        this.menuTarget.classList.toggle("opacity-0");
        this.menuTarget.classList.toggle("scale-95");
        this.menuTarget.classList.toggle("-translate-y-2");
        this.menuTarget.classList.toggle("pointer-events-none");

        event.currentTarget.classList.toggle("open");
        event.currentTarget.setAttribute("aria-expanded", (!isOpen).toString());
    }
}
