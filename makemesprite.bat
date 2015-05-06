@echo off
php -f makemesprite.php -- --config tests/main/sprite.conf --image out/main.png --css out/main.css --csspath main.png --html out/main.html --htmlpath main.css --verbose 4
pause
