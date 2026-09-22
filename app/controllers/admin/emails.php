<?php
$pageTitle = 'ایمیل‌ها';
$accounts = MailboxService::accounts();
$selectedId = (int)($_GET['account'] ?? ($accounts[0]['id'] ?? 0));
$account = $selectedId ? MailboxService::account($selectedId) : null;
$messages = []; $error = null;
if ($account) { try { $messages = MailboxService::messages($account); } catch (Throwable $e) { $error = $e->getMessage(); } }
renderView('admin/emails', compact('pageTitle','accounts','account','messages','error','selectedId'));
