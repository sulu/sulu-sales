#!/bin/bash
# Command for creating vendor symlinks in all sales bundles

VENDORNAME='vendor'
CURRENT=$(pwd)'/'

createSymlink () {
  if [ ! -d $1$VENDORNAME ]; then
    echo "create symlink for $1$VENDORNAME"
     ln -s $CURRENT$VENDORNAME $1$VENDORNAME
  fi
}

createSymlink "./src/Sulu/Bundle/Sales/CoreBundle/"
createSymlink "./src/Sulu/Bundle/Sales/OrderBundle/"
createSymlink "./src/Sulu/Bundle/Sales/ShippingBundle/"
