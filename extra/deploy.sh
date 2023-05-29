#!/bin/bash -e

path_to_module="/var/www/"$1"/bitrix/modules/trusted.cryptoarmdocsforms/"

sudo rm -rf $path_to_module
sudo cp -R trusted.cryptoarmdocsforms/ $path_to_module
sudo chown -R www-data:www-data $path_to_module
sudo find $path_to_module -type f -exec chmod 0664 {} \;
sudo find $path_to_module -type d -exec chmod 2775 {} \;
