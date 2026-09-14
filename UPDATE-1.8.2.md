# AB-Socks 1.8.2 — Dynamic Public Content

This update moves editable business/legal content out of public PHP views and into the existing `settings` key/value store.

## Apply

Extract the archive over the current project directory and allow files to be overwritten.

The included `config/private_content.php` is intentionally gitignored. It contains the initial private business/content values for this installation. Keep it on the server/local project, but never commit it.

Do not replace `config/config.php`; keep the existing live configuration and credentials.

After uploading:

1. Log in to the admin panel.
2. Open **تنظیمات فروشگاه**.
3. Review **اطلاعات کسب‌وکار و تماس**.
4. Review/edit **محتوای صفحات عمومی**.
5. Save each section once. From that point, the database is the source of truth.
6. Verify `/about`, `/contact`, `/terms`, `/privacy` and the site footer.

No database migration is required for this update.
