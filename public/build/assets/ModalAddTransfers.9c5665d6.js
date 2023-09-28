import{m as D,o as n,d as v,e as x,a as c,h as a,r as p,t as o,u as y,w as i,y as h,F as k,z as $,g as b,i as V,C as g,T as w,j as N}from"./app.e33e7107.js";import{a as U}from"./index.204b6520.js";const C={key:0,class:"modal-mask"},M={class:"modal-wrapper"},B={class:"modal-container dark:bg-gray-900"},z={class:"modal-header"},H={class:"text-center dark:text-gray-200"},j={class:"modal-body"},S={key:0,class:"grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-1 lg:gap-2"},E={class:"mb-4 mx-1"},T={class:"dark:text-gray-200",for:"color_id"},F={class:"relative"},O={selected:"",disabled:""},G=["value"],R={class:"relative"},L={key:0,className:"mb-4 mx-1"},P={class:"dark:text-gray-200",for:"number"},Y={class:"grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-1 lg:gap-2"},q={className:"mb-4 mx-1"},I={class:"dark:text-gray-200",for:"pin"},J={key:0,class:"text-red-700"},K={className:"mb-4 mx-1"},Q={class:"dark:text-gray-200",for:"pin"},W={className:"mb-4 mx-1"},X={class:"dark:text-gray-200",for:"pin"},Z={className:"mb-4 mx-1"},aa={class:"dark:text-gray-200",for:"pin"},ea={className:"mb-4 mx-1"},oa={class:"dark:text-gray-200",for:"car_number"},ta={className:"mb-4 mx-1"},ra={class:"dark:text-gray-200",for:"dinar"},da={className:"mb-4 mx-1"},sa={class:"dark:text-gray-200",for:"dolar_price"},la={className:"mb-4 mx-1"},na={class:"dark:text-gray-200",for:"shipping_dolar"},ia={className:"mb-4 mx-1"},ua={class:"dark:text-gray-200",for:"coc_dolar"},ma={className:"mb-4 mx-1"},ca={class:"dark:text-gray-200",for:"checkout"},ga={className:"mb-4 mx-1"},ba={class:"dark:text-gray-200",for:"date"},fa={className:"mb-4 mx-1"},ya={class:"dark:text-gray-200",for:"note"},ka={class:"modal-footer my-2"},ha={class:"flex flex-row"},$a={class:"basis-1/2 px-4"},va={class:"basis-1/2 px-4"},xa=["disabled"],Ht={__name:"ModalAddCars",props:{show:Boolean,formData:Object,client:Array},setup(e){function d(){const u=new Date,l=u.getFullYear(),m=String(u.getMonth()+1).padStart(2,"0"),_=String(u.getDate()).padStart(2,"0");return`${l}-${m}-${_}`}function t(u){u?U.get(`/api/check_vin?car_vin=${u}`).then(l=>{s.value=l.data,s.value}).catch(l=>{console.error(l)}):s.value=!1}let r=D(!1),s=D(!1);return(u,l)=>(n(),v(w,{name:"modal"},{default:x(()=>[e.show?(n(),c("div",C,[a("div",M,[a("div",B,[a("div",z,[p(u.$slots,"header",{},()=>[a("h2",H,o(u.$t("add_car")),1)])]),a("div",j,[e.formData.id?b("",!0):(n(),c("div",S,[a("div",E,[a("label",T,o(u.$t("car_owner")),1),a("div",F,[y(r)?b("",!0):i((n(),c("select",{key:0,"onUpdate:modelValue":l[0]||(l[0]=m=>e.formData.client_id=m),id:"color_id",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"},[a("option",O,o(u.$t("selectCustomer")),1),(n(!0),c(k,null,$(e.client,(m,_)=>(n(),c("option",{key:_,value:m.id},o(m.name),9,G))),128))],512)),[[h,e.formData.client_id]]),y(r)?b("",!0):(n(),c("button",{key:1,type:"button",onClick:l[1]||(l[1]=m=>{V(r)?r.value=!0:r=!0,e.formData.client_name="",e.formData.client_id=""}),class:"absolute left-0 top-0 h-full px-3 py-2 font-bold text-white bg-green-500 rounded-tl-lg rounded-bl-lg"},o(u.$t("addCustomer")),1))]),a("div",R,[y(r)?i((n(),c("input",{key:0,id:"note",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":l[2]||(l[2]=m=>e.formData.client_name=m)},null,512)),[[g,e.formData.client_name]]):b("",!0),y(r)?(n(),c("button",{key:1,type:"button",onClick:l[3]||(l[3]=m=>{V(r)?r.value=!1:r=!1,e.formData.client=""}),class:"absolute left-0 top-0 h-full px-3 py-2 font-bold text-white bg-pink-500 rounded-tl-lg rounded-bl-lg"},o(u.$t("selectCustomer")),1)):b("",!0)])]),y(r)?(n(),c("div",L,[a("label",P,o(u.$t("phoneNumber")),1),i(a("input",{id:"number",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":l[4]||(l[4]=m=>e.formData.client_phone=m)},null,512),[[g,e.formData.client_phone]])])):b("",!0)])),a("div",Y,[a("div",q,[a("label",I,o(u.$t("vin")),1),i(a("input",{id:"vin",type:"text",onChange:l[5]||(l[5]=m=>t(e.formData.vin)),class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":l[6]||(l[6]=m=>e.formData.vin=m)},null,544),[[g,e.formData.vin]]),y(s)?(n(),c("div",J," \u0631\u0642\u0645 \u0627\u0644\u0634\u0627\u0635\u064A \u0645\u0633\u062A\u062E\u062F\u0645 ")):b("",!0)]),a("div",K,[a("label",Q,o(u.$t("car_type")),1),i(a("input",{id:"car_type",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":l[7]||(l[7]=m=>e.formData.car_type=m)},null,512),[[g,e.formData.car_type]])]),a("div",W,[a("label",X,o(u.$t("year")),1),i(a("input",{id:"year",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":l[8]||(l[8]=m=>e.formData.year=m)},null,512),[[g,e.formData.year]])]),a("div",Z,[a("label",aa,o(u.$t("color")),1),i(a("input",{id:"car_color",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":l[9]||(l[9]=m=>e.formData.car_color=m)},null,512),[[g,e.formData.car_color]])]),a("div",ea,[a("label",oa,o(u.$t("car_number")),1),i(a("input",{id:"car_number",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":l[10]||(l[10]=m=>e.formData.car_number=m)},null,512),[[g,e.formData.car_number]])]),a("div",ta,[a("label",ra,o(u.$t("dinar")),1),i(a("input",{id:"dinar",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":l[11]||(l[11]=m=>e.formData.dinar=m)},null,512),[[g,e.formData.dinar]])]),a("div",da,[a("label",sa,o(u.$t("dolar_price")),1),i(a("input",{id:"dolar_price",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":l[12]||(l[12]=m=>e.formData.dolar_price=m)},null,512),[[g,e.formData.dolar_price]])]),a("div",la,[a("label",na,o(u.$t("shipping_dolar")),1),i(a("input",{id:"shipping_dolar",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":l[13]||(l[13]=m=>e.formData.shipping_dolar=m)},null,512),[[g,e.formData.shipping_dolar]])]),a("div",ia,[a("label",ua,o(u.$t("coc_dolar")),1),i(a("input",{id:"coc_dolar",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":l[14]||(l[14]=m=>e.formData.coc_dolar=m)},null,512),[[g,e.formData.coc_dolar]])]),a("div",ma,[a("label",ca,o(u.$t("checkout")),1),i(a("input",{id:"checkout",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":l[15]||(l[15]=m=>e.formData.checkout=m)},null,512),[[g,e.formData.checkout]])]),a("div",ga,[a("label",ba,o(u.$t("date")),1),i(a("input",{id:"date",type:"date",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":l[16]||(l[16]=m=>e.formData.date=m)},null,512),[[g,e.formData.date]])])]),a("div",fa,[a("label",ya,o(u.$t("note")),1),i(a("input",{id:"note",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":l[17]||(l[17]=m=>e.formData.note=m)},null,512),[[g,e.formData.note]])])]),a("div",ka,[a("div",ha,[a("div",$a,[a("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:l[18]||(l[18]=m=>u.$emit("close"))},o(u.$t("cancel")),1)]),a("div",va,[a("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:l[19]||(l[19]=m=>{e.formData.date=e.formData.date?e.formData.date:d(),u.$emit("a",e.formData),e.formData=""}),disabled:!e.formData.client_id&&!e.formData.client_name},o(u.$t("yes")),9,xa)])])])])])])):b("",!0)]),_:3}))}};const pa={key:0,class:"modal-mask"},wa={class:"modal-wrapper"},_a={class:"modal-container dark:bg-gray-900"},Da={class:"modal-header"},Va={class:"modal-body"},Aa={class:"text-center dark:text-gray-200"},Na={class:"grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-3 lg:gap-3"},Ua={className:"mb-4 mx-5"},Ca={class:"dark:text-gray-200",for:"company_id"},Ma={selected:"",disabled:""},Ba=["value"],za={className:"mb-4 mx-5"},Ha={class:"dark:text-gray-200",for:"name_id"},ja={selected:"",disabled:""},Sa=["value"],Ea={className:"mb-4 mx-5"},Ta={class:"dark:text-gray-200",for:"carmodel"},Fa={selected:"",disabled:""},Oa=["value"],Ga={class:"grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-3 lg:gap-4"},Ra={className:"mb-4 mx-5"},La={class:"dark:text-gray-200",for:"color_id"},Pa={selected:"",disabled:""},Ya=["value"],qa={className:"mb-4 mx-5"},Ia={class:"dark:text-gray-200",for:"pin"},Ja={className:"mb-4 mx-5"},Ka={class:"dark:text-gray-200",for:"purchase_data"},Qa={class:"grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-3 lg:gap-3"},Wa={className:"mb-4 mx-5"},Xa={class:"dark:text-gray-200",for:"purchase_price"},Za={className:"mb-4 mx-5"},ae={class:"dark:text-gray-200",for:"paid_amount"},ee={className:"mb-4 mx-5"},oe={class:"dark:text-gray-200",for:"paid_amount"},te=["value"],re={className:"mb-4 mx-5"},de={class:"dark:text-gray-200",for:"pay_price"},se={class:"mb-4 mx-5"},le={class:"dark:text-gray-200",for:"color_id"},ne={class:"relative"},ie={selected:"",disabled:""},ue=["value"],me={class:"relative"},ce={key:0,className:"mb-4 mx-5"},ge={className:"mb-4 mx-5"},be={class:"dark:text-gray-200",for:"paid_amount_pay"},fe={className:"mb-4 mx-5"},ye={class:"dark:text-gray-200",for:"note_pay"},ke={class:"modal-footer my-2"},he={class:"flex flex-row"},$e={class:"basis-1/2 px-4"},ve={class:"basis-1/2 px-4"},xe=["disabled"],jt={__name:"ModalAddSale",props:{show:Boolean,company:Array,color:Array,carModel:Array,name:Array,client:Array,formData:Object},setup(e){let d=D(!1);return(t,r)=>(n(),v(w,{name:"modal"},{default:x(()=>[e.show?(n(),c("div",pa,[a("div",wa,[a("div",_a,[a("div",Da,[p(t.$slots,"header")]),a("div",Va,[a("h2",Aa,o(t.$t("sellCar")),1),a("div",Na,[i(a("input",{id:"id",type:"text",disabled:"",style:{display:"none"},class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":r[0]||(r[0]=s=>e.formData.id=s)},null,512),[[g,e.formData.id]]),a("div",Ua,[a("label",Ca,o(t.$t("company")),1),i(a("select",{"onUpdate:modelValue":r[1]||(r[1]=s=>e.formData.company_id=s),id:"company_id",disabled:"",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"},[a("option",Ma,o(t.$t("select_company")),1),(n(!0),c(k,null,$(e.company,(s,u)=>(n(),c("option",{key:u,value:s.id},o(s.name),9,Ba))),128))],512),[[h,e.formData.company_id]])]),a("div",za,[a("label",Ha,o(t.$t("name")),1),i(a("select",{"onUpdate:modelValue":r[2]||(r[2]=s=>e.formData.name_id=s),id:"name_id",disabled:"",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"},[a("option",ja,o(t.$t("select_name")),1),(n(!0),c(k,null,$(e.name,(s,u)=>(n(),c(k,{key:u},[s.company_id==e.formData.company_id?(n(),c("option",{key:0,value:s.id},o(s.name),9,Sa)):b("",!0)],64))),128))],512),[[h,e.formData.name_id]])]),a("div",Ea,[a("label",Ta,o(t.$t("year")),1),i(a("select",{"onUpdate:modelValue":r[3]||(r[3]=s=>e.formData.model_id=s),id:"carmodel",disabled:"",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"},[a("option",Fa,o(t.$t("select_year")),1),(n(!0),c(k,null,$(e.carModel,(s,u)=>(n(),c("option",{key:u,value:s.id},o(s.name),9,Oa))),128))],512),[[h,e.formData.model_id]])])]),a("div",Ga,[a("div",Ra,[a("label",La,o(t.$t("color")),1),i(a("select",{"onUpdate:modelValue":r[4]||(r[4]=s=>e.formData.color_id=s),id:"color_id",disabled:"",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"},[a("option",Pa,o(t.$t("select_color")),1),(n(!0),c(k,null,$(e.color,(s,u)=>(n(),c("option",{key:u,value:s.id},o(s.name),9,Ya))),128))],512),[[h,e.formData.color_id]])]),a("div",qa,[a("label",Ia,o(t.$t("vim")),1),i(a("input",{id:"pin",type:"text",disabled:"",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":r[5]||(r[5]=s=>e.formData.pin=s)},null,512),[[g,e.formData.pin]])]),a("div",Ja,[a("label",Ka,o(t.$t("purchaseDate")),1),i(a("input",{id:"purchase_data",type:"date",disabled:"",style:{"padding-right":"0"},class:"mt-1 block text-end w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":r[6]||(r[6]=s=>e.formData.purchase_data=s)},null,512),[[g,e.formData.purchase_data]])])]),a("div",Qa,[a("div",Wa,[a("label",Xa,o(t.$t("purchase_price")),1),i(a("input",{id:"purchase_price",type:"number",disabled:"",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":r[7]||(r[7]=s=>e.formData.purchase_price=s)},null,512),[[g,e.formData.purchase_price]])]),a("div",Za,[a("label",ae,o(t.$t("paidAmountToSupplier")),1),i(a("input",{id:"paid_amount",type:"number",disabled:"",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":r[8]||(r[8]=s=>e.formData.paid_amount=s)},null,512),[[g,e.formData.paid_amount]])]),a("div",ee,[a("label",oe,o(t.$t("totalExpenses")),1),a("input",{id:"paid_amount",type:"number",value:e.formData.dubai_exp+e.formData.dubai_shipping+e.formData.erbil_exp+e.formData.erbil_shipping,disabled:"",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"},null,8,te)])]),a("div",re,[a("label",de,o(t.$t("sellingPrice")),1),i(a("input",{id:"pay_price",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":r[9]||(r[9]=s=>e.formData.pay_price=s)},null,512),[[g,e.formData.pay_price]])]),a("div",se,[a("label",le,o(t.$t("customer")),1),a("div",ne,[y(d)?b("",!0):i((n(),c("select",{key:0,"onUpdate:modelValue":r[10]||(r[10]=s=>e.formData.client_id=s),id:"color_id",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"},[a("option",ie,o(t.$t("selectCustomer")),1),(n(!0),c(k,null,$(e.client,(s,u)=>(n(),c("option",{key:u,value:s.id},o(s.name),9,ue))),128))],512)),[[h,e.formData.client_id]]),y(d)?b("",!0):(n(),c("button",{key:1,type:"button",onClick:r[11]||(r[11]=s=>{V(d)?d.value=!0:d=!0,e.formData.client_name=""}),class:"absolute left-0 top-0 h-full px-3 py-2 font-bold text-white bg-green-500 rounded-tl-lg rounded-bl-lg"},o(t.$t("addCustomer")),1))]),a("div",me,[y(d)?i((n(),c("input",{key:0,id:"note",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":r[12]||(r[12]=s=>e.formData.client_name=s)},null,512)),[[g,e.formData.client_name]]):b("",!0),y(d)?(n(),c("button",{key:1,type:"button",onClick:r[13]||(r[13]=s=>{V(d)?d.value=!1:d=!1,e.formData.client=""}),class:"absolute left-0 top-0 h-full px-3 py-2 font-bold text-white bg-pink-500 rounded-tl-lg rounded-bl-lg"},o(t.$t("selectCustomer")),1)):b("",!0)])]),y(d)?(n(),c("div",ce,[N(o(t.$t("phoneNumber"))+" ",1),i(a("input",{id:"note",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":r[14]||(r[14]=s=>e.formData.client_phone=s)},null,512),[[g,e.formData.client_phone]])])):b("",!0),a("div",ge,[a("label",be,o(t.$t("paid_amount")),1),i(a("input",{id:"paid_amount_pay",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":r[15]||(r[15]=s=>e.formData.paid_amount_pay=s)},null,512),[[g,e.formData.paid_amount_pay]])]),a("div",fe,[a("label",ye,o(t.$t("note")),1),i(a("input",{id:"note_pay",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":r[16]||(r[16]=s=>e.formData.note_pay=s)},null,512),[[g,e.formData.note_pay]])])]),a("div",ke,[a("div",he,[a("div",$e,[a("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:r[17]||(r[17]=s=>{t.$emit("close")})},o(t.$t("cancel")),1)]),a("div",ve,[a("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:r[18]||(r[18]=s=>{t.$emit("a",e.formData),e.formData=""}),disabled:!(e.formData.paid_amount_pay&&e.formData.pay_price)},o(t.$t("yes")),9,xe)])])])])])])):b("",!0)]),_:3}))}};const pe={key:0,class:"modal-mask"},we={class:"modal-wrapper"},_e={class:"modal-container dark:bg-gray-900"},De={class:"modal-header"},Ve={class:"grid grid-cols-5 md:grid-cols-5 lg:grid-cols-5 gap-5 lg:gap-5"},Ae={class:"relative mb-6 sm:mb-0"},Ne=a("div",{class:"flex items-center"},[a("div",{class:"z-10 flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full ring-0 ring-white dark:bg-blue-900 sm:ring-8 dark:ring-gray-900 shrink-0"},[a("svg",{"aria-hidden":"true",class:"w-3 h-3 text-blue-800 dark:text-blue-300",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[a("path",{"fill-rule":"evenodd",d:"M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z","clip-rule":"evenodd"})])]),a("div",{class:"hidden sm:flex w-full bg-gray-200 h-0.5 dark:bg-gray-700"})],-1),Ue={class:"mt-3 sm:pr-8"},Ce={class:"text-lg font-semibold text-gray-900 dark:text-black"},Me={class:"block mb-2 text-sm font-normal leading-none text-gray-400 dark:text-gray-500"},Be={class:"relative mb-6 sm:mb-0"},ze=a("div",{class:"flex items-center"},[a("div",{class:"z-10 flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full ring-0 ring-white dark:bg-blue-900 sm:ring-8 dark:ring-gray-900 shrink-0"},[a("svg",{"aria-hidden":"true",class:"w-3 h-3 text-blue-800 dark:text-blue-300",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[a("path",{"fill-rule":"evenodd",d:"M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z","clip-rule":"evenodd"})])]),a("div",{class:"hidden sm:flex w-full bg-gray-200 h-0.5 dark:bg-gray-700"})],-1),He={class:"mt-3 sm:pr-8"},je={class:"text-lg font-semibold text-gray-900 dark:text-black"},Se={class:"block mb-2 text-sm font-normal leading-none text-gray-400 dark:text-gray-500"},Ee={class:"relative mb-6 sm:mb-0"},Te=a("div",{class:"flex items-center"},[a("div",{class:"z-10 flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full ring-0 ring-white dark:bg-blue-900 sm:ring-8 dark:ring-gray-900 shrink-0"},[a("svg",{"aria-hidden":"true",class:"w-3 h-3 text-blue-800 dark:text-blue-300",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[a("path",{"fill-rule":"evenodd",d:"M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z","clip-rule":"evenodd"})])]),a("div",{class:"hidden sm:flex w-full bg-gray-200 h-0.5 dark:bg-gray-700"})],-1),Fe={class:"mt-3 sm:pr-8"},Oe={class:"text-lg font-semibold text-gray-900 dark:text-black"},Ge={class:"block mb-2 text-sm font-normal leading-none text-gray-400 dark:text-gray-500"},Re={class:"relative mb-6 sm:mb-0"},Le=a("div",{class:"flex items-center"},[a("div",{class:"z-10 flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full ring-0 ring-white dark:bg-blue-900 sm:ring-8 dark:ring-gray-900 shrink-0"},[a("svg",{"aria-hidden":"true",class:"w-3 h-3 text-blue-800 dark:text-blue-300",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[a("path",{"fill-rule":"evenodd",d:"M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z","clip-rule":"evenodd"})])]),a("div",{class:"hidden sm:flex w-full bg-gray-200 h-0.5 dark:bg-gray-700"})],-1),Pe={class:"mt-3 sm:pr-8"},Ye={class:"text-lg font-semibold text-gray-900 dark:text-black"},qe={class:"block mb-2 text-sm font-normal leading-none text-gray-400 dark:text-gray-500"},Ie={class:"relative mb-6 sm:mb-0"},Je=a("div",{class:"flex items-center"},[a("div",{class:"z-10 flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full ring-0 ring-white dark:bg-blue-900 sm:ring-8 dark:ring-gray-900 shrink-0"},[a("svg",{"aria-hidden":"true",class:"w-3 h-3 text-blue-800 dark:text-blue-300",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[a("path",{"fill-rule":"evenodd",d:"M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z","clip-rule":"evenodd"})])]),a("div",{class:"hidden sm:flex w-full bg-gray-200 h-0.5 dark:bg-gray-700"})],-1),Ke={class:"mt-3 sm:pr-8"},Qe={class:"text-lg font-semibold text-gray-900 dark:text-black"},We={class:"block mb-2 text-sm font-normal leading-none text-gray-400 dark:text-gray-500"},Xe={class:"modal-body"},Ze={className:"mb-4 mx-5"},ao={className:"mb-4 mx-5"},eo=a("label",{class:"dark:text-gray-200",for:"user_id"},"\u0627\u0644\u0645\u0633\u062A\u062E\u062F\u0645 \u0627\u0644\u0630\u064A \u062A\u0645 \u0628\u0627\u0644\u0635\u0631\u0641",-1),oo={selected:"",disabled:""},to=["value"],ro={className:"mb-4 mx-5"},so=a("label",{class:"dark:text-gray-200",for:"expenses_id"},"\u0627\u0644\u0633\u0628\u0628",-1),lo=a("option",{selected:"",disabled:""},"\u062A\u062D\u062F\u064A\u062F \u0627\u0644\u0633\u0628\u0628",-1),no=["value"],io={className:"mb-4 mx-5"},uo=a("label",{class:"dark:text-gray-200",for:"expens_amount"},"\u0627\u0644\u0645\u0628\u0644\u063A",-1),mo={className:"mb-4 mx-5"},co={class:"dark:text-gray-200",for:"noteExpenses"},go={class:"modal-footer my-2"},bo={class:"flex flex-row"},fo={class:"basis-1/2 px-4"},yo={class:"basis-1/2 px-4"},ko=["disabled"],St={__name:"ModalAddExpenses",props:{show:Boolean,company:Array,color:Array,carModel:Array,name:Array,client:Array,user:Array,expenses:Array,formData:Object},setup(e){return D(!1),(d,t)=>(n(),v(w,{name:"modal"},{default:x(()=>{var r,s,u,l,m,_;return[e.show?(n(),c("div",pe,[a("div",we,[a("div",_e,[a("div",De,[p(d.$slots,"header")]),a("div",null,[a("ol",Ve,[a("li",Ae,[Ne,a("div",Ue,[a("h3",Ce,o(d.$t("dubai_expenses")),1),a("time",Me,o((r=e.formData)==null?void 0:r.dubai_exp),1)])]),a("li",Be,[ze,a("div",He,[a("h3",je,o(d.$t("dubai_shipping")),1),a("time",Se,o((s=e.formData)==null?void 0:s.dubai_shipping),1)])]),a("li",Ee,[Te,a("div",Fe,[a("h3",Oe,o(d.$t("erbil_expenses")),1),a("time",Ge,o((u=e.formData)==null?void 0:u.erbil_exp),1)])]),a("li",Re,[Le,a("div",Pe,[a("h3",Ye,o(d.$t("erbil_shipping")),1),a("time",qe,o((l=e.formData)==null?void 0:l.erbil_shipping),1)])]),a("li",Ie,[Je,a("div",Ke,[a("h3",Qe,o(d.$t("supplier_debt")),1),a("time",We,o(((m=e.formData)==null?void 0:m.purchase_price)-((_=e.formData)==null?void 0:_.paid_amount)),1)])])])]),a("div",Xe,[a("div",Ze,[i(a("input",{id:"id",type:"text",disabled:"",style:{display:"none"},class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":t[0]||(t[0]=f=>e.formData.id=f)},null,512),[[g,e.formData.id]])]),a("div",ao,[eo,i(a("select",{"onUpdate:modelValue":t[1]||(t[1]=f=>e.formData.user_id=f),id:"name_id",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-black dark:focus:ring-blue-500 dark:focus:border-blue-500"},[a("option",oo,o(d.$t("select_name")),1),(n(!0),c(k,null,$(e.user,(f,A)=>(n(),c("option",{key:A,value:f.id},o(f.name),9,to))),128))],512),[[h,e.formData.user_id]])]),a("div",ro,[so,i(a("select",{"onUpdate:modelValue":t[2]||(t[2]=f=>e.formData.expenses_id=f),id:"expenses_id",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-black dark:focus:ring-blue-500 dark:focus:border-blue-500"},[lo,(n(!0),c(k,null,$(e.expenses,(f,A)=>(n(),c("option",{key:A,value:f.id},o(f.name_ar),9,no))),128))],512),[[h,e.formData.expenses_id]])]),a("div",io,[uo,i(a("input",{id:"expens_amount",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":t[3]||(t[3]=f=>e.formData.expens_amount=f)},null,512),[[g,e.formData.expens_amount]])]),a("div",mo,[a("label",co,o(d.$t("note")),1),i(a("input",{id:"noteExpenses",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":t[4]||(t[4]=f=>e.formData.noteExpenses=f)},null,512),[[g,e.formData.noteExpenses]])])]),a("div",go,[a("div",bo,[a("div",fo,[a("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:t[5]||(t[5]=f=>{d.$emit("close")})},o(d.$t("cancel")),1)]),a("div",yo,[a("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:t[6]||(t[6]=f=>{d.$emit("a",e.formData),e.formData=""}),disabled:!(e.formData.user_id&&e.formData.expenses_id&&e.formData.expens_amount)},o(d.$t("yes")),9,ko)])])])])])])):b("",!0)]}),_:3}))}};const ho={key:0,class:"modal-mask"},$o={class:"modal-wrapper"},vo={class:"modal-container dark:bg-gray-900"},xo={class:"modal-header"},po={class:"modal-body"},wo={className:"mb-4 mx-5"},_o={class:"dark:text-gray-200",for:"expens_amount"},Do={className:"mb-4 mx-5"},Vo={class:"dark:text-gray-200",for:"expenses_id"},Ao={className:"mb-4 mx-5"},No={class:"dark:text-gray-200",for:"expens_amount"},Uo=["value"],Co={className:"mb-4 mx-5"},Mo={class:"dark:text-gray-200",for:"note"},Bo={class:"modal-footer my-2"},zo={class:"flex flex-row"},Ho={class:"basis-1/2 px-4"},jo={class:"basis-1/2 px-4"},So=["disabled"],Et={__name:"ModalAddGenExpenses",props:{show:Boolean,company:Array,color:Array,carModel:Array,name:Array,client:Array,user:Array,expenses:Array,formData:Object},setup(e){return(d,t)=>(n(),v(w,{name:"modal"},{default:x(()=>[e.show?(n(),c("div",ho,[a("div",$o,[a("div",vo,[a("div",xo,[p(d.$slots,"header")]),a("div",po,[a("div",wo,[a("label",_o,o(d.$t("amount")),1),i(a("input",{id:"expens_amount",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":t[0]||(t[0]=r=>e.formData.amount=r)},null,512),[[g,e.formData.amount]])]),a("div",Do,[a("label",Vo,o(d.$t("factor")),1),i(a("input",{id:"note_expens",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":t[1]||(t[1]=r=>e.formData.factor=r)},null,512),[[g,e.formData.factor]])]),a("div",Ao,[a("label",No,o(d.$t("result")),1),a("input",{id:"expens_amount",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900",value:(e.formData.amount/e.formData.factor).toFixed(1)},null,8,Uo)]),a("div",Co,[a("label",Mo,o(d.$t("note")),1),i(a("input",{id:"note",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":t[2]||(t[2]=r=>e.formData.note=r)},null,512),[[g,e.formData.note]])])]),a("div",Bo,[a("div",zo,[a("div",Ho,[a("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:t[3]||(t[3]=r=>{d.$emit("close")})},o(d.$t("cancel")),1)]),a("div",jo,[a("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:t[4]||(t[4]=r=>{d.$emit("a",e.formData),e.formData=""}),disabled:!e.formData.amount},o(d.$t("yes")),9,So)])])])])])])):b("",!0)]),_:3}))}};const Eo={key:0,class:"modal-mask"},To={class:"modal-wrapper"},Fo={class:"modal-container dark:bg-gray-900"},Oo={class:"modal-header"},Go={class:"modal-body"},Ro={class:"text-center dark:text-gray-200"},Lo={className:"mb-4 mx-5"},Po={class:"dark:text-gray-200",for:"expens_amount"},Yo={className:"mb-4 mx-5"},qo={class:"dark:text-gray-200",for:"note"},Io={class:"modal-footer my-2"},Jo={class:"flex flex-row"},Ko={class:"basis-1/2 px-4"},Qo={class:"basis-1/2 px-4"},Wo=["disabled"],Tt={__name:"ModalAddToBox",props:{show:Boolean,company:Array,color:Array,carModel:Array,name:Array,client:Array,user:Array,expenses:Array,formData:Object},setup(e){return(d,t)=>(n(),v(w,{name:"modal"},{default:x(()=>[e.show?(n(),c("div",Eo,[a("div",To,[a("div",Fo,[a("div",Oo,[p(d.$slots,"header")]),a("div",Go,[a("h2",Ro,o(d.$t("addAmountToCashbox")),1),a("div",Lo,[a("label",Po,o(d.$t("amount")),1),i(a("input",{id:"expens_amount",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":t[0]||(t[0]=r=>e.formData.amount=r)},null,512),[[g,e.formData.amount]])]),a("div",Yo,[a("label",qo,o(d.$t("note")),1),i(a("input",{id:"note",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":t[1]||(t[1]=r=>e.formData.note=r)},null,512),[[g,e.formData.note]])])]),a("div",Io,[a("div",Jo,[a("div",Ko,[a("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:t[2]||(t[2]=r=>{d.$emit("close")})},o(d.$t("cancel")),1)]),a("div",Qo,[a("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:t[3]||(t[3]=r=>{d.$emit("a",e.formData),e.formData=""}),disabled:!e.formData.amount},o(d.$t("yes")),9,Wo)])])])])])])):b("",!0)]),_:3}))}};const Xo={key:0,class:"modal-mask"},Zo={class:"modal-wrapper"},at={class:"modal-container dark:bg-gray-900"},et={class:"modal-header"},ot={class:"modal-body"},tt={class:"text-center dark:text-gray-200"},rt={className:"mb-4 mx-5"},dt={class:"dark:text-gray-200",for:"expens_amount"},st={className:"mb-4 mx-5"},lt={class:"dark:text-gray-200",for:"note"},nt={class:"modal-footer my-2"},it={class:"flex flex-row"},ut={class:"basis-1/2 px-4"},mt={class:"basis-1/2 px-4"},ct=["disabled"],Ft={__name:"ModalSpanFromBox",props:{show:Boolean,company:Array,color:Array,carModel:Array,name:Array,client:Array,user:Array,expenses:Array,formData:Object},setup(e){return(d,t)=>(n(),v(w,{name:"modal"},{default:x(()=>[e.show?(n(),c("div",Xo,[a("div",Zo,[a("div",at,[a("div",et,[p(d.$slots,"header")]),a("div",ot,[a("h2",tt,o(d.$t("withdrawFromCashBox")),1),a("div",rt,[a("label",dt,o(d.$t("amount")),1),i(a("input",{id:"expens_amount",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":t[0]||(t[0]=r=>e.formData.amount=r)},null,512),[[g,e.formData.amount]])]),a("div",st,[a("label",lt,o(d.$t("note")),1),i(a("input",{id:"note",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":t[1]||(t[1]=r=>e.formData.note=r)},null,512),[[g,e.formData.note]])])]),a("div",nt,[a("div",it,[a("div",ut,[a("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:t[2]||(t[2]=r=>{d.$emit("close")})},o(d.$t("cancel")),1)]),a("div",mt,[a("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:t[3]||(t[3]=r=>{d.$emit("a",e.formData),e.formData=""}),disabled:!e.formData.amount},o(d.$t("yes")),9,ct)])])])])])])):b("",!0)]),_:3}))}};const gt={key:0,class:"modal-mask"},bt={class:"modal-wrapper"},ft={class:"modal-container dark:bg-gray-900"},yt={class:"modal-header"},kt={class:"modal-body"},ht={class:"text-center dark:text-gray-200"},$t={className:"mb-4 mx-5"},vt={class:"dark:text-gray-200",for:"user_id"},xt={selected:"",disabled:""},pt=["value"],wt={className:"mb-4 mx-5"},_t={class:"dark:text-gray-200",for:"expens_amount"},Dt={className:"mb-4 mx-5"},Vt={class:"dark:text-gray-200",for:"note"},At={class:"modal-footer my-2"},Nt={class:"flex flex-row"},Ut={class:"basis-1/2 px-4"},Ct={class:"basis-1/2 px-4"},Mt=["disabled"],Ot={__name:"ModalAddTransfers",props:{show:Boolean,company:Array,color:Array,carModel:Array,name:Array,client:Array,user:Array,expenses:Array,formData:Object},setup(e){return(d,t)=>(n(),v(w,{name:"modal"},{default:x(()=>[e.show?(n(),c("div",gt,[a("div",bt,[a("div",ft,[a("div",yt,[p(d.$slots,"header")]),a("div",kt,[a("h2",ht,o(d.$t("addRemittance")),1),a("div",$t,[a("label",vt,o(d.$t("user")),1),i(a("select",{"onUpdate:modelValue":t[0]||(t[0]=r=>e.formData.user_id=r),id:"name_id",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"},[a("option",xt,o(d.$t("select_name")),1),(n(!0),c(k,null,$(e.user,(r,s)=>(n(),c("option",{key:s,value:r.id},o(r.name),9,pt))),128))],512),[[h,e.formData.user_id]])]),a("div",wt,[a("label",_t,o(d.$t("amount")),1),i(a("input",{id:"expens_amount",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":t[1]||(t[1]=r=>e.formData.amount=r)},null,512),[[g,e.formData.amount]])]),a("div",Dt,[a("label",Vt,o(d.$t("note")),1),i(a("input",{id:"note",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":t[2]||(t[2]=r=>e.formData.note=r)},null,512),[[g,e.formData.note]])])]),a("div",At,[a("div",Nt,[a("div",Ut,[a("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:t[3]||(t[3]=r=>{d.$emit("close")})},o(d.$t("cancel")),1)]),a("div",Ct,[a("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:t[4]||(t[4]=r=>{d.$emit("a",e.formData),e.formData=""}),disabled:!(e.formData.amount&&e.formData.user_id)},o(d.$t("yes")),9,Mt)])])])])])])):b("",!0)]),_:3}))}};export{Ht as _,jt as a,St as b,Et as c,Tt as d,Ft as e,Ot as f};