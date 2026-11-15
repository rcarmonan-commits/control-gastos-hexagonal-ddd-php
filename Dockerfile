FROM php:8.3-apache

LABEL maintainer="Rosary Eilyn Carmona Navarro <rcarmonan@unicartagena.edu.co>"
LABEL description="CRUDL de Usuarios y Gastos (#6) con Arquitectura Hexagonal, DDD y CQRS en PHP 8.3"

# Instalar extensiones requeridas para MySQL PDO y calculo decimal exacto
RUN docker-php-ext-install pdo pdo_mysql bcmath \
    && a2enmod rewrite headers

# Configurar DocumentRoot hacia /var/www/html/public permitiendo .htaccess
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && printf '<Directory /var/www/html/public>\n    Options Indexes FollowSymLinks\n    AllowOverride All\n    Require all granted\n    FallbackResource /index.php\n</Directory>\n' > /etc/apache2/conf-available/hexagonal-public.conf \
    && a2enconf hexagonal-public

WORKDIR /var/www/html
COPY . /var/www/html

RUN mkdir -p /var/www/html/storage/cache /var/www/html/storage/queue /var/www/html/storage/data \
    && chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 775 /var/www/html/storage

EXPOSE 80

# Soporte para puerto dinamico ($PORT) en plataformas Cloud (Render, Railway, Zeabur)
CMD ["sh", "-c", "if [ -n \"$PORT\" ]; then sed -i \"s/80/$PORT/g\" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf; fi && apache2-foreground"]
