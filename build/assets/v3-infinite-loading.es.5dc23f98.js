import{B as C,r as v,C as L,D as $,m as D,p as H,o as w,a as E,e as N,s as M,b as s,d as u,g as O,t as g,i as y,E as h,G as R,I as T}from"./app.31e42467.js";function V(e,o){const n=e.getBoundingClientRect();if(!o)return n.top>=0&&n.bottom<=window.innerHeight;const t=o.getBoundingClientRect();return n.top>=t.top&&n.bottom<=t.bottom}async function q(e){return await h(),e.value instanceof HTMLElement?e.value:e.value?document.querySelector(e.value):null}function x(e){let o=`0px 0px ${e.distance}px 0px`;e.top&&(o=`${e.distance}px 0px 0px 0px`);const n=new IntersectionObserver(t=>{t[0].isIntersecting&&(e.firstload&&e.emit(),e.firstload=!0)},{root:e.parentEl,rootMargin:o});return n.observe(e.infiniteLoading.value),n}const I=(e,o)=>{const n=e.__vccOpts||e;for(const[t,c]of o)n[t]=c;return n},G={},U=e=>(R("data-v-d3e37633"),e=e(),T(),e),j={class:"container"},z=U(()=>s("div",{class:"spinner"},null,-1)),A=[z];function F(e,o){return w(),E("div",j,A)}const J=I(G,[["render",F],["__scopeId","data-v-d3e37633"]]),K={class:"state-error"},P=C({__name:"InfiniteLoading",props:{top:{type:Boolean,default:!1},target:{},distance:{default:0},identifier:{},firstload:{type:Boolean,default:!0},slots:{}},emits:["infinite"],setup(e,{emit:o}){const n=e;let t=null,c=0;const d=v(null),i=v(""),{top:p,firstload:k,distance:B}=n,{identifier:_,target:b}=L(n),a={infiniteLoading:d,top:p,firstload:k,distance:B,parentEl:null,emit(){c=(a.parentEl||document.documentElement).scrollHeight,m.loading(),o("infinite",m)}},m={loading(){i.value="loading"},async loaded(){i.value="loaded";const r=a.parentEl||document.documentElement;await h(),p&&(r.scrollTop=r.scrollHeight-c),V(d.value,a.parentEl)&&a.emit()},complete(){i.value="complete",t==null||t.disconnect()},error(){i.value="error"}};return $(_,()=>{t==null||t.disconnect(),t=x(a)}),D(async()=>{a.parentEl=await q(b),t=x(a)}),H(()=>{t==null||t.disconnect()}),(r,f)=>(w(),E("div",{ref_key:"infiniteLoading",ref:d,style:{"min-height":"1px"}},[N(s("div",null,[u(r.$slots,"spinner",{},()=>[O(J)],!0)],512),[[M,i.value=="loading"]]),i.value=="complete"?u(r.$slots,"complete",{key:0},()=>{var l;return[s("span",null,g(((l=r.slots)==null?void 0:l.complete)||"No more results!"),1)]},!0):y("",!0),i.value=="error"?u(r.$slots,"error",{key:1,retry:a.emit},()=>{var l;return[s("span",K,[s("span",null,g(((l=r.slots)==null?void 0:l.error)||"Oops something went wrong!"),1),s("button",{class:"retry",onClick:f[0]||(f[0]=(...S)=>a.emit&&a.emit(...S))},"retry")])]},!0):y("",!0)],512))}}),Q=I(P,[["__scopeId","data-v-a7077831"]]);export{Q as W};
