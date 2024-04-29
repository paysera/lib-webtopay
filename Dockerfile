ARG PHP_VER
FROM php:${PHP_VER}-fpm

RUN groupmod -g 1000 www-data && usermod -u 1000 -g 1000 www-data

RUN pecl install pcov \
    && docker-php-ext-enable pcov

ADD https://github.com/mlocati/docker-php-extension-installer/releases/download/2.1.2/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions
RUN install-php-extensions \
      xdebug \
	  @composer

RUN echo "\n[PHP]" >> /usr/local/etc/php/conf.d/docker-fpm.ini  \
  && echo "error_reporting=E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED" >> /usr/local/etc/php/conf.d/docker-fpm.ini \
  && echo "memory_limit=512M" >> /usr/local/etc/php/conf.d/docker-fpm.ini \
  && echo "upload_max_filesize=16M" >> /usr/local/etc/php/conf.d/docker-fpm.ini \
  && echo "max_post_size=16M" >> /usr/local/etc/php/conf.d/docker-fpm.ini

RUN echo "\n[xdebug]" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini  \
    && echo "zend_extension=xdebug.so" > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.mode=develop,debug,coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_discovery_header=\"\"" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=\"host.docker.internal\"" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9000" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.discover_client_host=On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.output_dir = \"/var/log/nginx/xdebug.log\"" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_cookie_expire_time=3600" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.max_nesting_level=512" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.log_level=0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

USER 1000
WORKDIR /var/www
