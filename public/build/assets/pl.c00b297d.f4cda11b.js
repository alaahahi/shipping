import{s as m}from"./vue-tailwind-datepicker.3dda0947.js";import"./app.1e1893f5.js";function _(i){return i%10<5&&i%10>1&&~~(i/10)%10!==1}function e(i,a,s){var t=i+" ";switch(s){case"m":return a?"minuta":"minut\u0119";case"mm":return t+(_(i)?"minuty":"minut");case"h":return a?"godzina":"godzin\u0119";case"hh":return t+(_(i)?"godziny":"godzin");case"MM":return t+(_(i)?"miesi\u0105ce":"miesi\u0119cy");case"yy":return t+(_(i)?"lata":"lat")}}var u="stycznia_lutego_marca_kwietnia_maja_czerwca_lipca_sierpnia_wrze\u015Bnia_pa\u017Adziernika_listopada_grudnia".split("_"),n="stycze\u0144_luty_marzec_kwiecie\u0144_maj_czerwiec_lipiec_sierpie\u0144_wrzesie\u0144_pa\u017Adziernik_listopad_grudzie\u0144".split("_"),o=/D MMMM/,r=function(i,a){return o.test(a)?u[i.month()]:n[i.month()]};r.s=n;r.f=u;var d={name:"pl",weekdays:"niedziela_poniedzia\u0142ek_wtorek_\u015Broda_czwartek_pi\u0105tek_sobota".split("_"),weekdaysShort:"ndz_pon_wt_\u015Br_czw_pt_sob".split("_"),weekdaysMin:"Nd_Pn_Wt_\u015Ar_Cz_Pt_So".split("_"),months:r,monthsShort:"sty_lut_mar_kwi_maj_cze_lip_sie_wrz_pa\u017A_lis_gru".split("_"),ordinal:function(i){return i+"."},weekStart:1,yearStart:4,relativeTime:{future:"za %s",past:"%s temu",s:"kilka sekund",m:e,mm:e,h:e,hh:e,d:"1 dzie\u0144",dd:"%d dni",M:"miesi\u0105c",MM:e,y:"rok",yy:e},formats:{LT:"HH:mm",LTS:"HH:mm:ss",L:"DD.MM.YYYY",LL:"D MMMM YYYY",LLL:"D MMMM YYYY HH:mm",LLLL:"dddd, D MMMM YYYY HH:mm"}};m.locale(d,null,!0);export{d as default};
