/**
 * ToolBox — Client-Side Image Processing
 * Uses Canvas API and browser-image-compression
 */

const ClientImg = {

    /**
     * Compress image using browser-image-compression
     */
    async compress(file, maxSizeKB = 200, maxDimension = 1920) {
        const options = {
            maxSizeMB: maxSizeKB / 1024,
            maxWidthOrHeight: maxDimension,
            useWebWorker: true,
            fileType: file.type || 'image/jpeg',
        };
        return await imageCompression(file, options);
    },

    /**
     * Resize image to specific dimensions
     */
    async resize(file, width, height, maintainAspect = true) {
        const img = await this._loadImage(file);
        let w = width, h = height;
        if (maintainAspect) {
            const ratio = img.naturalWidth / img.naturalHeight;
            if (width && !height) h = Math.round(width / ratio);
            else if (height && !width) w = Math.round(height * ratio);
            else {
                const fitRatio = Math.min(width / img.naturalWidth, height / img.naturalHeight);
                w = Math.round(img.naturalWidth * fitRatio);
                h = Math.round(img.naturalHeight * fitRatio);
            }
        }
        const canvas = document.createElement('canvas');
        canvas.width = w;
        canvas.height = h;
        const ctx = canvas.getContext('2d');
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        ctx.drawImage(img, 0, 0, w, h);
        return await this._canvasToBlob(canvas, file.type);
    },

    /**
     * Crop image to a rectangle
     */
    async crop(file, x, y, width, height) {
        const img = await this._loadImage(file);
        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, x, y, width, height, 0, 0, width, height);
        return await this._canvasToBlob(canvas, file.type);
    },

    /**
     * Create meme with top/bottom text
     */
    async meme(file, topText, bottomText, options = {}) {
        const img = await this._loadImage(file);
        const canvas = document.createElement('canvas');
        canvas.width = img.naturalWidth;
        canvas.height = img.naturalHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0);

        const fontSize = options.fontSize || Math.max(24, Math.floor(canvas.width / 14));
        const fontFamily = options.fontFamily || 'Impact, sans-serif';
        const textColor = options.textColor || '#FFFFFF';
        const strokeColor = options.strokeColor || '#000000';

        ctx.font = `bold ${fontSize}px ${fontFamily}`;
        ctx.textAlign = 'center';
        ctx.fillStyle = textColor;
        ctx.strokeStyle = strokeColor;
        ctx.lineWidth = Math.max(2, fontSize / 12);
        ctx.lineJoin = 'round';

        if (topText) {
            const y = fontSize + 10;
            ctx.strokeText(topText.toUpperCase(), canvas.width / 2, y);
            ctx.fillText(topText.toUpperCase(), canvas.width / 2, y);
        }
        if (bottomText) {
            const y = canvas.height - 15;
            ctx.strokeText(bottomText.toUpperCase(), canvas.width / 2, y);
            ctx.fillText(bottomText.toUpperCase(), canvas.width / 2, y);
        }

        return await this._canvasToBlob(canvas, 'image/png');
    },

    /**
     * Flip image horizontally or vertically
     */
    async flip(file, direction = 'horizontal') {
        const img = await this._loadImage(file);
        const canvas = document.createElement('canvas');
        canvas.width = img.naturalWidth;
        canvas.height = img.naturalHeight;
        const ctx = canvas.getContext('2d');

        if (direction === 'horizontal') {
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
        } else {
            ctx.translate(0, canvas.height);
            ctx.scale(1, -1);
        }

        ctx.drawImage(img, 0, 0);
        return await this._canvasToBlob(canvas, file.type);
    },

    /**
     * Convert image to grayscale
     */
    async grayscale(file) {
        const img = await this._loadImage(file);
        const canvas = document.createElement('canvas');
        canvas.width = img.naturalWidth;
        canvas.height = img.naturalHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0);

        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const data = imageData.data;
        for (let i = 0; i < data.length; i += 4) {
            const gray = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
            data[i] = data[i + 1] = data[i + 2] = gray;
        }
        ctx.putImageData(imageData, 0, 0);
        return await this._canvasToBlob(canvas, file.type);
    },

    /**
     * Get image dimensions
     */
    async getDimensions(file) {
        const img = await this._loadImage(file);
        return { width: img.naturalWidth, height: img.naturalHeight };
    },

    /**
     * Create preview URL
     */
    createPreview(file) {
        return URL.createObjectURL(file);
    },

    /**
     * Download a blob
     */
    download(blob, filename) {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        setTimeout(() => URL.revokeObjectURL(url), 5000);
    },

    // ─── Private helpers ──────────────────────

    _loadImage(file) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => resolve(img);
            img.onerror = reject;
            img.src = URL.createObjectURL(file instanceof Blob ? file : new Blob([file]));
        });
    },

    _canvasToBlob(canvas, type = 'image/png', quality = 0.92) {
        return new Promise(resolve => {
            canvas.toBlob(blob => resolve(blob), type, quality);
        });
    }
};
