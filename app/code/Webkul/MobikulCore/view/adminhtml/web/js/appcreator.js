define([
    "jquery",
    'Magento_Ui/js/modal/alert',
    "mage/translate",
    "jquery/ui"
], function ($, alert, $t) {
    'use strict';
    $.widget('mage.appCreator', {
        _create: function () {
            var self = this;
            var successMessage = $t('Component Added to layout.');
            var errorMessage =$t('Error in Adding layout.');
            var successMsgRemLayout =$t('Component Removed from layout successfully');
            var errorMsgRemLayout =$t('Error in Removing layout');
            var successMsgOrdLayout =$t('Component Order save successfully');
            var errorMsgOrdLayout =$t('Error in Ordering layout');
            var errorMsgMaxNoOfLayout =$t('Limit Reached !');
            $( document ).ready(function() {
                $('.list-group-item').on('click', function(element){
                    if ($('#items').children().length >= self.options.maxLayout) {
                      self.addMessage(errorMsgMaxNoOfLayout, 'error');
                      return false;
                    }
                    var text = $(this).text();
                    var imagePath =  $(this).attr('data-image-url')
                    var htmll ='<li class="slide" id="layout_component_27_157"><div><div style="text-align: center;padding: 5px;background: #c4c4c4;font-size: small;"><span>'+text+'</span></div><img class="mobileImage" src="'+self.options.baseUrl+'/'+imagePath+'"></div></li>';
                    var html = '<li class="slide" id="'+$(this).attr('id')+'" class="list-group-items">'+text+'<span class="delete_component"><i class="fa fa-trash  style="padding-right:5px"></i></li>';
                    $('#items').append(html);
                    $('.iframe_html').append(htmll);
                    var url = $("#items").children().map(function() {
                      return $(this).attr('id');
                    }).get().join( ", " );
                    $.ajax({
                      url: self.options.updateUrl,
                      data: { data:url},
                      dataType: "json"
                    }).done(function (data) {
                      if (data['success'] == true) {
                        var html = "";
                        $.each(data['data'], function(key,value) {
                          html +='<li class="slide" id="layout_component_27_157"><div><div style="text-align: center;padding: 5px;background: #c4c4c4;font-size: small;"><span>'+value.label+'</span></div><img class="mobileImage" src="'+self.options.baseUrl+'/'+value.imagePath+'"></div></li>';
                        });
                        
                        $('.iframe_html').empty();
                        $('.iframe_html').append(html);
                        self.addMessage(successMessage, 'success');
                      } else {
                        self.addMessage(errorMessage, 'error');
                      }
                    });
                  });
                  $('#items').on('click', '.delete_component',function(element){
                    var childrenLength = $('#items').children().length;
                    var elementId = $(this).parent().attr('id'); 
                    $(this).parent().remove();
                    var url = $("#items").children().map(function() {
                      return $(this).attr('id');
                    }).get().join( ", " );
                    $.ajax({
                      url: self.options.updateUrl,
                      data: { data:url},
                      dataType: "json"
                    }).done(function (data) {
                      if (data['success'] == true) {
                        var html = "";
                        $.each(data['data'], function(key,value) {
                            html +='<li class="slide" id="layout_component_27_157"><div><div style="text-align: center;padding: 5px;background: #c4c4c4;font-size: small;"><span>'+value.label+'</span></div><img class="mobileImage" src="'+self.options.baseUrl+'/'+value.imagePath+'"></div></li>';
                        });
                        $('.iframe_html').empty();
                        $('.iframe_html').append(html);
                        self.addMessage(successMsgRemLayout, 'success');
                      } else {
                        self.addMessage(errorMsgRemLayout, 'error');
                      }
                    });
                  });
                  $( "#items").sortable({
                     // connectWith: "#centerSide"
                    placeholder: "highlight",
                    update: function (event, ui) {
                        var ids = $(this).sortable('toArray').toString();
                        $.ajax({
                            url: self.options.updateUrl,
                            data: { data:ids},
                            dataType: "json"
                        }).done(function (data) {
                          if (data['success'] == true) {
                            var html = "";
                            $.each(data['data'], function(key,value) {
                                html +='<li class="slide" id="layout_component_27_157"><div><div style="text-align: center;padding: 5px;background: #c4c4c4;font-size: small;"><span>'+value.label+'</span></div><img class="mobileImage" src="'+self.options.baseUrl+'/'+value.imagePath+'"></div></li>';
                            });
                            $('.iframe_html').empty();
                            $('.iframe_html').append(html);
                            self.addMessage(successMsgOrdLayout, 'success');
                          } else {
                            self.addMessage(errorMsgOrdLayout, 'error');
                          }
                        });
                    }
                   }).disableSelection();
                });
        },
        
        addMessage: function ($message, $type) {
          var className = "success";
          if ($type=='success') {
            className = 'success';
          }
          if ($type=='error') {
            className = 'error';
          }
          if ($(".msg").length > 0) {
            var position = $(".msg").first().css('top');
            var res = position.split("px");
            position = parseInt(res[0], 10) + 75
            position =  position+"px";
          } else {
            var position = '142px';
          }
          var message = '<div class="msg '+className+'-message" style="top: '+position+';"><span>'+$message+'</span></div>';
          $('#container').prepend(message);
          $('.msg').fadeIn().fadeOut(2000, function() { $(this).remove(); });
        }
    });
    return $.mage.appCreator
});