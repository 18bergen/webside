#!/bin/sh
echo " " >> ~/cronlogs/make_sitemap.log
date >> ~/cronlogs/make_sitemap.log
echo "   User: $USER" >> ~/cronlogs/make_sitemap.log
echo "   Home: $HOME" >> ~/cronlogs/make_sitemap.log
~/make_sitemap.php >> ~/cronlogs/make_sitemap.log
cp ~/www/sitemap.tmp ~/www/sitemap.xml
gzip --force --quiet ~/www/sitemap.xml
echo "   Complete" >> ~/cronlogs/make_sitemap.log
