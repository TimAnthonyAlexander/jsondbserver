#!/usr/bin/env zsh

BASEDIR=${0:a:h}
BASEDIR=${BASEDIR:h}

cd ${BASEDIR}

composer install

if [[ ! -f /usr/local/bin/jsondbserver ]]; then
    sudo ln -s ${BASEDIR}/bin/jsondbserver.php /usr/local/bin/jsondbserver
fi

print "Installation complete."
print "Usage: jsondbserver help"
