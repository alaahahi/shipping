import{o as m,c as F,w as y,a as b,b as e,d as H,t as l,e as w,v as D,f as U,T as O,r as i,j as s,k as c,F as A,H as R,m as N,h as G,p as E,g as J,n as K,L as Q}from"./app.ac0b1eaa.js";import{_ as W}from"./AuthenticatedLayout.94ca367f.js";/* empty css                                              */import{t as X}from"./laravel-vue-pagination.es.d5c85ad2.js";/* empty css                                                         */import{_ as g,a as B}from"./TextInput.4cdf65fb.js";import{a as k}from"./index.0c393447.js";import{s as Y}from"./show.d273b25e.js";import{t as Z}from"./trash.f25d87e2.js";import{e as ee}from"./edit.df9dcfc7.js";import{M as te}from"./ModalDelCar.d3f58ed9.js";const ae={key:0,class:"modal-mask"},oe={class:"modal-wrapper max-h-[80vh]"},se={class:"modal-container dark:bg-gray-900 overflow-auto max-h-[80vh]"},le={class:"modal-header"},re={class:"modal-body"},de=e("h2",{class:"text-center dark:text-gray-200"}," \u062A\u0639\u062F\u064A\u0644 \u0632\u0628\u0648\u0646 ",-1),ne={className:"mb-4 mx-5"},ie={class:"dark:text-gray-200",for:"name"},ce={className:"mb-4 mx-5"},me={class:"dark:text-gray-200",for:"phone"},ue={class:"modal-footer my-2"},be={class:"flex flex-row"},pe={class:"basis-1/2 px-4"},ge={class:"basis-1/2 px-4"},fe=["disabled"],I={__name:"ModalEditClient",props:{show:Boolean,formData:Object},setup(u){return(d,r)=>(m(),F(O,{name:"modal"},{default:y(()=>[u.show?(m(),b("div",ae,[e("div",oe,[e("div",se,[e("div",le,[H(d.$slots,"header")]),e("div",re,[de,e("div",ne,[e("label",ie,l(d.$t("name")),1),w(e("input",{id:"name",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":r[0]||(r[0]=n=>u.formData.name=n)},null,512),[[D,u.formData.name]])]),e("div",ce,[e("label",me,l(d.$t("phone")),1),w(e("input",{id:"phone",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":r[1]||(r[1]=n=>u.formData.phone=n)},null,512),[[D,u.formData.phone]])])]),e("div",ue,[e("div",be,[e("div",pe,[e("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:r[2]||(r[2]=n=>{d.$emit("close")})},l(d.$t("cancel")),1)]),e("div",ge,[e("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:r[3]||(r[3]=n=>{d.$emit("a",u.formData),u.formData=""}),disabled:!u.formData.name},l(d.$t("yes")),9,fe)])])])])])])):U("",!0)]),_:3}))}};const he={class:"mb-5 dark:text-white text-center"},ye={class:"py-12"},_e={class:"mx-auto sm:px-6 lg:px-8"},ve={class:"bg-white overflow-hidden shadow-sm sm:rounded-lg"},xe={class:"p-6 dark:bg-gray-900"},ke={class:"grid grid-cols-2 md:grid-cols-5 lg:grid-cols-7 gap-2 lg:gap-1"},we={class:"flex items-center max-w-5xl"},$e=e("label",{class:"dark:text-gray-200",for:"simple-search"},null,-1),Ce={class:"relative w-full"},Ne=e("div",{class:"absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"},[e("svg",{"aria-hidden":"true",class:"w-5 h-5 text-gray-500 dark:text-gray-200 dark:text-gray-400",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{"fill-rule":"evenodd",d:"M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z","clip-rule":"evenodd"})])],-1),De={value:"0"},Me=e("option",{value:"debit"},"\u064A\u0648\u062C\u062F \u062F\u064A\u0646",-1),Ve={class:"text-center px-4"},Ae={class:"px-4"},Ee={className:"mb-4"},Be={class:"px-4"},Ie={className:"mb-4"},Ue={className:"mb-4  mr-5 print:hidden"},ze={key:0},Le={key:1},Se={className:"mb-4  mr-5 print:hidden"},Te={key:0},qe={key:1},Pe={class:"relative overflow-x-auto shadow-md sm:rounded-lg"},je={class:"w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center"},Fe={class:"text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center"},He={class:"rounded-l-lg mb-2 sm:mb-0"},Oe=e("th",{className:"px-1 py-2 text-base"},"#",-1),Re={className:"px-1 py-2 text-base"},Ge={className:"px-1 py-2 text-base"},Je=e("th",{className:"px-1 py-2 text-base"},"\u0645\u062C\u0645\u0648\u0639 \u0627\u0644\u0633\u064A\u0627\u0631\u0627\u062A \u063A\u064A\u0631 \u0645\u0643\u062A\u0645\u0644",-1),Ke=e("th",{className:"px-1 py-2 text-base"},"\u0645\u062C\u0645\u0648\u0639 \u0627\u0644\u0633\u064A\u0627\u0631\u0627\u062A \u0645\u0643\u062A\u0645\u0644",-1),Qe={className:"px-1 py-2 text-base"},We={className:"px-1 py-2 text-base"},Xe={class:"flex-1 sm:flex-none dark:bg-gray-700 dark:text-gray-200"},Ye={className:"border border-white  dark:border-gray-800 text-center px-4 py-2"},Ze={className:"border border-white dark:border-gray-800 text-center  dark:text-gray-200 text-black px-1 py-2 ",style:{"font-weight":"bold","font-size":"16px"}},et={className:"border border-white  dark:border-gray-800 text-center px-4 py-2"},tt={className:"border border-white  dark:border-gray-800 text-center px-4 py-2"},at={className:"border border-white  dark:border-gray-800 text-center px-4 py-2"},ot={className:"border border-white  dark:border-gray-800 text-center px-4 py-2"},st={className:"border border-white  dark:border-gray-800 text-center px-4 py-2",style:{"min-height":"42px"}},lt=["onClick"],rt=["onClick"],dt={class:"mt-3 text-center",style:{direction:"ltr"}},_t={__name:"Index",setup(u){let d=i(!1),r=i(!1),n=i(!1);const v=i([]);let p=i({}),z=i(0);const $=i(0),C=i(0),f=i(""),M=i(0),h=async(o=1)=>{k.get(`/getIndexClients?page=${o}&user_id=${z.value}&from=${$.value}&to=${C.value}&q=${f.value}`).then(t=>{var a;try{v.value=(a=t.data.data)==null?void 0:a.sort((_,x)=>{const V=x.wallet.balance-_.wallet.balance;return V===0?x.car_total_uncomplete-_.car_total_uncomplete:V})}catch{v.value=t.data.data}}).catch(t=>{console.error(t)})};h();function L(o={}){p.value=o,r.value=!0}function S(o={}){p.value=o,d.value=!0}function T(o){k.post("/api/clientsStore",o).then(t=>{window.location.reload()}).catch(t=>{console.error(t)})}function q(o){k.post("/api/clientsEdit",o).then(t=>{window.location.reload()}).catch(t=>{console.error(t)})}function P(o={}){p.value=o,n.value=!0}function j(o){k.post("/api/delClient",o).then(t=>{n.value=!1,h()}).catch(t=>{console.error(t)})}return(o,t)=>(m(),b(A,null,[s(c(R),{title:"Dashboard"}),s(W,null,{default:y(()=>[s(I,{show:c(r),formData:c(p),onA:t[0]||(t[0]=a=>T(a)),onClose:t[1]||(t[1]=a=>N(r)?r.value=!1:r=!1)},{header:y(()=>[]),_:1},8,["show","formData"]),s(te,{show:!!c(n),formData:c(p),onA:t[2]||(t[2]=a=>j(a)),onClose:t[3]||(t[3]=a=>N(n)?n.value=!1:n=!1)},{header:y(()=>[e("h2",he," \u0647\u0644 \u0645\u062A\u0623\u0643\u062F \u0645\u0646 \u062D\u0630\u0641 \u0627\u0644\u062A\u0627\u062C\u0631 "+l(c(p).name)+" \u061F ",1)]),_:1},8,["show","formData"]),s(I,{show:c(d),formData:c(p),onA:t[4]||(t[4]=a=>q(a)),onClose:t[5]||(t[5]=a=>N(d)?d.value=!1:d=!1)},{header:y(()=>[]),_:1},8,["show","formData"]),e("div",ye,[e("div",_e,[e("div",ve,[e("div",xe,[e("div",ke,[e("div",null,[s(g,{for:"from",value:o.$t("search"),class:"mb-1"},null,8,["value"]),e("form",we,[$e,e("div",Ce,[Ne,w(e("input",{"onUpdate:modelValue":t[6]||(t[6]=a=>f.value=a),onInput:t[7]||(t[7]=a=>h(f.value)),type:"text",id:"simple-search",class:"bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500",placeholder:"\u0628\u062D\u062B",required:""},null,544),[[D,f.value]])])])]),e("div",null,[s(g,{for:"from",value:"\u062A\u062D\u062F\u064A\u062F \u0627\u0644\u0641\u0626\u0629",class:"mb-1"}),w(e("select",{onChange:t[8]||(t[8]=a=>h()),"onUpdate:modelValue":t[9]||(t[9]=a=>f.value=a),id:"default",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 mb-6 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500"},[e("option",De,l(o.$t("allOwners")),1),Me],544),[[G,f.value]])]),e("div",Ve,[s(g,{for:"pay",value:"\u0627\u0636\u0627\u0641\u0629",class:"mb-1"}),e("button",{className:"px-6 mb-12 py-2 mt-1 font-bold text-white bg-red-500 rounded",onClick:t[10]||(t[10]=a=>L())},l(o.$t("addCustomer")),1)]),e("div",Ae,[e("div",Ee,[s(g,{for:"from",value:o.$t("from_date")},null,8,["value"]),s(B,{id:"from",type:"date",class:"mt-1 block w-full",modelValue:$.value,"onUpdate:modelValue":t[11]||(t[11]=a=>$.value=a)},null,8,["modelValue"])])]),e("div",Be,[e("div",Ie,[s(g,{for:"to",value:o.$t("to_date")},null,8,["value"]),s(B,{id:"to",type:"date",class:"mt-1 block w-full",modelValue:C.value,"onUpdate:modelValue":t[12]||(t[12]=a=>C.value=a)},null,8,["modelValue"])])]),e("div",Ue,[s(g,{for:"pay",value:"\u0641\u0644\u062A\u0631\u0629"}),e("button",{onClick:t[13]||(t[13]=E(a=>h(),["prevent"])),class:"px-6 mb-12 py-2 mt-1 font-bold text-white bg-gray-500 rounded",style:{width:"100%"}},[M.value?(m(),b("span",Le,"\u062C\u0627\u0631\u064A \u0627\u0644\u062D\u0641\u0638...")):(m(),b("span",ze,"\u0641\u0644\u062A\u0631\u0629"))])]),e("div",Se,[s(g,{for:"pay",value:"\u0637\u0628\u0627\u0639\u0629"}),e("button",{onClick:t[14]||(t[14]=E(a=>o.confirmAddPaymentTotal(o.total,o.client_id),["prevent"])),class:"px-6 mb-12 py-2 mt-1 font-bold text-white bg-orange-500 rounded",style:{width:"100%"}},[M.value?(m(),b("span",qe,"\u062C\u0627\u0631\u064A \u0627\u0644\u062D\u0641\u0638...")):(m(),b("span",Te,"\u0637\u0628\u0627\u0639\u0629"))])])]),e("div",Pe,[e("table",je,[e("thead",Fe,[e("tr",He,[Oe,e("th",Re,l(o.$t("name")),1),e("th",Ge,l(o.$t("phoneNumber")),1),Je,Ke,e("th",Qe,l(o.$t("debt")),1),e("th",We,l(o.$t("execute")),1)])]),e("tbody",Xe,[(m(!0),b(A,null,J(v.value,(a,_)=>(m(),b("tr",{key:a.id,class:K(["border-b border-white dark:bg-gray-900 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-600",a.car_total_uncomplete<=0?"bg-green-100 dark:bg-green-900":"bg-red-100 dark:bg-red-900"])},[e("td",Ye,l(_),1),e("td",Ze,l(a.name),1),e("td",et,l(a.phone),1),e("td",tt,l(a.car_total_uncomplete),1),e("td",at,l(a.car_total_complete),1),e("td",ot,l(a.wallet?"$"+a.wallet.balance:0),1),e("td",st,[s(c(Q),{style:{display:"inline-flex"},className:"px-1 py-1  text-white mx-1 bg-blue-500 rounded d-inline-block",href:o.route("showClients",a.id)},{default:y(()=>[s(Y)]),_:2},1032,["href"]),e("button",{tabIndex:"1",class:"px-1 py-1 text-white mx-1 bg-slate-500 rounded",onClick:x=>S(a)},[s(ee)],8,lt),(a==null?void 0:a.wallet.balance)<=0?(m(),b("button",{key:0,tabIndex:"1",class:"px-1 py-1 text-white mx-1 bg-orange-500 rounded",onClick:x=>P(a)},[s(Z)],8,rt)):U("",!0)])],2))),128))])])]),e("div",dt,[s(c(X),{data:v.value,onPaginationChangePage:h,limit:10},null,8,["data"])])])])])])]),_:1})],64))}};export{_t as default};
