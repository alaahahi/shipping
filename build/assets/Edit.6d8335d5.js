import{u as w,r as l,a as i,g as t,j as a,w as f,F as V,o as m,H as N,c as y,i as r,b as e,L as U,f as j,l as B}from"./app.31e42467.js";import{_ as F}from"./AuthenticatedLayout.8d9e20a0.js";import{_,a as u}from"./TextInput.f006265d.js";/* empty css                                                 */import{W as p}from"./WebCamUI.7c2f71e9.js";const H=e("h2",{class:"font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200"}," \u0645\u062D\u0627\u0641\u0638\u0629 \u0643\u0631\u0643\u0648\u0643 - \u0627\u0644\u0639\u0642\u062F \u0627\u0644\u0625\u0644\u0643\u062A\u0631\u0648\u0646\u064A ",-1),W=["onSubmit"],C={class:"flex flex-row"},E={class:"grow"},S={class:"py-6"},T={class:"max-w-7xl mx-auto sm:px-6 lg:px-8"},$={class:"bg-white overflow-hidden shadow-sm sm:rounded-lg"},A={class:"p-6 dark:bg-gray-900"},L=e("h2",{class:"text-center dark:text-gray-200text-xl py-2"},"\u0645\u0639\u0644\u0648\u0645\u0627\u062A \u0627\u0644\u0628\u0637\u0627\u0642\u0629",-1),P={className:"flex flex-col"},R={className:"mb-4"},D={key:0,className:"text-red-600"},I={className:"mb-4"},M={key:0,className:"text-red-600"},q={className:"mb-4"},z={key:0,className:"text-red-600"},G={className:"mb-4"},J={key:0,className:"text-red-600"},K={class:"flex flex-row"},O={class:"grow"},Q={class:"pb-3"},X={class:"mx-auto mx-7"},Y={class:"bg-white overflow-hidden shadow-sm sm:rounded-lg"},Z={class:"p-6 dark:bg-gray-900"},ee={class:"flex flex-row"},ae={class:"basis-1/2"},se={className:"mb-4 mx-5"},te={key:0,className:"text-red-600"},oe={class:"basis-1/2"},ne={className:"mb-4 mx-5"},de={key:0,className:"text-red-600"},le={className:"flex items-center justify-center my-6 "},me=e("button",{type:"submit",className:"px-6 mb-12 mx-2 py-2 font-bold text-white bg-rose-500 rounded"}," \u062D\u0641\u0638 \u0627\u0644\u062A\u0639\u062F\u064A\u0644\u0627\u062A ",-1),fe={__name:"Edit",props:{data:Array,url:String,sales:Array},setup(x){const o=x,s=w({name:o.data.name,birthdate:o.data.birthdate,certification:o.data.certification,job:o.data.job,address:o.data.address,phone_number:o.data.phone_number,invoice_number:o.data.invoice_number,relatives:o.data.relatives,saler_id:o.data.saler_id,card_number:o.data.card_number,family_name:o.data.family_name});let b=l(!1);l("\u0627\u0644\u064A\u0648\u0645"),l("\u0627\u0644\u0634\u0647\u0631"),l("\u0627\u0644\u0633\u0646\u0629"),l("\u0627\u0644\u064A\u0648\u0645"),l("\u0627\u0644\u0634\u0647\u0631"),l("\u0627\u0644\u0633\u0646\u0629");let h=l(!1);l([{key:"\u0628\u062F\u0648\u0646",name:"\u0628\u062F\u0648\u0646"},{key:"\u0627\u0628\u062A\u062F\u0627\u0626\u064A",name:"\u0627\u0628\u062A\u062F\u0627\u0626\u064A"},{key:"\u0627\u0639\u062F\u0627\u062F\u064A",name:"\u0627\u0639\u062F\u0627\u062F\u064A"},{key:"\u062F\u0628\u0644\u0648\u0645",name:"\u062F\u0628\u0644\u0648\u0645"},{key:"\u0628\u0643\u0627\u0644\u0648\u0631\u064A\u0648\u0633",name:"\u0628\u0643\u0627\u0644\u0648\u0631\u064A\u0648\u0633"},{key:"\u0645\u0627\u062C\u0633\u062A\u064A\u0631",name:"\u0645\u0627\u062C\u0633\u062A\u064A\u0631"},{key:"\u062F\u0643\u062A\u0648\u0631\u0627\u0647",name:"\u062F\u0643\u062A\u0648\u0631\u0627\u0647"}]),l([{key:"\u0642\u0631\u064A\u0628",name:"\u0642\u0631\u064A\u0628"},{key:"\u0628\u0639\u064A\u062F",name:"\u0628\u0639\u064A\u062F"}]),l([{key:"01",name:"01"},{key:"02",name:"02"},{key:"03",name:"03"},{key:"04",name:"04"},{key:"05",name:"05"},{key:"06",name:"06"},{key:"07",name:"07"},{key:"08",name:"08"},{key:"09",name:"09"}]);const v=()=>{s.post(route("formRegistrationstoreEdit",o.data.id))},k=c=>{s.husband_image=c.image_data_url,h.value=!1},g=c=>{s.wife_image=c.image_data_url,b.value=!1};return(c,n)=>(m(),i(V,null,[t(a(N),{title:"Dashboard"}),t(F,null,{header:f(()=>[H,a(h)?(m(),y(a(p),{key:0,onPhotoTaken:k})):r("",!0),a(b)?(m(),y(a(p),{key:1,onPhotoTaken:g})):r("",!0)]),default:f(()=>[e("form",{name:"createForm",onSubmit:B(v,["prevent"])},[e("div",C,[e("div",E,[e("div",S,[e("div",T,[e("div",$,[e("div",A,[L,e("div",P,[e("div",R,[t(_,{for:"card_number",value:"\u0631\u0642\u0645 \u0627\u0644\u0628\u0637\u0627\u0642\u0629"}),t(u,{id:"card_number",type:"number",class:"mt-1 block w-full",modelValue:a(s).card_number,"onUpdate:modelValue":n[0]||(n[0]=d=>a(s).card_number=d)},null,8,["modelValue"]),a(s).errors.card_number?(m(),i("span",D," \u0631\u0642\u0645 \u0627\u0644\u0628\u0637\u0627\u0642\u0629 \u062D\u0642\u0644 \u0645\u0637\u0644\u0648\u0628 ")):r("",!0)]),e("div",I,[t(_,{for:"name",value:"\u0627\u0644\u0623\u0633\u0645"}),t(u,{id:"name",type:"text",class:"mt-1 block w-full",modelValue:a(s).name,"onUpdate:modelValue":n[1]||(n[1]=d=>a(s).name=d)},null,8,["modelValue"]),a(s).errors.name?(m(),i("span",M," \u0627\u0644\u0623\u0633\u0645 \u062D\u0642\u0644 \u0645\u0637\u0644\u0648\u0628 ")):r("",!0)]),e("div",q,[t(_,{for:"address",value:"\u0627\u0644\u0639\u0646\u0648\u0627\u0646"}),t(u,{id:"address",type:"text",class:"mt-1 block w-full",modelValue:a(s).address,"onUpdate:modelValue":n[2]||(n[2]=d=>a(s).address=d)},null,8,["modelValue"]),a(s).errors.address?(m(),i("span",z," \u0627\u0644\u0639\u0646\u0648\u0627\u0646 \u062D\u0642\u0644 \u0645\u0637\u0644\u0648\u0628 ")):r("",!0)]),e("div",G,[t(_,{for:"family_name",value:"\u0623\u0641\u0631\u0627\u062F \u0627\u0644\u0639\u0627\u0626\u0644\u0629"}),t(u,{id:"family_name",type:"text",class:"mt-1 block w-full",modelValue:a(s).family_name,"onUpdate:modelValue":n[3]||(n[3]=d=>a(s).family_name=d)},null,8,["modelValue"]),a(s).errors.family_name?(m(),i("span",J," \u0623\u0641\u0631\u0627\u062F \u0627\u0644\u0639\u0627\u0626\u0644\u0629 \u062D\u0642\u0644 \u0645\u0637\u0644\u0648\u0628 ")):r("",!0)])])])])])])])]),e("div",K,[e("div",O,[e("div",Q,[e("div",X,[e("div",Y,[e("div",Z,[e("div",ee,[e("div",ae,[e("div",se,[t(_,{for:"invoice_number",value:"\u0631\u0642\u0645 \u0627\u0644\u0648\u0635\u0644"}),t(u,{id:"invoice_number",type:"number",class:"mt-1 block w-full",modelValue:a(s).invoice_number,"onUpdate:modelValue":n[4]||(n[4]=d=>a(s).invoice_number=d)},null,8,["modelValue"]),a(s).errors.invoice_number?(m(),i("span",te," \u0631\u0642\u0645 \u0627\u0644\u0648\u0635\u0644 \u062D\u0642\u0644 \u0645\u0637\u0644\u0648\u0628 ")):r("",!0)])]),e("div",oe,[e("div",ne,[t(_,{for:"phone_number",value:"\u0631\u0642\u0645 \u0627\u0644\u0647\u0627\u062A\u0641"}),t(u,{id:"phone_number",type:"text",class:"mt-1 block w-full",modelValue:a(s).phone_number,"onUpdate:modelValue":n[5]||(n[5]=d=>a(s).phone_number=d)},null,8,["modelValue"]),a(s).errors.phone_number?(m(),i("span",de," \u0631\u0642\u0645 \u0627\u0644\u0647\u0627\u062A\u0641 \u062D\u0642\u0644 \u0645\u0637\u0644\u0648\u0628 ")):r("",!0)])])])])])])])])]),e("div",le,[t(a(U),{className:"px-6 mx-2 py-2 mb-12 text-white bg-gray-500 rounded-md focus:outline-none rounded",href:c.route("formRegistration")},{default:f(()=>[j(" \u0627\u0644\u0639\u0648\u062F\u0629 ")]),_:1},8,["href"]),me])],40,W)]),_:1})],64))}};export{fe as default};
