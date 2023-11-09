import{l as V,r as n,a as d,j as t,k as s,w as b,F as y,o as l,H as N,b as e,f as i,e as k,h,g as v,t as x,L as U,i as F,p as g}from"./app.2fb5b7f5.js";import{_ as C}from"./AuthenticatedLayout.24f4f092.js";import{_ as m,a as u}from"./TextInput.fe87a3f5.js";/* empty css                                                 */const L=e("h2",{class:"font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200"}," \u0627\u0644\u0639\u0642\u062F \u0627\u0644\u0625\u0644\u0643\u062A\u0631\u0648\u0646\u064A ",-1),S=["onSubmit"],j={class:"flex flex-row"},A={class:"grow"},B={class:"py-6"},D={class:"max-w-7xl mx-auto sm:px-6 lg:px-8"},R={class:"bg-white overflow-hidden shadow-sm sm:rounded-lg"},T={class:"p-6 dark:bg-gray-900"},$=e("h2",{class:"text-center dark:text-gray-200text-xl py-2"},"\u0645\u0639\u0644\u0648\u0645\u0627\u062A \u0627\u0644\u0628\u0637\u0627\u0642\u0629",-1),H={className:"flex flex-col"},M={className:"mb-4"},E={key:0,className:"text-red-600"},q={className:"mb-4"},z={key:0,className:"text-red-600"},G={className:"mb-4"},I=e("option",{selected:"",disabled:""},"\u0627\u0644\u0645\u0646\u062F\u0648\u0628",-1),J=["value"],K={key:0,className:"text-red-600"},O={className:"mb-4"},P={key:0,className:"text-red-600"},Q={className:"mb-4"},W={key:0,className:"text-red-600"},X={class:"flex flex-row"},Y={class:"grow"},Z={class:"pb-3"},ee={class:"mx-auto mx-7"},se={class:"bg-white overflow-hidden shadow-sm sm:rounded-lg"},ae={class:"p-6 dark:bg-gray-900"},oe={class:"flex flex-row"},te={class:"basis-1/3"},re={className:"mb-4 mx-5"},de={key:0,className:"text-red-600"},le={class:"basis-1/3"},ne={className:"mb-4 mx-5"},ie={key:0,className:"text-red-600"},me={class:"basis-1/3"},ue={className:"mb-4 mx-5"},ce=e("option",{selected:"",disabled:""},"\u062A\u062D\u062F\u064A\u062F \u0627\u0644\u0628\u0637\u0627\u0642\u0629",-1),_e=["value"],be={key:0,className:"text-red-600"},ye={className:"flex items-center justify-center my-6 "},fe=["onClick","disabled"],pe={key:0},ke={key:1},we={__name:"FormRegistration",props:{usersType:Array,sales:Array,cards:Array},setup(f){const a=V({saler_id:"",card_number:"",name:"",birthdate:"",certification:"",job:"",address:"",image:"",phone_number:"",invoice_number:"",card_id:"",family_name:""});n(!1),n("\u0627\u0644\u064A\u0648\u0645"),n("\u0627\u0644\u0634\u0647\u0631"),n("\u0627\u0644\u0633\u0646\u0629"),n(!1),n([{key:"\u0628\u062F\u0648\u0646",name:"\u0628\u062F\u0648\u0646"},{key:"\u0627\u0628\u062A\u062F\u0627\u0626\u064A",name:"\u0627\u0628\u062A\u062F\u0627\u0626\u064A"},{key:"\u0645\u062A\u0648\u0633\u0637\u0629",name:"\u0645\u062A\u0648\u0633\u0637\u0629"},{key:"\u0627\u0639\u062F\u0627\u062F\u064A",name:"\u0627\u0639\u062F\u0627\u062F\u064A"},{key:"\u062F\u0628\u0644\u0648\u0645",name:"\u062F\u0628\u0644\u0648\u0645"},{key:"\u0628\u0643\u0627\u0644\u0648\u0631\u064A\u0648\u0633",name:"\u0628\u0643\u0627\u0644\u0648\u0631\u064A\u0648\u0633"},{key:"\u0645\u0627\u062C\u0633\u062A\u064A\u0631",name:"\u0645\u0627\u062C\u0633\u062A\u064A\u0631"},{key:"\u062F\u0643\u062A\u0648\u0631\u0627\u0647",name:"\u062F\u0643\u062A\u0648\u0631\u0627\u0647"}]),n([{key:"1",name:"\u0627\u0644\u0647\u0644\u0627\u0644 \u0627\u0644\u0623\u062D\u0645\u0631"}]),n([{key:"01",name:"01"},{key:"02",name:"02"},{key:"03",name:"03"},{key:"04",name:"04"},{key:"05",name:"05"},{key:"06",name:"06"},{key:"07",name:"07"},{key:"08",name:"08"},{key:"09",name:"09"}]);const c=n(!1),p=()=>{c.value=!0,a.post(route("formRegistration")),c.value=!1};return(w,r)=>(l(),d(y,null,[t(s(N),{title:"Dashboard"}),t(C,null,{header:b(()=>[L]),default:b(()=>[e("form",{name:"createForm",onSubmit:g(p,["prevent"])},[e("div",j,[e("div",A,[e("div",B,[e("div",D,[e("div",R,[e("div",T,[$,e("div",H,[e("div",M,[t(m,{for:"card_number",value:"\u0631\u0642\u0645 \u0627\u0644\u0628\u0637\u0627\u0642\u0629"}),t(u,{id:"card_number",type:"number",class:"mt-1 block w-full",modelValue:s(a).card_number,"onUpdate:modelValue":r[0]||(r[0]=o=>s(a).card_number=o)},null,8,["modelValue"]),s(a).errors.card_number?(l(),d("span",E," \u0631\u0642\u0645 \u0627\u0644\u0628\u0637\u0627\u0642\u0629 \u063A\u064A\u0631 \u0635\u062D\u064A\u062D \u0623\u0648 \u0645\u0633\u062A\u062E\u062F\u0645 \u0645\u0646 \u0642\u0628\u0644 ")):i("",!0)]),e("div",q,[t(m,{for:"name",value:"\u0627\u0644\u0623\u0633\u0645"}),t(u,{id:"name",type:"text",class:"mt-1 block w-full",modelValue:s(a).name,"onUpdate:modelValue":r[1]||(r[1]=o=>s(a).name=o)},null,8,["modelValue"]),s(a).errors.name?(l(),d("span",z," \u0627\u0644\u0623\u0633\u0645 \u062D\u0642\u0644 \u0645\u0637\u0644\u0648\u0628 ")):i("",!0)]),e("div",G,[t(m,{for:"sales_id",value:"\u0627\u0644\u0645\u0646\u062F\u0648\u0628"}),k(e("select",{"onUpdate:modelValue":r[2]||(r[2]=o=>s(a).saler_id=o),id:"userType",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"},[I,(l(!0),d(y,null,v(f.sales,(o,_)=>(l(),d("option",{key:_,value:o.id},x(o.name),9,J))),128))],512),[[h,s(a).saler_id]]),s(a).errors.saler_id?(l(),d("span",K," \u0627\u0633\u0645 \u0627\u0644\u0645\u0646\u062F\u0648\u0628 \u062D\u0642\u0644 \u0645\u0637\u0644\u0648\u0628 ")):i("",!0)]),e("div",O,[t(m,{for:"address",value:"\u0627\u0644\u0639\u0646\u0648\u0627\u0646"}),t(u,{id:"address",type:"text",class:"mt-1 block w-full",modelValue:s(a).address,"onUpdate:modelValue":r[3]||(r[3]=o=>s(a).address=o)},null,8,["modelValue"]),s(a).errors.address?(l(),d("span",P," \u0627\u0644\u0639\u0646\u0648\u0627\u0646 \u062D\u0642\u0644 \u0645\u0637\u0644\u0648\u0628 ")):i("",!0)]),e("div",Q,[t(m,{for:"family_name",value:"\u0623\u0641\u0631\u0627\u062F \u0627\u0644\u0639\u0627\u0626\u0644\u0629"}),t(u,{id:"family_name",type:"text",class:"mt-1 block w-full",modelValue:s(a).family_name,"onUpdate:modelValue":r[4]||(r[4]=o=>s(a).family_name=o)},null,8,["modelValue"]),s(a).errors.family_name?(l(),d("span",W," \u0623\u0641\u0631\u0627\u062F \u0627\u0644\u0639\u0627\u0626\u0644\u0629 \u062D\u0642\u0644 \u0645\u0637\u0644\u0648\u0628 ")):i("",!0)])])])])])])])]),e("div",X,[e("div",Y,[e("div",Z,[e("div",ee,[e("div",se,[e("div",ae,[e("div",oe,[e("div",te,[e("div",re,[t(m,{for:"invoice_number",value:"\u0631\u0642\u0645 \u0627\u0644\u0648\u0635\u0644"}),t(u,{id:"invoice_number",type:"number",class:"mt-1 block w-full",modelValue:s(a).invoice_number,"onUpdate:modelValue":r[5]||(r[5]=o=>s(a).invoice_number=o)},null,8,["modelValue"]),s(a).errors.invoice_number?(l(),d("span",de," \u0631\u0642\u0645 \u0627\u0644\u0648\u0635\u0644 \u062D\u0642\u0644 \u0645\u0637\u0644\u0648\u0628 ")):i("",!0)])]),e("div",le,[e("div",ne,[t(m,{for:"phone_number",value:"\u0631\u0642\u0645 \u0627\u0644\u0647\u0627\u062A\u0641"}),t(u,{id:"phone_number",type:"text",class:"mt-1 block w-full",modelValue:s(a).phone_number,"onUpdate:modelValue":r[6]||(r[6]=o=>s(a).phone_number=o)},null,8,["modelValue"]),s(a).errors.phone_number?(l(),d("span",ie," \u0631\u0642\u0645 \u0627\u0644\u0647\u0627\u062A\u0641 \u062D\u0642\u0644 \u0645\u0637\u0644\u0648\u0628 ")):i("",!0)])]),e("div",me,[e("div",ue,[t(m,{for:"card_id",value:"\u0627\u0644\u0628\u0637\u0627\u0642\u0629"}),k(e("select",{"onUpdate:modelValue":r[7]||(r[7]=o=>s(a).card_id=o),id:"card_id",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"},[ce,(l(!0),d(y,null,v(f.cards,(o,_)=>(l(),d("option",{key:_,value:o.id},x(o.name),9,_e))),128))],512),[[h,s(a).card_id]]),s(a).errors.card_id?(l(),d("span",be," \u0627\u0644\u0628\u0637\u0627\u0642\u0629 \u062D\u0642\u0644 \u0645\u0637\u0644\u0648\u0628 ")):i("",!0)])])])])])])])])]),e("div",ye,[t(s(U),{className:"px-6 mx-2 py-2 mb-12 text-white bg-gray-500 rounded-md focus:outline-none rounded",href:w.route("formRegistration")},{default:b(()=>[F(" \u0627\u0644\u0639\u0648\u062F\u0629 ")]),_:1},8,["href"]),e("button",{onClick:g(p,["prevent"]),disabled:c.value,class:"px-6 mb-12 mx-2 py-2 font-bold text-white bg-rose-500 rounded"},[c.value?(l(),d("span",ke,"\u062C\u0627\u0631\u064A \u0627\u0644\u062D\u0641\u0638...")):(l(),d("span",pe,"\u062D\u0641\u0638"))],8,fe)])],40,S)]),_:1})],64))}};export{we as default};
