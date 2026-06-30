(function($) {
    $(document).ready(function() {
        var config = window.ifsSliderConfig || {};
        var sliderId = config.id;
        var type = config.type;
        var feedType = config.feed_type;
        var height = config.height;
        var galleryHeight = config.gallery_height;
        var galleryNumPosts = config.gallery_num_posts;
        var galleryLoadMore = config.gallery_load_more;
        var autoplay = config.autoplay;
        var navigation = config.navigation;
        var pagination = config.pagination;
        var mediaTypes = config.mediaTypes || [];
        var userId = config.userId;
        var accessToken = config.accessToken;
        var slidesPerView = config.slides_per_view || 1;
        var imageFit = config.image_fit || 'cover';
        var hoverCaption = config.hover_caption;

        // Store posts for lightbox navigation
        var allPosts = [];
        var currentIndex = 0;
        var swiper = null;

        // Initialize lightbox with carousel previews and mobile navigation arrows
        var lightbox = $('<div class="ifs-lightbox">' +
            '<div class="ifs-lightbox-content">' +
                '<span class="ifs-lightbox-close"><svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" style="display: block;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></span>' +
                '<div class="ifs-lightbox-carousel">' +
                    '<button class="ifs-lightbox-arrow ifs-lightbox-arrow-prev" aria-label="Previous image">' +
                        '<svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"></polyline></svg>' +
                    '</button>' +
                    '<div class="ifs-lightbox-prev-thumb"></div>' +
                    '<div class="ifs-lightbox-main show"></div>' +
                    '<div class="ifs-lightbox-next-thumb"></div>' +
                    '<button class="ifs-lightbox-arrow ifs-lightbox-arrow-next" aria-label="Next image">' +
                        '<svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>' +
                    '</button>' +
                '</div>' +
                '<div class="ifs-lightbox-caption"></div>' +
            '</div>' +
        '</div>').appendTo('body');

        function renderLightbox(index) {
            if (!allPosts.length) return;
            var total = allPosts.length;
            var prevIdx = (index - 1 + total) % total;
            var nextIdx = (index + 1) % total;
            var post = allPosts[index];
            var prevPost = allPosts[prevIdx];
            var nextPost = allPosts[nextIdx];
            var mainMedia = '';
            var prevMedia = '';
            var nextMedia = '';
            // Main
            if (post.media_type === 'VIDEO') {
                mainMedia = '<video controls autoplay style="object-fit: contain; object-position: center; width: 100%; height: 100%;"><source src="' + post.media_url + '" type="video/mp4">Your browser does not support the video tag.</video>';
            } else {
                mainMedia = '<img src="' + post.media_url + '" alt="Instagram image" style="object-fit: contain; object-position: center; width: 100%; height: 100%;" />';
            }
            // Prev
            if (prevPost.media_type === 'VIDEO') {
                prevMedia = '<video muted style="object-fit: cover; object-position: center; width: 100%; height: 100%;"><source src="' + prevPost.media_url + '" type="video/mp4"></video>';
            } else {
                prevMedia = '<img src="' + prevPost.media_url + '" alt="Prev image" style="object-fit: cover; object-position: center; width: 100%; height: 100%;" />';
            }
            // Next
            if (nextPost.media_type === 'VIDEO') {
                nextMedia = '<video muted style="object-fit: cover; object-position: center; width: 100%; height: 100%;"><source src="' + nextPost.media_url + '" type="video/mp4"></video>';
            } else {
                nextMedia = '<img src="' + nextPost.media_url + '" alt="Next image" style="object-fit: cover; object-position: center; width: 100%; height: 100%;" />';
            }
            // Animate main image
            var $main = lightbox.find('.ifs-lightbox-main');
            $main.removeClass('show').addClass('fade');
            setTimeout(function() {
                $main.html(mainMedia).attr('data-index', index);
                $main.removeClass('fade').addClass('show');
            }, 200);
            lightbox.find('.ifs-lightbox-prev-thumb').html(prevMedia).attr('data-index', prevIdx);
            lightbox.find('.ifs-lightbox-next-thumb').html(nextMedia).attr('data-index', nextIdx);
            // Caption with truncation and read more
            var caption = post.caption || '';
            var captionHtml = '';
            if (caption.length > 0) {
                captionHtml = '<span class="caption-text">' + $('<div>').text(caption).html() + '</span>';
                // Always show the button for testing
                captionHtml += ' <button class="caption-read-more" style="background:none;border:none;color:#00aaff;cursor:pointer;padding:0 0 0 8px;font-size:0.9em;">Read More</button>';
            }
            lightbox.find('.ifs-lightbox-caption').html(captionHtml);
            lightbox.addClass('active');
            currentIndex = index;
            // Pause Swiper autoplay
            if (swiper && swiper.autoplay) swiper.autoplay.stop();
        }

        // Add CSS for truncation (only once)
        if (!document.getElementById('ifs-lightbox-caption-style')) {
            var style = document.createElement('style');
            style.id = 'ifs-lightbox-caption-style';
            style.innerHTML = `
            .caption-text {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
                max-height: 2.8em;
                transition: max-height 0.2s;
            }
            .caption-text.expanded {
                -webkit-line-clamp: unset;
                max-height: none;
            }
            .caption-read-more {
                background: none;
                border: none;
                color: #00aaff;
                cursor: pointer;
                padding: 0 0 0 8px;
                font-size: 0.9em;
            }
            .caption-read-more:hover {
                color: #00d0ff;
            }
            `;
            document.head.appendChild(style);
        }

        // Inject gallery grid CSS for natural cells, full image fill, and centering (NAMESPACED)
        if (!document.getElementById('s22-slider-style-ns')) {
            var style = document.createElement('style');
            style.id = 's22-slider-style-ns';
            style.innerHTML = `
            .s22-slider .instagram-gallery {
                width: 100%;
                max-width: 1200px;
                margin: 0 auto;
            }
            .s22-slider .gallery-item {
                width: 100%;
                position: relative;
                overflow: visible;
                padding-bottom: 0; /* Natural aspect ratio */
            }
            .s22-slider .gallery-item .ifs-lightbox-link {
                display: block;
                position: relative;
                width: 100%;
                height: auto;
            }
            .s22-slider .gallery-item .ifs-lightbox-link > img,
            .s22-slider .gallery-item .ifs-lightbox-link > video {
                position: relative !important;
                width: 100% !important;
                height: auto !important;
                object-fit: cover !important;
                display: block;
            }
            .s22-slider .swiper-slide .ifs-lightbox-link > img,
            .s22-slider .swiper-slide .ifs-lightbox-link > video {
                object-fit: cover !important;
                display: block;
            }
            `;
            document.head.appendChild(style);
        }

        // Lightbox event handlers
        $(document).on('click', '.ifs-lightbox-link', function(e) {
            e.preventDefault();
            var idx = $(this).data('index');
            if (typeof idx === 'undefined') idx = 0;
            renderLightbox(idx);
        });
        // Thumbnails navigation
        lightbox.on('click', '.ifs-lightbox-prev-thumb', function(e) {
            e.stopPropagation();
            var idx = parseInt($(this).attr('data-index'));
            renderLightbox(idx);
        });
        lightbox.on('click', '.ifs-lightbox-next-thumb', function(e) {
            e.stopPropagation();
            var idx = parseInt($(this).attr('data-index'));
            renderLightbox(idx);
        });
        // Arrow navigation (mobile/fallback)
        lightbox.on('click', '.ifs-lightbox-arrow-prev', function(e) {
            e.stopPropagation();
            if (!allPosts.length) return;
            var prev = (currentIndex - 1 + allPosts.length) % allPosts.length;
            renderLightbox(prev);
        });
        lightbox.on('click', '.ifs-lightbox-arrow-next', function(e) {
            e.stopPropagation();
            if (!allPosts.length) return;
            var next = (currentIndex + 1) % allPosts.length;
            renderLightbox(next);
        });
        // Keyboard navigation
        $(document).on('keydown', function(e) {
            if (!lightbox.hasClass('active')) return;
            if (e.key === 'ArrowLeft') {
                var prev = (currentIndex - 1 + allPosts.length) % allPosts.length;
                renderLightbox(prev);
            } else if (e.key === 'ArrowRight') {
                var next = (currentIndex + 1) % allPosts.length;
                renderLightbox(next);
            } else if (e.key === 'Escape') {
                lightbox.find('.ifs-lightbox-close').click();
            }
        });
        // Close logic
        lightbox.find('.ifs-lightbox-close').on('click', function(e) {
            e.stopPropagation();
            lightbox.removeClass('active');
            lightbox.find('video').each(function() { this.pause(); });
            if (swiper && swiper.autoplay) swiper.autoplay.start();
        });
        lightbox.on('click', function(e) {
            if ($(e.target).hasClass('ifs-lightbox')) {
                lightbox.removeClass('active');
                lightbox.find('video').each(function() { this.pause(); });
                if (swiper && swiper.autoplay) swiper.autoplay.start();
            }
        });
        // Toggle caption expand/collapse
        lightbox.on('click', '.caption-read-more', function(e) {
            e.stopPropagation();
            var $captionText = lightbox.find('.caption-text');
            if ($captionText.hasClass('expanded')) {
                $captionText.removeClass('expanded');
                $(this).text('Read More');
            } else {
                $captionText.addClass('expanded');
                $(this).text('Show Less');
            }
        });

        // Normalize mediaTypes to lowercase array
        if (typeof mediaTypes === 'string') {
            mediaTypes = [mediaTypes.toLowerCase()];
        } else if (Array.isArray(mediaTypes)) {
            mediaTypes = mediaTypes.map(function(t) { return t.toLowerCase(); });
        } else {
            mediaTypes = [];
        }
    

        function filterByMediaTypes(posts, allowedTypes) {
            if (!Array.isArray(allowedTypes) || allowedTypes.length === 0) return posts;
            return posts.filter(function(post) {
                var type = post.media_type ? post.media_type.toLowerCase() : '';
                return (type === 'image' && allowedTypes.includes('image')) ||
                       (type === 'video' && allowedTypes.includes('video'));
            });
        }

        function renderSlides(data) {
            allPosts = filterByMediaTypes(data, mediaTypes);
            if (config.hiddenPosts && Array.isArray(config.hiddenPosts)) {
                allPosts = allPosts.filter(function(post) {
                    return !config.hiddenPosts.includes(post.id);
                });
            }
            var swiperWrapper = $('#s22-slider-' + sliderId + ' .swiper-wrapper');
            swiperWrapper.empty();
            allPosts.forEach(function(post, idx) {
                var slide = $('<div class="swiper-slide"></div>');
                var mediaUrl = post.media_url;
                var caption = post.caption || '';
                var captionOverlay = '';
                if (hoverCaption == '1' && caption) {
                    captionOverlay = '<div class="ifs-hover-overlay"><div class="ifs-hover-caption-text">' + $('<div>').text(caption).html() + '</div></div>';
                }
                if (post.media_type === 'VIDEO') {
                    slide.append('<a href="#" class="ifs-lightbox-link" data-index="' + idx + '"><video autoplay muted preload="metadata" playsinline style="max-width: 100%; height: auto;"><source src="' + mediaUrl + '" type="video/mp4">Your browser does not support the video tag.</video>' + captionOverlay + '</a>');
                } else {
                    slide.append('<a href="#" class="ifs-lightbox-link" data-index="' + idx + '"><img src="' + mediaUrl + '" alt="Instagram image" style="object-fit: ' + imageFit + '; object-position: center;" />' + captionOverlay + '</a>');
                }
                swiperWrapper.append(slide);
            });
            if (swiper) swiper.update();
        }
        function drawGallery(gallery, posts, limit, isAppend) {
            if (!isAppend) {
                gallery.empty();
            } else {
                gallery.find('.load-more-button').remove();
            }

            var startIdx = isAppend ? gallery.find('.gallery-item').length : 0;
            var newItems = posts.slice(startIdx, limit);

            newItems.forEach(function(post, offsetIdx) {
                var idx = startIdx + offsetIdx;
                var item = $('<div class="gallery-item ifs-fade-in"></div>');
                
                // Staggered slide-up animation delay
                var delay = (offsetIdx * 0.08) + 's';
                item.css({
                    'animation-delay': delay,
                    '-webkit-animation-delay': delay
                });

                var mediaUrl = post.media_url;
                var caption = post.caption || '';
                var captionOverlay = '';
                if (hoverCaption == '1' && caption) {
                    captionOverlay = '<div class="ifs-hover-overlay"><div class="ifs-hover-caption-text">' + $('<div>').text(caption).html() + '</div></div>';
                }
                if (post.media_type === 'VIDEO') {
                    item.append('<a href="#" class="ifs-lightbox-link" data-index="' + idx + '"><video autoplay muted preload="metadata" playsinline style="max-width: 100%; height: auto;"><source src="' + mediaUrl + '" type="video/mp4">Your browser does not support the video tag.</video>' + captionOverlay + '</a>');
                } else {
                    item.append('<a href="#" class="ifs-lightbox-link" data-index="' + idx + '"><img src="' + mediaUrl + '" alt="Instagram image" style="object-fit: ' + imageFit + '; object-position: center;" />' + captionOverlay + '</a>');
                }
                gallery.append(item);
            });

            if (galleryLoadMore && posts.length > limit) {
                var btnText = config.gallery_load_more_text || 'Load More';
                gallery.append('<button class="load-more-button">' + $('<div>').text(btnText).html() + '</button>');
            }
        }

        function renderGallery(data) {
            allPosts = filterByMediaTypes(data, mediaTypes);
            if (config.hiddenPosts && Array.isArray(config.hiddenPosts)) {
                allPosts = allPosts.filter(function(post) {
                    return !config.hiddenPosts.includes(post.id);
                });
            }
            var gallery = $('#s22-slider-' + sliderId + ' .instagram-gallery');
            gallery.data('all-posts', allPosts);
            gallery.data('current-limit', galleryNumPosts);
            drawGallery(gallery, allPosts, galleryNumPosts, false);
        }

        // Load More button click handler: Load dynamic number of additional items at a time
        $(document).on('click', '.load-more-button', function(e) {
            e.preventDefault();
            var gallery = $(this).closest('.instagram-gallery');
            var posts = gallery.data('all-posts') || [];
            var currentLimit = gallery.data('current-limit') || galleryNumPosts;
            var loadMoreNum = parseInt(config.gallery_load_more_num) || 4;
            currentLimit += loadMoreNum;
            gallery.data('current-limit', currentLimit);
            drawGallery(gallery, posts, currentLimit, true);
        });

        function renderAccountInfo(user) {
            var html = '<div class="s22-ig-account-info-bar">';
            html += '<div class="s22-ig-account-left" style="display:flex;align-items:center;gap:16px;">';
            if (user.profile_picture_url) {
                html += '<img src="' + user.profile_picture_url + '" alt="Profile" class="s22-ig-profile-pic">';
            }
            html += '<div class="s22-ig-account-meta">';
            if (user.name) html += '<div class="s22-ig-name">' + user.name + '</div>';
            if (user.username) html += '<div class="s22-ig-username">@' + user.username + '</div>';
            if (user.website) html += '<div class="s22-ig-website"><a href="' + user.website + '" target="_blank" rel="noopener">' + user.website + '</a></div>';
            html += '</div></div>';
            html += '<div class="s22-ig-account-right">';
            if (user.username) {
                html += '<a href="https://instagram.com/' + user.username + '" target="_blank" rel="noopener" class="s22-ig-follow-btn">Follow</a>';
            }
            html += '</div>';
            html += '</div>';
            return html;
        }

        function fetchAndRenderAccountInfo(userId, accessToken, container) {
            $.ajax({
                url: 'https://graph.facebook.com/v19.0/' + userId,
                data: {
                    fields: 'has_profile_pic,name,profile_picture_url,username,website',
                    access_token: accessToken
                },
                success: function(user) {
                    var html = renderAccountInfo(user);
                    container.prepend(html);
                },
                error: function() {
                    // Optionally handle error
                }
            });
        }

        if (type === 'slider') {
            swiper = new Swiper('#s22-slider-' + sliderId + ' .swiper', {
                slidesPerView: slidesPerView,
                spaceBetween: 30,
                loop: true,
                autoplay: autoplay ? { delay: 3000 } : false,
                navigation: navigation ? {
                    nextEl: '#s22-slider-' + sliderId + ' .swiper-button-next',
                    prevEl: '#s22-slider-' + sliderId + ' .swiper-button-prev',
                } : false,
                pagination: pagination ? {
                    el: '#s22-slider-' + sliderId + ' .swiper-pagination',
                    clickable: true,
                } : false,
                breakpoints: {
                    320: { slidesPerView: Math.min(slidesPerView, 1), spaceBetween: 10 },
                    480: { slidesPerView: Math.min(slidesPerView, 2), spaceBetween: 20 },
                    768: { slidesPerView: Math.min(slidesPerView, 3), spaceBetween: 30 },
                    1024: { slidesPerView: slidesPerView, spaceBetween: 30 }
                }
            });
            if (feedType === 'instagram' && userId && accessToken) {
                // Render account info above slider
                var sliderContainer = $('#s22-slider-' + sliderId);
                fetchAndRenderAccountInfo(userId, accessToken, sliderContainer);
                loadInstagramFeed(userId, accessToken, mediaTypes, function(data) {
                    renderSlides(data);
                });
            }
        } else if (type === 'gallery' || type === 'masonry') {
            if (feedType === 'instagram' && userId && accessToken) {
                // Render account info above gallery
                var galleryContainer = $('#s22-slider-' + sliderId);
                fetchAndRenderAccountInfo(userId, accessToken, galleryContainer);
                loadInstagramFeed(userId, accessToken, mediaTypes, function(data) {
                    renderGallery(data);
                });
            }
        }
    });

    function loadInstagramFeed(userId, accessToken, mediaTypes, callback) {
        $.ajax({
            url: 'https://graph.facebook.com/v19.0/' + userId + '/media',
            data: {
                fields: 'id,caption,media_url,media_type,thumbnail_url,timestamp',
                access_token: accessToken
            },
            success: function(response) {
                if (response.data) {
                    callback(response.data);
                }
            },
            error: function() {
                console.error('Failed to load Instagram feed');
            }
        });
    }

    // Expiry date heartbeat check: runs every 30 seconds
    var heartbeatInterval = setInterval(function() {
        var ajaxUrl = (window.ifsData && ifsData.ajaxurl) ? ifsData.ajaxurl : '';
        var nonce = (window.ifsData && ifsData.nonce) ? ifsData.nonce : '';
        
        if (!ajaxUrl || !nonce) return;

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'ifs_heartbeat_license_check',
                nonce: nonce
            },
            success: function(response) {
                if (response && response.status && (response.status === 'expired' || response.status === 'invalid')) {
                    clearInterval(heartbeatInterval);
                    
                    var errorMsg = '<div class="s22-license-error" style="color: #d93838; font-weight: bold; padding: 16px; border: 1px dashed #d93838; background-color: #fff5f5; border-radius: 4px; font-family: sans-serif; text-align: center; margin: 20px 0;">' + 
                                   (response.status === 'expired' ? 'Error: Your license key has expired. Please renew your license in the WordPress admin settings.' : 'Error: Invalid license key. Please verify your license settings in the WordPress admin panel.') + 
                                   '</div>';
                    
                    // Disable the output by replacing the HTML of the sliders
                    $('.s22-slider').html(errorMsg);
                }
            }
        });
    }, 30000);
})(jQuery);