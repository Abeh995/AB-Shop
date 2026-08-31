// ==========================================================================
// Lightweight vanilla JS — no dependencies, no build step
// ==========================================================================

document.addEventListener('DOMContentLoaded', function () {

    // ---------- Mobile nav menu ----------
    var toggle = document.getElementById('navToggle');
    var nav = document.getElementById('mainNav');
    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            nav.classList.toggle('open');
        });
    }

    // ---------- Header search bar (collapsed behind a magnifier icon) ----------
    var searchToggle = document.getElementById('searchToggle');
    var searchBarPanel = document.getElementById('searchBarPanel');
    if (searchToggle && searchBarPanel) {
        searchToggle.addEventListener('click', function () {
            var isOpen = searchBarPanel.classList.toggle('open');
            searchToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            if (isOpen) {
                var input = document.getElementById('searchBarInput');
                if (input) input.focus();
            }
        });
    }

    // ---------- Variant (size/color) selection on the product page ----------
    document.querySelectorAll('.variant-chip input').forEach(function (input) {
        input.addEventListener('change', function () {
            var group = input.closest('.variant-options');
            group.querySelectorAll('.variant-chip').forEach(function (chip) {
                chip.classList.remove('selected');
            });
            if (input.checked) {
                input.closest('.variant-chip').classList.add('selected');
            }
        });
    });

    // ---------- Quantity stepper on the product page ----------
    document.querySelectorAll('.qty-selector').forEach(function (box) {
        var input = box.querySelector('input');
        var max = parseInt(input.getAttribute('max') || '99', 10);
        box.querySelector('.qty-minus').addEventListener('click', function () {
            var v = Math.max(1, (parseInt(input.value, 10) || 1) - 1);
            input.value = v;
        });
        box.querySelector('.qty-plus').addEventListener('click', function () {
            var v = Math.min(max, (parseInt(input.value, 10) || 1) + 1);
            input.value = v;
        });
    });

    // ---------- Add to cart (AJAX) ----------
    var addForm = document.getElementById('addToCartForm');
    if (addForm) {
        addForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = addForm.querySelector('button[type="submit"]');
            var originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'در حال افزودن...';

            fetch('/ajax/cart_add.php', {
                method: 'POST',
                body: new FormData(addForm),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.ok) {
                    var counter = document.getElementById('cartCount');
                    if (counter) counter.textContent = data.cartCount;
                    btn.textContent = 'به سبد اضافه شد ✓';
                    setTimeout(function () { btn.textContent = originalText; btn.disabled = false; }, 1500);
                } else {
                    alert(data.message || 'خطا در افزودن به سبد خرید');
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            })
            .catch(function () {
                alert('ارتباط با سرور برقرار نشد.');
                btn.disabled = false;
                btn.textContent = originalText;
            });
        });
    }

    // ---------- Quantity change / remove on the cart page ----------
    document.querySelectorAll('.cart-qty-input').forEach(function (input) {
        input.addEventListener('change', function () {
            input.closest('form').submit();
        });
    });

    // ---------- Product image gallery ----------
    document.querySelectorAll('.gallery-thumbs img').forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            var mainImg = document.getElementById('mainProductImage');
            if (mainImg) mainImg.src = thumb.getAttribute('data-full');
            document.querySelectorAll('.gallery-thumbs img').forEach(function (t) { t.classList.remove('active'); });
            thumb.classList.add('active');
        });
    });

});
