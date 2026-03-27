<?php
/**
 * Logout Action
 */

requireCsrf();
logout();
redirect('/login', 'Logged out successfully');
