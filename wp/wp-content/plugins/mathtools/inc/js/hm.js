function digitsalgebratiles() {

    var
        n = document,
        o = window.__gwtStatsEvent ? function (a) { return window.__gwtStatsEvent(a) } : null,
        p = window.__gwtStatsSessionId ? window.__gwtStatsSessionId : null,
        q,
        r,
        s,
        t = '',
        u = {},
        v = [],
        w = [],
        x = [],
        y = 0,
        z,
        A;

    o && o({moduleName: 'digitsalgebratiles', sessionId: p, subSystem: 'startup', evtGroup: 'bootstrap', millis: (new Date).getTime(), type: 'begin'});

    if (!window.__gwt_stylesLoaded) {
        window.__gwt_stylesLoaded = {}
    }

    if (!window.__gwt_scriptsLoaded) {
        window.__gwt_scriptsLoaded = {}
    }

    function B() {
        var b = false;
        try {
            var c = window.location.search;
            return (c.indexOf('gwt.codesvr=') != -1 || (c.indexOf('gwt.hosted=') != -1 || window.external && window.external.gwtOnLoad)) && c.indexOf('gwt.hybrid') == -1
        } catch (a) {
        }
        B = function () {
            return b
        };
        return b
    }

    function C() {
        if (q && r) {
            var b = n.getElementById('digitsalgebratiles');
            var c = b.contentWindow;
            if (B()) {
                c.__gwt_getProperty = function (a) {
                    return I(a)
                }
            }
            digitsalgebratiles = null;
            c.gwtOnLoad(z, 'digitsalgebratiles', t, y);
            o && o({moduleName: 'digitsalgebratiles', sessionId: p, subSystem: 'startup', evtGroup: 'moduleStartup', millis: (new Date).getTime(), type: 'end'})
        }
    }

    function D() {
        function e(a) {
            var b = a.lastIndexOf('#');
            if (b == -1) {
                b = a.length
            }
            var c = a.indexOf('?');
            if (c == -1) {
                c = a.length
            }
            var d = a.lastIndexOf('/', Math.min(c, b));
            return d >= 0 ? a.substring(0, d + 1) : ''
        }

        function f(a) {
            if (a.match(/^\w+:\/\//)) {
            } else {
                var b = n.createElement('img');
                b.src = a + 'clear.cache.gif';
                a = e(b.src)
            }
            return a
        }

        function g() {
            var a = G('baseUrl');
            if (a != null) {
                return a
            }
            return ''
        }

        function h() {
            var a = n.getElementsByTagName('script');
            for (var b = 0; b < a.length; ++b) {
                if (a[b].src.indexOf('digitsalgebratiles.nocache.js') != -1) {
                    return e(a[b].src)
                }
            }
            return ''
        }

        function i() {
            var a;
            if (typeof isBodyLoaded == 'undefined' || !isBodyLoaded()) {
                var b = '__gwt_marker_digitsalgebratiles';
                var c;
                n.write('<script id="' + b + '"><\/script>');
                c = n.getElementById(b);
                a = c && c.previousSibling;
                while (a && a.tagName != 'SCRIPT') {
                    a = a.previousSibling
                }
                if (c) {
                    c.parentNode.removeChild(c)
                }
                if (a && a.src) {
                    return e(a.src)
                }
            }
            return ''
        }

        function j() {
            var a = n.getElementsByTagName('base');
            if (a.length > 0) {
                return a[a.length - 1].href
            }
            return ''
        }

        function k() {
            var a = n.location;
            return a.href == a.protocol + '//' + a.host + a.pathname + a.search + a.hash
        }

        var l = g();
        if (l == '') {
            l = h()
        }
        if (l == '') {
            l = i()
        }
        if (l == '') {
            l = j()
        }
        if (l == '' && k()) {
            l = e(n.location.href)
        }
        l = f(l);
        t = l;
        return l
    }

    function E() {
        var b = document.getElementsByTagName('meta');
        for (var c = 0, d = b.length; c < d; ++c) {
            var e = b[c], f = e.getAttribute('name'), g;
            if (f) {
                f = f.replace('digitsalgebratiles::', '');
                if (f.indexOf('::') >= 0) {
                    continue
                }
                if (f == 'gwt:property') {
                    g = e.getAttribute('content');
                    if (g) {
                        var h, i = g.indexOf('=');
                        if (i >= 0) {
                            f = g.substring(0, i);
                            h = g.substring(i + 1)
                        } else {
                            f = g;
                            h = ''
                        }
                        u[f] = h
                    }
                } else if (f == 'gwt:onPropertyErrorFn') {
                    g = e.getAttribute('content');
                    if (g) {
                        try {
                            A = eval(g)
                        } catch (a) {
                            alert('Bad handler "' + g + '" for "gwt:onPropertyErrorFn"')
                        }
                    }
                } else if (f == 'gwt:onLoadErrorFn') {
                    g = e.getAttribute('content');
                    if (g) {
                        try {
                            z = eval(g)
                        } catch (a) {
                            alert('Bad handler "' + g + '" for "gwt:onLoadErrorFn"')
                        }
                    }
                }
            }
        }
    }

    function F(a, b) {
        return b in v[a]
    }

    function G(a) {
        var b = u[a];
        return b == null ? null : b
    }

    function H(a, b) {
        var c = x;
        for (var d = 0, e = a.length - 1; d < e; ++d) {
            c = c[a[d]] || (c[a[d]] = [])
        }
        c[a[e]] = b
    }

    function I(a) {
        var b = w[a](), c = v[a];
        if (b in c) {
            return b
        }
        var d = [];
        for (var e in c) {
            d[c[e]] = e
        }
        if (A) {
            A(a, d, b)
        }
        throw null
    }

    var J;

    function K() {
        if (!J) {
            J = true;
            var a = n.createElement('iframe');
            a.src = "javascript:''";
            a.id = 'digitsalgebratiles';
            a.style.cssText = 'position:absolute;width:0;height:0;border:none';
            a.tabIndex = -1;
            n.body.appendChild(a);
            o && o({moduleName: 'digitsalgebratiles', sessionId: p, subSystem: 'startup', evtGroup: 'moduleStartup', millis: (new Date).getTime(), type: 'moduleRequested'});
            a.contentWindow.location.replace(t + M)
        }
    }

    w['formfactor'] = function () {
        var a = location.search;
        var b = a.indexOf('formfactor');
        if (b >= 0) {
            var c = a.substring(b);
            var d = c.indexOf('=') + 1;
            var e = c.indexOf('&');
            if (e == -1) {
                e = c.length
            }
            return c.substring(d, e)
        }
        var f = navigator.userAgent.toLowerCase();
        if (f.indexOf('iphone') != -1 || (f.indexOf('ipod') != -1 || f.indexOf('ipad') != -1)) {
            return 'ios'
        }
        return 'desktop'
    };

    v['formfactor'] = {desktop: 0, ios: 1};

    w['locale'] = function () {
        var b = null;
        var c = 'default';
        try {
            if (!b) {
                var d = location.search;
                var e = d.indexOf('locale=');
                if (e >= 0) {
                    var f = d.substring(e + 7);
                    var g = d.indexOf('&', e);
                    if (g < 0) {
                        g = d.length
                    }
                    b = d.substring(e + 7, g)
                }
            }
            if (!b) {
                b = G('locale')
            }
            if (!b) {
                b = window['__gwt_Locale']
            }
            if (b) {
                c = b
            }
            while (b && !F('locale', b)) {
                var h = b.lastIndexOf('_');
                if (h < 0) {
                    b = null;
                    break
                }
                b = b.substring(0, h)
            }
        } catch (a) {
            alert('Unexpected exception in locale detection, using default: ' + a)
        }
        window['__gwt_Locale'] = c;
        return b || 'default'
    };
    v['locale'] = {'default': 0, fr_CA: 1};
    w['user.agent'] = function () {
        var b = navigator.userAgent.toLowerCase();
        var c = function (a) {
            return parseInt(a[1]) * 1000 + parseInt(a[2])
        };
        if (function () {
                return b.indexOf('opera') != -1
            }())return 'opera';
        if (function () {
                return b.indexOf('webkit') != -1
            }())return 'safari';
        if (function () {
                return b.indexOf('msie') != -1 && n.documentMode >= 9
            }())return 'ie9';
        if (function () {
                return b.indexOf('msie') != -1 && n.documentMode >= 8
            }())return 'ie8';
        if (function () {
                var a = /msie ([0-9]+)\.([0-9]+)/.exec(b);
                if (a && a.length == 3)return c(a) >= 6000
            }())return 'ie6';
        if (function () {
                return b.indexOf('gecko') != -1
            }())return 'gecko1_8';
        return 'unknown'
    };
    v['user.agent'] = {gecko1_8: 0, ie6: 1, ie8: 2, ie9: 3, opera: 4, safari: 5};
    digitsalgebratiles.onScriptLoad = function () {
        if (J) {
            r = true;
            C()
        }
    };
    digitsalgebratiles.onInjectionDone = function () {
        q = true;
        o && o({moduleName: 'digitsalgebratiles', sessionId: p, subSystem: 'startup', evtGroup: 'loadExternalRefs', millis: (new Date).getTime(), type: 'end'});
        C()
    };
    E();
    D();
    var L;
    var M;
    if (B()) {
        if (window.external && (window.external.initModule && window.external.initModule('digitsalgebratiles'))) {
            window.location.reload();
            return
        }
        M = 'hosted.html?digitsalgebratiles';
        L = ''
    }
    o && o({moduleName: 'digitsalgebratiles', sessionId: p, subSystem: 'startup', evtGroup: 'bootstrap', millis: (new Date).getTime(), type: 'selectingPermutation'});
    if (!B()) {
        try {
            H(['desktop', 'fr_CA', 'gecko1_8'], '1CED1724B66C7602C3ED6C9B67750884');
            H(['ios', 'fr_CA', 'gecko1_8'], '1CED1724B66C7602C3ED6C9B67750884');
            H(['desktop', 'fr_CA', 'gecko1_8'], '1CED1724B66C7602C3ED6C9B67750884' + ':1');
            H(['ios', 'fr_CA', 'gecko1_8'], '1CED1724B66C7602C3ED6C9B67750884' + ':1');

            H(['desktop', 'default', 'opera'], '343711586D098295546378413C35DE65');
            H(['ios', 'default', 'opera'], '343711586D098295546378413C35DE65');
            H(['desktop', 'default', 'opera'], '343711586D098295546378413C35DE65' + ':1');
            H(['ios', 'default', 'opera'], '343711586D098295546378413C35DE65' + ':1');

            H(['desktop', 'default', 'gecko1_8'], '759CB3176BC5CE8965DD75C333A9CC02');
            H(['ios', 'default', 'gecko1_8'], '759CB3176BC5CE8965DD75C333A9CC02');
            H(['desktop', 'default', 'gecko1_8'], '759CB3176BC5CE8965DD75C333A9CC02' + ':1');
            H(['ios', 'default', 'gecko1_8'], '759CB3176BC5CE8965DD75C333A9CC02' + ':1');

            H(['desktop', 'default', 'ie9'], 'AA49163F958D5B8B9EECB4BEA7A91F59');
            H(['ios', 'default', 'ie9'], 'AA49163F958D5B8B9EECB4BEA7A91F59');
            H(['desktop', 'default', 'ie9'], 'AA49163F958D5B8B9EECB4BEA7A91F59' + ':1');
            H(['ios', 'default', 'ie9'], 'AA49163F958D5B8B9EECB4BEA7A91F59' + ':1');

            H(['desktop', 'fr_CA', 'safari'], 'AC969689691D86AECC290506A99B1907');
            H(['ios', 'fr_CA', 'safari'], 'AC969689691D86AECC290506A99B1907');
            H(['desktop', 'fr_CA', 'safari'], 'AC969689691D86AECC290506A99B1907' + ':1');
            H(['ios', 'fr_CA', 'safari'], 'AC969689691D86AECC290506A99B1907' + ':1');

            H(['desktop', 'default', 'safari'], 'F55F90BE08F0909EB4FAA7FA46D5A22F');
            H(['ios', 'default', 'safari'], 'F55F90BE08F0909EB4FAA7FA46D5A22F');
            H(['desktop', 'default', 'safari'], 'F55F90BE08F0909EB4FAA7FA46D5A22F' + ':1');
            H(['ios', 'default', 'safari'], 'F55F90BE08F0909EB4FAA7FA46D5A22F' + ':1');

            H(['desktop', 'fr_CA', 'opera'], 'F6ADB3C8BA6D083C18D7DAB8DF95838B');
            H(['ios', 'fr_CA', 'opera'], 'F6ADB3C8BA6D083C18D7DAB8DF95838B');
            H(['desktop', 'fr_CA', 'opera'], 'F6ADB3C8BA6D083C18D7DAB8DF95838B' + ':1');
            H(['ios', 'fr_CA', 'opera'], 'F6ADB3C8BA6D083C18D7DAB8DF95838B' + ':1');

            H(['desktop', 'fr_CA', 'ie9'], 'FD2746E72EB576D3BEE18BA5570A0F23');
            H(['ios', 'fr_CA', 'ie9'], 'FD2746E72EB576D3BEE18BA5570A0F23');
            H(['desktop', 'fr_CA', 'ie9'], 'FD2746E72EB576D3BEE18BA5570A0F23' + ':1');
            H(['ios', 'fr_CA', 'ie9'], 'FD2746E72EB576D3BEE18BA5570A0F23' + ':1');
            L = x[I('formfactor')][I('locale')][I('user.agent')];
            var N = L.indexOf(':');
            if (N != -1) {
                y = Number(L.substring(N + 1));
                L = L.substring(0, N)
            }
            M = L + '.cache.html'
        } catch (a) {
            return
        }
    }
    var O;

    function P() {
        if (!s) {
            s = true;
            C();
            if (n.removeEventListener) {
                n.removeEventListener('DOMContentLoaded', P, false)
            }
            if (O) {
                clearInterval(O)
            }
        }
    }

    if (n.addEventListener) {
        n.addEventListener('DOMContentLoaded', function () {
            K();
            P()
        }, false)
    }
    var O = setInterval(function () {
        if (/loaded|complete/.test(n.readyState)) {
            K();
            P()
        }
    }, 50);
    o && o({moduleName: 'digitsalgebratiles', sessionId: p, subSystem: 'startup', evtGroup: 'bootstrap', millis: (new Date).getTime(), type: 'end'});
    o && o({moduleName: 'digitsalgebratiles', sessionId: p, subSystem: 'startup', evtGroup: 'loadExternalRefs', millis: (new Date).getTime(), type: 'begin'});
    n.write('<script defer="defer">digitsalgebratiles.onInjectionDone(\'digitsalgebratiles\')<\/script>')
}
digitsalgebratiles();