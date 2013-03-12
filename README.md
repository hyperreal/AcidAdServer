There are still work to do with Acid. Please use carefully.

# AcidAdServer

AcidAdServer is ad serving web application based on Symfony 2 framework.

With Acid you can:

 * Manage banners and advertisements of your customers
 * Allow users to manage their banners and advertisements
 * Pay for banners in Bitcoins (via MtGox)
 * Create zones in your sites' profiles (`zone` is a place on your site, where you probably want to put some banner(s))
 * Upload banners like images and swf's
 * Assign banners to zones (one banner can be assigned to multiple zones and one zone can contain multiple banners)

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

# Installation

If you don't have Composer yet, download it following the 
instructions on http://getcomposer.org/ or just run the 
following command:

    curl -s http://getcomposer.org/installer | php

Then, use the `install` command to download needed vendors:

    php composer.phar install

Next, adjust database settings in `app/config/parameters.yml` (copy an example file) file and run
file and run following command from project root dir:

    ./app/console doctrine:schema:create --force
    
Next create admin user
    
    ./app/console fos:user:create --super-admin admin

Look that your virtual host document root must point to `web` directory (recommended) *or* you must access front
controller starting with `web` path: `http://example.com/web/admin`. If you are using PHP 5.4, you can run

    ./app/console server:run
   
instead of installing Apache or nginx.

Look that your virtual host document root must point to `web`
directory (recommended) *or* you must access front controller 
starting with `web` path: `http://example.com/web/admin`

With `node` and `bower` installed, you can next install JS and CSS
dependencies:

    bower install

# Development

Acid is based on Symfony2 but does not utilize Assetic. We prefer Grunt so if you want to develop some UI-related tasks
install `nodejs` (we recommend source distribution and `./configure && make && sudo make install`), `grunt-cli`
(`npm install grunt-cli -g`) and in project's root perform `npm install`. Next you can edit bundle's stylesheet(s) in
LESS format and with `grunt watch` command check results immediately.