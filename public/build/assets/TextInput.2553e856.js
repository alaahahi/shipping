import{o as a,a as o,t as r,d as l,r as d,q as i}from"./app.5bcaadfe.js";const c={class:"block font-medium text-sm text-gray-700 dark:text-gray-400"},p={key:0},m={key:1},g={__name:"InputLabel",props:["value"],setup(t){return(e,s)=>(a(),o("label",c,[t.value?(a(),o("span",p,r(t.value),1)):(a(),o("span",m,[l(e.$slots,"default")]))]))}},_=["value"],y={__name:"TextInput",props:["modelValue"],emits:["update:modelValue"],setup(t){const e=d(null);return i(()=>{e.value.hasAttribute("autofocus")&&e.value.focus()}),(s,u)=>(a(),o("input",{class:"border-gray-300 focus:border-indigo-300 dark:bg-gray-800 dark:text-gray-200 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm",value:t.modelValue,onInput:u[0]||(u[0]=n=>s.$emit("update:modelValue",n.target.value)),ref_key:"input",ref:e},null,40,_))}};export{g as _,y as a};
