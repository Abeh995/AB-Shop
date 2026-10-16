/**
 * Admin Product Tags Workstation JavaScript (assets/js/admin-tags.js)
 * Clean, declarative interaction handler for tag creation, editing, and URL synchronization.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const tagModal = document.getElementById('tagModal');
        const tagForm = document.getElementById('tagForm');
        const tagFormId = document.getElementById('tagFormId');
        const tagNameInput = document.getElementById('tagNameInput');
        const tagSlugInput = document.getElementById('tagSlugInput');
        const tagModalTitle = document.getElementById('tagModalTitle');
        const tagSubmitBtn = document.getElementById('tagSubmitBtn');

        // Handle delegated clicks
        document.addEventListener('click', function (e) {
            // Edit tag button
            const editBtn = e.target.closest('[data-tag-edit]');
            if (editBtn) {
                e.preventDefault();
                const id = editBtn.getAttribute('data-id') || '0';
                const name = editBtn.getAttribute('data-name') || '';
                const slug = editBtn.getAttribute('data-slug') || '';

                if (tagFormId) tagFormId.value = id;
                if (tagNameInput) tagNameInput.value = name;
                if (tagSlugInput) tagSlugInput.value = slug;
                if (tagModalTitle) tagModalTitle.textContent = 'ویرایش برچسب';
                if (tagSubmitBtn) tagSubmitBtn.textContent = 'ذخیره تغییرات';

                if (window.AB && window.AB.modal) {
                    window.AB.modal.open('tagModal', editBtn);
                } else if (tagModal && typeof tagModal.showModal === 'function') {
                    tagModal.showModal();
                }
                if (tagNameInput) tagNameInput.focus();
                return;
            }

            // Create new tag button (reset modal to empty state)
            const openBtn = e.target.closest('[data-ab-modal-open="tagModal"]');
            if (openBtn && !openBtn.hasAttribute('data-tag-edit')) {
                if (tagForm) tagForm.reset();
                if (tagFormId) tagFormId.value = '0';
                if (tagNameInput) tagNameInput.value = '';
                if (tagSlugInput) tagSlugInput.value = '';
                if (tagModalTitle) tagModalTitle.textContent = 'تعریف برچسب جدید';
                if (tagSubmitBtn) tagSubmitBtn.textContent = 'ایجاد برچسب';
            }
        });

        // Cleanup URL query params when modal closes if ?edit= was present
        if (tagModal) {
            tagModal.addEventListener('close', function () {
                if (window.location.search.includes('edit=')) {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('edit');
                    window.history.replaceState({}, '', url.pathname + (url.search ? url.search : ''));
                }
            });
        }
    });
})();
