import{m as i,o as x,d as de,e as b,a as k,h as e,r as ne,t as r,u as m,w as g,y as R,F as z,z as j,g as F,C as f,T as le,s as ie,I as ce,f as p,i as h,H as ue,n as me}from"./app.a2e0d6f8.js";import{_ as ge}from"./AuthenticatedLayout.9c0cbbc0.js";import"./vue-tailwind-datepicker.11a06505.js";import{M as pe}from"./Modal.785f29fe.js";import{_ as fe,a as ye,b as be,c as he,d as xe,e as ke,f as ve}from"./index.c5cd169b.js";import{_ as we,M as $e}from"./ModalAddCarPayment.4859dd52.js";import{t as _e}from"./laravel-vue-pagination.es.d0c5aa39.js";import{a as C}from"./index.a1c8d068.js";/* empty css                                              */const De={key:0,class:"modal-mask"},Ce={class:"modal-wrapper"},Me={class:"modal-container dark:bg-gray-900"},Ne={class:"modal-header"},Te={class:"text-center dark:text-gray-200"},Ae={class:"modal-body"},Ie={class:"grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-1 lg:gap-2"},Ue={class:"mb-4 mx-1"},Be={class:"dark:text-gray-200",for:"color_id"},Ee={class:"relative"},ze={selected:"",disabled:""},Fe=["value"],Se={class:"grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-1 lg:gap-2"},Pe={className:"mb-4 mx-1"},je={class:"dark:text-gray-200",for:"pin"},Ve={className:"mb-4 mx-1"},Ye={class:"dark:text-gray-200",for:"pin"},Ge={className:"mb-4 mx-1"},Re={class:"dark:text-gray-200",for:"pin"},qe={className:"mb-4 mx-1"},He={class:"dark:text-gray-200",for:"pin"},Oe={key:0,class:"text-red-700"},Le={className:"mb-4 mx-1"},We={class:"dark:text-gray-200",for:"car_number"},Je={className:"mb-4 mx-1"},Ke={class:"dark:text-gray-200",for:"dinar"},Qe={className:"mb-4 mx-1"},Xe={class:"dark:text-gray-200",for:"dolar_price"},Ze={className:"mb-4 mx-1"},eo={class:"dark:text-gray-200",for:"shipping_dolar"},oo={className:"mb-4 mx-1"},to={class:"dark:text-gray-200",for:"coc_dolar"},ao={className:"mb-4 mx-1"},ro={class:"dark:text-gray-200",for:"checkout"},so={className:"mb-4 mx-1"},no={class:"dark:text-gray-200",for:"expenses"},lo={className:"mb-4 mx-1"},io={class:"dark:text-gray-200",for:"date"},co={className:"mb-4 mx-1"},uo={class:"dark:text-gray-200",for:"note"},mo={class:"modal-footer my-2"},go={class:"flex flex-row"},po={class:"basis-1/2 px-4"},fo={class:"basis-1/2 px-4"},yo=["disabled"],bo={__name:"ModalEditCars",props:{show:Boolean,formData:Object,client:Array},setup(d){function c(){const l=new Date,s=l.getFullYear(),n=String(l.getMonth()+1).padStart(2,"0"),w=String(l.getDate()).padStart(2,"0");return`${s}-${n}-${w}`}function S(l){l&&axios.get(`/api/check_vin?car_vin=${l}`).then(s=>{v.value=s.data}).catch(s=>{console.error(s)})}let $=i(!1),v=i(!1);return(l,s)=>(x(),de(le,{name:"modal"},{default:b(()=>[d.show?(x(),k("div",De,[e("div",Ce,[e("div",Me,[e("div",Ne,[ne(l.$slots,"header",{},()=>[e("h2",Te,r(l.$t("edit_car")),1)])]),e("div",Ae,[e("div",Ie,[e("div",Ue,[e("label",Be,r(l.$t("car_owner")),1),e("div",Ee,[m($)?F("",!0):g((x(),k("select",{key:0,"onUpdate:modelValue":s[0]||(s[0]=n=>d.formData.client_id=n),id:"color_id",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500",disabled:""},[e("option",ze,r(l.$t("selectCustomer")),1),(x(!0),k(z,null,j(d.client,(n,w)=>(x(),k("option",{key:w,value:n.id},r(n.name),9,Fe))),128))],512)),[[R,d.formData.client_id]])])])]),e("div",Se,[e("div",Pe,[e("label",je,r(l.$t("car_type")),1),g(e("input",{id:"car_type",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":s[1]||(s[1]=n=>d.formData.car_type=n)},null,512),[[f,d.formData.car_type]])]),e("div",Ve,[e("label",Ye,r(l.$t("year")),1),g(e("input",{id:"year",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":s[2]||(s[2]=n=>d.formData.year=n)},null,512),[[f,d.formData.year]])]),e("div",Ge,[e("label",Re,r(l.$t("color")),1),g(e("input",{id:"car_color",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":s[3]||(s[3]=n=>d.formData.car_color=n)},null,512),[[f,d.formData.car_color]])]),e("div",qe,[e("label",He,r(l.$t("vin")),1),g(e("input",{id:"vin",type:"text",onChange:s[4]||(s[4]=n=>S(d.formData.vin)),class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":s[5]||(s[5]=n=>d.formData.vin=n)},null,544),[[f,d.formData.vin]]),m(v)?(x(),k("div",Oe," \u0631\u0642\u0645 \u0627\u0644\u0634\u0627\u0635\u064A \u0645\u0633\u062A\u062E\u062F\u0645 ")):F("",!0)]),e("div",Le,[e("label",We,r(l.$t("car_number")),1),g(e("input",{id:"car_number",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":s[6]||(s[6]=n=>d.formData.car_number=n)},null,512),[[f,d.formData.car_number]])]),e("div",Je,[e("label",Ke,r(l.$t("dinar")),1),g(e("input",{id:"dinar",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":s[7]||(s[7]=n=>d.formData.dinar=n)},null,512),[[f,d.formData.dinar]])]),e("div",Qe,[e("label",Xe,r(l.$t("dolar_price")),1),g(e("input",{id:"dolar_price",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":s[8]||(s[8]=n=>d.formData.dolar_price=n)},null,512),[[f,d.formData.dolar_price]])]),e("div",Ze,[e("label",eo,r(l.$t("shipping_dolar")),1),g(e("input",{id:"shipping_dolar",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":s[9]||(s[9]=n=>d.formData.shipping_dolar=n)},null,512),[[f,d.formData.shipping_dolar]])]),e("div",oo,[e("label",to,r(l.$t("coc_dolar")),1),g(e("input",{id:"coc_dolar",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":s[10]||(s[10]=n=>d.formData.coc_dolar=n)},null,512),[[f,d.formData.coc_dolar]])]),e("div",ao,[e("label",ro,r(l.$t("checkout")),1),g(e("input",{id:"checkout",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":s[11]||(s[11]=n=>d.formData.checkout=n)},null,512),[[f,d.formData.checkout]])]),e("div",so,[e("label",no,r(l.$t("expenses")),1),g(e("input",{id:"expenses",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":s[12]||(s[12]=n=>d.formData.expenses=n)},null,512),[[f,d.formData.expenses]])]),e("div",lo,[e("label",io,r(l.$t("date")),1),g(e("input",{id:"date",type:"date",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":s[13]||(s[13]=n=>d.formData.date=n)},null,512),[[f,d.formData.date]])])]),e("div",co,[e("label",uo,r(l.$t("note")),1),g(e("input",{id:"note",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":s[14]||(s[14]=n=>d.formData.note=n)},null,512),[[f,d.formData.note]])])]),e("div",mo,[e("div",go,[e("div",po,[e("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:s[15]||(s[15]=n=>l.$emit("close"))},r(l.$t("cancel")),1)]),e("div",fo,[e("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:s[16]||(s[16]=n=>{d.formData.date=d.formData.date?d.formData.date:c(),l.$emit("a",d.formData),d.formData=""}),disabled:!d.formData.client_id&&!d.formData.client_name},r(l.$t("yes")),9,yo)])])])])])])):F("",!0)]),_:3}))}},ho=e("h2",{class:"text-center",style:{"font-size":"20px"}}," \u0647\u0644 \u0645\u062A\u0623\u0643\u062F \u0645\u0646 \u062A\u0639\u062F\u064A\u0644 \u0627\u0644\u0628\u064A\u0627\u0646\u0627\u062A ",-1),xo=e("h2",{class:"mb-5 dark:text-white text-center"}," \u0647\u0644 \u0645\u062A\u0623\u0643\u062F \u0645\u0646 \u062D\u0630\u0641 \u0627\u0644\u0633\u064A\u0627\u0631\u0629 \u061F ",-1),ko={class:"py-2"},vo={class:"max-w-9xl mx-auto sm:px-6 lg:px-8"},wo={class:"bg-white overflow-hidden shadow-sm"},$o={class:"p-6 dark:bg-gray-900"},_o={class:"flex flex-col"},Do={class:"grid grid-cols-2 md:grid-cols-5 lg:grid-cols-5 gap-2 lg:gap-1"},Co={class:"flex items-center max-w-5xl"},Mo=e("label",{class:"dark:text-gray-200",for:"simple-search"},null,-1),No={class:"relative w-full"},To=e("div",{class:"absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"},[e("svg",{"aria-hidden":"true",class:"w-5 h-5 text-gray-500 dark:text-gray-200 dark:text-gray-400",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{"fill-rule":"evenodd",d:"M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z","clip-rule":"evenodd"})])],-1),Ao={value:"undefined",disabled:""},Io={value:""},Uo=["value"],Bo={class:"text-center"},Eo={class:"relative overflow-x-auto shadow-md sm:rounded-lg"},zo={class:"w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center"},Fo={class:"text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center"},So={scope:"col",class:"px-1 py-3 text-base"},Po={scope:"col",class:"px-1 py-3 text-base"},jo={scope:"col",class:"px-1 py-3 text-base"},Vo={scope:"col",class:"px-1 py-3 text-base"},Yo={scope:"col",class:"px-1 py-3 text-base"},Go={scope:"col",class:"px-1 py-3 text-base"},Ro={scope:"col",class:"px-1 py-3 text-base"},qo={scope:"col",class:"px-1 py-3 text-base"},Ho={scope:"col",class:"px-1 py-3 text-base"},Oo={scope:"col",class:"px-1 py-3 text-base"},Lo={scope:"col",class:"px-1 py-3 text-base"},Wo={scope:"col",class:"px-1 py-3 text-base"},Jo={scope:"col",class:"px-1 py-3 text-base"},Ko={scope:"col",class:"px-1 py-3 text-base"},Qo={scope:"col",class:"px-1 py-3 text-base"},Xo={scope:"col",class:"px-1 py-3 text-base"},Zo={scope:"col",class:"px-1 py-3 text-base"},et={scope:"col",class:"px-1 py-3 text-base"},ot={scope:"col",class:"px-1 py-3 text-base"},tt={scope:"col",class:"px-1 py-3 text-base",style:{width:"150px"}},at={className:"border dark:border-gray-800 text-center px-2 py-2 "},rt={className:"border dark:border-gray-900 text-center text-black px-2 py-2 ",style:{"font-weight":"bold","font-size":"16px"}},st={className:"border dark:border-gray-800 text-center px-2 py-2 "},dt={className:"border dark:border-gray-800 text-center px-2 py-2 "},nt={className:"border dark:border-gray-800 text-center px-2 py-2 "},lt={className:"border dark:border-gray-800 text-center px-2 py-2 "},it={className:"border dark:border-gray-800 text-center px-2 py-2 "},ct={className:"border dark:border-gray-800 text-center px-2 py-2 "},ut={className:"border dark:border-gray-800 text-center px-2 py-2 "},mt={className:"border dark:border-gray-800 text-center px-2 py-2 "},gt={className:"border dark:border-gray-800 text-center px-2 py-2 "},pt={className:"border dark:border-gray-800 text-center px-2 py-2 "},ft={className:"border dark:border-gray-800 text-center px-2 py-2 "},yt={className:"border dark:border-gray-800 text-center px-2 py-2 "},bt={className:"border dark:border-gray-800 text-center px-2 py-2 "},ht={className:"border dark:border-gray-800 text-center px-2 py-2 "},xt={className:"border dark:border-gray-800 text-center px-2 py-2 "},kt={className:"border dark:border-gray-800 text-center px-2 py-2 "},vt={className:"border dark:border-gray-800 text-center px-2 py-2 "},wt={className:"border dark:border-gray-800 text-start px-2 py-2"},$t=["onClick"],_t=["onClick"],Dt={class:"mt-3 text-center",style:{direction:"ltr"}},Ct={key:0},Mt={class:"mt-4 grid grid-cols-1 gap-4 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-7"},Nt={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},Tt=e("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-orange-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"})])],-1),At={class:"mr-4"},It={class:"font-semibold"},Ut={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},Bt={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},Et=e("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-red-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"})])],-1),zt={class:"mr-4"},Ft={class:"font-semibold"},St={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},Pt=e("div",null,null,-1),Wt={__name:"purchases",props:{client:Array},setup(d){const{t:c}=ie();let S=i({});const $=ce();c("no"),c("car_owner"),c("car_type"),c("year"),c("car_color"),c("vin"),c("car_number"),c("dinar"),c("dolar_price"),c("dolar_custom"),c("shipping_dolar"),c("coc_dolar"),c("checkout"),c("expenses"),c("total"),c("paid"),c("profit"),c("date"),c("note");let v=i(!1),l=i(""),s=i(!1),n=i(!1),w=i(!1),M=i(!1),N=i(!1),T=i(!1),A=i(!1),I=i(!1),_=i(!1),D=i(!1),V=i(0),Y=i(0);function q(t={}){y.value=t,_.value=!0}function H(t={}){y.value=t,D.value=!0}function O(t={}){y.value=t,s.value=!0}const y=i({});i({});const E=i([]);i({startDate:"",endDate:""}),i(),i({date:"D/MM/YYYY",month:"MM"});const U=async(t=1,o="")=>{const a=await fetch(`/getIndexCar?page=${t}&user_id=${o}`);E.value=await a.json()},L=async(t="",o=1)=>{const a=await fetch(`/getIndexCarSearch?page=${o}&q=${t}`);E.value=await a.json()};i({shortcuts:{today:"\u0627\u0644\u064A\u0648\u0645",yesterday:"\u0627\u0644\u0628\u0627\u0631\u062D\u0629",past:t=>t+" \u0642\u0628\u0644 \u064A\u0648\u0645",currentMonth:"\u0627\u0644\u0634\u0647\u0631 \u0627\u0644\u062D\u0627\u0644\u064A",pastMonth:"\u0627\u0644\u0634\u0647\u0631 \u0627\u0644\u0633\u0627\u0628\u0642"},footer:{apply:"Terapkan",cancel:"Batal"}});const P=async()=>{C.get("/api/totalInfo").then(t=>{V.value=t.data.data.mainAccount,Y.value=t.data.data.allCars}).catch(t=>{console.error(t)})};P();function W(t){C.post("/api/addCars",t).then(o=>{s.value=!1,U(),P()}).catch(o=>{console.error(o)})}function G(t){_.value=!1,C.post("/api/updateCarsP",t).then(o=>{v.value=!1,$.success("\u062A\u0645 \u0627\u0644\u062A\u0639\u062F\u064A\u0644 \u0628\u0646\u062C\u0627\u062D",{timeout:2e3,position:"bottom-right",rtl:!0}),P(),U()}).catch(o=>{v.value=!1,$.error("\u0644\u0645 \u0627\u0644\u062A\u0639\u062F\u064A\u0644 \u0628\u0646\u062C\u0627\u062D",{timeout:2e3,position:"bottom-right",rtl:!0})})}function J(t){C.post("/api/payCar",t).then(o=>{n.value=!1,window.location.reload()}).catch(o=>{console.error(o)})}function K(t){var o,a;fetch(`/addExpenses?car_id=${t.id}&user_id=${t.user_id}&expenses_id=${t.expenses_id}&expens_amount=${(o=t.expens_amount)!=null?o:0}&note=${(a=t.noteExpenses)!=null?a:""}`).then(()=>{w.value=!1,window.location.reload()}).catch(u=>{console.error(u)})}function Q(t){var o,a,u;fetch(`/GenExpenses?user_id=${t.user_id}&amount=${(o=t.amount)!=null?o:0}&reason=${(a=t.reason)!=null?a:""}&note=${(u=t.note)!=null?u:""}`).then(()=>{M.value=!1,window.location.reload()}).catch(B=>{console.error(B)})}function X(t){var o,a;fetch(`/addTransfers?user_id=${t.user_id}&amount=${(o=t.amount)!=null?o:0}&note=${(a=t.note)!=null?a:""}`).then(()=>{A.value=!1,window.location.reload()}).catch(u=>{console.error(u)})}function Z(t){var o,a;fetch(`/addToBox?amount=${(o=t.amount)!=null?o:0}&note=${(a=t.note)!=null?a:""}`).then(()=>{N.value=!1,window.location.reload()}).catch(u=>{console.error(u)})}function ee(t){var o,a;fetch(`/withDrawFromBox?amount=${(o=t.amount)!=null?o:0}&note=${(a=t.note)!=null?a:""}`).then(()=>{T.value=!1,window.location.reload()}).catch(u=>{console.error(u)})}function oe(t){C.post("/api/DelCar",t).then(o=>{D.value=!1,window.location.reload()}).catch(o=>{console.error(o)})}U();function te(t){var o,a;C.get(`/api/addPaymentCar?car_id=${t.id}&amount=${(o=t.amountPayment)!=null?o:0}&note=${(a=t.notePayment)!=null?a:""}`).then(u=>{I.value=!1,$.success(" \u062A\u0645 \u062F\u0641\u0639 \u0645\u0628\u0644\u063A \u062F\u0648\u0644\u0627\u0631 "+t.amountPayment+" \u0628\u0646\u062C\u0627\u062D ",{timeout:3e3,position:"bottom-right",rtl:!0}),window.location.reload()}).catch(u=>{v.value=!1,$.error("\u0644\u0645 \u0627\u0644\u062A\u0639\u062F\u064A\u0644 \u0628\u0646\u062C\u0627\u062D",{timeout:2e3,position:"bottom-right",rtl:!0})})}return(t,o)=>(x(),k(z,null,[p(m(ue),{title:"Dashboard"}),p(pe,{data:m(S),show:!!m(v),carModel:t.carModel,onA:o[0]||(o[0]=a=>G(a)),onClose:o[1]||(o[1]=a=>h(v)?v.value=!1:v=!1)},{header:b(()=>[ho]),_:1},8,["data","show","carModel"]),p(fe,{formData:y.value,show:!!m(s),client:d.client,carModel:t.carModel,onA:o[2]||(o[2]=a=>W(a)),onClose:o[3]||(o[3]=a=>h(s)?s.value=!1:s=!1)},{header:b(()=>[]),_:1},8,["formData","show","client","carModel"]),p(bo,{formData:y.value,show:!!m(_),client:d.client,carModel:t.carModel,onA:o[4]||(o[4]=a=>G(a)),onClose:o[5]||(o[5]=a=>h(_)?_.value=!1:_=!1)},{header:b(()=>[]),_:1},8,["formData","show","client","carModel"]),p(ye,{formData:y.value,show:!!m(n),company:t.company,name:t.name,color:t.color,carModel:t.carModel,client:d.client,onA:o[6]||(o[6]=a=>J(a)),onClose:o[7]||(o[7]=a=>h(n)?n.value=!1:n=!1)},{header:b(()=>[]),_:1},8,["formData","show","company","name","color","carModel","client"]),p(be,{formData:y.value,expenses:t.expenses,show:!!m(w),user:t.user,onA:o[8]||(o[8]=a=>K(a)),onClose:o[9]||(o[9]=a=>h(w)?w.value=!1:w=!1)},{header:b(()=>[]),_:1},8,["formData","expenses","show","user"]),p(he,{formData:y.value,show:!!m(M),user:t.user,onA:o[10]||(o[10]=a=>Q(a)),onClose:o[11]||(o[11]=a=>h(M)?M.value=!1:M=!1)},{header:b(()=>[]),_:1},8,["formData","show","user"]),p(xe,{formData:y.value,expenses:t.expenses,show:!!m(N),user:t.user,onA:o[12]||(o[12]=a=>Z(a)),onClose:o[13]||(o[13]=a=>h(N)?N.value=!1:N=!1)},{header:b(()=>[]),_:1},8,["formData","expenses","show","user"]),p(ke,{formData:y.value,expenses:t.expenses,show:!!m(T),user:t.user,onA:o[14]||(o[14]=a=>ee(a)),onClose:o[15]||(o[15]=a=>h(T)?T.value=!1:T=!1)},{header:b(()=>[]),_:1},8,["formData","expenses","show","user"]),p(ve,{formData:y.value,expenses:t.expenses,show:!!m(A),user:t.user,onA:o[16]||(o[16]=a=>X(a)),onClose:o[17]||(o[17]=a=>h(A)?A.value=!1:A=!1)},{header:b(()=>[]),_:1},8,["formData","expenses","show","user"]),p(we,{formData:y.value,show:!!m(I),user:t.user,onA:o[18]||(o[18]=a=>te(a)),onClose:o[19]||(o[19]=a=>h(I)?I.value=!1:I=!1)},{header:b(()=>[]),_:1},8,["formData","show","user"]),p($e,{show:!!m(D),formData:y.value,onA:o[20]||(o[20]=a=>oe(a)),onClose:o[21]||(o[21]=a=>h(D)?D.value=!1:D=!1)},{header:b(()=>[xo]),_:1},8,["show","formData"]),p(ge,null,{default:b(()=>[e("div",ko,[e("div",vo,[e("div",wo,[e("div",$o,[e("div",_o,[e("div",Do,[e("div",null,[e("form",Co,[Mo,e("div",No,[To,g(e("input",{"onUpdate:modelValue":o[22]||(o[22]=a=>h(l)?l.value=a:l=a),onInput:o[23]||(o[23]=a=>L(m(l))),type:"text",id:"simple-search",class:"bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500",placeholder:"\u0628\u062D\u062B",required:""},null,544),[[f,m(l)]])])])]),e("div",null,[g(e("select",{onChange:o[24]||(o[24]=a=>U(1,t.user_id)),"onUpdate:modelValue":o[25]||(o[25]=a=>t.user_id=a),id:"default",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 mb-6 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500"},[e("option",Ao,r(t.$t("selectCustomer")),1),e("option",Io,r(t.$t("allOwners")),1),(x(!0),k(z,null,j(d.client,(a,u)=>(x(),k("option",{key:u,value:a.id},r(a.name),9,Uo))),128))],544),[[R,t.user_id]])]),e("div",Bo,[e("button",{type:"button",onClick:o[26]||(o[26]=a=>O()),style:{"min-width":"150px"},className:"px-6 mb-12 mx-2 py-2 font-bold text-white bg-green-500 rounded"},r(t.$t("addCar")),1)])]),e("div",null,[e("div",Eo,[e("table",zo,[e("thead",Fo,[e("tr",null,[e("th",So,r(t.$t("no")),1),e("th",Po,r(t.$t("car_owner")),1),e("th",jo,r(t.$t("car_type")),1),e("th",Vo,r(t.$t("year")),1),e("th",Yo,r(t.$t("color")),1),e("th",Go,r(t.$t("vin")),1),e("th",Ro,r(t.$t("car_number")),1),e("th",qo,r(t.$t("dinar")),1),e("th",Ho,r(t.$t("dolar_price")),1),e("th",Oo,r(t.$t("dolar_custom")),1),e("th",Lo,r(t.$t("note")),1),e("th",Wo,r(t.$t("shipping_dolar")),1),e("th",Jo,r(t.$t("coc_dolar")),1),e("th",Ko,r(t.$t("checkout")),1),e("th",Qo,r(t.$t("expenses")),1),e("th",Xo,r(t.$t("total")),1),e("th",Zo,r(t.$t("paid")),1),e("th",et,r(t.$t("profit")),1),e("th",ot,r(t.$t("date")),1),e("th",tt,r(t.$t("execute")),1)])]),e("tbody",null,[(x(!0),k(z,null,j(E.value.data,a=>{var u;return x(),k("tr",{key:a.id,class:me([a.results==0?"":a.results==1?"bg-red-100 dark:bg-red-900":"bg-green-100 dark:bg-green-900","bg-white border-b dark:bg-gray-900 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-600"])},[e("td",at,r(a.no),1),e("td",rt,r((u=a.client)==null?void 0:u.name),1),e("td",st,r(a.car_type),1),e("td",dt,r(a.year),1),e("td",nt,r(a.car_color),1),e("td",lt,r(a.vin),1),e("td",it,r(a.car_number),1),e("td",ct,r(a.dinar),1),e("td",ut,r(a.dolar_price),1),e("td",mt,r((a.dinar/a.dolar_price*100).toFixed(0)||0),1),e("td",gt,r(a.note),1),e("td",pt,r(a.shipping_dolar),1),e("td",ft,r(a.coc_dolar),1),e("td",yt,r(a.checkout),1),e("td",bt,r(a.expenses),1),e("td",ht,r(a.total.toFixed(0)),1),e("td",xt,r(a.paid),1),e("td",kt,r((a.total_s-a.total).toFixed(0)),1),e("td",vt,r(a.date),1),e("td",wt,[e("button",{tabIndex:"1",class:"px-2 py-1 text-white mx-1 bg-slate-500 rounded",onClick:B=>q(a)},r(t.$t("edit")),9,$t),e("button",{tabIndex:"1",class:"px-2 py-1 text-white mx-1 bg-orange-500 rounded",onClick:B=>H(a)},r(t.$t("delete")),9,_t)])],2)}),128))])])]),e("div",Dt,[p(m(_e),{data:E.value,onPaginationChangePage:U,limit:10,"item-classes":["bg-white","dark:bg-gray-600","text-gray-500","dark:text-gray-300","border-gray-300","dark:border-gray-900","hover:bg-gray-200"],activeClasses:["bg-rose-50","border-rose-500","text-rose-600"]},null,8,["data"])])]),t.$page.props.auth.user.type_id==1?(x(),k("div",Ct,[e("div",Mt,[e("div",Nt,[Tt,e("div",At,[e("h2",It,r(t.$t("capital")),1),e("p",Ut,r(m(V)),1)])]),e("div",Bt,[Et,e("div",zt,[e("h2",Ft,r(t.$t("all_cars")),1),e("p",St,r(m(Y)),1)])])])])):F("",!0)])])])])]),Pt]),_:1})],64))}};export{Wt as default};
