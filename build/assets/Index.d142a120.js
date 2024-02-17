import{o as i,p as P,w as h,c,d as e,l as V,t as r,m as y,F as w,g as $,y as Q,x as _,e as A,T as q,r as u,u as W,a as b,b as g,H as X,i as U,s as z,L as E}from"./app.e1a0aa8b.js";import{_ as Y}from"./AuthenticatedLayout.8f94c2db.js";/* empty css                                              */import{t as M}from"./laravel-vue-pagination.es.c1bef514.js";const Z={key:0,class:"modal-mask"},ee={class:"modal-wrapper max-h-[80vh]"},te={class:"modal-container dark:bg-gray-900 overflow-auto max-h-[80vh]"},ae={class:"modal-header"},se={class:"modal-body"},oe={class:"text-center dark:text-gray-200"},de={className:"mb-4 mx-5"},re={class:"dark:text-gray-200",for:"company_id"},le={selected:"",disabled:""},ne=["value"],ie={className:"mb-4 mx-5"},ce={class:"dark:text-gray-200",for:"name"},me={className:"mb-4 mx-5"},ue={class:"dark:text-gray-200",for:"name"},be={class:"modal-footer my-2"},ge={class:"flex flex-row"},he={class:"basis-1/2 px-4"},ye={class:"basis-1/2 px-4"},_e=["disabled"],fe={__name:"ModalAddNameCompany",props:{show:Boolean,data:Array,formData:Object},setup(d){return(o,l)=>(i(),P(q,{name:"modal"},{default:h(()=>[d.show?(i(),c("div",Z,[e("div",ee,[e("div",te,[e("div",ae,[V(o.$slots,"header")]),e("div",se,[e("h2",oe,r(o.$t("addCompanyName")),1),e("div",de,[e("label",re,r(o.$t("company")),1),y(e("select",{"onUpdate:modelValue":l[0]||(l[0]=n=>d.formData.company_id=n),id:"company_id",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"},[e("option",le,r(o.$t("select_company")),1),(i(!0),c(w,null,$(d.data,(n,C)=>(i(),c("option",{key:C,value:n.id},r(n.name),9,ne))),128))],512),[[Q,d.formData.company_id]])]),e("div",ie,[e("label",ce,r(o.$t("name")),1),y(e("input",{id:"name",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":l[1]||(l[1]=n=>d.formData.name=n)},null,512),[[_,d.formData.name]])]),e("div",me,[e("label",ue,r(o.$t("english_name")),1),y(e("input",{id:"name",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":l[2]||(l[2]=n=>d.formData.name_en=n)},null,512),[[_,d.formData.name_en]])])]),e("div",be,[e("div",ge,[e("div",he,[e("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:l[3]||(l[3]=n=>{o.$emit("close")})},r(o.$t("cancel")),1)]),e("div",ye,[e("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:l[4]||(l[4]=n=>{o.$emit("a",d.formData),d.formData.company_id="",d.formData.name="",d.formData.name_en="",d.formData.id=""}),disabled:!(d.formData.company_id&&d.formData.name)},r(o.$t("yes")),9,_e)])])])])])])):A("",!0)]),_:3}))}};const xe={key:0,class:"modal-mask"},ve={class:"modal-wrapper max-h-[80vh]"},pe={class:"modal-container dark:bg-gray-900 overflow-auto max-h-[80vh]"},ke={class:"modal-header"},we={class:"modal-body"},$e={class:"text-center dark:text-gray-200"},Ce={className:"mb-4 mx-5"},Ne={class:"dark:text-gray-200",for:"name"},De={className:"mb-4 mx-5"},Me={class:"dark:text-gray-200",for:"name"},Ae={class:"modal-footer my-2"},Ie={class:"flex flex-row"},je={class:"basis-1/2 px-4"},Be={class:"basis-1/2 px-4"},Ue=["disabled"],ze={__name:"ModalAddColor",props:{show:Boolean,formData:Object},setup(d){return(o,l)=>(i(),P(q,{name:"modal"},{default:h(()=>[d.show?(i(),c("div",xe,[e("div",ve,[e("div",pe,[e("div",ke,[V(o.$slots,"header")]),e("div",we,[e("h2",$e,r(o.$t("add_color_name")),1),e("div",Ce,[e("label",Ne,r(o.$t("name")),1),y(e("input",{id:"name",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":l[0]||(l[0]=n=>d.formData.name=n)},null,512),[[_,d.formData.name]])]),e("div",De,[e("label",Me,r(o.$t("english_name")),1),y(e("input",{id:"name",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":l[1]||(l[1]=n=>d.formData.name_en=n)},null,512),[[_,d.formData.name_en]])])]),e("div",Ae,[e("div",Ie,[e("div",je,[e("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:l[2]||(l[2]=n=>{o.$emit("close")})},r(o.$t("cancel")),1)]),e("div",Be,[e("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:l[3]||(l[3]=n=>{o.$emit("a",d.formData),d.formData.name="",d.formData.name_en="",d.formData.id=""}),disabled:!d.formData.name},r(o.$t("yes")),9,Ue)])])])])])])):A("",!0)]),_:3}))}};const Pe={key:0,class:"modal-mask"},Ve={class:"modal-wrapper max-h-[80vh]"},qe={class:"modal-container dark:bg-gray-900 overflow-auto max-h-[80vh]"},Re={class:"modal-header"},Te={class:"modal-body"},Se={class:"text-center dark:text-gray-200"},Fe={className:"mb-4 mx-5"},Le={class:"dark:text-gray-200",for:"name"},Oe={class:"modal-footer my-2"},Ee={class:"flex flex-row"},He={class:"basis-1/2 px-4"},Ge={class:"basis-1/2 px-4"},Je=["disabled"],Ke={__name:"ModalAddCarModel",props:{show:Boolean,formData:Object},setup(d){return(o,l)=>(i(),P(q,{name:"modal"},{default:h(()=>[d.show?(i(),c("div",Pe,[e("div",Ve,[e("div",qe,[e("div",Re,[V(o.$slots,"header")]),e("div",Te,[e("h2",Se,r(o.$t("add_model_year")),1),e("div",Fe,[e("label",Le,r(o.$t("name")),1),y(e("input",{id:"name",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":l[0]||(l[0]=n=>d.formData.name=n)},null,512),[[_,d.formData.name]])])]),e("div",Oe,[e("div",Ee,[e("div",He,[e("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:l[1]||(l[1]=n=>{o.$emit("close")})},r(o.$t("cancel")),1)]),e("div",Ge,[e("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:l[2]||(l[2]=n=>{o.$emit("a",d.formData),d.formData.name="",d.formData.name_en="",d.formData.id=""}),disabled:!d.formData.name},r(o.$t("yes")),9,Je)])])])])])])):A("",!0)]),_:3}))}};const Qe=e("h2",{class:"font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200"}," \u0625\u062F\u0627\u0631\u0629 \u0625\u0639\u062F\u0627\u062F\u0627\u062A \u0627\u0644\u0633\u064A\u0627\u0631\u0627\u062A ",-1),We={key:0},Xe={id:"alert-2",class:"p-4 mb-4 bg-red-100 rounded-lg dark:bg-red-200 text-center",role:"alert"},Ye={class:"ml-3 text-sm font-medium text-red-700 dark:text-red-800"},Ze={class:"py-12"},et={class:"max-w-9xl mx-auto sm:px-6 lg:px-8"},tt={class:"bg-white overflow-hidden shadow-sm sm:rounded-lg"},at={class:"p-6 dark:bg-gray-900"},st={class:"flex flex-row"},ot={class:"basis-1/2"},dt={className:"flex items-center justify-between mb-6"},rt={class:"basis-1/2"},lt={class:"flex items-center max-w-5xl"},nt=e("label",{class:"dark:text-gray-200",for:"simple-search"},null,-1),it={class:"relative w-full"},ct=e("div",{class:"absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"},[e("svg",{"aria-hidden":"true",class:"w-5 h-5 text-gray-500 dark:text-gray-400",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{"fill-rule":"evenodd",d:"M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z","clip-rule":"evenodd"})])],-1),mt={class:"overflow-x-auto shadow-md"},ut={class:"w-full my-5"},bt={class:"700 bg-rose-500 text-white text-center rounded-l-lg"},gt={class:"bg-rose-500 rounded-l-lg mb-2 sm:mb-0"},ht={className:"px-4 py-2 border dark:border-gray-900"},yt={className:"px-4 py-2 border dark:border-gray-900"},_t=e("th",{className:"px-4 py-2 border dark:border-gray-900  w-40"},"\u062A\u0646\u0641\u064A\u0630",-1),ft={class:"flex-1 sm:flex-none dark:bg-gray-700 dark:text-gray-200"},xt={className:"px-4 py-2 border dark:border-gray-900 td"},vt={className:"px-4 py-2 border dark:border-gray-900"},pt={className:"border px-2 py-2 dark:border-gray-900"},kt=["onClick"],wt={class:"mt-3 text-center",style:{direction:"ltr"}},$t={class:"py-12"},Ct={class:"max-w-9xl mx-auto sm:px-6 lg:px-8"},Nt={class:"bg-white overflow-hidden shadow-sm sm:rounded-lg"},Dt={class:"p-6 dark:bg-gray-900"},Mt={class:"flex flex-row"},At={class:"basis-1/2"},It={className:"flex items-center justify-between mb-6"},jt={class:"basis-1/2"},Bt={class:"flex items-center max-w-5xl"},Ut=e("label",{class:"dark:text-gray-200",for:"simple-search"},null,-1),zt={class:"relative w-full"},Pt=e("div",{class:"absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"},[e("svg",{"aria-hidden":"true",class:"w-5 h-5 text-gray-500 dark:text-gray-400",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{"fill-rule":"evenodd",d:"M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z","clip-rule":"evenodd"})])],-1),Vt={class:"overflow-x-auto shadow-md"},qt={class:"w-full my-5"},Rt={class:"700 bg-green-500 text-white text-center rounded-l-lg"},Tt={class:"bg-green-500 rounded-l-lg mb-2 sm:mb-0"},St={className:"px-4 py-2 border dark:border-gray-900"},Ft={className:"px-4 py-2 border dark:border-gray-900"},Lt={className:"px-4 py-2 border dark:border-gray-900"},Ot=e("th",{className:"px-4 py-2 border dark:border-gray-900  w-40"},"\u062A\u0646\u0641\u064A\u0630",-1),Et={class:"flex-1 sm:flex-none dark:bg-gray-700 dark:text-gray-200"},Ht={className:"px-4 py-2 border dark:border-gray-900 td"},Gt={className:"px-4 py-2 border dark:border-gray-900"},Jt={className:"px-4 py-2 border dark:border-gray-900"},Kt={className:"border px-2 py-2 dark:border-gray-900"},Qt=["onClick"],Wt=["onClick"],Xt={class:"mt-3 text-center",style:{direction:"ltr"}},Yt={class:"py-12"},Zt={class:"max-w-9xl mx-auto sm:px-6 lg:px-8"},ea={class:"bg-white overflow-hidden shadow-sm sm:rounded-lg"},ta={class:"p-6 dark:bg-gray-900"},aa={class:"flex flex-row"},sa={class:"basis-1/2"},oa={className:"flex items-center justify-between mb-6"},da={class:"basis-1/2"},ra={class:"flex items-center max-w-5xl"},la=e("label",{class:"dark:text-gray-200",for:"simple-search"},null,-1),na={class:"relative w-full"},ia=e("div",{class:"absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"},[e("svg",{"aria-hidden":"true",class:"w-5 h-5 text-gray-500 dark:text-gray-400",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{"fill-rule":"evenodd",d:"M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z","clip-rule":"evenodd"})])],-1),ca={class:"overflow-x-auto shadow-md"},ma={class:"w-full my-5"},ua={class:"700 bg-blue-500 text-white text-center rounded-l-lg"},ba={class:"bg-blue-500 rounded-l-lg mb-2 sm:mb-0"},ga={className:"px-4 py-2 border dark:border-gray-900"},ha=e("th",{className:"px-4 py-2 border dark:border-gray-900  w-40"},"\u062A\u0646\u0641\u064A\u0630",-1),ya={class:"flex-1 sm:flex-none dark:bg-gray-700 dark:text-gray-200"},_a={className:"px-4 py-2 border dark:border-gray-900 td"},fa={className:"border px-2 py-2 dark:border-gray-900"},xa=["onClick"],va=["onClick"],pa={class:"mt-3 text-center",style:{direction:"ltr"}},ka={class:"py-12"},wa={class:"max-w-9xl mx-auto sm:px-6 lg:px-8"},$a={class:"bg-white overflow-hidden shadow-sm sm:rounded-lg"},Ca={class:"p-6 dark:bg-gray-900"},Na={class:"flex flex-row"},Da={class:"basis-1/2"},Ma={className:"flex items-center justify-between mb-6"},Aa={class:"basis-1/2"},Ia={class:"flex items-center max-w-5xl"},ja=e("label",{class:"dark:text-gray-200",for:"simple-search"},null,-1),Ba={class:"relative w-full"},Ua=e("div",{class:"absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"},[e("svg",{"aria-hidden":"true",class:"w-5 h-5 text-gray-500 dark:text-gray-400",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{"fill-rule":"evenodd",d:"M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z","clip-rule":"evenodd"})])],-1),za={class:"overflow-x-auto shadow-md"},Pa={class:"w-full my-5"},Va={class:"700 bg-purple-500 text-white text-center rounded-l-lg"},qa={class:"bg-purple-500 rounded-l-lg mb-2 sm:mb-0"},Ra={className:"px-4 py-2 border dark:border-gray-900"},Ta={className:"px-4 py-2 border dark:border-gray-900"},Sa=e("th",{className:"px-4 py-2 border dark:border-gray-900  w-40"},"\u062A\u0646\u0641\u064A\u0630",-1),Fa={class:"flex-1 sm:flex-none dark:bg-gray-700 dark:text-gray-200"},La={className:"px-4 py-2 border dark:border-gray-900 td"},Oa={className:"px-4 py-2 border dark:border-gray-900"},Ea={className:"border px-2 py-2 dark:border-gray-900"},Ha=["onClick"],Ga=["onClick"],Ja={class:"mt-3 text-center",style:{direction:"ltr"}},Ya={__name:"Index",props:{url:String,company:Array},setup(d){const o=u({}),l=u({}),n=u({}),C=u({}),x=async(s=1)=>{const a=await fetch(`/getIndexCompany?page=${s}`);o.value=await a.json()},I=async(s=1)=>{const a=await fetch(`/getIndexName?page=${s}`);l.value=await a.json()},j=async(s=1)=>{const a=await fetch(`/getIndexModel?page=${s}`);n.value=await a.json()},B=async(s=1)=>{const a=await fetch(`/getIndexColor?page=${s}`);C.value=await a.json()},m=u(""),R=u({}),T=u({}),S=u({});x(),I(),j(),B();const N=async s=>{o.value=[],await fetch(`/livesearch?q=${s}`),o.value=await $data};W();let v=u(!1),p=u(!1),k=u(!1);const D=async(s,a)=>{try{fetch(`/${a}/${s}`),x(),I(),j(),B()}catch(t){console.error(t)}};function F(s={}){T.value=s,p.value=!0}function L(s={}){S.value=s,k.value=!0}function O(s={}){R.value=s,v.value=!0}function H(s){var a;fetch(`/addName?company_id=${s.company_id}&name=${s.name}&name_en=${s.name_en}&id=${(a=s.id)!=null?a:0}`).then(()=>{v.value=!1,I()}).catch(t=>{console.error(t)})}function G(s){var a;fetch(`/addCarModel?name=${s.name}&id=${(a=s.id)!=null?a:0}`).then(()=>{p.value=!1,j()}).catch(t=>{console.error(t)})}function J(s){var a;fetch(`/addColor?name=${s.name}&name_en=${s.name_en}&id=${(a=s.id)!=null?a:0}`).then(()=>{k.value=!1,B()}).catch(t=>{console.error(t)})}return(s,a)=>(i(),c(w,null,[b(g(X),{title:"Dashboard"}),b(Y,null,{header:h(()=>[Qe]),default:h(()=>[s.$page.props.success?(i(),c("div",We,[e("div",Xe,[e("div",Ye,r(s.$page.props.success),1)])])):A("",!0),b(fe,{show:!!g(v),formData:R.value,data:o.value.data,onA:a[0]||(a[0]=t=>H(t)),onClose:a[1]||(a[1]=t=>U(v)?v.value=!1:v=!1)},{header:h(()=>[z(" \u0652 ")]),_:1},8,["show","formData","data"]),b(Ke,{show:!!g(p),formData:T.value,data:o.value.data,onA:a[2]||(a[2]=t=>G(t)),onClose:a[3]||(a[3]=t=>U(p)?p.value=!1:p=!1)},{header:h(()=>[]),_:1},8,["show","formData","data"]),b(ze,{show:!!g(k),formData:S.value,data:o.value.data,onA:a[4]||(a[4]=t=>J(t)),onClose:a[5]||(a[5]=t=>U(k)?k.value=!1:k=!1)},{header:h(()=>[]),_:1},8,["show","formData","data"]),e("div",Ze,[e("div",et,[e("div",tt,[e("div",at,[e("div",st,[e("div",ot,[e("div",dt,[b(g(E),{className:"px-6 py-2 text-white bg-rose-500 rounded-md focus:outline-none",href:s.route("addCompany")},{default:h(()=>[z(" \u0625\u0646\u0634\u0627\u0621 \u0634\u0631\u0643\u0629 \u062C\u062F\u064A\u062F\u0629 ")]),_:1},8,["href"])])]),e("div",rt,[e("form",lt,[nt,e("div",it,[ct,y(e("input",{"onUpdate:modelValue":a[6]||(a[6]=t=>m.value=t),onInput:a[7]||(a[7]=t=>N(m.value)),type:"text",id:"simple-search",class:"bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500",placeholder:"\u0628\u062D\u062B \u062D\u0633\u0628 \u0627\u0644\u0634\u0631\u0643\u0629",required:""},null,544),[[_,m.value]])])])])]),e("div",mt,[e("table",ut,[e("thead",bt,[e("tr",gt,[e("th",ht,r(s.$t("name")),1),e("th",yt,r(s.$t("english_name")),1),_t])]),e("tbody",ft,[(i(!0),c(w,null,$(o.value.data,t=>(i(),c("tr",{key:t.id,class:"mb-2 sm:mb-0 text-center"},[e("td",xt,r(t.name),1),e("td",vt,r(t.name_en),1),e("td",pt,[b(g(E),{tabIndex:"1",className:"px-2 py-1 text-sm text-white mx-1 bg-slate-500 rounded",href:s.route("companyEdit",t.id)},{default:h(()=>[z(" \u062A\u0639\u062F\u064A\u0644 ")]),_:2},1032,["href"]),e("button",{tabIndex:"1",className:"px-2 py-1 text-sm text-white mx-1 bg-red-500 rounded",onClick:f=>D(t.id,"delCompany")}," \u062D\u0630\u0641 ",8,kt)])]))),128))])])]),e("div",wt,[b(g(M),{data:o.value,onPaginationChangePage:x,limit:10},null,8,["data"])])])])])]),e("div",$t,[e("div",Ct,[e("div",Nt,[e("div",Dt,[e("div",Mt,[e("div",At,[e("div",It,[e("button",{className:"px-6 py-2 text-white bg-green-500 rounded-md focus:outline-none",onClick:a[8]||(a[8]=t=>O())}," \u0625\u0646\u0634\u0627\u0621 \u0627\u0633\u0645 \u062C\u062F\u064A\u062F\u0629 ")])]),e("div",jt,[e("form",Bt,[Ut,e("div",zt,[Pt,y(e("input",{"onUpdate:modelValue":a[9]||(a[9]=t=>m.value=t),onInput:a[10]||(a[10]=t=>N(m.value)),type:"text",id:"simple-search",class:"bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500",placeholder:"\u0628\u062D\u062B \u062D\u0633\u0628 \u0627\u0633\u0645 \u0627\u0644\u0633\u064A\u0627\u0631\u0629",required:""},null,544),[[_,m.value]])])])])]),e("div",Vt,[e("table",qt,[e("thead",Rt,[e("tr",Tt,[e("th",St,r(s.$t("name")),1),e("th",Ft,r(s.$t("english_name")),1),e("th",Lt,r(s.$t("company")),1),Ot])]),e("tbody",Et,[(i(!0),c(w,null,$(l.value.data,t=>{var f;return i(),c("tr",{key:t.id,class:"mb-2 sm:mb-0 text-center"},[e("td",Ht,r(t.name),1),e("td",Gt,r(t.name_en),1),e("td",Jt,r((f=t.company)==null?void 0:f.name),1),e("td",Kt,[e("button",{tabIndex:"1",className:"px-2 py-1 text-sm text-white mx-1 bg-slate-500 rounded",onClick:K=>O(t)}," \u062A\u0639\u062F\u064A\u0644 ",8,Qt),e("button",{tabIndex:"1",className:"px-2 py-1 text-sm text-white mx-1 bg-green-500 rounded",onClick:K=>D(t.id,"delName")}," \u062D\u0630\u0641 ",8,Wt)])])}),128))])])]),e("div",Xt,[b(g(M),{data:o.value,onPaginationChangePage:x,limit:10},null,8,["data"])])])])])]),e("div",Yt,[e("div",Zt,[e("div",ea,[e("div",ta,[e("div",aa,[e("div",sa,[e("div",oa,[e("button",{className:"px-6 py-2 text-white bg-blue-500 rounded-md focus:outline-none",onClick:a[11]||(a[11]=t=>F())}," \u0625\u0646\u0634\u0627\u0621 \u0633\u0646\u0629 \u062C\u062F\u064A\u062F\u0629 ")])]),e("div",da,[e("form",ra,[la,e("div",na,[ia,y(e("input",{"onUpdate:modelValue":a[12]||(a[12]=t=>m.value=t),onInput:a[13]||(a[13]=t=>N(m.value)),type:"text",id:"simple-search",class:"bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500",placeholder:"\u0628\u062D\u062B \u062D\u0633\u0628  \u0633\u0646\u0629 \u0627\u0644\u0635\u0646\u0639",required:""},null,544),[[_,m.value]])])])])]),e("div",ca,[e("table",ma,[e("thead",ua,[e("tr",ba,[e("th",ga,r(s.$t("name")),1),ha])]),e("tbody",ya,[(i(!0),c(w,null,$(n.value.data,t=>(i(),c("tr",{key:t.id,class:"mb-2 sm:mb-0 text-center"},[e("td",_a,r(t.name),1),e("td",fa,[e("button",{tabIndex:"1",className:"px-2 py-1 text-sm text-white mx-1 bg-slate-500 rounded",onClick:f=>F(t)}," \u062A\u0639\u062F\u064A\u0644 ",8,xa),e("button",{tabIndex:"1",className:"px-2 py-1 text-sm text-white mx-1 bg-blue-500 rounded",onClick:f=>D(t.id,"delModel")}," \u062D\u0630\u0641 ",8,va)])]))),128))])])]),e("div",pa,[b(g(M),{data:o.value,onPaginationChangePage:x,limit:10},null,8,["data"])])])])])]),e("div",ka,[e("div",wa,[e("div",$a,[e("div",Ca,[e("div",Na,[e("div",Da,[e("div",Ma,[e("button",{className:"px-6 py-2 text-white bg-purple-500 rounded-md focus:outline-none",onClick:a[14]||(a[14]=t=>L())}," \u0625\u0646\u0634\u0627\u0621 \u0644\u0648\u0646 \u062C\u062F\u064A\u062F\u0629 ")])]),e("div",Aa,[e("form",Ia,[ja,e("div",Ba,[Ua,y(e("input",{"onUpdate:modelValue":a[15]||(a[15]=t=>m.value=t),onInput:a[16]||(a[16]=t=>N(m.value)),type:"text",id:"simple-search",class:"bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500",placeholder:"\u0628\u062D\u062B \u062D\u0633\u0628 \u0627\u0633\u0645 \u0627\u0644\u0644\u0648\u0646",required:""},null,544),[[_,m.value]])])])])]),e("div",za,[e("table",Pa,[e("thead",Va,[e("tr",qa,[e("th",Ra,r(s.$t("name")),1),e("th",Ta,r(s.$t("english_name")),1),Sa])]),e("tbody",Fa,[(i(!0),c(w,null,$(C.value.data,t=>(i(),c("tr",{key:t.id,class:"mb-2 sm:mb-0 text-center"},[e("td",La,r(t.name),1),e("td",Oa,r(t.name_en),1),e("td",Ea,[e("button",{tabIndex:"1",className:"px-2 py-1 text-sm text-white mx-1 bg-slate-500 rounded",onClick:f=>L(t)}," \u062A\u0639\u062F\u064A\u0644 ",8,Ha),e("button",{tabIndex:"1",className:"px-2 py-1 text-sm text-white mx-1 bg-purple-500 rounded",onClick:f=>D(t.id,"delColor")}," \u062D\u0630\u0641 ",8,Ga)])]))),128))])])]),e("div",Ja,[b(g(M),{data:o.value,onPaginationChangePage:x,limit:10},null,8,["data"])])])])])])]),_:1})],64))}};export{Ya as default};
