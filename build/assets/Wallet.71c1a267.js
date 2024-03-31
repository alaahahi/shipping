import{r as o,o as c,c as J,w as f,a as p,b as e,d as K,e as A,v as M,i as C,T as P,u as Q,g as d,j as i,F as W,H as X,k as _,t as N,l as Z,h as ee,n as se}from"./app.c570c989.js";import{_ as te}from"./AuthenticatedLayout.3e4be376.js";/* empty css                                              */import{M as oe,_ as ae,a as le,b as ne}from"./ModalDel.3b173492.js";import{_ as x,a as U}from"./TextInput.42a0c1f4.js";/* empty css                                                            */import{a as w}from"./index.3a46764c.js";import{W as re}from"./v3-infinite-loading.es.6369b438.js";/* empty css              */import{d as de}from"./debounce.d7763fcc.js";const ie={key:0,class:"modal-mask"},ue={class:"modal-wrapper max-h-[80vh]"},ce={class:"modal-container"},pe={class:"modal-header"},me={class:"modal-body"},ge={class:"grid grid-cols-1 md:grid-cols-1 lg:grid-cols-2 gap-3 lg:gap-3"},fe={className:"mb-4 mx-5"},ve=e("label",{for:"amountDollar"},"\u0627\u0644\u0645\u0628\u0644\u063A \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631",-1),be={className:"mb-4 mx-5"},_e=e("label",{for:"amountDinar"},"\u0627\u0644\u0645\u0628\u0644\u063A \u0628\u0627\u0644\u062F\u064A\u0646\u0627\u0631 \u0627\u0644\u0639\u0631\u0627\u0642\u064A",-1),he={className:"mb-4 mx-5"},ye=e("label",{for:"note"},"\u0645\u0644\u0627\u062D\u0638\u0629",-1),xe={className:"mb-4 mx-5"},we=e("label",{for:"card"},"\u0627\u0644\u062A\u0627\u0631\u064A\u062E",-1),$e={class:"modal-footer my-2"},ke={class:"flex flex-row"},De={class:"basis-1/2 px-4"},Ne={class:"basis-1/2 px-4"},Ce={__name:"ModalAddExpensesWallet",props:{show:Boolean,boxes:Array,sum_transactions:Intl,sum_transactions_dinar:Intl},setup(m){const $=m,n=o({id:$.boxes.id,date:g()});function g(){const u=new Date,l=u.getFullYear(),r=String(u.getMonth()+1).padStart(2,"0"),v=String(u.getDate()).padStart(2,"0");return`${l}-${r}-${v}`}const B=()=>{n.value={id:$.boxes.id,date:g()}};return(u,l)=>(c(),J(P,{name:"modal"},{default:f(()=>[m.show?(c(),p("div",ie,[e("div",ue,[e("div",ce,[e("div",pe,[K(u.$slots,"header")]),e("div",me,[e("div",ge,[e("div",fe,[ve,A(e("input",{id:"amountDollar",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":l[0]||(l[0]=r=>n.value.amountDollar=r)},null,512),[[M,n.value.amountDollar]])]),e("div",be,[_e,A(e("input",{id:"amountDinar",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":l[1]||(l[1]=r=>n.value.amountDinar=r)},null,512),[[M,n.value.amountDinar]])]),e("div",he,[ye,A(e("input",{id:"note",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":l[2]||(l[2]=r=>n.value.note=r)},null,512),[[M,n.value.note]])]),e("div",xe,[we,A(e("input",{id:"card",type:"date",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":l[3]||(l[3]=r=>n.value.date=r)},null,512),[[M,n.value.date]])])])]),e("div",$e,[e("div",ke,[e("div",De,[e("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:l[4]||(l[4]=r=>{u.$emit("close")})},"\u062A\u0631\u0627\u062C\u0639")]),e("div",Ne,[e("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:l[5]||(l[5]=r=>{u.$emit("a",n.value),B()})},"\u0646\u0639\u0645")])])])])])])):C("",!0)]),_:3}))}};const Ve=e("h2",{class:"mb-5 dark:text-white text-center"}," \u0647\u0644 \u0645\u062A\u0623\u0643\u062F \u0645\u0646 \u0627\u0644\u062D\u0630\u0641 \u061F ",-1),Ae=e("h3",{class:"text-center"},"\u0627\u0644\u0645\u062D\u0627\u0633\u0628\u0629",-1),Me=e("h3",{class:"text-center"}," \u0633\u062D\u0628 \u0645\u0646 \u0627\u0644\u0642\u0627\u0633\u0647",-1),Ue=e("h3",{class:"text-center"},"\u062A\u062D\u0648\u064A\u0644 \u0645\u0646 \u0627\u0644\u062F\u0648\u0644\u0627\u0631 \u0644\u0644\u062F\u064A\u0646\u0627\u0631",-1),Be=e("h3",{class:"text-center"},"\u062A\u062D\u0648\u064A\u0644 \u0645\u0646 \u0627\u0644\u062F\u064A\u0646\u0627\u0631 \u0644\u0644\u062F\u0648\u0644\u0627\u0631",-1),Te={key:0},Se={id:"alert-2",class:"p-4 mb-4 bg-red-100 rounded-lg dark:bg-red-200 text-center",role:"alert"},Ie={class:"ml-3 font-medium text-red-700 dark:text-red-800"},Ee={class:"max-w-9xl mx-auto sm:px-6 lg:px-8"},We={class:"overflow-hidden shadow-sm sm:rounded-lg"},Fe={class:"border-b border-gray-200"},je={class:"grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 lg:gap-3"},Oe={class:"pt-5 print:hidden"},qe={class:"pt-5 print:hidden"},He={class:"px-4"},Le={class:"px-4"},Re={className:" mr-5 print:hidden"},ze={key:0},Ye={key:1},Ge={className:" mr-5 print:hidden"},Je=["href"],Ke={key:0},Pe={key:1};const Qe={class:"grid grid-cols-2 md:grid-cols-5 lg:grid-cols-5 gap-3 lg:gap-3"},Xe={class:"px-4"},Ze={class:"px-4"},es={class:"overflow-x-auto shadow-md mt-5"},ss={class:"w-full text-right text-gray-500 dark:text-gray-400 text-center"},ts=e("thead",{class:"text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center"},[e("tr",{class:"rounded-l-lg mb-2 sm:mb-0"},[e("th",{className:"px-2 py-2"},"\u0631\u0642\u0645 \u0627\u0644\u0648\u0635\u0644"),e("th",{className:"px-2 py-2"},"\u0627\u0644\u062A\u0627\u0631\u064A\u062E"),e("th",{className:"px-2 py-2"},"\u0627\u0644\u0648\u0635\u0641"),e("th",{className:"px-2 py-2"},"\u0627\u0644\u0645\u0628\u0644\u063A"),e("th",{className:"px-2 py-2"},"\u062A\u0646\u0641\u064A\u0630")])],-1),os={className:"border dark:border-gray-800 text-center px-2 py-1"},as={className:"border dark:border-gray-800 text-center px-2 py-1"},ls={className:"border dark:border-gray-800 text-center px-2 py-1"},ns={className:"border dark:border-gray-800 text-center px-2 py-1"},rs=e("td",{className:"border dark:border-gray-800 text-center px-2 py-1"},null,-1),ds={class:"spaner"},xs={__name:"Wallet",props:{url:String,boxes:Object},setup(m){const $=m,n=o({});o("");let g=o(!1),B=o(!1),u=o(!1);o(!1);let l=o(!1),r=o(!1),v=o(!1),V=o([]);o(0);let F=o({});o({}),o({});let I=o(!1),h=o(""),y=o("");o(0),o(0),o(0),o(0),o(0),o(0),o(0),o(0),o(0),o(0),o(0);let T=o(!1),S=1,j="";const k=()=>{S=0,V.value.length=0,T.value=!T.value};de(k,500);const O=async a=>{try{const b=(await w.get("/getIndexAccounting",{params:{limit:100,page:S,q:j,user_id:$.boxes.id,from:h.value,to:y.value,type:"wallet"}})).data;b.transactions.data.length<100?(V.value.push(...b.transactions.data),a.complete()):(V.value.push(...b.transactions.data),a.loaded()),n.value=b,S++}catch(s){console.log(s)}};function q(){g.value=!0}function H(){u.value=!0}function is(){l.value=!0}function us(){r.value=!0}Q(),o(!1);const D=o(0);function L(a){a.id=$.boxes.id,w.post("/api/receiptArrivedUser",a).then(s=>{g.value=!1,window.location.reload()}).catch(s=>{D.value=s.response.data.errors})}function R(a){w.post("/api/salesDebtUser",a).then(s=>{B.value=!1,u.value=!1,window.location.reload()}).catch(s=>{D.value=s.response.data.errors})}function z(a){w.post("/api/convertDollarDinar",a).then(s=>{k(),l.value=!1}).catch(s=>{D.value=s.response.data.errors})}function Y(a){w.post("/api/convertDinarDollar",a).then(s=>{k(),r.value=!1}).catch(s=>{D.value=s.response.data.errors})}function G(a){w.post(`/api/delTransactions?id=${a.id}`).then(s=>{k(),v.value=!1}).catch(s=>{D.value=s.response.data.errors})}return(a,s)=>(c(),p(W,null,[d(i(X),{title:"Dashboard"}),d(te,null,{header:f(()=>[]),default:f(()=>{var b,E;return[d(oe,{show:!!i(v),formData:i(F),onA:s[0]||(s[0]=t=>G(t)),onClose:s[1]||(s[1]=t=>_(v)?v.value=!1:v=!1)},{header:f(()=>[Ve]),_:1},8,["show","formData"]),d(ae,{show:!!i(g),onA:s[2]||(s[2]=t=>L(t)),onClose:s[3]||(s[3]=t=>_(g)?g.value=!1:g=!1)},{header:f(()=>[Ae]),_:1},8,["show"]),d(Ce,{show:!!i(u),boxes:m.boxes,sum_transactions:n.value.sum_transactions,sum_transactions_dinar:n.value.sum_transactions_dinar,onA:s[4]||(s[4]=t=>R(t)),onClose:s[5]||(s[5]=t=>_(u)?u.value=!1:u=!1)},{header:f(()=>[Me]),_:1},8,["show","boxes","sum_transactions","sum_transactions_dinar"]),d(le,{show:!!i(l),boxes:m.boxes,onA:s[6]||(s[6]=t=>z(t)),onClose:s[7]||(s[7]=t=>_(l)?l.value=!1:l=!1)},{header:f(()=>[Ue]),_:1},8,["show","boxes"]),d(ne,{show:!!i(r),boxes:m.boxes,onA:s[8]||(s[8]=t=>Y(t)),onClose:s[9]||(s[9]=t=>_(r)?r.value=!1:r=!1)},{header:f(()=>[Be]),_:1},8,["show","boxes"]),a.$page.props.success?(c(),p("div",Te,[e("div",Se,[e("div",Ie,N(a.$page.props.success),1)])])):C("",!0),e("div",null,[e("div",Ee,[e("div",We,[e("div",Fe,[e("div",je,[e("div",Oe,[a.$page.props.auth.user.type_id==1||a.$page.props.auth.user.type_id==2||a.$page.props.auth.user.type_id==5?(c(),p("button",{key:0,style:{width:"100%","margin-top":"4px"},className:"px-4 py-2 text-white bg-green-800 rounded-md focus:outline-none",onClick:s[10]||(s[10]=t=>q())}," \u0648\u0635\u0644 \u0642\u0628\u0636 (\u0623\u0636\u0627\u0641\u0629) ")):C("",!0)]),e("div",qe,[a.$page.props.auth.user.type_id==1||a.$page.props.auth.user.type_id==2||a.$page.props.auth.user.type_id==5?(c(),p("button",{key:0,style:{width:"100%","margin-top":"4px"},className:"px-4 py-2 text-white bg-red-800 rounded-md focus:outline-none",onClick:s[11]||(s[11]=t=>H())}," \u0648\u0635\u0644 \u0635\u0631\u0641 (\u0633\u062D\u0628) ")):C("",!0)]),e("div",He,[e("div",null,[d(x,{for:"from",value:"\u0645\u0646 \u062A\u0627\u0631\u064A\u062E"}),d(U,{id:"from",type:"date",class:"mt-1 block w-full",modelValue:i(h),"onUpdate:modelValue":s[12]||(s[12]=t=>_(h)?h.value=t:h=t)},null,8,["modelValue"])])]),e("div",Le,[e("div",null,[d(x,{for:"to",value:"\u062D\u062A\u0649 \u062A\u0627\u0631\u064A\u062E"}),d(U,{id:"to",type:"date",class:"mt-1 block w-full",modelValue:i(y),"onUpdate:modelValue":s[13]||(s[13]=t=>_(y)?y.value=t:y=t)},null,8,["modelValue"])])]),e("div",Re,[d(x,{for:"pay",value:"\u0641\u0644\u062A\u0631\u0629"}),e("button",{onClick:s[14]||(s[14]=Z(t=>k(),["prevent"])),class:"px-6 mb-12 py-2 mt-1 font-bold text-white bg-gray-500 rounded",style:{width:"100%"}},[i(I)?(c(),p("span",Ye,"\u062C\u0627\u0631\u064A \u0627\u0644\u062D\u0641\u0638...")):(c(),p("span",ze,"\u0641\u0644\u062A\u0631\u0629"))])]),e("div",Ge,[d(x,{for:"pay",value:"\u0637\u0628\u0627\u0639\u0629"}),e("a",{class:"px-6 mb-12 py-2 mt-1 font-bold text-white bg-orange-500 rounded",style:{display:"block","text-align":"center"},href:`/getIndexAccounting?user_id=${(E=(b=n.value)==null?void 0:b.user)==null?void 0:E.id}&from=${i(h)}&to=${i(y)}&print=1`,target:"_blank"},[i(I)?(c(),p("span",Pe,"\u062C\u0627\u0631\u064A \u0627\u0644\u062D\u0641\u0638...")):(c(),p("span",Ke,"\u0637\u0628\u0627\u0639\u0629"))],8,Je)])]),C("",!0),e("div",Qe,[e("div",Xe,[e("div",null,[d(x,{for:"to",value:`\u062D\u0633\u0627\u0628 ${m.boxes.name} \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631`},null,8,["value"]),d(U,{id:"to",type:"number",disabled:"",class:"mt-1 block w-full",value:n.value.sumInTransactionsUser-n.value.sumOutTransactionsUser},null,8,["value"])])]),e("div",Ze,[e("div",null,[d(x,{for:"to",value:`\u062D\u0633\u0627\u0628 ${m.boxes.name} \u0628\u0627\u0644\u062F\u064A\u0646\u0627\u0631 \u0627\u0644\u0639\u0631\u0627\u0642\u064A`},null,8,["value"]),d(U,{id:"to",type:"number",disabled:"",class:"mt-1 block w-full",value:n.value.sumInTransactionsDinarUser-n.value.sumOutTransactionsDinarUser},null,8,["value"])])])]),e("div",es,[e("table",ss,[ts,e("tbody",null,[(c(!0),p(W,null,ee(i(V),t=>(c(),p("tr",{key:t.id,class:se([t.type!="inUser"?"bg-red-100 dark:bg-red-900":"bg-green-100 dark:bg-green-900","bg-white border-b dark:bg-gray-900 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-600"])},[e("td",os,N(t.id),1),e("td",as,N(t==null?void 0:t.created_at.slice(0,19).replace("T","  ")),1),e("th",ls,N(t.description),1),e("td",ns,N(t.amount+" "+t.currency),1),rs],2))),128))])])]),e("div",ds,[d(i(re),{car:a.car,onInfinite:O,identifier:i(T)},null,8,["car","identifier"])])])])])])]}),_:1})],64))}};export{xs as default};
