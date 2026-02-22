/*
 * jQuery Templates Plugin 1.0.0pre
 * http://github.com/jquery/jquery-tmpl
 * Requires jQuery 1.4.2
 *
 * Copyright 2011, Software Freedom Conservancy, Inc.
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 */
(function(a){var r=a.fn.domManip,d="_tmplitem",q=/^[^<]*(<[\w\W]+>)[^>]*$|\{\{\! /,b={},f={},e,p={key:0,data:{}},i=0,c=0,l=[];function g(g,d,h,e){var c={data:e||(e===0||e===false)?e:d?d.data:{},_wrap:d?d._wrap:null,tmpl:null,parent:d||null,nodes:[],calls:u,nest:w,wrap:x,html:v,update:t};g&&a.extend(c,g,{nodes:[],parent:d});if(h){c.tmpl=h;c._ctnt=c._ctnt||c.tmpl(a,c);c.key=++i;(l.length?f:b)[i]=c}return c}a.each({appendTo:"append",prependTo:"prepend",insertBefore:"before",insertAfter:"after",replaceAll:"replaceWith"},function(f,d){a.fn[f]=function(n){var g=[],i=a(n),k,h,m,l,j=this.length===1&&this[0].parentNode;e=b||{};if(j&&j.nodeType===11&&j.childNodes.length===1&&i.length===1){i[d](this[0]);g=this}else{for(h=0,m=i.length;h<m;h++){c=h;k=(h>0?this.clone(true):this).get();a(i[h])[d](k);g=g.concat(k)}c=0;g=this.pushStack(g,f,i.selector)}l=e;e=null;a.tmpl.complete(l);return g}});a.fn.extend({tmpl:function(d,c,b){return a.tmpl(this[0],d,c,b)},tmplItem:function(){return a.tmplItem(this[0])},template:function(b){return a.template(b,this[0])},domManip:function(d,m,k){if(d[0]&&a.isArray(d[0])){var g=a.makeArray(arguments),h=d[0],j=h.length,i=0,f;while(i<j&&!(f=a.data(h[i++],"tmplItem")));if(f&&c)g[2]=function(b){a.tmpl.afterManip(this,b,k)};r.apply(this,g)}else r.apply(this,arguments);c=0;!e&&a.tmpl.complete(b);return this}});a.extend({tmpl:function(d,h,e,c){var i,k=!c;if(k){c=p;d=a.template[d]||a.template(null,d);f={}}else if(!d){d=c.tmpl;b[c.key]=c;c.nodes=[];c.wrapped&&n(c,c.wrapped);return a(j(c,null,c.tmpl(a,c)))}if(!d)return[];if(typeof h==="function")h=h.call(c||{});e&&e.wrapped&&n(e,e.wrapped);i=a.isArray(h)?a.map(h,function(a){return a?g(e,c,d,a):null}):[g(e,c,d,h)];return k?a(j(c,null,i)):i},tmplItem:function(b){var c;if(b instanceof a)b=b[0];while(b&&b.nodeType===1&&!(c=a.data(b,"tmplItem"))&&(b=b.parentNode));return c||p},template:function(c,b){if(b){if(typeof b==="string")b=o(b);else if(b instanceof a)b=b[0]||{};if(b.nodeType)b=a.data(b,"tmpl")||a.data(b,"tmpl",o(b.innerHTML));return typeof c==="string"?(a.template[c]=b):b}return c?typeof c!=="string"?a.template(null,c):a.template[c]||a.template(null,q.test(c)?c:a(c)):null},encode:function(a){return(""+a).split("<").join("&lt;").split(">").join("&gt;").split('"').join("&#34;").split("'").join("&#39;")}});a.extend(a.tmpl,{tag:{tmpl:{_default:{$2:"null"},open:"if($notnull_1){__=__.concat($item.nest($1,$2));}"},wrap:{_default:{$2:"null"},open:"$item.calls(__,$1,$2);__=[];",close:"call=$item.calls();__=call._.concat($item.wrap(call,__));"},each:{_default:{$2:"$index, $value"},open:"if($notnull_1){$.each($1a,function($2){with(this){",close:"}});}"},"if":{open:"if(($notnull_1) && $1a){",close:"}"},"else":{_default:{$1:"true"},open:"}else if(($notnull_1) && $1a){"},html:{open:"if($notnull_1){__.push($1a);}"},"=":{_default:{$1:"$data"},open:"if($notnull_1){__.push($.encode($1a));}"},"!":{open:""}},complete:function(){b={}},afterManip:function(f,b,d){var e=b.nodeType===11?a.makeArray(b.childNodes):b.nodeType===1?[b]:[];d.call(f,b);m(e);c++}});function j(e,g,f){var b,c=f?a.map(f,function(a){return typeof a==="string"?e.key?a.replace(/(<\w+)(?=[\s>])(?![^>]*_tmplitem)([^>]*)/g,"$1 "+d+'="'+e.key+'" $2'):a:j(a,e,a._ctnt)}):e;if(g)return c;c=c.join("");c.replace(/^\s*([^<\s][^<]*)?(<[\w\W]+>)([^>]*[^>\s])?\s*$/,function(f,c,e,d){b=a(e).get();m(b);if(c)b=k(c).concat(b);if(d)b=b.concat(k(d))});return b?b:k(c)}function k(c){var b=document.createElement("div");b.innerHTML=c;return a.makeArray(b.childNodes)}function o(b){return new Function("jQuery","$item","var $=jQuery,call,__=[],$data=$item.data;with($data){__.push('"+a.trim(b).replace(/([\\'])/g,"\\$1").replace(/[\r\t\n]/g," ").replace(/\$\{([^\}]*)\}/g,"{{= $1}}").replace(/\{\{(\/?)(\w+|.)(?:\(((?:[^\}]|\}(?!\}))*?)?\))?(?:\s+(.*?)?)?(\(((?:[^\}]|\}(?!\}))*?)\))?\s*\}\}/g,function(m,l,k,g,b,c,d){var j=a.tmpl.tag[k],i,e,f;if(!j)throw"Unknown template tag: "+k;i=j._default||[];if(c&&!/\w$/.test(b)){b+=c;c=""}if(b){b=h(b);d=d?","+h(d)+")":c?")":"";e=c?b.indexOf(".")>-1?b+h(c):"("+b+").call($item"+d:b;f=c?e:"(typeof("+b+")==='function'?("+b+").call($item):("+b+"))"}else f=e=i.$1||"null";g=h(g);return"');"+j[l?"close":"open"].split("$notnull_1").join(b?"typeof("+b+")!=='undefined' && ("+b+")!=null":"true").split("$1a").join(f).split("$1").join(e).split("$2").join(g||i.$2||"")+"__.push('"})+"');}return __;")}function n(c,b){c._wrap=j(c,true,a.isArray(b)?b:[q.test(b)?b:a(b).html()]).join("")}function h(a){return a?a.replace(/\\'/g,"'").replace(/\\\\/g,"\\"):null}function s(b){var a=document.createElement("div");a.appendChild(b.cloneNode(true));return a.innerHTML}function m(o){var n="_"+c,k,j,l={},e,p,h;for(e=0,p=o.length;e<p;e++){if((k=o[e]).nodeType!==1)continue;j=k.getElementsByTagName("*");for(h=j.length-1;h>=0;h--)m(j[h]);m(k)}function m(j){var p,h=j,k,e,m;if(m=j.getAttribute(d)){while(h.parentNode&&(h=h.parentNode).nodeType===1&&!(p=h.getAttribute(d)));if(p!==m){h=h.parentNode?h.nodeType===11?0:h.getAttribute(d)||0:0;if(!(e=b[m])){e=f[m];e=g(e,b[h]||f[h]);e.key=++i;b[i]=e}c&&o(m)}j.removeAttribute(d)}else if(c&&(e=a.data(j,"tmplItem"))){o(e.key);b[e.key]=e;h=a.data(j.parentNode,"tmplItem");h=h?h.key:0}if(e){k=e;while(k&&k.key!=h){k.nodes.push(j);k=k.parent}delete e._ctnt;delete e._wrap;a.data(j,"tmplItem",e)}function o(a){a=a+n;e=l[a]=l[a]||g(e,b[e.parent.key+n]||e.parent)}}}function u(a,d,c,b){if(!a)return l.pop();l.push({_:a,tmpl:d,item:this,data:c,options:b})}function w(d,c,b){return a.tmpl(a.template(d),c,b,this)}function x(b,d){var c=b.options||{};c.wrapped=d;return a.tmpl(a.template(b.tmpl),b.data,c,b.item)}function v(d,c){var b=this._wrap;return a.map(a(a.isArray(b)?b.join(""):b).filter(d||"*"),function(a){return c?a.innerText||a.textContent:a.outerHTML||s(a)})}function t(){var b=this.nodes;a.tmpl(null,null,null,this).insertBefore(b[0]);a(b).remove()}})(jQuery);

/*
 * tmplPlus.js: for jQuery Templates Plugin 1.0.0pre
 * Additional templating features or support for more advanced/less common scenarios.
 * Requires jquery.tmpl.js
 * http://github.com/jquery/jquery-tmpl
 *
 * Copyright 2011, Software Freedom Conservancy, Inc.
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 */
(function (a) { var c = a.tmpl.complete, b = a.fn.domManip; a.tmpl.complete = function (d) { var b; c(d); for (b in d) { b = d[b]; b.addedTmplItems && a.inArray(b, b.addedTmplItems) === -1 && b.addedTmplItems.push(b) } for (b in d) { b = d[b]; b.rendered && b.rendered(b) } }; a.extend({ tmplCmd: function (f, b, c) { var e = [], d; function g(f, c) { for (var e = [], a, b, i = c.length, d, g = 0, h = f.length; g < h; ) { d = f[g++]; for (b = 0; b < i; ) { a = c[b++]; a.data === d && e.push(a) } } return e } b = a.isArray(b) ? b : [b]; switch (f) { case "find": return g(b, c); case "replace": b.reverse() } a.each(c ? g(b, c) : b, function (g, b) { coll = b.nodes; switch (f) { case "update": b.update(); break; case "remove": a(coll).remove(); c && c.splice(a.inArray(b, c), 1); break; case "replace": d = d ? a(coll).insertBefore(d)[0] : a(coll).appendTo(coll[0].parentNode)[0]; e.unshift(b) } }); return e } }); a.fn.extend({ domManip: function (c, i, f) { var e = c[1], g = c[0], d; if (c.length >= 2 && typeof e === "object" && !e.nodeType && !(e instanceof a)) { d = a.makeArray(arguments); d[0] = [a.tmpl(a.template(g), e, c[2], c[3])]; d[2] = function (b) { a.tmpl.afterManip(this, b, f) }; return b.apply(this, d) } return b.apply(this, arguments) } }) })(jQuery);

function objtostr(obj) {
	var s = " ";
	var objname = typeof (obj);
	if (objname == "object") {
		for (var i in obj) {
			if ((i != "indexOf") && (i != "remove") && (i != "removeById")) {
				if ( typeof (obj[i]) == "object") {
					s += "[" + i + "]=" + objtostr(obj[i]);
				} else {
					s += "    '" + i + "':" + objtostr(obj[i]);
				}
			}
		}
	} else {
		switch (objname) {
			case "number":
				s = obj + "\n";
				break;
			case "string":
				s = "'" + obj + "'\n";
				break;
			default:
				s = objname + ":" + obj;
		}
	}
	return s;
};

jQuery.comm = {
	asendmessage : function(url, Msg) {
		var html = $.ajax({
			url : url,
			async : false
		}).responseText;
		return html;
	},
	sendmessage : function(url, Msg, fcallback, ecallback) {
		$.ajax({
			url : url,
			type : 'post',
			// beforeSend : function(oXhr) {
			// 	oXhr.setRequestHeader('Connection', 'Keep-Alive');
			// },
			data : Msg,
			timeout : 120000,
			success : fcallback,
			error : ecallback
		});
	}
};

function ajsonp(uri, packet, onsuccess) {
	$.ajax({
		async : true,
		url : window.location.protocol + "//" + window.location.host + "/" + uri,
		type : 'GET',
		dataType : 'jsonp',
		jsonp : 'jsoncallback',
		data : packet,
		timeout : 5000,
		beforeSend : function() {
		},
		success : function(json) {
			onsuccess(json);
		},
		complete : function(XMLHttpRequest, textStatus) {
		},
		error : function(xhr) {
			$().toastmessage('showErrorToast', "请求出错(请检查相关度网络状况.)");
		}
	});
}

function setcookie(name, value, expTime) {
	var Days = expTime;
	var exp = new Date();
	exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
	document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString();
}

function setcookie1(_name, _value, _nSenconds) {
	var str = _name + "=" + escape(_value);
	if (_nSenconds > 0) {
		var date = new Date();
		var ms = _nSenconds * 24 * 60 * 60 * 1000;
		date.setTime(date.getTime() + ms);
		str += "; expires=" + date.toGMTString() + "; domain=" + window.location.host + "; path=/;";
	}
	document.cookie = str;
};

function getcookie(name) {
	var arr = document.cookie.match(new RegExp("(^| )" + name + "=([^;]*)(;|$)"));
	if (arr != null)
		return unescape(arr[2]);
	return null;

}

function delcookie(name) {
	var exp = new Date();
	exp.setTime(exp.getTime() - 1);
	var cval = getCookie(name);
	if (cval != null)
		document.cookie = name + "=" + cval + ";expires=" + exp.toGMTString();
}

function uuid() {
	var chars = '0123456789abcdef'.split('');
	var uuid = [], rnd = Math.random, r;
	uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-';
	uuid[14] = '4';
	for (var i = 0; i < 36; i++) {
		if (!uuid[i]) {
			r = 0 | rnd() * 16;
			uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r & 0xf];
		}
	}
	return uuid.join('');
}

function getfixstr(str,len){
    var strl1 = "00000000000000000000000"+str;
    return strl1.substr(strl1.length-len,len);
}


function getdatetime(delt) {
        var datex = new Date();
	var date = new Date(datex.getTime()+delt*1000);
	var now = "";
	now = date.getFullYear() + "-";
	now = now + (date.getMonth() + 1) + "-";
	now = now + date.getDate() + " ";
	now = now + date.getHours() + ":";
	now = now + date.getMinutes() + ":";
	now = now + date.getSeconds() + "";
	return now;
}

function getdatetime1(delt) {
	var date = new Date(delt*1000);
	var now = "";
	now = date.getFullYear() + "-";
	now = now + getfixstr((date.getMonth() + 1),2) + "-";
	now = now + getfixstr(date.getDate(),2) + " ";
	now = now + getfixstr(date.getHours(),2) + ":";
	now = now + getfixstr(date.getMinutes(),2) + ":";
	now = now + getfixstr(date.getSeconds(),2) + "";
	return now;
}

function getdate(delt) {
        var datex = new Date();
	var date = new Date(datex.getTime()+delt*1000);
	var now = "";
	now = date.getFullYear() + "-";
	now = now + getfixstr((date.getMonth() + 1),2) + "-";
	now = now + getfixstr(date.getDate(),2);
	return now;
}

function getdatem(delt) {
        var datex = new Date();
	var date = new Date(datex.getTime()+delt*1000);
	var now = "";
	now = date.getFullYear() + "-";
	now = now + getfixstr((date.getMonth() + 1),2) 
	return now;
}

function getdate1(timeticket) {
	var date = new Date(timeticket*1000);
	var now = "";
	now = date.getFullYear() + "-";
	now = now + (date.getMonth()+1) + "-";
	now = now + date.getDate();
	return now;
}

function getdatem1(timeticket) {
	var date = new Date(timeticket*1000);
	var now = "";
	now = date.getFullYear() + "-";
	now = now + (date.getMonth() +1)
	return now;
}

function gettime(delt) {
        var datex = new Date();
	var date = new Date(datex.getTime()+delt*1000);
	var now = "";
	now = now + date.getHours() + ":";
	now = now + date.getMinutes() + ":";
	now = now + date.getSeconds() + "";
	return now;
}

function gettimeticket(delt) {
	var nowticket = new Date();
	return (parseInt( nowticket.getTime() / 1000 )+delt);
}

function gettimeticketfromstr(str) {
        var dateArr = str.split("-");
	var nowticket = new Date(dateArr[0],dateArr[1]-1,dateArr[2],0,0,0);
	return parseInt( nowticket.getTime() / 1000 );
}

