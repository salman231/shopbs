define([
    "jquery",
    'jquery/ui'
], function ($) {
    if (!('remove' in Element.prototype)) {
        Element.prototype.remove = function () {
            if (this.parentNode) {
                this.parentNode.removeChild(this);
            }
        };
    }

// Event polyfill
    if (typeof Event !== 'function') {
        (function () {
            window.Event = function (evt) {
                var event = document.createEvent('Event');
                event.initEvent(evt, true, true);
                return event;
            };
        })();
    }

// Object.assign polyfill
    if (typeof Object.assign != 'function') {
        Object.assign = function (target) {
            'use strict';

            if (target == null) {
                throw new TypeError('Cannot convert undefined or null to object');
            }

            target = Object(target);
            for (var index = 1; index < arguments.length; index++) {
                var source = arguments[index];
                if (source != null) {
                    for (var key in source) {
                        if (Object.prototype.hasOwnProperty.call(source, key)) {
                            target[key] = source[key];
                        }
                    }
                }
            }
            return target;
        };
    }
    'use strict';
    /**
     *
     */

    var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) {
        return typeof obj;
    } : function (obj) {
        return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
    };

    var fbUtils = {};

// cleaner syntax for testing indexOf element
    fbUtils.inArray = function (needle, haystack) {
        return haystack.indexOf(needle) !== -1;
    };

    /**
     * Remove null or undefined values
     * @param  {Object} attrs {attrName: attrValue}
     * @return {Object}       Object trimmed of null or undefined values
     */
    fbUtils.trimObj = function (attrs) {
        var xmlRemove = [null, undefined, false, 'false'];
        for (var attr in attrs) {
            if (fbUtils.inArray(attrs[attr], xmlRemove)) {
                delete attrs[attr];
            } else if (Array.isArray(attrs[attr])) {
                if (!attrs[attr].length) {
                    delete attrs[attr];
                }
            }
        }

        return attrs;
    };

    /**
     * Test if attribute is a valid HTML attribute
     * @param  {String} attr
     * @return {Boolean}
     */
    fbUtils.validAttr = function (attr) {
        var invalid = ['values', 'enableOther', 'other', 'label',
            // 'style',
            'subtype'];
        return !fbUtils.inArray(attr, invalid);
    };

    /**
     * Convert an attrs object into a string
     *
     * @param  {Object} attrs object of attributes for markup
     * @return {string}
     */
    fbUtils.attrString = function (attrs) {
        var attributes = [];

        for (var attr in attrs) {
            if (attr == 'map_position' || attr == 'zoom') {
                continue;
            }
            if (attrs.hasOwnProperty(attr) && fbUtils.validAttr(attr)) {
                attr = fbUtils.safeAttr(attr, attrs[attr]);
                attributes.push(attr.name + attr.value);
            }
        }
        return attributes.join(' ');
    };

    /**
     * Convert attributes to markup safe strings
     * @param  {String} name  attribute name
     * @param  {String} value attribute value
     * @return {Object}       {attrName: attrValue}
     */
    fbUtils.safeAttr = function (name, value) {
        name = fbUtils.safeAttrName(name);

        if (value) {
            if (Array.isArray(value)) {
                value = value.join(' ');
            } else if (typeof value === 'boolean') {
                value = value.toString();
            }
        }

        value = value ? '="' + value + '"' : '';
        return {
            name: name,
            value: value
        };
    };

    fbUtils.safeAttrName = function (name) {
        var safeAttr = {
            className: 'class'
        };

        return safeAttr[name] || fbUtils.hyphenCase(name);
    };

    /**
     * Convert strings into lowercase-hyphen
     *
     * @param  {String} str
     * @return {String}
     */
    fbUtils.hyphenCase = function (str) {
        str = str.replace(/[^\w\s\-]/gi, '');
        str = str.replace(/([A-Z])/g, function ($1) {
            return '-' + $1.toLowerCase();
        });

        return str.replace(/\s/g, '-').replace(/^-+/g, '');
    };

    /**
     * convert a hyphenated string to camelCase
     * @param  {String} str
     * @return {String}
     */
    fbUtils.camelCase = function (str) {
        return str.replace(/-([a-z])/g, function (m, w) {
            return w.toUpperCase();
        });
    };

    /**
     * Generate markup wrapper where needed
     *
     * @param  {string}              tag
     * @param  {String|Array|Object} content we wrap this
     * @param  {Object}              attrs
     * @return {String}
     */
    fbUtils.markup = function (tag) {
        var content = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
        var attrs = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};

        var contentType = void 0,
            field = document.createElement(tag),
            getContentType = function getContentType(content) {
                return Array.isArray(content) ? 'array' : typeof content === 'undefined' ? 'undefined' : _typeof(content);
            },
            appendContent = {
                string: function string(content) {
                    field.innerHTML = content;
                },
                object: function object(content) {
                    return field.appendChild(content);
                },
                array: function array(content) {
                    for (var i = 0; i < content.length; i++) {
                        contentType = getContentType(content[i]);
                        appendContent[contentType](content[i]);
                    }
                }
            };

        for (var attr in attrs) {
            if (attrs.hasOwnProperty(attr)) {
                var name = fbUtils.safeAttrName(attr);
                field.setAttribute(name, attrs[attr]);
            }
        }

        contentType = getContentType(content);

        if (content) {
            appendContent[contentType].call(this, content);
        }

        return field;
    };

    /**
     * Convert html element attributes to key/value object
     * @param  {Object} DOM element
     * @return {Object} ex: {attrName: attrValue}
     */
    fbUtils.parseAttrs = function (elem) {
        var attrs = elem.attributes;
        var data = {};
        fbUtils.forEach(attrs, function (attr) {
            var attrVal = attrs[attr].value;
            if (attrVal.match(/false|true/g)) {
                attrVal = attrVal === 'true';
            } else if (attrVal.match(/undefined/g)) {
                attrVal = undefined;
            }

            if (attrVal) {
                data[attrs[attr].name] = attrVal;
            }
        });

        return data;
    };

    /**
     * Convert field options to optionData
     * @param  {Object} DOM element
     * @return {Array}      optionData array
     */
    fbUtils.parseOptions = function (field) {
        var options = field.getElementsByTagName('option'),
            optionData = {},
            data = [];

        if (options.length) {
            for (var i = 0; i < options.length; i++) {
                optionData = fbUtils.parseAttrs(options[i]);
                optionData.label = options[i].textContent;
                data.push(optionData);
            }
        }

        return data;
    };

    /**
     * Parse XML formData
     * @param  {String} xmlString
     * @return {Array}            formData array
     */
    fbUtils.parseXML = function (xmlString) {
        var parser = new window.DOMParser();
        var xml = parser.parseFromString(xmlString, 'text/xml'),
            formData = [];

        if (xml) {
            var fields = xml.getElementsByTagName('field');
            for (var i = 0; i < fields.length; i++) {
                var fieldData = fbUtils.parseAttrs(fields[i]);

                if (fields[i].children.length) {
                    fieldData.values = fbUtils.parseOptions(fields[i]);
                }

                formData.push(fieldData);
            }
        }

        return formData;
    };

    /**
     * Escape markup so it can be displayed rather than rendered
     * @param  {String} html markup
     * @return {String}      escaped html
     */
    fbUtils.escapeHtml = function (html) {
        var escapeElement = document.createElement('textarea');
        escapeElement.textContent = html;
        return escapeElement.innerHTML;
    };

