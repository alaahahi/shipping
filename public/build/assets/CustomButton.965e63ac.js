import{a,o as l}from"./app.ac0b1eaa.js";const i={__name:"CustomButton",props:["rowIndex","model","save","close"],setup(n){const{rowIndex:r,model:s,save:u,close:e}=n,c=t=>{const o=new CustomEvent("cell",{bubbles:!0,detail:{row:s}});$el.dispatchEvent(o),t.stopPropagation(),typeof e=="function"&&e()};return(t,o)=>(l(),a("button",{onClick:c},"Edit"))}};export{i as default};
