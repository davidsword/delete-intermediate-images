=== Delete Thumbnails ===
Contributors:      davidsword
Donate link:       http://davidsword.ca/🍺/
Tags:              delete, thumbnails, media, images, library, resized, delete sizes, image sizes, remove images, clean uploads, clean file sizes
Requires at least: 4.0.0
Tested up to:      5.0.0
Stable tag:        2.3.0

Find and delete thumbnails & intermediate images from your Media Library


== Description ==

= Delete thumbnails & intermediate images from your Media Library =

* 🗑 Delete some or all of WordPress's intermediate images (thumbnails, medium, and large, plus extra ones Plugins/Themes make secretly)
* 💣 This can clear thousands of unwanted files from your uploads directory
* 📈 Useful if you've had lots of different theme/plugins over the years, and inadvertently accumulated a vast number of intermediate images that are no longer used by your site
* 🙅🏼 Deleting is **permanent.** There's no undo. Be careful!
* ⚙ You can use [this plugin](http://wordpress.org/plugins/regenerate-thumbnails/) to regenerate your Media Library after (as Wordpress will need the default sizes)
* 🍺 This was a **re-written plugin**, the bad reviews were correct for the old versions, but not this current version. If you experience any issues, please open a [support request](https://wordpress.org/support/plugin/delete-thumbnails) or [github issue](https://github.com/davidsword/delete-intermediate-images/issues), I'm happy to help fix any issues and help plugin grow.
* 🙏 Contributors: please do! Looking forward to your [PRs](https://github.com/davidsword/sword-layouts/pulls)!


== Installation ==

1. Install the plugin from your Plugin browser, or download the plugin and extract the files and upload `delete-thumbnails` to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. View the *Delete Thumbnails* interface under *Tools* in your WordPress Admin


== Frequently Asked Questions ==

= What is a intermediate image? =

This may often be interchanged with "thumbnails" or "resized" images. Intermediate images are images WordPress generates when you upload an original upload. The default sizes of these Intermediate images are in **wp-admin > Settings > Media**. Uploading a single image to WordPress may result in at least 3 new smaller files being created. These images are created because original uploads are often far bigger in pixels and in file size, WordPress will use these smaller images throughout yoursite instead of the big original one.

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

== Screenshots ==

1. List of all resized images in uploads directory with options to select and delete


== Changelog ==

= 2.3.0 =
* Dec 9, 2018
* readme changes (capital P dangit!)
* added `if !defined ABSPATH` security
* changed `init` and class firing
* changed version numbering logic
* improved security
* linted all code to WPCS

= 2.2 =
* July 6, 2017
* Fixed 3 instances of PHP shorttags which'd break plugin on most server setups

= 2.1 =
* June 27, 2017
* Removed image header argument (seeing if image was compressed at WordPress's default 82%) as it only works with resized JPG's, not PNGs

= 2.0 =
* June 25, 2017
* Project revival
* Code overhaul/rewrite
* Added better assets
* Much cleaner and WordPress native looking interface, no more code-line look
* Readme, UI, and inline documentation corrected and improved
* Improved logic of deletion
* Improved logic for form submission with low `max_input_vars` values in mind
* better detection of thumbnails & cross checking media library attachments

= 1.0 =
* Sept 29, 2014
* Public Launch

= 0.1 =
* July 6, 2014
* Initial Build, private use


== Upgrade Notice ==

= 2.1 =
* all clear, blue sky

= 2.0 =
* all clear, blue sky

= 1.0 =
* all clear, blue sky


== TODO ==

- [ ] Add pagination for sites with more than 4000 images
- [ ] Add css animation/color to nag inputs when clicking disabled button
- [ ] Add `count($this->library)` result as 'exempt' in main info banner to assure Media Library items are safe
- [ ] Add size range filters (ie: delete resized that are within x - y restraints)
- [ ] Make `View` link work off of a Lightbox instead of a new browser tab
- [ ] Needs security audit and validate_file()
- [ ] cache the Get the Media Library results
- [ ] limit plugin to run only on its admin menu page
- [ ] fix i18n
- [ ] escape all output
- [ ] include a text domain
- [ ] check for WP5.0 Compat