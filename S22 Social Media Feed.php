<?php
/*
Plugin Name: S22 Social Media Feed
Description: Display social media feeds using Graph API with admin settings.
Version: 1.2
Author: Solution22
*/

// Enqueue scripts and styles
function ifs_enqueue_scripts()
{
    wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css');
    wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], null, true);

    wp_enqueue_style('ifs-style', plugin_dir_url(__FILE__) . 'assets/style.css', array(), filemtime(plugin_dir_path(__FILE__) . 'assets/style.css'));
    wp_enqueue_script('ifs-script', plugin_dir_url(__FILE__) . 'assets/slider.js', array('jquery', 'swiper-js'), filemtime(plugin_dir_path(__FILE__) . 'assets/slider.js'), true);

    wp_localize_script('ifs-script', 'ifsData', [
        'userId' => esc_js(get_option('ifs_user_id')),
        'accessToken' => esc_js(get_option('ifs_access_token')),
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ifs-frontend-nonce'),
    ]);
}
add_action('wp_enqueue_scripts', 'ifs_enqueue_scripts');

// Shortcode display functions
function ifs_display_feed_with_type($type = 'slider')
{
    $license_plan = get_option('ifs_license_plan_name', '');
    $is_standard = (stripos($license_plan, 'standard') !== false);
    if ($is_standard && $type === 'gallery') {
        $type = 'slider';
    }
    // Force a fresh license check on frontend refresh
    delete_transient('ifs_license_verification_status');

    $license_key = get_option('ifs_license_key', '');
    $email = get_option('ifs_license_email', '');
    if (empty($license_key) || empty($email)) {
        return '<p class="s22-license-error" style="color: #d93838; font-weight: bold; padding: 12px; border: 1px dashed #d93838; background-color: #fff5f5; border-radius: 4px;">Please activate your license key in the WordPress admin settings to display the feed.</p>';
    }

    if (!ifs_check_license_status_on_load()) {
        $status = get_option('ifs_license_status', 'invalid');
        if ($status === 'expired') {
            return '<p class="s22-license-error" style="color: #d93838; font-weight: bold; padding: 12px; border: 1px dashed #d93838; background-color: #fff5f5; border-radius: 4px;">Error: Your license key has expired. Please renew your license in the WordPress admin settings.</p>';
        } else {
            return '<p class="s22-license-error" style="color: #d93838; font-weight: bold; padding: 12px; border: 1px dashed #d93838; background-color: #fff5f5; border-radius: 4px;">Error: Invalid license key. Please verify your license settings in the WordPress admin panel.</p>';
        }
    }

    $user_id = get_option('ifs_user_id');
    $access_token = get_option('ifs_access_token');

    if (!$user_id || !$access_token) {
        return '<p>Missing Instagram credentials.</p>';
    }

    // 🎯 One API call with all needed fields
    $url = "https://graph.facebook.com/v19.0/{$user_id}/media?fields=id,caption,media_url,media_type,thumbnail_url,timestamp&access_token={$access_token}";
    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        return '<p>Failed to connect to Instagram API.</p>';
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (empty($body['data'])) {
        return '<p>Error loading Instagram feed.</p>';
    }

    ob_start();

    if ($type === 'slider') {
        echo '<div class="swiper mySwiper"><div class="swiper-wrapper">';
    } else {
        echo '<div class="instagram-gallery">';
    }

    $index = 0;
    foreach ($body['data'] as $post) {
        $media_url = '';
        $media_type = $post['media_type'];
        $caption = !empty($post['caption']) ? esc_attr($post['caption']) : '';

        // Use thumbnail_url for videos if available
        if ($media_type === 'VIDEO') {
            if (!empty($post['media_url']) && preg_match('/\.mp4(\?.*)?$/', $post['media_url'])) {
                $media_url = esc_url($post['media_url']);
            } else {
                // fallback to thumbnail
                $media_url = esc_url($post['thumbnail_url']);
            }
        } elseif ($media_type === 'IMAGE') {
            $media_url = esc_url($post['media_url']);
        }

        // Skip if still no media URL
        if (empty($media_url)) continue;

        // Common wrapper
        $wrapper_class = $type === 'slider' ? 'swiper-slide' : 'gallery-item';
        echo '<div class="' . $wrapper_class . '">';

        // Display video or image
        if ($media_type === 'VIDEO' && preg_match('/\.mp4(\?.*)?$/', $post['media_url'])) {
            echo '<a class="ifs-lightbox-link" data-img="' . $media_url . '" data-index="' . $index . '" data-caption="' . $caption . '">
                    <video autoplay muted preload="metadata" playsinline >
                        <source src="' . $media_url . '" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                  </a>';
        } else {
            echo '<a class="ifs-lightbox-link" data-img="' . $media_url . '" data-index="' . $index . '" data-caption="' . $caption . '">
                    <img src="' . $media_url . '" alt="Instagram image" />
                  </a>';
        }

        echo '</div>';
        $index++;
    }

    if ($type === 'slider') {
        echo '</div><div class="swiper-button-next"></div><div class="swiper-button-prev"></div><div class="swiper-pagination"></div></div>';
    } else {
        echo '</div>';
    }

    return ob_get_clean();
}


