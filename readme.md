<!-- https://github.com/naokazuterada/MarkdownTOC -->

<!-- MarkdownTOC list_bullets=" " bracket="round" lowercase="true" autolink="true" indent="  " -->

  [Skeleton application example](#skeleton-application-example)
  [Technical details](#technical-details)
  [Apache](#apache)
    [VHost config](#vhost-config)
    [.htaccess file](#htaccess-file)

<!-- /MarkdownTOC -->

# Skeleton application example

[Spin framework](https://github.com/Celarius/spin-framework) application example

Uses the following implementations and Factories:
* Guzzle for HTTP Factories
* Plates for Template engine (via /App/Controllers/AbstractController.php)
* Monolog for Logging
* APCu for SimpleCache

# Technical details
* [Request lifecycle](doc/request_lifecycle.md)
* [Template Engines](doc/template_engines.md)

# Apache
## VHost config
In order to run the skeleton an Apache VHost needs to be configured:

```txt
<VirtualHost *:80>

  ServerName {alias.domain.com}
  ServerAdmin webmaster@{alias.domain.com}

  DocumentRoot "{path_to_web_apps}\spin-skeleton\src\public"

  ErrorLog "logs/spin.skeleton-error.log"
  CustomLog "logs/spin.skeleton-access.log" common

  SetEnv ENVIRONMENT DEV

  <Directory "{path_to_web_apps}\spin-skeleton\src\public">
    <IfModule mod_negotiation.c>
        Options -MultiViews
    </IfModule>

    DirectoryIndex bootstrap.php index.php index.html

    Options -Indexes +FollowSymLinks
    AllowOverride All
    Order allow,deny
    Allow from all
    Require all granted

    DirectorySlash Off

    # Rewrite Engine to direct all requests to Spin bootstrap.php file
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ bootstrap.php [QSA,L]
  </Directory>

</VirtualHost>
```

## .htaccess file
```txt
  SetEnv ENVIRONMENT DEV

  <IfModule mod_negotiation.c>
      Options -MultiViews
  </IfModule>

  DirectoryIndex bootstrap.php index.php index.html

  Options -Indexes +FollowSymLinks
  AllowOverride All
  Order allow,deny
  Allow from all
  Require all granted

  DirectorySlash Off

  # Rewrite Engine to direct all requests to Spin bootstrap.php file
  RewriteEngine On
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^ bootstrap.php [QSA,L]
```
