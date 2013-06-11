=== Amazon Product in a Post Plugin ===
Contributors: prophecy2040
Donate link: http://www.fischercreativemedia.com/donations/
Tags: Amazon, Affilate, Product, Post, Page, Quick Post, Amazon Associate, Monetize, ASIN, Amazon.com, Shortcode
Requires at least: 2.5
Tested up to: 3.5.1
Stable tag: 3.5.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Quickly add formatted Amazon Products to post/page by using just the Amazon ASIN (ISBN-10). Great for monetizing your posts.

== Description ==
**NOTE:** Due to changes in Amazon's Product Advertising API Terms of Use, this plugin requires you have an AWS Access Key ID and Secret Access Key of your own to use. See FAQs for links to sign up.

Amazon Product In a Post Plugin is used to to quickly add a formatted Amazon Product/Item to a post or page by using just the Amazon product ASIN (also known as the ISBN-10).

What can you use it for? With this plugin you can:

* Add any Amazon product or item to an existing Post (or Page) or custom Post Type (using the shortcode).
* Monetize your blog posts with custom Amazon Product and add your own Reviews, descriptions or any other thing you would normally want to add to a post - and still have the Amazon product there.
* Easily add only the items that are right for your site.
* Add the product to the TOP of the post content, the BOTTOM of the post content, or make the post content become part of the product layout (see screenshots for examples)
* You can now add as many products as you want to any existing page/post content using a Shortcode - see Installation or FAQ page for details.
* Limited French, German and Spanish language support for text labels in displayed product.

If you have an Amazon Affiliate account and you don't think the available Amazon widgets are that great or are what you need, then this plugin might be for you.

**How it Works:**
The plugin uses the newly revised (and re-named) Amazon Product Advertising API.

To use the plugin, you must have an 1). an Amazon Affiliate Account, and 2). Amazon Product Advertising API keys. You can sign up here if you do not have them - it is free and not extremely difficuly (takes about 15 total for both). Once you have an account, install the plugin, then enter your Amazon Associate ID in the plugin options page. Then enter you API keys. You are now ready to start adding products to your post!

*PLEASE NOTE:* If you DO NOT add your own custom Associate ID, you WILL NOT get credit for any purchases made from your product posts - so don't set up products on a live site unless you enter that first - or you may lose out on some referral $$. You have been warned!


== Installation ==
After you intall the plugin, you need to set up your Amazon Affiliate/Associate ID in the Options panel located in the AMAZON PIP menu under AMAZON PIP OPTIONS. 

An AWS Access Key ID and Secrect Access Key REQUIRED. THere is no longer built in keys by default. This change is due to the Amazon Terms of Use agreement. 

No additional adjustments are needed unless you want to configure your own CSS styles. Styles can be adjusted or removed in the Options Panel as well.

*WARNING: If you do not add your Amazon Affiliate ID, you will NOT get credit for purchases made using this Plugin.*

**Adding products is a simple process.**

= To Add a product to an existing post: =
*  Go into the full edit mode for the post or page (Post/Edit then select the post).
*  Under the Content editor, there will be a box called Amazon Product In a Post Settings. Maximize it if it is not already fully visible.
*  There are 3 sections to fill out, Active, Location and ASIN (See below for adding via Shortcode).
*  If Active is checked, the product will be live if the post is published.
*  For the Location, pick the spot you want the product to show.
*  Then enter the ASIN in the ASIN field.
*  Save or Publish the post.

= To Add a New Product Post, you have 4 options: =
* You can add a new post the regular way (Post / Add New / fill out the items /Publish), and them add the product as outlined above
* You can use the Quick Add Product Feature (called New Amazon Post in the Amazon Product menu). This is the fastest method for adding a quick product with minimum text. This Method will create a New Post in the selected categories with the select post type.
* You can add a Product via Shortcode to an existing Page/Post or Custom Post Type.
* You can add products to the sidebar widgets using the shortcode method.