add_shortcode('instagram_feed', function () {
    $type = get_option('ifs_display_type', 'slider');
    return ifs_display_feed_with_type($type);
});
add_shortcode('instagram_slider', function () {
    return ifs_display_feed_with_type('slider');
});
add_shortcode('instagram_gallery', function () {
    return ifs_display_feed_with_type('gallery');
}); // Admin menu
function ifs_add_admin_menu()
{
    // Add main menu
    add_menu_page(
        'S22 Social Media Feed',
        'S22 Social Media',
        'manage_options',
        's22-social-media',
        'ifs_dashboard_page',
        'dashicons-share',
        30
    );
}
add_action('admin_menu', 'ifs_add_admin_menu');

// Register settings
function ifs_register_settings()
{
    register_setting('ifs_settings_group', 'ifs_user_id');
    register_setting('ifs_settings_group', 'ifs_access_token');
    register_setting('ifs_settings_group', 'ifs_display_type');
}
add_action('admin_init', 'ifs_register_settings');

// Dashboard page
function ifs_dashboard_page()
{
    include_once plugin_dir_path(__FILE__) . 'admin/views/dashboard.php';
}

// Add admin scripts
function ifs_admin_scripts($hook)
{
    if (strpos($hook, 's22-social-media') === false) {
        return;
    }

    // Force a fresh license check on admin dashboard refresh
    delete_transient('ifs_license_verification_status');
    ifs_sync_license_state();

    wp_enqueue_style('ifs-admin-style', plugin_dir_url(__FILE__) . 'assets/admin-style.css', array(), filemtime(plugin_dir_path(__FILE__) . 'assets/admin-style.css'));
    wp_enqueue_script('ifs-admin', plugin_dir_url(__FILE__) . 'assets/admin.js', array('jquery'), filemtime(plugin_dir_path(__FILE__) . 'assets/admin.js'), true);
    
    $sliders = get_option('ifs_saved_sliders', array());
    $license_plan = get_option('ifs_license_plan_name', '');
    $is_standard = (stripos($license_plan, 'standard') !== false);
    if (is_array($sliders)) {
        foreach ($sliders as $key => $slider) {
            if (isset($slider['type']) && $slider['type'] === 'masonry') {
                $sliders[$key]['type'] = 'gallery';
            }
            if ($is_standard && isset($sliders[$key]['type']) && $sliders[$key]['type'] === 'gallery') {
                $sliders[$key]['type'] = 'slider';
            }
        }
    }

    wp_localize_script('ifs-admin', 'ifsAdmin', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ifs-admin-nonce'),
        'sliders' => $sliders,
        'licenseStatus' => get_option('ifs_license_status', 'invalid'),
        'licensePlan' => get_option('ifs_license_plan_name', ''),
        'licenseEmail' => get_option('ifs_license_email', ''),
        'licenseKey' => get_option('ifs_license_key', ''),
        'licenseExpiryDate' => get_option('ifs_license_expiry_date', ''),
        'licensePurchasedDate' => get_option('ifs_license_purchased_date', ''),
        'instagramUserId' => get_option('ifs_user_id', ''),
        'instagramAccessToken' => get_option('ifs_access_token', '')
    ));
}
add_action('admin_enqueue_scripts', 'ifs_admin_scripts');

// Sync and check license status dynamically
function ifs_sync_license_state()
{
    $license_key = get_option('ifs_license_key', '');
    $email = get_option('ifs_license_email', '');

    if (empty($license_key) || empty($email)) {
        if (get_option('ifs_license_status') !== 'invalid') {
            update_option('ifs_license_status', 'invalid');
        }
        return;
    }

    // 1. Local Date Check (Immediate real-time expiry checking)
    $expiry_date = get_option('ifs_license_expiry_date', '');
    if (!empty($expiry_date)) {
        $expiry_timestamp = strtotime($expiry_date);
        if ($expiry_timestamp !== false && time() > $expiry_timestamp) {
            if (get_option('ifs_license_status') !== 'expired') {
                update_option('ifs_license_status', 'expired');
                delete_transient('ifs_license_verification_status');
            }
            return;
        }
    }

    // 2. Periodic Remote Check (Sync with API every hour to ensure status matches Vercel API state)
    $cached_status = get_transient('ifs_license_verification_status');
    if ($cached_status === false) {
        ifs_check_license_status_on_load();
    }
}

// Global action check function hooked to init, wp, template_redirect
function ifs_global_license_check()
{
    static $run_once = false;
    if ($run_once) {
        return;
    }
    $run_once = true;

    ifs_sync_license_state();
}
add_action('init', 'ifs_global_license_check');
add_action('wp', 'ifs_global_license_check');
add_action('template_redirect', 'ifs_global_license_check');

// License Helpers
function ifs_is_license_active()
{
    ifs_sync_license_state();
    $status = get_option('ifs_license_status', 'invalid');
    if ($status === 'valid' || $status === 'active') {
        $expiry_date = get_option('ifs_license_expiry_date', '');
        if (!empty($expiry_date) && $expiry_date !== 'Expired') {
            $expiry_timestamp = strtotime($expiry_date);
            if ($expiry_timestamp !== false && time() > $expiry_timestamp) {
                return false;
            }
        }
        return true;
    }
    return false;
}

