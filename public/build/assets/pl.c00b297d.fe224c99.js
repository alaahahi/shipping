import{s as m}from"./index.bfad2c32.js";import"./app.de011f70.js";import"./ModalAddCarPayment.ffcf5fb6.js";import"./AuthenticatedLayout.e91ec334.js";function r(i){return i%10<5&&i%10>1&&~~(i/10)%10!==1}function e(i,t,s){var a=i+" ";switch(s){case"m":return t?"minuta":"minut\u0119";case"mm":return a+(r(i)?"minuty":"minut");case"h":return t?"godzina":"godzin\u0119";case"hh":return a+(r(i)?"godziny":"godzin");case"MM":return a+(r(i)?"miesi\u0105ce":"miesi\u0119cy");case"yy":return a+(r(i)?"lata":"lat")}}var u="stycznia_lutego_marca_kwietnia_maja_czerwca_lipca_sierpnia_wrze\u015Bnia_pa\u017Adziernika_listopada_grudnia".split("_"),n="stycze\u0144_luty_marzec_kwiecie\u0144_maj_czerwiec_lipiec_sierpie\u0144_wrzesie\u0144_pa\u017Adziernik_listopad_grudzie\u0144".split("_"),o=/D MMMM/,_=function(i,t){return o.test(t)?u[i.month()]:n[i.month()]};_.s=n;_.f=u;var d={name:"pl",weekdays:"niedziela_poniedzia\u0142ek_wtorek_\u015Broda_czwartek_pi\u0105tek_sobota".split("_"),weekdaysShort:"ndz_pon_wt_\u015Br_czw_pt_sob".split("_"),weekdaysMin:"Nd_Pn_Wt_\u015Ar_Cz_Pt_So".split("_"),months:_,monthsShort:"sty_lut_mar_kwi_maj_cze_lip_sie_wrz_pa\u017A_lis_gru".split("_"),ordinal:function(i){return i+"."},weekStart:1,yearStart:4,relativeTime:{future:"za %s",past:"%s temu",s:"kilka sekund",m:e,mm:e,h:e,hh:e,d:"1 dzie\u0144",dd:"%d dni",M:"miesi\u0105c",MM:e,y:"rok",yy:e},formats:{LT:"HH:mm",LTS:"HH:mm:ss",L:"DD.MM.YYYY",LL:"D MMMM YYYY",LLL:"D MMMM YYYY HH:mm",LLLL:"dddd, D MMMM YYYY HH:mm"}};m.locale(d,null,!0);export{d as default};
