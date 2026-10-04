/**
 * Admin Order Detail Script (assets/js/admin-order-detail.js)
 * Handles product gallery lightbox, receipt modal, postal label copy, and quick scroll.
 */
(function() {
    'use strict';

    // Product Gallery Lightbox Engine
    let currentGallery = {
        images: [],
        index: 0,
        title: '',
        variant: ''
    };

    function toPersianNum(n) {
        if (window.AB && AB.fmt && typeof AB.fmt.faDigits === 'function') {
            return AB.fmt.faDigits(n);
        }
        return String(n).replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
    }

    function openProductGallery(el) {
        let imagesRaw = el.getAttribute('data-images') || '[]';
        let images = [];
        try {
            images = JSON.parse(imagesRaw);
        } catch(e) {
            images = [];
        }
        if (!images || images.length === 0) {
            const fallbackImg = el.querySelector('img')?.src;
            if (fallbackImg) images = [fallbackImg];
        }
        if (images.length === 0) return;

        currentGallery.images = images;
        currentGallery.index = 0;
        currentGallery.title = el.getAttribute('data-title') || 'تصویر محصول';
        currentGallery.variant = el.getAttribute('data-variant') || '';

        updateGalleryModal();
        const modal = document.getElementById('productGalleryModal');
        if (modal) modal.classList.add('open');
    }

    function updateGalleryModal() {
        const modal = document.getElementById('productGalleryModal');
        if (!modal) return;

        const titleEl = document.getElementById('galleryModalTitle');
        if (titleEl) titleEl.textContent = currentGallery.title;

        const variantEl = document.getElementById('galleryModalVariant');
        if (variantEl) {
            if (currentGallery.variant) {
                variantEl.textContent = currentGallery.variant;
                variantEl.style.display = 'inline-block';
            } else {
                variantEl.style.display = 'none';
            }
        }

        const img = document.getElementById('galleryMainImg');
        const total = currentGallery.images.length;
        const idx = currentGallery.index;

        if (img) {
            img.style.opacity = '0.3';
            img.src = currentGallery.images[idx];
            img.onload = () => { img.style.opacity = '1'; };
            img.onerror = () => { img.style.opacity = '1'; };
        }

        const counterEl = document.getElementById('galleryCounter');
        if (counterEl) {
            counterEl.textContent = `تصویر ${toPersianNum(idx + 1)} از ${toPersianNum(total)}`;
        }

        const prevBtn = document.getElementById('galleryPrevBtn');
        const nextBtn = document.getElementById('galleryNextBtn');
        if (total <= 1) {
            if (prevBtn) prevBtn.classList.add('hidden');
            if (nextBtn) nextBtn.classList.add('hidden');
        } else {
            if (prevBtn) prevBtn.classList.remove('hidden');
            if (nextBtn) nextBtn.classList.remove('hidden');
        }

        const thumbsStrip = document.getElementById('galleryThumbsStrip');
        if (thumbsStrip) {
            thumbsStrip.innerHTML = '';
            if (total > 1) {
                currentGallery.images.forEach((src, i) => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'product-gallery-thumb-btn' + (i === idx ? ' active' : '');
                    btn.title = `تصویر ${toPersianNum(i + 1)}`;
                    btn.onclick = () => {
                        currentGallery.index = i;
                        updateGalleryModal();
                    };
                    const thumbImg = document.createElement('img');
                    thumbImg.src = src;
                    thumbImg.alt = '';
                    btn.appendChild(thumbImg);
                    thumbsStrip.appendChild(btn);
                });
                thumbsStrip.style.display = 'flex';
            } else {
                thumbsStrip.style.display = 'none';
            }
        }
    }

    function galleryNav(direction) {
        const total = currentGallery.images.length;
        if (total <= 1) return;
        currentGallery.index = (currentGallery.index + direction + total) % total;
        updateGalleryModal();
    }

    function closeProductGallery(e) {
        if (e && e.target && e.target !== e.currentTarget) return;
        const modal = document.getElementById('productGalleryModal');
        if (modal) modal.classList.remove('open');
    }

    // Touch swipe support for gallery on mobile
    let touchStartX = 0;
    let touchEndX = 0;
    const galleryModalEl = document.getElementById('productGalleryModal');
    if (galleryModalEl) {
        galleryModalEl.addEventListener('touchstart', e => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });
        galleryModalEl.addEventListener('touchend', e => {
            touchEndX = e.changedTouches[0].screenX;
            const diff = touchEndX - touchStartX;
            if (Math.abs(diff) > 40) {
                // In RTL, swipe left goes to next (+1), swipe right goes to prev (-1)
                if (diff > 0) {
                    galleryNav(-1);
                } else {
                    galleryNav(1);
                }
            }
        }, { passive: true });
    }

    function openReceiptModal() {
        const modal = document.getElementById('receiptModal');
        if (modal) modal.classList.add('open');
    }

    function closeReceiptModal() {
        const modal = document.getElementById('receiptModal');
        if (modal) modal.classList.remove('open');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeReceiptModal();
            closeProductGallery();
        }
        const galModal = document.getElementById('productGalleryModal');
        if (galModal && galModal.classList.contains('open')) {
            if (e.key === 'ArrowRight') galleryNav(-1);
            if (e.key === 'ArrowLeft') galleryNav(1);
        }
    });

    function copyPostalLabel() {
        const name = document.getElementById('custName')?.textContent?.trim() || '';
        const phone = document.getElementById('custPhone')?.textContent?.trim() || '';
        const city = document.getElementById('custCity')?.textContent?.trim() || '';
        const address = document.getElementById('custAddress')?.textContent?.trim() || '';
        const postal = document.getElementById('custPostal')?.textContent?.trim() || '';

        const labelText = `گیرنده: ${name}\nهمراه: ${phone}\nنشانی: ${city}، ${address}\nکد پستی: ${postal}`;

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(labelText).then(() => {
                if (window.AB && AB.toast) {
                    AB.toast('اطلاعات برچسب پستی کپی شد', 'success');
                } else if (typeof window.showToast === 'function') {
                    window.showToast('اطلاعات برچسب پستی کپی شد');
                }
            }).catch(() => {
                fallbackCopy(labelText);
            });
        } else {
            fallbackCopy(labelText);
        }
    }

    function fallbackCopy(text) {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try {
            document.execCommand('copy');
            if (window.AB && AB.toast) {
                AB.toast('اطلاعات برچسب پستی کپی شد', 'success');
            } else if (typeof window.showToast === 'function') {
                window.showToast('اطلاعات برچسب پستی کپی شد');
            }
        } catch (err) {
            alert('امکان کپی خودکار فراهم نشد. لطفاً متن را دستی کپی کنید.');
        }
        document.body.removeChild(ta);
    }

    function scrollToStatusCard() {
        const card = document.getElementById('orderStatusCard');
        if (card) {
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            card.classList.remove('highlight-pulse');
            // Trigger reflow to restart CSS animation
            void card.offsetWidth;
            card.classList.add('highlight-pulse');
            const statusSelect = card.querySelector('select[name="status"]');
            if (statusSelect) {
                setTimeout(() => {
                    statusSelect.focus();
                }, 500);
            }
        }
    }

    // Expose handlers to window for inline onclick attributes
    window.openProductGallery = openProductGallery;
    window.updateGalleryModal = updateGalleryModal;
    window.galleryNav = galleryNav;
    window.closeProductGallery = closeProductGallery;
    window.openReceiptModal = openReceiptModal;
    window.closeReceiptModal = closeReceiptModal;
    window.copyPostalLabel = copyPostalLabel;
    window.fallbackCopy = fallbackCopy;
    window.scrollToStatusCard = scrollToStatusCard;
})();
