-- Migration 019 — v1.20.0: SMS Events Catalog & Dynamic Variable Data-Binding Pre-seeding
SET NAMES utf8mb4;

-- Ensure OTP pattern has source_token defined in variables_config
UPDATE sms_patterns 
SET variables_config = '[{"name":"code","type":"numeric","max_len":6,"label":"کد تایید","source_token":"code"},{"name":"code2","type":"numeric","max_len":6,"label":"کد تکرار","source_token":"code"}]'
WHERE event_key = 'otp' AND (variables_config NOT LIKE '%source_token%' OR variables_config IS NULL);

-- 1. order_created (Customer)
INSERT INTO sms_patterns (pattern_code, title, event_key, pattern_text, description, variables_count, variables_config, is_active)
SELECT 'unset', 'ثبت سفارش جدید برای مشتری', 'order_created', 'سفارش %order_code% برای %name% به مبلغ %amount% تومان ثبت شد.\nبا تشکر از شما', 'اطلاع‌رسانی ثبت موفق سفارش به مشتری', 3, '[{"name":"order_code","type":"alphanumeric","max_len":30,"label":"کد پیگیری سفارش","source_token":"order_code"},{"name":"name","type":"string","max_len":60,"label":"نام مشتری","source_token":"customer_name"},{"name":"amount","type":"numeric","max_len":20,"label":"مبلغ سفارش (تومان)","source_token":"total_price"}]', 0
WHERE NOT EXISTS (SELECT 1 FROM sms_patterns WHERE event_key = 'order_created');

-- 2. order_paid (Customer)
INSERT INTO sms_patterns (pattern_code, title, event_key, pattern_text, description, variables_count, variables_config, is_active)
SELECT 'unset', 'تایید پرداخت سفارش', 'order_paid', 'پرداخت سفارش %order_code% به مبلغ %amount% تومان با شماره پیگیری %ref_id% تایید شد.', 'اطلاع تایید موفقیت‌آمیز پرداخت به مشتری', 4, '[{"name":"order_code","type":"alphanumeric","max_len":30,"label":"کد پیگیری سفارش","source_token":"order_code"},{"name":"name","type":"string","max_len":60,"label":"نام مشتری","source_token":"customer_name"},{"name":"amount","type":"numeric","max_len":20,"label":"مبلغ پرداخت شده","source_token":"total_price"},{"name":"ref_id","type":"alphanumeric","max_len":40,"label":"شماره پیگیری تراکنش","source_token":"ref_id"}]', 0
WHERE NOT EXISTS (SELECT 1 FROM sms_patterns WHERE event_key = 'order_paid');

-- 3. order_shipped (Customer)
INSERT INTO sms_patterns (pattern_code, title, event_key, pattern_text, description, variables_count, variables_config, is_active)
SELECT 'unset', 'ارسال و تحویل سفارش به پست/پیک', 'order_shipped', 'سفارش %order_code% تحویل پست شد.\nکد رهگیری پستی شما:\n%tracking_code%', 'ارسال کد رهگیری پستی و اطلاع تحویل به پست', 3, '[{"name":"order_code","type":"alphanumeric","max_len":30,"label":"کد پیگیری سفارش","source_token":"order_code"},{"name":"name","type":"string","max_len":60,"label":"نام مشتری","source_token":"customer_name"},{"name":"tracking_code","type":"alphanumeric","max_len":40,"label":"کد رهگیری پستی","source_token":"tracking_code"}]', 0
WHERE NOT EXISTS (SELECT 1 FROM sms_patterns WHERE event_key = 'order_shipped');

-- 4. order_delivered (Customer)
INSERT INTO sms_patterns (pattern_code, title, event_key, pattern_text, description, variables_count, variables_config, is_active)
SELECT 'unset', 'تحویل سفارش به مشتری', 'order_delivered', 'سفارش %order_code% به شما تحویل داده شد. امیدواریم از خرید خود رضایت داشته باشید.', 'اطلاع تحویل نهایی سفارش به مشتری', 2, '[{"name":"order_code","type":"alphanumeric","max_len":30,"label":"کد پیگیری سفارش","source_token":"order_code"},{"name":"name","type":"string","max_len":60,"label":"نام مشتری","source_token":"customer_name"}]', 0
WHERE NOT EXISTS (SELECT 1 FROM sms_patterns WHERE event_key = 'order_delivered');

-- 5. order_cancelled (Customer)
INSERT INTO sms_patterns (pattern_code, title, event_key, pattern_text, description, variables_count, variables_config, is_active)
SELECT 'unset', 'لغو سفارش', 'order_cancelled', 'سفارش %order_code% لغو شد. در صورت کسر وجه، مبلغ ظرف ۷۲ ساعت عودت می‌گردد.', 'اطلاع لغو سفارش به مشتری', 2, '[{"name":"order_code","type":"alphanumeric","max_len":30,"label":"کد پیگیری سفارش","source_token":"order_code"},{"name":"name","type":"string","max_len":60,"label":"نام مشتری","source_token":"customer_name"}]', 0
WHERE NOT EXISTS (SELECT 1 FROM sms_patterns WHERE event_key = 'order_cancelled');

