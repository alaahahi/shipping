import{x as X,y as Y,r as n,a as i,g as l,j as r,w as N,k as g,F as M,o as c,H as Z,b as e,e as F,v as ee,z as te,t as o,h as z,l as se,i as S,n as oe,L as ae}from"./app.d82692b4.js";import{_ as re}from"./AuthenticatedLayout.174901c8.js";import{_ as b}from"./InputLabel.2d4c1ba4.js";import{_ as R}from"./TextInput.e5881e26.js";import{s as le}from"./show.61ee666b.js";import{p as de}from"./pay.056a1a3c.js";import{t as ne}from"./trash.6c194d44.js";import{e as ie}from"./edit.fbd0ded0.js";import{_ as ce}from"./ModalAddCarPayment.bcc20b67.js";import{M as pe}from"./ModalDelCar.8777abc8.js";import{_ as ue}from"./ModalEditCar_S.ccf2744d.js";import{W as he}from"./v3-infinite-loading.es.3c7782f9.js";import{d as _e}from"./debounce.76eb5c83.js";import{a as D}from"./index.5b89c259.js";/* empty css                                                    */import"./Uploader.74707fdd.js";/* empty css                                                                 *//* empty css                                                       */const me=e("h2",{class:"mb-5 dark:text-white text-center"}," \u0647\u0644 \u0645\u062A\u0623\u0643\u062F \u0645\u0646 \u062D\u0630\u0641 \u0627\u0644\u0633\u064A\u0627\u0631\u0629 \u061F ",-1),ge={key:0,class:"py-2"},be={class:"max-w-9xl mx-auto sm:px-6 lg:px-8"},xe={class:"bg-white overflow-hidden shadow-sm"},ye={class:"p-6 dark:bg-gray-900"},fe={class:"flex flex-col"},ve={class:"grid grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-2 lg:gap-1"},ke={class:"flex items-center max-w-5xl"},we=e("label",{class:"dark:text-gray-200",for:"simple-search"},null,-1),$e={class:"relative w-full"},Ce=e("div",{class:"absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"},[e("svg",{"aria-hidden":"true",class:"w-5 h-5 text-gray-500 dark:text-gray-200 dark:text-gray-400",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{"fill-rule":"evenodd",d:"M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z","clip-rule":"evenodd"})])],-1),Ne={value:"0",disabled:""},De={value:""},Me=["value"],Ie={class:"px-2"},Ae={className:"mb-4"},Pe={class:"px-2"},ze={className:"mb-4"},Se={className:"mb-4  mr-2 print:hidden"},Ue=e("span",null,"\u0641\u0644\u062A\u0631\u0629",-1),je=[Ue],Be={className:"mb-4  mr-5 print:hidden"},Ve=["href"],Ee=e("span",null,"Excel",-1),Fe=[Ee],Re={key:0,className:"mb-4  mr-5 print:hidden"},qe=["href"],Le=e("span",null,"\u0637\u0628\u0627\u0639\u0629",-1),Te=[Le],He=e("div",null,null,-1),Ge={class:"relative overflow-x-auto shadow-md sm:rounded-lg"},Oe={class:"w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center"},We={class:"text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center"},Je={scope:"col",class:"px-1 py-3 text-base"},Ke={scope:"col",class:"px-1 py-3 text-base"},Qe={scope:"col",class:"px-1 py-3 text-base"},Xe={scope:"col",class:"px-1 py-3 text-base"},Ye={scope:"col",class:"px-1 py-3 text-base"},Ze={scope:"col",class:"px-1 py-3 text-base"},et={scope:"col",class:"px-1 py-3 text-base"},tt={scope:"col",class:"px-1 py-3 text-base"},st={scope:"col",class:"px-1 py-3 text-base"},ot={scope:"col",class:"px-1 py-3 text-base"},at={scope:"col",class:"px-1 py-3 text-base"},rt={scope:"col",class:"px-1 py-3 text-base"},lt={scope:"col",class:"px-1 py-3 text-base"},dt={scope:"col",class:"px-1 py-3 text-base"},nt={scope:"col",class:"px-1 py-3 text-base"},it={scope:"col",class:"px-1 py-3 text-base"},ct={scope:"col",class:"px-1 py-3 text-base"},pt={scope:"col",class:"px-1 py-3 text-base"},ut={scope:"col",class:"px-1 py-3 text-base",style:{width:"180px"}},ht=e("th",{class:"px-1 py-3 text-base"},"\u062A\u062E\u0632\u064A\u0646",-1),_t={className:"border dark:border-gray-800 text-center  dark:text-gray-200 text-black px-1 py-2 ",style:{"font-weight":"bold","font-size":"16px"}},mt={className:"border dark:border-gray-800 text-center px-1 py-2 "},gt={className:"border dark:border-gray-800 text-center px-1 py-2 "},bt={className:"border dark:border-gray-800 text-center px-1 py-2 "},xt={className:"border dark:border-gray-800 text-center px-1 py-2 "},yt={className:"border dark:border-gray-800 text-center px-1 py-2 "},ft={className:"border dark:border-gray-800 text-center px-1 py-2 "},vt={className:"border dark:border-gray-800 text-center px-1 py-2 "},kt={className:"border dark:border-gray-800 text-center px-1 py-2 "},wt={className:"border dark:border-gray-800 text-center px-1 py-2 "},$t={className:"border dark:border-gray-800 text-center px-1 py-2 "},Ct={className:"border dark:border-gray-800 text-center px-1 py-2 "},Nt={className:"border dark:border-gray-800 text-center px-1 py-2 "},Dt={className:"border dark:border-gray-800 text-center px-1 py-2 "},Mt={className:"border dark:border-gray-800 text-center px-1 py-2 "},It={className:"border dark:border-gray-800 text-center px-1 py-2 "},At={className:"border dark:border-gray-800 text-center px-1 py-2 "},Pt={className:"border dark:border-gray-800 text-center px-1 py-2 "},zt={className:"border dark:border-gray-800 text-start px-1 py-2"},St=["onClick"],Ut=["onClick"],jt=["onClick"],Bt={className:"border dark:border-gray-800 text-center px-1 py-2 "},Vt=["href"],Et=["src"],Ft={class:"spaner"},Rt={class:"mt-4 grid grid-cols-1 gap-4 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-7"},qt={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},Lt=e("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-orange-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"})])],-1),Tt={class:"mr-4"},Ht={class:"font-semibold"},Gt={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},Ot={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},Wt=e("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-red-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"})])],-1),Jt={class:"mr-4"},Kt={class:"font-semibold"},Qt={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},Xt=e("div",null,null,-1),gs={__name:"Sales",props:{client:Array},setup(U){X();const k=Y();let I=n(!1);n(!1),n(!1);let x=n(!1),y=n(!1),f=n(!1),j=n(0),B=n(0);function q(s={}){d.value=s,d.value.dinar_s==0&&(d.value.dinar_s=d.value.dinar),d.value.expenses_s==0&&(d.value.expenses_s=d.value.expenses),y.value=!0}function L(s={}){d.value=s,f.value=!0}function T(s={}){d.value=s,d.value.notePayment=" \u0628\u064A\u062F ",x.value=!0}const d=n({}),w=n([]);let u=n(""),h=n(""),A=n(!1),p=0,P=1,$="";const v=()=>{P=0,w.value.length=0,A.value=!A.value},H=async s=>{console.log(s);try{const t=(await D.get("/getIndexCar",{params:{limit:100,page:P,q:$,user_id:p,from:u.value,to:h.value}})).data;t.data.length<100?(w.value.push(...t.data),s.complete()):(w.value.push(...t.data),s.loaded()),P++}catch(a){console.log(a)}},V=async()=>{D.get("/api/totalInfo").then(s=>{j.value=s.data.data.mainAccount,B.value=s.data.data.allCars}).catch(s=>{console.error(s)})};V();function G(s){y.value=!1,D.post("/api/updateCarsS",s).then(a=>{I.value=!1,k.success("\u062A\u0645 \u0627\u0644\u062A\u0639\u062F\u064A\u0644 \u0628\u0646\u062C\u0627\u062D",{timeout:2e3,position:"bottom-right",rtl:!0}),V(),v()}).catch(a=>{I.value=!1,k.error("\u0644\u0645 \u0627\u0644\u062A\u0639\u062F\u064A\u0644 \u0628\u0646\u062C\u0627\u062D",{timeout:2e3,position:"bottom-right",rtl:!0})})}function O(s){D.post("/api/DelCar",s).then(a=>{f.value=!1,k.success("\u062A\u0645 \u0627\u0644\u062A\u0639\u062F\u064A\u0644 \u0628\u0646\u062C\u0627\u062D \u0648\u062E\u0635\u0645 \u0627\u0644\u0645\u0628\u0644\u063A \u0645\u0646 \u062F\u064A\u0646 \u0627\u0644\u0632\u0628\u0648\u0646",{timeout:3e3,position:"bottom-right",rtl:!0}),v()}).catch(a=>{console.error(a)})}function W(s){var a,t,_;D.get(`/api/addPaymentCar?car_id=${s.id}&discount=${(a=s.discountPayment)!=null?a:0}&amount=${(t=s.amountPayment)!=null?t:0}&note=${(_=s.notePayment)!=null?_:""}`).then(C=>{v(),x.value=!1,k.success(" \u062A\u0645 \u062F\u0641\u0639 \u0645\u0628\u0644\u063A \u062F\u0648\u0644\u0627\u0631 "+s.amountPayment+" \u0628\u0646\u062C\u0627\u062D ",{timeout:3e3,position:"bottom-right",rtl:!0});let m=C.data;window.open(`/api/getIndexAccountsSelas?user_id=${s.client.id}&print=2&transactions_id=${m.id}`,"_blank")}).catch(C=>{I.value=!1,k.error("\u0644\u0645 \u0627\u0644\u062A\u0639\u062F\u064A\u0644 \u0628\u0646\u062C\u0627\u062D",{timeout:2e3,position:"bottom-right",rtl:!0})})}const E=_e(v,500);function J(s){return`/public/uploadsResized/${s}`}function K(s){return`/public/uploads/${s}`}return(s,a)=>(c(),i(M,null,[l(r(Z),{title:"Dashboard"}),l(ue,{formData:d.value,show:!!r(y),client:U.client,onA:a[0]||(a[0]=t=>G(t)),onClose:a[1]||(a[1]=t=>g(y)?y.value=!1:y=!1)},{header:N(()=>[]),_:1},8,["formData","show","client"]),l(ce,{formData:d.value,show:!!r(x),onA:a[2]||(a[2]=t=>W(t)),onClose:a[3]||(a[3]=t=>g(x)?x.value=!1:x=!1)},{header:N(()=>[]),_:1},8,["formData","show"]),l(pe,{show:!!r(f),formData:d.value,onA:a[4]||(a[4]=t=>O(t)),onClose:a[5]||(a[5]=t=>g(f)?f.value=!1:f=!1)},{header:N(()=>[me]),_:1},8,["show","formData"]),l(re,null,{default:N(()=>[s.$page.props.auth.user.type_id==1||s.$page.props.auth.user.type_id==6?(c(),i("div",ge,[e("div",be,[e("div",xe,[e("div",ye,[e("div",fe,[e("div",ve,[e("div",null,[l(b,{class:"mb-1",for:"pay",value:"\u0628\u062D\u062B"}),e("form",ke,[we,e("div",$e,[Ce,F(e("input",{"onUpdate:modelValue":a[6]||(a[6]=t=>g($)?$.value=t:$=t),onInput:a[7]||(a[7]=(...t)=>r(E)&&r(E)(...t)),type:"text",id:"simple-search",class:"bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500",placeholder:"\u0631\u0642\u0645 \u0627\u0644\u0634\u0627\u0646\u0635\u0649 - \u0631\u0642\u0645 \u0627\u0644\u0633\u064A\u0627\u0631\u0629 - \u0627\u0633\u0645 \u0627\u0644\u062A\u0627\u062C\u0631 - \u0627\u0633\u0645 \u0627\u0644\u0633\u064A\u0627\u0631\u0629",required:""},null,544),[[ee,r($)]])])])]),e("div",null,[l(b,{class:"mb-1",for:"pay",value:"\u0627\u062E\u062A\u064A\u0627\u0631 \u062A\u0627\u062C\u0631"}),F(e("select",{onChange:a[8]||(a[8]=t=>v()),"onUpdate:modelValue":a[9]||(a[9]=t=>g(p)?p.value=t:p=t),id:"default",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 mb-6 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500"},[e("option",Ne,o(s.$t("selectCustomer")),1),e("option",De,o(s.$t("allOwners")),1),(c(!0),i(M,null,z(U.client,(t,_)=>(c(),i("option",{key:_,value:t.id},o(t.name),9,Me))),128))],544),[[te,r(p)]])]),e("div",Ie,[e("div",Ae,[l(b,{for:"from",value:s.$t("from_date")},null,8,["value"]),l(R,{id:"from",type:"date",class:"mt-1 block w-full",modelValue:r(u),"onUpdate:modelValue":a[10]||(a[10]=t=>g(u)?u.value=t:u=t)},null,8,["modelValue"])])]),e("div",Pe,[e("div",ze,[l(b,{for:"to",value:s.$t("to_date")},null,8,["value"]),l(R,{id:"to",type:"date",class:"mt-1 block w-full",modelValue:r(h),"onUpdate:modelValue":a[11]||(a[11]=t=>g(h)?h.value=t:h=t)},null,8,["modelValue"])])]),e("div",Se,[l(b,{for:"pay",value:"\u0641\u0644\u062A\u0631\u0629"}),e("button",{onClick:a[12]||(a[12]=se(t=>v(),["prevent"])),class:"px-6 mb-12 py-2 mt-1 font-bold text-white bg-gray-500 rounded",style:{width:"100%"}},je)]),e("div",Be,[l(b,{for:"pay",value:"\u0637\u0628\u0627\u0639\u0629"}),e("a",{href:`/api/getIndexCar?user_id=${r(p)}&from=${r(u)}&to=${r(h)}&print=1&printExcel=1`,target:"_blank",class:"px-6 mb-12 py-2 mt-1 font-bold text-white bg-green-500 rounded block text-center",style:{width:"100%"}},Fe,8,Ve)]),r(p)?(c(),i("div",Re,[l(b,{for:"pay",value:"\u0637\u0628\u0627\u0639\u0629"}),e("a",{target:"_blank",href:`api/getIndexAccountsSelas?user_id=${r(p)}&from=${r(u)}&to=${r(h)}&print=1&showComplatedCars=0`,class:"px-6 mb-12 py-2 mt-1 font-bold text-white bg-orange-500 rounded d-block",style:{width:"100%",display:"block","text-align":"center"}},Te,8,qe)])):S("",!0)]),e("div",null,[He,e("div",Ge,[e("table",Oe,[e("thead",We,[e("tr",null,[e("th",Je,o(s.$t("car_owner")),1),e("th",Ke,o(s.$t("car_type")),1),e("th",Qe,o(s.$t("year")),1),e("th",Xe,o(s.$t("color")),1),e("th",Ye,o(s.$t("vin")),1),e("th",Ze,o(s.$t("car_number")),1),e("th",et,o(s.$t("dinar")),1),e("th",tt,o(s.$t("dolar_price")),1),e("th",st,o(s.$t("dolar_custom")),1),e("th",ot,o(s.$t("note")),1),e("th",at,o(s.$t("shipping_dolar")),1),e("th",rt,o(s.$t("coc_dolar")),1),e("th",lt,o(s.$t("checkout")),1),e("th",dt,o(s.$t("expenses")),1),e("th",nt,o(s.$t("total")),1),e("th",it,o(s.$t("paid")),1),e("th",ct,o(s.$t("discount")),1),e("th",pt,o(s.$t("date")),1),e("th",ut,o(s.$t("execute")),1),ht])]),e("tbody",null,[(c(!0),i(M,null,z(w.value,t=>{var _,C;return c(),i("tr",{key:t.id,class:oe([t.results==0?"":t.results==1?"bg-red-100 dark:bg-red-900":"bg-green-100 dark:bg-green-900","bg-white border-b dark:bg-gray-900 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-600"])},[e("td",_t,o((_=t.client)==null?void 0:_.name),1),e("td",mt,o(t.car_type),1),e("td",gt,o(t.year),1),e("td",bt,o(t.car_color),1),e("td",xt,o(t.vin),1),e("td",yt,o(t.car_number),1),e("td",ft,o(t.dinar_s),1),e("td",vt,o(t.dolar_price_s),1),e("td",kt,o((t.dinar_s/t.dolar_price_s*100).toFixed(0)||0),1),e("td",wt,o(t.note),1),e("td",$t,o(t.shipping_dolar_s),1),e("td",Ct,o(t.coc_dolar_s),1),e("td",Nt,o(t.checkout_s),1),e("td",Dt,o(t.expenses_s),1),e("td",Mt,o(t.total_s.toFixed(0)),1),e("td",It,o(t.paid),1),e("td",At,o(t.discount),1),e("td",Pt,o(t.date),1),e("td",zt,[e("button",{tabIndex:"1",class:"px-1 py-1 text-white mx-1 bg-slate-500 rounded",onClick:m=>q(t)},[l(ie)],8,St),e("button",{tabIndex:"1",class:"px-1 py-1 text-white mx-1 bg-orange-500 rounded",onClick:m=>L(t)},[l(ne)],8,Ut),t.total_s!=t.paid+t.discount?(c(),i("button",{key:0,tabIndex:"1",class:"px-1 py-1 text-white mx-1 bg-green-500 rounded",onClick:m=>T(t)},[l(de)],8,jt)):S("",!0),l(r(ae),{style:{display:"inline-flex"},className:"px-1 py-1  text-white mx-1 bg-blue-500 rounded d-inline-block",href:s.route("showClients",(C=t.client)==null?void 0:C.id)},{default:N(()=>[l(le)]),_:2},1032,["href"])]),e("td",Bt,[(c(!0),i(M,null,z(t.car_images,(m,Q)=>(c(),i("a",{key:Q,href:K(m.name),style:{cursor:"pointer"},target:"_blank"},[e("img",{src:J(m.name),alt:"",class:"px-1",style:{"max-width":"100px","max-height":"50px",display:"inline"}},null,8,Et)],8,Vt))),128))])],2)}),128))])])])]),e("div",Ft,[l(r(he),{car:w.value,onInfinite:H,identifier:r(A)},null,8,["car","identifier"])]),e("div",null,[e("div",Rt,[e("div",qt,[Lt,e("div",Tt,[e("h2",Ht,o(s.$t("capital")),1),e("p",Gt,o(r(j)),1)])]),e("div",Ot,[Wt,e("div",Jt,[e("h2",Kt,o(s.$t("all_cars")),1),e("p",Qt,o(r(B)),1)])])])])])])])])])):S("",!0),Xt]),_:1})],64))}};export{gs as default};