/**
 * AJAX handler for frontend license heartbeat check.
 * Accessible by both logged-in and guest users.
 */
function ifs_ajax_heartbeat_license_check()
{
    // Verification of nonce for security
    check_ajax_referer('ifs-frontend-nonce', 'nonce');

    // Sync license state
    ifs_sync_license_state();

    $status = get_option('ifs_license_status', 'invalid');
    $is_active = ($status === 'active' || $status === 'valid');

    wp_send_json(array(
        'status' => $status,
        'is_active' => $is_active,
        'message' => $is_active ? 'License is active.' : 'License is invalid or expired.'
    ));
}
add_action('wp_ajax_ifs_heartbeat_license_check', 'ifs_ajax_heartbeat_license_check');
add_action('wp_ajax_nopriv_ifs_heartbeat_license_check', 'ifs_ajax_heartbeat_license_check');

/**
 * Helper to check the license key API status on shortcode load.
 * Uses transient caching to avoid slow page loads.
 */
function ifs_check_license_status_on_load()
{
    $license_key = get_option('ifs_license_key', '');
    $email = get_option('ifs_license_email', '');

    if (empty($license_key) || empty($email)) {
        return false;
    }

    $cached_status = get_transient('ifs_license_verification_status');
    if ($cached_status !== false) {
        return $cached_status === 'active' || $cached_status === 'valid';
    }

    $api_url = 'https://s22-plugify.vercel.app/api/license/verify';
    $domain = get_site_url();
    $plugin_name = 'S22 Social Media Feed';

    $body = array(
        'domain'      => $domain,
        'email'       => $email,
        'license_key' => $license_key,
        'plugin_name' => $plugin_name,
    );

    $args = array(
        'body'        => json_encode($body),
        'headers'     => array(
            'Content-Type' => 'application/json',
        ),
        'timeout'     => 10,
    );

    $response = wp_remote_post($api_url, $args);

    if (is_wp_error($response)) {
        // Fallback to local option status to prevent breaking front-end when the licensing API is unreachable
        $local_status = get_option('ifs_license_status', 'invalid');
        set_transient('ifs_license_verification_status', $local_status, 10 * MINUTE_IN_SECONDS);
        return $local_status === 'active' || $local_status === 'valid';
    }

    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);
    $data = json_decode($response_body, true);

    $is_active = false;
    if (is_array($data) && (isset($data['expiry_date']) || isset($data['error']))) {
        $is_success = false;
        if (isset($data['status'])) {
            $val = $data['status'];
            $is_success = ($val === true || $val === 'true' || $val === 1 || $val === '1' || strcasecmp(strval($val), 'success') === 0 || strcasecmp(strval($val), 'active') === 0 || strcasecmp(strval($val), 'verified') === 0);
        } elseif (isset($data['success'])) {
            $val = $data['success'];
            $is_success = ($val === true || $val === 'true' || $val === 1 || $val === '1' || strcasecmp(strval($val), 'success') === 0);
        } elseif (isset($data['valid'])) {
            $val = $data['valid'];
            $is_success = ($val === true || $val === 'true' || $val === 1 || $val === '1');
        } else {
            $is_success = !isset($data['error']);
        }

        $expiry_date = isset($data['expiry_date']) ? sanitize_text_field($data['expiry_date']) : '';
        $is_expired_by_date = false;
        if (!empty($expiry_date)) {
            $expiry_timestamp = strtotime($expiry_date);
            if ($expiry_timestamp !== false && time() > $expiry_timestamp) {
                $is_expired_by_date = true;
            }
        }
        
        $error_msg = isset($data['error']) ? sanitize_text_field($data['error']) : '';
        if (stripos($error_msg, 'expired') !== false) {
            $is_expired_by_date = true;
        }

        if ($is_success && !$is_expired_by_date) {
            update_option('ifs_license_status', 'active');
            update_option('ifs_license_plan_name', isset($data['plan_name']) ? sanitize_text_field($data['plan_name']) : 'Enterprise Premium');
            update_option('ifs_license_purchased_date', isset($data['purchased_date']) ? sanitize_text_field($data['purchased_date']) : '');
            update_option('ifs_license_expiry_date', $expiry_date);
            $is_active = true;
        } else {
            $status = $is_expired_by_date ? 'expired' : 'invalid';
            update_option('ifs_license_status', $status);
            if (!empty($expiry_date)) {
                update_option('ifs_license_expiry_date', $expiry_date);
            }
            if (isset($data['plan_name'])) {
                update_option('ifs_license_plan_name', sanitize_text_field($data['plan_name']));
            }
            if (isset($data['purchased_date'])) {
                update_option('ifs_license_purchased_date', sanitize_text_field($data['purchased_date']));
            }
        }
    } else {
        update_option('ifs_license_status', 'invalid');
    }

    $final_status = get_option('ifs_license_status', 'invalid');
    set_transient('ifs_license_verification_status', $final_status, 12 * HOUR_IN_SECONDS);
    return $is_active;
}

