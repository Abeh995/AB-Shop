<?php
/**
 * Storefront FAQ Accordion Component
 *
 * Accessible details/summary FAQ block supporting plain ['q', 'a'] or Schema.org items.
 * Props:
 * - title (string): section heading
 * - items (array): list of ['q' => string, 'a' => string] or Schema mainEntity entries
 * - icon (string|null): optional 'question' SVG indicator
 */
$items = !empty($items) && is_array($items) ? $items : [];
if ($items === []) {
    return;
}

$title = isset($title) && (string) $title !== '' ? (string) $title : 'پرسش‌های متداول';
$icon = isset($icon) ? (string) $icon : '';
?>
<section class="c-faq">
    <h2 class="c-faq__title c-cluster c-cluster--sm">
        <?php if ($icon === 'question'): ?>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
        <?php endif; ?>
        <span><?= e($title) ?></span>
    </h2>
    <div class="c-faq__list">
        <?php foreach ($items as $faq):
            $question = (string) ($faq['q'] ?? ($faq['name'] ?? ''));
            $answer = (string) ($faq['a'] ?? ($faq['acceptedAnswer']['text'] ?? ''));
            if ($question === '' && $answer === '') {
                continue;
            }
        ?>
            <details class="c-faq__item">
                <summary class="c-faq__summary"><?= e($question) ?></summary>
                <div class="c-faq__answer"><?= nl2br(e($answer)) ?></div>
            </details>
        <?php endforeach; ?>
    </div>
</section>
