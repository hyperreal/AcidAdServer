...work in progress...

# AcidAdServer

AcidAdServer is ad serving web application based on Symfony 2 framework.

With Acid you can:

 * Manage campaigns of your customers
 * Create zones in your sites' profiles (`zone` is a place on your site, where you want to place some banner(s))
 * Upload banners like images and swf's
 * Assign banners to zones (one banner can be assigned to multiple zones and one zone can contain multiple banners)
 * View statistics of clicks and views of your banners

If you have questions, feel free to e-mail me at *fajka at hyperreal dot info*

# Why I should (or shouldn't) use Acid?

If you want your customers to manage their campaigns and banners, you should **not** use Acid. Every user see all
campaigns, banners, etc. It is some lack of security for certain types of organizations.

Otherwise - clone, install, and use :-)

# Benefits

 * Acid is fast. When you view zone it performs only two `SELECT`'s AND one `UPDATE`.
 * Code is very simple, especially for Symfony2 users. If you're tired of adapting OpenX, OrbitOpenAdServer or other
   open source adservers, you'll be in heaven.

# Plans

 * Text banners
 * Multiple banners in one zone at once (eg. you can place four small boxes in scyscrapper)
 * Counting clicks of swfs with `clickTAG`
 * Statistic reports
 * Nagios monitoring URL
 * E-mail notifications
 * Performance and UI improvements

# Installation using composer

If you don't have Composer yet, download it following the instructions on
http://getcomposer.org/ or just run the following command:

    curl -s http://getcomposer.org/installer | php

Then, use the `install` command to download needed vendors:

    php composer.phar install

Next, adjust database settings in `app/config/parameters.yml` file and run following command from project root dir:

    ./app/console doctrine:schema:update --force

Look that your virtual host document root must point to `web` directory (recommended) *or* you must access front
controller starting with `web` path: `http://example.com/web/admin`
