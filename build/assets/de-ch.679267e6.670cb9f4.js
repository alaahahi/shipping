import{s as a}from"./OnlineContracts.7e6b58a9.js";import"./app.ba6aa00c.js";import"./AuthenticatedLayout.239eef01.js";import"./index.a409e988.js";import"./show.8f21004f.js";import"./pay.79897be5.js";import"./new.a3e3c254.js";import"./debounce.a215c036.js";var _={s:"ein paar Sekunden",m:["eine Minute","einer Minute"],mm:"%d Minuten",h:["eine Stunde","einer Stunde"],hh:"%d Stunden",d:["ein Tag","einem Tag"],dd:["%d Tage","%d Tagen"],M:["ein Monat","einem Monat"],MM:["%d Monate","%d Monaten"],y:["ein Jahr","einem Jahr"],yy:["%d Jahre","%d Jahren"]};function e(n,t,i){var r=_[i];return Array.isArray(r)&&(r=r[t?0:1]),r.replace("%d",n)}var M={name:"de-ch",weekdays:"Sonntag_Montag_Dienstag_Mittwoch_Donnerstag_Freitag_Samstag".split("_"),weekdaysShort:"So_Mo_Di_Mi_Do_Fr_Sa".split("_"),weekdaysMin:"So_Mo_Di_Mi_Do_Fr_Sa".split("_"),months:"Januar_Februar_M\xE4rz_April_Mai_Juni_Juli_August_September_Oktober_November_Dezember".split("_"),monthsShort:"Jan._Feb._M\xE4rz_Apr._Mai_Juni_Juli_Aug._Sep._Okt._Nov._Dez.".split("_"),ordinal:function(n){return n+"."},weekStart:1,formats:{LT:"HH:mm",LTS:"HH:mm:ss",L:"DD.MM.YYYY",LL:"D. MMMM YYYY",LLL:"D. MMMM YYYY HH:mm",LLLL:"dddd, D. MMMM YYYY HH:mm"},relativeTime:{future:"in %s",past:"vor %s",s:e,m:e,mm:e,h:e,hh:e,d:e,dd:e,M:e,MM:e,y:e,yy:e}};a.locale(M,null,!0);export{M as default};
