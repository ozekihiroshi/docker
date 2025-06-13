FROM php:8.2-apache

# ビルド時のプロキシは ARG にする
ARG HTTP_PROXY
ARG HTTPS_PROXY

# 必要パッケージのインストール
RUN apt-get update && \
    apt-get install -y \
        curl \
        unzip \
        git \
        zip \
        libzip-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libmariadb-dev \
        libldap2-dev \
        ca-certificates \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install mysqli pdo pdo_mysql zip ldap gd \
    && rm -rf /var/lib/apt/lists/*

# 証明書とLDAP設定
COPY ./certs/ /usr/local/share/ca-certificates/
COPY ./etc/ldap/ldap.conf /etc/ldap/ldap.conf
RUN update-ca-certificates

# Composer インストール（プロキシ不要なら ARG を使わずOK）
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Composer 用ファイルをコピー
COPY ./www/composer.json /var/www/composer.json

# 作業ディレクトリ変更
WORKDIR /var/www

RUN mkdir /var/www/templates_c
RUN mkdir /var/www/exports
RUN chown -R www-data:www-data /var/www
RUN chmod -R 755 /var/www/templates_c

# Composer 設定：vendor ディレクトリを指定して install
RUN composer config vendor-dir /var/www/vendor
RUN composer install --no-dev --optimize-autoloader
RUN composer require phpoffice/phpspreadsheet smarty/smarty



# 作業ディレクトリを戻す
WORKDIR /var/www/html

# ポート設定（Apache）
EXPOSE 80

