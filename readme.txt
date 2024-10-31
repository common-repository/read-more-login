=== Read More Login ===
Contributors: arildur
Donate link: https://www.readmorelogin.com/
Tags: read more, login, register, shortcode, access
Requires at least: 4.7
Tested up to: 5.8
Requires PHP: 5.5
Stable tag: 2.0.3
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/lgpl-3.0.html

Put a combined read more/login/registration form in your posts and pages. The visitors must log in or sign up to read more.

== Description ==

This plugin puts a combined read more/login/registration form in your posts and pages. The visitors must log in or sign up to read more. Remaining text will be protected and hidden from non-logged in users. Visitors can sign up and log in from inside articles and don't need to leave the page. Text fades out above the login form and will indicate more text can be read. This could increase conversion rate.

Live demo: [readmorelogin.com/live-demo](https://www.readmorelogin.com/live-demo/)

= Main features =
* Creates login/register form inside articles on posts and pages
* Visitors can log in from inside the articles, remaining text loads automatically
* Text will fade close to login form, this indicate more text be to read 
* Easy access to register button to sign-up new visitors
* The e-mail confirmation link sends the signed up user right back to the article
* Works with both pages and posts
* SEO friendly, Google search engines can read without login

= Membership handling =
* Login/registration forms inside articles
* Registration page
* Login/logout page
* Profile page
* Password recovery page
* E-mail confirmation
* E-mail notifications for registered users and admin

= Admin panels =
* Configurable forms and text messages
* Configurable linking
* Login/logout/register/password page redirect
* E-mail customization
* Registration status
* Sign-up statistics

More info, live-demo, user guides, documentation, support:
[readmorelogin.com](https://www.readmorelogin.com)

== Installation ==

The plugin is installed from your WordPress admin panel.

To put a login form in your posts and pages, put the shortcode [rml_read_more] on that page in between the text.

The admin page for the plugin lists more shortcodes available for membership handling.

Plugin has Configuration Wizard, which will guide you through the setup. The wizard will appear when you enter the plugin's admin page.

== Frequently Asked Questions ==
No questions have been asked yet. Please post questions in support forum.

== Screenshots ==

1. Log-in forms can be put on any post and pages. User must log in or register to read more.
2. If the visitor press Register button, he will be asked for additional information to sign up. First name and last name are optional and configurable fields.
3. After the visitor has completed the registration an e-mail will be sent to conform the e-mail address. A link sent in the e-mail will bring the new user right back to same page to continue reading.
4. Plugin also provides membership handling like profile page.
5. Plugin also provides password change page.
6. If user has the forgotten the username or password, he can ask for e-mail with password reset link.
7. Plugin also provides separate login/logout pages.
8. Plugin also provides separate register pages. E-mail will be sent to confirm e-mail address.
9. Registration using the separate registration page. First name and last name are optional and configurable fields.
10. Admin page for form setup of texts and fields. The preview will show how it will look like.
11. Admin page for style, color and animation settings. The preview will show how it will look like.
12. Admin page for setting e-mail texts.
13. Admin page for setting up links to user handling pages.
14. Status pages with registration and password recovery status.
15. Statistics pages indicates sign-up rate.

== Changelog ==

= 2.0.3 =
* Improved compatibility with Elementor and other content building plugins. Added end block shortcode and optional settings for page reloading. See https://www.readmorelogin.com/docs/ for details.
* Added option to reload whole page when logged in from inside articles.
* Fixed bug in e-mail verification link, which sometimes could have invalid access code.
* Fixed e-mail sender's name. When sending e-mails to users, the name was stuck to "WordPress".
* Fixed a missing CSS class in a placeholder table, which could cause conflict with some themes.
* Fixed issue with Gutenberg placeholder for shortcode blocks, it sometimes was copied to the front page.
* Fixed Screen Option in Admin Settings not able to enable or disable the Configuration Wizard.
* Fixed php 8 compatibility issues.
* Tested up to WordPress 5.8.

= 2.0.2 =
* Fixed short code not filtered on static front pages.
* Fixed e-mail variables not handling certain characters slashes and quotes.
* Fixed various page redirect errors. Redirect can now accept complete urls.
* Fixed registration not correctly handled when e-mail address existed.
* Fixed right-to-left styling.
* Fixed potential ajax security hole.
* Fixed potential security hole, visitor could flood debug logs.
* Fixed profile not giving error on wrong data. 
* Fixed tittle variable in e-mail not set.
* Fixed a typo in form text.
* Improved error message due to invalid nonce, due to time-out.
* Improved registration. Log out existing user when clicking registration link in e-mail.

= 2.0.1 =
* Missing user handling pages now redirects to WordPress default. 
* Fixed plain permalink not correctly handled.
* Fixed non-checked accept terms for registration not handled.
* Fixed various uncommon registration scenarios not handled.
* Fixed missing required registration user input not giving red border.
* Improved texts and language translations.

= 2.0.0 =
* Added configurable text for the forms.
* Added flexibility to forms, it can take html code too.
* Added optional fields to ask for first name and last name during registration.
* Added configurable styles and colors for the forms.
* Added options for animation effects for forms and text loading.
* Added more variables for forms and e-mail messages.
* Added configuration wizard to the admin panel.
* Rewrote plugin for better and more efficient code.
* Made plugin ready for internationalization and language translations.

= 1.1.3 =
* Fixed login error when used with some other plugin.
* Fixed error when reloading login form after successful login
* Added improved installation instructions in admin panel.

= 1.1.2 =
* Fixed various css issue causing forms to be wrongly rendered in some themes.

= 1.1.1 =
* Added link in the profile form to password change page.
* Added check if e-mail already in use when register.
* Fixed logout failing after login after registration completed.
* Fixed input data was reset if update failed.
* Fixed e-mail not correctly set in form after update.
* Fixed visitor unable to log out without confirm question after password change.
* Fixed missing handler for login if login form empty.
* Fixed bug mini widget wrongly changed.

= 1.1.0 =
* Added mini widget with login status and links.
* Added login, logout, register and password redirect.
* Improved text labels for forms.
* Added debug levels to plugin debug log, can be set in admin panel.
* Fixed broken logout/login due to changed session nonce.
* Rewrote membership handling to improved code.

= 1.0.2 =
* Fixed bug in registration statistics and added percent and totals.
* Fixed embedded links like youtube not expanded to iframes.
* Improving registration forms.
* Fixed problem with remaining text sometimes incorrectly loaded from wrong post after login.
* Added background color to read-more login form.

= 1.0.1 =
* Fixed expiration for registration and password recovery e-mail links.
* Fixed typos and improved text in admin panel.
* Removed unused option in admin panel.

= 1.0.0 =
* First version.

== Upgrade Notice ==
To upgrade plugin select update from your admin dashboard.
When upgrading the plugin it will read configuration settings from previous installed version. 
If you want to upgrade without using settings from previous installed version, first install version 2.0.0, then deactivate and delete it, then re-install version 2.0.0. (The previous version did not delete its settings, you need to have 2.0.0 installed to delete the previous settings.)