// AJAX handler: Verify license key
function ifs_ajax_verify_license()
{
    check_ajax_referer('ifs-admin-nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        status_header(403);
        wp_send_json(array('message' => 'Permission Denied'));
    }

    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $license_key = isset($_POST['license_key']) ? sanitize_text_field($_POST['license_key']) : '';
    $domain = isset($_POST['domain']) ? esc_url_raw($_POST['domain']) : '';
    $plugin_name = isset($_POST['plugin_name']) ? sanitize_text_field($_POST['plugin_name']) : 'S22 Social Media Feed';

    $api_url = 'https://s22-plugify.vercel.app/api/license/verify';

    $body = array(
        'domain'      => $domain,
        'email'       => $email,
        'license_key' => $license_key,
        'plugin_name' => $plugin_name,
    );

    $args = array(
        'body'        => json_encode($body),
        'headers'     => array(
            'Content-Type' => 'application/json',
        ),
        'timeout'     => 15,
    );

    $response = wp_remote_post($api_url, $args);

    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => $response->get_error_message()));
    }

    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);
    $data = json_decode($response_body, true);

    if (is_array($data) && (isset($data['expiry_date']) || isset($data['error']))) {
        $is_success = false;
        if (isset($data['status'])) {
            $val = $data['status'];
            $is_success = ($val === true || $val === 'true' || $val === 1 || $val === '1' || strcasecmp(strval($val), 'success') === 0 || strcasecmp(strval($val), 'active') === 0 || strcasecmp(strval($val), 'verified') === 0);
        } elseif (isset($data['success'])) {
            $val = $data['success'];
            $is_success = ($val === true || $val === 'true' || $val === 1 || $val === '1' || strcasecmp(strval($val), 'success') === 0);
        } elseif (isset($data['valid'])) {
            $val = $data['valid'];
            $is_success = ($val === true || $val === 'true' || $val === 1 || $val === '1');
        } else {
            $is_success = !isset($data['error']);
        }

        $expiry_date = isset($data['expiry_date']) ? sanitize_text_field($data['expiry_date']) : '';
        $is_expired_by_date = false;
        if (!empty($expiry_date)) {
            $expiry_timestamp = strtotime($expiry_date);
            if ($expiry_timestamp !== false && time() > $expiry_timestamp) {
                $is_expired_by_date = true;
            }
        }
        
        $error_msg = isset($data['error']) ? sanitize_text_field($data['error']) : '';
        if (stripos($error_msg, 'expired') !== false) {
            $is_expired_by_date = true;
        }

        if ($is_success && !$is_expired_by_date) {
            update_option('ifs_license_status', 'active');
            update_option('ifs_license_plan_name', isset($data['plan_name']) ? sanitize_text_field($data['plan_name']) : 'Enterprise Premium');
            update_option('ifs_license_email', $email);
            update_option('ifs_license_key', $license_key);
            update_option('ifs_license_purchased_date', isset($data['purchased_date']) ? sanitize_text_field($data['purchased_date']) : '');
            update_option('ifs_license_expiry_date', $expiry_date);
        } else {
            update_option('ifs_license_status', $is_expired_by_date ? 'expired' : 'invalid');
            update_option('ifs_license_email', $email);
            update_option('ifs_license_key', $license_key);
            if (!empty($expiry_date)) {
                update_option('ifs_license_expiry_date', $expiry_date);
            }
            if (isset($data['plan_name'])) {
                update_option('ifs_license_plan_name', sanitize_text_field($data['plan_name']));
            }
            if (isset($data['purchased_date'])) {
                update_option('ifs_license_purchased_date', sanitize_text_field($data['purchased_date']));
            }
        }
    } else {
        update_option('ifs_license_status', 'invalid');
    }

    delete_transient('ifs_license_verification_status');

    status_header($response_code);
    header('Content-Type: application/json');
    echo $response_body;
    wp_die();
}
add_action('wp_ajax_ifs_verify_license', 'ifs_ajax_verify_license');

// AJAX handler: Disconnect license
function ifs_ajax_disconnect_license()
{
    check_ajax_referer('ifs-admin-nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission Denied');
    }
    update_option('ifs_license_status', 'invalid');
    delete_option('ifs_license_plan_name');
    delete_option('ifs_license_email');
    delete_option('ifs_license_key');
    delete_option('ifs_license_purchased_date');
    delete_option('ifs_license_expiry_date');
    delete_transient('ifs_license_verification_status');
    wp_send_json_success();
}
add_action('wp_ajax_ifs_disconnect_license', 'ifs_ajax_disconnect_license');

