!function(t){var e={};function n(r){if(e[r])return e[r].exports;var o=e[r]={i:r,l:!1,exports:{}};return t[r].call(o.exports,o,o.exports,n),o.l=!0,o.exports}n.m=t,n.c=e,n.d=function(t,e,r){n.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:r})},n.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},n.t=function(t,e){if(1&e&&(t=n(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var o in t)n.d(r,o,function(e){return t[e]}.bind(null,o));return r},n.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return n.d(e,"a",e),e},n.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},n.p="",n(n.s=270)}({10:function(t,e,n){var r=n(15),o=n(86),i=n(52),u=Math.max,c=Math.min;t.exports=function(t,e,n){var f,a,l,s,p,v,d=0,y=!1,b=!1,m=!0;if("function"!=typeof t)throw new TypeError("Expected a function");function x(e){var n=f,r=a;return f=a=void 0,d=e,s=t.apply(r,n)}function h(t){var n=t-v;return void 0===v||e<=n||n<0||b&&l<=t-d}function j(){var t=o();if(h(t))return g(t);p=setTimeout(j,function(t){var n=e-(t-v);return b?c(n,l-(t-d)):n}(t))}function g(t){return p=void 0,m&&f?x(t):(f=a=void 0,s)}function O(){var t=o(),n=h(t);if(f=arguments,a=this,v=t,n){if(void 0===p)return function(t){return d=t,p=setTimeout(j,e),y?x(t):s}(v);if(b)return clearTimeout(p),p=setTimeout(j,e),x(v)}return void 0===p&&(p=setTimeout(j,e)),s}return e=i(e)||0,r(n)&&(y=!!n.leading,l=(b="maxWait"in n)?u(i(n.maxWait)||0,e):l,m="trailing"in n?!!n.trailing:m),O.cancel=function(){void 0!==p&&clearTimeout(p),f=v=a=p=void(d=0)},O.flush=function(){return void 0===p?s:g(o())},O}},15:function(t,e){t.exports=function(t){var e=typeof t;return null!=t&&("object"==e||"function"==e)}},16:function(t,e){t.exports=function(t){return null!=t&&"object"==typeof t}},17:function(t,e,n){var r=n(22),o=n(65),i=n(66),u=r?r.toStringTag:void 0;t.exports=function(t){return null==t?void 0===t?"[object Undefined]":"[object Null]":u&&u in Object(t)?o(t):i(t)}},2:function(t,e){t.exports=jQuery},22:function(t,e,n){var r=n(9);t.exports=r.Symbol},26:function(t,e,n){var r=n(17),o=n(16);t.exports=function(t){return"symbol"==typeof t||o(t)&&"[object Symbol]"==r(t)}},270:function(t,e,n){"use strict";n.r(e);var r=n(2),o=n.n(r),i=n(10),u=n.n(i);function c(t,e){for(var n=0;n<e.length;n++){var r=e[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(t,r.key,r)}}var f=function(){function t(){!function(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}(this,t),this.excerpt=o()("#excerpt"),void 0!==this.excerpt&&this.hooks()}return function(t,e,n){e&&c(t.prototype,e)}(t,[{key:"hooks",value:function(){wp.hooks.addFilter("rank_math_content","rank-math",this.getContent.bind(this)),this.events()}},{key:"getContent",value:function(t){return t+("undefined"!=typeof tinymce&&tinymce.activeEditor&&"excerpt"===tinymce.activeEditor.id?tinymce.activeEditor.getContent():this.excerpt.val())}},{key:"events",value:function(){"undefined"!=typeof tinymce&&tinymce.activeEditor&&void 0!==tinymce.editors.excerpt&&tinyMCE.editors.excerpt.on("keyup change",u()((function(){RankMathApp.refresh("content")}),500))}}]),t}();o()(window).on("load",(function(){new f}))},43:function(t,e,n){var r;r=n(61),t.exports="object"==typeof r&&r&&r.Object===Object&&r},52:function(t,e,n){var r=n(15),o=n(26),i=/^\s+|\s+$/g,u=/^[-+]0x[0-9a-f]+$/i,c=/^0b[01]+$/i,f=/^0o[0-7]+$/i,a=parseInt;t.exports=function(t){if("number"==typeof t)return t;if(o(t))return NaN;if(r(t)){var e="function"==typeof t.valueOf?t.valueOf():t;t=r(e)?e+"":e}if("string"!=typeof t)return 0===t?t:+t;t=t.replace(i,"");var n=c.test(t);return n||f.test(t)?a(t.slice(2),n?2:8):u.test(t)?NaN:+t}},61:function(t,e){var n;n=function(){return this}();try{n=n||Function("return this")()}catch(t){"object"==typeof window&&(n=window)}t.exports=n},65:function(t,e,n){var r=n(22),o=Object.prototype,i=o.hasOwnProperty,u=o.toString,c=r?r.toStringTag:void 0;t.exports=function(t){var e=i.call(t,c),n=t[c];try{var r=!(t[c]=void 0)}catch(t){}var o=u.call(t);return r&&(e?t[c]=n:delete t[c]),o}},66:function(t,e){var n=Object.prototype.toString;t.exports=function(t){return n.call(t)}},86:function(t,e,n){var r=n(9);t.exports=function(){return r.Date.now()}},9:function(t,e,n){var r=n(43),o="object"==typeof self&&self&&self.Object===Object&&self,i=r||o||Function("return this")();t.exports=i}});