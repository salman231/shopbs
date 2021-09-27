# WeSupply_Toolbox


### Installation

With composer:

```sh
$ composer config repositories.wesupply-toolbox git git@github.com:rusdragos/WeSupply_Toolbox.git
$ composer require wesupply/toolbox:dev-master
```

Note: Composer installation only available for internal use for the moment as the repositos are not public. However, there is a work around that will allow you to install the product via composer, described in the article below: https://support.weltpixel.com/hc/en-us/articles/115000216654-How-to-use-composer-and-install-Pearl-Theme-or-other-WeltPixel-extensions

Manually:

Copy the zip into app/code/WeSupply/Toolbox directory


#### After installation by either means, enable the extension by running following commands:

```sh
$ php bin/magento module:enable WeSupply_Toolbox --clear-static-content
$ php bin/magento setup:upgrade
```

###Release Notes  

Version 1.9.9, April 22th, 2020  

-Optimized WeSupply connection steps  
-Small bug fixes and other minor optimizations  

Version 1.9.8, March 25th, 2020  

-Added new functionality that allows setting multiple refunds types on the same return request  
-Other minor optimizations / improvements  

Version 1.9.7, March 2nd, 2020  

-Added new functionality that allows for choosing which Magento orders are exported to WeSupply: All Orders, No Orders or Exclude Specific Orders based on shipping country  
-Added link to product in WeSupply confirmation email templates  
-Fixed a bug which caused the WeSupply view orders functionality in Magento to break on Safari (iOS) by adding a new option in WeSupply called "Domain Alias"  
-Added more customer details to exported orders from Magento to WeSupply  
-Added WeSupply Return comments in Magento Credit Memo history  
-Other minor optimizations / improvements  

Version 1.9.6, February 14th, 2020  

-Improved functionality of SMS Notification subscription  
-Added SMS Notification unsubscription functionality  

Version 1.9.5, February 6th, 2020

-WeSupply and Magento connection errors are now more specific. Before this version, a generic "Invalid API credentials" error was thrown.  
-Orders are now updated in the WeSupply dashboard based on tracking number modifications/updates via Magento.  
-A new "None" option was added in the WeSupply dashboard for the Refund Processor setting. Before this update, the only available option was "Magento", and could not be deselected after being saved once.  
-Upon issuing a refund via WeSupply, if there is no Refund Processor selected, the Refund button is now disabled and a notice is shown which prompts you to set a Refund Processor.  