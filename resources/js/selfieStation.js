// resources/js/selfieStation.js
if (!window.selfieStation) {
    window.selfieStation = function(options = {}) {
        return {
            apiKey: options.apiKey || '',
            uploadRoute: options.uploadRoute || '',
            csrfToken: options.csrfToken || '',
            guestId: options.guestId || '',
            preview: '',

            webcam: null,
            canvas: null,
            video: null,
            isCameraOn: false,

            init() {
                this.video = document.getElementById('webcam');
                this.canvas = document.getElementById('canvas');
                this.webcam = new Webcam(this.video, 'user', this.canvas);
                this.startCamera();
            },

            capture() {
                if (!this.isCameraOn) return;
                this.preview = this.webcam.snap();
                this.$dispatch('open-modal', { id: 'selfie-preview-modal' });
            },

            startCamera() {
                this.webcam.start()
                    .then(() => {
                        this.isCameraOn = true;
                    })
                    .catch(e => {
                        alert("Unable to start webcam: " + e.message);
                    });
            },

            stopCamera() {
                this.webcam.stop();
                this.isCameraOn = false;
            },

            toggleCamera() {
                if (this.isCameraOn) {
                    this.stopCamera();
                } else {
                    this.startCamera();
                }
            },

            confirm() {
                fetch(this.uploadRoute, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({
                        photoData: this.preview,
                        guestId: this.guestId,
                    })
                })
                .then(res => res.ok ? res.json() : Promise.reject(res))
                .then(() => window.location.href = '/dashboard')
                .catch(err => {
                    alert('Upload failed. Please try again.');
                    console.error(err);
                });
            }
        };
    }
}