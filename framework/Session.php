<?php

class Session {
    private static $instance = null;

    public function __construct() {
        session_start();
        if (!isset($_SESSION['csrf'])) {
            $_SESSION['csrf'] = Session::generateRandomToken();
        }
    }

    private static function generateRandomToken() {
        return bin2hex(openssl_random_pseudo_bytes(32));
    }

    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public function get($key) {
        return $_SESSION[$key] ?? null;
    }

    public function getCsrf() {
        return $_SESSION['csrf'];
    }

    public function remove($key) {
        unset($_SESSION[$key]);
    }

    public function destroy() {
        session_unset();
        session_destroy();
    }
}