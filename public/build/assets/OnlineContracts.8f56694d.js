import{m as i,o as c,d as z,e as v,a as u,h as t,r as O,w as m,C as b,t as o,g as p,T as E,s as L,I as G,f as h,u as g,i as $,F as C,H as J,y as K,z as B,n as Q}from"./app.ff76a5b9.js";import{_ as W}from"./AuthenticatedLayout.af2769c1.js";import"./vue-tailwind-datepicker.ac9a424a.js";import{t as U}from"./laravel-vue-pagination.es.07de9000.js";import{a as D}from"./index.c1e29c5b.js";const X={key:0,class:"modal-mask"},Y={class:"modal-wrapper"},Z={class:"modal-container dark:bg-gray-900"},tt={class:"modal-header"},et={class:"modal-body"},at={className:"mb-4 mx-5"},ot=t("label",{class:"dark:text-gray-200",for:"amountTotal"},"\u0633\u0639\u0631 \u0627\u0644\u0639\u0642\u062F",-1),rt={className:"mb-4 mx-5"},st={class:"dark:text-gray-200",for:"amountPayment"},dt={className:"mb-4 mx-5"},nt={class:"dark:text-gray-200",for:"notePayment"},lt={class:"modal-footer my-2"},it={class:"flex flex-row"},ct={class:"basis-1/2 px-4"},ut={class:"basis-1/2 px-4"},gt=["disabled"],mt={__name:"ModalAddCarContracts",props:{show:Boolean,company:Array,color:Array,carModel:Array,name:Array,client:Array,user:Array,expenses:Array,formData:Object},setup(s){return i(0),(l,r)=>(c(),z(E,{name:"modal"},{default:v(()=>[s.show?(c(),u("div",X,[t("div",Y,[t("div",Z,[t("div",tt,[O(l.$slots,"header")]),t("div",et,[t("div",null,[t("div",at,[ot,m(t("input",{id:"amountTotal",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":r[0]||(r[0]=d=>s.formData.amountTotal=d)},null,512),[[b,s.formData.amountTotal]])]),t("div",rt,[t("label",st,o(l.$t("amount")),1),m(t("input",{id:"amountPayment",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":r[1]||(r[1]=d=>s.formData.amountPaid=d)},null,512),[[b,s.formData.amountPaid]])]),t("div",dt,[t("label",nt,o(l.$t("note")),1),m(t("input",{id:"notePayment",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":r[2]||(r[2]=d=>s.formData.note=d)},null,512),[[b,s.formData.note]])])])]),t("div",lt,[t("div",it,[t("div",ct,[t("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:r[3]||(r[3]=d=>{l.$emit("close")})},o(l.$t("cancel")),1)]),t("div",ut,[t("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:r[4]||(r[4]=d=>{l.$emit("a",s.formData),s.formData=""}),disabled:!s.formData.amountTotal},o(l.$t("yes")),9,gt)])])])])])])):p("",!0)]),_:3}))}};const yt={key:0,class:"modal-mask"},bt={class:"modal-wrapper"},ft={class:"modal-container dark:bg-gray-900"},ht={class:"modal-header"},pt={class:"modal-body"},xt={class:"grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 gap-3 lg:gap-3"},vt={className:"mb-4 mx-5"},kt={class:"dark:text-gray-200",for:"user_id"},_t={className:"mb-4 mx-5"},wt={class:"dark:text-gray-200",for:"user_id"},$t={className:"mb-4 mx-5"},Ct={class:"dark:text-gray-200",for:"userId"},Dt=["value"],Pt={className:"mb-4 mx-5"},At={class:"dark:text-gray-200",for:"amountPayment"},Nt={className:"mb-4 mx-5"},Mt={class:"dark:text-gray-200",for:"notePayment"},Tt={class:"modal-footer my-2"},It={class:"flex flex-row"},jt={class:"basis-1/2 px-4"},Bt={class:"basis-1/2 px-4"},Ut=["disabled"],zt={__name:"ModalEditCarContracts",props:{show:Boolean,company:Array,color:Array,carModel:Array,name:Array,client:Array,user:Array,expenses:Array,formData:Object},setup(s){return i(0),(l,r)=>(c(),z(E,{name:"modal"},{default:v(()=>[s.show?(c(),u("div",yt,[t("div",bt,[t("div",ft,[t("div",ht,[O(l.$slots,"header")]),t("div",pt,[t("div",xt,[t("div",vt,[t("label",kt,o(l.$t("totalForCar")),1),m(t("input",{id:"id",type:"text",style:{display:"none"},disabled:"","onUpdate:modelValue":r[0]||(r[0]=d=>s.formData.id=d)},null,512),[[b,s.formData.id]]),m(t("input",{id:"id",type:"text",disabled:"",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":r[1]||(r[1]=d=>s.formData.contract.price=d)},null,512),[[b,s.formData.contract.price]])]),t("div",_t,[t("label",wt,o(l.$t("paid_amount")),1),m(t("input",{id:"id",type:"text",disabled:"",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":r[2]||(r[2]=d=>s.formData.contract.paid=d)},null,512),[[b,s.formData.contract.paid]])]),t("div",$t,[t("label",Ct,o(l.$t("debtRemaining")),1),t("input",{id:"id",type:"text",disabled:"",value:s.formData.contract.price-s.formData.contract.paid,class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"},null,8,Dt)])]),t("div",null,[t("div",Pt,[t("label",At,o(l.$t("amount")),1),m(t("input",{id:"amountPayment",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":r[3]||(r[3]=d=>s.formData.amountPayment=d)},null,512),[[b,s.formData.amountPayment]])]),t("div",Nt,[t("label",Mt,o(l.$t("note")),1),m(t("input",{id:"notePayment",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":r[4]||(r[4]=d=>s.formData.notePayment=d)},null,512),[[b,s.formData.notePayment]])])])]),t("div",Tt,[t("div",It,[t("div",jt,[t("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:r[5]||(r[5]=d=>{l.$emit("close")})},o(l.$t("cancel")),1)]),t("div",Bt,[t("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:r[6]||(r[6]=d=>{l.$emit("a",s.formData),s.formData=""}),disabled:!s.formData.amountPayment},o(l.$t("yes")),9,Ut)])])])])])])):p("",!0)]),_:3}))}},Ot={key:0,class:"py-2"},Et={class:"max-w-9xl mx-auto sm:px-6 lg:px-8"},St={class:"bg-white overflow-hidden shadow-sm"},Vt={class:"p-6 dark:bg-gray-900"},Rt={class:"flex flex-col"},Ft={class:"grid grid-cols-2 md:grid-cols-5 lg:grid-cols-5 gap-2 lg:gap-1"},qt={class:"flex items-center max-w-5xl"},Ht=t("label",{class:"dark:text-gray-200",for:"simple-search"},null,-1),Lt={class:"relative w-full"},Gt=t("div",{class:"absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"},[t("svg",{"aria-hidden":"true",class:"w-5 h-5 text-gray-500 dark:text-gray-200 dark:text-gray-400",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[t("path",{"fill-rule":"evenodd",d:"M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z","clip-rule":"evenodd"})])],-1),Jt={value:"undefined",disabled:""},Kt={value:""},Qt=["value"],Wt={class:"mt-3 text-center",style:{direction:"ltr"}},Xt={class:"relative overflow-x-auto shadow-md sm:rounded-lg"},Yt={class:"w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center"},Zt={class:"text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center"},te={scope:"col",class:"px-1 py-3 text-base"},ee={scope:"col",class:"px-1 py-3 text-base"},ae={scope:"col",class:"px-1 py-3 text-base"},oe={scope:"col",class:"px-1 py-3 text-base"},re={scope:"col",class:"px-1 py-3 text-base"},se={scope:"col",class:"px-1 py-3 text-base"},de={scope:"col",class:"px-1 py-3 text-base"},ne={scope:"col",class:"px-1 py-3 text-base"},le={scope:"col",class:"px-1 py-3 text-base",style:{width:"250px"}},ie={className:"border dark:border-gray-800 text-center px-2 py-2 "},ce={className:"border dark:border-gray-800 text-center px-2 py-2 "},ue={className:"border dark:border-gray-800 text-center px-2 py-2 "},ge={className:"border dark:border-gray-800 text-center px-2 py-2 "},me={className:"border dark:border-gray-800 text-center px-2 py-2 "},ye={className:"border dark:border-gray-800 text-center px-2 py-2 "},be={className:"border dark:border-gray-800 text-center px-2 py-2 "},fe={className:"border dark:border-gray-800 text-center px-2 py-2 "},he={className:"border dark:border-gray-800 text-start px-2 py-2"},pe=["onClick"],xe=["onClick"],ve={key:2,tabIndex:"1",class:"px-2 py-1 text-white mx-1 bg-green-500 rounded"},ke={class:"mt-3 text-center",style:{direction:"ltr"}},_e={class:"mt-4 grid grid-cols-1 gap-4 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-7"},we={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},$e=t("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50"},[t("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-orange-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"})])],-1),Ce={class:"mr-4"},De={class:"font-semibold"},Pe={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},Ae={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},Ne=t("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50"},[t("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-orange-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"})])],-1),Me={class:"mr-4"},Te={class:"font-semibold"},Ie={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},je={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},Be=t("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50"},[t("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-red-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"})])],-1),Ue={class:"mr-4"},ze={class:"font-semibold"},Oe={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},Ee=t("div",null,null,-1),Le={__name:"OnlineContracts",props:{client:Array},setup(s){L(),i({});const l=G();let r=i(""),d=i(!1),f=i(!1),P=i(0),A=i(0),N=i(0);function S(e={}){k.value=e,d.value=!0}function V(e={}){k.value=e,f.value=!0}const k=i({});i({});const x=i([]);i({startDate:"",endDate:""});const _=async(e=1,n="")=>{const a=await fetch(`/getIndexCar?page=${e}&user_id=${n}`);x.value=await a.json()},R=async(e="",n=1)=>{const a=await fetch(`/getIndexCarSearch?page=${n}&q=${e}`);x.value=await a.json()};(async()=>{D.get("/api/totalInfo").then(e=>{P.value=e.data.data.onlineContracts,A.value=e.data.data.debtOnlineContracts,N.value=e.data.data.allCars}).catch(e=>{console.error(e)})})(),_();function F(e){var n,a,y;D.get(`/api/addCarContracts?car_id=${e.id}&amountTotal=${(n=e.amountTotal)!=null?n:0}&amountPaid=${(a=e.amountPaid)!=null?a:0}&note=${(y=e.note)!=null?y:""}`).then(w=>{d.value=!1,l.success(" \u062A\u0645 \u062F\u0641\u0639 \u0645\u0628\u0644\u063A \u062F\u0648\u0644\u0627\u0631 "+e.amountPayment+" \u0628\u0646\u062C\u0627\u062D ",{timeout:3e3,position:"bottom-right",rtl:!0}),window.location.reload()}).catch(w=>{d.value=!1,l.error("\u0644\u0645 \u0627\u0644\u062A\u0639\u062F\u064A\u0644 \u0628\u0646\u062C\u0627\u062D",{timeout:2e3,position:"bottom-right",rtl:!0})})}function q(e){var n,a;D.get(`/api/editCarContracts?car_id=${e.id}&amountPayment=${(n=e.amountPayment)!=null?n:0}&note=${(a=e.notePayment)!=null?a:""}`).then(y=>{f.value=!1,l.success(" \u062A\u0645 \u062F\u0641\u0639 \u0645\u0628\u0644\u063A \u062F\u0648\u0644\u0627\u0631 "+e.amountPayment+" \u0628\u0646\u062C\u0627\u062D ",{timeout:3e3,position:"bottom-right",rtl:!0}),window.location.reload()}).catch(y=>{f.value=!1,l.error("\u0644\u0645 \u0627\u0644\u062A\u0639\u062F\u064A\u0644 \u0628\u0646\u062C\u0627\u062D",{timeout:2e3,position:"bottom-right",rtl:!0})})}return(e,n)=>(c(),u(C,null,[h(g(J),{title:"Dashboard"}),h(mt,{formData:k.value,show:!!g(d),user:e.user,onA:n[0]||(n[0]=a=>F(a)),onClose:n[1]||(n[1]=a=>$(d)?d.value=!1:d=!1)},{header:v(()=>[]),_:1},8,["formData","show","user"]),h(zt,{formData:k.value,show:!!g(f),user:e.user,onA:n[2]||(n[2]=a=>q(a)),onClose:n[3]||(n[3]=a=>$(f)?f.value=!1:f=!1)},{header:v(()=>[]),_:1},8,["formData","show","user"]),h(W,null,{default:v(()=>[e.$page.props.auth.user.type_id==1?(c(),u("div",Ot,[t("div",Et,[t("div",St,[t("div",Vt,[t("div",Rt,[t("div",Ft,[t("div",null,[t("form",qt,[Ht,t("div",Lt,[Gt,m(t("input",{"onUpdate:modelValue":n[4]||(n[4]=a=>$(r)?r.value=a:r=a),onInput:n[5]||(n[5]=a=>R(g(r))),type:"text",id:"simple-search",class:"bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500",placeholder:"\u0628\u062D\u062B",required:""},null,544),[[b,g(r)]])])])]),t("div",null,[m(t("select",{onChange:n[6]||(n[6]=a=>_(1,e.user_id)),"onUpdate:modelValue":n[7]||(n[7]=a=>e.user_id=a),id:"default",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 mb-6 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500"},[t("option",Jt,o(e.$t("selectCustomer")),1),t("option",Kt,o(e.$t("allOwners")),1),(c(!0),u(C,null,B(s.client,(a,y)=>(c(),u("option",{key:y,value:a.id},o(a.name),9,Qt))),128))],544),[[K,e.user_id]])])]),t("div",null,[t("div",null,[t("div",Wt,[h(g(U),{data:x.value,onPaginationChangePage:_,limit:10,"item-classes":["bg-white","dark:bg-gray-600","text-gray-500","dark:text-gray-300","border-gray-300","dark:border-gray-900","hover:bg-gray-200"],activeClasses:["bg-rose-50","border-rose-500","text-rose-600"]},null,8,["data"])])]),t("div",Xt,[t("table",Yt,[t("thead",Zt,[t("tr",null,[t("th",te,o(e.$t("no")),1),t("th",ee,o(e.$t("car_owner")),1),t("th",ae,o(e.$t("car_type")),1),t("th",oe,o(e.$t("year")),1),t("th",re,o(e.$t("color")),1),t("th",se,o(e.$t("vin")),1),t("th",de,o(e.$t("car_number")),1),t("th",ne,o(e.$t("date")),1),t("th",le,o(e.$t("execute")),1)])]),t("tbody",null,[(c(!0),u(C,null,B(x.value.data,a=>{var y,w,M,T,I,j;return c(),u("tr",{key:a.id,class:Q([a.results==0?"":a.results==1?"bg-red-100 dark:bg-red-900":"bg-green-100 dark:bg-green-900","bg-white border-b dark:bg-gray-900 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-600"])},[t("td",ie,o(a.no),1),t("td",ce,o((y=a.client)==null?void 0:y.name),1),t("td",ue,o(a.car_type),1),t("td",ge,o(a.year),1),t("td",me,o(a.car_color),1),t("td",ye,o(a.vin),1),t("td",be,o(a.car_number),1),t("td",fe,o(a.date),1),t("td",he,[a.contract&&((w=a.contract)==null?void 0:w.price)!=((M=a.contract)==null?void 0:M.paid)?(c(),u("button",{key:0,tabIndex:"1",class:"px-2 py-1 text-white mx-1 bg-red-500 rounded",onClick:H=>V(a)},o(e.$t("complet_pay")),9,pe)):p("",!0),a.contract?p("",!0):(c(),u("button",{key:1,tabIndex:"1",class:"px-2 py-1 text-white mx-1 bg-green-500 rounded",onClick:H=>S(a)},o(e.$t("create_contract")),9,xe)),a.contract&&((T=a.contract)==null?void 0:T.price)==((I=a.contract)==null?void 0:I.paid)?(c(),u("button",ve," \u062A\u0645 \u062F\u0641\u0639 \u0645\u0628\u0644\u063A "+o((j=a.contract)==null?void 0:j.price),1)):p("",!0)])],2)}),128))])])]),t("div",ke,[h(g(U),{data:x.value,onPaginationChangePage:_,limit:10,"item-classes":["bg-white","dark:bg-gray-600","text-gray-500","dark:text-gray-300","border-gray-300","dark:border-gray-900","hover:bg-gray-200"],activeClasses:["bg-rose-50","border-rose-500","text-rose-600"]},null,8,["data"])])]),t("div",null,[t("div",_e,[t("div",we,[$e,t("div",Ce,[t("h2",De,o(e.$t("online_contracts")),1),t("p",Pe,o(g(P)),1)])]),t("div",Ae,[Ne,t("div",Me,[t("h2",Te,o(e.$t("debtOnlineContracts")),1),t("p",Ie,o(g(A)),1)])]),t("div",je,[Be,t("div",Ue,[t("h2",ze,o(e.$t("all_cars")),1),t("p",Oe,o(g(N)),1)])])])])])])])])])):p("",!0),Ee]),_:1})],64))}};export{Le as default};
