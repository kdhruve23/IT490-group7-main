#!/bin/bash

SOURCE="/var/www/shapeshift/"
DESTINATION="keya@10.248.179.18:/var/www/shapeshift"

#Exclude the sync script from being synced
EXLUDE_OPTIONS="--exclude=sync_from_node1.sh"

#Run rsync with exlude opitions
rsync -avz --delete $EXLCUDE_OPTIONS $SOURCE $DESTINATION
