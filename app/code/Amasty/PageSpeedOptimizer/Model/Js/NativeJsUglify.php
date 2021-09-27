<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Js;

/**
 * Class NativeJsUglify native js inline script
 *
 * @package Amasty\PageSpeedOptimizer
 */
class NativeJsUglify
{
    const SCRIPT = '!function(){var e=window.addEventListener||function(e,t){window.attachEvent("on"+e,t)}'
        . ',t=window.removeEventListener||function(e,t,o){window.detachEvent("on"+e,t)},o={cache:[],'
        . 'mobileScreenSize:500,addObservers:function(){e("scroll",o.throttledLoad),e("resize",o.throttledLoad)},'
        . 'removeObservers:function(){t("scroll",o.throttledLoad,!1),t("resize",o.throttledLoad,!1)},'
        . 'throttleTimer:(new Date).getTime(),throttledLoad:function(){var e=(new Date).getTime();'
        . 'e-o.throttleTimer>=200&&(o.throttleTimer=e,o.loadVisibleImages())},loadVisibleImages:'
        . 'function(){for(var e=window.pageYOffset||document.documentElement.scrollTop,t=e-200,n=e+'
        . '(window.innerHeight||document.documentElement.clientHeight)+200,a=0;a<o.cache.length;){'
        . 'var i=o.cache[a],l=r(i);if(l>=t-(i.height||0)&&l<=n){var c=i.getAttribute("data-src-mobile");'
        . 'i.onload=function(){this.className=this.className.replace(/(^|\s+)lazy-load(\s+|$)/,"$1lazy-loaded$2")},'
        . 'c&&screen.width<=o.mobileScreenSize?i.src=c:i.src=i.getAttribute("data-amsrc")'
        . ',i.removeAttribute("data-amsrc"),i.removeAttribute("data-src-mobile"),o.cache.splice(a,1)}'
        . 'else a++}0===o.cache.length&&o.removeObservers()},init:function(){document.querySelectorAll||'
        . '(document.querySelectorAll=function(e){var t=document,o=t.documentElement.firstChild,'
        . 'r=t.createElement("STYLE");return o.appendChild(r),t.__qsaels=[],r.styleSheet.cssText=e+"'
        . '{x:expression(document.__qsaels.push(this))}",window.scrollBy(0,0),t.__qsaels});'
        . 'for(var e=document.querySelectorAll("img[data-amsrc]"),t=0;t<e.length;t++){'
        . 'var r=e[t];o.cache.push(r)}o.addObservers(),o.loadVisibleImages()}};function r(e){var t=0;'
        . 'if(e.offsetParent){do{t+=e.offsetTop}while(e=e.offsetParent);return t}}o.init()}();';
}
