#!/usr/bin/env bash
export WORKING_DIR=/var/www
export APP_WORKSPACE=${APP_WORKSPACE:-app}
export APP_ENV=${APP_ENV:-local}

echo ">>Preparing app $APP_NAME..."
echo "APP_ENV: ${APP_ENV}"

if [[ $APP_ENV != "local" && $APP_ENV != "testing" ]]; then
  # Download and setup repository if not in local environment
  source repo.sh init
fi


#Change directory to APP_WORKSPACE
cd "${WORKING_DIR}/${APP_WORKSPACE}" || exit 1

if [[ "${APP_ENV}" = "testing" ]]
then

    chmod +x "$WORKING_DIR/$APP_WORKSPACE/arch/scripts/run-test.sh"
    source "$WORKING_DIR/$APP_WORKSPACE/arch/scripts/run-test.sh" || exit 1
    echo ">>Test ran successfully";exit 0
else

    #Do any necessary setup for nginx
    chmod +x "$WORKING_DIR/$APP_WORKSPACE/arch/scripts/nginx.sh"
    source "$WORKING_DIR/$APP_WORKSPACE/arch/scripts/nginx.sh" || exit 1

    #Do any necessary setup Move this back
    chmod +x "$WORKING_DIR/$APP_WORKSPACE/arch/scripts/setup.sh"
    source "$WORKING_DIR/$APP_WORKSPACE/arch/scripts/setup.sh" || exit 1

    #start the server
    echo ">>Runing server"
    php-fpm
fi
