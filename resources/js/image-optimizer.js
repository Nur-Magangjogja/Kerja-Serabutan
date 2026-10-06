/**
 * Mobile Image Optimizer for SayaBantu
 * Canonical client-side image compression and resizing engine.
 * 
 * Supports: Android Chrome/WebView, iOS Safari/WebView, Desktop Chrome/Edge/Firefox/Safari.
 * Presets: document (1920px @ 0.85), selfie (1600px @ 0.82), evidence (1600px @ 0.80).
 */

export const PRESETS = {
    document: {
        maxLongEdge: 1920,
        quality: 0.85,
        mime: 'image/jpeg',
        maxSizeKB: 2048, // 2 MB server target guard
        backgroundColor: '#FFFFFF',
    },
    selfie: {
        maxLongEdge: 1600,
        quality: 0.82,
        mime: 'image/jpeg',
        maxSizeKB: 1536, // 1.5 MB server target guard
        backgroundColor: '#FFFFFF',
    },
    evidence: {
        maxLongEdge: 1600,
        quality: 0.80,
        mime: 'image/jpeg',
        maxSizeKB: 1536, // 1.5 MB server target guard
        backgroundColor: '#FFFFFF',
    },
    banner: {
        maxLongEdge: 1440,
        quality: 0.85,
        mime: 'image/jpeg',
        maxSizeKB: 1024, // 1 MB server target guard
        backgroundColor: '#FFFFFF',
    },
    qris: {
        maxLongEdge: 1200,
        quality: 1.0,
        mime: 'image/png', // Canonical lossless barcode preservation
        maxSizeKB: 1024, // 1 MB server target guard
        backgroundColor: '#FFFFFF',
    },
};

/**
 * Resolve preset configuration
 */
export function getPreset(presetNameOrConfig) {
    if (typeof presetNameOrConfig === 'string') {
        const key = presetNameOrConfig.toLowerCase();
        if (PRESETS[key]) {
            return { ...PRESETS[key], name: key };
        }
    } else if (presetNameOrConfig && typeof presetNameOrConfig === 'object') {
        return {
            maxLongEdge: presetNameOrConfig.maxLongEdge || 1600,
            quality: presetNameOrConfig.quality || 0.80,
            mime: presetNameOrConfig.mime || 'image/jpeg',
            maxSizeKB: presetNameOrConfig.maxSizeKB || 1536,
            backgroundColor: presetNameOrConfig.backgroundColor !== undefined ? presetNameOrConfig.backgroundColor : '#FFFFFF',
            name: presetNameOrConfig.name || 'custom',
        };
    }
    // Default fallback to evidence preset
    return { ...PRESETS.evidence, name: 'evidence' };
}

/**
 * Calculate proportional dimensions without upscaling
 */
export function calculateTargetDimensions(originalWidth, originalHeight, maxLongEdge) {
    if (!originalWidth || !originalHeight || originalWidth <= 0 || originalHeight <= 0) {
        return { width: 0, height: 0, scaled: false };
    }

    const longEdge = Math.max(originalWidth, originalHeight);
    if (longEdge <= maxLongEdge) {
        return {
            width: Math.round(originalWidth),
            height: Math.round(originalHeight),
            scaled: false,
        };
    }

    const scaleRatio = maxLongEdge / longEdge;
    return {
        width: Math.max(1, Math.round(originalWidth * scaleRatio)),
        height: Math.max(1, Math.round(originalHeight * scaleRatio)),
        scaled: true,
    };
}

/**
 * Format safe filename matching destination MIME type
 */
