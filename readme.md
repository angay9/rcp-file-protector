# RCP File Protector Plugin
This plugin allows you to protect files and only make them available to people who are in your membership.

## Requirements
This plugin requires you to have [Restrict Content Pro](https://restrictcontentpro.com/ "Restrict Content Pro") (RCP) plugin installed.

It has been tested with RCP version 3.2.3, Wordpress 5.2.5 and PHP 7.2.

I haven't tested it with previous versions, but I'm almost sure that it shoul work fine with RCP 3.\*, WordPress 5.\* and PHP 7.\*.

## How to use it

#### Video
Check out this video tutorial - https://www.youtube.com/watch?v=pFC9PP3481

### Matching patterns that you can use:

1. `2019/11/twitter.png` - will match  **wp-content/uploads/2019/11/twitter.png**
2. `2019/12/` - will match any file/folder inside **wp-content/uploads/2019/12/**
3. `_private\/.*` (regular expression) - will match any file inside any folder named "_private" in **wp-content/uploads**
4. `_private\/.*\.pdf` (regular expression) - will match any pdf file that exists in any "_privae" folder either directly or nested.
So **wp-content/uploads/_private/test.pdf**, **wp-content/uploads/_private/12/test.pdf**, **wp-content/uploads/2019/_private/12/test.pdf** will all be matched.
5. `.*\.(pdf|doc|docx|zip)` (regular expression) - will match any pdf, doc, docx, zip file inside any folder in **wp-content/uploads**

**Please keep in mind that in order to use regular expression matching patterns above, you need to check the "Use Regular Expression" checkbox.**


## Available fitlers and actions
### Filters
1. `add_filter('rcp-file-protector/admin/settings/protection_levels', function ($levels) {})` - Filter all the available protection levels before the admin settings page is rendered.

2. `add_filter('rcp-file-protector/admin/settings/memberships', function ($memberships) {})` - Filter all the available membership levels (RCP Membership levels) before the admin settings page is rendered.

3. `add_filter('rcp-file-protector/front/guard/protection_levels', function ($levels) {})` - Filter all the available protection levels before the file/folder requiest by the user will be processed. File/folder is a path that was protected in admin settings).

4. `add_filter('rcp-file-protector/front/guard/user_memberships', function ($memberships) {})` - Filter all the available membership levels (RCP) before the file/folder requiest by the user will be processed. File/folder is a path that was protected in admin settings).

5. `add_filter('rcp-file-protector/front/guard/is_allowed_access', function ($isAllowed) {})` - Modify the variable that checks if user is allowed to access the file.

6. `add_filter('rcp-file-protector/front/guard/before_save_protection_levels', function ($levels) {})` - Before protection levels are saved on admin settings page

7. `add_filter('rcp-file-protector/admin/settings/get_request_data', function($data) {})` - Get request ($\_POST) form data on admin page before settings will be validated and saved

### Actions
1. `add_action('rcp-file-protector/front/guard/before_abort', function() {})` - Before file guard will abort the request

2. `add_action('rcp-file-protector/front/guard/before_request_processed', function() {})` - After file guard will abort the request

3. `add_action('rcp_file_protector/admin/after_save_protection_levels', function () {})` - after protection levels have been saved on admin settings page.

## License
This plugin is released under GPL license

## Author
Author of this plugin is me, Andriy Haydash.
I help people build and launch successful membership websites.
Check out my website to find out more about me - [https://andriy.space](https://andriy.space)

