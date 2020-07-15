FROM nginx

LABEL maintainer="wangke@iauto.com"

# 按照调试和编译依赖的包
RUN apt-get update \
&& apt-get install -y procps \
&& apt-get  install  -y build-essential \
&& apt-get install -y libxml2-dev \
&& apt-get install -y libzip-dev

# 安装php和php-fpm
WORKDIR /root
RUN apt-get -y install wget \
&& wget https://www.php.net/distributions/php-7.3.20.tar.gz \
&& tar xzvf php-7.3.20.tar.gz \
&& rm php-7.3.20.tar.gz \
&& cd php-7.3.20 \
&& ./configure --enable-fpm --enable-zip \
&& make \
&& make install

# php配置
RUN cp php.ini-development /usr/local/php/php.ini \
&& cp /usr/local/etc/php-fpm.d/www.conf.default /usr/local/etc/php-fpm.d/www.conf \
&& cp sapi/fpm/php-fpm /usr/local/bin \
&& cp ./sapi/fpm/php-fpm.conf /usr/local/etc/ \
&& sed -i "s/include=NONE\/etc\/php-fpm.d\/\*.conf/include=.\/etc\/php-fpm.d\/\*.conf/g"  /usr/local/etc/php-fpm.conf \
&& sed -i "s/cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/g" /usr/local/php/php.ini \
&& sed -i "s/user = nobody/user = nginx/g" /usr/local/etc/php-fpm.d/www.conf \
&& sed -i "s/group = nobody/group = nginx/g" /usr/local/etc/php-fpm.d/www.conf \
&& /usr/local/bin/php-fpm

# Configure nginx
COPY config/nginx.conf /etc/nginx/nginx.conf
