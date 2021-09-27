/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 /*global define*/
 define([
    'underscore',
    'ko',
    'jquery',
    'uiComponent',
    'Magento_Ui/js/modal/modal',
    'Ves_Megamenu/js/jquery.nestable',
    'Ves_Megamenu/js/vesbrowser',
    'mage/adminhtml/wysiwyg/tiny_mce/setup',
    'mage/adminhtml/wysiwyg/widget',
    'Ves_Megamenu/js/jquery.minicolors.min',
    'mage/translate'
    ], function (_, ko, $, Component, modal, nestable, vesbrowser, setup) {
     "use strict";

     var nestablOptions = {  
        group: 1,
        maxDepth: 8
    };
    var actE = [];

    function ObjectToArray(x) {
        for(var i = 0, a = []; i < x.length; i++)
            a.push(x[i]);

        return a
    }

    function optimizeJson(item) {
        if (typeof(item['children']) != "undefined" && item['children']) {
            
            for (var x = 0; x < item['children'].length; x++) {
                if(typeof(item['children'][x]) != "undefined"){
                   delete item['children'][x]['bind'];
                   if (typeof(item['children'][x]['children']) != "undefined" && item['children'][x]['children']) {
                        if(typeof(item['children'][x]) === 'object') {

                        } else {
                           item['children'][x] = optimizeJson(item['children'][x]); 
                        }
                        
                   }
                }
                
            }
        }
        delete item['bind'];
        return item;
    }

    var initGetCmsPageLink = function() {
        if($("#option-cms_page").length > 0) {
            $("#option-cms_page").on("change", function(){
                var selected_value = $(this).val();
                if(selected_value){
                    var generated_link = '{{store url="'+selected_value+'"}}';
                    $("#option-link").val(generated_link);
                    $("#option-link").change();
                }
            })
        }
    }

    var updateListData = function(e, eventType){
        var list   = e.length ? e : $(e.target),
        output = list.data('output');

        if (window.JSON) {
            var items = list.nestable('serialize');
            var newItems = [];
            for (var i = -0; i < items.length; i++) {
                newItems[i] = optimizeJson(items[i]);
            }
            items = window.JSON.stringify(newItems);
            output.val(items);
            $('#nestable .ves-spinner').css({height: $('#nestable .dd-list').height() + 'px'});
        } else {
            output.val('JSON browser support required for this demo.');
        }

        if (eventType!='init') {
            $('#nestable').find('.dd-list').each(function (index, element) {
                var parent = $(this).parent();
                if ($(this).children().length && parent.children('button').length==0 && index>0) {
                    var expandBtnHTML = '<button data-action="expand" style="display: block" type="button"><i class="fa fa-caret-down"></i></button>';
                    var collapseBtnHTML = '<button data-action="collapse" style="display: none" type="button"><i class="fa fa-caret-up"></i></button>';
                    $(parent).prepend(collapseBtnHTML + expandBtnHTML);
                }
                if ($(this).is(":visible")) {
                    $(parent).children('button[data-action=collapse]').show();
                    $(parent).children('button[data-action=expand]').hide();
                } else {
                    $(parent).children('button[data-action=collapse]').hide();
                    $(parent).children('button[data-action=expand]').show();
                }
            });
        }

        if (eventType=='init') {
            jQuery('#nestable .dd-list button[data-action="collapse"]').remove();
            jQuery('#nestable .dd-list button[data-action="expand"]').remove();
            jQuery(document).find('.dd-list').each(function (index, element) {
                if (index!=0) {
                    var expandVisible = '';
                    var collapseVisible = '';
                    var eParent = jQuery(element).parent("li");
                    var id = String(eParent.data("id"));
                    if (actE[id] && actE[id] == "1") {
                        expandVisible = 'none';
                        collapseVisible = 'block';
                    } else {
                        expandVisible = 'block';
                        collapseVisible = 'none';
                    }
                    jQuery(element).css({"display":collapseVisible});
                    var expandBtnHTML = '<button data-action="expand" style="display:'+expandVisible+'" type="button"><i class="fa fa-caret-down"></i></button>';
                    var collapseBtnHTML = '<button data-action="collapse" style="display:'+collapseVisible+'" type="button"><i class="fa fa-caret-up"></i></button>';
                    if(jQuery(element).children().length > 0){
                        eParent.prepend(collapseBtnHTML+expandBtnHTML);
                    }
                }
            });
        }
    }

    jQuery(document).on('click', '#nestable-menu button', function(e) {
        var target = jQuery(e.target),
        action = target.data('action');
        if (action === 'expand-all') {
            jQuery('.dd').nestable('expandAll');
            var i = 0;
            for (i; i < actE.length; i++) {
                actE[i] = "1";
            };
        }
        if (action === 'collapse-all') {
            jQuery('.dd').nestable('collapseAll');
            var i = 0;
            for (i; i < actE.length; i++) {
                actE[i] = "0";
            };
        }
    });

    jQuery(document).on('click', '.dd-item > button', function(e) {
        jQuery('#nestable .ves-spinner').css({height: jQuery('#nestable .dd-list').height() + 'px'});
    });

    var fields = window.megamenu.fields;

    var IDGenerator = function () {
        this.length = 8;
        this.timestamp = +new Date;
        var _getRandomInt = function( min, max ) {
            return Math.floor( Math.random() * ( max - min + 1 ) ) + min;
        }
        this.generate = function() {
            var ts = this.timestamp.toString();
            var parts = ts.split( "" ).reverse();
            var id = "";
            for( var i = 0; i < this.length; ++i ) {
                var index = _getRandomInt( 0, parts.length - 1 );
                id += parts[index];  
            }
            return id;
        }
    }

    var Message = function(text) {
        if(!jQuery(".ves-notify").hasClass("in")){
            jQuery(".ves-notify").html(text);
            jQuery(".ves-notify").addClass('in');
            setTimeout(function(){
                jQuery(".ves-notify").removeClass('in');
                if(!jQuery(".ves-notify").hasClass("out")){
                    jQuery(".ves-notify").addClass('out');
                    setTimeout(function(){
                        jQuery(".ves-notify").removeClass('out');
                    }, 500);
                }
            }, 800);
        }
    }

    var Item = function(data) {
        for (var i = 0; i < fields.length; i++) {
            var type = fields[i]['type'];
            if (type == 'fieldset' || type == 'separator')  {
                continue;
            }
            var key = fields[i]['name'];
            this[key] = ko.observable(data[key]);
        }
        var generator = new IDGenerator();
        var id = generator.generate();
        if (data.item_id) {
            id = data.item_id;
        } else if(data.id) {
            id = data.id;
        }
        if (data.id) {
            this.id = data.id;
        }
        this.item_id  = id;
        this.children = data.children && ko.observableArray(data.children);
        this.update(data);
    }

    ko.utils.extend(Item.prototype, {
        update: function(data) {
            for (var i = 0; i < fields.length; i++) {
                var type = fields[i]['type'];
                if (type == 'fieldset' || type == 'separator')  {
                    continue;
                }
                var key   = fields[i]['name'];
                if (typeof(this[key]) === 'function') {
                    if (typeof(data[key]) == 'undefined') {
                        data[key] = fields[i]['value'];
                    }
                    this[key](data[key]);
                }
            }
        }
    });

    ko.bindingHandlers.bootstrapTooltip = {
        init: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
            var valueUnwrapped = ko.utils.unwrapObservable(valueAccessor());
            $(element).hover(function() {

                var eTitle = $(this).data('title');
                if (eTitle && $('.nestable-lists').hasClass('vertical')) {
                    $(this).parent().find('button').eq(0).css({'z-index': '-1'});
                    $(this).parent().find('button').eq(1).css({'z-index': '-1'});
                    eTitle = '<span class="ves-tooltip">' + eTitle + '</span>';
                    $('.ves-tooltip').remove();
                    $(this).append(eTitle);
                }
            }, function() {
                $(this).parent().find('button').eq(0).css({'z-index': ''});
                $(this).parent().find('button').eq(1).css({'z-index': ''});
                $('.ves-tooltip').remove();
            });
        },
    };

    var itemForEditing      = ko.observable();
    var editorVisible       = ko.observable(false);
    var itemForEditing      = ko.observable();
    var selectedItem        = ko.observable();
    var loadcategory        = ko.observable();
    var importsubcategries  = ko.observable(false);
    var startindex          = ko.observable();
    var endindex            = ko.observable();
    var menulevel           = ko.observable();
    var icons               = ko.observableArray(window.megamenu.icons);
    var items               = window.megamenu.items;
    var previewStore        = ko.observable();
    var devices             = ko.observable('1280px');

    return Component.extend({
        items: '',
        itemForEditing: itemForEditing,
        editorVisible: editorVisible,
        selectedItem: selectedItem,
        loadcategory: loadcategory,
        importSelectedCategory: '',
        importsubcategries: importsubcategries,
        menulevel: menulevel,
        startindex: startindex,
        endindex: endindex,
        icons: icons,
        totalItems: 0,
        targetField: '',
        previewStore: previewStore,
        devices: devices,
        loading: ko.observable(false),
        requestTime: 0,
        ajaxCount: 0,
        clickTarget: '',
        modeClass: ko.observable('vertical'),

        initialize: function () {
            this.importedIndex = [];
            this.importingItems = [];
            this._super();
            _.bindAll(this, 'removeItem', 'selectItem', 'btnSelectItem', 'acceptItem', 'addAppendChild', 'addPrependChild', 'showIconList', 'selectIcon', 'revertItem', 'previewMenu', 'saveItem', 'submitForm', 'switcher', 'setMenuType');
            return this;
        },

        initObservable: function () {
            var self = this;
            this._super()
            .observe('opened');
            this.items = ko.observableArray(ko.utils.arrayMap(window.megamenu.items, function(data){
                if(data.children) {
                    data.children = self.convertToObject(data.children, '', 0, 0, 0, 0, false);
                }
                return new Item(data);
            }));

            setTimeout(function () {
                $('#save-button').click(function() {
                    if (self.clickTarget == '') self.clickTarget = '#save-button';
                    self.submitForm('back/edit');
                });

                $('#duplicate').click(function() {
                    if (self.clickTarget == '') self.clickTarget = '#duplicate';
                    self.submitForm('duplicate/1');
                });

                $('#save-duplicate-button').click(function() {
                    if (self.clickTarget == '') self.clickTarget = '#save-duplicate-button';
                    self.submitForm('duplicate/1');
                });

                $('#save-duplicate-button').click(function() {
                    if (self.clickTarget == '') self.clickTarget = '#save-duplicate-button';
                    self.submitForm('duplicate/1');
                });

                $('#save-new-button').click(function() {
                    if (self.clickTarget == '') self.clickTarget = '#save-new-button';
                    self.submitForm('new/1');
                });

                $('#save-cache').click(function() {
                    if (self.clickTarget == '') self.clickTarget = '#save-cache';
                    self.submitForm('cache/1');
                });

                $('#icon-search input').on('keypress change', function(e) {
                    var s = $(this).val();
                    $('.icon-list i').each(function(index, el) {
                        var title = $(this).data('title');
                        if (title.indexOf(s) > -1) {
                            $(this).parent().show();
                        } else {
                            $(this).parent().hide();
                        }
                    });
                });

                updateListData(jQuery('#nestable').data('output', jQuery('#nestable-output')), 'init');
                $('#nestable').nestable(nestablOptions).on('change', updateListData).change();
                self.activeFirstItem();

                if (window.megamenu.desktopTemplate == 'horizontal') {
                    self.setMenuType('vertical');
                }

                jQuery('.minicolor').each( function() {
                    jQuery(this).minicolors({
                        control: jQuery(this).attr('data-control') || 'hue',
                        defaultValue: jQuery(this).attr('data-defaultValue') || '',
                        format: jQuery(this).attr('data-format') || 'hex',
                        keywords: jQuery(this).attr('data-keywords') || '',
                        inline: jQuery(this).attr('data-inline') === 'true',
                        letterCase: jQuery(this).attr('data-letterCase') || 'lowercase',
                        opacity: jQuery(this).attr('data-opacity'),
                        position: jQuery(this).attr('data-position') || 'bottom left',
                        swatches: jQuery(this).attr('data-swatches') ? jQuery(this).attr('data-swatches').split('|') : [],
                        change: function(value, opacity) {
                            if( !value ) return;
                            if( typeof console === 'object' ) {
                                $(this).trigger('change');
                                $(this).val(value);
                            }
                        },
                        theme: 'bootstrap'
                    });
                });

            }, 500);
            return this;
        },

        addItem: function() {
            var d = new Date();
            var id = '_' + d.getTime() + '_' + d.getMilliseconds();
            var data = {
                name: 'Menu Item',
                item_id: id
            };
            var item = new Item(data);
            this.items.push(item);
            this.selectItem(item);
            $('#nestable').nestable(nestablOptions).trigger('change');
            updateListData(jQuery('#nestable').data('output', jQuery('#nestable-output')), 'add');
            this.saveItem(item, 'add');
            this.gotoScroll();
            initGetCmsPageLink();
        },

        activeFirstItem: function() {
            if(!item){
                var item = this.items()[0];
            }
            this.selectItem(item);
        },

        importStoreCategory: function() {
            var options = {
                type: 'popup',
                modalClass: 'vesmodal',
                responsive: true,
                innerScroll: true,
                title: ''
            };
            if("" == $("#loadStoreCategories").html()){
                if(typeof(_tree_categories_html) !== 'undefined' && typeof(ajax_load_categories) !== 'undefined'){
                    if(_tree_categories_html) {
                        $("#loadStoreCategories").html(_tree_categories_html);
                    } else {
                        this.ajaxGetStoreCategories(ajax_load_categories);
                    }
                }
            }
            var popup = modal(options, jQuery('#import-form'));
            jQuery('#import-form').modal('openModal');
        },

        getSelectedCategory: function(categories, catId, result) {
            for (var i = 0; i < categories.length; i++) {
                if (parseInt(categories[i]['value']) == parseInt(catId)) {
                    result = categories[i];
                    categories[i];
                    this.importSelectedCategory = categories[i];
                    return categories[i];
                }
                if (categories[i]['children']) {
                    this.getSelectedCategory(categories[i]['children'], catId, result);
                }
            }
        },
        ajaxGetStoreCategories: function (ajax_url) {
            if(typeof(store_categories) == 'undefined') {
                var store_categories = [];
            }
            if(store_categories.length == 0 ){
                jQuery.ajax({
                    url: ajax_url,
                    method: "GET",
                    cache: true,
                    dataType: 'json',
                    beforeSend: function(){
                        jQuery(".ves-spinner").show();
                    },
                    success: function(dataResponse){
                        store_categories = window.megamenu.storeCategories = dataResponse.store_categories;
                        _tree_categories_html = '<select data-bind="value: loadcategory">';
                        _tree_categories_html += dataResponse.tree_html;
                        _tree_categories_html += '</select>';
                        $("#loadStoreCategories").html(_tree_categories_html);
                        jQuery(".ves-spinner").hide();
                    }
                });
            }
            return store_categories;
        },
        importSubCategory: function() {
            var selectedCat;
            var catId               = this.loadcategory();
            var currentLevel        = 0;
            var storeCategories     = window.megamenu.storeCategories;
            if(typeof(store_categories) !== 'undefined' &&  typeof(ajax_load_categories) !== 'undefined' && (storeCategories.length == 0 || storeCategories==null || storeCategories=='')) {
                storeCategories = window.megamenu.storeCategories = this.ajaxGetStoreCategories(ajax_load_categories);
            }
            this.getSelectedCategory(storeCategories, catId);

            if (this.importSelectedCategory) {
                var importsubcategries = this.importsubcategries();
                var menulevel = this.menulevel();
                var startindex = this.startindex();
                var endindex = this.endindex();
                var category = this.importSelectedCategory;
                var item;
                if (importsubcategries == 0) {
                    if (category.children) {
                        if(menulevel) {
                            if(menulevel > 1) {
                                category.children = this.convertToObject(category.children, 'import', startindex, endindex, 1, menulevel);
                            } else {
                                category.children = [];
                            }
                        } else {
                            category.children = this.convertToObject(category.children, 'import', startindex, endindex, 1, 0);
                        }
                    }
                    for (var x = 0; x < fields.length; x++) {
                        var type = fields[x]['type'];
                        if (type == 'fieldset' || type == 'separator')  {
                            continue;
                        }
                        var key = fields[x]['name'];
                        if (typeof(category[key]) == 'undefined') {
                            category[key] = fields[x]['value'];
                        }
                    }
                    var item = new Item(category);
                    this.items.push(item);
                    this.importingItems.push(item);
                    /*this.saveItem(item, 'import');*/
                } else {

                    if (category.children) {
                        this.ajaxCount = 0;
                        var children = category.children;
                        for (var i = 0; i < children.length; i++) {
                            if(endindex > 0 && endindex != '' && endindex !='0' && endindex != null && endindex != 'undefined') {
                                if(i < startindex) continue;
                                if(endindex != 'end' && endindex != 'all' && i >= endindex) continue;
                            }

                            var data = children[i];
                            for (var x = 0; x < fields.length; x++) {
                                var type = fields[x]['type'];
                                if (type == 'fieldset' || type == 'separator')  {
                                    continue;
                                }
                                var key = fields[x]['name'];
                                if (typeof(data[key]) == 'undefined') {
                                    data[key] = fields[x]['value'];
                                }
                            }
                            if (typeof(data.children)!='undefined'){
                                if(menulevel) {
                                    if(menulevel > 1) {
                                        data.children = this.convertToObject(data.children, 'import', startindex, endindex, 1, menulevel);
                                    } else {
                                        data.children = [];
                                    }
                                } else {
                                    data.children = this.convertToObject(data.children, 'import', startindex, endindex, 1, menulevel);
                                }
                            }
                            item = new Item(data);
                            this.items.push(item);
                            this.importingItems.push(item);
                            /*this.saveItem(item, 'import');*/
                        }
                    }
                }
                if(this.importingItems.length > 0) { /*If have importing items, then save all menu items into database*/
                    this.saveImportedItems('all', 'import');
                }
                this.importSelectedCategory = '';
            }
            updateListData(jQuery('#nestable').data('output', jQuery('#nestable-output')), 'import');

            if (typeof(this.selectItem()) == 'undefined') {
                this.activeFirstItem();
            }
        },
        initStoreCategories: function (){
            if(typeof(ajax_load_categories) !== 'undefined'){
                if(("" == $("#option-category").html()) || ("" == $("#option-parentcat").html()) ){
                    if(typeof(_list_category_options) !== 'undefined' && (_list_category_options == '' || _list_category_options == null)){
                        $.ajax({
                            url: ajax_load_categories,
                            method: "GET",
                            cache: true,
                            dataType: 'json',
                            beforeSend: function(){
                                $(".ves-spinner").show();
                            },
                            success: function(dataResponse){
                                _list_category_options = dataResponse.tree_html;
                                $("#option-category").html(_list_category_options);
                                $("#option-parentcat").html(_list_category_options);
                                $(".ves-spinner").hide();
                                console.log("\n - Completely ajax load categories...");
                            }
                        });
                    } else if(typeof(_list_category_options) !== 'undefined' && _list_category_options) {
                        console.log("\n - Load list category options...")
                        $("#option-category").html(_list_category_options);
                        $("#option-parentcat").html(_list_category_options);
                    }
                }
            }
            
        },
        removeItem: function(item) {
            this.items.remove(item);
            $("[data-id=" + item.item_id + "]").remove();
            updateListData(jQuery('#nestable').data('output', jQuery('#nestable-output')), 'delete');
            this.activeFirstItem();
        },

        acceptItem: function(item) {
            var edited = ko.toJS(this.itemForEditing());
            var selected = this.selectedItem();
            selected.update(edited);
            this.selectItem(item);
            this.saveItem(this.itemForEditing(), 'save');
            initGetCmsPageLink();
        },

        revertItem: function() {
            var selectedItem = this.selectedItem();
            var editingItem = this.itemForEditing();
            editingItem.update(selectedItem);
            this.selectItem(editingItem);
        },

        btnSelectItem: function(item) {
            this.selectItem(item);
            this.loadSpinner($.mage.__('Item Selected'), 800);
            this.gotoScroll();
        },

        selectItem: function(item) {
            if (item) {
                this.selectedItem(ko.toJS(item));
                this.editorVisible(true);
                this.initStoreCategories();
                this.itemForEditing(item);
                this.activeCurrentItem();
                this.animateSwitcher();
                this.loadDependField();
                this.loadWysiwygEditor();
                this.loadColorPicker();
                initGetCmsPageLink();
            }
        },

        addAppendChild: function(item) {
            this.addItem();
            var itemActive = this.selectedItem();
            jQuery('#' + itemActive.item_id).appendTo('#' + item.item_id  + ' > .dd-list');
            updateListData(jQuery('#nestable').data('output', jQuery('#nestable-output')), 'update');
            jQuery('#' + item.item_id  + ' > .dd-list').show();
            jQuery('#' + item.item_id).parents('.dd-list').css({'display':'block'});
            this.gotoScroll();

        },

        addPrependChild: function(item) {
            this.addItem();
            var itemActive = this.selectedItem();
            jQuery('#' + itemActive.item_id).prependTo('#' + item.item_id  + ' > .dd-list');
            updateListData(jQuery('#nestable').data('output', jQuery('#nestable-output')), 'add');
            jQuery('#' + item.item_id).parents('.dd-list').css({'display':'block'});
            jQuery('#' + item.item_id  + ' > .dd-list').show();
            $('#' + item.item_id  + ' > button[data-action=collapse]').show();
            $('#' + item.item_id  + ' > button[data-action=expand]').hide();
            this.gotoScroll();
        },

        activeCurrentItem: function() {
            var item = this.selectedItem();
            if (typeof(item) != 'undefined') {
                jQuery("#nestable li").removeClass("active");
                jQuery('#' + item['item_id']).addClass('active');
            }
        },

        showIconList: function(targetField, item) {
            $('.icon-list .icon-item i').removeClass('active');
            var options = {
                type: 'popup',
                modalClass: 'vesmodal',
                responsive: true,
                innerScroll: true,
                title: ''
            };
            jQuery('#icon-search input').val('').trigger('change');
            jQuery('#icon-search li').show();
            var popup = modal(options, jQuery('#icon-form'));
            jQuery('#icon-form').modal('openModal');
            this.targetField = targetField;
        },

        selectIcon: function(icon) {
            $('.icon-list .icon-item i').removeClass('active');
            $('#icon' + icon['value']).addClass('active');
            var item = this.itemForEditing();
            var edited = ko.toJS(item);
            var value = icon['value'];
            var targetField = this.targetField;
            value = icon['value'];
            edited[targetField] = value;
            item.update(edited);
            initGetCmsPageLink();
        },

        saveItem: function(item, action) {
            var start_time = new Date().getTime();
            item = ko.toJS(item);
            var data = {
                item: ko.toJSON(item),
                menu_id: $('#menu_id').val(),
                action: action
            };
            var ajaxUrl = window.megamenu.ajaxSaveItemUrl;
            var self = this;
            this.loading(true);
            this.ajaxCount = this.ajaxCount + 1;
            jQuery.ajax({
                url: ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function(response) {
                    if (response['status'] === true) {
                        switch (action) {
                            case 'add':
                            Message($.mage.__('Item Created'));
                            break;

                            case 'save':
                            Message($.mage.__('Item Saved'));
                            break;
                        }
                        self.ajaxCount = self.ajaxCount-1;

                        if (self.ajaxCount == 0) {
                            console.log(action);
                            if (self.requestTime==0 && action =='add') {
                                self.requestTime = new Date().getTime() - start_time;
                            }

                            if (action=='import') {
                                Message($.mage.__('Import Sucessfully'));
                                updateListData(jQuery('#nestable').data('output', jQuery('#nestable-output')), 'add');
                            }

                            if  (self.clickTarget != '') {
                                $(self.clickTarget).trigger('click');
                            }
                            self.loading(false);
                        }
                        initGetCmsPageLink();
                    }
                }
            });

            if (self.requestTime && action =='add') {
                setTimeout(function() {
                    self.loading(false);
                }, (self.requestTime/2));
            }
        },
        saveImportedItems: function(item, action) {
            if(item == 'all') { /*get first item of importing array*/
                item = this.importingItems[0];
                this.importingItems.shift();
            }
            var start_time = new Date().getTime();
            var item2 = ko.toJS(item);
            var data = {
                item: ko.toJSON(item2),
                menu_id: $('#menu_id').val(),
                action: action
            };
            var ajaxUrl = window.megamenu.ajaxSaveItemUrl;
            var self = this;
            this.loading(true);
            this.ajaxCount = this.ajaxCount + 1;
            jQuery.ajax({
                url: ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function(response) {
                    if (response['status'] === true) {
                        switch (action) {
                            case 'add':
                            Message($.mage.__('Item Created'));
                            break;

                            case 'save':
                            Message($.mage.__('Item Saved'));
                            break;
                        }
                        self.ajaxCount = self.ajaxCount-1;

                        if (self.ajaxCount == 0) {
                            console.log(action);
                            if (self.requestTime==0 && action =='add') {
                                self.requestTime = new Date().getTime() - start_time;
                            }

                            if (action=='import') {
                                Message($.mage.__('Menu Item')+' "'+item2.name+'" '+$.mage.__('Was Imported Sucessfully'));
                                updateListData(jQuery('#nestable').data('output', jQuery('#nestable-output')), 'add');
                            }

                            if  (self.clickTarget != '') {
                                $(self.clickTarget).trigger('click');
                            }
                            self.loading(false);
                        }
                        initGetCmsPageLink();
                    }
                    /*Call back this saveImportedItems to save next item in importing array*/
                    if(self.importingItems.length > 0) {
                        setTimeout(function() {
                            var nextItem = self.importingItems[0];
                            self.importingItems.shift();
                            self.saveImportedItems(nextItem, action);
                        }, 1000);
                    }
                }
            });

            if (self.requestTime && action =='add') {
                setTimeout(function() {
                    self.loading(false);
                }, (self.requestTime/2));
            }
        },
        loadDependField: function() {
            for (var i = 0; i < fields.length; i++) {
                var fieldDepend = fields[i]['depend'];
                if (typeof(fieldDepend) === 'object') {
                    var field = fields[i];
                    if (jQuery('.ves-option-' + fieldDepend.field).val() == fieldDepend.value) {
                        jQuery('.ves-option-' + fields[i].name).parents(".control-group").show();
                    } else {
                        jQuery('.ves-option-' + fields[i].name).parents(".control-group").hide();
                    }
                    (function (field) {
                        var fieldDepend = field['depend'];
                        jQuery(".ves-option-" + fieldDepend.field).change(function() {
                            if (jQuery(this).val() == fieldDepend.value) {
                                jQuery('.ves-option-' + field.name).parents(".control-group").show();
                            } else {
                                jQuery('.ves-option-' + field.name).parents(".control-group").hide();
                            }
                        });
                    })(field);
                }
            }
        },

        loadWysiwygEditor: function() {
            var self = this;
            var item = ko.toJS(this.itemForEditing());
            jQuery(".megamenu-editor1").find(".ves-editor").each(function(index, element){
                var id = jQuery(element).attr("id");
                if(jQuery('#'+id).length) {
                    var config = window.megamenu.editor;
                    var editor;
                    //tinymce.EditorManager.execCommand('mceRemoveEditor',true, id);
                    tinymce.EditorManager.execCommand('mceRemoveEditor',true, id);
                    tinymce.EditorManager.execCommand('mceRemoveControl',true, id);
                    //tinymce.execCommand('mceRemoveControl', true, id);
                    var key = jQuery(element).data("key");
                    config = JSON.parse(config);
                    config['forced_root_block'] = false;
                    editor = new wysiwygSetup(id, config);
                    if(typeof(editor)=='undefined'){
                        jQuery('.action-wysiwyg').hide();
                    }
                    editor.toggle();

                    jQuery('#'+id)
                        .addClass('wysiwyg-editor')
                        .data(
                            'wysiwygEditor',
                            editor
                        );
                                                
                    varienGlobalEvents.clearEventHandlers("open_browser_callback");
                    varienGlobalEvents.attachEventHandler("open_browser_callback", editor.openFileBrowser);

                    if (tinyMCE.get(id)) {
                        if (typeof(item[key]) == 'string') {
                            item[key] = self.encodeDirectives(item[key]);
                            tinyMCE.get(id).setContent(item[key]);
                        } else {
                            tinyMCE.get(id).setContent('');
                        }
                    } else if (item[key]) {
                        $(this).val(self.encodeDirectives(item[key]));
                    }
                }
            });
        },

        /* retrieve directives URL with substituted directive value*/
        makeDirectiveUrl: function(directive) {

            var config = $.parseJSON('[' + window.megamenu.editor + ']');
            return config[0].directives_url.replace('directive', 'directive/___directive/' + directive);
        },

        encodeDirectives: function(content) {
            /* collect all HTML tags with attributes that contain directives*/
            return content.gsub(/<([a-z0-9\-\_]+.+?)([a-z0-9\-\_]+=".*?\{\{.+?\}\}.*?".+?)>/i, function(match) {
                var attributesString = match[2];
                /* process tag attributes string */
                attributesString = attributesString.gsub(/([a-z0-9\-\_]+)="(.*?)(\{\{.+?\}\})(.*?)"/i, function(m) {
                    return m[1] + '="' + m[2] + this.makeDirectiveUrl(Base64.mageEncode(m[3])) + m[4] + '"';
                }.bind(this));

                return '<' + match[1] + attributesString + '>';

            }.bind(this));
        },

        loadColorPicker: function() {
            $(".megamenu-editor1").find(".ip-color").each(function (index, element) {
                var bgColor = $(this).val();
                if (bgColor!='') {
                    $(this).css({"background-color":bgColor});
                }
            });
        },

        loadCollapsible: function(element)  {
            var $parent = jQuery(element).parent();
            $parent.toggleClass("active");
            $parent.find('.ves-fieldset-content').eq(0).toggleClass('active');
        },

        convertToObject: function(children, $type = '', $startindex = 0, $endindex = 0, $level = 0, $maxlevel = 0, $isSave = true) {
            var _self = this;
            var itemChidrens = [];
            for (var i = 0; i < children.length; i++) {
                if($endindex > 0 && $endindex != '' && $endindex !='0' && $endindex != null && $endindex != 'undefined') {
                    if(i < $startindex) continue;
                    if($endindex != 'end' && $endindex != 'all' && i >= $endindex) continue;
                }
                if (children[i] instanceof Item) {
                    var item = ko.toJS(children[i]);
                    itemChidrens[i] = '';
                    var defaultValue = {
                        name: item['name'],
                        children: item.children
                    };
                    for (var x = 0; x < fields.length; x++) {
                        var type = fields[x]['type'];
                        if (type == 'fieldset' || type == 'separator')  {
                            continue;
                        }
                        var key = fields[x]['name'];
                        if (fields[x]['value']) {
                            defaultValue[key] = fields[x]['value'];
                        }
                    }
                    var newItem = new Item(defaultValue);
                    itemChidrens[i] = newItem;
                    if($isSave) 
                        this.importingItems.push(newItem);
                        /*this.saveItem(newItem, 'import');*/
                } else {
                    for (var x = 0; x < fields.length; x++) {
                        var type = fields[x]['type'];
                        if (type == 'fieldset' || type == 'separator')  {
                            continue;
                        }
                        var key = fields[x]['name'];
                        if (typeof(children[i][key]) == 'undefined') {
                            children[i][key] = fields[x]['value'];
                        }
                    }
                    var item               = new Item(children[i]);
                    itemChidrens[i]        = item;

                    if($isSave)
                        this.importingItems.push(item);
                        /*this.saveItem(item, 'import');*/
                }
                if (children[i] && children[i]['children']) {
                    if($level && $maxlevel) {
                        if ($level < $maxlevel) {
                            itemChidrens[i]['children'] = this.convertToObject(children[i]['children'], $type, $startindex, $endindex, ++$level, $maxlevel, $isSave);
                        } else {
                            itemChidrens[i]['children'] = [];
                        }
                    } else {
                        itemChidrens[i]['children'] = this.convertToObject(children[i]['children'], $type, $startindex, $endindex, $level, $maxlevel, $isSave);
                    }
                }
                _self.importedIndex.push("cat"+i);
            }
            return itemChidrens;
        },

        preview: function() {
            var iframeUrl = '';
            var url = jQuery('#preview-form option:selected', this).attr('url');
            var data = jQuery('#edit_form').serialize();
            var previewStore = this.previewStore();
            jQuery('#preview-form option').each(function(index, el) {
                var value = jQuery(this).attr('value');
                if (value == previewStore) {
                    iframeUrl = jQuery(this).data('url');
                }
            });
            var self = this;
            jQuery.ajax({
                url: window.megamenu.ajaxSaveMenuUrl,
                type: 'POST',
                processData: false,
                dataType: 'json',
                data: $('#edit_form').serialize(),
                beforeSend: function() {
                    self.loadSpinner('', 5000);
                },
                success: function(response) {
                    if (response['status'] === true && iframeUrl) {
                        var iframe = $("#myiFrame");
                        iframe.attr("src", iframeUrl + "?menu_id=" + $('#menu_id').val());
                        jQuery('#myiFrame').show();
                    }
                }
            });

            var options = {
                type: 'popup',
                modalClass: 'vesmodal ves-preview',
                responsive: true,
                innerScroll: true,
                title: 'Preview',
                closed: function(obj) {
                    $("#myiFrame").attr("src", "");
                }
            };
            var popup = modal(options, jQuery('#preview-form'));
            jQuery('#preview-form').modal('openModal');
        },

        gotoScroll: function() {
            var scrollTop = jQuery(window).scrollTop();
            if (scrollTop>1000) {
                jQuery('html, body').animate({
                    scrollTop: jQuery("#megamenu-editor1").offset().top - 100
                }, 1000);
            }
        },

        loadSpinner: function(message, time = 800) {
            var position = $(window).scrollTop();
            var pageActionsHeight = jQuery('.page-actions').height();
            var self = this;
            self.loading(true);
            setTimeout(function() {
                if (self.ajaxCount == 0) {
                    self.loading(false);
                }
                if (message!='') {
                    Message($.mage.__(message));
                }
            }, time);
        },

        switcher: function(targetField, item) {
            var item        = this.itemForEditing();
            var edited      = ko.toJS(item);
            var value;
            if (typeof(edited[targetField]) !== 'undefined' && edited[targetField] != null) {
                if (edited[targetField] == 1) {
                    value = 0;
                    $('.ves-option-' + targetField).val(0).prop( "checked", false );
                } else {
                    value = 1;
                    $('.ves-option-' + targetField).val(1).prop( "checked", true );
                }
            }
            edited[targetField] = value;
            item.update(edited);
            this.loadDependField();
        },

        previewMenu: function() {
            var iframeUrl = '';
            var url = jQuery('#preview-form option:selected', this).attr('url');
            var data = jQuery('#edit_form').serialize();
            var previewStore = this.previewStore();
            this.loadSpinner('', 5000);
            jQuery('#preview-form option').each(function(index, el) {
                var value = jQuery(this).attr('value');
                if (value == previewStore) {
                    var iframe = $("#myiFrame");
                    iframe.attr("src", jQuery(this).data('url') + "?menu_id=" + $('#menu_id').val());
                    jQuery('#myiFrame').show();
                }
            });
        },

        animateSwitcher: function() {
            $('.megamenu-editor1').find('.admin__actions-switch').each(function(index, el) {
                if ($(this).data('value') == 0) {
                    $(this).find('.admin__actions-switch-checkbox').val(0).prop( "checked", false );
                } else {
                    $(this).find('.admin__actions-switch-checkbox').val(1).prop( "checked", true );
                }
            });
        },

        submitForm: function(params) {
            var self = this;
            if (self.ajaxCount > 0) {
                return false;
            }
            var actionUrl = $('#edit_form').attr('action') + params;
            $('#edit_form').attr('action', actionUrl);
            $('#edit_form').submit();
        },

        setMenuType: function(type) {
            this.modeClass(type);
        },

        changeDesign: function(obj, event) {
            var element  = $(event.currentTarget);
            var property = $(element).data('name');
            var val      = element.val();

            if (parseFloat(val) > 0) {
                if (element.data('attribute') == 'margin') {
                    val = val.toString() + $('.margin-units').val();
                }

                if (element.data('attribute') == 'border') {
                    val = val.toString() + $('.border-units').val();
                }

                if (element.data('attribute') == 'padding') {
                    val = val.toString() + $('.padding-units').val();
                }

                if (element.data('attribute') == 'borderradius') {
                    val = val.toString() + $('.borderradius-units').val();
                }
            }

            $('#nestable > .dd-list').css(property, val);
        },

        changeUnit: function(obj, event) {
            var element = $(event.currentTarget);
            var attr = element.data('name');
            $('input[data-attribute=' + attr + ']').addClass("DEMO123").trigger('change');
        },

        changeBoxShadow: function(obj, event) {
            var val = '';
            if ($('.boxshadow-inset').hasClass('active')) {
                val += 'inset ';
            }
            val += ' ' + $('#boxshadow_x').val() + $('.boxshadow-units').val();
            val += ' ' + $('#boxshadow_y').val() + $('.boxshadow-units').val();
            val += ' ' + $('#boxshadow_blur').val() + $('.boxshadow-units').val();
            val += ' ' + $('#boxshadow_spread').val() + $('.boxshadow-units').val();
            val += ' ' + $('#boxshadow_color').val();
            $('#nestable > .dd-list').css('box-shadow', val);
        }

    });
});