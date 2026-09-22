<?php
/**
 * General store settings (the `settings` table) — a simple key/value store
 * for settings that don't need a dedicated column in another table.
 */

function getSetting(string $key, $default = null)
{
    static $cache = [];
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }
    $stmt = db()->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $value = $stmt->fetchColumn();
    $cache[$key] = ($value === false) ? $default : $value;
    return $cache[$key];
}

function setSetting(string $key, string $value): void
{
    $stmt = db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $stmt->execute([$key, $value]);
}

/**
 * Read editable storefront content from the database settings table.
 * Public business/legal content is data, not application secrets.
 */
function getSiteContent(string $key, string $default = ''): string
{
    return (string) getSetting($key, $default);
}

/**
 * Render a small, deliberately limited text format used by editable legal pages.
 * Supported blocks: ## headings, - list items, and normal paragraphs.
 * All content is escaped before rendering; no arbitrary HTML is accepted.
 */
function renderSiteContent(string $content): string
{
    $lines = preg_split('/\R/u', trim($content));
    $html = '';
    $paragraph = [];
    $list = [];

    $flushParagraph = static function () use (&$html, &$paragraph): void {
        if ($paragraph === []) {
            return;
        }
        $text = trim(implode("\n", $paragraph));
        if ($text !== '') {
            $html .= '<p>' . nl2br(e($text)) . '</p>';
        }
        $paragraph = [];
    };

    $flushList = static function () use (&$html, &$list): void {
        if ($list === []) {
            return;
        }
        $html .= '<ul>';
        foreach ($list as $item) {
            $html .= '<li>' . e($item) . '</li>';
        }
        $html .= '</ul>';
        $list = [];
    };

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            $flushParagraph();
            $flushList();
            continue;
        }

        if (str_starts_with($line, '## ')) {
            $flushParagraph();
            $flushList();
            $html .= '<h2>' . e(trim(substr($line, 3))) . '</h2>';
            continue;
        }

        if (str_starts_with($line, '- ')) {
            $flushParagraph();
            $list[] = trim(substr($line, 2));
            continue;
        }

        $flushList();
        $paragraph[] = $line;
    }

    $flushParagraph();
    $flushList();

    return $html;
}
