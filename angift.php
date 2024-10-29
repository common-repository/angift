<?php
/**
 * Plugin Name: Angift
 * Plugin URI: https://angift.ru/
 * Description: This plugin will give you access to the unlimited amount of gifts wich you can give to you customers or clients. It will help you to increase loyality, work with abandoned carts and make extra remind about you. <strong>Just click on the 'Angift' link in your administration console menu and follow instructions.</strong>
 * Version: 1.0.0
 * Author: Alexey 'gdever' Dodonov
 * Author URI: http://gdzone.ru
 * License: GPLv2 or later
 */
if (! defined('ABSPATH'))
    exit(); // Exit if accessed directly

if (strpos($_SERVER['HTTP_HOST'], '.local') !== false) {
    define('ANGIFT_API_URL', 'http://angift-en.aeon.su/');
} else {
    // define('ANGIFT_API_URL', 'http://angift-aeon.su');
    define('ANGIFT_API_URL', 'http://angift-en.aeon.su/');
}

define('ANGIFT_LOGIN_FIELD', 'angift_login');
define('ANGIFT_PASSWORD_FIELD', 'angift_password');

define('ANGIFT_PASSWORD_FORM_FIELD', 'password');
define('ANGIFT_EMAIL_FORM_FIELD', 'email');
define('ANGIFT_PASSWORD_CONFIRMATION_FORM_FIELD', 'password-confirmation');

$sessionId = '';

/**
 * Method sends request on the specified URL
 *
 * @param string $url
 *            endpoint
 * @param string $method
 *            HTTP method
 * @param array $data
 *            submitting data
 * @return array result
 */
function angift_http_request(string $url, string $method, array $data = [])
{
    global $sessionId;

    $args = [
        'headers' => [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0'
        ],
        'method' => $method
    ];

    if ($sessionId !== '') {
        $args['headers']['Cgi-Authorization'] = 'Basic ' . $sessionId;
    }

    if (count($data)) {
        $args['body'] = $data;
    }

    $response = wp_remote_request($url, $args);
    $result = wp_remote_retrieve_body($response);

    return json_decode($result);
}

/**
 * Method sends POST request on the specified URL
 *
 * @param string $url
 *            endpoint
 * @param array $data
 *            submitting data
 * @return array result
 */
function angift_post_http_request(string $url, array $data)
{
    return angift_http_request($url, 'POST', $data);
}

/**
 * Method sends GET request on the specified URL
 *
 * @param string $url
 *            endpoint
 * @param array $data
 *            submitting data
 * @return array result
 */
function angift_get_http_request(string $url)
{
    return angift_http_request($url, 'GET');
}

/**
 * Method handles connection to the angift api
 *
 * @param string $login
 *            connection login
 * @param string $password
 *            connection password
 * @return mixed result of the connection
 */
function angift_api_connect(string $login, string $password)
{
    $result = angift_post_http_request(
        ANGIFT_API_URL . '/connect',
        [
            'login' => $login,
            ANGIFT_PASSWORD_FORM_FIELD => $password
        ]);

    if (isset($result->session_id)) {
        global $sessionId;
        $sessionId = $result->session_id;
    }

    return $result;
}

/**
 * Method handles registration
 *
 * @param string $login
 *            new user's login
 * @param string $password
 *            new user's password
 * @param string $passwordConfirmation
 *            new user's password confirmation
 * @return mixed result
 */
function angift_api_register(string $login, string $password, string $passwordConfirmation)
{
    return angift_post_http_request(
        ANGIFT_API_URL . '/registration',
        [
            ANGIFT_EMAIL_FORM_FIELD => $login,
            ANGIFT_PASSWORD_FORM_FIELD => $password,
            ANGIFT_PASSWORD_CONFIRMATION_FORM_FIELD => $passwordConfirmation
        ]);
}

/**
 * Method connects to API if necessary
 */
function angift_api_lazy_connect(): void
{
    global $sessionId;

    if ($sessionId === '') {
        angift_api_connect(get_option(ANGIFT_LOGIN_FIELD), get_option(ANGIFT_PASSWORD_FIELD));
    }
}

/**
 * Method returns active actions
 *
 * @return array|object active actions or error description
 */
function angift_api_get_actions()
{
    angift_api_lazy_connect();

    return angift_get_http_request(ANGIFT_API_URL . '/actions/active');
}

/**
 * Method giving gift
 *
 * @param int $actionId
 *            id of the action
 * @param int $count
 *            count of gifts to give
 * @return mixed given gifts or object wich describes error
 */
function angift_give_gift(int $actionId, int $count = 1)
{
    angift_api_lazy_connect();

    return angift_post_http_request(ANGIFT_API_URL . '/action/' . $actionId . '/gifts/give/' . $count, []);
}

/**
 * Method handles registration form
 */
