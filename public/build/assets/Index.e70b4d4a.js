import{o as u,c as P,w as g,a as p,b as e,d as W,t as l,e as x,v as M,i as U,T as G,r as i,g as s,j as c,F as A,H as J,k as D,z as K,l as B,h as Q,n as X,L as E}from"./app.c70f1507.js";import{a as Y,_ as Z}from"./AuthenticatedLayout.1f32cf51.js";/* empty css                                              *//* empty css                                                         */import{_ as b}from"./InputLabel.05f00cdd.js";import{_ as H}from"./TextInput.c63d8366.js";import{a as y}from"./index.dabc264a.js";import{s as ee}from"./show.ed102e38.js";import"./trash.705b0bff.js";import{e as te}from"./edit.a9f41e3d.js";import{M as oe}from"./ModalDelCar.d81e8ad5.js";import{W as ae}from"./v3-infinite-loading.es.beaa6117.js";/* empty css              *//* empty css                                                    */const se={key:0,class:"modal-mask"},le={class:"modal-wrapper max-h-[80vh]"},re={class:"modal-container dark:bg-gray-900 overflow-auto max-h-[80vh]"},de={class:"modal-header"},ne={class:"modal-body"},ie=e("h2",{class:"text-center dark:text-gray-200"}," \u062A\u0639\u062F\u064A\u0644 \u0632\u0628\u0648\u0646 ",-1),ce={className:"mb-4 mx-5"},me={class:"dark:text-gray-200",for:"name"},ue={className:"mb-4 mx-5"},pe={class:"dark:text-gray-200",for:"phone"},he={class:"modal-footer my-2"},be={class:"flex flex-row"},ge={class:"basis-1/2 px-4"},fe={class:"basis-1/2 px-4"},_e=["disabled"],I={__name:"ModalEditClient",props:{show:Boolean,formData:Object},setup(m){return(r,d)=>(u(),P(G,{name:"modal"},{default:g(()=>[m.show?(u(),p("div",se,[e("div",le,[e("div",re,[e("div",de,[W(r.$slots,"header")]),e("div",ne,[ie,e("div",ce,[e("label",me,l(r.$t("name")),1),x(e("input",{id:"name",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":d[0]||(d[0]=n=>m.formData.name=n)},null,512),[[M,m.formData.name]])]),e("div",ue,[e("label",pe,l(r.$t("phone")),1),x(e("input",{id:"phone",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":d[1]||(d[1]=n=>m.formData.phone=n)},null,512),[[M,m.formData.phone]])])]),e("div",he,[e("div",be,[e("div",ge,[e("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:d[2]||(d[2]=n=>{r.$emit("close")})},l(r.$t("cancel")),1)]),e("div",fe,[e("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:d[3]||(d[3]=n=>{r.$emit("a",m.formData),m.formData=""}),disabled:!m.formData.name},l(r.$t("yes")),9,_e)])])])])])])):U("",!0)]),_:3}))}},ve={},ye={xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24","stroke-width":"1.5",stroke:"currentColor",class:"w-6 h-6"},xe=e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3"},null,-1),ke=[xe];function we(m,r){return u(),p("svg",ye,ke)}const $e=Y(ve,[["render",we]]);const Ce={class:"mb-5 dark:text-white text-center"},Ne={class:"py-12"},De={class:"mx-auto sm:px-6 lg:px-8"},Me={class:"bg-white overflow-hidden shadow-sm sm:rounded-lg"},Ve={class:"p-6 dark:bg-gray-900"},Ae={class:"grid grid-cols-2 md:grid-cols-5 lg:grid-cols-7 gap-2 lg:gap-1"},Be={class:"flex items-center max-w-5xl"},Ee=e("label",{class:"dark:text-gray-200",for:"simple-search"},null,-1),He={class:"relative w-full"},Ie=e("div",{class:"absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"},[e("svg",{"aria-hidden":"true",class:"w-5 h-5 text-gray-500 dark:text-gray-200 dark:text-gray-400",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{"fill-rule":"evenodd",d:"M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z","clip-rule":"evenodd"})])],-1),Ue={value:"0"},ze=e("option",{value:"debit"},"\u064A\u0648\u062C\u062F \u062F\u064A\u0646",-1),je={class:"text-center px-4"},Le={class:"px-4"},Re={className:"mb-4"},Se={class:"px-4"},Te={className:"mb-4"},qe={className:"mb-4  mr-5 print:hidden"},Fe={key:0},Oe={key:1},Pe={className:"mb-4  mr-5 print:hidden"},We={key:0},Ge={key:1},Je={class:"relative overflow-x-auto shadow-md sm:rounded-lg"},Ke={class:"w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center"},Qe={class:"text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center"},Xe={class:"rounded-l-lg mb-2 sm:mb-0"},Ye=e("th",{className:"px-1 py-2 text-base"},"#",-1),Ze={className:"px-1 py-2 text-base"},et={className:"px-1 py-2 text-base"},tt=e("th",{className:"px-1 py-2 text-base"},"\u0645\u062C\u0645\u0648\u0639 \u0627\u0644\u0633\u064A\u0627\u0631\u0627\u062A \u063A\u064A\u0631 \u0645\u062F\u0641\u0648\u0639",-1),ot=e("th",{className:"px-1 py-2 text-base"},"\u0645\u062C\u0645\u0648\u0639 \u0627\u0644\u0633\u064A\u0627\u0631\u0627\u062A \u0645\u062F\u0641\u0648\u0639",-1),at=e("th",{className:"px-1 py-2 text-base"},"\u0627\u0644\u0639\u0642\u0648\u062F \u0627\u0644\u0627\u0644\u0643\u062A\u0631\u0648\u0646\u064A\u0629 \u0627\u0644\u0645\u0646\u062C\u0632\u0629",-1),st=e("th",{className:"px-1 py-2 text-base"},"\u0627\u0644\u0639\u0642\u0648\u062F \u0627\u0644\u0627\u0644\u0643\u062A\u0631\u0648\u0646\u064A\u0629 \u063A\u064A\u0631 \u0627\u0644\u0645\u0646\u062C\u0632\u0629",-1),lt={className:"px-1 py-2 text-base"},rt={className:"px-1 py-2 text-base"},dt={class:"flex-1 sm:flex-none dark:bg-gray-700 dark:text-gray-200"},nt={className:"border border-white  dark:border-gray-800 text-center px-4 py-2"},it={className:"border border-white dark:border-gray-800 text-center  dark:text-gray-200 text-black px-1 py-2 ",style:{"font-weight":"bold","font-size":"16px"}},ct={className:"border border-white  dark:border-gray-800 text-center px-4 py-2"},mt={className:"border border-white  dark:border-gray-800 text-center px-4 py-2"},ut={className:"border border-white  dark:border-gray-800 text-center px-4 py-2"},pt={className:"border border-white  dark:border-gray-800 text-center px-4 py-2"},ht={className:"border border-white  dark:border-gray-800 text-center px-4 py-2"},bt={className:"border border-white  dark:border-gray-800 text-center px-4 py-2"},gt={className:"border border-white  dark:border-gray-800 text-center px-4 py-2",style:{"min-height":"42px"}},ft=["onClick"];const _t={class:"mt-3 text-center",style:{direction:"ltr"}},vt={class:"spaner"},zt={__name:"Index",setup(m){let r=i(!1),d=i(!1),n=i(!1);const f=i([]);let h=i({}),z=i(0);const k=i(0),w=i(0),_=i(""),V=i(0);let $=i(!1),C=1,v=i({});const N=()=>{C=0,f.value.length=0,$.value=!$.value},j=async a=>{console.log(a);try{const t=await y.get("/getIndexClients",{params:{limit:100,page:C,q:_.value,user_id:z.value,from:k.value,to:w.value}});v.value=t.data,v.value.data.length<100?(f.value.push(...v.value.data),a.complete()):(f.value.push(...v.value.data),a.loaded()),C++}catch(t){console.log(t)}};N();function L(a={}){h.value=a,d.value=!0}function R(a={}){h.value=a,r.value=!0}function S(a){y.post("/api/clientsStore",a).then(t=>{window.location.reload()}).catch(t=>{console.error(t)})}function T(a){y.post("/api/clientsEdit",a).then(t=>{window.location.reload()}).catch(t=>{console.error(t)})}function yt(a={}){h.value=a,n.value=!0}function q(a){y.post("/api/delClient",a).then(t=>{n.value=!1,getResults()}).catch(t=>{console.error(t)})}return(a,t)=>(u(),p(A,null,[s(c(J),{title:"Dashboard"}),s(Z,null,{default:g(()=>[s(I,{show:c(d),formData:c(h),onA:t[0]||(t[0]=o=>S(o)),onClose:t[1]||(t[1]=o=>D(d)?d.value=!1:d=!1)},{header:g(()=>[]),_:1},8,["show","formData"]),s(oe,{show:!!c(n),formData:c(h),onA:t[2]||(t[2]=o=>q(o)),onClose:t[3]||(t[3]=o=>D(n)?n.value=!1:n=!1)},{header:g(()=>[e("h2",Ce," \u0647\u0644 \u0645\u062A\u0623\u0643\u062F \u0645\u0646 \u062D\u0630\u0641 \u0627\u0644\u062A\u0627\u062C\u0631 "+l(c(h).name)+" \u061F ",1)]),_:1},8,["show","formData"]),s(I,{show:c(r),formData:c(h),onA:t[4]||(t[4]=o=>T(o)),onClose:t[5]||(t[5]=o=>D(r)?r.value=!1:r=!1)},{header:g(()=>[]),_:1},8,["show","formData"]),e("div",Ne,[e("div",De,[e("div",Me,[e("div",Ve,[e("div",Ae,[e("div",null,[s(b,{for:"from",value:a.$t("search"),class:"mb-1"},null,8,["value"]),e("form",Be,[Ee,e("div",He,[Ie,x(e("input",{"onUpdate:modelValue":t[6]||(t[6]=o=>_.value=o),onInput:t[7]||(t[7]=o=>N()),type:"text",id:"simple-search",class:"bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500",placeholder:"\u0628\u062D\u062B",required:""},null,544),[[M,_.value]])])])]),e("div",null,[s(b,{for:"from",value:"\u062A\u062D\u062F\u064A\u062F \u0627\u0644\u0641\u0626\u0629",class:"mb-1"}),x(e("select",{onChange:t[8]||(t[8]=o=>a.getResults()),"onUpdate:modelValue":t[9]||(t[9]=o=>_.value=o),id:"default",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 mb-6 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500"},[e("option",Ue,l(a.$t("allOwners")),1),ze],544),[[K,_.value]])]),e("div",je,[s(b,{for:"pay",value:"\u0627\u0636\u0627\u0641\u0629",class:"mb-1"}),e("button",{className:"px-6 mb-12 py-2 mt-1 font-bold text-white bg-red-500 rounded",onClick:t[10]||(t[10]=o=>L())},l(a.$t("addCustomer")),1)]),e("div",Le,[e("div",Re,[s(b,{for:"from",value:a.$t("from_date")},null,8,["value"]),s(H,{id:"from",type:"date",class:"mt-1 block w-full",modelValue:k.value,"onUpdate:modelValue":t[11]||(t[11]=o=>k.value=o)},null,8,["modelValue"])])]),e("div",Se,[e("div",Te,[s(b,{for:"to",value:a.$t("to_date")},null,8,["value"]),s(H,{id:"to",type:"date",class:"mt-1 block w-full",modelValue:w.value,"onUpdate:modelValue":t[12]||(t[12]=o=>w.value=o)},null,8,["modelValue"])])]),e("div",qe,[s(b,{for:"pay",value:"\u0641\u0644\u062A\u0631\u0629"}),e("button",{onClick:t[13]||(t[13]=B(o=>N(),["prevent"])),class:"px-6 mb-12 py-2 mt-1 font-bold text-white bg-gray-500 rounded",style:{width:"100%"}},[V.value?(u(),p("span",Oe,"\u062C\u0627\u0631\u064A \u0627\u0644\u062D\u0641\u0638...")):(u(),p("span",Fe,"\u0641\u0644\u062A\u0631\u0629"))])]),e("div",Pe,[s(b,{for:"pay",value:"\u0637\u0628\u0627\u0639\u0629"}),e("button",{onClick:t[14]||(t[14]=B(o=>a.confirmAddPaymentTotal(a.total,a.client_id),["prevent"])),class:"px-6 mb-12 py-2 mt-1 font-bold text-white bg-orange-500 rounded",style:{width:"100%"}},[V.value?(u(),p("span",Ge,"\u062C\u0627\u0631\u064A \u0627\u0644\u062D\u0641\u0638...")):(u(),p("span",We,"\u0637\u0628\u0627\u0639\u0629"))])])]),e("div",Je,[e("table",Ke,[e("thead",Qe,[e("tr",Xe,[Ye,e("th",Ze,l(a.$t("name")),1),e("th",et,l(a.$t("phoneNumber")),1),tt,ot,at,st,e("th",lt,l(a.$t("debt")),1),e("th",rt,l(a.$t("execute")),1)])]),e("tbody",dt,[(u(!0),p(A,null,Q(f.value,(o,F)=>(u(),p("tr",{key:o.id,class:X(["border-b border-white dark:bg-gray-900 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-600",o.car_total_uncomplete<=0?"bg-green-100 dark:bg-green-900":"bg-red-100 dark:bg-red-900"])},[e("td",nt,l(F),1),e("td",it,l(o.name),1),e("td",ct,l(o.phone),1),e("td",mt,l(o.car_total_uncomplete),1),e("td",ut,l(o.car_total_complete),1),e("td",pt,l(o.count_contract),1),e("td",ht,l(o.car_total_uncomplete+o.car_total_complete-o.count_contract),1),e("td",bt,l(o.wallet?"$"+o.wallet.balance:0),1),e("td",gt,[s(c(E),{style:{display:"inline-flex"},className:"px-1 py-1  text-white mx-1 bg-blue-500 rounded d-inline-block",href:a.route("showClients",o.id)},{default:g(()=>[s(ee)]),_:2},1032,["href"]),e("button",{tabIndex:"1",class:"px-1 py-1 text-white mx-1 bg-slate-500 rounded",onClick:O=>R(o)},[s(te)],8,ft),U("",!0),s(c(E),{style:{display:"inline-flex"},className:"px-1 py-1  text-white mx-1 bg-purple-900 rounded d-inline-block",href:a.route("wallet",{id:o.id})},{default:g(()=>[s($e)]),_:2},1032,["href"])])],2))),128))])])]),e("div",_t,[e("div",vt,[s(c(ae),{laravelData:f.value,onInfinite:j,identifier:c($)},null,8,["laravelData","identifier"])])])])])])])]),_:1})],64))}};export{zt as default};
