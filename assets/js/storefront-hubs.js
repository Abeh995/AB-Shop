/**
 * Storefront Hubs & Modular Landing Scripts
 * Handles the Liquid Glass Dock droplet physics (Squash & Stretch),
 * instant catalog category filtering, and FAQ accordion toggling.
 */
document.addEventListener('DOMContentLoaded', () => {
  // -------------------------------------------------------------------------
  // 1. Liquid Glass Dock Droplet Animation (Squash & Stretch)
  // -------------------------------------------------------------------------
  const dock = document.getElementById('liquidDock');
  const drop = document.getElementById('liquidDrop');
  const items = document.querySelectorAll('.dock-item');

  if (dock && drop && items.length > 0) {
    let currentIndex = 0;

    const updateDropPosition = (targetItem, animateElastic = true) => {
      const dockRect = dock.getBoundingClientRect();
      const targetRect = targetItem.getBoundingClientRect();

      // Compute horizontal offset relative to the dock container
      const offsetLeft = targetRect.left - dockRect.left;
      const targetWidth = targetRect.width;
      const targetIndex = parseInt(targetItem.getAttribute('data-index') || '0', 10);
      const distance = targetIndex - currentIndex;

      if (animateElastic && distance !== 0) {
        // Fluid squash and stretch physics
        const stretchFactor = Math.min(1 + Math.abs(distance) * 0.22, 1.6);
        const squeezeY = 1 / Math.sqrt(stretchFactor);

        drop.style.transform = `translate3d(${offsetLeft}px, 0, 0) scaleX(${stretchFactor}) scaleY(${squeezeY})`;

        window.setTimeout(() => {
          drop.style.transform = `translate3d(${offsetLeft}px, 0, 0) scale(1, 1)`;
        }, 160);
      } else {
        drop.style.transform = `translate3d(${offsetLeft}px, 0, 0) scale(1, 1)`;
      }

      drop.style.width = `${targetWidth}px`;
      currentIndex = targetIndex;
    };

    // Position drop over the active hub initially
    const activeItem = dock.querySelector('.dock-item.active') || items[0];
    if (activeItem) {
      // Short delay ensures layout metrics are resolved on mobile browsers
      window.setTimeout(() => updateDropPosition(activeItem, false), 50);
    }

    // Keep positioned on window resize
    window.addEventListener('resize', () => {
      const curActive = dock.querySelector('.dock-item.active') || items[0];
      if (curActive) updateDropPosition(curActive, false);
    });
  }

  // -------------------------------------------------------------------------
  // 2. Instant Catalog Category Filter
  // -------------------------------------------------------------------------
  const filterBtns = document.querySelectorAll('.catalog-tab-btn');
  const productCards = document.querySelectorAll('.catalog-product-item');

  if (filterBtns.length > 0 && productCards.length > 0) {
    filterBtns.forEach((btn) => {
      btn.addEventListener('click', () => {
        filterBtns.forEach((b) => b.classList.remove('active'));
        btn.classList.add('active');

        const selectedCat = btn.getAttribute('data-cat-slug') || 'all';

        productCards.forEach((card) => {
          const cardCat = card.getAttribute('data-cat-slug') || '';
          if (selectedCat === 'all' || cardCat === selectedCat) {
            card.style.display = '';
          } else {
            card.style.display = 'none';
          }
        });
      });
    });
  }

  // -------------------------------------------------------------------------
  // 3. FAQ Accordion Toggling
  // -------------------------------------------------------------------------
  const faqItems = document.querySelectorAll('.faq-item');
  if (faqItems.length > 0) {
    faqItems.forEach((item) => {
      const qBtn = item.querySelector('.faq-question');
      if (!qBtn) return;

      qBtn.addEventListener('click', () => {
        const isOpen = item.classList.contains('is-open');

        // Close other open questions
        faqItems.forEach((other) => {
          if (other !== item) other.classList.remove('is-open');
        });

        // Toggle clicked question
        if (isOpen) {
          item.classList.remove('is-open');
        } else {
          item.classList.add('is-open');
        }
      });
    });
  }
});

