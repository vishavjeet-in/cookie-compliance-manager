# Security Policy

## Supported Versions

We take security seriously and actively maintain Cookie Compliance Manager. The following versions are currently supported with security updates:

| Version | Supported          | End of Support |
| ------- | ------------------ | -------------- |
| 1.0.x   | :white_check_mark: | Active         |
| < 1.0   | :x:                | Not supported  |

**Note:** We recommend always using the latest version of the plugin to ensure you have the latest security patches and features.

---

## Security Features

Cookie Compliance Manager implements the following security measures:

### Input Sanitization
- All user inputs are sanitized using WordPress functions:
  - `sanitize_text_field()` for text inputs
  - `sanitize_textarea_field()` for textarea inputs
  - `sanitize_hex_color()` for color picker values

### Output Escaping
- All outputs are properly escaped:
  - `esc_html()` for HTML content
  - `esc_attr()` for HTML attributes
  - `esc_url()` for URLs
  - `esc_textarea()` for textarea content

### CSRF Protection
- All forms use WordPress nonces via `settings_fields()`
- Nonce verification on all data processing

### Access Control
- Settings page requires `manage_options` capability
- User capability checks on all admin actions

### Data Storage
- Uses WordPress Options API (no direct SQL queries)
- Cookie storage uses standard browser cookies with 365-day expiration
- No personal data collected or stored

### Code Security
- No use of `eval()`, `base64_decode()`, or dangerous PHP functions
- No obfuscated code
- Direct file access prevention on all PHP files
- Proper WordPress coding standards followed

---

## Reporting a Vulnerability

We appreciate the security research community's efforts in helping keep Cookie Compliance Manager secure. If you discover a security vulnerability, please follow responsible disclosure practices.

### How to Report

**Please DO NOT report security vulnerabilities through public GitHub issues.**

Instead, report security vulnerabilities via one of these methods:

1. **Email (Preferred):**
   - Send details to: **vishavjeet@vishavjeet.in**
   - Subject: `[SECURITY] Cookie Compliance Manager - [Brief Description]`

2. **WordPress.org:**
   - Use the WordPress.org plugin security reporting system
   - Plugin page: https://wordpress.org/plugins/cookie-compliance-manager/

### What to Include

Please include the following information in your report:

- **Description:** Clear description of the vulnerability
- **Impact:** Potential impact and severity assessment
- **Steps to Reproduce:** Detailed steps to reproduce the issue
- **Proof of Concept:** If applicable, provide PoC code or screenshots
- **Affected Versions:** Which plugin version(s) are affected
- **Environment:** WordPress version, PHP version, server details
- **Suggested Fix:** If you have a recommendation for fixing the issue

### Example Report Format

```
Subject: [SECURITY] Cookie Compliance Manager - XSS in Settings Page

Description:
A cross-site scripting (XSS) vulnerability exists in the banner message field.

Impact:
An authenticated admin user could inject malicious JavaScript.

Steps to Reproduce:
1. Go to Settings → Cookie Compliance
2. Enter <script>alert('XSS')</script> in the banner message
3. Save settings
4. View frontend

Affected Versions:
- Version 1.0.0

Environment:
- WordPress 6.7
- PHP 8.1
- Apache 2.4

Suggested Fix:
Add additional escaping to the banner message output.
```

---

## Response Timeline

We are committed to addressing security issues promptly:

| Timeline | Action |
|----------|--------|
| **24 hours** | Initial response acknowledging receipt |
| **48 hours** | Preliminary assessment of severity |
| **7 days** | Regular updates on investigation progress |
| **30 days** | Target resolution and patch release |

**Note:** Complex issues may require more time. We'll keep you informed throughout the process.

---

## Vulnerability Disclosure Process

1. **Report Received:** We acknowledge receipt within 24 hours
2. **Investigation:** We investigate and assess the vulnerability
3. **Development:** We develop and test a fix
4. **Private Disclosure:** We share the fix with the reporter for verification
5. **Release:** We release the patched version
6. **Public Disclosure:** After 90 days or patch release (whichever comes first), we may publish details

---

## Security Update Policy

### Critical Vulnerabilities
- **Response:** Immediate (within 24-48 hours)
- **Release:** Emergency patch within 7 days
- **Notification:** Email to all users via WordPress.org, blog post, GitHub

### High Severity
- **Response:** Within 48 hours
- **Release:** Patch within 14 days
- **Notification:** Standard update notification

### Medium/Low Severity
- **Response:** Within 1 week
- **Release:** Included in next scheduled release
- **Notification:** Mentioned in changelog

---

## Security Best Practices for Users

To maintain security when using Cookie Compliance Manager:

1. **Keep Updated:** Always use the latest version
2. **WordPress Core:** Keep WordPress core updated
3. **PHP Version:** Use PHP 7.4 or higher (8.0+ recommended)
4. **HTTPS:** Use SSL/TLS encryption on your site
5. **Admin Access:** Limit admin access to trusted users only
6. **Backups:** Maintain regular backups
7. **Security Plugins:** Consider using a WordPress security plugin
8. **Monitor:** Check for updates regularly

---

## Security Checklist

Cookie Compliance Manager has been reviewed for:

- [x] SQL Injection vulnerabilities
- [x] Cross-Site Scripting (XSS)
- [x] Cross-Site Request Forgery (CSRF)
- [x] Remote Code Execution (RCE)
- [x] Local File Inclusion (LFI)
- [x] Authentication bypass
- [x] Authorization issues
- [x] Sensitive data exposure
- [x] Insecure deserialization
- [x] XML External Entities (XXE)

---

## Hall of Fame

We appreciate security researchers who responsibly disclose vulnerabilities:

<!-- Contributors who report security issues will be listed here -->

*No security vulnerabilities reported yet.*

---

## Contact Information

- **Security Email:** vishavjeet@vishavjeet.in
- **Plugin Author:** Vishavjeet Choubey
- **Website:** https://vishavjeet.in
- **Plugin Page:** https://wordpress.org/plugins/cookie-compliance-manager/
- **GitHub:** https://github.com/vishavjeet-in/cookie-compliance-manager

---

## Additional Resources

- [WordPress Plugin Security](https://developer.wordpress.org/plugins/security/)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)

---

## Legal

By reporting a vulnerability, you agree to:

1. Not publicly disclose the vulnerability until a fix is released
2. Not exploit the vulnerability beyond what is necessary to demonstrate it
3. Act in good faith toward our users' privacy and data
4. Not violate any laws in your research

We commit to:

1. Respond to your report in a timely manner
2. Keep you informed about our progress
3. Credit you in our security advisory (unless you prefer to remain anonymous)
4. Not take legal action against researchers who follow this policy

---

## Changelog of Security Fixes

### Version 1.0.0 (Current)
- Initial release with comprehensive security measures
- Full input sanitization implemented
- Output escaping on all dynamic content
- CSRF protection via WordPress nonces
- Capability checks on all admin actions

---

**Last Updated:** November 22, 2025

Thank you for helping keep Cookie Compliance Manager and its users safe!
