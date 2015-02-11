#! /bin/sh
uglifyjs -v assets/js/tiles.js assets/js/jquery.dotdotdot.js assets/js/wp-tiles.js -o assets/js/wp-tiles.min.js
compass compile --force -s compressed
composer update --optimize-autoloader