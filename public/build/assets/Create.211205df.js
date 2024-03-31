import{u as y,a as d,g as a,j as s,c as h,w as u,i,F as c,o as r,H as x,b as e,f as g,L as v,l as w,e as k,z as N,h as V,t as T}from"./app.c70f1507.js";import{_ as U}from"./AuthenticatedLayout.1f32cf51.js";import{_ as n}from"./InputLabel.05f00cdd.js";import{_ as m}from"./TextInput.c63d8366.js";const B=e("h2",{class:"font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200"}," \u0625\u0646\u0634\u0627\u0621 \u0645\u0633\u062A\u062E\u062F\u0645 ",-1),F={class:"py-12"},S={class:"max-w-7xl mx-auto sm:px-6 lg:px-8"},$={class:"bg-white overflow-hidden shadow-sm sm:rounded-lg"},C={class:"p-6 dark:bg-gray-900"},D={className:"flex items-center justify-between mb-6"},L=["onSubmit"],j={className:"flex flex-col"},H={className:"mb-4"},M={key:0,className:"text-red-600"},z={className:"mb-4"},A={key:0,className:"text-red-600"},E={className:"mb-4"},q={key:0,className:"text-red-600"},G={className:"mb-4"},I=e("option",{selected:"",disabled:""},"\u0635\u0644\u0627\u062D\u064A\u0627\u062A \u0627\u0644\u0645\u0633\u062A\u062E\u062F\u0645 \u0627\u0644\u0645\u062A\u0627\u062D\u0629",-1),J=["value"],K={className:"mb-4"},O=e("div",{className:"mt-4"},[e("button",{type:"submit",className:"px-6 py-2 font-bold text-white bg-rose-500 rounded"}," \u062D\u0641\u0638 ")],-1),X={__name:"Create",props:{usersType:Array},setup(_){const t=y({name:"",email:"",password:"",userType:"",parent_id:"",phone:""}),f=()=>{t.post(route("users.store"))};return(p,l)=>(r(),d(c,null,[a(s(x),{title:"Dashboard"}),p.$page.props.auth.user.type_id==1?(r(),h(U,{key:0},{header:u(()=>[B]),default:u(()=>[e("div",F,[e("div",S,[e("div",$,[e("div",C,[e("div",D,[a(s(v),{className:"px-6 py-2 text-white bg-gray-500 rounded-md focus:outline-none",href:p.route("users.index")},{default:u(()=>[g(" \u0627\u0644\u0639\u0648\u062F\u0629 ")]),_:1},8,["href"])]),e("form",{name:"createForm",onSubmit:w(f,["prevent"])},[e("div",j,[e("div",H,[a(n,{for:"name",value:"\u0627\u0644\u0623\u0633\u0645"}),a(m,{id:"name",type:"text",class:"mt-1 block w-full",modelValue:s(t).name,"onUpdate:modelValue":l[0]||(l[0]=o=>s(t).name=o),autofocus:""},null,8,["modelValue"]),s(t).errors.name?(r(),d("span",M," \u0627\u0644\u0623\u0633\u0645 \u062D\u0642\u0644 \u0645\u0637\u0644\u0648\u0628 ")):i("",!0)]),e("div",z,[a(n,{for:"email",value:"\u0627\u0633\u0645 \u0627\u0644\u0645\u0633\u062A\u062E\u062F\u0645"}),a(m,{id:"email",type:"text",class:"mt-1 block w-full",modelValue:s(t).email,"onUpdate:modelValue":l[1]||(l[1]=o=>s(t).email=o)},null,8,["modelValue"]),s(t).errors.email?(r(),d("span",A," \u0627\u0633\u0645 \u0627\u0644\u0645\u0633\u062A\u062E\u062F\u0645 \u0647\u0630\u0627 \u063A\u064A\u0631 \u0645\u062A\u0627\u062D ")):i("",!0)]),e("div",E,[a(n,{for:"password",value:"\u0643\u0644\u0645\u0629 \u0627\u0644\u0645\u0631\u0648\u0631"}),a(m,{id:"password",type:"text",class:"mt-1 block w-full",modelValue:s(t).password,"onUpdate:modelValue":l[2]||(l[2]=o=>s(t).password=o)},null,8,["modelValue"]),s(t).errors.password?(r(),d("span",q," \u0643\u0644\u0645\u0629 \u0627\u0644\u0645\u0631\u0648\u0631 \u062D\u0642\u0644 \u0645\u0637\u0644\u0648\u0628 ")):i("",!0)]),e("div",G,[a(n,{for:"userType",value:"\u0635\u0644\u0627\u062C\u064A\u0627\u062A \u0627\u0644\u0645\u0633\u062A\u062E\u062F\u0645"}),k(e("select",{"onUpdate:modelValue":l[3]||(l[3]=o=>s(t).userType=o),id:"userType",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"},[I,(r(!0),d(c,null,V(_.usersType,(o,b)=>(r(),d("option",{key:b,value:o.id},T(o.name),9,J))),128))],512),[[N,s(t).userType]])]),e("div",K,[a(n,{for:"phone",value:"\u0631\u0642\u0645 \u0627\u0644\u0647\u0627\u062A\u0641"}),a(m,{id:"phone",type:"number",class:"mt-1 block w-full",modelValue:s(t).phone,"onUpdate:modelValue":l[4]||(l[4]=o=>s(t).phone=o)},null,8,["modelValue"])])]),O],40,L)])])])])]),_:1})):i("",!0)],64))}};export{X as default};
