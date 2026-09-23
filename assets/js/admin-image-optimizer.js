/**
 * AB-Socks Admin Image Optimizer
 *
 * Provides client-side image resizing, WebP conversion, EXIF/GPS stripping,
 * interactive Full-Screen Quality Inspector with live Zoom & Pan,
 * side-by-side original comparison, and seamless HTML5 DataTransfer form integration.
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
     * Singleton Fullscreen Quality Inspector Modal
     */
    class FullscreenInspector {
        constructor() {
            this.activeItem = null;
            this.activeWidget = null;
            this.zoomLevel = 1.0;
            this.panX = 0;
            this.panY = 0;
            this.isDragging = false;
            this.dragStartX = 0;
            this.dragStartY = 0;
            this.isComparingOriginal = false;
            this.origObjectUrl = null;

            this.createModal();
            this.bindEvents();
        }

        createModal() {
            this.modal = document.createElement('div');
            this.modal.className = 'aio-inspector-modal';
            this.modal.id = 'aioInspectorModal';
            this.modal.innerHTML = `
                <div class="aio-inspector-backdrop"></div>
                <div class="aio-inspector-container">
                    <div class="aio-inspector-topbar">
                        <div class="aio-inspector-meta">
                            <span class="aio-inspector-filename"></span>
                            <span class="aio-badge aio-badge-primary aio-inspector-savings-badge"></span>
                            <span class="aio-inspector-dims"></span>
                            <span class="aio-inspector-sizes"></span>
                            <span class="aio-inspector-mode-badge">نسخه بهینه‌شده WebP</span>
                        </div>
                        
                        <div class="aio-inspector-actions">
                            <div class="aio-inspector-slider-wrap">
                                <span>کیفیت: <strong class="aio-inspector-qval">۳۰٪</strong></span>
                                <input type="range" class="aio-inspector-slider" min="10" max="90" step="5" value="30">
                            </div>

                            <button type="button" class="aio-inspector-btn aio-inspector-compare-btn" title="نگه دارید تا عکس خام نمایش یابد">
                                👁️ <span class="aio-compare-label">نگه دارید برای عکس اصلی</span>
                            </button>

                            <div class="aio-inspector-zoom-group">
                                <button type="button" class="aio-zoom-btn aio-zoom-out" title="کوچک‌نمایی (−)">−</button>
                                <span class="aio-zoom-val">۱۰۰٪</span>
                                <button type="button" class="aio-zoom-btn aio-zoom-in" title="بزرگ‌نمایی (+)">+</button>
                                <button type="button" class="aio-zoom-btn aio-zoom-fit" title="انطباق با پنجره">Fit</button>
                                <button type="button" class="aio-zoom-btn aio-zoom-100" title="اندازه واقعی">1:1</button>
                            </div>

                            <button type="button" class="aio-inspector-btn-close" title="بستن (Esc)">✕</button>
                        </div>
                    </div>

                    <div class="aio-inspector-stage">
                        <div class="aio-inspector-canvas-holder">
                            <img class="aio-inspector-image" src="" alt="Full preview" draggable="false">
                        </div>
                        <div class="aio-inspector-hint-bar">
                            💡 با غلتک ماوس یا دکمه‌های + و - زوم کنید • با کشیدن ماوس تصویر را جابجا کنید • دکمه «عکس اصلی» را نگه دارید تا مقایسه شود.
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(this.modal);

            this.stage = this.modal.querySelector('.aio-inspector-stage');
            this.canvasHolder = this.modal.querySelector('.aio-inspector-canvas-holder');
            this.img = this.modal.querySelector('.aio-inspector-image');
            this.filenameEl = this.modal.querySelector('.aio-inspector-filename');
            this.savingsBadge = this.modal.querySelector('.aio-inspector-savings-badge');
            this.dimsEl = this.modal.querySelector('.aio-inspector-dims');
            this.sizesEl = this.modal.querySelector('.aio-inspector-sizes');
            this.modeBadge = this.modal.querySelector('.aio-inspector-mode-badge');
            this.slider = this.modal.querySelector('.aio-inspector-slider');
            this.qval = this.modal.querySelector('.aio-inspector-qval');
            this.zoomVal = this.modal.querySelector('.aio-zoom-val');
            this.compareBtn = this.modal.querySelector('.aio-inspector-compare-btn');
            this.closeBtn = this.modal.querySelector('.aio-inspector-btn-close');
            this.backdrop = this.modal.querySelector('.aio-inspector-backdrop');
        }

        bindEvents() {
            const self = this;

            // Close
            this.closeBtn.addEventListener('click', () => self.close());
            this.backdrop.addEventListener('click', () => self.close());
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && self.isOpen()) {
                    self.close();
                }
            });

            // Zoom In / Out / Fit / 1:1
            this.modal.querySelector('.aio-zoom-in').addEventListener('click', () => self.changeZoom(0.25));
            this.modal.querySelector('.aio-zoom-out').addEventListener('click', () => self.changeZoom(-0.25));
            this.modal.querySelector('.aio-zoom-fit').addEventListener('click', () => self.resetZoom(true));
            this.modal.querySelector('.aio-zoom-100').addEventListener('click', () => self.resetZoom(false));

            // Mouse wheel zoom
            this.stage.addEventListener('wheel', function (e) {
                e.preventDefault();
                const delta = e.deltaY < 0 ? 0.2 : -0.2;
                self.changeZoom(delta);
            }, { passive: false });

            // Drag to Pan
            this.canvasHolder.addEventListener('mousedown', function (e) {
                if (e.button !== 0) return;
                self.isDragging = true;
                self.dragStartX = e.clientX - self.panX;
                self.dragStartY = e.clientY - self.panY;
                self.canvasHolder.classList.add('aio-dragging');
            });

            window.addEventListener('mousemove', function (e) {
                if (!self.isDragging) return;
                self.panX = e.clientX - self.dragStartX;
                self.panY = e.clientY - self.dragStartY;
                self.updateTransform();
            });

            window.addEventListener('mouseup', function () {
                if (self.isDragging) {
                    self.isDragging = false;
                    self.canvasHolder.classList.remove('aio-dragging');
                }
            });

            // Mobile Touch Events for Zoom and Pan
            let initialDistance = 0;
            let initialZoom = 1;
            this.canvasHolder.addEventListener('touchstart', function (e) {
                if (e.touches.length === 1) {
                    self.isDragging = true;
                    self.dragStartX = e.touches[0].clientX - self.panX;
                    self.dragStartY = e.touches[0].clientY - self.panY;
                } else if (e.touches.length === 2) {
                    self.isDragging = false;
                    initialDistance = Math.hypot(
                        e.touches[0].clientX - e.touches[1].clientX,
                        e.touches[0].clientY - e.touches[1].clientY
                    );
                    initialZoom = self.zoomLevel;
                }
            }, { passive: true });

            this.canvasHolder.addEventListener('touchmove', function (e) {
                if (e.touches.length === 1 && self.isDragging) {
                    self.panX = e.touches[0].clientX - self.dragStartX;
                    self.panY = e.touches[0].clientY - self.dragStartY;
                    self.updateTransform();
                } else if (e.touches.length === 2 && initialDistance > 0) {
                    const currentDistance = Math.hypot(
                        e.touches[0].clientX - e.touches[1].clientX,
                        e.touches[0].clientY - e.touches[1].clientY
                    );
                    const factor = currentDistance / initialDistance;
                    self.zoomLevel = Math.max(0.2, Math.min(5.0, initialZoom * factor));
                    self.zoomVal.textContent = toPersianDigits(Math.round(self.zoomLevel * 100)) + '٪';
                    self.updateTransform();
                }
            }, { passive: true });

            this.canvasHolder.addEventListener('touchend', function () {
                self.isDragging = false;
                initialDistance = 0;
            });

            // Quality slider in modal
            let debounceTimer = null;
            this.slider.addEventListener('input', function (e) {
                const newQVal = parseInt(e.target.value, 10);
                self.qval.textContent = toPersianDigits(newQVal) + '٪';
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(async function () {
                    if (!self.activeItem || !self.activeWidget) return;
                    await self.activeWidget.recompressItem(self.activeItem, newQVal / 100);
                    self.refreshItemData();
                }, 120);
            });

            // Compare with Original: Hold or Click
            const startCompare = function () {
                if (!self.activeItem || self.activeItem.isSvg) return;
                self.isComparingOriginal = true;
                if (!self.origObjectUrl) {
                    self.origObjectUrl = URL.createObjectURL(self.activeItem.originalFile);
                }
                self.img.src = self.origObjectUrl;
                self.modeBadge.textContent = 'در حال نمایش: تصویر اصلی خام';
                self.modeBadge.className = 'aio-inspector-mode-badge aio-mode-original';
            };

            const endCompare = function () {
                if (!self.activeItem || !self.isComparingOriginal) return;
                self.isComparingOriginal = false;
                self.img.src = self.activeItem.previewUrl;
                self.modeBadge.textContent = 'در حال نمایش: نسخه بهینه‌شده WebP';
                self.modeBadge.className = 'aio-inspector-mode-badge';
            };

            this.compareBtn.addEventListener('mousedown', startCompare);
            this.compareBtn.addEventListener('mouseup', endCompare);
            this.compareBtn.addEventListener('mouseleave', endCompare);
            this.compareBtn.addEventListener('touchstart', (e) => { e.preventDefault(); startCompare(); });
            this.compareBtn.addEventListener('touchend', endCompare);
        }

        open(item, widget) {
            this.activeItem = item;
            this.activeWidget = widget;
            this.isComparingOriginal = false;
            if (this.origObjectUrl) {
                URL.revokeObjectURL(this.origObjectUrl);
                this.origObjectUrl = null;
            }

            this.refreshItemData();
            this.resetZoom(true);
            this.modal.classList.add('aio-modal-open');
            document.body.style.overflow = 'hidden';
        }

        refreshItemData() {
            const item = this.activeItem;
            if (!item) return;

            this.filenameEl.textContent = item.compressedFile.name;
            this.img.src = item.previewUrl;

            if (item.isSvg) {
                this.savingsBadge.textContent = 'وکتور SVG';
                this.dimsEl.textContent = 'کیفیت برداری نامحدود';
                this.sizesEl.textContent = formatBytes(item.origSize);
                this.slider.parentElement.style.display = 'none';
                this.compareBtn.style.display = 'none';
            } else {
                const savingsPercent = Math.max(0, Math.round(((item.origSize - item.compressedSize) / item.origSize) * 100));
                const qualityPercent = Math.round(item.quality * 100);

                this.savingsBadge.textContent = toPersianDigits(savingsPercent) + '٪ کاهش حجم';
                this.dimsEl.textContent = toPersianDigits(item.targetW) + '×' + toPersianDigits(item.targetH) + ' پیکسل';
                this.sizesEl.innerHTML = `<span class="aio-stat-old">${formatBytes(item.origSize)}</span> ← <strong class="aio-stat-new">${formatBytes(item.compressedSize)}</strong>`;

                this.slider.parentElement.style.display = 'flex';
                this.compareBtn.style.display = 'inline-flex';
                this.slider.value = qualityPercent;
                this.qval.textContent = toPersianDigits(qualityPercent) + '٪';
                this.modeBadge.textContent = 'در حال نمایش: نسخه بهینه‌شده WebP';
                this.modeBadge.className = 'aio-inspector-mode-badge';
            }
        }

        changeZoom(delta) {
            this.zoomLevel = Math.max(0.2, Math.min(5.0, this.zoomLevel + delta));
            this.zoomVal.textContent = toPersianDigits(Math.round(this.zoomLevel * 100)) + '٪';
            this.updateTransform();
        }

        resetZoom(fit) {
            this.panX = 0;
            this.panY = 0;
            this.zoomLevel = fit ? 1.0 : 1.0;
            this.zoomVal.textContent = toPersianDigits(Math.round(this.zoomLevel * 100)) + '٪';
            this.updateTransform();
        }

        updateTransform() {
            this.img.style.transform = `translate(${this.panX}px, ${this.panY}px) scale(${this.zoomLevel})`;
        }

        close() {
            this.modal.classList.remove('aio-modal-open');
            document.body.style.overflow = '';
            if (this.origObjectUrl) {
                URL.revokeObjectURL(this.origObjectUrl);
                this.origObjectUrl = null;
            }
            this.activeItem = null;
            this.activeWidget = null;
        }

        isOpen() {
            return this.modal.classList.contains('aio-modal-open');
        }
    }

    // Global single instance of inspector
    let globalInspector = null;
    function getInspector() {
        if (!globalInspector) {
            globalInspector = new FullscreenInspector();
        }
        return globalInspector;
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
            this.defaultQuality = parseFloat(inputEl.dataset.defaultQuality || '0.30');
            this.allowSvg = inputEl.dataset.allowSvg === 'true';
            this.role = inputEl.dataset.optimizeImage || 'image';

            this.items = [];
            this.isProcessing = false;

            this.initUI();
            this.bindEvents();
        }

        initUI() {
            this.input.style.position = 'absolute';
            this.input.style.width = '1px';
            this.input.style.height = '1px';
            this.input.style.opacity = '0';
            this.input.style.pointerEvents = 'none';

            this.wrapper = document.createElement('div');
            this.wrapper.className = 'aio-widget';

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

            this.progressBox = document.createElement('div');
            this.progressBox.className = 'aio-progress-box';
            this.progressBox.style.display = 'none';
            this.progressBox.innerHTML = `
                <div class="aio-spinner"></div>
                <div class="aio-progress-text">در حال بهینه‌سازی و کاهش حجم تصویر...</div>
            `;

            this.previewContainer = document.createElement('div');
            this.previewContainer.className = this.isMultiple ? 'aio-previews-grid' : 'aio-preview-single';

            this.wrapper.appendChild(this.dropzone);
            this.wrapper.appendChild(this.progressBox);
            this.wrapper.appendChild(this.previewContainer);

            this.input.parentNode.insertBefore(this.wrapper, this.input.nextSibling);
        }

        bindEvents() {
            const self = this;

            this.dropzone.addEventListener('click', function () {
                self.input.click();
            });

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

            this.input.addEventListener('change', function () {
                if (self.input.files && self.input.files.length) {
                    self.handleFiles(Array.from(self.input.files));
                }
            });

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

            const img = await this.loadImageFromBlob(sourceBlob);
            const origW = img.naturalWidth;
            const origH = img.naturalHeight;

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

            const canvas = document.createElement('canvas');
            canvas.width = targetW;
            canvas.height = targetH;
            const ctx = canvas.getContext('2d', { alpha: true });
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = 'high';
            ctx.drawImage(img, 0, 0, targetW, targetH);

            const quality = self.defaultQuality;
            const blob = await this.canvasToWebpBlob(canvas, quality);

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
                img.onerror = function () {
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
                        canvas.toBlob(function (fallbackBlob) {
                            resolve(fallbackBlob);
                        }, 'image/jpeg', quality);
                    }
                }, 'image/webp', quality);
            });
        }

        async recompressItem(item, newQuality) {
            item.quality = newQuality;
            const newBlob = await this.canvasToWebpBlob(item.canvas, newQuality);
            item.compressedBlob = newBlob;
            item.compressedSize = newBlob.size;
            item.compressedFile = new File([newBlob], item.compressedFile.name, { type: newBlob.type, lastModified: Date.now() });

            if (item.previewUrl) URL.revokeObjectURL(item.previewUrl);
            item.previewUrl = URL.createObjectURL(newBlob);

            // Update card DOM
            const card = document.getElementById(item.id);
            if (card) {
                card.querySelector('.aio-card-thumb img').src = item.previewUrl;
                card.querySelector('.aio-stat-new').textContent = formatBytes(newBlob.size);
                const newSavings = Math.max(0, Math.round(((item.origSize - newBlob.size) / item.origSize) * 100));
                card.querySelector('.aio-badge-primary').textContent = toPersianDigits(newSavings) + '٪ کاهش حجم';
                const slider = card.querySelector('.aio-quality-slider');
                if (slider) slider.value = Math.round(newQuality * 100);
                const qval = card.querySelector('.aio-quality-val');
                if (qval) qval.textContent = toPersianDigits(Math.round(newQuality * 100)) + '٪';
            }

            this.syncFilesToInput();
        }

        renderPreviews() {
            const self = this;
            this.previewContainer.innerHTML = '';

            if (!this.items.length) {
                return;
            }

            this.items.forEach(function (item) {
                const card = document.createElement('div');
                card.className = 'aio-card';
                card.id = item.id;

                if (item.isSvg) {
                    card.innerHTML = `
                        <div class="aio-card-thumb" title="پیش‌نمایش لوگوی SVG">
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
                        <div class="aio-card-thumb" title="برای مشاهده بزرگ‌نمایی و بررسی کیفیت کلیک کنید">
                            <img src="${item.previewUrl}" alt="Preview">
                            <span class="aio-thumb-badge">WebP</span>
                            <div class="aio-thumb-zoom-overlay">🔍 تمام‌صفحه</div>
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
                                    <button type="button" class="aio-btn-link aio-open-inspector-btn">🔍 باز کردن در اندازه بزرگ</button>
                                </div>
                                <input type="range" class="aio-quality-slider" min="10" max="90" step="5" value="${qualityPercent}">
                            </div>
                        </div>
                        <button type="button" class="aio-btn-remove" title="حذف این تصویر">✕</button>
                    `;

                    // Bind zoom click
                    card.querySelector('.aio-card-thumb').addEventListener('click', function () {
                        getInspector().open(item, self);
                    });
                    card.querySelector('.aio-open-inspector-btn').addEventListener('click', function () {
                        getInspector().open(item, self);
                    });

                    // Bind quality slider on card
                    const slider = card.querySelector('.aio-quality-slider');
                    const qualityVal = card.querySelector('.aio-quality-val');
                    let debounceTimer = null;

                    slider.addEventListener('input', function (e) {
                        const newQVal = parseInt(e.target.value, 10);
                        qualityVal.textContent = toPersianDigits(newQVal) + '٪';
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(async function () {
                            await self.recompressItem(item, newQVal / 100);
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

    window.ImageOptimizerWidget = ImageOptimizerWidget;
    window.getInspector = getInspector;
})();
