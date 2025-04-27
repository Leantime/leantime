#!/bin/sh

if [[  -n "${LEAN_DB_PASSWORD_FILE}" ]]; then
  LEAN_DB_PASSWORD=$(cat "${LEAN_DB_PASSWORD_FILE}")
  export LEAN_DB_PASSWORD
fi

if [[  -n "${LEAN_EMAIL_SMTP_PASSWORD_FILE}" ]]; then
  LEAN_EMAIL_SMTP_PASSWORD=$(cat "${LEAN_EMAIL_SMTP_PASSWORD_FILE}")
  export LEAN_EMAIL_SMTP_PASSWORD
fi

if [[  -n "${LEAN_S3_SECRET_FILE}" ]]; then
  LEAN_S3_SECRET=$(cat "${LEAN_S3_SECRET_FILE}")
  export LEAN_S3_SECRET
fi

if [[  -n "${LEAN_SESSION_PASSWORD_FILE}" ]]; then
  LEAN_SESSION_PASSWORD=$(cat "${LEAN_SESSION_PASSWORD_FILE}")
  export LEAN_SESSION_PASSWORD
fi

if [[  -n "${LEAN_REDIS_PASSWORD_FILE}" ]]; then
  LEAN_REDIS_PASSWORD=$(cat "${LEAN_REDIS_PASSWORD_FILE}")
  export LEAN_REDIS_PASSWORD
fi

if [[  -n "${LEAN_DB_HOST_FILE}" ]]; then
  LEAN_DB_HOST=$(cat "${LEAN_DB_HOST_FILE}")
  export LEAN_DB_HOST
fi

if [[  -n "${LEAN_DB_DATABASE_FILE}" ]]; then
  LEAN_DB_DATABASE=$(cat "${LEAN_DB_DATABASE_FILE}")
  export LEAN_DB_DATABASE
fi

if [[  -n "${LEAN_DB_USER_FILE}" ]]; then
  LEAN_DB_USER=$(cat "${LEAN_DB_USER_FILE}")
  export LEAN_DB_USER
fi

if [[  -n "${LEAN_EMAIL_SMTP_USERNAME_FILE}" ]]; then
  LEAN_EMAIL_SMTP_USERNAME=$(cat "${LEAN_EMAIL_SMTP_USERNAME_FILE}")
  export LEAN_EMAIL_SMTP_USERNAME
fi

# Ensure supervisord can write its pid file
mkdir -p /run

/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
