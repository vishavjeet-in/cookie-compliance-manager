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
            $('#wccm-accept-btn').on('click', function(e) {
                e.preventDefault();
                WCCMCookie.set(
                    wccmSettings.cookieName,
                    'accepted',
                    wccmSettings.cookieExpiry
                );
                self.hideBanner();
            });

            // Reject button
            $('#wccm-reject-btn').on('click', function(e) {
                e.preventDefault();
                WCCMCookie.set(
                    wccmSettings.cookieName,
                    'rejected',
                    wccmSettings.cookieExpiry
                );
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