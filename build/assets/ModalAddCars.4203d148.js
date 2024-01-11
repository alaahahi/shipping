import{r as g,o as n,c as w,w as p,a as u,b as a,d as $,t as d,k as m,e as s,g as V,F as N,f as C,h as c,m as k,v as l,T as U}from"./app.30635ba4.js";import{a as S}from"./index.72c4dc04.js";const B={key:0,class:"modal-mask"},M={class:"modal-wrapper max-h-[80vh]"},T={class:"modal-container dark:bg-gray-900 overflow-auto max-h-[80vh]"},E={class:"modal-header"},A={class:"text-center dark:text-gray-200"},F={class:"modal-body"},R={key:0,class:"grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-1 lg:gap-2"},j={class:"mb-4 mx-1"},z={class:"dark:text-gray-200",for:"color_id"},L={class:"relative"},O={selected:"",disabled:""},Y=["value"],q={class:"relative"},G={key:0,className:"mb-4 mx-1"},H={class:"dark:text-gray-200",for:"number"},I={class:"grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-1 lg:gap-2"},J={className:"mb-4 mx-1"},K={class:"dark:text-gray-200",for:"pin"},P={key:0,class:"text-red-700"},Q={className:"mb-4 mx-1"},W={class:"dark:text-gray-200",for:"pin"},X={className:"mb-4 mx-1"},Z={class:"dark:text-gray-200",for:"pin"},_={className:"mb-4 mx-1"},aa={class:"dark:text-gray-200",for:"pin"},ea={className:"mb-4 mx-1"},ta={class:"dark:text-gray-200",for:"car_number"},oa={className:"mb-4 mx-1"},ra={class:"dark:text-gray-200",for:"dinar"},da={className:"mb-4 mx-1"},sa={class:"dark:text-gray-200",for:"dolar_price"},la={key:0,class:"text-red-500"},na={className:"mb-4 mx-1"},ia={class:"dark:text-gray-200",for:"shipping_dolar"},ua={className:"mb-4 mx-1"},ca={class:"dark:text-gray-200",for:"coc_dolar"},ga={className:"mb-4 mx-1"},ma={class:"dark:text-gray-200",for:"checkout"},fa={className:"mb-4 mx-1"},ba={class:"dark:text-gray-200",for:"expenses"},ya={className:"mb-4 mx-1"},ka={class:"dark:text-gray-200",for:"date"},va={className:"mb-4 mx-1"},xa={class:"dark:text-gray-200",for:"note"},ha={class:"modal-footer my-2"},Da={class:"flex flex-row"},wa={class:"basis-1/2 px-4"},pa={class:"basis-1/2 px-4"},$a=["disabled"],Ca={__name:"ModalAddCars",props:{show:Boolean,formData:Object,client:Array},setup(t){const v=t;g(""),g([]),g({}),g([{value:"1",text:"aa - 1"},{value:"2",text:"ab - 2"},{value:"3",text:"bc - 3"},{value:"4",text:"cd - 4"},{value:"5",text:"de - 5"},{value:"6",text:"ef - 6"},{value:"7",text:"ef - 7"},{value:"8",text:"ef - 8"},{value:"9",text:"ef - 9"},{value:"10",text:"ef - 10"},{value:"11",text:"ef - 11"},{value:"12",text:"ef - 12"},{value:"13",text:"down case - testcase"},{value:"14",text:"camel case - testCase"},{value:"15",text:"Capitalize case - Testcase"}]);function x(){const r=new Date,e=r.getFullYear(),o=String(r.getMonth()+1).padStart(2,"0"),y=String(r.getDate()).padStart(2,"0");return`${e}-${o}-${y}`}function h(r){r?S.get(`/api/check_vin?car_vin=${r}`).then(e=>{f.value=e.data,f.value}).catch(e=>{console.error(e)}):f.value=!1}let i=g(!1),f=g(!1),b=g(!1);function D(r){const e=v.formData.dolar_price;/^\d{6}$/.test(e)?b.value=!1:b.value=!0}return(r,e)=>(n(),w(U,{name:"modal"},{default:p(()=>[t.show?(n(),u("div",B,[a("div",M,[a("div",T,[a("div",E,[$(r.$slots,"header",{},()=>[a("h2",A,d(r.$t("add_car")),1)])]),a("div",F,[t.formData.id?c("",!0):(n(),u("div",R,[a("div",j,[a("label",z,d(r.$t("car_owner")),1),a("div",L,[m(i)?c("",!0):s((n(),u("select",{key:0,"onUpdate:modelValue":e[0]||(e[0]=o=>t.formData.client_id=o),id:"color_id",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"},[a("option",O,d(r.$t("selectCustomer")),1),(n(!0),u(N,null,C(t.client,(o,y)=>(n(),u("option",{key:y,value:o.id},d(o.name),9,Y))),128))],512)),[[V,t.formData.client_id]]),m(i)?c("",!0):(n(),u("button",{key:1,type:"button",onClick:e[1]||(e[1]=o=>{k(i)?i.value=!0:i=!0,t.formData.client_name="",t.formData.client_id=""}),class:"absolute left-0 top-0 h-full px-3 py-2 font-bold text-white bg-green-500 rounded-tl-lg rounded-bl-lg"},d(r.$t("addCustomer")),1))]),a("div",q,[m(i)?s((n(),u("input",{key:0,id:"note",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":e[2]||(e[2]=o=>t.formData.client_name=o)},null,512)),[[l,t.formData.client_name]]):c("",!0),m(i)?(n(),u("button",{key:1,type:"button",onClick:e[3]||(e[3]=o=>{k(i)?i.value=!1:i=!1,t.formData.client=""}),class:"absolute left-0 top-0 h-full px-3 py-2 font-bold text-white bg-pink-500 rounded-tl-lg rounded-bl-lg"},d(r.$t("selectCustomer")),1)):c("",!0)])]),m(i)?(n(),u("div",G,[a("label",H,d(r.$t("phoneNumber")),1),s(a("input",{id:"number",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":e[4]||(e[4]=o=>t.formData.client_phone=o)},null,512),[[l,t.formData.client_phone]])])):c("",!0)])),a("div",I,[a("div",J,[a("label",K,d(r.$t("vin")),1),s(a("input",{id:"vin",type:"text",onChange:e[5]||(e[5]=o=>h(t.formData.vin)),class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":e[6]||(e[6]=o=>t.formData.vin=o)},null,544),[[l,t.formData.vin]]),m(f)?(n(),u("div",P," \u0631\u0642\u0645 \u0627\u0644\u0634\u0627\u0635\u064A \u0645\u0633\u062A\u062E\u062F\u0645 ")):c("",!0)]),a("div",Q,[a("label",W,d(r.$t("car_type")),1),s(a("input",{id:"car_type",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":e[7]||(e[7]=o=>t.formData.car_type=o)},null,512),[[l,t.formData.car_type]])]),a("div",X,[a("label",Z,d(r.$t("year")),1),s(a("input",{id:"year",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":e[8]||(e[8]=o=>t.formData.year=o)},null,512),[[l,t.formData.year]])]),a("div",_,[a("label",aa,d(r.$t("color")),1),s(a("input",{id:"car_color",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":e[9]||(e[9]=o=>t.formData.car_color=o)},null,512),[[l,t.formData.car_color]])]),a("div",ea,[a("label",ta,d(r.$t("car_number")),1),s(a("input",{id:"car_number",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":e[10]||(e[10]=o=>t.formData.car_number=o)},null,512),[[l,t.formData.car_number]])]),a("div",oa,[a("label",ra,d(r.$t("dinar")),1),s(a("input",{id:"dinar",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":e[11]||(e[11]=o=>t.formData.dinar=o)},null,512),[[l,t.formData.dinar]])]),a("div",da,[a("label",sa,d(r.$t("dolar_price")),1),s(a("input",{id:"dolar_price",onChange:D,type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":e[12]||(e[12]=o=>t.formData.dolar_price=o)},null,544),[[l,t.formData.dolar_price]]),m(b)?(n(),u("div",la," \u0645\u0637\u0644\u0648\u0628 \u0631\u0642\u0645 \u0645\u0646 6 \u062E\u0627\u0646\u0629 \u0641\u0642\u0637 ")):c("",!0)]),a("div",na,[a("label",ia,d(r.$t("shipping_dolar")),1),s(a("input",{id:"shipping_dolar",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":e[13]||(e[13]=o=>t.formData.shipping_dolar=o)},null,512),[[l,t.formData.shipping_dolar]])]),a("div",ua,[a("label",ca,d(r.$t("coc_dolar")),1),s(a("input",{id:"coc_dolar",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":e[14]||(e[14]=o=>t.formData.coc_dolar=o)},null,512),[[l,t.formData.coc_dolar]])]),a("div",ga,[a("label",ma,d(r.$t("checkout")),1),s(a("input",{id:"checkout",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":e[15]||(e[15]=o=>t.formData.checkout=o)},null,512),[[l,t.formData.checkout]])]),a("div",fa,[a("label",ba,d(r.$t("expenses")),1),s(a("input",{id:"expenses",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":e[16]||(e[16]=o=>t.formData.expenses=o)},null,512),[[l,t.formData.expenses]])]),a("div",ya,[a("label",ka,d(r.$t("date")),1),s(a("input",{id:"date",type:"date",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":e[17]||(e[17]=o=>t.formData.date=o)},null,512),[[l,t.formData.date]])])]),a("div",va,[a("label",xa,d(r.$t("note")),1),s(a("input",{id:"note",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":e[18]||(e[18]=o=>t.formData.note=o)},null,512),[[l,t.formData.note]])])]),a("div",ha,[a("div",Da,[a("div",wa,[a("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:e[19]||(e[19]=o=>r.$emit("close"))},d(r.$t("cancel")),1)]),a("div",pa,[a("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:e[20]||(e[20]=o=>{t.formData.date=t.formData.date?t.formData.date:x(),r.$emit("a",t.formData),t.formData=""}),disabled:!t.formData.client_id&&!t.formData.client_name},d(r.$t("yes")),9,$a)])])])])])])):c("",!0)]),_:3}))}};export{Ca as _};
