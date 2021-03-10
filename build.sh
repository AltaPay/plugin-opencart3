#!/usr/bin/env bash

set -e

if ! command -v zip
then
  echo "Zip package is not currently installed"
  exit
fi

if ! command -v php7.3
then
  echo "PHP 7.3 package is not currently installed"
  exit
fi

if ! command -v composer
then
  echo "Composer package is not currently installed"
  exit
fi

DIR=$PWD"/dist"
if [ -d "$DIR" ]
then
    echo "Directory exists."
else
    mkdir dist
fi
rm -rf altapay-libs
php7.3 $(command -v composer) install --no-dev -o
zip -r dist/altapay-3x.ocmod.zip * -x "dist/"
