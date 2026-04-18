{{-- Shared: W3C Geolocation API first, then getUserMedia (camera). Include once per page if needed. --}}
@once
<script>
(function () {
    if (window.polarisRequestLocationThenCamera) return;
    // opts: latEl, lngEl, accEl (optional inputs), setHint (optional fn)
    window.polarisRequestLocationThenCamera = function (video, opts) {
        opts = opts || {};
        var latEl = opts.latEl;
        var lngEl = opts.lngEl;
        var accEl = opts.accEl;
        var setHint = opts.setHint || function () {};

        var geoDone = function () { return Promise.resolve(); };

        if (navigator.geolocation) {
            geoDone = function () {
                return new Promise(function (resolve) {
                    navigator.geolocation.getCurrentPosition(
                        function (pos) {
                            if (latEl) latEl.value = String(pos.coords.latitude);
                            if (lngEl) lngEl.value = String(pos.coords.longitude);
                            if (accEl && pos.coords.accuracy != null) {
                                accEl.value = String(pos.coords.accuracy);
                            }
                            setHint('Location saved. Please allow camera access when your browser asks.');
                            resolve();
                        },
                        function () {
                            setHint('Location unavailable. Please allow camera access when your browser asks.');
                            resolve();
                        },
                        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                    );
                });
            };
        } else {
            setHint('Please allow camera access when your browser asks.');
        }

        return geoDone().then(function () {
            return navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user' },
                audio: false,
            });
        }).then(function (stream) {
            video.srcObject = stream;
            return video.play().catch(function () {}).then(function () { return stream; });
        });
    };

    /**
     * Camera only (no geolocation). opts: setHint optional.
     */
    window.polarisRequestCameraOnly = function (video, opts) {
        opts = opts || {};
        var setHint = opts.setHint || function () {};
        setHint('Please allow camera access when your browser asks.');
        return navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user' },
            audio: false,
        }).then(function (stream) {
            video.srcObject = stream;
            return video.play().catch(function () {}).then(function () { return stream; });
        });
    };

    /**
     * Camera first, then geolocation. opts: latEl, lngEl, accEl, setHint optional.
     * Geolocation is non-blocking after camera stream starts.
     */
    window.polarisRequestCameraThenLocation = function (video, opts) {
        opts = opts || {};
        var latEl = opts.latEl;
        var lngEl = opts.lngEl;
        var accEl = opts.accEl;
        var setHint = opts.setHint || function () {};

        setHint('Please allow camera access when your browser asks.');
        return navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user' },
            audio: false,
        }).then(function (stream) {
            video.srcObject = stream;
            return video.play().catch(function () {}).then(function () {
                if (navigator.geolocation) {
                    setHint('Camera ready. Next, allow location when your browser asks.');
                    navigator.geolocation.getCurrentPosition(
                        function (pos) {
                            if (latEl) latEl.value = String(pos.coords.latitude);
                            if (lngEl) lngEl.value = String(pos.coords.longitude);
                            if (accEl && pos.coords.accuracy != null) {
                                accEl.value = String(pos.coords.accuracy);
                            }
                            setHint('Camera and location ready. Position your face, then capture.');
                        },
                        function () {
                            setHint('Camera ready. Location unavailable, you can still continue.');
                        },
                        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                    );
                } else {
                    setHint('Camera ready. Position your face, then capture.');
                }
                return stream;
            });
        });
    };

    /**
     * Map a FaceDetector bounding box from intrinsic video pixels to normalized
     * coordinates on the rendered video element (object-fit: cover, object-position center).
     * Without this, alignment colors invert vs. the on-screen guide when the frame is cropped.
     */
    window.polarisVideoFaceLayoutOnDisplay = function (video, box) {
        var vw = video.videoWidth;
        var vh = video.videoHeight;
        var rw = video.clientWidth;
        var rh = video.clientHeight;
        if (!vw || !vh || !rw || !rh) {
            return { xRatio: 0.5, yRatio: 0.5, sizeRatio: 0 };
        }
        var cx = box.x + box.width / 2;
        var cy = box.y + box.height / 2;
        var scale = Math.max(rw / vw, rh / vh);
        var dispW = vw * scale;
        var dispH = vh * scale;
        var offX = (rw - dispW) / 2;
        var offY = (rh - dispH) / 2;
        var ex = cx * scale + offX;
        var ey = cy * scale + offY;
        var displayFaceW = box.width * scale;
        return {
            xRatio: ex / rw,
            yRatio: ey / rh,
            sizeRatio: displayFaceW / rw
        };
    };
})();
</script>
@endonce
