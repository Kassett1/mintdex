import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["video", "canvas", "result"];

    connect() {
        this.startCamera();
    }

    async startCamera() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: "environment" },
            });

            this.videoTarget.srcObject = stream;
        } catch (e) {
            this.resultTarget.textContent = "Accès à la caméra refusé";
        }
    }

    capture() {
        const video = this.videoTarget;
        const canvas = this.canvasTarget;
        const ctx = canvas.getContext("2d");

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        // Convertit le canvas en image
        const imgData = canvas.toDataURL("image/png");

        // Crée un <img> et remplace la vidéo
        const img = document.createElement("img");
        img.src = imgData;
        img.className = video.className;
        video.replaceWith(img);
    }
}
