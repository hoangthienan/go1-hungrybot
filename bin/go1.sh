#!/bin/bash
php=`which php`
while true
do
    # Echo current date to stdout
    echo 'Start :' `date`

    $php /var/www/app/console go1:sheet:config >/dev/null 2>&1

    # Echo 'error!' to stderr
    echo 'Error! ' `date` >&2

    sleep 60
done
