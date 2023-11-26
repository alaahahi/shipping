import{o as u,c as P,w as f,a as p,b as e,d as F,t as l,e as w,v as D,h as U,T as O,r as m,j as s,k as i,F as A,H as R,l as N,g as G,m as B,f as J,n as K,L as E}from"./app.8ae45477.js";import{a as Q,_ as W}from"./AuthenticatedLayout.efab7962.js";/* empty css                                              */import{t as X}from"./laravel-vue-pagination.es.2d4437f7.js";/* empty css                                                         */import{_ as h,a as H}from"./TextInput.1813167a.js";import{a as k}from"./index.fcfb0e97.js";import{s as Y}from"./show.d92a5a02.js";import"./trash.717a981b.js";import{e as Z}from"./edit.c649cdcf.js";import{M as ee}from"./ModalDelCar.64bed027.js";const te={key:0,class:"modal-mask"},oe={class:"modal-wrapper max-h-[80vh]"},ae={class:"modal-container dark:bg-gray-900 overflow-auto max-h-[80vh]"},se={class:"modal-header"},le={class:"modal-body"},re=e("h2",{class:"text-center dark:text-gray-200"}," \u062A\u0639\u062F\u064A\u0644 \u0632\u0628\u0648\u0646 ",-1),de={className:"mb-4 mx-5"},ne={class:"dark:text-gray-200",for:"name"},ie={className:"mb-4 mx-5"},ce={class:"dark:text-gray-200",for:"phone"},me={class:"modal-footer my-2"},ue={class:"flex flex-row"},pe={class:"basis-1/2 px-4"},be={class:"basis-1/2 px-4"},he=["disabled"],I={__name:"ModalEditClient",props:{show:Boolean,formData:Object},setup(c){return(r,d)=>(u(),P(O,{name:"modal"},{default:f(()=>[c.show?(u(),p("div",te,[e("div",oe,[e("div",ae,[e("div",se,[F(r.$slots,"header")]),e("div",le,[re,e("div",de,[e("label",ne,l(r.$t("name")),1),w(e("input",{id:"name",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":d[0]||(d[0]=n=>c.formData.name=n)},null,512),[[D,c.formData.name]])]),e("div",ie,[e("label",ce,l(r.$t("phone")),1),w(e("input",{id:"phone",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":d[1]||(d[1]=n=>c.formData.phone=n)},null,512),[[D,c.formData.phone]])])]),e("div",me,[e("div",ue,[e("div",pe,[e("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:d[2]||(d[2]=n=>{r.$emit("close")})},l(r.$t("cancel")),1)]),e("div",be,[e("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:d[3]||(d[3]=n=>{r.$emit("a",c.formData),c.formData=""}),disabled:!c.formData.name},l(r.$t("yes")),9,he)])])])])])])):U("",!0)]),_:3}))}},fe={},ge={xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24","stroke-width":"1.5",stroke:"currentColor",class:"w-6 h-6"},_e=e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3"},null,-1),ye=[_e];function ve(c,r){return u(),p("svg",ge,ye)}const xe=Q(fe,[["render",ve]]);const ke={class:"mb-5 dark:text-white text-center"},we={class:"py-12"},$e={class:"mx-auto sm:px-6 lg:px-8"},Ce={class:"bg-white overflow-hidden shadow-sm sm:rounded-lg"},Ne={class:"p-6 dark:bg-gray-900"},De={class:"grid grid-cols-2 md:grid-cols-5 lg:grid-cols-7 gap-2 lg:gap-1"},Me={class:"flex items-center max-w-5xl"},Ve=e("label",{class:"dark:text-gray-200",for:"simple-search"},null,-1),Ae={class:"relative w-full"},Be=e("div",{class:"absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"},[e("svg",{"aria-hidden":"true",class:"w-5 h-5 text-gray-500 dark:text-gray-200 dark:text-gray-400",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{"fill-rule":"evenodd",d:"M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z","clip-rule":"evenodd"})])],-1),Ee={value:"0"},He=e("option",{value:"debit"},"\u064A\u0648\u062C\u062F \u062F\u064A\u0646",-1),Ie={class:"text-center px-4"},Ue={class:"px-4"},ze={className:"mb-4"},Le={class:"px-4"},Se={className:"mb-4"},Te={className:"mb-4  mr-5 print:hidden"},je={key:0},qe={key:1},Pe={className:"mb-4  mr-5 print:hidden"},Fe={key:0},Oe={key:1},Re={class:"relative overflow-x-auto shadow-md sm:rounded-lg"},Ge={class:"w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center"},Je={class:"text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center"},Ke={class:"rounded-l-lg mb-2 sm:mb-0"},Qe=e("th",{className:"px-1 py-2 text-base"},"#",-1),We={className:"px-1 py-2 text-base"},Xe={className:"px-1 py-2 text-base"},Ye=e("th",{className:"px-1 py-2 text-base"},"\u0645\u062C\u0645\u0648\u0639 \u0627\u0644\u0633\u064A\u0627\u0631\u0627\u062A \u063A\u064A\u0631 \u0645\u0643\u062A\u0645\u0644",-1),Ze=e("th",{className:"px-1 py-2 text-base"},"\u0645\u062C\u0645\u0648\u0639 \u0627\u0644\u0633\u064A\u0627\u0631\u0627\u062A \u0645\u0643\u062A\u0645\u0644",-1),et={className:"px-1 py-2 text-base"},tt={className:"px-1 py-2 text-base"},ot={class:"flex-1 sm:flex-none dark:bg-gray-700 dark:text-gray-200"},at={className:"border border-white  dark:border-gray-800 text-center px-4 py-2"},st={className:"border border-white dark:border-gray-800 text-center  dark:text-gray-200 text-black px-1 py-2 ",style:{"font-weight":"bold","font-size":"16px"}},lt={className:"border border-white  dark:border-gray-800 text-center px-4 py-2"},rt={className:"border border-white  dark:border-gray-800 text-center px-4 py-2"},dt={className:"border border-white  dark:border-gray-800 text-center px-4 py-2"},nt={className:"border border-white  dark:border-gray-800 text-center px-4 py-2"},it={className:"border border-white  dark:border-gray-800 text-center px-4 py-2",style:{"min-height":"42px"}},ct=["onClick"];const mt={class:"mt-3 text-center",style:{direction:"ltr"}},Ct={__name:"Index",setup(c){let r=m(!1),d=m(!1),n=m(!1);const v=m([]);let b=m({}),z=m(0);const $=m(0),C=m(0),g=m(""),M=m(0),_=async(a=1)=>{k.get(`/getIndexClients?page=${a}&user_id=${z.value}&from=${$.value}&to=${C.value}&q=${g.value}`).then(t=>{var o;try{v.value=(o=t.data.data)==null?void 0:o.sort((y,x)=>{const V=x.wallet.balance-y.wallet.balance;return V===0?x.car_total_uncomplete-y.car_total_uncomplete:V})}catch{v.value=t.data.data}}).catch(t=>{console.error(t)})};_();function L(a={}){b.value=a,d.value=!0}function S(a={}){b.value=a,r.value=!0}function T(a){k.post("/api/clientsStore",a).then(t=>{window.location.reload()}).catch(t=>{console.error(t)})}function j(a){k.post("/api/clientsEdit",a).then(t=>{window.location.reload()}).catch(t=>{console.error(t)})}function ut(a={}){b.value=a,n.value=!0}function q(a){k.post("/api/delClient",a).then(t=>{n.value=!1,_()}).catch(t=>{console.error(t)})}return(a,t)=>(u(),p(A,null,[s(i(R),{title:"Dashboard"}),s(W,null,{default:f(()=>[s(I,{show:i(d),formData:i(b),onA:t[0]||(t[0]=o=>T(o)),onClose:t[1]||(t[1]=o=>N(d)?d.value=!1:d=!1)},{header:f(()=>[]),_:1},8,["show","formData"]),s(ee,{show:!!i(n),formData:i(b),onA:t[2]||(t[2]=o=>q(o)),onClose:t[3]||(t[3]=o=>N(n)?n.value=!1:n=!1)},{header:f(()=>[e("h2",ke," \u0647\u0644 \u0645\u062A\u0623\u0643\u062F \u0645\u0646 \u062D\u0630\u0641 \u0627\u0644\u062A\u0627\u062C\u0631 "+l(i(b).name)+" \u061F ",1)]),_:1},8,["show","formData"]),s(I,{show:i(r),formData:i(b),onA:t[4]||(t[4]=o=>j(o)),onClose:t[5]||(t[5]=o=>N(r)?r.value=!1:r=!1)},{header:f(()=>[]),_:1},8,["show","formData"]),e("div",we,[e("div",$e,[e("div",Ce,[e("div",Ne,[e("div",De,[e("div",null,[s(h,{for:"from",value:a.$t("search"),class:"mb-1"},null,8,["value"]),e("form",Me,[Ve,e("div",Ae,[Be,w(e("input",{"onUpdate:modelValue":t[6]||(t[6]=o=>g.value=o),onInput:t[7]||(t[7]=o=>_(g.value)),type:"text",id:"simple-search",class:"bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500",placeholder:"\u0628\u062D\u062B",required:""},null,544),[[D,g.value]])])])]),e("div",null,[s(h,{for:"from",value:"\u062A\u062D\u062F\u064A\u062F \u0627\u0644\u0641\u0626\u0629",class:"mb-1"}),w(e("select",{onChange:t[8]||(t[8]=o=>_()),"onUpdate:modelValue":t[9]||(t[9]=o=>g.value=o),id:"default",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 mb-6 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500"},[e("option",Ee,l(a.$t("allOwners")),1),He],544),[[G,g.value]])]),e("div",Ie,[s(h,{for:"pay",value:"\u0627\u0636\u0627\u0641\u0629",class:"mb-1"}),e("button",{className:"px-6 mb-12 py-2 mt-1 font-bold text-white bg-red-500 rounded",onClick:t[10]||(t[10]=o=>L())},l(a.$t("addCustomer")),1)]),e("div",Ue,[e("div",ze,[s(h,{for:"from",value:a.$t("from_date")},null,8,["value"]),s(H,{id:"from",type:"date",class:"mt-1 block w-full",modelValue:$.value,"onUpdate:modelValue":t[11]||(t[11]=o=>$.value=o)},null,8,["modelValue"])])]),e("div",Le,[e("div",Se,[s(h,{for:"to",value:a.$t("to_date")},null,8,["value"]),s(H,{id:"to",type:"date",class:"mt-1 block w-full",modelValue:C.value,"onUpdate:modelValue":t[12]||(t[12]=o=>C.value=o)},null,8,["modelValue"])])]),e("div",Te,[s(h,{for:"pay",value:"\u0641\u0644\u062A\u0631\u0629"}),e("button",{onClick:t[13]||(t[13]=B(o=>_(),["prevent"])),class:"px-6 mb-12 py-2 mt-1 font-bold text-white bg-gray-500 rounded",style:{width:"100%"}},[M.value?(u(),p("span",qe,"\u062C\u0627\u0631\u064A \u0627\u0644\u062D\u0641\u0638...")):(u(),p("span",je,"\u0641\u0644\u062A\u0631\u0629"))])]),e("div",Pe,[s(h,{for:"pay",value:"\u0637\u0628\u0627\u0639\u0629"}),e("button",{onClick:t[14]||(t[14]=B(o=>a.confirmAddPaymentTotal(a.total,a.client_id),["prevent"])),class:"px-6 mb-12 py-2 mt-1 font-bold text-white bg-orange-500 rounded",style:{width:"100%"}},[M.value?(u(),p("span",Oe,"\u062C\u0627\u0631\u064A \u0627\u0644\u062D\u0641\u0638...")):(u(),p("span",Fe,"\u0637\u0628\u0627\u0639\u0629"))])])]),e("div",Re,[e("table",Ge,[e("thead",Je,[e("tr",Ke,[Qe,e("th",We,l(a.$t("name")),1),e("th",Xe,l(a.$t("phoneNumber")),1),Ye,Ze,e("th",et,l(a.$t("debt")),1),e("th",tt,l(a.$t("execute")),1)])]),e("tbody",ot,[(u(!0),p(A,null,J(v.value,(o,y)=>(u(),p("tr",{key:o.id,class:K(["border-b border-white dark:bg-gray-900 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-600",o.car_total_uncomplete<=0?"bg-green-100 dark:bg-green-900":"bg-red-100 dark:bg-red-900"])},[e("td",at,l(y),1),e("td",st,l(o.name),1),e("td",lt,l(o.phone),1),e("td",rt,l(o.car_total_uncomplete),1),e("td",dt,l(o.car_total_complete),1),e("td",nt,l(o.wallet?"$"+o.wallet.balance:0),1),e("td",it,[s(i(E),{style:{display:"inline-flex"},className:"px-1 py-1  text-white mx-1 bg-blue-500 rounded d-inline-block",href:a.route("showClients",o.id)},{default:f(()=>[s(Y)]),_:2},1032,["href"]),e("button",{tabIndex:"1",class:"px-1 py-1 text-white mx-1 bg-slate-500 rounded",onClick:x=>S(o)},[s(Z)],8,ct),U("",!0),s(i(E),{style:{display:"inline-flex"},className:"px-1 py-1  text-white mx-1 bg-purple-900 rounded d-inline-block",href:a.route("wallet",{id:o.id})},{default:f(()=>[s(xe)]),_:2},1032,["href"])])],2))),128))])])]),e("div",mt,[s(i(X),{data:v.value,onPaginationChangePage:_,limit:10},null,8,["data"])])])])])])]),_:1})],64))}};export{Ct as default};
