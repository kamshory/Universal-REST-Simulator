FROM jtreminio/php-nginx:5.6

WORKDIR /var/www
ADD . /var/www

# change modifier for attachment folders
RUN mkdir -p /var/www/data
RUN chmod 777 /var/www/data

ENV TZ=Asia/Jakarta
RUN apt-get install tzdata

# enable library GD
ENV PHP_INI_SCAN_DIR=:/p/gd
ENV PHP.upload_max_filesize=10M
ENV PHP.post_max_size=10M

# ADD .docker/config/php.ini /etc/php5/fpm/
ADD .docker/config/add_env.sh /add_env.sh
RUN chmod +x /add_env.sh

ADD .docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

VOLUME [ "/var/www" ]
EXPOSE 8080

SHELL ["/bin/bash", "-c"] 

# Load env, boot up Nginx, and PHP5-FPM when container is started
ENTRYPOINT ["/entrypoint.sh"]
