import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["form", "button", "cardList"];
    static values = {
        updateButton: { type: Boolean, default: true },
    };

    connect() {
        this.handleClickOutside = this.handleClickOutside.bind(this);
        document.addEventListener("click", this.handleClickOutside);
    }

    disconnect() {
        document.removeEventListener("click", this.handleClickOutside);
    }

    toggle() {
        const form = this.formTarget;

        if (form.classList.contains("hidden")) {
            form.classList.remove("hidden");
            form.classList.remove("translate-y-full");
            form.classList.add("flex");
        } else {
            form.classList.remove("flex");
            form.classList.add("hidden");
            form.classList.add("translate-y-full");
        }
    }

    sendData(event) {
        event.preventDefault();

        const form = event.currentTarget;
        const formData = new FormData(form);

        let spinner = null;

        if (this.hasCardListTarget) {
            spinner = this.addSpinner();
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
                if (spinner) spinner.remove();

                if (!data.success) return;

                if (this.updateButtonValue) {
                    this.buttonTarget.classList.remove(
                        "bg-orange-500/30",
                        "bg-orange-500/50",
                    );
                    this.buttonTarget.classList.add("bg-green-500/30");

                    this.buttonTarget.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             stroke-width="1.5" stroke="currentColor" class="w-full h-full">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                    `;
                }

                if (this.hasCardListTarget && data.card) {
                    this.addCard(data.card);
                }

                form.classList.remove("flex");
                form.classList.add("hidden");
                form.classList.add("translate-y-full");
            })
            .catch((err) => {
                if (spinner) spinner.remove();
                console.error(err);
            });
    }

    addSpinner() {
        const li = document.createElement("li");
        li.className = "flex items-center justify-center p-2 bg-gray-100";

        li.innerHTML = `
            <svg class="h-5 w-5 animate-spin text-gray-500"
                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10"
                        stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
        `;

        this.cardListTarget.appendChild(li);
        return li;
    }

    addCard(card) {
        const li = document.createElement("li");
        li.className =
            "flex items-center justify-between bg-violet-100 w-full rounded-xl px-3 py-1 shadow-sm";

        li.innerHTML = `
            <div>
                <p class="font-medium">${card.name}</p>
                <p class="text-sm text-gray-600">${card.condition}</p>
            </div>

            <button 
                class="text-red-500 hover:text-red-700"
                data-action="click->remove-card#removeCard"
                data-card-id="${card.id}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                </svg>
            </button>
        `;

        this.cardListTarget.appendChild(li);
    }

    handleClickOutside(event) {
        const form = this.formTarget;

        // Si le form est caché → on fait rien
        if (form.classList.contains("hidden")) return;

        // Si le click est DANS le form → on ignore
        if (form.contains(event.target)) return;

        // Si le click est sur le bouton → on ignore
        if (this.buttonTarget.contains(event.target)) return;

        // Sinon → on ferme
        form.classList.remove("flex");
        form.classList.add("hidden");
        form.classList.add("translate-y-full");
    }
}
