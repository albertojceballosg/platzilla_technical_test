/**
*
*  UTF-8 data encode / decode
*  http://www.webtoolkit.info/
*
**/

var Utf8 = {
    // public method for url encoding
    encode : function (string) {

        string = string.replace(/\r\n/g,"\n");
        var utftext = "";
        for (var n = 0; n < string.length; n++) {
            var c = string.charCodeAt(n);
            if (c < 128) {
                utftext += String.fromCharCode(c);
            }
            else if((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            }
            else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }
        }
        return utftext;
    },

    // public method for url decoding
    decode : function (utftext) {

        var string = utftext;
        if (string.indexOf("&aacute;") !=- 1) {
            var string = string.replace("&aacute;","á");
        }
        if (string.indexOf("&eacute;") !=- 1) {
            var string = string.replace("&eacute;","é");
        }
        if (string.indexOf("&iacute;") !=- 1) {
            var string = string.replace("&iacute;","í");
        }
        if (string.indexOf("&oacute;") !=- 1) {
            var string = string.replace("&oacute;","ó");
        }
        if (string.indexOf("&uacute;") !=- 1) {
            var string = string.replace("&uacute;","ú");
        }
        if (string.indexOf("&ntilde;") !=- 1) {
            var string = string.replace("&ntilde;","ñ");
        }
        return string;
    }
}

