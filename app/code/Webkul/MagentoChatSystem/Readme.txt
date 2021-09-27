#Installation

Magento 2 Admin Buyer ChatSystem module installation is very easy, please follow the steps for installation-

1. Unzip the respective extension zip and create Webkul(vendor) and MagentoChatSystem(module) name folder inside your magento/app/code/ directory and then move all module's files into magento root directory Magento2/app/code/Webkul/MagentoChatSystem/ folder.

Run Following Command via terminal
-----------------------------------
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy

## Make sure Node is already installed on your server.##
## Make sure telnet is already installed on your server.##

Run Following command from root directory of your Magento to install socket.io on your server.
-----------------------------------------------------------------------------------------------
First, you have to install the npm on your server for that run the command-
sudo apt-get update
sudo apt-get install npm

To install socket.io run command-
npm install
npm install socket.io socketio-file-upload

2. Flush the cache and reindex all.

3. If the CSP is configured in Restrict Mode then please run below command after saving the Host Name and Port Number in Admin Stores Configuration-
php bin/magento setup:di:compile


#User Guide

For Magento 2 Admin Buyer ChatSystem module's working process follow user guide : https://webkul.com/blog/chat-system-magento2/

#Support

Find us our support policy - https://store.webkul.com/support.html/

#Refund

Find us our refund policy - https://store.webkul.com/refund-policy.html/