import{r as v,o as c,c as j,w as D,a as u,b as e,d as q,k as g,e as f,g as F,F as V,f as z,t as d,h as p,l as w,v as h,j as k,T as O,z as H,y as Q,H as X,n as Z}from"./app.67cca86e.js";import{_ as ee}from"./AuthenticatedLayout.9a0c67e2.js";import{t as te}from"./trash.66b1e500.js";import{e as ae}from"./edit.2b1d90f1.js";import{a as C}from"./index.c2533ba5.js";import{U as E}from"./Uploader.149e5271.js";import{M as oe}from"./ModalDelCar.c21dbc92.js";import{d as re,W as se}from"./debounce.60ec479b.js";const de={key:0,class:"modal-mask"},le={class:"modal-wrapper max-h-[80vh]"},ne={class:"modal-container dark:bg-gray-900 overflow-auto max-h-[80vh]"},ie={class:"modal-header"},ce={class:"text-center dark:text-gray-200"},ue={class:"modal-body"},ge={key:0,class:"grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-1 lg:gap-2"},me={class:"mb-4 mx-1"},be=e("label",{class:"dark:text-gray-200",for:"color_id"}," \u0635\u0627\u062D\u0628 \u0627\u0644\u0633\u064A\u0627\u0631\u0629 ",-1),fe={class:"relative"},ye=e("option",{selected:"",disabled:""}," \u062A\u062D\u062F\u064A\u062F \u0635\u0627\u062D\u0628 \u0627\u0644\u0633\u064A\u0627\u0631\u0629 ",-1),he=["value"],pe={class:"relative"},ve={key:0,className:"mb-4 mx-1"},ke={class:"dark:text-gray-200",for:"number"},xe={class:"grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-1 lg:gap-2"},$e={className:"mb-4 mx-1"},_e={class:"dark:text-gray-200",for:"car_number"},we={className:"mb-4 mx-1"},De={class:"dark:text-gray-200",for:"pin"},Ce={className:"mb-4 mx-1"},Ae={class:"dark:text-gray-200",for:"pin"},Me={className:"mb-4 mx-1"},Ue={class:"dark:text-gray-200",for:"pin"},Ne={className:"mb-4 mx-1"},Ve={class:"dark:text-gray-200",for:"pin"},Ie={class:"grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-1 lg:gap-2"},Be={className:"mb-4 mx-1"},ze={class:"dark:text-gray-200",for:"note"},Re={key:1,class:"grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-1 lg:gap-2"},Se=["disabled"],Te={key:2,class:"grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-1 lg:gap-2"},je={class:"mb-4"},qe=e("label",{class:"form-label"},"\u0627\u0644\u0635\u0648\u0631",-1),Fe={class:"mt-3"},Oe={key:0,class:"text-danger"},He={class:"modal-footer my-2"},Ee={class:"flex flex-row m-auto"},Ge={__name:"ModalAddCarsAnnual",props:{show:Boolean,formData:Object,client:Array,saveCar:Boolean},setup(t){function $(){const a=new Date,o=a.getFullYear(),y=String(a.getMonth()+1).padStart(2,"0"),x=String(a.getDate()).padStart(2,"0");return`${o}-${y}-${x}`}let m=v(!0);function i(a){C.get("/api/carsAnnualImageDel?name="+a.name).then(o=>{toast.success("\u062A\u0645  \u062D\u0630\u0641 \u0627\u0644\u0635\u0648\u0631\u0629 \u0628\u0646\u062C\u0627\u062D",{timeout:5e3,position:"bottom-right",rtl:!0})}).catch(o=>{console.error(o)})}return(a,o)=>(c(),j(O,{name:"modal"},{default:D(()=>{var y,x,b;return[t.show?(c(),u("div",de,[e("div",le,[e("div",ne,[e("div",ie,[q(a.$slots,"header",{},()=>[e("h2",ce,d(a.$t("add_car")),1)])]),e("div",ue,[t.formData.id?p("",!0):(c(),u("div",ge,[e("div",me,[be,e("div",fe,[g(m)?p("",!0):f((c(),u("select",{key:0,"onUpdate:modelValue":o[0]||(o[0]=n=>t.formData.client_id=n),id:"color_id",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"},[ye,(c(!0),u(V,null,z(t.client,(n,_)=>(c(),u("option",{key:_,value:n.id},d(n.name),9,he))),128))],512)),[[F,t.formData.client_id]]),g(m)?p("",!0):(c(),u("button",{key:1,type:"button",onClick:o[1]||(o[1]=n=>{w(m)?m.value=!0:m=!0,t.formData.client_name="",t.formData.client_id=""}),class:"absolute left-0 top-0 h-full px-3 py-2 font-bold text-white bg-green-500 rounded-tl-lg rounded-bl-lg"}," \u0625\u0636\u0627\u0641\u0629 \u0635\u0627\u062D\u0628 \u0633\u064A\u0627\u0631\u0629 "))]),e("div",pe,[g(m)?f((c(),u("input",{key:0,id:"note",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":o[2]||(o[2]=n=>t.formData.client_name=n)},null,512)),[[h,t.formData.client_name]]):p("",!0),g(m)?(c(),u("button",{key:1,type:"button",onClick:o[3]||(o[3]=n=>{w(m)?m.value=!1:m=!1,t.formData.client=""}),class:"absolute left-0 top-0 h-full px-3 py-2 font-bold text-white bg-pink-500 rounded-tl-lg rounded-bl-lg"}," \u062A\u062D\u062F\u064A\u062F \u0635\u0627\u062D\u0628 \u0627\u0644\u0633\u064A\u0627\u0631\u0629 ")):p("",!0)])]),g(m)?(c(),u("div",ve,[e("label",ke,d(a.$t("phoneNumber")),1),f(e("input",{id:"number",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":o[4]||(o[4]=n=>t.formData.client_phone=n)},null,512),[[h,t.formData.client_phone]])])):p("",!0)])),e("div",xe,[e("div",$e,[e("label",_e,d(a.$t("car_number")),1),f(e("input",{id:"car_number",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":o[5]||(o[5]=n=>t.formData.car_number=n)},null,512),[[h,t.formData.car_number]])]),e("div",we,[e("label",De,d(a.$t("car_type")),1),f(e("input",{id:"car_type",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":o[6]||(o[6]=n=>t.formData.car_type=n)},null,512),[[h,t.formData.car_type]])]),e("div",Ce,[e("label",Ae,d(a.$t("year")),1),f(e("input",{id:"year",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":o[7]||(o[7]=n=>t.formData.year=n)},null,512),[[h,t.formData.year]])]),e("div",Me,[e("label",Ue,d(a.$t("color")),1),f(e("input",{id:"car_color",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":o[8]||(o[8]=n=>t.formData.car_color=n)},null,512),[[h,t.formData.car_color]])]),e("div",Ne,[e("label",Ve,d(a.$t("date")),1),f(e("input",{id:"date",type:"date",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":o[9]||(o[9]=n=>t.formData.date=n)},null,512),[[h,t.formData.date]])])]),e("div",Ie,[e("div",Be,[e("label",ze,d(a.$t("note")),1),f(e("input",{id:"note",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":o[10]||(o[10]=n=>t.formData.note=n)},null,512),[[h,t.formData.note]])])]),t.saveCar?p("",!0):(c(),u("div",Re,[e("button",{class:"modal-default-button py-3 bg-blue-500 rounded col-6",onClick:o[11]||(o[11]=n=>{t.formData.date=t.formData.date?t.formData.date:$(),a.$emit("a",t.formData)}),disabled:!t.formData.client_id&&!t.formData.client_name}," \u062D\u0641\u0638 \u0648\u0645\u062A\u0627\u0628\u0639\u0629 ",8,Se)])),t.saveCar?(c(),u("div",Te,[e("div",je,[qe,e("div",Fe,[k(g(E),{server:"/api/carsAnnualUpload?carId="+t.saveCar,"is-invalid":!!((y=a.errors)!=null&&y.media),onChange:a.changeMedia,location:"/storage/posts/media",onInit:a.initMedia,onAdd:a.addMedia,onRemove:i},null,8,["server","is-invalid","onChange","onInit","onAdd"])]),(x=a.errors)!=null&&x.media?(c(),u("p",Oe,d((b=a.errors)==null?void 0:b.media[0]),1)):p("",!0)])])):p("",!0)]),e("div",He,[e("div",Ee,[e("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:o[12]||(o[12]=n=>{a.$emit("close"),t.formData=""})}," \u0625\u063A\u0644\u0627\u0642 ")])])])])])):p("",!0)]}),_:3}))}};const Le={key:0,class:"modal-mask"},We={class:"modal-wrapper max-h-[80vh]"},Ye={class:"modal-container dark:bg-gray-900 overflow-auto max-h-[80vh]"},Je={class:"modal-header"},Ke=e("h2",{class:"text-center dark:text-gray-200"}," \u062A\u0639\u062F\u064A\u0644 \u0645\u0639\u0644\u0648\u0645\u0627\u062A ",-1),Pe={class:"modal-body"},Qe={class:"grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-1 lg:gap-2"},Xe={className:"mb-4 mx-1"},Ze={class:"dark:text-gray-200",for:"car_number"},et={className:"mb-4 mx-1"},tt={class:"dark:text-gray-200",for:"pin"},at={className:"mb-4 mx-1"},ot={class:"dark:text-gray-200",for:"pin"},rt={className:"mb-4 mx-1"},st={class:"dark:text-gray-200",for:"pin"},dt={class:"grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-1 lg:gap-2"},lt={className:"mb-4 mx-1"},nt={class:"dark:text-gray-200",for:"note"},it={class:"grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-1 lg:gap-2"},ct={class:"m-4"},ut=e("label",{class:"form-label"},"\u0627\u0644\u0635\u0648\u0631",-1),gt={key:0,class:"text-danger"},mt={class:"modal-footer my-2"},bt={class:"flex flex-row"},ft={class:"basis-1/2 px-4"},yt={class:"basis-1/2 px-4"},ht=["disabled"],pt={__name:"ModalUpdateCarsAnnual",props:{show:Boolean,formData:Object,client:Array,saveCar:Boolean},setup(t){const $=H();function m(i){C.get("/api/carsAnnualImageDel?name="+i.name).then(a=>{$.success("\u062A\u0645  \u062D\u0630\u0641 \u0627\u0644\u0635\u0648\u0631\u0629 \u0628\u0646\u062C\u0627\u062D",{timeout:5e3,position:"bottom-right",rtl:!0})}).catch(a=>{console.error(a)})}return(i,a)=>(c(),j(O,{name:"modal"},{default:D(()=>{var o,y,x;return[t.show?(c(),u("div",Le,[e("div",We,[e("div",Ye,[e("div",Je,[q(i.$slots,"header",{},()=>[Ke])]),e("div",Pe,[e("div",Qe,[e("div",Xe,[e("label",Ze,d(i.$t("car_number")),1),f(e("input",{id:"car_number",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":a[0]||(a[0]=b=>t.formData.car_number=b)},null,512),[[h,t.formData.car_number]])]),e("div",et,[e("label",tt,d(i.$t("car_type")),1),f(e("input",{id:"car_type",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":a[1]||(a[1]=b=>t.formData.car_type=b)},null,512),[[h,t.formData.car_type]])]),e("div",at,[e("label",ot,d(i.$t("year")),1),f(e("input",{id:"year",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":a[2]||(a[2]=b=>t.formData.year=b)},null,512),[[h,t.formData.year]])]),e("div",rt,[e("label",st,d(i.$t("color")),1),f(e("input",{id:"car_color",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":a[3]||(a[3]=b=>t.formData.car_color=b)},null,512),[[h,t.formData.car_color]])])]),e("div",dt,[e("div",lt,[e("label",nt,d(i.$t("note")),1),f(e("input",{id:"note",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":a[4]||(a[4]=b=>t.formData.note=b)},null,512),[[h,t.formData.note]])])]),e("div",it,[e("div",ct,[ut,e("div",null,[k(g(E),{server:"/api/carsAnnualUpload?carId="+t.formData.id,"is-invalid":!!((o=i.errors)!=null&&o.media),onChange:i.changeMedia,onInitMedia:i.media,location:"/public/uploadsResized",media:t.formData.car_images,onAdd:i.addMedia,onRemove:m},null,8,["server","is-invalid","onChange","onInitMedia","media","onAdd"])]),(y=i.errors)!=null&&y.media?(c(),u("p",gt,d((x=i.errors)==null?void 0:x.media[0]),1)):p("",!0)])])]),e("div",mt,[e("div",bt,[e("div",ft,[e("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:a[5]||(a[5]=b=>i.$emit("close"))}," \u0627\u063A\u0644\u0627\u0642 ")]),e("div",yt,[e("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:a[6]||(a[6]=b=>{t.formData.date=t.formData.date?t.formData.date:i.getTodayDate(),i.$emit("a",t.formData),t.formData=""}),disabled:!t.formData.client_id&&!t.formData.client_name}," \u062D\u0641\u0638 \u0627\u0644\u0645\u0639\u0644\u0648\u0645\u0627\u062A ",8,ht)])])])])])])):p("",!0)]}),_:3}))}},vt=e("h2",{class:"mb-5 dark:text-white text-center"}," \u0647\u0644 \u0645\u062A\u0623\u0643\u062F \u0645\u0646 \u062D\u0630\u0641 \u0627\u0644\u0633\u064A\u0627\u0631\u0629 \u061F ",-1),kt={key:0,class:"py-2"},xt={class:"max-w-9xl mx-auto sm:px-6 lg:px-8"},$t={class:"bg-white overflow-hidden shadow-sm"},_t={class:"p-6 dark:bg-gray-900"},wt={class:"flex flex-col"},Dt={class:"grid grid-cols-2 md:grid-cols-5 lg:grid-cols-5 gap-2 lg:gap-1"},Ct={class:"flex items-center max-w-5xl"},At=e("label",{class:"dark:text-gray-200",for:"simple-search"},null,-1),Mt={class:"relative w-full"},Ut=e("div",{class:"absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"},[e("svg",{"aria-hidden":"true",class:"w-5 h-5 text-gray-500 dark:text-gray-200 dark:text-gray-400",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{"fill-rule":"evenodd",d:"M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z","clip-rule":"evenodd"})])],-1),Nt={value:"0",disabled:""},Vt={value:""},It=["value"],Bt={class:"text-center"},zt=e("div",null,null,-1),Rt={class:"relative overflow-x-auto shadow-md sm:rounded-lg"},St={class:"w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center"},Tt={class:"text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center"},jt=e("th",{scope:"col",class:"px-1 py-3 text-base"}," \u0627\u0633\u0645 \u0635\u0627\u062D\u0628 \u0627\u0644\u0633\u06CC\u0627\u0631\u06D5 ",-1),qt={scope:"col",class:"px-1 py-3 text-base"},Ft={scope:"col",class:"px-1 py-3 text-base"},Ot={scope:"col",class:"px-1 py-3 text-base"},Ht={scope:"col",class:"px-1 py-3 text-base"},Et={scope:"col",class:"px-1 py-3 text-base"},Gt={scope:"col",class:"px-1 py-3 text-base"},Lt=e("th",{scope:"col",class:"px-1 py-3 text-base"}," \u0627\u0644\u0645\u0644\u0641\u0627\u062A \u0627\u0644\u0645\u062E\u0632\u0646\u0629 ",-1),Wt={scope:"col",class:"px-1 py-3 text-base",style:{width:"90px"}},Yt={className:"border dark:border-gray-800 text-center  dark:text-gray-200 text-black px-1 py-2 ",style:{"font-weight":"bold","font-size":"16px"}},Jt={className:"border dark:border-gray-800 text-center px-1 py-2 "},Kt={className:"border dark:border-gray-800 text-center px-1 py-2 "},Pt={className:"border dark:border-gray-800 text-center px-1 py-2 "},Qt={className:"border dark:border-gray-800 text-center px-1 py-2 "},Xt={className:"border dark:border-gray-800 text-center px-1 py-2 "},Zt={className:"border dark:border-gray-800 text-center px-1 py-2 "},ea={className:"border dark:border-gray-800 text-center px-1 py-2 "},ta=["href"],aa=["src"],oa={className:"border dark:border-gray-800 text-start px-1 py-2"},ra=["onClick"],sa=["onClick"],da={class:"spaner"},la=e("div",null,null,-1),ya={__name:"Index",props:{clientAnnual:Array},setup(t){Q();const $=H();let m=v(!1),i=v(!1),a=v(!1),o=v(!1);v(0),v(0);let y=v(!1);function x(l={}){_.value=l,a.value=!0}function b(l={}){_.value=l,o.value=!0}function n(l={}){_.value={},i.value=!0}const _=v({}),A=v([]);let R=v(!1),M=0,S=1,U="";const N=()=>{S=0,A.value.length=0,R.value=!R.value},G=async l=>{console.log(l);try{const r=(await C.get("api/getIndexCarAnnual",{params:{limit:100,page:S,q:U,user_id:M}})).data;r.data.length<100?(A.value.push(...r.data),l.complete()):(A.value.push(...r.data),l.loaded()),S++}catch(s){console.log(s)}};function L(l){a.value=!1,C.post("/api/updateCarsAnnual",l).then(s=>{m.value=!1,$.success("\u062A\u0645 \u0627\u0644\u062A\u0639\u062F\u064A\u0644 \u0628\u0646\u062C\u0627\u062D",{timeout:2e3,position:"bottom-right",rtl:!0}),N()}).catch(s=>{m.value=!1,$.error("\u0644\u0645 \u0627\u0644\u062A\u0639\u062F\u064A\u0644 \u0628\u0646\u062C\u0627\u062D",{timeout:2e3,position:"bottom-right",rtl:!0})})}function W(l){C.post("/api/delCarsAnnualr",l).then(s=>{o.value=!1,$.success("\u062D\u0630\u0641 \u0627\u0644\u0633\u064A\u0627\u0631\u0629 \u0628\u0646\u062C\u0627\u062D",{timeout:3e3,position:"bottom-right",rtl:!0}),N()}).catch(s=>{console.error(s)})}const T=re(N,500);function Y(l){C.post("/api/addCarsAnnual",l).then(s=>{y.value=s.data.id,$.success("\u062A\u0645 \u0627\u0636\u0627\u0641\u0629 \u0627\u0644\u0645\u0639\u0644\u0648\u0645\u0627\u062A \u0628\u0646\u062C\u0627\u062D",{timeout:5e3,position:"bottom-right",rtl:!0})}).catch(s=>{console.error(s)})}function J(l){return`/public/uploadsResized/${l}`}function K(l){return`/public/uploads/${l}`}return(l,s)=>(c(),u(V,null,[k(g(X),{title:"Dashboard"}),k(Ge,{formData:_.value,saveCar:g(y),show:!!g(i),client:t.clientAnnual,onA:s[0]||(s[0]=r=>Y(r)),onClose:s[1]||(s[1]=r=>{w(i)?i.value=!1:i=!1,w(y)?y.value=0:y=0,N()})},{header:D(()=>[]),_:1},8,["formData","saveCar","show","client"]),k(pt,{formData:_.value,saveCar:g(y),show:!!g(a),client:t.clientAnnual,onA:s[2]||(s[2]=r=>L(r)),onClose:s[3]||(s[3]=r=>w(a)?a.value=!1:a=!1)},{header:D(()=>[]),_:1},8,["formData","saveCar","show","client"]),k(oe,{show:!!g(o),formData:_.value,onA:s[4]||(s[4]=r=>W(r)),onClose:s[5]||(s[5]=r=>w(o)?o.value=!1:o=!1)},{header:D(()=>[vt]),_:1},8,["show","formData"]),k(ee,null,{default:D(()=>[l.$page.props.auth.user.type_id==1?(c(),u("div",kt,[e("div",xt,[e("div",$t,[e("div",_t,[e("div",wt,[e("div",Dt,[e("div",null,[e("form",Ct,[At,e("div",Mt,[Ut,f(e("input",{"onUpdate:modelValue":s[6]||(s[6]=r=>w(U)?U.value=r:U=r),onInput:s[7]||(s[7]=(...r)=>g(T)&&g(T)(...r)),type:"text",id:"simple-search",class:"bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500",placeholder:"\u0628\u062D\u062B",required:""},null,544),[[h,g(U)]])])])]),e("div",null,[f(e("select",{onChange:s[8]||(s[8]=r=>N()),"onUpdate:modelValue":s[9]||(s[9]=r=>w(M)?M.value=r:M=r),id:"default",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 mb-6 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500"},[e("option",Nt,d(l.$t("selectCustomer")),1),e("option",Vt,d(l.$t("allOwners")),1),(c(!0),u(V,null,z(t.clientAnnual,(r,I)=>(c(),u("option",{key:I,value:r.id},d(r.name),9,It))),128))],544),[[F,g(M)]])]),e("div",Bt,[e("button",{type:"button",onClick:s[10]||(s[10]=r=>n()),style:{"min-width":"150px"},className:"px-6 mb-12 mx-2 py-2 font-bold text-white bg-purple-600 rounded"}," \u0627\u0636\u0627\u0641\u0629 \u0633\u064A\u0627\u0631\u0629 ")])]),e("div",null,[zt,e("div",Rt,[e("table",St,[e("thead",Tt,[e("tr",null,[jt,e("th",qt,d(l.$t("car_type")),1),e("th",Ft,d(l.$t("year")),1),e("th",Ot,d(l.$t("color")),1),e("th",Ht,d(l.$t("car_number")),1),e("th",Et,d(l.$t("note")),1),e("th",Gt,d(l.$t("date")),1),Lt,e("th",Wt,d(l.$t("execute")),1)])]),e("tbody",null,[(c(!0),u(V,null,z(A.value,r=>{var I;return c(),u("tr",{key:r.id,class:Z([r.results==0?"":r.results==1?"bg-red-100 dark:bg-red-900":"bg-green-100 dark:bg-green-900","bg-white border-b dark:bg-gray-900 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-600"])},[e("td",Yt,d((I=r.client)==null?void 0:I.name),1),e("td",Jt,d(r.car_type),1),e("td",Kt,d(r.year),1),e("td",Pt,d(r.car_color),1),e("td",Qt,d(r.car_number),1),e("td",Xt,d(r.note),1),e("td",Zt,d(r.date),1),e("td",ea,[(c(!0),u(V,null,z(r.car_images,(B,P)=>(c(),u("a",{key:P,href:K(B.name),style:{cursor:"pointer"},target:"_blank"},[e("img",{src:J(B.name),alt:"",class:"px-1",style:{"max-width":"100px","max-height":"50px",display:"inline"}},null,8,aa)],8,ta))),128))]),e("td",oa,[e("button",{tabIndex:"1",class:"px-1 py-1 text-white mx-1 bg-slate-500 rounded",onClick:B=>x(r)},[k(ae)],8,ra),e("button",{tabIndex:"1",class:"px-1 py-1 text-white mx-1 bg-orange-500 rounded",onClick:B=>b(r)},[k(te)],8,sa)])],2)}),128))])])])]),e("div",da,[k(g(se),{car:A.value,onInfinite:G,identifier:g(R)},null,8,["car","identifier"])])])])])])])):p("",!0),la]),_:1})],64))}};export{ya as default};
