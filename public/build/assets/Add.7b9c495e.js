import{l as h,r as v,a as i,j as t,k as a,w as u,F as x,o as m,H as b,b as e,t as n,f as g,L as y,i as k,p as _}from"./app.7ffb48a5.js";import{_ as w}from"./AuthenticatedLayout.9200d117.js";import{_ as f,a as p}from"./TextInput.3fe0c817.js";const $={class:"font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200"},N=["onSubmit"],V={class:"flex flex-row"},C={class:"grow"},E={class:"py-6"},F={class:"max-w-7xl mx-auto sm:px-6 lg:px-8"},B={class:"bg-white overflow-hidden shadow-sm sm:rounded-lg"},L={class:"p-6 dark:bg-gray-900"},S={class:"text-center dark:text-gray-200text-xl py-2"},j={className:"flex flex-col"},D={className:"mb-4"},H={key:0,class:"text-red-600"},R={className:"mb-4"},U={className:"flex items-center justify-center my-6 "},q=["onClick","disabled"],A={key:0},I={key:1},J={__name:"Add",setup(M){const o=h({name:"",nameEn:""}),l=v(!1),c=()=>{l.value=!0,o.post(route("addCompany")),l.value=!1};return(s,d)=>(m(),i(x,null,[t(a(b),{title:"Dashboard"}),t(w,null,{header:u(()=>[e("h2",$,n(s.$t("addCompany")),1)]),default:u(()=>[e("form",{name:"createForm",onSubmit:_(c,["prevent"])},[e("div",V,[e("div",C,[e("div",E,[e("div",F,[e("div",B,[e("div",L,[e("h2",S,n(s.$t("companyInformation")),1),e("div",j,[e("div",D,[t(f,{for:"name",value:s.$t("name")},null,8,["value"]),t(p,{id:"name",type:"text",class:"mt-1 block w-full",modelValue:a(o).name,"onUpdate:modelValue":d[0]||(d[0]=r=>a(o).name=r)},null,8,["modelValue"]),a(o).errors.name?(m(),i("span",H,n(s.$t("nameRequired")),1)):g("",!0)]),e("div",R,[t(f,{for:"name",value:s.$t("englishName")},null,8,["value"]),t(p,{id:"name",type:"text",class:"mt-1 block w-full",modelValue:a(o).nameEn,"onUpdate:modelValue":d[1]||(d[1]=r=>a(o).nameEn=r)},null,8,["modelValue"])])])])])])])])]),e("div",U,[t(a(y),{className:"px-6 mx-2 py-2 mb-12 text-white bg-gray-500 rounded-md focus:outline-none rounded",href:s.route("formRegistration")},{default:u(()=>[k(n(s.$t("return")),1)]),_:1},8,["href"]),e("button",{onClick:_(c,["prevent"]),disabled:l.value,class:"px-6 mb-12 mx-2 py-2 font-bold text-white bg-rose-500 rounded"},[l.value?(m(),i("span",I,n(s.$t("saving")),1)):(m(),i("span",A,n(s.$t("save")),1))],8,q)])],40,N)]),_:1})],64))}};export{J as default};
