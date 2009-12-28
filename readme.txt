=== Plugin Name ===
Contributors: Don Fischer
Donate link: http://fischercreativemedia.com/donate/
Tags: Amazon, Affilate, Product, Post, Page, Quick Post, Amazon Associate, Monetize, ASIN, Amazon.com, Shortcode
Requires at least: 2.5
Tested up to: 2.9.1
Stable tag: 1.8

Quickly add formatted Amazon Products to post/page by using just the Amazon ASIN (ISBN-10). Great for monetizing your posts.
== Description ==
Amazon Product In a Post Plugin is used to to quickly add a formatted Amazon Product/Item to a post or page by using just the Amazon product ASIN (also known as the ISBN-10).

What can you use it for? With this plugin you can:

* Add any Amazon product or item to an existing Post (or Page).
* Monetize your blog posts with custom Amazon Product and add your own Reviews, descriptions or any other thing you would normally want to add to a post - and still have the Amazon product there.
* Easily add only the items that are right for your site.
* Add the product to the TOP of the post content, the BOTTOM of the post content, or make the post content become part of the product layout (see screenshots for examples)
* *UPDATE 10/4/09* - You can now add as many products as you want to any existing page/post content using a Shortcode - see Installation or FAQ page for details.
* *UPDATE 12/22/09* - Added French and Spanish language support for text labels in displayed product.

If you have an Amazon Affiliate account and you don't think the available Amazon widgets are that great or are what you need, then this plugin might be for you.

**How it Works:**
The plugin uses the newly revised (and re-named) Amazon Product Advertising API. Older plugins that use Old API calls will no longer work as of 8/2009 because Amazon implemented a new security call procedure that makes many of them obsolete.

To use the plugin, you must have an Amazon Affiliate Account. You can sign up here if you do not have one - it is free and pretty easy to do. Once you have an account, install the plugin, then enter your Amazon Associate ID in the plugin options page. You are now ready to start adding products to your post!

*PLEASE NOTE:* If you DO NOT add your own custom Associate ID, you WILL NOT get credit for any purchases made from your product posts - so don't set up products on a live site unless you enter that first - or you may lose out on some referral $$. You have been warned!


== Installation ==
After you intall the plugin, you need to set up your Amazon Affiliate/Associate ID in the Options panel located in the AMAZON PIP menu under AMAZON PIP OPTIONS. 

No additional adjustments are needed unless you want to configure your own CSS styles. Styles can be adjusted or removed in the Options Panel as well.

*WARNING: If you do not add your Amazon Affiliate ID, you will NOT get credit for purchases made using this Plugin.*

**Adding products is a simple process.**

= To Add a product to an existing post: =
*  Go into the full edit mode for the post (Post/Edit then select the post).
*  Under the Content editor, there will be a box called Amazon Product In a Post Settings. Maximize it if it is not already fully visible.
*  There are 3 sections to fill out, Active, Location and ASIN (See below for adding via Shortcode).
*  If Active is checked, the product will be live if the post is published.
*  For the Location, pick the spot you want the product to show.
*  Then enter the ASIN in the ASIN field.
*  Save or Publish the post.

= To Add a New Product Post, you have 3 options: =
* You can add a new post the regular way (Post / Add New / fill out the items /Publish), and them add the product as outlined above
* You can use the Quick Add Product Feature (called Amazon PIP in the Amazon PIP menu). This is the fastest method for adding a quick product with minimum text. This Method will create a New Post in the selected categories.
* You can add a Product via Shortcode to an existing Page/Post.

= To Add a product Via Shortcode: =
* In the Content editor, add **[AMAZONPRODUCT=XXXXXXXXXX]** where XXXXXXXXXX is the Amazon ASIN/ISBN10 number.
* Update the Page/Post. You can add as many products this way as you like. Just keep in mind that there is a call made to the Amazon API for each product added, so addin too many products could cause a noticable increase in page loading time.
* Some settings in the individual page/post options will affect the shortcodes - you can use the 'open in new window, 

== Frequently Asked Questions ==
See the Installation Page for deatils on setting up the Products. 
A more detailed FAQ will come as questions arise.

= How do I add a product =
* To Add a New Product Post, you have 3 options:
* You can add a new post the regular way (Post / Add New / fill out the items /Publish), and them add the product as outlined above
* You can use the Quick Add Product Feature (called Amazon PIP in the Amazon PIP menu). This is the fastest method for adding a quick product with minimum text. This Method will create a New Post in the selected categories.
* You can add a Product via Shortcode to an existing Page/Post.

= How do I use the Shortcode? =
* To Add a product Via Shortcode, go to the Content editor for an existing page/post (or create a new post/page), add **[AMAZONPRODUCT=XXXXXXXXXX]** where XXXXXXXXXX is the Amazon ASIN/ISBN10 number. Then update or save the Page/Post. You can add as many products this way as you like. Just keep in mind that there is a call made to the Amazon API for each product added, so addin too many products could cause a noticable increase in page loading time.

= My Products are coming up blank - what do I do? =
* Most likely the reson is your server does not support external URL call using the file_get_contents() function. If you don't have access to your sites php.ini file to change the settings (most people don't), then on the options pages for the plugin, change the API get method to "CURL" and save the settings. This will fix the problem in most cases.

= I Want to Change the Look of the Products - can you do that for me? = 
* I would love to say yes, but we cannot make custom changes for everyone that askes - there are just not enough hours in the day - if you REALLY want us to do it for you, contact us - and for a small fee, we will give you whatever look/style you want. Kepp in mind that your theme has a lot to do with the way your products look. 
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