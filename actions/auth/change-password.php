<?php
/**
 * Change Password Action
 */

requireCsrf();
requireAuth();

try {
    $data = validate($_POST, [
        'current_password' => 'required|string',
        'new_password' => 'required|string|min:8',
        'confirm_password' => 'required|string'
    ]);
    
    if ($data['new_password'] !== $data['confirm_password']) {
        throw new ValidationError('New passwords do not match');
    }
    
    if (changePassword(currentUserId(), $data['current_password'], $data['new_password'])) {
        redirect('/dashboard', 'Password changed successfully');
    }
    
    redirect('/change-password', 'Failed to change password', 'error');
    
} catch (ValidationError $e) {
    redirect('/change-password', $e->getMessage(), 'error');
}
