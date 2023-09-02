import{s as oe,m as n,G as ae,a as E,f as d,u as l,e as u,i as c,F as S,o as F,H as te,j as se,h as t,w as G,C as ne,y as re,z as le,t as p,g as de,x as ie}from"./app.35a17718.js";import{_ as ue}from"./AuthenticatedLayout.18e2bba7.js";import{P as ce,N as me,_ as fe,a as pe,b as he,c as we,d as ve,e as xe,f as ge,g as ye,M as _e,h as k}from"./index.d5e35f6f.js";import{M as be}from"./Modal.413b0151.js";/* empty css                                              */const $e=t("h2",{class:"text-center",style:{"font-size":"20px"}}," \u0647\u0644 \u0645\u062A\u0623\u0643\u062F \u0645\u0646 \u062A\u0639\u062F\u064A\u0644 \u0627\u0644\u0628\u064A\u0627\u0646\u0627\u062A ",-1),ke={class:"py-2"},Ce={class:"max-w-9xl mx-auto sm:px-6 lg:px-8"},Me={class:"bg-white overflow-hidden shadow-sm"},De={class:"p-6 dark:bg-gray-900"},Te={class:"flex flex-col"},Ae={class:"grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 gap-2 lg:gap-1"},Ie={class:"flex items-center max-w-5xl"},Be=t("label",{class:"dark:text-gray-200",for:"simple-search"},null,-1),Ee={class:"relative w-full"},Fe=t("div",{class:"absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"},[t("svg",{"aria-hidden":"true",class:"w-5 h-5 text-gray-500 dark:text-gray-200 dark:text-gray-400",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[t("path",{"fill-rule":"evenodd",d:"M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z","clip-rule":"evenodd"})])],-1),ze=t("option",{value:"0",disabled:""},"\u0627\u062E\u062A\u0627\u0631 \u0627\u0644\u0645\u0646\u062F\u0648\u0628",-1),Ne=["value"],Pe={class:"text-center"},je={class:"text-center"};const Se={class:"mt-4 grid grid-cols-1 gap-4 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-7"},Ge={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},Ye=t("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50"},[t("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-orange-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"})])],-1),Le={class:"mr-4"},Re={class:"font-semibold"},Ue={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},qe={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},He=t("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50"},[t("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-red-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"})])],-1),We={class:"mr-4"},Je={class:"font-semibold"},Ke={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},Oe=t("div",null,null,-1),oo={__name:"Dashboard",props:{client:Array},setup(M){const{t:r}=oe();let Y=n({});n({date:new ce,numeric:new me("0,0")});const z=ae();r("no"),r("car_owner"),r("car_type"),r("year"),r("car_color"),r("vin"),r("car_number"),r("dinar"),r("dolar_price"),r("dolar_custom"),r("note"),r("shipping_dolar"),r("coc_dolar"),r("checkout"),r("expenses"),r("total"),r("paid"),r("profit"),r("date");let h=n(!1),v=n(""),x=n(!1),g=n(!1),y=n(!1),_=n(!1),w=n(!1),m=n(!1),b=n(!1),C=n(!1),$=n(!1),N=n(0),P=n(0);function L(o={}){i.value=o,w.value=!0}function R(o={}){i.value=o,m.value=!0}const i=n({});n({});const j=n([]);n({startDate:"",endDate:""}),n(),n({date:"D/MM/YYYY",month:"MM"});const D=async(o="",e=1)=>{const s=await fetch(`/getIndexCar?page=${e}&user_id=${o}`);j.value=await s.json()},U=async(o="",e=1)=>{const s=await fetch(`/getIndexCarSearch?page=${e}&q=${o}`);j.value=await s.json()};n({shortcuts:{today:"\u0627\u0644\u064A\u0648\u0645",yesterday:"\u0627\u0644\u0628\u0627\u0631\u062D\u0629",past:o=>o+" \u0642\u0628\u0644 \u064A\u0648\u0645",currentMonth:"\u0627\u0644\u0634\u0647\u0631 \u0627\u0644\u062D\u0627\u0644\u064A",pastMonth:"\u0627\u0644\u0634\u0647\u0631 \u0627\u0644\u0633\u0627\u0628\u0642"},footer:{apply:"Terapkan",cancel:"Batal"}});const T=async()=>{k.get("/api/totalInfo").then(o=>{N.value=o.data.data.mainAccount,P.value=o.data.data.allCars}).catch(o=>{console.error(o)})};T();function q(o){k.post("/api/addCars",o).then(e=>{x.value=!1,D(),T()}).catch(e=>{console.error(e)})}function H(o){k.post("/api/updateCars",o).then(e=>{h.value=!1,z.success("\u062A\u0645 \u0627\u0644\u062A\u0639\u062F\u064A\u0644 \u0628\u0646\u062C\u0627\u062D",{timeout:2e3,position:"bottom-right",rtl:!0}),T()}).catch(e=>{h.value=!1,z.error("\u0644\u0645 \u0627\u0644\u062A\u0639\u062F\u064A\u0644 \u0628\u0646\u062C\u0627\u062D",{timeout:2e3,position:"bottom-right",rtl:!0})})}function W(o){k.post("/api/payCar",o).then(e=>{g.value=!1,window.location.reload()}).catch(e=>{console.error(e)})}function J(o){var e,s;fetch(`/addExpenses?car_id=${o.id}&user_id=${o.user_id}&expenses_id=${o.expenses_id}&expens_amount=${(e=o.expens_amount)!=null?e:0}&note=${(s=o.noteExpenses)!=null?s:""}`).then(()=>{y.value=!1,window.location.reload()}).catch(a=>{console.error(a)})}function K(o){var e,s,a;fetch(`/GenExpenses?user_id=${o.user_id}&amount=${(e=o.amount)!=null?e:0}&reason=${(s=o.reason)!=null?s:""}&note=${(a=o.note)!=null?a:""}`).then(()=>{_.value=!1,window.location.reload()}).catch(f=>{console.error(f)})}function O(o){var e,s;fetch(`/addTransfers?user_id=${o.user_id}&amount=${(e=o.amount)!=null?e:0}&note=${(s=o.note)!=null?s:""}`).then(()=>{b.value=!1,window.location.reload()}).catch(a=>{console.error(a)})}function Q(o){var e,s;fetch(`/addToBox?amount=${(e=o.amount)!=null?e:0}&note=${(s=o.note)!=null?s:""}`).then(()=>{w.value=!1,window.location.reload()}).catch(a=>{console.error(a)})}function X(o){var e,s;fetch(`/withDrawFromBox?amount=${(e=o.amount)!=null?e:0}&note=${(s=o.note)!=null?s:""}`).then(()=>{m.value=!1,window.location.reload()}).catch(a=>{console.error(a)})}function Z(o){var e,s;fetch(`/addPaymentCar?car_id=${o.id}&user_id=${o.user_id}&amount=${(e=o.amountPayment)!=null?e:0}&note=${(s=o.notePayment)!=null?s:""}`).then(()=>{m.value=!1,window.location.reload()}).catch(a=>{console.error(a)})}function V(o){k.post("/api/DelCar",o).then(e=>{$.value=!1,window.location.reload()}).catch(e=>{console.error(e)})}return D(),(o,e)=>{const s=ie("InputLabel");return F(),E(S,null,[d(l(te),{title:"Dashboard"}),d(be,{data:l(Y),show:!!l(h),carModel:o.carModel,onA:e[0]||(e[0]=a=>H(a)),onClose:e[1]||(e[1]=a=>c(h)?h.value=!1:h=!1)},{header:u(()=>[$e]),_:1},8,["data","show","carModel"]),d(fe,{formData:i.value,show:!!l(x),client:M.client,carModel:o.carModel,onA:e[2]||(e[2]=a=>q(a)),onClose:e[3]||(e[3]=a=>c(x)?x.value=!1:x=!1)},{header:u(()=>[]),_:1},8,["formData","show","client","carModel"]),d(pe,{formData:i.value,show:!!l(g),company:o.company,name:o.name,color:o.color,carModel:o.carModel,client:M.client,onA:e[4]||(e[4]=a=>W(a)),onClose:e[5]||(e[5]=a=>c(g)?g.value=!1:g=!1)},{header:u(()=>[]),_:1},8,["formData","show","company","name","color","carModel","client"]),d(he,{formData:i.value,expenses:o.expenses,show:!!l(y),user:o.user,onA:e[6]||(e[6]=a=>J(a)),onClose:e[7]||(e[7]=a=>c(y)?y.value=!1:y=!1)},{header:u(()=>[]),_:1},8,["formData","expenses","show","user"]),d(we,{formData:i.value,show:!!l(_),user:o.user,onA:e[8]||(e[8]=a=>K(a)),onClose:e[9]||(e[9]=a=>c(_)?_.value=!1:_=!1)},{header:u(()=>[]),_:1},8,["formData","show","user"]),d(ve,{formData:i.value,expenses:o.expenses,show:!!l(w),user:o.user,onA:e[10]||(e[10]=a=>Q(a)),onClose:e[11]||(e[11]=a=>c(w)?w.value=!1:w=!1)},{header:u(()=>[]),_:1},8,["formData","expenses","show","user"]),d(xe,{formData:i.value,expenses:o.expenses,show:!!l(m),user:o.user,onA:e[12]||(e[12]=a=>X(a)),onClose:e[13]||(e[13]=a=>c(m)?m.value=!1:m=!1)},{header:u(()=>[]),_:1},8,["formData","expenses","show","user"]),d(ge,{formData:i.value,expenses:o.expenses,show:!!l(b),user:o.user,onA:e[14]||(e[14]=a=>O(a)),onClose:e[15]||(e[15]=a=>c(b)?b.value=!1:b=!1)},{header:u(()=>[]),_:1},8,["formData","expenses","show","user"]),d(ye,{formData:i.value,show:!!l(C),user:o.user,onA:e[16]||(e[16]=a=>Z(a)),onClose:e[17]||(e[17]=a=>c(C)?C.value=!1:C=!1)},{header:u(()=>[]),_:1},8,["formData","show","user"]),d(_e,{show:!!l($),formData:i.value,onA:e[18]||(e[18]=a=>V(a)),onClose:e[19]||(e[19]=a=>c($)?$.value=!1:$=!1)},{header:u(()=>[se(" \u0647\u0644 \u0645\u062A\u0623\u0643\u062F \u0645\u0646 \u062D\u0630\u0641 \u0627\u0644\u0633\u064A\u0627\u0631\u0629 \u061F ")]),_:1},8,["show","formData"]),d(ue,null,{default:u(()=>[t("div",ke,[t("div",Ce,[t("div",Me,[t("div",De,[t("div",Te,[t("div",Ae,[t("div",null,[t("form",Ie,[Be,t("div",Ee,[Fe,G(t("input",{"onUpdate:modelValue":e[20]||(e[20]=a=>c(v)?v.value=a:v=a),onInput:e[21]||(e[21]=a=>U(l(v))),type:"text",id:"simple-search",class:"bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500",placeholder:"\u0628\u062D\u062B",required:""},null,544),[[ne,l(v)]])])])]),t("div",null,[d(s,{class:"mb-1",for:"invoice_number",value:"\u062D\u0633\u0627\u0628"}),G(t("select",{onChange:e[22]||(e[22]=a=>D(o.user_id)),"onUpdate:modelValue":e[23]||(e[23]=a=>o.user_id=a),id:"default",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 mb-6 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500"},[ze,(F(!0),E(S,null,le(M.client,(a,f)=>(F(),E("option",{key:f,value:a.id},p(a.name),9,Ne))),128))],544),[[re,o.user_id]])]),t("div",Pe,[t("button",{type:"button",onClick:e[24]||(e[24]=a=>L()),style:{"min-width":"150px"},className:"px-6 mb-12 mx-2 py-2 font-bold text-white bg-purple-600 rounded"},p(o.$t("addToTheFund")),1)]),t("div",je,[t("button",{type:"button",onClick:e[25]||(e[25]=a=>R()),style:{"min-width":"150px"},className:"px-6 mb-12 mx-2 py-2 font-bold text-white bg-pink-600 rounded"},p(o.$t("withdrawFromTheFund")),1)])]),t("div",null,[de("",!0)]),t("div",null,[t("div",Se,[t("div",Ge,[Ye,t("div",Le,[t("h2",Re,p(o.$t("capital")),1),t("p",Ue,p(l(N)),1)])]),t("div",qe,[He,t("div",We,[t("h2",Je,p(o.$t("all_cars")),1),t("p",Ke,p(l(P)),1)])])])])])])])])]),Oe]),_:1})],64)}}};export{oo as default};