// Escape an attribute
    fbUtils.escapeAttr = function (str) {
        var match = {
            '"': '&quot;',
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;'
        };

        function replaceTag(tag) {
            return match[tag] || tag;
        }

        return typeof str === 'string' ? str.replace(/["&<>]/g, replaceTag) : str;
    };

// Escape attributes
    fbUtils.escapeAttrs = function (attrs) {
        for (var attr in attrs) {
            if (attrs.hasOwnProperty(attr)) {
                attrs[attr] = fbUtils.escapeAttr(attrs[attr]);
            }
        }

        return attrs;
    };

// forEach that can be used on nodeList
    fbUtils.forEach = function (array, callback, scope) {
        for (var i = 0; i < array.length; i++) {
            callback.call(scope, i, array[i]); // passes back stuff we need
        }
    };

    /**
     * Remove duplicates from an array of elements
     * @param  {Array} arrArg array with possible duplicates
     * @return {Array}        array with only unique values
     */
    fbUtils.unique = function (array) {
        return array.filter(function (elem, pos, arr) {
            return arr.indexOf(elem) === pos;
        });
    };

    /**
     * Generate preview markup
     * @param  {object} fieldData
     * @return {string}       preview markup for field
     */
    fbUtils.fieldRender = function (fieldData, opts) {
        var preview = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
        fieldData.placeholder = fieldData.placeholder
            ? fbUtils.cutJs(fieldData.placeholder)
            : '';
        fieldData.label = fieldData.label
            ? fbUtils.cutJs(fieldData.label)
            : '';

        var fieldMarkup = '',
            fieldLabel = '',
            optionsMarkup = '',
            fieldLabelText = fieldData.label || '',
            fieldDesc = fieldData.description || '',
            fieldRequired = '',
            fieldOptions = fieldData.values;

        fieldData.name = preview ? fieldData.name + '-preview' : fieldData.name;
        fieldData.id = fieldData.name;
        fieldData.name = fieldData.multiple ? fieldData.name + '[]' : fieldData.name;

        fieldData.type = fieldData.subtype || fieldData.type;
        if (fieldData.type === 'datetime') {
            fieldData.type = 'datetime-local';
        }

        if (fieldData.required) {
            fieldData.required = null;
            fieldData['aria-required'] = 'true';
            fieldRequired = '<span class="required">*</span>';
        }

        if (fieldData.type !== 'hidden') {
            if (fieldDesc) {
                //fieldDesc = '<span class="tooltip-element" tooltip="' + fieldDesc + '">?</span>';
                fieldDesc = '<span class="tooltip-element" tooltip="' + fieldDesc + '">i</span>';
            }

            fieldLabel = '<label for="' + fieldData.id + '" class="fb-' + fieldData.type + '-label">'
                + fieldLabelText + ' ' + fieldRequired + ' ' + fieldDesc + '</label>';
        }

        var fieldLabelVal = fieldData.label;

        delete fieldData.label;
        delete fieldData.description;

        var fieldDataString = fbUtils.attrString(fieldData);

        /* rendering text fields*/
        var withBr = true;
        switch (fieldData.type) {
            case 'text':
                fieldData.type = 'div';
                break;
            case 'hone':
                fieldData.type = 'h1';
                break;
            case 'htwo':
                fieldData.type = 'h2';
                break;
            case 'hthree':
                fieldData.type = 'h3';
                break;
            case 'checkboxtwo':
                fieldData.type = 'checkbox';
                withBr = false;
                break;
            case 'radiotwo':
                fieldData.type = 'radio';
            case 'rating':
                withBr = false;
                break;
            case 'listbox':
                fieldData.type = 'dropdown';
                fieldData.multiple = true;
                break;
        }

        switch (fieldData.type) {
            case 'textarea':
            case 'rich-text':
                delete fieldData.type;
                var fieldVal = fieldData.value || '';
                fieldMarkup = fieldLabel + '<textarea ' + fieldDataString + '>' + fieldVal + '</textarea>';
                break;
            case 'dropdown':
                var optionAttrsString;
                fieldData.type = fieldData.type.replace('-group', '');

                if (fieldOptions) {

                    if (fieldData.placeholder) {
                        optionsMarkup += '<option disabled selected>' + fieldData.placeholder + '</option>';
                    }

                    for (var i = 0; i < fieldOptions.length; i++) {
                        if (!fieldOptions[i].selected || fieldData.placeholder) {
                            delete fieldOptions[i].selected;
                        }
                        var optionLabel = fieldOptions[i].label
                            ? fbUtils.cutJs(fieldOptions[i].label)
                            : '';
                        optionAttrsString = fbUtils.attrString(fieldOptions[i]);
                        optionsMarkup += '<option ' + optionAttrsString + '>' + optionLabel + '</option>';
                    }
                }

                fieldMarkup = fieldLabel + '<select ' + fieldDataString + '>' + optionsMarkup + '</select>';
                break;
            case 'checkbox':
            case 'radio':
            case 'rating':
                var optionAttrs = void 0,
                    ratingClass = '';
                fieldData.type = fieldData.type.replace('-group', '');
                if (fieldData.type === 'rating') {
                    fieldData.type = 'radio';
                    ratingClass = 'amform-rating-container ';
                    fieldOptions = fieldOptions.reverse();
                }

                if (fieldData.type === 'checkbox' || fieldData.type === 'checkboxtwo') {
                    fieldData.name = fieldData.name + '[]';
                }

                if (fieldOptions) {
                    var _optionAttrsString = void 0;

                    for (var _i = 0; _i < fieldOptions.length; _i++) {
                        optionAttrs = Object.assign({value: '', label: ''}, fieldData, fieldOptions[_i]);

                        if (optionAttrs.value) {
                            if (optionAttrs.selected) {
                                delete optionAttrs.selected;
                                optionAttrs.checked = null;
                            }

                            optionAttrs.id = fieldData.id + '-' + _i;
                            _optionAttrsString = fbUtils.attrString(optionAttrs);

                            var br = '';
                            var classNameLabel = ' class="amform-versiontwo-label"';
                            if (withBr) {
                                br = '<br>';
                                className = '';
                            }

                            var labelText = (ratingClass) ? '' : optionAttrs.label;

                            optionsMarkup += '<input ' + _optionAttrsString + ' /> <label for="'
                                + optionAttrs.id + '" ' + classNameLabel + '>' + fbUtils.cutJs(labelText) + '</label>' + br;
                        }

                    }

                    if (fieldData.other) {
                        var otherOptionAttrs = {
                            id: fieldData.id + '-' + 'other',
                            className: fieldData.className + ' other-option',
                            onclick: 'fbUtils.otherOptionCB(\'' + fieldData.id + '-other\')'
                        };

                        _optionAttrsString = fbUtils.attrString(Object.assign({}, fieldData, otherOptionAttrs));

                        optionsMarkup += '<input ' + _optionAttrsString + ' /> <label for="' + otherOptionAttrs.id + '">'
                            + opts.messages.other + '</label> <input type="text" name="'
                            + fieldData.name + '" id="' + otherOptionAttrs.id + '-value" class="amform-no-display" />';
                    }
                }
                fieldMarkup = fieldLabel + '<div class="' + ratingClass + fieldData.type + '-group">' + optionsMarkup + '</div>';
                break;
            case 'textinput':
            case 'password':
            case 'email':
            case 'number':
            case 'file':
            case 'hidden':
            case 'date':
            case 'time':
            case 'datetime-local':
            case 'daterange':
            case 'tel':
            case 'autocomplete':
                fieldMarkup = fieldLabel + ' <input ' + fieldDataString + '>';
                break;
            case 'color':
                fieldMarkup = fieldLabel + ' <input ' + fieldDataString + '> ' + opts.messages.selectColor;
                break;
            case 'button':
            case 'submit':
                fieldMarkup = '<button ' + fieldDataString + '>' + fieldLabelVal + '</button>';
                break;
            case 'googlemap':
                fieldMarkup = fieldLabel + ' <input ' + fieldDataString + '><div class="map"></div>';
                break;
            default:
                fieldMarkup = '<' + fieldData.type + ' ' + fieldDataString + '>' + fieldLabelVal + '</' + fieldData.type + '>';
        }

        if (fieldData.type !== 'hidden') {
            var className = fieldData.id ? 'fb-' + fieldData.type + ' form-group field-' + fieldData.id : '';

            className += ' amform-layout-' + fieldData.layout;
            fieldMarkup = fbUtils.markup('div', fieldMarkup, {
                className: className
            });
        } else {
            fieldMarkup = fbUtils.markup('input', null, fieldData);
        }

        if (fieldData.type == 'googlemap') {
            var position = null,
                zoom = 1,
                oldMapSelector = '.' + fieldMarkup.className.replace(/\s+/g, '.'),
                oldMap = $(oldMapSelector);
            if (oldMap.length > 0
                && $.data(oldMap[0], 'mage-amGoogleMap')
            ) {
                position = oldMap.amGoogleMap('getPosition');
                zoom = oldMap.amGoogleMap('getZoom');
            } else if (fieldData.map_position) {
                position = {
                    'lat': parseFloat(fieldData.map_position.lat),
                    'lng': parseFloat(fieldData.map_position.lng)
                };
                zoom = fieldData.zoom;
            }
            $(fieldMarkup).amGoogleMap({
                'position': position,
                'zoom': zoom
            });
        }

        return fieldMarkup;
    };

    /**
     * Callback for other option.
     * Toggles the hidden text area for "other" option.
     * @param  {String} otherId id of the "other" option input
     */
    fbUtils.otherOptionCB = function (otherId) {
        var otherInput = document.getElementById(otherId),
            otherInputValue = document.getElementById(otherId + '-value');

        if (otherInput.checked) {
            otherInput.style.display = 'none';
            otherInputValue.style.display = 'inline-block';
        } else {
            otherInput.style.display = 'inline-block';
            otherInputValue.style.display = 'none';
        }
    };

    /**
     * Capitalizes a string
     * @param  {String} str uncapitalized string
     * @return {String} str capitalized string
     */
    fbUtils.capitalize = function (str) {
        return str.replace(/\b\w/g, function (m) {
            return m.toUpperCase();
        });
    };

    fbUtils.cutJs = function (str) {
        return str.replace(/<\/?script.*?>/ig, '');
    };
    'use strict';

    function formBuilderHelpersFn(opts, formBuilder) {
        'use strict';

        var _helpers = {
            doCancel: false
        };
        var utils = fbUtils;

        formBuilder.events = formBuilderEventsFn();

        /**
         * Convert converts messy `cl#ssNames` into valid `class-names`
         *
         * @param  {string} str
         * @return {string}
         */
        _helpers.makeClassName = function (str) {
            str = str.replace(/[^\w\s\-]/gi, '');
            return utils.hyphenCase(str);
        };

        /**
         * Add a mobile class
         *
         * @return {string}
         */
        _helpers.mobileClass = function () {
            var mobileClass = '';
            (function (a) {
                if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4))) {
                    mobileClass = ' fb-mobile';
                }
            })(navigator.userAgent || navigator.vendor || window.opera);
            return mobileClass;
        };

        /**
         * Callback for when a drag begins
         *
         * @param  {Object} event
         * @param  {Object} ui
         */
        _helpers.startMoving = function (event, ui) {
            ui.item.show().addClass('moving');
            _helpers.startIndex = $('li', this).index(ui.item);
        };

        /**
         * Callback for when a drag ends
         *
         * @param  {Object} event
         * @param  {Object} ui
         */
        _helpers.stopMoving = function (event, ui) {
            ui.item.removeClass('moving');
            if (_helpers.doCancel) {
                $(ui.sender).sortable('cancel');
                $(this).sortable('cancel');
            }
            _helpers.save();
            _helpers.doCancel = false;
        };

        /**
         * jQuery UI sortable beforeStop callback used for both lists.
         * Logic for canceling the sort or drop.
         */
        _helpers.beforeStop = function (event, ui) {
            var form = document.getElementById(opts.formID),
                lastIndex = form.children.length - 1,
                cancelArray = [];
            _helpers.stopIndex = ui.placeholder.index() - 1;

            if (!opts.sortableControls && ui.item.parent().hasClass('frmb-control')) {
                cancelArray.push(true);
            }

            if (opts.prepend) {
                cancelArray.push(_helpers.stopIndex === 0);
            }

            if (opts.append) {
                cancelArray.push(_helpers.stopIndex + 1 === lastIndex);
            }

            _helpers.doCancel = cancelArray.some(function (elem) {
                return elem === true;
            });
        };

        /**
         * Make strings safe to be used as classes
         *
         * @param  {string} str string to be converted
         * @return {string}     converter string
         */
        _helpers.safename = function (str) {
            return str.replace(/\s/g, '-').replace(/[^a-zA-Z0-9\-]/g, '').toLowerCase();
        };

        /**
         * Strips non-numbers from a number only input
         *
         * @param  {string} str string with possible number
         * @return {string}     string without numbers
         */
        _helpers.forceNumber = function (str) {
            return str.replace(/[^0-9]/g, '');
        };

        /**
         * hide and show mouse tracking tooltips, only used for disabled
         * fields in the editor.
         *
         * @todo   remove or refactor to make better use
         * @param  {Object} tt jQuery option with nexted tooltip
         * @return {void}
         */
        _helpers.initTooltip = function (tt) {
            var tooltip = tt.find('.tooltip');
            tt.mouseenter(function () {
                if (tooltip.outerWidth() > 200) {
                    tooltip.addClass('max-width');
                }
                tooltip.css('left', tt.width() + 14);
                tooltip.stop(true, true).fadeIn('fast');
            }).mouseleave(function () {
                tt.find('.tooltip').stop(true, true).fadeOut('fast');
            });
            tooltip.hide();
        };

        /**
         * Attempts to get element type and subtype
         *
         * @param  {Object} $field
         * @return {Object}
         */
        _helpers.getTypes = function ($field) {
            var types = {
                    type: $field.attr('type')
                },
                subtype = $('.fld-subtype', $field).val();

            if (subtype !== types.type) {
                types.subtype = subtype;
            }

            return types;
        };

        /**
         * Get option data for a field
         * @param  {Object} field jQuery field object
         * @return {Array}        Array of option values
         */
        _helpers.fieldOptionData = function (field) {
            var options = [];

            $('.sortable-options li', field).each(function () {
                var $option = $(this),
                    selected = $('.option-selected', $option).is(':checked'),
                    attrs = {
                        label: $('.option-label', $option).val(),
                        value: $('.option-value', $option).val()
                    };

                if (selected) {
                    attrs.selected = selected;
                }
                if (attrs.value) {
                    options.push(attrs);
                }
            });

            return options;
        };

        /**
         * XML save
         *
         * @param  {Object} form sortableFields node
         */
        _helpers.xmlSave = function (form) {

            var formData = _helpers.prepData(form),
                xml = ['<form-template>\n\t<fields>'];

            utils.forEach(formData, function (fieldIndex, field) {
                var fieldContent = null;

                // Handle options
                if (field.type.match(/(select|checkbox-group|radio-group)/)) {
                    var optionData = field.values,
                        options = [];

                    for (var i = 0; i < optionData.length; i++) {
                        var option = utils.markup('option', optionData[i].label, optionData[i]).outerHTML;
                        options.push('\n\t\t\t' + option);
                    }
                    options.push('\n\t\t');

                    fieldContent = options.join('');
                    delete field.values;
                }

                var xmlField = utils.markup('field', fieldContent, field);
                xml.push('\n\t\t' + xmlField.outerHTML);
            });

            xml.push('\n\t</fields>\n</form-template>');

            return xml.join('');
        };

        _helpers.prepData = function (form) {
            var formData = [];

            if (form.childNodes.length !== 0) {
                // build data object
                utils.forEach(form.childNodes, function (index, field) {
                    var $field = $(field);

                    if (!$field.hasClass('disabled')) {
                        var match;
                        var multipleField;

                        (function () {
                            var fieldData = _helpers.getTypes($field),
                                roleVals = $('.roles-field:checked', field).map(function () {
                                    return this.value;
                                }).get();

                            $('[class*="fld-"]', field).each(function () {
                                var name = utils.camelCase(this.name);
                                fieldData[name] = this.type === 'checkbox' ? this.checked : this.value;
                            });

                            if (roleVals.length) {
                                fieldData.role = roleVals.join(',');
                            }

                            fieldData.className = fieldData.className || fieldData.class; // backwards compatibility
                            if (utils.inArray(
                                    fieldData.type,
                                    ['textinput', 'number', 'file', 'dropdown', 'listbox', 'textarea']
                                ) && fieldData.className != 'form-control'
                            ) {
                                fieldData.className += ' form-control';
                            }

                            match = /(?:^|\s)btn-(.*?)(?:\s|$)/g.exec(fieldData.className);

                            if (match) {
                                fieldData.style = match[1];
                            }

                            fieldData = utils.trimObj(fieldData);

                            multipleField = fieldData.type
                                .match(/(select|dropdown|listbox|radio|radiotwo|checkbox|checkboxtwo|radio-group|rating)/);


                            if (multipleField) {
                                fieldData.values = _helpers.fieldOptionData($field);
                            }

                            if (fieldData.type == 'googlemap') {
                                var fbGoogleMap = $(field).find('.fb-googlemap');
                                fieldData.map_position = fbGoogleMap.amGoogleMap('getPosition');
                                fieldData.zoom = fbGoogleMap.amGoogleMap('getZoom');
                            }
                            if (fieldData.type == 'date') {
                                fieldData.value = $field.find('.amform-date._has-datepicker').val();
                                fieldData.format = $field.attr('date-format');
                            }

                            fieldData.dependency = _helpers.fieldDependencyData($field);

                            formData.push(fieldData);
                        })();
                    }
                });
            }

            return formData;
        };

        _helpers.jsonSave = function (form) {
            return window.JSON.stringify(_helpers.prepData(form), null, '\t');
        };

        _helpers.getData = function (formData) {

            var data = formData || opts.formData;

            if (!data) {
                return false;
            }

            var setData = {
                xml: function xml(formData) {
                    return utils.parseXML(formData);
                },
                json: function json(formData) {
                    return window.JSON.parse(formData);
                }
            };

            formBuilder.formData = setData[opts.dataType](data) || [];

            return formBuilder.formData;
        };

        /**
         * Saves and returns formData
         * @return {XML|JSON}
         */
        _helpers.save = function () {
            var form = document.getElementById(opts.formID);

            var doSave = {
                xml: _helpers.xmlSave,
                json: _helpers.jsonSave
            };

            // save action for current `dataType`
            formBuilder.formData = doSave[opts.dataType](form);

            //trigger formSaved event
            document.dispatchEvent(formBuilder.events.formSaved);
            return formBuilder.formData;
        };

        /**
         * increments the field ids with support for multiple editors
         * @param  {String} id field ID
         * @return {String}    incremented field ID
         */
        _helpers.incrementId = function (id) {
            var split = id.lastIndexOf('-'),
                newFieldNumber = parseInt(id.substring(split + 1)) + 1,
                baseString = id.substring(0, split);

            return baseString + '-' + newFieldNumber;
        };

        /**
         * Collect field attribute values and call fieldPreview to generate preview
         * @param  {Object} field jQuery wrapped dom object @todo, remove jQuery dependency
         * @param {Object} orig jQuery saved original when creating clone
         */
        _helpers.updatePreview = function (field, orig) {
            var fieldClass = field.attr('class');
            if (fieldClass && fieldClass.indexOf('ui-sortable-handle') !== -1) {
                return;
            }

            var fieldType = $(field).attr('type'),
                $prevHolder = $('.prev-holder', field),
                previewData = {
                    type: fieldType
                },
                preview;

            $('[class*="fld-"]', field).each(function () {
                var name = utils.camelCase(this.name);
                previewData[name] = this.type === 'checkbox' ? this.checked : this.value;
            });

            var style = $('.btn-style', field).val();
            if (style) {
                previewData.style = style;
            }

            if (fieldType.match(/(select|dropdown|listbox|radio|radiotwo|checkbox|checkboxtwo|radio-group|rating)/)) {
                previewData.values = [];
                previewData.multiple = (fieldType == 'listbox');

                $('.sortable-options li', field).each(function () {
                    var option = {};
                    option.selected = $('.option-selected', this).is(':checked');
                    option.value = $('.option-value', this).val();
                    option.label = $('.option-label', this).val();
                    previewData.values.push(option);
                });
            }
            if (fieldType == 'date') {
                var value = field.data('fieldData').attrs ?
                    field.data('fieldData').attrs.value :
                    field.data('fieldData').value;
                previewData.value = value;
            }

            previewData = utils.trimObj(previewData);

            previewData.className = _helpers.classNames(field, previewData);
            $('.fld-className', field).val(previewData.className);

            if (fieldType == 'googlemap') {
                var fieldData = field.data('fieldData');
                if (fieldData) {
                    // first load
                    if (fieldData.attrs) {
                        previewData.map_position = fieldData.attrs.map_position;
                        previewData.zoom = fieldData.attrs.zoom;
                    }
                } else if (orig) {
                    // for clone action
                    var oldMap = orig.find('.fb-googlemap');
                    if (oldMap.length > 0
                        && $.data(oldMap[0], 'mage-amGoogleMap')
                    ) {
                        var latLng = oldMap.amGoogleMap('getPosition');
                        previewData.map_position = {
                            lat: latLng.lat(),
                            lng: latLng.lng()
                        };
                        previewData.zoom = oldMap.amGoogleMap('getZoom');
                    }
                }
            }

            field.data('fieldData', previewData);
            preview = utils.fieldRender(previewData, opts, true);

            if (fieldType == 'date') {
                this.updateDateField(preview, field.attr('date-format'), previewData.value);
            }

            $prevHolder.html(preview);
            if ($prevHolder.find('.amform-layout-two').length) {
                $prevHolder.find('.amform-layout-two').removeClass('amform-layout-two');
                $prevHolder.parents('li.form-field').addClass('amform-layout-two');
            }
            
            if ($prevHolder.find('.amform-layout-three').length) {
                $prevHolder.find('.amform-layout-three').removeClass('amform-layout-three');
                $prevHolder.parents('li.form-field').addClass('amform-layout-three');
            }

            $('input[toggle]', $prevHolder).kcToggle();
        };

        _helpers.updateDateField = function (fieldMarkup, format, value) {
            var dateInput = $(fieldMarkup).find('input');
            dateInput.removeAttr('type');
            dateInput.attr('readonly', 'readonly');
            dateInput.datepicker({
                showOn: 'both',
                changeYear: true,
                yearRange: '1900:2100',
                autoSize: true,
                dateFormat: format
            });
            if (value) {
                dateInput.datepicker('setDate', value);
            }
            dateInput.attr('placeholder', format);
            dateInput.attr('size', 15);
            $(fieldMarkup).find('button').html('');
            var datepicker = $('#ui-datepicker-div');
            if (!datepicker.hasClass('am-picker-year')) {
                datepicker.addClass('am-picker-year');
            }
        };

        _helpers.debounce = function (func) {
            var wait = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 250;
            var immediate = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;

            var timeout;
            return function () {
                var context = this,
                    args = arguments;
                var later = function later() {
                    timeout = null;
                    if (!immediate) {
                        func.apply(context, args);
                    }
                };
                var callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) {
                    func.apply(context, args);
                }
            };
        };

        /**
         * Display a custom tooltip for disabled fields.
         *
         * @param  {Object} field
         */
        _helpers.disabledTT = {
            className: 'frmb-tt',
            add: function add(field) {
                var title = opts.messages.fieldNonEditable;

                if (title) {
                    var tt = utils.markup('p', title, {className: _helpers.disabledTT.className});
                    field.append(tt);
                }
            },
            remove: function remove(field) {
                $('.frmb-tt', field).remove();
            }
        };

        _helpers.classNames = function (field, previewData) {
            var i = void 0,
                type = previewData.type,
                style = previewData.style;
            var className = field[0].querySelector('.fld-className').value;
            var classes = className.split(' ');
            var types = {
                button: 'btn',
                submit: 'btn'
            };

            var primaryType = types[type];

            if (primaryType) {
                if (style) {
                    for (i = 0; i < classes.length; i++) {
                        var re = new RegExp('(?:^|\s)' + primaryType + '-(.*?)(?:\s|$)+', 'g');
                        var match = classes[i].match(re);
                        if (match) {
                            classes.splice(i, 1);
                        }
                    }
                    classes.push(primaryType + '-' + style);
                }
                classes.push(primaryType);
            }

            // reverse the array to put custom classes at end, remove any duplicates, convert to string, remove whitespace
            return utils.unique(classes).join(' ').trim();
        };

        /**
         * Closes and open dialog
         *
         * @param  {Object} overlay Existing overlay if there is one
         * @param  {Object} dialog  Existing dialog
         * @return {Event}          Triggers modalClosed event
         */
        _helpers.closeConfirm = function (overlay, dialog) {
            overlay = overlay || document.getElementsByClassName('form-builder-overlay')[0];
            dialog = dialog || document.getElementsByClassName('form-builder-dialog')[0];
            overlay.classList.remove('visible');
            dialog.remove();
            overlay.remove();
            document.dispatchEvent(formBuilder.events.modalClosed);
        };

        /**
         * Returns the layout data based on controlPosition option
         * @param  {String} controlPosition 'left' or 'right'
         * @return {Object}
         */
        _helpers.editorLayout = function (controlPosition) {
            var layoutMap = {
                left: {
                    stage: 'pull-right',
                    controls: 'pull-left'
                },
                right: {
                    stage: 'pull-left',
                    controls: 'pull-right'
                }
            };

            return layoutMap[controlPosition] ? layoutMap[controlPosition] : '';
        };

        /**
         * Adds overlay to the page. Used for modals.
         * @return {Object}
         */
        _helpers.showOverlay = function () {
            var overlay = utils.markup('div', null, {
                className: 'form-builder-overlay'
            });
            document.body.appendChild(overlay);
            overlay.classList.add('visible');

            overlay.onclick = function () {
                _helpers.closeConfirm(overlay);
            };

            return overlay;
        };

        /**
         * Custom confirmation dialog
         *
         * @param  {Object}  message   Content to be displayed in the dialog
         * @param  {Func}  yesAction callback to fire if they confirm
         * @param  {Boolean} coords    location to put the dialog
         * @param  {String}  className Custom class to be added to the dialog
         * @return {Object}            Reference to the modal
         */
        _helpers.confirm = function (message, yesAction) {
            var coords = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
            var className = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : '';

            var overlay = _helpers.showOverlay();
            var yes = utils.markup('button', opts.messages.yes, {className: 'yes btn btn-success btn-sm'}),
                no = utils.markup('button', opts.messages.no, {className: 'no btn btn-danger btn-sm'});

            no.onclick = function () {
                _helpers.closeConfirm(overlay);
            };

            yes.onclick = function () {
                yesAction();
                _helpers.closeConfirm(overlay);
            };

            var btnWrap = utils.markup('div', [no, yes], {className: 'button-wrap'});

            className = 'form-builder-dialog ' + className;

            var miniModal = utils.markup('div', [message, btnWrap], {className: className});
            if (!coords) {
                coords = {
                    pageX: Math.max(document.documentElement.clientWidth, window.innerWidth || 0) / 2,
                    pageY: Math.max(document.documentElement.clientHeight, window.innerHeight || 0) / 2
                };
                miniModal.style.position = 'fixed';
            } else {
                miniModal.classList.add('positioned');
            }

            miniModal.style.left = coords.pageX + 'px';
            miniModal.style.top = coords.pageY + 'px';

            document.body.appendChild(miniModal);

            yes.focus();
            return miniModal;
        };

        /**
         * Popup dialog the does not require confirmation.
         * @param  {String|DOM|Array}  content
         * @param  {Boolean} coords    false if no coords are provided. Without coordinates
         *                             the popup will appear center screen.
         * @param  {String}  className classname to be added to the dialog
         * @return {Object}            dom
         */
        _helpers.dialog = function (content) {
            var coords = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
            var className = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : '';

            _helpers.showOverlay();

            className = 'form-builder-dialog ' + className;

            var miniModal = utils.markup('div', content, {className: className});
            if (!coords) {
                coords = {
                    pageX: Math.max(document.documentElement.clientWidth, window.innerWidth || 0) / 2,
                    pageY: Math.max(document.documentElement.clientHeight, window.innerHeight || 0) / 2
                };
                miniModal.style.position = 'fixed';
            } else {
                miniModal.classList.add('positioned');
            }

            miniModal.style.left = coords.pageX + 'px';
            miniModal.style.top = coords.pageY + 'px';

            document.body.appendChild(miniModal);

            document.dispatchEvent(formBuilder.events.modalOpened);

            if (className.indexOf('data-dialog') !== -1) {
                document.dispatchEvent(formBuilder.events.viewData);
            }

            return miniModal;
        };

        /**
         * Removes all fields from the form
         */
        _helpers.removeAllfields = function () {
            var form = document.getElementById(opts.formID);
            var fields = form.querySelectorAll('li.form-field');
            var $fields = $(fields);
            var markEmptyArray = [];

            if (!fields.length) {
                return false;
            }

            if (opts.prepend) {
                markEmptyArray.push(true);
            }

            if (opts.append) {
                markEmptyArray.push(true);
            }

            if (!markEmptyArray.some(function (elem) {
                    return elem === true;
                })) {
                form.parentElement.classList.add('empty');
                form.parentElement.dataset.content = opts.messages.getStarted;
            }

            form.classList.add('removing');

            var outerHeight = 0;
            $fields.each(function () {
                outerHeight += $(this).outerHeight() + 3;
            });

            fields[0].style.marginTop = -outerHeight + 'px';

            setTimeout(function () {
                $fields.remove();
                document.getElementById(opts.formID).classList.remove('removing');
                _helpers.save();
            }, 400);
        };

        /**
         * If user re-orders the elements their order should be saved.
         *
         * @param {Object} $cbUL our list of elements
         */
        _helpers.setFieldOrder = function ($cbUL) {
            if (!opts.sortableControls) {
                return false;
            }
            var fieldOrder = {};
            $cbUL.children().each(function (index, element) {
                fieldOrder[index] = $(element).data('attrs').type;
            });
            if (window.sessionStorage) {
                window.sessionStorage.setItem('fieldOrder', window.JSON.stringify(fieldOrder));
            }
        };

        /**
         * Reorder the controls if the user has previously ordered them.
         *
         * @param  {Array} frmbFields
         * @return {Array}
         */
        _helpers.orderFields = function (frmbFields) {
            var fieldOrder = false;

            if (window.sessionStorage) {
                if (opts.sortableControls) {
                    fieldOrder = window.sessionStorage.getItem('fieldOrder');
                } else {
                    window.sessionStorage.removeItem('fieldOrder');
                }
            }

            if (!fieldOrder) {
                var controlOrder = opts.controlOrder.concat(frmbFields.map(function (field) {
                    return field.attrs.type;
                }));
                fieldOrder = utils.unique(controlOrder);
            } else {
                fieldOrder = window.JSON.parse(fieldOrder);
                fieldOrder = Object.keys(fieldOrder).map(function (i) {
                    return fieldOrder[i];
                });
            }

            var newOrderFields = [];

            fieldOrder.forEach(function (fieldType) {
                var field = frmbFields.filter(function (field) {
                    return field.attrs.type === fieldType;
                })[0];
                newOrderFields.push(field);
            });

            return newOrderFields.filter(Boolean);
        };

        /**
         * Close fields being editing
         * @param  {Object} stage
         */
        _helpers.closeAllEdit = function (stage) {
            var fields = $('> li.editing', stage),
                toggleBtns = $('.toggle-form', stage),
                editModes = $('.frm-holder', fields);

            toggleBtns.removeClass('open');
            fields.removeClass('editing');
            editModes.hide();
            $('.prev-holder', fields).show();
        };

        /**
         * Toggles the edit mode for the given field
         * @param  {String} fieldId
         */
        _helpers.toggleEdit = function (fieldId) {
            var field = document.getElementById(fieldId),
                toggleBtn = $('.toggle-form', field),
                editMode = $('.frm-holder', field);
            field.classList.toggle('editing');
            toggleBtn.toggleClass('open');
            $('.prev-holder', field).slideToggle(250);
            editMode.slideToggle(250);
        };

        /**
         * Controls follow scroll to the bottom of the editor
         * @param  {Object} $sortableFields
         * @param  {Object} cbUL
         */
        _helpers.stickyControls = function ($sortableFields, cbUL) {

            var $cbWrap = $(cbUL).parent(),
                $stageWrap = $sortableFields.parent(),
                cbWidth = $cbWrap.width(),
                cbPosition = cbUL.getBoundingClientRect();

            $(window).scroll(function () {

                var scrollTop = $(this).scrollTop();

                if (scrollTop > $stageWrap.offset().top) {

                    var cbStyle = {
                        position: 'fixed',
                        width: cbWidth,
                        top: 0,
                        bottom: 'auto',
                        right: 'auto',
                        left: cbPosition.left
                    };

                    var cbOffset = $cbWrap.offset(),
                        stageOffset = $stageWrap.offset(),
                        cbBottom = cbOffset.top + $cbWrap.height(),
                        stageBottom = stageOffset.top + $stageWrap.height();

                    if (cbBottom > stageBottom && cbOffset.top !== stageOffset.top) {
                        $cbWrap.css({
                            position: 'absolute',
                            top: 'auto',
                            bottom: 0,
                            right: 0,
                            left: 'auto'
                        });
                    }

                    if (cbBottom < stageBottom || cbBottom === stageBottom && cbOffset.top > scrollTop) {
                        $cbWrap.css(cbStyle);
                    }
                } else {
                    cbUL.parentElement.removeAttribute('style');
                }
            });
        };

        /**
         * Open a dialog with the form's data
         */
        _helpers.showData = function () {
            var data = utils.escapeHtml(formBuilder.formData),
                code = utils.markup('code', data, {className: 'formData-' + opts.dataType}),
                pre = utils.markup('pre', code);

            _helpers.dialog(pre, null, 'data-dialog');
        };

        /**
         * Remove a field from the stage
         * @param  {String}  fieldID ID of the field to be removed
         * @return {Boolean} fieldRemoved returns true if field is removed
         */
        _helpers.removeField = function (fieldID) {
            var fieldRemoved = false,
                form = document.getElementById(opts.formID),
                fields = form.getElementsByClassName('form-field');

            if (!fields.length) {
                console.warn('No fields to remove');
                return false;
            }

            if (!fieldID) {
                var availableIds = [].slice.call(fields).map(function (field) {
                    return field.id;
                });
                console.warn('fieldID required to use `removeField` action.');
                console.warn('Available IDs: ' + availableIds.join(', '));
            }

            var field = document.getElementById(fieldID),
                $field = $(field);
            if (!field) {
                console.warn('Field not found');
                return false;
            }

            $field.slideUp(250, function () {
                $field.removeClass('deleting');
                $field.remove();
                fieldRemoved = true;
                _helpers.save();
                if (!form.childNodes.length) {
                    var stageWrap = form.parentElement;
                    stageWrap.classList.add('empty');
                    stageWrap.dataset.content = opts.messages.getStarted;
                }
            });

            document.dispatchEvent(formBuilder.events.fieldRemoved);
            return fieldRemoved;
        };

        _helpers.fieldDependencyData = function (field) {
            var options = [];

            $('.sortable-options li.dependent-field', field).each(function () {
                var $option = $(this),
                    selected = $('.option-selected', $option).is(':checked'),
                    attrs = {
                        field: $option.find('select').first().val(),
                        type: $option.find('select').first().find('[selected="selected"]').attr('type'),
                        value: $option.find('select').last().val()
                    };

                if (selected) {
                    attrs.selected = selected;
                }
                if (attrs.value) {
                    options.push(attrs);
                }
            });

            return options;
        };

        return _helpers;
    }

    'use strict';

    function formBuilderEventsFn() {
        'use strict';

        var events = {};

        events.loaded = new Event('loaded');
        events.viewData = new Event('viewData');
        events.userDeclined = new Event('userDeclined');
        events.modalClosed = new Event('modalClosed');
        events.modalOpened = new Event('modalOpened');
        events.formSaved = new Event('formSaved');
        events.fieldAdded = new Event('fieldAdded');
        events.fieldRemoved = new Event('fieldRemoved');

        return events;
    }

    'use strict';

    (function ($) {
        'use strict';

        var Toggle = function Toggle(element, options) {

            var defaults = {
                theme: 'fresh',
                messages: {
                    off: 'Off',
                    on: 'On'
                }
            };

            var opts = $.extend(defaults, options),
                $kcToggle = $('<div class="kc-toggle"/>').insertAfter(element).append(element);

            $kcToggle.toggleClass('on', element.is(':checked'));

            var kctOn = '<div class="kct-on">' + opts.messages.on + '</div>',
                kctOff = '<div class="kct-off">' + opts.messages.off + '</div>',
                kctHandle = '<div class="kct-handle"></div>',
                kctInner = '<div class="kct-inner">' + kctOn + kctHandle + kctOff + '</div>';

            $kcToggle.append(kctInner);

            $kcToggle.click(function () {
                element.attr('checked', !element.attr('checked'));
                $(this).toggleClass('on');
            });
        };

        $.fn.kcToggle = function (options) {
            var toggle = this;
            return toggle.each(function () {
                var element = $(this);
                if (element.data('kcToggle')) {
                    return;
                }
                var kcToggle = new Toggle(element, options);
                element.data('kcToggle', kcToggle);
            });
        };
    })(jQuery);


    return [fbUtils, formBuilderHelpersFn];
});

