import{B as K,r as k,C as X,D as Q,q as Y,s as Z,o as P,a as A,e as ee,y as te,b as y,d as h,j as ne,t as w,f as L,E as D,G as re,I as ie,J as x}from"./app.ba6aa00c.js";function oe(e,t){const n=e.getBoundingClientRect();if(!t)return n.top>=0&&n.bottom<=window.innerHeight;const r=t.getBoundingClientRect();return n.top>=r.top&&n.bottom<=r.bottom}async function ae(e){return await D(),e.value instanceof HTMLElement?e.value:e.value?document.querySelector(e.value):null}function B(e){let t=`0px 0px ${e.distance}px 0px`;e.top&&(t=`${e.distance}px 0px 0px 0px`);const n=new IntersectionObserver(r=>{r[0].isIntersecting&&(e.firstload&&e.emit(),e.firstload=!0)},{root:e.parentEl,rootMargin:t});return n.observe(e.infiniteLoading.value),n}const H=(e,t)=>{const n=e.__vccOpts||e;for(const[r,c]of t)n[r]=c;return n},se={},ce=e=>(re("data-v-d3e37633"),e=e(),ie(),e),le={class:"container"},de=ce(()=>y("div",{class:"spinner"},null,-1)),ue=[de];function fe(e,t){return P(),A("div",le,ue)}const me=H(se,[["render",fe],["__scopeId","data-v-d3e37633"]]),pe={class:"state-error"},ve=K({__name:"InfiniteLoading",props:{top:{type:Boolean,default:!1},target:{},distance:{default:0},identifier:{},firstload:{type:Boolean,default:!0},slots:{}},emits:["infinite"],setup(e,{emit:t}){const n=e;let r=null,c=0;const d=k(null),a=k(""),{top:o,firstload:u,distance:p}=n,{identifier:j,target:v}=X(n),s={infiniteLoading:d,top:o,firstload:u,distance:p,parentEl:null,emit(){c=(s.parentEl||document.documentElement).scrollHeight,g.loading(),t("infinite",g)}},g={loading(){a.value="loading"},async loaded(){a.value="loaded";const l=s.parentEl||document.documentElement;await D(),o&&(l.scrollTop=l.scrollHeight-c),oe(d.value,s.parentEl)&&s.emit()},complete(){a.value="complete",r==null||r.disconnect()},error(){a.value="error"}};return Q(j,()=>{r==null||r.disconnect(),r=B(s)}),Y(async()=>{s.parentEl=await ae(v),r=B(s)}),Z(()=>{r==null||r.disconnect()}),(l,$)=>(P(),A("div",{ref_key:"infiniteLoading",ref:d,style:{"min-height":"1px"}},[ee(y("div",null,[h(l.$slots,"spinner",{},()=>[ne(me)],!0)],512),[[te,a.value=="loading"]]),a.value=="complete"?h(l.$slots,"complete",{key:0},()=>{var f;return[y("span",null,w(((f=l.slots)==null?void 0:f.complete)||"No more results!"),1)]},!0):L("",!0),a.value=="error"?h(l.$slots,"error",{key:1,retry:s.emit},()=>{var f;return[y("span",pe,[y("span",null,w(((f=l.slots)==null?void 0:f.error)||"Oops something went wrong!"),1),y("button",{class:"retry",onClick:$[0]||($[0]=(...b)=>s.emit&&s.emit(...b))},"retry")])]},!0):L("",!0)],512))}}),vt=H(ve,[["__scopeId","data-v-a7077831"]]);function ge(e){var t=typeof e;return e!=null&&(t=="object"||t=="function")}var F=ge,be=typeof x=="object"&&x&&x.Object===Object&&x,ye=be,Te=ye,Se=typeof self=="object"&&self&&self.Object===Object&&self,je=Te||Se||Function("return this")(),U=je,$e=U,xe=function(){return $e.Date.now()},Oe=xe,he=/\s/;function _e(e){for(var t=e.length;t--&&he.test(e.charAt(t)););return t}var Ie=_e,Ee=Ie,ke=/^\s+/;function we(e){return e&&e.slice(0,Ee(e)+1).replace(ke,"")}var Le=we,Be=U,Ne=Be.Symbol,q=Ne,N=q,V=Object.prototype,Ce=V.hasOwnProperty,Ge=V.toString,S=N?N.toStringTag:void 0;function Re(e){var t=Ce.call(e,S),n=e[S];try{e[S]=void 0;var r=!0}catch{}var c=Ge.call(e);return r&&(t?e[S]=n:delete e[S]),c}var Me=Re,We=Object.prototype,Pe=We.toString;function Ae(e){return Pe.call(e)}var De=Ae,C=q,He=Me,Fe=De,Ue="[object Null]",qe="[object Undefined]",G=C?C.toStringTag:void 0;function Ve(e){return e==null?e===void 0?qe:Ue:G&&G in Object(e)?He(e):Fe(e)}var Je=Ve;function ze(e){return e!=null&&typeof e=="object"}var Ke=ze,Xe=Je,Qe=Ke,Ye="[object Symbol]";function Ze(e){return typeof e=="symbol"||Qe(e)&&Xe(e)==Ye}var et=Ze,tt=Le,R=F,nt=et,M=0/0,rt=/^[-+]0x[0-9a-f]+$/i,it=/^0b[01]+$/i,ot=/^0o[0-7]+$/i,at=parseInt;function st(e){if(typeof e=="number")return e;if(nt(e))return M;if(R(e)){var t=typeof e.valueOf=="function"?e.valueOf():e;e=R(t)?t+"":t}if(typeof e!="string")return e===0?e:+e;e=tt(e);var n=it.test(e);return n||ot.test(e)?at(e.slice(2),n?2:8):rt.test(e)?M:+e}var ct=st,lt=F,_=Oe,W=ct,dt="Expected a function",ut=Math.max,ft=Math.min;function mt(e,t,n){var r,c,d,a,o,u,p=0,j=!1,v=!1,s=!0;if(typeof e!="function")throw new TypeError(dt);t=W(t)||0,lt(n)&&(j=!!n.leading,v="maxWait"in n,d=v?ut(W(n.maxWait)||0,t):d,s="trailing"in n?!!n.trailing:s);function g(i){var m=r,T=c;return r=c=void 0,p=i,a=e.apply(T,m),a}function l(i){return p=i,o=setTimeout(b,t),j?g(i):a}function $(i){var m=i-u,T=i-p,E=t-m;return v?ft(E,d-T):E}function f(i){var m=i-u,T=i-p;return u===void 0||m>=t||m<0||v&&T>=d}function b(){var i=_();if(f(i))return I(i);o=setTimeout(b,$(i))}function I(i){return o=void 0,s&&r?g(i):(r=c=void 0,a)}function J(){o!==void 0&&clearTimeout(o),p=0,r=u=c=o=void 0}function z(){return o===void 0?a:I(_())}function O(){var i=_(),m=f(i);if(r=arguments,c=this,u=i,m){if(o===void 0)return l(u);if(v)return clearTimeout(o),o=setTimeout(b,t),g(u)}return o===void 0&&(o=setTimeout(b,t)),a}return O.cancel=J,O.flush=z,O}var gt=mt;export{vt as W,gt as d};