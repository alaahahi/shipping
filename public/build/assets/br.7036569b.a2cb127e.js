import{s as a}from"./ModalShowExitCar.vue_vue_type_style_index_0_lang.d43023cd.js";import"./app.6ea23e35.js";function n(e){return e>9?n(e%10):e}function o(e){var r={m:"v",b:"v",d:"z"};return r[e.charAt(0)]+e.substring(1)}function s(e,r){return r===2?o(e):e}function u(e,r,_){var t={mm:"munutenn",MM:"miz",dd:"devezh"};return e+" "+s(t[_],e)}function M(e){switch(n(e)){case 1:case 3:case 4:case 5:case 9:return e+" bloaz";default:return e+" vloaz"}}var m={name:"br",weekdays:"Sul_Lun_Meurzh_Merc\u02BCher_Yaou_Gwener_Sadorn".split("_"),months:"Genver_C\u02BChwevrer_Meurzh_Ebrel_Mae_Mezheven_Gouere_Eost_Gwengolo_Here_Du_Kerzu".split("_"),weekStart:1,weekdaysShort:"Sul_Lun_Meu_Mer_Yao_Gwe_Sad".split("_"),monthsShort:"Gen_C\u02BChwe_Meu_Ebr_Mae_Eve_Gou_Eos_Gwe_Her_Du_Ker".split("_"),weekdaysMin:"Su_Lu_Me_Mer_Ya_Gw_Sa".split("_"),ordinal:function(e){return e},formats:{LT:"h[e]mm A",LTS:"h[e]mm:ss A",L:"DD/MM/YYYY",LL:"D [a viz] MMMM YYYY",LLL:"D [a viz] MMMM YYYY h[e]mm A",LLLL:"dddd, D [a viz] MMMM YYYY h[e]mm A"},relativeTime:{future:"a-benn %s",past:"%s \u02BCzo",s:"un nebeud segondenno\xF9",m:"ur vunutenn",mm:u,h:"un eur",hh:"%d eur",d:"un devezh",dd:u,M:"ur miz",MM:u,y:"ur bloaz",yy:M},meridiem:function(e){return e<12?"a.m.":"g.m."}};a.locale(m,null,!0);export{m as default};
