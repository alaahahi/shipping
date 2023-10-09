import{B as P,o as g,c as f,w as v,a as d,E as c,b as r,G as h,d as m,F as x,g as b,t as y,f as k}from"./app.a837e6d9.js";const R={emits:["pagination-change-page"],props:{data:{type:Object,default:()=>{}},limit:{type:Number,default:0}},computed:{isApiResource(){return!!this.data.meta},currentPage(){return this.isApiResource?this.data.meta.current_page:this.data.current_page},firstPageUrl(){return this.isApiResource?this.data.links.first:null},from(){return this.isApiResource?this.data.meta.from:this.data.from},lastPage(){return this.isApiResource?this.data.meta.last_page:this.data.last_page},lastPageUrl(){return this.isApiResource?this.data.links.last:null},nextPageUrl(){return this.isApiResource?this.data.links.next:this.data.next_page_url},perPage(){return this.isApiResource?this.data.meta.per_page:this.data.per_page},prevPageUrl(){return this.isApiResource?this.data.links.prev:this.data.prev_page_url},to(){return this.isApiResource?this.data.meta.to:this.data.to},total(){return this.isApiResource?this.data.meta.total:this.data.total},pageRange(){if(this.limit===-1)return 0;if(this.limit===0)return this.lastPage;for(var e=this.currentPage,i=this.lastPage,a=this.limit,l=e-a,u=e+a+1,p=[],n=[],t,s=1;s<=i;s++)(s===1||s===i||s>=l&&s<u)&&p.push(s);return p.forEach(function(o){t&&(o-t===2?n.push(t+1):o-t!==1&&n.push("...")),n.push(o),t=o}),n}},methods:{previousPage(){this.selectPage(this.currentPage-1)},nextPage(){this.selectPage(this.currentPage+1)},selectPage(e){e!=="..."&&this.$emit("pagination-change-page",e)}},render(){return this.$slots.default({data:this.data,limit:this.limit,computed:{isApiResource:this.isApiResource,currentPage:this.currentPage,firstPageUrl:this.firstPageUrl,from:this.from,lastPage:this.lastPage,lastPageUrl:this.lastPageUrl,nextPageUrl:this.nextPageUrl,perPage:this.perPage,prevPageUrl:this.prevPageUrl,to:this.to,total:this.total,pageRange:this.pageRange},prevButtonEvents:{click:e=>{e.preventDefault(),this.previousPage()}},nextButtonEvents:{click:e=>{e.preventDefault(),this.nextPage()}},pageButtonEvents:e=>({click:i=>{i.preventDefault(),this.selectPage(e)}})})}},w=(e,i)=>{const a=e.__vccOpts||e;for(const[l,u]of i)a[l]=u;return a},C={compatConfig:{MODE:3},inheritAttrs:!1,emits:["pagination-change-page"],components:{RenderlessPagination:R},props:{data:{type:Object,default:()=>{}},limit:{type:Number,default:0},itemClasses:{type:Array,default:()=>["bg-white","text-gray-500","border-gray-300","hover:bg-gray-50"]},activeClasses:{type:Array,default:()=>["bg-blue-50","border-blue-500","text-blue-600"]}},methods:{onPaginationChangePage(e){this.$emit("pagination-change-page",e)}}},A=["disabled"],U=r("span",{class:"sr-only"},"Previous",-1),B=r("svg",{class:"h-5 w-5","aria-hidden":"true",xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24","stroke-width":"1.5",stroke:"currentColor"},[r("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M15.75 19.5L8.25 12l7.5-7.5"})],-1),_=["aria-current"],E=["disabled"],$=r("span",{class:"sr-only"},"Next",-1),D=r("svg",{class:"w-5 h-5","aria-hidden":"true",xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24","stroke-width":"1.5",stroke:"currentColor"},[r("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M8.25 4.5l7.5 7.5-7.5 7.5"})],-1);function N(e,i,a,l,u,p){const n=P("RenderlessPagination");return g(),f(n,{data:a.data,limit:a.limit,onPaginationChangePage:p.onPaginationChangePage},{default:v(t=>[t.computed.total>t.computed.perPage?(g(),d("nav",c({key:0},e.$attrs,{class:"isolate inline-flex -space-x-px rounded-md shadow-sm","aria-label":"Pagination"}),[r("button",c({class:["relative inline-flex items-center rounded-l-md border px-2 py-2 text-sm font-medium focus:z-20 disabled:opacity-50",a.itemClasses],disabled:!t.computed.prevPageUrl},h(t.prevButtonEvents,!0)),[m(e.$slots,"prev-nav",{},()=>[U,B])],16,A),(g(!0),d(x,null,b(t.computed.pageRange,(s,o)=>(g(),d("button",c({class:["relative inline-flex items-center border px-4 py-2 text-sm font-medium focus:z-20",[s==t.computed.currentPage?a.activeClasses:a.itemClasses,s==t.computed.currentPage?"z-30":""]],"aria-current":t.computed.currentPage?"page":null,key:o},h(t.pageButtonEvents(s),!0)),y(s),17,_))),128)),r("button",c({class:["relative inline-flex items-center rounded-r-md border px-2 py-2 text-sm font-medium focus:z-20 disabled:opacity-50",a.itemClasses],disabled:!t.computed.nextPageUrl},h(t.nextButtonEvents,!0)),[m(e.$slots,"next-nav",{},()=>[$,D])],16,E)],16)):k("",!0)]),_:3},8,["data","limit","onPaginationChangePage"])}const z=w(C,[["render",N]]);export{z as t};