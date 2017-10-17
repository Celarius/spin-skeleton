<!-- https://github.com/naokazuterada/MarkdownTOC -->

<!-- MarkdownTOC list_bullets="-" bracket="round" lowercase="true" autolink="true" indent="  " -->

- [1. Skeleton application example](#1-skeleton-application-example)
  - [1.1. Installation](#11-installation)
  - [1.2. Packages / Components](#12-packages--components)
- [2. Technical details](#2-technical-details)
- [3. Apache](#3-apache)
  - [3.1. VHost config](#31-vhost-config)
  - [3.2. .htaccess file](#32-htaccess-file)

<!-- /MarkdownTOC -->

# 1. Skeleton application example
[Spin framework](https://github.com/Celarius/spin-framework) application example

## 1.1. Installation
To use the skeleton, simply clone the repository, run a composer update and you are ready to start making your own project.

Cloning the repository:
```bash
> git clone https://github.com/Celarius/spin-skeleton.git
```

Composer update:
```bash
> composer update -o --no-dev
```

## 1.2. Packages / Components
Uses the following implementations and Factories:
* [Guzzle](https://github.com/guzzle/guzzle) for HTTP Factories
* Template Engine samples
  - [Plates](http://platesphp.com/)
  - [Twig](http://platesphp.com/)
* [Monolog](https://github.com/Seldaek/monolog) for Logging
* APCu for SimpleCache

# 2. Technical details
* [Request lifecycle](doc/request_lifecycle.md)
* [Template Engines](doc/template_engines.md)

# 3. Apache
## 3.1. VHost config
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

## 3.2. .htaccess file
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
