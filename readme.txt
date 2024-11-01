=== WP Support Centre ===
Contributors: cloughit
Tags: support,tickets,ticket,jobs,job,help,email,helpdesk,pipe,files,attachments,bootstrap,jquery
Donate link: https://cloughit.com.au/donate/
Requires at least: 4.0
Tested up to: 4.8.1
Stable tag: 2017.12.02
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The WP Support Centre is a helpdesk plugin which can be used by clients to submit support tickets via front end, or via email using email piping.

== Description ==

NOTE: This plugin is currently no longer maintained.  If you would like to take over this plugin please email support@cloughit.com.au

WP Support Centre is a helpdesk plugin which allows registered clients or guests to submit support tickets which can be managed by agents via the administration panel.  Clients / guests can select the relevant category and choose the priority of the ticket as well as being able to include file attachments.

Administrators / agents can manage and respond to tickets via the administration panel and responses are sent to users via email.  If Email Piping or IMAP is enabled clients can reply to these emails which will update the ticket and alert the agent via email.

WP Support Centre Features:

* Users can view their ticket history via the front end.
* Users can view and respond to tickets via the front end.
* Ability to add HTML content to tickets.
* File Attachments.
* Ticket Filtering.
* Agent / Supervisor Management.
* Delete Tickets.
* CC / BCC ticket replies.
* Change status, category & priority.
* Email piping / IMAP.
* Manage attachments.
* Allow tickets to be shared between users.
* Account Information section for users.
* Ability to set common Reply Templates which can be inserted into replies.
* Ability to set Recurring Tickets daily, weekly, fortnightly, monthly, quarterly or annually.
* Set custom Item Name eg: Ticket, Job, Task etc... (Default: Ticket)
* Set custom End User Name eg: Client, Customer, User etc... (Default: Client)
* Add notes to tickets without notifying user.
* Add private notes (visible only to administrators/agents)
* Create new ticket from thread.
* View statistics (status, category, priority, client)
* Set custom status colour.
* Set custom priority colour.
* Custom email templates.
* Select threads to include in replies.

https://cloughit.com.au/wordpress-plugins/

== Installation ==
Just install from your WordPress `Plugins | Add New` screen and all will be well.

Manual installation is very straightforward as well:

1. Upload the zip-file and unzip it in the /wp-content/plugins/ directory
2. Activate the plugin through the `Plugins` menu in WordPress
3. Go to `Support Centre` menu
4. Use shortcode [wpsc_tickets] to display front end portal

== Frequently Asked Questions ==
= Where can I report an error? =

https://cloughit.com.au/support/

= Help! It all looks wrong and nothing works! =

Navigate to {your_site_url}/wp-admin/admin.php?page=wpsc_admin_settings&wpsc_debug=true&wpsc_ebs=true

This will force enable Bootstrap support.  Settings can be adjusted under Support Centre -> Settings -> Help

= What information should I include when requesting support =

* A description of the problem, including screenshots and information from your browser`s error / debug console
* URL of your blog (you can turn WP Support Centre off, but should be willing to turn it briefly on to have the error visible)
* your WP Support Centre settings (including a description of changes you made to the configuration to try to troubleshoot yourself)
* the Theme used (including the Theme`s download link)
* optionally plugins used (if you suspect one or more plugins are raising havoc)
* you may be asked to provide an administrator login to your WordPress installation

= I want out, how should I remove WP Support Centre? =

* Disable the plugin
* Delete the plugin

= Email Piping =

NOTE: We recommend using IMAP settings now available in WP Support Centre

IMPORTANT: Email Piping support will be removed in favour of IMAP in an upcoming release.  We recommend changing to IMAP ASAP!

Email Piping is the method of sending email messages to a program allowing the program to process the message.
It is recommended that the email address being used be dedicated to email piping only.
You will also need to enable Email Piping within your Email Account / Hosting Server settings.

We can only advise on how to configure within cPanel. If you are using a different hosting method then you will have to identify how to set up email forwarding yourself I am sorry.

For cPanel:

1. Log in to your cPanel
2. Navigate to Mail and select Forwarders
3. Click on Add Forwarder
4. Enter the Address to Forward
5. Select Pipe to a program (you may need to click on Advanced Options to see the Pipe to a program option)
6. Enter the path to the piping.php file of the WP Support Centre plugin. For example: /home/username/public_html/wp-content/plugins/wp-support-centre/piping/piping.php
7. Click on Add Forwarder

= IMAP =

Account Types:
- Primary: This is the account where all emails are automatically processed by WP Support Centre.  Only one (1) Primary account can be set.
- Catch All: Allows read only access in the mailbox (Support Centre > Mailbox) and allows you to read emails and select to create a new ticket or add the email to an existing ticket as a new thread.

