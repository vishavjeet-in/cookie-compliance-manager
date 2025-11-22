/**
 * Frontend JavaScript
 *
 * @package Cookie_Compliance_Manager
 */

(function($) {
    'use strict';

    /**
     * Cookie utilities
     */
    var WCCMCookie = {
        /**
         * Set a cookie
         */
        set: function(name, value, days) {
            var expires = '';
            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = '; expires=' + date.toUTCString();
            }
            document.cookie = name + '=' + (value || '') + expires + '; path=/';
        },

        /**
         * Get a cookie
         */
        get: function(name) {
            var nameEQ = name + '=';
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) === ' ') {
                    c = c.substring(1, c.length);
                }
                if (c.indexOf(nameEQ) === 0) {
                    return c.substring(nameEQ.length, c.length);
                }
            }
            return null;
        },

        /**
         * Delete a cookie
         */
        delete: function(name) {
            document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
        }
    };

    /**
     * Cookie banner functionality
     */
    var WCCMBanner = {
        /**
         * Initialize
         */
        init: function() {
            var consent = WCCMCookie.get(wccmSettings.cookieName);
            
            if (!consent) {
                this.showBanner();
            }

            this.bindEvents();
        },

        /**
         * Show banner
         */
        showBanner: function() {
            $('#wccm-cookie-banner').fadeIn(400);
        },

        /**
         * Hide banner
         */
        hideBanner: function() {
            $('#wccm-cookie-banner').fadeOut(400);
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            var self = this;

            // Accept button
            function getUTMParams() {
                var params = {};
                var query = window.location.search.substring(1).split('&');
                for (var i = 0; i < query.length; i++) {
                    var pair = query[i].split('=');
                    if (pair.length === 2) {
                        params[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1]);
                    }
                }
                return params;
            }

            function getDeviceType() {
                var ua = navigator.userAgent;
                if (/mobile/i.test(ua)) return 'Mobile';
                if (/tablet/i.test(ua)) return 'Tablet';
                return 'Desktop';
            }

            function sendConsent(status) {
                var utm = getUTMParams();
                var data = {
                    action: 'wccm_store_consent',
                    status: status,
                    session_id: WCCMCookie.get('wccm_session_id') || (function(){
                        var sid = Math.random().toString(36).substr(2, 16);
                        WCCMCookie.set('wccm_session_id', sid, wccmSettings.cookieExpiry);
                        return sid;
                    })(),
                    landing_page: window.location.pathname + window.location.search,
                    source: utm.utm_source || '',
                    medium: utm.utm_medium || '',
                    campaign: utm.utm_campaign || '',
                    referrer: document.referrer,
                    device: getDeviceType(),
                    nonce: wccmSettings.nonce
                };
                $.post(wccmSettings.ajaxUrl, data);
            }

            $('#wccm-accept-btn').on('click', function(e) {
                e.preventDefault();
                WCCMCookie.set(
                    wccmSettings.cookieName,
                    'accepted',
                    wccmSettings.cookieExpiry
                );
                sendConsent('accepted');
                self.hideBanner();

                // GTM consent and config update if GTM ID is present
                if (typeof wccmSettings.gtmId !== 'undefined' && wccmSettings.gtmId) {
                    window.dataLayer = window.dataLayer || [];
                    function gtag(){dataLayer.push(arguments);}
                    gtag('js', new Date());
                    gtag('config', wccmSettings.gtmId);
                    gtag('consent', 'update', {
                        ad_storage: 'granted',
                        analytics_storage: 'granted',
                        ad_user_data: 'granted',
                        ad_personalization: 'granted',
                        functionality_storage: 'granted',
                        security_storage: 'granted',
                        personalization_storage: 'granted'
                    });
                }
            });

            $('#wccm-reject-btn').on('click', function(e) {
                e.preventDefault();
                WCCMCookie.set(
                    wccmSettings.cookieName,
                    'rejected',
                    wccmSettings.cookieExpiry
                );
                sendConsent('rejected');
                self.hideBanner();
            });
        }
    };

    /**
     * Document ready
     */
    $(document).ready(function() {
        WCCMBanner.init();
    });

})(jQuery);