/*! For license information please see 761.bundle-2bd3a6701f00dd40af0b.js.LICENSE.txt */
"use strict";(self.webpackChunksemantyca=self.webpackChunksemantyca||[]).push([[761],{1761:(e,t,n)=>{n.r(t),n.d(t,{default:()=>a});var r=n(2201);const o=[{path:"/",component:()=>n.e(572).then(n.bind(n,2572))},{path:"/list",component:()=>n.e(572).then(n.bind(n,2572))},{path:"/form",component:()=>n.e(169).then(n.bind(n,8169))},{path:"/form/:id",component:()=>n.e(169).then(n.bind(n,8169))}],a=(0,r.p7)({history:(0,r.r5)(),routes:o})},2201:(e,t,n)=>{n.d(t,{p7:()=>qe,r5:()=>H,tv:()=>Ae,yj:()=>Fe});var r=n(5166);const o="undefined"!=typeof document;const a=Object.assign;function c(e,t){const n={};for(const r in t){const o=t[r];n[r]=l(o)?o.map(e):e(o)}return n}const s=()=>{},l=Array.isArray,i=/#/g,u=/&/g,f=/\//g,p=/=/g,h=/\?/g,d=/\+/g,m=/%5B/g,g=/%5D/g,v=/%5E/g,y=/%60/g,b=/%7B/g,w=/%7C/g,E=/%7D/g,k=/%20/g;function R(e){return encodeURI(""+e).replace(w,"|").replace(m,"[").replace(g,"]")}function O(e){return R(e).replace(d,"%2B").replace(k,"+").replace(i,"%23").replace(u,"%26").replace(y,"`").replace(b,"{").replace(E,"}").replace(v,"^")}function P(e){return null==e?"":function(e){return R(e).replace(i,"%23").replace(h,"%3F")}(e).replace(f,"%2F")}function C(e){try{return decodeURIComponent(""+e)}catch(e){}return""+e}const x=/\/$/,S=e=>e.replace(x,"");function j(e,t,n="/"){let r,o={},a="",c="";const s=t.indexOf("#");let l=t.indexOf("?");return s<l&&s>=0&&(l=-1),l>-1&&(r=t.slice(0,l),a=t.slice(l+1,s>-1?s:t.length),o=e(a)),s>-1&&(r=r||t.slice(0,s),c=t.slice(s,t.length)),r=function(e,t){if(e.startsWith("/"))return e;if(!e)return t;const n=t.split("/"),r=e.split("/"),o=r[r.length-1];".."!==o&&"."!==o||r.push("");let a,c,s=n.length-1;for(a=0;a<r.length;a++)if(c=r[a],"."!==c){if(".."!==c)break;s>1&&s--}return n.slice(0,s).join("/")+"/"+r.slice(a).join("/")}(null!=r?r:t,n),{fullPath:r+(a&&"?")+a+c,path:r,query:o,hash:C(c)}}function $(e,t){return t&&e.toLowerCase().startsWith(t.toLowerCase())?e.slice(t.length)||"/":e}function q(e,t){return(e.aliasOf||e)===(t.aliasOf||t)}function A(e,t){if(Object.keys(e).length!==Object.keys(t).length)return!1;for(const n in e)if(!F(e[n],t[n]))return!1;return!0}function F(e,t){return l(e)?L(e,t):l(t)?L(t,e):e===t}function L(e,t){return l(t)?e.length===t.length&&e.every(((e,n)=>e===t[n])):1===e.length&&e[0]===t}const M={path:"/",name:void 0,params:{},query:{},hash:"",fullPath:"/",matched:[],meta:{},redirectedFrom:void 0};var U,B;!function(e){e.pop="pop",e.push="push"}(U||(U={})),function(e){e.back="back",e.forward="forward",e.unknown=""}(B||(B={}));const G=/^[^#]+#/;function I(e,t){return e.replace(G,"#")+t}const _=()=>({left:window.scrollX,top:window.scrollY});function W(e,t){return(history.state?history.state.position-t:-1)+e}const D=new Map;let J=()=>location.protocol+"//"+location.host;function T(e,t){const{pathname:n,search:r,hash:o}=t,a=e.indexOf("#");if(a>-1){let t=o.includes(e.slice(a))?e.slice(a).length:1,n=o.slice(t);return"/"!==n[0]&&(n="/"+n),$(n,"")}return $(n,e)+r+o}function V(e,t,n,r=!1,o=!1){return{back:e,current:t,forward:n,replaced:r,position:window.history.length,scroll:o?_():null}}function z(e){const t=function(e){const{history:t,location:n}=window,r={value:T(e,n)},o={value:t.state};function c(r,a,c){const s=e.indexOf("#"),l=s>-1?(n.host&&document.querySelector("base")?e:e.slice(s))+r:J()+e+r;try{t[c?"replaceState":"pushState"](a,"",l),o.value=a}catch(e){console.error(e),n[c?"replace":"assign"](l)}}return o.value||c(r.value,{back:null,current:r.value,forward:null,position:t.length-1,replaced:!0,scroll:null},!0),{location:r,state:o,push:function(e,n){const s=a({},o.value,t.state,{forward:e,scroll:_()});c(s.current,s,!0),c(e,a({},V(r.value,e,null),{position:s.position+1},n),!1),r.value=e},replace:function(e,n){c(e,a({},t.state,V(o.value.back,e,o.value.forward,!0),n,{position:o.value.position}),!0),r.value=e}}}(e=function(e){if(!e)if(o){const t=document.querySelector("base");e=(e=t&&t.getAttribute("href")||"/").replace(/^\w+:\/\/[^\/]+/,"")}else e="/";return"/"!==e[0]&&"#"!==e[0]&&(e="/"+e),S(e)}(e)),n=function(e,t,n,r){let o=[],c=[],s=null;const l=({state:a})=>{const c=T(e,location),l=n.value,i=t.value;let u=0;if(a){if(n.value=c,t.value=a,s&&s===l)return void(s=null);u=i?a.position-i.position:0}else r(c);o.forEach((e=>{e(n.value,l,{delta:u,type:U.pop,direction:u?u>0?B.forward:B.back:B.unknown})}))};function i(){const{history:e}=window;e.state&&e.replaceState(a({},e.state,{scroll:_()}),"")}return window.addEventListener("popstate",l),window.addEventListener("beforeunload",i,{passive:!0}),{pauseListeners:function(){s=n.value},listen:function(e){o.push(e);const t=()=>{const t=o.indexOf(e);t>-1&&o.splice(t,1)};return c.push(t),t},destroy:function(){for(const e of c)e();c=[],window.removeEventListener("popstate",l),window.removeEventListener("beforeunload",i)}}}(e,t.state,t.location,t.replace),r=a({location:"",base:e,go:function(e,t=!0){t||n.pauseListeners(),history.go(e)},createHref:I.bind(null,e)},t,n);return Object.defineProperty(r,"location",{enumerable:!0,get:()=>t.location.value}),Object.defineProperty(r,"state",{enumerable:!0,get:()=>t.state.value}),r}function H(e){return(e=location.host?e||location.pathname+location.search:"").includes("#")||(e+="#"),z(e)}function K(e){return"string"==typeof e||"symbol"==typeof e}const Y=Symbol("");var X;function Z(e,t){return a(new Error,{type:e,[Y]:!0},t)}function Q(e,t){return e instanceof Error&&Y in e&&(null==t||!!(e.type&t))}!function(e){e[e.aborted=4]="aborted",e[e.cancelled=8]="cancelled",e[e.duplicated=16]="duplicated"}(X||(X={}));const N="[^/]+?",ee={sensitive:!1,strict:!1,start:!0,end:!0},te=/[.+*?^${}()[\]/\\]/g;function ne(e,t){let n=0;for(;n<e.length&&n<t.length;){const r=t[n]-e[n];if(r)return r;n++}return e.length<t.length?1===e.length&&80===e[0]?-1:1:e.length>t.length?1===t.length&&80===t[0]?1:-1:0}function re(e,t){let n=0;const r=e.score,o=t.score;for(;n<r.length&&n<o.length;){const e=ne(r[n],o[n]);if(e)return e;n++}if(1===Math.abs(o.length-r.length)){if(oe(r))return 1;if(oe(o))return-1}return o.length-r.length}function oe(e){const t=e[e.length-1];return e.length>0&&t[t.length-1]<0}const ae={type:0,value:""},ce=/[a-zA-Z0-9_]/;function se(e,t,n){const r=function(e,t){const n=a({},ee,t),r=[];let o=n.start?"^":"";const c=[];for(const t of e){const e=t.length?[]:[90];n.strict&&!t.length&&(o+="/");for(let r=0;r<t.length;r++){const a=t[r];let s=40+(n.sensitive?.25:0);if(0===a.type)r||(o+="/"),o+=a.value.replace(te,"\\$&"),s+=40;else if(1===a.type){const{value:e,repeatable:n,optional:l,regexp:i}=a;c.push({name:e,repeatable:n,optional:l});const u=i||N;if(u!==N){s+=10;try{new RegExp(`(${u})`)}catch(t){throw new Error(`Invalid custom RegExp for param "${e}" (${u}): `+t.message)}}let f=n?`((?:${u})(?:/(?:${u}))*)`:`(${u})`;r||(f=l&&t.length<2?`(?:/${f})`:"/"+f),l&&(f+="?"),o+=f,s+=20,l&&(s+=-8),n&&(s+=-20),".*"===u&&(s+=-50)}e.push(s)}r.push(e)}if(n.strict&&n.end){const e=r.length-1;r[e][r[e].length-1]+=.7000000000000001}n.strict||(o+="/?"),n.end?o+="$":n.strict&&(o+="(?:/|$)");const s=new RegExp(o,n.sensitive?"":"i");return{re:s,score:r,keys:c,parse:function(e){const t=e.match(s),n={};if(!t)return null;for(let e=1;e<t.length;e++){const r=t[e]||"",o=c[e-1];n[o.name]=r&&o.repeatable?r.split("/"):r}return n},stringify:function(t){let n="",r=!1;for(const o of e){r&&n.endsWith("/")||(n+="/"),r=!1;for(const e of o)if(0===e.type)n+=e.value;else if(1===e.type){const{value:a,repeatable:c,optional:s}=e,i=a in t?t[a]:"";if(l(i)&&!c)throw new Error(`Provided param "${a}" is an array but it is not repeatable (* or + modifiers)`);const u=l(i)?i.join("/"):i;if(!u){if(!s)throw new Error(`Missing required param "${a}"`);o.length<2&&(n.endsWith("/")?n=n.slice(0,-1):r=!0)}n+=u}}return n||"/"}}}(function(e){if(!e)return[[]];if("/"===e)return[[ae]];if(!e.startsWith("/"))throw new Error(`Invalid path "${e}"`);function t(e){throw new Error(`ERR (${n})/"${i}": ${e}`)}let n=0,r=n;const o=[];let a;function c(){a&&o.push(a),a=[]}let s,l=0,i="",u="";function f(){i&&(0===n?a.push({type:0,value:i}):1===n||2===n||3===n?(a.length>1&&("*"===s||"+"===s)&&t(`A repeatable param (${i}) must be alone in its segment. eg: '/:ids+.`),a.push({type:1,value:i,regexp:u,repeatable:"*"===s||"+"===s,optional:"*"===s||"?"===s})):t("Invalid state to consume buffer"),i="")}function p(){i+=s}for(;l<e.length;)if(s=e[l++],"\\"!==s||2===n)switch(n){case 0:"/"===s?(i&&f(),c()):":"===s?(f(),n=1):p();break;case 4:p(),n=r;break;case 1:"("===s?n=2:ce.test(s)?p():(f(),n=0,"*"!==s&&"?"!==s&&"+"!==s&&l--);break;case 2:")"===s?"\\"==u[u.length-1]?u=u.slice(0,-1)+s:n=3:u+=s;break;case 3:f(),n=0,"*"!==s&&"?"!==s&&"+"!==s&&l--,u="";break;default:t("Unknown state")}else r=n,n=4;return 2===n&&t(`Unfinished custom RegExp for param "${i}"`),f(),c(),o}(e.path),n),o=a(r,{record:e,parent:t,children:[],alias:[]});return t&&!o.record.aliasOf==!t.record.aliasOf&&t.children.push(o),o}function le(e,t){const n={};for(const r of t)r in e&&(n[r]=e[r]);return n}function ie(e){const t={},n=e.props||!1;if("component"in e)t.default=n;else for(const r in e.components)t[r]="object"==typeof n?n[r]:n;return t}function ue(e){for(;e;){if(e.record.aliasOf)return!0;e=e.parent}return!1}function fe(e){return e.reduce(((e,t)=>a(e,t.meta)),{})}function pe(e,t){const n={};for(const r in e)n[r]=r in t?t[r]:e[r];return n}function he({record:e}){return!!(e.name||e.components&&Object.keys(e.components).length||e.redirect)}function de(e){const t={};if(""===e||"?"===e)return t;const n=("?"===e[0]?e.slice(1):e).split("&");for(let e=0;e<n.length;++e){const r=n[e].replace(d," "),o=r.indexOf("="),a=C(o<0?r:r.slice(0,o)),c=o<0?null:C(r.slice(o+1));if(a in t){let e=t[a];l(e)||(e=t[a]=[e]),e.push(c)}else t[a]=c}return t}function me(e){let t="";for(let n in e){const r=e[n];(n=O(n).replace(p,"%3D"),null!=r)?(l(r)?r.map((e=>e&&O(e))):[r&&O(r)]).forEach((e=>{void 0!==e&&(t+=(t.length?"&":"")+n,null!=e&&(t+="="+e))})):void 0!==r&&(t+=(t.length?"&":"")+n)}return t}function ge(e){const t={};for(const n in e){const r=e[n];void 0!==r&&(t[n]=l(r)?r.map((e=>null==e?null:""+e)):null==r?r:""+r)}return t}const ve=Symbol(""),ye=Symbol(""),be=Symbol(""),we=Symbol(""),Ee=Symbol("");function ke(){let e=[];return{add:function(t){return e.push(t),()=>{const n=e.indexOf(t);n>-1&&e.splice(n,1)}},list:()=>e.slice(),reset:function(){e=[]}}}function Re(e,t,n,r,o,a=(e=>e())){const c=r&&(r.enterCallbacks[o]=r.enterCallbacks[o]||[]);return()=>new Promise(((s,l)=>{const i=e=>{var a;!1===e?l(Z(4,{from:n,to:t})):e instanceof Error?l(e):"string"==typeof(a=e)||a&&"object"==typeof a?l(Z(2,{from:t,to:e})):(c&&r.enterCallbacks[o]===c&&"function"==typeof e&&c.push(e),s())},u=a((()=>e.call(r&&r.instances[o],t,n,i)));let f=Promise.resolve(u);e.length<3&&(f=f.then(i)),f.catch((e=>l(e)))}))}function Oe(e,t,n,r,o=(e=>e())){const a=[];for(const s of e)for(const e in s.components){let l=s.components[e];if("beforeRouteEnter"===t||s.instances[e])if("object"==typeof(c=l)||"displayName"in c||"props"in c||"__vccOpts"in c){const c=(l.__vccOpts||l)[t];c&&a.push(Re(c,n,r,s,e,o))}else{let c=l();a.push((()=>c.then((a=>{if(!a)return Promise.reject(new Error(`Couldn't resolve component "${e}" at "${s.path}"`));const c=(l=a).__esModule||"Module"===l[Symbol.toStringTag]?a.default:a;var l;s.components[e]=c;const i=(c.__vccOpts||c)[t];return i&&Re(i,n,r,s,e,o)()}))))}}var c;return a}function Pe(e){const t=(0,r.f3)(be),n=(0,r.f3)(we),o=(0,r.Fl)((()=>{const n=(0,r.SU)(e.to);return t.resolve(n)})),a=(0,r.Fl)((()=>{const{matched:e}=o.value,{length:t}=e,r=e[t-1],a=n.matched;if(!r||!a.length)return-1;const c=a.findIndex(q.bind(null,r));if(c>-1)return c;const s=xe(e[t-2]);return t>1&&xe(r)===s&&a[a.length-1].path!==s?a.findIndex(q.bind(null,e[t-2])):c})),c=(0,r.Fl)((()=>a.value>-1&&function(e,t){for(const n in t){const r=t[n],o=e[n];if("string"==typeof r){if(r!==o)return!1}else if(!l(o)||o.length!==r.length||r.some(((e,t)=>e!==o[t])))return!1}return!0}(n.params,o.value.params))),i=(0,r.Fl)((()=>a.value>-1&&a.value===n.matched.length-1&&A(n.params,o.value.params)));return{route:o,href:(0,r.Fl)((()=>o.value.href)),isActive:c,isExactActive:i,navigate:function(n={}){return function(e){if(!(e.metaKey||e.altKey||e.ctrlKey||e.shiftKey||e.defaultPrevented||void 0!==e.button&&0!==e.button)){if(e.currentTarget&&e.currentTarget.getAttribute){const t=e.currentTarget.getAttribute("target");if(/\b_blank\b/i.test(t))return}return e.preventDefault&&e.preventDefault(),!0}}(n)?t[(0,r.SU)(e.replace)?"replace":"push"]((0,r.SU)(e.to)).catch(s):Promise.resolve()}}}const Ce=(0,r.aZ)({name:"RouterLink",compatConfig:{MODE:3},props:{to:{type:[String,Object],required:!0},replace:Boolean,activeClass:String,exactActiveClass:String,custom:Boolean,ariaCurrentValue:{type:String,default:"page"}},useLink:Pe,setup(e,{slots:t}){const n=(0,r.qj)(Pe(e)),{options:o}=(0,r.f3)(be),a=(0,r.Fl)((()=>({[Se(e.activeClass,o.linkActiveClass,"router-link-active")]:n.isActive,[Se(e.exactActiveClass,o.linkExactActiveClass,"router-link-exact-active")]:n.isExactActive})));return()=>{const o=t.default&&t.default(n);return e.custom?o:(0,r.h)("a",{"aria-current":n.isExactActive?e.ariaCurrentValue:null,href:n.href,onClick:n.navigate,class:a.value},o)}}});function xe(e){return e?e.aliasOf?e.aliasOf.path:e.path:""}const Se=(e,t,n)=>null!=e?e:null!=t?t:n;function je(e,t){if(!e)return null;const n=e(t);return 1===n.length?n[0]:n}const $e=(0,r.aZ)({name:"RouterView",inheritAttrs:!1,props:{name:{type:String,default:"default"},route:Object},compatConfig:{MODE:3},setup(e,{attrs:t,slots:n}){const o=(0,r.f3)(Ee),c=(0,r.Fl)((()=>e.route||o.value)),s=(0,r.f3)(ye,0),l=(0,r.Fl)((()=>{let e=(0,r.SU)(s);const{matched:t}=c.value;let n;for(;(n=t[e])&&!n.components;)e++;return e})),i=(0,r.Fl)((()=>c.value.matched[l.value]));(0,r.JJ)(ye,(0,r.Fl)((()=>l.value+1))),(0,r.JJ)(ve,i),(0,r.JJ)(Ee,c);const u=(0,r.iH)();return(0,r.YP)((()=>[u.value,i.value,e.name]),(([e,t,n],[r,o,a])=>{t&&(t.instances[n]=e,o&&o!==t&&e&&e===r&&(t.leaveGuards.size||(t.leaveGuards=o.leaveGuards),t.updateGuards.size||(t.updateGuards=o.updateGuards))),!e||!t||o&&q(t,o)&&r||(t.enterCallbacks[n]||[]).forEach((t=>t(e)))}),{flush:"post"}),()=>{const o=c.value,s=e.name,l=i.value,f=l&&l.components[s];if(!f)return je(n.default,{Component:f,route:o});const p=l.props[s],h=p?!0===p?o.params:"function"==typeof p?p(o):p:null,d=(0,r.h)(f,a({},h,t,{onVnodeUnmounted:e=>{e.component.isUnmounted&&(l.instances[s]=null)},ref:u}));return je(n.default,{Component:d,route:o})||d}}});function qe(e){const t=function(e,t){const n=[],r=new Map;function o(e,n,r){const i=!r,u=function(e){return{path:e.path,redirect:e.redirect,name:e.name,meta:e.meta||{},aliasOf:void 0,beforeEnter:e.beforeEnter,props:ie(e),children:e.children||[],instances:{},leaveGuards:new Set,updateGuards:new Set,enterCallbacks:{},components:"components"in e?e.components||null:e.component&&{default:e.component}}}(e);u.aliasOf=r&&r.record;const f=pe(t,e),p=[u];if("alias"in e){const t="string"==typeof e.alias?[e.alias]:e.alias;for(const e of t)p.push(a({},u,{components:r?r.record.components:u.components,path:e,aliasOf:r?r.record:u}))}let h,d;for(const t of p){const{path:a}=t;if(n&&"/"!==a[0]){const e=n.record.path,r="/"===e[e.length-1]?"":"/";t.path=n.record.path+(a&&r+a)}if(h=se(t,n,f),r?r.alias.push(h):(d=d||h,d!==h&&d.alias.push(h),i&&e.name&&!ue(h)&&c(e.name)),he(h)&&l(h),u.children){const e=u.children;for(let t=0;t<e.length;t++)o(e[t],h,r&&r.children[t])}r=r||h}return d?()=>{c(d)}:s}function c(e){if(K(e)){const t=r.get(e);t&&(r.delete(e),n.splice(n.indexOf(t),1),t.children.forEach(c),t.alias.forEach(c))}else{const t=n.indexOf(e);t>-1&&(n.splice(t,1),e.record.name&&r.delete(e.record.name),e.children.forEach(c),e.alias.forEach(c))}}function l(e){const t=function(e,t){let n=0,r=t.length;for(;n!==r;){const o=n+r>>1;re(e,t[o])<0?r=o:n=o+1}const o=function(e){let t=e;for(;t=t.parent;)if(he(t)&&0===re(e,t))return t}(e);return o&&(r=t.lastIndexOf(o,r-1)),r}(e,n);n.splice(t,0,e),e.record.name&&!ue(e)&&r.set(e.record.name,e)}return t=pe({strict:!1,end:!0,sensitive:!1},t),e.forEach((e=>o(e))),{addRoute:o,resolve:function(e,t){let o,c,s,l={};if("name"in e&&e.name){if(o=r.get(e.name),!o)throw Z(1,{location:e});s=o.record.name,l=a(le(t.params,o.keys.filter((e=>!e.optional)).concat(o.parent?o.parent.keys.filter((e=>e.optional)):[]).map((e=>e.name))),e.params&&le(e.params,o.keys.map((e=>e.name)))),c=o.stringify(l)}else if(null!=e.path)c=e.path,o=n.find((e=>e.re.test(c))),o&&(l=o.parse(c),s=o.record.name);else{if(o=t.name?r.get(t.name):n.find((e=>e.re.test(t.path))),!o)throw Z(1,{location:e,currentLocation:t});s=o.record.name,l=a({},t.params,e.params),c=o.stringify(l)}const i=[];let u=o;for(;u;)i.unshift(u.record),u=u.parent;return{name:s,path:c,params:l,matched:i,meta:fe(i)}},removeRoute:c,clearRoutes:function(){n.length=0,r.clear()},getRoutes:function(){return n},getRecordMatcher:function(e){return r.get(e)}}}(e.routes,e),n=e.parseQuery||de,i=e.stringifyQuery||me,u=e.history,f=ke(),p=ke(),h=ke(),d=(0,r.XI)(M);let m=M;o&&e.scrollBehavior&&"scrollRestoration"in history&&(history.scrollRestoration="manual");const g=c.bind(null,(e=>""+e)),y=c.bind(null,P),w=c.bind(null,C);function k(e,r){if(r=a({},r||d.value),"string"==typeof e){const o=j(n,e,r.path),c=t.resolve({path:o.path},r),s=u.createHref(o.fullPath);return a(o,c,{params:w(c.params),hash:C(o.hash),redirectedFrom:void 0,href:s})}let o;if(null!=e.path)o=a({},e,{path:j(n,e.path,r.path).path});else{const t=a({},e.params);for(const e in t)null==t[e]&&delete t[e];o=a({},e,{params:y(t)}),r.params=y(r.params)}const c=t.resolve(o,r),s=e.hash||"";c.params=g(w(c.params));const l=function(e,t){const n=t.query?e(t.query):"";return t.path+(n&&"?")+n+(t.hash||"")}(i,a({},e,{hash:(f=s,R(f).replace(b,"{").replace(E,"}").replace(v,"^")),path:c.path}));var f;const p=u.createHref(l);return a({fullPath:l,hash:s,query:i===me?ge(e.query):e.query||{}},c,{redirectedFrom:void 0,href:p})}function O(e){return"string"==typeof e?j(n,e,d.value.path):a({},e)}function x(e,t){if(m!==e)return Z(8,{from:t,to:e})}function S(e){return F(e)}function $(e){const t=e.matched[e.matched.length-1];if(t&&t.redirect){const{redirect:n}=t;let r="function"==typeof n?n(e):n;return"string"==typeof r&&(r=r.includes("?")||r.includes("#")?r=O(r):{path:r},r.params={}),a({query:e.query,hash:e.hash,params:null!=r.path?{}:e.params},r)}}function F(e,t){const n=m=k(e),r=d.value,o=e.state,c=e.force,s=!0===e.replace,l=$(n);if(l)return F(a(O(l),{state:"object"==typeof l?a({},o,l.state):o,force:c,replace:s}),t||n);const u=n;let f;return u.redirectedFrom=t,!c&&function(e,t,n){const r=t.matched.length-1,o=n.matched.length-1;return r>-1&&r===o&&q(t.matched[r],n.matched[o])&&A(t.params,n.params)&&e(t.query)===e(n.query)&&t.hash===n.hash}(i,r,n)&&(f=Z(16,{to:u,from:r}),N(r,r,!0,!1)),(f?Promise.resolve(f):G(u,r)).catch((e=>Q(e)?Q(e,2)?e:X(e):Y(e,u,r))).then((e=>{if(e){if(Q(e,2))return F(a({replace:s},O(e.to),{state:"object"==typeof e.to?a({},o,e.to.state):o,force:c}),t||u)}else e=J(u,r,!0,s,o);return I(u,r,e),e}))}function L(e,t){const n=x(e,t);return n?Promise.reject(n):Promise.resolve()}function B(e){const t=ne.values().next().value;return t&&"function"==typeof t.runWithContext?t.runWithContext(e):e()}function G(e,t){let n;const[r,o,a]=function(e,t){const n=[],r=[],o=[],a=Math.max(t.matched.length,e.matched.length);for(let c=0;c<a;c++){const a=t.matched[c];a&&(e.matched.find((e=>q(e,a)))?r.push(a):n.push(a));const s=e.matched[c];s&&(t.matched.find((e=>q(e,s)))||o.push(s))}return[n,r,o]}(e,t);n=Oe(r.reverse(),"beforeRouteLeave",e,t);for(const o of r)o.leaveGuards.forEach((r=>{n.push(Re(r,e,t))}));const c=L.bind(null,e,t);return n.push(c),ae(n).then((()=>{n=[];for(const r of f.list())n.push(Re(r,e,t));return n.push(c),ae(n)})).then((()=>{n=Oe(o,"beforeRouteUpdate",e,t);for(const r of o)r.updateGuards.forEach((r=>{n.push(Re(r,e,t))}));return n.push(c),ae(n)})).then((()=>{n=[];for(const r of a)if(r.beforeEnter)if(l(r.beforeEnter))for(const o of r.beforeEnter)n.push(Re(o,e,t));else n.push(Re(r.beforeEnter,e,t));return n.push(c),ae(n)})).then((()=>(e.matched.forEach((e=>e.enterCallbacks={})),n=Oe(a,"beforeRouteEnter",e,t,B),n.push(c),ae(n)))).then((()=>{n=[];for(const r of p.list())n.push(Re(r,e,t));return n.push(c),ae(n)})).catch((e=>Q(e,8)?e:Promise.reject(e)))}function I(e,t,n){h.list().forEach((r=>B((()=>r(e,t,n)))))}function J(e,t,n,r,c){const s=x(e,t);if(s)return s;const l=t===M,i=o?history.state:{};n&&(r||l?u.replace(e.fullPath,a({scroll:l&&i&&i.scroll},c)):u.push(e.fullPath,c)),d.value=e,N(e,t,n,l),X()}let T;let V,z=ke(),H=ke();function Y(e,t,n){X(e);const r=H.list();return r.length?r.forEach((r=>r(e,t,n))):console.error(e),Promise.reject(e)}function X(e){return V||(V=!e,T||(T=u.listen(((e,t,n)=>{if(!oe.listening)return;const r=k(e),c=$(r);if(c)return void F(a(c,{replace:!0}),r).catch(s);m=r;const l=d.value;var i,f;o&&(i=W(l.fullPath,n.delta),f=_(),D.set(i,f)),G(r,l).catch((e=>Q(e,12)?e:Q(e,2)?(F(e.to,r).then((e=>{Q(e,20)&&!n.delta&&n.type===U.pop&&u.go(-1,!1)})).catch(s),Promise.reject()):(n.delta&&u.go(-n.delta,!1),Y(e,r,l)))).then((e=>{(e=e||J(r,l,!1))&&(n.delta&&!Q(e,8)?u.go(-n.delta,!1):n.type===U.pop&&Q(e,20)&&u.go(-1,!1)),I(r,l,e)})).catch(s)}))),z.list().forEach((([t,n])=>e?n(e):t())),z.reset()),e}function N(t,n,a,c){const{scrollBehavior:s}=e;if(!o||!s)return Promise.resolve();const l=!a&&function(e){const t=D.get(e);return D.delete(e),t}(W(t.fullPath,0))||(c||!a)&&history.state&&history.state.scroll||null;return(0,r.Y3)().then((()=>s(t,n,l))).then((e=>e&&function(e){let t;if("el"in e){const n=e.el,r="string"==typeof n&&n.startsWith("#"),o="string"==typeof n?r?document.getElementById(n.slice(1)):document.querySelector(n):n;if(!o)return;t=function(e,t){const n=document.documentElement.getBoundingClientRect(),r=e.getBoundingClientRect();return{behavior:t.behavior,left:r.left-n.left-(t.left||0),top:r.top-n.top-(t.top||0)}}(o,e)}else t=e;"scrollBehavior"in document.documentElement.style?window.scrollTo(t):window.scrollTo(null!=t.left?t.left:window.scrollX,null!=t.top?t.top:window.scrollY)}(e))).catch((e=>Y(e,t,n)))}const ee=e=>u.go(e);let te;const ne=new Set,oe={currentRoute:d,listening:!0,addRoute:function(e,n){let r,o;return K(e)?(r=t.getRecordMatcher(e),o=n):o=e,t.addRoute(o,r)},removeRoute:function(e){const n=t.getRecordMatcher(e);n&&t.removeRoute(n)},clearRoutes:t.clearRoutes,hasRoute:function(e){return!!t.getRecordMatcher(e)},getRoutes:function(){return t.getRoutes().map((e=>e.record))},resolve:k,options:e,push:S,replace:function(e){return S(a(O(e),{replace:!0}))},go:ee,back:()=>ee(-1),forward:()=>ee(1),beforeEach:f.add,beforeResolve:p.add,afterEach:h.add,onError:H.add,isReady:function(){return V&&d.value!==M?Promise.resolve():new Promise(((e,t)=>{z.add([e,t])}))},install(e){e.component("RouterLink",Ce),e.component("RouterView",$e),e.config.globalProperties.$router=this,Object.defineProperty(e.config.globalProperties,"$route",{enumerable:!0,get:()=>(0,r.SU)(d)}),o&&!te&&d.value===M&&(te=!0,S(u.location).catch((e=>{})));const t={};for(const e in M)Object.defineProperty(t,e,{get:()=>d.value[e],enumerable:!0});e.provide(be,this),e.provide(we,(0,r.Um)(t)),e.provide(Ee,d);const n=e.unmount;ne.add(e),e.unmount=function(){ne.delete(e),ne.size<1&&(m=M,T&&T(),T=null,d.value=M,te=!1,V=!1),n()}}};function ae(e){return e.reduce(((e,t)=>e.then((()=>B(t)))),Promise.resolve())}return oe}function Ae(){return(0,r.f3)(be)}function Fe(e){return(0,r.f3)(we)}}}]);