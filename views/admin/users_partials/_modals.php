<?php
/**
 * Admin Users Partial: Interactive Modals
 * Native <dialog> Modals via component('modal', ...)
 */
?>

<!-- 1. Create Admin Modal -->
<?php ob_start(); ?>
<form method="post" action="users.php" id="formCreateAdmin" class="ab-form">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="create">

    <div class="ab-form-grid">
        <?php component('form_field', [
            'name'        => 'username',
            'label'       => 'نام کاربری',
            'placeholder' => 'admin_username',
            'required'    => true,
            'dir'         => 'ltr',
            'attrs'       => ['minlength' => '3'],
        ]); ?>

        <?php component('form_field', [
            'name'    => 'role',
            'label'   => 'سطح دسترسی',
            'type'    => 'select',
            'options' => [
                'admin'       => 'مدیر عادی (سفارش‌ها، انبار، مالی)',
                'super_admin' => 'مدیر کل (دسترسی تام و مدیران)',
            ],
            'value'   => 'admin',
        ]); ?>
    </div>

    <?php component('form_field', [
        'name'        => 'full_name',
        'label'       => 'نام و نام خانوادگی',
        'placeholder' => 'مثال: علی رضایی',
    ]); ?>

    <div class="ab-form-grid">
        <?php component('form_field', [
            'name'        => 'phone',
            'label'       => 'شماره تماس (اختیاری)',
            'placeholder' => '0912...',
            'dir'         => 'ltr',
        ]); ?>

        <?php component('form_field', [
            'name'        => 'email',
            'label'       => 'آدرس ایمیل (اختیاری)',
            'type'        => 'email',
            'placeholder' => 'admin@domain.com',
            'dir'         => 'ltr',
        ]); ?>
    </div>

    <?php component('form_field', [
        'name'        => 'password',
        'label'       => 'رمز عبور اولیه',
        'type'        => 'password',
        'placeholder' => 'حداقل ۸ کاراکتر',
        'required'    => true,
        'attrs'       => ['minlength' => '8'],
    ]); ?>
</form>
<?php $createAdminBody = ob_get_clean(); ?>

<?php component('modal', [
    'id'             => 'modalCreateAdmin',
    'title'          => 'افزودن مدیر جدید به سیستم',
    'size'           => 'md',
    'content'        => $createAdminBody,
    'footer_actions' => [
        [
            'label'   => 'انصراف',
            'variant' => 'secondary',
            'attrs'   => ['data-ab-modal-close' => ''],
        ],
        [
            'label'   => 'ایجاد حساب مدیر',
            'variant' => 'primary',
            'type'    => 'submit',
            'attrs'   => ['form' => 'formCreateAdmin'],
        ],
    ],
]); ?>

<!-- 2. Edit Admin Modal -->
<?php ob_start(); ?>
<form method="post" action="users.php" id="formEditAdmin" class="ab-form">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="id" id="editAdminId" value="">

    <div class="ab-form-grid">
        <?php component('form_field', [
            'name'     => 'username',
            'id'       => 'editAdminUsername',
            'label'    => 'نام کاربری',
            'required' => true,
            'dir'      => 'ltr',
            'attrs'    => ['minlength' => '3'],
        ]); ?>

        <?php component('form_field', [
            'name'    => 'role',
            'id'      => 'editAdminRole',
            'label'   => 'سطح دسترسی',
            'type'    => 'select',
            'options' => [
                'admin'       => 'مدیر عادی (سفارش‌ها، انبار، مالی)',
                'super_admin' => 'مدیر کل (دسترسی تام و مدیران)',
            ],
        ]); ?>
    </div>

    <?php component('form_field', [
        'name'  => 'full_name',
        'id'    => 'editAdminFullName',
        'label' => 'نام و نام خانوادگی',
    ]); ?>

    <div class="ab-form-grid">
        <?php component('form_field', [
            'name'  => 'phone',
            'id'    => 'editAdminPhone',
            'label' => 'شماره تماس',
            'dir'   => 'ltr',
        ]); ?>

        <?php component('form_field', [
            'name'  => 'email',
            'id'    => 'editAdminEmail',
            'label' => 'آدرس ایمیل',
            'type'  => 'email',
            'dir'   => 'ltr',
        ]); ?>
    </div>
</form>
<?php $editAdminBody = ob_get_clean(); ?>

<?php component('modal', [
    'id'             => 'modalEditAdmin',
    'title'          => 'ویرایش مشخصات مدیر',
    'size'           => 'md',
    'content'        => $editAdminBody,
    'footer_actions' => [
        [
            'label'   => 'انصراف',
            'variant' => 'secondary',
            'attrs'   => ['data-ab-modal-close' => ''],
        ],
        [
            'label'   => 'ذخیره تغییرات',
            'variant' => 'primary',
            'type'    => 'submit',
            'attrs'   => ['form' => 'formEditAdmin'],
        ],
    ],
]); ?>

<!-- 3. Change Password Modal -->
<?php ob_start(); ?>
<form method="post" action="users.php" id="formPasswordAdmin" class="ab-form">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="change_password">
    <input type="hidden" name="id" id="pwAdminId" value="">

    <div class="ab-stack ab-stack--sm">
        <p class="ab-text-sm ab-text-muted">
            تغییر رمز عبور برای حساب کاربری: <span id="pwAdminUsername" class="font-bold text-tone" dir="ltr"></span>
        </p>
        <?php component('form_field', [
            'name'        => 'password',
            'label'       => 'رمز عبور جدید',
            'type'        => 'password',
            'placeholder' => 'رمز عبور جدید (حداقل ۸ کاراکتر)...',
            'required'    => true,
            'attrs'       => ['minlength' => '8'],
        ]); ?>
    </div>
</form>
<?php $passwordAdminBody = ob_get_clean(); ?>

<?php component('modal', [
    'id'             => 'modalPasswordAdmin',
    'title'          => 'تغییر رمز عبور مدیر',
    'size'           => 'sm',
    'content'        => $passwordAdminBody,
    'footer_actions' => [
        [
            'label'   => 'انصراف',
            'variant' => 'secondary',
            'attrs'   => ['data-ab-modal-close' => ''],
        ],
        [
            'label'   => 'ثبت رمز جدید',
            'variant' => 'primary',
            'type'    => 'submit',
            'attrs'   => ['form' => 'formPasswordAdmin'],
        ],
    ],
]); ?>
