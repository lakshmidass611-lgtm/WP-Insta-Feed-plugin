<?php
/**
 * Admin Dashboard View template for S22 Social Media Feed.
 */

if (!defined('ABSPATH')) {
    exit;
}

$sliders = get_option('ifs_saved_sliders', array());
$license_status = get_option('ifs_license_status', 'invalid');
$license_plan = get_option('ifs_license_plan_name', '');
$license_email = get_option('ifs_license_email', '');
$license_key = get_option('ifs_license_key', '');
$license_purchased = get_option('ifs_license_purchased_date', '');
$license_expiry = get_option('ifs_license_expiry_date', '');

$is_standard = (stripos($license_plan, 'standard') !== false);

$user_id = get_option('ifs_user_id', '');
$access_token = get_option('ifs_access_token', '');

$license_key = get_option('ifs_license_key', '');
$license_email = get_option('ifs_license_email', '');
$isConnected = (!empty($license_key) && !empty($license_email));

$active_tab = 'login';
if ($isConnected) {
    $active_tab = 'my-widgets';
}

if (isset($_GET['tab'])) {
    $tab_param = sanitize_key($_GET['tab']);
    if (in_array($tab_param, array('login', 'my-widgets', 'help-guide'))) {
        $active_tab = $tab_param;
    }
}
?>
<div class="wrap ifs-admin-wrap" style="margin-top: 20px;">
    
    <div class="dashboard-container">
        
        <!-- Top Header -->
        <header class="header-bar">
            <div class="brand-section">
                <div class="logo-container">S</div>
                <div class="brand-name">S22 Social Media <span class="premium-badge"><?php esc_html_e('Premium', 's22-social-media'); ?></span></div>
            </div>
            <div class="header-stats">
                <div class="header-stat-pill">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span id="header-widget-count"><?php echo sprintf(esc_html__('%d Sliders', 's22-social-media'), count($sliders)); ?></span>
                </div>
                <div class="header-stat-pill">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-.778.099-1.533.284-2.253"/></svg>
                    <span id="header-api-count"><?php echo (!empty($user_id) && !empty($access_token)) ? '1 API Connected' : '0 API Connected'; ?></span>
                </div>
                <?php 
                $status_class = ($license_status === 'valid' || $license_status === 'active') ? 'status-active' : 'status-expired';
                $status_text = ($license_status === 'valid' || $license_status === 'active') ? 'Active' : (($license_status === 'expired') ? 'Expired' : 'Inactive');
                ?>
                <div class="header-stat-pill <?php echo $status_class; ?>" id="header-status-pill">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="<?php echo ($license_status === 'invalid') ? 'display:none;' : ''; ?>"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span id="header-status-text"><?php echo esc_html($status_text); ?></span>
                </div>
            </div>
        </header>

        <!-- Navigation Tabs -->
        <nav class="tabs-container">
            <div class="tabs-menu">
                <button class="tab-link <?php echo ($active_tab === 'login') ? 'active' : ''; ?>" id="tab-btn-login" onclick="switchTab('login')" style="display: <?php echo $isConnected ? 'none' : 'flex'; ?>;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                    <?php esc_html_e('S22 Account', 's22-social-media'); ?>
                </button>
                <button class="tab-link <?php echo ($active_tab === 'my-widgets') ? 'active' : ''; ?> <?php echo ($license_status !== 'valid' && $license_status !== 'active' && $license_status !== 'expired') ? 'tab-disabled' : ''; ?>" id="tab-btn-my-widgets" onclick="switchTab('my-widgets')">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <?php esc_html_e('My Sliders', 's22-social-media'); ?>
                </button>
                <button class="tab-link <?php echo ($active_tab === 'help-guide') ? 'active' : ''; ?> <?php echo ($license_status !== 'valid' && $license_status !== 'active' && $license_status !== 'expired') ? 'tab-disabled' : ''; ?>" id="tab-btn-help-guide" onclick="switchTab('help-guide')">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <?php esc_html_e('Settings', 's22-social-media'); ?>
                </button>
            </div>
            <div class="version-tag">v<?php echo esc_html(get_option('ifs_version', '1.2')); ?></div>
        </nav>

        <!-- Main Content -->
        <main class="dashboard-content">

            <?php if ($license_status === 'expired'): ?>
                <div class="s22-expired-banner" style="background: #fef2f2; border: 1px solid #fee2e2; border-radius: 8px; padding: 16px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; width: 100%; box-sizing: border-box;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="background: #fee2e2; color: #ef4444; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; flex-shrink: 0;">!</div>
                        <div>
                            <h4 style="margin: 0; color: #991b1b; font-size: 15px; font-weight: 700;"><?php esc_html_e('Your license key has expired', 's22-social-media'); ?></h4>
                            <p style="margin: 2px 0 0 0; color: #7f1d1d; font-size: 13px;"><?php esc_html_e('Please renew your plan to reactivate premium features and display the feeds on your website.', 's22-social-media'); ?></p>
                        </div>
                    </div>
                    <a href="https://s22-plugify.vercel.app/customer/dashboard" target="_blank" class="btn-primary" style="background: #dc2626; color: white; border: none; text-decoration: none; display: inline-flex; align-items: center; height: 38px; padding: 0 16px; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer; justify-content: center; box-shadow: 0 2px 6px rgba(220, 38, 38, 0.2); transition: all 0.2s;"><?php esc_html_e('Renew Plan', 's22-social-media'); ?></a>
                </div>
            <?php endif; ?>

            <!-- ================= S22 PORTAL ACCOUNT PANEL ================= -->
            <div id="panel-login" class="tab-panel <?php echo ($active_tab === 'login') ? 'active' : ''; ?>">
                <div style="display: flex; justify-content: center; align-items: center; padding: 24px 0; width: 100%;">
                    
                    <!-- Glassmorphic Connection Card -->
                    <div class="stat-card" style="max-width: 580px; width: 100%; display: flex; flex-direction: column; gap: 20px; padding: 32px; background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(10px); border: 1px solid rgba(226, 232, 240, 0.8); border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); transition: var(--transition);">
                        
                        <?php 
                        $isConnected = ($license_status === 'valid' || $license_status === 'active' || $license_status === 'expired');
                        ?>
 
                        <!-- Form: Initially visible if not connected -->
                        <div id="login-form-container" style="display: <?php echo $isConnected ? 'none' : 'flex'; ?>; flex-direction: column; gap: 20px; width: 100%;">
                            <div>
                                <h3 style="font-size: 22px; font-weight: 800; color: #0f172a; margin: 0 0 6px 0; display: flex; align-items: center; gap: 8px;">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:24px; height:24px; color: var(--primary);"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 009 11.5c0-.733.056-1.45.166-2.152m1.94 11.516l.049-.09A13.929 13.929 0 0012 11.5c0-.733-.056-1.45-.166-2.152m0 0a5.002 5.002 0 01-9.224-2.28m9.224 2.28a5.002 5.002 0 009.224-2.28m-9.224 2.28V4.75L12 3l.75 1.75V9.348m-9.224-2.28a5.002 5.002 0 0110.47 0m0 0a5.002 5.002 0 017.978 4.75M9 11.5V16.5m3-5V16.5m0-5.5a2.5 2.5 0 00-5 0v5h5v-5z"/></svg>
                                    <?php esc_html_e('Connect S22 Portal', 's22-social-media'); ?>
                                </h3>
                                <p style="font-size: 13.5px; color: #64748b; line-height: 1.5; margin: 0;">
                                    <?php esc_html_e('Link your website to the S22 Feed Sync Engine to get automated real-time background feed updates, smart caching, and priority API quota limits.', 's22-social-media'); ?>
                                </p>
                            </div>
 
                            <!-- Error Message Banner -->
                            <div id="login-error-message" style="display: none; align-items: center; gap: 10px; padding: 12px 16px; background: #fef2f2; border: 1px solid #fee2e2; border-radius: var(--radius-sm); color: #991b1b; font-size: 13px; font-weight: 500;">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:18px; height:18px; flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span id="login-error-text"></span>
                            </div>
 
                            <div style="display: flex; flex-direction: column; gap: 16px;">
                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    <label class="form-label" style="font-weight: 600; font-size: 13px; color: #334155;"><?php esc_html_e('Portal Email Address', 's22-social-media'); ?></label>
                                    <input type="text" id="portal-email" class="form-input" placeholder="e.g. account@mybusiness.com" style="height: 42px;">
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    <label class="form-label" style="font-weight: 600; font-size: 13px; color: #334155;"><?php esc_html_e('S22 Access License Key', 's22-social-media'); ?></label>
                                    <input type="password" id="portal-license-key" class="form-input" placeholder="e.g. s22_live_••••••••••••••••" style="height: 42px;">
                                </div>
 
                                <button class="btn-primary" id="btn-cloud-connect" onclick="attemptLicenseVerify()" style="height: 46px; justify-content: center; font-size: 14.5px; font-weight: 700; margin-top: 8px; background: linear-gradient(135deg, var(--primary) 0%, #4f46e5 100%); border: none; border-radius: 8px; cursor: pointer; color: white; display: flex; align-items: center; gap: 8px; width: 100%; box-shadow: 0 4px 12px rgba(30, 98, 236, 0.25); transition: var(--transition);">
                                    <svg class="connect-spinner" fill="none" viewBox="0 0 24 24" style="width: 18px; height: 18px; display: none; animation: spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity: 0.25;"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" style="opacity: 0.75;"></path></svg>
                                    <span id="connect-btn-text"><?php esc_html_e('Connect Cloud Engine', 's22-social-media'); ?></span>
                                </button>
                                 
                                <!-- API Response Output Area -->
                                <div id="ifs-api-response-wrapper" style="display: none; margin-top: 14px; padding: 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; width: 100%;">
                                    <div style="margin-bottom: 12px;">
                                        <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px; font-family: sans-serif;"><?php esc_html_e( 'Request Sent Data:', 's22-social-media' ); ?></span>
                                        <pre id="ifs-api-request-output" style="font-size: 12px; color: #0f172a; white-space: pre-wrap; word-break: break-all; margin: 0; padding: 0; background: transparent; border: none; font-family: monospace;"></pre>
                                    </div>
                                    <div style="border-top: 1px solid #e2e8f0; padding-top: 10px;">
                                        <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px; font-family: sans-serif;"><?php esc_html_e( 'Response Received Data:', 's22-social-media' ); ?></span>
                                        <pre id="ifs-api-response-output" style="font-size: 12px; color: #0f172a; white-space: pre-wrap; word-break: break-all; margin: 0; padding: 0; background: transparent; border: none; font-family: monospace;"></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
 
            <!-- ================= MY SLIDERS TAB ================= -->
            <div id="panel-my-widgets" class="tab-panel <?php echo ($active_tab === 'my-widgets') ? 'active' : ''; ?>">
                
                <!-- Stats Summary Row -->
                <section class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon-wrapper purple">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/></svg>
                        </div>
                        <div class="stat-info">
                            <span class="stat-label"><?php esc_html_e('Sliders', 's22-social-media'); ?></span>
                            <span id="stat-widgets-count" class="stat-value"><?php echo count($sliders); ?></span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon-wrapper blue">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        </div>
                        <div class="stat-info">
                            <span class="stat-label"><?php esc_html_e('API Connected', 's22-social-media'); ?></span>
                            <span id="stat-api-count" class="stat-value">
                                <?php echo (!empty($user_id) && !empty($access_token)) ? '1' : '0'; ?>
                            </span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon-wrapper yellow">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="stat-info">
                            <span class="stat-label"><?php esc_html_e('Sync status', 's22-social-media'); ?></span>
                            <span class="stat-value" style="font-size:18px; color:var(--accent-green); margin-top:4px;">Excellent</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon-wrapper pink">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div class="stat-info">
                            <span class="stat-label"><?php esc_html_e('Platform', 's22-social-media'); ?></span>
                            <span class="stat-value" style="font-size:18px;">Instagram</span>
                        </div>
                    </div>
                </section>

                <!-- Search & Filters Bar -->
                <section class="controls-bar">
                    <div class="search-filter-group">
                        <div class="search-wrapper">
                            <svg class="search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" class="search-input" id="widget-search" placeholder="<?php esc_attr_e('Search sliders..', 's22-social-media'); ?>" oninput="filterSliders()">
                        </div>
                        <select class="select-dropdown" id="layout-filter" onchange="filterSliders()">
                            <option value="all"><?php esc_html_e('All Layouts', 's22-social-media'); ?></option>
                            <option value="slider"><?php esc_html_e('Slider', 's22-social-media'); ?></option>
                            <option value="gallery"><?php esc_html_e('Gallery', 's22-social-media'); ?></option>
                        </select>
                    </div>
                    <button class="btn-primary" onclick="openCreateModal()">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <?php esc_html_e('New Slider', 's22-social-media'); ?>
                    </button>
                </section>

                <!-- Saved Sliders List Table -->
                <section class="table-container">
                    <div class="table-header-row">
                        <h2 class="table-title"><?php esc_html_e('Feeds Sliders', 's22-social-media'); ?></h2>
                        <span class="table-badge-counter" id="table-row-counter">0 sliders</span>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Slider Name', 's22-social-media'); ?></th>
                                <th><?php esc_html_e('Layout', 's22-social-media'); ?></th>
                                <th><?php esc_html_e('Source Mode', 's22-social-media'); ?></th>
                                <th><?php esc_html_e('Shortcode', 's22-social-media'); ?></th>
                                <th><?php esc_html_e('Created', 's22-social-media'); ?></th>
                                <th><?php esc_html_e('Actions', 's22-social-media'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="sliders-table-body">
                            <!-- Populated dynamically by JS -->
                        </tbody>
                    </table>
                </section>
            </div>

            <!-- ================= HELP & CREDENTIAL SETTINGS TAB ================= -->
            <div id="panel-help-guide" class="tab-panel <?php echo ($active_tab === 'help-guide') ? 'active' : ''; ?>">
                
                <?php if ($license_status === 'invalid'): ?>
                    <div class="s22-expired-settings-banner" style="background: #fef2f2; border: 1px solid #fee2e2; border-radius: 8px; padding: 16px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; width: 100%; box-sizing: border-box;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="background: #fee2e2; color: #ef4444; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; flex-shrink: 0;">!</div>
                            <div>
                                <h4 style="margin: 0; color: #991b1b; font-size: 15px; font-weight: 700;"><?php esc_html_e('Your plan is expired', 's22-social-media'); ?></h4>
                                <p style="margin: 2px 0 0 0; color: #7f1d1d; font-size: 13px;"><?php esc_html_e('Please renew your plan to reactivate premium functionality.', 's22-social-media'); ?></p>
                            </div>
                        </div>
                        <a href="https://s22-plugify.vercel.app/customer/dashboard" target="_blank" class="btn-primary" style="background: #dc2626; color: white; border: none; text-decoration: none; display: inline-flex; align-items: center; height: 38px; padding: 0 16px; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer; justify-content: center; box-shadow: 0 2px 6px rgba(220, 38, 38, 0.2); transition: all 0.2s;"><?php esc_html_e('Renew Your Plan', 's22-social-media'); ?></a>
                    </div>
                <?php endif; ?>
                
                <!-- Account Info Panel (only displayed when connected) -->
                <div id="settings-account-info-container" style="display: <?php echo $isConnected ? 'block' : 'none'; ?>; margin-bottom: 32px; width: 100%;">
                    <div class="premium-connection-card">
                        
                        <div class="pcc-header">
                            <div class="pcc-header-left">
                                <div class="pcc-icon-glow">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </div>
                                <div class="pcc-title-block">
                                    <h3><?php esc_html_e('S22 Sync Engine Connection', 's22-social-media'); ?></h3>
                                    <p><?php esc_html_e('Your site is fully synced with S22 Portal for automated real-time Instagram feed updates.', 's22-social-media'); ?></p>
                                </div>
                            </div>
                            
                            <div class="pcc-header-actions">
                                <?php 
                                if ($license_status === 'expired') {
                                    $status_badge_class = 'pcc-badge-expired';
                                    $status_badge_text = 'Expired';
                                } elseif ($license_status === 'invalid') {
                                    $status_badge_class = 'pcc-badge-expired';
                                    $status_badge_text = 'Invalid / Expired';
                                } else {
                                    $status_badge_class = 'pcc-badge-active';
                                    $status_badge_text = 'Active & Secured';
                                }
                                ?>
                                <span id="settings-account-status" class="pcc-status-badge <?php echo $status_badge_class; ?>">
                                    <?php if ($status_badge_class === 'pcc-badge-active'): ?>
                                        <span class="pcc-pulse-dot"></span>
                                    <?php endif; ?>
                                    <span class="pcc-status-text-val"><?php echo esc_html($status_badge_text); ?></span>
                                </span>
                                
                                <button class="btn-pcc-disconnect" onclick="disconnectLicense()" title="<?php esc_attr_e('Disconnect website from S22 Portal', 's22-social-media'); ?>">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    <?php esc_html_e('Disconnect', 's22-social-media'); ?>
                                </button>
                            </div>
                        </div>

                        <!-- Details Grid -->
                        <div class="pcc-details-grid">
                            
                            <!-- Email -->
                            <div class="pcc-detail-cell">
                                <div class="pdc-icon">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <div class="pdc-info">
                                    <span class="pdc-label"><?php esc_html_e('Email Account', 's22-social-media'); ?></span>
                                    <span id="connected-email" class="pdc-value"><?php echo esc_html($license_email); ?></span>
                                </div>
                            </div>

                            <!-- License -->
                            <div class="pcc-detail-cell">
                                <div class="pdc-icon">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m-5 4a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3zm0 0v6a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                </div>
                                <div class="pdc-info">
                                    <span class="pdc-label"><?php esc_html_e('Access License Key', 's22-social-media'); ?></span>
                                    <span id="connected-license" class="pdc-value monospace">
                                        <?php echo esc_html(substr($license_key, 0, min(strlen($license_key), 7)) . '••••••••••••'); ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Plan -->
                            <div class="pcc-detail-cell">
                                <div class="pdc-icon">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                </div>
                                <div class="pdc-info">
                                    <span class="pdc-label"><?php esc_html_e('Subscription Tier', 's22-social-media'); ?></span>
                                    <div>
                                        <span class="sync-tier-plan-display pdc-tier-badge">
                                            <?php echo esc_html($license_plan ?: 'Enterprise Premium'); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Purchase Date -->
                            <div class="pcc-detail-cell">
                                <div class="pdc-icon">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <div class="pdc-info">
                                    <span class="pdc-label"><?php esc_html_e('Purchase Date', 's22-social-media'); ?></span>
                                    <span id="connected-purchase-date" class="pdc-value"><?php echo esc_html($license_purchased ?: '-'); ?></span>
                                </div>
                            </div>

                            <!-- Expiry Date -->
                            <div class="pcc-detail-cell">
                                <div class="pdc-icon">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div class="pdc-info">
                                    <span class="pdc-label"><?php esc_html_e('Expiry Date', 's22-social-media'); ?></span>
                                    <span id="connected-expiry-date" class="pdc-value"><?php echo esc_html($license_expiry ?: '-'); ?></span>
                                </div>
                            </div>

                        </div>

                        <!-- API JSON logs section: Collapsible drawer -->
                        <div class="pcc-api-logs-section">
                            <button type="button" class="btn-pcc-logs-toggle" onclick="toggleApiLogsDrawer()">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" class="pcc-arrow-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                <span><?php esc_html_e('Developer Settings: View Raw Portal API Response', 's22-social-media'); ?></span>
                            </button>
                            
                            <div class="pcc-logs-drawer" id="ifs-api-response-wrapper-success" style="display: none;">
                                <div class="pcc-terminal-header">
                                    <div class="pcc-terminal-dots">
                                        <span class="dot red"></span>
                                        <span class="dot yellow"></span>
                                        <span class="dot green"></span>
                                    </div>
                                    <div class="pcc-terminal-title">s22_portal_api_response.json</div>
                                    <button class="btn-pcc-copy-json" onclick="copyApiJsonToClipboard()" title="<?php esc_attr_e('Copy JSON to clipboard', 's22-social-media'); ?>">
                                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                        <?php esc_html_e('Copy JSON', 's22-social-media'); ?>
                                    </button>
                                </div>
                                <div class="pcc-terminal-body">
                                    <pre id="ifs-api-response-output-success" class="pcc-terminal-pre"></pre>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="help-grid">
                    
                    <div class="help-card">
                        <h3 class="help-section-title">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <?php esc_html_e('Instagram Credentials Setup', 's22-social-media'); ?>
                        </h3>
                        
                        <form method="post" action="options.php" style="background:#f8fafc; border:1px solid #e2e8f0; padding:20px; border-radius:8px; display:flex; flex-direction:column; gap:16px;">
                            <?php settings_fields('ifs_settings_group'); ?>
                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;"><?php esc_html_e('Instagram User ID', 's22-social-media'); ?></label>
                                <input type="text" class="form-input" name="ifs_user_id" value="<?php echo esc_attr(get_option('ifs_user_id')); ?>" placeholder="e.g. 17841401234567890">
                            </div>
                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;"><?php esc_html_e('Access Token', 's22-social-media'); ?></label>
                                <input type="password" class="form-input" name="ifs_access_token" value="<?php echo esc_attr(get_option('ifs_access_token')); ?>" placeholder="IGQWRN...">
                            </div>
                            <?php submit_button(__('Save Settings', 's22-social-media'), 'primary', 'submit', false); ?>
                        </form>
                    </div>

                    <div class="help-card">
                        <h3 class="help-section-title">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                            <?php esc_html_e('Integration Guide', 's22-social-media'); ?>
                        </h3>
                        <div class="help-step-list">
                            <div class="help-step">
                                <span class="help-step-num">1</span>
                                <div class="help-step-body">
                                    <span class="help-step-title"><?php esc_html_e('Connect Instagram Business Account', 's22-social-media'); ?></span>
                                    <span class="help-step-desc"><?php esc_html_e('Ensure you have a Facebook Developer App set up with Instagram Basic Display or Graph API access to fetch User ID and Access Token.', 's22-social-media'); ?></span>
                                </div>
                            </div>
                            <div class="help-step">
                                <span class="help-step-num">2</span>
                                <div class="help-step-body">
                                    <span class="help-step-title"><?php esc_html_e('Create Sliders or Galleries', 's22-social-media'); ?></span>
                                    <span class="help-step-desc"><?php esc_html_e('Open the "My Sliders" tab and click "New Slider" to launch the setup wizard. Copy the shortcode.', 's22-social-media'); ?></span>
                                </div>
                            </div>
                            <div class="help-step">
                                <span class="help-step-num">3</span>
                                <div class="help-step-body">
                                    <span class="help-step-title"><?php esc_html_e('Moderate Live Posts', 's22-social-media'); ?></span>
                                    <span class="help-step-desc"><?php esc_html_e('Use the "Moderation" tab to hide or show individual posts dynamically on the frontend.', 's22-social-media'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- ================= WIDGET CREATE MODAL WIZARD ================= -->
    <div class="modal-backdrop" id="create-modal">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <div>
                    <h3 class="modal-title"><?php esc_html_e('Create New Slider', 's22-social-media'); ?></h3>
                    <p style="margin:0; font-size:12px; color:var(--text-muted);" id="create-step-subtitle"><?php esc_html_e('Guided slider config setup wizard', 's22-social-media'); ?></p>
                </div>
                <button class="modal-close" onclick="closeCreateModal()">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Progress Tracker -->
            <div class="setup-progress-container">
                <div class="setup-progress-steps">
                    <!-- Step 1 -->
                    <div class="setup-step flex-1">
                        <div class="setup-step-node">
                            <button type="button" class="step-circle active" id="create-label-icon-1" onclick="clickCreateStep(1)">1</button>
                            <span class="step-title active" id="create-label-1"><?php esc_html_e('General', 's22-social-media'); ?></span>
                        </div>
                        <div class="setup-step-connector">
                            <div class="connector-line" id="create-connector-1"></div>
                        </div>
                    </div>
                    <!-- Step 2 -->
                    <div class="setup-step flex-1">
                        <div class="setup-step-node">
                            <button type="button" class="step-circle" id="create-label-icon-2" onclick="clickCreateStep(2)">2</button>
                            <span class="step-title" id="create-label-2"><?php esc_html_e('Layout Type', 's22-social-media'); ?></span>
                        </div>
                        <div class="setup-step-connector">
                            <div class="connector-line" id="create-connector-2"></div>
                        </div>
                    </div>
                    <!-- Step 3 -->
                    <div class="setup-step flex-1">
                        <div class="setup-step-node">
                            <button type="button" class="step-circle" id="create-label-icon-3" onclick="clickCreateStep(3)">3</button>
                            <span class="step-title" id="create-label-3"><?php esc_html_e('Options', 's22-social-media'); ?></span>
                        </div>
                        <div class="setup-step-connector">
                            <div class="connector-line" id="create-connector-3"></div>
                        </div>
                    </div>
                    <!-- Step 4 -->
                    <div class="setup-step">
                        <div class="setup-step-node">
                            <button type="button" class="step-circle" id="create-label-icon-4" onclick="clickCreateStep(4)">4</button>
                            <span class="step-title" id="create-label-4"><?php esc_html_e('Preview', 's22-social-media'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                
                <!-- STEP 1: General Details -->
                <div class="create-step-content active" id="create-step-1">
                    <div class="form-group">
                        <label class="form-label" for="slider-name"><?php esc_html_e('Slider Name', 's22-social-media'); ?> <span style="color:var(--accent-red);">*</span></label>
                        <input type="text" class="form-input" id="slider-name" placeholder="e.g. My Website Instagram Slider" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e('Supported Media Types', 's22-social-media'); ?></label>
                        
                        <!-- Hidden checkboxes to preserve legacy JS state logic -->
                        <input type="checkbox" id="media-images" checked style="display:none;">
                        <input type="checkbox" id="media-videos" checked style="display:none;">

                        <div class="media-card-grid">
                            <!-- Photos Card Selector -->
                            <div class="media-type-card active" id="media-card-images" onclick="toggleMediaCard('images')">
                                <div class="mtc-icon">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <div class="mtc-info">
                                    <span class="mtc-title"><?php esc_html_e('Include Photos', 's22-social-media'); ?></span>
                                    <span class="mtc-desc"><?php esc_html_e('Standard grid & carousel image posts', 's22-social-media'); ?></span>
                                </div>
                                <div class="mtc-checkbox"></div>
                            </div>

                            <!-- Videos Card Selector -->
                            <div class="media-type-card active" id="media-card-videos" onclick="toggleMediaCard('videos')">
                                <div class="mtc-icon">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </div>
                                <div class="mtc-info">
                                    <span class="mtc-title"><?php esc_html_e('Include Videos', 's22-social-media'); ?></span>
                                    <span class="mtc-desc"><?php esc_html_e('Render video playback items dynamically', 's22-social-media'); ?></span>
                                </div>
                                <div class="mtc-checkbox"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Layout Style Selection -->
                <input type="hidden" id="wizard-layout-val" value="slider">
                <div class="create-step-content" id="create-step-2">
                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e('Select Layout Template', 's22-social-media'); ?></label>
                        <div class="layout-card-grid">
                            
                            <!-- Slider card -->
                            <div class="layout-card active" id="layout-card-slider" onclick="selectCreateLayout('slider')">
                                <div class="layout-card-preview">
                                    <div class="lcp-slider">
                                        <span class="lcp-arrow">‹</span><span class="lcp-slide"></span><span class="lcp-arrow">›</span>
                                    </div>
                                </div>
                                <div class="layout-card-label"><?php esc_html_e('Slider Carousel', 's22-social-media'); ?></div>
                                <div class="layout-card-desc"><?php esc_html_e('Horizontal swiping slides', 's22-social-media'); ?></div>
                                <span class="layout-card-check">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </span>
                            </div>

                            <!-- Gallery card -->
                            <div class="layout-card <?php echo $is_standard ? 'premium-only-card' : ''; ?>" id="layout-card-gallery" onclick="selectCreateLayout('gallery')">
                                <?php if ($is_standard): ?>
                                    <span class="premium-lock-badge">
                                        <svg fill="currentColor" viewBox="0 0 24 24" style="width:12px;height:12px;margin-right:2px;vertical-align:middle;"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
                                        Premium
                                    </span>
                                <?php endif; ?>
                                <div class="layout-card-preview">
                                    <div class="lcp-gallery">
                                        <span></span><span></span><span></span>
                                        <span></span><span></span><span></span>
                                    </div>
                                </div>
                                <div class="layout-card-label"><?php esc_html_e('Gallery Grid', 's22-social-media'); ?></div>
                                <div class="layout-card-desc"><?php esc_html_e('Responsive columns layout', 's22-social-media'); ?></div>
                                <span class="layout-card-check">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </span>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- STEP 3: Detail Configuration Options -->
                <div class="create-step-content" id="create-step-3">
                    
                    <!-- Common Layout Configurations -->
                    <div style="display:flex; gap:16px; margin-bottom: 12px;">
                        <div class="form-group" style="flex: 1; margin-bottom: 12px;">
                            <label class="form-label" for="slider-perview"><?php esc_html_e('Columns / Items per Row', 's22-social-media'); ?></label>
                            <input type="number" class="form-input" id="slider-perview" value="4" min="1" max="10" style="width:100%; max-width:none;">
                        </div>
                        <div class="form-group" style="flex: 1; margin-bottom: 12px;">
                            <label class="form-label" for="slider-fit"><?php esc_html_e('Image Fit Mode', 's22-social-media'); ?></label>
                            <select class="select-dropdown" id="slider-fit" style="width:100%; max-width:none;">
                                <option value="cover"><?php esc_html_e('Cover (Fill)', 's22-social-media'); ?></option>
                                <option value="contain"><?php esc_html_e('Contain (Aspect)', 's22-social-media'); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Slider Layout Configurations -->
                    <div class="wizard-slider-only" style="display:flex; flex-direction:column; gap:12px;">
                        <div style="display:flex; gap:16px;">
                            <div class="form-group" style="flex: 1; margin-bottom: 12px;">
                                <label class="form-label" for="slider-height"><?php esc_html_e('Slider Height (px)', 's22-social-media'); ?></label>
                                <input type="number" class="form-input" id="slider-height" value="500" min="200" style="width:100%; max-width:none;">
                            </div>
                        </div>
                        
                        <!-- Toggle switch: Autoplay -->
                        <div class="toggle-switch-group">
                            <div class="toggle-switch-label" onclick="clickToggleSwitch('slider-autoplay')">
                                <span class="tsl-title"><?php esc_html_e('Autoplay Carousel', 's22-social-media'); ?></span>
                                <span class="tsl-desc"><?php esc_html_e('Automatically rotate carousel slides over time', 's22-social-media'); ?></span>
                            </div>
                            <label class="switch-control">
                                <input type="checkbox" id="slider-autoplay" checked>
                                <span class="switch-slider"></span>
                            </label>
                        </div>

                        <!-- Toggle switch: Navigation -->
                        <div class="toggle-switch-group">
                            <div class="toggle-switch-label" onclick="clickToggleSwitch('slider-navigation')">
                                <span class="tsl-title"><?php esc_html_e('Navigation Arrows', 's22-social-media'); ?></span>
                                <span class="tsl-desc"><?php esc_html_e('Show next/previous navigation arrows on the slider', 's22-social-media'); ?></span>
                            </div>
                            <label class="switch-control">
                                <input type="checkbox" id="slider-navigation" checked>
                                <span class="switch-slider"></span>
                            </label>
                        </div>

                        <!-- Toggle switch: Pagination -->
                        <div class="toggle-switch-group">
                            <div class="toggle-switch-label" onclick="clickToggleSwitch('slider-pagination')">
                                <span class="tsl-title"><?php esc_html_e('Pagination Bullets', 's22-social-media'); ?></span>
                                <span class="tsl-desc"><?php esc_html_e('Show indicator dots/bullets at bottom of slider', 's22-social-media'); ?></span>
                            </div>
                            <label class="switch-control">
                                <input type="checkbox" id="slider-pagination" checked>
                                <span class="switch-slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Gallery Layout Configurations -->
                    <div class="wizard-gallery-only" style="display:flex; flex-direction:column; gap:12px;">
                        <div class="form-group">
                            <label class="form-label" for="gallery-posts"><?php esc_html_e('Number of Posts to Show', 's22-social-media'); ?></label>
                            <input type="number" class="form-input" id="gallery-posts" value="9" min="1" max="50" style="max-width:120px;">
                        </div>
                        
                        <!-- Toggle switch: Gallery Load More -->
                        <div class="toggle-switch-group">
                            <div class="toggle-switch-label" onclick="clickToggleSwitch('gallery-loadmore')">
                                <span class="tsl-title"><?php esc_html_e('Show Load More Button', 's22-social-media'); ?></span>
                                <span class="tsl-desc"><?php esc_html_e('Render button at grid footer to query older posts', 's22-social-media'); ?></span>
                            </div>
                            <label class="switch-control">
                                <input type="checkbox" id="gallery-loadmore" checked>
                                <span class="switch-slider"></span>
                            </label>
                        </div>

                        <!-- Load More Button Configuration Inputs -->
                        <div id="gallery-loadmore-settings" style="display: flex; gap: 16px; margin-top: 12px; flex-wrap: wrap;">
                            <div class="form-group" style="flex: 1; min-width: 180px; margin: 0;">
                                <label class="form-label" for="gallery-loadmore-text-label"><?php esc_html_e('Load More Button Custom Text', 's22-social-media'); ?></label>
                                <input type="text" class="form-input" id="gallery-loadmore-text-label" value="Load More" placeholder="e.g. View More">
                            </div>
                            <div class="form-group" style="flex: 1; min-width: 180px; margin: 0;">
                                <label class="form-label" for="gallery-loadmore-num"><?php esc_html_e('Posts to Load on Click', 's22-social-media'); ?></label>
                                <input type="number" class="form-input" id="gallery-loadmore-num" value="4" min="1" max="50" style="width:100%;">
                            </div>
                        </div>
                    </div>

                    <!-- Scoped Styling settings inside step 3 -->
                    <div class="wizard-styles-section" style="margin-top: 24px; border-top: 1px dashed var(--border); padding-top: 20px;">
                        <h4 style="margin: 0 0 16px 0; font-size: 14px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:16px; height:16px; color:var(--primary);"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                            <?php esc_html_e('Custom Styling Settings', 's22-social-media'); ?>
                        </h4>
                        
                        <!-- Row 1: Background, Radius, Gap -->
                        <div style="display:flex; gap:16px; margin-bottom: 12px; flex-wrap: wrap;">
                            <div class="form-group" style="flex: 1; min-width: 180px; margin: 0;">
                                <label class="form-label" for="widget-feed-bg"><?php esc_html_e('Background Color', 's22-social-media'); ?></label>
                                <div style="display: flex; gap: 8px; align-items: center; width: 100%;">
                                    <input type="text" class="form-input" id="widget-feed-bg" value="transparent" placeholder="e.g. transparent or #fff" style="flex: 1; min-width: 0;">
                                    <input type="color" id="widget-feed-bg-picker" value="#ffffff" style="width: 42px; height: 42px; padding: 2px; border: 1px solid var(--border); border-radius: var(--radius-sm); cursor: pointer; background: white; flex-shrink: 0;" title="<?php esc_attr_e('Choose color', 's22-social-media'); ?>">
                                </div>
                            </div>
                            <div class="form-group" style="flex: 1; min-width: 180px; margin: 0;">
                                <label class="form-label" for="widget-border-radius"><?php esc_html_e('Post Border Radius (px)', 's22-social-media'); ?></label>
                                <input type="number" class="form-input" id="widget-border-radius" value="8" min="0" max="50" style="width:100%;">
                            </div>
                            <div class="form-group" style="flex: 1; min-width: 180px; margin: 0;">
                                <label class="form-label" for="widget-slider-gap"><?php esc_html_e('Space Between Posts (px)', 's22-social-media'); ?></label>
                                <input type="number" class="form-input" id="widget-slider-gap" value="16" min="0" max="100" style="width:100%;">
                            </div>
                        </div>

                        <!-- Row 2: Hover Color & Hover Overlay Checkbox -->
                        <div style="display:flex; gap:16px; margin-bottom: 12px; flex-wrap: wrap;">
                            <div class="form-group" style="flex: 1.5; min-width: 180px; margin: 0;">
                                <label class="form-label" for="widget-hover-bg"><?php esc_html_e('Hover Overlay Background', 's22-social-media'); ?></label>
                                <div style="display: flex; gap: 8px; align-items: center; width: 100%;">
                                    <input type="text" class="form-input" id="widget-hover-bg" value="rgba(0,0,0,0.5)" placeholder="rgba(0,0,0,0.5)" style="flex: 1; min-width: 0;">
                                    <input type="color" id="widget-hover-bg-picker" value="#000000" style="width: 42px; height: 42px; padding: 2px; border: 1px solid var(--border); border-radius: var(--radius-sm); cursor: pointer; background: white; flex-shrink: 0;" title="<?php esc_attr_e('Choose color', 's22-social-media'); ?>">
                                </div>
                            </div>
                            <div class="form-group" style="flex: 1; min-width: 180px; margin: 0; display:flex; flex-direction:column; justify-content:flex-end;">
                                <label class="form-label" style="font-weight: 600; margin-bottom: 8px;"><?php esc_html_e('Show Hover Caption', 's22-social-media'); ?></label>
                                <div style="display:flex; align-items:center; height:42px;">
                                    <input type="checkbox" id="widget-hover-caption" checked style="width:20px; height:20px; margin:0 8px 0 0; cursor:pointer;">
                                    <span style="font-size:12px; color:var(--text-muted);"><?php esc_html_e('Caption on hover', 's22-social-media'); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Row 3: Arrows/Dots Colors (Only for Slider layouts) -->
                        <div class="wizard-slider-only" style="display:flex; gap:16px; margin-bottom: 12px; flex-wrap: wrap;">
                            <div class="form-group" style="flex: 1; min-width: 180px; margin: 0;">
                                <label class="form-label" for="widget-arrow-color"><?php esc_html_e('Swiper Arrows Color', 's22-social-media'); ?></label>
                                <div style="display: flex; gap: 8px; align-items: center; width: 100%;">
                                    <input type="text" class="form-input" id="widget-arrow-color" value="#1e62ec" placeholder="#1e62ec" style="flex: 1; min-width: 0;">
                                    <input type="color" id="widget-arrow-color-picker" value="#1e62ec" style="width: 42px; height: 42px; padding: 2px; border: 1px solid var(--border); border-radius: var(--radius-sm); cursor: pointer; background: white; flex-shrink: 0;" title="<?php esc_attr_e('Choose color', 's22-social-media'); ?>">
                                </div>
                            </div>
                            <div class="form-group" style="flex: 1; min-width: 180px; margin: 0;">
                                <label class="form-label" for="widget-dot-color"><?php esc_html_e('Swiper Pagination Dots Color', 's22-social-media'); ?></label>
                                <div style="display: flex; gap: 8px; align-items: center; width: 100%;">
                                    <input type="text" class="form-input" id="widget-dot-color" value="#1e62ec" placeholder="#1e62ec" style="flex: 1; min-width: 0;">
                                    <input type="color" id="widget-dot-color-picker" value="#1e62ec" style="width: 42px; height: 42px; padding: 2px; border: 1px solid var(--border); border-radius: var(--radius-sm); cursor: pointer; background: white; flex-shrink: 0;" title="<?php esc_attr_e('Choose color', 's22-social-media'); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Row: Post Border Styles -->
                        <div style="display:flex; gap:16px; margin-bottom: 12px; flex-wrap: wrap;">
                            <div class="form-group" style="flex: 1; min-width: 180px; margin: 0;">
                                <label class="form-label" for="widget-border-width"><?php esc_html_e('Post Border Width (px)', 's22-social-media'); ?></label>
                                <input type="number" class="form-input" id="widget-border-width" value="1" min="0" max="10" style="width:100%;">
                            </div>
                            <div class="form-group" style="flex: 1.5; min-width: 180px; margin: 0;">
                                <label class="form-label" for="widget-border-color"><?php esc_html_e('Post Border Color', 's22-social-media'); ?></label>
                                <div style="display: flex; gap: 8px; align-items: center; width: 100%;">
                                    <input type="text" class="form-input" id="widget-border-color" value="rgba(0,0,0,0.03)" placeholder="rgba(0,0,0,0.03)" style="flex: 1; min-width: 0;">
                                    <input type="color" id="widget-border-color-picker" value="#000000" style="width: 42px; height: 42px; padding: 2px; border: 1px solid var(--border); border-radius: var(--radius-sm); cursor: pointer; background: white; flex-shrink: 0;" title="<?php esc_attr_e('Choose color', 's22-social-media'); ?>">
                                </div>
                            </div>
                            <div class="form-group" style="flex: 1.5; min-width: 180px; margin: 0;">
                                <label class="form-label" for="widget-border-hover-color"><?php esc_html_e('Post Border Hover Color', 's22-social-media'); ?></label>
                                <div style="display: flex; gap: 8px; align-items: center; width: 100%;">
                                    <input type="text" class="form-input" id="widget-border-hover-color" value="rgba(0,0,0,0.06)" placeholder="rgba(0,0,0,0.06)" style="flex: 1; min-width: 0;">
                                    <input type="color" id="widget-border-hover-color-picker" value="#000000" style="width: 42px; height: 42px; padding: 2px; border: 1px solid var(--border); border-radius: var(--radius-sm); cursor: pointer; background: white; flex-shrink: 0;" title="<?php esc_attr_e('Choose color', 's22-social-media'); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Row: Follow Button Styles -->
                        <div style="display:flex; gap:16px; margin-bottom: 12px; flex-wrap: wrap;">
                            <div class="form-group" style="flex: 1; min-width: 180px; margin: 0;">
                                <label class="form-label" for="widget-follow-btn-bg"><?php esc_html_e('Follow Button BG', 's22-social-media'); ?></label>
                                <div style="display: flex; gap: 8px; align-items: center; width: 100%;">
                                    <input type="text" class="form-input" id="widget-follow-btn-bg" value="#0f1419" placeholder="#0f1419" style="flex: 1; min-width: 0;">
                                    <input type="color" id="widget-follow-btn-bg-picker" value="#0f1419" style="width: 42px; height: 42px; padding: 2px; border: 1px solid var(--border); border-radius: var(--radius-sm); cursor: pointer; background: white; flex-shrink: 0;" title="<?php esc_attr_e('Choose color', 's22-social-media'); ?>">
                                </div>
                            </div>
                            <div class="form-group" style="flex: 1; min-width: 180px; margin: 0;">
                                <label class="form-label" for="widget-follow-btn-text"><?php esc_html_e('Follow Button Text', 's22-social-media'); ?></label>
                                <div style="display: flex; gap: 8px; align-items: center; width: 100%;">
                                    <input type="text" class="form-input" id="widget-follow-btn-text" value="#ffffff" placeholder="#ffffff" style="flex: 1; min-width: 0;">
                                    <input type="color" id="widget-follow-btn-text-picker" value="#ffffff" style="width: 42px; height: 42px; padding: 2px; border: 1px solid var(--border); border-radius: var(--radius-sm); cursor: pointer; background: white; flex-shrink: 0;" title="<?php esc_attr_e('Choose color', 's22-social-media'); ?>">
                                </div>
                            </div>
                            <div class="form-group" style="flex: 1; min-width: 180px; margin: 0;">
                                <label class="form-label" for="widget-follow-btn-hover-bg"><?php esc_html_e('Follow Button Hover BG', 's22-social-media'); ?></label>
                                <div style="display: flex; gap: 8px; align-items: center; width: 100%;">
                                    <input type="text" class="form-input" id="widget-follow-btn-hover-bg" value="#4f46e5" placeholder="#4f46e5" style="flex: 1; min-width: 0;">
                                    <input type="color" id="widget-follow-btn-hover-bg-picker" value="#4f46e5" style="width: 42px; height: 42px; padding: 2px; border: 1px solid var(--border); border-radius: var(--radius-sm); cursor: pointer; background: white; flex-shrink: 0;" title="<?php esc_attr_e('Choose color', 's22-social-media'); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Row: Load More Button Styles -->
                        <div style="display:flex; gap:16px; margin-bottom: 12px; flex-wrap: wrap;">
                            <div class="form-group" style="flex: 1; min-width: 180px; margin: 0;">
                                <label class="form-label" for="widget-load-more-bg"><?php esc_html_e('Load More Button BG', 's22-social-media'); ?></label>
                                <div style="display: flex; gap: 8px; align-items: center; width: 100%;">
                                    <input type="text" class="form-input" id="widget-load-more-bg" value="#ffffff" placeholder="#ffffff" style="flex: 1; min-width: 0;">
                                    <input type="color" id="widget-load-more-bg-picker" value="#ffffff" style="width: 42px; height: 42px; padding: 2px; border: 1px solid var(--border); border-radius: var(--radius-sm); cursor: pointer; background: white; flex-shrink: 0;" title="<?php esc_attr_e('Choose color', 's22-social-media'); ?>">
                                </div>
                            </div>
                            <div class="form-group" style="flex: 1; min-width: 180px; margin: 0;">
                                <label class="form-label" for="widget-load-more-text"><?php esc_html_e('Load More Button Text', 's22-social-media'); ?></label>
                                <div style="display: flex; gap: 8px; align-items: center; width: 100%;">
                                    <input type="text" class="form-input" id="widget-load-more-text" value="#0f1419" placeholder="#0f1419" style="flex: 1; min-width: 0;">
                                    <input type="color" id="widget-load-more-text-picker" value="#0f1419" style="width: 42px; height: 42px; padding: 2px; border: 1px solid var(--border); border-radius: var(--radius-sm); cursor: pointer; background: white; flex-shrink: 0;" title="<?php esc_attr_e('Choose color', 's22-social-media'); ?>">
                                </div>
                            </div>
                            <div class="form-group" style="flex: 1; min-width: 180px; margin: 0;">
                                <label class="form-label" for="widget-load-more-hover-bg"><?php esc_html_e('Load More Hover BG', 's22-social-media'); ?></label>
                                <div style="display: flex; gap: 8px; align-items: center; width: 100%;">
                                    <input type="text" class="form-input" id="widget-load-more-hover-bg" value="#0f1419" placeholder="#0f1419" style="flex: 1; min-width: 0;">
                                    <input type="color" id="widget-load-more-hover-bg-picker" value="#0f1419" style="width: 42px; height: 42px; padding: 2px; border: 1px solid var(--border); border-radius: var(--radius-sm); cursor: pointer; background: white; flex-shrink: 0;" title="<?php esc_attr_e('Choose color', 's22-social-media'); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Row 4: Custom CSS overrides textarea -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" for="widget-custom-css"><?php esc_html_e('Custom CSS overrides', 's22-social-media'); ?></label>
                            <textarea class="form-input" id="widget-custom-css" rows="3" style="height:auto; font-family:monospace; padding:10px; font-size:12px; width:100%;" placeholder="/* Scoped selector: #s22-slider-ID */"></textarea>
                        </div>
                    </div>

                </div>

                <!-- STEP 4: Live Mock Preview -->
                <div class="create-step-content" id="create-step-4">
                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e('Interactive Mock Preview', 's22-social-media'); ?></label>
                        <div class="live-preview-box" id="wizard-live-preview-box">
                            <!-- Visual mock rendered dynamically by JS -->
                        </div>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button class="btn-secondary" id="btn-create-cancel" onclick="closeCreateModal()"><?php esc_html_e('Cancel', 's22-social-media'); ?></button>
                <button class="btn-secondary" id="btn-create-prev" onclick="prevCreateStep()" style="display:none;">&larr; <?php esc_html_e('Previous', 's22-social-media'); ?></button>
                <button class="btn-primary" id="btn-create-next" onclick="nextCreateStep()"><?php esc_html_e('Next Step', 's22-social-media'); ?> &rarr;</button>
                <button class="btn-primary" id="btn-create-submit" onclick="saveSlider()" style="display:none; background:var(--accent-green) !important; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);"><?php esc_html_e('Create Slider', 's22-social-media'); ?></button>
            </div>

        </div>
    </div>

    <!-- Toast Banner Notifier -->
    <div class="toast" id="toast-notify">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" style="width:16px;height:16px;color:var(--accent-green);"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        <span id="toast-message"></span>
    </div>

</div>
