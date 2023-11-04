/* empty css                                                 */import{a as g}from"./AuthenticatedLayout.68908968.js";import{o as l,a as c,b as r,n as d,F as p,N as w,j as y,e as k,h as C,t as u,g as I,f as h,G as x,I as _}from"./app.8dc27581.js";let m;const v={getDeviceOrientation:function(e,t=!1){if(!e&&!m||(e||(e=m),!e||!e.beta||!e.gamma))return 0;const s=e.beta,o=e.gamma;let i=Math.atan2(o,s)*(180/Math.PI);const a=(screen.orientation||screen.mozOrientation||screen.msOrientation).angle||window.orientation||0;return i=Math.round(i+a),i<0&&(i=360+i),t?i:i>55&&i<=135?90:i>135&&i<225?180:i>=225&&i<305?270:0},isPortrait(e){const t=this.getDeviceOrientation(e);return t===0||t===180},isLandscape(e){const t=this.getDeviceOrientation(e);return t===90||t===270},isPortraitDefault(e){return this.getDeviceOrientation(e)===0},isPortraitReversed(e){return this.getDeviceOrientation(e)===180},isLandscapeRight(e){return this.getDeviceOrientation(e)===90},isLandscapeLeft(e){return this.getDeviceOrientation(e)===270},init(e=!1){if(window.DeviceOrientationEvent===void 0){if(e)throw new Error("DeviceOrientationEvent is not supported in current browser");return}window.addEventListener("deviceorientation",t=>{m=t},!0)}};function D(e,t,s,o){const n=t.width,i=t.height;(o===90||o===270)&&(t.width=i,t.height=n),o===90?s.translate(i,0):o===180?s.translate(n,i):o===270?s.translate(0,n):s.translate(0,0),s.rotate(o*Math.PI/180),s.drawImage(e,-e.width/2,-e.width/2,n,i)}const O={name:"Webcam",props:{rememberDevice:{type:Boolean,default:!0},preferCamerasWithLabel:{type:Array,default:["back","usb"]},classList:{type:String,default:"w-full h-auto"},constraints:{type:Object,default:{video:{width:{ideal:2560},height:{ideal:1440}},facingMode:"environment"}},tryToRotateImage:{type:Boolean,default:!0},imageType:{type:String,default:"image/jpeg"},rememberDeviceTokenName:{type:String,default:"_vwl_device_id"},autoStart:{type:Boolean,default:!0},audio:{type:Boolean,default:!0},shutterEffect:{type:Boolean,default:!0}},data(){return{deviceId:null,cameras:[],innited:!1}},emits:["clear","stop","start","pause","resume","error","unsupported","init","photoTaken"],async mounted(){this.setupMedia(),v.init()},beforeUnmount(){this.stop()},methods:{loadCameras(){navigator.mediaDevices.enumerateDevices().then(e=>{for(let t=0;t!==e.length;++t){let s=e[t];s.kind==="videoinput"&&this.cameras.find(o=>o.deviceId===s.deviceId)===void 0&&this.cameras.push(s)}}).then(()=>{this.innited||(this.deviceId===null&&this.autoStart&&this.start(),this.$emit("init",this.deviceId),this.innited=!0)}).catch(e=>this.$emit("unsupported",e))},changeCamera(e){if(this.deviceId!==e){this.deviceId=e;return}this.stop(),e&&this.loadCamera(e)},loadCamera(e){navigator.mediaDevices.getUserMedia(this.buildConstraints(e)).then(t=>{this.$refs.video.srcObject=t,this.rememberDevice&&window.localStorage.setItem(this.rememberDeviceTokenName,e)}).catch(t=>this.$emit("error",t))},legacyGetUserMediaSupport(){return e=>{let t=navigator.getUserMedia||navigator.webkitGetUserMedia||navigator.mozGetUserMedia||navigator.msGetUserMedia||navigator.oGetUserMedia;return t?new Promise(function(s,o){t.call(navigator,e,s,o)}):Promise.reject(new Error("getUserMedia is not implemented in this browser"))}},testMediaAccess(){navigator.mediaDevices.getUserMedia(this.buildConstraints()).then(e=>{e.getTracks().forEach(s=>{s.stop()}),this.loadCameras()}).catch(e=>this.$emit("error",e))},setupMedia(){navigator.mediaDevices===void 0&&(navigator.mediaDevices={}),navigator.mediaDevices.getUserMedia===void 0&&(navigator.mediaDevices.getUserMedia=this.legacyGetUserMediaSupport()),this.testMediaAccess()},clear(e){e.srcObject.getTracks().forEach(t=>{t.stop(),this.$refs.video.srcObject=null,this.source=null}),this.$emit("clear")},stop(){var e;(e=this.$refs.video)!=null&&e.srcObject&&this.clear(this.$refs.video),this.$emit("stop")},start(){if(this.deviceId)this.loadCamera(this.deviceId);else{const e=window.localStorage.getItem(this.rememberDeviceTokenName);if(e&&this.rememberDevice&&this.cameras.find(t=>t.deviceId===e))this.deviceId=e;else if(this.cameras.length>1)for(const t of this.preferCamerasWithLabel){const s=this.cameras.find(o=>o.label.toLowerCase().indexOf(t)!==-1);if(s){this.deviceId=s.deviceId;break}}!this.deviceId&&this.cameras.length>0&&this.cameras[0].deviceId}this.$emit("start")},pause(){var e;(e=this.$refs.video)!=null&&e.srcObject&&this.$refs.video.pause(),this.$emit("pause")},resume(){var e;(e=this.$refs.video)!=null&&e.srcObject&&this.$refs.video.play(),this.$emit("resume")},buildConstraints(e){const s={...{video:!0,audio:!1},...this.constraints};return e&&((typeof s.video!="object"||s.video===null)&&(s.video={}),s.video.deviceId={exact:e}),s},async takePhoto(){let e=this.$refs.video,t=this.$refs.canvas;t.height=e.videoHeight,t.width=e.videoWidth;let s=t.getContext("2d");D(e,t,s,this.tryToRotateImage?v.getDeviceOrientation():0);let o=t.toDataURL(this.imageType);t.toBlob(n=>{this.audio&&this.$refs.audio.play(),this.shutterEffect&&(this.$refs.shutter.classList.add("on"),setTimeout(()=>{this.$refs.shutter.classList.remove("on")},30*2+45)),this.$emit("photoTaken",{blob:n,image_data_url:o})},this.imageType)}},watch:{deviceId:function(e){this.changeCamera(e)}}},T={ref:"canvas",style:{display:"none"}},M={class:"hidden"},B={ref:"audio",volume:"0.5",src:"https://www.soundjay.com/mechanical/camera-shutter-click-08.mp3"},S={ref:"shutter",class:"shutter"};function P(e,t,s,o,n,i){return l(),c(p,null,[r("video",{ref:"video",class:d(["w-full h-auto",{hidden:!n.deviceId,classList:!0}]),autoplay:""},null,2),r("canvas",T,null,512),r("div",M,[r("audio",B,null,512)]),r("div",S,null,512)],64)}const j=g(O,[["render",P],["__scopeId","data-v-fa5f08cb"]]),U={components:{Webcam:j},props:{reloadCamerasButton:{type:Object,default:{display:!1,text:"Reload cameras",css:"inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-500 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"}},takePhotoButton:{type:Object,default:{display:!0,text:"Take a photo",css:"inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-500 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"}},fullscreenButton:{type:Object,default:{display:!0,text:"Fullscreen",css:"inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-500 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"}},selectCameraLabel:{type:String,default:"Select camera..."},fullscreenState:{type:Boolean,default:!1}},data(){return{cameras:[],deviceId:"",fullscreen:!1,photoTaken:!1,photoFailed:!1,reloadCamInterval:null}},emits:["clear","stop","start","pause","resume","error","unsupported","init","photoTaken","fullscreen"],beforeUnmount(){this.reloadCamInterval&&clearInterval(this.reloadCamInterval),this.exit()},methods:{async takePhoto(){try{await this.$refs.webcam.takePhoto(),this.photoTaken=!0,setTimeout(()=>{this.photoTaken=!1},500)}catch{this.photoFailed=!0,setTimeout(()=>{this.photoFailed=!1},500)}},loadCameras(){this.$refs.webcam.loadCameras(),this.cameras=this.$refs.webcam.cameras},webcamInit(e){this.deviceId=e,this.$emit("init",this.deviceId)},setCamera(){this.$refs.webcam.changeCamera(this.deviceId===""?null:this.deviceId)},flipCamera(){if(this.loadCameras(),this.cameras.length>1){let t=this.cameras.findIndex(s=>s.deviceId===this.deviceId)+1;t>=this.cameras.length&&(t=0),this.deviceId=this.cameras[t].deviceId,this.$refs.webcam.changeCamera(this.cameras[t].deviceId)}},toggleFullscreen(){this.fullscreen=!this.fullscreen,this.$emit("fullscreen",this.fullscreen),this.fullscreen?document.querySelector("#webcam-ui").requestFullscreen!==void 0?document.querySelector("#webcam-ui").requestFullscreen():document.fullscreenElement.requestFullscreen():document.exitFullscreen()},exit(){this.$refs.webcam.stop()},clear(){this.$emit("clear")},stop(){this.$emit("stop")},start(){this.$emit("start")},pause(){this.$emit("pause")},resume(){this.$emit("resume")},error(e){this.$emit("error",e)},unsupported(e){this.$emit("unsupported",e)},photoTakenEvent({blob:e,image_data_url:t}){this.$emit("photoTaken",{blob:e,image_data_url:t})}},mounted(){this.cameras=this.$refs.webcam.cameras,this.cameras.length===0&&(this.reloadCamInterval=setInterval(()=>{this.loadCameras(),this.cameras.length>0&&clearInterval(this.reloadCamInterval)},1e3))},watch:{fullscreenState:{immediate:!0,handler:function(e){this.fullscreen=e}}}},b=e=>(x("data-v-017e214c"),e=e(),_(),e),E={key:0,class:"flex flex-col justify-center py-2 mx-auto text-center sm:flex-row align-center"},F={value:""},L=["value"],W={key:0,class:"button-control"},R={key:1,class:"pr-2"},G={key:2},N={key:1,class:"fullscreen-ui",style:{background:"rgba(0,0,0,0.4)"}},$={class:"flex items-center justify-center h-full"},q={class:"flex items-center justify-between p-8 align-center w-80"},z=b(()=>r("button",{class:"text-white"},[r("svg",{xmlns:"http://www.w3.org/2000/svg",class:"w-10 h-10",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[r("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"})])],-1)),A=[z],V=b(()=>r("button",{class:"text-white"},[r("svg",{xmlns:"http://www.w3.org/2000/svg",class:"w-10 h-10",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[r("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"})])],-1)),H=[V];function J(e,t,s,o,n,i){const f=w("Webcam");return l(),c("div",{class:d({"fullscreen-overlay":n.fullscreen,"":!n.fullscreen}),id:"webcam-ui"},[y(f,{ref:"webcam",onInit:i.webcamInit,onClear:i.clear,onStop:i.stop,onStart:i.start,onPause:i.pause,onResume:i.resume,onError:i.error,onUnsupported:i.unsupported,onPhotoTaken:i.photoTakenEvent,shutterEffect:n.fullscreen},null,8,["onInit","onClear","onStop","onStart","onPause","onResume","onError","onUnsupported","onPhotoTaken","shutterEffect"]),n.fullscreen?(l(),c("div",N,[r("div",$,[r("div",q,[r("div",{onClick:t[6]||(t[6]=(...a)=>i.toggleFullscreen&&i.toggleFullscreen(...a))},A),r("div",{onClick:t[7]||(t[7]=(...a)=>i.takePhoto&&i.takePhoto(...a))},[r("button",{class:d(["camera",{"camera-success":n.photoTaken,"camera-failed":n.photoFailed}])},"\xA0",2)]),r("div",{onClick:t[8]||(t[8]=(...a)=>i.flipCamera&&i.flipCamera(...a)),class:d({invisible:n.cameras.length<2})},H,2)])])])):(l(),c("div",E,[r("div",{onClick:t[2]||(t[2]=(...a)=>i.loadCameras&&i.loadCameras(...a))},[k(r("select",{onChange:t[0]||(t[0]=(...a)=>i.setCamera&&i.setCamera(...a)),"onUpdate:modelValue":t[1]||(t[1]=a=>n.deviceId=a),class:"block w-full py-2 pl-3 pr-10 text-base border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"},[r("option",F,u(s.selectCameraLabel),1),(l(!0),c(p,null,I(n.cameras,a=>(l(),c("option",{value:a.deviceId},u(a.label),9,L))),256))],544),[[C,n.deviceId]])]),s.takePhotoButton.display?(l(),c("div",W,[n.deviceId?(l(),c("button",{key:0,onClick:t[3]||(t[3]=(...a)=>i.takePhoto&&i.takePhoto(...a)),type:"button",class:d(s.takePhotoButton.css)},u(s.takePhotoButton.text),3)):h("",!0)])):h("",!0),s.reloadCamerasButton.display?(l(),c("div",R,[r("button",{onClick:t[4]||(t[4]=(...a)=>i.loadCameras&&i.loadCameras(...a)),type:"button",class:d(s.reloadCamerasButton.css)},u(s.reloadCamerasButton.text),3)])):h("",!0),s.fullscreenButton.display?(l(),c("div",G,[r("button",{onClick:t[5]||(t[5]=(...a)=>i.toggleFullscreen&&i.toggleFullscreen(...a)),type:"button",class:d(s.fullscreenButton.css)},u(s.fullscreenButton.text),3)])):h("",!0)]))],2)}const Y=g(U,[["render",J],["__scopeId","data-v-017e214c"]]);export{Y as W};
