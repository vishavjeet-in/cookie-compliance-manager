=== Cookie Compliance Manager ===
Contributors: Vishavjeet Choubey
Donate link: https://vishavjeet.in
Tags: cookie, compliance, gdpr, cookie-banner
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A lightweight cookie compliance banner manager for WordPress with customizable settings.

== Description ==

Cookie Compliance Manager is a simple, lightweight plugin that helps your website comply with cookie consent regulations such as GDPR and CCPA. Display a customizable cookie banner to inform visitors about cookie usage and obtain their consent.

= Features =

* **Easy Setup** - Configure in minutes from Settings → Cookie Compliance Manager
* **Customizable Design** - Choose colors, position, and button text
* **Multiple Positions** - Display banner at bottom (full width), bottom left, or bottom right
* **Lightweight** - Minimal impact on page load speed
* **Cookie-Based Storage** - Uses proper browser cookies (not localStorage)
* **Fully Translatable** - Ready for internationalization
* **Developer Friendly** - Clean, object-oriented code following WordPress standards

= Banner Options =

* Enable/Disable cookie banner
* Customize banner message
* Change accept/reject button text
* Choose banner position (bottom full width, bottom left, bottom right)
* Select background color
* Select button color

= Privacy & Compliance =

This plugin helps you display cookie consent notices but does not provide legal advice. Please consult with a legal professional to ensure your website complies with applicable privacy laws.

= Developer Notes =

The plugin follows WordPress coding standards and best practices:
* Object-oriented architecture
* Proper sanitization and escaping
* Nonce verification
* Translation-ready
* Minified assets for performance

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Navigate to Plugins → Add New
3. Search for "Cookie Compliance Manager"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin zip file
2. Log in to your WordPress admin panel
3. Navigate to Plugins → Add New → Upload Plugin
4. Choose the downloaded zip file and click "Install Now"
5. Activate the plugin

= Configuration =

1. Go to Settings → Cookie Compliance Manager
2. Enable the cookie banner
3. Customize the message, button text, colors, and position
4. Click "Save Changes"

== Frequently Asked Questions ==

= Does this plugin block cookies automatically? =

No, this plugin only displays a consent banner. You'll need to implement cookie blocking logic based on user consent in your custom code or use additional plugins.

= Is this plugin GDPR compliant? =

This plugin provides the tools to display a cookie consent banner, but GDPR compliance depends on your entire website setup. Please consult with a legal professional.

= Can I customize the banner design? =

Yes! You can customize the background color, button color, banner position, and all text content from the settings page.

= Does it work with caching plugins? =

Yes, the plugin is compatible with caching plugins. The cookie banner is loaded via JavaScript and respects cached pages.

= How long does the cookie consent last? =

The consent cookie is stored for 365 days (1 year) by default.

= Can I translate the plugin? =

Yes! The plugin is translation-ready. All strings are wrapped in translation functions and you can use tools like Loco Translate or Poedit.

== Screenshots ==

1. Settings page - Configure your cookie banner
2. Bottom full-width banner position
3. Bottom left corner banner position
4. Bottom right corner banner position
5. Color picker for customization

== Changelog ==

= 1.0.0 =
* Initial release
* Cookie consent banner with accept/reject options
* Customizable colors and positions
* Admin settings page
* Translation-ready

== Upgrade Notice ==

= 1.0.0 =
Initial release of Cookie Compliance Manager.

== Support ==

For support, please visit the plugin's support forum on WordPress.org or contact us through our website.

== Developer Information ==

= Hooks & Filters =

The plugin provides several hooks for developers:

**Actions:**
* `wccm_before_banner` - Fires before the banner HTML
* `wccm_after_banner` - Fires after the banner HTML

**Filters:**
* `wccm_banner_message` - Filter the banner message
* `wccm_cookie_expiry` - Filter cookie expiration days (default: 365)
* `wccm_settings` - Filter all plugin settings

= Code Example =

Change cookie expiry to 30 days:
`
add_filter( 'wccm_cookie_expiry', function() {
    return 30;
});
`

== Privacy Policy ==

Cookie Compliance Manager stores a cookie on the user's browser to remember their consent choice. This cookie contains only the consent status (accepted/rejected) and no personal information.

Cookie name: `wccm_cookie_consent`
Cookie duration: 365 days
Cookie value: "accepted" or "rejected"

== Credits ==

Developed with ❤️ following WordPress coding standards and best practices.