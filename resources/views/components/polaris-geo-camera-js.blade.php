<script>
(function () {
    if (typeof window.polarisRequestCameraOnly === 'function') return;

    /**
     * Request camera stream with optional facing mode, attach to <video>, return MediaStream.
     * Used by attendance and verification camera pages.
     */
    window.polarisRequestCameraOnly = async function (videoEl, options) {
        if (!videoEl) {
            throw new Error('No video element');
        }
        var setHint =
            options && typeof options.setHint === 'function'
                ? options.setHint
                : function () {};

        var gum =
            navigator.mediaDevices &&
            typeof navigator.mediaDevices.getUserMedia === 'function'
                ? navigator.mediaDevices.getUserMedia.bind(navigator.mediaDevices)
                : null;

        if (!gum && typeof navigator.webkitGetUserMedia === 'function') {
            gum = function (constraints) {
                return new Promise(function (resolve, reject) {
                    navigator.webkitGetUserMedia(constraints, resolve, reject);
                });
            };
        }

        if (!gum) {
            setHint('Camera is not supported in this browser.');
            throw new Error('getUserMedia not available');
        }

        var requestedFacingMode =
            options && typeof options.facingMode === 'string'
                ? options.facingMode
                : 'user';

        var constraintsIdeal = {
            video: {
                facingMode: { ideal: requestedFacingMode },
                width: { ideal: 1280 },
                height: { ideal: 720 },
            },
            audio: false,
        };
        var constraintsExact = {
            video: {
                facingMode: { exact: requestedFacingMode },
                width: { ideal: 1280 },
                height: { ideal: 720 },
            },
            audio: false,
        };
        var constraintsFallback = { video: true, audio: false };

        var mediaStream;
        try {
            mediaStream = await gum(constraintsExact);
        } catch (e0) {
            try {
                mediaStream = await gum(constraintsIdeal);
            } catch (e1) {
                try {
                    mediaStream = await gum(constraintsFallback);
                } catch (e2) {
                    throw e0 || e1 || e2;
                }
            }
        }
        videoEl.srcObject = mediaStream;
        videoEl.muted = true;
        videoEl.setAttribute('playsinline', '');
        videoEl.setAttribute('webkit-playsinline', '');
        await videoEl.play();
        return mediaStream;
    };
})();
</script>
