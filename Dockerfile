FROM public.ecr.aws/docker/library/php:8.3-fpm

ARG user=www-data

RUN apt-get update -y \
  && apt-get install -y --no-install-recommends \
    sendmail zlib1g-dev wget libpng-dev libicu-dev libzip-dev libmagickwand-dev \
    apache2 libapache2-mod-fcgid \
    zip unzip git curl libonig-dev libxml2-dev supervisor \
  && pecl install imagick redis \
  && docker-php-ext-enable imagick redis \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install bcmath gd intl zip exif mysqli pdo pdo_mysql opcache

RUN a2enmod proxy_fcgi setenvif rewrite

COPY apache.conf /etc/apache2/sites-available/000-default.conf
COPY entrypoint.sh /etc/entrypoint.sh
COPY custom.ini /usr/local/etc/php/conf.d/
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN rm -rf /var/www/html/index.html
RUN chmod +x /etc/entrypoint.sh

WORKDIR /var/www/html/clearpay   # (recommended)
ENTRYPOINT ["/etc/entrypoint.sh"]