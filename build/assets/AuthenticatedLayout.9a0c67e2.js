import{p as E,q as j,s as $,r as x,o as n,a as f,b as t,d as k,e as L,x as B,j as i,w as s,n as y,k as _,T as z,c as g,L as M,h as u,y as N,i as r,t as d}from"./app.67cca86e.js";const S=(l,o)=>{const c=l.__vccOpts||l;for(const[h,e]of o)c[h]=e;return c},V={class:"relative"},D={__name:"Dropdown",props:{align:{default:"right"},width:{default:"48"},contentClasses:{default:()=>["py-1","bg-white "]}},setup(l){const o=l,c=p=>{a.value&&p.key==="Escape"&&(a.value=!1)};E(()=>document.addEventListener("keydown",c)),j(()=>document.removeEventListener("keydown",c));const h=$(()=>({48:"w-48"})[o.width.toString()]),e=$(()=>o.align==="left"?"origin-top-left left-0":o.align==="right"?"origin-top-right right-0":"origin-top"),a=x(!1);return(p,b)=>(n(),f("div",V,[t("div",{onClick:b[0]||(b[0]=C=>a.value=!a.value)},[k(p.$slots,"trigger")]),L(t("div",{class:"fixed inset-0 z-40",onClick:b[1]||(b[1]=C=>a.value=!1)},null,512),[[B,a.value]]),i(z,{"enter-active-class":"transition ease-out duration-200","enter-from-class":"transform opacity-0 scale-95","enter-to-class":"transform opacity-100 scale-100","leave-active-class":"transition ease-in duration-75","leave-from-class":"transform opacity-100 scale-100","leave-to-class":"transform opacity-0 scale-95"},{default:s(()=>[L(t("div",{class:y(["absolute z-50 mt-2 rounded-md shadow-lg",[_(h),_(e)]]),style:{display:"none"},onClick:b[2]||(b[2]=C=>a.value=!1)},[t("div",{class:y(["rounded-md ring-1 ring-black ring-opacity-5",l.contentClasses])},[k(p.$slots,"content")],2)],2),[[B,a.value]])]),_:3})]))}},w={__name:"DropdownLink",setup(l){return(o,c)=>(n(),g(_(M),{class:"block w-full px-4 py-2 border dark:border-gray-900 text-left text-sm leading-5 text-gray-700 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"},{default:s(()=>[k(o.$slots,"default")]),_:3}))}},v={__name:"NavLink",props:["href","active"],setup(l){const o=l,c=$(()=>o.active?"inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 text-sm font-medium leading-5 text-gray-900 dark:text-gray-500 focus:outline-none focus:border-indigo-700 transition  duration-150 ease-in-out":"inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out");return(h,e)=>(n(),g(_(M),{href:l.href,class:y(_(c))},{default:s(()=>[k(h.$slots,"default")]),_:3},8,["href","class"]))}},m={__name:"ResponsiveNavLink",props:["href","active"],setup(l){const o=l,c=$(()=>o.active?"block pl-3 pr-4 py-2 border-l-4 border-indigo-400 text-base font-medium text-indigo-700 bg-indigo-50 focus:outline-none focus:text-indigo-800 focus:bg-indigo-100 focus:border-indigo-700 transition duration-150 ease-in-out":"block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out");return(h,e)=>(n(),g(_(M),{href:l.href,class:y(_(c))},{default:s(()=>[k(h.$slots,"default")]),_:3},8,["href","class"]))}},I={data(){return{darkMode:!1}},created(){this.darkMode=localStorage.getItem("darkMode")==="true",this.darkMode&&document.documentElement.classList.add("dark")},methods:{toggleDarkMode(){this.darkMode=!this.darkMode,this.darkMode?document.documentElement.classList.add("dark"):document.documentElement.classList.remove("dark"),localStorage.setItem("darkMode",this.darkMode)}},computed:{iconClass(){return this.darkMode?"fas fa-sun":"fas fa-moon"}}},T={key:0,xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24","stroke-width":"1.5",stroke:"currentColor",class:"w-6 h-6"},A=t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"},null,-1),O=[A],q={key:1,xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24","stroke-width":"1.5",stroke:"currentColor",class:"w-6 h-6"},H=t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"},null,-1),R=[H];function U(l,o,c,h,e,a){return n(),f("button",{onClick:o[0]||(o[0]=(...p)=>a.toggleDarkMode&&a.toggleDarkMode(...p)),class:"px-2 py-1 rounded-full focus:outline-none dark:text-gray-400"},[e.darkMode?u("",!0):(n(),f("svg",T,O)),e.darkMode?(n(),f("svg",q,R)):u("",!0)])}const F=S(I,[["render",U]]),G={class:"min-h-screen bg-gray-100 dark:bg-gray-800"},J={class:"bg-white border-gray-100 dark:bg-gray-900 print:hidden"},K={class:"max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"},P={class:"flex justify-between h-16"},Q={class:"flex"},W={class:"hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"},X={class:"hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"},Y={key:1,class:"hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"},Z={key:2,class:"hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"},ee={key:3,class:"hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"},te={key:4,class:"hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"},se={key:5,class:"hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"},oe={class:"hidden sm:flex sm:items-center sm:ml-6"},re={class:"ml-3 relative"},ae={class:"inline-flex rounded-md"},ne={type:"button",class:"dark:bg-gray-800 dark:text-gray-300 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"},ie=t("svg",{class:"ml-2 -mr-0.5 h-4 w-4",xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 20 20",fill:"currentColor"},[t("path",{"fill-rule":"evenodd",d:"M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z","clip-rule":"evenodd"})],-1),de={class:"ml-3 relative"},le={class:"inline-flex rounded-md"},ue={type:"button",class:"dark:bg-gray-800 dark:text-gray-300 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"},ce=t("svg",{class:"ml-2 -mr-0.5 h-4 w-4",xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 20 20",fill:"currentColor"},[t("path",{"fill-rule":"evenodd",d:"M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z","clip-rule":"evenodd"})],-1),he={class:"-mr-2 flex items-center sm:hidden"},fe={class:"h-6 w-6",stroke:"currentColor",fill:"none",viewBox:"0 0 24 24"},pe={class:"pt-2 pb-3 space-y-1"},me={class:"pt-4 pb-1 border-t border-gray-200"},ge={class:"px-4"},ve={class:"font-medium text-base text-gray-800"},ye={class:"font-medium text-sm text-gray-500"},ke={class:"mt-3 space-y-1"},_e={key:0,class:"bg-white shadow dark:bg-gray-900 dark:text-gray-200"},be={class:"max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8"},we={class:"dark:bg-gray-800"},xe={__name:"AuthenticatedLayout",setup(l){const o=x(!1),c=N();x("en");const h=e=>{c.locale.value=e,localStorage.setItem("lang",e)};return(e,a)=>(n(),f("div",null,[t("div",G,[t("nav",J,[t("div",K,[t("div",P,[t("div",Q,[u("",!0),t("div",W,[i(v,{href:e.route("dashboard"),active:e.route().current("dashboard")},{default:s(()=>[r(d(e.$t("home")),1)]),_:1},8,["href","active"])]),t("div",X,[i(v,{href:e.route("purchases"),active:e.route().current("purchases")},{default:s(()=>[r(d(e.$t("purchases")),1)]),_:1},8,["href","active"])]),e.$page.props.auth.user.type_id==1?(n(),f("div",Y,[i(v,{href:e.route("sales"),active:e.route().current("sales")},{default:s(()=>[r(d(e.$t("sales")),1)]),_:1},8,["href","active"])])):u("",!0),e.$page.props.auth.user.type_id==1?(n(),f("div",Z,[i(v,{href:e.route("clients"),active:e.route().current("clients")},{default:s(()=>[r(d(e.$t("clients")),1)]),_:1},8,["href","active"])])):u("",!0),e.$page.props.auth.user.type_id==1?(n(),f("div",ee,[i(v,{href:e.route("accounting"),active:e.route().current("accounting")},{default:s(()=>[r(" \u0627\u0644\u0645\u062D\u0627\u0633\u0628\u0629 ")]),_:1},8,["href","active"])])):u("",!0),e.$page.props.auth.user.type_id==1?(n(),f("div",te,[i(v,{href:e.route("online_contracts"),active:e.route().current("online_contracts")},{default:s(()=>[r(d(e.$t("online_contracts")),1)]),_:1},8,["href","active"])])):u("",!0),e.$page.props.auth.user.type_id==1?(n(),f("div",se,[i(v,{href:e.route("annual_information"),active:e.route().current("annual_information")},{default:s(()=>[r(" \u0645\u0639\u0644\u0648\u0645\u0627\u062A \u0627\u0644\u0633\u0646\u0648\u064A\u0629 ")]),_:1},8,["href","active"])])):u("",!0)]),t("div",oe,[t("div",re,[i(D,{align:"right",width:"48"},{trigger:s(()=>[t("span",ae,[t("button",ne,[r(d(e.$t("lang"))+" ",1),ie])])]),content:s(()=>[i(w,{onClick:a[0]||(a[0]=p=>h("ar")),as:"button"},{default:s(()=>[r(" \u0639\u0631\u0628\u064A ")]),_:1}),i(w,{onClick:a[1]||(a[1]=p=>h("en")),as:"button"},{default:s(()=>[r(" \u0627\u0646\u0643\u0644\u064A\u0632\u064A ")]),_:1}),i(w,{onClick:a[2]||(a[2]=p=>h("kr")),as:"button"},{default:s(()=>[r(" \u0643\u0631\u062F\u064A ")]),_:1})]),_:1})]),t("div",de,[i(D,{align:"right",width:"48"},{trigger:s(()=>[t("span",le,[t("button",ue,[r(d(e.$page.props.auth.user.name)+" ",1),ce])])]),content:s(()=>[i(w,{href:e.route("logout"),method:"post",as:"button"},{default:s(()=>[r(d(e.$t("logout")),1)]),_:1},8,["href"])]),_:1})]),i(F)]),t("div",he,[t("button",{onClick:a[3]||(a[3]=p=>o.value=!o.value),class:"inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"},[(n(),f("svg",fe,[t("path",{class:y({hidden:o.value,"inline-flex":!o.value}),"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M4 6h16M4 12h16M4 18h16"},null,2),t("path",{class:y({hidden:!o.value,"inline-flex":o.value}),"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M6 18L18 6M6 6l12 12"},null,2)]))])])])]),t("div",{class:y([{block:o.value,hidden:!o.value},"sm:hidden"])},[t("div",pe,[i(m,{href:e.route("dashboard"),active:e.route().current("dashboard")},{default:s(()=>[r(d(e.$t("dashboard")),1)]),_:1},8,["href","active"])]),t("div",me,[t("div",ge,[t("div",ve,d(e.$page.props.auth.user.name),1),t("div",ye,d(e.$page.props.auth.user.email),1)]),t("div",ke,[i(m,{href:e.route("dashboard"),active:e.route().current("dashboard")},{default:s(()=>[r(d(e.$t("home")),1)]),_:1},8,["href","active"]),e.$page.props.auth.user.type_id==1?(n(),g(m,{key:0,href:e.route("purchases"),active:e.route().current("purchases")},{default:s(()=>[r(d(e.$t("purchases")),1)]),_:1},8,["href","active"])):u("",!0),e.$page.props.auth.user.type_id==1?(n(),g(m,{key:1,href:e.route("sales"),active:e.route().current("sales")},{default:s(()=>[r(d(e.$t("sales")),1)]),_:1},8,["href","active"])):u("",!0),e.$page.props.auth.user.type_id==1?(n(),g(m,{key:2,href:e.route("accounting"),active:e.route().current("accounting")},{default:s(()=>[r(" \u0627\u0644\u0645\u062D\u0627\u0633\u0628\u0629 ")]),_:1},8,["href","active"])):u("",!0),e.$page.props.auth.user.type_id==1?(n(),g(m,{key:3,href:e.route("online_contracts"),active:e.route().current("online_contracts")},{default:s(()=>[r(d(e.$t("online_contracts")),1)]),_:1},8,["href","active"])):u("",!0),e.$page.props.auth.user.type_id==1?(n(),g(m,{key:4,href:e.route("annual_information"),active:e.route().current("annual_information")},{default:s(()=>[r(" \u0645\u0639\u0644\u0648\u0645\u0627\u062A \u0627\u0644\u0633\u0646\u0648\u064A\u0629 ")]),_:1},8,["href","active"])):u("",!0),i(m,{href:e.route("logout"),method:"post",as:"button"},{default:s(()=>[r(d(e.$t("logout")),1)]),_:1},8,["href"])])])],2)]),e.$slots.header?(n(),f("header",_e,[t("div",be,[k(e.$slots,"header")])])):u("",!0),t("main",we,[k(e.$slots,"default")])])]))}};export{xe as _,S as a};
