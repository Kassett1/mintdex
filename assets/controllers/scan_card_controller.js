import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = [
        "video",
        "canvas",
        "frame",
        "rawPreview",
        "cropPreview",
        "result",
    ];

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
        const frame = this.frameTarget;
        const canvas = this.canvasTarget;
        const ctx = canvas.getContext("2d");

        const vw = video.videoWidth;
        const vh = video.videoHeight;

        const frameRect = frame.getBoundingClientRect();
        const frameRatio = frameRect.width / frameRect.height;
        const videoRatio = vw / vh;

        let sx, sy, sw, sh;

        if (videoRatio > frameRatio) {
            // vidéo trop large → crop gauche/droite
            sh = vh;
            sw = vh * frameRatio;
            sx = (vw - sw) / 2;
            sy = 0;
        } else {
            // vidéo trop haute → crop haut/bas
            sw = vw;
            sh = vw / frameRatio;
            sx = 0;
            sy = (vh - sh) / 2;
        }

        canvas.width = sw;
        canvas.height = sh;

        ctx.drawImage(video, sx, sy, sw, sh, 0, 0, sw, sh);

        this.cropPreviewTarget.src = canvas.toDataURL("image/png");
    }
}
