import{l as N,r as n,a as i,j as o,k as a,w as b,F as p,o as l,H as U,c as k,f as m,b as e,e as S,h as j,g as B,t as F,L as H,i as T,p as W}from"./app.1146821b.js";import{_ as C}from"./AuthenticatedLayout.6af70782.js";import{_ as u,a as c}from"./TextInput.1140b97a.js";/* empty css                                                 */import{W as v}from"./WebCamUI.4fbe0362.js";const D=e("h2",{class:"font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200"}," \u0645\u062D\u0627\u0641\u0638\u0629 \u0643\u0631\u0643\u0648\u0643 - \u0627\u0644\u0639\u0642\u062F \u0627\u0644\u0625\u0644\u0643\u062A\u0631\u0648\u0646\u064A ",-1),E=["onSubmit"],L={class:"flex flex-row"},$={class:"grow"},A={class:"py-6"},M={class:"max-w-7xl mx-auto sm:px-6 lg:px-8"},P={class:"bg-white overflow-hidden shadow-sm sm:rounded-lg"},R={class:"p-6 dark:bg-gray-900"},I=e("h2",{class:"text-center dark:text-gray-200text-xl py-2"},"\u0645\u0639\u0644\u0648\u0645\u0627\u062A \u0627\u0644\u0628\u0637\u0627\u0642\u0629",-1),q={className:"flex flex-col"},z={className:"mb-4"},G={key:0,className:"text-red-600"},J={className:"mb-4"},K={key:0,className:"text-red-600"},O={className:"mb-4"},Q=e("option",{selected:"",disabled:""},"\u0627\u0644\u0645\u0646\u062F\u0648\u0628",-1),X=["value"],Y={key:0,className:"text-red-600"},Z={className:"mb-4"},ee={key:0,className:"text-red-600"},ae={className:"mb-4"},se={key:0,className:"text-red-600"},te={class:"flex flex-row"},oe={class:"grow"},de={class:"pb-3"},re={class:"mx-auto mx-7"},le={class:"bg-white overflow-hidden shadow-sm sm:rounded-lg"},ne={class:"p-6 dark:bg-gray-900"},ie={class:"flex flex-row"},me={class:"basis-1/2"},ue={className:"mb-4 mx-5"},ce={key:0,className:"text-red-600"},_e={class:"basis-1/2"},be={className:"mb-4 mx-5"},fe={key:0,className:"text-red-600"},ye={className:"flex items-center justify-center my-6 "},he=e("button",{type:"submit",className:"px-6 mb-12 mx-2 py-2 font-bold text-white bg-rose-500 rounded"}," \u062D\u0641\u0638 \u0627\u0644\u062A\u0639\u062F\u064A\u0644\u0627\u062A ",-1),we={__name:"Edit",props:{data:Array,url:String,sales:Array},setup(f){const r=f,s=N({name:r.data.name,birthdate:r.data.birthdate,certification:r.data.certification,job:r.data.job,address:r.data.address,phone_number:r.data.phone_number,invoice_number:r.data.invoice_number,relatives:r.data.relatives,saler_id:r.data.saler_id,card_number:r.data.card_number,family_name:r.data.family_name});let y=n(!1);n("\u0627\u0644\u064A\u0648\u0645"),n("\u0627\u0644\u0634\u0647\u0631"),n("\u0627\u0644\u0633\u0646\u0629"),n("\u0627\u0644\u064A\u0648\u0645"),n("\u0627\u0644\u0634\u0647\u0631"),n("\u0627\u0644\u0633\u0646\u0629");let h=n(!1);n([{key:"\u0628\u062F\u0648\u0646",name:"\u0628\u062F\u0648\u0646"},{key:"\u0627\u0628\u062A\u062F\u0627\u0626\u064A",name:"\u0627\u0628\u062A\u062F\u0627\u0626\u064A"},{key:"\u0627\u0639\u062F\u0627\u062F\u064A",name:"\u0627\u0639\u062F\u0627\u062F\u064A"},{key:"\u062F\u0628\u0644\u0648\u0645",name:"\u062F\u0628\u0644\u0648\u0645"},{key:"\u0628\u0643\u0627\u0644\u0648\u0631\u064A\u0648\u0633",name:"\u0628\u0643\u0627\u0644\u0648\u0631\u064A\u0648\u0633"},{key:"\u0645\u0627\u062C\u0633\u062A\u064A\u0631",name:"\u0645\u0627\u062C\u0633\u062A\u064A\u0631"},{key:"\u062F\u0643\u062A\u0648\u0631\u0627\u0647",name:"\u062F\u0643\u062A\u0648\u0631\u0627\u0647"}]),n([{key:"\u0642\u0631\u064A\u0628",name:"\u0642\u0631\u064A\u0628"},{key:"\u0628\u0639\u064A\u062F",name:"\u0628\u0639\u064A\u062F"}]),n([{key:"01",name:"01"},{key:"02",name:"02"},{key:"03",name:"03"},{key:"04",name:"04"},{key:"05",name:"05"},{key:"06",name:"06"},{key:"07",name:"07"},{key:"08",name:"08"},{key:"09",name:"09"}]);const x=()=>{s.post(route("formRegistrationstoreEdit",r.data.id))},g=_=>{s.husband_image=_.image_data_url,h.value=!1},w=_=>{s.wife_image=_.image_data_url,y.value=!1};return(_,d)=>(l(),i(p,null,[o(a(U),{title:"Dashboard"}),o(C,null,{header:b(()=>[D,a(h)?(l(),k(a(v),{key:0,onPhotoTaken:g})):m("",!0),a(y)?(l(),k(a(v),{key:1,onPhotoTaken:w})):m("",!0)]),default:b(()=>[e("form",{name:"createForm",onSubmit:W(x,["prevent"])},[e("div",L,[e("div",$,[e("div",A,[e("div",M,[e("div",P,[e("div",R,[I,e("div",q,[e("div",z,[o(u,{for:"card_number",value:"\u0631\u0642\u0645 \u0627\u0644\u0628\u0637\u0627\u0642\u0629"}),o(c,{id:"card_number",type:"number",class:"mt-1 block w-full",modelValue:a(s).card_number,"onUpdate:modelValue":d[0]||(d[0]=t=>a(s).card_number=t)},null,8,["modelValue"]),a(s).errors.card_number?(l(),i("span",G," \u0631\u0642\u0645 \u0627\u0644\u0628\u0637\u0627\u0642\u0629 \u062D\u0642\u0644 \u0645\u0637\u0644\u0648\u0628 ")):m("",!0)]),e("div",J,[o(u,{for:"name",value:"\u0627\u0644\u0623\u0633\u0645"}),o(c,{id:"name",type:"text",class:"mt-1 block w-full",modelValue:a(s).name,"onUpdate:modelValue":d[1]||(d[1]=t=>a(s).name=t)},null,8,["modelValue"]),a(s).errors.name?(l(),i("span",K," \u0627\u0644\u0623\u0633\u0645 \u062D\u0642\u0644 \u0645\u0637\u0644\u0648\u0628 ")):m("",!0)]),e("div",O,[o(u,{for:"sales_id",value:"\u0627\u0644\u0645\u0646\u062F\u0648\u0628"}),S(e("select",{"onUpdate:modelValue":d[2]||(d[2]=t=>a(s).saler_id=t),id:"userType",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"},[Q,(l(!0),i(p,null,B(f.sales,(t,V)=>(l(),i("option",{key:V,value:t.id},F(t.name),9,X))),128))],512),[[j,a(s).saler_id]]),a(s).errors.saler_id?(l(),i("span",Y," \u0627\u0633\u0645 \u0627\u0644\u0645\u0646\u062F\u0648\u0628 \u062D\u0642\u0644 \u0645\u0637\u0644\u0648\u0628 ")):m("",!0)]),e("div",Z,[o(u,{for:"address",value:"\u0627\u0644\u0639\u0646\u0648\u0627\u0646"}),o(c,{id:"address",type:"text",class:"mt-1 block w-full",modelValue:a(s).address,"onUpdate:modelValue":d[3]||(d[3]=t=>a(s).address=t)},null,8,["modelValue"]),a(s).errors.address?(l(),i("span",ee," \u0627\u0644\u0639\u0646\u0648\u0627\u0646 \u062D\u0642\u0644 \u0645\u0637\u0644\u0648\u0628 ")):m("",!0)]),e("div",ae,[o(u,{for:"family_name",value:"\u0623\u0641\u0631\u0627\u062F \u0627\u0644\u0639\u0627\u0626\u0644\u0629"}),o(c,{id:"family_name",type:"text",class:"mt-1 block w-full",modelValue:a(s).family_name,"onUpdate:modelValue":d[4]||(d[4]=t=>a(s).family_name=t)},null,8,["modelValue"]),a(s).errors.family_name?(l(),i("span",se," \u0623\u0641\u0631\u0627\u062F \u0627\u0644\u0639\u0627\u0626\u0644\u0629 \u062D\u0642\u0644 \u0645\u0637\u0644\u0648\u0628 ")):m("",!0)])])])])])])])]),e("div",te,[e("div",oe,[e("div",de,[e("div",re,[e("div",le,[e("div",ne,[e("div",ie,[e("div",me,[e("div",ue,[o(u,{for:"invoice_number",value:"\u0631\u0642\u0645 \u0627\u0644\u0648\u0635\u0644"}),o(c,{id:"invoice_number",type:"number",class:"mt-1 block w-full",modelValue:a(s).invoice_number,"onUpdate:modelValue":d[5]||(d[5]=t=>a(s).invoice_number=t)},null,8,["modelValue"]),a(s).errors.invoice_number?(l(),i("span",ce," \u0631\u0642\u0645 \u0627\u0644\u0648\u0635\u0644 \u062D\u0642\u0644 \u0645\u0637\u0644\u0648\u0628 ")):m("",!0)])]),e("div",_e,[e("div",be,[o(u,{for:"phone_number",value:"\u0631\u0642\u0645 \u0627\u0644\u0647\u0627\u062A\u0641"}),o(c,{id:"phone_number",type:"text",class:"mt-1 block w-full",modelValue:a(s).phone_number,"onUpdate:modelValue":d[6]||(d[6]=t=>a(s).phone_number=t)},null,8,["modelValue"]),a(s).errors.phone_number?(l(),i("span",fe," \u0631\u0642\u0645 \u0627\u0644\u0647\u0627\u062A\u0641 \u062D\u0642\u0644 \u0645\u0637\u0644\u0648\u0628 ")):m("",!0)])])])])])])])])]),e("div",ye,[o(a(H),{className:"px-6 mx-2 py-2 mb-12 text-white bg-gray-500 rounded-md focus:outline-none rounded",href:_.route("formRegistration")},{default:b(()=>[T(" \u0627\u0644\u0639\u0648\u062F\u0629 ")]),_:1},8,["href"]),he])],40,E)]),_:1})],64))}};export{we as default};