// AJAX handler: Save slider wizard
function ifs_ajax_save_slider()
{
    check_ajax_referer('ifs-admin-nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission Denied');
    }

    if (!ifs_is_license_active()) {
        wp_send_json_error('License is expired or invalid. Access to plugin functionality is restricted.');
    }

    $slider_id = isset($_POST['slider_id']) ? intval($_POST['slider_id']) : 0;
    
    $slider_data = array(
        'id' => $slider_id ?: time(),
        'name' => sanitize_text_field($_POST['name']),
        'type' => sanitize_text_field($_POST['type']),
        'feed_type' => sanitize_text_field($_POST['feed_type']),
        'height' => intval($_POST['height']),
        'gallery_num_posts' => intval($_POST['gallery_num_posts']),
        'gallery_load_more' => isset($_POST['gallery_load_more']) ? intval($_POST['gallery_load_more']) : 0,
        'gallery_load_more_text' => isset($_POST['gallery_load_more_text']) ? sanitize_text_field($_POST['gallery_load_more_text']) : 'Load More',
        'gallery_load_more_num' => isset($_POST['gallery_load_more_num']) ? intval($_POST['gallery_load_more_num']) : 4,
        'media_types' => isset($_POST['media_types']) ? array_map('sanitize_text_field', $_POST['media_types']) : array(),
        'autoplay' => isset($_POST['autoplay']) ? intval($_POST['autoplay']) : 0,
        'navigation' => isset($_POST['navigation']) ? intval($_POST['navigation']) : 0,
        'pagination' => isset($_POST['pagination']) ? intval($_POST['pagination']) : 0,
        'slides_per_view' => intval($_POST['slides_per_view']),
        'image_fit' => sanitize_text_field($_POST['image_fit']),
        // Style Customizations
        'feed_bg' => isset($_POST['feed_bg']) ? sanitize_text_field($_POST['feed_bg']) : 'transparent',
        'border_radius' => isset($_POST['border_radius']) ? intval($_POST['border_radius']) : 8,
        'slider_gap' => isset($_POST['slider_gap']) ? intval($_POST['slider_gap']) : 16,
        'hover_bg' => isset($_POST['hover_bg']) ? sanitize_text_field($_POST['hover_bg']) : 'rgba(0,0,0,0.5)',
        'hover_caption' => isset($_POST['hover_caption']) ? intval($_POST['hover_caption']) : 0,
        'arrow_color' => isset($_POST['arrow_color']) ? sanitize_text_field($_POST['arrow_color']) : '#1e62ec',
        'dot_color' => isset($_POST['dot_color']) ? sanitize_text_field($_POST['dot_color']) : '#1e62ec',
        'border_width' => isset($_POST['border_width']) ? intval($_POST['border_width']) : 1,
        'border_color' => isset($_POST['border_color']) ? sanitize_text_field($_POST['border_color']) : 'rgba(0,0,0,0.03)',
        'border_hover_color' => isset($_POST['border_hover_color']) ? sanitize_text_field($_POST['border_hover_color']) : 'rgba(0,0,0,0.06)',
        'follow_btn_bg' => isset($_POST['follow_btn_bg']) ? sanitize_text_field($_POST['follow_btn_bg']) : '#0f1419',
        'follow_btn_text' => isset($_POST['follow_btn_text']) ? sanitize_text_field($_POST['follow_btn_text']) : '#ffffff',
        'follow_btn_hover_bg' => isset($_POST['follow_btn_hover_bg']) ? sanitize_text_field($_POST['follow_btn_hover_bg']) : '#4f46e5',
        'load_more_bg' => isset($_POST['load_more_bg']) ? sanitize_text_field($_POST['load_more_bg']) : '#ffffff',
        'load_more_text' => isset($_POST['load_more_text']) ? sanitize_text_field($_POST['load_more_text']) : '#0f1419',
        'load_more_hover_bg' => isset($_POST['load_more_hover_bg']) ? sanitize_text_field($_POST['load_more_hover_bg']) : '#0f1419',
        'custom_css' => isset($_POST['custom_css']) ? sanitize_textarea_field($_POST['custom_css']) : '',
        'created' => date('M d, Y')
    );

    if ($slider_id) {
        $old_slider = get_option('ifs_slider_' . $slider_id);
        if ($old_slider && isset($old_slider['created'])) {
            $slider_data['created'] = $old_slider['created'];
        }
    } else {
        $slider_id = time();
        $slider_data['id'] = $slider_id;
    }

    update_option('ifs_slider_' . $slider_id, $slider_data);
    
    $saved_sliders = get_option('ifs_saved_sliders', array());
    $saved_sliders[$slider_id] = $slider_data;
    update_option('ifs_saved_sliders', $saved_sliders);

    wp_send_json_success(array('slider_id' => $slider_id, 'created' => $slider_data['created']));
}
add_action('wp_ajax_ifs_save_slider_ajax', 'ifs_ajax_save_slider');

// AJAX handler: Delete slider
function ifs_ajax_delete_slider()
{
    check_ajax_referer('ifs-admin-nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission Denied');
    }

    if (!ifs_is_license_active()) {
        wp_send_json_error('License is expired or invalid. Access to plugin functionality is restricted.');
    }

    $slider_id = isset($_POST['slider_id']) ? intval($_POST['slider_id']) : 0;
    if ($slider_id) {
        delete_option('ifs_slider_' . $slider_id);
        delete_option('ifs_hidden_posts_' . $slider_id);

        $saved_sliders = get_option('ifs_saved_sliders', array());
        unset($saved_sliders[$slider_id]);
        update_option('ifs_saved_sliders', $saved_sliders);
        wp_send_json_success();
    }
    wp_send_json_error('Invalid slider ID.');
}
add_action('wp_ajax_ifs_delete_slider_ajax', 'ifs_ajax_delete_slider');

