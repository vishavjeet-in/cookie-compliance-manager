/**
 * Admin JavaScript
 *
 * @package Cookie_Compliance_Manager
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize color pickers
        if ($.fn.wpColorPicker) {
            $('.wccm-color-picker').wpColorPicker();
        }
    });

})(jQuery);