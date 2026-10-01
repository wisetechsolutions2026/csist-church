<?php
// Copy this file to admin-credentials.php and set your own values.
// admin-credentials.php is gitignored and never committed.
//
// Each admin has a role:
//   'super'   - full access to every admin page
//   'limited' - can only access service.php and celebrations.php
//
// Generate a password hash with: password_hash('yourpassword', PASSWORD_DEFAULT)
define('ADMINS', [
    'admin' => [
        'hash' => '',
        'role' => 'limited',
    ],
    'superadmin' => [
        'hash' => '',
        'role' => 'super',
    ],
]);