// AJAX handler: Fetch moderation feed
function ifs_ajax_get_instagram_feed()
{
    check_ajax_referer('ifs-admin-nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission Denied');
    }

    if (!ifs_is_license_active()) {
        wp_send_json_error('License is expired or invalid. Access to plugin functionality is restricted.');
    }

    $slider_id = isset($_POST['slider_id']) ? intval($_POST['slider_id']) : 0;
    if (!$slider_id) {
        wp_send_json_error('Invalid slider ID.');
    }

    $user_id = get_option('ifs_user_id');
    $access_token = get_option('ifs_access_token');

    if (empty($user_id) || empty($access_token)) {
        wp_send_json_error('Missing Instagram credentials. Please configure them in Settings tab.');
    }

    $url = "https://graph.facebook.com/v19.0/{$user_id}/media?fields=id,caption,media_url,media_type,thumbnail_url,timestamp&access_token={$access_token}";
    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        wp_send_json_error('Failed to retrieve Instagram feed.');
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (empty($body['data'])) {
        wp_send_json_error('No data found or invalid credentials.');
    }

    $hidden_posts = get_option('ifs_hidden_posts_' . $slider_id, array());

    wp_send_json_success(array(
        'posts' => $body['data'],
        'hidden_posts' => $hidden_posts
    ));
}
add_action('wp_ajax_ifs_get_instagram_feed_ajax', 'ifs_ajax_get_instagram_feed');

// AJAX handler: Toggle post hidden/approved status
function ifs_ajax_toggle_post_moderation()
{
    check_ajax_referer('ifs-admin-nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission Denied');
    }

    if (!ifs_is_license_active()) {
        wp_send_json_error('License is expired or invalid. Access to plugin functionality is restricted.');
    }

    $slider_id = isset($_POST['slider_id']) ? intval($_POST['slider_id']) : 0;
    $post_id = isset($_POST['post_id']) ? sanitize_text_field($_POST['post_id']) : '';
    $action = isset($_POST['mod_action']) ? sanitize_text_field($_POST['mod_action']) : '';

    if (!$slider_id || !$post_id || !$action) {
        wp_send_json_error('Invalid parameters.');
    }

    $hidden_posts = get_option('ifs_hidden_posts_' . $slider_id, array());

    if ($action === 'hide') {
        if (!in_array($post_id, $hidden_posts)) {
            $hidden_posts[] = $post_id;
        }
    } else {
        $hidden_posts = array_values(array_diff($hidden_posts, array($post_id)));
    }

    update_option('ifs_hidden_posts_' . $slider_id, $hidden_posts);
    wp_send_json_success();
}
add_action('wp_ajax_ifs_toggle_post_moderation_ajax', 'ifs_ajax_toggle_post_moderation');

// Register shortcode
function ifs_register_shortcodes()
{
    add_shortcode('s22_slider', 'ifs_slider_shortcode');
}
add_action('init', 'ifs_register_shortcodes');

