import{o as t,c as l,w as n,a as r,b as o,d as i,h as c,T as m}from"./app.135f5568.js";import{a as _}from"./AuthenticatedLayout.f17e9bd4.js";const u={props:{show:Boolean,formData:Object}},f={key:0,class:"modal-mask"},h={class:"modal-wrapper max-h-[80vh]"},v={class:"modal-container dark:bg-gray-900 overflow-auto max-h-[80vh]"},p={class:"modal-header"},b={class:"modal-footer my-2"},k={class:"flex flex-row"},x={class:"basis-1/2 px-4"},y={class:"basis-1/2 px-4"};function w(e,s,a,$,g,C){return t(),l(m,{name:"modal"},{default:n(()=>[a.show?(t(),r("div",f,[o("div",h,[o("div",v,[o("div",p,[i(e.$slots,"header")]),o("div",b,[o("div",k,[o("div",x,[o("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:s[0]||(s[0]=d=>{e.$emit("close")})},"\u062A\u0631\u0627\u062C\u0639")]),o("div",y,[o("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:s[1]||(s[1]=d=>{e.$emit("a",a.formData)})},"\u0646\u0639\u0645")])])])])])])):c("",!0)]),_:3})}const M=_(u,[["render",w]]);export{M};