export function getSafeFilename(originalFilename, mimeType) {
    const rawName = (originalFilename || 'photo').replace(/[/\\?%*:|"<>]/g, '_');
    const dotIndex = rawName.lastIndexOf('.');
    const baseName = dotIndex > 0 ? rawName.substring(0, dotIndex) : rawName;
    const safeBaseName = baseName.replace(/[^a-zA-Z0-9_-]/g, '_').substring(0, 100) || 'image';

    const ext = mimeType === 'image/png' ? '.png' : (mimeType === 'image/webp' ? '.webp' : '.jpg');
    return `${safeBaseName}${ext}`;
}

/**
 * Check if file is HEIC / HEIF format
 */
export function isHeicFile(file) {
    if (!file) return false;
    const type = (file.type || '').toLowerCase();
    const name = (file.name || '').toLowerCase();
    return type === 'image/heic' || type === 'image/heif' || name.endsWith('.heic') || name.endsWith('.heif');
}

/**
 * Check if file is PNG format
 */
export function isPngFile(file) {
    if (!file) return false;
    const type = (file.type || '').toLowerCase();
    const name = (file.name || '').toLowerCase();
    return type === 'image/png' || name.endsWith('.png');
}

/**
 * Check if file is a recognized image type or extension
 */
export function isImageFile(file) {
    if (!file) return false;
    if (file.type && file.type.startsWith('image/')) {
        return true;
    }
    const name = (file.name || '').toLowerCase();
    return /\.(jpe?g|png|webp|bmp|gif|heic|heif|avif)$/i.test(name);
}

/**
 * Detect whether an image contains genuine alpha/transparency pixels
 */
export function hasAlphaChannel(source, width, height) {
    if (typeof document === 'undefined' || !source || !width || !height) return false;
    try {
        const testWidth = Math.min(width, 400);
        const testHeight = Math.max(1, Math.round(height * (testWidth / width)));
        const canvas = document.createElement('canvas');
        canvas.width = testWidth;
        canvas.height = testHeight;
        const ctx = canvas.getContext('2d', { willReadFrequently: true });
        if (!ctx) return false;
        ctx.clearRect(0, 0, testWidth, testHeight);
        ctx.drawImage(source, 0, 0, testWidth, testHeight);
        const imgData = ctx.getImageData(0, 0, testWidth, testHeight);
        if (!imgData || !imgData.data) return false;
        const data = imgData.data;
        for (let i = 3; i < data.length; i += 4) {
            if (data[i] < 255) {
                return true;
            }
        }
        return false;
    } catch (e) {
        return false;
    }
}

/**
 * Decode image file using native browser decoding fallback chain
 * 1. createImageBitmap (modern, async off-thread, auto-orientation)
 * 2. HTMLImageElement + Object URL
 * 3. HTMLImageElement + FileReader DataURL
 */
export async function decodeImageFile(file) {
    // Check basic type
    if (!isImageFile(file)) {
        throw {
            code: 'invalid-image',
            message: 'File yang dipilih bukan file gambar yang valid.',
        };
    }

    // Try createImageBitmap first
    if (typeof window !== 'undefined' && typeof window.createImageBitmap === 'function') {
        try {
            // Note: imageOrientation: 'from-image' is default in modern browsers
            const bitmap = await window.createImageBitmap(file, { imageOrientation: 'from-image' });
            return {
                source: bitmap,
                width: bitmap.width,
                height: bitmap.height,
                cleanup: () => {
                    if (typeof bitmap.close === 'function') {
                        bitmap.close();
                    }
                },
            };
        } catch (err) {
            // If createImageBitmap fails on HEIC or specific format, continue to Image element fallback
            if (isHeicFile(file)) {
                throw {
                    code: 'unsupported-heic',
                    message: 'Foto HEIC/HEIF ini tidak dapat diproses oleh browser ini. Silakan pilih foto JPG/PNG atau ubah format foto terlebih dahulu.',
                };
            }
        }
    }

    // Fallback to HTMLImageElement + Object URL / DataURL
    return new Promise((resolve, reject) => {
        if (typeof document === 'undefined') {
            return reject({
                code: 'no-dom',
                message: 'Lingkungan eksekusi tidak mendukung manipulasi gambar HTML/Canvas.',
            });
        }

        const img = document.createElement('img');
        let objectUrl = null;

        const cleanup = () => {
            img.onload = null;
            img.onerror = null;
            if (objectUrl && typeof URL !== 'undefined' && typeof URL.revokeObjectURL === 'function') {
                URL.revokeObjectURL(objectUrl);
            }
        };

        img.onload = () => {
            const width = img.naturalWidth || img.width;
            const height = img.naturalHeight || img.height;
            if (!width || !height) {
                cleanup();
                return reject({
                    code: 'corrupt-image',
                    message: 'Gambar rusak atau memiliki dimensi nol.',
                });
            }
            resolve({
                source: img,
                width,
                height,
                cleanup,
            });
        };

        img.onerror = () => {
            cleanup();
            if (isHeicFile(file)) {
                reject({
                    code: 'unsupported-heic',
                    message: 'Foto HEIC/HEIF ini tidak dapat diproses oleh browser ini. Silakan pilih foto JPG/PNG atau ubah format foto terlebih dahulu.',
                });
            } else {
                reject({
                    code: 'corrupt-image',
                    message: 'Gambar rusak atau tidak dapat dibaca oleh browser.',
                });
            }
        };

        try {
            if (typeof URL !== 'undefined' && typeof URL.createObjectURL === 'function') {
                objectUrl = URL.createObjectURL(file);
                img.src = objectUrl;
            } else {
                const reader = new FileReader();
                reader.onload = (e) => {
                    img.src = e.target.result;
                };
                reader.onerror = () => {
                    reject({
                        code: 'read-error',
                        message: 'Gagal membaca file gambar dari perangkat.',
                    });
                };
                reader.readAsDataURL(file);
            }
        } catch (e) {
            reject({
                code: 'load-error',
                message: 'Gagal memuat gambar: ' + (e.message || 'Unknown error'),
            });
        }
    });
}

/**
 * Render image onto canvas and export as Blob
 */
export async function renderCanvasToBlob(source, targetWidth, targetHeight, mimeType, quality, backgroundColor) {
    if (typeof document === 'undefined') {
        throw new Error('Canvas rendering requires a document environment');
    }

    const canvas = document.createElement('canvas');
    canvas.width = targetWidth;
    canvas.height = targetHeight;

    const ctx = canvas.getContext('2d');
    if (!ctx) {
        throw new Error('Gagal menginisialisasi konteks 2D canvas.');
    }

    // Handle background color
    if (backgroundColor) {
        ctx.fillStyle = backgroundColor;
        ctx.fillRect(0, 0, targetWidth, targetHeight);
    } else if (mimeType === 'image/jpeg') {
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(0, 0, targetWidth, targetHeight);
    } else {
        // Clear canvas for transparent PNG
        ctx.clearRect(0, 0, targetWidth, targetHeight);
    }

    // High quality smoothing
    ctx.imageSmoothingEnabled = true;
    ctx.imageSmoothingQuality = 'high';

    // Draw image onto canvas
    ctx.drawImage(source, 0, 0, targetWidth, targetHeight);

    // Export to Blob
    if (typeof canvas.toBlob === 'function') {
        return new Promise((resolve, reject) => {
            canvas.toBlob(
                (blob) => {
                    if (blob) {
                        resolve(blob);
                    } else {
                        reject(new Error('Canvas toBlob menghasilkan output kosong.'));
                    }
                },
                mimeType,
                quality
            );
        });
    }

    // Fallback for older browsers
    const dataUrl = canvas.toDataURL(mimeType, quality);
    const arr = dataUrl.split(',');
    const mimeMatch = arr[0].match(/:(.*?);/);
    const mime = mimeMatch ? mimeMatch[1] : mimeType;
    const bstr = atob(arr[1]);
    let n = bstr.length;
    const u8arr = new Uint8Array(n);
    while (n--) {
        u8arr[n] = bstr.charCodeAt(n);
    }
    return new Blob([u8arr], { type: mime });
}

/**
 * Canonical Image Optimizer Function
 * 
 * @param {File|Blob} file - The original file selected by user
 * @param {string|object} presetConfig - Preset name ('document', 'selfie', 'evidence', 'banner', 'qris') or custom config object
 * @returns {Promise<object>} Structured result containing optimized File, dimensions, and metadata
 */
export async function optimizeImage(file, presetConfig = 'evidence') {
    if (!file) {
        return {
            error: true,
            code: 'no-file',
            message: 'Tidak ada file yang dipilih.',
        };
    }

    const preset = getPreset(presetConfig);
    const presetName = preset.name || 'custom';

    let decoded = null;
    try {
        decoded = await decodeImageFile(file);
    } catch (err) {
        return {
            error: true,
            code: err.code || 'decode-error',
            message: err.message || 'Gagal membaca gambar.',
        };
    }

    try {
        const { source, width: originalWidth, height: originalHeight, cleanup } = decoded;
        const maxSizeBytes = preset.maxSizeKB * 1024;
        const isInputPng = isPngFile(file);

        // -------------------------------------------------------------
        // QRIS PRESET PIPELINE
        // -------------------------------------------------------------
        if (presetName === 'qris') {
            const hasAlpha = isInputPng ? hasAlphaChannel(source, originalWidth, originalHeight) : false;

            // Keep-original fast path:
            // If already PNG, <= 1200px long edge, <= 1024 KB, and NO alpha needing flattening
            if (isInputPng && Math.max(originalWidth, originalHeight) <= 1200 && file.size <= maxSizeBytes && !hasAlpha) {
                if (typeof cleanup === 'function') cleanup();
                const safeFilename = getSafeFilename(file.name, 'image/png');
                return {
                    error: false,
                    file: file,
                    blob: file,
                    name: safeFilename,
                    mime: 'image/png',
                    originalSize: file.size,
                    optimizedSize: file.size,
                    originalWidth,
                    originalHeight,
                    width: originalWidth,
                    height: originalHeight,
                    scaled: false,
                    preset: 'qris',
                    optimized: false,
                    keepOriginal: true,
                };
            }

            // Always canonical PNG output flattened onto white background
            let currentTarget = calculateTargetDimensions(originalWidth, originalHeight, 1200);
            let blob = await renderCanvasToBlob(
                source,
                currentTarget.width,
                currentTarget.height,
                'image/png',
                1.0,
                '#FFFFFF'
            );

            // QRIS Size guard: if > 1024 KB, one additional deterministic reduction to 1000 px
            if (blob.size > maxSizeBytes) {
                currentTarget = calculateTargetDimensions(originalWidth, originalHeight, 1000);
                const secondBlob = await renderCanvasToBlob(
                    source,
                    currentTarget.width,
                    currentTarget.height,
                    'image/png',
                    1.0,
                    '#FFFFFF'
                );
                if (secondBlob.size < blob.size) {
                    blob = secondBlob;
                }
            }

            if (typeof cleanup === 'function') cleanup();

            if (blob.size > maxSizeBytes) {
                return {
                    error: true,
                    code: 'output-too-large',
                    message: `Ukuran gambar QRIS (${Math.round(blob.size / 1024)} KB) melebihi batas maksimal ${(preset.maxSizeKB / 1024).toFixed(1)} MB. Silakan gunakan gambar QRIS yang lebih sederhana.`,
                    originalSize: file.size,
                    optimizedSize: blob.size,
                    maxSizeKB: preset.maxSizeKB,
                };
            }

            const safeFilename = getSafeFilename(file.name, 'image/png');
            let optimizedFile;
            try {
                optimizedFile = new File([blob], safeFilename, {
                    type: 'image/png',
                    lastModified: Date.now(),
                });
            } catch (e) {
                blob.name = safeFilename;
                blob.lastModifiedDate = new Date();
                optimizedFile = blob;
            }

            return {
                error: false,
                file: optimizedFile,
                blob: blob,
                name: safeFilename,
                mime: 'image/png',
                originalSize: file.size || blob.size,
                optimizedSize: blob.size,
                originalWidth,
                originalHeight,
                width: currentTarget.width,
                height: currentTarget.height,
                scaled: currentTarget.scaled,
                preset: 'qris',
                optimized: true,
            };
        }

        // -------------------------------------------------------------
        // BANNER PRESET PIPELINE
        // -------------------------------------------------------------
        if (presetName === 'banner') {
            const hasAlpha = isInputPng ? hasAlphaChannel(source, originalWidth, originalHeight) : false;

            if (isInputPng && hasAlpha) {
                // Transparent PNG banner: preserve PNG & transparency
                let targetDims = calculateTargetDimensions(originalWidth, originalHeight, 1440);
                let blob = await renderCanvasToBlob(
                    source,
                    targetDims.width,
                    targetDims.height,
                    'image/png',
                    1.0,
                    null // no background color to preserve transparency
                );

                // Deterministic fallback chain: 1440 -> 1280 -> 1080 if > 1024 KB
                if (blob.size > maxSizeBytes) {
                    targetDims = calculateTargetDimensions(originalWidth, originalHeight, 1280);
                    const b1280 = await renderCanvasToBlob(
                        source,
                        targetDims.width,
                        targetDims.height,
                        'image/png',
                        1.0,
                        null
                    );
                    if (b1280.size < blob.size) blob = b1280;
                }

                if (blob.size > maxSizeBytes) {
                    targetDims = calculateTargetDimensions(originalWidth, originalHeight, 1080);
                    const b1080 = await renderCanvasToBlob(
                        source,
                        targetDims.width,
                        targetDims.height,
                        'image/png',
                        1.0,
                        null
                    );
                    if (b1080.size < blob.size) blob = b1080;
                }

                if (typeof cleanup === 'function') cleanup();

                if (blob.size > maxSizeBytes) {
                    return {
                        error: true,
                        code: 'output-too-large',
                        message: `Ukuran banner transparan (${Math.round(blob.size / 1024)} KB) melebihi batas maksimal ${(preset.maxSizeKB / 1024).toFixed(1)} MB.`,
                        originalSize: file.size,
                        optimizedSize: blob.size,
                        maxSizeKB: preset.maxSizeKB,
                    };
                }

                const safeFilename = getSafeFilename(file.name, 'image/png');
                let optimizedFile;
                try {
                    optimizedFile = new File([blob], safeFilename, {
                        type: 'image/png',
                        lastModified: Date.now(),
                    });
                } catch (e) {
                    blob.name = safeFilename;
                    blob.lastModifiedDate = new Date();
                    optimizedFile = blob;
                }

                return {
                    error: false,
                    file: optimizedFile,
                    blob: blob,
                    name: safeFilename,
                    mime: 'image/png',
                    originalSize: file.size || blob.size,
                    optimizedSize: blob.size,
                    originalWidth,
                    originalHeight,
                    width: targetDims.width,
                    height: targetDims.height,
                    scaled: targetDims.scaled,
                    preset: 'banner',
                    optimized: true,
                };
            } else {
                // Opaque PNG or JPEG banner: optimize to JPEG @ 0.85
                const targetDims = calculateTargetDimensions(originalWidth, originalHeight, 1440);
                let blob = await renderCanvasToBlob(
                    source,
                    targetDims.width,
                    targetDims.height,
                    'image/jpeg',
                    0.85,
                    '#FFFFFF'
                );

                if (blob.size > maxSizeBytes) {
                    const fallbackQuality = 0.70;
                    const secondBlob = await renderCanvasToBlob(
                        source,
                        targetDims.width,
                        targetDims.height,
                        'image/jpeg',
                        fallbackQuality,
                        '#FFFFFF'
                    );
                    if (secondBlob.size < blob.size) {
                        blob = secondBlob;
                    }
                }

                if (typeof cleanup === 'function') cleanup();

                if (blob.size > maxSizeBytes) {
                    return {
                        error: true,
                        code: 'output-too-large',
                        message: `Ukuran banner (${Math.round(blob.size / 1024)} KB) melebihi batas maksimal ${(preset.maxSizeKB / 1024).toFixed(1)} MB.`,
                        originalSize: file.size,
                        optimizedSize: blob.size,
                        maxSizeKB: preset.maxSizeKB,
                    };
                }

                const safeFilename = getSafeFilename(file.name, 'image/jpeg');
                let optimizedFile;
                try {
                    optimizedFile = new File([blob], safeFilename, {
                        type: 'image/jpeg',
                        lastModified: Date.now(),
                    });
                } catch (e) {
                    blob.name = safeFilename;
                    blob.lastModifiedDate = new Date();
                    optimizedFile = blob;
                }

                return {
                    error: false,
                    file: optimizedFile,
                    blob: blob,
                    name: safeFilename,
                    mime: 'image/jpeg',
                    originalSize: file.size || blob.size,
                    optimizedSize: blob.size,
                    originalWidth,
                    originalHeight,
                    width: targetDims.width,
                    height: targetDims.height,
                    scaled: targetDims.scaled,
                    preset: 'banner',
                    optimized: true,
                };
            }
        }

        // -------------------------------------------------------------
        // STANDARD PHOTO PRESETS (document, selfie, evidence)
        // -------------------------------------------------------------
        const { width: targetWidth, height: targetHeight, scaled } = calculateTargetDimensions(
            originalWidth,
            originalHeight,
            preset.maxLongEdge
        );

        // First pass rendering
        let blob = await renderCanvasToBlob(
            source,
            targetWidth,
            targetHeight,
            preset.mime,
            preset.quality,
            preset.backgroundColor
        );

        // Deterministic size guard & optional single-pass quality adjustment
        if (blob.size > maxSizeBytes && preset.quality > 0.65) {
            // Secondary deterministic pass with reduced quality
            const fallbackQuality = Math.max(0.60, preset.quality - 0.15);
            const secondBlob = await renderCanvasToBlob(
                source,
                targetWidth,
                targetHeight,
                preset.mime,
                fallbackQuality,
                preset.backgroundColor
            );
            if (secondBlob.size < blob.size) {
                blob = secondBlob;
            }
        }

        // Clean up decoded source (ImageBitmap or Object URL)
        if (typeof cleanup === 'function') {
            cleanup();
        }

        // Check if output exceeds server hard guard limit after compression
        if (blob.size > maxSizeBytes) {
            return {
                error: true,
                code: 'output-too-large',
                message: `Ukuran foto setelah dioptimalkan (${Math.round(blob.size / 1024)} KB) melebihi batas maksimal ${(preset.maxSizeKB / 1024).toFixed(1)} MB. Silakan gunakan foto lain.`,
                originalSize: file.size,
                optimizedSize: blob.size,
                maxSizeKB: preset.maxSizeKB,
            };
        }

        // Create safe File object
        const safeFilename = getSafeFilename(file.name, preset.mime);
        let optimizedFile;
        try {
            optimizedFile = new File([blob], safeFilename, {
                type: preset.mime,
                lastModified: Date.now(),
            });
        } catch (e) {
            blob.name = safeFilename;
            blob.lastModifiedDate = new Date();
            optimizedFile = blob;
        }

        return {
            error: false,
            file: optimizedFile,
            blob: blob,
            name: safeFilename,
            mime: preset.mime,
            originalSize: file.size || blob.size,
            optimizedSize: blob.size,
            originalWidth,
            originalHeight,
            width: targetWidth,
            height: targetHeight,
            scaled,
            preset: preset.name,
            optimized: true,
        };
    } catch (renderError) {
        if (decoded && typeof decoded.cleanup === 'function') {
            decoded.cleanup();
        }
        return {
            error: true,
            code: 'render-error',
            message: 'Gagal memproses dan mengompresi gambar: ' + (renderError.message || 'Unknown error'),
        };
    }
}

// Attach to window object for global availability in Livewire / Alpine components
if (typeof window !== 'undefined') {
    window.MobileImageOptimizer = {
        optimizeImage,
        calculateTargetDimensions,
        getPreset,
        getSafeFilename,
        isImageFile,
        isHeicFile,
        isPngFile,
        hasAlphaChannel,
        PRESETS,
    };
}
