/* the uri of the actual OAuth endpoint we want to reach,
 * should probably be specified in a different way... */
var proxy_for = "https://frkosp.wind.surfnet.nl/fts/index.php";
/* the key and secret should really not be here! */
var proxy_use_key = "demo";
var proxy_use_secret = "a1bf8348016c52f81498cd576d55a932";

/* the location of the proxy */
var proxy_endpoint = "proxy.php";

function serverCall (action, params, method, callback) {
        var xhr, formData, i, getParams, callUrl, j;
        xhr = new XMLHttpRequest();
        xhr.addEventListener("load", function () {
		var x = { 'data' : JSON.parse(xhr.responseText) };
		callback(x);
        }, false);
        if (method === 'POST') {
            callUrl = this.proxyUrl + '?action=' + action;
            formData = new FormData();
            for (i = 0; i < params.length; i += 1) {
                formData.append(i, params[i]);
            }
            xhr.open(method, callUrl, true);
            xhr.send(formData);
        } else if (method === 'GET') {
	    getParams = [];
            getParams[0] = 'proxy_for='+proxy_for;
	    getParams[1] = 'proxy_use_key='+proxy_use_key;
	    getParams[2] = 'proxy_use_secret='+proxy_use_secret;
	    getParams[3] = 'action='+action;
            j = 4;
            for(i in params) {
                getParams[j] = i + "=" + params[i];
                j += 1;
            }
            getParams = getParams.join("&");
            if(proxy_endpoint.indexOf("?") === -1) {
               callUrl = proxy_endpoint + "?" + getParams;
            } else {
               callUrl = proxy_endpoint + "&" + getParams;
            }
//            alert(callUrl);
            xhr.open(method, callUrl, true);
            xhr.send();
        } else {
            alert('unsupported method');
        }
}


