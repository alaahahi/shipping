import{b as f,a as m,f as o,u as s,d as b,e as d,g as u,F as g,o as i,H as x,h as e,j as y,L as v,k,t as w}from"./app.eaa1f3f6.js";import{_ as N}from"./AuthenticatedLayout.03465553.js";import{_ as c,a as p}from"./TextInput.d04560d5.js";const V=e("h2",{class:"font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200"}," \u062A\u0639\u062F\u064A\u0644 \u0645\u0639\u0644\u0648\u0645\u0627\u062A \u0627\u0644\u0632\u0628\u0648\u0646 ",-1),S={class:"py-12"},$={class:"max-w-7xl mx-auto sm:px-6 lg:px-8"},B={class:"bg-white overflow-hidden shadow-sm sm:rounded-lg"},F={class:"p-6 dark:bg-gray-900"},j={className:"flex items-center justify-between mb-6"},D=["onSubmit"],H={className:"flex flex-col"},C={className:"mb-4"},E={key:0,className:"text-red-600"},L={class:"mb-4"},T=e("div",{className:"mt-4"},[e("button",{type:"submit",className:"px-6 py-2 font-bold text-white bg-rose-500 rounded"}," Save ")],-1),O={__name:"Edit",props:{user:Object,url:String,usersType:Array,userSeles:String,userHospital:String,userDoctor:String},setup(_){const r=_,t=f({name:r.user.name,phone:r.user.phone}),h=()=>{t.put(route("users.update",r.user.id))};return(n,a)=>(i(),m(g,null,[o(s(x),{title:"Dashboard"}),n.$page.props.auth.user.type_id==1?(i(),b(N,{key:0},{header:d(()=>[V]),default:d(()=>[e("div",S,[e("div",$,[e("div",B,[e("div",F,[e("div",j,[o(s(v),{className:"px-6 py-2 text-white bg-gray-500 rounded-md focus:outline-none",href:n.route("users.index")},{default:d(()=>[y(" \u0627\u0644\u0639\u0648\u062F\u0629 ")]),_:1},8,["href"])]),e("form",{name:"createForm",onSubmit:k(h,["prevent"])},[e("div",H,[e("div",C,[o(c,{for:"name",value:"\u0627\u0644\u0623\u0633\u0645"}),o(p,{id:"name",type:"text",class:"mt-1 block w-full",modelValue:s(t).name,"onUpdate:modelValue":a[0]||(a[0]=l=>s(t).name=l),autofocus:""},null,8,["modelValue"]),s(t).errors.name?(i(),m("span",E,w(s(t).errors.name),1)):u("",!0)]),e("div",L,[o(c,{for:"phone",value:n.$t("phone")},null,8,["value"]),o(p,{id:"phone",type:"number",class:"mt-1 block w-full",modelValue:s(t).phone,"onUpdate:modelValue":a[1]||(a[1]=l=>s(t).phone=l)},null,8,["modelValue"])])]),T],40,D)])])])])]),_:1})):u("",!0)],64))}};export{O as default};