/**
 * Card-to-Card Receipt Uploader Module (assets/js/client/card-to-card.js)
 * Client-side WebP compression with on-demand HEIC decoding via ABClient API & Toast.
 */
(function (window, document) {
  'use strict';

  const ABClient = window.ABClient;
  if (!ABClient || !ABClient.behavior) return;

  let heicScriptPromise = null;

  const loadHeicLibrary = function (heicSrc) {
    if (window.heic2any) return Promise.resolve();
    if (heicScriptPromise) return heicScriptPromise;
    heicScriptPromise = new Promise(function (resolve, reject) {
      const script = document.createElement('script');
      script.src = heicSrc || '/assets/js/vendor/heic2any.min.js';
      script.onload = resolve;
      script.onerror = function () {
        heicScriptPromise = null;
        reject(new Error('بارگذاری مبدل تصویر HEIC انجام نشد.'));
      };
      document.head.appendChild(script);
    });
    return heicScriptPromise;
  };

  const compressImageToWebp = async function (file, loadingText, heicSrc) {
    let sourceBlob = file;
    const isHeic = Boolean(file.name.match(/\.(heic|heif)$/i) || (file.type && file.type.includes('heic')));

    if (isHeic) {
      if (loadingText) loadingText.textContent = 'در حال آماده‌سازی تصویر فیش...';
      await loadHeicLibrary(heicSrc);
      const converted = await window.heic2any({ blob: file, toType: 'image/jpeg', quality: 0.90 });
      sourceBlob = Array.isArray(converted) ? converted[0] : converted;
    }

    if (loadingText) loadingText.textContent = 'در حال بهینه‌سازی تصویر رسید...';

    const img = await new Promise(function (resolve, reject) {
      const url = URL.createObjectURL(sourceBlob);
      const image = new Image();
      image.onload = function () {
        URL.revokeObjectURL(url);
        resolve(image);
      };
      image.onerror = function () {
        URL.revokeObjectURL(url);
        reject(new Error('بارگذاری فایل تصویر انجام نشد.'));
      };
      image.src = url;
    });

    const maxDim = 1200;
    const origW = img.naturalWidth;
    const origH = img.naturalHeight;
    let targetW = origW;
    let targetH = origH;

    if (origW > maxDim || origH > maxDim) {
      if (origW >= origH) {
        targetW = maxDim;
        targetH = Math.round((origH * maxDim) / origW);
      } else {
        targetH = maxDim;
        targetW = Math.round((origW * maxDim) / origH);
      }
    }

    const canvas = document.createElement('canvas');
    canvas.width = targetW;
    canvas.height = targetH;
    const ctx = canvas.getContext('2d', { alpha: true });
    ctx.imageSmoothingEnabled = true;
    ctx.imageSmoothingQuality = 'high';
    ctx.drawImage(img, 0, 0, targetW, targetH);

    const exportBlob = function (type, quality) {
      return new Promise(function (resolve) {
        canvas.toBlob(resolve, type, quality);
      });
    };

    let webpBlob = await exportBlob('image/webp', 0.78);
    if (webpBlob && webpBlob.size > 300 * 1024) {
      webpBlob = await exportBlob('image/webp', 0.65);
    }
    if (webpBlob && webpBlob.size > 300 * 1024) {
      webpBlob = await exportBlob('image/webp', 0.50);
    }
    if (!webpBlob || webpBlob.size === 0) {
      webpBlob = await exportBlob('image/jpeg', 0.75);
    }

    return webpBlob || file;
  };

  ABClient.behavior.register('c2c-receipt-uploader', '[data-ab-c2c-uploader], #c2cDropzone', function (dropzone) {
    const fileInput = document.getElementById('c2cFileInput');
    const emptyState = document.getElementById('c2cEmptyState');
    const loadingState = document.getElementById('c2cLoadingState');
    const loadingText = document.getElementById('c2cLoadingText');
    const previewState = document.getElementById('c2cPreviewState');
    const previewThumb = document.getElementById('c2cPreviewThumb');
    const previewFilename = document.getElementById('c2cPreviewFilename');
    const previewMeta = document.getElementById('c2cPreviewMeta');
    const btnRemove = document.getElementById('c2cBtnRemove');
    const submitBtn = document.getElementById('c2cSubmitBtn');
    const statusMsg = document.getElementById('c2cStatusMsg');

    if (!fileInput) return;

    const heicSrc = dropzone.getAttribute('data-ab-heic-src') || '/assets/js/vendor/heic2any.min.js';
    let currentPreviewUrl = null;

    const showStatus = function (msg, isError) {
      if (statusMsg) {
        statusMsg.textContent = msg || '';
        statusMsg.classList.toggle('c-text-danger', Boolean(msg) && Boolean(isError));
        statusMsg.classList.toggle('c-text-success', Boolean(msg) && !isError);
      }
      if (msg) {
        ABClient.toast.show(msg, isError ? 'error' : 'success');
      }
    };

    const handleReceiptFile = async function (file) {
      if (!file) return;
      if (statusMsg) {
        statusMsg.textContent = '';
        statusMsg.classList.remove('c-text-danger', 'c-text-success');
      }

      if (emptyState) emptyState.hidden = true;
      if (previewState) previewState.hidden = true;
      if (loadingState) loadingState.hidden = false;
      if (submitBtn) submitBtn.disabled = true;

      const timeoutCtrl = new AbortController();
      const timeoutTimer = window.setTimeout(function () {
        timeoutCtrl.abort();
      }, 45000);

      try {
        const compressedBlob = await compressImageToWebp(file, loadingText, heicSrc);
        const uploadFile = new File([compressedBlob], 'receipt.webp', {
          type: compressedBlob.type || 'image/webp'
        });

        if (loadingText) loadingText.textContent = 'در حال ذخیره‌سازی امن فیش روی سرور...';
        const formData = new FormData();
        formData.append('receipt', uploadFile);

        const res = await ABClient.api.post('/ajax/card_to_card_receipt_upload.php', formData, { signal: timeoutCtrl.signal });
        if (loadingState) loadingState.hidden = true;

        if (res && res.ok) {
          if (currentPreviewUrl) URL.revokeObjectURL(currentPreviewUrl);
          currentPreviewUrl = URL.createObjectURL(uploadFile);

          if (previewThumb) previewThumb.src = currentPreviewUrl;
          if (previewFilename) previewFilename.textContent = file.name || 'رسید پرداخت واریزی';
          if (previewMeta) {
            previewMeta.textContent = 'حجم بهینه‌شده: ' + ABClient.fmt.bytes(res.size || uploadFile.size) + ' (WebP)';
          }
          if (previewState) previewState.hidden = false;
          if (submitBtn) submitBtn.disabled = false;
          showStatus('تصویر فیش با موفقیت تایید و آماده ثبت سفارش شد.', false);
        } else {
          if (emptyState) emptyState.hidden = false;
          showStatus((res && (res.error || res.message)) || 'خطا در بارگذاری فیش. لطفاً دوباره تلاش کنید.', true);
        }
      } catch (err) {
        if (loadingState) loadingState.hidden = true;
        if (emptyState) emptyState.hidden = false;
        const msg = err && err.name === 'AbortError'
          ? 'زمان بارگذاری تصویر به پایان رسید؛ لطفاً دوباره تلاش کنید.'
          : 'خطا در پردازش تصویر: ' + (err && err.message ? err.message : String(err));
        showStatus(msg, true);
      } finally {
        window.clearTimeout(timeoutTimer);
      }
    };

    dropzone.addEventListener('click', function (e) {
      if (e.target === btnRemove) return;
      fileInput.click();
    });
    dropzone.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        fileInput.click();
      }
    });

    if (btnRemove) {
      btnRemove.addEventListener('click', function (e) {
        e.stopPropagation();
        fileInput.value = '';
        if (currentPreviewUrl) URL.revokeObjectURL(currentPreviewUrl);
        currentPreviewUrl = null;
        if (previewState) previewState.hidden = true;
        if (emptyState) emptyState.hidden = false;
        if (submitBtn) submitBtn.disabled = true;
        const fd = new FormData();
        fd.append('action', 'discard');
        ABClient.api.post('/ajax/card_to_card_receipt_upload.php', fd).catch(function () {});
        showStatus('تصویر فیش حذف شد. لطفاً فیش معتبر انتخاب کنید.', true);
      });
    }

    fileInput.addEventListener('change', function () {
      if (fileInput.files && fileInput.files.length) {
        handleReceiptFile(fileInput.files[0]);
      }
    });

    ['dragenter', 'dragover'].forEach(function (evt) {
      dropzone.addEventListener(evt, function (e) {
        e.preventDefault();
        dropzone.classList.add('is-dragover');
      });
    });
    ['dragleave', 'drop'].forEach(function (evt) {
      dropzone.addEventListener(evt, function (e) {
        e.preventDefault();
        dropzone.classList.remove('is-dragover');
      });
    });
    dropzone.addEventListener('drop', function (e) {
      if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
        handleReceiptFile(e.dataTransfer.files[0]);
      }
    });
  });
})(window, document);
