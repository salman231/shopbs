#Infobeans OSComments Magento 2 Integration
##Quick instructions

###Manual Install
- Create folder structure /app/code/Infobeans/OSComments/
- Download the .ZIP file from the marketplace
- Extract the contents of the .ZIP file to the folder you just created

###Composer Install
- Go to magento2 root directory.
- Enter command composer require infobeans/magento2-oscomments:2.0.0

#### Run install commands:
```
php bin/magento module:enable Infobeans_OSComments
```
php bin/magento setup:upgrade
```
- You may need to run the following command to flush the Magento cache:
```
php bin/magento cache:flush
