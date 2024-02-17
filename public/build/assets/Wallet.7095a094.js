import{r as t,o as m,c as J,w as f,a as p,b as e,d as K,e as B,v as M,h as k,T as P,l as Q,j as r,k as i,F,H as X,m as b,t as A,p as Z,f as ee,n as se}from"./app.9ace330f.js";import{a as oe}from"./AuthenticatedLayout.97f59875.js";/* empty css                                              */import{M as te,_ as ae,a as le,b as ne,c as re}from"./ModalDel.4a27504b.js";import{_ as w,a as U}from"./TextInput.4a8f31cb.js";/* empty css                                                            */import{a as $}from"./index.3f82cb61.js";import{W as de}from"./v3-infinite-loading.es.5296af7e.js";import{d as ie}from"./debounce.4bd4e5b9.js";const ue={key:0,class:"modal-mask"},ce={class:"modal-wrapper max-h-[80vh]"},me={class:"modal-container"},pe={class:"modal-header"},ge={class:"modal-body"},fe={class:"grid grid-cols-2 md:grid-cols-2 lg:grid-cols-2 gap-3 lg:gap-3"},ve={className:"mb-4 mx-5"},be=e("label",{for:"card"},"\u0627\u0644\u062A\u0627\u0631\u064A\u062E",-1),_e={className:"mb-4 mx-5"},he=e("label",{for:"user_id"},"\u0627\u0644\u062D\u0633\u0627\u0628",-1),ye=["value"],xe={className:"mb-4 mx-5"},we=e("label",{for:"balance"},"\u0627\u0644\u0631\u0635\u064A\u062F \u0627\u0644\u062D\u0627\u0644\u064A \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631",-1),$e=["value"],ke={className:"mb-4 mx-5"},De=e("label",{for:"balance"},"\u0627\u0644\u0631\u0635\u064A\u062F \u0627\u0644\u062D\u0627\u0644\u064A \u0628\u0627\u0644\u062F\u064A\u0646\u0627\u0631 \u0627\u0644\u0639\u0631\u0627\u0642\u064A",-1),Ne=["value"],Ce={class:"grid grid-cols-2 md:grid-cols-2 lg:grid-cols-2 gap-3 lg:gap-3"},Ae={className:"mb-4 mx-5"},Ve=e("label",{for:"amountDollar"},"\u0627\u0644\u0645\u0628\u0644\u063A \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631",-1),Be={className:"mb-4 mx-5"},Me=e("label",{for:"amountDinar"},"\u0627\u0644\u0645\u0628\u0644\u063A \u0628\u0627\u0644\u062F\u064A\u0646\u0627\u0631 \u0627\u0644\u0639\u0631\u0627\u0642\u064A",-1),Ue={className:"mb-4 mx-5"},Se=e("label",{for:"note"},"\u0645\u0644\u0627\u062D\u0638\u0629",-1),Te={class:"modal-footer my-2"},Ie={class:"flex flex-row"},Ee={class:"basis-1/2 px-4"},We={class:"basis-1/2 px-4"},Fe={__name:"ModalAddExpensesWallet",props:{show:Boolean,boxes:Array,sum_transactions:Intl,sum_transactions_dinar:Intl},setup(c){const D=c,n=t({id:D.boxes.id,date:g()});function g(){const u=new Date,l=u.getFullYear(),d=String(u.getMonth()+1).padStart(2,"0"),_=String(u.getDate()).padStart(2,"0");return`${l}-${d}-${_}`}const v=()=>{n.value={id:D.boxes.id,date:g()}};return(u,l)=>(m(),J(P,{name:"modal"},{default:f(()=>[c.show?(m(),p("div",ue,[e("div",ce,[e("div",me,[e("div",pe,[K(u.$slots,"header")]),e("div",ge,[e("div",fe,[e("div",ve,[be,B(e("input",{id:"card",type:"date",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":l[0]||(l[0]=d=>n.value.date=d)},null,512),[[M,n.value.date]])]),e("div",_e,[he,e("input",{id:"card",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm",value:c.boxes.name},null,8,ye)]),e("div",xe,[we,e("input",{id:"balance",type:"number",value:c.sum_transactions,class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"},null,8,$e)]),e("div",ke,[De,e("input",{id:"balance",type:"number",value:c.sum_transactions_dinar,class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"},null,8,Ne)])]),e("div",Ce,[e("div",Ae,[Ve,B(e("input",{id:"amountDollar",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":l[1]||(l[1]=d=>n.value.amountDollar=d)},null,512),[[M,n.value.amountDollar]])]),e("div",Be,[Me,B(e("input",{id:"amountDinar",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":l[2]||(l[2]=d=>n.value.amountDinar=d)},null,512),[[M,n.value.amountDinar]])]),e("div",Ue,[Se,B(e("input",{id:"note",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":l[3]||(l[3]=d=>n.value.note=d)},null,512),[[M,n.value.note]])])])]),e("div",Te,[e("div",Ie,[e("div",Ee,[e("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:l[4]||(l[4]=d=>{u.$emit("close")})},"\u062A\u0631\u0627\u062C\u0639")]),e("div",We,[e("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:l[5]||(l[5]=d=>{u.$emit("a",n.value),v()})},"\u0646\u0639\u0645")])])])])])])):k("",!0)]),_:3}))}};const je=e("h2",{class:"mb-5 dark:text-white text-center"}," \u0647\u0644 \u0645\u062A\u0623\u0643\u062F \u0645\u0646 \u0627\u0644\u062D\u0630\u0641 \u061F ",-1),Oe=e("h3",{class:"text-center"},"\u0627\u0644\u0645\u062D\u0627\u0633\u0628\u0629",-1),qe=e("h3",{class:"text-center"}," \u0633\u062D\u0628 \u0645\u0646 \u0627\u0644\u0642\u0627\u0633\u0647",-1),He=e("h3",{class:"text-center"},"\u062A\u062D\u0648\u064A\u0644 \u0645\u0646 \u0627\u0644\u062F\u0648\u0644\u0627\u0631 \u0644\u0644\u062F\u064A\u0646\u0627\u0631",-1),Le=e("h3",{class:"text-center"},"\u062A\u062D\u0648\u064A\u0644 \u0645\u0646 \u0627\u0644\u062F\u064A\u0646\u0627\u0631 \u0644\u0644\u062F\u0648\u0644\u0627\u0631",-1),Re={key:0},ze={id:"alert-2",class:"p-4 mb-4 bg-red-100 rounded-lg dark:bg-red-200 text-center",role:"alert"},Ye={class:"ml-3 font-medium text-red-700 dark:text-red-800"},Ge={class:"max-w-9xl mx-auto sm:px-6 lg:px-8"},Je={class:"overflow-hidden shadow-sm sm:rounded-lg"},Ke={class:"border-b border-gray-200"},Pe={class:"grid grid-cols-4 md:grid-cols-4 lg:grid-cols-7 gap-3 lg:gap-3"},Qe={class:"pt-5 print:hidden"},Xe={class:"pt-5"},Ze={class:"pt-5 print:hidden"},es={class:"px-4"},ss={class:"px-4"},os={className:" mr-5 print:hidden"},ts={key:0},as={key:1},ls={className:" mr-5 print:hidden"},ns=["href"],rs={key:0},ds={key:1};const is={class:"grid grid-cols-2 md:grid-cols-5 lg:grid-cols-5 gap-3 lg:gap-3"},us={class:"px-4"},cs={class:"px-4"},ms={class:"overflow-x-auto shadow-md mt-5"},ps={class:"w-full text-right text-gray-500 dark:text-gray-400 text-center"},gs=e("thead",{class:"text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center"},[e("tr",{class:"rounded-l-lg mb-2 sm:mb-0"},[e("th",{className:"px-2 py-2"},"\u0631\u0642\u0645 \u0627\u0644\u0648\u0635\u0644"),e("th",{className:"px-2 py-2"},"\u0627\u0644\u062A\u0627\u0631\u064A\u062E"),e("th",{className:"px-2 py-2"},"\u0627\u0644\u0648\u0635\u0641"),e("th",{className:"px-2 py-2"},"\u0627\u0644\u0645\u0628\u0644\u063A"),e("th",{className:"px-2 py-2"},"\u062A\u0646\u0641\u064A\u0630")])],-1),fs={className:"border dark:border-gray-800 text-center px-2 py-1"},vs={className:"border dark:border-gray-800 text-center px-2 py-1"},bs={className:"border dark:border-gray-800 text-center px-2 py-1"},_s={className:"border dark:border-gray-800 text-center px-2 py-1"},hs=e("td",{className:"border dark:border-gray-800 text-center px-2 py-1"},null,-1),ys={class:"spaner"},Ss={__name:"Wallet",props:{url:String,boxes:Object},setup(c){const D=c,n=t({});t("");let g=t(!1),v=t(!1),u=t(!1);t(!1);let l=t(!1),d=t(!1),_=t(!1),V=t([]);t(0);let j=t({});t({}),t({});let I=t(!1),y=t(""),x=t("");t(0),t(0),t(0),t(0),t(0),t(0),t(0),t(0),t(0),t(0),t(0);let S=t(!1),T=1,O="";const N=()=>{T=0,V.value.length=0,S.value=!S.value};ie(N,500);const q=async a=>{try{const h=(await $.get("/getIndexAccounting",{params:{limit:100,page:T,q:O,user_id:D.boxes.id,from:y.value,to:x.value,type:"wallet"}})).data;h.transactions.data.length<100?(V.value.push(...h.transactions.data),a.complete()):(V.value.push(...h.transactions.data),a.loaded()),n.value=h,T++}catch(s){console.log(s)}};function H(){g.value=!0}function xs(){v.value=!0}function L(){u.value=!0}function ws(){l.value=!0}function $s(){d.value=!0}Q(),t(!1);const C=t(0);function R(a){a.id=D.boxes.id,$.post("/api/receiptArrivedUser",a).then(s=>{g.value=!1,window.location.reload()}).catch(s=>{C.value=s.response.data.errors})}function E(a){$.post("/api/salesDebtUser",a).then(s=>{v.value=!1,u.value=!1,window.location.reload()}).catch(s=>{C.value=s.response.data.errors})}function z(a){$.post("/api/convertDollarDinar",a).then(s=>{N(),l.value=!1}).catch(s=>{C.value=s.response.data.errors})}function Y(a){$.post("/api/convertDinarDollar",a).then(s=>{N(),d.value=!1}).catch(s=>{C.value=s.response.data.errors})}function G(a){$.post(`/api/delTransactions?id=${a.id}`).then(s=>{N(),_.value=!1}).catch(s=>{C.value=s.response.data.errors})}return(a,s)=>(m(),p(F,null,[r(i(X),{title:"Dashboard"}),r(oe,null,{header:f(()=>[]),default:f(()=>{var h,W;return[r(te,{show:!!i(_),formData:i(j),onA:s[0]||(s[0]=o=>G(o)),onClose:s[1]||(s[1]=o=>b(_)?_.value=!1:_=!1)},{header:f(()=>[je]),_:1},8,["show","formData"]),r(ae,{show:!!i(g),onA:s[2]||(s[2]=o=>R(o)),onClose:s[3]||(s[3]=o=>b(g)?g.value=!1:g=!1)},{header:f(()=>[Oe]),_:1},8,["show"]),r(le,{show:!!i(v),onA:s[4]||(s[4]=o=>E(o)),onClose:s[5]||(s[5]=o=>b(v)?v.value=!1:v=!1)},{header:f(()=>[]),_:1},8,["show"]),r(Fe,{show:!!i(u),boxes:c.boxes,sum_transactions:n.value.sum_transactions,sum_transactions_dinar:n.value.sum_transactions_dinar,onA:s[6]||(s[6]=o=>E(o)),onClose:s[7]||(s[7]=o=>b(u)?u.value=!1:u=!1)},{header:f(()=>[qe]),_:1},8,["show","boxes","sum_transactions","sum_transactions_dinar"]),r(ne,{show:!!i(l),boxes:c.boxes,onA:s[8]||(s[8]=o=>z(o)),onClose:s[9]||(s[9]=o=>b(l)?l.value=!1:l=!1)},{header:f(()=>[He]),_:1},8,["show","boxes"]),r(re,{show:!!i(d),boxes:c.boxes,onA:s[10]||(s[10]=o=>Y(o)),onClose:s[11]||(s[11]=o=>b(d)?d.value=!1:d=!1)},{header:f(()=>[Le]),_:1},8,["show","boxes"]),a.$page.props.success?(m(),p("div",Re,[e("div",ze,[e("div",Ye,A(a.$page.props.success),1)])])):k("",!0),e("div",null,[e("div",Ge,[e("div",Je,[e("div",Ke,[e("div",Pe,[e("div",Qe,[a.$page.props.auth.user.type_id==1||a.$page.props.auth.user.type_id==2||a.$page.props.auth.user.type_id==5?(m(),p("button",{key:0,style:{width:"100%","margin-top":"4px"},className:"px-4 py-2 text-white bg-green-800 rounded-md focus:outline-none",onClick:s[12]||(s[12]=o=>H())}," \u0648\u0635\u0644 \u0642\u0628\u0636 (\u0623\u0636\u0627\u0641\u0629) ")):k("",!0)]),e("div",Xe,[k("",!0)]),e("div",Ze,[a.$page.props.auth.user.type_id==1||a.$page.props.auth.user.type_id==2||a.$page.props.auth.user.type_id==5?(m(),p("button",{key:0,style:{width:"100%","margin-top":"4px"},className:"px-4 py-2 text-white bg-red-800 rounded-md focus:outline-none",onClick:s[14]||(s[14]=o=>L())}," \u0648\u0635\u0644 \u0635\u0631\u0641 (\u0633\u062D\u0628) ")):k("",!0)]),e("div",es,[e("div",null,[r(w,{for:"from",value:"\u0645\u0646 \u062A\u0627\u0631\u064A\u062E"}),r(U,{id:"from",type:"date",class:"mt-1 block w-full",modelValue:i(y),"onUpdate:modelValue":s[15]||(s[15]=o=>b(y)?y.value=o:y=o)},null,8,["modelValue"])])]),e("div",ss,[e("div",null,[r(w,{for:"to",value:"\u062D\u062A\u0649 \u062A\u0627\u0631\u064A\u062E"}),r(U,{id:"to",type:"date",class:"mt-1 block w-full",modelValue:i(x),"onUpdate:modelValue":s[16]||(s[16]=o=>b(x)?x.value=o:x=o)},null,8,["modelValue"])])]),e("div",os,[r(w,{for:"pay",value:"\u0641\u0644\u062A\u0631\u0629"}),e("button",{onClick:s[17]||(s[17]=Z(o=>N(),["prevent"])),class:"px-6 mb-12 py-2 mt-1 font-bold text-white bg-gray-500 rounded",style:{width:"100%"}},[i(I)?(m(),p("span",as,"\u062C\u0627\u0631\u064A \u0627\u0644\u062D\u0641\u0638...")):(m(),p("span",ts,"\u0641\u0644\u062A\u0631\u0629"))])]),e("div",ls,[r(w,{for:"pay",value:"\u0637\u0628\u0627\u0639\u0629"}),e("a",{class:"px-6 mb-12 py-2 mt-1 font-bold text-white bg-orange-500 rounded",style:{display:"block","text-align":"center"},href:`/getIndexAccounting?user_id=${(W=(h=n.value)==null?void 0:h.user)==null?void 0:W.id}&from=${i(y)}&to=${i(x)}&print=1`,target:"_blank"},[i(I)?(m(),p("span",ds,"\u062C\u0627\u0631\u064A \u0627\u0644\u062D\u0641\u0638...")):(m(),p("span",rs,"\u0637\u0628\u0627\u0639\u0629"))],8,ns)])]),k("",!0),e("div",is,[e("div",us,[e("div",null,[r(w,{for:"to",value:`\u062D\u0633\u0627\u0628 ${c.boxes.name} \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631`},null,8,["value"]),r(U,{id:"to",type:"number",disabled:"",class:"mt-1 block w-full",value:n.value.sumInTransactionsUser-n.value.sumOutTransactionsUser},null,8,["value"])])]),e("div",cs,[e("div",null,[r(w,{for:"to",value:`\u062D\u0633\u0627\u0628 ${c.boxes.name} \u0628\u0627\u0644\u062F\u064A\u0646\u0627\u0631 \u0627\u0644\u0639\u0631\u0627\u0642\u064A`},null,8,["value"]),r(U,{id:"to",type:"number",disabled:"",class:"mt-1 block w-full",value:n.value.sumInTransactionsDinarUser-n.value.sumOutTransactionsDinarUser},null,8,["value"])])])]),e("div",ms,[e("table",ps,[gs,e("tbody",null,[(m(!0),p(F,null,ee(i(V),o=>(m(),p("tr",{key:o.id,class:se([o.type!="inUser"?"bg-red-100 dark:bg-red-900":"bg-green-100 dark:bg-green-900","bg-white border-b dark:bg-gray-900 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-600"])},[e("td",fs,A(o.id),1),e("td",vs,A(o==null?void 0:o.created_at.slice(0,19).replace("T","  ")),1),e("th",bs,A(o.description),1),e("td",_s,A(o.amount+" "+o.currency),1),hs],2))),128))])])]),e("div",ys,[r(i(de),{car:a.car,onInfinite:q,identifier:i(S)},null,8,["car","identifier"])])])])])])]}),_:1})],64))}};export{Ss as default};
