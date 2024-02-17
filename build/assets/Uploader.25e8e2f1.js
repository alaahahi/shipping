import{_ as f}from"./AuthenticatedLayout.97f59875.js";import{o as s,a as l,b as t,c as g,w as x,e as w,n as p,K as _,d as b,M as L,t as C,h as y,y as S,T as z,N as k,j as A,F as m,f as h,G as N,I as B}from"./app.9ace330f.js";/* empty css                                                                 */import{a as F}from"./index.3f82cb61.js";const I={name:"line-scale",props:{color:{type:String,default:"#CCC"},size:{type:String,default:"40px"},duration:{type:String,default:"0.6s"}}},T=["width","height"],D=["fill"],$=["dur"],X=["dur"],R=["fill"],U=["dur"],V=["dur"],Y=["fill"],j=["dur"],E=["dur"];function O(i,a,e,u,n,d){return s(),l("svg",{version:"1.1",id:"Layer_1",xmlns:"http://www.w3.org/2000/svg","xmlns:xlink":"http://www.w3.org/1999/xlink",x:"0px",y:"0px",width:e.size,height:e.size,viewBox:"0 0 24 30",style:{"enable-background":"new 0 0 50 50"},"xml:space":"preserve"},[t("rect",{x:"0",y:"13",width:"4",height:"5",fill:e.color},[t("animate",{attributeName:"height",attributeType:"XML",values:"5;21;5",begin:"0s",dur:e.duration,repeatCount:"indefinite"},null,8,$),t("animate",{attributeName:"y",attributeType:"XML",values:"13; 5; 13",begin:"0s",dur:e.duration,repeatCount:"indefinite"},null,8,X)],8,D),t("rect",{x:"10",y:"13",width:"4",height:"5",fill:e.color},[t("animate",{attributeName:"height",attributeType:"XML",values:"5;21;5",begin:"0.15s",dur:e.duration,repeatCount:"indefinite"},null,8,U),t("animate",{attributeName:"y",attributeType:"XML",values:"13; 5; 13",begin:"0.15s",dur:e.duration,repeatCount:"indefinite"},null,8,V)],8,R),t("rect",{x:"20",y:"13",width:"4",height:"5",fill:e.color},[t("animate",{attributeName:"height",attributeType:"XML",values:"5;21;5",begin:"0.3s",dur:e.duration,repeatCount:"indefinite"},null,8,j),t("animate",{attributeName:"y",attributeType:"XML",values:"13; 5; 13",begin:"0.3s",dur:e.duration,repeatCount:"indefinite"},null,8,E)],8,Y)],8,T)}const G=f(I,[["render",O]]),H={LineScale:G},K={name:"vue-element-loading",props:{active:Boolean,spinner:{type:String,default:"spinner"},text:{type:String,default:""},textStyle:{type:Object,default:function(){return{}}},color:{type:String,default:"#000"},isFullScreen:{type:Boolean,default:!1},backgroundColor:{type:String,default:"rgba(255, 255, 255, .9)"},size:{type:String,default:"40"},duration:{type:String,default:"0.6"},delay:{type:[String,Number],default:0}},data(){return{isActive:this.active||!1,isActiveDelay:!1}},mounted(){if(this.$refs.velmld.parentNode.classList.add("velmld-parent"),this.delay){const i=+this.delay*1e3;this.delayActive(i)}},methods:{delayActive(i){this.isActiveDelay=!0,setTimeout(()=>{this.isActiveDelay=!1},i)}},watch:{active(i){this.isActive=i,i&&this.$refs.velmld.parentNode.classList.add("velmld-parent")}},components:H},q={class:"velmld-spinner"};function J(i,a,e,u,n,d){return s(),g(z,{name:"fade"},{default:x(()=>[w(t("div",{class:p([{"velmld-full-screen":e.isFullScreen},"velmld-overlay"]),style:_({backgroundColor:e.backgroundColor}),ref:"velmld"},[t("div",q,[b(i.$slots,"default",{},()=>[(s(),g(L(e.spinner),{color:e.color,size:`${e.size}px`,duration:`${e.duration}s`},null,8,["color","size","duration"]))],!0),e.text.length?(s(),l("div",{key:0,style:_({color:e.color,...e.textStyle})},C(e.text),5)):y("",!0)])],6),[[S,n.isActive||n.isActiveDelay]])]),_:3})}const P=f(K,[["render",J],["__scopeId","data-v-82159762"]]),Q={props:{server:{type:String,default:"/api/upload"},isInvalid:{type:Boolean,default:!1},media:{type:Array,default:[]},location:{type:String,default:""},max:{type:Number,default:null},maxFilesize:{type:Number,default:4},warnings:{type:Boolean,default:!0}},mounted(){this.init()},data(){return{addedMedia:[],savedMedia:[],removedMedia:[],isLoading:!0}},methods:{init(){this.savedMedia=this.media,this.savedMedia.forEach((i,a)=>{this.savedMedia[a].url||(this.savedMedia[a].url=this.location+"/"+i.name)}),setTimeout(()=>this.isLoading=!1,1e3),this.$emit("init",this.allMedia)},async fileChange(i){this.isLoading=!0;let a=i.target.files;for(var e=0;e<a.length;e++)if(!this.max||this.allMedia.length<this.max)if(a[e].size<=this.maxFilesize*1e6){let u=new FormData,n=URL.createObjectURL(a[e]);u.set("image",a[e]);const{data:d}=await F.post(this.server,u);let c={url:n,name:d.name,size:a[e].size,type:a[e].type};this.addedMedia.push(c),this.$emit("change",this.allMedia),this.$emit("add",c,this.addedMedia)}else{this.$emit("maxFilesize",a[e].size),this.warnings&&alert(`The file you are trying to upload is too big. 
Maximum Filesize: `+this.maxFilesize+"MB");break}else{this.$emit("max"),this.warnings&&alert(`You have reached the maximum number of files that you can upload. 
Maximum Files: `+this.max);break}i.target.value=null,this.isLoading=!1},removeAddedMedia(i){let a=this.addedMedia[i];this.addedMedia.splice(i,1),this.$emit("change",this.allMedia),this.$emit("remove",a,this.removedMedia)},removeSavedMedia(i){let a=this.savedMedia[i];this.removedMedia.push(a),this.savedMedia.splice(i,1),this.$emit("change",this.allMedia),this.$emit("remove",a,this.removedMedia)}},computed:{allMedia(){return[...this.savedMedia,...this.addedMedia]}},emits:["init","change","add","remove","max","maxFilesize"],components:{Loader:P}},v=i=>(N("data-v-1238f422"),i=i(),B(),i),W={class:"mu-elements-wraper"},Z={class:"mu-plusbox-container"},ee=v(()=>t("label",{for:"mu-file-input",class:"mu-plusbox"},[t("svg",{class:"mu-plus-icon",xmlns:"http://www.w3.org/2000/svg",width:"1em",height:"1em",preserveAspectRatio:"xMidYMid meet",viewBox:"0 0 24 24"},[t("g",{fill:"none"},[t("path",{d:"M12 1C5.925 1 1 5.925 1 12s4.925 11 11 11s11-4.925 11-11S18.075 1 12 1zm1 15a1 1 0 1 1-2 0v-3H8a1 1 0 1 1 0-2h3V8a1 1 0 1 1 2 0v3h3a1 1 0 1 1 0 2h-3v3z",fill:"currentColor"})])])],-1)),te=["src"],ie=["onClick"],ae=v(()=>t("svg",{class:"mu-times-icon",xmlns:"http://www.w3.org/2000/svg",width:"0.65em",height:"0.65em",preserveAspectRatio:"xMidYMid meet",viewBox:"0 0 352 512"},[t("path",{d:"m242.72 256l100.07-100.07c12.28-12.28 12.28-32.19 0-44.48l-22.24-22.24c-12.28-12.28-32.19-12.28-44.48 0L176 189.28L75.93 89.21c-12.28-12.28-32.19-12.28-44.48 0L9.21 111.45c-12.28 12.28-12.28 32.19 0 44.48L109.28 256L9.21 356.07c-12.28 12.28-12.28 32.19 0 44.48l22.24 22.24c12.28 12.28 32.2 12.28 44.48 0L176 322.72l100.07 100.07c12.28 12.28 32.2 12.28 44.48 0l22.24-22.24c12.28-12.28 12.28-32.19 0-44.48L242.72 256z",fill:"currentColor"})],-1)),se=[ae],le=["src"],ne=["onClick"],de=v(()=>t("svg",{class:"mu-times-icon",xmlns:"http://www.w3.org/2000/svg",width:"0.65em",height:"0.65em",preserveAspectRatio:"xMidYMid meet",viewBox:"0 0 352 512"},[t("path",{d:"m242.72 256l100.07-100.07c12.28-12.28 12.28-32.19 0-44.48l-22.24-22.24c-12.28-12.28-32.19-12.28-44.48 0L176 189.28L75.93 89.21c-12.28-12.28-32.19-12.28-44.48 0L9.21 111.45c-12.28 12.28-12.28 32.19 0 44.48L109.28 256L9.21 356.07c-12.28 12.28-12.28 32.19 0 44.48l22.24 22.24c12.28 12.28 32.2 12.28 44.48 0L176 322.72l100.07 100.07c12.28 12.28 32.2 12.28 44.48 0l22.24-22.24c12.28-12.28 12.28-32.19 0-44.48L242.72 256z",fill:"currentColor"})],-1)),oe=[de],re=["value"],ue=["value"],ce={key:0,class:"mu-mt-1"},me=v(()=>t("input",{type:"text",name:"media",value:"1",hidden:""},null,-1)),he=[me];function ve(i,a,e,u,n,d){const c=k("Loader");return s(),l("div",null,[t("div",{class:p(["mu-container",e.isInvalid?"mu-red-border":""])},[A(c,{color:"#0275d8",active:n.isLoading,spinner:"line-scale","background-color":"rgba(255, 255, 255, .4)"},null,8,["active"]),t("div",W,[t("div",Z,[ee,t("input",{onChange:a[0]||(a[0]=(...o)=>d.fileChange&&d.fileChange(...o)),id:"mu-file-input",type:"file",accept:"image/*",multiple:"",hidden:""},null,32)]),(s(!0),l(m,null,h(n.savedMedia,(o,r)=>(s(),l("div",{key:r,class:"mu-image-container"},[t("img",{src:e.location+"/"+o.name,alt:"",class:"mu-images-preview"},null,8,te),t("button",{onClick:M=>d.removeSavedMedia(r),class:"mu-close-btn",type:"button"},se,8,ie)]))),128)),(s(!0),l(m,null,h(n.addedMedia,(o,r)=>(s(),l("div",{key:r,class:"mu-image-container"},[t("img",{src:o.url,alt:"",class:"mu-images-preview"},null,8,le),t("button",{onClick:M=>d.removeAddedMedia(r),class:"mu-close-btn",type:"button"},oe,8,ne)]))),128))])],2),t("div",null,[(s(!0),l(m,null,h(n.addedMedia,(o,r)=>(s(),l("div",{key:r,class:"mu-mt-1"},[t("input",{type:"text",name:"added_media[]",value:o.name,hidden:""},null,8,re)]))),128)),(s(!0),l(m,null,h(n.removedMedia,(o,r)=>(s(),l("div",{key:r,class:"mu-mt-1"},[t("input",{type:"text",name:"removed_media[]",value:o.name,hidden:""},null,8,ue)]))),128)),d.allMedia.length?(s(),l("div",ce,he)):y("",!0)])])}const ye=f(Q,[["render",ve],["__scopeId","data-v-1238f422"]]);export{ye as U};
