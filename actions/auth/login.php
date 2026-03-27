<?php
/**
 * Login Action
 */

requireCsrf();

$password = stringParam($_POST['password'] ?? '');

if (empty($password)) {
    redirect('/login', 'Password is required', 'error');
}

if (attemptLogin($password)) {
    $redirect = $_SESSION['redirect_after_login'] ?? '/dashboard';
    unset($_SESSION['redirect_after_login']);
    redirect($redirect, 'Welcome back!');
}

redirect('/login', 'Invalid password', 'error');
