...work in progress...

# AcidAdServer

AcidAdServer is ad serving software based on Symfony 2 framework.

# Installation using composer

If you don't have Composer yet, download it following the instructions on
http://getcomposer.org/ or just run the following command:

    curl -s http://getcomposer.org/installer | php

Then, use the `install` command to download needed vendors:

    php composer.phar install

Next, adjust database settings in `app/config/parameters.yml` file and run following command from project root dir:

    ./app/console doctrine:schema:update --force

Look that your virtual host document root must point to `web` directory (recommended) *or* you must access front controller
starting with `web` path: `http://example.com/web/admin`