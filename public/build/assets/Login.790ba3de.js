import{c as b,w as k,v as h,u as s,o as m,a as _,i as v,b as w,d as x,e as u,f as t,H as y,t as i,g as V,h as r,j as $,n as B,k as C}from"./app.9da8c4a5.js";import{_ as N,a as c,b as S}from"./PrimaryButton.9dbc46b3.js";import{_ as p,a as f}from"./TextInput.316e5ad8.js";const U=["value"],j={__name:"Checkbox",props:{checked:{type:[Array,Boolean],default:!1},value:{default:null}},emits:["update:checked"],setup(n,{emit:e}){const d=n,o=b({get(){return d.checked},set(a){e("update:checked",a)}});return(a,l)=>k((m(),_("input",{type:"checkbox",value:n.value,"onUpdate:modelValue":l[0]||(l[0]=g=>v(o)?o.value=g:null),class:"rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"},null,8,U)),[[h,s(o)]])}},q={key:0,class:"mb-4 font-medium text-sm text-green-600"},D=["onSubmit"],F={class:"mt-4"},H={class:"block mt-4"},L={class:"dark:text-gray-200 flex items-center"},M={class:"ml-2 text-sm text-gray-600"},R={class:"flex items-center justify-end mt-4"},P={__name:"Login",props:{canResetPassword:Boolean,status:String},setup(n){const e=w({email:"",password:"",remember:!1}),d=()=>{e.post(route("login"),{onFinish:()=>e.reset("password")})};return(o,a)=>(m(),x(N,null,{default:u(()=>[t(s(y),{title:"Log in"}),n.status?(m(),_("div",q,i(n.status),1)):V("",!0),r("form",{onSubmit:C(d,["prevent"])},[r("div",null,[t(p,{for:"email",value:o.$t("username")},null,8,["value"]),t(f,{id:"email",type:"text",class:"mt-1 block w-full",modelValue:s(e).email,"onUpdate:modelValue":a[0]||(a[0]=l=>s(e).email=l),required:"",autofocus:"",autocomplete:"username"},null,8,["modelValue"]),t(c,{class:"mt-2",message:s(e).errors.email},null,8,["message"])]),r("div",F,[t(p,{for:"password",value:o.$t("password")},null,8,["value"]),t(f,{id:"password",type:"password",class:"mt-1 block w-full",modelValue:s(e).password,"onUpdate:modelValue":a[1]||(a[1]=l=>s(e).password=l),required:"",autocomplete:"current-password"},null,8,["modelValue"]),t(c,{class:"mt-2",message:s(e).errors.password},null,8,["message"])]),r("div",H,[r("label",L,[t(j,{name:"remember",checked:s(e).remember,"onUpdate:checked":a[2]||(a[2]=l=>s(e).remember=l)},null,8,["checked"]),r("span",M,i(o.$t("remember")),1)])]),r("div",R,[t(S,{class:B(["ml-4",{"opacity-25":s(e).processing}]),disabled:s(e).processing},{default:u(()=>[$(i(o.$t("login")),1)]),_:1},8,["class","disabled"])])],40,D)]),_:1}))}};export{P as default};
