import{o as t,c as n,w as l,a as r,b as o,d as i,h as c,T as _}from"./app.12c4a53d.js";import{_ as m}from"./AuthenticatedLayout.4115f61a.js";const v={props:{show:Boolean,data:String}},u={key:0,class:"modal-mask"},h={class:"modal-wrapper max-h-[80vh]"},p={class:"modal-container dark:bg-gray-900 overflow-auto max-h-[80vh]"},f={class:"modal-header"},b={class:"modal-footer my-2"},k={class:"flex flex-row"},x={class:"basis-1/2 px-4"},y={class:"basis-1/2 px-4"};function g(e,s,a,w,$,B){return t(),n(_,{name:"modal"},{default:l(()=>[a.show?(t(),r("div",u,[o("div",h,[o("div",p,[o("div",f,[i(e.$slots,"header")]),o("div",b,[o("div",k,[o("div",x,[o("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:s[0]||(s[0]=d=>{e.$emit("close")})},"\u062A\u0631\u0627\u062C\u0639")]),o("div",y,[o("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:s[1]||(s[1]=d=>{e.$emit("a",a.data)})},"\u0646\u0639\u0645")])])])])])])):c("",!0)]),_:3})}const M=m(v,[["render",g]]);export{M};
