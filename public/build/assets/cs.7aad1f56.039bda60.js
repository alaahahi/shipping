import{s as _}from"./OnlineContracts.c1c4984b.js";import"./app.cd048905.js";import"./AuthenticatedLayout.8cb8c84b.js";import"./index.7b1a1625.js";import"./show.1d5b1382.js";import"./pay.aa5cd6e2.js";import"./new.ebfb05be.js";import"./debounce.35b1ef42.js";function r(t){return t>1&&t<5&&~~(t/10)!==1}function n(t,e,m,u){var s=t+" ";switch(m){case"s":return e||u?"p\xE1r sekund":"p\xE1r sekundami";case"m":return e?"minuta":u?"minutu":"minutou";case"mm":return e||u?s+(r(t)?"minuty":"minut"):s+"minutami";case"h":return e?"hodina":u?"hodinu":"hodinou";case"hh":return e||u?s+(r(t)?"hodiny":"hodin"):s+"hodinami";case"d":return e||u?"den":"dnem";case"dd":return e||u?s+(r(t)?"dny":"dn\xED"):s+"dny";case"M":return e||u?"m\u011Bs\xEDc":"m\u011Bs\xEDcem";case"MM":return e||u?s+(r(t)?"m\u011Bs\xEDce":"m\u011Bs\xEDc\u016F"):s+"m\u011Bs\xEDci";case"y":return e||u?"rok":"rokem";case"yy":return e||u?s+(r(t)?"roky":"let"):s+"lety"}}var o={name:"cs",weekdays:"ned\u011Ble_pond\u011Bl\xED_\xFAter\xFD_st\u0159eda_\u010Dtvrtek_p\xE1tek_sobota".split("_"),weekdaysShort:"ne_po_\xFAt_st_\u010Dt_p\xE1_so".split("_"),weekdaysMin:"ne_po_\xFAt_st_\u010Dt_p\xE1_so".split("_"),months:"leden_\xFAnor_b\u0159ezen_duben_kv\u011Bten_\u010Derven_\u010Dervenec_srpen_z\xE1\u0159\xED_\u0159\xEDjen_listopad_prosinec".split("_"),monthsShort:"led_\xFAno_b\u0159e_dub_kv\u011B_\u010Dvn_\u010Dvc_srp_z\xE1\u0159_\u0159\xEDj_lis_pro".split("_"),weekStart:1,yearStart:4,ordinal:function(t){return t+"."},formats:{LT:"H:mm",LTS:"H:mm:ss",L:"DD.MM.YYYY",LL:"D. MMMM YYYY",LLL:"D. MMMM YYYY H:mm",LLLL:"dddd D. MMMM YYYY H:mm",l:"D. M. YYYY"},relativeTime:{future:"za %s",past:"p\u0159ed %s",s:n,m:n,mm:n,h:n,hh:n,d:n,dd:n,M:n,MM:n,y:n,yy:n}};_.locale(o,null,!0);export{o as default};
