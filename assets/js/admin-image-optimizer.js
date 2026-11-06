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

    // Persian number formatter & byte size delegators (SSoT: assets/js/ab-kit.js)
    const toPersianDigits = function (str) {
        return (window.AB && window.AB.fmt) ? window.AB.fmt.faDigits(str) : String(str ?? '');
    };

    const formatBytes = function (bytes) {
        return (window.AB && window.AB.fmt) ? window.AB.fmt.bytes(bytes) : String(bytes ?? '');
    };

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
                                <span>کیفیت: <strong class="aio-inspector-qval">۸۵٪</strong></span>
                                <button type="button" class="aio-step-btn aio-inspector-step-minus" title="کاهش ۱٪ کیفیت">−</button>
                                <input type="range" class="aio-inspector-slider" min="1" max="100" step="1" value="85">
                                <button type="button" class="aio-step-btn aio-inspector-step-plus" title="افزایش ۱٪ کیفیت">+</button>
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

                            <button type="button" class="aio-inspector-btn aio-inspector-confirm-btn" title="تایید یا لغو تصویر">
                                <span class="aio-inspector-confirm-label">✓ تایید کیفیت تصویر</span>
                            </button>

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
            this.stepMinus = this.modal.querySelector('.aio-inspector-step-minus');
            this.stepPlus = this.modal.querySelector('.aio-inspector-step-plus');
            this.zoomVal = this.modal.querySelector('.aio-zoom-val');
            this.compareBtn = this.modal.querySelector('.aio-inspector-compare-btn');
            this.confirmBtn = this.modal.querySelector('.aio-inspector-confirm-btn');
            this.confirmLabel = this.modal.querySelector('.aio-inspector-confirm-label');
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

            // 1% Step Buttons in Inspector
            if (this.stepMinus) {
                this.stepMinus.addEventListener('click', function () {
                    const currentVal = parseInt(self.slider.value, 10) || 85;
                    const newVal = Math.max(1, currentVal - 1);
                    self.slider.value = newVal;
                    self.slider.dispatchEvent(new Event('input'));
                });
            }
            if (this.stepPlus) {
                this.stepPlus.addEventListener('click', function () {
                    const currentVal = parseInt(self.slider.value, 10) || 85;
                    const newVal = Math.min(100, currentVal + 1);
                    self.slider.value = newVal;
                    self.slider.dispatchEvent(new Event('input'));
                });
            }

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

            // Confirm toggle inside inspector
            if (this.confirmBtn) {
                this.confirmBtn.addEventListener('click', function () {
                    if (!self.activeItem || !self.activeWidget) return;
                    self.activeWidget.toggleConfirmItem(self.activeItem.id);
                    self.refreshItemData();
                });
            }
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
            } else if (item.isOriginal || Math.round(item.quality * 100) >= 100) {
                this.savingsBadge.textContent = 'فایل اصلی دست‌نخورده (۱۰۰٪)';
                this.dimsEl.textContent = toPersianDigits(item.origW) + '×' + toPersianDigits(item.origH) + ' پیکسل';
                this.sizesEl.innerHTML = `حجم اصلی: <strong class="aio-stat-new">${formatBytes(item.origSize)}</strong>`;
                this.slider.parentElement.style.display = 'flex';
                this.compareBtn.style.display = 'none';
                this.slider.value = 100;
                this.qval.textContent = '۱۰۰٪';
                this.modeBadge.textContent = 'در حال نمایش: تصویر اصلی خام';
                this.modeBadge.className = 'aio-inspector-mode-badge aio-mode-original';
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

            if (this.confirmBtn && this.confirmLabel) {
                if (item.confirmed) {
                    this.confirmBtn.className = 'aio-inspector-btn aio-inspector-cancel-btn';
                    this.confirmLabel.textContent = 'لغو تایید ❌';
                } else {
                    this.confirmBtn.className = 'aio-inspector-btn aio-inspector-confirm-btn';
                    this.confirmLabel.textContent = '✓ تایید کیفیت تصویر';
                }
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
            this.defaultQuality = parseFloat(inputEl.dataset.defaultQuality || '0.85');
            this.allowSvg = inputEl.dataset.allowSvg === 'true';
            this.role = inputEl.dataset.optimizeImage || 'image';
            this.isCompact = inputEl.dataset.layout === 'compact';
            this.maxSizeText = inputEl.dataset.maxSize || '۴۰ مگابایت';
            this.formatsText = inputEl.dataset.formats || (this.allowSvg ? 'JPG، PNG، WebP، SVG' : 'JPG، PNG، WebP');

            this.items = [];
            this.isProcessing = false;
            this._isInternalSync = false;

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
            this.wrapper.className = this.isCompact ? 'aio-widget aio-widget-compact' : 'aio-widget';

            this.dropzone = document.createElement('div');
            this.dropzone.className = this.isCompact ? 'aio-dropzone aio-dropzone-compact' : 'aio-dropzone';
            this.dropzone.innerHTML = `
                <div class="aio-dropzone-icon">📷</div>
                <div class="aio-dropzone-content">
                    <div class="aio-dropzone-text">
                        <strong>برای انتخاب ${this.isMultiple ? 'تصاویر' : 'تصویر'} کلیک کنید</strong> یا فایل را اینجا بکشید
                    </div>
                    <div class="aio-dropzone-hint">
                        فرمت‌های مجاز: ${this.formatsText} (حداکثر ${this.maxSizeText})
                    </div>
                </div>
            `;

            this.progressBox = document.createElement('div');
            this.progressBox.className = 'aio-progress-box';
            this.progressBox.style.display = 'none';
            this.progressBox.innerHTML = `
                <div class="aio-spinner"></div>
                <div class="aio-progress-text">در حال بهینه‌سازی تصویر...</div>
            `;

            if (this.isMultiple) {
                this.batchToolbar = document.createElement('div');
                this.batchToolbar.className = 'aio-batch-toolbar';
                this.batchToolbar.style.display = 'none';
            }

            this.previewContainer = document.createElement('div');
            this.previewContainer.className = this.isMultiple ? 'aio-previews-grid' : 'aio-preview-single';

            this.wrapper.appendChild(this.dropzone);
            this.wrapper.appendChild(this.progressBox);
            if (this.batchToolbar) {
                this.wrapper.appendChild(this.batchToolbar);
            }
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

            // Prevent recursive loop: ignore changes triggered programmatically via syncFilesToInput
            this.input.addEventListener('change', function () {
                if (self._isInternalSync) return;
                if (self.input.files && self.input.files.length) {
                    self.handleFiles(Array.from(self.input.files));
                }
            });

            if (this.form) {
                this.form.addEventListener('submit', function (e) {
                    if (self.isProcessing) {
                        e.preventDefault();
                        alert('لطفاً تا اتمام پردازش و فشرده‌سازی تصاویر صبر کنید.');
                        return;
                    }

                    // For gallery with unconfirmed images: prompt admin before form submission
                    if (self.isMultiple && self.items.length > 0) {
                        const confirmed = self.items.filter(function (it) { return it.confirmed; });
                        const unconfirmed = self.items.filter(function (it) { return !it.confirmed; });

                        if (unconfirmed.length > 0) {
                            if (confirmed.length > 0) {
                                const ok = confirm(
                                    'تعداد ' + toPersianDigits(unconfirmed.length) + ' تصویر در گالری تایید نشده‌اند و بارگذاری نخواهند شد.\n\n' +
                                    'آیا مایلید فقط ' + toPersianDigits(confirmed.length) + ' تصویر تایید‌شده ذخیره شوند؟'
                                );
                                if (!ok) {
                                    e.preventDefault();
                                    return;
                                }
                                // User confirmed saving only approved images; do NOT call confirmAll().
                            } else {
                                const ok = confirm(
                                    'هیچ تصویری در گالری تایید نشده است و هیچ تصویری بارگذاری نخواهد شد.\n\n' +
                                    'آیا مایلید بدون افزودن تصویر جدید به گالری ذخیره انجام شود؟'
                                );
                                if (!ok) {
                                    e.preventDefault();
                                    return;
                                }
                            }
                        }
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
            const isConfirmedByDefault = !self.isMultiple; // Single cover image auto-confirms; gallery requires review

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
                    quality: 1,
                    confirmed: isConfirmedByDefault
                });
                return;
            }

            let sourceBlob = file;

            if (isHeic) {
                this.progressBox.querySelector('.aio-progress-text').textContent = 'در حال تبدیل فرمت تصویر...';
                await loadHeicLibrary();
                const conversionResult = await window.heic2any({
                    blob: file,
                    toType: 'image/jpeg',
                    quality: 0.95
                });
                sourceBlob = Array.isArray(conversionResult) ? conversionResult[0] : conversionResult;
            }

            this.progressBox.querySelector('.aio-progress-text').textContent = 'در حال بهینه‌سازی تصویر...';

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

            const quality = Math.max(0.01, Math.min(1.0, self.defaultQuality));
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
                previewUrl: previewUrl,
                confirmed: isConfirmedByDefault
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
                const q = Math.max(0.01, Math.min(1.0, quality));
                canvas.toBlob(function (blob) {
                    if (blob && blob.size > 0) {
                        resolve(blob);
                    } else {
                        canvas.toBlob(function (fallbackBlob) {
                            resolve(fallbackBlob);
                        }, 'image/jpeg', q);
                    }
                }, 'image/webp', q);
            });
        }

        async recompressItem(item, newQuality) {
            const q = Math.max(0.01, Math.min(1.0, newQuality));
            item.quality = q;

            if (q >= 0.999) {
                // 100% Quality: bypass compression completely, use raw original file
                item.isOriginal = true;
                item.compressedFile = item.originalFile;
                item.compressedBlob = item.originalFile;
                item.compressedSize = item.origSize;
                item.targetW = item.origW;
                item.targetH = item.origH;

                if (item.previewUrl) URL.revokeObjectURL(item.previewUrl);
                item.previewUrl = URL.createObjectURL(item.originalFile);
            } else {
                item.isOriginal = false;
                const newBlob = await this.canvasToWebpBlob(item.canvas, q);
                item.compressedBlob = newBlob;
                item.compressedSize = newBlob.size;
                const rawBaseName = item.originalFile.name.replace(/\.[^.]+$/, '').replace(/[^a-zA-Z0-9_\-\.]/g, '_');
                const cleanName = (rawBaseName || 'image') + '.webp';
                item.compressedFile = new File([newBlob], cleanName, { type: newBlob.type, lastModified: Date.now() });

                if (item.previewUrl) URL.revokeObjectURL(item.previewUrl);
                item.previewUrl = URL.createObjectURL(newBlob);
            }

            // Update card DOM
            const card = document.getElementById(item.id);
            if (card) {
                const thumbImg = card.querySelector('.aio-card-thumb img');
                if (thumbImg) thumbImg.src = item.previewUrl;

                const thumbBadge = card.querySelector('.aio-thumb-badge');
                if (thumbBadge) thumbBadge.textContent = item.isOriginal ? 'Original' : 'WebP';

                const filenameEl = card.querySelector('.aio-card-filename');
                if (filenameEl) {
                    filenameEl.textContent = item.compressedFile.name;
                    filenameEl.title = item.compressedFile.name;
                }

                const statsRow = card.querySelector('.aio-stats-row');
                if (statsRow) {
                    statsRow.innerHTML = `
                        <div class="aio-stat">
                            <span class="aio-stat-label">حجم:</span>
                            <span class="aio-stat-values">
                                ${item.isOriginal ? `<strong class="aio-stat-new">${formatBytes(item.origSize)}</strong>` : `
                                    <span class="aio-stat-old">${formatBytes(item.origSize)}</span>
                                    <span class="aio-stat-arrow">←</span>
                                    <strong class="aio-stat-new">${formatBytes(item.compressedSize)}</strong>
                                `}
                            </span>
                        </div>
                        <div class="aio-stat">
                            <span class="aio-stat-label">ابعاد:</span>
                            <span class="aio-stat-values">
                                ${item.isOriginal ? `<strong class="aio-stat-new aio-dims-new">${toPersianDigits(item.origW)}×${toPersianDigits(item.origH)}</strong>` : `
                                    <span class="aio-stat-old">${toPersianDigits(item.origW)}×${toPersianDigits(item.origH)}</span>
                                    <span class="aio-stat-arrow">←</span>
                                    <strong class="aio-stat-new aio-dims-new">${toPersianDigits(item.targetW)}×${toPersianDigits(item.targetH)}</strong>
                                `}
                            </span>
                        </div>
                    `;
                }

                const savingsEl = card.querySelector('.aio-badge-primary');
                if (savingsEl) {
                    if (item.isOriginal) {
                        savingsEl.textContent = 'فایل اصلی (۱۰۰٪)';
                    } else {
                        const newSavings = Math.max(0, Math.round(((item.origSize - item.compressedSize) / item.origSize) * 100));
                        savingsEl.textContent = toPersianDigits(newSavings) + '٪ کاهش حجم';
                    }
                }

                const slider = card.querySelector('.aio-quality-slider');
                if (slider) slider.value = Math.round(q * 100);
                const qval = card.querySelector('.aio-quality-val');
                if (qval) qval.textContent = toPersianDigits(Math.round(q * 100)) + '٪';
            }

            this.syncFilesToInput();
        }

        toggleConfirmItem(itemId) {
            const item = this.items.find(function (it) { return it.id === itemId; });
            if (!item) return;
            item.confirmed = !item.confirmed;
            this.syncFilesToInput();
            this.renderPreviews();
            if (globalInspector && globalInspector.isOpen() && globalInspector.activeItem && globalInspector.activeItem.id === itemId) {
                globalInspector.refreshItemData();
            }
        }

        confirmAll() {
            this.items.forEach(function (item) {
                item.confirmed = true;
            });
            this.syncFilesToInput();
            this.renderPreviews();
            if (globalInspector && globalInspector.isOpen() && globalInspector.activeWidget === this) {
                globalInspector.refreshItemData();
            }
        }

        removeAll() {
            if (!this.items.length) return;
            if (confirm('آیا مطمئن هستید که می‌خواهید تمام تصاویر انتخاب‌شده این بخش را حذف کنید؟')) {
                this.clearItems();
                this.syncFilesToInput();
                this.renderPreviews();
                if (globalInspector && globalInspector.isOpen() && globalInspector.activeWidget === this) {
                    globalInspector.close();
                }
            }
        }

        renderPreviews() {
            const self = this;

            // Render/Update batch toolbar for multiple uploads
            if (this.batchToolbar) {
                if (!this.items.length) {
                    this.batchToolbar.style.display = 'none';
                    this.batchToolbar.innerHTML = '';
                } else {
                    this.batchToolbar.style.display = 'flex';
                    const confirmedCount = this.items.filter(function (it) { return it.confirmed; }).length;
                    const totalCount = this.items.length;
                    const allConfirmed = confirmedCount === totalCount;

                    this.batchToolbar.innerHTML = `
                        <div class="aio-batch-status-text">
                            <strong>تصاویر جدید گالری (${toPersianDigits(totalCount)} تصویر):</strong>
                            <span class="aio-badge ${allConfirmed ? 'aio-badge-success' : 'aio-badge-warning'}">
                                ${allConfirmed ? '✓ همه تصاویر تایید شده‌اند' : (toPersianDigits(confirmedCount) + ' از ' + toPersianDigits(totalCount) + ' تصویر تایید شده')}
                            </span>
                        </div>
                        <div class="aio-batch-actions">
                            <button type="button" class="btn btn-sm ${allConfirmed ? 'btn-outline' : 'btn-primary'} aio-btn-confirm-all">
                                ${allConfirmed ? '✓ همه تایید شده‌اند' : '✓ تایید همه تصاویر'}
                            </button>
                            <button type="button" class="btn btn-sm btn-outline aio-btn-remove-all" style="color: var(--color-danger); border-color: var(--color-danger);">
                                ✕ حذف همه
                            </button>
                        </div>
                    `;

                    this.batchToolbar.querySelector('.aio-btn-confirm-all').addEventListener('click', function () {
                        self.confirmAll();
                    });

                    this.batchToolbar.querySelector('.aio-btn-remove-all').addEventListener('click', function () {
                        self.removeAll();
                    });
                }
            }

            this.previewContainer.innerHTML = '';

            if (!this.items.length) {
                return;
            }

            this.items.forEach(function (item) {
                const card = document.createElement('div');
                card.className = 'aio-card' + (item.confirmed ? ' aio-card-confirmed' : ' aio-card-pending');
                card.id = item.id;

                if (item.isSvg) {
                    card.innerHTML = `
                        <div class="aio-card-thumb" title="پیش‌نمایش لوگوی SVG">
                            <img src="${item.previewUrl}" alt="SVG Logo" style="object-fit:contain;">
                        </div>
                        <div class="aio-card-info">
                            <div class="aio-card-header">
                                <span class="aio-card-filename" title="${item.originalFile.name}">${item.originalFile.name}</span>
                                <button type="button" class="aio-btn-remove" title="حذف این فایل">✕</button>
                            </div>
                            <div class="aio-card-badges">
                                <span class="aio-badge aio-badge-success">فایل وکتور SVG • کیفیت بی‌نهایت • ${formatBytes(item.origSize)}</span>
                                <span class="aio-badge ${item.confirmed ? 'aio-badge-success' : 'aio-badge-warning'}">
                                    ${item.confirmed ? '✓ تایید شد • آماده ارسال' : 'در انتظار تایید'}
                                </span>
                            </div>
                            <div class="aio-card-actions">
                                ${item.confirmed ? `
                                    <button type="button" class="aio-btn-cancel" title="لغو تایید این تصویر">
                                        لغو تایید ❌
                                    </button>
                                ` : `
                                    <button type="button" class="aio-btn-confirm" title="تایید و افزودن به لیست بارگذاری">
                                        ✓ تایید این تصویر
                                    </button>
                                `}
                            </div>
                        </div>
                    `;
                } else {
                    const isOrig = item.isOriginal || Math.round(item.quality * 100) >= 100;
                    const savingsPercent = Math.max(0, Math.round(((item.origSize - item.compressedSize) / item.origSize) * 100));
                    const qualityPercent = Math.round(item.quality * 100);

                    card.innerHTML = `
                        <div class="aio-card-thumb" title="برای مشاهده بزرگ‌نمایی و بررسی کیفیت کلیک کنید">
                            <img src="${item.previewUrl}" alt="Preview">
                            <span class="aio-thumb-badge">${isOrig ? 'Original' : 'WebP'}</span>
                            <div class="aio-thumb-zoom-overlay">🔍 تمام‌صفحه</div>
                        </div>
                        <div class="aio-card-info">
                            <div class="aio-card-header">
                                <span class="aio-card-filename" title="${item.compressedFile.name}">${item.compressedFile.name}</span>
                                <button type="button" class="aio-btn-remove" title="حذف این تصویر">✕</button>
                            </div>
                            <div class="aio-card-badges">
                                <span class="aio-badge aio-badge-primary">
                                    ${isOrig ? 'فایل اصلی (۱۰۰٪)' : toPersianDigits(savingsPercent) + '٪ کاهش حجم'}
                                </span>
                                <span class="aio-badge ${item.confirmed ? 'aio-badge-success' : 'aio-badge-warning'}">
                                    ${item.confirmed ? '✓ تایید شد • آماده ارسال' : 'در انتظار تایید'}
                                </span>
                            </div>
                            <div class="aio-stats-row">
                                <div class="aio-stat">
                                    <span class="aio-stat-label">حجم:</span>
                                    <span class="aio-stat-values">
                                        ${isOrig ? `<strong class="aio-stat-new">${formatBytes(item.origSize)}</strong>` : `
                                            <span class="aio-stat-old">${formatBytes(item.origSize)}</span>
                                            <span class="aio-stat-arrow">←</span>
                                            <strong class="aio-stat-new">${formatBytes(item.compressedSize)}</strong>
                                        `}
                                    </span>
                                </div>
                                <div class="aio-stat">
                                    <span class="aio-stat-label">ابعاد:</span>
                                    <span class="aio-stat-values">
                                        ${isOrig ? `<strong class="aio-stat-new aio-dims-new">${toPersianDigits(item.origW)}×${toPersianDigits(item.origH)}</strong>` : `
                                            <span class="aio-stat-old">${toPersianDigits(item.origW)}×${toPersianDigits(item.origH)}</span>
                                            <span class="aio-stat-arrow">←</span>
                                            <strong class="aio-stat-new aio-dims-new">${toPersianDigits(item.targetW)}×${toPersianDigits(item.targetH)}</strong>
                                        `}
                                    </span>
                                </div>
                            </div>
                            <div class="aio-slider-wrap">
                                <div class="aio-slider-header">
                                    <span>کیفیت تصویر: <strong class="aio-quality-val">${toPersianDigits(qualityPercent)}٪</strong></span>
                                    <button type="button" class="aio-btn-link aio-open-inspector-btn">🔍 باز کردن در اندازه بزرگ</button>
                                </div>
                                <div class="aio-slider-control-row">
                                    <button type="button" class="aio-step-btn aio-step-minus" title="کاهش ۱٪ کیفیت">−</button>
                                    <input type="range" class="aio-quality-slider" min="1" max="100" step="1" value="${qualityPercent}">
                                    <button type="button" class="aio-step-btn aio-step-plus" title="افزایش ۱٪ کیفیت">+</button>
                                </div>
                            </div>
                            <div class="aio-card-actions">
                                ${item.confirmed ? `
                                    <button type="button" class="aio-btn-cancel" title="لغو تایید این تصویر">
                                        لغو تایید ❌
                                    </button>
                                ` : `
                                    <button type="button" class="aio-btn-confirm" title="تایید و افزودن به لیست بارگذاری">
                                        ✓ تایید این تصویر
                                    </button>
                                `}
                            </div>
                        </div>
                    `;

                    // Bind zoom click
                    card.querySelector('.aio-card-thumb').addEventListener('click', function () {
                        getInspector().open(item, self);
                    });
                    card.querySelector('.aio-open-inspector-btn').addEventListener('click', function () {
                        getInspector().open(item, self);
                    });

                    // Bind quality slider and +/- step buttons on card
                    const slider = card.querySelector('.aio-quality-slider');
                    const qualityVal = card.querySelector('.aio-quality-val');
                    const stepMinus = card.querySelector('.aio-step-minus');
                    const stepPlus = card.querySelector('.aio-step-plus');
                    let debounceTimer = null;

                    slider.addEventListener('input', function (e) {
                        const newQVal = parseInt(e.target.value, 10);
                        qualityVal.textContent = toPersianDigits(newQVal) + '٪';
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(async function () {
                            await self.recompressItem(item, newQVal / 100);
                        }, 120);
                    });

                    if (stepMinus) {
                        stepMinus.addEventListener('click', function () {
                            const currentVal = parseInt(slider.value, 10) || 85;
                            const newVal = Math.max(1, currentVal - 1);
                            slider.value = newVal;
                            slider.dispatchEvent(new Event('input'));
                        });
                    }

                    if (stepPlus) {
                        stepPlus.addEventListener('click', function () {
                            const currentVal = parseInt(slider.value, 10) || 85;
                            const newVal = Math.min(100, currentVal + 1);
                            slider.value = newVal;
                            slider.dispatchEvent(new Event('input'));
                        });
                    }
                }

                // Bind confirm/cancel button
                const actionBtn = card.querySelector('.aio-btn-confirm, .aio-btn-cancel');
                if (actionBtn) {
                    actionBtn.addEventListener('click', function () {
                        self.toggleConfirmItem(item.id);
                    });
                }

                // Bind remove button
                const removeBtn = card.querySelector('.aio-btn-remove');
                if (removeBtn) {
                    removeBtn.addEventListener('click', function () {
                        self.removeItem(item.id);
                    });
                }

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
                if (globalInspector && globalInspector.isOpen() && globalInspector.activeItem && globalInspector.activeItem.id === itemId) {
                    globalInspector.close();
                }
            }
        }

        syncFilesToInput() {
            try {
                const dt = new DataTransfer();
                let confirmedCount = 0;
                this.items.forEach(function (item) {
                    if (item.confirmed) {
                        dt.items.add(item.compressedFile);
                        confirmedCount++;
                    }
                });

                // Programmatically update files without triggering recursive native change handler
                this._isInternalSync = true;
                this.input.files = dt.files;
                this._isInternalSync = false;

                // Dispatch custom non-recursive notification for external observers if needed
                this.input.dispatchEvent(new CustomEvent('aio:synced', {
                    bubbles: true,
                    detail: {
                        role: this.role,
                        totalCount: this.items.length,
                        confirmedCount: confirmedCount
                    }
                }));
            } catch (err) {
                console.warn('DataTransfer could not be set directly:', err);
            }
        }

        updateSubmitButtons(enabled) {
            if (!this.form) return;
            const form = this.form;
            form._aioProcessing = (form._aioProcessing || 0) + (enabled ? -1 : 1);
            if (form._aioProcessing < 0) form._aioProcessing = 0;

            const isProcessing = form._aioProcessing > 0;
            const buttons = form.querySelectorAll('button[type="submit"]');
            buttons.forEach(function (btn) {
                btn.disabled = isProcessing;
                if (isProcessing) {
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
