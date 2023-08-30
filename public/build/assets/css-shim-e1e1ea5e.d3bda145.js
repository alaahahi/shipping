/*!
 * Built by Revolist
 */var c=function(){return(c=Object.assign||function(t){for(var e,r=1,n=arguments.length;r<n;r++)for(var s in e=arguments[r])Object.prototype.hasOwnProperty.call(e,s)&&(t[s]=e[s]);return t}).apply(this,arguments)},y=function(){this.start=0,this.end=0,this.previous=null,this.parent=null,this.rules=null,this.parsedCssText="",this.cssText="",this.atRule=!1,this.type=0,this.keyframesName="",this.selector="",this.parsedSelector=""};function I(t){return R(w(t=L(t)),t)}function L(t){return t.replace(p.comments,"").replace(p.port,"")}function w(t){var e=new y;e.start=0,e.end=t.length;for(var r=e,n=0,s=t.length;n<s;n++)if(t[n]===N){r.rules||(r.rules=[]);var o=r,a=o.rules[o.rules.length-1]||null;(r=new y).start=n+1,r.parent=o,r.previous=a,o.rules.push(r)}else t[n]===V&&(r.end=n+1,r=r.parent||e);return e}function R(t,e){var r=e.substring(t.start,t.end-1);if(t.parsedCssText=t.cssText=r.trim(),t.parent){var n=t.previous?t.previous.end:t.parent.start;r=(r=(r=k(r=e.substring(n,t.start-1))).replace(p.multipleSpaces," ")).substring(r.lastIndexOf(";")+1);var s=t.parsedSelector=t.selector=r.trim();t.atRule=s.indexOf(G)===0,t.atRule?s.indexOf(U)===0?t.type=l.MEDIA_RULE:s.match(p.keyframesRule)&&(t.type=l.KEYFRAMES_RULE,t.keyframesName=t.selector.split(p.multipleSpaces).pop()):s.indexOf(O)===0?t.type=l.MIXIN_RULE:t.type=l.STYLE_RULE}var o=t.rules;if(o)for(var a=0,i=o.length,u=void 0;a<i&&(u=o[a]);a++)R(u,e);return t}function k(t){return t.replace(/\\([0-9a-f]{1,6})\s/gi,function(){for(var e=arguments[1],r=6-e.length;r--;)e="0"+e;return"\\"+e})}var l={STYLE_RULE:1,KEYFRAMES_RULE:7,MEDIA_RULE:4,MIXIN_RULE:1e3},N="{",V="}",p={comments:/\/\*[^*]*\*+([^/*][^*]*\*+)*\//gim,port:/@import[^;]*;/gim,customProp:/(?:^[^;\-\s}]+)?--[^;{}]*?:[^{};]*?(?:[;\n]|$)/gim,mixinProp:/(?:^[^;\-\s}]+)?--[^;{}]*?:[^{};]*?{[^}]*?}(?:[;\n]|$)?/gim,mixinApply:/@apply\s*\(?[^);]*\)?\s*(?:[;\n]|$)?/gim,varApply:/[^;:]*?:[^;]*?var\([^;]*\)(?:[;\n]|$)?/gim,keyframesRule:/^@[^\s]*keyframes/,multipleSpaces:/\s+/g},O="--",U="@media",G="@";function A(t,e,r){t.lastIndex=0;var n=e.substring(r).match(t);if(n){var s=r+n.index;return{start:s,end:s+n[0].length}}return null}var P=/\bvar\(/,D=/\B--[\w-]+\s*:/,F=/\/\*[^*]*\*+([^/*][^*]*\*+)*\//gim,B=/^[\t ]+\n/gm;function $(t,e,r){return t[e]?t[e]:r?f(r,t):""}function H(t,e){for(var r=0,n=e;n<t.length;n++){var s=t[n];if(s==="(")r++;else if(s===")"&&--r<=0)return n+1}return n}function X(t,e){var r=A(P,t,e);if(!r)return null;var n=H(t,r.start),s=t.substring(r.end,n-1).split(","),o=s[0],a=s.slice(1);return{start:r.start,end:n,propName:o.trim(),fallback:a.length>0?a.join(",").trim():void 0}}function Y(t,e,r){var n=X(t,r);if(!n)return e.push(t.substring(r,t.length)),t.length;var s=n.propName,o=n.fallback!=null?m(n.fallback):void 0;return e.push(t.substring(r,n.start),function(a){return $(a,s,o)}),n.end}function f(t,e){for(var r="",n=0;n<t.length;n++){var s=t[n];r+=typeof s=="string"?s:s(e)}return r}function j(t,e){for(var r=!1,n=!1,s=e;s<t.length;s++){var o=t[s];if(r)n&&o==='"'&&(r=!1),n||o!=="'"||(r=!1);else if(o==='"')r=!0,n=!0;else if(o==="'")r=!0,n=!1;else{if(o===";")return s+1;if(o==="}")return s}}return s}function q(t){for(var e="",r=0;;){var n=A(D,t,r),s=n?n.start:t.length;if(e+=t.substring(r,s),!n)break;r=j(t,s)}return e}function m(t){var e=0;t=q(t=t.replace(F,"")).replace(B,"");for(var r=[];e<t.length;)e=Y(t,r,e);return r}function x(t){var e={};t.forEach(function(a){a.declarations.forEach(function(i){e[i.prop]=i.value})});for(var r={},n=Object.entries(e),s=function(a){var i=!1;if(n.forEach(function(u){var S=u[0],g=f(u[1],r);g!==r[S]&&(r[S]=g,i=!0)}),!i)return"break"},o=0;o<10&&s()!=="break";o++);return r}function W(t,e){if(e===void 0&&(e=0),!t.rules)return[];var r=[];return t.rules.filter(function(n){return n.type===l.STYLE_RULE}).forEach(function(n){var s=Z(n.cssText);s.length>0&&n.parsedSelector.split(",").forEach(function(o){o=o.trim(),r.push({selector:o,declarations:s,specificity:z(),nu:e})}),e++}),r}function z(t){return 1}var E="!important",K=/(?:^|[;\s{]\s*)(--[\w-]*?)\s*:\s*(?:((?:'(?:\\'|.)*?'|"(?:\\"|.)*?"|\([^)]*?\)|[^};{])+)|\{([^}]*)\}(?:(?=[;\s}])|$))/gm;function Z(t){for(var e,r=[];e=K.exec(t.trim());){var n=J(e[2]),s=n.value,o=n.important;r.push({prop:e[1].trim(),value:m(s),important:o})}return r}function J(t){var e=(t=t.replace(/\s+/gim," ").trim()).endsWith(E);return e&&(t=t.slice(0,t.length-E.length).trim()),{value:t,important:e}}function Q(t,e,r){var n=[],s=tt(e,t);return r.forEach(function(o){return n.push(o)}),s.forEach(function(o){return n.push(o)}),et(_(n).filter(function(o){return rt(t,o.selector)}))}function tt(t,e){for(var r=[];e;){var n=t.get(e);n&&r.push(n),e=e.parentElement}return r}function _(t){var e=[];return t.forEach(function(r){e.push.apply(e,r.selectors)}),e}function et(t){return t.sort(function(e,r){return e.specificity===r.specificity?e.nu-r.nu:e.specificity-r.specificity}),t}function rt(t,e){return e===":root"||e==="html"||t.matches(e)}function M(t){var e=I(t),r=m(t);return{original:t,template:r,selectors:W(e),usesCssVars:r.length>1}}function d(t,e){if(t.some(function(n){return n.styleEl===e}))return!1;var r=M(e.textContent);return r.styleEl=e,t.push(r),!0}function v(t){var e=x(_(t));t.forEach(function(r){r.usesCssVars&&(r.styleEl.textContent=f(r.template,e))})}function nt(t,e){var r=t.template.map(function(s){return typeof s=="string"?b(s,t.scopeId,e):s}),n=t.selectors.map(function(s){return c(c({},s),{selector:b(s.selector,t.scopeId,e)})});return c(c({},t),{template:r,selectors:n,scopeId:e})}function b(t,e,r){return t=st(t,"\\.".concat(e),".".concat(r))}function st(t,e,r){return t.replace(new RegExp(e,"g"),r)}function ot(t,e){return C(t,e),it(t,e).then(function(){v(e)})}function at(t,e){typeof MutationObserver<"u"&&new MutationObserver(function(){C(t,e)&&v(e)}).observe(document.head,{childList:!0})}function it(t,e){for(var r=[],n=t.querySelectorAll('link[rel="stylesheet"][href]:not([data-no-shim])'),s=0;s<n.length;s++)r.push(T(t,e,n[s]));return Promise.all(r)}function C(t,e){return Array.from(t.querySelectorAll("style:not([data-styles]):not([data-no-shim])")).map(function(r){return d(e,r)}).some(Boolean)}function T(t,e,r){var n=r.href;return fetch(n).then(function(s){return s.text()}).then(function(s){if(ct(s)&&r.parentNode){lt(s)&&(s=pt(s,n));var o=t.createElement("style");o.setAttribute("data-styles",""),o.textContent=s,d(e,o),r.parentNode.insertBefore(o,r),r.remove()}}).catch(function(s){console.error(s)})}var ut=/[\s;{]--[-a-zA-Z0-9]+\s*:/m;function ct(t){return t.indexOf("var(")>-1||ut.test(t)}var h=/url[\s]*\([\s]*['"]?(?!(?:https?|data)\:|\/)([^\'\"\)]*)[\s]*['"]?\)[\s]*/gim;function lt(t){return h.lastIndex=0,h.test(t)}function pt(t,e){var r=e.replace(/[^/]*$/,"");return t.replace(h,function(n,s){var o=r+s;return n.replace(s,o)})}var ft=function(){function t(e,r){this.win=e,this.doc=r,this.count=0,this.hostStyleMap=new WeakMap,this.hostScopeMap=new WeakMap,this.globalScopes=[],this.scopesMap=new Map,this.didInit=!1}return t.prototype.i=function(){var e=this;return this.didInit||!this.win.requestAnimationFrame?Promise.resolve():(this.didInit=!0,new Promise(function(r){e.win.requestAnimationFrame(function(){at(e.doc,e.globalScopes),ot(e.doc,e.globalScopes).then(function(){return r()})})}))},t.prototype.addLink=function(e){var r=this;return T(this.doc,this.globalScopes,e).then(function(){r.updateGlobal()})},t.prototype.addGlobalStyle=function(e){d(this.globalScopes,e)&&this.updateGlobal()},t.prototype.createHostStyle=function(e,r,n,s){if(this.hostScopeMap.has(e))throw new Error("host style already created");var o=this.registerHostTemplate(n,r,s),a=this.doc.createElement("style");return a.setAttribute("data-no-shim",""),o.usesCssVars?s?(a["s-sc"]=r="".concat(o.scopeId,"-").concat(this.count),a.textContent="/*needs update*/",this.hostStyleMap.set(e,a),this.hostScopeMap.set(e,nt(o,r)),this.count++):(o.styleEl=a,o.usesCssVars||(a.textContent=f(o.template,{})),this.globalScopes.push(o),this.updateGlobal(),this.hostScopeMap.set(e,o)):a.textContent=n,a},t.prototype.removeHost=function(e){var r=this.hostStyleMap.get(e);r&&r.remove(),this.hostStyleMap.delete(e),this.hostScopeMap.delete(e)},t.prototype.updateHost=function(e){var r=this.hostScopeMap.get(e);if(r&&r.usesCssVars&&r.isScoped){var n=this.hostStyleMap.get(e);if(n){var s=x(Q(e,this.hostScopeMap,this.globalScopes));n.textContent=f(r.template,s)}}},t.prototype.updateGlobal=function(){v(this.globalScopes)},t.prototype.registerHostTemplate=function(e,r,n){var s=this.scopesMap.get(r);return s||((s=M(e)).scopeId=r,s.isScoped=n,this.scopesMap.set(r,s)),s},t}();(function(t){!t||t.__cssshim||t.CSS&&t.CSS.supports&&t.CSS.supports("color","var(--c)")||(t.__cssshim=new ft(t,t.document))})(typeof window<"u"&&window);