// Slider shortcode handler
function ifs_slider_shortcode($atts)
{
    // Force a fresh license check on frontend refresh
    delete_transient('ifs_license_verification_status');

    $license_key = get_option('ifs_license_key', '');
    $email = get_option('ifs_license_email', '');
    if (empty($license_key) || empty($email)) {
        return '<p class="s22-license-error" style="color: #d93838; font-weight: bold; padding: 12px; border: 1px dashed #d93838; background-color: #fff5f5; border-radius: 4px;">Please activate your license key in the WordPress admin settings to display the feed.</p>';
    }

    if (!ifs_check_license_status_on_load()) {
        $status = get_option('ifs_license_status', 'invalid');
        if ($status === 'expired') {
            return '<p class="s22-license-error" style="color: #d93838; font-weight: bold; padding: 12px; border: 1px dashed #d93838; background-color: #fff5f5; border-radius: 4px;">Error: Your license key has expired. Please renew your license in the WordPress admin settings.</p>';
        } else {
            return '<p class="s22-license-error" style="color: #d93838; font-weight: bold; padding: 12px; border: 1px dashed #d93838; background-color: #fff5f5; border-radius: 4px;">Error: Invalid license key. Please verify your license settings in the WordPress admin panel.</p>';
        }
    }

    $atts = shortcode_atts(array(
        'id' => 0
    ), $atts);

    $slider_id = intval($atts['id']);
    if (!$slider_id) {
        return '<p>Error: Invalid slider ID</p>';
    }

    $slider = get_option('ifs_slider_' . $slider_id);
    if (!$slider) {
        return '<p>Error: Slider not found</p>';
    }
    if (isset($slider['type']) && $slider['type'] === 'masonry') {
        $slider['type'] = 'gallery';
    }
    $license_plan = get_option('ifs_license_plan_name', '');
    $is_standard = (stripos($license_plan, 'standard') !== false);
    if ($is_standard && isset($slider['type']) && $slider['type'] === 'gallery') {
        $slider['type'] = 'slider';
    }

    // Enqueue necessary scripts and styles
    wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css');
    wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), null, true);
    wp_enqueue_style('ifs-style', plugin_dir_url(__FILE__) . 'assets/style.css', array(), filemtime(plugin_dir_path(__FILE__) . 'assets/style.css'));
    wp_enqueue_script('ifs-script', plugin_dir_url(__FILE__) . 'assets/slider.js', array('jquery', 'swiper-js'), filemtime(plugin_dir_path(__FILE__) . 'assets/slider.js'), true);

    // Prepare slider configuration
    $config = array(
        'id' => $slider_id,
        'type' => $slider['type'],
        'feed_type' => $slider['feed_type'],
        'height' => $slider['height'],
        'gallery_num_posts' => $slider['gallery_num_posts'],
        'gallery_load_more' => $slider['gallery_load_more'],
        'autoplay' => $slider['autoplay'],
        'navigation' => $slider['navigation'],
        'pagination' => $slider['pagination'],
        'mediaTypes' => $slider['media_types'],
        'slides_per_view' => $slider['slides_per_view'],
        'image_fit' => $slider['image_fit'],
        'hover_caption' => isset($slider['hover_caption']) ? $slider['hover_caption'] : 1,
        'gallery_load_more_text' => isset($slider['gallery_load_more_text']) ? $slider['gallery_load_more_text'] : 'Load More',
        'gallery_load_more_num' => isset($slider['gallery_load_more_num']) ? intval($slider['gallery_load_more_num']) : 4,
        'hiddenPosts' => get_option('ifs_hidden_posts_' . $slider_id, array())
    );

    if ($slider['feed_type'] === 'instagram') {
        $config['userId'] = get_option('ifs_user_id');
        $config['accessToken'] = get_option('ifs_access_token');
    }

    wp_localize_script('ifs-script', 'ifsSliderConfig', $config);

    $container_height = $slider['type'] === 'slider' ? ($slider['height'] . 'px') : 'auto';

    // Fetch custom styling settings from slider config
    $feed_bg = isset($slider['feed_bg']) ? $slider['feed_bg'] : 'transparent';
    $border_radius = isset($slider['border_radius']) ? intval($slider['border_radius']) : 8;
    $slider_gap = isset($slider['slider_gap']) ? intval($slider['slider_gap']) : 16;
    $hover_bg = isset($slider['hover_bg']) ? $slider['hover_bg'] : 'rgba(0,0,0,0.5)';
    $arrow_color = isset($slider['arrow_color']) ? $slider['arrow_color'] : '#1e62ec';
    $dot_color = isset($slider['dot_color']) ? $slider['dot_color'] : '#1e62ec';
    $gallery_load_more_text = isset($slider['gallery_load_more_text']) ? $slider['gallery_load_more_text'] : 'Load More';

    $border_width = isset($slider['border_width']) ? intval($slider['border_width']) : 1;
    $border_color = isset($slider['border_color']) ? $slider['border_color'] : 'rgba(0,0,0,0.03)';
    $border_hover_color = isset($slider['border_hover_color']) ? $slider['border_hover_color'] : 'rgba(0,0,0,0.06)';
    $follow_btn_bg = isset($slider['follow_btn_bg']) ? $slider['follow_btn_bg'] : '#0f1419';
    $follow_btn_text = isset($slider['follow_btn_text']) ? $slider['follow_btn_text'] : '#ffffff';
    $follow_btn_hover_bg = isset($slider['follow_btn_hover_bg']) ? $slider['follow_btn_hover_bg'] : '#4f46e5';
    $load_more_bg = isset($slider['load_more_bg']) ? $slider['load_more_bg'] : '#ffffff';
    $load_more_text = isset($slider['load_more_text']) ? $slider['load_more_text'] : '#0f1419';
    $load_more_hover_bg = isset($slider['load_more_hover_bg']) ? $slider['load_more_hover_bg'] : '#0f1419';

    $custom_css = isset($slider['custom_css']) ? $slider['custom_css'] : '';

    ob_start();
