import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    toggle(event) {
        event.preventDefault();

        const button = event.currentTarget.closest("button");

        const cardId = button.dataset.wishlistCardId;

        let inWishlist = button.dataset.wishlistInitialValue === "true";

        const url = inWishlist ? "/wishlist/remove/card" : "/wishlist/add/card";
        console.log(url);

        fetch(url, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ cardId: cardId }),
        }).then((response) => {
            if (!response.ok) throw new Error("Erreur");
            return response.json();
        });

        inWishlist = !inWishlist;
        button.dataset.wishlistInitialValue = inWishlist.toString();

        this.updateUI(button, inWishlist);
    }

    updateUI(button, inWishlist) {
        button.classList.toggle("bg-pink-500/30");
        button.classList.toggle("bg-indigo-900/30");

        const svg = button.querySelector("svg");

        svg.setAttribute("fill", inWishlist ? "currentColor" : "none");
    }
}
