import{o as r,a as n,h as t,I as pt,m as u,b as _t,f as d,u as a,e as L,F as T,H as mt,i as b,t as l,g as p,w as ht,y as bt,z as O,k as U,j as q,n as yt}from"./app.9c428bcb.js";import{a as xt,_ as gt}from"./AuthenticatedLayout.02363316.js";import{M as vt}from"./Modal.abeab0d9.js";import{t as ft}from"./laravel-vue-pagination.es.e33a16f4.js";import{_ as c,a as _}from"./TextInput.f8babdd2.js";import{a as F}from"./index.4a252210.js";import{M as kt}from"./ModalDelCar.4abd343f.js";import{_ as wt}from"./ModalEditCar_S.14e8cca8.js";import{_ as $t}from"./ModalAddCarPayment.8ccf0618.js";import{p as Nt}from"./pay.0f11ee28.js";import{t as Ct}from"./trash.f8942522.js";import{e as At}from"./edit.f407fb06.js";/* empty css                                              */const Vt={},Dt={xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24","stroke-width":"1.5",stroke:"currentColor",class:"w-6 h-6"},Pt=t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"},null,-1),Mt=[Pt];function It(z,B){return r(),n("svg",Dt,Mt)}const W=xt(Vt,[["render",It]]);const St=t("h2",{class:"font-semibold text-xl dark:text-gray-400 text-gray-800 leading-tight"}," \u0634\u0631\u0643\u0629 \u0633\u0644\u0627\u0645 \u062C\u0644\u0627\u0644 ",-1),Tt=t("h2",{class:"mb-5 dark:text-gray-400 text-center"}," \u0647\u0644 \u0645\u062A\u0623\u0643\u062F \u0645\u0646 \u062D\u0630\u0641 \u0627\u0644\u0633\u064A\u0627\u0631\u0629 \u061F ",-1),Ut={key:0},Ft={id:"alert-2",class:"p-4 mb-4 bg-red-100 rounded-lg dark:bg-red-200 text-center",role:"alert"},Ht={class:"ml-3 text-sm font-medium text-red-700 dark:text-red-800"},Lt={key:1,class:"py-4"},zt={class:"text-center pb-2 dark:text-gray-400"},Bt={class:"max-w-9xl mx-auto sm:px-6 lg:px-8 p-6 dark:bg-gray-900"},Et={class:"overflow-hidden shadow-sm sm:rounded-lg"},jt={class:"grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2 lg:gap-1"},Rt={class:"pr-4"},Ot={value:"undefined",disabled:""},qt=["value"],Gt={className:"mb-4  mr-5"},Jt={class:"px-4"},Kt={className:"mb-4 mx-5"},Qt={class:"px-4"},Wt={className:"mb-4 mx-5"},Xt={className:"mb-4  mr-5 print:hidden"},Yt=t("span",null,"\u0641\u0644\u062A\u0631\u0629",-1),Zt=[Yt],te={className:"mb-4  mr-5 print:hidden"},ee=["href"],se=t("span",null,"\u0637\u0628\u0627\u0639\u0629",-1),oe=[se],ae={className:"mb-4  mr-5"},le={className:"mb-4  mr-5"},de={className:"mb-4  mr-5"},re={className:"mb-4  mr-5"},ne={className:"mb-4  mr-5"},ie={className:"mb-4  mr-5"},ce={className:"mb-4  mr-5"},ue={className:"mb-4  mr-5"},pe={key:0,className:"mb-4  mr-5 print:hidden"},_e=["disabled"],me=t("span",null,"\u0627\u0636\u0627\u0641\u0629 \u062F\u0641\u0639\u0629",-1),he=[me],be=["disabled"],ye=t("span",null,"\u0627\u062E\u0641\u0627\u0621 \u062F\u0641\u0639\u0629",-1),xe=[ye],ge={className:"mb-4  mr-5 print:hidden"},ve=["disabled"],fe=t("span",null,"\u0639\u0631\u0636 \u0627\u0644\u062F\u0641\u0639\u0627\u062A",-1),ke=[fe],we=t("span",null,"\u0627\u062E\u0641\u0627\u0621 \u0627\u0644\u062F\u0641\u0639\u0627\u062A",-1),$e=[we],Ne={key:0,class:"grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 lg:gap-1"},Ce={className:"mb-4  mr-5"},Ae={className:"mb-4  mr-5"},Ve={className:"mb-4  mr-5"},De={className:"mb-4  mr-5 print:hidden"},Pe=["disabled"],Me={key:0},Ie={key:1},Se={key:2},Te={key:1,class:"relative overflow-x-auto shadow-md sm:rounded-lg mt-4 mb-5"},Ue={class:"w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center"},Fe={class:"text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center"},He={class:"bg-rose-500 text-gray-100 rounded-l-lg mb-2 sm:mb-0"},Le=t("th",{className:"px-1 py-2 text-base"},"#",-1),ze={className:"px-1 py-2 text-base"},Be={className:"px-1 py-2 text-base"},Ee={className:"px-1 py-2 text-base"},je={scope:"col",class:"px-1 py-2 text-base print:hidden",style:{width:"250px"}},Re={class:"text-center px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},Oe=t("td",null,null,-1),qe=t("td",null,null,-1),Ge=t("td",null,null,-1),Je=t("td",null,null,-1),Ke={className:"px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},Qe=["href"],We={key:0,class:"text-center"},Xe={className:"px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},Ye={className:"px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},Ze={className:"px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},ts={className:"px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},es={className:"px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},ss=["href"],os={class:"relative overflow-x-auto shadow-md sm:rounded-lg"},as={class:"w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center"},ls={class:"text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center"},ds={class:"rounded-l-lg mb-2 sm:mb-0"},rs={scope:"col",class:"px-1 py-2 text-base"},ns={scope:"col",class:"px-1 py-2 text-base"},is={scope:"col",class:"px-1 py-2 text-base"},cs={scope:"col",class:"px-1 py-2 text-base"},us={scope:"col",class:"px-1 py-2 text-base"},ps={scope:"col",class:"px-1 py-2 text-base"},_s={scope:"col",class:"px-1 py-2 text-base"},ms={scope:"col",class:"px-1 py-2 text-base"},hs={scope:"col",class:"px-1 py-2 text-base print:hidden"},bs={scope:"col",class:"px-1 py-2 text-base print:hidden"},ys={scope:"col",class:"px-1 py-2 text-base"},xs={scope:"col",class:"px-1 py-2 text-base"},gs={scope:"col",class:"px-1 py-2 text-base"},vs={scope:"col",class:"px-1 py-2 text-base"},fs={scope:"col",class:"px-1 py-2 text-base"},ks={scope:"col",class:"px-1 py-2 text-base"},ws={scope:"col",class:"px-1 py-2 text-base"},$s={scope:"col",class:"px-1 py-2 text-base"},Ns={scope:"col",class:"px-1 py-2 text-base print:hidden",style:{width:"150px"}},Cs={className:"border dark:border-gray-800 text-center px-2 py-1"},As={className:"border dark:border-gray-800 text-center px-2 py-1"},Vs={className:"border dark:border-gray-800 text-center px-2 py-1"},Ds={className:"border dark:border-gray-800 text-center px-2 py-1"},Ps={className:"border dark:border-gray-800 text-center px-2 py-1"},Ms={className:"border dark:border-gray-800 text-center px-2 py-1"},Is={className:"border dark:border-gray-800 text-center px-2 py-1"},Ss={className:"border dark:border-gray-800 text-center px-2 py-1"},Ts={className:"border dark:border-gray-800 text-center px-2 py-1 print:hidden"},Us={className:"border dark:border-gray-800 text-center px-2 py-1 print:hidden"},Fs={className:"border dark:border-gray-800 text-center px-2 py-1"},Hs={className:"border dark:border-gray-800 text-center px-2 py-1"},Ls={className:"border dark:border-gray-800 text-center px-2 py-1"},zs={className:"border dark:border-gray-800 text-center px-2 py-1"},Bs={className:"border dark:border-gray-800 text-center px-2 py-1"},Es={className:"border dark:border-gray-800 text-center px-2 py-1"},js={className:"border dark:border-gray-800 text-center px-1 py-2 "},Rs={className:"border dark:border-gray-800 text-center px-2 py-1"},Os={className:"border dark:border-gray-800 text-start px-2 py-1 print:hidden"},qs=["onClick"],Gs=["onClick"],Js=["onClick"],Ks={class:"mt-3 text-center",style:{direction:"ltr"}},Qs={class:"max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 dark:text-gray-400 hidden print:block"},Ws={class:"flex flex-row"},Xs={class:"basis-1/2"},Ys=t("br",null,null,-1),Zs=t("div",{class:"basis-1/2 text-center"},"\u062A\u0648\u0642\u064A\u0639 \u0642\u0633\u0645 \u0627\u0644\u0645\u062D\u0627\u0633\u0628\u0629",-1),to=t("div",{class:"basis-1/2 text-end"},"\u062A\u0648\u0642\u064A\u0639 \u0627\u0644\u0645\u062F\u064A\u0631",-1),ho={__name:"Show",props:{url:String,clients:Array,client_id:String,client:Object},setup(z){const B=z;let w=pt(),i=u({}),k=u(0),y=u(0),x=u(0),$=u(!1),V=u(!1),D=u(!1),N=u(!1),j=u(!1),P=u(!1);u(0);let g=u({}),M=u(0),I=u(""),C=u(0),m=u(0);u(0);let E=async(o=1)=>{F.get(`/api/getIndexAccountsSelas?page=${o}&user_id=${B.client_id}&from=${y.value}&to=${x.value}`).then(s=>{i.value=s.data,m.value=s.data.client.id}).catch(s=>{console.error(s)})};const H=async(o=1)=>{F.get(`/api/getIndexAccountsSelas?page=${o}&user_id=${m.value}&from=${y.value}&to=${x.value}`).then(s=>{i.value=s.data,m.value=s.data.client.id}).catch(s=>{console.error(s)})};E();const X=_t();let v=u(!1);function Y(o){X.get(route("sentToCourt",o)),E(),v.value=!1}function Z(o={}){g.value=o,D.value=!0}function tt(o={}){g.value=o,g.value.dinar_s==0&&(g.value.dinar_s=g.value.dinar),V.value=!0}function et(o={}){g.value=o,N.value=!0}function st(o){F.post("/api/DelCar",o).then(s=>{D.value=!1,window.location.reload()}).catch(s=>{console.error(s)})}function ot(o){V.value=!1,F.post("/api/updateCarsS",o).then(s=>{v.value=!1,w.success("\u062A\u0645 \u0627\u0644\u062A\u0639\u062F\u064A\u0644 \u0628\u0646\u062C\u0627\u062D",{timeout:2e3,position:"bottom-right",rtl:!0}),H()}).catch(s=>{w.error("\u0644\u0645 \u0627\u0644\u062A\u0639\u062F\u064A\u0644 \u0628\u0646\u062C\u0627\u062D",{timeout:2e3,position:"bottom-right",rtl:!0}),H()})}function at(o){var s,f,A;F.get(`/api/addPaymentCar?car_id=${o.id}&discount=${(s=o.discountPayment)!=null?s:0}&amount=${(f=o.amountPayment)!=null?f:0}&note=${(A=o.notePayment)!=null?A:""}`).then(h=>{N.value=!1,w.success(" \u062A\u0645 \u062F\u0641\u0639 \u0645\u0628\u0644\u063A \u062F\u0648\u0644\u0627\u0631 "+o.amountPayment+" \u0628\u0646\u062C\u0627\u062D ",{timeout:3e3,position:"bottom-right",rtl:!0}),H();let S=h.data;window.open(`/api/getIndexAccountsSelas?user_id=${B.client_id}&print=2&transactions_id=${S.id}`,"_blank")}).catch(h=>{v.value=!1,console.log(h),w.error("\u0644\u0645 \u0627\u0644\u062A\u0639\u062F\u064A\u0644 \u0628\u0646\u062C\u0627\u062D",{timeout:2e3,position:"bottom-right",rtl:!0})})}function lt(o,s,f,A){k.value=!0,F.get(`/api/addPaymentCarTotal?amount=${o!=null?o:0}&discount=${f!=null?f:0}&note=${A}&client_id=${s!=null?s:0}`).then(h=>{N.value=!1,w.success(" \u062A\u0645 \u062F\u0641\u0639 \u0645\u0628\u0644\u063A \u062F\u0648\u0644\u0627\u0631 "+o+" \u0628\u0646\u062C\u0627\u062D ",{timeout:3e3,position:"bottom-right",rtl:!0}),$.value=!1,k.value=!1,H(),dt();let S=h.data;window.open(`/api/getIndexAccountsSelas?user_id=${B.client_id}&print=2&transactions_id=${S.id}`,"_blank")}).catch(h=>{console.log(h),v.value=!1,k.value=!1,w.error("\u0644\u0645 \u0627\u0644\u062A\u0639\u062F\u064A\u0644 \u0628\u0646\u062C\u0627\u062D",{timeout:2e3,position:"bottom-right",rtl:!0})})}function dt(){C.value=0,M.value=0,I.value=""}function rt(){$.value=!0,P.value=!1}function nt(){$.value=!1}function it(){P.value=!0,$.value=!1}function ct(){P.value=!1}function ut(){var o,s;C.value>((o=i.value)==null?void 0:o.cars_need_paid)?(j.value=!0,w.info(" \u0627\u0644\u0645\u0628\u0644\u063A \u0627\u0643\u0628\u0631 \u0645\u0646 \u0627\u0644\u062F\u064A\u0646 \u0627\u0644\u0645\u0637\u0644\u0648\u0628 "+((s=i.value)==null?void 0:s.cars_need_paid),{timeout:4e3,position:"bottom-right",rtl:!0})):j.value=!1}return(o,s)=>(r(),n(T,null,[d(a(mt),{title:"Dashboard"}),d(gt,null,{header:L(()=>[St]),default:L(()=>{var f,A,h,S,G,J,K;return[d(wt,{formData:a(g),show:!!a(V),client:z.clients,onA:s[0]||(s[0]=e=>ot(e)),onClose:s[1]||(s[1]=e=>b(V)?V.value=!1:V=!1)},{header:L(()=>[]),_:1},8,["formData","show","client"]),d($t,{formData:a(g),show:!!a(N),onA:s[2]||(s[2]=e=>at(e)),onClose:s[3]||(s[3]=e=>b(N)?N.value=!1:N=!1)},{header:L(()=>[]),_:1},8,["formData","show"]),d(kt,{show:!!a(D),formData:a(g),onA:s[4]||(s[4]=e=>st(e)),onClose:s[5]||(s[5]=e=>b(D)?D.value=!1:D=!1)},{header:L(()=>[Tt]),_:1},8,["show","formData"]),d(vt,{show:!!a(v),data:a(v).toString(),onA:s[6]||(s[6]=e=>Y(e,o.arg1)),onClose:s[7]||(s[7]=e=>b(v)?v.value=!1:v=!1)},null,8,["show","data"]),o.$page.props.success?(r(),n("div",Ut,[t("div",Ft,[t("div",Ht,l(o.$page.props.success),1)])])):p("",!0),o.$page.props.auth.user.type_id==1?(r(),n("div",Lt,[t("h2",zt,l(o.$t("sales_bill")),1),t("div",Bt,[t("div",Et,[t("div",jt,[t("div",Rt,[d(c,{class:"mb-1",for:"invoice_number",value:o.$t("Account")},null,8,["value"]),ht(t("select",{onChange:s[8]||(s[8]=e=>H()),"onUpdate:modelValue":s[9]||(s[9]=e=>b(m)?m.value=e:m=e),id:"default",class:"pr-8 border border-gray-300 text-gray-900 mb-6 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:border-gray-400 dark:placeholder-gray-400 dark:text-gray-600 dark:focus:ring-red-500 dark:focus:border-red-500"},[t("option",Ot,l(o.$t("selectCustomer")),1),(r(!0),n(T,null,O(z.clients,(e,R)=>(r(),n(T,{key:R},[e.wallet.balance>0||e.id==a(m)?(r(),n("option",{key:0,value:e.id},l(e.name),9,qt)):p("",!0)],64))),128))],544),[[bt,a(m)]])]),t("div",null,[t("div",Gt,[d(c,{for:"totalAmount",value:o.$t("phoneNumber")},null,8,["value"]),d(_,{id:"totalAmount",type:"text",class:"mt-1 block w-full",value:(f=a(i).client)==null?void 0:f.phone,disabled:""},null,8,["value"])])]),t("div",Jt,[t("div",Kt,[d(c,{for:"from",value:o.$t("from_date")},null,8,["value"]),d(_,{id:"from",type:"date",class:"mt-1 block w-full",modelValue:a(y),"onUpdate:modelValue":s[10]||(s[10]=e=>b(y)?y.value=e:y=e)},null,8,["modelValue"])])]),t("div",Qt,[t("div",Wt,[d(c,{for:"to",value:o.$t("to_date")},null,8,["value"]),d(_,{id:"to",type:"date",class:"mt-1 block w-full",modelValue:a(x),"onUpdate:modelValue":s[11]||(s[11]=e=>b(x)?x.value=e:x=e)},null,8,["modelValue"])])]),t("div",Xt,[d(c,{for:"pay",value:"\u0641\u0644\u062A\u0631\u0629"}),t("button",{onClick:s[12]||(s[12]=U(e=>a(E)(),["prevent"])),class:"px-6 mb-12 py-2 mt-1 font-bold text-white bg-gray-500 rounded",style:{width:"100%"}},Zt)]),t("div",te,[d(c,{for:"pay",value:"\u0637\u0628\u0627\u0639\u0629"}),t("a",{href:`/api/getIndexAccountsSelas?user_id=${a(m)}&from=${a(y)}&to=${a(x)}&print=1`,target:"_blank",class:"px-6 mb-12 py-2 mt-1 font-bold text-white bg-orange-500 rounded block text-center",style:{width:"100%"}},oe,8,ee)]),t("div",ae,[d(c,{for:"car_total",value:"\u0645\u062C\u0645\u0648\u0639 \u0627\u0644\u0633\u064A\u0627\u0631\u0627\u062A"}),d(_,{id:"car_total",type:"text",class:"mt-1 block w-full",value:a(i).car_total,disabled:""},null,8,["value"])]),t("div",le,[d(c,{for:"car_total_complete",value:"\u0645\u062C\u0645\u0648\u0639 \u0627\u0644\u0633\u064A\u0627\u0631\u0627\u062A \u0645\u0643\u062A\u0645\u0644"}),d(_,{id:"car_total_complete",type:"text",class:"mt-1 block w-full",value:a(i).car_total_complete,disabled:""},null,8,["value"])]),t("div",de,[d(c,{for:"car_total_unpaid",value:"\u0645\u062C\u0645\u0648\u0639 \u0627\u0644\u0633\u064A\u0627\u0631\u0627\u062A \u063A\u064A\u0631 \u0645\u062F\u0641\u0648\u0639"}),d(_,{id:"car_total_unpaid",type:"text",class:"mt-1 block w-full",value:a(i).car_total_unpaid,disabled:""},null,8,["value"])]),t("div",re,[d(c,{for:"car_total_uncomplete",value:" \u0645\u062C\u0645\u0648\u0639 \u0627\u0644\u0633\u064A\u0627\u0631\u0627\u062A \u0645\u062F\u0641\u0648\u0639 \u0648\u063A\u064A\u0631 \u0645\u0643\u0645\u0644"}),d(_,{id:"car_total_uncomplete",type:"text",class:"mt-1 block w-full",value:a(i).car_total_uncomplete,disabled:""},null,8,["value"])]),t("div",ne,[d(c,{for:"cars_sum",value:o.$t("Total_in_dollars")},null,8,["value"]),d(_,{id:"cars_sum",type:"text",class:"mt-1 block w-full",value:(A=a(i))==null?void 0:A.cars_sum,disabled:""},null,8,["value"])]),t("div",ie,[d(c,{for:"cars_paid",value:"\u0645\u062C\u0645\u0648\u0639 \u0627\u0644\u0645\u062F\u0641\u0648\u0639 \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631"}),d(_,{id:"cars_paid",type:"number",class:"mt-1 block w-full",value:(h=a(i))==null?void 0:h.cars_paid,disabled:""},null,8,["value"])]),t("div",ce,[d(c,{for:"cars_discount",value:"\u0645\u062C\u0645\u0648\u0639 \u0627\u0644\u062E\u0635\u0648\u0645\u0627\u062A \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631"}),d(_,{id:"cars_discount",type:"text",class:"mt-1 block w-full",value:(S=a(i))==null?void 0:S.cars_discount,disabled:""},null,8,["value"])]),t("div",ue,[d(c,{for:"cars_need_paid",value:"\u0645\u062C\u0645\u0648\u0639 \u0627\u0644\u062F\u064A\u0646 \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631"}),d(_,{id:"cars_need_paid",type:"text",class:"mt-1 block w-full",value:(G=a(i))==null?void 0:G.cars_need_paid,disabled:""},null,8,["value"])]),(J=a(i))!=null&&J.cars_need_paid?(r(),n("div",pe,[d(c,{for:"pay",value:"\u0627\u0636\u0627\u0641\u0629 \u062F\u0641\u0639\u0629"}),a($)?p("",!0):(r(),n("button",{key:0,onClick:s[13]||(s[13]=U(e=>rt(),["prevent"])),disabled:a(k),class:"px-6 mb-12 py-2 mt-1 font-bold text-white bg-green-500 rounded",style:{width:"100%"}},he,8,_e)),a($)?(r(),n("button",{key:1,onClick:s[14]||(s[14]=U(e=>nt(),["prevent"])),disabled:a(k),class:"px-6 mb-12 py-2 mt-1 font-bold text-white bg-pink-500 rounded",style:{width:"100%"}},xe,8,be)):p("",!0)])):p("",!0),t("div",ge,[d(c,{for:"pay",value:"\u0639\u0631\u0636 \u0627\u0644\u062F\u0641\u0639\u0627\u062A"}),a(P)?p("",!0):(r(),n("button",{key:0,onClick:s[15]||(s[15]=U(e=>it(),["prevent"])),disabled:a(k),class:"px-6 mb-12 py-2 mt-1 font-bold text-white bg-purple-500 rounded",style:{width:"100%"}},ke,8,ve)),a(P)?(r(),n("button",{key:1,onClick:s[16]||(s[16]=U(e=>ct(),["prevent"])),class:"px-6 mb-12 py-2 mt-1 font-bold text-white bg-pink-500 rounded",style:{width:"100%"}},$e)):p("",!0)])]),a($)?(r(),n("div",Ne,[t("div",Ce,[d(c,{for:"percentage",value:" \u0627\u0644\u0645\u0628\u0644\u063A \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631 \u0627\u0644\u0645\u0631\u0627\u062F \u062F\u0641\u0639\u0647"}),d(_,{id:"percentage",type:"number",onInput:ut,class:"mt-1 block w-full",modelValue:a(C),"onUpdate:modelValue":s[17]||(s[17]=e=>b(C)?C.value=e:C=e)},null,8,["modelValue"])]),t("div",Ae,[d(c,{for:"discount",value:"\u0627\u0644\u062E\u0635\u0645"}),d(_,{id:"discount",type:"number",class:"mt-1 block w-full",modelValue:a(M),"onUpdate:modelValue":s[18]||(s[18]=e=>b(M)?M.value=e:M=e)},null,8,["modelValue"])]),t("div",Ve,[d(c,{for:"discount",value:"\u0645\u0644\u0627\u062D\u0638\u0629"}),d(_,{id:"discount",type:"text",class:"mt-1 block w-full",modelValue:a(I),"onUpdate:modelValue":s[19]||(s[19]=e=>b(I)?I.value=e:I=e)},null,8,["modelValue"])]),t("div",De,[d(c,{for:"pay",value:"\u062A\u0623\u0643\u064A\u062F \u0627\u0644\u062F\u0641\u0639"}),t("button",{onClick:s[20]||(s[20]=U(e=>lt(a(C),a(m),a(M),a(I)),["prevent"])),disabled:a(k),class:"px-6 mb-12 py-2 mt-1 font-bold text-white bg-green-500 rounded",style:{width:"100%"}},[a(j)?(r(),n("span",Me,"\u064A\u0631\u062C\u0649 \u0645\u0631\u0627\u062C\u0639\u0629 \u0627\u0644\u0645\u0628\u0644\u063A \u0644")):p("",!0),a(k)?(r(),n("span",Se,"\u062C\u0627\u0631\u064A \u0627\u0644\u0637\u0628\u0627\u0639\u0629...")):(r(),n("span",Ie,"\u062F\u0641\u0639"))],8,Pe)])])):p("",!0),a(P)?(r(),n("div",Te,[t("table",Ue,[t("thead",Fe,[t("tr",He,[Le,t("th",ze,l(o.$t("date")),1),t("th",Be,l(o.$t("description")),1),t("th",Ee,l(o.$t("amount")),1),t("th",je,l(o.$t("execute")),1)])]),t("tbody",null,[t("tr",Re,[Oe,qe,Ge,Je,t("td",Ke,[t("a",{target:"_blank",style:{display:"inline-flex"},href:`/api/getIndexAccountsSelas?user_id=${a(i).client.id}&from=${a(y)}&to=${a(x)}&print=4`,tabIndex:"1",class:"px-4 py-1 text-white m-1 bg-blue-500 rounded"},[q(" \u062C\u0645\u064A\u0639 \u0627\u0644\u062F\u0641\u0639\u0627\u062A "),d(W)],8,Qe)])]),(r(!0),n(T,null,O(a(i).transactions,e=>(r(),n(T,{key:e.id},[e.type=="out"&&e.amount<0&&e.is_pay==1?(r(),n("tr",We,[t("td",Xe,l(e.id),1),t("td",Ye,l(e.created),1),t("td",Ze,l(e.description),1),t("td",ts,l(e.amount*-1),1),t("td",es,[e.type=="out"&&e.amount<0?(r(),n("a",{key:0,target:"_blank",style:{display:"inline-flex"},href:`/api/getIndexAccountsSelas?user_id=${a(i).client.id}&from=${a(y)}&to=${a(x)}&print=2&transactions_id=${e.id}`,tabIndex:"1",class:"px-4 py-1 text-white m-1 bg-green-500 rounded"},[d(W)],8,ss)):p("",!0)])])):p("",!0)],64))),128))])])])):p("",!0),t("div",null,[t("div",os,[t("table",as,[t("thead",ls,[t("tr",ds,[t("th",rs,l(o.$t("no")),1),t("th",ns,l(o.$t("car_type")),1),t("th",is,l(o.$t("year")),1),t("th",cs,l(o.$t("color")),1),t("th",us,l(o.$t("vin")),1),t("th",ps,l(o.$t("car_number")),1),t("th",_s,l(o.$t("dinar")),1),t("th",ms,l(o.$t("dolar_price")),1),t("th",hs,l(o.$t("dolar_custom")),1),t("th",bs,l(o.$t("note")),1),t("th",ys,l(o.$t("shipping_dolar")),1),t("th",xs,l(o.$t("coc_dolar")),1),t("th",gs,l(o.$t("checkout")),1),t("th",vs,l(o.$t("expenses")),1),t("th",fs,l(o.$t("total")),1),t("th",ks,l(o.$t("paid")),1),t("th",ws,l(o.$t("discount")),1),t("th",$s,l(o.$t("date")),1),t("th",Ns,l(o.$t("execute")),1)])]),t("tbody",null,[(r(!0),n(T,null,O(a(i).data,(e,R)=>(r(),n("tr",{key:e.id,class:yt([e.results==0?"":e.results==1?"bg-red-100 dark:bg-red-900":"bg-green-100 dark:bg-green-900","border-b dark:bg-gray-900 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-600"])},[t("td",Cs,l(R+1),1),t("td",As,l(e.car_type),1),t("td",Vs,l(e.year),1),t("td",Ds,l(e.car_color),1),t("td",Ps,l(e.vin),1),t("td",Ms,l(e.car_number),1),t("td",Is,l(e.dinar_s),1),t("td",Ss,l(e.dolar_price_s),1),t("td",Ts,l((e.dinar_s/e.dolar_price_s*100).toFixed(0)||0),1),t("td",Us,l(e.note),1),t("td",Fs,l(e.shipping_dolar_s),1),t("td",Hs,l(e.coc_dolar_s),1),t("td",Ls,l(e.checkout_s),1),t("td",zs,l(e.expenses_s),1),t("td",Bs,l(e.total_s.toFixed(0)),1),t("td",Es,l(e.paid),1),t("td",js,l(e.discount),1),t("td",Rs,l(e.date),1),t("td",Os,[t("button",{tabIndex:"1",class:"px-1 py-1 text-white mx-1 bg-slate-500 rounded",onClick:Q=>tt(e)},[d(At)],8,qs),t("button",{tabIndex:"1",class:"px-1 py-1 text-white mx-1 bg-orange-500 rounded",onClick:Q=>Z(e)},[d(Ct)],8,Gs),e.total_s!=e.paid+e.discount?(r(),n("button",{key:0,tabIndex:"1",class:"px-1 py-1 text-white mx-1 bg-green-500 rounded",onClick:Q=>et(e)},[d(Nt)],8,Js)):p("",!0)])],2))),128))])])])]),t("div",Ks,[d(a(ft),{data:a(i),onPaginationChangePage:a(E),limit:2},null,8,["data","onPaginationChangePage"])])])])])):p("",!0),t("div",Qs,[t("div",Ws,[t("div",Xs,[q(" \u062A\u0648\u0642\u064A\u0639 \u0635\u0627\u062D\u0628 \u0627\u0644\u062D\u0633\u0627\u0628 "),Ys,q(" "+l((K=a(i).client)==null?void 0:K.name),1)]),Zs,to])])]}),_:1})],64))}};export{ho as default};
