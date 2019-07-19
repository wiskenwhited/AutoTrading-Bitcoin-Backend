<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- <link rel="stylesheet" href="/public/vendor/bootstrap-v4.css"/> -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:500">
    <link rel="shortcut icon" type="image/ico" href="/public/assets/images/logo.png"/>
    <title>XChange</title>
    <link href="/styles.min.css" rel="stylesheet">
</head>

<body>
<div id="app">
</div>
<script>!function (e) {
        function n(r) {
            if (t[r]) return t[r].exports;
            var o = t[r] = {i: r, l: !1, exports: {}};
            return e[r].call(o.exports, o, o.exports, n), o.l = !0, o.exports
        }

        var r = window.webpackJsonp;
        window.webpackJsonp = function (t, a, c) {
            for (var u, i, f, l = 0, s = []; l < t.length; l++) i = t[l], o[i] && s.push(o[i][0]), o[i] = 0;
            for (u in a) Object.prototype.hasOwnProperty.call(a, u) && (e[u] = a[u]);
            for (r && r(t, a, c); s.length;) s.shift()();
            if (c) for (l = 0; l < c.length; l++) f = n(n.s = c[l]);
            return f
        };
        var t = {}, o = {2: 0};
        n.e = function (e) {
            function r() {
                u.onerror = u.onload = null, clearTimeout(i);
                var n = o[e];
                0 !== n && (n && n[1](new Error("Loading chunk " + e + " failed.")), o[e] = void 0)
            }

            var t = o[e];
            if (0 === t) return new Promise(function (e) {
                e()
            });
            if (t) return t[2];
            var a = new Promise(function (n, r) {
                t = o[e] = [n, r]
            });
            t[2] = a;
            var c = document.getElementsByTagName("head")[0], u = document.createElement("script");
            u.type = "text/javascript", u.charset = "utf-8", u.async = !0, u.timeout = 12e4, n.nc && u.setAttribute("nonce", n.nc), u.src = n.p + "" + e + "." + {
                0: "9303b453886b21ea503b",
                1: "ab76d72ef7bf9cdba507"
            }[e] + ".js";
            var i = setTimeout(r, 12e4);
            return u.onerror = u.onload = r, c.appendChild(u), a
        }, n.m = e, n.c = t, n.d = function (e, r, t) {
            n.o(e, r) || Object.defineProperty(e, r, {configurable: !1, enumerable: !0, get: t})
        }, n.n = function (e) {
            var r = e && e.__esModule ? function () {
                return e.default
            } : function () {
                return e
            };
            return n.d(r, "a", r), r
        }, n.o = function (e, n) {
            return Object.prototype.hasOwnProperty.call(e, n)
        }, n.p = "/", n.oe = function (e) {
            throw console.error(e), e
        }
    }([]);</script>
<!-- <script src="/public/vendor/font-awesome-3e86dd6aef.min.js"></script> -->
<script type="text/javascript" src="/vendor.9303b453886b21ea503b.js"></script>
<script type="text/javascript" src="/app.ab76d72ef7bf9cdba507.js"></script>
</body>

</html>
