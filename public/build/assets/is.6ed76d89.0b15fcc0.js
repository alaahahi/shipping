import{s as i}from"./OnlineContracts.a394d3d2.js";import"./app.740522a6.js";import"./AuthenticatedLayout.5f7b7290.js";import"./new.a04eb886.js";import"./Uploader.b94f4e90.js";import"./index.47d1ecbe.js";import"./show.b7a4fe74.js";import"./pay.bcb74980.js";import"./debounce.7c9e584c.js";var F={s:["nokkrar sek\xFAndur","nokkrar sek\xFAndur","nokkrum sek\xFAndum"],m:["m\xEDn\xFAta","m\xEDn\xFAtu","m\xEDn\xFAtu"],mm:["m\xEDn\xFAtur","m\xEDn\xFAtur","m\xEDn\xFAtum"],h:["klukkustund","klukkustund","klukkustund"],hh:["klukkustundir","klukkustundir","klukkustundum"],d:["dagur","dag","degi"],dd:["dagar","daga","d\xF6gum"],M:["m\xE1nu\xF0ur","m\xE1nu\xF0","m\xE1nu\xF0i"],MM:["m\xE1nu\xF0ir","m\xE1nu\xF0i","m\xE1nu\xF0um"],y:["\xE1r","\xE1r","\xE1ri"],yy:["\xE1r","\xE1r","\xE1rum"]};function k(x,m,u,n){var t=u?1:2,_=n?0:t,d=x.length===2&&m%10===1,s=d?x[0]:x,e=F[s],a=e[_];return x.length===1?a:"%d "+a}function r(x,m,u,n){var t=k(u,x,n,m);return t.replace("%d",x)}var E={name:"is",weekdays:"sunnudagur_m\xE1nudagur_\xFEri\xF0judagur_mi\xF0vikudagur_fimmtudagur_f\xF6studagur_laugardagur".split("_"),months:"jan\xFAar_febr\xFAar_mars_apr\xEDl_ma\xED_j\xFAn\xED_j\xFAl\xED_\xE1g\xFAst_september_okt\xF3ber_n\xF3vember_desember".split("_"),weekStart:1,weekdaysShort:"sun_m\xE1n_\xFEri_mi\xF0_fim_f\xF6s_lau".split("_"),monthsShort:"jan_feb_mar_apr_ma\xED_j\xFAn_j\xFAl_\xE1g\xFA_sep_okt_n\xF3v_des".split("_"),weekdaysMin:"Su_M\xE1_\xDEr_Mi_Fi_F\xF6_La".split("_"),ordinal:function(x){return x},formats:{LT:"H:mm",LTS:"H:mm:ss",L:"DD.MM.YYYY",LL:"D. MMMM YYYY",LLL:"D. MMMM YYYY [kl.] H:mm",LLLL:"dddd, D. MMMM YYYY [kl.] H:mm"},relativeTime:{future:"eftir %s",past:"fyrir %s s\xED\xF0an",s:r,m:r,mm:r,h:r,hh:r,d:r,dd:r,M:r,MM:r,y:r,yy:r}};i.locale(E,null,!0);export{E as default};