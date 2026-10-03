<?php
/**
 * Admin Users Partial: Interactive Modals
 */
?>

<!-- 1. Create Admin Modal -->
<div class="usr-modal-backdrop" id="modalCreateAdmin" onclick="if(event.target===this) closeAllModals()">
    <div class="usr-modal-card">
        <div class="usr-modal-header">
            <h3 class="usr-modal-title">افزودن مدیر جدید به سیستم</h3>
            <button type="button" class="usr-modal-close" onclick="closeAllModals()">&times;</button>
        </div>
        <form method="post" action="users.php">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="create">
            <div class="usr-modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>نام کاربری <span style="color:var(--usr-danger)">*</span></label>
                        <input class="form-control" type="text" name="username" dir="ltr" required minlength="3" placeholder="admin_username">
                    </div>
                    <div class="form-group">
                        <label>سطح دسترسی</label>
                        <select class="form-control" name="role">
                            <option value="admin">مدیر عادی (سفارش‌ها، انبار، مالی)</option>
                            <option value="super_admin">مدیر کل (دسترسی تام و مدیران)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>نام و نام خانوادگی</label>
                    <input class="form-control" type="text" name="full_name" placeholder="مثال: علی رضایی">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>شماره تماس (اختیاری)</label>
                        <input class="form-control" type="text" name="phone" dir="ltr" placeholder="0912...">
                    </div>
                    <div class="form-group">
                        <label>آدرس ایمیل (اختیاری)</label>
                        <input class="form-control" type="email" name="email" dir="ltr" placeholder="admin@domain.com">
                    </div>
                </div>

                <div class="form-group">
                    <label>رمز عبور اولیه <span style="color:var(--usr-danger)">*</span></label>
                    <input class="form-control" type="password" name="password" required minlength="8" placeholder="حداقل ۸ کاراکتر">
                </div>
            </div>
            <div class="usr-modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAllModals()">انصراف</button>
                <button type="submit" class="btn btn-primary">ایجاد حساب مدیر</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. Edit Admin Modal -->
<div class="usr-modal-backdrop" id="modalEditAdmin" onclick="if(event.target===this) closeAllModals()">
    <div class="usr-modal-card">
        <div class="usr-modal-header">
            <h3 class="usr-modal-title">ویرایش مشخصات مدیر</h3>
            <button type="button" class="usr-modal-close" onclick="closeAllModals()">&times;</button>
        </div>
        <form method="post" action="users.php">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="editAdminId" value="">
            <div class="usr-modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>نام کاربری <span style="color:var(--usr-danger)">*</span></label>
                        <input class="form-control" type="text" name="username" id="editAdminUsername" dir="ltr" required minlength="3">
                    </div>
                    <div class="form-group">
                        <label>سطح دسترسی</label>
                        <select class="form-control" name="role" id="editAdminRole">
                            <option value="admin">مدیر عادی (سفارش‌ها، انبار، مالی)</option>
                            <option value="super_admin">مدیر کل (دسترسی تام و مدیران)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>نام و نام خانوادگی</label>
                    <input class="form-control" type="text" name="full_name" id="editAdminFullName">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>شماره تماس</label>
                        <input class="form-control" type="text" name="phone" id="editAdminPhone" dir="ltr">
                    </div>
                    <div class="form-group">
                        <label>آدرس ایمیل</label>
                        <input class="form-control" type="email" name="email" id="editAdminEmail" dir="ltr">
                    </div>
                </div>
            </div>
            <div class="usr-modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAllModals()">انصراف</button>
                <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
            </div>
        </form>
    </div>
</div>

<!-- 3. Change Password Modal -->
<div class="usr-modal-backdrop" id="modalPasswordAdmin" onclick="if(event.target===this) closeAllModals()">
    <div class="usr-modal-card" style="max-width:440px;">
        <div class="usr-modal-header">
            <h3 class="usr-modal-title">تغییر رمز عبور حساب <span id="pwAdminUsername" style="color:var(--usr-primary);" dir="ltr"></span></h3>
            <button type="button" class="usr-modal-close" onclick="closeAllModals()">&times;</button>
        </div>
        <form method="post" action="users.php">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="change_password">
            <input type="hidden" name="id" id="pwAdminId" value="">
            <div class="usr-modal-body">
                <div class="form-group">
                    <label>رمز عبور جدید (حداقل ۸ کاراکتر) <span style="color:var(--usr-danger)">*</span></label>
                    <input class="form-control" type="password" name="password" required minlength="8" placeholder="رمز عبور قوی وارد کنید...">
                </div>
            </div>
            <div class="usr-modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAllModals()">انصراف</button>
                <button type="submit" class="btn btn-primary">ثبت رمز جدید</button>
            </div>
        </form>
    </div>
</div>
