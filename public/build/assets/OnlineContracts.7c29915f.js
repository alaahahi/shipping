import{I as F,r as m,o as y,c as B,w as _,a as h,b as t,y as R,t as a,d as T,e as f,v as b,f as w,T as U,x as it,i as k,j as g,k as N,F as O,H as lt,h as ct,g as W,s as ut}from"./app.a837e6d9.js";import{a as Z,_ as gt}from"./AuthenticatedLayout.c839ed68.js";import"./vue-tailwind-datepicker.f6a81efc.js";import{t as X}from"./laravel-vue-pagination.es.9aa5a546.js";import{a as P}from"./index.d057a337.js";import{s as mt}from"./show.6035ab03.js";import{p as yt}from"./pay.987e0dff.js";const ft={key:0,class:"modal-mask"},bt={class:"modal-wrapper"},ht={class:"modal-container dark:bg-gray-900"},pt={class:"modal-header text-center py-4"},xt={class:"modal-body"},kt={class:"grid grid-cols-2 md:grid-cols-2 lg:grid-cols-2 gap-3 lg:gap-2"},vt={className:"mb-4 mx-5"},_t=t("label",{class:"dark:text-gray-200",for:"amountTotal"},"\u0633\u0639\u0631 \u0627\u0644\u0639\u0642\u062F \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631",-1),wt={className:"mb-4 mx-5"},$t=t("label",{class:"dark:text-gray-200",for:"amountTotal"},"\u0633\u0639\u0631 \u0627\u0644\u0639\u0642\u062F \u0628\u0627\u0644\u062F\u064A\u0646\u0627\u0631",-1),Ct={class:"grid grid-cols-2 md:grid-cols-2 lg:grid-cols-2 gap-3 lg:gap-2"},Dt={className:"mb-4 mx-5"},At=t("label",{class:"dark:text-gray-200",for:"amountPayment"},"\u0627\u0644\u0645\u0628\u0644\u063A \u0627\u0644\u0645\u062F\u0641\u0639 \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631",-1),Mt={className:"mb-4 mx-5"},Nt=t("label",{class:"dark:text-gray-200",for:"amountPayment"},"\u0627\u0644\u0645\u0628\u0644\u063A \u0627\u0644\u0645\u062F\u0641\u0639 \u0628\u0627\u0644\u062F\u064A\u0646\u0627\u0631",-1),jt={className:"mb-4 mx-5"},Et={class:"dark:text-gray-200",for:"notePayment"},Pt={className:"mb-4 mx-5"},Bt={class:"dark:text-gray-200",for:"notePayment"},Tt={class:"modal-footer my-2"},Ut={class:"flex flex-row"},St={class:"basis-1/2 px-4"},It={class:"basis-1/2 px-4"},Vt={__name:"ModalAddCarContracts",props:{show:Boolean,company:Array,color:Array,carModel:Array,name:Array,client:Array,user:Array,expenses:Array,formData:Object},setup(e){const n=e;let c=F();m(0);function u(){let d=n.formData.prices;n.formData.paids>d&&(n.formData.paids=n.formData.prices,c.info(" \u0627\u0644\u0645\u0628\u0644\u063A \u0627\u0643\u0628\u0631 \u0645\u0646 \u0627\u0644\u062F\u064A\u0646 \u0627\u0644\u0645\u0637\u0644\u0648\u0628 "+n.formData.prices,{timeout:4e3,position:"bottom-right",rtl:!0}))}function p(){let d=n.formData.price_dinars;n.formData.paid_dinars>d&&(n.formData.paid_dinars=n.formData.price_dinars,c.info(" \u0627\u0644\u0645\u0628\u0644\u063A \u0627\u0643\u0628\u0631 \u0645\u0646 \u0627\u0644\u062F\u064A\u0646 \u0627\u0644\u0645\u0637\u0644\u0648\u0628 "+n.formData.price_dinars,{timeout:4e3,position:"bottom-right",rtl:!0}))}return(d,s)=>(y(),B(U,{name:"modal"},{default:_(()=>[e.show?(y(),h("div",ft,[t("div",bt,[t("div",ht,[t("div",pt,[R(" \u0625\u0636\u0627\u0641\u0629 \u0639\u0642\u062F \u0627\u0644\u0643\u062A\u0631\u0648\u0646\u064A \u0644\u0644\u0633\u064A\u0627\u0631\u0629 "+a(e.formData.car_type)+" "+a(e.formData.year)+" "+a(e.formData.car_color)+" \u0631\u0642\u0645 \u0634\u0627\u0646\u0635\u0649 "+a(e.formData.vin)+" ",1),T(d.$slots,"header")]),t("div",xt,[t("div",null,[t("div",kt,[t("div",vt,[_t,f(t("input",{id:"amountTotal",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":s[0]||(s[0]=l=>e.formData.prices=l)},null,512),[[b,e.formData.prices]])]),t("div",wt,[$t,f(t("input",{id:"amountTotal",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":s[1]||(s[1]=l=>e.formData.price_dinars=l)},null,512),[[b,e.formData.price_dinars]])])]),t("div",Ct,[t("div",Dt,[At,f(t("input",{id:"amountPayment",type:"number",onInput:u,class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":s[2]||(s[2]=l=>e.formData.paids=l)},null,544),[[b,e.formData.paids]])]),t("div",Mt,[Nt,f(t("input",{id:"amountPayment",type:"number",onInput:p,class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":s[3]||(s[3]=l=>e.formData.paid_dinars=l)},null,544),[[b,e.formData.paid_dinars]])])]),t("div",jt,[t("label",Et,a(d.$t("phone")),1),f(t("input",{id:"notePayment",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":s[4]||(s[4]=l=>e.formData.phone=l)},null,512),[[b,e.formData.phone]])]),t("div",Pt,[t("label",Bt,a(d.$t("note")),1),f(t("input",{id:"notePayment",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":s[5]||(s[5]=l=>e.formData.note=l)},null,512),[[b,e.formData.note]])])])]),t("div",Tt,[t("div",Ut,[t("div",St,[t("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:s[6]||(s[6]=l=>{d.$emit("close")})},a(d.$t("cancel")),1)]),t("div",It,[t("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:s[7]||(s[7]=l=>{d.$emit("a",e.formData),e.formData=""})},a(d.$t("yes")),1)])])])])])])):w("",!0)]),_:3}))}};const zt={key:0,class:"modal-mask"},Ot={class:"modal-wrapper"},Ft={class:"modal-container dark:bg-gray-900"},Rt={class:"modal-header"},Ht={class:"modal-body"},qt={class:"grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 gap-3 lg:gap-3"},Lt={className:"mb-4 mx-5"},Yt={class:"dark:text-gray-200",for:"user_id"},Gt={className:"mb-4 mx-5"},Jt={class:"dark:text-gray-200",for:"user_id"},Kt={className:"mb-4 mx-5"},Qt={class:"dark:text-gray-200",for:"userId"},Wt=["value"],Xt={class:"grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 gap-3 lg:gap-3"},Zt={className:"mb-4 mx-5"},te={class:"dark:text-gray-200",for:"user_id"},ee={className:"mb-4 mx-5"},ae={class:"dark:text-gray-200",for:"user_id"},oe={className:"mb-4 mx-5"},re={class:"dark:text-gray-200",for:"userId"},se=["value"],de={className:"mb-4 mx-5"},ne={class:"dark:text-gray-200",for:"amountPayment"},ie={className:"mb-4 mx-5"},le={class:"dark:text-gray-200",for:"amountPayment"},ce={className:"mb-4 mx-5"},ue={class:"dark:text-gray-200",for:"notePayment"},ge={class:"modal-footer my-2"},me={class:"flex flex-row"},ye={class:"basis-1/2 px-4"},fe={class:"basis-1/2 px-4"},be=["disabled"],he={__name:"ModalEditCarContracts",props:{show:Boolean,company:Array,color:Array,carModel:Array,name:Array,client:Array,user:Array,expenses:Array,formData:Object},setup(e){const n=e;let c=F();function u(){let d=n.formData.contract.price-n.formData.contract.paid;n.formData.paids>d&&(n.formData.paids=d,c.info(" \u0627\u0644\u0645\u0628\u0644\u063A \u0627\u0643\u0628\u0631 \u0645\u0646 \u0627\u0644\u062F\u064A\u0646 \u0627\u0644\u0645\u0637\u0644\u0648\u0628 "+d,{timeout:4e3,position:"bottom-right",rtl:!0}))}function p(){let d=n.formData.contract.price_dinar-n.formData.contract.paid_dinar;n.formData.paid_dinars>d&&(n.formData.paid_dinars=d,c.info(" \u0627\u0644\u0645\u0628\u0644\u063A \u0627\u0643\u0628\u0631 \u0645\u0646 \u0627\u0644\u062F\u064A\u0646 \u0627\u0644\u0645\u0637\u0644\u0648\u0628 "+d,{timeout:4e3,position:"bottom-right",rtl:!0}))}return(d,s)=>(y(),B(U,{name:"modal"},{default:_(()=>[e.show?(y(),h("div",zt,[t("div",Ot,[t("div",Ft,[t("div",Rt,[T(d.$slots,"header")]),t("div",Ht,[t("div",qt,[t("div",Lt,[t("label",Yt,a(d.$t("totalForCar"))+" \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631",1),f(t("input",{type:"text",style:{display:"none"},disabled:"","onUpdate:modelValue":s[0]||(s[0]=l=>e.formData.id=l)},null,512),[[b,e.formData.id]]),f(t("input",{type:"text",disabled:"",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":s[1]||(s[1]=l=>e.formData.contract.price=l)},null,512),[[b,e.formData.contract.price]])]),t("div",Gt,[t("label",Jt,a(d.$t("paid_amount"))+" \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631",1),f(t("input",{type:"text",disabled:"",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":s[2]||(s[2]=l=>e.formData.contract.paid=l)},null,512),[[b,e.formData.contract.paid]])]),t("div",Kt,[t("label",Qt,a(d.$t("debtRemaining"))+" \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631",1),t("input",{type:"text",disabled:"",value:e.formData.contract.price-e.formData.contract.paid,class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"},null,8,Wt)])]),t("div",Xt,[t("div",Zt,[t("label",te,a(d.$t("totalForCar"))+" \u0628\u0627\u0644\u062F\u064A\u0646\u0627\u0631",1),f(t("input",{type:"text",style:{display:"none"},disabled:"","onUpdate:modelValue":s[3]||(s[3]=l=>e.formData.id=l)},null,512),[[b,e.formData.id]]),f(t("input",{type:"text",disabled:"",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":s[4]||(s[4]=l=>e.formData.contract.price_dinar=l)},null,512),[[b,e.formData.contract.price_dinar]])]),t("div",ee,[t("label",ae,a(d.$t("paid_amount"))+" \u0628\u0627\u0644\u062F\u064A\u0646\u0627\u0631",1),f(t("input",{type:"text",disabled:"",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":s[5]||(s[5]=l=>e.formData.contract.paid_dinar=l)},null,512),[[b,e.formData.contract.paid_dinar]])]),t("div",oe,[t("label",re,a(d.$t("debtRemaining"))+" \u0628\u0627\u0644\u062F\u064A\u0646\u0627\u0631",1),t("input",{type:"text",disabled:"",value:e.formData.contract.price_dinar-e.formData.contract.paid_dinar,class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"},null,8,se)])]),t("div",null,[t("div",de,[t("label",ne,a(d.$t("amount"))+" \u0628\u0627\u0644\u062F\u0648\u0644\u0627\u0631",1),f(t("input",{id:"amountPayment",type:"number",onInput:u,class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":s[6]||(s[6]=l=>e.formData.paids=l)},null,544),[[b,e.formData.paids]])]),t("div",ie,[t("label",le,a(d.$t("amount"))+" \u0628\u0627\u0644\u062F\u064A\u0646\u0627\u0631",1),f(t("input",{onInput:p,id:"amountPayment",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":s[7]||(s[7]=l=>e.formData.paid_dinars=l)},null,544),[[b,e.formData.paid_dinars]])]),t("div",ce,[t("label",ue,a(d.$t("note")),1),f(t("input",{id:"notePayment",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":s[8]||(s[8]=l=>e.formData.notePayment=l)},null,512),[[b,e.formData.notePayment]])])])]),t("div",ge,[t("div",me,[t("div",ye,[t("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:s[9]||(s[9]=l=>{d.$emit("close")})},a(d.$t("cancel")),1)]),t("div",fe,[t("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:s[10]||(s[10]=l=>{d.$emit("a",e.formData),e.formData=""}),disabled:!(e.formData.paids||e.formData.paid_dinars)},a(d.$t("yes")),9,be)])])])])])])):w("",!0)]),_:3}))}};const pe={key:0,class:"modal-mask"},xe={class:"modal-wrapper"},ke={class:"modal-container dark:bg-gray-900"},ve={class:"modal-header text-center py-4"},_e={class:"modal-body"},we={class:"grid grid-cols-2 md:grid-cols-2 lg:grid-cols-2 gap-3 lg:gap-2"},$e={className:"mb-4 mx-5"},Ce=t("label",{class:"dark:text-gray-200",for:"amountTotal"},"\u0631\u0642\u0645 \u0627\u0644\u0647\u0627\u062A\u0641",-1),De={className:"mb-4 mx-5"},Ae=t("label",{class:"dark:text-gray-200",for:"amountTotal"},"\u062A\u0627\u0631\u064A\u062E \u0627\u0644\u062E\u0631\u0648\u062C\u064A\u0629",-1),Me={className:"mb-4 mx-5"},Ne={class:"dark:text-gray-200",for:"notePayment"},je={class:"modal-footer my-2"},Ee={class:"flex flex-row"},Pe={class:"basis-1/2 px-4"},Be={class:"basis-1/2 px-4"},Te={__name:"ModalAddExitCar",props:{show:Boolean,company:Array,color:Array,carModel:Array,name:Array,client:Array,user:Array,expenses:Array,formData:Object},setup(e){return m(0),(n,c)=>(y(),B(U,{name:"modal"},{default:_(()=>[e.show?(y(),h("div",pe,[t("div",xe,[t("div",ke,[t("div",ve,[R(" \u062E\u0631\u0648\u062C\u064A\u0629 \u0644\u0644\u0633\u064A\u0627\u0631\u0629 "+a(e.formData.car_type)+" "+a(e.formData.year)+" "+a(e.formData.car_color)+" \u0631\u0642\u0645 \u0634\u0627\u0646\u0635\u0649 "+a(e.formData.vin)+" ",1),T(n.$slots,"header")]),t("div",_e,[t("div",null,[t("div",we,[t("div",$e,[Ce,f(t("input",{id:"amountTotal",type:"number",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":c[0]||(c[0]=u=>e.formData.phoneExit=u)},null,512),[[b,e.formData.phoneExit]])]),t("div",De,[Ae,f(t("input",{id:"amountTotal",type:"date",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":c[1]||(c[1]=u=>e.formData.createdExit=u)},null,512),[[b,e.formData.createdExit]])])]),t("div",Me,[t("label",Ne,a(n.$t("note")),1),f(t("input",{id:"notePayment",type:"text",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900","onUpdate:modelValue":c[2]||(c[2]=u=>e.formData.noteExit=u)},null,512),[[b,e.formData.noteExit]])])])]),t("div",je,[t("div",Ee,[t("div",Pe,[t("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:c[3]||(c[3]=u=>{n.$emit("close")})},a(n.$t("cancel")),1)]),t("div",Be,[t("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:c[4]||(c[4]=u=>{n.$emit("a",e.formData),e.formData=""})},a(n.$t("yes")),1)])])])])])])):w("",!0)]),_:3}))}};const Ue={key:0,class:"modal-mask"},Se={class:"modal-wrapper"},Ie={class:"modal-container dark:bg-gray-900"},Ve={class:"modal-header text-center py-4"},ze={class:"modal-body"},Oe={class:"grid grid-cols-2 md:grid-cols-2 lg:grid-cols-2 gap-3 lg:gap-2"},Fe={className:"mb-4 mx-5"},Re=t("label",{class:"dark:text-gray-200",for:"amountTotal"},"\u0631\u0642\u0645 \u0627\u0644\u0647\u0627\u062A\u0641",-1),He=["value"],qe={className:"mb-4 mx-5"},Le=t("label",{class:"dark:text-gray-200",for:"amountTotal"},"\u062A\u0627\u0631\u064A\u062E \u0627\u0644\u062E\u0631\u0648\u062C\u064A\u0629",-1),Ye=["value"],Ge={className:"mb-4 mx-5"},Je={class:"dark:text-gray-200",for:"notePayment"},Ke=["value"],Qe={class:"modal-footer my-2"},We={class:"flex flex-row"},Xe={class:"basis-1/2 px-4"},Ze={class:"basis-1/2 px-4"},ta=["disabled"],ea={__name:"ModalShowExitCar",props:{show:Boolean,company:Array,color:Array,carModel:Array,name:Array,client:Array,user:Array,expenses:Array,formData:Object},setup(e){return m(0),(n,c)=>(y(),B(U,{name:"modal"},{default:_(()=>{var u,p,d;return[e.show?(y(),h("div",Ue,[t("div",Se,[t("div",Ie,[t("div",Ve,[R(" \u0639\u0631\u0636 \u062E\u0631\u0648\u062C\u064A\u0629 "+a(e.formData.car_type)+" "+a(e.formData.year)+" "+a(e.formData.car_color)+" \u0631\u0642\u0645 \u0634\u0627\u0646\u0635\u0649 "+a(e.formData.vin)+" ",1),T(n.$slots,"header")]),t("div",ze,[t("div",null,[t("div",Oe,[t("div",Fe,[Re,t("input",{id:"amountTotal",type:"number",disabled:"",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900",value:(u=e.formData.exitcar)==null?void 0:u.phone},null,8,He)]),t("div",qe,[Le,t("input",{id:"amountTotal",type:"date",disabled:"",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900",value:(p=e.formData.exitcar)==null?void 0:p.created},null,8,Ye)])]),t("div",Ge,[t("label",Je,a(n.$t("note")),1),t("input",{id:"notePayment",type:"text",disabled:"",class:"mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900",value:(d=e.formData.exitcar)==null?void 0:d.note},null,8,Ke)])])]),t("div",Qe,[t("div",We,[t("div",Xe,[t("button",{class:"modal-default-button py-3 bg-gray-500 rounded",onClick:c[0]||(c[0]=s=>{n.$emit("close")})},a(n.$t("cancel")),1)]),t("div",Ze,[t("button",{class:"modal-default-button py-3 bg-rose-500 rounded col-6",onClick:c[1]||(c[1]=s=>{n.$emit("a",e.formData),e.formData=""}),disabled:!(e.formData.prices||e.formData.price_dinars)},a(n.$t("yes")),9,ta)])])])])])])):w("",!0)]}),_:3}))}},aa={},oa={xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24","stroke-width":"1.5",stroke:"currentColor",class:"w-6 h-6"},ra=t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"},null,-1),sa=[ra];function da(e,n){return y(),h("svg",oa,sa)}const na=Z(aa,[["render",da]]),ia={},la={xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24","stroke-width":"1.5",stroke:"currentColor",class:"w-6 h-6"},ca=t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 10.5v6m3-3H9m4.06-7.19l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"},null,-1),ua=[ca];function ga(e,n){return y(),h("svg",la,ua)}const ma=Z(ia,[["render",ga]]),ya={key:0,class:"py-2"},fa={class:"max-w-9xl mx-auto sm:px-6 lg:px-8"},ba={class:"bg-white overflow-hidden shadow-sm"},ha={class:"p-6 dark:bg-gray-900"},pa={class:"flex flex-col"},xa={class:"grid grid-cols-2 md:grid-cols-5 lg:grid-cols-5 gap-2 lg:gap-1"},ka={class:"flex items-center max-w-5xl"},va=t("label",{class:"dark:text-gray-200",for:"simple-search"},null,-1),_a={class:"relative w-full"},wa=t("div",{class:"absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"},[t("svg",{"aria-hidden":"true",class:"w-5 h-5 text-gray-500 dark:text-gray-200 dark:text-gray-400",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[t("path",{"fill-rule":"evenodd",d:"M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z","clip-rule":"evenodd"})])],-1),$a={value:"undefined",disabled:""},Ca={value:""},Da=["value"],Aa={class:"mt-4 grid grid-cols-1 gap-4 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-7"},Ma={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},Na=t("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50"},[t("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-orange-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"})])],-1),ja={class:"mr-4"},Ea={class:"font-semibold"},Pa={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},Ba={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},Ta=t("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50"},[t("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-orange-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"})])],-1),Ua={class:"mr-4"},Sa={class:"font-semibold"},Ia={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},Va={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},za=t("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50"},[t("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-orange-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"})])],-1),Oa={class:"mr-4"},Fa={class:"font-semibold"},Ra={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},Ha={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},qa=t("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50"},[t("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-orange-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"})])],-1),La={class:"mr-4"},Ya={class:"font-semibold"},Ga={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},Ja={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},Ka=t("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50"},[t("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-red-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"})])],-1),Qa={class:"mr-4"},Wa=t("h2",{class:"font-semibold"}," \u0627\u0644\u0639\u0642\u0648\u062F \u0627\u0644\u0645\u0646\u062C\u0632\u0629 ",-1),Xa={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},Za={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},to=t("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50"},[t("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-red-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"})])],-1),eo={class:"mr-4"},ao=t("h2",{class:"font-semibold"}," \u0627\u0644\u0639\u0642\u0648\u062F \u0627\u0644\u0645\u062A\u0628\u0642\u064A\u0629 ",-1),oo={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},ro={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},so=t("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50"},[t("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-red-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"})])],-1),no={class:"mr-4"},io={class:"font-semibold"},lo={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},co={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},uo=t("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50"},[t("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-red-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"})])],-1),go={class:"mr-4"},mo=t("h2",{class:"font-semibold"}," \u062E\u0631\u0648\u062C\u064A\u0629 \u0627\u0644\u0633\u064A\u0627\u0631\u0627\u062A ",-1),yo={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},fo={class:"flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"},bo=t("div",{class:"flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50"},[t("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6 text-red-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"})])],-1),ho={class:"mr-4"},po=t("h2",{class:"font-semibold"}," \u0627\u0644\u0633\u064A\u0627\u0631\u0627\u062A \u0627\u0644\u0645\u062A\u0628\u0642\u064A\u0629 ",-1),xo={class:"mt-2 text-sm text-gray-500 dark:text-gray-200"},ko={class:"mt-3 text-center",style:{direction:"ltr"}},vo={class:"relative overflow-x-auto shadow-md sm:rounded-lg"},_o={class:"w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center"},wo={class:"text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center"},$o={scope:"col",class:"px-1 py-3 text-base"},Co={scope:"col",class:"px-1 py-3 text-base"},Do={scope:"col",class:"px-1 py-3 text-base"},Ao={scope:"col",class:"px-1 py-3 text-base"},Mo={scope:"col",class:"px-1 py-3 text-base"},No={scope:"col",class:"px-1 py-3 text-base"},jo={scope:"col",class:"px-1 py-3 text-base"},Eo={scope:"col",class:"px-1 py-3 text-base"},Po=t("th",{scope:"col",class:"px-1 py-3 text-base"}," \u0645\u062F\u0641\u0648\u0639 \u062F\u0648\u0644\u0627\u0631 ",-1),Bo=t("th",{scope:"col",class:"px-1 py-3 text-base"}," \u0645\u062F\u0641\u0648\u0639 \u062F\u064A\u0646\u0627\u0631 ",-1),To={scope:"col",class:"px-1 py-3 text-base"},Uo={scope:"col",class:"px-1 py-3 text-base",style:{width:"150px"}},So={className:"border dark:border-gray-800 text-center px-2 py-2 "},Io={className:"border dark:border-gray-800 text-center px-2 py-2 "},Vo={className:"border dark:border-gray-800 text-center px-2 py-2 "},zo={className:"border dark:border-gray-800 text-center px-2 py-2 "},Oo={className:"border dark:border-gray-800 text-center px-2 py-2 "},Fo={className:"border dark:border-gray-800 text-center px-2 py-2 "},Ro={className:"border dark:border-gray-800 text-center px-2 py-2 "},Ho={className:"border dark:border-gray-800 text-center px-2 py-2 "},qo={className:"border dark:border-gray-800 text-center px-2 py-2 "},Lo={className:"border dark:border-gray-800 text-center px-2 py-2 "},Yo={className:"border dark:border-gray-800 text-center px-2 py-2 "},Go={className:"border dark:border-gray-800 text-start px-2 py-2"},Jo=["onClick"],Ko=["onClick"],Qo=["onClick"],Wo=["onClick"],Xo={class:"mt-3 text-center",style:{direction:"ltr"}},Zo=t("div",null,null,-1),nr={__name:"OnlineContracts",props:{client:Array},setup(e){it(),m({});const n=F();let c=m(""),u=m(!1),p=m(!1),d=m(!1),s=m(!1),l=m(0),H=m(0),q=m(0),L=m(0),S=m(0),I=m(0),j=m(0);function tt(o={}){v.value=o,v.value.prices=100,v.value.price_dinars=5e4,u.value=!0}function et(o={}){v.value=o,p.value=!0}function at(o={}){v.value=o,v.value.createdExit=nt(),d.value=!0}function ot(o={}){v.value=o,s.value=!0}const v=m({});m({});const D=m([]);m({startDate:"",endDate:""});const $=async(o=1,i="")=>{const r=await fetch(`/getIndexCar?page=${o}&user_id=${i}`);D.value=await r.json()},rt=async(o="",i=1)=>{const r=await fetch(`/getIndexCarSearch?page=${i}&q=${o}`);D.value=await r.json()},V=async()=>{P.get("/api/totalInfo").then(o=>{S.value=o.data.data.contarts,I.value=o.data.data.exitCar,l.value=o.data.data.onlineContracts,q.value=o.data.data.onlineContractsDinar,L.value=o.data.data.debtOnlineContractsDinar,H.value=o.data.data.debtOnlineContracts,j.value=o.data.data.allCars}).catch(o=>{console.error(o)})};V(),$();function st(o){var i,r,x,C,A,M;P.get(`/api/addCarContracts?car_id=${o.id}&price=${(i=o.prices)!=null?i:0}&price_dinar=${(r=o.price_dinars)!=null?r:0}&paid=${(x=o.paids)!=null?x:0}&paid_dinar=${(C=o.paid_dinars)!=null?C:0}&phone=${(A=o.phone)!=null?A:""}&note=${(M=o.note)!=null?M:""}`).then(E=>{u.value=!1,n.success(" \u062A\u0645 \u062F\u0641\u0639 \u0645\u0628\u0644\u063A \u0628\u0646\u062C\u0627\u062D ",{timeout:4e3,position:"bottom-right",rtl:!0}),$(),V()}).catch(E=>{u.value=!1,n.error("\u0644\u0645 \u0627\u0644\u062A\u0639\u062F\u064A\u0644 \u0628\u0646\u062C\u0627\u062D",{timeout:2e3,position:"bottom-right",rtl:!0})})}function dt(o){var i,r,x;P.get(`/api/editCarContracts?car_id=${o.id}&paid=${(i=o.paids)!=null?i:0}&paid_dinar=${(r=o.paid_dinars)!=null?r:0}&note=${(x=o.notePayment)!=null?x:""}`).then(C=>{p.value=!1,n.success(" \u062A\u0645 \u062F\u0641\u0639 \u0645\u0628\u0644\u063A \u062F\u0648\u0644\u0627\u0631 "+o.amountPayment+" \u0628\u0646\u062C\u0627\u062D ",{timeout:3e3,position:"bottom-right",rtl:!0}),$(),V()}).catch(C=>{p.value=!1,n.error("\u0644\u0645 \u0627\u0644\u062A\u0639\u062F\u064A\u0644 \u0628\u0646\u062C\u0627\u062D",{timeout:2e3,position:"bottom-right",rtl:!0})})}function Y(o){P.get(`/api/makeCarExit?car_id=${o.id}&created=${o.createdExit}&phone=${o.phoneExit}&note=${o.noteExit}`).then(i=>{d.value=!1,n.success("\u062A\u0645 \u0627\u0636\u0627\u0641\u0629 \u062E\u0631\u0648\u062C\u064A\u0629 \u0644\u0644\u0633\u064A\u0627\u0631\u0629 \u0628\u0646\u062C\u0627\u062D ",{timeout:5e3,position:"bottom-right",rtl:!0}),$()}).catch(i=>{d.value=!1,n.error("\u0644\u0645 \u0627\u0644\u062A\u0639\u062F\u064A\u0644 \u0628\u0646\u062C\u0627\u062D",{timeout:2e3,position:"bottom-right",rtl:!0})})}function nt(){const o=new Date,i=o.getFullYear(),r=String(o.getMonth()+1).padStart(2,"0"),x=String(o.getDate()).padStart(2,"0");return`${i}-${r}-${x}`}return(o,i)=>(y(),h(O,null,[k(g(lt),{title:"Dashboard"}),k(Vt,{formData:v.value,show:!!g(u),user:o.user,onA:i[0]||(i[0]=r=>st(r)),onClose:i[1]||(i[1]=r=>N(u)?u.value=!1:u=!1)},{header:_(()=>[]),_:1},8,["formData","show","user"]),k(he,{formData:v.value,show:!!g(p),user:o.user,onA:i[2]||(i[2]=r=>dt(r)),onClose:i[3]||(i[3]=r=>N(p)?p.value=!1:p=!1)},{header:_(()=>[]),_:1},8,["formData","show","user"]),k(Te,{formData:v.value,show:!!g(d),user:o.user,onA:i[4]||(i[4]=r=>Y(r)),onClose:i[5]||(i[5]=r=>N(d)?d.value=!1:d=!1)},{header:_(()=>[]),_:1},8,["formData","show","user"]),k(ea,{formData:v.value,show:!!g(s),user:o.user,onA:i[6]||(i[6]=r=>Y(r)),onClose:i[7]||(i[7]=r=>N(s)?s.value=!1:s=!1)},{header:_(()=>[]),_:1},8,["formData","show","user"]),k(gt,null,{default:_(()=>[o.$page.props.auth.user.type_id==1?(y(),h("div",ya,[t("div",fa,[t("div",ba,[t("div",ha,[t("div",pa,[t("div",xa,[t("div",null,[t("form",ka,[va,t("div",_a,[wa,f(t("input",{"onUpdate:modelValue":i[8]||(i[8]=r=>N(c)?c.value=r:c=r),onInput:i[9]||(i[9]=r=>rt(g(c))),type:"text",id:"simple-search",class:"bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500",placeholder:"\u0628\u062D\u062B",required:""},null,544),[[b,g(c)]])])])]),t("div",null,[f(t("select",{onChange:i[10]||(i[10]=r=>$(1,o.user_id)),"onUpdate:modelValue":i[11]||(i[11]=r=>o.user_id=r),id:"default",class:"pr-8 bg-gray-50 border border-gray-300 text-gray-900 mb-6 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500"},[t("option",$a,a(o.$t("selectCustomer")),1),t("option",Ca,a(o.$t("allOwners")),1),(y(!0),h(O,null,W(e.client,(r,x)=>(y(),h("option",{key:x,value:r.id},a(r.name),9,Da))),128))],544),[[ct,o.user_id]])])]),t("div",null,[t("div",Aa,[t("div",Ma,[Na,t("div",ja,[t("h2",Ea,a(o.$t("online_contracts")),1),t("p",Pa,a(g(l))+" \u062F\u0648\u0644\u0627\u0631",1)])]),t("div",Ba,[Ta,t("div",Ua,[t("h2",Sa,a(o.$t("debtOnlineContracts")),1),t("p",Ia,a(g(H))+" \u062F\u0648\u0644\u0627\u0631",1)])]),t("div",Va,[za,t("div",Oa,[t("h2",Fa,a(o.$t("online_contracts")),1),t("p",Ra,a(g(q))+" \u062F\u064A\u0646\u0627\u0631",1)])]),t("div",Ha,[qa,t("div",La,[t("h2",Ya,a(o.$t("debtOnlineContracts")),1),t("p",Ga,a(g(L))+" \u062F\u064A\u0646\u0627\u0631",1)])]),t("div",Ja,[Ka,t("div",Qa,[Wa,t("p",Xa,a(g(S)),1)])]),t("div",Za,[to,t("div",eo,[ao,t("p",oo,a(g(j)-g(S)),1)])]),t("div",ro,[so,t("div",no,[t("h2",io,a(o.$t("all_cars")),1),t("p",lo,a(g(j)),1)])]),t("div",co,[uo,t("div",go,[mo,t("p",yo,a(g(I)),1)])]),t("div",fo,[bo,t("div",ho,[po,t("p",xo,a(g(j)-g(I)),1)])])])]),t("div",null,[t("div",null,[t("div",ko,[k(g(X),{data:D.value,onPaginationChangePage:$,limit:10,"item-classes":["bg-white","dark:bg-gray-600","text-gray-500","dark:text-gray-300","border-gray-300","dark:border-gray-900","hover:bg-gray-200"],activeClasses:["bg-rose-50","border-rose-500","text-rose-600"]},null,8,["data"])])]),t("div",vo,[t("table",_o,[t("thead",wo,[t("tr",null,[t("th",$o,a(o.$t("no")),1),t("th",Co,a(o.$t("car_owner")),1),t("th",Do,a(o.$t("car_type")),1),t("th",Ao,a(o.$t("year")),1),t("th",Mo,a(o.$t("color")),1),t("th",No,a(o.$t("vin")),1),t("th",jo,a(o.$t("car_number")),1),t("th",Eo,a(o.$t("date")),1),Po,Bo,t("th",To,a(o.$t("note")),1),t("th",Uo,a(o.$t("execute")),1)])]),t("tbody",null,[(y(!0),h(O,null,W(D.value.data,r=>{var x,C,A,M,E,G,J,K,Q;return y(),h("tr",{key:r.id,class:ut([r.results==0?"":r.results==1?"bg-red-100 dark:bg-red-900":"bg-green-100 dark:bg-green-900","bg-white border-b dark:bg-gray-900 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-600"])},[t("td",So,a(r.no),1),t("td",Io,a((x=r.client)==null?void 0:x.name),1),t("td",Vo,a(r.car_type),1),t("td",zo,a(r.year),1),t("td",Oo,a(r.car_color),1),t("td",Fo,a(r.vin),1),t("td",Ro,a(r.car_number),1),t("td",Ho,a((C=r.contract)==null?void 0:C.created),1),t("td",qo,a(((A=r.contract)==null?void 0:A.paid)||0),1),t("td",Lo,a(((M=r.contract)==null?void 0:M.paid_dinar)||0),1),t("td",Yo,a((E=r.contract)==null?void 0:E.note),1),t("td",Go,[((G=r.contract)==null?void 0:G.price)!=((J=r.contract)==null?void 0:J.paid)||((K=r.contract)==null?void 0:K.price_dinar)!=((Q=r.contract)==null?void 0:Q.paid_dinar)?(y(),h("button",{key:0,tabIndex:"1",class:"px-2 py-1 text-white mx-1 bg-pink-500 rounded",onClick:z=>et(r)},[k(yt)],8,Jo)):w("",!0),r.contract?w("",!0):(y(),h("button",{key:1,tabIndex:"1",class:"px-2 py-1 text-white mx-1 bg-yellow-500 rounded",onClick:z=>tt(r)},[k(ma)],8,Ko)),r.is_exit?w("",!0):(y(),h("button",{key:2,tabIndex:"1",class:"px-2 py-1 text-white mx-1 bg-red-500 rounded",onClick:z=>at(r)},[k(na)],8,Qo)),r.is_exit?(y(),h("button",{key:3,tabIndex:"1",class:"px-2 py-1 text-white mx-1 bg-green-500 rounded",onClick:z=>ot(r)},[k(mt)],8,Wo)):w("",!0)])],2)}),128))])])]),t("div",Xo,[k(g(X),{data:D.value,onPaginationChangePage:$,limit:10,"item-classes":["bg-white","dark:bg-gray-600","text-gray-500","dark:text-gray-300","border-gray-300","dark:border-gray-900","hover:bg-gray-200"],activeClasses:["bg-rose-50","border-rose-500","text-rose-600"]},null,8,["data"])])])])])])])])):w("",!0),Zo]),_:1})],64))}};export{nr as default};
