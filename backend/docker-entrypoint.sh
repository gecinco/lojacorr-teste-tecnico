#!/bin/bash
set -e

# Roda package:discover caso ainda não tenha sido executado
if [ ! -f bootstrap/cache/packages.php ]; then
    php artisan package:discover --ansi
fi

# Aguarda o banco de dados ficar disponível
echo "Aguardando o banco de dados..."
until php -r 'try { new PDO("mysql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT"), getenv("DB_USERNAME"), getenv("DB_PASSWORD")); } catch (Exception $e) { exit(1); }'; do
    sleep 2
done

# Executa as migrations
php artisan migrate --force

# Roda os seeds apenas na primeira inicialização (volume do banco persistente)
if [ ! -f storage/app/.seeded ]; then
    php artisan db:seed --force
    mkdir -p storage/app && touch storage/app/.seeded
fi

# Executa o comando passado como argumento (do docker-compose "command")
exec "$@"
