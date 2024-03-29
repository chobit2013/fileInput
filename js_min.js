!(function (e) {
    "use strict";
    function r(e, n) {
        var t = /[^\w\-.:]/.test(e)
            ? new Function(
                r.arg + ",tmpl",
                "var _e=tmpl.encode" +
                r.helper +
                ",_s='" +
                e.replace(r.regexp, r.func) +
                "';return _s;"
            )
            : (r.cache[e] = r.cache[e] || r(r.load(e)));
        return n
            ? t(n, r)
            : function (e) {
                return t(e, r);
            };
    }
    (r.cache = {}),
        (r.load = function (e) {
            return document.getElementById(e).innerHTML;
        }),
        (r.regexp =
            /([\s'\\])(?!(?:[^{]|\{(?!%))*%\})|(?:\{%(=|#)([\s\S]+?)%\})|(\{%)|(%\})/g),
        (r.func = function (e, n, t, r, c, u) {
            return n
                ? { "\n": "\\n", "\r": "\\r", "\t": "\\t", " ": " " }[n] || "\\" + n
                : t
                    ? "=" === t
                        ? "'+_e(" + r + ")+'"
                        : "'+(" + r + "==null?'':" + r + ")+'"
                    : c
                        ? "';"
                        : u
                            ? "_s+='"
                            : void 0;
        }),
        (r.encReg = /[<>&"'\x00]/g),
        (r.encMap = {
            "<": "&lt;",
            ">": "&gt;",
            "&": "&amp;",
            '"': "&quot;",
            "'": "&#39;",
        }),
        (r.encode = function (e) {
            return (null == e ? "" : "" + e).replace(r.encReg, function (e) {
                return r.encMap[e] || "";
            });
        }),
        (r.arg = "o"),
        (r.helper =
            ",print=function(s,e){_s+=e?(s==null?'':s):_e(s);},include=function(s,d){_s+=tmpl(s,d);}"),
        "function" == typeof define && define.amd
            ? define(function () {
                return r;
            })
            : "object" == typeof module && module.exports
                ? (module.exports = r)
                : (e.tmpl = r);
})(this);
//# sourceMappingURL=tmpl.min.js.map
