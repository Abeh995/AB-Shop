/**
 * AB-Socks Admin Image Optimizer
 *
 * Provides client-side image resizing, WebP conversion, EXIF/GPS stripping,
 * live preview, quality slider, and seamless HTML5 DataTransfer form integration.
 * Offloads heavy image processing from shared hosting RAM/CPU to the admin's device.
 *
 * Supports: JPEG, PNG, WEBP, GIF, and HEIC/HEIF (via vendored heic2any).
 */
(function () {
    'use strict';

    // Persian number formatter
    function toPersianDigits(str) {
        if (str === null || str === undefined) return '';
        const id = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return String(str).replace(/[0-9]/g, function (w) {
            return id[+w];
        });
    }

    // Format byte sizes into readable Persian units
    function formatBytes(bytes) {
        if (!bytes || bytes === 0) return '۰ بایت';
        const k = 1024;
        const sizes = ['بایت', 'کیلوبایت', 'مگابایت', 'گیگابایت'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        const val = (bytes / Math.pow(k, i)).toFixed(i === 0 ? 0 : 1);
        return toPersianDigits(val) + ' ' + sizes[i];
    }

    // Dynamically load heic2any if a HEIC/HEIF file is encountered
    let heicPromise = null;
    function loadHeicLibrary() {
        if (typeof window.heic2any !== 'undefined') {
            return Promise.resolve();
        }
        if (heicPromise) {
            return heicPromise;
        }
        heicPromise = new Promise(function (resolve, reject) {
            const script = document.createElement('script');
            script.src = '/assets/js/vendor/heic2any.min.js';
            script.onload = function () {
                resolve();
            };
            script.onerror = function () {
                // Fallback to CDN if local file fails
                const cdnScript = document.createElement('script');
                cdnScript.src = 'https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js';
                cdnScript.onload = function () { resolve(); };
                cdnScript.onerror = function () {
                    reject(new Error('امکان بارگذاری کتابخانه تبدیل HEIC وجود ندارد.'));
                };
                document.head.appendChild(cdnScript);
            };
            document.head.appendChild(script);
        });
        return heicPromise;
    }

    /**
     * Optimizer class attached to a specific file input
     */
    class ImageOptimizerWidget {
        constructor(inputEl) {
            this.input = inputEl;
            this.form = inputEl.closest('form');
            this.isMultiple = inputEl.hasAttribute('multiple');
            this.maxDimension = parseInt(inputEl.dataset.maxDimension || '1600', 10);
            this.defaultQuality = parseFloat(inputEl.dataset.defaultQuality || '0.82');
            this.allowSvg = inputEl.dataset.allowSvg === 'true';
            this.role = inputEl.dataset.optimizeImage || 'image';

            // Items state: array of { id, originalFile, canvas, compressedBlob, compressedFile, quality, targetW, targetH, origW, origH, isSvg, previewUrl }
            this.items = [];
            this.isProcessing = false;

            this.initUI();
            this.bindEvents();
        }

        initUI() {
            // Hide original input but keep it in DOM for form submission
            this.input.style.position = 'absolute';
            this.input.style.width = '1px';
            this.input.style.height = '1px';
            this.input.style.opacity = '0';
            this.input.style.pointerEvents = 'none';

            // Create wrapper
            this.wrapper = document.createElement('div');
            this.wrapper.className = 'aio-widget';

            // Dropzone
            this.dropzone = document.createElement('div');
            this.dropzone.className = 'aio-dropzone';
            this.dropzone.innerHTML = `
                <div class="aio-dropzone-icon">📷</div>
                <div class="aio-dropzone-text">
                    <strong>برای انتخاب ${this.isMultiple ? 'تصاویر' : 'تصویر'} کلیک کنید</strong> یا فایل را اینجا بکشید
                </div>
                <div class="aio-dropzone-hint">
                    پشتیبانی از انواع فرمت‌ها (JPG, PNG, WebP, HEIC دوربین آیفون تا ۴۰ مگابایت) • فشرده‌سازی خودکار و حذف متادیتا
                </div>
            `;

            // Progress bar container
            this.progressBox = document.createElement('div');
            this.progressBox.className = 'aio-progress-box';
            this.progressBox.style.display = 'none';
            this.progressBox.innerHTML = `
                <div class="aio-spinner"></div>
                <div class="aio-progress-text">در حال بهینه‌سازی و کاهش حجم تصویر...</div>
            `;

            // Previews container
            this.previewContainer = document.createElement('div');
            this.previewContainer.className = this.isMultiple ? 'aio-previews-grid' : 'aio-preview-single';

            this.wrapper.appendChild(this.dropzone);
            this.wrapper.appendChild(this.progressBox);
            this.wrapper.appendChild(this.previewContainer);

            this.input.parentNode.insertBefore(this.wrapper, this.input.nextSibling);
        }

        bindEvents() {
            const self = this;

            // Clicking dropzone opens file dialog
            this.dropzone.addEventListener('click', function () {
                self.input.click();
            });

            // Drag and drop handlers
            ['dragenter', 'dragover'].forEach(function (eventName) {
                self.dropzone.addEventListener(eventName, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    self.dropzone.classList.add('aio-dragover');
                }, false);
            });

            ['dragleave', 'drop'].forEach(function (eventName) {
                self.dropzone.addEventListener(eventName, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    self.dropzone.classList.remove('aio-dragover');
                }, false);
            });

            this.dropzone.addEventListener('drop', function (e) {
                const dt = e.dataTransfer;
                if (dt && dt.files && dt.files.length) {
                    self.handleFiles(Array.from(dt.files));
                }
            });

            // Native input change
            this.input.addEventListener('change', function () {
                if (self.input.files && self.input.files.length) {
                    self.handleFiles(Array.from(self.input.files));
                }
            });

            // Intercept form submit if processing
            if (this.form) {
                this.form.addEventListener('submit', function (e) {
                    if (self.isProcessing) {
                        e.preventDefault();
                        alert('لطفاً تا اتمام پردازش و فشرده‌سازی تصاویر صبر کنید.');
                    }
                });
            }
        }

        async handleFiles(files) {
            if (!files || !files.length) return;

            // Filter image files or HEIC extensions
            const validFiles = files.filter(function (f) {
                const name = f.name.toLowerCase();
                return f.type.startsWith('image/') ||
                    name.endsWith('.heic') ||
                    name.endsWith('.heif') ||
                    name.endsWith('.webp') ||
                    name.endsWith('.svg');
            });

            if (!validFiles.length) {
                alert('لطفاً فقط فایل‌های تصویری انتخاب کنید.');
                return;
            }

            this.isProcessing = true;
            this.progressBox.style.display = 'flex';
            this.updateSubmitButtons(false);

            if (!this.isMultiple) {
                // Clear previous items
                this.clearItems();
            }

            for (let i = 0; i < validFiles.length; i++) {
                try {
                    await this.processSingleFile(validFiles[i]);
                } catch (err) {
                    console.error('Error processing image:', err);
                    alert('خطا در پردازش تصویر: ' + (err.message || err));
                }
            }

            this.isProcessing = false;
            this.progressBox.style.display = 'none';
            this.updateSubmitButtons(true);
            this.syncFilesToInput();
            this.renderPreviews();
        }

        clearItems() {
            this.items.forEach(function (item) {
                if (item.previewUrl) URL.revokeObjectURL(item.previewUrl);
            });
            this.items = [];
        }

        async processSingleFile(file) {
            const self = this;
            const itemId = 'aio-' + Math.random().toString(36).substr(2, 9);
            const isHeic = file.name.match(/\.(heic|heif)$/i) || file.type.includes('heic');
            const isSvg = self.allowSvg && (file.type === 'image/svg+xml' || file.name.toLowerCase().endsWith('.svg'));

            // Handle SVG: Keep intact as vector
            if (isSvg) {
                const url = URL.createObjectURL(file);
                self.items.push({
                    id: itemId,
                    originalFile: file,
                    compressedFile: file,
                    compressedBlob: file,
                    isSvg: true,
                    previewUrl: url,
                    origSize: file.size,
                    compressedSize: file.size,
                    quality: 1
                });
                return;
            }

            let sourceBlob = file;

            // Decode HEIC if needed
            if (isHeic) {
                this.progressBox.querySelector('.aio-progress-text').textContent = 'در حال تبدیل فرمت HEIC آیفون...';
                await loadHeicLibrary();
                const conversionResult = await window.heic2any({
                    blob: file,
                    toType: 'image/jpeg',
                    quality: 0.95
                });
                sourceBlob = Array.isArray(conversionResult) ? conversionResult[0] : conversionResult;
            }

            this.progressBox.querySelector('.aio-progress-text').textContent = 'در حال بهینه‌سازی و فشرده‌سازی در مرورگر...';

            // Load into HTMLImageElement
            const img = await this.loadImageFromBlob(sourceBlob);
            const origW = img.naturalWidth;
            const origH = img.naturalHeight;

            // Calculate target dimensions
            let targetW = origW;
            let targetH = origH;
            if (origW > self.maxDimension || origH > self.maxDimension) {
                if (origW >= origH) {
                    targetW = self.maxDimension;
                    targetH = Math.round((origH * self.maxDimension) / origW);
                } else {
                    targetH = self.maxDimension;
                    targetW = Math.round((origW * self.maxDimension) / origH);
                }
            }

            // Draw to Canvas (stripping all EXIF/GPS metadata automatically)
            const canvas = document.createElement('canvas');
            canvas.width = targetW;
            canvas.height = targetH;
            const ctx = canvas.getContext('2d', { alpha: true });
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = 'high';
            ctx.drawImage(img, 0, 0, targetW, targetH);

            // Export to WebP
            const quality = self.defaultQuality;
            const blob = await this.canvasToWebpBlob(canvas, quality);

            // Base filename with .webp extension
            const rawBaseName = file.name.replace(/\.[^.]+$/, '').replace(/[^a-zA-Z0-9_\-\.]/g, '_');
            const cleanName = (rawBaseName || 'image') + '.webp';
            const compressedFile = new File([blob], cleanName, { type: blob.type, lastModified: Date.now() });
            const previewUrl = URL.createObjectURL(blob);

            self.items.push({
                id: itemId,
                originalFile: file,
                canvas: canvas,
                compressedBlob: blob,
                compressedFile: compressedFile,
                quality: quality,
                origW: origW,
                origH: origH,
                targetW: targetW,
                targetH: targetH,
                origSize: file.size,
                compressedSize: blob.size,
                isSvg: false,
                previewUrl: previewUrl
            });
        }

        loadImageFromBlob(blob) {
            return new Promise(function (resolve, reject) {
                const url = URL.createObjectURL(blob);
                const img = new Image();
                img.onload = function () {
                    URL.revokeObjectURL(url);
                    resolve(img);
                };
                img.onerror = function (e) {
                    URL.revokeObjectURL(url);
                    reject(new Error('بارگذاری تصویر برای پردازش ناموفق بود.'));
                };
                img.src = url;
            });
        }

        canvasToWebpBlob(canvas, quality) {
            return new Promise(function (resolve) {
                canvas.toBlob(function (blob) {
                    if (blob && blob.size > 0) {
                        resolve(blob);
                    } else {
                        // Fallback to JPEG if WebP export is unsupported
                        canvas.toBlob(function (fallbackBlob) {
                            resolve(fallbackBlob);
                        }, 'image/jpeg', quality);
                    }
                }, 'image/webp', quality);
            });
        }

        renderPreviews() {
            const self = this;
            this.previewContainer.innerHTML = '';

            if (!this.items.length) {
                return;
            }

            this.items.forEach(function (item, index) {
                const card = document.createElement('div');
                card.className = 'aio-card';
                card.id = item.id;

                if (item.isSvg) {
                    card.innerHTML = `
                        <div class="aio-card-thumb">
                            <img src="${item.previewUrl}" alt="SVG Logo" style="object-fit:contain;">
                        </div>
                        <div class="aio-card-info">
                            <div class="aio-card-title">${item.originalFile.name}</div>
                            <div class="aio-badge aio-badge-success">فایل وکتور SVG • کیفیت بی‌نهایت • ${formatBytes(item.origSize)}</div>
                        </div>
                        <button type="button" class="aio-btn-remove" title="حذف">✕</button>
                    `;
                } else {
                    const savingsPercent = Math.max(0, Math.round(((item.origSize - item.compressedSize) / item.origSize) * 100));
                    const qualityPercent = Math.round(item.quality * 100);

                    card.innerHTML = `
                        <div class="aio-card-thumb" title="پیش‌نمایش تصویر بهینه‌شده">
                            <img src="${item.previewUrl}" alt="Preview">
                            <span class="aio-thumb-badge">WebP</span>
                        </div>
                        <div class="aio-card-info">
                            <div class="aio-card-header">
                                <span class="aio-card-filename">${item.compressedFile.name}</span>
                                <span class="aio-badge aio-badge-primary">${toPersianDigits(savingsPercent)}٪ کاهش حجم</span>
                            </div>
                            <div class="aio-stats-row">
                                <span class="aio-stat">
                                    <span class="aio-stat-label">حجم:</span>
                                    <span class="aio-stat-old">${formatBytes(item.origSize)}</span>
                                    <span class="aio-stat-arrow">←</span>
                                    <strong class="aio-stat-new">${formatBytes(item.compressedSize)}</strong>
                                </span>
                                <span class="aio-stat">
                                    <span class="aio-stat-label">ابعاد:</span>
                                    <span class="aio-stat-old">${toPersianDigits(item.origW)}×${toPersianDigits(item.origH)}</span>
                                    <span class="aio-stat-arrow">←</span>
                                    <strong class="aio-stat-new">${toPersianDigits(item.targetW)}×${toPersianDigits(item.targetH)}</strong>
                                </span>
                            </div>
                            <div class="aio-security-badge">
                                🛡️ متادیتا و موقعیت مکانی (GPS/EXIF) با موفقیت حذف شدند
                            </div>
                            <div class="aio-slider-wrap">
                                <div class="aio-slider-header">
                                    <span>کیفیت تصویر: <strong class="aio-quality-val">${toPersianDigits(qualityPercent)}٪</strong></span>
                                    <small style="opacity:.7;">(جابجا کنید تا در لحظه حجم تغییر کند)</small>
                                </div>
                                <input type="range" class="aio-quality-slider" min="50" max="95" step="5" value="${qualityPercent}">
                            </div>
                        </div>
                        <button type="button" class="aio-btn-remove" title="حذف این تصویر">✕</button>
                    `;

                    // Bind quality slider
                    const slider = card.querySelector('.aio-quality-slider');
                    const qualityVal = card.querySelector('.aio-quality-val');
                    let debounceTimer = null;

                    slider.addEventListener('input', function (e) {
                        const newQVal = parseInt(e.target.value, 10);
                        qualityVal.textContent = toPersianDigits(newQVal) + '٪';
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(async function () {
                            const newQ = newQVal / 100;
                            item.quality = newQ;
                            const newBlob = await self.canvasToWebpBlob(item.canvas, newQ);
                            item.compressedBlob = newBlob;
                            item.compressedSize = newBlob.size;
                            item.compressedFile = new File([newBlob], item.compressedFile.name, { type: newBlob.type, lastModified: Date.now() });

                            if (item.previewUrl) URL.revokeObjectURL(item.previewUrl);
                            item.previewUrl = URL.createObjectURL(newBlob);

                            // Update thumbnail and stats in DOM
                            card.querySelector('.aio-card-thumb img').src = item.previewUrl;
                            card.querySelector('.aio-stat-new').textContent = formatBytes(newBlob.size);
                            const newSavings = Math.max(0, Math.round(((item.origSize - newBlob.size) / item.origSize) * 100));
                            card.querySelector('.aio-badge-primary').textContent = toPersianDigits(newSavings) + '٪ کاهش حجم';

                            self.syncFilesToInput();
                        }, 120);
                    });
                }

                // Bind remove button
                card.querySelector('.aio-btn-remove').addEventListener('click', function () {
                    self.removeItem(item.id);
                });

                self.previewContainer.appendChild(card);
            });
        }

        removeItem(itemId) {
            const idx = this.items.findIndex(function (it) { return it.id === itemId; });
            if (idx !== -1) {
                if (this.items[idx].previewUrl) URL.revokeObjectURL(this.items[idx].previewUrl);
                this.items.splice(idx, 1);
                this.syncFilesToInput();
                this.renderPreviews();
            }
        }

        syncFilesToInput() {
            // Use DataTransfer to populate input.files
            try {
                const dt = new DataTransfer();
                this.items.forEach(function (item) {
                    dt.items.add(item.compressedFile);
                });
                this.input.files = dt.files;
            } catch (err) {
                console.warn('DataTransfer could not be set directly:', err);
            }
        }

        updateSubmitButtons(enabled) {
            if (!this.form) return;
            const buttons = this.form.querySelectorAll('button[type="submit"]');
            buttons.forEach(function (btn) {
                btn.disabled = !enabled;
                if (!enabled) {
                    btn.dataset.origText = btn.dataset.origText || btn.textContent;
                    btn.textContent = 'در حال فشرده‌سازی تصویر...';
                } else if (btn.dataset.origText) {
                    btn.textContent = btn.dataset.origText;
                }
            });
        }
    }

    // Auto-initialize on DOM ready
    function initOptimizers() {
        const inputs = document.querySelectorAll('input[type="file"][data-optimize-image]');
        inputs.forEach(function (input) {
            if (!input._aioInitialized) {
                input._aioInitialized = true;
                new ImageOptimizerWidget(input);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initOptimizers);
    } else {
        initOptimizers();
    }

    // Expose globally if needed
    window.ImageOptimizerWidget = ImageOptimizerWidget;
})();
