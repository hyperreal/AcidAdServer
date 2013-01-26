...work in progress... **please don't use on production environments** because huge changes will be made in workflow of
adding and managing advertisements and banners.

# AcidAdServer

AcidAdServer is ad serving web application based on Symfony 2 framework.

With Acid you can:

 * Manage banners and advertisements of your customers
 * Allow users to manage their banners and advertisements (**work in progress**)
 * Pay for banners in Bitcoins (**work in progress**)
 * Create zones in your sites' profiles (`zone` is a place on your site, where you probably want to put some banner(s))
 * Upload banners like images and swf's
 * Assign banners to zones (one banner can be assigned to multiple zones and one zone can contain multiple banners)
 * View statistics of clicks and views of your banners

If you have questions, feel free to e-mail me at *fajka at hyperreal dot info*

# Benefits

 * Acid is fast. When you view zone it performs only two `SELECT`'s and one `UPDATE`.
 * Code is very simple, especially for Symfony2 users. If you're tired of adapting OpenX, OrbitOpenAdServer or other
   open source adservers, you'll be in heaven.

# Plans

These features are sorted by planned implementation-done time.

 * Text banners (advertisements)
 * API to retrieve text advertisements
 * Nagios monitoring URL
 * E-mail notifications
 * UI improvements
 * Multiple banners in one zone at once (e.g. you can place 
   four small boxes in scyscrapper)
 * Performance improvements
 * Counting clicks of swfs with `clickTAG`
 * Statistic reports

# Installation using composer

If you don't have Composer yet, download it following the 
instructions on http://getcomposer.org/ or just run the 
following command:

    curl -s http://getcomposer.org/installer | php

Then, use the `install` command to download needed vendors:

    php composer.phar install

Next, adjust database settings in `app/config/parameters.yml` 
file and run following command from project root dir:

    ./app/console doctrine:schema:update --force

Look that your virtual host document root must point to `web`
directory (recommended) *or* you must access front controller 
starting with `web` path: `http://example.com/web/admin`

With `node` and `bower` installed, you can next install JS and CSS
dependencies:

    bower install

Alternatively you can run below command, but `bower` is prefered.

    ./bin/install-asset-deps
