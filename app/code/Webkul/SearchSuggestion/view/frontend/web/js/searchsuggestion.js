/**
 * Webkul searchsuggestion js.
 * @category Webkul
 * @package Webkul_SearchSuggestion
 * @author Webkul
 * @copyright Copyright (c)   Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
define([
    "jquery",
    "jquery/ui",
    ], function ($) {
        'use strict';
        $.widget('searchsuggestion.searchsuggestion', {
            options: {},
            _create: function () {
                var self = this;
                $(document).ready(function () {
                    var baseurl = self.options.url;
                    var ajax;
                    var qprev='';
                    var liid;
                    var val;
                    var typingTimer;
                    var doneTypingInterval = 2;
                    var finaldoneTypingInterval = 300;
                    var down;
                    var url;
                    var rate;
                    var qInput;
                    $('#search').keydown(function () {
                        clearTimeout(typingTimer);
                        if ($('#search').val) {
                            typingTimer = setTimeout(function () {
                            }, doneTypingInterval);
                        }
                    });
                    $(document).on('keyup change','#search',(function (e) {
                        qInput = $(this).val();
                        if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
                            $('#wk_ss_loader').css('display','block');
                            $('body').trigger('processStart');
                            if (qInput=='') {
                                $('#wk_ss_loader').css('display','none');
                                $('body').trigger('processStop');
                            }
                            var clonePosition = {
                                position: 'absolute',
                                width: $('#search').outerWidth()
                            };
                            $.ajax({
                                url: baseurl+"search/ajax/suggest",
                                data:"q="+qInput,
                                type: "get",
                                success: function (response) {
                                    var i=1;
                                    down=0;
                                    $('#wk_ss_loader').css('display','none');
                                    $('body').trigger('processStop');
                                    $('.wk_ss_list').css('display','block');
                                    $('#search_autocomplete').show();
                                    $('#search_autocomplete').css(clonePosition);
                                    $('.wk_ss_showlist li').remove();
                                    $.each(response,function (index,element) {
                                        $.each(element,function (index,element) {
                                            if (element.cat && element.cat_name && element.cat_url) {
                                                $('.wk_ss_showlist').append('<li title="'+element.cat+'"><a href="'+element.cat_url+'"><div><span class="wk_ss_left">'+element.cat+' in </span><span class="wk_ss_cat">'+element.cat_name+'</span></div></a></li>');
                                                $('.wk_ss_left').wrapInTag({"words" : [qInput], "ignoreChildNodes" : true});
                                            }
                                            if (element.title || element.num_results) {
                                                $('.wk_ss_showlist').append('<li title="'+element.title+'"><a href="'+baseurl+'catalogsearch/result/?q='+element.title+'"><div><span class="wk_ss_left">'+element.title+'</span><span class="wk_ss_num">'+element.num_results+'</span></div></a></li>');
                                                $('.wk_ss_left').wrapInTag({"words" : [qInput], "ignoreChildNodes" : true});
                                            }
                                            if (element.price && element.name && element.product_url) {
                                                if (i==1) {
                                                    $('.wk_ss_showlist').append('<li id="popular"><span class="wk_ss_bottom"></span><span class="wk_ss_heading">Popular Products</span></li>');
                                                }
                                                if (element.rate == null) {
                                                    rate=0;
                                                } else {
                                                    rate=element.rate;
                                                }
                                                $('.wk_ss_showlist').append('<li title="'+element.name+'"><a href="'+element.product_url+'"><div class="wk_ss_img"><img src="'+element.image_url+'"alt="' +element.name+'" ></div><div class="wk_ss_proname"><span class="wk_ss_prd">'+element.name+'</span><div class="rating-summary item wk_ss_rating'+rate+'" ><span class="label rating-label"><span>rate product</span></span><div class="rating-result" title="'+rate+'%"><meta itemprop="worstRating" content="1"><meta itemprop="bestRating" content="100"><span style="width:'+rate+'%"><span itemprop="ratingValue">'+rate+'%</span></span></div></div><div class="wk_ss_price">'+element.price+'</div></div></a></li>');
                                                $('.wk_ss_prd').wrapInTag({"words" : [qInput], "ignoreChildNodes" : true});
                                                i++;
                                            }
                                        })
                                    })
                                    if (response.terms== '' && response.items == '') {
                                            $('.wk_ss_showlist').append('<li id="popular"><span class="wk_ss_bottom"></span><span class="wk_ss_heading">No Results Found</span></li>');
                                    }
                                },
                                error: function (ts) {  }
                            })
                        } else {
                            ajaxcalled(e, qInput);
                        }
                        
                    }));
                    function ajaxcalled(e, qInput)
                    {
                        clearTimeout(typingTimer);
                        typingTimer = setTimeout(function () {
                            var qinput = qInput.trim();
                            if ((e.keyCode > 47 && e.keyCode < 58 && qprev != qinput)  || (e.keyCode > 64 && e.keyCode < 91 && qprev != qinput)   ||   (e.keyCode > 95 && e.keyCode < 112 && qprev != qinput)  || (e.keyCode > 185 && e.keyCode < 193 && qprev != qinput) ||  (e.keyCode > 218 && e.keyCode < 223 && qprev != qinput) || (e.keyCode==8 && qinput !='' && qprev != qinput) ) {
                                $('#wk_ss_loader').css('display','block');
                                if (qInput=='') {
                                    $('#wk_ss_loader').css('display','none');
                                }
                                if (ajax) {
                                    ajax.abort();
                                }
                                var clonePosition = {
                                    position: 'absolute',
                                    width: $('#search').outerWidth()
                                };
                                    ajax = $.ajax({
                                        url: baseurl+"search/ajax/suggest",
                                        data:"q="+qInput,
                                        type: "get",
                                        success: function (response) {
                                            var i=1;
                                            down=0;
                                            $('#wk_ss_loader').css('display','none');
                                            $('.wk_ss_list').css('display','block');
                                            $('#search_autocomplete').show();
                                            $('#search_autocomplete').css(clonePosition);
                                            $('.wk_ss_showlist li').remove();
                                            $.each(response,function (index,element) {
                                                $.each(element,function (index,element) {
                                                    if (element.cat && element.cat_name && element.cat_url) {
                                                        $('.wk_ss_showlist').append('<li title="'+element.cat+'"><a href="'+element.cat_url+'"><div><span class="wk_ss_left">'+element.cat+' in </span><span class="wk_ss_cat">'+element.cat_name+'</span></div></a></li>');
                                                        $('.wk_ss_left').wrapInTag({"words" : [qInput], "ignoreChildNodes" : true});
                                                    }
                                                    if (element.title || element.num_results) {
                                                        $('.wk_ss_showlist').append('<li title="'+element.title+'"><a href="'+baseurl+'catalogsearch/result/?q='+element.title+'"><div><span class="wk_ss_left">'+element.title+'</span><span class="wk_ss_num">'+element.num_results+'</span></div></a></li>');
                                                        $('.wk_ss_left').wrapInTag({"words" : [qInput], "ignoreChildNodes" : true});
                                                    }
                                                    if (element.price && element.name && element.product_url) {
                                                        if (i==1) {
                                                            $('.wk_ss_showlist').append('<li id="popular"><span class="wk_ss_bottom"></span><span class="wk_ss_heading">Popular Products</span></li>');
                                                        }
                                                        if (element.rate == null) {
                                                            rate=0;
                                                        } else {
                                                            rate=element.rate;
                                                        }
                                                        $('.wk_ss_showlist').append('<li title="'+element.name+'"><a href="'+element.product_url+'"><div class="wk_ss_img"><img src="'+element.image_url+'"alt="' +element.name+'" ></div><div class="wk_ss_proname"><span class="wk_ss_prd">'+element.name+'</span><div class="rating-summary item wk_ss_rating'+rate+'" ><span class="label rating-label"><span>rate product</span></span><div class="rating-result" title="'+rate+'%"><meta itemprop="worstRating" content="1"><meta itemprop="bestRating" content="100"><span style="width:'+rate+'%"><span itemprop="ratingValue">'+rate+'%</span></span></div></div><div class="wk_ss_price">'+element.price+'</div></div></a></li>');
                                                        $('.wk_ss_prd').wrapInTag({"words" : [qInput], "ignoreChildNodes" : true});
                                                        i++;
                                                    }
                                                })
                                            })
                                            if (response.terms== '' && response.items == '') {
                                                    $('.wk_ss_showlist').append('<li id="popular"><span class="wk_ss_bottom"></span><span class="wk_ss_heading">No Results Found</span></li>');
                                            }
                                        },
                                        error: function (ts) {  }
                                    })
                            };
                            qprev=qinput;
                        }, finaldoneTypingInterval);
                    }
                    $(document).on('keyup change','#search',(function (e) {
                        if (e.which !==0 && e.keyCode==40 || e.keyCode==38) {
                            var len=$('.wk_ss_list ul li').length;
                            $('.wk_ss_list').css('display','block');
                            if (e.keyCode==40) {
                                var x = e.keyCode;
                                down=checkpop(down,len,x);
                                if (down>len) {
                                    down=0;
                                    down=checkpop(down,len,x);
                                }
                                if (down==1) {
                                    $('.wk_ss_list ul li').removeAttr('class','wk_ss_selected');
                                    $('.wk_ss_list ul li:first').attr('class','wk_ss_selected');
                                } else if (down==len) {
                                        $('.wk_ss_list ul li').removeAttr('class','wk_ss_selected');
                                        $('.wk_ss_list ul li:last').attr('class','wk_ss_selected');
                                } else {
                                        $('.wk_ss_list ul li').removeAttr('class','wk_ss_selected');
                                        $('.wk_ss_list ul li:nth-child('+down+')').attr('class','wk_ss_selected');
                                }
                                    val=$('.wk_ss_list ul li.wk_ss_selected').attr('title');
                                    $('#search').val(val);
                                    url=$('.wk_ss_list ul li.wk_ss_selected a').attr('href');
                                    $('#search_mini_form').attr('action',url);
                            }
                            if (e.keyCode==38) {
                                    var x = e.keyCode;
                                    down=checkpop(down,len,x);
                                if (down<=0) {
                                        down=len;
                                }
                                if (down==1) {
                                        $('.wk_ss_list ul li').removeAttr('class','wk_ss_selected');
                                        $('.wk_ss_list ul li:first').attr('class','wk_ss_selected');
                                } else if (down==len) {
                                        $('.wk_ss_list ul li').removeAttr('class','wk_ss_selected');
                                        $('.wk_ss_list ul li:last').attr('class','wk_ss_selected');
                                } else {
                                        $('.wk_ss_list ul li').removeAttr('class','wk_ss_selected');
                                        $('.wk_ss_list ul li:nth-child('+down+')').attr('class','wk_ss_selected');
                                }
                                    val=$('.wk_ss_list ul li.wk_ss_selected').attr('title');
                                    $('#search').val(val);
                                    url=$('.wk_ss_list ul li.wk_ss_selected a').attr('href');
                                    $('#search_mini_form').attr('action',url);
                            }
                        }
                    }));
                    function checkpop(down,len,x)
                    {
                        if (x == 38) {
                            down--;
                        } else {
                            down++;
                        }
                        if (down==1) {
                            liid= $('.wk_ss_list ul li:first').attr('id');
                        } else if (down==len) {
                            liid= $('.wk_ss_list ul li:last').attr('id');
                        } else {
                            liid= $('.wk_ss_list ul li:nth-child('+down+')').attr('id');
                        }
                        if (liid=='popular') {
                            if (x == 38) {
                                down--;
                            }
                            if (x == 40) {
                                down++;
                            }
                        }
                        return down;
                    }
                    $("body").click(function (e) {
                        if (!(e.target.className.match(/^wk_ss_.*$/) || e.target.id.match(/^wk_ss_.*$/) || e.target.className.match('block-search'))) {
                            $(".wk_ss_list").hide();
                        }
                    });
                    $.fn.wrapInTag = function (opts) {
                        function getText(obj)
                        {
                            return obj.textContent ? obj.textContent : obj.innerText;
                        }
                        var tag = opts.tag || 'strong',
                        words = opts.words || [],
                        regex = RegExp(words.join('|'), 'gi'),
                        replacement = '<' + tag + '>$&</' + tag + '>';
                        $(this).contents().each(function () {
                            if (this.nodeType === 3) {
                                $(this).replaceWith(getText(this).replace(regex, replacement));
                            } else if (!opts.ignoreChildNodes) {
                                $(this).wrapInTag(opts);
                            }
                        });
                    };
                });
            }
        });
        return $.searchsuggestion.searchsuggestion;
    });
    