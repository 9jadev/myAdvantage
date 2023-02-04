#!/usr/bin/env bash
if [[ "${APP_ENV}" = "local" ]]
then
    echo ">>Installing dependencies..."
    composer install
else
    echo ">>Copying environment..."
    cp "${WORKING_DIR}/${APP_WORKSPACE}/.env.${APP_ENV}" "${WORKING_DIR}/${APP_WORKSPACE}/.env"
    echo ">>Installing dependencies without dev..."
    composer install --no-dev
fi

echo ">>Running composer dump-autoload..."
composer dump-autoload

if [[ ! -f "${WORKING_DIR}/${APP_WORKSPACE}/storage/oauth-private.key" ]]; then
    echo ">>Generating passport keys..."
    php artisan passport:keys
fi
# echo ">>Running migration..."
# php artisan migrate --force
# echo ">>Running seeding..."
# php artisan db:seed --force
echo ">>Publishing vendor assets..."
php artisan vendor:publish --tag=public --force
echo ">>removing storage symbolic link..."
rm -rf "$WORKING_DIR/$APP_WORKSPACE/public/storage"
echo ">>Linking storage..."
php artisan storage:link
if [[ "${APP_ENV}" != "local" ]]
then
    echo ">> Performing optimization..."
    php artisan optimize

    echo ">>Giving permission..."
    chown -R www-data:root .
fi

SCHEDULE_FREQUENCY="*/5 * * * *"
SCHEDULE_SCRIPT="cd $WORKING_DIR/$APP_WORKSPACE && php artisan schedule:run >> /dev/null 2>&1"
if crontab -l | grep -q "${SCHEDULE_SCRIPT}"; then
    echo ">> $(date) : Job already exist";
else
    echo ">> $(date) : Adding job to crontab...";
    echo "$(echo "${SCHEDULE_FREQUENCY} ${SCHEDULE_SCRIPT}"; crontab -l)" | crontab -
fi

echo ">> $(date) : Running crontab in background...";
pgrep -x crond >/dev/null && echo "Crond running" || crond

echo ">>Copying queue supervisor..."
cp "$WORKING_DIR/$APP_WORKSPACE/arch/supervisor/workers/"*.conf /etc/supervisor/conf.d/
/usr/bin/supervisord -n -c /etc/supervisord.conf -u www-data &

