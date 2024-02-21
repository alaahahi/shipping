import{r as $,o as i,p as x,w as p,c as u,d as t,l as v,m as b,x as g,F as _,g as f,t as d,y as A,e as y,T as k,n as h,s as T,a as w,z as M,b as V}from"./app.cb6f9c6b.js";/* empty css                                                         */import{p as D}from"./print.04fa5952.js";/* empty css                                                            */import{a as N}from"./index.3da26083.js";import{U}from"./Uploader.7a321cd3.js";import{a as B}from"./AuthenticatedLayout.9246acda.js";const E={key:0,class:"modal-mask"},I={class:"modal-wrapper max-h-[80vh]"},S={class:"modal-container"},Z={class:"modal-header"},F={class:"modal-body"},O={class:"grid grid-cols-2 md:grid-cols-2 lg:grid-cols-2 gap-3 lg:gap-3"},j={className:"mb-4 mx-5"},H=t("label",{for:"card"},"\u0627\u0644\u062A\u0627\u0631\u064A\u062E",-1),G={className:"mb-4 mx-5"},z=t("label",{for:"user_id"},"\u0627\u0644\u062D\u0633\u0627\u0628",-1),R=t("option",{selected:"",disabled:""},"\u062A\u062D\u062F\u064A\u062F \u0627\u0644\u062D\u0633\u0627\u0628",-1),L=["value"],Y={className:"mb-4 mx-5"},q=t("label",{for:"balance"},"\u0627\u0644\u0631\u0635\u064A\u062F \u0627\u0644\u062D\u0627\u0644\u064A \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631",-1),J=["value"],K={className:"mb-4 mx-5"},P=t("label",{for:"balance"},"\u0627\u0644\u0631\u0635\u064A\u062F \u0627\u0644\u062D\u0627\u0644\u064A \u0628\u0627\u0644\u062F\u064A\u0646\u0627\u0631 \u0627\u0644\u0639\u0631\u0627\u0642\u064A",-1),Q=["value"],W={class:"grid grid-cols-2 md:grid-cols-2 lg:grid-cols-2 gap-3 lg:gap-3"},X={className:"mb-4 mx-5"},tt=t("label",{for:"amountDollar"},"\u0627\u0644\u0645\u0628\u0644\u063A \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631",-1),et={className:"mb-4 mx-5"},ot=t("label",{for:"amountDinar"},"\u0627\u0644\u0645\u0628\u0644\u063A \u0628\u0627\u0644\u062F\u064A\u0646\u0627\u0631 \u0627\u0644\u0639\u0631\u0627\u0642\u064A",-1),at={className:"mb-4 mx-5"},st=t("label",{for:"note"},"\u0645\u0644\u0627\u062D\u0638\u0629",-1),rt={class:"modal-footer my-2"},dt={class:"flex flex-row"},lt={class:"basis-1/2 px-4"},nt={class:"basis-1/2 px-4"},ba={__name:"ModalAddExpenses",props:{show:Boolean,boxes:Array},setup(s){const n=$({user:{percentage:0},date:m(),amount:0});function m(){const o=new Date,e=o.getFullYear(),a=String(o.getMonth()+1).padStart(2,"0"),r=String(o.getDate()).padStart(2,"0");return`${e}-${a}-${r}`}const l=()=>{n.value={user:{percentage:0},date:m(),amount:0}};return(o,e)=>(i(),x(k,{name:"modal"},{default:p(()=>{var a,r;return[s.show?(i(),u("div",E,[t("div",I,[t("div",S,[t("div",Z,[v(o.$slots,"header")]),t("div",F,[t("div",O,[t("div",j,[H,b(t("input",{id:"card",type:"date",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":e[0]||(e[0]=c=>n.value.date=c)},null,512),[[g,n.value.date]])]),t("div",G,[z,b(t("select",{"onUpdate:modelValue":e[1]||(e[1]=c=>n.value.user=c),id:"user_id",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"},[R,(i(!0),u(_,null,f(s.boxes,(c,C)=>(i(),u("option",{key:C,value:c},d(c==null?void 0:c.name),9,L))),128))],512),[[A,n.value.user]])]),t("div",Y,[q,t("input",{id:"balance",type:"number",value:(a=n.value.user.wallet)==null?void 0:a.balance,class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"},null,8,J)]),t("div",K,[P,t("input",{id:"balance",type:"number",value:(r=n.value.user.wallet)==null?void 0:r.balance_dinar,class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"},null,8,Q)])]),t("div",W,[t("div",X,[tt,b(t("input",{id:"amountDollar",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":e[2]||(e[2]=c=>n.value.amountDollar=c)},null,512),[[g,n.value.amountDollar]])]),t("div",et,[ot,b(t("input",{id:"amountDinar",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":e[3]||(e[3]=c=>n.value.amountDinar=c)},null,512),[[g,n.value.amountDinar]])]),t("div",at,[st,b(t("input",{id:"note",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":e[4]||(e[4]=c=>n.value.note=c)},null,512),[[g,n.value.note]])])])]),t("div",rt,[t("div",dt,[t("div",lt,[t("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:e[5]||(e[5]=c=>{o.$emit("close")})},"\u062A\u0631\u0627\u062C\u0639")]),t("div",nt,[t("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:e[6]||(e[6]=c=>{o.$emit("a",n.value),l()})},"\u0646\u0639\u0645")])])])])])])):y("",!0)]}),_:3}))}},it={key:0,class:"modal-mask"},ct={class:"modal-wrapper max-h-[80vh]"},ut={class:"modal-container dark:bg-gray-900 overflow-auto max-h-[80vh]"},mt={class:"modal-header"},bt={class:"modal-body"},gt={class:"text-sm font-medium text-center text-gray-500 border-b border-gray-200 dark:text-gray-400 dark:border-gray-700"},yt={class:"flex flex-wrap -mb-px"},_t={key:0},ht=t("h1",{class:"text-center dark:text-gray-200 mt-4"}," \u0627\u0636\u0627\u0641\u0629 \u062F\u0641\u0639\u0629",-1),ft={className:"mb-4 mx-5"},xt={class:"dark:text-gray-200",for:"expens_amount"},pt={className:"mb-4 mx-5"},vt={class:"dark:text-gray-200",for:"expenses_id"},kt={className:"mb-4 mx-5"},$t={class:"dark:text-gray-200",for:"expens_amount"},wt=["value"],Nt={className:"mb-4 mx-5"},Dt={class:"dark:text-gray-200",for:"note"},Ct={key:1},At=t("h1",{class:"text-center dark:text-gray-200 mt-4"}," \u0633\u062C\u0644 \u0627\u0644\u062D\u0648\u0644\u0627\u062A",-1),Tt={class:"relative overflow-x-auto shadow-md sm:rounded-lg mt-4 mb-5"},Mt={class:"w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center"},Vt={class:"text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center"},Ut={class:"bg-rose-500 text-gray-100 rounded-l-lg mb-2 sm:mb-0"},Bt=t("th",{className:"px-1 py-2 text-base"},"\u0631\u0642\u0645 \u0627\u0644\u0648\u0635\u0644",-1),Et={className:"px-1 py-2 text-base"},It=t("th",{className:"px-1 py-2 text-base"},"\u0633\u0639\u0631 \u0627\u0644\u0635\u0631\u0641",-1),St=t("th",{className:"px-1 py-2 text-base"},"\u0627\u0644\u0645\u0628\u0644\u063A \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631",-1),Zt=t("th",{className:"px-1 py-2 text-base"},"\u0645\u0644\u0627\u062D\u0638\u0629",-1),Ft={scope:"col",class:"px-1 py-2 text-base print:hidden",style:{width:"250px"}},Ot={class:"text-center px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},jt=t("td",null,null,-1),Ht=t("td",null,null,-1),Gt=t("td",null,null,-1),zt=t("td",null,null,-1),Rt={className:"px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},Lt=["href"],Yt={className:"px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},qt={className:"px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},Jt={className:"px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},Kt={className:"px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},Pt={className:"px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},Qt={className:"px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},Wt=["href"],Xt={class:"modal-footer my-2"},te={class:"flex flex-row"},ee={class:"basis-1/2 px-4"},oe={class:"basis-1/2 px-4"},ae=["disabled"],ga={__name:"ModalAddGenExpenses",props:{show:Boolean,company:Array,color:Array,carModel:Array,name:Array,client:Array,user:Array,expenses:Array,GenExpenses:Array,formData:Object},setup(s){const n=$("add"),m=l=>{n.value=l};return(l,o)=>(i(),x(k,{name:"modal"},{default:p(()=>{var e;return[s.show?(i(),u("div",it,[t("div",ct,[t("div",ut,[t("div",mt,[v(l.$slots,"header")]),t("div",bt,[t("div",gt,[t("ul",yt,[t("li",{class:"mr-2",onClick:o[0]||(o[0]=a=>m("add"))},[t("button",{class:h(["inline-block p-4 border-b-2 border-transparent rounded-t-lg",n.value=="add"?"dark:text-blue-500 dark:border-blue-500":"hover:text-gray-600 hover:border-gray-300"])}," \u0627\u0636\u0627\u0641\u0629 ",2)]),t("li",{class:"mr-2",onClick:o[1]||(o[1]=a=>m("record"))},[t("button",{class:h(["inline-block p-4 border-b-2 border-transparent rounded-t-lg",n.value=="record"?"dark:text-blue-500 dark:border-blue-500":"hover:text-gray-600 hover:border-gray-300"])}," \u0627\u0644\u0633\u062C\u0644 ",2)])])]),n.value=="add"?(i(),u("div",_t,[t("div",null,[ht,t("div",ft,[t("label",xt,d(l.$t("amount")),1),b(t("input",{id:"expens_amount",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":o[2]||(o[2]=a=>s.formData.amount=a)},null,512),[[g,s.formData.amount]])]),t("div",pt,[t("label",vt,d(l.$t("factor")),1),b(t("input",{id:"note_expens",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":o[3]||(o[3]=a=>s.formData.factor=a)},null,512),[[g,s.formData.factor]])]),t("div",kt,[t("label",$t,d(l.$t("result")),1),t("input",{id:"expens_amount",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900",value:(s.formData.amount/s.formData.factor).toFixed(1)},null,8,wt)]),t("div",Nt,[t("label",Dt,d(l.$t("note")),1),b(t("input",{id:"note",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":o[4]||(o[4]=a=>s.formData.note=a)},null,512),[[g,s.formData.note]])])])])):(i(),u("div",Ct,[At,t("div",Tt,[t("table",Mt,[t("thead",Vt,[t("tr",Ut,[Bt,t("th",Et,d(l.$t("date")),1),It,St,Zt,t("th",Ft,d(l.$t("execute")),1)])]),t("tbody",null,[t("tr",Ot,[jt,Ht,Gt,zt,t("td",Rt,[t("a",{target:"_blank",style:{display:"inline-flex"},href:`/api/getIndexAccountsSelas?user_id=${(e=s.GenExpenses[0])==null?void 0:e.user_id}&print=5`,tabIndex:"1",class:"px-4 py-1 text-white m-1 bg-blue-500 rounded"},[T(" \u062C\u0645\u064A\u0639 \u0627\u0644\u062F\u0641\u0639\u0627\u062A "),w(D)],8,Lt)])]),(i(!0),u(_,null,f(s.GenExpenses,a=>{var r;return i(),u("tr",{key:a.id,class:"text-center"},[t("td",Yt,d(a.id),1),t("td",qt,d((r=a==null?void 0:a.created_at)==null?void 0:r.slice(0,19).replace("T"," ")),1),t("td",Jt,d(a.factor),1),t("td",Kt,d(a.amount),1),t("td",Pt,d(a.reason),1),t("td",Qt,[t("a",{target:"_blank",style:{display:"inline-flex"},href:`/api/getIndexAccountsSelas?user_id=${a.user_id}&print=3&transactions_id=${a.transaction_id}`,tabIndex:"1",class:"px-4 py-1 text-white m-1 bg-green-500 rounded"},[w(D)],8,Wt)])])}),128))])])])]))]),t("div",Xt,[t("div",te,[t("div",ee,[t("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:o[5]||(o[5]=a=>{l.$emit("close"),n.value="add"})},d(l.$t("cancel")),1)]),t("div",oe,[t("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:o[6]||(o[6]=a=>{l.$emit("a",s.formData),s.formData=""}),disabled:!s.formData.amount},d(l.$t("yes")),9,ae)])])])])])])):y("",!0)]}),_:3}))}};const se={key:0,class:"modal-mask"},re={class:"modal-wrapper max-h-[80vh]"},de={class:"modal-container dark:bg-gray-900 overflow-auto max-h-[80vh]"},le={class:"modal-header"},ne={class:"modal-body"},ie={class:"text-sm font-medium text-center text-gray-500 border-b border-gray-200 dark:text-gray-400 dark:border-gray-700"},ce={class:"flex flex-wrap -mb-px"},ue={key:0},me=t("h1",{class:"text-center dark:text-gray-200 mt-4"}," \u0627\u0636\u0627\u0641\u0629 \u062F\u0641\u0639\u0629",-1),be={className:"mb-4 mx-5"},ge=t("label",{class:"dark:text-gray-200",for:"expens_amount"},"\u0627\u0644\u0645\u0628\u0644\u063A \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631",-1),ye={className:"mb-4 mx-5"},_e={class:"dark:text-gray-200",for:"note"},he={key:1},fe=t("h1",{class:"text-center dark:text-gray-200 mt-4"}," \u0633\u062C\u0644 \u0627\u0644\u062D\u0648\u0644\u0627\u062A",-1),xe={class:"relative overflow-x-auto shadow-md sm:rounded-lg mt-4 mb-5"},pe={class:"w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center"},ve={class:"text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center"},ke={class:"bg-rose-500 text-gray-100 rounded-l-lg mb-2 sm:mb-0"},$e=t("th",{className:"px-1 py-2 text-base"},"\u0631\u0642\u0645 \u0627\u0644\u0648\u0635\u0644",-1),we={className:"px-1 py-2 text-base"},Ne=t("th",{className:"px-1 py-2 text-base"},"\u0627\u0644\u0645\u0628\u0644\u063A \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631",-1),De=t("th",{className:"px-1 py-2 text-base"},"\u0623\u062C\u0648\u0631 \u0627\u0644\u062D\u0648\u0644\u0627\u062A",-1),Ce=t("th",{className:"px-1 py-2 text-base"},"\u0627\u0644\u0645\u0628\u0644\u063A \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631 \u0627\u0644\u0635\u0627\u0641\u064A",-1),Ae=t("th",{className:"px-1 py-2 text-base"},"\u0645\u0644\u0627\u062D\u0638\u0629",-1),Te=t("th",{className:"px-1 py-2 text-base"},"\u0627\u0644\u062D\u0627\u0644\u0629",-1),Me={className:"px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},Ve={className:"px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},Ue={className:"px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},Be={className:"px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},Ee={className:"px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},Ie={className:"px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},Se={className:"px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},Ze={class:"modal-footer my-2"},Fe={class:"flex flex-row"},Oe={class:"basis-1/2 px-4"},je={class:"basis-1/2 px-4"},He=["disabled"],ya={__name:"ModalAddExpensesToMainBransh",props:{show:Boolean,user:Array,allTransfers:Array,formData:Object},setup(s){const n=$("add"),m=l=>{n.value=l};return(l,o)=>(i(),x(k,{name:"modal"},{default:p(()=>[s.show?(i(),u("div",se,[t("div",re,[t("div",de,[t("div",le,[v(l.$slots,"header")]),t("div",ne,[t("div",ie,[t("ul",ce,[t("li",{class:"mr-2",onClick:o[0]||(o[0]=e=>m("add"))},[t("button",{class:h(["inline-block p-4 border-b-2 border-transparent rounded-t-lg",n.value=="add"?"dark:text-blue-500 dark:border-blue-500":"hover:text-gray-600 hover:border-gray-300"])}," \u0627\u0636\u0627\u0641\u0629 ",2)]),t("li",{class:"mr-2",onClick:o[1]||(o[1]=e=>m("record"))},[t("button",{class:h(["inline-block p-4 border-b-2 border-transparent rounded-t-lg",n.value=="record"?"dark:text-blue-500 dark:border-blue-500":"hover:text-gray-600 hover:border-gray-300"])}," \u0627\u0644\u0633\u062C\u0644 ",2)])])]),n.value=="add"?(i(),u("div",ue,[t("div",null,[me,t("div",be,[ge,b(t("input",{id:"expens_amount",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":o[2]||(o[2]=e=>s.formData.amount=e)},null,512),[[g,s.formData.amount]])]),t("div",ye,[t("label",_e,d(l.$t("note")),1),b(t("input",{id:"note",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":o[3]||(o[3]=e=>s.formData.note=e)},null,512),[[g,s.formData.note]])])])])):(i(),u("div",he,[fe,t("div",xe,[t("table",pe,[t("thead",ve,[t("tr",ke,[$e,t("th",we,d(l.$t("date")),1),Ne,De,Ce,Ae,Te])]),t("tbody",null,[(i(!0),u(_,null,f(s.allTransfers,e=>{var a;return i(),u("tr",{key:e.id,class:"text-center"},[t("td",Me,d(e.id),1),t("td",Ve,d((a=e==null?void 0:e.created_at)==null?void 0:a.slice(0,19).replace("T"," ")),1),t("td",Ue,d(e.amount),1),t("td",Be,d(e.fee),1),t("td",Ee,d(e.amount-e.fee),1),t("td",Ie,d(e.note),1),t("td",Se,d(e.stauts),1)])}),128))])])])]))]),t("div",Ze,[t("div",Fe,[t("div",Oe,[t("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:o[4]||(o[4]=e=>{l.$emit("close"),n.value="add"})},d(l.$t("cancel")),1)]),t("div",je,[t("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:o[5]||(o[5]=e=>{l.$emit("a",s.formData),s.formData=""}),disabled:!s.formData.amount},d(l.$t("yes")),9,He)])])])])])])):y("",!0)]),_:3}))}};const Ge={key:0,class:"modal-mask"},ze={class:"modal-wrapper max-h-[80vh]"},Re={class:"modal-container dark:bg-gray-900 overflow-auto max-h-[80vh]"},Le={class:"modal-header"},Ye={class:"modal-body"},qe={class:"text-sm font-medium text-center text-gray-500 border-b border-gray-200 dark:text-gray-400 dark:border-gray-700"},Je={class:"flex flex-wrap -mb-px"},Ke={key:0},Pe=t("h2",{class:"text-center py-3"},"\u0637\u0644\u0628\u0627\u062A \u0642\u064A\u062F \u0627\u0644\u062A\u062D\u0648\u064A\u0644",-1),Qe={key:0,id:"alert-additional-content-4 my-3",class:"p-4 mb-4 text-yellow-800 border border-yellow-300 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300 dark:border-yellow-800",role:"alert"},We={class:"flex items-center"},Xe=t("svg",{class:"flex-shrink-0 w-4 h-4 me-2","aria-hidden":"true",xmlns:"http://www.w3.org/2000/svg",fill:"currentColor",viewBox:"0 0 20 20"},[t("path",{d:"M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"})],-1),to=t("span",{class:"sr-only"},"Info",-1),eo=t("h3",{class:"text-lg font-medium px-2"},"\u0637\u0644\u0628 \u062D\u0648\u0627\u0644\u0629 \u0645\u0646 \u0641\u0631\u0639 \u0643\u0631\u0643\u0648\u0643",-1),oo={class:"text-lg font-medium"},ao={class:"mt-2 mb-4 text-sm px-4"},so=t("label",{class:"dark:text-gray-200 px-4",for:"note"},"\u0623\u062C\u0648\u0631 \u0627\u0644\u062D\u0648\u0627\u0644\u0629 \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631",-1),ro=["onUpdate:modelValue"],lo=["onUpdate:modelValue"],no={key:0,class:"flex items-center py-3"},io=t("svg",{class:"flex-shrink-0 w-4 h-4 me-2","aria-hidden":"true",xmlns:"http://www.w3.org/2000/svg",fill:"currentColor",viewBox:"0 0 20 20"},[t("path",{d:"M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"})],-1),co=t("span",{class:"sr-only"},"Info",-1),uo=t("h3",{class:"text-lg font-medium px-2"}," \u0635\u0627\u0641\u064A \u0627\u0644\u062D\u0648\u0627\u0644\u0629 \u0628\u0639\u062F \u062E\u0635\u0645 \u0623\u062C\u0631 \u0627\u0644\u062A\u062D\u0648\u064A\u0644",-1),mo={class:"text-lg font-medium"},bo={key:1,class:"flex mt-5"},go=["onClick"],yo=["onClick","disabled"],_o={key:1},ho=t("h1",{class:"text-center dark:text-gray-200 mt-4"}," \u0633\u062C\u0644 \u0627\u0644\u062D\u0648\u0644\u0627\u062A",-1),fo={class:"relative overflow-x-auto shadow-md sm:rounded-lg mt-4 mb-5"},xo={class:"w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center"},po={class:"text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center"},vo={class:"bg-rose-500 text-gray-100 rounded-l-lg mb-2 sm:mb-0"},ko=t("th",{className:"px-1 py-2 text-base"},"\u0631\u0642\u0645 \u0627\u0644\u0648\u0635\u0644",-1),$o={className:"px-1 py-2 text-base"},wo=t("th",{className:"px-1 py-2 text-base"},"\u0627\u0644\u0645\u0628\u0644\u063A \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631",-1),No=t("th",{className:"px-1 py-2 text-base"},"\u0623\u062C\u0648\u0631 \u0627\u0644\u062D\u0648\u0644\u0627\u062A",-1),Do=t("th",{className:"px-1 py-2 text-base"},"\u0627\u0644\u0645\u0628\u0644\u063A \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631 \u0627\u0644\u0635\u0627\u0641\u064A",-1),Co=t("th",{className:"px-1 py-2 text-base"},"\u0645\u0644\u0627\u062D\u0638\u0629",-1),Ao=t("th",{className:"px-1 py-2 text-base"},"\u0627\u0644\u062D\u0627\u0644\u0629",-1),To={className:"px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},Mo={className:"px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},Vo={className:"px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},Uo={className:"px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},Bo={className:"px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},Eo={className:"px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},Io={className:"px-4 py-2 border dark:border-gray-800 dark:text-gray-200"},So={class:"modal-footer my-2"},Zo={class:"flex flex-row"},Fo={class:"basis-1/2 px-4"},Oo={class:"basis-1/2 px-4"},jo=["disabled"],_a={__name:"ModalExpensesFromOtherBransh",props:{show:Boolean,user:Array,allTransfers:Array,formData:Object},setup(s){const n=$("add"),m=e=>{n.value=e};function l(e){N.post("/api/confirmTransfers",e).then(a=>{window.location.reload()}).catch(a=>{})}function o(e){N.post("/api/cancelTransfers",e).then(a=>{window.location.reload()}).catch(a=>{})}return(e,a)=>(i(),x(k,{name:"modal"},{default:p(()=>[s.show?(i(),u("div",Ge,[t("div",ze,[t("div",Re,[t("div",Le,[v(e.$slots,"header")]),t("div",Ye,[t("div",qe,[t("ul",Je,[t("li",{class:"mr-2",onClick:a[0]||(a[0]=r=>m("add"))},[t("button",{class:h(["inline-block p-4 border-b-2 border-transparent rounded-t-lg",n.value=="add"?"dark:text-blue-500 dark:border-blue-500":"hover:text-gray-600 hover:border-gray-300"])}," \u0637\u0644\u0628\u0627\u062A \u0642\u064A\u062F \u0627\u0644\u062A\u062D\u0648\u064A\u0644 ",2)]),t("li",{class:"mr-2",onClick:a[1]||(a[1]=r=>m("record"))},[t("button",{class:h(["inline-block p-4 border-b-2 border-transparent rounded-t-lg",n.value=="record"?"dark:text-blue-500 dark:border-blue-500":"hover:text-gray-600 hover:border-gray-300"])}," \u0627\u0644\u0633\u062C\u0644 ",2)])])]),n.value=="add"?(i(),u("div",Ke,[Pe,(i(!0),u(_,null,f(s.allTransfers,r=>(i(),u(_,{key:r.id},[r.stauts!="\u062A\u0645 \u0627\u0644\u0623\u0633\u062A\u0644\u0627\u0645"?(i(),u("div",Qe,[t("div",We,[Xe,to,eo,t("h3",oo," \u0645\u0628\u0644\u063A "+d(r.amount)+" \u062F\u0648\u0644\u0627\u0631 ",1)]),t("div",ao,d(r.note),1),so,b(t("input",{type:"number",class:"mt-3 mx-3 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":c=>r.inputValue=c},null,8,ro),[[g,r.inputValue]]),b(t("input",{type:"text",class:"mt-3 mx-3 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":c=>r.receiver=c},null,8,lo),[[g,r.receiver]]),r.inputValue?(i(),u("div",no,[io,co,uo,t("h3",mo," \u0645\u0628\u0644\u063A "+d(r.amount-r.inputValue)+" \u062F\u0648\u0644\u0627\u0631 ",1)])):y("",!0),r.inputValue?(i(),u("div",bo,[t("button",{onClick:c=>l(r),type:"button",class:"mx-2 text-white bg-yellow-800 hover:bg-yellow-900 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-xs px-3 py-1.5 me-2 text-center inline-flex items-center dark:bg-yellow-300 dark:text-gray-800 dark:hover:bg-yellow-400 dark:focus:ring-yellow-800"}," \u062A\u0623\u0643\u064A\u062F \u0627\u0644\u062D\u0648\u0627\u0644\u0627\u062A ",8,go),t("button",{onClick:c=>o(r),type:"button",class:"text-yellow-800 bg-transparent border border-yellow-800 hover:bg-yellow-900 hover:text-white focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-xs px-3 py-1.5 text-center dark:hover:bg-yellow-300 dark:border-yellow-300 dark:text-yellow-300 dark:hover:text-gray-800 dark:focus:ring-yellow-800","data-dismiss-target":"#alert-additional-content-4","aria-label":"Close",disabled:!s.formData.fee}," \u0625\u0644\u063A\u0627\u0621 ",8,yo)])):y("",!0)])):y("",!0)],64))),128))])):(i(),u("div",_o,[ho,t("div",fo,[t("table",xo,[t("thead",po,[t("tr",vo,[ko,t("th",$o,d(e.$t("date")),1),wo,No,Do,Co,Ao])]),t("tbody",null,[(i(!0),u(_,null,f(s.allTransfers,r=>{var c;return i(),u("tr",{key:r.id,class:"text-center"},[t("td",To,d(r.id),1),t("td",Mo,d((c=r==null?void 0:r.created_at)==null?void 0:c.slice(0,19).replace("T"," ")),1),t("td",Vo,d(r.amount),1),t("td",Uo,d(r.fee),1),t("td",Bo,d(r.amount-r.fee),1),t("td",Eo,d(r.note),1),t("td",Io,d(r.stauts),1)])}),128))])])])]))]),t("div",So,[t("div",Zo,[t("div",Fo,[t("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:a[2]||(a[2]=r=>{e.$emit("close"),n.value="add"})},d(e.$t("cancel")),1)]),t("div",Oo,[t("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:a[3]||(a[3]=r=>{e.$emit("a",s.formData),s.formData=""}),disabled:!s.formData.amount},d(e.$t("yes")),9,jo)])])])])])])):y("",!0)]),_:3}))}};const Ho={key:0,class:"modal-mask"},Go={class:"modal-wrapper max-h-[80vh]"},zo={class:"modal-container dark:bg-gray-900 overflow-auto max-h-[80vh]"},Ro={class:"modal-header"},Lo={class:"dark:text-gray-300 py-4 text-center"},Yo={class:"grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-1 lg:gap-2"},qo={class:"mb-4"},Jo=t("label",{class:"form-label"},"\u0627\u0644\u0635\u0648\u0631",-1),Ko={class:"mt-3"},Po={key:0,class:"text-danger"},Qo={class:"modal-footer my-2"},Wo={class:"flex flex-row"},Xo={class:"basis-1/2 px-4"},ta={class:"basis-1/2 px-4"},ha={__name:"ModalUploader",props:{show:Boolean,formData:Object,client:Array},setup(s){const n=M();function m(l){N.get("/api/TransactionsImageDel?name="+l.name).then(o=>{n.success("\u062A\u0645  \u062D\u0630\u0641 \u0627\u0644\u0635\u0648\u0631\u0629 \u0628\u0646\u062C\u0627\u062D",{timeout:5e3,position:"bottom-right",rtl:!0})}).catch(o=>{console.error(o)})}return(l,o)=>(i(),x(k,{name:"modal"},{default:p(()=>{var e,a,r;return[s.show?(i(),u("div",Ho,[t("div",Go,[t("div",zo,[t("div",Ro,[v(l.$slots,"header")]),t("h2",Lo,d(s.formData.description),1),t("div",Yo,[t("div",qo,[Jo,t("div",Ko,[w(V(U),{server:"/api/TransactionsUpload?transactionsId="+s.formData.id,"is-invalid":!!((e=l.errors)!=null&&e.media),onChange:l.changeMedia,onInitMedia:l.media,location:"/public/uploadsResized",media:s.formData.transactions_images,onAdd:l.addMedia,onRemove:m},null,8,["server","is-invalid","onChange","onInitMedia","media","onAdd"])]),(a=l.errors)!=null&&a.media?(i(),u("p",Po,d((r=l.errors)==null?void 0:r.media[0]),1)):y("",!0)])]),t("div",Qo,[t("div",Wo,[t("div",Xo,[t("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:o[0]||(o[0]=c=>{l.$emit("close")})},"\u062A\u0631\u0627\u062C\u0639")]),t("div",ta,[t("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:o[1]||(o[1]=c=>{l.$emit("a",s.formData)})},"\u0646\u0639\u0645")])])])])])])):y("",!0)]}),_:3}))}},ea={},oa={xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24","stroke-width":"1.5",stroke:"currentColor",class:"w-6 h-6"},aa=t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"},null,-1),sa=[aa];function ra(s,n){return i(),u("svg",oa,sa)}const fa=B(ea,[["render",ra]]);export{ha as _,ga as a,ya as b,_a as c,ba as d,fa as i};