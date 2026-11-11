<?php
/**
 * Amethystclothing.lk — Admin Logout
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
unset($_SESSION['admin_id']);
redirect(base_url('admin/login'));
