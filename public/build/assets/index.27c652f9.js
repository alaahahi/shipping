import{x as J,r as n,y as K,a as r,g as u,j as c,w as h,k,F as $,o as l,H as O,b as s,n as D,e as P,v as Q,i as A,h as E,f as N,t as a}from"./app.0f94b091.js";import{_ as X}from"./AuthenticatedLayout.d903610c.js";import"./vue-tailwind-datepicker.a7575db8.js";import{M as Y,a as Z,_ as S,b as ee}from"./ModalArchiveCarBack.c19ed9ad.js";import{M as se}from"./ModalDelCar.8994c6fb.js";import{a as y}from"./index.82b0f3f2.js";import{d as te}from"./debounce.75ef1d44.js";/* empty css                                                                 */import"./VueSearchSelect.e733923e.js";/* empty css                                                            */import"./print.8056c9f5.js";import"./trash.d88b0f0f.js";/* empty css                                                    */const oe=s("h2",{class:"mb-5 dark:text-gray-400 text-center"}," \u0647\u0644 \u0645\u062A\u0623\u0643\u062F \u0645\u0646 \u062D\u0630\u0641 \u0627\u0644\u0633\u064A\u0627\u0631\u0629 \u061F ",-1),ae={key:0,class:"py-2"},re={class:"sm:px-6 lg:px-8 text-sm font-medium text-center text-gray-500 rounded-lg flex dark:divide-gray-700 dark:text-gray-400"},le={class:"w-full"},ne={class:"w-full"},ie={class:"max-w-9xl mx-auto sm:px-6 lg:px-8"},ce={class:"bg-white overflow-hidden shadow-sm"},de={class:"p-6 dark:bg-gray-900"},pe={class:"flex flex-col"},ue=s("br",null,null,-1),me=["disabled"],he={key:0},ye={key:1},xe={key:0,class:"text-center mt-4 text-gray-500"},_e=s("span",{class:"loader"},null,-1),fe=s("p",null,"\u062C\u0627\u0631\u064A \u0627\u0644\u0628\u062D\u062B\u060C \u064A\u0631\u062C\u0649 \u0627\u0644\u0627\u0646\u062A\u0638\u0627\u0631...",-1),ve=[_e,fe],ge=s("h3",{class:"text-center h3 py-3"},"\u0627\u0644\u0646\u062A\u0627\u0626\u062C",-1),be={key:1},ke=s("h3",{class:"mt-8 text-lg font-semibold text-red-500"},"\u0627\u0644\u0623\u0631\u0642\u0627\u0645 \u0627\u0644\u062A\u064A \u0644\u064A\u0633 \u0644\u0647\u0627 \u0646\u062A\u0627\u0626\u062C:",-1),we={class:"text-sm text-gray-300 mb-2"},Ce=s("span",{class:"font-bold"},"\u0631\u0642\u0645 \u0627\u0644\u0634\u0627\u0635\u064A:",-1),$e={key:2},De={class:"text-lg font-semibold text-gray-100"},Ae={class:"text-sm text-gray-300 mb-2"},Me=s("span",{class:"font-bold"},"\u0631\u0642\u0645 \u0627\u0644\u0634\u0627\u0635\u064A:",-1),Ve={key:0},Be={class:"text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center"},Ee={scope:"col",class:"px-3 py-2 sm:px-4 sm:py-2"},Ne={scope:"col",class:"px-3 py-2 sm:px-4 sm:py-2"},Fe={scope:"col",class:"px-3 py-2 sm:px-4 sm:py-2"},Ie={scope:"col",class:"px-3 py-2 sm:px-4 sm:py-2"},Re={scope:"col",class:"px-3 py-2 sm:px-4 sm:py-2"},Te={scope:"col",class:"px-3 py-2 sm:px-4 sm:py-2"},We={scope:"col",class:"px-3 py-2 sm:px-4 sm:py-2"},He={scope:"col",class:"px-3 py-2 sm:px-4 sm:py-2"},Le=s("th",{scope:"col",class:"px-3 py-2 sm:px-4 sm:py-2"},"\u0641\u0631\u0639",-1),je={class:"px-3 py-2 sm:px-4 sm:py-2 text-center"},ze={class:"px-3 py-2 sm:px-4 sm:py-2 text-center"},Ue={class:"px-3 py-2 sm:px-4 sm:py-2 text-center"},qe={class:"px-3 py-2 sm:px-4 sm:py-2 text-center"},Ge={class:"px-3 py-2 sm:px-4 sm:py-2 text-center"},Je={class:"px-3 py-2 sm:px-4 sm:py-2 text-center"},Ke={class:"px-3 py-2 sm:px-4 sm:py-2 text-center"},Oe={class:"px-3 py-2 sm:px-4 sm:py-2 text-center"},Pe={class:"px-3 py-2 sm:px-4 sm:py-2 text-center"},Qe={key:1},Xe={class:"text-red-500"},Ye={class:"font-bold"},Ze=s("div",null,null,-1),us={__name:"index",props:{client:Array},setup(T){J();const x=n({}),_=K();n("");let f=n(!1),v=n(!1),d=n(!1),m=n(!1),g=n(!1),b=n(!1),W=n([]);const w=n(!0);let F=n(!1);const p=()=>{W.value.length=0,F.value=!F.value};te(p,500);function H(o){y.post("/api/confirmExpensesCar",o).then(e=>{d.value=!1,_.success("\u062A\u0645 \u0625\u0636\u0627\u0641\u0629 \u0627\u0644\u0633\u064A\u0627\u0631\u0629 \u0628\u0646\u062C\u0627\u062D ",{timeout:3e3,position:"bottom-right",rtl:!0}),p()}).catch(e=>{console.error(e)})}function L(o){y.post("/api/addCarFavorite",o).then(e=>{d.value=!1,_.success("\u062A\u0645 \u0625\u0636\u0627\u0641\u0629 \u0627\u0644\u0633\u064A\u0627\u0631\u0629 \u0628\u0646\u062C\u0627\u062D ",{timeout:3e3,position:"bottom-right",rtl:!0}),p(),v.value=!1}).catch(e=>{console.error(e)})}function j(o){y.post("/api/confirmArchiveCar",o).then(e=>{d.value=!1,_.success("\u062A\u0645 \u0646\u0642\u0644 \u0627\u0644\u0633\u064A\u0627\u0631\u0629 \u0628\u0646\u062C\u0627\u062D ",{timeout:3e3,position:"bottom-right",rtl:!0}),p(),m.value=!1}).catch(e=>{console.error(e)})}function z(o){y.post("/api/confirmArchiveCarBack",o).then(e=>{g.value=!1,_.success("\u062A\u0645 \u0646\u0642\u0644 \u0627\u0644\u0633\u064A\u0627\u0631\u0629 \u0628\u0646\u062C\u0627\u062D ",{timeout:3e3,position:"bottom-right",rtl:!0}),p(),m.value=!1}).catch(e=>{console.error(e)})}function I(o){w.value=o,p()}function U(o){y.post("/api/confirmDelCarFav",o).then(e=>{b.value=!1,_.success("\u062D\u0630\u0641 \u0627\u0644\u0633\u064A\u0627\u0631\u0629 \u0628\u0646\u062C\u0627\u062D",{timeout:3e3,position:"bottom-right",rtl:!0}),p()}).catch(e=>{console.error(e)})}const M=n(""),V=n([]),B=n([]),q=async()=>{f.value=!0;const o=M.value.split(`
`).map(e=>e.trim());try{const e=await y.post("/api/search-vins",{vins:o});V.value=e.data.results,B.value=e.data.noResultsVINs,f.value=!1}catch(e){console.error("\u062E\u0637\u0623 \u0641\u064A \u0627\u0644\u0628\u062D\u062B:",e)}};return(o,e)=>(l(),r($,null,[u(c(O),{title:"Dashboard"}),u(Y,{formData:x.value,show:!!c(m),onA:e[0]||(e[0]=t=>j(t)),onClose:e[1]||(e[1]=t=>k(m)?m.value=!1:m=!1)},{header:h(()=>[]),_:1},8,["formData","show"]),u(Z,{formData:x.value,show:!!c(g),onA:e[2]||(e[2]=t=>z(t)),onClose:e[3]||(e[3]=t=>k(g)?g.value=!1:g=!1)},{header:h(()=>[]),_:1},8,["formData","show"]),u(S,{formData:x.value,show:!!c(v),client:T.client,onA:e[4]||(e[4]=t=>L(t)),onClose:e[5]||(e[5]=t=>k(v)?v.value=!1:v=!1)},{header:h(()=>[]),_:1},8,["formData","show","client"]),u(ee,{formData:x.value,show:!!c(d),currentWork:w.value,onA:e[6]||(e[6]=t=>H(t)),onClose:e[7]||(e[7]=t=>k(d)?d.value=!1:d=!1)},{header:h(()=>[]),_:1},8,["formData","show","currentWork"]),u(se,{show:!!c(b),formData:x.value,onA:e[8]||(e[8]=t=>U(t)),onClose:e[9]||(e[9]=t=>k(b)?b.value=!1:b=!1)},{header:h(()=>[oe]),_:1},8,["show","formData"]),u(X,null,{default:h(()=>[o.$page.props.auth.user.type_id==1||o.$page.props.auth.user.type_id==7?(l(),r("div",ae,[s("ul",re,[s("li",le,[s("button",{onClick:e[10]||(e[10]=t=>I(!0)),class:D(["inline-block w-full p-4 border-r border-gray-200 dark:border-gray-700 hover:text-gray-700 hover:bg-gray-50 focus:outline-none dark:text-white",w.value?"bg-white  dark:bg-gray-900":"dark:bg-gray-800 dark:hover:bg-gray-700"])},"\u0642\u064A\u062F \u0627\u0644\u0639\u0645\u0644 ",2)]),s("li",ne,[s("button",{onClick:e[11]||(e[11]=t=>I(!1)),class:D(["inline-block w-full p-4 border-r border-gray-200 dark:border-gray-700 hover:text-gray-700 hover:bg-gray-50 focus:outline-none dark:text-white",w.value?"dark:bg-gray-800 dark:hover:bg-gray-700":"bg-white  dark:bg-gray-900"])},"\u0627\u0644\u0633\u064A\u0627\u0631\u0629 \u0627\u0644\u0645\u0643\u062A\u0645\u0644\u0629",2)])]),s("div",ie,[s("div",ce,[s("div",de,[s("div",pe,[P(s("textarea",{"onUpdate:modelValue":e[12]||(e[12]=t=>M.value=t),placeholder:"\u0623\u062F\u062E\u0644 \u0627\u0644\u0623\u0643\u0648\u0627\u062F \u0647\u0646\u0627",style:{height:"500px"}},null,512),[[Q,M.value]]),ue,s("button",{class:"px-6 mb-12 mx-2 py-2 font-bold text-white bg-green-500 rounded",onClick:q,disabled:c(f)},[c(f)?(l(),r("span",ye,"\u062C\u0627\u0631\u064A \u0627\u0644\u062A\u062D\u0645\u064A\u0644...")):(l(),r("span",he,"\u062A\u0634\u063A\u064A\u0644"))],8,me),c(f)?(l(),r("div",xe,ve)):A("",!0),ge,B.value.length?(l(),r("div",be,[ke,s("ul",null,[(l(!0),r($,null,E(B.value,(t,C)=>(l(),r("li",{key:C},[s("p",we,[Ce,N(" "+a(t),1)])]))),128))])])):A("",!0),V.value.length?(l(),r("div",$e,[(l(!0),r($,null,E(V.value,(t,C)=>(l(),r("div",{key:C},[s("h4",De,"\u0628\u062D\u062B "+a(C+1),1),s("p",Ae,[Me,N(" "+a(t.vin||"\u063A\u064A\u0631 \u0645\u0639\u0631\u0648\u0641"),1)]),t.cars&&t.cars.length?(l(),r("div",Ve,[s("table",{class:D([t.cars.length>1?"bg-yellow-200 text-gray-800":"bg-emerald-200 text-gray-800","w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center divide-y divide-gray-200 dark:divide-gray-800"])},[s("thead",Be,[s("tr",{class:D(t.cars.length>1?"bg-yellow-600 text-gray-100":"bg-emerald-600 text-gray-100")},[s("th",Ee,a(o.$t("date")),1),s("th",Ne,a(o.$t("car_owner")),1),s("th",Fe,a(o.$t("car_type")),1),s("th",Ie,a(o.$t("year")),1),s("th",Re,a(o.$t("color")),1),s("th",Te,a(o.$t("vin")),1),s("th",We,a(o.$t("car_number")),1),s("th",He,a(o.$t("note")),1),Le],2)]),s("tbody",null,[(l(!0),r($,null,E(t.cars,(i,G)=>{var R;return l(),r("tr",{key:G,class:"bg-white dark:bg-gray-800"},[s("td",je,a(i.date),1),s("td",ze,a((R=i.client)==null?void 0:R.name),1),s("td",Ue,a(i.car_type),1),s("td",qe,a(i.year),1),s("td",Ge,a(i.car_color),1),s("td",Je,a(i.vin),1),s("td",Ke,a(i.car_number),1),s("td",Oe,a(i.note),1),s("td",Pe,a(i.owner_id==1?"\u0627\u0631\u0628\u064A\u0644":"\u0643\u0631\u0643\u0648\u0643"),1)])}),128))])],2)])):(l(),r("div",Qe,[s("p",Xe,[N(" \u0644\u0627 \u062A\u0648\u062C\u062F \u0646\u062A\u0627\u0626\u062C \u0644\u0647\u0630\u0627 \u0627\u0644\u0631\u0642\u0645: "),s("span",Ye,a(t.vin||"\u063A\u064A\u0631 \u0645\u0639\u0631\u0648\u0641"),1)])]))]))),128))])):A("",!0)])])])])])):A("",!0),Ze]),_:1})],64))}};export{us as default};
