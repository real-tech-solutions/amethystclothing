<?php
/**
 * Amethystclothing.lk — Customer Logout
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
unset($_SESSION['customer_id']);
redirect(base_url('auth'));
