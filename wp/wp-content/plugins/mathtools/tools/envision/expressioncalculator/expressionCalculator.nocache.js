function expressionCalculator(){var Q='',yb='" for "gwt:onLoadErrorFn"',wb='" for "gwt:onPropertyErrorFn"',jb='"><\/script>',$='#',Eb='&',sc='.cache.html',ab='/',mb='//',dc='08E3541ADFE55E8ED6D7393C6AA943A4',gc='13857A7E750D05E7FDE3F03296BE6995',hc='3540A9F0907C7C0723B4AFF373B266E0',ic='469849951E4B34C571386D5892C2E4C5',jc='4C141D54E1329A182343D64300B1DFE6',kc='53B8AA92282F68DA52429BDC4D1A901B',lc='53C2499FF3F20D7FBE21C52310C54E7B',mc='6231036DCDCD38F30992956C98FECD31',nc='69D014ED05EE129F16C1CFF7F5A6AF2E',rc=':',ec=':1',qb='::',uc='<script defer="defer">expressionCalculator.onInjectionDone(\'expressionCalculator\')<\/script>',ib='<script id="',tb='=',_='?',oc='ABB99307B897AE65593465DEF9179497',pc='AEBD5D6854393714519A0FAF20C0945F',vb='Bad handler "',tc='DOMContentLoaded',qc='F17106FB1CE927761AA7B0B3E413662C',kb='SCRIPT',Pb='Unexpected exception in locale detection, using default: ',Ob='_',Nb='__gwt_Locale',hb='__gwt_marker_expressionCalculator',lb='base',db='baseUrl',U='begin',T='bootstrap',cb='clear.cache.gif',sb='content',Lb='default',Jb='desktop',Z='end',cc='es_US',R='expressionCalculator',fb='expressionCalculator.nocache.js',pb='expressionCalculator::',Db='formfactor',fc='fr_CA',Yb='gecko',Zb='gecko1_8',V='gwt.codesvr=',W='gwt.hosted=',X='gwt.hybrid',xb='gwt:onLoadErrorFn',ub='gwt:onPropertyErrorFn',rb='gwt:property',ac='hosted.html?expressionCalculator',Xb='ie6',Wb='ie8',Vb='ie9',zb='iframe',bb='img',Ib='ios',Hb='ipad',Fb='iphone',Gb='ipod',Ab="javascript:''",_b='loadExternalRefs',Kb='locale',Mb='locale=',nb='meta',Cb='moduleRequested',Y='moduleStartup',Ub='msie',ob='name',Rb='opera',Bb='position:absolute;width:0;height:0;border:none',Tb='safari',eb='script',bc='selectingPermutation',S='startup',gb='undefined',$b='unknown',Qb='user.agent',Sb='webkit';var m=window,n=document,o=m.__gwtStatsEvent?function(a){return m.__gwtStatsEvent(a)}:null,p=m.__gwtStatsSessionId?m.__gwtStatsSessionId:null,q,r,s,t=Q,u={},v=[],w=[],x=[],y=0,z,A;o&&o({moduleName:R,sessionId:p,subSystem:S,evtGroup:T,millis:(new Date).getTime(),type:U});if(!m.__gwt_stylesLoaded){m.__gwt_stylesLoaded={}}if(!m.__gwt_scriptsLoaded){m.__gwt_scriptsLoaded={}}function B(){var b=false;try{var c=m.location.search;return (c.indexOf(V)!=-1||(c.indexOf(W)!=-1||m.external&&m.external.gwtOnLoad))&&c.indexOf(X)==-1}catch(a){}B=function(){return b};return b}
function C(){if(q&&r){var b=n.getElementById(R);var c=b.contentWindow;if(B()){c.__gwt_getProperty=function(a){return I(a)}}expressionCalculator=null;c.gwtOnLoad(z,R,t,y);o&&o({moduleName:R,sessionId:p,subSystem:S,evtGroup:Y,millis:(new Date).getTime(),type:Z})}}
function D(){function e(a){var b=a.lastIndexOf($);if(b==-1){b=a.length}var c=a.indexOf(_);if(c==-1){c=a.length}var d=a.lastIndexOf(ab,Math.min(c,b));return d>=0?a.substring(0,d+1):Q}
function f(a){if(a.match(/^\w+:\/\//)){}else{var b=n.createElement(bb);b.src=a+cb;a=e(b.src)}return a}
function g(){var a=G(db);if(a!=null){return a}return Q}
function h(){var a=n.getElementsByTagName(eb);for(var b=0;b<a.length;++b){if(a[b].src.indexOf(fb)!=-1){return e(a[b].src)}}return Q}
function i(){var a;if(typeof isBodyLoaded==gb||!isBodyLoaded()){var b=hb;var c;n.write(ib+b+jb);c=n.getElementById(b);a=c&&c.previousSibling;while(a&&a.tagName!=kb){a=a.previousSibling}if(c){c.parentNode.removeChild(c)}if(a&&a.src){return e(a.src)}}return Q}
function j(){var a=n.getElementsByTagName(lb);if(a.length>0){return a[a.length-1].href}return Q}
function k(){var a=n.location;return a.href==a.protocol+mb+a.host+a.pathname+a.search+a.hash}
var l=g();if(l==Q){l=h()}if(l==Q){l=i()}if(l==Q){l=j()}if(l==Q&&k()){l=e(n.location.href)}l=f(l);t=l;return l}
function E(){var b=document.getElementsByTagName(nb);for(var c=0,d=b.length;c<d;++c){var e=b[c],f=e.getAttribute(ob),g;if(f){f=f.replace(pb,Q);if(f.indexOf(qb)>=0){continue}if(f==rb){g=e.getAttribute(sb);if(g){var h,i=g.indexOf(tb);if(i>=0){f=g.substring(0,i);h=g.substring(i+1)}else{f=g;h=Q}u[f]=h}}else if(f==ub){g=e.getAttribute(sb);if(g){try{A=eval(g)}catch(a){alert(vb+g+wb)}}}else if(f==xb){g=e.getAttribute(sb);if(g){try{z=eval(g)}catch(a){alert(vb+g+yb)}}}}}}
function F(a,b){return b in v[a]}
function G(a){var b=u[a];return b==null?null:b}
function H(a,b){var c=x;for(var d=0,e=a.length-1;d<e;++d){c=c[a[d]]||(c[a[d]]=[])}c[a[e]]=b}
function I(a){var b=w[a](),c=v[a];if(b in c){return b}var d=[];for(var e in c){d[c[e]]=e}if(A){A(a,d,b)}throw null}
var J;function K(){if(!J){J=true;var a=n.createElement(zb);a.src=Ab;a.id=R;a.style.cssText=Bb;a.tabIndex=-1;n.body.appendChild(a);o&&o({moduleName:R,sessionId:p,subSystem:S,evtGroup:Y,millis:(new Date).getTime(),type:Cb});a.contentWindow.location.replace(t+M)}}
w[Db]=function(){var a=location.search;var b=a.indexOf(Db);if(b>=0){var c=a.substring(b);var d=c.indexOf(tb)+1;var e=c.indexOf(Eb);if(e==-1){e=c.length}return c.substring(d,e)}var f=navigator.userAgent.toLowerCase();if(f.indexOf(Fb)!=-1||(f.indexOf(Gb)!=-1||f.indexOf(Hb)!=-1)){return Ib}return Jb};v[Db]={desktop:0,ios:1};w[Kb]=function(){var b=null;var c=Lb;try{if(!b){var d=location.search;var e=d.indexOf(Mb);if(e>=0){var f=d.substring(e+7);var g=d.indexOf(Eb,e);if(g<0){g=d.length}b=d.substring(e+7,g)}}if(!b){b=G(Kb)}if(!b){b=m[Nb]}if(b){c=b}while(b&&!F(Kb,b)){var h=b.lastIndexOf(Ob);if(h<0){b=null;break}b=b.substring(0,h)}}catch(a){alert(Pb+a)}m[Nb]=c;return b||Lb};v[Kb]={'default':0,es_US:1,fr_CA:2};w[Qb]=function(){var b=navigator.userAgent.toLowerCase();var c=function(a){return parseInt(a[1])*1000+parseInt(a[2])};if(function(){return b.indexOf(Rb)!=-1}())return Rb;if(function(){return b.indexOf(Sb)!=-1}())return Tb;if(function(){return b.indexOf(Ub)!=-1&&n.documentMode>=9}())return Vb;if(function(){return b.indexOf(Ub)!=-1&&n.documentMode>=8}())return Wb;if(function(){var a=/msie ([0-9]+)\.([0-9]+)/.exec(b);if(a&&a.length==3)return c(a)>=6000}())return Xb;if(function(){return b.indexOf(Yb)!=-1}())return Zb;return $b};v[Qb]={gecko1_8:0,ie6:1,ie8:2,ie9:3,opera:4,safari:5};expressionCalculator.onScriptLoad=function(){if(J){r=true;C()}};expressionCalculator.onInjectionDone=function(){q=true;o&&o({moduleName:R,sessionId:p,subSystem:S,evtGroup:_b,millis:(new Date).getTime(),type:Z});C()};E();D();var L;var M;if(B()){if(m.external&&(m.external.initModule&&m.external.initModule(R))){m.location.reload();return}M=ac;L=Q}o&&o({moduleName:R,sessionId:p,subSystem:S,evtGroup:T,millis:(new Date).getTime(),type:bc});if(!B()){try{H([Jb,cc,Vb],dc);H([Ib,cc,Vb],dc);H([Jb,cc,Vb],dc+ec);H([Ib,cc,Vb],dc+ec);H([Jb,fc,Tb],gc);H([Ib,fc,Tb],gc);H([Jb,fc,Tb],gc+ec);H([Ib,fc,Tb],gc+ec);H([Jb,Lb,Tb],hc);H([Ib,Lb,Tb],hc);H([Jb,Lb,Tb],hc+ec);H([Ib,Lb,Tb],hc+ec);H([Jb,Lb,Zb],ic);H([Ib,Lb,Zb],ic);H([Jb,Lb,Zb],ic+ec);H([Ib,Lb,Zb],ic+ec);H([Jb,cc,Tb],jc);H([Ib,cc,Tb],jc);H([Jb,cc,Tb],jc+ec);H([Ib,cc,Tb],jc+ec);H([Jb,fc,Rb],kc);H([Ib,fc,Rb],kc);H([Jb,fc,Rb],kc+ec);H([Ib,fc,Rb],kc+ec);H([Jb,Lb,Vb],lc);H([Ib,Lb,Vb],lc);H([Jb,Lb,Vb],lc+ec);H([Ib,Lb,Vb],lc+ec);H([Jb,Lb,Rb],mc);H([Ib,Lb,Rb],mc);H([Jb,Lb,Rb],mc+ec);H([Ib,Lb,Rb],mc+ec);H([Jb,fc,Vb],nc);H([Ib,fc,Vb],nc);H([Jb,fc,Vb],nc+ec);H([Ib,fc,Vb],nc+ec);H([Jb,cc,Zb],oc);H([Ib,cc,Zb],oc);H([Jb,cc,Zb],oc+ec);H([Ib,cc,Zb],oc+ec);H([Jb,cc,Rb],pc);H([Ib,cc,Rb],pc);H([Jb,cc,Rb],pc+ec);H([Ib,cc,Rb],pc+ec);H([Jb,fc,Zb],qc);H([Ib,fc,Zb],qc);H([Jb,fc,Zb],qc+ec);H([Ib,fc,Zb],qc+ec);L=x[I(Db)][I(Kb)][I(Qb)];var N=L.indexOf(rc);if(N!=-1){y=Number(L.substring(N+1));L=L.substring(0,N)}M=L+sc}catch(a){return}}var O;function P(){if(!s){s=true;C();if(n.removeEventListener){n.removeEventListener(tc,P,false)}if(O){clearInterval(O)}}}
if(n.addEventListener){n.addEventListener(tc,function(){K();P()},false)}var O=setInterval(function(){if(/loaded|complete/.test(n.readyState)){K();P()}},50);o&&o({moduleName:R,sessionId:p,subSystem:S,evtGroup:T,millis:(new Date).getTime(),type:Z});o&&o({moduleName:R,sessionId:p,subSystem:S,evtGroup:_b,millis:(new Date).getTime(),type:U});n.write(uc)}
expressionCalculator();