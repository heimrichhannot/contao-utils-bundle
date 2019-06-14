import GeneralUtil from './general-util';
import UrlUtil from './url-util';

class AjaxUtil {
  static get(url, data, config) {
    config = AjaxUtil.modifyConfig(config);

    let request = AjaxUtil.initializeRequest('GET', UrlUtil.addParametersToUri(url, data), config),
        submitData = {
          config: config,
          action: url,
          data: data,
        };

    AjaxUtil.doAjaxSubmit(request, submitData);
  }

  static post(url, data, config) {
    config = AjaxUtil.modifyConfig(config);

    let request = AjaxUtil.initializeRequest('POST', url, config),
        submitData = {
          config: config,
          action: url,
          data: data,
          body: UrlUtil.buildQueryString(data),
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

    if ('undefined' === typeof submitData.body) {
      request.send();
    } else {
      request.send(submitData.body);
    }
  }

  static initializeRequest(method, url, config) {
    let request = new XMLHttpRequest();

    request.open(method, url, true);
    request = AjaxUtil.setRequestHeaders(request, config);

    return request;
  }

  static setRequestHeaders(request, config) {
    if ('undefined' !== typeof config.headers) {
      for (let key in config.headers) {
        request.setRequestHeader(key, config.headers[key]);
      }
    }

    return request;
  }

  static modifyConfig(config) {
    if ('undefined' === typeof config.headers) {
      config.headers = {'X-Requested-With': 'XMLHttpRequest'};
    }

    return config;
  }
}

export default AjaxUtil;