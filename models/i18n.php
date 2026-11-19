<?php
/**
 * Internationalization (i18n) Engine for DjoKhouse SCI
 * Supports French (fr) & English (en)
 */
class i18n {
    private static string $lang = 'fr';
    private static array $strings = [];
    private static bool $initialized = false;

    public static function init(): void {
        if (self::$initialized) {
            return;
        }

        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            $sessPath = session_save_path();
            if (empty($sessPath) || !is_writable($sessPath)) {
                @session_save_path(sys_get_temp_dir());
            }
            @session_start();
        }

        // Check query param
        if (isset($_GET['lang']) && in_array(strtolower($_GET['lang']), ['fr', 'en'])) {
            self::$lang = strtolower($_GET['lang']);
            $_SESSION['lang'] = self::$lang;
            if (!headers_sent()) {
                setcookie('djok_lang', self::$lang, time() + (86400 * 365), '/');
            }
        } elseif (isset($_SESSION['lang']) && in_array($_SESSION['lang'], ['fr', 'en'])) {
            self::$lang = $_SESSION['lang'];
        } elseif (isset($_COOKIE['djok_lang']) && in_array($_COOKIE['djok_lang'], ['fr', 'en'])) {
            self::$lang = $_COOKIE['djok_lang'];
            $_SESSION['lang'] = self::$lang;
        } else {
            self::$lang = 'fr';
            $_SESSION['lang'] = 'fr';
        }

        // Load language dictionary
        $langFile = __DIR__ . '/../langs/' . self::$lang . '.php';
        if (file_exists($langFile)) {
            self::$strings = require $langFile;
        }

        self::$initialized = true;
    }

    public static function lang(): string {
        if (!self::$initialized) {
            self::init();
        }
        return self::$lang;
    }

    public static function isEn(): bool {
        return self::lang() === 'en';
    }

    public static function isFr(): bool {
        return self::lang() === 'fr';
    }

    public static function get(string $key, string $default = ''): string {
        if (!self::$initialized) {
            self::init();
        }
        if (isset(self::$strings[$key])) {
            return self::$strings[$key];
        }
        return !empty($default) ? $default : $key;
    }

    /**
     * Generate URL to switch language while preserving existing query params
     */
    public static function switchUrl(string $targetLang): string {
        $params = $_GET;
        $params['lang'] = $targetLang;

        $queryString = http_build_query($params);
        $page = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');

        return $page . ($queryString ? '?' . $queryString : '');
    }

    /**
     * Render the visual Language Switcher HTML component
     */
    public static function renderSwitcher(string $extraClass = ''): string {
        $cur = self::lang();

        $frClass = $cur === 'fr' ? 'lang-btn active' : 'lang-btn';
        $enClass = $cur === 'en' ? 'lang-btn active' : 'lang-btn';

        return "
        <div class='djok-lang-switcher {$extraClass}'>
            <a href='#' class='{$frClass} djok-switch-lang' data-lang='fr' title='Français'>FR</a>
            <span class='lang-sep'>|</span>
            <a href='#' class='{$enClass} djok-switch-lang' data-lang='en' title='English'>EN</a>
        </div>";
    }
}

// Global translation helpers
if (!function_exists('__')) {
    function __(string $key, string $default = ''): string {
        return i18n::get($key, $default);
    }
}

if (!function_exists('_e')) {
    function _e(string $key, string $default = ''): void {
        echo i18n::get($key, $default);
    }
}

// Auto-initialize
i18n::init();
