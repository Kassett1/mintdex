import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["cardList"];

    toggle(event) {
        const cardItem = event.currentTarget.closest("li");
        const form = cardItem.querySelector('[data-add-card-target="form"]');
        form.classList.toggle("hidden");
    }

    sendData(event) {
        event.preventDefault();

        const form = event.currentTarget.closest("form");
        const formData = new FormData(form);

        // 1. Logique bouton (+ → ✓)
        if (form.dataset.hidden === "true") {
            this.handleToggleButton(form);
        }

        // 2. Logique liste + spinner
        let liSpinner = null;
        if (form.dataset.addToList === "true") {
            liSpinner = this.handleListAdd();
        }

        fetch("/collection/add/card", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                cardId: formData.get("cardId"),
                condition: formData.get("cardCondition"),
            }),
        })
            .then((res) => res.json())
            .then((data) => {
                if (liSpinner) liSpinner.remove();
                if (data.success && form.dataset.addToList === "true") {
                    this.addCard(data.card);
                }
            })
            .catch((err) => {
                console.error(err);
                if (liSpinner) liSpinner.remove();
            });
    }

    addCard(card) {
        const li = document.createElement("li");
        li.className =
            "flex items-center justify-between p-2 bg-gray-100 rounded border";

        li.innerHTML = `
            <div>
                <p class="font-medium">${card.name}</p>
                <p class="text-sm text-gray-600">${card.condition}</p>
            </div>
            <button class="w-8 h-8 text-red-500 hover:text-red-700" data-action="click->remove-card#removeCard" data-card-id="${card.id}">
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewbox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
					<path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
				</svg>
			</button>
        `;

        this.cardListTarget.appendChild(li);
    }

    handleToggleButton(form) {
        form.classList.add("hidden");

        const button = form.parentElement.querySelector(
            'button[data-action="click->add-card#toggle"]',
        );

        if (!button) return;

        button.classList.remove("bg-orange-500/30");
        button.classList.add("bg-green-500/30");

        button.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
             stroke-width="1.5" stroke="currentColor" class="w-full h-full">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M4.5 12.75l6 6 9-13.5"/>
        </svg>
    `;
    }

    handleListAdd() {
        const liSpinner = document.createElement("li");
        liSpinner.className =
            "flex items-center justify-center p-2 bg-gray-100 rounded border";

        liSpinner.innerHTML = `
        <svg class="h-5 w-5 animate-spin text-gray-500"
             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10"
                    stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
    `;

        this.cardListTarget.appendChild(liSpinner);
        return liSpinner;
    }
}
