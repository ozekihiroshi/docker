FROM ltbproject/self-service-password:latest

# 必要なパッケージをインストール（プロキシ設定を追加）
ENV http_proxy=http://10.97.12.1:3128
ENV https_proxy=http://10.97.12.1:3128

# 必要なパッケージをインストール
# 必要なパッケージをインストールして MySQL 拡張を追加
RUN apt-get update && \
    apt-get install -y unzip git zip ldap-utils ca-certificates \
    libmariadb-dev libmariadb-dev-compat libzip-dev build-essential && \
    docker-php-ext-install mysqli pdo pdo_mysql zip && \
    docker-php-ext-enable mysqli pdo pdo_mysql zip && \
    rm -rf /var/lib/apt/lists/*



# Composer のインストール
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# コンテナのデフォルトディレクトリ
WORKDIR /var/www/htdocs

# コンテナ内でPHPのバージョン確認
RUN php -v

# ポート80を公開（必要であれば）
EXPOSE 80


# 準備ができたらカスタムスクリプトやファイルをコンテナにコピー
#COPY ./ssp-conf/index.php /var/www/htdocs/index.php
RUN rm -rf /var/www/htdocs/index.php
RUN rm -rf /var/www/htdocs/changecustompwdfield.php
RUN rm -rf /var/www/htdocs/changesshkey.php
RUN rm -rf /var/www/htdocs/newcaptcha.php        
RUN rm -rf /var/www/htdocs/resetbytoken.php   
RUN rm -rf /var/www/htdocs/setquestions.php
RUN rm -rf /var/www/htdocs/change.php           
RUN rm -rf /var/www/htdocs/checkentropy.php          
RUN rm -rf /var/www/htdocs/resetbyquestions.php  
RUN rm -rf /var/www/htdocs/setattributes.php
RUN rm -rf /var/www/htdocs/sendtoken.php      
RUN rm -rf /var/www/htdocs/sendsms.php
RUN rm -rf /var/www/htdocs/setquestions.php
RUN rm -rf /var/www/htdocs/vendor
RUN rm -rf /var/www/vendor
RUN ln -s /var/www/htdocs/reset_request.php /var/www/htdocs/index.php

RUN mkdir /etc/ldap
COPY ./ssp-conf/composer.json /var/www/htdocs/composer.json
COPY ./etc/ldap/ldap.conf /etc/ldap/ldap.conf
COPY ./ssp-conf/ldap_config.php /var/www/conf/ldap_config.php
COPY ./ssp-conf/reset_request.php /var/www/htdocs/reset_request.php
COPY ./ssp-conf/reset_request.tpl /var/www/templates/reset_request.tpl
COPY ./ssp-conf/reset_password.php /var/www/htdocs/reset_password.php
COPY ./ssp-conf/admin_dashboard.php /var/www/htdocs/admin_dashboard.php
COPY ./ssp-conf/admin_dashboard.tpl /var/www/templates/admin_dashboard.tpl
COPY ./ssp-conf/change_password.php /var/www/htdocs/change_password.php
COPY ./ssp-conf/change_password.tpl /var/www/templates/change_password.tpl
COPY ./ssp-conf/ldap_user_import.php /var/www/htdocs/ldap_user_import.php
COPY ./ssp-conf/ldap_user_import.tpl /var/www/templates/ldap_user_import.tpl
COPY ./ssp-conf/header.tpl /var/www/templates/header.tpl
COPY ./ssp-conf/footer.tpl /var/www/templates/footer.tpl
COPY ./css/navilink.css /var/www/htdocs/css/navilink.css
COPY ./certs/gtc-RootCA.crt /usr/local/share/ca-certificates/
COPY ./certs/staff-STAFFDC2-CA.crt /usr/local/share/ca-certificates/
RUN update-ca-certificates

# Composer のインストール
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Composer で必要なライブラリをインストール
#RUN composer config -g http-proxy http://10.97.12.1:3128
RUN composer install --no-dev --prefer-dist --optimize-autoloader
RUN composer require phpoffice/phpspreadsheet

# 確認用
RUN php -r "echo 'PhpSpreadsheet loaded: ' . (class_exists('\PhpOffice\PhpSpreadsheet\IOFactory') ? 'Yes' : 'No') . PHP_EOL;"
