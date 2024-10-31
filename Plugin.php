<?php
/*
Plugin Name: Redirect multisite user to their own site (UNMAINTAINED)
Plugin URI: https://wordpress.org/plugins/redirect-multisite-user-to-their-own-site/
Description: THIS PLUGIN IS NO LONGER MAINTAINED. If the current user in a multisite environment accesses a subsite to which access has not been granted, then redirect the user back to their own site. This plugin requires PHP 5.3 or newer because it uses PHP namespaces.
Author: Mark Howells-Mead
Version: 1.1.2
Author URI: https://www.permanenttourist.ch/
Text Domain: redirect-multisite-user-to-their-own-site
*/

namespace MHM\MultisiteRedirectuser;

class Plugin
{
    public function __construct()
    {
        load_plugin_textdomain('redirect-multisite-user-to-their-own-site');
        if (is_multisite()) {
            add_action('parse_request', array($this, 'checkAccess'));
        }
    }

    public function checkAccess()
    {
        global $current_user, $wpdb;
        $current_site_id = (int) $wpdb->blogid;
        $user_sites = get_blogs_of_user($current_user->ID);
        if (is_array($user_sites)) {
            switch (count($user_sites)) {
                case 0:
                    if (is_user_logged_in()) {
                        do_action('redirect-multisite-user-to-their-own-site/no-sites', $current_user, $current_site_id);
                    }
                    break;
                case 1:
                    $user_target_site = array_values($user_sites)[0];
                    if ((int) $user_target_site->userblog_id !== $current_site_id) {
                        do_action('redirect-multisite-user-to-their-own-site/redirecting', $current_user, $current_site_id, $user_target_site->siteurl);
                        wp_redirect($user_target_site->siteurl);
                        exit;
                    }
                    break;
                default:
                    if (!array_key_exists($current_site_id, $user_sites)) {
                        do_action('redirect-multisite-user-to-their-own-site/not-allowed', $current_user, $current_site_id, $user_sites);
                    }
                    break;
            }
        }
    }
}

new Plugin();
