import{s as A}from"./OnlineContracts.4f05f74e.js";import"./app.3afe6a1c.js";import"./AuthenticatedLayout.c2058d06.js";import"./new.d0aa26de.js";import"./Uploader.00882bf7.js";import"./index.fd3eab94.js";import"./show.ca2fcf4e.js";import"./pay.1816b13b.js";import"./debounce.a63d6147.js";var B={name:"dv",weekdays:"\u0787\u07A7\u078B\u07A8\u0787\u07B0\u078C\u07A6_\u0780\u07AF\u0789\u07A6_\u0787\u07A6\u0782\u07B0\u078E\u07A7\u0783\u07A6_\u0784\u07AA\u078B\u07A6_\u0784\u07AA\u0783\u07A7\u0790\u07B0\u078A\u07A6\u078C\u07A8_\u0780\u07AA\u0786\u07AA\u0783\u07AA_\u0780\u07AE\u0782\u07A8\u0780\u07A8\u0783\u07AA".split("_"),months:"\u0796\u07AC\u0782\u07AA\u0787\u07A6\u0783\u07A9_\u078A\u07AC\u0784\u07B0\u0783\u07AA\u0787\u07A6\u0783\u07A9_\u0789\u07A7\u0783\u07A8\u0797\u07AA_\u0787\u07AD\u0795\u07B0\u0783\u07A9\u078D\u07AA_\u0789\u07AD_\u0796\u07AB\u0782\u07B0_\u0796\u07AA\u078D\u07A6\u0787\u07A8_\u0787\u07AF\u078E\u07A6\u0790\u07B0\u0793\u07AA_\u0790\u07AC\u0795\u07B0\u0793\u07AC\u0789\u07B0\u0784\u07A6\u0783\u07AA_\u0787\u07AE\u0786\u07B0\u0793\u07AF\u0784\u07A6\u0783\u07AA_\u0782\u07AE\u0788\u07AC\u0789\u07B0\u0784\u07A6\u0783\u07AA_\u0791\u07A8\u0790\u07AC\u0789\u07B0\u0784\u07A6\u0783\u07AA".split("_"),weekStart:7,weekdaysShort:"\u0787\u07A7\u078B\u07A8\u0787\u07B0\u078C\u07A6_\u0780\u07AF\u0789\u07A6_\u0787\u07A6\u0782\u07B0\u078E\u07A7\u0783\u07A6_\u0784\u07AA\u078B\u07A6_\u0784\u07AA\u0783\u07A7\u0790\u07B0\u078A\u07A6\u078C\u07A8_\u0780\u07AA\u0786\u07AA\u0783\u07AA_\u0780\u07AE\u0782\u07A8\u0780\u07A8\u0783\u07AA".split("_"),monthsShort:"\u0796\u07AC\u0782\u07AA\u0787\u07A6\u0783\u07A9_\u078A\u07AC\u0784\u07B0\u0783\u07AA\u0787\u07A6\u0783\u07A9_\u0789\u07A7\u0783\u07A8\u0797\u07AA_\u0787\u07AD\u0795\u07B0\u0783\u07A9\u078D\u07AA_\u0789\u07AD_\u0796\u07AB\u0782\u07B0_\u0796\u07AA\u078D\u07A6\u0787\u07A8_\u0787\u07AF\u078E\u07A6\u0790\u07B0\u0793\u07AA_\u0790\u07AC\u0795\u07B0\u0793\u07AC\u0789\u07B0\u0784\u07A6\u0783\u07AA_\u0787\u07AE\u0786\u07B0\u0793\u07AF\u0784\u07A6\u0783\u07AA_\u0782\u07AE\u0788\u07AC\u0789\u07B0\u0784\u07A6\u0783\u07AA_\u0791\u07A8\u0790\u07AC\u0789\u07B0\u0784\u07A6\u0783\u07AA".split("_"),weekdaysMin:"\u0787\u07A7\u078B\u07A8_\u0780\u07AF\u0789\u07A6_\u0787\u07A6\u0782\u07B0_\u0784\u07AA\u078B\u07A6_\u0784\u07AA\u0783\u07A7_\u0780\u07AA\u0786\u07AA_\u0780\u07AE\u0782\u07A8".split("_"),ordinal:function(u){return u},formats:{LT:"HH:mm",LTS:"HH:mm:ss",L:"D/M/YYYY",LL:"D MMMM YYYY",LLL:"D MMMM YYYY HH:mm",LLLL:"dddd D MMMM YYYY HH:mm"},relativeTime:{future:"\u078C\u07AC\u0783\u07AD\u078E\u07A6\u0787\u07A8 %s",past:"\u0786\u07AA\u0783\u07A8\u0782\u07B0 %s",s:"\u0790\u07A8\u0786\u07AA\u0782\u07B0\u078C\u07AA\u0786\u07AE\u0785\u07AC\u0787\u07B0",m:"\u0789\u07A8\u0782\u07A8\u0793\u07AC\u0787\u07B0",mm:"\u0789\u07A8\u0782\u07A8\u0793\u07AA %d",h:"\u078E\u07A6\u0791\u07A8\u0787\u07A8\u0783\u07AC\u0787\u07B0",hh:"\u078E\u07A6\u0791\u07A8\u0787\u07A8\u0783\u07AA %d",d:"\u078B\u07AA\u0788\u07A6\u0780\u07AC\u0787\u07B0",dd:"\u078B\u07AA\u0788\u07A6\u0790\u07B0 %d",M:"\u0789\u07A6\u0780\u07AC\u0787\u07B0",MM:"\u0789\u07A6\u0790\u07B0 %d",y:"\u0787\u07A6\u0780\u07A6\u0783\u07AC\u0787\u07B0",yy:"\u0787\u07A6\u0780\u07A6\u0783\u07AA %d"}};A.locale(B,null,!0);export{B as default};
