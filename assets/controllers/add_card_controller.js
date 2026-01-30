import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["form"];

    toggle(event) {
        const cardItem = event.currentTarget.closest("li");
        const form = cardItem.querySelector('[data-add-card-target="form"]');
        form.classList.toggle("hidden");
    }

    sendData(event) {
        event.preventDefault();

        const form = event.currentTarget;
        const formData = new FormData(form);

        fetch("/collection/add/card", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                cardId: formData.get("cardId"),
                condition: formData.get("cardCondition"),
            }),
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                form.classList.add("hidden");
                // TODO: changer icône en "✓"
            }
        })
        .catch(err => console.error(err));
    }
}
