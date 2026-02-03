import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    removeCard(event) {
        const cardButton = event.currentTarget;
        const li = cardButton.closest("li")

        li.remove();

        fetch("/collection/remove/card", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                cardId: cardButton.dataset.cardId,
            }),
        })
            .then((res) => res.json())
            .catch((err) => console.error(err));
    }
}
