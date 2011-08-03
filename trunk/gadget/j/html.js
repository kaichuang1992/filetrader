/* the uri of the actual OAuth endpoint we want to reach,
 * should probably be specified in a different way... */
var proxy_for = "https://frkosp.wind.surfnet.nl/fts/index.php";
/* the key and secret should really not be here! */
var proxy_use_key = "12345";
var proxy_use_secret = "54321";

/* the location of the proxy */
var proxy_endpoint = "proxy.php";

function callService (action, params, method, callback) {
        var xhr, formData, i, getParams, callUrl, j;
        xhr = new XMLHttpRequest();
        xhr.addEventListener("load", function () {
            callback(xhr.responseText);
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
            j = 0;
            for(i in params) {
                getParams[j] = i + "=" + params[i];
                j += 1;
            }
            getParams = getParams.join("&");
            if(this.apiUrl.indexOf("?") === -1) {
               callUrl = this.apiUrl + "?" + getParams;
            } else {
               callUrl = this.apiUrl + "&" + getParams;
            }
            alert(callUrl);
            xhr.open(method, callUrl, true);
            xhr.send();
        } else {
            alert('unsupported method');
        }
    }
}