= To Add a product Via Shortcode: =
* In the Content editor, add **[AMAZONPRODUCTS asin="XXXXXXXXXX"]** where XXXXXXXXXX is the Amazon ASIN/ISBN10 number.
* To add many products at a time, Separate the ASINs with a comma, like so (up to 10 per shortcode): **[AMAZONPRODUCTS asin="XXXXXXXXXX,XXXXXXXXXX,XXXXXXXXXX,XXXXXXXXXX"]**
* Update the Page/Post. You can add as many products this way as you like. Just keep in mind that there is a call made to the Amazon API for each shortcode added, so adding too separate shortcodes many could cause a noticable increase in page loading time.
* Some settings in the individual page/post options will affect the shortcodes

== Frequently Asked Questions ==
See the Installation Page for details on setting up the Products. There is a dynamic FAQs feed in the plugin that will allow for adding new FAQs as they come up. 
More detailed FAQs will come as questions/solutions arise.

= MY PROCUCTS DO NOT DISPLAY! What is wrong? =
* It could be several things. The FIRST thing to check is the AMAZON Access Key ID and Secret Access Key in the options page. If they are blank, you need to sign up for your own from amazon. You can sign up here http://aws-portal.amazon.com/gp/aws/developer/account/index.html to get your own. Without it, your products will display.
* Also check to make sure you are using Product Advertising Keys. There are many different prodcts from Amazon that require API keys - and this one needs the Product Advertising API Keys specifically.
* Another common cause, is the method of the api call. By default, it is set to use "file_get_contents", but since some servers do not allow this method you may need to use the older CURL method. Change this in the options page and then check to see if the problem is resolved.

= My Products only display a blank image and a "()" for the title - what is wrong? =
* See the above question and answer - the cause and fix are the same.

= Can I uninstall everything if I don't want to use the plugin anymore? =
* Yes. With version 2.0 forward, if you want to remove EVERY TRACE of the plugin, you can (There is also a less permanent removal if you you think you may want it back in the future).
* The full removal can be initiated by checking "Uninstall when deactivated" AND "Remove ALL traces when uninstalled" in the options page - then deactivate the plugin as usual. This will remove ALL traces of the plugin... that means: the database, options, post meta values, and shortcodes in posts and pages.
* The partial removal can be initiated by checking only the "Uninstall when deactivated" option, then save and decativate the plugin as usual. This will remove the database (which is only chached products) and the basic options. All meta and shortcodes will remain.

= How do I add a product? =
* To Add a New Product Post, you have 3 options:
* You can add a new post the regular way (Post / Add New / fill out the items /Publish), and them add the product as outlined above
* You can use the Quick Add Product Feature (called Amazon PIP in the Amazon PIP menu). This is the fastest method for adding a quick product with minimum text. This Method will create a New Post in the selected categories.
* You can add a Product via Shortcode to an existing Page/Post.

= How do I use the Shortcode? =
* To Add a product Via Shortcode, go to the Content editor for an existing page/post (or create a new post/page), add **[AMAZONPRODUCTS asin="XXXXXXXXXX"]** where XXXXXXXXXX is the Amazon ASIN/ISBN10 number. Then update or save the Page/Post. You can add as many products this way as you like (up to 10 per shortcode). Just keep in mind that there is a call made to the Amazon API for each shortcode added, so adding too many products could cause a noticable increase in page loading time.

= My Products are coming up blank - what do I do? =
* Most likely the reson is your server does not support external URL call using the file_get_contents() function. If you don't have access to your sites php.ini file to change the settings (most people don't), then on the options pages for the plugin, change the API get method to "CURL" and save the settings. This will fix the problem in most cases.
* Another common cause is that the API keys are not the correct ones. Be sure you have the Product Advertising keys - if they are not, the plugin will not return an error and not display the products.

= I Want to Change the Look of the Products - can you do that for me? = 
* I would love to say yes, but we cannot make custom changes for everyone that askes - there are just not enough hours in the day - if you REALLY want us to do it for you, contact us - and for a small fee, we will give you whatever look/style you want. Keep in mind that your theme has a lot to do with the way your products look. 
You can tweak the look yourself if you have experience with CSS styles. The options page have a style for each element displayed in the product, so you can tweak it however you like. If you screw it up - just reset it back to the default and start over.

