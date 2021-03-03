#!/usr/bin/env bash
DIR=$PWD"/dist"
if [ -d "$DIR" ] 
then
    echo "Directory exists." 
else
    mkdir dist
fi
zip -r dist/altapay-3x.ocmod.zip * -x "dist/" 