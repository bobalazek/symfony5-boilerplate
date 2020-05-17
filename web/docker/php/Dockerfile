ARG PHP_VERSION=7.4

FROM php:${PHP_VERSION}-fpm

WORKDIR /var/www/web

RUN apt-get update && apt-get upgrade -y && apt-get install -y
RUN apt-get install git curl wget -y

# Imagick
RUN apt-get install ufraw libmagickwand-dev -y
RUN pecl install imagick
RUN docker-php-ext-enable imagick

# Opcache
RUN docker-php-ext-configure opcache --enable-opcache
RUN docker-php-ext-install opcache

# Zip
RUN apt-get install zip unzip libzip-dev -y
RUN docker-php-ext-configure zip
RUN docker-php-ext-install zip

# PDO Mysql
RUN docker-php-ext-install pdo_mysql

# Redis
RUN pecl install redis
RUN docker-php-ext-enable redis

# Intl
RUN apt-get install zlib1g-dev libicu-dev g++ -y
RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl

# GD
RUN docker-php-ext-configure gd
RUN docker-php-ext-install gd

# Config
COPY config/php.ini $PHP_INI_DIR/conf.d/
COPY config/www.conf /usr/local/etc/php-fpm.d/

# Supervisor
RUN apt-get install supervisor -y
COPY supervisor/supervisord.conf /etc/supervisor/conf.d/

# Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === 'e0012edf3e80b6978849f5eff0d4b4e4c79ff1609dd1e613307e16318854d24ae64f26d17af3ef0bf7cfb710ca74755a') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer

## Install Symfony Flex
RUN composer global require symfony/flex --prefer-dist --no-progress --no-suggest --classmap-authoritative

## Install PHP CS Fixer
RUN composer global require friendsofphp/php-cs-fixer --prefer-dist --no-progress --no-suggest --classmap-authoritative

# Add composer global dependencies path to $PATH
ENV PATH="${PATH}:/root/.composer/vendor/bin"

EXPOSE 9000

CMD ["/usr/bin/supervisord"]
