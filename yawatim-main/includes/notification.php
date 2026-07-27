<?php
// includes/flash.php - Session-based flash message system

/**
 * Set a flash message to be displayed after redirect.
 * @param string $type 'success' or 'error'
 * @param string $message The message text
 */
function set_flash($type, $message) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Retrieve and clear the flash message.
 * @return array|null ['type' => ..., 'message' => ...] or null
 */
function get_flash() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
