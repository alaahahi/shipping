import{m as i,o as y,d as ie,e as w,a as x,h as e,r as ce,t as r,u as c,w as h,y as q,F as P,z as E,g as A,C as k,T as ue,Y as pe,n as H,J as ge,s as me,I as fe,f as m,i as $,H as he,L as ye}from"./app.ff76a5b9.js";import{_ as be}from"./AuthenticatedLayout.af2769c1.js";import{M as xe}from"./Modal.600dd940.js";import{_ as ve,a as we,b as ke,c as _e,d as $e,e as Ce,f as De}from"./ModalAddTransfers.59a03499.js";import{_ as Ie}from"./ModalAddCarPayment.275e72d2.js";import{M as Me}from"./ModalDelCar.8b9b95f5.js";import{t as Ne}from"./laravel-vue-pagination.es.07de9000.js";import{a as N}from"./index.c1e29c5b.js";import{s as Ae}from"./show.b446b7b1.js";import{e as Be,t as je}from"./edit.38032698.js";/* empty css                                              */const Ue={key:0,class:"modal-mask"},Ve={class:"modal-wrapper"},Se={class:"modal-container dark:bg-gray-900"},Pe={class:"modal-header"},Te={class:"text-center dark:text-gray-200"},ze={class:"modal-body"},Ee={class:"grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-1 lg:gap-2"},Fe={class:"mb-4 mx-1"},Re={class:"dark:text-gray-200",for:"color_id"},Ye={class:"relative"},Ke={selected:"",disabled:""},Oe=["value"],Ge={class:"grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-1 lg:gap-2"},Le={className:"mb-4 mx-1"},We={class:"dark:text-gray-200",for:"pin"},qe={className:"mb-4 mx-1"},He={class:"dark:text-gray-200",for:"pin"},Je={className:"mb-4 mx-1"},Qe={class:"dark:text-gray-200",for:"pin"},Xe={className:"mb-4 mx-1"},Ze={class:"dark:text-gray-200",for:"pin"},et={key:0,class:"text-red-700"},tt={className:"mb-4 mx-1"},st={class:"dark:text-gray-200",for:"car_number"},ot={className:"mb-4 mx-1"},at={class:"dark:text-gray-200",for:"dinar"},rt={className:"mb-4 mx-1"},dt={class:"dark:text-gray-200",for:"dolar_price"},nt={className:"mb-4 mx-1"},lt={class:"dark:text-gray-200",for:"shipping_dolar"},it={className:"mb-4 mx-1"},ct={class:"dark:text-gray-200",for:"coc_dolar"},ut={className:"mb-4 mx-1"},pt={class:"dark:text-gray-200",for:"checkout"},gt={className:"mb-4 mx-1"},mt={class:"dark:text-gray-200",for:"expenses"},ft={className:"mb-4 mx-1"},ht={class:"dark:text-gray-200",for:"date"},yt={className:"mb-4 mx-1"},bt={class:"dark:text-gray-200",for:"note"},xt={class:"modal-footer my-2"},vt={class:"flex flex-row"},wt={class:"basis-1/2 px-4"},kt={class:"basis-1/2 px-4"},_t=["disabled"],$t={__name:"ModalEditCars",props:{show:Boolean,formData:Object,client:Array},setup(a){function g(){const l=new Date,n=l.getFullYear(),d=String(l.getMonth()+1).padStart(2,"0"),b=String(l.getDate()).padStart(2,"0");return`${n}-${d}-${b}`}function u(l){l&&axios.get(`/api/check_vin?car_vin=${l}`).then(n=>{p.value=n.data}).catch(n=>{console.error(n)})}let v=i(!1),p=i(!1);return(l,n)=>(y(),ie(ue,{name:"modal"},{default:w(()=>[a.show?(y(),x("div",Ue,[e("div",Ve,[e("div",Se,[e("div",Pe,[ce(l.$slots,"header",{},()=>[e("h2",Te,r(l.$t("edit_car")),1)])]),e("div",ze,[e("div",Ee,[e("div",Fe,[e("label",Re,r(l.$t("car_owner")),1),e("div",Ye,[c(v)?A("",!0):h((y(),x("select",{key:0,"onUpdate:modelValue":n[0]||(n[0]=d=>a.formData.client_id=d),id:"color_id",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500",disabled:""},[e("option",Ke,r(l.$t("selectCustomer")),1),(y(!0),x(P,null,E(a.client,(d,b)=>(y(),x("option",{key:b,value:d.id},r(d.name),9,Oe))),128))],512)),[[q,a.formData.client_id]])])])]),e("div",Ge,[e("div",Le,[e("label",We,r(l.$t("car_type")),1),h(e("input",{id:"car_type",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":n[1]||(n[1]=d=>a.formData.car_type=d)},null,512),[[k,a.formData.car_type]])]),e("div",qe,[e("label",He,r(l.$t("year")),1),h(e("input",{id:"year",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":n[2]||(n[2]=d=>a.formData.year=d)},null,512),[[k,a.formData.year]])]),e("div",Je,[e("label",Qe,r(l.$t("color")),1),h(e("input",{id:"car_color",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":n[3]||(n[3]=d=>a.formData.car_color=d)},null,512),[[k,a.formData.car_color]])]),e("div",Xe,[e("label",Ze,r(l.$t("vin")),1),h(e("input",{id:"vin",type:"text",onChange:n[4]||(n[4]=d=>u(a.formData.vin)),class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":n[5]||(n[5]=d=>a.formData.vin=d)},null,544),[[k,a.formData.vin]]),c(p)?(y(),x("div",et," \u0631\u0642\u0645 \u0627\u0644\u0634\u0627\u0635\u064A \u0645\u0633\u062A\u062E\u062F\u0645 ")):A("",!0)]),e("div",tt,[e("label",st,r(l.$t("car_number")),1),h(e("input",{id:"car_number",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":n[6]||(n[6]=d=>a.formData.car_number=d)},null,512),[[k,a.formData.car_number]])]),e("div",ot,[e("label",at,r(l.$t("dinar")),1),h(e("input",{id:"dinar",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":n[7]||(n[7]=d=>a.formData.dinar=d)},null,512),[[k,a.formData.dinar]])]),e("div",rt,[e("label",dt,r(l.$t("dolar_price")),1),h(e("input",{id:"dolar_price",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":n[8]||(n[8]=d=>a.formData.dolar_price=d)},null,512),[[k,a.formData.dolar_price]])]),e("div",nt,[e("label",lt,r(l.$t("shipping_dolar")),1),h(e("input",{id:"shipping_dolar",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":n[9]||(n[9]=d=>a.formData.shipping_dolar=d)},null,512),[[k,a.formData.shipping_dolar]])]),e("div",it,[e("label",ct,r(l.$t("coc_dolar")),1),h(e("input",{id:"coc_dolar",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":n[10]||(n[10]=d=>a.formData.coc_dolar=d)},null,512),[[k,a.formData.coc_dolar]])]),e("div",ut,[e("label",pt,r(l.$t("checkout")),1),h(e("input",{id:"checkout",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":n[11]||(n[11]=d=>a.formData.checkout=d)},null,512),[[k,a.formData.checkout]])]),e("div",gt,[e("label",mt,r(l.$t("expenses")),1),h(e("input",{id:"expenses",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":n[12]||(n[12]=d=>a.formData.expenses=d)},null,512),[[k,a.formData.expenses]])]),e("div",ft,[e("label",ht,r(l.$t("date")),1),h(e("input",{id:"date",type:"date",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":n[13]||(n[13]=d=>a.formData.date=d)},null,512),[[k,a.formData.date]])])]),e("div",yt,[e("label",bt,r(l.$t("note")),1),h(e("input",{id:"note",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":n[14]||(n[14]=d=>a.formData.note=d)},null,512),[[k,a.formData.note]])])]),e("div",xt,[e("div",vt,[e("div",wt,[e("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:n[15]||(n[15]=d=>l.$emit("close"))},r(l.$t("cancel")),1)]),e("div",kt,[e("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:n[16]||(n[16]=d=>{a.formData.date=a.formData.date?a.formData.date:g(),l.$emit("a",a.formData),a.formData=""}),disabled:!a.formData.client_id&&!a.formData.client_name},r(l.$t("yes")),9,_t)])])])])])])):A("",!0)]),_:3}))}};(function(){try{if(typeof document<"u"){var a=document.createElement("style");a.appendChild(document.createTextNode(".vue-pincode-input-wrapper{display:flex;flex-wrap:wrap;row-gap:1rem}.vue-pincode-input-wrapper.is-success .vue-pincode-input{border:1px solid #62c633}.vue-pincode-input-wrapper.is-error .vue-pincode-input{border:1px solid #da3945}.vue-pincode-input-wrapper .vue-pincode-input{text-align:center;vertical-align:middle}.vue-pincode-input-wrapper .vue-pincode-input.default{width:65px;height:65px;border:1px solid #c4c4c4;font-size:1.8rem;transition:all .3s}.vue-pincode-input-wrapper .vue-pincode-input.default:focus{box-shadow:0 20px 25px -5px #0000001a,0 8px 10px -6px #0000001a}.vue-pincode-input-wrapper .vue-pincode-input.default:not(:last-child){margin-right:.5rem}.vue-pincode-input-wrapper .vue-pincode-input:focus{outline-style:none}")),document.head.appendChild(a)}}catch(g){console.error("vite-plugin-css-injected-by-js",g)}})();const Ct=(a,g)=>{const u=a.__vccOpts||a;for(const[v,p]of g)u[v]=p;return u},Dt={name:"PincodeInput",props:{modelValue:{type:String,default:""},digits:{type:Number,default:4},placeholder:{type:String,default:""},secure:{type:Boolean,default:!1},autofocus:{type:Boolean,default:!1},inputClass:{type:String,default:""},successClass:{type:String,default:""},spacingClass:{type:String,default:""}},data(){return{baseRefName:"vue-pincode-input",focusedInputIndex:0,watchers:{},inputs:this.initialInputs()}},computed:{inputClasses(){return[this.inputClass||"default",this.isValid?this.successClass:""].join(" ")},isValid(){return this.inputs.join("").length===this.digits}},mounted(){this.$nextTick(()=>{this.init(),this.autofocus&&this.$refs["vue-pincode-input0"]&&this.$refs["vue-pincode-input0"][0].focus()})},beforeDestroy(){this.unwatchInputs()},methods:{init(){this.inputs=this.initialInputs();for(let a in this.inputs)this.setInputWatcher(a)},focusPreviousInput(){this.focusedInputIndex&&this.focusInputByIndex(this.focusedInputIndex-1)},focusNextInput(){this.focusedInputIndex!==this.digits-1&&this.focusInputByIndex(this.focusedInputIndex+1)},focusInputByIndex(a){const g=`${this.baseRefName}${a}`,u=this.$refs[g];u&&(u[0].focus(),u[0].select()),this.focusedInputIndex=a},hadleKeyDown(a){switch(a.keyCode){case 37:return this.focusPreviousInput();case 39:return this.focusNextInput()}if(this.inputs[this.focusedInputIndex])return this.inputs[this.focusedInputIndex]=""},setInputWatcher(a){const g=`inputs.${a}`;this.watchers[g]=this.$watch(g,(u,v)=>this.hadleInputChange(a,u,v))},isInputValid(a,g=!0){return a?!!a.match("^\\d{1}$"):g?a==="":!1},hadleInputChange(a,g,u){if(this.$emit("update:modelValue",this.inputs.join("")),!this.isInputValid(g,!1)){this.inputs[a]="";return}if(+a===this.digits-1){const v=this.inputs.findIndex(p=>!p);v!==-1&&this.focusInputByIndex(v);return}this.focusNextInput()},handleFocus(a){this.$refs[a][0].setSelectionRange(1,1)},pinfocus(a){this.$refs[a][0].focus()},hadleDelete(a,g){this.inputs[a].length||(this.focusPreviousInput(),g.preventDefault())},initialInputs(){return this.modelValue?this.modelValue.length<=this.digits?[...this.modelValue,...[...Array(this.digits-this.modelValue.length)].map(()=>"")]:[...this.modelValue.slice(0,this.digits)]:[...Array(this.digits)].map(()=>"")},reset(){this.unwatchInputs(),this.init()},unwatchInputs(){Object.keys(this.watchers).forEach(a=>this.watchers[a]())}}},It={class:"vue-pincode-input-wrapper"},Mt=["onUpdate:modelValue","type","placeholder","onFocus","onKeydown"];function Nt(a,g,u,v,p,l){return y(),x("div",It,[(y(!0),x(P,null,E(p.inputs,(n,d)=>h((y(),x("input",{key:d,ref_for:!0,ref:`${p.baseRefName}${d}`,"onUpdate:modelValue":b=>p.inputs[d]=b,type:u.secure?"password":"tel",placeholder:u.placeholder,maxlength:"1",class:H(["vue-pincode-input",[l.inputClasses,u.spacingClass]]),onFocus:b=>p.focusedInputIndex=d,onKeydown:[ge(b=>l.hadleDelete(d,b),["delete"]),b=>l.hadleKeyDown(b,d)]},null,42,Mt)),[[pe,p.inputs[d],void 0,{trim:!0}]])),128))])}const At=Ct(Dt,[["render",Nt]]),Bt=e("h2",{class:"text-center",style:{"font-size":"20px"}}," \u0647\u0644 \u0645\u062A\u0623\u0643\u062F \u0645\u0646 \u062A\u0639\u062F\u064A\u0644 \u0627\u0644\u0628\u064A\u0627\u0646\u0627\u062A ",-1),jt=e("h2",{class:"mb-5 dark:text-white text-center"}," \u0647\u0644 \u0645\u062A\u0623\u0643\u062F \u0645\u0646 \u062D\u0630\u0641 \u0627\u0644\u0633\u064A\u0627\u0631\u0629 \u061F ",-1),Ut={key:0,class:"py-2"},Vt={class:"max-w-9xl mx-auto sm:px-6 lg:px-8"},St={class:"bg-white overflow-hidden shadow-sm d-flex text-center",dir:"ltr"},Pt={key:1,class:"py-2"},Tt={class:"max-w-9xl mx-auto sm:px-6 lg:px-8"},zt={class:"bg-white overflow-hidden shadow-sm"},Et={class:"p-6 dark:bg-gray-900"},Ft={class:"flex flex-col"},Rt={class:"grid grid-cols-2 md:grid-cols-5 lg:grid-cols-5 gap-2 lg:gap-1"},Yt={class:"flex items-center max-w-5xl"},Kt=e("label",{class:"dark:text-gray-200",for:"simple-search"},null,-1),Ot={class:"relative w-full"},Gt=e("div",{class:"absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"},[e("svg",{"aria-hidden":"true",class:"w-5 h-5 text-gray-500 dark:text-gray-200 dark:text-gray-400",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{"fill-rule":"evenodd",d:"M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z","clip-rule":"evenodd"})])],-1),Lt={value:"undefined",disabled:""},Wt={value:""},qt=["value"],Ht={class:"text-center"},Jt={class:"relative overflow-x-auto shadow-md sm:rounded-lg"},Qt={class:"w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center"},Xt={class:"text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center"},Zt={scope:"col",class:"px-1 py-3 text-base"},es={scope:"col",class:"px-1 py-3 text-base"},ts={scope:"col",class:"px-1 py-3 text-base"},ss={scope:"col",class:"px-1 py-3 text-base"},os={scope:"col",class:"px-1 py-3 text-base"},as={scope:"col",class:"px-1 py-3 text-base"},rs={scope:"col",class:"px-1 py-3 text-base"},ds={scope:"col",class:"px-1 py-3 text-base"},ns={scope:"col",class:"px-1 py-3 text-base"},ls={scope:"col",class:"px-1 py-3 text-base"},is={scope:"col",class:"px-1 py-3 text-base"},cs={scope:"col",class:"px-1 py-3 text-base"},us={scope:"col",class:"px-1 py-3 text-base"},ps={scope:"col",class:"px-1 py-3 text-base"},gs={scope:"col",class:"px-1 py-3 text-base"},ms={scope:"col",class:"px-1 py-3 text-base"},fs={scope:"col",class:"px-1 py-3 text-base"},hs={scope:"col",class:"px-1 py-3 text-base"},ys={scope:"col",class:"px-1 py-3 text-base"},bs={scope:"col",class:"px-1 py-3 text-base",style:{width:"150px"}},xs={className:"border dark:border-gray-800 text-center px-1 py-2 "},vs={className:"border dark:border-gray-900 text-center dark:text-gray-200 text-black px-1 py-2 ",style:{"font-weight":"bold","font-size":"16px"}},ws={className:"border dark:border-gray-800 text-center px-1 py-2 "},ks={className:"border dark:border-gray-800 text-center px-1 py-2 "},_s={className:"border dark:border-gray-800 text-center px-1 py-2 "},$s={className:"border dark:border-gray-800 text-center px-1 py-2 "},Cs={className:"border dark:border-gray-800 text-center px-1 py-2 "},Ds={className:"border dark:border-gray-800 text-center px-1 py-2 "},Is={className:"border dark:border-gray-800 text-center px-1 py-2 "},Ms={className:"border dark:border-gray-800 text-center px-1 py-2 "},Ns={className:"border dark:border-gray-800 text-center px-1 py-2 "},As={className:"border dark:border-gray-800 text-center px-1 py-2 "},Bs={className:"border dark:border-gray-800 text-center px-1 py-2 "},js={className:"border dark:border-gray-800 text-center px-1 py-2 "},Us={className:"border dark:border-gray-800 text-center px-1 py-2 "},Vs={className:"border dark:border-gray-800 text-center px-1 py-2 "},Ss={className:"border dark:border-gray-800 text-center px-1 py-2 "},Ps={className:"border dark:border-gray-800 text-center px-1 py-2 "},Ts={className:"border dark:border-gray-800 text-center px-1 py-2 "},zs={className:"border dark:border-gray-800 text-start px-1 py-2"},Es=["onClick"],Fs=["onClick"],Rs={class:"mt-3 text-center",style:{direction:"ltr"}},Ys={key:0},Ks={class:"mt-4 grid grid-cols-1 gap-4 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-5"},Os={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},Gs=e("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-orange-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"})])],-1),Ls={class:"mr-4"},Ws={class:"font-semibold"},qs={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},Hs={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},Js=e("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-orange-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"})])],-1),Qs={class:"mr-4"},Xs=e("h2",{class:"font-semibold"},"\u0625\u062C\u0645\u0627\u0644\u064A \u0627\u0644\u062A\u0643\u0627\u0644\u064A\u0641 ",-1),Zs={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},eo={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},to=e("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-orange-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"})])],-1),so={class:"mr-4"},oo=e("h2",{class:"font-semibold"},"\u0625\u062C\u0645\u0627\u0644\u064A \u0627\u0644\u0645\u062F\u0641\u0648\u0639\u0627\u062A \u0645\u0646 \u0627\u0644\u0632\u0628\u0627\u0626\u0646",-1),ao={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},ro={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},no=e("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-orange-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"})])],-1),lo={class:"mr-4"},io=e("h2",{class:"font-semibold"}," \u0635\u0627\u0641\u064A \u0627\u0644\u0631\u0628\u062D",-1),co={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},uo={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},po=e("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-red-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"})])],-1),go={class:"mr-4"},mo=e("h2",{class:"font-semibold"}," \u0627\u0644\u062F\u064A\u0646 ",-1),fo={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},ho={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},yo=e("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-red-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"})])],-1),bo={class:"mr-4"},xo={class:"font-semibold"},vo={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},wo=e("div",null,null,-1),Uo={__name:"purchases",props:{client:Array},setup(a){me();let g=i({}),u=i(0);const v=fe();let p=i(!1),l=i(""),n=i(!1),d=i(!1),b=i(!1),B=i(!1),j=i(!1),U=i(!1),V=i(!1),S=i(!1),D=i(!1),I=i(!1),R=i(0),Y=i(0),K=i(0),O=i(0),G=i(0),L=i(0);function J(t={}){_.value=t,D.value=!0}function Q(t={}){_.value=t,I.value=!0}function X(t={}){_.value=t,n.value=!0}const _=i({});i({});const T=i([]);i({startDate:"",endDate:""}),i(),i({date:"D/MM/YYYY",month:"MM"});const M=async(t=1,o="")=>{const s=await fetch(`/getIndexCar?page=${t}&user_id=${o}`);T.value=await s.json()},Z=async(t="",o=1)=>{const s=await fetch(`/getIndexCarSearch?page=${o}&q=${t}`);T.value=await s.json()};i({shortcuts:{today:"\u0627\u0644\u064A\u0648\u0645",yesterday:"\u0627\u0644\u0628\u0627\u0631\u062D\u0629",past:t=>t+" \u0642\u0628\u0644 \u064A\u0648\u0645",currentMonth:"\u0627\u0644\u0634\u0647\u0631 \u0627\u0644\u062D\u0627\u0644\u064A",pastMonth:"\u0627\u0644\u0634\u0647\u0631 \u0627\u0644\u0633\u0627\u0628\u0642"},footer:{apply:"Terapkan",cancel:"Batal"}});const z=async()=>{N.get("/api/totalInfo").then(t=>{R.value=t.data.data.mainAccount,K.value=t.data.data.sumTotal,O.value=t.data.data.sumPaid,Y.value=t.data.data.allCars,G.value=t.data.data.sumProfit,L.value=t.data.data.sumDebit}).catch(t=>{console.error(t)})};z();function ee(t){N.post("/api/addCars",t).then(o=>{n.value=!1,M(),z()}).catch(o=>{console.error(o)})}function W(t){D.value=!1,N.post("/api/updateCarsP",t).then(o=>{p.value=!1,v.success("\u062A\u0645 \u0627\u0644\u062A\u0639\u062F\u064A\u0644 \u0628\u0646\u062C\u0627\u062D",{timeout:2e3,position:"bottom-right",rtl:!0}),z(),M()}).catch(o=>{p.value=!1,v.error("\u0644\u0645 \u0627\u0644\u062A\u0639\u062F\u064A\u0644 \u0628\u0646\u062C\u0627\u062D",{timeout:2e3,position:"bottom-right",rtl:!0})})}function te(t){N.post("/api/payCar",t).then(o=>{d.value=!1,window.location.reload()}).catch(o=>{console.error(o)})}function se(t){var o,s;fetch(`/addExpenses?car_id=${t.id}&user_id=${t.user_id}&expenses_id=${t.expenses_id}&expens_amount=${(o=t.expens_amount)!=null?o:0}&note=${(s=t.noteExpenses)!=null?s:""}`).then(()=>{b.value=!1,window.location.reload()}).catch(f=>{console.error(f)})}function oe(t){var o,s,f;fetch(`/GenExpenses?user_id=${t.user_id}&amount=${(o=t.amount)!=null?o:0}&reason=${(s=t.reason)!=null?s:""}&note=${(f=t.note)!=null?f:""}`).then(()=>{B.value=!1,window.location.reload()}).catch(C=>{console.error(C)})}function ae(t){var o,s;fetch(`/addTransfers?user_id=${t.user_id}&amount=${(o=t.amount)!=null?o:0}&note=${(s=t.note)!=null?s:""}`).then(()=>{V.value=!1,window.location.reload()}).catch(f=>{console.error(f)})}function re(t){var o,s;fetch(`/addToBox?amount=${(o=t.amount)!=null?o:0}&note=${(s=t.note)!=null?s:""}`).then(()=>{j.value=!1,window.location.reload()}).catch(f=>{console.error(f)})}function de(t){var o,s;fetch(`/withDrawFromBox?amount=${(o=t.amount)!=null?o:0}&note=${(s=t.note)!=null?s:""}`).then(()=>{U.value=!1,window.location.reload()}).catch(f=>{console.error(f)})}function ne(t){N.post("/api/DelCar",t).then(o=>{I.value=!1,M(),z()}).catch(o=>{console.error(o)})}M();function le(t){var o,s,f;N.get(`/api/addPaymentCar?car_id=${t.id}&discount=${(o=t.discount)!=null?o:0}&amount=${(s=t.amountPayment)!=null?s:0}&note=${(f=t.notePayment)!=null?f:""}`).then(C=>{S.value=!1,v.success(" \u062A\u0645 \u062F\u0641\u0639 \u0645\u0628\u0644\u063A \u062F\u0648\u0644\u0627\u0631 "+t.amountPayment+" \u0628\u0646\u062C\u0627\u062D ",{timeout:3e3,position:"bottom-right",rtl:!0});let F=C.data;window.open(`/api/getIndexAccountsSelas?user_id=${t.client.id}&print=2&transactions_id=${F.id}`,"_blank")}).catch(C=>{p.value=!1,v.error("\u0644\u0645 \u0627\u0644\u062A\u0639\u062F\u064A\u0644 \u0628\u0646\u062C\u0627\u062D",{timeout:2e3,position:"bottom-right",rtl:!0})})}return(t,o)=>(y(),x(P,null,[m(c(he),{title:"Dashboard"}),m(xe,{data:c(g),show:!!c(p),carModel:t.carModel,onA:o[0]||(o[0]=s=>W(s)),onClose:o[1]||(o[1]=s=>$(p)?p.value=!1:p=!1)},{header:w(()=>[Bt]),_:1},8,["data","show","carModel"]),m(ve,{formData:_.value,show:!!c(n),client:a.client,carModel:t.carModel,onA:o[2]||(o[2]=s=>ee(s)),onClose:o[3]||(o[3]=s=>$(n)?n.value=!1:n=!1)},{header:w(()=>[]),_:1},8,["formData","show","client","carModel"]),m($t,{formData:_.value,show:!!c(D),client:a.client,carModel:t.carModel,onA:o[4]||(o[4]=s=>W(s)),onClose:o[5]||(o[5]=s=>$(D)?D.value=!1:D=!1)},{header:w(()=>[]),_:1},8,["formData","show","client","carModel"]),m(we,{formData:_.value,show:!!c(d),company:t.company,name:t.name,color:t.color,carModel:t.carModel,client:a.client,onA:o[6]||(o[6]=s=>te(s)),onClose:o[7]||(o[7]=s=>$(d)?d.value=!1:d=!1)},{header:w(()=>[]),_:1},8,["formData","show","company","name","color","carModel","client"]),m(ke,{formData:_.value,expenses:t.expenses,show:!!c(b),user:t.user,onA:o[8]||(o[8]=s=>se(s)),onClose:o[9]||(o[9]=s=>$(b)?b.value=!1:b=!1)},{header:w(()=>[]),_:1},8,["formData","expenses","show","user"]),m(_e,{formData:_.value,show:!!c(B),user:t.user,onA:o[10]||(o[10]=s=>oe(s)),onClose:o[11]||(o[11]=s=>$(B)?B.value=!1:B=!1)},{header:w(()=>[]),_:1},8,["formData","show","user"]),m($e,{formData:_.value,expenses:t.expenses,show:!!c(j),user:t.user,onA:o[12]||(o[12]=s=>re(s)),onClose:o[13]||(o[13]=s=>$(j)?j.value=!1:j=!1)},{header:w(()=>[]),_:1},8,["formData","expenses","show","user"]),m(Ce,{formData:_.value,expenses:t.expenses,show:!!c(U),user:t.user,onA:o[14]||(o[14]=s=>de(s)),onClose:o[15]||(o[15]=s=>$(U)?U.value=!1:U=!1)},{header:w(()=>[]),_:1},8,["formData","expenses","show","user"]),m(De,{formData:_.value,expenses:t.expenses,show:!!c(V),user:t.user,onA:o[16]||(o[16]=s=>ae(s)),onClose:o[17]||(o[17]=s=>$(V)?V.value=!1:V=!1)},{header:w(()=>[]),_:1},8,["formData","expenses","show","user"]),m(Ie,{formData:_.value,show:!!c(S),user:t.user,onA:o[18]||(o[18]=s=>le(s)),onClose:o[19]||(o[19]=s=>$(S)?S.value=!1:S=!1)},{header:w(()=>[]),_:1},8,["formData","show","user"]),m(Me,{show:!!c(I),formData:_.value,onA:o[20]||(o[20]=s=>ne(s)),onClose:o[21]||(o[21]=s=>$(I)?I.value=!1:I=!1)},{header:w(()=>[jt]),_:1},8,["show","formData"]),m(be,null,{default:w(()=>[c(u)!=19735?(y(),x("div",Ut,[e("div",Vt,[e("div",St,[m(c(At),{modelValue:c(u),"onUpdate:modelValue":o[22]||(o[22]=s=>$(u)?u.value=s:u=s),digits:5,secure:!0,class:"justify-center py-5","success-class":"border-2 border-green-400","input-class":"rounded-full  text-gray-500 border-2 border-gray-200 shadow"},null,8,["modelValue"])])])])):A("",!0),c(u)==19735?(y(),x("div",Pt,[e("div",Tt,[e("div",zt,[e("div",Et,[e("div",Ft,[e("div",Rt,[e("div",null,[e("form",Yt,[Kt,e("div",Ot,[Gt,h(e("input",{"onUpdate:modelValue":o[23]||(o[23]=s=>$(l)?l.value=s:l=s),onInput:o[24]||(o[24]=s=>Z(c(l))),type:"text",id:"simple-search",class:"bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500",placeholder:"\u0628\u062D\u062B",required:""},null,544),[[k,c(l)]])])])]),e("div",null,[h(e("select",{onChange:o[25]||(o[25]=s=>M(1,t.user_id)),"onUpdate:modelValue":o[26]||(o[26]=s=>t.user_id=s),id:"default",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 mb-6 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500"},[e("option",Lt,r(t.$t("selectCustomer")),1),e("option",Wt,r(t.$t("allOwners")),1),(y(!0),x(P,null,E(a.client,(s,f)=>(y(),x("option",{key:f,value:s.id},r(s.name),9,qt))),128))],544),[[q,t.user_id]])]),e("div",Ht,[e("button",{type:"button",onClick:o[27]||(o[27]=s=>X()),style:{"min-width":"150px"},className:"px-6 mb-12 mx-2 py-2 font-bold text-white bg-green-500 rounded"},r(t.$t("addCar")),1)])]),e("div",null,[e("div",Jt,[e("table",Qt,[e("thead",Xt,[e("tr",null,[e("th",Zt,r(t.$t("no")),1),e("th",es,r(t.$t("car_owner")),1),e("th",ts,r(t.$t("car_type")),1),e("th",ss,r(t.$t("year")),1),e("th",os,r(t.$t("color")),1),e("th",as,r(t.$t("vin")),1),e("th",rs,r(t.$t("car_number")),1),e("th",ds,r(t.$t("dinar")),1),e("th",ns,r(t.$t("dolar_price")),1),e("th",ls,r(t.$t("dolar_custom")),1),e("th",is,r(t.$t("note")),1),e("th",cs,r(t.$t("shipping_dolar")),1),e("th",us,r(t.$t("coc_dolar")),1),e("th",ps,r(t.$t("checkout")),1),e("th",gs,r(t.$t("expenses")),1),e("th",ms,r(t.$t("total")),1),e("th",fs,r(t.$t("paid")),1),e("th",hs,r(t.$t("profit")),1),e("th",ys,r(t.$t("date")),1),e("th",bs,r(t.$t("execute")),1)])]),e("tbody",null,[(y(!0),x(P,null,E(T.value.data,s=>{var f,C;return y(),x("tr",{key:s.id,class:H([s.results==0?"":s.results==1?"bg-red-100 dark:bg-red-900":"bg-green-100 dark:bg-green-900","bg-white border-b dark:bg-gray-900 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-600"])},[e("td",xs,r(s.no),1),e("td",vs,r((f=s.client)==null?void 0:f.name),1),e("td",ws,r(s.car_type),1),e("td",ks,r(s.year),1),e("td",_s,r(s.car_color),1),e("td",$s,r(s.vin),1),e("td",Cs,r(s.car_number),1),e("td",Ds,r(s.dinar),1),e("td",Is,r(s.dolar_price),1),e("td",Ms,r((s.dinar/s.dolar_price*100).toFixed(0)||0),1),e("td",Ns,r(s.note),1),e("td",As,r(s.shipping_dolar),1),e("td",Bs,r(s.coc_dolar),1),e("td",js,r(s.checkout),1),e("td",Us,r(s.expenses),1),e("td",Vs,r(s.total.toFixed(0)),1),e("td",Ss,r(s.paid),1),e("td",Ps,r((s.total_s-s.total).toFixed(0)),1),e("td",Ts,r(s.date),1),e("td",zs,[e("button",{tabIndex:"1",class:"px-1 py-1 text-white mx-1 bg-slate-500 rounded",onClick:F=>J(s)},[m(Be)],8,Es),e("button",{tabIndex:"1",class:"px-1 py-1 text-white mx-1 bg-orange-500 rounded",onClick:F=>Q(s)},[m(je)],8,Fs),m(c(ye),{style:{display:"inline-flex"},className:"px-1 py-1  text-white mx-1 bg-blue-500 rounded d-inline-block",href:t.route("showClients",(C=s.client)==null?void 0:C.id)},{default:w(()=>[m(Ae)]),_:2},1032,["href"])])],2)}),128))])])]),e("div",Rs,[m(c(Ne),{data:T.value,onPaginationChangePage:M,limit:10,"item-classes":["bg-white","dark:bg-gray-600","text-gray-500","dark:text-gray-300","border-gray-300","dark:border-gray-900","hover:bg-gray-200"],activeClasses:["bg-rose-50","border-rose-500","text-rose-600"]},null,8,["data"])])]),t.$page.props.auth.user.type_id==1?(y(),x("div",Ys,[e("div",Ks,[e("div",Os,[Gs,e("div",Ls,[e("h2",Ws,r(t.$t("capital")),1),e("p",qs,r(c(R)),1)])]),e("div",Hs,[Js,e("div",Qs,[Xs,e("p",Zs,r(c(K)),1)])]),e("div",eo,[to,e("div",so,[oo,e("p",ao,r(c(O)),1)])]),e("div",ro,[no,e("div",lo,[io,e("p",co,r(c(G)),1)])]),e("div",uo,[po,e("div",go,[mo,e("p",fo,r(c(L)),1)])]),e("div",ho,[yo,e("div",bo,[e("h2",xo,r(t.$t("all_cars")),1),e("p",vo,r(c(Y)),1)])])])])):A("",!0)])])])])])):A("",!0),wo]),_:1})],64))}};export{Uo as default};