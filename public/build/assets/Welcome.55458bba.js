import{a as t,f as s,u as r,h as o,d as u,e as n,F as i,g as l,o as e,H as m,j as c,L as d}from"./app.e33e7107.js";const p={class:"relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center sm:pt-0"},h={key:0,class:"fixed top-0 right-0 px-6 py-4 sm:block"},f=o("div",{class:"max-w-6xl mx-auto sm:px-6 lg:px-8",style:{"border-radius":"25px",padding:"0","background-color":"#282634"}},[o("img",{src:"img/logo-color.png",alt:"karbala",style:{margin:"auto",width:"100%","border-radius":"25px"}})],-1),y={__name:"Welcome",props:{canLogin:Boolean,canRegister:Boolean,laravelVersion:String,phpVersion:String,config:Object},setup(g){return(a,x)=>(e(),t(i,null,[s(r(m),{title:"Welcome"}),o("div",p,[g.canLogin?(e(),t("div",h,[a.$page.props.auth.user?(e(),u(r(d),{key:0,href:a.route("dashboard"),class:"text-sm text-gray-700 dark:text-gray-500 underline"},{default:n(()=>[c("Dashboard")]),_:1},8,["href"])):(e(),t(i,{key:1},[s(r(d),{href:a.route("login"),class:"px-6 py-2 text-white bg-rose-500 rounded-md focus:outline-none"},{default:n(()=>[c("\u062A\u0633\u062C\u064A\u0644 \u0627\u0644\u062F\u062E\u0648\u0644")]),_:1},8,["href"]),l("",!0)],64))])):l("",!0),f])],64))}};export{y as default};