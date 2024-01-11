import{l as _,a as d,j as o,k as s,c as f,w as u,h as m,F as h,o as r,H as x,b as e,i as b,L as y,p as w}from"./app.30635ba4.js";import{a as g}from"./AuthenticatedLayout.a69d75dd.js";import{_ as n,a as i}from"./TextInput.d94634bf.js";const v=e("h2",{class:"font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200"}," \u0625\u0646\u0634\u0627\u0621 \u0645\u0633\u062A\u062E\u062F\u0645 ",-1),N={class:"py-12"},V={class:"max-w-7xl mx-auto sm:px-6 lg:px-8"},k={class:"bg-white overflow-hidden shadow-sm sm:rounded-lg"},S={class:"p-6 dark:bg-gray-900"},B={className:"flex items-center justify-between mb-6"},F=["onSubmit"],U={className:"flex flex-col"},$={className:"mb-4"},C={key:0,className:"text-red-600"},H={className:"mb-4"},T={key:0,className:"text-red-600"},j={className:"mb-4"},A={key:0,className:"text-red-600"},D={className:"mb-4"},L=e("div",{className:"mt-4"},[e("button",{type:"submit",className:"px-6 py-2 font-bold text-white bg-rose-500 rounded"}," \u062D\u0641\u0638 ")],-1),G={__name:"Create",props:{usersType:Array,coordinators:Array,userSeles:String,userHospital:String,userDoctor:String},setup(E){const t=_({name:"",email:"",password:"",userType:"",parent_id:"",phone:""}),c=()=>{t.post(route("users.store"))};return(p,a)=>(r(),d(h,null,[o(s(x),{title:"Dashboard"}),p.$page.props.auth.user.type_id==1?(r(),f(g,{key:0},{header:u(()=>[v]),default:u(()=>[e("div",N,[e("div",V,[e("div",k,[e("div",S,[e("div",B,[o(s(y),{className:"px-6 py-2 text-white bg-gray-500 rounded-md focus:outline-none",href:p.route("users.index")},{default:u(()=>[b(" \u0627\u0644\u0639\u0648\u062F\u0629 ")]),_:1},8,["href"])]),e("form",{name:"createForm",onSubmit:w(c,["prevent"])},[e("div",U,[e("div",$,[o(n,{for:"name",value:"\u0627\u0644\u0623\u0633\u0645"}),o(i,{id:"name",type:"text",class:"mt-1 block w-full",modelValue:s(t).name,"onUpdate:modelValue":a[0]||(a[0]=l=>s(t).name=l),autofocus:""},null,8,["modelValue"]),s(t).errors.name?(r(),d("span",C," \u0627\u0644\u0623\u0633\u0645 \u062D\u0642\u0644 \u0645\u0637\u0644\u0648\u0628 ")):m("",!0)]),e("div",H,[o(n,{for:"email",value:"\u0627\u0633\u0645 \u0627\u0644\u0645\u0633\u062A\u062E\u062F\u0645"}),o(i,{id:"email",type:"text",class:"mt-1 block w-full",modelValue:s(t).email,"onUpdate:modelValue":a[1]||(a[1]=l=>s(t).email=l)},null,8,["modelValue"]),s(t).errors.email?(r(),d("span",T," \u0627\u0633\u0645 \u0627\u0644\u0645\u0633\u062A\u062E\u062F\u0645 \u0647\u0630\u0627 \u063A\u064A\u0631 \u0645\u062A\u0627\u062D ")):m("",!0)]),e("div",j,[o(n,{for:"password",value:"\u0643\u0644\u0645\u0629 \u0627\u0644\u0645\u0631\u0648\u0631"}),o(i,{id:"password",type:"text",class:"mt-1 block w-full",modelValue:s(t).password,"onUpdate:modelValue":a[2]||(a[2]=l=>s(t).password=l)},null,8,["modelValue"]),s(t).errors.password?(r(),d("span",A," \u0643\u0644\u0645\u0629 \u0627\u0644\u0645\u0631\u0648\u0631 \u062D\u0642\u0644 \u0645\u0637\u0644\u0648\u0628 ")):m("",!0)]),e("div",D,[o(n,{for:"phone",value:"\u0631\u0642\u0645 \u0627\u0644\u0647\u0627\u062A\u0641"}),o(i,{id:"phone",type:"number",class:"mt-1 block w-full",modelValue:s(t).phone,"onUpdate:modelValue":a[3]||(a[3]=l=>s(t).phone=l)},null,8,["modelValue"])])]),L],40,F)])])])])]),_:1})):m("",!0)],64))}};export{G as default};
