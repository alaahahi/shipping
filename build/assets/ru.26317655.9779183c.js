import{s as e}from"./OnlineContracts.208a469e.js";import"./app.333cb358.js";import"./AuthenticatedLayout.f727f796.js";import"./index.671659a7.js";import"./show.2c8bd6cc.js";import"./pay.2308a8ed.js";import"./new.c8bf99dc.js";import"./debounce.7db34f48.js";var C="\u044F\u043D\u0432\u0430\u0440\u044F_\u0444\u0435\u0432\u0440\u0430\u043B\u044F_\u043C\u0430\u0440\u0442\u0430_\u0430\u043F\u0440\u0435\u043B\u044F_\u043C\u0430\u044F_\u0438\u044E\u043D\u044F_\u0438\u044E\u043B\u044F_\u0430\u0432\u0433\u0443\u0441\u0442\u0430_\u0441\u0435\u043D\u0442\u044F\u0431\u0440\u044F_\u043E\u043A\u0442\u044F\u0431\u0440\u044F_\u043D\u043E\u044F\u0431\u0440\u044F_\u0434\u0435\u043A\u0430\u0431\u0440\u044F".split("_"),m="\u044F\u043D\u0432\u0430\u0440\u044C_\u0444\u0435\u0432\u0440\u0430\u043B\u044C_\u043C\u0430\u0440\u0442_\u0430\u043F\u0440\u0435\u043B\u044C_\u043C\u0430\u0439_\u0438\u044E\u043D\u044C_\u0438\u044E\u043B\u044C_\u0430\u0432\u0433\u0443\u0441\u0442_\u0441\u0435\u043D\u0442\u044F\u0431\u0440\u044C_\u043E\u043A\u0442\u044F\u0431\u0440\u044C_\u043D\u043E\u044F\u0431\u0440\u044C_\u0434\u0435\u043A\u0430\u0431\u0440\u044C".split("_"),s="\u044F\u043D\u0432._\u0444\u0435\u0432\u0440._\u043C\u0430\u0440._\u0430\u043F\u0440._\u043C\u0430\u044F_\u0438\u044E\u043D\u044F_\u0438\u044E\u043B\u044F_\u0430\u0432\u0433._\u0441\u0435\u043D\u0442._\u043E\u043A\u0442._\u043D\u043E\u044F\u0431._\u0434\u0435\u043A.".split("_"),i="\u044F\u043D\u0432._\u0444\u0435\u0432\u0440._\u043C\u0430\u0440\u0442_\u0430\u043F\u0440._\u043C\u0430\u0439_\u0438\u044E\u043D\u044C_\u0438\u044E\u043B\u044C_\u0430\u0432\u0433._\u0441\u0435\u043D\u0442._\u043E\u043A\u0442._\u043D\u043E\u044F\u0431._\u0434\u0435\u043A.".split("_"),n=/D[oD]?(\[[^[\]]*\]|\s)+MMMM?/;function E(u,_){var t=u.split("_");return _%10===1&&_%100!==11?t[0]:_%10>=2&&_%10<=4&&(_%100<10||_%100>=20)?t[1]:t[2]}function D(u,_,t){var o={mm:_?"\u043C\u0438\u043D\u0443\u0442\u0430_\u043C\u0438\u043D\u0443\u0442\u044B_\u043C\u0438\u043D\u0443\u0442":"\u043C\u0438\u043D\u0443\u0442\u0443_\u043C\u0438\u043D\u0443\u0442\u044B_\u043C\u0438\u043D\u0443\u0442",hh:"\u0447\u0430\u0441_\u0447\u0430\u0441\u0430_\u0447\u0430\u0441\u043E\u0432",dd:"\u0434\u0435\u043D\u044C_\u0434\u043D\u044F_\u0434\u043D\u0435\u0439",MM:"\u043C\u0435\u0441\u044F\u0446_\u043C\u0435\u0441\u044F\u0446\u0430_\u043C\u0435\u0441\u044F\u0446\u0435\u0432",yy:"\u0433\u043E\u0434_\u0433\u043E\u0434\u0430_\u043B\u0435\u0442"};return t==="m"?_?"\u043C\u0438\u043D\u0443\u0442\u0430":"\u043C\u0438\u043D\u0443\u0442\u0443":u+" "+E(o[t],+u)}var F=function(u,_){return n.test(_)?C[u.month()]:m[u.month()]};F.s=m;F.f=C;var r=function(u,_){return n.test(_)?s[u.month()]:i[u.month()]};r.s=i;r.f=s;var M={name:"ru",weekdays:"\u0432\u043E\u0441\u043A\u0440\u0435\u0441\u0435\u043D\u044C\u0435_\u043F\u043E\u043D\u0435\u0434\u0435\u043B\u044C\u043D\u0438\u043A_\u0432\u0442\u043E\u0440\u043D\u0438\u043A_\u0441\u0440\u0435\u0434\u0430_\u0447\u0435\u0442\u0432\u0435\u0440\u0433_\u043F\u044F\u0442\u043D\u0438\u0446\u0430_\u0441\u0443\u0431\u0431\u043E\u0442\u0430".split("_"),weekdaysShort:"\u0432\u0441\u043A_\u043F\u043D\u0434_\u0432\u0442\u0440_\u0441\u0440\u0434_\u0447\u0442\u0432_\u043F\u0442\u043D_\u0441\u0431\u0442".split("_"),weekdaysMin:"\u0432\u0441_\u043F\u043D_\u0432\u0442_\u0441\u0440_\u0447\u0442_\u043F\u0442_\u0441\u0431".split("_"),months:F,monthsShort:r,weekStart:1,yearStart:4,formats:{LT:"H:mm",LTS:"H:mm:ss",L:"DD.MM.YYYY",LL:"D MMMM YYYY \u0433.",LLL:"D MMMM YYYY \u0433., H:mm",LLLL:"dddd, D MMMM YYYY \u0433., H:mm"},relativeTime:{future:"\u0447\u0435\u0440\u0435\u0437 %s",past:"%s \u043D\u0430\u0437\u0430\u0434",s:"\u043D\u0435\u0441\u043A\u043E\u043B\u044C\u043A\u043E \u0441\u0435\u043A\u0443\u043D\u0434",m:D,mm:D,h:"\u0447\u0430\u0441",hh:D,d:"\u0434\u0435\u043D\u044C",dd:D,M:"\u043C\u0435\u0441\u044F\u0446",MM:D,y:"\u0433\u043E\u0434",yy:D},ordinal:function(u){return u},meridiem:function(u){return u<4?"\u043D\u043E\u0447\u0438":u<12?"\u0443\u0442\u0440\u0430":u<17?"\u0434\u043D\u044F":"\u0432\u0435\u0447\u0435\u0440\u0430"}};e.locale(M,null,!0);export{M as default};
