!function(t){var e={};function n(r){if(e[r])return e[r].exports;var o=e[r]={i:r,l:!1,exports:{}};return t[r].call(o.exports,o,o.exports,n),o.l=!0,o.exports}n.m=t,n.c=e,n.d=function(t,e,r){n.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:r})},n.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},n.t=function(t,e){if(1&e&&(t=n(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var o in t)n.d(r,o,function(e){return t[e]}.bind(null,o));return r},n.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return n.d(e,"a",e),e},n.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},n.p="./dist/",n(n.s=1)}([function(t,e,n){var r,o;
/*!
 * JavaScript Cookie v2.2.1
 * https://github.com/js-cookie/js-cookie
 *
 * Copyright 2006, 2015 Klaus Hartl & Fagner Brack
 * Released under the MIT license
 */!function(i){if(void 0===(o="function"==typeof(r=i)?r.call(e,n,e,t):r)||(t.exports=o),!0,t.exports=i(),!!0){var a=window.Cookies,p=window.Cookies=i();p.noConflict=function(){return window.Cookies=a,p}}}(function(){function t(){for(var t=0,e={};t<arguments.length;t++){var n=arguments[t];for(var r in n)e[r]=n[r]}return e}function e(t){return t.replace(/(%[0-9A-Z]{2})+/g,decodeURIComponent)}return function n(r){function o(){}function i(e,n,i){if("undefined"!=typeof document){"number"==typeof(i=t({path:"/"},o.defaults,i)).expires&&(i.expires=new Date(1*new Date+864e5*i.expires)),i.expires=i.expires?i.expires.toUTCString():"";try{var a=JSON.stringify(n);/^[\{\[]/.test(a)&&(n=a)}catch(t){}n=r.write?r.write(n,e):encodeURIComponent(String(n)).replace(/%(23|24|26|2B|3A|3C|3E|3D|2F|3F|40|5B|5D|5E|60|7B|7D|7C)/g,decodeURIComponent),e=encodeURIComponent(String(e)).replace(/%(23|24|26|2B|5E|60|7C)/g,decodeURIComponent).replace(/[\(\)]/g,escape);var p="";for(var u in i)i[u]&&(p+="; "+u,!0!==i[u]&&(p+="="+i[u].split(";")[0]));return document.cookie=e+"="+n+p}}function a(t,n){if("undefined"!=typeof document){for(var o={},i=document.cookie?document.cookie.split("; "):[],a=0;a<i.length;a++){var p=i[a].split("="),u=p.slice(1).join("=");n||'"'!==u.charAt(0)||(u=u.slice(1,-1));try{var c=e(p[0]);if(u=(r.read||r)(u,c)||e(u),n)try{u=JSON.parse(u)}catch(t){}if(o[c]=u,t===c)break}catch(t){}}return t?o[t]:o}}return o.set=i,o.get=function(t){return a(t,!1)},o.getJSON=function(t){return a(t,!0)},o.remove=function(e,n){i(e,"",t(n,{expires:-1}))},o.defaults={},o.withConverter=n,o}(function(){})})},function(t,e,n){"use strict";n.r(e);var r=n(0),o=n.n(r);!function(t){const e="ppw_rc";function n(n){n.preventDefault();const i=t(this).attr("data-submit"),a=t(this).closest("div"),p=t(this).find("input.ppw-password-input");if(!p.length)return;const u=t(this).find("input.ppw-submit"),c=u.attr("data-loading");!function(t,e){t.attr("disabled",!0),t.attr("data-text",t.val()),t.val(e)}(u,c||"Loading...");const s=t(p[0]).val(),f=function(n,a){const p=t(this).find("div.ppw-error");a?(p.html(a.responseJSON.message),r(u)):n.isValid?(function(t,n,r){const i=function(t,e){return t+"-"+e}(e,t),a=o.a.get(i),p=a?JSON.parse(a):a,u=Array.isArray(p)?p:[],c=u.findIndex(function(e){return e.post_id===t});c>-1?-1===u[c].passwords.indexOf(n)&&u[c].passwords.push(n):u.push({post_id:t,passwords:[n]}),o.a.set(i,JSON.stringify(u),{expires:new Date(1e3*r),path:"/"})}(i,s,n.cookie_expired_time),location.reload(!0)):(p.html(n.message),r(u))}.bind(this);!function(e,n,r){t("#submit").prop("disabled",!0),t.ajax({beforeSend:function(t){t.setRequestHeader("Content-Type","application/x-www-form-urlencoded"),t.setRequestHeader("X-WP-Nonce",ppwContentGlobal.nonce)},url:ppwContentGlobal.restUrl+"wppp/v1/check-content-password/"+e,type:"POST",data:n,success:function(t){r(t,null)},error:function(t){r(null,t)},timeout:5e3})}(i,{pss:s,idx:a.attr("ppw-data-index"),page:t(this).find("input.ppw-page").val(),formType:a.attr("ppwp-type")||"",metaKey:a.attr("ppwp-metakey")||""},f)}function r(t){t.removeAttr("disabled"),t.val(t.attr("data-text"))}Array.prototype.findIndex||Object.defineProperty(Array.prototype,"findIndex",{value:function(t){if(null==this)throw new TypeError('"this" is null or not defined');let e=Object(this),n=e.length>>>0;if("function"!=typeof t)throw new TypeError("predicate must be a function");let r=arguments[1],o=0;for(;o<n;){let n=e[o];if(t.call(r,n,o,e))return o;o++}return-1},configurable:!0,writable:!0}),t(document).ready(function(){!function(){const e=window.ppwContentGlobal.supportedClassNames.defaultType;(function(e){const n=t(".ppwp-is-custom-field");for(let r=0;r<n.length;r++){const o=t(n[r]).attr("ppwp-data-mt"),i=t(n[r]).find("."+e);for(let e=0;e<i.length;e++){const n=t(i[e]);n.attr("ppw-data-index",e),n.attr("ppwp-type","cf"),n.attr("ppwp-metakey",o)}}})(e),function(e){const n=t("."+e);let r=[];for(let e=0;e<n.length;e++){const o=t(n[e]).parent().attr("class");"ppwp-is-custom-field"!==o&&r.push(n[e])}for(let e=0;e<r.length;e++)t(r[e]).attr("ppw-data-index",e)}(e)}(),t(".ppw-form").bind("submit",n)})}(jQuery)}]);