import{r as t,o as m,c as P,w as v,a as p,b as e,d as Q,e as M,v as B,h as k,T as X,u as Z,j as n,k as i,F as O,H as ee,l as h,t as A,m as se,f as oe,n as te}from"./app.3afe6a1c.js";import{_ as ae}from"./AuthenticatedLayout.c2058d06.js";/* empty css                                              */import{M as le,_ as ne,a as re,b as de,c as ie}from"./ModalDel.9497ec18.js";import{_ as w,a as U}from"./TextInput.8cf2a39f.js";/* empty css                                                            */import{a as $}from"./index.fd3eab94.js";import{t as ue}from"./trash.ff4cd797.js";import{d as ce,W as me}from"./debounce.a63d6147.js";const pe={key:0,class:"modal-mask"},ge={class:"modal-wrapper max-h-[80vh]"},fe={class:"modal-container"},ve={class:"modal-header"},be={class:"modal-body"},_e={class:"grid grid-cols-2 md:grid-cols-2 lg:grid-cols-2 gap-3 lg:gap-3"},he={className:"mb-4 mx-5"},ye=e("label",{for:"card"},"\u0627\u0644\u062A\u0627\u0631\u064A\u062E",-1),xe={className:"mb-4 mx-5"},we=e("label",{for:"user_id"},"\u0627\u0644\u062D\u0633\u0627\u0628",-1),$e=["value"],ke={className:"mb-4 mx-5"},De=e("label",{for:"balance"},"\u0627\u0644\u0631\u0635\u064A\u062F \u0627\u0644\u062D\u0627\u0644\u064A \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631",-1),Ne=["value"],Ce={className:"mb-4 mx-5"},Ae=e("label",{for:"balance"},"\u0627\u0644\u0631\u0635\u064A\u062F \u0627\u0644\u062D\u0627\u0644\u064A \u0628\u0627\u0644\u062F\u064A\u0646\u0627\u0631 \u0627\u0644\u0639\u0631\u0627\u0642\u064A",-1),Se=["value"],Ve={class:"grid grid-cols-2 md:grid-cols-2 lg:grid-cols-2 gap-3 lg:gap-3"},Me={className:"mb-4 mx-5"},Be=e("label",{for:"amountDollar"},"\u0627\u0644\u0645\u0628\u0644\u063A \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631",-1),Ue={className:"mb-4 mx-5"},Te=e("label",{for:"amountDinar"},"\u0627\u0644\u0645\u0628\u0644\u063A \u0628\u0627\u0644\u062F\u064A\u0646\u0627\u0631 \u0627\u0644\u0639\u0631\u0627\u0642\u064A",-1),Ie={className:"mb-4 mx-5"},Ee=e("label",{for:"note"},"\u0645\u0644\u0627\u062D\u0638\u0629",-1),We={class:"modal-footer my-2"},Fe={class:"flex flex-row"},je={class:"basis-1/2 px-4"},Oe={class:"basis-1/2 px-4"},qe={__name:"ModalAddExpensesWallet",props:{show:Boolean,boxes:Array,sum_transactions:Intl,sum_transactions_dinar:Intl},setup(c){const D=c,r=t({id:D.boxes.id,date:g()});function g(){const u=new Date,l=u.getFullYear(),d=String(u.getMonth()+1).padStart(2,"0"),_=String(u.getDate()).padStart(2,"0");return`${l}-${d}-${_}`}const b=()=>{r.value={id:D.boxes.id,date:g()}};return(u,l)=>(m(),P(X,{name:"modal"},{default:v(()=>[c.show?(m(),p("div",pe,[e("div",ge,[e("div",fe,[e("div",ve,[Q(u.$slots,"header")]),e("div",be,[e("div",_e,[e("div",he,[ye,M(e("input",{id:"card",type:"date",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":l[0]||(l[0]=d=>r.value.date=d)},null,512),[[B,r.value.date]])]),e("div",xe,[we,e("input",{id:"card",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm",value:c.boxes.name},null,8,$e)]),e("div",ke,[De,e("input",{id:"balance",type:"number",value:c.sum_transactions,class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"},null,8,Ne)]),e("div",Ce,[Ae,e("input",{id:"balance",type:"number",value:c.sum_transactions_dinar,class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"},null,8,Se)])]),e("div",Ve,[e("div",Me,[Be,M(e("input",{id:"amountDollar",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":l[1]||(l[1]=d=>r.value.amountDollar=d)},null,512),[[B,r.value.amountDollar]])]),e("div",Ue,[Te,M(e("input",{id:"amountDinar",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":l[2]||(l[2]=d=>r.value.amountDinar=d)},null,512),[[B,r.value.amountDinar]])]),e("div",Ie,[Ee,M(e("input",{id:"note",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":l[3]||(l[3]=d=>r.value.note=d)},null,512),[[B,r.value.note]])])])]),e("div",We,[e("div",Fe,[e("div",je,[e("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:l[4]||(l[4]=d=>{u.$emit("close")})},"\u062A\u0631\u0627\u062C\u0639")]),e("div",Oe,[e("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:l[5]||(l[5]=d=>{u.$emit("a",r.value),b()})},"\u0646\u0639\u0645")])])])])])])):k("",!0)]),_:3}))}};const He=e("h2",{class:"mb-5 dark:text-white text-center"}," \u0647\u0644 \u0645\u062A\u0623\u0643\u062F \u0645\u0646 \u0627\u0644\u062D\u0630\u0641 \u061F ",-1),Le=e("h3",{class:"text-center"},"\u0627\u0644\u0645\u062D\u0627\u0633\u0628\u0629",-1),Re=e("h3",{class:"text-center"}," \u0633\u062D\u0628 \u0645\u0646 \u0627\u0644\u0642\u0627\u0633\u0647",-1),Ye=e("h3",{class:"text-center"},"\u062A\u062D\u0648\u064A\u0644 \u0645\u0646 \u0627\u0644\u062F\u0648\u0644\u0627\u0631 \u0644\u0644\u062F\u064A\u0646\u0627\u0631",-1),ze=e("h3",{class:"text-center"},"\u062A\u062D\u0648\u064A\u0644 \u0645\u0646 \u0627\u0644\u062F\u064A\u0646\u0627\u0631 \u0644\u0644\u062F\u0648\u0644\u0627\u0631",-1),Ge={key:0},Je={id:"alert-2",class:"p-4 mb-4 bg-red-100 rounded-lg dark:bg-red-200 text-center",role:"alert"},Ke={class:"ml-3 font-medium text-red-700 dark:text-red-800"},Pe={class:"max-w-9xl mx-auto sm:px-6 lg:px-8"},Qe={class:"overflow-hidden shadow-sm sm:rounded-lg"},Xe={class:"border-b border-gray-200"},Ze={class:"grid grid-cols-4 md:grid-cols-4 lg:grid-cols-7 gap-3 lg:gap-3"},es={class:"pt-5 print:hidden"},ss={class:"pt-5"},os={class:"pt-5 print:hidden"},ts={class:"px-4"},as={class:"px-4"},ls={className:" mr-5 print:hidden"},ns={key:0},rs={key:1},ds={className:" mr-5 print:hidden"},is=["href"],us={key:0},cs={key:1};const ms={class:"grid grid-cols-2 md:grid-cols-5 lg:grid-cols-5 gap-3 lg:gap-3"},ps={class:"px-4"},gs={class:"px-4"},fs={class:"overflow-x-auto shadow-md mt-5"},vs={class:"w-full text-right text-gray-500 dark:text-gray-400 text-center"},bs=e("thead",{class:"text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center"},[e("tr",{class:"rounded-l-lg mb-2 sm:mb-0"},[e("th",{className:"px-2 py-2"},"\u0631\u0642\u0645 \u0627\u0644\u0648\u0635\u0644"),e("th",{className:"px-2 py-2"},"\u0627\u0644\u062A\u0627\u0631\u064A\u062E"),e("th",{className:"px-2 py-2"},"\u0627\u0644\u0648\u0635\u0641"),e("th",{className:"px-2 py-2"},"\u0627\u0644\u0645\u0628\u0644\u063A"),e("th",{className:"px-2 py-2"},"\u062A\u0646\u0641\u064A\u0630")])],-1),_s={className:"border dark:border-gray-800 text-center px-2 py-1"},hs={className:"border dark:border-gray-800 text-center px-2 py-1"},ys={className:"border dark:border-gray-800 text-center px-2 py-1"},xs={className:"border dark:border-gray-800 text-center px-2 py-1"},ws={className:"border dark:border-gray-800 text-center px-2 py-1"},$s=["onClick"],ks={class:"spaner"},Fs={__name:"Wallet",props:{url:String,boxes:Object},setup(c){const D=c,r=t({});t("");let g=t(!1),b=t(!1),u=t(!1);t(!1);let l=t(!1),d=t(!1),_=t(!1),S=t([]);t(0);let E=t({});t({}),t({});let W=t(!1),y=t(j()),x=t(j());t(0),t(0),t(0),t(0),t(0),t(0),t(0),t(0),t(0),t(0),t(0);let T=t(!1),I=1,q="";const N=()=>{I=0,S.value.length=0,T.value=!T.value};ce(N,500);const H=async a=>{try{const f=(await $.get("/getIndexAccounting",{params:{limit:100,page:I,q,user_id:D.boxes.id,from:y.value,to:x.value}})).data;f.transactions.data.length<100?(S.value.push(...f.transactions.data),a.complete()):(S.value.push(...f.transactions.data),a.loaded()),r.value=f,I++}catch(s){console.log(s)}};function L(){g.value=!0}function Ds(){b.value=!0}function R(){u.value=!0}function Ns(){l.value=!0}function Cs(){d.value=!0}function Y(a){E.value=a,_.value=!0}Z(),t(!1);const C=t(0);function z(a){a.id=D.boxes.id,$.post("/api/receiptArrivedUser",a).then(s=>{g.value=!1,window.location.reload()}).catch(s=>{C.value=s.response.data.errors})}function F(a){$.post("/api/salesDebtUser",a).then(s=>{b.value=!1,u.value=!1,window.location.reload()}).catch(s=>{C.value=s.response.data.errors})}function G(a){$.post("/api/convertDollarDinar",a).then(s=>{N(),l.value=!1}).catch(s=>{C.value=s.response.data.errors})}function J(a){$.post("/api/convertDinarDollar",a).then(s=>{N(),d.value=!1}).catch(s=>{C.value=s.response.data.errors})}function j(){const a=new Date,s=a.getFullYear(),f=String(a.getMonth()+1).padStart(2,"0"),V=String(a.getDate()).padStart(2,"0");return`${s}-${f}-${V}`}function K(a){$.post(`/api/delTransactions?id=${a.id}`).then(s=>{N(),_.value=!1}).catch(s=>{C.value=s.response.data.errors})}return(a,s)=>(m(),p(O,null,[n(i(ee),{title:"Dashboard"}),n(ae,null,{header:v(()=>[]),default:v(()=>{var f,V;return[n(le,{show:!!i(_),formData:i(E),onA:s[0]||(s[0]=o=>K(o)),onClose:s[1]||(s[1]=o=>h(_)?_.value=!1:_=!1)},{header:v(()=>[He]),_:1},8,["show","formData"]),n(ne,{show:!!i(g),onA:s[2]||(s[2]=o=>z(o)),onClose:s[3]||(s[3]=o=>h(g)?g.value=!1:g=!1)},{header:v(()=>[Le]),_:1},8,["show"]),n(re,{show:!!i(b),onA:s[4]||(s[4]=o=>F(o)),onClose:s[5]||(s[5]=o=>h(b)?b.value=!1:b=!1)},{header:v(()=>[]),_:1},8,["show"]),n(qe,{show:!!i(u),boxes:c.boxes,sum_transactions:r.value.sum_transactions,sum_transactions_dinar:r.value.sum_transactions_dinar,onA:s[6]||(s[6]=o=>F(o)),onClose:s[7]||(s[7]=o=>h(u)?u.value=!1:u=!1)},{header:v(()=>[Re]),_:1},8,["show","boxes","sum_transactions","sum_transactions_dinar"]),n(de,{show:!!i(l),boxes:c.boxes,onA:s[8]||(s[8]=o=>G(o)),onClose:s[9]||(s[9]=o=>h(l)?l.value=!1:l=!1)},{header:v(()=>[Ye]),_:1},8,["show","boxes"]),n(ie,{show:!!i(d),boxes:c.boxes,onA:s[10]||(s[10]=o=>J(o)),onClose:s[11]||(s[11]=o=>h(d)?d.value=!1:d=!1)},{header:v(()=>[ze]),_:1},8,["show","boxes"]),a.$page.props.success?(m(),p("div",Ge,[e("div",Je,[e("div",Ke,A(a.$page.props.success),1)])])):k("",!0),e("div",null,[e("div",Pe,[e("div",Qe,[e("div",Xe,[e("div",Ze,[e("div",es,[a.$page.props.auth.user.type_id==1||a.$page.props.auth.user.type_id==2||a.$page.props.auth.user.type_id==5?(m(),p("button",{key:0,style:{width:"100%","margin-top":"4px"},className:"px-4 py-2 text-white bg-green-800 rounded-md focus:outline-none",onClick:s[12]||(s[12]=o=>L())}," \u0648\u0635\u0644 \u0642\u0628\u0636 (\u0623\u0636\u0627\u0641\u0629) ")):k("",!0)]),e("div",ss,[k("",!0)]),e("div",os,[a.$page.props.auth.user.type_id==1||a.$page.props.auth.user.type_id==2||a.$page.props.auth.user.type_id==5?(m(),p("button",{key:0,style:{width:"100%","margin-top":"4px"},className:"px-4 py-2 text-white bg-red-800 rounded-md focus:outline-none",onClick:s[14]||(s[14]=o=>R())}," \u0648\u0635\u0644 \u0635\u0631\u0641 (\u0633\u062D\u0628) ")):k("",!0)]),e("div",ts,[e("div",null,[n(w,{for:"from",value:"\u0645\u0646 \u062A\u0627\u0631\u064A\u062E"}),n(U,{id:"from",type:"date",class:"mt-1 block w-full",modelValue:i(y),"onUpdate:modelValue":s[15]||(s[15]=o=>h(y)?y.value=o:y=o)},null,8,["modelValue"])])]),e("div",as,[e("div",null,[n(w,{for:"to",value:"\u062D\u062A\u0649 \u062A\u0627\u0631\u064A\u062E"}),n(U,{id:"to",type:"date",class:"mt-1 block w-full",modelValue:i(x),"onUpdate:modelValue":s[16]||(s[16]=o=>h(x)?x.value=o:x=o)},null,8,["modelValue"])])]),e("div",ls,[n(w,{for:"pay",value:"\u0641\u0644\u062A\u0631\u0629"}),e("button",{onClick:s[17]||(s[17]=se(o=>N(),["prevent"])),class:"px-6 mb-12 py-2 mt-1 font-bold text-white bg-gray-500 rounded",style:{width:"100%"}},[i(W)?(m(),p("span",rs,"\u062C\u0627\u0631\u064A \u0627\u0644\u062D\u0641\u0638...")):(m(),p("span",ns,"\u0641\u0644\u062A\u0631\u0629"))])]),e("div",ds,[n(w,{for:"pay",value:"\u0637\u0628\u0627\u0639\u0629"}),e("a",{class:"px-6 mb-12 py-2 mt-1 font-bold text-white bg-orange-500 rounded",style:{display:"block","text-align":"center"},href:`/getIndexAccounting?user_id=${(V=(f=r.value)==null?void 0:f.user)==null?void 0:V.id}&from=${i(y)}&to=${i(x)}&print=1`,target:"_blank"},[i(W)?(m(),p("span",cs,"\u062C\u0627\u0631\u064A \u0627\u0644\u062D\u0641\u0638...")):(m(),p("span",us,"\u0637\u0628\u0627\u0639\u0629"))],8,is)])]),k("",!0),e("div",ms,[e("div",ps,[e("div",null,[n(w,{for:"to",value:`\u062D\u0633\u0627\u0628 ${c.boxes.name} \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631`},null,8,["value"]),n(U,{id:"to",type:"number",disabled:"",class:"mt-1 block w-full",value:r.value.sumInTransactionsUser-r.value.sumOutTransactionsUser},null,8,["value"])])]),e("div",gs,[e("div",null,[n(w,{for:"to",value:`\u062D\u0633\u0627\u0628 ${c.boxes.name} \u0628\u0627\u0644\u062F\u064A\u0646\u0627\u0631 \u0627\u0644\u0639\u0631\u0627\u0642\u064A`},null,8,["value"]),n(U,{id:"to",type:"number",disabled:"",class:"mt-1 block w-full",value:r.value.sumInTransactionsDinarUser-r.value.sumOutTransactionsDinarUser},null,8,["value"])])])]),e("div",fs,[e("table",vs,[bs,e("tbody",null,[(m(!0),p(O,null,oe(i(S),o=>(m(),p("tr",{key:o.id,class:te([o.type!="inUser"?"bg-red-100 dark:bg-red-900":"bg-green-100 dark:bg-green-900","bg-white border-b dark:bg-gray-900 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-600"])},[e("td",_s,A(o.id),1),e("td",hs,A(o==null?void 0:o.created_at.slice(0,19).replace("T","  ")),1),e("th",ys,A(o.description),1),e("td",xs,A(o.amount+" "+o.currency),1),e("td",ws,[e("button",{class:"px-1 py-1 text-white bg-rose-500 rounded-md focus:outline-none",onClick:As=>Y(o)},[n(ue)],8,$s)])],2))),128))])])]),e("div",ks,[n(i(me),{car:a.car,onInfinite:H,identifier:i(T)},null,8,["car","identifier"])])])])])])]}),_:1})],64))}};export{Fs as default};
