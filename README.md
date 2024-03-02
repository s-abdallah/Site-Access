1- Introduction
2- System Requirements
3- Installation
4- Configuration
5- Usage
    * Managing Site Content
    * Media Uploads
    * Log Monitoring
    * Security Management
    * Accessing Third-Party CRM
6- JSON Data Structure
7- Troubleshooting
8- Support and Feedback

1- Introduction
Custom CMS is a versatile tool designed to simplify the process of managing website content, media files, logs, and security. It also integrates seamlessly with third-party CRM systems to enhance customer relationship management.

2- System Requirements
* PHP 7.0 or higher
* Web server (e.g., Apache, Nginx)
* JSON support enabled in PHP

3- Installation
To install the Custom CMS, follow these steps:
* Download the CMS files from our repository.
* Upload the files to your web server directory.
* Configure the CMS settings (see Configuration section).
* Ensure proper file permissions for uploads and logs directories.

4- Configuration
Open the config.php file in the CMS directory and configure the following settings:
define("PROJECTNAME", ""); // the name of this project
define("DASHLOGMAX", 5); // how many log items to show on the dashboard page
define("ADMINEMAIL", ""); // email address of admin to receive security warnings etc.
define("TESTMODE", true); // whether or not we are in test mode which will remove login requirement, etc.
define("NOTIFYNEWUSERS", false); // send users an email when their account has been created
define("ALLOWWYSIWYG", true); // allow extra controls on certain text areas fields within forms
define("USESOCIAL", false); // will include the social class for access to certain features
define("SUPPORTEMAIL", ""); // the email address for all support and error emails
define("LOGMAXLENGTH", 100); // max length of log records
define("USEMAPS", true);
define("PAGIITEMS", 25); // define how many items is going to be in each page.
define("MAPSAPIKEY", "");

5- Usage
-- Managing Site Content
    * Login to the CMS dashboard using your credentials.
    * Navigate to the content management section to create, edit, or delete pages and articles.
    * Use the WYSIWYG editor to format and style your content.
    * Save your changes to update the website.

-- Media Uploads
    * Access the media management section to upload images and other media files.
    * Support for single and multiple file uploads.
    * Organize and manage your media library efficiently.

-- Log Monitoring 
    * Monitor system logs to track user activities, errors, and security events.
    * Log entries are timestamped and categorized for easy analysis.

-- Security Management 
    * Utilize built-in security features to protect your CMS instance.
    * Configure password policies, session timeouts, and access controls.

6- JSON DATA STRUCTURE 
The CMS utilizes JSON data structures to store various records, such as user profiles, content data, and configuration settings. JSON provides a flexible and efficient way to organize and access data within the system.

7- Troubleshooting
If you encounter any issues or errors while using the CMS, refer to the troubleshooting reach out to aibneliwa@gmail.com.

8. Support and Feedback
For support inquiries, feedback, or feature requests, please contact me at info@abdallaheliwa.com. I welcome your input and strive to improve my CMS based on user feedback.

Thank you for choosing our Custom CMS! 



