import{_ as le}from"./AuthenticatedLayout.e3a7f5ec.js";import"./vue-tailwind-datepicker.6fbe19d3.js";import{M as ne}from"./Modal.5f892b83.js";import{_ as ie,a as ue,b as ce,c as me}from"./ModalAddTransfers.0e53bd6a.js";import{m as i,o as c,d as W,e as y,a as m,h as e,r as ge,t as r,w as f,C as w,y as P,F as $,z as A,g as x,u,i as v,j as I,T as fe,s as be,I as ye,f as p,H as he,n as pe,L as ve}from"./app.437283aa.js";/* empty css                                                     */import{_ as we,a as ke}from"./ModalAddTransfers.vue_vue_type_style_index_0_lang.9c440867.js";import{_ as xe}from"./ModalAddCarPayment.0b9e6616.js";import{M as $e}from"./ModalDelCar.95f80641.js";import{a as M}from"./index.7620dff0.js";/* empty css                                              */const _e={key:0,class:"modal-mask"},Ce={class:"modal-wrapper"},De={class:"modal-container dark:bg-gray-900"},Me={class:"modal-header"},Ae={class:"modal-body"},Ne={class:"text-center dark:text-gray-200"},Be={class:"grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-3 lg:gap-3"},je={className:"mb-4 mx-5"},Ue={class:"dark:text-gray-200",for:"company_id"},Te={selected:"",disabled:""},Ee=["value"],Se={className:"mb-4 mx-5"},Pe={class:"dark:text-gray-200",for:"name_id"},Ve={selected:"",disabled:""},ze=["value"],Fe={className:"mb-4 mx-5"},Ie={class:"dark:text-gray-200",for:"carmodel"},Ge={selected:"",disabled:""},Oe=["value"],Ye={class:"grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-3 lg:gap-4"},Le={className:"mb-4 mx-5"},Re={class:"dark:text-gray-200",for:"color_id"},He={selected:"",disabled:""},qe=["value"],We={className:"mb-4 mx-5"},Je={class:"dark:text-gray-200",for:"pin"},Ke={className:"mb-4 mx-5"},Qe={class:"dark:text-gray-200",for:"purchase_data"},Xe={class:"grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-3 lg:gap-3"},Ze={className:"mb-4 mx-5"},et={class:"dark:text-gray-200",for:"purchase_price"},tt={className:"mb-4 mx-5"},ot={class:"dark:text-gray-200",for:"paid_amount"},at={className:"mb-4 mx-5"},st={class:"dark:text-gray-200",for:"paid_amount"},rt=["value"],dt={className:"mb-4 mx-5"},lt={class:"dark:text-gray-200",for:"pay_price"},nt={class:"mb-4 mx-5"},it={class:"dark:text-gray-200",for:"color_id"},ut={class:"relative"},ct={selected:"",disabled:""},mt=["value"],gt={class:"relative"},ft={key:0,className:"mb-4 mx-5"},bt={className:"mb-4 mx-5"},yt={class:"dark:text-gray-200",for:"paid_amount_pay"},ht={className:"mb-4 mx-5"},pt={class:"dark:text-gray-200",for:"note_pay"},vt={class:"modal-footer my-2"},wt={class:"flex flex-row"},kt={class:"basis-1/2 px-4"},xt={class:"basis-1/2 px-4"},$t=["disabled"],_t={__name:"ModalAddSale",props:{show:Boolean,company:Array,color:Array,carModel:Array,name:Array,client:Array,formData:Object},setup(d){let b=i(!1);return(n,l)=>(c(),W(fe,{name:"modal"},{default:y(()=>[d.show?(c(),m("div",_e,[e("div",Ce,[e("div",De,[e("div",Me,[ge(n.$slots,"header")]),e("div",Ae,[e("h2",Ne,r(n.$t("sellCar")),1),e("div",Be,[f(e("input",{id:"id",type:"text",disabled:"",style:{display:"none"},class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":l[0]||(l[0]=s=>d.formData.id=s)},null,512),[[w,d.formData.id]]),e("div",je,[e("label",Ue,r(n.$t("company")),1),f(e("select",{"onUpdate:modelValue":l[1]||(l[1]=s=>d.formData.company_id=s),id:"company_id",disabled:"",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"},[e("option",Te,r(n.$t("select_company")),1),(c(!0),m($,null,A(d.company,(s,h)=>(c(),m("option",{key:h,value:s.id},r(s.name),9,Ee))),128))],512),[[P,d.formData.company_id]])]),e("div",Se,[e("label",Pe,r(n.$t("name")),1),f(e("select",{"onUpdate:modelValue":l[2]||(l[2]=s=>d.formData.name_id=s),id:"name_id",disabled:"",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"},[e("option",Ve,r(n.$t("select_name")),1),(c(!0),m($,null,A(d.name,(s,h)=>(c(),m($,{key:h},[s.company_id==d.formData.company_id?(c(),m("option",{key:0,value:s.id},r(s.name),9,ze)):x("",!0)],64))),128))],512),[[P,d.formData.name_id]])]),e("div",Fe,[e("label",Ie,r(n.$t("year")),1),f(e("select",{"onUpdate:modelValue":l[3]||(l[3]=s=>d.formData.model_id=s),id:"carmodel",disabled:"",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"},[e("option",Ge,r(n.$t("select_year")),1),(c(!0),m($,null,A(d.carModel,(s,h)=>(c(),m("option",{key:h,value:s.id},r(s.name),9,Oe))),128))],512),[[P,d.formData.model_id]])])]),e("div",Ye,[e("div",Le,[e("label",Re,r(n.$t("color")),1),f(e("select",{"onUpdate:modelValue":l[4]||(l[4]=s=>d.formData.color_id=s),id:"color_id",disabled:"",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"},[e("option",He,r(n.$t("select_color")),1),(c(!0),m($,null,A(d.color,(s,h)=>(c(),m("option",{key:h,value:s.id},r(s.name),9,qe))),128))],512),[[P,d.formData.color_id]])]),e("div",We,[e("label",Je,r(n.$t("vim")),1),f(e("input",{id:"pin",type:"text",disabled:"",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":l[5]||(l[5]=s=>d.formData.pin=s)},null,512),[[w,d.formData.pin]])]),e("div",Ke,[e("label",Qe,r(n.$t("purchaseDate")),1),f(e("input",{id:"purchase_data",type:"date",disabled:"",style:{"padding-right":"0"},class:"mt-1 block text-end w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":l[6]||(l[6]=s=>d.formData.purchase_data=s)},null,512),[[w,d.formData.purchase_data]])])]),e("div",Xe,[e("div",Ze,[e("label",et,r(n.$t("purchase_price")),1),f(e("input",{id:"purchase_price",type:"number",disabled:"",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":l[7]||(l[7]=s=>d.formData.purchase_price=s)},null,512),[[w,d.formData.purchase_price]])]),e("div",tt,[e("label",ot,r(n.$t("paidAmountToSupplier")),1),f(e("input",{id:"paid_amount",type:"number",disabled:"",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":l[8]||(l[8]=s=>d.formData.paid_amount=s)},null,512),[[w,d.formData.paid_amount]])]),e("div",at,[e("label",st,r(n.$t("totalExpenses")),1),e("input",{id:"paid_amount",type:"number",value:d.formData.dubai_exp+d.formData.dubai_shipping+d.formData.erbil_exp+d.formData.erbil_shipping,disabled:"",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"},null,8,rt)])]),e("div",dt,[e("label",lt,r(n.$t("sellingPrice")),1),f(e("input",{id:"pay_price",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":l[9]||(l[9]=s=>d.formData.pay_price=s)},null,512),[[w,d.formData.pay_price]])]),e("div",nt,[e("label",it,r(n.$t("customer")),1),e("div",ut,[u(b)?x("",!0):f((c(),m("select",{key:0,"onUpdate:modelValue":l[10]||(l[10]=s=>d.formData.client_id=s),id:"color_id",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"},[e("option",ct,r(n.$t("selectCustomer")),1),(c(!0),m($,null,A(d.client,(s,h)=>(c(),m("option",{key:h,value:s.id},r(s.name),9,mt))),128))],512)),[[P,d.formData.client_id]]),u(b)?x("",!0):(c(),m("button",{key:1,type:"button",onClick:l[11]||(l[11]=s=>{v(b)?b.value=!0:b=!0,d.formData.client_name=""}),class:"absolute left-0 top-0 h-full px-3 py-2 font-bold text-white bg-green-500 rounded-tl-lg rounded-bl-lg"},r(n.$t("addCustomer")),1))]),e("div",gt,[u(b)?f((c(),m("input",{key:0,id:"note",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":l[12]||(l[12]=s=>d.formData.client_name=s)},null,512)),[[w,d.formData.client_name]]):x("",!0),u(b)?(c(),m("button",{key:1,type:"button",onClick:l[13]||(l[13]=s=>{v(b)?b.value=!1:b=!1,d.formData.client=""}),class:"absolute left-0 top-0 h-full px-3 py-2 font-bold text-white bg-pink-500 rounded-tl-lg rounded-bl-lg"},r(n.$t("selectCustomer")),1)):x("",!0)])]),u(b)?(c(),m("div",ft,[I(r(n.$t("phoneNumber"))+" ",1),f(e("input",{id:"note",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":l[14]||(l[14]=s=>d.formData.client_phone=s)},null,512),[[w,d.formData.client_phone]])])):x("",!0),e("div",bt,[e("label",yt,r(n.$t("paid_amount")),1),f(e("input",{id:"paid_amount_pay",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":l[15]||(l[15]=s=>d.formData.paid_amount_pay=s)},null,512),[[w,d.formData.paid_amount_pay]])]),e("div",ht,[e("label",pt,r(n.$t("note")),1),f(e("input",{id:"note_pay",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":l[16]||(l[16]=s=>d.formData.note_pay=s)},null,512),[[w,d.formData.note_pay]])])]),e("div",vt,[e("div",wt,[e("div",kt,[e("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:l[17]||(l[17]=s=>{n.$emit("close")})},r(n.$t("cancel")),1)]),e("div",xt,[e("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:l[18]||(l[18]=s=>{n.$emit("a",d.formData),d.formData=""}),disabled:!(d.formData.paid_amount_pay&&d.formData.pay_price)},r(n.$t("yes")),9,$t)])])])])])])):x("",!0)]),_:3}))}};const Ct=e("h2",{class:"text-center",style:{"font-size":"20px"}}," \u0647\u0644 \u0645\u062A\u0623\u0643\u062F \u0645\u0646 \u062A\u0639\u062F\u064A\u0644 \u0627\u0644\u0628\u064A\u0627\u0646\u0627\u062A ",-1),Dt={key:0,class:"py-2"},Mt={class:"max-w-9xl mx-auto sm:px-6 lg:px-8"},At={class:"bg-white overflow-hidden shadow-sm"},Nt={class:"p-6 dark:bg-gray-900"},Bt={class:"flex flex-col"},jt={class:"grid grid-cols-2 md:grid-cols-6 lg:grid-cols-7 gap-2 lg:gap-1"};const Ut={class:"mt-4 grid grid-cols-1 gap-4 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-7"},Tt={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},Et=e("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-orange-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"})])],-1),St={class:"mr-4"},Pt={class:"font-semibold"},Vt={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},zt={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},Ft=e("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-red-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"})])],-1),It={class:"mr-4"},Gt={class:"font-semibold"},Ot={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},Yt={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},Lt=e("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-red-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"})])],-1),Rt={class:"mr-4"},Ht={class:"font-semibold"},qt={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},Wt={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},Jt=e("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-red-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"})])],-1),Kt={class:"mr-4"},Qt={class:"font-semibold"},Xt={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},Zt={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},eo=e("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-red-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"})])],-1),to={class:"mr-4"},oo={class:"font-semibold"},ao={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},so={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},ro=e("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-red-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"})])],-1),lo={class:"mr-4"},no={class:"font-semibold"},io={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},uo={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},co=e("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-red-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"})])],-1),mo={class:"mr-4"},go={class:"font-semibold"},fo={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},bo=e("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-red-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"})])],-1),yo={class:"mr-4"},ho={class:"font-semibold"},po={class:"mt-2 text-sm text-gray-200 dark:text-gray-200"},vo={class:"inline-flex items-center justify-center w-4 h-4 ml-2 text-xs font-semibold text-blue-800 bg-blue-200 rounded-full"},wo=e("div",null,null,-1),To={__name:"Dashboard",props:{client:Array},setup(d){be();let b=i({});const n=i({});(async(o=1)=>{M.get(`/getIndexClients?page=${o}&q=debit`).then(t=>{var a;try{n.value=(a=t.data.Object.values(b))==null?void 0:a.sort((g,F)=>F.wallet.balance-g.wallet.balance)}catch{n.value=t.data.data}}).catch(t=>{console.error(t)})})();let s=i(0);const h=ye();let _=i(!1);i("");let N=i(!1),B=i(!1),j=i(!1),C=i(!1),U=i(!1),D=i(!1),T=i(!1),V=i(!1),E=i(!1),G=i(0),O=i(0),Y=i(0),L=i(0),R=i(0),H=i(0),q=i(0),J=i(0),K=i(0);function S(o){s.value=o,C.value=!0}const k=i({});i({}),i([]),i({startDate:"",endDate:""}),i(),i({date:"D/MM/YYYY",month:"MM"}),i({shortcuts:{today:"\u0627\u0644\u064A\u0648\u0645",yesterday:"\u0627\u0644\u0628\u0627\u0631\u062D\u0629",past:o=>o+" \u0642\u0628\u0644 \u064A\u0648\u0645",currentMonth:"\u0627\u0644\u0634\u0647\u0631 \u0627\u0644\u062D\u0627\u0644\u064A",pastMonth:"\u0627\u0644\u0634\u0647\u0631 \u0627\u0644\u0633\u0627\u0628\u0642"},footer:{apply:"Terapkan",cancel:"Batal"}});const z=async()=>{M.get("/api/totalInfo").then(o=>{G.value=o.data.data.mainAccount,O.value=o.data.data.onlineContracts,Y.value=o.data.data.howler,L.value=o.data.data.shippingCoc,R.value=o.data.data.border,H.value=o.data.data.iran,q.value=o.data.data.dubai,J.value=o.data.data.debtOnlineContracts,K.value=o.data.data.allCars}).catch(o=>{console.error(o)})};z();function Q(o){M.post("/api/addCars",o).then(t=>{N.value=!1,z()}).catch(t=>{console.error(t)})}function X(o){M.post("/api/updateCars",o).then(t=>{_.value=!1,h.success("\u062A\u0645 \u0627\u0644\u062A\u0639\u062F\u064A\u0644 \u0628\u0646\u062C\u0627\u062D",{timeout:2e3,position:"bottom-right",rtl:!0}),z()}).catch(t=>{_.value=!1,h.error("\u0644\u0645 \u0627\u0644\u062A\u0639\u062F\u064A\u0644 \u0628\u0646\u062C\u0627\u062D",{timeout:2e3,position:"bottom-right",rtl:!0})})}function Z(o){M.post("/api/payCar",o).then(t=>{B.value=!1,window.location.reload()}).catch(t=>{console.error(t)})}function ee(o){var t,a;fetch(`/addExpenses?car_id=${o.id}&user_id=${o.user_id}&expenses_id=${o.expenses_id}&expens_amount=${(t=o.expens_amount)!=null?t:0}&note=${(a=o.noteExpenses)!=null?a:""}`).then(()=>{j.value=!1,window.location.reload()}).catch(g=>{console.error(g)})}function te(o){var t,a,g;fetch(`/GenExpenses?amount=${(t=o.amount)!=null?t:0}&expenses_type_id=${s.value}&factor=${(a=o.factor)!=null?a:1}&note=${(g=o.note)!=null?g:""}`).then(()=>{C.value=!1,window.location.reload()}).catch(F=>{console.error(F)})}function oe(o){var t,a;fetch(`/addTransfers?user_id=${o.user_id}&amount=${(t=o.amount)!=null?t:0}&note=${(a=o.note)!=null?a:""}`).then(()=>{T.value=!1,window.location.reload()}).catch(g=>{console.error(g)})}function ae(o){var t,a;fetch(`/addToBox?amount=${(t=o.amount)!=null?t:0}&note=${(a=o.note)!=null?a:""}`).then(()=>{U.value=!1,window.location.reload()}).catch(g=>{console.error(g)})}function se(o){var t,a;fetch(`/withDrawFromBox?amount=${(t=o.amount)!=null?t:0}&note=${(a=o.note)!=null?a:""}`).then(()=>{D.value=!1,window.location.reload()}).catch(g=>{console.error(g)})}function re(o){var t,a;fetch(`/addPaymentCar?car_id=${o.id}&user_id=${o.user_id}&amount=${(t=o.amountPayment)!=null?t:0}&note=${(a=o.notePayment)!=null?a:""}`).then(()=>{D.value=!1,window.location.reload()}).catch(g=>{console.error(g)})}function de(o){M.post("/api/DelCar",o).then(t=>{E.value=!1,window.location.reload()}).catch(t=>{console.error(t)})}return(o,t)=>(c(),m($,null,[p(u(he),{title:"Dashboard"}),p(ne,{data:u(b),show:!!u(_),carModel:o.carModel,onA:t[0]||(t[0]=a=>X(a)),onClose:t[1]||(t[1]=a=>v(_)?_.value=!1:_=!1)},{header:y(()=>[Ct]),_:1},8,["data","show","carModel"]),p(ie,{formData:k.value,show:!!u(N),client:d.client,carModel:o.carModel,onA:t[2]||(t[2]=a=>Q(a)),onClose:t[3]||(t[3]=a=>v(N)?N.value=!1:N=!1)},{header:y(()=>[]),_:1},8,["formData","show","client","carModel"]),p(_t,{formData:k.value,show:!!u(B),company:o.company,name:o.name,color:o.color,carModel:o.carModel,client:d.client,onA:t[4]||(t[4]=a=>Z(a)),onClose:t[5]||(t[5]=a=>v(B)?B.value=!1:B=!1)},{header:y(()=>[]),_:1},8,["formData","show","company","name","color","carModel","client"]),p(ue,{formData:k.value,expenses:o.expenses,show:!!u(j),user:o.user,onA:t[6]||(t[6]=a=>ee(a)),onClose:t[7]||(t[7]=a=>v(j)?j.value=!1:j=!1)},{header:y(()=>[]),_:1},8,["formData","expenses","show","user"]),p(ce,{formData:k.value,show:!!u(C),expenses_type_id:u(s),user:o.user,onA:t[8]||(t[8]=a=>te(a)),onClose:t[9]||(t[9]=a=>v(C)?C.value=!1:C=!1)},{header:y(()=>[]),_:1},8,["formData","show","expenses_type_id","user"]),p(we,{formData:k.value,expenses:o.expenses,show:!!u(U),user:o.user,onA:t[10]||(t[10]=a=>ae(a)),onClose:t[11]||(t[11]=a=>v(U)?U.value=!1:U=!1)},{header:y(()=>[]),_:1},8,["formData","expenses","show","user"]),p(ke,{formData:k.value,expenses:o.expenses,show:!!u(D),user:o.user,onA:t[12]||(t[12]=a=>se(a)),onClose:t[13]||(t[13]=a=>v(D)?D.value=!1:D=!1)},{header:y(()=>[]),_:1},8,["formData","expenses","show","user"]),p(me,{formData:k.value,expenses:o.expenses,show:!!u(T),user:o.user,onA:t[14]||(t[14]=a=>oe(a)),onClose:t[15]||(t[15]=a=>v(T)?T.value=!1:T=!1)},{header:y(()=>[]),_:1},8,["formData","expenses","show","user"]),p(xe,{formData:k.value,show:!!u(V),user:o.user,onA:t[16]||(t[16]=a=>re(a)),onClose:t[17]||(t[17]=a=>v(V)?V.value=!1:V=!1)},{header:y(()=>[]),_:1},8,["formData","show","user"]),p($e,{show:!!u(E),formData:k.value,onA:t[18]||(t[18]=a=>de(a)),onClose:t[19]||(t[19]=a=>v(E)?E.value=!1:E=!1)},{header:y(()=>[I(" \u0647\u0644 \u0645\u062A\u0623\u0643\u062F \u0645\u0646 \u062D\u0630\u0641 \u0627\u0644\u0633\u064A\u0627\u0631\u0629 \u061F ")]),_:1},8,["show","formData"]),p(le,null,{default:y(()=>[o.$page.props.auth.user.type_id==1?(c(),m("div",Dt,[e("div",Mt,[e("div",At,[e("div",Nt,[e("div",Bt,[e("div",jt,[e("div",null,[e("button",{type:"button",onClick:t[20]||(t[20]=a=>S(1)),style:{"min-width":"150px"},className:"px-6 mb-12 mx-2 py-2 font-bold text-white bg-red-500 rounded"},r(o.$t("genExpenses")),1)]),e("div",null,[e("button",{type:"button",onClick:t[21]||(t[21]=a=>S(2)),style:{"min-width":"150px"},className:"px-6 mb-12 text-center mx-2 py-2 font-bold text-white bg-blue-600 rounded"},r(o.$t("dubai")),1)]),e("div",null,[e("button",{type:"button",onClick:t[22]||(t[22]=a=>S(3)),style:{"min-width":"150px"},className:"px-6 mb-12 text-center mx-2 py-2 font-bold text-white bg-blue-600 rounded"},r(o.$t("iran")),1)]),e("div",null,[e("button",{type:"button",onClick:t[23]||(t[23]=a=>S(4)),style:{"min-width":"150px"},className:"px-6 mb-12 mx-2 py-2 font-bold text-white bg-indigo-600 rounded"},r(o.$t("border")),1)]),e("div",null,[e("button",{type:"button",onClick:t[24]||(t[24]=a=>S(5)),style:{"min-width":"150px"},className:"px-6 mb-12 mx-2 py-2 font-bold text-white bg-pink-600 rounded"},r(o.$t("shipping_coc")),1)])]),e("div",null,[x("",!0)]),e("div",null,[e("div",Ut,[e("div",Tt,[Et,e("div",St,[e("h2",Pt,r(o.$t("capital")),1),e("p",Vt,r(u(G)),1)])]),e("div",zt,[Ft,e("div",It,[e("h2",Gt,r(o.$t("genExpenses")),1),e("p",Ot,r(u(Y)),1)])]),e("div",Yt,[Lt,e("div",Rt,[e("h2",Ht,r(o.$t("dubai")),1),e("p",qt,r(u(q)),1)])]),e("div",Wt,[Jt,e("div",Kt,[e("h2",Qt,r(o.$t("iran")),1),e("p",Xt,r(u(H)),1)])]),e("div",Zt,[eo,e("div",to,[e("h2",oo,r(o.$t("border")),1),e("p",ao,r(u(R)),1)])]),e("div",so,[ro,e("div",lo,[e("h2",no,r(o.$t("shipping_coc")),1),e("p",io,r(u(L)),1)])]),e("div",uo,[co,e("div",mo,[e("h2",go,r(o.$t("online_contracts")),1),e("p",fo,r(u(O)),1)])]),(c(!0),m($,null,A(n.value,(a,g)=>(c(),W(u(ve),{key:g,class:pe(["flex items-start rounded-xl text-gray-200 dark:text-gray-300 p-4 shadow-lg",a.car_total_uncomplete?"bg-red-500  dark:bg-red-500":"bg-green-600  dark:bg-green-600"]),href:o.route("showClients",a.id)},{default:y(()=>[bo,e("div",yo,[e("h2",ho,r(a.name),1),e("p",po,[I(r(a.wallet?"$"+a.wallet.balance:0)+" ",1),e("span",vo,r(a.car_total_un_pay),1)])])]),_:2},1032,["href","class"]))),128))])])])])])])])):x("",!0),wo]),_:1})],64))}};export{To as default};