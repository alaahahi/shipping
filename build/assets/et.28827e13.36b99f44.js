import{s as u}from"./OnlineContracts.950f58a0.js";import"./app.67cca86e.js";import"./AuthenticatedLayout.9a0c67e2.js";import"./new.a7629b31.js";import"./Uploader.149e5271.js";import"./index.c2533ba5.js";import"./show.84d12cc1.js";import"./pay.ba775d65.js";import"./debounce.60ec479b.js";function a(_,m,t,s){var e={s:["m\xF5ne sekundi","m\xF5ni sekund","paar sekundit"],m:["\xFChe minuti","\xFCks minut"],mm:["%d minuti","%d minutit"],h:["\xFChe tunni","tund aega","\xFCks tund"],hh:["%d tunni","%d tundi"],d:["\xFChe p\xE4eva","\xFCks p\xE4ev"],M:["kuu aja","kuu aega","\xFCks kuu"],MM:["%d kuu","%d kuud"],y:["\xFChe aasta","aasta","\xFCks aasta"],yy:["%d aasta","%d aastat"]};return m?(e[t][2]?e[t][2]:e[t][1]).replace("%d",_):(s?e[t][0]:e[t][1]).replace("%d",_)}var i={name:"et",weekdays:"p\xFChap\xE4ev_esmasp\xE4ev_teisip\xE4ev_kolmap\xE4ev_neljap\xE4ev_reede_laup\xE4ev".split("_"),weekdaysShort:"P_E_T_K_N_R_L".split("_"),weekdaysMin:"P_E_T_K_N_R_L".split("_"),months:"jaanuar_veebruar_m\xE4rts_aprill_mai_juuni_juuli_august_september_oktoober_november_detsember".split("_"),monthsShort:"jaan_veebr_m\xE4rts_apr_mai_juuni_juuli_aug_sept_okt_nov_dets".split("_"),ordinal:function(_){return _+"."},weekStart:1,relativeTime:{future:"%s p\xE4rast",past:"%s tagasi",s:a,m:a,mm:a,h:a,hh:a,d:a,dd:"%d p\xE4eva",M:a,MM:a,y:a,yy:a},formats:{LT:"H:mm",LTS:"H:mm:ss",L:"DD.MM.YYYY",LL:"D. MMMM YYYY",LLL:"D. MMMM YYYY H:mm",LLLL:"dddd, D. MMMM YYYY H:mm"}};u.locale(i,null,!0);export{i as default};
