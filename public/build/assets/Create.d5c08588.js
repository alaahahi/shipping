import{b as h,a as m,f as a,u as t,d as f,e as d,g as u,F as b,o as i,H as g,h as e,t as n,j as y,L as v,k as x}from"./app.eaa1f3f6.js";import{_ as k}from"./AuthenticatedLayout.03465553.js";import{_ as c,a as p}from"./TextInput.d04560d5.js";const w={class:"font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200"},V={class:"py-12"},$={class:"max-w-7xl mx-auto sm:px-6 lg:px-8"},S={class:"bg-white overflow-hidden shadow-sm sm:rounded-lg"},N={class:"p-6 dark:bg-gray-900"},B={className:"flex items-center justify-between mb-6"},C=["onSubmit"],F={class:"flex flex-col"},D={class:"mb-4"},H={key:0,class:"text-red-600"},j={class:"mb-4"},A={class:"mt-4"},L={type:"submit",class:"px-6 py-2 font-bold text-white bg-rose-500 rounded"},M={__name:"Create",props:{usersType:Array,coordinators:Array,userSeles:String,userHospital:String,userDoctor:String},setup(T){const o=h({name:"",phone:""}),_=()=>{o.post(route("clientsStore"))};return(s,r)=>(i(),m(b,null,[a(t(g),{title:"Dashboard"}),s.$page.props.auth.user.type_id==1?(i(),f(k,{key:0},{header:d(()=>[e("h2",w,n(s.$t("addCustomer")),1)]),default:d(()=>[e("div",V,[e("div",$,[e("div",S,[e("div",N,[e("div",B,[a(t(v),{className:"px-6 py-2 text-white bg-gray-500 rounded-md focus:outline-none",href:s.route("clients")},{default:d(()=>[y(n(s.$t("return")),1)]),_:1},8,["href"])]),e("form",{name:"createForm",onSubmit:x(_,["prevent"])},[e("div",F,[e("div",D,[a(c,{for:"name",value:s.$t("name")},null,8,["value"]),a(p,{id:"name",type:"text",class:"mt-1 block w-full",modelValue:t(o).name,"onUpdate:modelValue":r[0]||(r[0]=l=>t(o).name=l),autofocus:""},null,8,["modelValue"]),t(o).errors.name?(i(),m("span",H,n(s.$t("nameRequired")),1)):u("",!0)]),e("div",j,[a(c,{for:"phone",value:s.$t("phone")},null,8,["value"]),a(p,{id:"phone",type:"number",class:"mt-1 block w-full",modelValue:t(o).phone,"onUpdate:modelValue":r[1]||(r[1]=l=>t(o).phone=l)},null,8,["modelValue"])])]),e("div",A,[e("button",L,n(s.$t("save")),1)])],40,C)])])])])]),_:1})):u("",!0)],64))}};export{M as default};