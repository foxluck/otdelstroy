AddDefaultCharset UTF-8
Options -Indexes
DirectoryIndex index.php
RewriteEngine On
#shop
RewriteCond  %{REQUEST_FILENAME} !-f
RewriteRule ^shop/(repo_themes|js|3rdparty|images_common|products_pictures|images|themes|css)/(.*)$ published/SC/html/scripts/$1/$2?frontend=1 [L]

RewriteCond  %{REQUEST_FILENAME} !-f
RewriteRule ^shop/(imgval.php|wbs_messageserserver.php) published/SC/html/scripts/$1 [L]

RewriteCond  %{REQUEST_FILENAME} !-f
RewriteRule ^(repo_themes|js|3rdparty|images_common|products_pictures|images|themes|css)/(.*)$ published/SC/html/scripts/$1/$2?frontend=1 [L]

RewriteCond  %{REQUEST_FILENAME} !-f
RewriteRule ^(imgval.php|wbs_messageserserver.php|get_file.php) published/SC/html/scripts/$1 [L]

RewriteCond  %{REQUEST_FILENAME} !-f
RewriteCond  %{REQUEST_FILENAME} !-d
RewriteRule ^shop(.*) published/SC/html/scripts/$1&frontend=1 [L]

RewriteCond  %{REQUEST_FILENAME} !-f
RewriteCond  %{REQUEST_FILENAME} !-d
RewriteRule ^photos/(album|view)/(.*) photos/index.php?q=$1/$2 [L,QSA]

RewriteCond  %{REQUEST_FILENAME} !-f
RewriteCond  %{REQUEST_FILENAME} !-d
RewriteRule ^photos/fullsize/(.*)/(.*) photos/getfullsize.php?filename=$1&hash=$2 [L,QSA]

RewriteCond  %{REQUEST_FILENAME} !-f
RewriteCond  %{REQUEST_FILENAME} !-d
RewriteRule ^photos/(.+)$ photos/index.php?q=$1 [L,QSA]

#common
RewriteCond  %{REQUEST_FILENAME} !-f
RewriteRule ^login/(.*) login/index.php [L]

RewriteCond  %{REQUEST_FILENAME} !-f
RewriteRule ^installer/(.*) installer/index.php [L]

RewriteCond  %{REQUEST_FILENAME} !-f
RewriteCond  %{REQUEST_FILENAME} !-d
RewriteRule ^published/$ published/index.php [L]

RewriteCond  %{REQUEST_FILENAME} !-f
RewriteCond  %{REQUEST_FILENAME} !-d
RewriteCond  %{REQUEST_URI} !^published
RewriteRule (.*) published/SC/html/scripts/index.php?__furl_path=$1&frontend=1 [QSA]
