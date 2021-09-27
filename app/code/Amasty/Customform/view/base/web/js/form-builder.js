define([
    "jquery",
    'jquery/ui',
    'Amasty_Customform/js/form-builder-helper',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function ($, ui, helper, alert) {
    'use strict';

    $.widget('mage.customFormBuilder', {
        options: {
            controlPosition: 'right',
            controlOrder: ['autocomplete', 'button', 'checkbox', 'checkbox-group',
                'date', 'file', 'header', 'hidden', 'paragraph', 'number', 'radio-group', 'select', 'text', 'textarea'],
            dataType: 'json',
            // Array of fields to disable
            disableFields: [],
            editOnAdd: false,
            // Uneditable fields or other content you would like to appear before and after regular fields:
            append: false,
            prepend: false,
            defaultFields: [],
            inputSets: [],
            fieldRemoveWarn: false,
            messages: {},
            frmbFields: [{
                label: 'autocomplete',
                attrs: {
                    type: 'autocomplete',
                    className: 'autocomplete',
                    name: 'autocomplete'
                }
            }],
            notify: {
                error: function error(message) {
                    return console.error(message);
                },
                success: function success(message) {
                    return console.log(message);
                },
                warning: function warning(message) {
                    return console.warn(message);
                }
            },
            sortableControls: false,
            stickyControls: false,
            showActionButtons: true,
            typeUserAttrs: {},
            typeUserEvents: {},
            prefix: 'form-builder-',
            fbInstances: [],
            savedValue: '[name="form_json"]',
            savedTitles: '[name="form_title"]',
            pageFormId: [],
            pageTitles: []
        },

        _create: function () {
            var self = this,
                formbId,
                savedValue,
                formDataJSON = [],
                savedTitles,
                pageCount;

            self.generateSubtypes();

            savedValue = $(self.options.savedValue).val();
            if (savedValue) {
                formDataJSON = window.JSON.parse(savedValue);
            }

            savedTitles = $(self.options.savedTitles).val();
            if (savedTitles) {
                self.formTitle = window.JSON.parse(savedTitles);
            }

            if (formDataJSON.length && !Array.isArray(formDataJSON[0])) {
                var createPage = [];
                createPage.push(formDataJSON);
                formDataJSON = createPage;
            }

            if (formDataJSON.length) {
                pageCount = formDataJSON.length;

                for (var i = 0; i < pageCount; i++) {
                    self.formData = formDataJSON[i];
                    formbId = 'frmb-' + i;

                    if (i == 0) {
                        self.initialization(formbId);
                        self.setPageTitle(i);
                    } else {
                        self.createNewPage(formbId);
                        self.setPageTitle(i);
                    }
                }
                $('[data-amcform-role="page"]:first-child').find('a').trigger('click');
            } else {
                formbId = 'frmb-' + $('ul[id^=frmb-].frmb').length++;
                self.formData = formDataJSON;
                self.initialization(formbId);
                if ($('[data-amcform-role="page"]').length == 1) {
                    $('[data-amcform-role="page"]').find('a').trigger('click');
                }
            }
        },

        initialization: function (formbId) {
            var self = this,
                $cbUL,
                boxID = formbId + '-control-box';

            this.lastID = formbId + '-fld-1';
            this.options.formID = formbId;
            this.options.pageFormId.push(formbId);

            this.helpers = helper[1](this.options, this);
            this.utils = helper[0];

            this.layout = this.helpers.editorLayout(this.options.controlPosition);

            // create array of field objects to cycle through
            var frmbFields = this.options.frmbFields;
            //frmbFields = this.helpers.orderFields(frmbFields);

            if (this.options.disableFields) {
                // remove disabledFields
                frmbFields = frmbFields.filter(function (field) {
                    return !self.utils.inArray(field.attrs.type, self.options.disableFields);
                });
            }

            // Create draggable fields for this.options
            var cbUl = this.utils.markup('ul', null, {id: boxID, className: 'frmb-control'});

            if (this.options.sortableControls) {
                cbUl.classList.add('sort-enabled');
            }

            $cbUL = $(cbUl);
            this.generateFormFields(frmbFields, $cbUL);

            if (this.options.inputSets.length) {
                $('<li/>', {'class': 'fb-separator'}).html('<hr>').appendTo($cbUL);
                this.options.inputSets.forEach(function (set) {
                    set.name = set.name || this.helpers.makeClassName(set.label);
                    var $set = $('<li/>', {'class': 'input-set-control', type: set.name});
                    $set.html(set.label).appendTo($cbUL);
                });
            }

            this.generateSortable(formbId, $cbUL);
            this.generateWrapperContent(formbId, $cbUL);

            $cbUL.prev('.amcustomform-element-tabs').find('li:first-child').click();

            this.observeFields($cbUL, formbId);

            this.helpers.getData();
            this.loadFields();

            this.sortableFields.css('min-height', $cbUL.height());

            // If option set, controls will remain in view in editor
            if (this.options.stickyControls) {
                this.helpers.stickyControls(this.sortableFields, cbUl);
            }

            document.dispatchEvent(self.events.loaded);

            // Make actions accessible
            self.actions = {
                clearFields: self.helpers.removeAllfields,
                showData: self.helpers.showData,
                save: self.helpers.save,
                addField: function addField(field, index) {
                    self.helpers.stopIndex = self.sortableFields[0].children.length ? index : undefined;
                    self.prepFieldVars(field);
                    document.dispatchEvent(self.events.fieldAdded);
                },
                removeField: self.helpers.removeField,
                setData: function setData(formData) {
                    self.helpers.removeAllfields();
                    self.helpers.getData(formData);
                    self.loadFields();
                }
            };
        },

        /**
         * Set page title for current page if there is a saved title
         * @param page - (int) current page
         */

        setPageTitle: function (page) {
            var self = this,
                id = page + 1;

            if (self.formTitle) {
                $('#page-title-' + id).val(self.formTitle[page]);
            }
        },

        /**
         * Create new page
         * @param formbId - (string) id for new form
         */

        createNewPage: function (formbId) {
            var self = this,
                tabCount = $('[data-amcform-js="tabs-wrap"]').children().length,
                tabId = "page-" + tabCount,
                titleId = 'page-title-' + tabCount,
                $newPageTemplate = $('[data-amcform-js="new-page"]'),
                $newPage = $newPageTemplate
                    .clone()
                    .removeAttr("data-amcform-js")
                    .attr("id", tabId)
                    .addClass("fb-editor"),
                $newTab = $('[data-amcform-js="add-new-page"]')
                    .clone()
                    .removeAttr("data-amcform-js")
                    .attr('data-amcform-role', 'page')
                    .removeClass('-new'),
                $newTitle = $newPage.find('[data-amcform-js="title-edit-new"]');

            $("a", $newTab)
                    .attr("href", "#" + tabId)
                    .attr('data-amcform-role', 'page-link')
                    .attr("title", $.mage.__('Page ') + tabCount)
                    .text($.mage.__('Page ') + tabCount);

            $newTitle.attr('data-amcform-js', 'title-edit');
            $newTitle.find('input').attr('id', titleId);
            $newTitle.find('label').attr('for', titleId);

            $newPage.insertBefore($newPageTemplate);
            $newTab.insertBefore('[data-amcform-js="add-new-page"]');

            self.options.formWrapper.tabs("refresh");
            self.options.formWrapper.tabs("option", "active", tabCount - 1);

            self.element = $newPage;

            $('[data-amcform-role="page"]').removeClass('active');
            $newTab.addClass('active');

            if (!formbId) {
                self._create();
                return;
            }

            self.initialization(formbId);
        },

        /**
         * Delete current page
         * @param page - DOM node
         * @param e - event
         */

        deleteCurentPage: function (page, e) {
            var self = this,
                tab = $(page).parent(),
                prevTab = tab.prev().find('a'),
                pageId = $(page).prev().attr('href').split('-')[1];

            e.stopPropagation();

            self.downgradeTabNumber(tab);

            tab.remove();
            $('#page-' + pageId).remove();
            prevTab.trigger('click');
        },

        /**
         * Downgrade tabs number after deleting previos tab
         * @param tab - DOM element
         */
        downgradeTabNumber: function (tab) {
            var nextTabs = tab.nextAll().find('a');

            nextTabs.each(function (index, page) {
                if (index == nextTabs.length -1) {
                    return;
                }

                var pageValue = $(page).text().split(' '),
                    newPageId = pageValue[1] - 1,
                    pageLabel = pageValue[0];

                $(page).text(pageLabel + ' ' + newPageId);
                $(page).attr('title', pageLabel + ' ' + newPageId);
            });
        },

        /**
         * Navigate between form pages
         * @param page - DOM node
         */

        navigatePageForm: function (page) {
            if ($(page).length > 0) {
                var pageId = $(page).attr('href'),
                    tab = $(page).parent(),
                    tabId = pageId.split('-')[1] - 1,
                    fields;

                $('[data-amcform-role="page"]').removeClass('active');
                $(tab).addClass('active');
                $(pageId).find('.amcustomform-element-tabs li:first-child').trigger('click');

                // Set new settings
                this.options.formID = 'frmb-' + tabId;
                fields = $('#' + this.options.formID).children().length + 1;
                this.lastID = this.options.formID + '-fld-' + fields;
                this.sortableFields = $('ul#' + this.options.formID + '.frmb.ui-sortable');
                this.stageWrap = $('div#' + this.options.formID + '-stage-wrap.stage-wrap.pull-left');
            }
        },

        navigateKeyboard: function () {
            this.navigatePageForm(
                $('[data-amcform-role="page"][aria-selected="true"] a')
            );
        },

        /**
         * Set page titles in hidden input
         */

        savePageTitles: function () {
            var self = this;

            $.each($('[data-amcform-js="title-edit"] .amcform-input'), function (index, title) {
                self.options.pageTitles.push($(title).val());
            });

            $('#form_form_title').val(window.JSON.stringify(self.options.pageTitles, null, '\t'));

            $('[data-amcform-js^="title-edit"] .amcform-input').each(function (key, element) {
                $(element).attr('disabled', 'disabled');
            });
        },

        /**
         * Save form configuration
         */

        generateSaveEvent: function (event) {
            var self = this;

            self.savePageTitles();

            $.each(self.options.pageFormId, function (index, id) {
                var form = document.getElementById(id),
                    json;

                if (form) {
                    json = self.helpers.prepData(form);

                    if (json.length) {
                        self.options.fbInstances.push(json);
                    }
                }
            });

            var formFields = 'input, textarea, select',
                formContent = window.JSON.stringify(self.options.fbInstances, null, '');
            if (formContent.length < 65000) {
                $('[name="form_json"]').val(formContent);
                $('.form-wrap').find(formFields).each(function (key, element) {
                    $(element).removeAttr('required').attr('disabled', 'disabled');
                });
            } else {
                event.preventDefault();
                self.options.fbInstances = [];
                self.options.pageTitles = [];
                alert({
                    title: $.mage.__('Error'),
                    content: $.mage.__('You have exceeded the maximum allowed number of fields. Please try to create several forms instead of this one.')
                });
            }
        },

        generateSubtypes: function () {
            this.options.messages.subtypes = function () {
                var subtypeDefault = function subtypeDefault(subtype) {
                    return {
                        label: subtype,
                        value: subtype
                    };
                };

                return {
                    text: ['text', 'password', 'email', 'color', 'tel'].map(subtypeDefault),
                    header: ['h1', 'h2', 'h3'].map(subtypeDefault)
                };
            }();
        },

        generateWrapperContent: function (formbId, $cbUL) {
            var $formWrap = $('<div/>', {
                id: formbId + '-form-wrap',
                'class': 'form-wrap form-builder' + this.helpers.mobileClass()
            });

            this.stageWrap = $('<div/>', {
                id: formbId + '-stage-wrap',
                'class': 'stage-wrap ' + this.layout.stage
            });

            var controlbWrap = $('<div/>', {
                id: formbId + '-cb-wrap',
                'class': 'cb-wrap ' + this.layout.controls
            });

            /*show type controls*/
            var typesWrap = $('<ul/>', {
                'class': 'type-wrap amcform-tabs-wrap -second amcustomform-element-tabs'
            });

            var fieldsTypes = this.options.fieldsTypes;
            this.utils.forEach(fieldsTypes, function (i) {
                var $field = $('<li/>', {
                    'id': 'amcustomform-type-' + fieldsTypes[i].type,
                    'type': fieldsTypes[i].type,
                    'text': fieldsTypes[i].title,
                    'class': 'amcform-tab'
                }).appendTo(typesWrap);

                $field.on('click', function () {
                    $('.amelement-container').hide();
                    $('.amcustomform-element-tabs li').removeClass('active');
                    $(this).addClass('active');
                    var type = $(this).attr('type');
                    $('.amelement-container[parenttype="' + type + '"]').fadeIn();
                });
            });

            controlbWrap.append(typesWrap);
            controlbWrap.append($cbUL[0]);

            if (this.options.showActionButtons) {
                // Build our headers and action links
                var viewDataText = this.options.dataType === 'xml' ? this.options.messages.viewXML : this.options.messages.viewJSON,

                    clearAll = this.utils.markup('button', this.options.messages.clearAll, {
                        id: formbId + '-clear-all',
                        type: 'button',
                        className: 'clear-all btn btn-default'
                    });
                var formActions = this.utils.markup('div', [clearAll], {
                    className: 'form-actions btn-group'
                });

                controlbWrap.append(formActions);
            }

            this.stageWrap.append($(this.element).find('[data-amcform-js="title-edit"]'));
            this.stageWrap.append(this.sortableFields, controlbWrap);
            this.stageWrap.before($formWrap);
            $formWrap.append(this.stageWrap, controlbWrap);
            $(this.element).append($formWrap);
        },

        generateSortable: function (formbId, $cbUL) {
            // Sortable fields
            this.sortableFields = $('<ul/>').attr('id', formbId).addClass('frmb');
            this.sortableFields.sortable({
                cursor: 'move',
                opacity: 0.9,
                revert: 150,
                beforeStop: this.helpers.beforeStop,
                start: this.helpers.startMoving,
                stop: this.helpers.stopMoving,
                cancel: 'input, select, .disabled, .form-group, .btn',
                placeholder: 'frmb-placeholder'
            });

            // ControlBox with different fields
            var self = this;
            $cbUL.sortable({
                helper: 'clone',
                opacity: 0.9,
                connectWith: this.sortableFields,
                cancel: '.fb-separator',
                cursor: 'move',
                scroll: false,
                placeholder: 'ui-state-highlight',
                start: this.helpers.startMoving,
                stop: this.helpers.stopMoving,
                revert: 150,
                beforeStop: this.helpers.beforeStop,
                distance: 3,
                update: function update(event, ui) {
                    if (self.helpers.doCancel) {
                        return false;
                    }
                    if (ui.item.parent()[0] === self.sortableFields[0]) {
                        self.processControl(ui.item);
                        self.helpers.doCancel = true;
                    } else {
                        self.helpers.setFieldOrder($cbUL);
                        self.helpers.doCancel = !self.options.sortableControls;
                    }
                }
            });
        },

        generateFormFields: function (frmbFields, $cbUL) {
            var self = this;
            this.utils.forEach(frmbFields, function (i) {
                var $field = $('<li/>', {
                    'class': 'amelement-container icon-' + frmbFields[i].attrs.className,
                    'type': frmbFields[i].attrs.type,
                    'parentType': frmbFields[i].attrs.parentType,
                    'name': frmbFields[i].className,
                    'label': frmbFields[i].attrs.label
                });

                var $title = $('<div/>', {
                    'class': 'amelement-title',
                    'html': frmbFields[i].label
                }).appendTo($field);

                var $contentContainer = $('<div/>', {
                    'class': 'amelement-content',
                    'html': frmbFields[i].content
                }).appendTo($field);

                $field.data('newFieldData', frmbFields[i]);

                $field.appendTo($cbUL);

                if (frmbFields[i].attrs.type == 'date') {
                    self.helpers.updateDateField($field, frmbFields[i].attrs.format);
                }
            });
        },

        saveAndUpdate: function () {
            var self = this;
            return this.helpers.debounce(function (evt) {
                if (evt) {
                    if (evt.type === 'keyup' && this.name === 'className') {
                        return false;
                    }
                }

                var $field = $(this).parents('.form-field:eq(0)');
                self.helpers.updatePreview($field);
                self.helpers.save();
            });
        },

        processControl: function (control) {
            if (control[0].classList.contains('input-set-control')) {
                var inputSet = this.options.inputSets.filter(function (set) {
                    return set.name === control[0].type;
                })[0];
                if (inputSet.showHeader) {
                    var header = {
                        type: 'header',
                        subtype: 'h2',
                        id: inputSet.name,
                        label: inputSet.label
                    };
                    this.prepFieldVars(header, true);
                }
                inputSet.fields.forEach(function (field) {
                    //TODO Check this
                    this.prepFieldVars(field, true);
                }).bind(this);
            } else {
                this.prepFieldVars(control, true);
            }
        },

        prepFieldVars: function ($field) {
            var isNew = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false,
                self = this;

            var field = {};
            if ($field instanceof jQuery) {
                var fieldData = $field.data('newFieldData');
                if (fieldData) {
                    field = fieldData.attrs;
                    field.label = fieldData.label;
                    field.options = fieldData.options;
                    if (fieldData.childs) {
                        fieldData.childs.each(function (child) {
                            var element = $('.amelement-container[type="' + child.type + '"]');
                            if (element) {
                                var childData = element.data('newFieldData');
                                if (childData) {
                                    var childField = childData.attrs;
                                    childField.label = childData.label;
                                    childField.options = childData.options;
                                    childField = Object.assign(childField, child.data);
                                    self.prepFieldVars(childField, true);
                                }
                            }
                        });
                        return;
                    }
                } else {
                    var attrs = $field[0].attributes;
                    if (!isNew) {
                        field.values = $field.children().map(function (index, elem) {
                            return {
                                label: $(elem).text(),
                                value: $(elem).attr('value'),
                                selected: Boolean($(elem).attr('selected'))
                            };
                        });
                    }

                    for (var i = attrs.length - 1; i >= 0; i--) {
                        field[attrs[i].name] = attrs[i].value;
                    }
                }
            } else {
                field = Object.assign({}, $field);
            }

            field.name = isNew ? this.nameAttr(field) : field.name || this.nameAttr(field);

            switch (field.type) {
                case 'country' :
                    field.type = 'dropdown';
                    break;
            }

            if (isNew && this.utils.inArray(
                field.type,
                ['textinput', 'number', 'file', 'dropdown', 'listbox', 'textarea']
            )
            ) {
                field.className = 'form-control'; // backwards compatibility
            } else {
                field.className = field.class || field.className; // backwards compatibility
            }

            var match = /(?:^|\s)btn-(.*?)(?:\s|$)/g.exec(field.className);
            if (match) {
                field.style = match[1];
            }

            this.utils.escapeAttrs(field);

            this.appendNewField(field);
            if (isNew) {
                document.dispatchEvent(this.events.fieldAdded);
            }
            this.stageWrap.removeClass('empty');
        },

        appendNewField: function (values) {
            var self = this;
            var type = values.type || 'text',
                label = values.label || this.options.messages[type] || this.options.messages.label,
                delBtn = this.utils.markup('a', this.options.messages.remove, {
                    id: 'del_' + this.lastID,
                    className: 'del-button btn delete-confirm',
                    title: this.options.messages.removeMessage
                }),
                toggleBtn = this.utils.markup('a', null, {
                    id: this.lastID + '-edit',
                    className: 'toggle-form btn icon-pencil',
                    title: this.options.messages.hide
                }),
                copyBtn = this.utils.markup('a', this.options.messages.copyButton, {
                    id: this.lastID + '-copy',
                    className: 'copy-button btn icon-copy',
                    title: this.options.messages.copyButtonTooltip
                });

            var liContents = this.utils.markup('div', [toggleBtn, copyBtn, delBtn], {className: 'field-actions'}).outerHTML;

            // Field preview Label
            if (['hone', 'text', 'htwo', 'hthree'].indexOf(type) != -1) {
                label = ''; //do not show label for text elements
            }
            liContents += '<label class="field-label">' + label + '</label>';
            var requiredDisplay = values.required ? 'style="display:inline"' : '';
            liContents += '<span class="required-asterisk" ' + requiredDisplay + '> *</span>';
            liContents += this.utils.markup('a', 'x', {className: 'close-field top-close-field'}).outerHTML;
            if (values.description) {
                //liContents += '<span class="tooltip-element" tooltip="' + values.description + '">?</span>';
                liContents += '<span class="tooltip-element" tooltip="' + values.description + '">i</span>';
            }

            liContents = '<div class="field-label-container">' + liContents + '</div>';

            liContents += this.utils.markup('div', '', {className: 'prev-holder'}).outerHTML;
            liContents += '<div id="' + this.lastID + '-holder" class="frm-holder">';
            liContents += '<div class="form-elements">';

            liContents += this.advFields(values);
            liContents += this.utils.markup('a', this.options.messages.close, {className: 'close-field'}).outerHTML;

            liContents += '</div>';
            liContents += '</div>';

            var field = this.utils.markup('li', liContents, {
                    'class': type + '-field form-field',
                    'type': type,
                    id: this.lastID
                }),
                $li = $(field);

            $li.data('fieldData', {attrs: values});
            if (typeof this.helpers.stopIndex !== 'undefined') {
                $('> li', this.sortableFields).eq(this.helpers.stopIndex).before($li);
            } else {
                this.sortableFields.append($li);
            }

            $('.sortable-options', $li).sortable({
                update: function update() {
                    self.helpers.updatePreview($li);
                }
            }); // make dynamically added option fields sortable if they exist.

            if (values.type == 'date') {
                $li.attr('date-format', values.format);
            }
            this.helpers.updatePreview($li);

            if (this.options.editOnAdd) {
                this.helpers.closeAllEdit(this.sortableFields);
                this.helpers.toggleEdit(this.lastID);
            }

            if (this.options.typeUserEvents[type] && this.options.typeUserEvents[type].onadd) {
                this.options.typeUserEvents[type].onadd(field);
            }

            this.lastID = this.helpers.incrementId(this.lastID);
        },

        // Add append and prepend options if necessary
        nonEditableFields: function () {
            var cancelArray = [];

            if (this.options.prepend && !$('.disabled.prepend', this.sortableFields).length) {
                var prependedField = this.utils.markup('li', this.options.prepend, {className: 'disabled prepend'});
                cancelArray.push(true);
                this.sortableFields.prepend(prependedField);
            }

            if (this.options.append && !$('.disabled.append', this.sortableFields).length) {
                var appendedField = this.utils.markup('li', this.options.append, {className: 'disabled append'});
                cancelArray.push(true);
                this.sortableFields.append(appendedField);
            }

            if (cancelArray.some(function (elem) {
                return elem === true;
            })) {
                this.stageWrap.removeClass('empty');
            }
        },

        // Parse saved XML template data
        loadFields: function () {
            var self = this;
            var formData = this.formData;
            if (formData && formData.length) {
                for (var i = 0; i < formData.length; i++) {
                    this.prepFieldVars(formData[i]);
                }
                this.stageWrap.removeClass('empty');
            } else if (this.options.defaultFields && this.options.defaultFields.length) {
                // Load default fields if none are set
                this.options.defaultFields.forEach(function (field) {
                    return self.prepFieldVars(field);
                });
                this.stageWrap.removeClass('empty');
            } else if (!this.options.prepend && !this.options.append) {
                this.stageWrap.addClass('empty').attr('data-content', this.options.messages.getStarted);
            }
            this.helpers.save();

            $('li.form-field:not(.disabled)', this.sortableFields).each(function () {
                self.helpers.updatePreview($(this));
            });

            this.nonEditableFields();
        },

        nameAttr: function (field) {
            var epoch = new Date().getTime();
            return field.type + '-' + epoch;
        },

        /**
         * Add data for field with options [select, checkbox-group, radio-group]
         *
         * @todo   refactor this nasty ~crap~ code, its actually painful to look at
         * @param  {object} values
         */
        fieldOptions: function (values) {
            var self = this;
            var optionActions = [this.utils.markup('a', this.options.messages.addOption, {className: 'add add-opt'})],
                fieldOptions = ['<label class="false-label">' + this.options.messages.selectOptions + '</label>'],
                isMultiple = values.multiple || values.type.match(/(listbox|checkbox|checkboxtwo|checkbox-group)/);

            if (!values.values || !values.values.length) {
                var counter = [1, 2, 3],
                    optionLabel = self.options.messages.option;

                if (values.type === 'rating') {
                    counter = [1, 2, 3, 4, 5];
                    optionLabel = self.options.messages.star;
                }

                if (values.options) {
                    values.values = values.options;
                } else {
                    values.values = counter.map(function (index) {
                        var label = optionLabel + ' ' + index,
                            option = {
                                selected: false,
                                label: label,
                                value: self.utils.hyphenCase(label)
                            };
                        return option;
                    });
                }

                values.values[0].selected = true;
            }

            fieldOptions.push('<div class="sortable-options-wrap">');

            fieldOptions.push('<ol class="sortable-options">');

            var emptyFieldSelected = ' checked="true"',
                valuesOptions = [];
            this.utils.forEach(values.values, function (i) {
                if (values.values[i].selected) {
                    emptyFieldSelected = '';
                }
                valuesOptions.push(self.selectFieldOptions(values.name, values.values[i], isMultiple));
            });

            fieldOptions.push('<li class="touch-disabled"><input type="radio" class="option-selected"' + emptyFieldSelected + ' value="false" name="'
                + values.name + '-option"></li>');
            fieldOptions = fieldOptions.concat(valuesOptions);

            fieldOptions.push('</ol>');
            fieldOptions.push(this.utils.markup('div', optionActions, {className: 'option-actions'}).outerHTML);
            fieldOptions.push('</div>');

            return this.utils.markup('div', fieldOptions.join(''), {className: 'form-group field-options'}).outerHTML;
        },

        /**
         * Build the editable properties for the field
         * @param  {object} values configuration object for advanced fields
         * @return {String}        markup for advanced fields
         */
        advFields: function (values) {
            var advFields = [],
                key,
                optionFields = ['dropdown', 'listbox', 'checkbox', 'radio', 'checkboxtwo', 'radiotwo', 'rating', 'country'],
                isOptionField = function () {
                    return optionFields.indexOf(values.type) !== -1;
                }(),
                valueField = !this.utils.inArray(values.type, ['header', 'paragraph', 'file'].concat(optionFields));

            advFields.push(this.textAttribute('name', values));
            advFields.push(this.hiddenAttribute('entity_id', values));
            advFields.push(this.textAttribute('label', values));
            // Class
            advFields.push(this.textAttribute('className', values));
            advFields.push(this.textAttribute('style', values));

            if (values.parentType !== 'other' || isOptionField) {

                advFields.push(this.textAttribute('placeholder', values));
                advFields.push(this.requiredField(values));
                advFields.push(this.textAttribute('description', values));

                if (values.type === 'checkbox' || values.type === 'checkboxtwo') {
                    advFields.push(this.boolAttribute('toggle', values, {first: this.options.messages.toggle}));
                }

                values.size = values.size || 'm';
                values.style = values.style || 'default';

                //Help Text / Description Field
                /* if (!this.utils.inArray(values.type, ['header', 'paragraph', 'button'])) {

                 }*/

                if (this.options.messages.subtypes[values.type]) {
                    var optionData = this.options.messages.subtypes[values.type];
                    advFields.push(this.selectAttribute('subtype', values, optionData));
                }

                if (values.type === 'number') {
                    advFields.push(this.numberAttribute('min', values));
                    advFields.push(this.numberAttribute('max', values));
                    advFields.push(this.numberAttribute('step', values));
                }

                if (values.type === 'textinput') {
                    advFields.push(this.selectAttribute('validation', values, this.options.messages.validations));
                }

                if (values.type === 'date') {
                    values.validation = 'validate-date';
                    advFields.push(this.hiddenAttribute('validation', values));
                }

                //TextArea Rows Attribute
                if (values.type === 'textarea') {
                    advFields.push(this.numberAttribute('rows', values));
                }

                if (valueField && values.type != 'googlemap') {
                    advFields.push(this.textAttribute('value', values));
                } else if (values.type == 'googlemap') {
                    advFields.push(this.hiddenAttribute('value', values));
                }

                if (values.type === 'file') {
                    advFields.push(this.textAttribute('allowed_extension', values));
                    advFields.push(this.textAttribute('max_file_size', values));

                    /* Disabled multiple uploading
                    var labels = {
                        first: this.options.messages.multiplefiles,
                        second: this.options.messages.allowMultiplefiles
                    };
                    advFields.push(this.boolAttribute('multiple', values, labels));*/
                }
                /* other feature for future
                if (values.type === 'checkbox' || values.type === 'radio') {
                    advFields.push(this.boolAttribute('other', values, {
                        first: this.options.messages.enableOther,
                        second: this.options.messages.enableOtherMsg
                    }));
                }*/

                if (isOptionField) {
                    advFields.push(this.fieldOptions(values));
                }

                if (this.utils.inArray(values.type, ['textinput', 'textarea'])) {
                    advFields.push(this.numberAttribute('maxlength', values));
                }

                // Append custom attributes as defined in typeUserAttrs option
                if (this.options.typeUserAttrs[values.type]) {
                    advFields.push(this.processTypeUserAttrs(this.options.typeUserAttrs[values.type], values));
                }
            }
            var dependency = [];
            if (values.dependency) {
                dependency = values.dependency;
            }
            advFields.push(this.fieldDependencyArea(dependency, values.name));

            advFields.push(this.selectAttribute('layout', values, this.options.messages.layouts));

            advFields.push(this.hiddenAttribute('parentType', values));

            return advFields.join('');
        },


        processTypeUserAttrs: function (typeUserAttr, values) {
            var advField = [];

            for (var attribute in typeUserAttr) {
                if (typeUserAttr.hasOwnProperty(attribute)) {
                    var orig = this.options.messages[attribute];
                    var origValue = typeUserAttr[attribute].value;
                    typeUserAttr[attribute].value = values[attribute] || typeUserAttr[attribute].value || '';

                    if (typeUserAttr[attribute].label) {
                        this.options.messages[attribute] = typeUserAttr[attribute].label;
                    }

                    if (typeUserAttr[attribute].options) {
                        advField.push(selectUserAttrs(attribute, typeUserAttr[attribute]));
                    } else {
                        advField.push(inputUserAttrs(attribute, typeUserAttr[attribute]));
                    }

                    this.options.messages[attribute] = orig;
                    typeUserAttr[attribute].value = origValue;
                }
            }

            return advField.join('');
        },

        inputUserAttrs: function (name, attrs) {
            var textAttrs = {
                    id: name + '-' + this.lastID,
                    title: attrs.description || attrs.label || name.toUpperCase(),
                    name: name,
                    type: attrs.type || 'text',
                    className: ['fld-' + name]
                },
                label = '<label for="' + textAttrs.id + '">' + this.options.messages[name] + '</label>';

            if (!this.utils.inArray(textAttrs.type, ['checkbox', 'radio', 'checkboxtwo', 'radiotwo'])) {
                textAttrs.className.push('form-control');
            }

            if (attrs.type === 'datetime') {
                attrs.type = 'datetime-local';
            }

            textAttrs = Object.assign({}, attrs, textAttrs);
            var textInput = '<input ' + this.utils.attrString(textAttrs) + '>',
                inputWrap = '<div class="input-wrap">' + textInput + '</div>';

            return '<div class="form-group ' + name + '-wrap">' + label + inputWrap + '</div>';
        },

        selectUserAttrs: function (name, options) {
            var optis = Object.keys(options.options).map(function (val) {
                    var attrs = {value: val};
                    if (val === options.value) {
                        attrs.selected = null;
                    }
                    return '<option ' + this.utils.attrString(attrs) + '>' + options.options[val] + '</option>';
                }),
                selectAttrs = {
                    id: name + '-' + this.lastID,
                    title: options.description || options.label || name.toUpperCase(),
                    name: name,
                    className: 'fld-' + name + ' form-control'
                },
                label = '<label for="' + selectAttrs.id + '">' + this.options.messages[name] + '</label>';

            Object.keys(options).filter(function (prop) {
                return !utils.inArray(prop, ['value', 'options', 'label']);
            }).forEach(function (attr) {
                selectAttrs[attr] = options[attr];
            });

            var select = '<select ' + this.utils.attrString(selectAttrs) + '>' + optis.join('') + '</select>',
                inputWrap = '<div class="input-wrap">' + select + '</div>';
            return '<div class="form-group ' + name + '-wrap">' + label + inputWrap + '</div>';
        },

        boolAttribute: function (name, values, labels) {
            if (this.options.typeUserAttrs[values.type] && this.options.typeUserAttrs[values.type][name]) {
                return;
            }

            var checked = values[name] !== undefined ? 'checked' : '',
                input = '<input type="checkbox" class="fld-' + name + '" name="' + name + '" value="true" ' + checked + ' id="' + name + '-' + this.lastID + '"/> ',
                left = [],
                right = [input];

            if (labels.first) {
                left.unshift(this.label(labels.first));
            }

            if (labels.second) {
                right.push(this.label(labels.second));
            }

            if (labels.content) {
                right.push(labels.content);
            }

            right.unshift('<div class="input-wrap">');
            right.push('</div>');

            return '<div class="form-group ' + name + '-wrap">' + left.concat(right).join('') + '</div>';
        },

        label: function (txt) {
            return '<label for="' + name + '-' + this.lastID + '">' + txt + '</label>';
        },

        btnStyles: function (style, type) {
            var tags = {
                    button: 'btn'
                },
                styles = this.options.messages.styles[tags[type]],
                styleField = '';

            if (styles) {
                var styleLabel = '<label>' + this.options.messages.style + '</label>';
                styleField += '<input value="' + style + '" name="style" type="hidden" class="btn-style">';
                styleField += '<div class="btn-group" role="group">';

                Object.keys(this.options.messages.styles[tags[type]]).forEach(function (element) {
                    var active = style === element ? 'active' : '';
                    styleField += '<button value="' + element + '" type="' + type + '" class="' + active + ' btn-xs ' + tags[type] + ' ' + tags[type] + '-' + element + '">' + this.options.messages.styles[tags[type]][element] + '</button>';
                });

                styleField += '</div>';

                styleField = '<div class="form-group style-wrap">' + styleLabel + ' ' + styleField + '</div>';
            }

            return styleField;
        },

        /**
         * Add a number attribute to a field.
         * @param  {String} attribute
         * @param  {Object} values
         * @return {String}
         */
        numberAttribute: function (attribute, values) {
            if (this.options.typeUserAttrs[values.type] && this.options.typeUserAttrs[values.type][attribute]) {
                return '';
            }

            var attrVal = values[attribute],
                attrLabel = this.options.messages[attribute] || attribute,
                placeholder = this.options.messages.placeholders[attribute],
                inputConfig = {
                    type: 'number',
                    value: attrVal,
                    name: attribute,
                    min: '0',
                    placeholder: placeholder,
                    className: 'fld-' + attribute + ' form-control',
                    id: attribute + '-' + this.lastID
                },
                numberAttribute = '<input ' + this.utils.attrString(this.utils.trimObj(inputConfig)) + '>',
                inputWrap = '<div class="input-wrap">' + numberAttribute + '</div>';

            return '<div class="form-group ' + attribute + '-wrap"><label for="' + inputConfig.id + '">'
                + attrLabel + '</label> ' + inputWrap + '</div>';

        },

        selectAttribute: function (attribute, values, optionData) {
            if (this.options.typeUserAttrs[values.type] && this.options.typeUserAttrs[values.type][attribute]) {
                return;
            }
            var self = this,
                allowedTypes = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;
            var selectOptions = optionData.map(function (option, i) {
                    if (allowedTypes && allowedTypes.indexOf(option.type) === -1) {
                        return false;
                    }
                    var optionAttrs = Object.assign({
                        label: self.options.messages.option + ' ' + i,
                        value: undefined
                    }, option);

                    if (values[attribute] != undefined) {
                        try {
                            var validationJson = JSON.parse(values[attribute].replace(/&quot;/g, '"'));
                        } catch (ex) {
                            validationJson = {validation: values[attribute]};
                        }

                        if (validationJson.hasOwnProperty("validation") && (option.value === validationJson.validation)) {
                            optionAttrs.selected = true;
                        }
                    }

                    return '<option ' + self.utils.attrString(self.utils.trimObj(optionAttrs)) + '>'
                        + optionAttrs.label + '</option>';
                }),
                selectAttrs = {
                    id: attribute + '-' + this.lastID,
                    name: attribute,
                    value: attribute,
                    className: 'fld-' + attribute + ' form-control'
                },

                label = '<label for="' + selectAttrs.id + '">' + (this.options.messages[attribute]
                    || this.utils.capitalize(attribute)) + '</label>';


            var select = '<select ' + this.utils.attrString(selectAttrs) + '>' + selectOptions.join('') + '</select>',
                inputWrap = '<div class="input-wrap">' + select + '</div>';

            return '<div class="form-group ' + selectAttrs.name + '-wrap">' + label + inputWrap + '</div>';
        },

        /**
         * Generate some text inputs for field attributes, **will be replaced**
         * @param  {String} attribute
         * @param  {Object} values
         * @return {String}
         */
        textAttribute: function (attribute, values) {
            if (this.options.typeUserAttrs[values.type] && this.options.typeUserAttrs[values.type][attribute]) {
                return '';
            }

            var textArea = ['paragraph'];

            var attrVal = values[attribute] || '',
                attrLabel = this.options.messages[attribute];
            if (attribute === 'label' && this.utils.inArray(values.type, textArea)) {
                attrLabel = this.options.messages.content;
            }

            var placeholderFields = ['textinput', 'textarea', 'dropdown'];
            var placeholders = this.options.messages.placeholders,
                placeholder = placeholders[attribute] || '',
                attributefield = '',
                noMakeAttr = [];
            // Field has placeholder attribute
            if (attribute === 'placeholder' && !this.utils.inArray(values.type, placeholderFields)) {
                noMakeAttr.push(true);
            }

            if (!noMakeAttr.some(function (elem) {
                return elem === true;
            })) {
                var inputConfig = {
                    name: attribute,
                    placeholder: placeholder,
                    className: 'fld-' + attribute + ' form-control',
                    id: attribute + '-' + this.lastID
                };
                var attributeLabel = '<label for="' + inputConfig.id + '">' + attrLabel + '</label>';

                if (attribute === 'label' && this.utils.inArray(values.type, textArea)
                    || attribute === 'value' && values.type === 'textarea') {
                    attributefield += '<textarea ' + this.utils.attrString(inputConfig) + '>' + attrVal + '</textarea>';
                } else if (attribute === 'value' && values.type === 'date') {
                    inputConfig.value = attrVal;
                    inputConfig.type = 'date';
                    attributefield += '<input ' + this.utils.attrString(inputConfig) + '>';
                } else {
                    inputConfig.value = attrVal;
                    inputConfig.type = 'text';
                    attributefield += '<input ' + this.utils.attrString(inputConfig) + '>';
                }

                var inputWrap = '<div class="input-wrap">' + attributefield + '</div>';

                attributefield = '<div class="form-group ' + attribute + '-wrap">' + attributeLabel + ' ' + inputWrap + '</div>';
            }

            return attributefield;
        },

        hiddenAttribute: function (attribute, values) {
            var attrValue = values[attribute] || '',
                attributeField = '<input class="fld-' + attribute + ' form-control" type="hidden" name="'
                    + attribute + '" value="' + attrValue + '">';

            return attributeField;
        },

        requiredField: function (values) {
            var noRequire = ['header', 'paragraph', 'button'],
                noMake = [],
                requireField = '';

            if (this.utils.inArray(values.type, noRequire)) {
                noMake.push(true);
            }
            if (!noMake.some(function (elem) {
                return elem === true;
            })) {
                requireField = this.boolAttribute('required', values, {first: this.options.messages.required});
            }

            return requireField;
        },

        // Select field html, since there may be multiple
        selectFieldOptions: function (name, optionData, multipleSelect) {
            var optionInputType = {
                    selected: multipleSelect ? 'checkbox' : 'radio'
                },
                optionDataOrder = ['value', 'label', 'selected'],
                optionInputs = [];

            optionData = Object.assign({selected: false, label: '', value: ''}, optionData);

            for (var i = optionDataOrder.length - 1; i >= 0; i--) {
                var prop = optionDataOrder[i];
                if (optionData.hasOwnProperty(prop)) {
                    var attrs = {
                        type: optionInputType[prop] || 'text',
                        'class': 'option-' + prop,
                        value: optionData[prop],
                        name: name + '-option'
                    };

                    if (this.options.messages.placeholders[prop]) {
                        attrs.placeholder = this.options.messages.placeholders[prop];
                    }

                    if (prop === 'selected'
                        && (optionData.selected === "1" || optionData.selected === true)
                    ) {
                        attrs.checked = optionData.selected;
                    }

                    optionInputs.push(this.utils.markup('input', null, attrs));
                }
            }

            var removeAttrs = {
                className: 'remove btn',
                title: this.options.messages.removeMessage
            };

            optionInputs.push(this.utils.markup('a', this.options.messages.remove, removeAttrs));
            var field = this.utils.markup('li', optionInputs);

            return field.outerHTML;
        },


        cloneItem: function (currentItem) {
            var self = this;
            var currentId = currentItem.attr('id'),
                type = currentItem.attr('type'),
                ts = new Date().getTime(),
                cloneName = type + '-' + ts;

            var $clone = currentItem.clone();

            $clone.find('[id]').each(function () {
                this.id = this.id.replace(currentId, self.lastID);
            });

            $clone.find('[for]').each(function () {
                this.setAttribute('for', this.getAttribute('for').replace(currentId, self.lastID));
            });

            $clone.each(function () {
                $('e:not(.form-elements)').each(function () {
                    var newName = this.getAttribute('name');
                    newName = newName.substring(0, newName.lastIndexOf('-') + 1);
                    newName = newName + ts.toString();
                    this.setAttribute('name', newName);
                });
            });

            $clone.find('.form-elements').find(':input').each(function () {
                if (this.getAttribute('name') === 'name') {
                    var newVal = this.getAttribute('value');
                    newVal = newVal.substring(0, newVal.lastIndexOf('-') + 1);
                    newVal = newVal + ts.toString();
                    this.setAttribute('value', newVal);
                }
            });

            $clone.attr('id', this.lastID);
            $clone.attr('name', cloneName);
            $clone.addClass('cloned');
            $('.sortable-options', $clone).sortable();

            if (this.options.typeUserEvents[type] && this.options.typeUserEvents[type].onclone) {
                this.options.typeUserEvents[type].onclone($clone[0]);
            }

            this.lastID = this.helpers.incrementId(this.lastID);
            return $clone;
        },

        observeFields: function ($cbUL, formbId) {
            var self = this;
            // Save field on change
            this.sortableFields.on(
                'change blur keyup click', '.form-elements input, .form-elements select, .form-elements textarea, .toggle-form',
                self.saveAndUpdate()
            );

            $('li', $cbUL).click(function (e) {
                self.helpers.stopIndex = undefined;
                self.processControl($(this));
                self.helpers.save();
                e.preventDefault();
            });

            // callback to track disabled tooltips
            this.sortableFields.on('mousemove', 'li.disabled', function (e) {
                $('.frmb-tt', this).css({
                    left: e.offsetX - 16,
                    top: e.offsetY - 34
                });
            });

            // callback to call disabled tooltips
            this.sortableFields.on('mouseenter', 'li.disabled', function () {
                self.helpers.disabledTT.add($(this));
            });

            // callback to call disabled tooltips
            this.sortableFields.on('mouseleave', 'li.disabled', function () {
                self.helpers.disabledTT.remove($(this));
            });

            // ---------------------- UTILITIES ---------------------- //
            // delete options
            this.sortableFields.on('click touchstart', '.remove', function (e) {
                var $field = $(this).parents('.form-field:eq(0)');
                e.preventDefault();
                var optionsCount = $(this).parents('.sortable-options:eq(0)').children('li').length;
                if (optionsCount <= 0) {
                    self.options.notify.error('Error: ' + self.options.messages.minOptionMessage);
                } else {
                    $(this).parent('li').slideUp('250', function () {
                        $(this).remove();
                        self.helpers.updatePreview($field);
                        self.helpers.save();
                    });
                }
            });

            // touch focus
            this.sortableFields.on('touchstart', 'input', function (e) {
                if (e.handled !== true) {
                    if ($(this).attr('type') === 'checkbox') {
                        $(this).trigger('click');
                    } else {
                        $(this).focus();
                        var fieldVal = $(this).val();
                        $(this).val(fieldVal);
                    }
                } else {
                    return false;
                }
            });

            // toggle fields
            this.sortableFields.on('click touchstart', '.toggle-form, .close-field', function (e) {
                e.stopPropagation();
                e.preventDefault();
                if (e.handled !== true) {
                    var targetID = $(this).parents('.form-field:eq(0)').attr('id');
                    self.helpers.toggleEdit(targetID);
                    e.handled = true;
                } else {
                    return false;
                }
            });

            this.sortableFields.on('change', '.prev-holder input, .prev-holder select', function (e) {
                if (e.target.classList.contains('other-option') || $(e.target).attr('type') == 'googlemap') {
                    return;
                }
                var field = $(e.target).closest('li.form-field')[0];
                if (self.utils.inArray(field.type, ['checkbox', 'checkboxtwo'])) {
                    field.querySelector('[class="option-value"][value="' + e.target.value + '"]')
                        .parentElement.childNodes[0].checked = field
                        .querySelector('.prev-holder input[value="' + e.target.value + '"]').checked;
                } else if (self.utils.inArray(field.type, ['listbox'])) {
                    $(field).find('[class="option-selected"]').attr('checked', false);
                    $.each($(field).find('.prev-holder select').val(), function (key, value) {
                        field.querySelector('[class="option-value"][value="' + value + '"]')
                            .parentElement.childNodes[0].checked = true;
                    });
                } else if (self.utils.inArray(field.type, ['dropdown', 'rating', 'radio', 'radiotwo'])) {
                    field.querySelector('[class="option-value"][value="' + e.target.value + '"]')
                    .parentElement.childNodes[0].checked = true;
                } else {
                    document.getElementById('value-' + field.id).value = e.target.value;
                }

                self.helpers.save();
            });

            // update preview to label
            this.sortableFields.on('keyup change', '[name="label"]', function () {
                $('.field-label', $(this).closest('li:not(.hone-field):not(.text-field):not(.hthree-field):not(.htwo-field)')).text($(this).val());
            });

            // remove error styling when users tries to correct mistake
            this.sortableFields.delegate('input.error', 'keyup', function () {
                $(this).removeClass('error');
            });

            // update preview for description
            this.sortableFields.on('keyup', 'input[name="description"]', function () {
                var $field = $(this).parents('.form-field:eq(0)');
                var closestToolTip = $('.tooltip-element', $field);
                var ttVal = $(this).val();
                if (ttVal !== '') {
                    if (!closestToolTip.length) {
                        var tt = '<span class="tooltip-element" tooltip="' + ttVal + '">?</span>';
                        $('.field-label', $field).after(tt);
                    } else {
                        closestToolTip.attr('tooltip', ttVal).css('display', 'inline-block');
                    }
                } else {
                    if (closestToolTip.length) {
                        closestToolTip.css('display', 'none');
                    }
                }
            });

            this.sortableFields.on('change', '.fld-multiple', function (e) {
                var newType = e.target.checked ? 'checkbox' : 'radio';

                $(e.target).parents('.form-elements:eq(0)')
                    .find('.sortable-options input.option-selected').each(function () {
                    this.type = newType;
                });
            });

            // format name attribute
            this.sortableFields.on('blur', 'input.fld-name', function () {
                this.value = self.helpers.safename(this.value);
                if (this.value === '') {
                    $(this).addClass('field-error').attr('placeholder', self.options.messages.cannotBeEmpty);
                } else {
                    $(this).removeClass('field-error');
                }
            });

            this.sortableFields.on('blur', 'input.fld-maxlength', function () {
                this.value = self.helpers.forcenumber(this.value);
            });

            // Copy field
            this.sortableFields.on('click touchstart', '.icon-copy', function (e) {
                e.preventDefault();
                var currentItem = $(this).parents('li');
                var $clone = self.cloneItem(currentItem);
                $clone.insertAfter(currentItem);
                self.helpers.updatePreview($clone, currentItem);
                self.helpers.save();
            });

            // Delete field
            this.sortableFields.on('click touchstart', '.delete-confirm', function (e) {
                e.preventDefault();

                var buttonPosition = this.getBoundingClientRect(),
                    bodyRect = document.body.getBoundingClientRect(),
                    coords = {
                        pageX: buttonPosition.left + buttonPosition.width / 2,
                        pageY: buttonPosition.top - bodyRect.top - 12
                    };

                var deleteID = $(this).parents('.form-field:eq(0)').attr('id'),
                    $field = $(document.getElementById(deleteID));

                document.addEventListener('modalClosed', function () {
                    $field.removeClass('deleting');
                }, false);

                // Check if user is sure they want to remove the field
                if (self.options.fieldRemoveWarn) {
                    var warnH3 = self.utils.markup('h3', self.options.messages.warning),
                        warnMessage = self.utils.markup('p', self.options.messages.fieldRemoveWarning);
                    self.helpers.confirm([warnH3, warnMessage], function () {
                        return self.helpers.removeField(deleteID);
                    }, coords);
                    $field.addClass('deleting');
                } else {
                    self.helpers.removeField(deleteID);
                }
            });

            // Update button style selection
            this.sortableFields.on('click', '.style-wrap button', function () {
                var styleVal = $(this).val(),
                    $parent = $(this).parent(),
                    $btnStyle = $parent.prev('.btn-style');
                $btnStyle.val(styleVal);
                $(this).siblings('.btn').removeClass('active');
                $(this).addClass('active');
                self.saveAndUpdate().call($parent);
            });

            // Attach a callback to toggle required asterisk
            this.sortableFields.on('click', 'input.fld-required', function () {
                var requiredAsterisk = $(this).parents('li.form-field').find('.required-asterisk');
                requiredAsterisk.toggle();
            });

            // Attach a callback to add new options
            this.sortableFields.on('click', '.add-opt', function (e) {
                e.preventDefault();
                var $optionWrap = $(this).parents('.field-options:eq(0)'),
                    $multiple = $('[name="multiple"]', $optionWrap),
                    $firstOption = $('.option-selected:eq(0)', $optionWrap),
                    isMultiple = $multiple.length ? $multiple.prop('checked') : $firstOption.attr('type') === 'checkbox',
                    name = $firstOption.attr('name') || $optionWrap.attr('name'),
                    sortOptions = $('.sortable-options', $optionWrap);

                if (sortOptions.attr('dependent')) {
                    sortOptions.append(self.createDependencyRow(false, false, false, name, false));
                } else {
                    sortOptions.append(self.selectFieldOptions(name, false, isMultiple));
                }
            });

            this.sortableFields.on('mouseover mouseout', '.remove, .del-button', function () {
                $(this).parents('li:eq(0)').toggleClass('delete');
            });

            this.sortableFields.on('change', '[name="dependency-field"]', function (e) {
                var fieldName = this.value,
                    field = self.findField(fieldName),
                    values = [],
                    selects = $(this).parent('li').find('select');

                if (field && field.values) {
                    values = field.values;
                    values = self.createDependencyRow(fieldName, '', values, false, true);
                    $(this).find('[selected="selected"]').removeAttr('selected');
                    $(this).find('[value="' + $(this).val() + '"]').attr('selected', true);
                    if (selects.length > 1) {
                        selects.last().replaceWith(values);
                    } else {
                        selects.last().after(values);
                    }
                } else {
                    $(this).parent('li').find('select').last().remove();
                }
            });

            if (self.options.showActionButtons) {
                // View XML
                var xmlButton = $(document.getElementById(formbId + '-view-data'));
                xmlButton.click(function (e) {
                    e.preventDefault();
                    self.helpers.showData();
                });

                // Clear all fields in form editor
                var clearButton = $(document.getElementById(formbId + '-clear-all'));
                clearButton.click(function () {
                    var fields = $('li.form-field');
                    var buttonPosition = this.getBoundingClientRect(),
                        bodyRect = document.body.getBoundingClientRect(),
                        coords = {
                            pageX: buttonPosition.left + buttonPosition.width / 2,
                            pageY: buttonPosition.top - bodyRect.top - 12
                        };

                    if (fields.length) {
                        self.helpers.confirm(self.options.messages.clearAllMessage, function () {
                            self.helpers.removeAllfields();
                            self.options.notify.success(self.options.messages.allFieldsRemoved);
                            self.helpers.save();
                        }, coords);
                    } else {
                        self.helpers.dialog('There are no fields to clear', {pageX: coords.pageX, pageY: coords.pageY});
                    }
                });
            }
        },

        fieldDependencyArea: function (values, name) {
            var self = this,
                optionActions = [this.utils.markup('a', this.options.messages.addOption, {className: 'add add-opt'})],
                fieldOptions = ['<label class="false-label">' + this.options.messages.dependencyTitle + '</label>'];

            fieldOptions.push('<div class="sortable-options-wrap">');

            fieldOptions.push('<ol class="sortable-options" dependent="true">');

            this.utils.forEach(values, function (i, dependency) {
                fieldOptions.push(self.createDependencyRow(dependency.field, dependency.value, false, false, false));
            });

            fieldOptions.push('</ol>');
            fieldOptions.push(this.utils.markup('div', optionActions, {className: 'option-actions'}).outerHTML);
            fieldOptions.push('</div>');

            return this.utils.markup('div', fieldOptions.join(''), {
                className: 'form-group field-options',
                name: name
            }).outerHTML;
        },

        createDependencyRow: function (selectedField, selectedValue, value, currentField, onlyValueSelect) {
            var optionInputs = [],
                formData = this.getParsedFormData(),
                self = this,
                allowedDependencyTypes = ['no-select', 'dropdown', 'checkbox', 'radio', 'checkboxtwo', 'radiotwo', 'listbox'],
                updateValues = function (index) {
                    var label = index.label
                        ? self.utils.cutJs(index.label)
                        : '';
                    var option = {
                        selected: false,
                        label: label,
                        value: index.name || index.value,
                        type: index.type
                    };

                    if (currentField && option.value == currentField) {
                        return false;
                    }

                    if (selectedField && option.value == selectedField) {
                        option.selected = 'selected';
                    }

                    return option;
                };

            optionInputs.push(
                $(this.selectAttribute(
                    'dependency-field',
                    '',
                    [{
                        name: 'empty',
                        label: 'Choose an Option...',
                        type: 'no-select'
                    }].concat(formData.map(updateValues)),
                    allowedDependencyTypes
                )).find('select')[0]
            );

            if (selectedField) {
                selectedField = self.findField(selectedField);
                if (selectedField && selectedField.values) {
                    value = selectedField.values;
                    selectedField = selectedValue;
                    value = value.map(updateValues);
                }
            }

            if (typeof value != 'undefined' && value) {
                optionInputs.push($(this.selectAttribute('dependency-value', '', value)).find('select')[0]);
            }

            var removeAttrs = {
                className: 'remove btn',
                title: this.options.messages.removeMessage
            };

            optionInputs.push(this.utils.markup('a', this.options.messages.remove, removeAttrs));
            var field = this.utils.markup('li', optionInputs, {className: 'dependent-field'});
            if (onlyValueSelect) {
                field = $(field).find('select').last()[0];
            }

            return field.outerHTML;
        },

        getParsedFormData: function () {
            var formData = null;

            try {
                formData = JSON.parse(this.formData);
            } catch (e) {
                formData = this.formData;
            }

            return formData;
        },

        findField: function (name) {
            var field = null;

            this.getParsedFormData().forEach(function (element) {
                if (element.name == name) {
                    field = element;
                }
            });

            return field;
        }
    });
    return $.mage.customFormBuilder;
});
