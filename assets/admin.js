jQuery(document).ready(function ($) {
    // State variables from localized data
    let sliders = ifsAdmin.sliders || {};
    let licenseStatus = ifsAdmin.licenseStatus || 'invalid';
    let licensePlan = ifsAdmin.licensePlan || '';
    let licenseEmail = ifsAdmin.licenseEmail || '';
    let licenseKey = ifsAdmin.licenseKey || '';
    let licenseExpiryDate = ifsAdmin.licenseExpiryDate || '';
    
    let activeEditingSliderId = null;
    let currentCreateStep = 1;

    // Toast notify helper
    function showToast(message) {
        const toast = $('#toast-notify');
        $('#toast-message').text(message);
        toast.addClass('show');
        setTimeout(() => {
            toast.removeClass('show');
        }, 2500);
    }

    // Escape HTML utility
    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    // Switch Tab Panel
    window.switchTab = function (tabId) {
        const isConnected = (licenseKey !== '' && licenseEmail !== '');

        // Check if navigation to this tab is allowed
        if (!isConnected && tabId !== 'login') {
            showToast("Please connect S22 Portal license first.");
            return;
        }

        // Handle button classes
        $('.tab-link').removeClass('active');
        $('.tab-panel').removeClass('active');

        $(`#tab-btn-${tabId}`).addClass('active');
        $(`#panel-${tabId}`).addClass('active');
    };

    // Render Saved Sliders Table
    function renderSlidersTable(filteredList = null) {
        const list = filteredList || Object.values(sliders);
        const tbody = $('#sliders-table-body');
        tbody.empty();

        if (list.length === 0) {
            tbody.append(`<tr><td colspan="6" style="text-align:center; color:var(--text-light); padding:40px;">No sliders found. Create one to get started!</td></tr>`);
            $('#table-row-counter').text('0 sliders');
            return;
        }

        list.forEach(w => {
            const shortcode = `[s22_slider id="${w.id}"]`;
            const typeLabel = w.type === 'slider' ? 'Slider' : 'Gallery';
            const detailText = w.type === 'slider' 
                ? `Height: ${w.height || 500}px | Autoplay: ${w.autoplay ? 'On' : 'Off'}`
                : `Limit: ${w.gallery_num_posts || 9} | Load More: ${w.gallery_load_more ? 'On' : 'Off'}`;

            const tr = `
                <tr>
                    <td>
                        <div class="widget-name-cell">
                            <div class="widget-title-container" style="font-weight: 600;">${escapeHtml(w.name)}</div>
                            <div class="widget-subtitle">#${w.id} • ${escapeHtml(detailText)}</div>
                        </div>
                    </td>
                    <td><span class="badge-layout">${typeLabel}</span></td>
                    <td>
                        <span class="badge-mode api">
                            <svg fill="currentColor" viewBox="0 0 24 24" style="width:12px;height:12px;margin-right:2px;"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
                            ${escapeHtml(w.feed_type || 'Instagram')}
                        </span>
                    </td>
                    <td>
                        <div class="shortcode-container" onclick="copyShortcode(this, '${w.id}')">
                            ${shortcode}
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                        </div>
                    </td>
                    <td>${w.created || 'Just now'}</td>
                    <td>
                        <div class="actions-cell">
                            <button class="btn-action edit" onclick="openWidgetEditor('${w.id}')">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                Edit
                            </button>
                            <button class="btn-action delete" onclick="deleteSlider('${w.id}')">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(tr);
        });

        $('#table-row-counter').text(`${list.length} slider${list.length !== 1 ? 's' : ''}`);
    }

    // Filter Sliders in List
    window.filterSliders = function () {
        const query = $('#widget-search').val().toLowerCase();
        const layout = $('#layout-filter').val();

        const filtered = Object.values(sliders).filter(w => {
            const matchesQuery = w.name.toLowerCase().includes(query);
            const matchesLayout = layout === 'all' || w.type === layout;
            return matchesQuery && matchesLayout;
        });

        renderSlidersTable(filtered);
    };

    // Copy shortcode to clipboard
    window.copyShortcode = function (element, id) {
        if (licenseStatus === 'expired' || licenseStatus === 'invalid') {
            showToast("Actions locked: S22 License is expired. Please renew your plan.");
            return;
        }
        const text = `[s22_slider id="${id}"]`;
        navigator.clipboard.writeText(text).then(() => {
            showToast(`Shortcode copied: ${text}`);
        });
    };

    // Toggle collapsible raw API logs drawer
    window.toggleApiLogsDrawer = function () {
        const drawer = $('#ifs-api-response-wrapper-success');
        const btn = $('.btn-pcc-logs-toggle');
        if (drawer.css('display') === 'none') {
            drawer.css('display', 'flex');
            btn.addClass('open');
        } else {
            drawer.css('display', 'none');
            btn.removeClass('open');
        }
    };

    // Copy raw API JSON response
    window.copyApiJsonToClipboard = function () {
        const text = $('#ifs-api-response-output-success').text();
        if (!text) return;
        navigator.clipboard.writeText(text).then(() => {
            showToast("JSON payload copied to clipboard!");
        });
    };

    // Attempt License verify
    window.attemptLicenseVerify = function () {
        const email = $('#portal-email').val().trim();
        const key = $('#portal-license-key').val().trim();

        if (!email || !key) {
            $('#login-error-text').text("Please fill out both Email and License Key fields.");
            $('#login-error-message').css('display', 'flex');
            return;
        }

        $('#login-error-message').hide();
        $('.connect-spinner').show();
        $('#connect-btn-text').text("Connecting Portal...");
        $('#btn-cloud-connect').prop('disabled', true);

        const siteUrl = window.location.origin + '/';
        const requestData = {
            domain: siteUrl,
            email: email,
            license_key: key,
            plugin_name: 'S22 Social Media Feed'
        };

        // Display sent payload details
        $('#ifs-api-request-output').text(JSON.stringify(requestData, null, 4));
        $('#ifs-api-response-output').text('Awaiting response from portal...');
        $('#ifs-api-response-wrapper').show();

        $.ajax({
            url: ifsAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'ifs_verify_license',
                nonce: ifsAdmin.nonce,
                email: email,
                license_key: key,
                domain: siteUrl,
                plugin_name: 'S22 Social Media Feed'
            },
            success: function (response) {
                $('.connect-spinner').hide();
                $('#btn-cloud-connect').prop('disabled', false);
                $('#connect-btn-text').text("Connect Cloud Engine");

                // Print the exact API response payload
                let responseData = response;
                if (typeof response === 'string') {
                    try {
                        responseData = JSON.parse(response);
                    } catch (e) {
                        responseData = { raw_response: response };
                    }
                }
                $('#ifs-api-response-output').text(JSON.stringify(responseData, null, 4));

                if (response.success || response.status === 'success' || response.status === 'active' || response.status === true) {
                    licenseStatus = 'valid';
                    licensePlan = response.plan_name || 'Enterprise Premium';
                    licenseEmail = email;
                    licenseKey = key;
                    licenseExpiryDate = response.expiry_date || '';

                    // Update UI
                    $('#connected-email').text(email);
                    const masked = key.substring(0, Math.min(key.length, 7)) + '••••••••••••';
                    $('#connected-license').text(masked);
                    $('#connected-purchase-date').text(response.purchased_date || '-');
                    $('#connected-expiry-date').text(licenseExpiryDate || '-');
                    $('.sync-tier-plan-display').text(licensePlan);

                    $('#header-status-pill').removeClass('status-expired').addClass('status-active');
                    $('#header-status-text').text('Active');
                    $('#header-status-pill svg').show();

                    // Hide S22 Account tab and show Settings profile card
                    $('#tab-btn-login').hide();
                    $('#settings-account-info-container').show();

                    // Update settings tab status badge class
                    $('#settings-account-status')
                        .removeClass('pcc-badge-expired')
                        .addClass('pcc-badge-active')
                        .html('<span class="pcc-pulse-dot"></span><span class="pcc-status-text-val">Active & Secured</span>');

                    // Print to settings success response card
                    $('#ifs-api-response-output-success').text(JSON.stringify(responseData, null, 4));
                    $('#ifs-api-response-wrapper-success').css('display', 'none');
                    $('.btn-pcc-logs-toggle').removeClass('open');

                    // Enable other tabs
                    $('#tab-btn-my-widgets, #tab-btn-help-guide').removeClass('tab-disabled');
                    switchTab('my-widgets');
                    showToast("S22 Portal connection active!");
                } else {
                    $('#login-error-text').text(response.message || "Failed to verify access key. Please try again.");
                    $('#login-error-message').css('display', 'flex');
                }
            },
            error: function (xhr) {
                $('.connect-spinner').hide();
                $('#btn-cloud-connect').prop('disabled', false);
                $('#connect-btn-text').text("Connect Cloud Engine");

                // Print network/server error details
                let responseData = xhr.responseJSON;
                if (!responseData) {
                    try {
                        responseData = JSON.parse(xhr.responseText);
                    } catch (e) {
                        responseData = {
                            status: xhr.status,
                            statusText: xhr.statusText,
                            responseText: xhr.responseText || "Network connection failed"
                        };
                    }
                }
                $('#ifs-api-response-output').text(JSON.stringify(responseData, null, 4));

                let msg = "Network error. Failed to reach verification portal.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                $('#login-error-text').text(msg);
                $('#login-error-message').css('display', 'flex');
            }
        });
    };

    // Disconnect license
    window.disconnectLicense = function () {
        if (!confirm("Are you sure you want to disconnect from S22 portal? This will reset local API caches.")) {
            return;
        }

        $.ajax({
            url: ifsAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'ifs_disconnect_license',
                nonce: ifsAdmin.nonce
            },
            success: function () {
                licenseStatus = 'invalid';
                licensePlan = '';
                licenseEmail = '';
                licenseKey = '';
                licenseExpiryDate = '';

                // Update UI
                $('#portal-email').val('');
                $('#portal-license-key').val('');
                $('#ifs-api-request-output').text('');
                $('#ifs-api-response-output').text('');
                $('#ifs-api-response-wrapper').hide();
                $('#ifs-api-response-output-success').text('');
                $('#ifs-api-response-wrapper-success').css('display', 'none');
                $('.btn-pcc-logs-toggle').removeClass('open');

                $('#header-status-pill').removeClass('status-active').addClass('status-expired');
                $('#header-status-text').text('Inactive');

                $('#settings-account-info-container').hide();
                $('#tab-btn-login').show();
                $('#login-form-container').show();

                // Disable tabs
                $('#tab-btn-my-widgets, #tab-btn-help-guide').addClass('tab-disabled');
                switchTab('login');
                showToast("S22 Portal disconnected.");
            }
        });
    };

    // Delete a Slider
    window.deleteSlider = function (id) {
        if (licenseStatus === 'expired' || licenseStatus === 'invalid') {
            showToast("Actions locked: S22 License is expired. Please renew your plan.");
            return;
        }
        if (!confirm("Are you sure you want to delete this slider? This action cannot be undone.")) {
            return;
        }

        $.ajax({
            url: ifsAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'ifs_delete_slider_ajax',
                nonce: ifsAdmin.nonce,
                slider_id: id
            },
            success: function (response) {
                if (response.success) {
                    delete sliders[id];
                    renderSlidersTable();
                    updateHeaderStats();
                    showToast("Slider deleted successfully.");
                } else {
                    alert(response.data || "Failed to delete slider.");
                }
            }
        });
    };

    // Update Top Header/Summary stats
    function updateHeaderStats() {
        const list = Object.values(sliders);
        $('#stat-widgets-count').text(list.length);
        $('#header-widget-count').text(`${list.length} Slider${list.length !== 1 ? 's' : ''}`);

        const instagramConnected = (ifsAdmin.instagramUserId && ifsAdmin.instagramAccessToken) ? 1 : 0;
        $('#stat-api-count').text(instagramConnected);
        $('#header-api-count').text(`${instagramConnected} API Connected`);
    }

    // ==========================================
    // CREATE/EDIT SLIDER WIZARD MODAL ENGINE
    // ==========================================

    // Helper to toggle visual cards for supported media types
    window.toggleMediaCard = function (type) {
        const checkbox = $(`#media-${type}`);
        const card = $(`#media-card-${type}`);
        const isChecked = checkbox.is(':checked');
        checkbox.prop('checked', !isChecked);
        if (!isChecked) {
            card.addClass('active');
        } else {
            card.removeClass('active');
        }
    };

    // Helper to click toggle switches by label
    window.clickToggleSwitch = function (id) {
        const checkbox = $(`#${id}`);
        const isChecked = !checkbox.is(':checked');
        checkbox.prop('checked', isChecked);
        if (id === 'gallery-loadmore') {
            $('#gallery-loadmore-settings').toggle(isChecked);
        }
    };

    window.openCreateModal = function () {
        if (licenseStatus === 'expired' || licenseStatus === 'invalid') {
            showToast("Actions locked: S22 License is expired. Please renew your plan.");
            return;
        }
        activeEditingSliderId = null;
        $('#create-modal .modal-title').text('Create New Slider');
        $('#btn-create-submit').text('Create Slider');

        // Reset wizard forms to default
        $('#slider-name').val('');
        selectCreateLayout('slider');
        $('#slider-height').val(500);
        $('#gallery-posts').val(9);
        $('#gallery-loadmore').prop('checked', true);
        
        $('#media-images').prop('checked', true);
        $('#media-videos').prop('checked', true);
        $('#media-card-images').addClass('active');
        $('#media-card-videos').addClass('active');

          $('#slider-autoplay').prop('checked', true);
          $('#slider-navigation').prop('checked', true);
          $('#slider-pagination').prop('checked', true);
          $('#slider-perview').val(1);
          $('#slider-fit').val('cover');
          $('#widget-feed-bg').val('transparent');
          $('#widget-feed-bg-picker').val('#ffffff');
          $('#widget-border-radius').val(8);
          $('#widget-slider-gap').val(16);
          $('#widget-hover-bg').val('rgba(0,0,0,0.5)');
          $('#widget-hover-bg-picker').val('#000000');
          $('#widget-hover-caption').prop('checked', true);
          $('#widget-arrow-color').val('#1e62ec');
          $('#widget-arrow-color-picker').val('#1e62ec');
          $('#widget-dot-color').val('#1e62ec');
          $('#widget-dot-color-picker').val('#1e62ec');
          $('#widget-custom-css').val('');
  
          showCreateStep(1);
          $('#create-modal').css('display', 'flex');
      };
  
      window.openWidgetEditor = function (id) {
          if (licenseStatus === 'expired' || licenseStatus === 'invalid') {
              showToast("Actions locked: S22 License is expired. Please renew your plan.");
              return;
          }
          const w = sliders[id];
          if (!w) return;
  
          activeEditingSliderId = id;
          $('#create-modal .modal-title').text(`Edit Slider: ${w.name}`);
          $('#btn-create-submit').text('Update Slider');
  
          // Populate slider data
          $('#slider-name').val(w.name);
          selectCreateLayout(w.type || 'slider');
          $('#slider-height').val(w.height || 500);
          $('#gallery-posts').val(w.gallery_num_posts || 9);
          $('#gallery-loadmore').prop('checked', !!w.gallery_load_more);
          $('#gallery-loadmore-text-label').val(w.gallery_load_more_text || 'Load More');
          $('#gallery-loadmore-num').val(w.gallery_load_more_num || 4);
          $('#gallery-loadmore-settings').toggle(!!w.gallery_load_more);
  
          const mediaTypes = w.media_types || [];
          $('#media-images').prop('checked', mediaTypes.includes('image'));
          $('#media-videos').prop('checked', mediaTypes.includes('video'));
          
          // Update visual selector cards
          if (mediaTypes.includes('image')) {
              $('#media-card-images').addClass('active');
          } else {
              $('#media-card-images').removeClass('active');
          }
          if (mediaTypes.includes('video')) {
              $('#media-card-videos').addClass('active');
          } else {
              $('#media-card-videos').removeClass('active');
          }
  
          $('#slider-autoplay').prop('checked', !!w.autoplay);
          $('#slider-navigation').prop('checked', !!w.navigation);
          $('#slider-pagination').prop('checked', !!w.pagination);
          $('#slider-perview').val(w.slides_per_view || 1);
          $('#slider-fit').val(w.image_fit || 'cover');
          $('#widget-feed-bg').val(w.feed_bg || 'transparent');
          $('#widget-border-radius').val(typeof w.border_radius !== 'undefined' ? w.border_radius : 8);
          $('#widget-slider-gap').val(typeof w.slider_gap !== 'undefined' ? w.slider_gap : 16);
          $('#widget-hover-bg').val(w.hover_bg || 'rgba(0,0,0,0.5)');
          $('#widget-hover-caption').prop('checked', w.hover_caption !== 0);
          $('#widget-arrow-color').val(w.arrow_color || '#1e62ec');
          $('#widget-dot-color').val(w.dot_color || '#1e62ec');

          $('#widget-border-width').val(typeof w.border_width !== 'undefined' ? w.border_width : 1);
          $('#widget-border-color').val(w.border_color || 'rgba(0,0,0,0.03)');
          $('#widget-border-hover-color').val(w.border_hover_color || 'rgba(0,0,0,0.06)');
          $('#widget-follow-btn-bg').val(w.follow_btn_bg || '#0f1419');
          $('#widget-follow-btn-text').val(w.follow_btn_text || '#ffffff');
          $('#widget-follow-btn-hover-bg').val(w.follow_btn_hover_bg || '#4f46e5');
          $('#widget-load-more-bg').val(w.load_more_bg || '#ffffff');
          $('#widget-load-more-text').val(w.load_more_text || '#0f1419');
          $('#widget-load-more-hover-bg').val(w.load_more_hover_bg || '#0f1419');

          $('#widget-custom-css').val(w.custom_css || '');
          
          // Update color picker swatches to match populated hex values
          const updatePickerIfHex = (val, pickerInput) => {
              const trimmed = val ? val.trim() : '';
              if (/^#[0-9A-F]{6}$/i.test(trimmed)) {
                  pickerInput.val(trimmed);
              } else if (/^#[0-9A-F]{3}$/i.test(trimmed)) {
                  const fullHex = '#' + trimmed[1] + trimmed[1] + trimmed[2] + trimmed[2] + trimmed[3] + trimmed[3];
                  pickerInput.val(fullHex);
              }
          };
          updatePickerIfHex(w.feed_bg, $('#widget-feed-bg-picker'));
          updatePickerIfHex(w.hover_bg, $('#widget-hover-bg-picker'));
          updatePickerIfHex(w.arrow_color, $('#widget-arrow-color-picker'));
          updatePickerIfHex(w.dot_color, $('#widget-dot-color-picker'));

          updatePickerIfHex(w.border_color, $('#widget-border-color-picker'));
          updatePickerIfHex(w.border_hover_color, $('#widget-border-hover-color-picker'));
          updatePickerIfHex(w.follow_btn_bg, $('#widget-follow-btn-bg-picker'));
          updatePickerIfHex(w.follow_btn_text, $('#widget-follow-btn-text-picker'));
          updatePickerIfHex(w.follow_btn_hover_bg, $('#widget-follow-btn-hover-bg-picker'));
          updatePickerIfHex(w.load_more_bg, $('#widget-load-more-bg-picker'));
          updatePickerIfHex(w.load_more_text, $('#widget-load-more-text-picker'));
          updatePickerIfHex(w.load_more_hover_bg, $('#widget-load-more-hover-bg-picker'));
  
          showCreateStep(1);
          $('#create-modal').css('display', 'flex');
      };

    window.closeCreateModal = function () {
        $('#create-modal').hide();
    };

    function validateStep(current, target) {
        if (current === 1 && target > 1) {
            const name = $('#slider-name').val().trim();
            if (!name) {
                $('#slider-name').focus().css({ 'border-color': 'var(--accent-red)', 'box-shadow': '0 0 0 3px rgba(239,68,68,0.15)' });
                setTimeout(() => { $('#slider-name').css({ 'border-color': '', 'box-shadow': '' }); }, 2000);
                return false;
            }
        }
        if (current === 2 && target > 2) {
            const layout = $('#wizard-layout-val').val();
            if (layout === 'gallery' && (/standard/i.test(licensePlan) || $('#layout-card-gallery').hasClass('premium-only-card'))) {
                showToast("Gallery Grid layout is only available on Premium plans. Please upgrade your license.");
                return false;
            }
        }
        return true;
    }

    window.clickCreateStep = function (step) {
        if (step === currentCreateStep) return;
        if (!validateStep(currentCreateStep, step)) return;
        showCreateStep(step);
    };

    window.showCreateStep = function (step) {
        currentCreateStep = step;
        $('.create-step-content').removeClass('active');
        $(`#create-step-${step}`).addClass('active');

        // Update progress icons
        for (let i = 1; i <= 4; i++) {
            const circle = $(`#create-label-icon-${i}`);
            const title = $(`#create-label-${i}`);
            const connector = $(`#create-connector-${i}`);

            circle.removeClass('active completed');
            title.removeClass('active completed');
            if (connector.length) connector.removeClass('completed');

            if (i < step) {
                circle.addClass('completed').html('<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" style="width:14px;height:14px;display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>');
                title.addClass('completed');
                if (connector.length) connector.addClass('completed');
            } else if (i === step) {
                circle.addClass('active').text(i);
                title.addClass('active');
            } else {
                circle.text(i);
            }
        }

        // Toggle footer buttons
        $('#btn-create-prev').css('display', step > 1 ? 'flex' : 'none');
        $('#btn-create-cancel').css('display', step > 1 ? 'none' : 'inline-flex');
        $('#btn-create-next').css('display', step < 4 ? 'flex' : 'none');
        $('#btn-create-submit').css('display', step === 4 ? 'flex' : 'none');

        // Step-specific field displays
        if (step === 3) {
            const layout = $('#wizard-layout-val').val();
            if (layout === 'slider') {
                $('.wizard-slider-only').show();
                $('.wizard-gallery-only').hide();
            } else {
                $('.wizard-slider-only').hide();
                $('.wizard-gallery-only').show();
            }
        }

        if (step === 4) {
            updateWizardLivePreview();
        }
    };

    window.selectCreateLayout = function (layout) {
        if (layout === 'gallery' && (/standard/i.test(licensePlan) || $('#layout-card-gallery').hasClass('premium-only-card'))) {
            showToast("Gallery Grid layout is only available on Premium plans. Please upgrade your license.");
            return;
        }
        $('#wizard-layout-val').val(layout);
        $('.layout-card').removeClass('active');
        if (layout === 'slider') {
            $('#layout-card-slider').addClass('active');
        } else if (layout === 'gallery') {
            $('#layout-card-gallery').addClass('active');
        }
    };

    window.nextCreateStep = function () {
        if (!validateStep(currentCreateStep, currentCreateStep + 1)) return;
        if (currentCreateStep < 4) {
            showCreateStep(currentCreateStep + 1);
        }
    };

    window.prevCreateStep = function () {
        if (currentCreateStep > 1) {
            showCreateStep(currentCreateStep - 1);
        }
    };

    // Render step 4 wizard preview
    window.updateWizardLivePreview = function () {
        const layout = $('#wizard-layout-val').val();
        const previewBox = $('#wizard-live-preview-box');
        previewBox.empty();

        // Standard dynamic placeholder images
        const placeholders = [
            'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=400&fit=crop&q=80',
            'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=400&fit=crop&q=80',
            'https://images.unsplash.com/photo-1472214222555-d404758b1c42?w=400&fit=crop&q=80',
            'https://images.unsplash.com/photo-1447752875215-b2761acb3c5d?w=400&fit=crop&q=80'
        ];

        if (layout === 'slider') {
            const fit = $('#slider-fit').val();

            let html = `
                <div class="instagram-mock-post">
                    <div class="imp-header">
                        <div class="imp-user">
                            <div class="imp-avatar">
                                <div class="imp-avatar-inner"></div>
                            </div>
                            <div>
                                <div class="imp-username">s22_feed_preview</div>
                                <div class="imp-location">Mock Feed Preview</div>
                            </div>
                        </div>
                        <div class="imp-more-btn">•••</div>
                    </div>
                    <div class="imp-media">
                        <img src="${placeholders[0]}" class="imp-img" style="object-fit: ${fit};">
                    </div>
                    <div class="imp-actions">
                        <div class="imp-actions-left">
                            <span class="imp-action-icon heart"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg></span>
                            <span class="imp-action-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></span>
                            <span class="imp-action-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M8.684 10.742L12 8l3.316 2.742m0 0a3 3 0 105.367-2.684L15 3M8.684 10.742A3 3 0 113.316 8.058L9 3m0 0a3 3 0 016 0v2a3 3 0 01-6 0V3z"/></svg></span>
                        </div>
                        <span class="imp-action-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5h14l-1.5 9h-11L5 5z"/></svg></span>
                    </div>
                    <div class="imp-likes">1,248 likes</div>
                    <div class="imp-caption-block">
                        <span class="imp-caption-user">s22_feed_preview</span>
                        <span class="imp-caption-text">This is a live carousel mockup. Looking amazing with <b>"${fit}"</b> image fit! #instagram #socialfeed</span>
                        <div class="imp-date">2 hours ago</div>
                    </div>
                </div>
            `;
            previewBox.append(html);

        } else {
            const limit = Math.min(parseInt($('#gallery-posts').val()) || 6, 6);
            let gridItemsHtml = '';
            for (let i = 0; i < limit; i++) {
                const img = placeholders[i % placeholders.length];
                gridItemsHtml += `
                    <div style="aspect-ratio: 1; overflow: hidden; background: #cbd5e1;">
                        <img src="${img}" style="width:100%; height:100%; object-fit: cover;">
                    </div>
                `;
            }

            let html = `
                <div class="instagram-mock-post" style="max-width: 440px;">
                    <div class="imp-header" style="border-bottom:none; padding-bottom: 6px;">
                        <div class="imp-user">
                            <div class="imp-avatar" style="width: 28px; height: 28px;">
                                <div class="imp-avatar-inner"></div>
                            </div>
                            <div>
                                <div class="imp-username" style="font-size:12px;">s22_gallery_preview</div>
                            </div>
                        </div>
                        <button class="btn-primary" style="font-size:11px; padding: 4px 12px; height: 26px; border-radius: 4px; border:none; color:white; cursor:pointer;">Follow</button>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 3px; padding: 0 4px 4px;">
                        ${gridItemsHtml}
                    </div>
                </div>
            `;
            previewBox.append(html);
        }
    };

    // Save wizard slider
    window.saveSlider = function () {
        const name = $('#slider-name').val().trim();
        const type = $('#wizard-layout-val').val();

        if (type === 'gallery' && (/standard/i.test(licensePlan) || $('#layout-card-gallery').hasClass('premium-only-card'))) {
            showToast("Gallery Grid layout is only available on Premium plans. Please upgrade your license.");
            return;
        }

        const mediaTypes = [];
        if ($('#media-images').is(':checked')) mediaTypes.push('image');
        if ($('#media-videos').is(':checked')) mediaTypes.push('video');

        const sliderData = {
            action: 'ifs_save_slider_ajax',
            nonce: ifsAdmin.nonce,
            slider_id: activeEditingSliderId || 0,
            name: name,
            type: type,
            feed_type: 'instagram',
            height: parseInt($('#slider-height').val()) || 500,
            gallery_num_posts: parseInt($('#gallery-posts').val()) || 9,
            gallery_load_more: $('#gallery-loadmore').is(':checked') ? 1 : 0,
            gallery_load_more_text: $('#gallery-loadmore-text-label').val() || 'Load More',
            gallery_load_more_num: parseInt($('#gallery-loadmore-num').val()) || 4,
            media_types: mediaTypes,
            autoplay: $('#slider-autoplay').is(':checked') ? 1 : 0,
            navigation: $('#slider-navigation').is(':checked') ? 1 : 0,
            pagination: $('#slider-pagination').is(':checked') ? 1 : 0,
            slides_per_view: parseInt($('#slider-perview').val()) || 1,
            image_fit: $('#slider-fit').val(),
            feed_bg: $('#widget-feed-bg').val() || 'transparent',
            border_radius: parseInt($('#widget-border-radius').val()) || 0,
            slider_gap: parseInt($('#widget-slider-gap').val()) || 0,
            hover_bg: $('#widget-hover-bg').val() || 'rgba(0,0,0,0.5)',
            hover_caption: $('#widget-hover-caption').is(':checked') ? 1 : 0,
            arrow_color: $('#widget-arrow-color').val() || '#1e62ec',
            dot_color: $('#widget-dot-color').val() || '#1e62ec',
            border_width: parseInt($('#widget-border-width').val()) || 0,
            border_color: $('#widget-border-color').val() || 'rgba(0,0,0,0.03)',
            border_hover_color: $('#widget-border-hover-color').val() || 'rgba(0,0,0,0.06)',
            follow_btn_bg: $('#widget-follow-btn-bg').val() || '#0f1419',
            follow_btn_text: $('#widget-follow-btn-text').val() || '#ffffff',
            follow_btn_hover_bg: $('#widget-follow-btn-hover-bg').val() || '#4f46e5',
            load_more_bg: $('#widget-load-more-bg').val() || '#ffffff',
            load_more_text: $('#widget-load-more-text').val() || '#0f1419',
            load_more_hover_bg: $('#widget-load-more-hover-bg').val() || '#0f1419',
            custom_css: $('#widget-custom-css').val() || ''
        };

        $.ajax({
            url: ifsAdmin.ajaxurl,
            type: 'POST',
            data: sliderData,
            success: function (response) {
                if (response.success && response.data) {
                    const savedId = response.data.slider_id;
                    sliders[savedId] = {
                        id: savedId,
                        name: name,
                        type: type,
                        feed_type: 'instagram',
                        height: sliderData.height,
                        gallery_num_posts: sliderData.gallery_num_posts,
                        gallery_load_more: sliderData.gallery_load_more,
                        gallery_load_more_text: sliderData.gallery_load_more_text,
                        gallery_load_more_num: sliderData.gallery_load_more_num,
                        media_types: mediaTypes,
                        autoplay: sliderData.autoplay,
                        navigation: sliderData.navigation,
                        pagination: sliderData.pagination,
                        slides_per_view: sliderData.slides_per_view,
                        image_fit: sliderData.image_fit,
                        feed_bg: sliderData.feed_bg,
                        border_radius: sliderData.border_radius,
                        slider_gap: sliderData.slider_gap,
                        hover_bg: sliderData.hover_bg,
                        hover_caption: sliderData.hover_caption,
                        arrow_color: sliderData.arrow_color,
                        dot_color: sliderData.dot_color,
                        border_width: sliderData.border_width,
                        border_color: sliderData.border_color,
                        border_hover_color: sliderData.border_hover_color,
                        follow_btn_bg: sliderData.follow_btn_bg,
                        follow_btn_text: sliderData.follow_btn_text,
                        follow_btn_hover_bg: sliderData.follow_btn_hover_bg,
                        load_more_bg: sliderData.load_more_bg,
                        load_more_text: sliderData.load_more_text,
                        load_more_hover_bg: sliderData.load_more_hover_bg,
                        custom_css: sliderData.custom_css,
                        created: response.data.created || 'Just now'
                    };

                    renderSlidersTable();
                    updateHeaderStats();
                    closeCreateModal();
                    showToast(activeEditingSliderId ? "Slider updated successfully!" : "Slider created successfully!");
                } else {
                    alert(response.data || "Error saving slider.");
                }
            }
        });
    };



    // On page boot
    renderSlidersTable();
    updateHeaderStats();

    // Reconstruct and display cached API response payload if already connected
    if (licenseKey !== '' && licenseEmail !== '') {
        const reconstructedResponse = {
            status: licenseStatus === 'valid' ? 'active' : licenseStatus,
            plan_name: licensePlan || 'Enterprise Premium',
            purchased_date: ifsAdmin.licensePurchasedDate || '',
            expiry_date: licenseExpiryDate || ''
        };
        $('#ifs-api-response-output-success').text(JSON.stringify(reconstructedResponse, null, 4));
        $('#ifs-api-response-wrapper-success').css('display', 'none');
        $('.btn-pcc-logs-toggle').removeClass('open');

        // Hide S22 Account tab button and display status container in Settings tab
        $('#tab-btn-login').hide();
        $('#settings-account-info-container').show();
    } else {
        $('#tab-btn-login').show();
        $('#settings-account-info-container').hide();
    }

    // Check if initial tab parameters exist
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');

    if (tabParam) {
        switchTab(tabParam);
    } else {
        // Switch to correct tab based on license
        if (licenseKey !== '' && licenseEmail !== '') {
            switchTab('my-widgets');
        } else {
            switchTab('login');
        }
    }
    
    // Sync text input with color picker swatches
    function setupColorPickerSync(textId, pickerId) {
        const textInput = $(`#${textId}`);
        const pickerInput = $(`#${pickerId}`);

        pickerInput.on('input', function() {
            textInput.val($(this).val());
        });

        textInput.on('input', function() {
            const val = $(this).val().trim();
            if (/^#[0-9A-F]{6}$/i.test(val)) {
                pickerInput.val(val);
            } else if (/^#[0-9A-F]{3}$/i.test(val)) {
                const fullHex = '#' + val[1] + val[1] + val[2] + val[2] + val[3] + val[3];
                pickerInput.val(fullHex);
            }
        });
    }

    setupColorPickerSync('widget-feed-bg', 'widget-feed-bg-picker');
    setupColorPickerSync('widget-hover-bg', 'widget-hover-bg-picker');
    setupColorPickerSync('widget-arrow-color', 'widget-arrow-color-picker');
    setupColorPickerSync('widget-dot-color', 'widget-dot-color-picker');
    setupColorPickerSync('widget-border-color', 'widget-border-color-picker');
    setupColorPickerSync('widget-border-hover-color', 'widget-border-hover-color-picker');
    setupColorPickerSync('widget-follow-btn-bg', 'widget-follow-btn-bg-picker');
    setupColorPickerSync('widget-follow-btn-text', 'widget-follow-btn-text-picker');
    setupColorPickerSync('widget-follow-btn-hover-bg', 'widget-follow-btn-hover-bg-picker');
    setupColorPickerSync('widget-load-more-bg', 'widget-load-more-bg-picker');
    setupColorPickerSync('widget-load-more-text', 'widget-load-more-text-picker');
    setupColorPickerSync('widget-load-more-hover-bg', 'widget-load-more-hover-bg-picker');

    $('#gallery-loadmore').on('change', function() {
        $('#gallery-loadmore-settings').toggle($(this).is(':checked'));
    });
});