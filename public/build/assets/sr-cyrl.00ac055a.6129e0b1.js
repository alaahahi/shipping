import{s as _}from"./OnlineContracts.a394d3d2.js";import"./app.740522a6.js";import"./AuthenticatedLayout.5f7b7290.js";import"./new.a04eb886.js";import"./Uploader.b94f4e90.js";import"./index.47d1ecbe.js";import"./show.b7a4fe74.js";import"./pay.bcb74980.js";import"./debounce.7c9e584c.js";var u={words:{m:["\u0458\u0435\u0434\u0430\u043D \u043C\u0438\u043D\u0443\u0442","\u0458\u0435\u0434\u043D\u043E\u0433 \u043C\u0438\u043D\u0443\u0442\u0430"],mm:["%d \u043C\u0438\u043D\u0443\u0442","%d \u043C\u0438\u043D\u0443\u0442\u0430","%d \u043C\u0438\u043D\u0443\u0442\u0430"],h:["\u0458\u0435\u0434\u0430\u043D \u0441\u0430\u0442","\u0458\u0435\u0434\u043D\u043E\u0433 \u0441\u0430\u0442\u0430"],hh:["%d \u0441\u0430\u0442","%d \u0441\u0430\u0442\u0430","%d \u0441\u0430\u0442\u0438"],d:["\u0458\u0435\u0434\u0430\u043D \u0434\u0430\u043D","\u0458\u0435\u0434\u043D\u043E\u0433 \u0434\u0430\u043D\u0430"],dd:["%d \u0434\u0430\u043D","%d \u0434\u0430\u043D\u0430","%d \u0434\u0430\u043D\u0430"],M:["\u0458\u0435\u0434\u0430\u043D \u043C\u0435\u0441\u0435\u0446","\u0458\u0435\u0434\u043D\u043E\u0433 \u043C\u0435\u0441\u0435\u0446\u0430"],MM:["%d \u043C\u0435\u0441\u0435\u0446","%d \u043C\u0435\u0441\u0435\u0446\u0430","%d \u043C\u0435\u0441\u0435\u0446\u0438"],y:["\u0458\u0435\u0434\u043D\u0443 \u0433\u043E\u0434\u0438\u043D\u0443","\u0458\u0435\u0434\u043D\u0435 \u0433\u043E\u0434\u0438\u043D\u0435"],yy:["%d \u0433\u043E\u0434\u0438\u043D\u0443","%d \u0433\u043E\u0434\u0438\u043D\u0435","%d \u0433\u043E\u0434\u0438\u043D\u0430"]},correctGrammarCase:function(r,e){return r%10>=1&&r%10<=4&&(r%100<10||r%100>=20)?r%10===1?e[0]:e[1]:e[2]},relativeTimeFormatter:function(r,e,t,i){var m=u.words[t];if(t.length===1)return t==="y"&&e?"\u0458\u0435\u0434\u043D\u0430 \u0433\u043E\u0434\u0438\u043D\u0430":i||e?m[0]:m[1];var D=u.correctGrammarCase(r,m);return t==="yy"&&e&&D==="%d \u0433\u043E\u0434\u0438\u043D\u0443"?r+" \u0433\u043E\u0434\u0438\u043D\u0430":D.replace("%d",r)}},a={name:"sr-cyrl",weekdays:"\u041D\u0435\u0434\u0435\u0459\u0430_\u041F\u043E\u043D\u0435\u0434\u0435\u0459\u0430\u043A_\u0423\u0442\u043E\u0440\u0430\u043A_\u0421\u0440\u0435\u0434\u0430_\u0427\u0435\u0442\u0432\u0440\u0442\u0430\u043A_\u041F\u0435\u0442\u0430\u043A_\u0421\u0443\u0431\u043E\u0442\u0430".split("_"),weekdaysShort:"\u041D\u0435\u0434._\u041F\u043E\u043D._\u0423\u0442\u043E._\u0421\u0440\u0435._\u0427\u0435\u0442._\u041F\u0435\u0442._\u0421\u0443\u0431.".split("_"),weekdaysMin:"\u043D\u0435_\u043F\u043E_\u0443\u0442_\u0441\u0440_\u0447\u0435_\u043F\u0435_\u0441\u0443".split("_"),months:"\u0408\u0430\u043D\u0443\u0430\u0440_\u0424\u0435\u0431\u0440\u0443\u0430\u0440_\u041C\u0430\u0440\u0442_\u0410\u043F\u0440\u0438\u043B_\u041C\u0430\u0458_\u0408\u0443\u043D_\u0408\u0443\u043B_\u0410\u0432\u0433\u0443\u0441\u0442_\u0421\u0435\u043F\u0442\u0435\u043C\u0431\u0430\u0440_\u041E\u043A\u0442\u043E\u0431\u0430\u0440_\u041D\u043E\u0432\u0435\u043C\u0431\u0430\u0440_\u0414\u0435\u0446\u0435\u043C\u0431\u0430\u0440".split("_"),monthsShort:"\u0408\u0430\u043D._\u0424\u0435\u0431._\u041C\u0430\u0440._\u0410\u043F\u0440._\u041C\u0430\u0458_\u0408\u0443\u043D_\u0408\u0443\u043B_\u0410\u0432\u0433._\u0421\u0435\u043F._\u041E\u043A\u0442._\u041D\u043E\u0432._\u0414\u0435\u0446.".split("_"),weekStart:1,relativeTime:{future:"\u0437\u0430 %s",past:"\u043F\u0440\u0435 %s",s:"\u043D\u0435\u043A\u043E\u043B\u0438\u043A\u043E \u0441\u0435\u043A\u0443\u043D\u0434\u0438",m:u.relativeTimeFormatter,mm:u.relativeTimeFormatter,h:u.relativeTimeFormatter,hh:u.relativeTimeFormatter,d:u.relativeTimeFormatter,dd:u.relativeTimeFormatter,M:u.relativeTimeFormatter,MM:u.relativeTimeFormatter,y:u.relativeTimeFormatter,yy:u.relativeTimeFormatter},ordinal:function(r){return r+"."},formats:{LT:"H:mm",LTS:"H:mm:ss",L:"D. M. YYYY.",LL:"D. MMMM YYYY.",LLL:"D. MMMM YYYY. H:mm",LLLL:"dddd, D. MMMM YYYY. H:mm"}};_.locale(a,null,!0);export{a as default};