= My product shows up fine, but there is no price - what's wrong? =
* Some products on Amazon.com are provided my external vendors. If a vendor want to hide the price from others until a certain price is reached, they have that ability. When that occurs, Amazon will NOT send the price in the API call - so no price will be displayed. You can edit the custom message that is displayed in the options panel if you wish.

= Great Plugin! How do I donate to the cause? = 
* Excellent question! The plugin is provided free to the public - you can use it however you like - where ever you like - you can even change it however you like. Should you decide that the plugin has been a great help and want to donate to our plugin development fund, you may do so [here](http://fischercreativemedia.com/wordpress-plugins/donate/ "here").

== Screenshots ==

1. Amazon Products displayed on a page with the Post Content as part of the product (option 3 when setting up product). Note, this is using the default styles with the Fusion 3.1 theme by [digitalnature](http://digitalnature.ro/ "digitalnature"). Different theme styles may cause your layout to look VERY different.
2. Admin post/page edit box used for adding a product.
3. Amazon Quick Product Post option for quickly adding the basic information needed for a product. This options automatically creates the corresponding Post for the product.
4. Plugin Options Panel.
5. Plugin Menu.
6. Shortcode Addition to allow unlimited products in post content.

== Changelog ==
= 3.5.1 =
* Basic template integration (for future use - or if you are good at hooks and filters and can figure it out on your own - go ahead - the structure is there!)
* Removed traces of Developer Keys at Amazon's request.
* Added Amazon Elements shortcode to add bits and pieces of a product to a post - very hady for making a custom layout.
* Added Amazon Cache Viewer - allows you to manually delete a product cache to initate a new amazon Call. Caches are stored for 60 minutes and updated as the calls are needed.
* Added Getting Started page to help users set up affiliate and API Key signup. This is becoming more and more complex, so a little help was needed.
* Added Shortcode Help Page to give examples of how to use the shortcodes effectively.
* Added feed driven FAQs page - easier for me to update FAQs on the fly that way.
* Added several Filters and Hooks - will lay them all out in next revision.
	
= 3.1 to 3.5.0 =
* development versions.
= 3.0=	
* Added New Shortcode [AMAZONPRODUCTS] (instead of [AMAZONPRODUCT=B0084IG8TM]) - old method will still work 
* Added Bulk API Call to limit number of calls to API (can use up to 10 ASINs at one time)
* Updated the depricated function calls
* Increased API return values for use in theme - puts all items in the array now
* Updated styles to include some new elements
* Updated database field for amazoncache table to allow for larger data size of cached XML body (as API can now return up to 10 itmes at a time)
* Updated aws_request function
* Wrapped generic helper functions in !function_exists wrapper to eliminate conflicts with some other Amazon plugins.
* Updated Install function with styles and database upgrade
* Added amazon icon button to editor to easily add shortcode.
* Added new parameters to shortcode to allow custom additions to any post/page:
	* asin – this is the ASIN or ASINs up to 10 comma separated
	* locale – this is the Amazon locale you want to get the product from, i.e., com, co.uk, fr, etc. default is your plugin setting
	* desc – using 1 shows Amazon description (if available) and 0 hides it – default is 0.
	* features – using 1 shows Amazon Features (if available) and 0 hides it  - default is 0.
	* listprice – using 1 shows the list price and 0 hides it – default is 1.
	* partner_id – allows you to add a different parent ID if different for other locale – default is ID in settings.
	* private_key – allows you to add different private key for locale if different – default is private key in settings.
	* public_key – allows you to add a different private key for locale if different – default is public key in settings.
* New Shortcode would be used like this:
	* If you want to add a .com item and you have the same partner id, public key, private key and want the features showing:
	**[AMAZONPRODUCTS asin="B0084IG8TM" features="1" locale="com"]**
	* If you want to add a .com item and you have a different partner id, public key, private key and want the description showing but features not showing:
	**[AMAZONPRODUCTS asin="B0084IG8TM,B005LAIHPE" locale="com" public_key="AKIAJDRNJ6OU527HKGXQ" private_key="Nz3FYyeVysc5yjcZwrIV3bhDti/OGyRHEYOWO005" partner_id="wordseen-20"]
	*If you just want to use your same locale but want 2 items with no list price and features showing:
	**[AMAZONPRODUCTS asin="B0084IG8TM,B005LAIHPE" features="1" listprice="0"]
	*If you just want 2 products with regular settings:
	**[AMAZONPRODUCTS asin="B0084IG8TM,B005LAIHPE"]
	*If you want to add text to a product:
	**[AMAZONPRODUCTS asin="B0084IG8TM"]your text can go here![/AMAZONPRODUCTS]

= 2.0 =
* Added Database for caching api calls (10/20/2010)
* Added Options for Private and Public Keys in the options panel. (10/22/2010)
* Added Options for Complete Removal and Partial Removal of Plugin on Deactivate. (10/24/2010)
* Added new error checks to comply with API changes.
* Added new Display checks to not display anything on error (except an HTML Comment in the code) (10/24/2010)
* Fixed option naming convention to resolve a few issues with previous versions (10/24/2010)
* Fixed some code to resolve headers sent issues. (10/23/2010)
* Modified Style calls to fix issues with earlier versions upgrading to newer version (10/23/2010)
* Updated FAQs (10/24/2010)

= 1.9.1 =
* Fix to WordPress Core location assumption. Caused Problem when WP core was located outside root. (1/3/2010)
*  Added German Language. (special thanks to Henri Sequeira for translations). (1/3/2010)
= 1.9 =
* fix to not defined function error. (12/28/2009)
= 1.8 =
* Added Fix for users without encoding functions in PHP4 to be able to use. This may have caused some problems with users on 1.7. (12/21/2009)
* Added Options for Language selection. (12/21/2009)
* Added French Language and buttons (special thanks to Henri Sequeira for translations). (12/21/2009)
* Added Lightbox type effect for "View Larger Image" function.(12/22/2009)
* Modified Style Call to use a more WP friendly method to not rely on a "guess" as to where the core WP files are located. (12/22/2009)
* Fixed Open in new window functionality - was not working 100% of the time. (12/22/2009)
= 1.7 = 
* Add Curl option for users that cant use file_get_contents() for some reason or another. (12/1/2009)
* Added Show on Single Page Only option to Options Page.(11/30/2009)
* Added a way to change encoding display of prices from API if funny characters are showing.(12/1/2009)
= 1.6 =
* Added Options to let admin choose if they want to "Hook" into the_excerpt and the_content hooks in Main Options with override on each post/page.(10/3/2009)
* Added Open in a New Window Option (for Amazon button) in Main Options with override on each page/post.(10/3/2009)
* Added "Show Only on Single Page" option to individual post/page options.(10/4/2009)
* Added Shortcode functionality to allow addition of unlimited products in the post/page content.(10/4/2009)
* Added "Quick Fix - Hide Warnings" option in Main Options. Adds ini_set("display_errors", 0) to code to help some admins hide any Warnings if their PHP settings are set to show them.(10/3/2009)
* Fixed Array Merge Warning when item is not an array.(10/3/2009)
* Fixed "This Items not available in your locale" message as to when it acatually displays and spelling.(10/3/2009)
* Added Options to let admin add their own Error Messages for Item Not available and Amazon Hidden Price notificaton.(10/3/2009)
* Updated Default CSS styles to include in Stock and Out of Stock classes and made adjustments to other improper styles. (10/3/2009)
= 1.5 = 
* Remove hook to the_excerpt because it could cause problems in themes that only want to show text. (9/17/2009)
= 1.4 =
* Added menthod to restore default CSS if needed - by deleting all CSS in options panel and saving - default css will re-appear in box. (9/16/2009)
= 1.3 =	
* Added new feature to be able to adjust or add your own styles. (9/16/2009)
= 1.2 =
* Fix to image call procedure to help with "no image available" issue. (9/15/2009)
= 1.1 =
* Minor Fixes/Spelling Error corrections & code cleanup to prep for WordPress hosting of Plugin. (9/14/2009)
= 1.0 =
* Plugin Release (9/12/2009)
== Upgrade Notice ==

= 3.5.1 =
* Adds many new features, fixes several bugs and implements some mandatory Amazon affilate Terms of Use changes.

= 3.1.0 =
* 3.0.0 up to 3.1.0 where devlopement versions. Figured I would just up the version to match WordPress version after this point.