function angift_registration_form_handler(): void
{
    if (isset($_POST[ANGIFT_EMAIL_FORM_FIELD])) {
        // processing submit
        if (is_email(sanitize_email($_POST[ANGIFT_EMAIL_FORM_FIELD])) === false) {
            $message = 'Invalid email';
        } elseif (sanitize_text_field($_POST[ANGIFT_PASSWORD_FORM_FIELD]) !=
            sanitize_text_field($_POST[ANGIFT_PASSWORD_CONFIRMATION_FORM_FIELD])) {
            $message = 'Passwords do not match';
        } else {
            $result = angift_api_register(
                sanitize_email($_POST[ANGIFT_EMAIL_FORM_FIELD]),
                sanitize_text_field($_POST[ANGIFT_PASSWORD_FORM_FIELD]),
                sanitize_text_field($_POST[ANGIFT_PASSWORD_CONFIRMATION_FORM_FIELD]));

            if (isset($result->message)) {
                $message = $result->message;
            } else {
                // set options here
                if (add_option(ANGIFT_LOGIN_FIELD, sanitize_email($_POST[ANGIFT_EMAIL_FORM_FIELD])) === true) {
                    // new user was added
                    add_option(ANGIFT_PASSWORD_FIELD, sanitize_text_field($_POST[ANGIFT_PASSWORD_FORM_FIELD]));
                } else {
                    // updating settings
                    update_option(ANGIFT_LOGIN_FIELD, sanitize_email($_POST[ANGIFT_EMAIL_FORM_FIELD]));
                    update_option(ANGIFT_PASSWORD_FIELD, sanitize_text_field($_POST[ANGIFT_PASSWORD_FORM_FIELD]));
                }
                $message = 'Congratulations! You have registered.';
            }
        }
    }

    require_once (__DIR__ . '/registration-form.php');
}

/**
 * Method handles feedback form
 */
function angift_feedback_form_handler(): void
{
    require_once (__DIR__ . '/feedback-form.php');
}

/**
 * Function handles settings form
 */
function angift_settings_form_handler(): void
{
    if (isset($_POST[ANGIFT_EMAIL_FORM_FIELD])) {
        $result = angift_api_connect(
            sanitize_email($_POST[ANGIFT_EMAIL_FORM_FIELD]),
            sanitize_text_field($_POST[ANGIFT_PASSWORD_FORM_FIELD]));

        if (isset($result->message)) {
            $message = $result->message;
        } else {
            update_option(ANGIFT_LOGIN_FIELD, sanitize_email($_POST[ANGIFT_EMAIL_FORM_FIELD]));
            update_option(ANGIFT_PASSWORD_FIELD, sanitize_text_field($_POST[ANGIFT_PASSWORD_FORM_FIELD]));
            print('<h4>Settings were saved</h4>');
            return;
        }
    }

    require_once (__DIR__ . '/settings-form.php');
}

/**
 * Method handles list of gifts form
 */
function angift_actions_form_handler(): void
{
    $actions = angift_api_get_actions();

    foreach ($actions as $i => $action) {
        $actions[$i]->title = strip_tags($action->title);
    }

    require_once (__DIR__ . '/actions-form.php');
}

/**
 * Method handles form for gift giving confirmation
 */
function angift_actions_shure_get_gift(): void
{
    require_once (__DIR__ . '/shure-get-gift-form.php');
}

/**
 * Method handles gift giving
 */
function angift_actions_get_gift(): void
{
    $gifts = angift_give_gift(sanitize_key($_GET['action_id']));

    if (is_array($gifts) && count($gifts) == 1) {
        print
            ('Here is your promo : <strong>' . $gifts[0]->gift_code .
            '</strong><br/><br/>Give it to your customer to increase his loyality )<br/><br/>');
        print('And here is the instruction how to use this code: <br/><br/><i>' . $gifts[0]->how_to_get . '</i>');
    } else {
        if ($gifts->code === - 1) {
            print('You are only allowed to give 3 gifts per day');
        }
    }
}

/**
 * Method outputs main view
 */
function angift_main_form_handler(): void
{
    switch (sanitize_key($_GET['tab'])) {
        default:
        case ('manual'):
            $activeTab = 'manual';
            break;
        case ('actions'):
            $activeTab = 'actions';
            break;
        case ('gifts'):
            $activeTab = 'gifts';
            break;
        case ('settings'):
            $activeTab = 'settings';
            break;
        case ('shure_get_gift'):
            $activeTab = 'shure_get_gift';
            break;
        case ('registration'):
            $activeTab = 'registration';
            break;
        case ('get_gift'):
            $activeTab = 'get_gift';
            break;
        case ('feedback'):
            $activeTab = 'feedback';
            break;
    }

    require_once (__DIR__ . '/main-form.php');
}

/**
 * Method outputs angift form content
 */
function angift_menu_render(): void
{
    if (get_option(ANGIFT_LOGIN_FIELD, '') === '') {
        // need registration
        angift_registration_form_handler();
    } else {
        angift_main_form_handler();
    }
}

/**
 * Plugin activation hook
 */
function angift_activate(): void
{
    // add connection options here
    add_option(ANGIFT_LOGIN_FIELD, '');
    add_option(ANGIFT_PASSWORD_FIELD, '');
}

register_activation_hook(__FILE__, 'angift_activate');

/**
 * Method clears plugin's options
 */
function angift_clear_options(): void
{
    delete_option(ANGIFT_LOGIN_FIELD);
    delete_option(ANGIFT_PASSWORD_FIELD);
}

/**
 * Plugin deactivation hook
 */
function angift_deactivate(): void
{
    angift_clear_options();
}

register_deactivation_hook(__FILE__, 'angift_deactivate');

/**
 * Plugin uninstall hook
 */
function angift_uninstall(): void
{
    angift_deactivate();
}

register_uninstall_hook(__FILE__, 'angift_uninstall');

/**
 * Updating admin menu
 */
function angift_register_menu(): void
{
    add_menu_page(
        'Gifts',
        'Gifts',
        'edit_others_posts',
        'angift',
        'angift_menu_render',
        plugins_url('angift/assets/menu-icon-16.png'),
        4);
}

add_action('admin_menu', 'angift_register_menu');
