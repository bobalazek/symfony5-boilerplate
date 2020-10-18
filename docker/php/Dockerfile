ARG PHP_VERSION=7.4
ARG COMPOSER_HASH=c31c1e292ad7be5f49291169c0ac8f683499edddcfd4e42232982d0fd193004208a58ff6f353fde0012d35fdd72bc394

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

# XSL
RUN apt-get install libxslt-dev -y
RUN docker-php-ext-configure xsl
RUN docker-php-ext-install xsl

# Copy config
COPY config/php.ini $PHP_INI_DIR/conf.d/php.ini
COPY config/www.conf /usr/local/etc/php-fpm.d/www.conf

# Supervisor
RUN apt-get install supervisor -y
COPY supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === '${COMPOSER_HASH}') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
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