?>
    <style>
        #s22-slider-<?php echo esc_attr($slider_id); ?> {
            background-color: <?php echo esc_html($feed_bg); ?> !important;
        }
        #s22-slider-<?php echo esc_attr($slider_id); ?> .swiper-wrapper,
        #s22-slider-<?php echo esc_attr($slider_id); ?> .swiper-slide {
            overflow: visible !important;
        }
        #s22-slider-<?php echo esc_attr($slider_id); ?> .gallery-item,
        #s22-slider-<?php echo esc_attr($slider_id); ?> .swiper-slide,
        #s22-slider-<?php echo esc_attr($slider_id); ?> .gallery-item a,
        #s22-slider-<?php echo esc_attr($slider_id); ?> .swiper-slide a {
            border-radius: <?php echo esc_html($border_radius); ?>px !important;
        }
        #s22-slider-<?php echo esc_attr($slider_id); ?> .gallery-item .ifs-lightbox-link > img,
        #s22-slider-<?php echo esc_attr($slider_id); ?> .gallery-item .ifs-lightbox-link > video,
        #s22-slider-<?php echo esc_attr($slider_id); ?> .swiper-slide .ifs-lightbox-link > img,
        #s22-slider-<?php echo esc_attr($slider_id); ?> .swiper-slide .ifs-lightbox-link > video,
        #s22-slider-<?php echo esc_attr($slider_id); ?> .ifs-hover-overlay {
            border-radius: calc(<?php echo esc_html($border_radius); ?>px - <?php echo esc_html($border_width); ?>px) !important;
        }
        #s22-slider-<?php echo esc_attr($slider_id); ?> .gallery-item a,
        #s22-slider-<?php echo esc_attr($slider_id); ?> .swiper-slide a {
            box-sizing: border-box !important;
            border-width: <?php echo esc_html($border_width); ?>px !important;
            border-color: <?php echo esc_html($border_color); ?> !important;
            border-style: solid !important;
        }
        #s22-slider-<?php echo esc_attr($slider_id); ?> .gallery-item a:hover,
        #s22-slider-<?php echo esc_attr($slider_id); ?> .swiper-slide a:hover {
            border-color: <?php echo esc_html($border_hover_color); ?> !important;
        }
        #s22-slider-<?php echo esc_attr($slider_id); ?> .s22-ig-follow-btn {
            background: <?php echo esc_html($follow_btn_bg); ?> !important;
            color: <?php echo esc_html($follow_btn_text); ?> !important;
        }
        #s22-slider-<?php echo esc_attr($slider_id); ?> .s22-ig-follow-btn:hover {
            background: <?php echo esc_html($follow_btn_hover_bg); ?> !important;
            color: <?php echo esc_html($follow_btn_text); ?> !important;
        }
        #s22-slider-<?php echo esc_attr($slider_id); ?> .load-more-button {
            background: <?php echo esc_html($load_more_bg); ?> !important;
            color: <?php echo esc_html($load_more_text); ?> !important;
            border-color: <?php echo esc_html($load_more_text); ?> !important;
        }
        #s22-slider-<?php echo esc_attr($slider_id); ?> .load-more-button:hover {
            background: <?php echo esc_html($load_more_hover_bg); ?> !important;
            color: <?php echo esc_html($load_more_bg); ?> !important;
            border-color: <?php echo esc_html($load_more_hover_bg); ?> !important;
        }
        #s22-slider-<?php echo esc_attr($slider_id); ?> .instagram-gallery {
            grid-template-columns: repeat(<?php echo esc_html($slider['slides_per_view'] ?: 4); ?>, 1fr) !important;
            gap: <?php echo esc_html($slider_gap); ?>px !important;
        }
        @media screen and (max-width: 768px) {
            #s22-slider-<?php echo esc_attr($slider_id); ?> .instagram-gallery {
                grid-template-columns: repeat(<?php echo esc_html(min($slider['slides_per_view'] ?: 4, 3)); ?>, 1fr) !important;
            }
        }
        @media screen and (max-width: 480px) {
            #s22-slider-<?php echo esc_attr($slider_id); ?> .instagram-gallery {
                grid-template-columns: repeat(<?php echo esc_html(min($slider['slides_per_view'] ?: 4, 2)); ?>, 1fr) !important;
            }
        }

        #s22-slider-<?php echo esc_attr($slider_id); ?> .swiper-button-next,
        #s22-slider-<?php echo esc_attr($slider_id); ?> .swiper-button-prev {
            color: <?php echo esc_html($arrow_color); ?> !important;
        }
        #s22-slider-<?php echo esc_attr($slider_id); ?> .swiper-pagination-bullet-active {
            background: <?php echo esc_html($dot_color); ?> !important;
        }
        #s22-slider-<?php echo esc_attr($slider_id); ?> .ifs-hover-overlay {
            background-color: <?php echo esc_html($hover_bg); ?> !important;
        }
        /* Custom CSS Box */
        <?php echo $custom_css; ?>
    </style>
    <div class="s22-slider" id="s22-slider-<?php echo esc_attr($slider_id); ?>" style="height: <?php echo esc_attr($container_height); ?>;">
        <?php if ($slider['type'] === 'slider'): ?>
            <div class="swiper">
                <div class="swiper-wrapper">
                    <?php if ($slider['feed_type'] === 'instagram'): ?>
                        <!-- Instagram feed will be loaded via JavaScript -->
                        <div class="swiper-slide">
                            <div class="loading-placeholder">Loading Instagram feed...</div>
                        </div>
                    <?php else: ?>
                        <!-- Custom media will be loaded here -->
                        <div class="swiper-slide">
                            <div class="loading-placeholder">Loading media...</div>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($slider['navigation']): ?>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                <?php endif; ?>
                <?php if ($slider['pagination']): ?>
                    <div class="swiper-pagination"></div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="s22-instagram-gallery-container">
                <div class="instagram-gallery">
                    <?php if ($slider['feed_type'] === 'instagram'): ?>
                        <!-- Instagram gallery will be loaded via JavaScript -->
                        <div class="loading-placeholder">Loading Instagram gallery...</div>
                    <?php else: ?>
                        <!-- Custom gallery will be loaded here -->
                        <div class="loading-placeholder">Loading gallery...</div>
                    <?php endif; ?>
                    <?php if ($slider['gallery_load_more']): ?>
                        <button class="load-more-button"><?php echo esc_html($gallery_load_more_text); ?></button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php
    return ob_get_clean();
}