-- 6. c2c_instructions (Customer)
INSERT INTO sms_patterns (pattern_code, title, event_key, pattern_text, description, variables_count, variables_config, is_active)
SELECT 'unset', 'دستورالعمل واریز کارت‌به‌کارت برای مشتری', 'c2c_instructions', 'سفارش %order_code% ثبت شد.\nمبلغ: %amount% تومان\nشماره کارت:\n%card_number%\nبه نام %holder%', 'ارسال شماره کارت و مشخصات واریز به خریدار', 4, '[{"name":"order_code","type":"alphanumeric","max_len":30,"label":"کد پیگیری سفارش","source_token":"order_code"},{"name":"amount","type":"numeric","max_len":20,"label":"مبلغ واریزی","source_token":"total_price"},{"name":"card_number","type":"numeric","max_len":20,"label":"شماره کارت فروشگاه","source_token":"card_number"},{"name":"holder","type":"string","max_len":60,"label":"نام صاحب حساب","source_token":"card_holder"}]', 0
WHERE NOT EXISTS (SELECT 1 FROM sms_patterns WHERE event_key = 'c2c_instructions');

-- 7. card_to_card_approved (Customer)
INSERT INTO sms_patterns (pattern_code, title, event_key, pattern_text, description, variables_count, variables_config, is_active)
SELECT 'unset', 'تایید واریز کارت‌به‌کارت', 'card_to_card_approved', 'فیش واریزی سفارش %order_code% به مبلغ %amount% تومان تایید شد و سفارش در صف پردازش قرار گرفت.', 'اطلاع تایید فیش کارت‌به‌کارت توسط مدیریت', 3, '[{"name":"order_code","type":"alphanumeric","max_len":30,"label":"کد پیگیری سفارش","source_token":"order_code"},{"name":"name","type":"string","max_len":60,"label":"نام مشتری","source_token":"customer_name"},{"name":"amount","type":"numeric","max_len":20,"label":"مبلغ تایید شده","source_token":"total_price"}]', 0
WHERE NOT EXISTS (SELECT 1 FROM sms_patterns WHERE event_key = 'card_to_card_approved');

-- 8. card_to_card_rejected (Customer)
INSERT INTO sms_patterns (pattern_code, title, event_key, pattern_text, description, variables_count, variables_config, is_active)
SELECT 'unset', 'رد فیش کارت‌به‌کارت', 'card_to_card_rejected', 'فیش سفارش %order_code% تایید نشد.\nعلت رد: %reason%\nجهت پیگیری با پشتیبانی تماس بگیرید.', 'اطلاع رد فیش بانکی با ذکر دلیل به خریدار', 3, '[{"name":"order_code","type":"alphanumeric","max_len":30,"label":"کد پیگیری سفارش","source_token":"order_code"},{"name":"name","type":"string","max_len":60,"label":"نام مشتری","source_token":"customer_name"},{"name":"reason","type":"string","max_len":120,"label":"علت رد فیش","source_token":"rejection_reason"}]', 0
WHERE NOT EXISTS (SELECT 1 FROM sms_patterns WHERE event_key = 'card_to_card_rejected');

-- 9. admin_new_order (Admin)
INSERT INTO sms_patterns (pattern_code, title, event_key, pattern_text, description, variables_count, variables_config, is_active)
SELECT 'unset', 'اطلاع سفارش جدید به مدیر فروشگاه', 'admin_new_order', 'مدیر گرامی، سفارش جدید با کد %order_code% به مبلغ %amount% تومان توسط %customer% (%payment%) ثبت شد.', 'ارسال پیامک هشدار به شماره همراه مدیر در زمان ثبت هر سفارش', 4, '[{"name":"order_code","type":"alphanumeric","max_len":30,"label":"کد سفارش جدید","source_token":"order_code"},{"name":"amount","type":"numeric","max_len":20,"label":"مبلغ سفارش","source_token":"total_price"},{"name":"customer","type":"string","max_len":60,"label":"نام مشتری","source_token":"customer_name"},{"name":"payment","type":"string","max_len":30,"label":"روش پرداخت","source_token":"payment_method"}]', 0
WHERE NOT EXISTS (SELECT 1 FROM sms_patterns WHERE event_key = 'admin_new_order');

-- 10. admin_c2c_receipt (Admin)
INSERT INTO sms_patterns (pattern_code, title, event_key, pattern_text, description, variables_count, variables_config, is_active)
SELECT 'unset', 'اطلاع ثبت فیش کارت‌به‌کارت به مدیر', 'admin_c2c_receipt', 'مدیر گرامی، فیش کارت‌به‌کارت جدید برای سفارش %order_code% توسط %customer% بارگذاری شد.', 'ارسال پیامک هشدار به مدیر هنگام آپلود فیش کارت‌به‌کارت', 3, '[{"name":"order_code","type":"alphanumeric","max_len":30,"label":"کد سفارش","source_token":"order_code"},{"name":"customer","type":"string","max_len":60,"label":"نام مشتری","source_token":"customer_name"},{"name":"amount","type":"numeric","max_len":20,"label":"مبلغ سفارش","source_token":"total_price"}]', 0
WHERE NOT EXISTS (SELECT 1 FROM sms_patterns WHERE event_key = 'admin_c2c_receipt');
