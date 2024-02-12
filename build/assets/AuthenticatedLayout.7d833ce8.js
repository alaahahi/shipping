import{q as z,s as E,x as w,r as M,o as a,a as l,b as t,d as _,e as L,y as B,j as n,w as s,n as y,k,T as j,c as m,L as x,h as i,z as N,i as r,t as u}from"./app.215e2a43.js";const S=(c,o)=>{const p=c.__vccOpts||c;for(const[h,e]of o)p[h]=e;return p},V={class:"relative"},D={__name:"Dropdown",props:{align:{default:"right"},width:{default:"48"},contentClasses:{default:()=>["py-1","bg-white "]}},setup(c){const o=c,p=g=>{d.value&&g.key==="Escape"&&(d.value=!1)};z(()=>document.addEventListener("keydown",p)),E(()=>document.removeEventListener("keydown",p));const h=w(()=>({48:"w-48"})[o.width.toString()]),e=w(()=>o.align==="left"?"origin-top-left left-0":o.align==="right"?"origin-top-right right-0":"origin-top"),d=M(!1);return(g,b)=>(a(),l("div",V,[t("div",{onClick:b[0]||(b[0]=C=>d.value=!d.value)},[_(g.$slots,"trigger")]),L(t("div",{class:"fixed inset-0 z-40",onClick:b[1]||(b[1]=C=>d.value=!1)},null,512),[[B,d.value]]),n(j,{"enter-active-class":"transition ease-out duration-200","enter-from-class":"transform opacity-0 scale-95","enter-to-class":"transform opacity-100 scale-100","leave-active-class":"transition ease-in duration-75","leave-from-class":"transform opacity-100 scale-100","leave-to-class":"transform opacity-0 scale-95"},{default:s(()=>[L(t("div",{class:y(["absolute z-50 mt-2 rounded-md shadow-lg",[k(h),k(e)]]),style:{display:"none"},onClick:b[2]||(b[2]=C=>d.value=!1)},[t("div",{class:y(["rounded-md ring-1 ring-black ring-opacity-5",c.contentClasses])},[_(g.$slots,"content")],2)],2),[[B,d.value]])]),_:3})]))}},$={__name:"DropdownLink",setup(c){return(o,p)=>(a(),m(k(x),{class:"block w-full px-4 py-2 border dark:border-gray-900 text-left text-sm leading-5 text-gray-700 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"},{default:s(()=>[_(o.$slots,"default")]),_:3}))}},v={__name:"NavLink",props:["href","active"],setup(c){const o=c,p=w(()=>o.active?"inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 text-sm font-medium leading-5 text-gray-900 dark:text-gray-500 focus:outline-none focus:border-indigo-700 transition  duration-150 ease-in-out":"inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out");return(h,e)=>(a(),m(k(x),{href:c.href,class:y(k(p))},{default:s(()=>[_(h.$slots,"default")]),_:3},8,["href","class"]))}},f={__name:"ResponsiveNavLink",props:["href","active"],setup(c){const o=c,p=w(()=>o.active?"block pl-3 pr-4 py-2 border-l-4 border-indigo-400 text-base font-medium text-indigo-700 bg-indigo-50 focus:outline-none focus:text-indigo-800 focus:bg-indigo-100 focus:border-indigo-700 transition duration-150 ease-in-out":"block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out");return(h,e)=>(a(),m(k(x),{href:c.href,class:y(k(p))},{default:s(()=>[_(h.$slots,"default")]),_:3},8,["href","class"]))}},A={data(){return{darkMode:!1}},created(){this.darkMode=localStorage.getItem("darkMode")==="true",this.darkMode&&document.documentElement.classList.add("dark")},methods:{toggleDarkMode(){this.darkMode=!this.darkMode,this.darkMode?document.documentElement.classList.add("dark"):document.documentElement.classList.remove("dark"),localStorage.setItem("darkMode",this.darkMode)}},computed:{iconClass(){return this.darkMode?"fas fa-sun":"fas fa-moon"}}},I={key:0,xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24","stroke-width":"1.5",stroke:"currentColor",class:"w-6 h-6"},T=t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"},null,-1),O=[T],q={key:1,xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24","stroke-width":"1.5",stroke:"currentColor",class:"w-6 h-6"},H=t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"},null,-1),R=[H];function U(c,o,p,h,e,d){return a(),l("button",{onClick:o[0]||(o[0]=(...g)=>d.toggleDarkMode&&d.toggleDarkMode(...g)),class:"px-2 py-1 rounded-full focus:outline-none dark:text-gray-400"},[e.darkMode?i("",!0):(a(),l("svg",I,O)),e.darkMode?(a(),l("svg",q,R)):i("",!0)])}const F=S(A,[["render",U]]);const G={class:"min-h-screen bg-gray-100 dark:bg-gray-800"},J={class:"bg-white border-gray-100 dark:bg-gray-900 print:hidden"},K={class:"max-w-8xl mx-auto px-4 sm:px-2"},P={class:"flex justify-between h-16"},Q={class:"flex"},W={class:"hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"},X={key:1,class:"hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"},Y={key:2,class:"hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"},Z={key:3,class:"hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"},ee={key:4,class:"hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"},te={key:5,class:"hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"},se={key:6,class:"hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"},re={key:7,class:"hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"},ae={key:8,class:"hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"},oe={key:9,class:"hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"},ne={class:"hidden sm:flex sm:items-center sm:ml-6"},ie={class:"ml-3 relative"},de={class:"inline-flex rounded-md"},ue={type:"button",class:"dark:bg-gray-800 dark:text-gray-300 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"},le=t("svg",{class:"ml-2 -mr-0.5 h-4 w-4",xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 20 20",fill:"currentColor"},[t("path",{"fill-rule":"evenodd",d:"M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z","clip-rule":"evenodd"})],-1),ce={class:"ml-3 relative"},pe={class:"inline-flex rounded-md"},he={type:"button",class:"dark:bg-gray-800 dark:text-gray-300 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"},fe=t("svg",{class:"ml-2 -mr-0.5 h-4 w-4",xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 20 20",fill:"currentColor"},[t("path",{"fill-rule":"evenodd",d:"M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z","clip-rule":"evenodd"})],-1),me={class:"-mr-2 flex items-center sm:hidden"},ge={class:"h-6 w-6",stroke:"currentColor",fill:"none",viewBox:"0 0 24 24"},ve={class:"pt-2 pb-3 space-y-1"},ye={class:"pt-4 pb-1 border-t border-gray-200"},_e={class:"px-4"},ke={class:"font-medium text-base text-gray-800"},be={class:"font-medium text-sm text-gray-500"},$e={class:"mt-3 space-y-1"},we={key:0,class:"bg-white shadow dark:bg-gray-900 dark:text-gray-200"},Me={class:"max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8"},xe={class:"dark:bg-gray-800"},Le={__name:"AuthenticatedLayout",setup(c){const o=M(!1),p=N();M("en");const h=e=>{p.locale.value=e,localStorage.setItem("lang",e)};return(e,d)=>(a(),l("div",null,[t("div",G,[t("nav",J,[t("div",K,[t("div",P,[t("div",Q,[i("",!0),t("div",W,[n(v,{href:e.route("dashboard"),active:e.route().current("dashboard")},{default:s(()=>[r(u(e.$t("home")),1)]),_:1},8,["href","active"])]),e.$page.props.auth.user.type_id==1||e.$page.props.auth.user.type_id==6?(a(),l("div",X,[n(v,{href:e.route("purchases"),active:e.route().current("purchases")},{default:s(()=>[r(u(e.$t("purchases")),1)]),_:1},8,["href","active"])])):i("",!0),e.$page.props.auth.user.type_id==1||e.$page.props.auth.user.type_id==6?(a(),l("div",Y,[n(v,{href:e.route("sales"),active:e.route().current("sales")},{default:s(()=>[r(u(e.$t("sales")),1)]),_:1},8,["href","active"])])):i("",!0),e.$page.props.auth.user.type_id==1||e.$page.props.auth.user.type_id==6?(a(),l("div",Z,[n(v,{href:e.route("clients"),active:e.route().current("clients")},{default:s(()=>[r(u(e.$t("clients")),1)]),_:1},8,["href","active"])])):i("",!0),e.$page.props.auth.user.type_id==1||e.$page.props.auth.user.type_id==6?(a(),l("div",ee,[n(v,{href:e.route("accounting"),active:e.route().current("accounting")},{default:s(()=>[r(" \u0627\u0644\u0645\u062D\u0627\u0633\u0628\u0629 ")]),_:1},8,["href","active"])])):i("",!0),e.$page.props.auth.user.type_id==1||e.$page.props.auth.user.type_id==6?(a(),l("div",te,[n(v,{href:e.route("online_contracts"),active:e.route().current("online_contracts")},{default:s(()=>[r(u(e.$t("online_contracts")),1)]),_:1},8,["href","active"])])):i("",!0),e.$page.props.auth.user.type_id==1?(a(),l("div",se,[n(v,{href:e.route("annual_information"),active:e.route().current("annual_information")},{default:s(()=>[r(" \u0645\u0639\u0644\u0648\u0645\u0627\u062A \u0627\u0644\u0633\u0646\u0648\u064A\u0629 ")]),_:1},8,["href","active"])])):i("",!0),e.$page.props.auth.user.type_id==1||e.$page.props.auth.user.type_id==7?(a(),l("div",re,[n(v,{href:e.route("car_expenses"),active:e.route().current("car_expenses")},{default:s(()=>[r(" \u0645\u0635\u0627\u0631\u064A\u0641 \u0627\u0644\u0633\u064A\u0627\u0631\u0627\u062A ")]),_:1},8,["href","active"])])):i("",!0),e.$page.props.auth.user.type_id==8?(a(),l("div",ae,[n(v,{href:e.route("car_contract"),active:e.route().current("car_contract")},{default:s(()=>[r(" \u0639\u0642\u0648\u062F \u0627\u0644\u0628\u064A\u0639 ")]),_:1},8,["href","active"])])):i("",!0),e.$page.props.auth.user.type_id==8?(a(),l("div",oe,[n(v,{href:e.route("contract_account"),active:e.route().current("contract_account")},{default:s(()=>[r(" \u0645\u062D\u0627\u0633\u0628\u0629 \u0639\u0642\u0648\u062F ")]),_:1},8,["href","active"])])):i("",!0)]),t("div",ne,[t("div",ie,[n(D,{align:"right",width:"48"},{trigger:s(()=>[t("span",de,[t("button",ue,[r(u(e.$t("lang"))+" ",1),le])])]),content:s(()=>[n($,{onClick:d[0]||(d[0]=g=>h("ar")),as:"button"},{default:s(()=>[r(" \u0639\u0631\u0628\u064A ")]),_:1}),n($,{onClick:d[1]||(d[1]=g=>h("en")),as:"button"},{default:s(()=>[r(" \u0627\u0646\u0643\u0644\u064A\u0632\u064A ")]),_:1}),n($,{onClick:d[2]||(d[2]=g=>h("kr")),as:"button"},{default:s(()=>[r(" \u0643\u0631\u062F\u064A ")]),_:1})]),_:1})]),t("div",ce,[n(D,{align:"right",width:"48"},{trigger:s(()=>[t("span",pe,[t("button",he,[r(u(e.$page.props.auth.user.name)+" ",1),fe])])]),content:s(()=>[n($,{href:e.route("logout"),method:"post",as:"button"},{default:s(()=>[r(u(e.$t("logout")),1)]),_:1},8,["href"])]),_:1})]),n(F)]),t("div",me,[t("button",{onClick:d[3]||(d[3]=g=>o.value=!o.value),class:"inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"},[(a(),l("svg",ge,[t("path",{class:y({hidden:o.value,"inline-flex":!o.value}),"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M4 6h16M4 12h16M4 18h16"},null,2),t("path",{class:y({hidden:!o.value,"inline-flex":o.value}),"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M6 18L18 6M6 6l12 12"},null,2)]))])])])]),t("div",{class:y([{block:o.value,hidden:!o.value},"sm:hidden"])},[t("div",ve,[n(f,{href:e.route("dashboard"),active:e.route().current("dashboard")},{default:s(()=>[r(u(e.$t("dashboard")),1)]),_:1},8,["href","active"])]),t("div",ye,[t("div",_e,[t("div",ke,u(e.$page.props.auth.user.name),1),t("div",be,u(e.$page.props.auth.user.email),1)]),t("div",$e,[n(f,{href:e.route("dashboard"),active:e.route().current("dashboard")},{default:s(()=>[r(u(e.$t("home")),1)]),_:1},8,["href","active"]),e.$page.props.auth.user.type_id==1?(a(),m(f,{key:0,href:e.route("purchases"),active:e.route().current("purchases")},{default:s(()=>[r(u(e.$t("purchases")),1)]),_:1},8,["href","active"])):i("",!0),e.$page.props.auth.user.type_id==1?(a(),m(f,{key:1,href:e.route("sales"),active:e.route().current("sales")},{default:s(()=>[r(u(e.$t("sales")),1)]),_:1},8,["href","active"])):i("",!0),e.$page.props.auth.user.type_id==1||e.$page.props.auth.user.type_id==6?(a(),m(f,{key:2,href:e.route("accounting"),active:e.route().current("accounting")},{default:s(()=>[r(" \u0627\u0644\u0645\u062D\u0627\u0633\u0628\u0629 ")]),_:1},8,["href","active"])):i("",!0),e.$page.props.auth.user.type_id==1||e.$page.props.auth.user.type_id==6?(a(),m(f,{key:3,href:e.route("online_contracts"),active:e.route().current("online_contracts")},{default:s(()=>[r(u(e.$t("online_contracts")),1)]),_:1},8,["href","active"])):i("",!0),e.$page.props.auth.user.type_id==1||e.$page.props.auth.user.type_id==6?(a(),m(f,{key:4,href:e.route("annual_information"),active:e.route().current("annual_information")},{default:s(()=>[r(" \u0645\u0639\u0644\u0648\u0645\u0627\u062A \u0627\u0644\u0633\u0646\u0648\u064A\u0629 ")]),_:1},8,["href","active"])):i("",!0),e.$page.props.auth.user.type_id==1||e.$page.props.auth.user.type_id==7?(a(),m(f,{key:5,href:e.route("car_expenses"),active:e.route().current("car_expenses")},{default:s(()=>[r(" \u0645\u0635\u0627\u0631\u064A\u0641 \u0627\u0644\u0633\u064A\u0627\u0631\u0627\u062A ")]),_:1},8,["href","active"])):i("",!0),e.$page.props.auth.user.type_id==8?(a(),m(f,{key:6,href:e.route("car_contract"),active:e.route().current("car_contract")},{default:s(()=>[r(" \u0639\u0642\u0648\u062F \u0627\u0644\u0628\u064A\u0639 ")]),_:1},8,["href","active"])):i("",!0),e.$page.props.auth.user.type_id==8?(a(),m(f,{key:7,href:e.route("contract_account"),active:e.route().current("contract_account")},{default:s(()=>[r(" \u0645\u062D\u0627\u0633\u0628\u0629 \u0639\u0642\u0648\u062F ")]),_:1},8,["href","active"])):i("",!0),n(f,{href:e.route("logout"),method:"post",as:"button"},{default:s(()=>[r(u(e.$t("logout")),1)]),_:1},8,["href"])])])],2)]),e.$slots.header?(a(),l("header",we,[t("div",Me,[_(e.$slots,"header")])])):i("",!0),t("main",xe,[_(e.$slots,"default")])])]))}};export{S as _,Le as a};
