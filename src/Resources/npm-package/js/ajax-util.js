import GeneralUtil from './general-util';
import UrlUtil from './url-util';

class AjaxUtil {
    static get(url, data, config) {
        config = AjaxUtil.setDefaults(config);

        let request = AjaxUtil.initializeRequest('GET', UrlUtil.addParametersToUri(url, data), config),
            submitData = {
                config: config,
                action: url,
                data: data
            };

        AjaxUtil.doAjaxSubmit(request, submitData);
    }

    static post(url, data, config) {
        config = AjaxUtil.setDefaults(config);

        let request = AjaxUtil.initializeRequest('POST', url, config),
            submitData = {
                config: config,
                action: url,
                data: data
            };

        AjaxUtil.doAjaxSubmit(request, submitData);
    }

    static doAjaxSubmit(request, submitData) {
        let config = submitData.config;

        request.onload = function() {
            if (request.status >= 200 && request.status < 400) {
                GeneralUtil.call(config.onSuccess, request);
            } else {
                GeneralUtil.call(config.onError, request);
            }

            GeneralUtil.call(config.afterSubmit, submitData.action, submitData.data, config);
        };

        GeneralUtil.call(config.beforeSubmit, submitData.action, submitData.data, config);

        if ('undefined' === typeof submitData.data) {
            request.send();
        } else {
            submitData.data = AjaxUtil.prepareDataForSend(submitData.data);

            request.send(submitData.data);
        }
    }

    static prepareDataForSend(data) {
        if (!(data instanceof FormData))
        {
            let formData = new FormData();

            Object.keys(data).forEach(field => {
                formData.append(field, data[field]);
            });

            return formData;
        }

        return data;
    }

    static initializeRequest(method, url, config) {
        let request = new XMLHttpRequest();

        request.open(method, url, true);
        request = AjaxUtil.setRequestHeaders(request, config);

        if (config.hasOwnProperty('responseType'))
        {
            request.responseType = config.responseType;
        }
        return request;
    }

    static setRequestHeaders(request, config) {
        if (config.hasOwnProperty('headers')) {
            Object.keys(config.headers).forEach(key => {
                request.setRequestHeader(key, config.headers[key]);
            });
        }
        return request;
    }

    static setDefaults(config) {
        if (!config.hasOwnProperty('headers')) {
            config.headers = {'X-Requested-With': 'XMLHttpRequest'};
        }
        return config;
    }
}

export default AjaxUtil;