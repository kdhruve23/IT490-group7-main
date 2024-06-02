#!/bin/bash

SOURCE="/var/www/shapeshift/"
DESTINATION="nikitas@10.147.17.34:/var/www/shapeshift/"

# Exclude the synchronization script from being synced
EXCLUDE_OPTIONS="--exclude=sync_from_node2.sh"

# Run rsync with exclude options
rsync -avz --delete $EXCLUDE_OPTIONS $SOURCE $DESTINATION
