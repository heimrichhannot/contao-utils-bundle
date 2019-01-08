class ArrayUtil {
    static removeFromArray(value, array) {
        for (var i = 0; i < array.length; i++) {
            if (JSON.stringify(value) == JSON.stringify(array[i])) {
                array.splice(i, 1);
            }
        }
        return array;
    }
}

class DomUtil {
    static getTextWithoutChildren(element, notrim) {
        let result = element.clone();
        result.children().remove();

        if (typeof notrim !== 'undefined' && notrim === true) {
            return result.text();
        } else {
            return result.text().trim();
        }
    }

    static scrollTo(element, offset = 0, delay = 0, force = false) {
        let rect = element.getBoundingClientRect();
        let scrollPosition = (rect.top + window.pageYOffset - offset);
        setTimeout(() => {
            if (!this.elementInViewport(element) || force === true)
                window.scrollTo({
                    'top': scrollPosition,
                    'behavior': 'smooth',
                });
        }, delay);
    }

    static elementInViewport(el) {
        let top = el.offsetTop;
        let left = el.offsetLeft;
        let width = el.offsetWidth;
        let height = el.offsetHeight;

        while (el.offsetParent) {
            el = el.offsetParent;
            top += el.offsetTop;
            left += el.offsetLeft;
        }

        return (
            top < (window.pageYOffset + window.innerHeight) &&
            left < (window.pageXOffset + window.innerWidth) &&
            (top + height) > window.pageYOffset &&
            (left + width) > window.pageXOffset
        );
    }

    static getAllParentNodes(node, callback) {
        var parents = [];

        while (node) {
            parents.unshift(node);
            node = node.parentNode;
        }

        ArrayUtil.removeFromArray(document, parents);

        return parents;
    }
}

class EventUtil {
    static addDynamicEventListener(eventName, selector, callback, disableBubbling) {
        document.addEventListener(eventName, function(e) {
            var parents = (GeneralUtil.isTruthy(disableBubbling) ? [e.target] : DomUtil.getAllParentNodes(e.target));

            if (!Array.isArray(parents))
            {
                return;
            }

            parents.reverse().forEach(function(item) {
                if (item && item.matches(selector)) {
                    callback(item, e);
                }
            });
        });
    }
}

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
        for (let key in parameters)
        {
            if (parameters.hasOwnProperty(key))
            {
                uri = this.addParameterToUri(uri, key, parameters[key]);
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
}

class GeneralUtil {
    static isTruthy(value) {
        return typeof value !== 'undefined' && value !== null;
    }

    static call(func) {
        if (typeof func === 'function') {
            func.apply(this, Array.prototype.slice.call(arguments, 1));
        }
    }
}

let utils = {
    array: ArrayUtil,
    dom: DomUtil,
    event: EventUtil,
    url: UrlUtil,
    util: GeneralUtil
};

if (typeof module === 'object')
{
    module.exports = utils;
}
else
{
    window.utilsBundle = utils;
}
