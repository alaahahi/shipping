import{s as X,m as r,G as Z,a as j,f as d,u as l,e as i,i as u,F as P,o as B,H as ee,j as ae,h as t,g as R,t as _,z as oe,d as te,n as se,L as re,x as ne}from"./app.976cf3e0.js";import{_ as le}from"./AuthenticatedLayout.53ca6879.js";import{P as de,N as ie,_ as ce,a as ue,b as me,c as fe,d as he,e as pe,f as _e}from"./index.d05b535d.js";import{M as we}from"./Modal.07e757c8.js";import{_ as ve,M as xe,a as k}from"./ModalAddCarPayment.46fe044e.js";/* empty css                                              */const ge=t("h2",{class:"text-center",style:{"font-size":"20px"}}," \u0647\u0644 \u0645\u062A\u0623\u0643\u062F \u0645\u0646 \u062A\u0639\u062F\u064A\u0644 \u0627\u0644\u0628\u064A\u0627\u0646\u0627\u062A ",-1),ye={class:"py-2"},be={class:"max-w-9xl mx-auto sm:px-6 lg:px-8"},ke={class:"bg-white overflow-hidden shadow-sm"},$e={class:"p-6 dark:bg-gray-900"},Ce={class:"flex flex-col"};const De={class:"mt-4 grid grid-cols-1 gap-4 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-7"},Me={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},Te=t("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50"},[t("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-orange-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"})])],-1),Be={class:"mr-4"},Ae={class:"font-semibold"},Ie={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},Ne={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},Ee=t("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50"},[t("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-red-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"})])],-1),Fe={class:"mr-4"},ze={class:"font-semibold"},Se={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},je=t("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50"},[t("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-red-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"})])],-1),Pe={class:"mr-4"},Re={class:"font-semibold"},Le={class:"mt-2 text-sm text-gray-200 dark:text-gray-200"},Ge=t("div",null,null,-1),oa={__name:"Dashboard",props:{client:Array},setup(A){const{t:n}=X();let L=r({});const I=r({});(async(a=1)=>{const e=await fetch(`/getIndexClients?page=${a}&q=debit`);I.value=await e.json()})(),r({date:new de,numeric:new ie("0,0")});const N=Z();n("no"),n("car_owner"),n("car_type"),n("year"),n("car_color"),n("vin"),n("car_number"),n("dinar"),n("dolar_price"),n("dolar_custom"),n("note"),n("shipping_dolar"),n("coc_dolar"),n("checkout"),n("expenses"),n("total"),n("paid"),n("profit"),n("date");let f=r(!1),qe=r(""),w=r(!1),v=r(!1),x=r(!1),g=r(!1),h=r(!1),m=r(!1),y=r(!1),$=r(!1),b=r(!1),E=r(0),F=r(0);function Ue(a={}){c.value=a,h.value=!0}function Ve(a={}){c.value=a,m.value=!0}const c=r({});r({});const z=r([]);r({startDate:"",endDate:""}),r(),r({date:"D/MM/YYYY",month:"MM"});const S=async(a="",e=1)=>{const s=await fetch(`/getIndexCar?page=${e}&user_id=${a}`);z.value=await s.json()},He=async(a="",e=1)=>{const s=await fetch(`/getIndexCarSearch?page=${e}&q=${a}`);z.value=await s.json()};r({shortcuts:{today:"\u0627\u0644\u064A\u0648\u0645",yesterday:"\u0627\u0644\u0628\u0627\u0631\u062D\u0629",past:a=>a+" \u0642\u0628\u0644 \u064A\u0648\u0645",currentMonth:"\u0627\u0644\u0634\u0647\u0631 \u0627\u0644\u062D\u0627\u0644\u064A",pastMonth:"\u0627\u0644\u0634\u0647\u0631 \u0627\u0644\u0633\u0627\u0628\u0642"},footer:{apply:"Terapkan",cancel:"Batal"}});const C=async()=>{k.get("/api/totalInfo").then(a=>{E.value=a.data.data.mainAccount,F.value=a.data.data.allCars}).catch(a=>{console.error(a)})};C();function G(a){k.post("/api/addCars",a).then(e=>{w.value=!1,S(),C()}).catch(e=>{console.error(e)})}function Y(a){k.post("/api/updateCars",a).then(e=>{f.value=!1,N.success("\u062A\u0645 \u0627\u0644\u062A\u0639\u062F\u064A\u0644 \u0628\u0646\u062C\u0627\u062D",{timeout:2e3,position:"bottom-right",rtl:!0}),C()}).catch(e=>{f.value=!1,N.error("\u0644\u0645 \u0627\u0644\u062A\u0639\u062F\u064A\u0644 \u0628\u0646\u062C\u0627\u062D",{timeout:2e3,position:"bottom-right",rtl:!0})})}function q(a){k.post("/api/payCar",a).then(e=>{v.value=!1,window.location.reload()}).catch(e=>{console.error(e)})}function U(a){var e,s;fetch(`/addExpenses?car_id=${a.id}&user_id=${a.user_id}&expenses_id=${a.expenses_id}&expens_amount=${(e=a.expens_amount)!=null?e:0}&note=${(s=a.noteExpenses)!=null?s:""}`).then(()=>{x.value=!1,window.location.reload()}).catch(o=>{console.error(o)})}function V(a){var e,s,o;fetch(`/GenExpenses?user_id=${a.user_id}&amount=${(e=a.amount)!=null?e:0}&reason=${(s=a.reason)!=null?s:""}&note=${(o=a.note)!=null?o:""}`).then(()=>{g.value=!1,window.location.reload()}).catch(p=>{console.error(p)})}function H(a){var e,s;fetch(`/addTransfers?user_id=${a.user_id}&amount=${(e=a.amount)!=null?e:0}&note=${(s=a.note)!=null?s:""}`).then(()=>{y.value=!1,window.location.reload()}).catch(o=>{console.error(o)})}function W(a){var e,s;fetch(`/addToBox?amount=${(e=a.amount)!=null?e:0}&note=${(s=a.note)!=null?s:""}`).then(()=>{h.value=!1,window.location.reload()}).catch(o=>{console.error(o)})}function J(a){var e,s;fetch(`/withDrawFromBox?amount=${(e=a.amount)!=null?e:0}&note=${(s=a.note)!=null?s:""}`).then(()=>{m.value=!1,window.location.reload()}).catch(o=>{console.error(o)})}function K(a){var e,s;fetch(`/addPaymentCar?car_id=${a.id}&user_id=${a.user_id}&amount=${(e=a.amountPayment)!=null?e:0}&note=${(s=a.notePayment)!=null?s:""}`).then(()=>{m.value=!1,window.location.reload()}).catch(o=>{console.error(o)})}function O(a){k.post("/api/DelCar",a).then(e=>{b.value=!1,window.location.reload()}).catch(e=>{console.error(e)})}return S(),(a,e)=>{const s=ne("InputLabel");return B(),j(P,null,[d(l(ee),{title:"Dashboard"}),d(we,{data:l(L),show:!!l(f),carModel:a.carModel,onA:e[0]||(e[0]=o=>Y(o)),onClose:e[1]||(e[1]=o=>u(f)?f.value=!1:f=!1)},{header:i(()=>[ge]),_:1},8,["data","show","carModel"]),d(ce,{formData:c.value,show:!!l(w),client:A.client,carModel:a.carModel,onA:e[2]||(e[2]=o=>G(o)),onClose:e[3]||(e[3]=o=>u(w)?w.value=!1:w=!1)},{header:i(()=>[]),_:1},8,["formData","show","client","carModel"]),d(ue,{formData:c.value,show:!!l(v),company:a.company,name:a.name,color:a.color,carModel:a.carModel,client:A.client,onA:e[4]||(e[4]=o=>q(o)),onClose:e[5]||(e[5]=o=>u(v)?v.value=!1:v=!1)},{header:i(()=>[]),_:1},8,["formData","show","company","name","color","carModel","client"]),d(me,{formData:c.value,expenses:a.expenses,show:!!l(x),user:a.user,onA:e[6]||(e[6]=o=>U(o)),onClose:e[7]||(e[7]=o=>u(x)?x.value=!1:x=!1)},{header:i(()=>[]),_:1},8,["formData","expenses","show","user"]),d(fe,{formData:c.value,show:!!l(g),user:a.user,onA:e[8]||(e[8]=o=>V(o)),onClose:e[9]||(e[9]=o=>u(g)?g.value=!1:g=!1)},{header:i(()=>[]),_:1},8,["formData","show","user"]),d(he,{formData:c.value,expenses:a.expenses,show:!!l(h),user:a.user,onA:e[10]||(e[10]=o=>W(o)),onClose:e[11]||(e[11]=o=>u(h)?h.value=!1:h=!1)},{header:i(()=>[]),_:1},8,["formData","expenses","show","user"]),d(pe,{formData:c.value,expenses:a.expenses,show:!!l(m),user:a.user,onA:e[12]||(e[12]=o=>J(o)),onClose:e[13]||(e[13]=o=>u(m)?m.value=!1:m=!1)},{header:i(()=>[]),_:1},8,["formData","expenses","show","user"]),d(_e,{formData:c.value,expenses:a.expenses,show:!!l(y),user:a.user,onA:e[14]||(e[14]=o=>H(o)),onClose:e[15]||(e[15]=o=>u(y)?y.value=!1:y=!1)},{header:i(()=>[]),_:1},8,["formData","expenses","show","user"]),d(ve,{formData:c.value,show:!!l($),user:a.user,onA:e[16]||(e[16]=o=>K(o)),onClose:e[17]||(e[17]=o=>u($)?$.value=!1:$=!1)},{header:i(()=>[]),_:1},8,["formData","show","user"]),d(xe,{show:!!l(b),formData:c.value,onA:e[18]||(e[18]=o=>O(o)),onClose:e[19]||(e[19]=o=>u(b)?b.value=!1:b=!1)},{header:i(()=>[ae(" \u0647\u0644 \u0645\u062A\u0623\u0643\u062F \u0645\u0646 \u062D\u0630\u0641 \u0627\u0644\u0633\u064A\u0627\u0631\u0629 \u061F ")]),_:1},8,["show","formData"]),d(le,null,{default:i(()=>[t("div",ye,[t("div",be,[t("div",ke,[t("div",$e,[t("div",Ce,[R("",!0),t("div",null,[R("",!0)]),t("div",null,[t("div",De,[t("div",Me,[Te,t("div",Be,[t("h2",Ae,_(a.$t("capital")),1),t("p",Ie,_(l(E)),1)])]),t("div",Ne,[Ee,t("div",Fe,[t("h2",ze,_(a.$t("all_cars")),1),t("p",Se,_(l(F)),1)])]),(B(!0),j(P,null,oe(I.value.data,o=>(B(),te(l(re),{key:o.id,class:se(["flex items-start rounded-xl text-gray-200 dark:text-gray-300 p-4 shadow-lg",o.car_total_uncomplete?"bg-red-500  dark:bg-red-500":"bg-green-600  dark:bg-green-600"]),href:a.route("showClients",o.id)},{default:i(()=>[je,t("div",Pe,[t("h2",Re,_(o.name),1),t("p",Le,_(o.wallet?"$"+o.wallet.balance:0),1)])]),_:2},1032,["href","class"]))),128))])])])])])])]),Ge]),_:1})],64)}}};export{oa as default};
