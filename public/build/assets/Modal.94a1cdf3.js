import{o as d,d as n,e as l,a as i,h as o,r,Y as c,g as m,T as _}from"./app.9db95cc4.js";import"./laravel-vue-pagination.es.a1b74a32.js";import{a as u}from"./AuthenticatedLayout.f6a05ee4.js";const p={props:{show:Boolean,data:Object}},f={key:0,class:"modal-mask"},v={class:"modal-wrapper"},b={class:"modal-container dark:bg-gray-900"},h={class:"modal-header"},y=o("div",{class:"modal-body"},null,-1),k={class:"modal-footer my-2"},$={class:"flex flex-row"},g={class:"basis-1/2 px-4"},w={class:"basis-1/2 px-4"};function B(e,s,t,x,C,K){return d(),n(_,{name:"modal"},{default:l(()=>[t.show?(d(),i("div",f,[o("div",v,[o("div",b,[o("div",h,[r(e.$slots,"header")]),y,o("div",k,[o("div",$,[o("div",g,[o("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:s[0]||(s[0]=a=>{e.$emit("close")})},"\u062A\u0631\u0627\u062C\u0639")]),o("div",w,[o("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:s[1]||(s[1]=a=>{e.$emit("a",t.data)}),onKeyup:s[2]||(s[2]=c(a=>{e.$emit("a",t.data)},["enter"]))},"\u0646\u0639\u0645",32)])])])])])])):m("",!0)]),_:3})}const V=u(p,[["render",B]]);export{V as M};
