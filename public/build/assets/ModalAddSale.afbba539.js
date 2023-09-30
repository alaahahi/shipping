import{m as p,o as l,d as h,e as v,a as s,h as a,r as x,t as r,w as i,C as u,y as b,F as c,z as y,g,u as f,i as k,j as D,T as w}from"./app.16e8688e.js";const _={key:0,class:"modal-mask"},$={class:"modal-wrapper"},V={class:"modal-container dark:bg-gray-900"},N={class:"modal-header"},U={class:"modal-body"},C={class:"text-center dark:text-gray-200"},A={class:"grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-3 lg:gap-3"},M={className:"mb-4 mx-5"},S={class:"dark:text-gray-200",for:"company_id"},B={selected:"",disabled:""},T=["value"],j={className:"mb-4 mx-5"},E={class:"dark:text-gray-200",for:"name_id"},F={selected:"",disabled:""},z=["value"],L={className:"mb-4 mx-5"},O={class:"dark:text-gray-200",for:"carmodel"},P={selected:"",disabled:""},R=["value"],q={class:"grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-3 lg:gap-4"},G={className:"mb-4 mx-5"},H={class:"dark:text-gray-200",for:"color_id"},I={selected:"",disabled:""},J=["value"],K={className:"mb-4 mx-5"},Q={class:"dark:text-gray-200",for:"pin"},W={className:"mb-4 mx-5"},X={class:"dark:text-gray-200",for:"purchase_data"},Y={class:"grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-3 lg:gap-3"},Z={className:"mb-4 mx-5"},aa={class:"dark:text-gray-200",for:"purchase_price"},ea={className:"mb-4 mx-5"},oa={class:"dark:text-gray-200",for:"paid_amount"},da={className:"mb-4 mx-5"},ra={class:"dark:text-gray-200",for:"paid_amount"},ta=["value"],la={className:"mb-4 mx-5"},sa={class:"dark:text-gray-200",for:"pay_price"},ia={class:"mb-4 mx-5"},na={class:"dark:text-gray-200",for:"color_id"},ua={class:"relative"},ma={selected:"",disabled:""},ga=["value"],ca={class:"relative"},ba={key:0,className:"mb-4 mx-5"},ya={className:"mb-4 mx-5"},fa={class:"dark:text-gray-200",for:"paid_amount_pay"},ka={className:"mb-4 mx-5"},pa={class:"dark:text-gray-200",for:"note_pay"},ha={class:"modal-footer my-2"},va={class:"flex flex-row"},xa={class:"basis-1/2 px-4"},Da={class:"basis-1/2 px-4"},wa=["disabled"],$a={__name:"ModalAddSale",props:{show:Boolean,company:Array,color:Array,carModel:Array,name:Array,client:Array,formData:Object},setup(o){let n=p(!1);return(t,d)=>(l(),h(w,{name:"modal"},{default:v(()=>[o.show?(l(),s("div",_,[a("div",$,[a("div",V,[a("div",N,[x(t.$slots,"header")]),a("div",U,[a("h2",C,r(t.$t("sellCar")),1),a("div",A,[i(a("input",{id:"id",type:"text",disabled:"",style:{display:"none"},class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":d[0]||(d[0]=e=>o.formData.id=e)},null,512),[[u,o.formData.id]]),a("div",M,[a("label",S,r(t.$t("company")),1),i(a("select",{"onUpdate:modelValue":d[1]||(d[1]=e=>o.formData.company_id=e),id:"company_id",disabled:"",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"},[a("option",B,r(t.$t("select_company")),1),(l(!0),s(c,null,y(o.company,(e,m)=>(l(),s("option",{key:m,value:e.id},r(e.name),9,T))),128))],512),[[b,o.formData.company_id]])]),a("div",j,[a("label",E,r(t.$t("name")),1),i(a("select",{"onUpdate:modelValue":d[2]||(d[2]=e=>o.formData.name_id=e),id:"name_id",disabled:"",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"},[a("option",F,r(t.$t("select_name")),1),(l(!0),s(c,null,y(o.name,(e,m)=>(l(),s(c,{key:m},[e.company_id==o.formData.company_id?(l(),s("option",{key:0,value:e.id},r(e.name),9,z)):g("",!0)],64))),128))],512),[[b,o.formData.name_id]])]),a("div",L,[a("label",O,r(t.$t("year")),1),i(a("select",{"onUpdate:modelValue":d[3]||(d[3]=e=>o.formData.model_id=e),id:"carmodel",disabled:"",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"},[a("option",P,r(t.$t("select_year")),1),(l(!0),s(c,null,y(o.carModel,(e,m)=>(l(),s("option",{key:m,value:e.id},r(e.name),9,R))),128))],512),[[b,o.formData.model_id]])])]),a("div",q,[a("div",G,[a("label",H,r(t.$t("color")),1),i(a("select",{"onUpdate:modelValue":d[4]||(d[4]=e=>o.formData.color_id=e),id:"color_id",disabled:"",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"},[a("option",I,r(t.$t("select_color")),1),(l(!0),s(c,null,y(o.color,(e,m)=>(l(),s("option",{key:m,value:e.id},r(e.name),9,J))),128))],512),[[b,o.formData.color_id]])]),a("div",K,[a("label",Q,r(t.$t("vim")),1),i(a("input",{id:"pin",type:"text",disabled:"",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":d[5]||(d[5]=e=>o.formData.pin=e)},null,512),[[u,o.formData.pin]])]),a("div",W,[a("label",X,r(t.$t("purchaseDate")),1),i(a("input",{id:"purchase_data",type:"date",disabled:"",style:{"padding-right":"0"},class:"mt-1 block text-end w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":d[6]||(d[6]=e=>o.formData.purchase_data=e)},null,512),[[u,o.formData.purchase_data]])])]),a("div",Y,[a("div",Z,[a("label",aa,r(t.$t("purchase_price")),1),i(a("input",{id:"purchase_price",type:"number",disabled:"",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":d[7]||(d[7]=e=>o.formData.purchase_price=e)},null,512),[[u,o.formData.purchase_price]])]),a("div",ea,[a("label",oa,r(t.$t("paidAmountToSupplier")),1),i(a("input",{id:"paid_amount",type:"number",disabled:"",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":d[8]||(d[8]=e=>o.formData.paid_amount=e)},null,512),[[u,o.formData.paid_amount]])]),a("div",da,[a("label",ra,r(t.$t("totalExpenses")),1),a("input",{id:"paid_amount",type:"number",value:o.formData.dubai_exp+o.formData.dubai_shipping+o.formData.erbil_exp+o.formData.erbil_shipping,disabled:"",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"},null,8,ta)])]),a("div",la,[a("label",sa,r(t.$t("sellingPrice")),1),i(a("input",{id:"pay_price",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":d[9]||(d[9]=e=>o.formData.pay_price=e)},null,512),[[u,o.formData.pay_price]])]),a("div",ia,[a("label",na,r(t.$t("customer")),1),a("div",ua,[f(n)?g("",!0):i((l(),s("select",{key:0,"onUpdate:modelValue":d[10]||(d[10]=e=>o.formData.client_id=e),id:"color_id",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"},[a("option",ma,r(t.$t("selectCustomer")),1),(l(!0),s(c,null,y(o.client,(e,m)=>(l(),s("option",{key:m,value:e.id},r(e.name),9,ga))),128))],512)),[[b,o.formData.client_id]]),f(n)?g("",!0):(l(),s("button",{key:1,type:"button",onClick:d[11]||(d[11]=e=>{k(n)?n.value=!0:n=!0,o.formData.client_name=""}),class:"absolute left-0 top-0 h-full px-3 py-2 font-bold text-white bg-green-500 rounded-tl-lg rounded-bl-lg"},r(t.$t("addCustomer")),1))]),a("div",ca,[f(n)?i((l(),s("input",{key:0,id:"note",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":d[12]||(d[12]=e=>o.formData.client_name=e)},null,512)),[[u,o.formData.client_name]]):g("",!0),f(n)?(l(),s("button",{key:1,type:"button",onClick:d[13]||(d[13]=e=>{k(n)?n.value=!1:n=!1,o.formData.client=""}),class:"absolute left-0 top-0 h-full px-3 py-2 font-bold text-white bg-pink-500 rounded-tl-lg rounded-bl-lg"},r(t.$t("selectCustomer")),1)):g("",!0)])]),f(n)?(l(),s("div",ba,[D(r(t.$t("phoneNumber"))+" ",1),i(a("input",{id:"note",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":d[14]||(d[14]=e=>o.formData.client_phone=e)},null,512),[[u,o.formData.client_phone]])])):g("",!0),a("div",ya,[a("label",fa,r(t.$t("paid_amount")),1),i(a("input",{id:"paid_amount_pay",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":d[15]||(d[15]=e=>o.formData.paid_amount_pay=e)},null,512),[[u,o.formData.paid_amount_pay]])]),a("div",ka,[a("label",pa,r(t.$t("note")),1),i(a("input",{id:"note_pay",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":d[16]||(d[16]=e=>o.formData.note_pay=e)},null,512),[[u,o.formData.note_pay]])])]),a("div",ha,[a("div",va,[a("div",xa,[a("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:d[17]||(d[17]=e=>{t.$emit("close")})},r(t.$t("cancel")),1)]),a("div",Da,[a("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:d[18]||(d[18]=e=>{t.$emit("a",o.formData),o.formData=""}),disabled:!(o.formData.paid_amount_pay&&o.formData.pay_price)},r(t.$t("yes")),9,wa)])])])])])])):g("",!0)]),_:3}))}};export{$a as _};
