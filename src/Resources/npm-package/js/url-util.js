class UrlUtil {
    static getParameterByName(name, url)
    {
        if (!url)
        {
            url = window.location.href;
        }

        name = name.replace(/[\[\]]/g, "\\$&");

        let regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
            results = regex.exec(url);

        if (!results)
        {
            return null;
        }

        if (!results[2])
        {
            return '';
        }

        return decodeURIComponent(results[2].replace(/\+/g, " "));
    }

    static addParameterToUri(uri, key, value)
    {
        if (!uri)
        {
            uri = window.location.href;
        }

        let re = new RegExp("([?&])" + key + "=.*?(&|#|$)(.*)", "gi"),
            hash;

        if (re.test(uri))
        {
            if (typeof value !== 'undefined' && value !== null)
            {
                return uri.replace(re, '$1' + key + "=" + value + '$2$3');
            }
            else
            {
                hash = uri.split('#');
                uri = hash[0].replace(re, '$1$3').replace(/(&|\?)$/, '');

                if (typeof hash[1] !== 'undefined' && hash[1] !== null)
                {
                    uri += '#' + hash[1];
                }

                return uri;
            }
        }
        else
        {
            if (typeof value !== 'undefined' && value !== null)
            {
                let separator = uri.indexOf('?') !== -1 ? '&' : '?';
                hash = uri.split('#');
                uri = hash[0] + separator + key + '=' + value;

                if (typeof hash[1] !== 'undefined' && hash[1] !== null)
                {
                    uri += '#' + hash[1];
                }

                return uri;
            }
            else
            {
                return uri;
            }
        }
    }

    static addParametersToUri(uri, parameters)
    {
        if(parameters instanceof FormData) {
            for(let entry of parameters.entries()) {
                if(parameters.has(entry[0])) {
                    uri = this.addParameterToUri(uri, entry[0], entry[1]);
                }
            }
        } else {
            for (let key in parameters)
            {
                if (parameters.hasOwnProperty(key))
                {
                    uri = this.addParameterToUri(uri, key, parameters[key]);
                }
            }
        }

        return uri;
    }

    static removeParameterFromUri(uri, parameter)
    {
        //prefer to use l.search if you have a location/link object
        let uriparts = uri.split('?');

        if (uriparts.length >= 2)
        {

            let prefix = encodeURIComponent(parameter) + '=';
            let pars = uriparts[1].split(/[&;]/g);

            //reverse iteration as may be destructive
            for (let i = pars.length; i-- > 0;)
            {
                //idiom for string.startsWith
                if (pars[i].lastIndexOf(prefix, 0) !== -1)
                {
                    pars.splice(i, 1);
                }
            }

            uri = uriparts[0] + '?' + pars.join('&');
            return uri;
        }
        else
        {
            return uri;
        }
    }

    static removeParametersFromUri(uri, parameters)
    {
        for (let key in parameters)
        {
            if (parameters.hasOwnProperty(key))
            {
                uri = this.removeParameterFromUri(uri, key);
            }
        }

        return uri;
    }

    static replaceParameterInUri(uri, key, value)
    {
        this.addParameterToUri(this.removeParameterFromUri(uri, key), key, value);
    }

    static parseQueryString(queryString) {
        return JSON.parse('{"' + decodeURI(queryString).replace(/"/g, '\\"').replace(/&/g, '","').replace(/=/g,'":"') + '"}')
    }

    static buildQueryString(parameters) {
        let query = '';

        for (let key in parameters) {
            if ('' !== query) {
                query += '&';
            }

            query += key + '=' + parameters[key];
        }

        return query;
    }
}

export default UrlUtil
