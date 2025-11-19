=== BruteFort ===
Contributors: y0000el
Tags: brute force, login protection, custom login url, geo blocking, ip restriction
Requires at least: 5.0
Tested up to: 6.8.3
Requires PHP: 7.4
Stable tag: 0.0.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

BruteFort – Complete WordPress login security with custom login URLs, geo blocking, brute force protection, and IP restrictions in one plugin.

== Description ==

**BruteFort** is your WordPress site's complete login security solution. Protect against brute force attacks, hide your login page with a custom URL, block countries using geo-blocking, and manage IP restrictions — all in one lightweight, performance-optimized plugin.

Whether you're running a blog, a WooCommerce store, or a membership site, BruteFort keeps bots, hackers, and unauthorized users out while maintaining fast page speeds.

= 🔐 Key Features =

**🌐 Geo Blocking (Country-Based Restrictions)**
- Block or allow login attempts by country
- Blacklist mode: Block specific countries from accessing wp-login.php
- Whitelist mode: Only allow login from selected countries
- IP geolocation detection (Cloudflare compatible)
- Perfect for region-specific sites or blocking high-risk countries

**🔗 Custom Login URL (Hide wp-login.php)**
- Hide default WordPress login page (wp-login.php)
- Create custom login slug (e.g., yoursite.com/secure-access)
- Automatically redirect wp-login.php to 404
- Prevent automated bot attacks targeting /wp-login.php
- Easy to remember custom URLs for authorized users

**🛡️ Brute Force Protection & Rate Limiting**
- Block brute force attacks with smart rate limiting
- Set maximum login attempts per IP address
- Configurable time windows and lockout durations
- Progressive lockout extensions for repeated attacks
- Custom error messages for locked users

**📍 IP Whitelist & Blacklist Management**
- Manage custom IP whitelists and blacklists
- Add individual IPs or CIDR ranges
- Instantly block suspicious IPs
- Whitelist your own IP to prevent lockouts
- Bulk IP management with easy interface

**📊 Real-Time Monitoring & Logs**
- View failed login attempts in real-time
- Track IP addresses, usernames, and timestamps
- Filter logs by status, date, or IP
- Manual unlock for accidentally locked users
- Export logs for security audits

**⚡ Performance & Compatibility**
- Lightweight and performance-optimized
- Works with Cloudflare, proxy servers, and CDNs
- Compatible with most security plugins
- Dark mode UI support
- No impact on page load speeds

= 🎯 Perfect For =
- **WooCommerce stores** protecting customer data and preventing unauthorized access
- **Membership sites** restricting access by geographic location
- **Corporate websites** blocking countries where business doesn't operate
- **Blog owners** hiding login page from automated bots and scanners
- **Agencies** managing multiple client sites with different security requirements
- **High-traffic sites** experiencing frequent brute force attacks
- **International sites** wanting region-specific login restrictions

= 🚀 Why Choose BruteFort? =
- **All-in-one solution**: Custom login URL + Geo blocking + IP restrictions in one plugin
- **Easy to use**: Simple, intuitive interface with no complex configuration
- **Performance-focused**: Minimal resource usage, no site slowdown
- **SEO-friendly**: Properly handles redirects and 404s
- **Privacy-conscious**: No external API calls for basic features (optional geo API)
- **Regular updates**: Actively maintained with new features added regularly

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/brutefort` directory, or install the plugin through the WordPress plugin screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to **Settings > BruteFort** to configure IP restrictions, whitelist/blacklist, and login attempt limits.
4. Navigate to **Custom Login URL** tab to set up a custom login slug and hide wp-login.php
5. Use **Geo Blocking** tab to block or allow countries from accessing your login page

== Frequently Asked Questions ==

= Does this plugin slow down my site? =
No. BruteFort is lightweight and optimized for performance, with minimal impact on page load times.

= How does the custom login URL feature work? =
BruteFort creates a custom slug (e.g., /secure-login) for your login page and automatically blocks access to /wp-login.php, returning a 404 error to unauthorized users.

= What is Geo Blocking and how does it work? =
Geo Blocking restricts login attempts based on the visitor's country. You can either blacklist specific countries (block mode) or whitelist only allowed countries (allow mode). It uses IP geolocation to detect the user's location.

= Can I whitelist my own IP address? =
Yes! Add your IP to the whitelist to ensure you're never locked out, even if other restrictions are active.

= What happens if I forget my custom login URL? =
You can disable the custom login URL feature via FTP by deactivating the plugin, or by accessing your database to change the setting.

= Does Geo Blocking work with VPNs or proxy servers? =
Yes, BruteFort is compatible with Cloudflare and most proxy servers. It checks the CF-IPCountry header first, then falls back to IP-based geolocation.

= Is this compatible with other security plugins? =
Yes. BruteFort works alongside most WordPress security plugins like Wordfence, iThemes Security, and All In One WP Security.

= Can I block entire countries from logging in? =
Yes! The Geo Blocking feature lets you select specific countries to block or allow for login attempts.

== Screenshots ==
1. Dashboard Overview - Rate Limit Settings
2. Custom Login URL Settings - Hide wp-login.php
3. Geo Blocking Settings - Country-based restrictions
4. IP Whitelist/Blacklist Management
5. Real-time Login Attempt Logs
6. Dark Mode Interface Support

== Changelog ==

= 0.0.7 - 20/11/2025 =
* Fix   - Removed extra tags and shortened extra long short descriptions.


= 0.0.6 - 19/11/2025 =
* Feature - **Custom Login URL**: Hide wp-login.php and create custom login slugs
* Feature - **Geo Blocking**: Block or allow login attempts by country (blacklist/whitelist mode)
* Feature - Complete country list (249 countries) for geo-blocking
* Enhance - Unified card-based UI design across all settings pages
* Enhance - Improved toggle switches and form controls
* Enhance - Better dark mode support throughout the plugin
* Fix - LogsService type error causing fatal errors on live sites

= 0.0.5 - 14/11/2025 =
* Fix - Entry already exists issue on setup wizard

= 0.0.4 - 14/11/2025 =
* Feature – Basic Setup wizard
* Enhance - Refresh option on logs page
* Fix - Dark mode design update on datatable and modals
* Fix - Unlock feature for locked users

= 0.0.3 - 13/11/2025 =
* Fix – Settings redirect from all plugins page
* Fix - Compatibility with 7.4

= 0.0.2 - 12/11/2025 =
* Fix – Autoload not working issue

= 0.0.1 - 12/11/2025 =
* Initial release – login protection, IP whitelist/blacklist, brute force detection

== Upgrade Notice ==

= 0.0.6 =
Major update! New features: Custom Login URL to hide wp-login.php and Geo Blocking for country-based restrictions. Improved UI and critical bug fixes.

= 0.0.5 =
Bug fix release for setup wizard compatibility.

= 0.0.1 =
Initial release with login protection, IP whitelist/blacklist, and brute force detection.
