<?php

// ABSPATH prevent public user to directly access your .php files through URL.
defined('ABSPATH') or die('No script kiddies please!');

function defaults()
{
    return [
        'client_id'             => '',
        'client_secret'         => '',
        'backend'               => '',
        'redirect_to_dashboard' => 0,
        'login_only'            => 0,
    ];
}

function get_options()
{
    $options = get_option(casdoor_admin::OPTIONS_NAME, []);
    if (!is_array($options)) {
        $options = defaults();
    }
    $options = array_merge(defaults(), $options);
    return $options;
}

/**
 * get option value
 *
 * @param string $option_name
 *
 * @return void|string
 */
function casdoor_get_option(string $option_name)
{
    $options = get_options();
    if (!empty($v = $options[$option_name])) {
        return $v;
    }
}

function casdoor_set_options(string $key, $value)
{
    $options = get_options();
    $options[$key] = $value;
    update_option(casdoor_admin::OPTIONS_NAME, $options);
}

/**
 * Get the login url of casdoor
 *
 * @param string $redirect
 *
 * @return string
 */
function get_casdoor_login_url(string $redirect = ''): string
{
    $params = [
        'oauth'         => 'authorize',
        'response_type' => 'code',
        'client_id'     => casdoor_get_option('client_id'),
        'client_secret' => casdoor_get_option('client_secret'),
        'redirect_uri'  => site_url('?auth=casdoor'),
        'state'         => urlencode($redirect)
    ];
    $params = http_build_query($params);
    return casdoor_get_option('backend') . '/login/oauth/authorize?' . $params;
}

/**
 * Add login button for casdoor on the login form.
 *
 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/login_form
 */
function casdoor_login_form_button()
{
    ?>
    <a style="color:#FFF; width:100%; text-align:center; margin-bottom:1em;" class="button button-primary button-large"
       href="<?php echo site_url('?auth=casdoor'); ?>">Casdoor Single Sign On</a>
    <div style="clear:both;"></div>
    <?php
}
// Fires following the ‘Password’ field in the login form.
// It can be used to customize the built-in WordPress login form. Use in conjunction with ‘login_head‘ (for validation).
// add_action('login_form', 'casdoor_login_form_button');

/**
 * Login Button Shortcode
 *
 * @param  [type] $atts [description]
 *
 * @return [type]       [description]
 */
function casdoor_login_button_shortcode($atts)
{
    $a = shortcode_atts([
        'type'   => 'primary',
        'title'  => 'Login using Casdoor',
        'class'  => 'sso-button',
        'target' => '_blank',
        'text'   => 'Casdoor Single Sign On'
    ], $atts);

    return '<a class="' . $a['class'] . '" href="' . site_url('?auth=casdoor') . '" title="' . $a['title'] . '" target="' . $a['target'] . '">' . $a['text'] . '</a>';
}
add_shortcode('sso_button', 'casdoor_login_button_shortcode');

/**
 * Get user login redirect.
 * Just in case the user wants to redirect the user to a new url.
 *
 * @return string
 */
function casdoor_get_user_redirect_url(): string
{
    $options           = get_option('casdoor_options');
    // Retrieves the URL to the user’s dashboard.
    $user_redirect_set = $options['redirect_to_dashboard'] == '1' ? get_dashboard_url() : site_url();
    $user_redirect     = apply_filters('casdoor_user_redirect_url', $user_redirect_set);

    return $user_redirect;
}
