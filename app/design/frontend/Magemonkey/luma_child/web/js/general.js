
require([
    'jquery',
    'jquery/ui',
    'mage/translate'
], function ($) {
    $(window).load(function (){
        var translates = {"Update":"Uppdatera"};
        $.mage.translate.add(translates);
        var translates = {"Buyer Dashboard":"Tillbaka till konto"};
        $.mage.translate.add(translates);
        var translates = {"Logout":"Logga ut"};
        $.mage.translate.add(translates);

    });

});