IMAP used with WordPress CRON system to check the Primary account for new emails every 2 minutes.  As the WordPress CRON is reliant on traffic to the site to trigger the CRON jobs you may wish to configure your own CRON job if your hosting allows.  For example:

wget -q -O - https://domain.com/wp-content/plugins/wp-support-centre/imap/imap.php >/dev/null 2>&1

== Screenshots ==
* Screenshots to be updated soon

1. Front end ticket submission form
2. Admin ticket table
3. Admin ticket submission form
4. Admin ticket view
5. Settings - General
6. Settings - Agent
7. Settings - Email
8. Settings - Status
9. Settings - Category
10. Settings - Priority

== Changelog ==
= TO DO LIST =

* Finish Reminders System
* Add ability to share account details with other users with permissions
* Update Screenshots (WAY out of date!)
* Create new YouTube video highlighting features of WP Support Centre
* Create client groups (ie users 1, 3 and 4 all word for Company A, users 2, 5, 6 work for Company B etc)
* Reply templates - auto insert data
* Groups Plugin Support (group clients by company / organisation)

= 2017.12.02 =

* DISCONTINUATION ANNOUNCEMENT

= 2017.09.15 =

* FIX: various small fixes

= 2017.09.06 =

* FIX: strpos() error on some installations
* FIX: various other small fixes

= 2017.06.11 =

* FIX: Add reset to email body variable between emails

= 2017.06.10 =

* FIX : Seperate plain / html email parts 

= 2017.06.09 =

* FIX: Non insertion of body text

= 2017.06.08 =

* FIX: Reading multipart email using IMAP if more than one PLAIN or HTML part, subsequent parts would overwrite rather than append.
* NEW: Basic IMAP documentation in readme.txt

= 2017.05.26 =

* MOD: Improved support for Divi theme

= 2017.05.25 =

* MOD: Added wpsc_ajax_url filter to allow for modifying the AJAX URL

= 2017.05.24 =

* FIX: General settings not saving

= 2017.04.25 =

* FIX: Ability to delete IMAP account not working

= 2017.04.06 =

* FIX: Agent Upload Images not working on some older WP installs

= 2017.04.05 =

* NEW: New menu item: 'Utilities'
* NEW: Ability to delete IMAP account
* NEW: Add check for Bootstrap to allow one-click (hopefully) fix for display issues
* MOD: Make 'Mailbox' menu item appear only if Catch All IMAP accounts are configured
* MOD: Move 'Settings' > 'Help' to 'Utilities' > 'Compatibility'
* MOD: Move 'Statictics' to 'Utilities' > 'Statistics'
* MOD: Support Centre Admin Bar links now redirect to configured support page for restricted users
* MOD: Remove Add Image From URL option in Media Uploader due to some email servers blocking emails with inline images

= 2017.03.06 =

* MOD: Adjust code to negotiate PHP Notices
* MOD: Adjust IMAP Connection for more efficient connections

= 2017.02.06 =

* MOD: Change cookie handling to work around other plugins / themes early output causing headers already sent error

= 2017.02.05 =

* NEW: Added switch to force enable bootstrap support (/wp-admin/admin.php?page=wpsc_admin_settings&wpsc_debug=true&wpsc_ebs=true)

= 2017.02.04 =

* FIX: Missing files from commit

= 2017.02.02 =

* NEW: WPSC will now check for existing file before uploading again to save server space
* NEW: Ability to enable / disable Bootstrap for back and / or front end for better compatibility
* MOD: Thread data saved in database as base64 encoded value
* MOD: Styling changes
* MOD: WPSC will now automatically detect server configuration string
* FIX: Modal positioning to bring to front of admin menu
* FIX: Button dropdown not working

= 2016.12.16 =

* FIX: Divi theme compatibility loading on non-Divi themes causing JavaScript errors
* FIX: Improved Bootstrap support (front end)

= 2016.09.17 =

* NEW: Ability to change 'Support Centre' text
* NEW: Ability to disable front end file upload

= 2016.09.03 =

* FIX: removed left over debug message

= 2016.09.01 =

* FIX: Client Name autocomplete will now only return registered users with a valid First and Last Name
* FIX: Email Settings Page IMAP Fields incorrectly detected as required

= 2016.08.03 =

* FIX: IMAP Subject Encoding

= 2016.07.25 =

