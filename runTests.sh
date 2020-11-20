#!/usr/bin/env bash

[[ ! -f  phpunit ]] && wget -O phpunit https://phar.phpunit.de/phpunit-7.phar
[[ ! -f  phpab ]] && wget -O phpab https://github.com/theseer/Autoload/releases/download/1.26.0/phpab-1.26.0.phar
chmod +x phpunit phpab

./phpab -c -o core/autoload.inc.php core resources

./phpunit -c phpunit.xml