import{s as o}from"./OnlineContracts.a3d20c4a.js";import"./app.a8537d6e.js";import"./AuthenticatedLayout.9d50cc29.js";import"./new.fb44b547.js";import"./Uploader.07a57c5c.js";import"./index.bb5dcf7a.js";import"./show.cccb9a1c.js";import"./pay.d2a9a64f.js";import"./debounce.33f75560.js";function n(e){return e>9?n(e%10):e}function a(e){var r={m:"v",b:"v",d:"z"};return r[e.charAt(0)]+e.substring(1)}function m(e,r){return r===2?a(e):e}function u(e,r,t){var _={mm:"munutenn",MM:"miz",dd:"devezh"};return e+" "+m(_[t],e)}function i(e){switch(n(e)){case 1:case 3:case 4:case 5:case 9:return e+" bloaz";default:return e+" vloaz"}}var s={name:"br",weekdays:"Sul_Lun_Meurzh_Merc\u02BCher_Yaou_Gwener_Sadorn".split("_"),months:"Genver_C\u02BChwevrer_Meurzh_Ebrel_Mae_Mezheven_Gouere_Eost_Gwengolo_Here_Du_Kerzu".split("_"),weekStart:1,weekdaysShort:"Sul_Lun_Meu_Mer_Yao_Gwe_Sad".split("_"),monthsShort:"Gen_C\u02BChwe_Meu_Ebr_Mae_Eve_Gou_Eos_Gwe_Her_Du_Ker".split("_"),weekdaysMin:"Su_Lu_Me_Mer_Ya_Gw_Sa".split("_"),ordinal:function(e){return e},formats:{LT:"h[e]mm A",LTS:"h[e]mm:ss A",L:"DD/MM/YYYY",LL:"D [a viz] MMMM YYYY",LLL:"D [a viz] MMMM YYYY h[e]mm A",LLLL:"dddd, D [a viz] MMMM YYYY h[e]mm A"},relativeTime:{future:"a-benn %s",past:"%s \u02BCzo",s:"un nebeud segondenno\xF9",m:"ur vunutenn",mm:u,h:"un eur",hh:"%d eur",d:"un devezh",dd:u,M:"ur miz",MM:u,y:"ur bloaz",yy:i},meridiem:function(e){return e<12?"a.m.":"g.m."}};o.locale(s,null,!0);export{s as default};