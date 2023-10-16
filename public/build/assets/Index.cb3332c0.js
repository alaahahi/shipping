import{r,o as m,c as Z,w as y,a as h,b as e,d as ee,e as x,v as $,f as A,T as te,F as T,g as se,t as d,h as me,u as pe,i as l,j as i,H as he,k as M,l as be,n as ve}from"./app.9340c02c.js";import{_ as _e}from"./AuthenticatedLayout.59d450dd.js";/* empty css                                              */import{_ as fe,a as ye}from"./ModalAddGenExpenses.9dc279ce.js";import{_ as b,a as _}from"./TextInput.717dcabe.js";import{a as S}from"./index.d710d184.js";import{t as xe}from"./trash.fb5b01a5.js";/* empty css                                                            */import"./print.14425ae2.js";const we={key:0,class:"modal-mask"},ke={class:"modal-wrapper"},$e={class:"modal-container"},Ce={class:"modal-header"},Ne={class:"modal-body"},De=e("h2",{class:"text-center pb-5"}," \u0648\u0635\u0644 \u0642\u0628\u0636 ",-1),Me={class:"grid grid-cols-2 md:grid-cols-2 lg:grid-cols-2 gap-3 lg:gap-3"},Se={className:"mb-4 mx-5"},Ae=e("label",{for:"card"},"\u0627\u0644\u0645\u0628\u0644\u063A \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631",-1),je={className:"mb-4 mx-5"},Be=e("label",{for:"card"},"\u0627\u0644\u0645\u0628\u0644\u063A \u0628\u0627\u0644\u062F\u064A\u0646\u0627\u0631",-1),Ve={className:"mb-4 mx-5"},Ee=e("label",{for:"card"},"\u0627\u0644\u062A\u0627\u0631\u064A\u062E",-1),ze={className:"mb-4 mx-5"},Te=e("label",{for:"card"},"\u0645\u0644\u0627\u062D\u0638\u0629",-1),Ue={class:"modal-footer my-2"},Ge={class:"flex flex-row"},Ie={class:"basis-1/2 px-4"},Fe={class:"basis-1/2 px-4"},Oe=["disabled"],Le={__name:"ModalAddSales",props:{show:Boolean,data:Array,accounts:Array},setup(v){const n=r({user:{percentage:0},date:p(),card:0,amount:0,box:0,hospital:0,doctor:0});function p(){const g=new Date,o=g.getFullYear(),u=String(g.getMonth()+1).padStart(2,"0"),c=String(g.getDate()).padStart(2,"0");return`${o}-${u}-${c}`}const j=()=>{n.value={user:{percentage:0},date:p(),card:0,amount:0,box:0,hospital:0,doctor:0}};return(g,o)=>(m(),Z(te,{name:"modal"},{default:y(()=>[v.show?(m(),h("div",we,[e("div",ke,[e("div",$e,[e("div",Ce,[ee(g.$slots,"header")]),e("div",Ne,[De,e("div",Me,[e("div",Se,[Ae,x(e("input",{id:"card",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":o[0]||(o[0]=u=>n.value.amountDollar=u)},null,512),[[$,n.value.amountDollar]])]),e("div",je,[Be,x(e("input",{id:"card",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":o[1]||(o[1]=u=>n.value.amountDinar=u)},null,512),[[$,n.value.amountDinar]])]),e("div",Ve,[Ee,x(e("input",{id:"card",type:"date",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":o[2]||(o[2]=u=>n.value.date=u)},null,512),[[$,n.value.date]])]),e("div",ze,[Te,x(e("input",{id:"card",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":o[3]||(o[3]=u=>n.value.amountNote=u)},null,512),[[$,n.value.amountNote]])])])]),e("div",Ue,[e("div",Ge,[e("div",Ie,[e("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:o[4]||(o[4]=u=>{g.$emit("close")})},"\u062A\u0631\u0627\u062C\u0639")]),e("div",Fe,[e("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:o[5]||(o[5]=u=>{g.$emit("a",n.value),j()}),disabled:!n.value.amountDollar&&!n.value.amountDinar},"\u0646\u0639\u0645",8,Oe)])])])])])])):A("",!0)]),_:3}))}};const Re={key:0,class:"modal-mask"},Ye={class:"modal-wrapper"},He={class:"modal-container"},qe={class:"modal-header"},Je={class:"modal-body"},Ke=e("h2",{class:"text-center pb-5"}," \u0625\u0636\u0627\u0641\u0629 \u0633\u0644\u0641\u0629 \u0644\u0644\u0645\u0646\u062F\u0648\u0628 ",-1),Pe={class:"grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 gap-3 lg:gap-3"},Qe={className:"mb-4 mx-5"},We=e("label",{for:"card"},"\u0627\u0644\u062A\u0627\u0631\u064A\u062E",-1),Xe={className:"mb-4 mx-5"},Ze=e("label",{for:"user_id"},"\u0627\u0644\u0645\u0646\u062F\u0648\u0628",-1),et=e("option",{selected:"",disabled:""},"\u062A\u062D\u062F\u064A\u062F \u0627\u0644\u0645\u0646\u062F\u0648\u0628",-1),tt=["value"],st={className:"mb-4 mx-5"},ot=e("label",{for:"balance"},"\u0627\u0644\u0631\u0635\u064A\u062F \u0627\u0644\u062D\u0627\u0644\u064A",-1),at=["value"],dt={class:"grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 gap-3 lg:gap-3"},lt={className:"mb-4 mx-5"},rt=e("label",{for:"percentage"},"\u0646\u0633\u0628\u0629 \u0627\u0644\u0645\u0628\u064A\u0639 \u0644\u0644\u0628\u0637\u0627\u0642\u0629",-1),nt={className:"mb-4 mx-5"},it=e("label",{for:"amount"},"\u0627\u0644\u0645\u0628\u0644\u063A",-1),ut={class:"modal-footer my-2"},ct={class:"flex flex-row"},gt={class:"basis-1/2 px-4"},mt={class:"basis-1/2 px-4"},pt=["disabled"],ht={__name:"ModalAddDebt",props:{show:Boolean,data:Array,accounts:Array},setup(v){const n=r({user:{percentage:0},date:p(),amount:0});function p(){const g=new Date,o=g.getFullYear(),u=String(g.getMonth()+1).padStart(2,"0"),c=String(g.getDate()).padStart(2,"0");return`${o}-${u}-${c}`}const j=()=>{n.value={user:{percentage:0},date:p(),amount:0}};return(g,o)=>(m(),Z(te,{name:"modal"},{default:y(()=>{var u;return[v.show?(m(),h("div",Re,[e("div",Ye,[e("div",He,[e("div",qe,[ee(g.$slots,"header")]),e("div",Je,[Ke,e("div",Pe,[e("div",Qe,[We,x(e("input",{id:"card",type:"date",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":o[0]||(o[0]=c=>n.value.date=c)},null,512),[[$,n.value.date]])]),e("div",Xe,[Ze,x(e("select",{"onUpdate:modelValue":o[1]||(o[1]=c=>n.value.user=c),id:"user_id",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"},[et,(m(!0),h(T,null,se(v.data,(c,C)=>(m(),h("option",{key:C,value:c},d(c.name),9,tt))),128))],512),[[me,n.value.user]])]),e("div",st,[ot,e("input",{id:"balance",type:"number",value:(u=n.value.user.wallet)==null?void 0:u.balance,class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"},null,8,at)])]),e("div",dt,[e("div",lt,[rt,x(e("input",{id:"percentage",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":o[2]||(o[2]=c=>n.value.user.percentage=c)},null,512),[[$,n.value.user.percentage]])]),e("div",nt,[it,x(e("input",{id:"amount",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":o[3]||(o[3]=c=>n.value.amount=c)},null,512),[[$,n.value.amount]])])])]),e("div",ut,[e("div",ct,[e("div",gt,[e("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:o[4]||(o[4]=c=>{g.$emit("close")})},"\u062A\u0631\u0627\u062C\u0639")]),e("div",mt,[e("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:o[5]||(o[5]=c=>{g.$emit("a",n.value),j()}),disabled:!n.value.amount},"\u0646\u0639\u0645",8,pt)])])])])])])):A("",!0)]}),_:3}))}};const bt=e("h3",{class:"text-center"},"\u0627\u0644\u0645\u062D\u0627\u0633\u0628\u0629",-1),vt=e("h3",{class:"text-center"},"\u0627\u062F\u062E\u0627\u0644 \u0645\u0635\u0627\u0631\u064A\u0641 \u0627\u0644\u064A\u0648\u0645\u064A\u0629",-1),_t={key:0},ft={id:"alert-2",class:"p-4 mb-4 bg-red-100 rounded-lg dark:bg-red-200 text-center",role:"alert"},yt={class:"ml-3 font-medium text-red-700 dark:text-red-800"},xt={class:"max-w-9xl mx-auto sm:px-6 lg:px-8"},wt={class:"overflow-hidden shadow-sm sm:rounded-lg"},kt={class:"border-b border-gray-200"},$t={class:"mt-4 mb-4 grid grid-cols-1 gap-4 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-7"},Ct={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},Nt=e("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-orange-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"})])],-1),Dt={class:"mr-4"},Mt={class:"font-semibold"},St={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},At={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},jt=e("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-red-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"})])],-1),Bt={class:"mr-4"},Vt={class:"font-semibold"},Et={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},zt={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},Tt=e("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-red-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"})])],-1),Ut={class:"mr-4"},Gt={class:"font-semibold"},It={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},Ft={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},Ot=e("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-red-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"})])],-1),Lt={class:"mr-4"},Rt={class:"font-semibold"},Yt={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},Ht={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},qt=e("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-red-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"})])],-1),Jt={class:"mr-4"},Kt={class:"font-semibold"},Pt={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},Qt={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},Wt=e("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-red-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"})])],-1),Xt={class:"mr-4"},Zt={class:"font-semibold"},es={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},ts={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},ss=e("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-orange-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"})])],-1),os={class:"mr-4"},as={class:"font-semibold"},ds={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},ls={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},rs=e("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-orange-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"})])],-1),ns={class:"mr-4"},is={class:"font-semibold"},us={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},cs={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},gs=e("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-orange-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"})])],-1),ms={class:"mr-4"},ps={class:"font-semibold"},hs={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},bs={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},vs=e("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-orange-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"})])],-1),_s={class:"mr-4"},fs={class:"font-semibold"},ys={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},xs={class:"grid grid-cols-4 md:grid-cols-4 lg:grid-cols-7 gap-3 lg:gap-3"},ws={class:"pt-5 print:hidden"},ks={class:"pt-5 hidden"},$s={class:"pt-5 print:hidden"},Cs={class:"pt-5"},Ns=["href"],Ds={key:0},Ms={key:1},Ss={class:"px-4"},As={class:"px-4"},js={className:" mr-5 print:hidden"},Bs={key:0},Vs={key:1},Es={className:" mr-5 print:hidden"},zs=["href"],Ts={key:0},Us={key:1},Gs={class:"grid grid-cols-4 md:grid-cols-4 lg:grid-cols-7 gap-3 lg:gap-3"},Is={class:"grid grid-cols-2 md:grid-cols-5 lg:grid-cols-5 gap-3 lg:gap-3"},Fs={class:"px-4"},Os={class:"px-4"},Ls={class:"px-4"},Rs={class:"grid grid-cols-2 md:grid-cols-5 lg:grid-cols-5 gap-3 lg:gap-3 pt-3"},Ys={class:"px-4"},Hs={class:"px-4"},qs={class:"px-4"},Js={class:"overflow-x-auto shadow-md mt-5"},Ks={class:"w-full text-right text-gray-500 dark:text-gray-400 text-center"},Ps=e("thead",{class:"text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center"},[e("tr",{class:"rounded-l-lg mb-2 sm:mb-0"},[e("th",{className:"px-2 py-2"},"\u0631\u0642\u0645 \u0627\u0644\u0648\u0635\u0644"),e("th",{className:"px-2 py-2"},"\u0627\u0644\u062A\u0627\u0631\u064A\u062E"),e("th",{className:"px-2 py-2"},"\u0627\u0644\u0648\u0635\u0641"),e("th",{className:"px-2 py-2"},"\u0627\u0644\u0645\u0628\u0644\u063A"),e("th",{className:"px-2 py-2"},"\u062A\u0646\u0641\u064A\u0630")])],-1),Qs={className:"border dark:border-gray-800 text-center px-2 py-1"},Ws={className:"border dark:border-gray-800 text-center px-2 py-1"},Xs={className:"border dark:border-gray-800 text-center px-2 py-1"},Zs={className:"border dark:border-gray-800 text-center px-2 py-1"},eo={className:"border dark:border-gray-800 text-center px-2 py-1"},to=["onClick"],po={__name:"Index",props:{url:String,users:Array,accounts:Array,boxes:Array},setup(v){const n=v,p=r({});r(0);const j=r("");let g=r(!1),o=r(!1),u=r(!1),c=r(!1),C=r(0),oe=r({}),U=r({}),z=r(!1),N=r(P()),D=r(P()),G=r(0),I=r(0),F=r(0),O=r(0),L=r(0),R=r(0),Y=r(0),H=r(0),ae=r(0),q=r(0),J=r(0);const B=async(t=1)=>{j.value="";const s=await fetch(`/getIndexAccounting?page=${t}&user_id=${n.boxes[0].id}&from=${N.value}&to=${D.value}`);p.value=await s.json()};(async()=>{S.get("/api/totalInfo").then(t=>{G.value=t.data.data.mainAccount,I.value=t.data.data.onlineContracts,F.value=t.data.data.howler,O.value=t.data.data.shippingCoc,L.value=t.data.data.border,R.value=t.data.data.iran,Y.value=t.data.data.dubai,H.value=t.data.data.debtOnlineContracts,q.value=t.data.data.onlineContractsDinar,J.value=t.data.data.debtOnlineContractsDinar,ae.value=t.data.data.allCars}).catch(t=>{console.error(t)})})();function de(){g.value=!0}function le(){o.value=!0}function re(){u.value=!0}B(),pe(),r(!1);const V=r(0);function ne(t){S.post("/api/receiptArrived",t).then(s=>{g.value=!1,window.location.reload()}).catch(s=>{V.value=s.response.data.errors})}function K(t){S.post("/api/salesDebt",t).then(s=>{B(),o.value=!1,u.value=!1,window.location.reload()}).catch(s=>{V.value=s.response.data.errors})}function P(){const t=new Date,s=t.getFullYear(),w=String(t.getMonth()+1).padStart(2,"0"),k=String(t.getDate()).padStart(2,"0");return`${s}-${w}-${k}`}function ie(t){S.post(`/api/delTransactions?id=${t}`).then(s=>{B(),o.value=!1,u.value=!1}).catch(s=>{V.value=s.response.data.errors})}function E(t){C.value=t,ue(),c.value=!0}function ue(){S.get(`/api/getGenExpenses?expenses_type_id=${C.value}`).then(t=>{U.value=t.data}).catch(t=>{V.value=t.response.data.errors})}function ce(t){return typeof t!="number"&&(t=parseFloat(t)||0),t.toLocaleString()}function ge(t){var s,w,k;S.post(`/api/GenExpenses?amount=${(s=t.amount)!=null?s:0}&expenses_type_id=${C.value}&factor=${(w=t.factor)!=null?w:1}&note=${(k=t.note)!=null?k:""}`).then(f=>{B(),c.value=!1,console.log(f.data),window.open(`/api/getIndexAccountsSelas?user_id=${f.data.morphed_id}&print=3&transactions_id=${f.data.id}`,"_blank"),window.location.reload()}).catch(f=>{V.value=f.response.data.errors})}return(t,s)=>(m(),h(T,null,[l(i(he),{title:"Dashboard"}),l(_e,null,{header:y(()=>[]),default:y(()=>{var w,k,f,Q,W,X;return[l(fe,{formData:i(oe),show:!!i(c),expenses_type_id:i(C),GenExpenses:i(U),onA:s[0]||(s[0]=a=>ge(a)),onClose:s[1]||(s[1]=a=>M(c)?c.value=!1:c=!1)},{header:y(()=>[]),_:1},8,["formData","show","expenses_type_id","GenExpenses"]),l(Le,{show:!!i(g),data:v.users,accounts:v.accounts,onA:s[2]||(s[2]=a=>ne(a)),onClose:s[3]||(s[3]=a=>M(g)?g.value=!1:g=!1)},{header:y(()=>[bt]),_:1},8,["show","data","accounts"]),l(ht,{show:!!i(o),data:v.users,accounts:v.accounts,onA:s[4]||(s[4]=a=>K(a)),onClose:s[5]||(s[5]=a=>M(o)?o.value=!1:o=!1)},{header:y(()=>[]),_:1},8,["show","data","accounts"]),l(ye,{show:!!i(u),boxes:v.boxes,onA:s[6]||(s[6]=a=>K(a)),onClose:s[7]||(s[7]=a=>M(u)?u.value=!1:u=!1)},{header:y(()=>[vt]),_:1},8,["show","boxes"]),t.$page.props.success?(m(),h("div",_t,[e("div",ft,[e("div",yt,d(t.$page.props.success),1)])])):A("",!0),e("div",null,[e("div",xt,[e("div",wt,[e("div",kt,[e("div",$t,[e("div",Ct,[Nt,e("div",Dt,[e("h2",Mt,d(t.$t("capital")),1),e("p",St,d(ce(i(G))),1)])]),e("div",At,[jt,e("div",Bt,[e("h2",Vt,d(t.$t("genExpenses")),1),e("p",Et,d(i(F)),1)])]),e("div",zt,[Tt,e("div",Ut,[e("h2",Gt,d(t.$t("dubai")),1),e("p",It,d(i(Y)),1)])]),e("div",Ft,[Ot,e("div",Lt,[e("h2",Rt,d(t.$t("iran")),1),e("p",Yt,d(i(R)),1)])]),e("div",Ht,[qt,e("div",Jt,[e("h2",Kt,d(t.$t("border")),1),e("p",Pt,d(i(L)),1)])]),e("div",Qt,[Wt,e("div",Xt,[e("h2",Zt,d(t.$t("shipping_coc")),1),e("p",es,d(i(O)),1)])]),e("div",ts,[ss,e("div",os,[e("h2",as,d(t.$t("online_contracts")),1),e("p",ds,d(i(I))+" \u062F\u0648\u0644\u0627\u0631",1)])]),e("div",ls,[rs,e("div",ns,[e("h2",is,d(t.$t("debtOnlineContracts")),1),e("p",us,d(i(H))+" \u062F\u0648\u0644\u0627\u0631",1)])]),e("div",cs,[gs,e("div",ms,[e("h2",ps,d(t.$t("online_contracts")),1),e("p",hs,d(i(q))+" \u062F\u064A\u0646\u0627\u0631",1)])]),e("div",bs,[vs,e("div",_s,[e("h2",fs,d(t.$t("debtOnlineContracts")),1),e("p",ys,d(i(J))+" \u062F\u064A\u0646\u0627\u0631",1)])])]),e("div",xs,[e("div",ws,[t.$page.props.auth.user.type_id==1||t.$page.props.auth.user.type_id==2||t.$page.props.auth.user.type_id==5?(m(),h("button",{key:0,style:{width:"100%","margin-top":"4px"},className:"px-4 py-2 text-white bg-rose-500 rounded-md focus:outline-none",onClick:s[8]||(s[8]=a=>de())}," \u0648\u0635\u0644 \u0642\u0628\u0636 (\u0623\u0636\u0627\u0641\u0629) ")):A("",!0)]),e("div",ks,[t.$page.props.auth.user.type_id==1||t.$page.props.auth.user.type_id==2||t.$page.props.auth.user.type_id==5?(m(),h("button",{key:0,style:{width:"100%","margin-top":"4px"},className:"px-4 py-2 text-white bg-yellow-500 rounded-md focus:outline-none",onClick:s[9]||(s[9]=a=>le())}," \u062A\u062D\u0648\u064A\u0644 \u0644\u062D\u0633\u0627\u0628 ")):A("",!0)]),e("div",$s,[t.$page.props.auth.user.type_id==1||t.$page.props.auth.user.type_id==2||t.$page.props.auth.user.type_id==5?(m(),h("button",{key:0,style:{width:"100%","margin-top":"4px"},className:"px-4 py-2 text-white bg-blue-500 rounded-md focus:outline-none",onClick:s[10]||(s[10]=a=>re())}," \u0648\u0635\u0644 \u0635\u0631\u0641 (\u0633\u062D\u0628) ")):A("",!0)]),e("div",Cs,[e("a",{class:"px-2 mb-12 py-2 mt-1 font-bold text-white bg-pink-500 rounded",style:{display:"block","text-align":"center"},href:t.route("transfers"),target:"_blank"},[i(z)?(m(),h("span",Ms,"\u062C\u0627\u0631\u064A \u0627\u0644\u062D\u0641\u0638...")):(m(),h("span",Ds,"\u0627\u0644\u0623\u0631\u0634\u064A\u0641"))],8,Ns)]),e("div",Ss,[e("div",null,[l(b,{for:"from",value:"\u0645\u0646 \u062A\u0627\u0631\u064A\u062E"}),l(_,{id:"from",type:"date",class:"mt-1 block w-full",modelValue:i(N),"onUpdate:modelValue":s[11]||(s[11]=a=>M(N)?N.value=a:N=a)},null,8,["modelValue"])])]),e("div",As,[e("div",null,[l(b,{for:"to",value:"\u062D\u062A\u0649 \u062A\u0627\u0631\u064A\u062E"}),l(_,{id:"to",type:"date",class:"mt-1 block w-full",modelValue:i(D),"onUpdate:modelValue":s[12]||(s[12]=a=>M(D)?D.value=a:D=a)},null,8,["modelValue"])])]),e("div",js,[l(b,{for:"pay",value:"\u0641\u0644\u062A\u0631\u0629"}),e("button",{onClick:s[13]||(s[13]=be(a=>B(),["prevent"])),class:"px-6 mb-12 py-2 mt-1 font-bold text-white bg-gray-500 rounded",style:{width:"100%"}},[i(z)?(m(),h("span",Vs,"\u062C\u0627\u0631\u064A \u0627\u0644\u062D\u0641\u0638...")):(m(),h("span",Bs,"\u0641\u0644\u062A\u0631\u0629"))])]),e("div",Es,[l(b,{for:"pay",value:"\u0637\u0628\u0627\u0639\u0629"}),e("a",{class:"px-6 mb-12 py-2 mt-1 font-bold text-white bg-orange-500 rounded",style:{display:"block","text-align":"center"},href:`/getIndexAccounting?user_id=${(k=(w=p.value)==null?void 0:w.user)==null?void 0:k.id}&from=${i(N)}&to=${i(D)}&print=1`,target:"_blank"},[i(z)?(m(),h("span",Us,"\u062C\u0627\u0631\u064A \u0627\u0644\u062D\u0641\u0638...")):(m(),h("span",Ts,"\u0637\u0628\u0627\u0639\u0629"))],8,zs)])]),e("div",Gs,[e("div",null,[e("button",{type:"button",onClick:s[14]||(s[14]=a=>E(1)),style:{"min-width":"150px"},className:"px-6 mb-12 py-2 font-bold text-white bg-red-500 rounded  w-full"},d(t.$t("genExpenses")),1)]),e("div",null,[e("button",{type:"button",onClick:s[15]||(s[15]=a=>E(2)),style:{"min-width":"150px"},className:"px-6 mb-12 text-center py-2 font-bold text-white bg-blue-600 rounded  w-full"},d(t.$t("dubai")),1)]),e("div",null,[e("button",{type:"button",onClick:s[16]||(s[16]=a=>E(3)),style:{"min-width":"150px"},className:"px-6 mb-12 text-center w-full py-2 font-bold text-white bg-blue-600 rounded"},d(t.$t("iran")),1)]),e("div",null,[e("button",{type:"button",onClick:s[17]||(s[17]=a=>E(4)),style:{"min-width":"150px"},className:"px-6 mb-12 w-full py-2 font-bold text-white bg-indigo-600 rounded"},d(t.$t("border")),1)]),e("div",null,[e("button",{type:"button",onClick:s[18]||(s[18]=a=>E(5)),style:{"min-width":"150px"},className:"px-6 mb-12 w-full py-2 font-bold text-white bg-pink-600 rounded"},d(t.$t("shipping_coc")),1)])]),e("div",Is,[e("div",Fs,[e("div",null,[l(b,{for:"to",value:"\u062D\u0633\u0627\u0628 \u0627\u0644\u0635\u0646\u062F\u0648\u0642 \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631"}),l(_,{id:"to",type:"number",disabled:"",class:"mt-1 block w-full",value:p.value.sum_transactions},null,8,["value"])])]),e("div",Os,[e("div",null,[l(b,{for:"to",value:"\u0645\u0633\u062D\u0648\u0628\u0627\u062A \u0627\u0644\u0635\u0646\u062F\u0648\u0642 \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631"}),l(_,{id:"to",type:"number",disabled:"",class:"mt-1 block w-full",value:p.value.sum_transactions_debit},null,8,["value"])])]),e("div",Ls,[e("div",null,[l(b,{for:"to",value:"\u062F\u062E\u0644 \u0627\u0644\u0635\u0646\u062F\u0648\u0642 \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631"}),l(_,{id:"to",type:"number",disabled:"",class:"mt-1 block w-full",value:p.value.sum_transactions_in},null,8,["value"])])]),e("div",null,[l(b,{for:"to",value:"\u0631\u0635\u064A\u062F \u0627\u0644\u0635\u0646\u062F\u0648\u0642 \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631"}),l(_,{id:"to",type:"number",disabled:"",class:"mt-1 block w-full",value:(Q=(f=p.value)==null?void 0:f.user)==null?void 0:Q.wallet.balance},null,8,["value"])])]),e("div",Rs,[e("div",Ys,[e("div",null,[l(b,{for:"to",value:"\u062D\u0633\u0627\u0628 \u0627\u0644\u0635\u0646\u062F\u0648\u0642 \u0628\u0627\u0644\u062F\u064A\u0646\u0627\u0631 \u0627\u0644\u0639\u0631\u0627\u0642\u064A"}),l(_,{id:"to",type:"number",disabled:"",class:"mt-1 block w-full",value:p.value.sum_transactions_dinar},null,8,["value"])])]),e("div",Hs,[e("div",null,[l(b,{for:"to",value:"\u0645\u0633\u062D\u0648\u0628\u0627\u062A \u0627\u0644\u0635\u0646\u062F\u0648\u0642 \u0628\u0627\u0644\u062F\u064A\u0646\u0627\u0631 \u0627\u0644\u0639\u0631\u0627\u0642\u064A"}),l(_,{id:"to",type:"number",disabled:"",class:"mt-1 block w-full",value:p.value.sum_transactions_debit_dinar},null,8,["value"])])]),e("div",qs,[e("div",null,[l(b,{for:"to",value:"\u062F\u062E\u0644 \u0627\u0644\u0635\u0646\u062F\u0648\u0642 \u0628\u0627\u0644\u062F\u064A\u0646\u0627\u0631 \u0627\u0644\u0639\u0631\u0627\u0642\u064A"}),l(_,{id:"to",type:"number",disabled:"",class:"mt-1 block w-full",value:p.value.sum_transactions_in_dinar},null,8,["value"])])]),e("div",null,[l(b,{for:"to",value:"\u0631\u0635\u064A\u062F \u0627\u0644\u0635\u0646\u062F\u0648\u0642 \u0628\u0627\u0644\u062F\u064A\u0646\u0627\u0631 \u0627\u0644\u0639\u0631\u0627\u0642\u064A"}),l(_,{id:"to",type:"number",disabled:"",class:"mt-1 block w-full",value:(X=(W=p.value)==null?void 0:W.user)==null?void 0:X.wallet.balance_dinar},null,8,["value"])])]),e("div",Js,[e("table",Ks,[Ps,e("tbody",null,[(m(!0),h(T,null,se(p.value.transactions,a=>(m(),h("tr",{key:a.id,class:ve([a.type!="in"?"bg-red-100 dark:bg-red-900":"bg-green-100 dark:bg-green-900","bg-white border-b dark:bg-gray-900 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-600"])},[e("td",Qs,d(a.id),1),e("td",Ws,d(a==null?void 0:a.created),1),e("th",Xs,d(a.description),1),e("td",Zs,d(a.amount+" "+a.currency),1),e("td",eo,[e("button",{class:"px-1 py-1 text-white bg-rose-500 rounded-md focus:outline-none hidden",onClick:oo=>ie(a.id)},[l(xe)],8,to)])],2))),128))])])])])])])])]}),_:1})],64))}};export{po as default};