* This release incorporates many, many changes to improve the working and performance of WP Support Centre. These include:
* - Many fixed for small bugs
* - Changed ajax handler from WordPress to a custom handler which has resulted in a massive performance improvement.
* - Added ability to use IMAP to read emails rather than Email Piping if desired (a lot more stable and easier to configure than Email Piping)
* - Started work on adding the ability to set reminder notifications. This will currently alert if a ticket has missed its SLA or if a ticket has been idle for 7 days. The reminders function will be refined further for a later release.
* - Multiple other small enhancements and changes

= 2016.06.13 =

* FIX: Admin modals not displaying
* FIX: Email piping errors
* FIX: Include all threads checkbox not working
* FIX: Removed experimental features included by mistake
* FIX: Added modal for Email Piping Catch All
* MOD: Modify New Recurring Ticket form layout and add placeholders
* NEW: Updated background / text colour updates every 30 seconds rather than when table reloaded

= 2016.06.11 =

* RE-RELEASE: Too many changes to list, plugin has been merged with WP Support Centre Pro and all Pro features are now available in this free version.
* DECOMMISION: WP Support Centre Pro has been decommisioned

= 2016.03.05.2 =

* FIX: Catch DataTables re-initialisation

= 2016.03.05.1 =

* FIX: Email piping multisite vs single site detection

= 2016.03.05 =

* FIX: Improved email piping handling

= 2016.02.16 =

* FIX: Fix include for admin tickets functions

= 2016.01.22 =

* FIX: Email headers not resetting between admin and client notifications

= 2016.01.11 =

* NEW: Added ability to delete attachments in attachment tab
* NEW: Allow tickets to be shared between users
* MOD: Better attachment handling

= 2016.01.04 =

* NEW: Added user account information to allow recording notes for clients, visible to admin and client
* NEW: Added preview to attachments in attachment tab
* NEW: Added quick link to view client ticket history from open ticket
* FIX: Phone not saving from new ticket save (admin)

= 2015.12.31 =

* NEW: Added quick view tabs for ticket participants / attachments (admin)

= 2015.12.29 =

* NEW: Added support for modification of ticket subject (Pro)

= 2015.12.24 =

* FIX: SQL error in email piping without attachments

= 2015.12.17 =

* FIX: SQL error in email piping without attachments
* NEW: Added instructions for Email Piping setup

= 2015.12.16 =

* FIX: Include not loading under certain circumstances

= 2015.12.14 =

* FIX: Strip slashes on subject

= 2015.12.08 =

* NEW: Users can view their ticket history via the front end
* NEW: Users can view and respond to tickets via the front end
* NEW: Add setting to redirect to Thank You page for front end ticket submission

= 2015.11.29 =

* NEW: Improved notification template handling
* FIX: Prevent ticket table refresh on ticket open

= 2015.11.24 =

* NEW: Add new field to ticket creation form - phone
* FIX: Notification ticket URL's for free version

= 2015.11.23 =

* FIX: Further refinements for notification templates

= 2015.11.22 =

* FIX: Notification templates not created on some servers under certain conditions
* NEW: Added manual reset flags

= 2015.11.20 =

* NEW: Provisioned for WP Support Pro licencing
* NEW: Notifications for agents on new tickets / ticket replies

= 2015.11.19 =

* FIX: SQL query error in email piping
* NEW: Add support for Microsoft Word attachments in email piping

= 2015.11.18 =

* NEW: Refresh admin tickets table on ticket reply

= 2015.11.17 =

* FIX: Strip slashes from notification email subject
* FIX: Remove CC / BCC for Admin email notifications

= 2015.11.11 =

* NEW: Refresh admin tickets table on ticket change
* FIX: Email Piping Multi-Site Support

= 2015.11.10 =

* NEW: Added screenshots to readme.txt

= 2015.11.09 =

* FIX: Filter not filtering
* FIX: Notification not stripping slashes
* NEW: Email Piping Multi-Site Support

= 2015.11.07 =

* NEW: Added youtube clip to readme.txt
* FIX: Set default priority

= 2015.11.06.1 =

* FIX: Priority table creation issue

= 2015.11.06 =

* MISC: Remove unnecessary js
* NEW: Allow for detection of ticket replies via email piping for previous item names
* FIX: Form button actions
* FIX: Moved recurring ticket settings to Pro settings

= 2015.11.05 =

* NEW: Quick Find to open ticket by entering id
* FIX: Set updated by for ticket change event
* FIX: Admin ticket table redraw on page resize
* FIX: Email piping admin notification from name and email

= 2015.11.04 =

* NEW: Add new status - Reply Received
* NEW: Set ticket status to Reply Received upon receipt of reply from client
* FIX: Responsive on admin ticket page

= 2015.11.03 =

* Update readme.txt

= 2015.09.27 =

* Genesis