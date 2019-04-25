=== Delete Thumbnails & Intermediate Images ===
Contributors:      davidsword
Donate link:       http://davidsword.ca/🍺/
Tags:              delete, thumbnails, media, images, library, resized, delete sizes, image sizes, remove images, clean uploads, clean file sizes
Requires at least: 5.1.0
Tested up to:      5.1.1
Stable tag:        2.3.0

Find and delete thumbnails & intermediate images from your Media Library.

== Description ==

= Delete thumbnails & intermediate images from your Media Library =

* 🗑 Delete some or all of WordPress's intermediate images (thumbnails, medium, and large, plus extra ones Plugins/Themes make secretly)
* 💣 This can clear thousands of unwanted files from your uploads directory
* 📈 Useful if you've had lots of different theme/plugins over the years, and inadvertently accumulated a vast number of intermediate images that are no longer used by your site
* 🙅🏼 Deleting is **permanent.** There's no undo. Be careful!
* ⚙ You can use [this plugin](http://wordpress.org/plugins/regenerate-thumbnails/) to regenerate your Media Library after (as Wordpress will need the default sizes)
* 🍺 This was a **re-written plugin**, the bad reviews were correct for the old versions, but not this current version. If you experience any issues, please open a [support request](https://wordpress.org/support/plugin/delete-thumbnails) or [github issue](https://github.com/davidsword/delete-thumbnails/issues), I'm happy to help fix any issues and help plugin grow.
* 🙏 Contributors: please do! Looking forward to your [PRs](https://github.com/davidsword/delete-thumbnails/pulls)!

Read More:

* https://davidsword.ca/projects/delete-thumbnails/
* https://wordpress.org/plugins/delete-thumbnails/
* https://github.com/davidsword/delete-thumbnails/

== Installation ==

1. Install the plugin from your Plugin browser, or download the plugin and extract the files and upload `delete-thumbnails` to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. View the *Delete Thumbnails* interface under *Tools* in your WordPress Admin


== Frequently Asked Questions ==

= I have > 1000 images, this plugin doesn't work =

Sadly, yes. This plugin was originally written for smaller "normal" sized sites. This plugin was not designed for larger sites. If your site has a lot of images, or a lot of intermediate images, it will probably not work.

In the future this plugin will be redesigned to handle the library items in chunks instead of all at once which will make this plugin able to handle larger sites.

= What is a intermediate image? =

This may often be interchanged with "thumbnails" or "resized" images. Intermediate images are images WordPress generates when you upload an original upload. The default sizes of these Intermediate images are in **wp-admin > Settings > Media**. Uploading a single image to WordPress may result in at least 3 new smaller files being created. These images are created because original uploads are often far bigger in pixels and in file size, WordPress will use these smaller images throughout your site instead of the big original one.

For example, while browsing the Media Library, you're not viewing the original files, you're viewing the `thumbnail` intermediate image size WordPress created so the page loads as fast as possible.

= What parameters are used to determine if a image is an intermediate image? =

After looking at all files in the WordPress uploads directory, a file is determined as resized when three criteria are meet:

1. The file is an image (a jpg, png, gif)
1. The file URL is not an original upload file from the WordPress Media Library
1. The filename ends with `-###-###.`

= What is this warning about `chmod` about? =

The method of deletion only works when the server allows PHP to edit the folders contents. CHMOD is the permission settings for files and folders. If you've received a warning of this, the CHMOD on the upload directory is too low.

= It says I have no resized images, but I do =

Please note this is a work in progress plugin, this plugin was developed and tested in only two environments in spare time. Variables on your setup may of not been considered while developing. Please open a request in the Support tab or a Github issue and provide as much info as you're willing to give to help resolve this issue and ensure this plugin works on all setups:

* WordPress & PHP version
* Location of directory
* Wether or not files are stored in year/month sub folders
* ect.

= How do I backup my files? =

You must backup your files as this plugin DELETES files permanently, you can ignore and bypass, however you should always [backup your WordPress installation](https://codex.wordpress.org/WordPress_Backups#Backing_Up_Your_WordPress_Site). It's quick and easy and saves you a lot of headache down the road.

= Why is there a discrepancy between files found, originals, and intermediate? =

You may see a notice like:

> Browsing: /wp-content/uploads/ **231** files were found, **47** are original images. **170** images were detected as resized images and are listed below:

You'll notice that `47+170` does not equal `231`. This is not an error, it is because some files that were found were not images, these were files like .PDFs, .txt, or even .php files. In future versions of this plugin this will be simplified, not including non-image files in the total count.

== Screenshots ==

1. List of all resized images in uploads directory with options to select and delete

== Changelog ==

= 2.3.0 =
* April, 2019
* added security improvements throughout
* change readme (capital P dangit!)
* added `if !defined ABSPATH` security
* change `init` and class firing
* change version numbering logic
* linted all code to WPCS/VIPCS
* change to use `scandir()` instead of `readdir( opendir() )`
* add text domain and language support, proper i18n
* add escape for all output
* fix plugin to run only on its admin menu page
* add cache the Get the Media Library results
* improve validation of file before deletion
* add compat for >WP5.0
* improve notifications to be more clear about what exists and what was done

= 2.2.0 =
* July 6, 2017
* Fixed 3 instances of PHP short tags which would break plugin on most server setups

= 2.1.0 =
* June 27, 2017
* Removed image header argument (seeing if image was compressed at WordPress's default 82%) as it only works with resized JPG's, not PNGs

= 2.0.0 =
* June 25, 2017
* Project revival
* Code overhaul/rewrite
* Added better assets
* Much cleaner and WordPress native looking interface, no more code-line look
* Readme, UI, and inline documentation corrected and improved
* Improved logic of deletion
* Improved logic for form submission with low `max_input_vars` values in mind
* better detection of thumbnails & cross checking media library attachments

= 1.0.0 =
* Sept 29, 2014
* Public Launch

= 0.1.0 =
* July 6, 2014
* Initial Build, private use

== Upgrade Notice ==

= 2.1.0 =
* all clear, blue sky

= 2.0.0 =
* all clear, blue sky

= 1.0.0 =
* all clear, blue sky

== Contributors ==

Install required PHPCS by running:

* `$ composer install`

The following grunt tasks are available during development:

* `$ grunt i18n` (containing `addtextdomain` and `makepot`)
* `$ grunt readme` (containing `wp_readme_to_markdown`)
* `$ grunt` run the two commands above

For tests (on a VVV setup):

* `$ cd /path/to/delete-thumbnails/`
* `$ ./bin/install-wp-tests.sh phpunit root root`
* `$ phpunit`
