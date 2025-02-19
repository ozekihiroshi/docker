FROM ltbproject/self-service-password:latest

# 必要なパッケージをインストール（プロキシ設定を追加）
ENV http_proxy=http://10.97.12.1:3128
ENV https_proxy=http://10.97.12.1:3128

# 必要なパッケージをインストール
# 必要なパッケージをインストールして MySQL 拡張を追加
RUN apt-get update && \
    apt-get install -y ldap-utils ca-certificates libmariadb-dev libmariadb-dev-compat build-essential && \
    docker-php-ext-install mysqli pdo pdo_mysql && \
    docker-php-ext-enable mysqli pdo pdo_mysql && \
    rm -rf /var/lib/apt/lists/*


# コンテナのデフォルトディレクトリ
WORKDIR /var/www/htdocs

# コンテナ内でPHPのバージョン確認
RUN php -v

# ポート80を公開（必要であれば）
EXPOSE 80


# 準備ができたらカスタムスクリプトやファイルをコンテナにコピー
#COPY ./ssp-conf/index.php /var/www/htdocs/index.php
RUN mkdir /etc/ldap
COPY ./etc/ldap/ldap.conf /etc/ldap/ldap.conf
COPY ./ssp-conf/ldap_config.php /var/www/conf/ldap_config.php
COPY ./ssp-conf/reset_request.php /var/www/htdocs/reset_request.php
COPY ./ssp-conf/reset_request.tpl /var/www/templates/reset_request.tpl
COPY ./ssp-conf/reset_password.php /var/www/htdocs/reset_password.php
COPY ./ssp-conf/admin_dashboard.php /var/www/htdocs/admin_dashboard.php
COPY ./ssp-conf/admin_dashboard.tpl /var/www/templates/admin_dashboard.tpl
COPY ./ssp-conf/change_password.php /var/www/htdocs/change_password.php
COPY ./ssp-conf/change_password.tpl /var/www/templates/change_password.tpl
COPY ./ssp-conf/menu.tpl /var/www/templates/menu.tpl
COPY ./ssp-conf/header.tpl /var/www/templates/header.tpl
COPY ./ssp-conf/footer.tpl /var/www/templates/footer.tpl
COPY ./css/navilink.css /var/www/htdocs/css/navilink.css
COPY ./certs/gtc-RootCA.crt /usr/local/share/ca-certificates/
COPY ./certs/staff-STAFFDC2-CA.crt /usr/local/share/ca-certificates/
RUN update-ca-certificates
#Run rm /var/www/htdocs/index.php
#RUN ln -s /var/www/htdocs/reset_request.php /var/www/htdocs/index.php

