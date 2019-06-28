/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/public/js/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./node_modules/@hundh/contao-utils-bundle/js/contao-utils-bundle.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./node_modules/@hundh/contao-utils-bundle/js/ajax-util.js":
/*!*****************************************************************!*\
  !*** ./node_modules/@hundh/contao-utils-bundle/js/ajax-util.js ***!
  \*****************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _general_util__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./general-util */ "./node_modules/@hundh/contao-utils-bundle/js/general-util.js");
/* harmony import */ var _url_util__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./url-util */ "./node_modules/@hundh/contao-utils-bundle/js/url-util.js");
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }




var AjaxUtil =
/*#__PURE__*/
function () {
  function AjaxUtil() {
    _classCallCheck(this, AjaxUtil);
  }

  _createClass(AjaxUtil, null, [{
    key: "get",
    value: function get(url, data, config) {
      config = AjaxUtil.setDefaults(config);
      var request = AjaxUtil.initializeRequest('GET', _url_util__WEBPACK_IMPORTED_MODULE_1__["default"].addParametersToUri(url, data), config),
          submitData = {
        config: config,
        action: url,
        data: data
      };
      AjaxUtil.doAjaxSubmit(request, submitData);
    }
  }, {
    key: "post",
    value: function post(url, data, config) {
      config = AjaxUtil.setDefaults(config);
      var request = AjaxUtil.initializeRequest('POST', url, config),
          submitData = {
        config: config,
        action: url,
        data: data
      };
      AjaxUtil.doAjaxSubmit(request, submitData);
    }
  }, {
    key: "doAjaxSubmit",
    value: function doAjaxSubmit(request, submitData) {
      var config = submitData.config;

      request.onload = function () {
        if (request.status >= 200 && request.status < 400) {
          _general_util__WEBPACK_IMPORTED_MODULE_0__["default"].call(config.onSuccess, request);
        } else {
          _general_util__WEBPACK_IMPORTED_MODULE_0__["default"].call(config.onError, request);
        }

        _general_util__WEBPACK_IMPORTED_MODULE_0__["default"].call(config.afterSubmit, submitData.action, submitData.data, config);
      };

      _general_util__WEBPACK_IMPORTED_MODULE_0__["default"].call(config.beforeSubmit, submitData.action, submitData.data, config);

      if ('undefined' === typeof submitData.data) {
        request.send();
      } else {
        submitData.data = AjaxUtil.prepareDataForSend(submitData.data);
        request.send(submitData.data);
      }
    }
  }, {
    key: "prepareDataForSend",
    value: function prepareDataForSend(data) {
      if ('object' === _typeof(data)) {
        var formData = new FormData();
        Object.keys(data).forEach(function (field) {
          formData.append(field, data[field]);
        });
        return formData;
      }

      return data;
    }
  }, {
    key: "initializeRequest",
    value: function initializeRequest(method, url, config) {
      var request = new XMLHttpRequest();
      request.open(method, url, true);
      request = AjaxUtil.setRequestHeaders(request, config);
      return request;
    }
  }, {
    key: "setRequestHeaders",
    value: function setRequestHeaders(request, config) {
      if ('undefined' !== typeof config.headers) {
        Object.keys(config.headers).forEach(function (key) {
          request.setRequestHeader(key, config.headers[key]);
        });
      }

      return request;
    }
  }, {
    key: "setDefaults",
    value: function setDefaults(config) {
      if ('undefined' === typeof config.headers) {
        config.headers = {
          'X-Requested-With': 'XMLHttpRequest'
        };
      }

      return config;
    }
  }]);

  return AjaxUtil;
}();

/* harmony default export */ __webpack_exports__["default"] = (AjaxUtil);

/***/ }),

/***/ "./node_modules/@hundh/contao-utils-bundle/js/array-util.js":
/*!******************************************************************!*\
  !*** ./node_modules/@hundh/contao-utils-bundle/js/array-util.js ***!
  \******************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

var ArrayUtil =
/*#__PURE__*/
function () {
  function ArrayUtil() {
    _classCallCheck(this, ArrayUtil);
  }

  _createClass(ArrayUtil, null, [{
    key: "removeFromArray",
    value: function removeFromArray(value, array) {
      for (var i = 0; i < array.length; i++) {
        if (JSON.stringify(value) == JSON.stringify(array[i])) {
          array.splice(i, 1);
        }
      }

      return array;
    }
  }]);

  return ArrayUtil;
}();

/* harmony default export */ __webpack_exports__["default"] = (ArrayUtil);

/***/ }),

/***/ "./node_modules/@hundh/contao-utils-bundle/js/contao-utils-bundle.js":
/*!***************************************************************************!*\
  !*** ./node_modules/@hundh/contao-utils-bundle/js/contao-utils-bundle.js ***!
  \***************************************************************************/
/*! exports provided: utilsBundle, AjaxUtil, ArrayUtil, DomUtil, EventUtil, GeneralUtil, UrlUtil */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "utilsBundle", function() { return utilsBundle; });
/* harmony import */ var _polyfills__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./polyfills */ "./node_modules/@hundh/contao-utils-bundle/js/polyfills.js");
/* harmony import */ var _array_util__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./array-util */ "./node_modules/@hundh/contao-utils-bundle/js/array-util.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "ArrayUtil", function() { return _array_util__WEBPACK_IMPORTED_MODULE_1__["default"]; });

/* harmony import */ var _dom_util__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./dom-util */ "./node_modules/@hundh/contao-utils-bundle/js/dom-util.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "DomUtil", function() { return _dom_util__WEBPACK_IMPORTED_MODULE_2__["default"]; });

/* harmony import */ var _event_util__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./event-util */ "./node_modules/@hundh/contao-utils-bundle/js/event-util.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "EventUtil", function() { return _event_util__WEBPACK_IMPORTED_MODULE_3__["default"]; });

/* harmony import */ var _url_util__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./url-util */ "./node_modules/@hundh/contao-utils-bundle/js/url-util.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "UrlUtil", function() { return _url_util__WEBPACK_IMPORTED_MODULE_4__["default"]; });

/* harmony import */ var _general_util__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./general-util */ "./node_modules/@hundh/contao-utils-bundle/js/general-util.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "GeneralUtil", function() { return _general_util__WEBPACK_IMPORTED_MODULE_5__["default"]; });

/* harmony import */ var _ajax_util__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./ajax-util */ "./node_modules/@hundh/contao-utils-bundle/js/ajax-util.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "AjaxUtil", function() { return _ajax_util__WEBPACK_IMPORTED_MODULE_6__["default"]; });








var utilsBundle = {
  ajax: _ajax_util__WEBPACK_IMPORTED_MODULE_6__["default"],
  array: _array_util__WEBPACK_IMPORTED_MODULE_1__["default"],
  dom: _dom_util__WEBPACK_IMPORTED_MODULE_2__["default"],
  event: _event_util__WEBPACK_IMPORTED_MODULE_3__["default"],
  url: _url_util__WEBPACK_IMPORTED_MODULE_4__["default"],
  util: _general_util__WEBPACK_IMPORTED_MODULE_5__["default"]
};
window.utilsBundle = utilsBundle;


/***/ }),

/***/ "./node_modules/@hundh/contao-utils-bundle/js/dom-util.js":
/*!****************************************************************!*\
  !*** ./node_modules/@hundh/contao-utils-bundle/js/dom-util.js ***!
  \****************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _array_util__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./array-util */ "./node_modules/@hundh/contao-utils-bundle/js/array-util.js");
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }



var DomUtil =
/*#__PURE__*/
function () {
  function DomUtil() {
    _classCallCheck(this, DomUtil);
  }

  _createClass(DomUtil, null, [{
    key: "getTextWithoutChildren",
    value: function getTextWithoutChildren(element, notrim) {
      var result = element.clone();
      result.children().remove();

      if (typeof notrim !== 'undefined' && notrim === true) {
        return result.text();
      } else {
        return result.text().trim();
      }
    }
  }, {
    key: "scrollTo",
    value: function scrollTo(element) {
      var _this = this;

      var offset = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
      var delay = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 0;
      var force = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : false;
      var rect = element.getBoundingClientRect();
      var scrollPosition = rect.top + window.pageYOffset - offset;
      setTimeout(function () {
        if (!_this.elementInViewport(element) || force === true) {
          var isSmoothScrollSupported = 'scrollBehavior' in document.documentElement.style;

          if (isSmoothScrollSupported) {
            window.scrollTo({
              'top': scrollPosition,
              'behavior': 'smooth'
            });
          } else {
            window.scrollTo(0, scrollPosition);
          }
        }
      }, delay);
    }
  }, {
    key: "elementInViewport",
    value: function elementInViewport(el) {
      var top = el.offsetTop;
      var left = el.offsetLeft;
      var width = el.offsetWidth;
      var height = el.offsetHeight;

      while (el.offsetParent) {
        el = el.offsetParent;
        top += el.offsetTop;
        left += el.offsetLeft;
      }

      return top < window.pageYOffset + window.innerHeight && left < window.pageXOffset + window.innerWidth && top + height > window.pageYOffset && left + width > window.pageXOffset;
    }
  }, {
    key: "getAllParentNodes",
    value: function getAllParentNodes(node) {
      var parents = [];

      while (node) {
        parents.unshift(node);
        node = node.parentNode;
      }

      for (var i = 0; i < parents.length; i++) {
        if (parents[i] === document) {
          parents.splice(i, 1);
        }
      }

      return parents;
    }
  }]);

  return DomUtil;
}();

/* harmony default export */ __webpack_exports__["default"] = (DomUtil);

/***/ }),

/***/ "./node_modules/@hundh/contao-utils-bundle/js/event-util.js":
/*!******************************************************************!*\
  !*** ./node_modules/@hundh/contao-utils-bundle/js/event-util.js ***!
  \******************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _dom_util__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./dom-util */ "./node_modules/@hundh/contao-utils-bundle/js/dom-util.js");
/* harmony import */ var _general_util__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./general-util */ "./node_modules/@hundh/contao-utils-bundle/js/general-util.js");
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }




var EventUtil =
/*#__PURE__*/
function () {
  function EventUtil() {
    _classCallCheck(this, EventUtil);
  }

  _createClass(EventUtil, null, [{
    key: "addDynamicEventListener",
    value: function addDynamicEventListener(eventName, selector, callback, scope, disableBubbling) {
      if (typeof scope === 'undefined') {
        scope = document;
      }

      scope.addEventListener(eventName, function (e) {
        var parents;

        if (_general_util__WEBPACK_IMPORTED_MODULE_1__["default"].isTruthy(disableBubbling)) {
          parents = [e.target];
        } else if (e.target !== document) {
          parents = _dom_util__WEBPACK_IMPORTED_MODULE_0__["default"].getAllParentNodes(e.target);
        } // for instance window load/resize event


        if (!Array.isArray(parents)) {
          document.querySelectorAll(selector).forEach(function (item) {
            callback(item, e);
          });
          return;
        }

        parents.reverse().forEach(function (item) {
          if (item && item.matches(selector)) {
            callback(item, e);
          }
        });
      });
    }
  }]);

  return EventUtil;
}();

/* harmony default export */ __webpack_exports__["default"] = (EventUtil);

/***/ }),

/***/ "./node_modules/@hundh/contao-utils-bundle/js/general-util.js":
/*!********************************************************************!*\
  !*** ./node_modules/@hundh/contao-utils-bundle/js/general-util.js ***!
  \********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

var GeneralUtil =
/*#__PURE__*/
function () {
  function GeneralUtil() {
    _classCallCheck(this, GeneralUtil);
  }

  _createClass(GeneralUtil, null, [{
    key: "isTruthy",
    value: function isTruthy(value) {
      return typeof value !== 'undefined' && value !== null;
    }
  }, {
    key: "call",
    value: function call(func) {
      if (typeof func === 'function') {
        func.apply(this, Array.prototype.slice.call(arguments, 1));
      }
    }
  }]);

  return GeneralUtil;
}();

/* harmony default export */ __webpack_exports__["default"] = (GeneralUtil);

/***/ }),

/***/ "./node_modules/@hundh/contao-utils-bundle/js/polyfills.js":
/*!*****************************************************************!*\
  !*** ./node_modules/@hundh/contao-utils-bundle/js/polyfills.js ***!
  \*****************************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var nodelist_foreach_polyfill__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! nodelist-foreach-polyfill */ "./node_modules/nodelist-foreach-polyfill/index.js");
/* harmony import */ var nodelist_foreach_polyfill__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(nodelist_foreach_polyfill__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var element_closest__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! element-closest */ "./node_modules/element-closest/index.mjs");
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

// foreach on nodelists
 // closest() and matches polyfill


Object(element_closest__WEBPACK_IMPORTED_MODULE_1__["default"])(window); // replaceWith

function ReplaceWith(Ele) {
  'use-strict'; // For safari, and IE > 10

  var parent = this.parentNode,
      i = arguments.length,
      firstIsNode = +(parent && _typeof(Ele) === 'object');
  if (!parent) return;

  while (i-- > firstIsNode) {
    if (parent && _typeof(arguments[i]) !== 'object') {
      arguments[i] = document.createTextNode(arguments[i]);
    }

    if (!parent && arguments[i].parentNode) {
      arguments[i].parentNode.removeChild(arguments[i]);
      continue;
    }

    parent.insertBefore(this.previousSibling, arguments[i]);
  }

  if (firstIsNode) parent.replaceChild(Ele, this);
}

if (!Element.prototype.replaceWith) Element.prototype.replaceWith = ReplaceWith;
if (!CharacterData.prototype.replaceWith) CharacterData.prototype.replaceWith = ReplaceWith;
if (!DocumentType.prototype.replaceWith) CharacterData.prototype.replaceWith = ReplaceWith;

/***/ }),

/***/ "./node_modules/@hundh/contao-utils-bundle/js/url-util.js":
/*!****************************************************************!*\
  !*** ./node_modules/@hundh/contao-utils-bundle/js/url-util.js ***!
  \****************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

var UrlUtil =
/*#__PURE__*/
function () {
  function UrlUtil() {
    _classCallCheck(this, UrlUtil);
  }

  _createClass(UrlUtil, null, [{
    key: "getParameterByName",
    value: function getParameterByName(name, url) {
      if (!url) {
        url = window.location.href;
      }

      name = name.replace(/[\[\]]/g, "\\$&");
      var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
          results = regex.exec(url);

      if (!results) {
        return null;
      }

      if (!results[2]) {
        return '';
      }

      return decodeURIComponent(results[2].replace(/\+/g, " "));
    }
  }, {
    key: "addParameterToUri",
    value: function addParameterToUri(uri, key, value) {
      if (!uri) {
        uri = window.location.href;
      }

      var re = new RegExp("([?&])" + key + "=.*?(&|#|$)(.*)", "gi"),
          hash;

      if (re.test(uri)) {
        if (typeof value !== 'undefined' && value !== null) {
          return uri.replace(re, '$1' + key + "=" + value + '$2$3');
        } else {
          hash = uri.split('#');
          uri = hash[0].replace(re, '$1$3').replace(/(&|\?)$/, '');

          if (typeof hash[1] !== 'undefined' && hash[1] !== null) {
            uri += '#' + hash[1];
          }

          return uri;
        }
      } else {
        if (typeof value !== 'undefined' && value !== null) {
          var separator = uri.indexOf('?') !== -1 ? '&' : '?';
          hash = uri.split('#');
          uri = hash[0] + separator + key + '=' + value;

          if (typeof hash[1] !== 'undefined' && hash[1] !== null) {
            uri += '#' + hash[1];
          }

          return uri;
        } else {
          return uri;
        }
      }
    }
  }, {
    key: "addParametersToUri",
    value: function addParametersToUri(uri, parameters) {
      for (var key in parameters) {
        if (parameters.hasOwnProperty(key)) {
          uri = this.addParameterToUri(uri, key, parameters[key]);
        }
      }

      return uri;
    }
  }, {
    key: "removeParameterFromUri",
    value: function removeParameterFromUri(uri, parameter) {
      //prefer to use l.search if you have a location/link object
      var uriparts = uri.split('?');

      if (uriparts.length >= 2) {
        var prefix = encodeURIComponent(parameter) + '=';
        var pars = uriparts[1].split(/[&;]/g); //reverse iteration as may be destructive

        for (var i = pars.length; i-- > 0;) {
          //idiom for string.startsWith
          if (pars[i].lastIndexOf(prefix, 0) !== -1) {
            pars.splice(i, 1);
          }
        }

        uri = uriparts[0] + '?' + pars.join('&');
        return uri;
      } else {
        return uri;
      }
    }
  }, {
    key: "removeParametersFromUri",
    value: function removeParametersFromUri(uri, parameters) {
      for (var key in parameters) {
        if (parameters.hasOwnProperty(key)) {
          uri = this.removeParameterFromUri(uri, key);
        }
      }

      return uri;
    }
  }, {
    key: "replaceParameterInUri",
    value: function replaceParameterInUri(uri, key, value) {
      this.addParameterToUri(this.removeParameterFromUri(uri, key), key, value);
    }
  }, {
    key: "parseQueryString",
    value: function parseQueryString(queryString) {
      return JSON.parse('{"' + decodeURI(queryString).replace(/"/g, '\\"').replace(/&/g, '","').replace(/=/g, '":"') + '"}');
    }
  }, {
    key: "buildQueryString",
    value: function buildQueryString(parameters) {
      var query = '';

      for (var key in parameters) {
        if ('' !== query) {
          query += '&';
        }

        query += key + '=' + parameters[key];
      }

      return query;
    }
  }]);

  return UrlUtil;
}();

/* harmony default export */ __webpack_exports__["default"] = (UrlUtil);

/***/ }),

/***/ "./node_modules/element-closest/index.mjs":
/*!************************************************!*\
  !*** ./node_modules/element-closest/index.mjs ***!
  \************************************************/
/*! exports provided: default */
/***/ (function(__webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
function polyfill(window) {
  const ElementPrototype = window.Element.prototype;

  if (typeof ElementPrototype.matches !== 'function') {
    ElementPrototype.matches = ElementPrototype.msMatchesSelector || ElementPrototype.mozMatchesSelector || ElementPrototype.webkitMatchesSelector || function matches(selector) {
      let element = this;
      const elements = (element.document || element.ownerDocument).querySelectorAll(selector);
      let index = 0;

      while (elements[index] && elements[index] !== element) {
        ++index;
      }

      return Boolean(elements[index]);
    };
  }

  if (typeof ElementPrototype.closest !== 'function') {
    ElementPrototype.closest = function closest(selector) {
      let element = this;

      while (element && element.nodeType === 1) {
        if (element.matches(selector)) {
          return element;
        }

        element = element.parentNode;
      }

      return null;
    };
  }
}

/* harmony default export */ __webpack_exports__["default"] = (polyfill);
//# sourceMappingURL=index.mjs.map


/***/ }),

/***/ "./node_modules/nodelist-foreach-polyfill/index.js":
/*!*********************************************************!*\
  !*** ./node_modules/nodelist-foreach-polyfill/index.js ***!
  \*********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

if (window.NodeList && !NodeList.prototype.forEach) {
    NodeList.prototype.forEach = function (callback, thisArg) {
        thisArg = thisArg || window;
        for (var i = 0; i < this.length; i++) {
            callback.call(thisArg, this[i], i, this);
        }
    };
}


/***/ })

/******/ });
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vd2VicGFjay9ib290c3RyYXAiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL0BodW5kaC9jb250YW8tdXRpbHMtYnVuZGxlL2pzL2FqYXgtdXRpbC5qcyIsIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvQGh1bmRoL2NvbnRhby11dGlscy1idW5kbGUvanMvYXJyYXktdXRpbC5qcyIsIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvQGh1bmRoL2NvbnRhby11dGlscy1idW5kbGUvanMvY29udGFvLXV0aWxzLWJ1bmRsZS5qcyIsIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvQGh1bmRoL2NvbnRhby11dGlscy1idW5kbGUvanMvZG9tLXV0aWwuanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL0BodW5kaC9jb250YW8tdXRpbHMtYnVuZGxlL2pzL2V2ZW50LXV0aWwuanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL0BodW5kaC9jb250YW8tdXRpbHMtYnVuZGxlL2pzL2dlbmVyYWwtdXRpbC5qcyIsIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvQGh1bmRoL2NvbnRhby11dGlscy1idW5kbGUvanMvcG9seWZpbGxzLmpzIiwid2VicGFjazovLy8uL25vZGVfbW9kdWxlcy9AaHVuZGgvY29udGFvLXV0aWxzLWJ1bmRsZS9qcy91cmwtdXRpbC5qcyIsIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvZWxlbWVudC1jbG9zZXN0L2luZGV4Lm1qcyIsIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvbm9kZWxpc3QtZm9yZWFjaC1wb2x5ZmlsbC9pbmRleC5qcyJdLCJuYW1lcyI6WyJBamF4VXRpbCIsInVybCIsImRhdGEiLCJjb25maWciLCJzZXREZWZhdWx0cyIsInJlcXVlc3QiLCJpbml0aWFsaXplUmVxdWVzdCIsIlVybFV0aWwiLCJhZGRQYXJhbWV0ZXJzVG9VcmkiLCJzdWJtaXREYXRhIiwiYWN0aW9uIiwiZG9BamF4U3VibWl0Iiwib25sb2FkIiwic3RhdHVzIiwiR2VuZXJhbFV0aWwiLCJjYWxsIiwib25TdWNjZXNzIiwib25FcnJvciIsImFmdGVyU3VibWl0IiwiYmVmb3JlU3VibWl0Iiwic2VuZCIsInByZXBhcmVEYXRhRm9yU2VuZCIsImZvcm1EYXRhIiwiRm9ybURhdGEiLCJPYmplY3QiLCJrZXlzIiwiZm9yRWFjaCIsImZpZWxkIiwiYXBwZW5kIiwibWV0aG9kIiwiWE1MSHR0cFJlcXVlc3QiLCJvcGVuIiwic2V0UmVxdWVzdEhlYWRlcnMiLCJoZWFkZXJzIiwia2V5Iiwic2V0UmVxdWVzdEhlYWRlciIsIkFycmF5VXRpbCIsInZhbHVlIiwiYXJyYXkiLCJpIiwibGVuZ3RoIiwiSlNPTiIsInN0cmluZ2lmeSIsInNwbGljZSIsInV0aWxzQnVuZGxlIiwiYWpheCIsImRvbSIsIkRvbVV0aWwiLCJldmVudCIsIkV2ZW50VXRpbCIsInV0aWwiLCJ3aW5kb3ciLCJlbGVtZW50Iiwibm90cmltIiwicmVzdWx0IiwiY2xvbmUiLCJjaGlsZHJlbiIsInJlbW92ZSIsInRleHQiLCJ0cmltIiwib2Zmc2V0IiwiZGVsYXkiLCJmb3JjZSIsInJlY3QiLCJnZXRCb3VuZGluZ0NsaWVudFJlY3QiLCJzY3JvbGxQb3NpdGlvbiIsInRvcCIsInBhZ2VZT2Zmc2V0Iiwic2V0VGltZW91dCIsImVsZW1lbnRJblZpZXdwb3J0IiwiaXNTbW9vdGhTY3JvbGxTdXBwb3J0ZWQiLCJkb2N1bWVudCIsImRvY3VtZW50RWxlbWVudCIsInN0eWxlIiwic2Nyb2xsVG8iLCJlbCIsIm9mZnNldFRvcCIsImxlZnQiLCJvZmZzZXRMZWZ0Iiwid2lkdGgiLCJvZmZzZXRXaWR0aCIsImhlaWdodCIsIm9mZnNldEhlaWdodCIsIm9mZnNldFBhcmVudCIsImlubmVySGVpZ2h0IiwicGFnZVhPZmZzZXQiLCJpbm5lcldpZHRoIiwibm9kZSIsInBhcmVudHMiLCJ1bnNoaWZ0IiwicGFyZW50Tm9kZSIsImV2ZW50TmFtZSIsInNlbGVjdG9yIiwiY2FsbGJhY2siLCJzY29wZSIsImRpc2FibGVCdWJibGluZyIsImFkZEV2ZW50TGlzdGVuZXIiLCJlIiwiaXNUcnV0aHkiLCJ0YXJnZXQiLCJnZXRBbGxQYXJlbnROb2RlcyIsIkFycmF5IiwiaXNBcnJheSIsInF1ZXJ5U2VsZWN0b3JBbGwiLCJpdGVtIiwicmV2ZXJzZSIsIm1hdGNoZXMiLCJmdW5jIiwiYXBwbHkiLCJwcm90b3R5cGUiLCJzbGljZSIsImFyZ3VtZW50cyIsImVsZW1lbnRDbG9zZXN0IiwiUmVwbGFjZVdpdGgiLCJFbGUiLCJwYXJlbnQiLCJmaXJzdElzTm9kZSIsImNyZWF0ZVRleHROb2RlIiwicmVtb3ZlQ2hpbGQiLCJpbnNlcnRCZWZvcmUiLCJwcmV2aW91c1NpYmxpbmciLCJyZXBsYWNlQ2hpbGQiLCJFbGVtZW50IiwicmVwbGFjZVdpdGgiLCJDaGFyYWN0ZXJEYXRhIiwiRG9jdW1lbnRUeXBlIiwibmFtZSIsImxvY2F0aW9uIiwiaHJlZiIsInJlcGxhY2UiLCJyZWdleCIsIlJlZ0V4cCIsInJlc3VsdHMiLCJleGVjIiwiZGVjb2RlVVJJQ29tcG9uZW50IiwidXJpIiwicmUiLCJoYXNoIiwidGVzdCIsInNwbGl0Iiwic2VwYXJhdG9yIiwiaW5kZXhPZiIsInBhcmFtZXRlcnMiLCJoYXNPd25Qcm9wZXJ0eSIsImFkZFBhcmFtZXRlclRvVXJpIiwicGFyYW1ldGVyIiwidXJpcGFydHMiLCJwcmVmaXgiLCJlbmNvZGVVUklDb21wb25lbnQiLCJwYXJzIiwibGFzdEluZGV4T2YiLCJqb2luIiwicmVtb3ZlUGFyYW1ldGVyRnJvbVVyaSIsInF1ZXJ5U3RyaW5nIiwicGFyc2UiLCJkZWNvZGVVUkkiLCJxdWVyeSJdLCJtYXBwaW5ncyI6IjtBQUFBO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBOzs7QUFHQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTtB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LEVBQWlFQSxPQUFqRSxDQUF5RSxJQUF6RSxFQUE4RSxLQUE5RSxDQUFQLEdBQThGLElBQXpHLENBQVA7QUFDSDs7O3FDQUV1QmEsVSxFQUFZO0FBQ2hDLFVBQUljLEtBQUssR0FBRyxFQUFaOztBQUVBLFdBQUssSUFBSTFILEdBQVQsSUFBZ0I0RyxVQUFoQixFQUE0QjtBQUN4QixZQUFJLE9BQU9jLEtBQVgsRUFBa0I7QUFDZEEsZUFBSyxJQUFJLEdBQVQ7QUFDSDs7QUFFREEsYUFBSyxJQUFJMUgsR0FBRyxHQUFHLEdBQU4sR0FBWTRHLFVBQVUsQ0FBQzVHLEdBQUQsQ0FBL0I7QUFDSDs7QUFFRCxhQUFPMEgsS0FBUDtBQUNIOzs7Ozs7QUFHVXJKLHNFQUFmLEU7Ozs7Ozs7Ozs7OztBQzdKQTtBQUFBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBOztBQUVlLHVFQUFRLEVBQUM7QUFDeEI7Ozs7Ozs7Ozs7OztBQ25DQTtBQUNBO0FBQ0E7QUFDQSx1QkFBdUIsaUJBQWlCO0FBQ3hDO0FBQ0E7QUFDQTtBQUNBIiwiZmlsZSI6ImNvbnRhby11dGlscy1idW5kbGUuanMiLCJzb3VyY2VzQ29udGVudCI6WyIgXHQvLyBUaGUgbW9kdWxlIGNhY2hlXG4gXHR2YXIgaW5zdGFsbGVkTW9kdWxlcyA9IHt9O1xuXG4gXHQvLyBUaGUgcmVxdWlyZSBmdW5jdGlvblxuIFx0ZnVuY3Rpb24gX193ZWJwYWNrX3JlcXVpcmVfXyhtb2R1bGVJZCkge1xuXG4gXHRcdC8vIENoZWNrIGlmIG1vZHVsZSBpcyBpbiBjYWNoZVxuIFx0XHRpZihpbnN0YWxsZWRNb2R1bGVzW21vZHVsZUlkXSkge1xuIFx0XHRcdHJldHVybiBpbnN0YWxsZWRNb2R1bGVzW21vZHVsZUlkXS5leHBvcnRzO1xuIFx0XHR9XG4gXHRcdC8vIENyZWF0ZSBhIG5ldyBtb2R1bGUgKGFuZCBwdXQgaXQgaW50byB0aGUgY2FjaGUpXG4gXHRcdHZhciBtb2R1bGUgPSBpbnN0YWxsZWRNb2R1bGVzW21vZHVsZUlkXSA9IHtcbiBcdFx0XHRpOiBtb2R1bGVJZCxcbiBcdFx0XHRsOiBmYWxzZSxcbiBcdFx0XHRleHBvcnRzOiB7fVxuIFx0XHR9O1xuXG4gXHRcdC8vIEV4ZWN1dGUgdGhlIG1vZHVsZSBmdW5jdGlvblxuIFx0XHRtb2R1bGVzW21vZHVsZUlkXS5jYWxsKG1vZHVsZS5leHBvcnRzLCBtb2R1bGUsIG1vZHVsZS5leHBvcnRzLCBfX3dlYnBhY2tfcmVxdWlyZV9fKTtcblxuIFx0XHQvLyBGbGFnIHRoZSBtb2R1bGUgYXMgbG9hZGVkXG4gXHRcdG1vZHVsZS5sID0gdHJ1ZTtcblxuIFx0XHQvLyBSZXR1cm4gdGhlIGV4cG9ydHMgb2YgdGhlIG1vZHVsZVxuIFx0XHRyZXR1cm4gbW9kdWxlLmV4cG9ydHM7XG4gXHR9XG5cblxuIFx0Ly8gZXhwb3NlIHRoZSBtb2R1bGVzIG9iamVjdCAoX193ZWJwYWNrX21vZHVsZXNfXylcbiBcdF9fd2VicGFja19yZXF1aXJlX18ubSA9IG1vZHVsZXM7XG5cbiBcdC8vIGV4cG9zZSB0aGUgbW9kdWxlIGNhY2hlXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLmMgPSBpbnN0YWxsZWRNb2R1bGVzO1xuXG4gXHQvLyBkZWZpbmUgZ2V0dGVyIGZ1bmN0aW9uIGZvciBoYXJtb255IGV4cG9ydHNcbiBcdF9fd2VicGFja19yZXF1aXJlX18uZCA9IGZ1bmN0aW9uKGV4cG9ydHMsIG5hbWUsIGdldHRlcikge1xuIFx0XHRpZighX193ZWJwYWNrX3JlcXVpcmVfXy5vKGV4cG9ydHMsIG5hbWUpKSB7XG4gXHRcdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KGV4cG9ydHMsIG5hbWUsIHsgZW51bWVyYWJsZTogdHJ1ZSwgZ2V0OiBnZXR0ZXIgfSk7XG4gXHRcdH1cbiBcdH07XG5cbiBcdC8vIGRlZmluZSBfX2VzTW9kdWxlIG9uIGV4cG9ydHNcbiBcdF9fd2VicGFja19yZXF1aXJlX18uciA9IGZ1bmN0aW9uKGV4cG9ydHMpIHtcbiBcdFx0aWYodHlwZW9mIFN5bWJvbCAhPT0gJ3VuZGVmaW5lZCcgJiYgU3ltYm9sLnRvU3RyaW5nVGFnKSB7XG4gXHRcdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KGV4cG9ydHMsIFN5bWJvbC50b1N0cmluZ1RhZywgeyB2YWx1ZTogJ01vZHVsZScgfSk7XG4gXHRcdH1cbiBcdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KGV4cG9ydHMsICdfX2VzTW9kdWxlJywgeyB2YWx1ZTogdHJ1ZSB9KTtcbiBcdH07XG5cbiBcdC8vIGNyZWF0ZSBhIGZha2UgbmFtZXNwYWNlIG9iamVjdFxuIFx0Ly8gbW9kZSAmIDE6IHZhbHVlIGlzIGEgbW9kdWxlIGlkLCByZXF1aXJlIGl0XG4gXHQvLyBtb2RlICYgMjogbWVyZ2UgYWxsIHByb3BlcnRpZXMgb2YgdmFsdWUgaW50byB0aGUgbnNcbiBcdC8vIG1vZGUgJiA0OiByZXR1cm4gdmFsdWUgd2hlbiBhbHJlYWR5IG5zIG9iamVjdFxuIFx0Ly8gbW9kZSAmIDh8MTogYmVoYXZlIGxpa2UgcmVxdWlyZVxuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy50ID0gZnVuY3Rpb24odmFsdWUsIG1vZGUpIHtcbiBcdFx0aWYobW9kZSAmIDEpIHZhbHVlID0gX193ZWJwYWNrX3JlcXVpcmVfXyh2YWx1ZSk7XG4gXHRcdGlmKG1vZGUgJiA4KSByZXR1cm4gdmFsdWU7XG4gXHRcdGlmKChtb2RlICYgNCkgJiYgdHlwZW9mIHZhbHVlID09PSAnb2JqZWN0JyAmJiB2YWx1ZSAmJiB2YWx1ZS5fX2VzTW9kdWxlKSByZXR1cm4gdmFsdWU7XG4gXHRcdHZhciBucyA9IE9iamVjdC5jcmVhdGUobnVsbCk7XG4gXHRcdF9fd2VicGFja19yZXF1aXJlX18ucihucyk7XG4gXHRcdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShucywgJ2RlZmF1bHQnLCB7IGVudW1lcmFibGU6IHRydWUsIHZhbHVlOiB2YWx1ZSB9KTtcbiBcdFx0aWYobW9kZSAmIDIgJiYgdHlwZW9mIHZhbHVlICE9ICdzdHJpbmcnKSBmb3IodmFyIGtleSBpbiB2YWx1ZSkgX193ZWJwYWNrX3JlcXVpcmVfXy5kKG5zLCBrZXksIGZ1bmN0aW9uKGtleSkgeyByZXR1cm4gdmFsdWVba2V5XTsgfS5iaW5kKG51bGwsIGtleSkpO1xuIFx0XHRyZXR1cm4gbnM7XG4gXHR9O1xuXG4gXHQvLyBnZXREZWZhdWx0RXhwb3J0IGZ1bmN0aW9uIGZvciBjb21wYXRpYmlsaXR5IHdpdGggbm9uLWhhcm1vbnkgbW9kdWxlc1xuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5uID0gZnVuY3Rpb24obW9kdWxlKSB7XG4gXHRcdHZhciBnZXR0ZXIgPSBtb2R1bGUgJiYgbW9kdWxlLl9fZXNNb2R1bGUgP1xuIFx0XHRcdGZ1bmN0aW9uIGdldERlZmF1bHQoKSB7IHJldHVybiBtb2R1bGVbJ2RlZmF1bHQnXTsgfSA6XG4gXHRcdFx0ZnVuY3Rpb24gZ2V0TW9kdWxlRXhwb3J0cygpIHsgcmV0dXJuIG1vZHVsZTsgfTtcbiBcdFx0X193ZWJwYWNrX3JlcXVpcmVfXy5kKGdldHRlciwgJ2EnLCBnZXR0ZXIpO1xuIFx0XHRyZXR1cm4gZ2V0dGVyO1xuIFx0fTtcblxuIFx0Ly8gT2JqZWN0LnByb3RvdHlwZS5oYXNPd25Qcm9wZXJ0eS5jYWxsXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLm8gPSBmdW5jdGlvbihvYmplY3QsIHByb3BlcnR5KSB7IHJldHVybiBPYmplY3QucHJvdG90eXBlLmhhc093blByb3BlcnR5LmNhbGwob2JqZWN0LCBwcm9wZXJ0eSk7IH07XG5cbiBcdC8vIF9fd2VicGFja19wdWJsaWNfcGF0aF9fXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLnAgPSBcIi9wdWJsaWMvanMvXCI7XG5cblxuIFx0Ly8gTG9hZCBlbnRyeSBtb2R1bGUgYW5kIHJldHVybiBleHBvcnRzXG4gXHRyZXR1cm4gX193ZWJwYWNrX3JlcXVpcmVfXyhfX3dlYnBhY2tfcmVxdWlyZV9fLnMgPSBcIi4vbm9kZV9tb2R1bGVzL0BodW5kaC9jb250YW8tdXRpbHMtYnVuZGxlL2pzL2NvbnRhby11dGlscy1idW5kbGUuanNcIik7XG4iLCJpbXBvcnQgR2VuZXJhbFV0aWwgZnJvbSAnLi9nZW5lcmFsLXV0aWwnO1xuaW1wb3J0IFVybFV0aWwgZnJvbSAnLi91cmwtdXRpbCc7XG5cbmNsYXNzIEFqYXhVdGlsIHtcbiAgc3RhdGljIGdldCh1cmwsIGRhdGEsIGNvbmZpZykge1xuICAgIGNvbmZpZyA9IEFqYXhVdGlsLnNldERlZmF1bHRzKGNvbmZpZyk7XG5cbiAgICBsZXQgcmVxdWVzdCA9IEFqYXhVdGlsLmluaXRpYWxpemVSZXF1ZXN0KCdHRVQnLCBVcmxVdGlsLmFkZFBhcmFtZXRlcnNUb1VyaSh1cmwsIGRhdGEpLCBjb25maWcpLFxuICAgICAgICBzdWJtaXREYXRhID0ge1xuICAgICAgICAgIGNvbmZpZzogY29uZmlnLFxuICAgICAgICAgIGFjdGlvbjogdXJsLFxuICAgICAgICAgIGRhdGE6IGRhdGFcbiAgICAgICAgfTtcblxuICAgIEFqYXhVdGlsLmRvQWpheFN1Ym1pdChyZXF1ZXN0LCBzdWJtaXREYXRhKTtcbiAgfVxuXG4gIHN0YXRpYyBwb3N0KHVybCwgZGF0YSwgY29uZmlnKSB7XG4gICAgY29uZmlnID0gQWpheFV0aWwuc2V0RGVmYXVsdHMoY29uZmlnKTtcblxuICAgIGxldCByZXF1ZXN0ID0gQWpheFV0aWwuaW5pdGlhbGl6ZVJlcXVlc3QoJ1BPU1QnLCB1cmwsIGNvbmZpZyksXG4gICAgICAgIHN1Ym1pdERhdGEgPSB7XG4gICAgICAgICAgY29uZmlnOiBjb25maWcsXG4gICAgICAgICAgYWN0aW9uOiB1cmwsXG4gICAgICAgICAgZGF0YTogZGF0YVxuICAgICAgICB9O1xuXG4gICAgQWpheFV0aWwuZG9BamF4U3VibWl0KHJlcXVlc3QsIHN1Ym1pdERhdGEpO1xuICB9XG5cbiAgc3RhdGljIGRvQWpheFN1Ym1pdChyZXF1ZXN0LCBzdWJtaXREYXRhKSB7XG4gICAgbGV0IGNvbmZpZyA9IHN1Ym1pdERhdGEuY29uZmlnO1xuXG4gICAgcmVxdWVzdC5vbmxvYWQgPSBmdW5jdGlvbigpIHtcbiAgICAgIGlmIChyZXF1ZXN0LnN0YXR1cyA+PSAyMDAgJiYgcmVxdWVzdC5zdGF0dXMgPCA0MDApIHtcbiAgICAgICAgR2VuZXJhbFV0aWwuY2FsbChjb25maWcub25TdWNjZXNzLCByZXF1ZXN0KTtcbiAgICAgIH0gZWxzZSB7XG4gICAgICAgIEdlbmVyYWxVdGlsLmNhbGwoY29uZmlnLm9uRXJyb3IsIHJlcXVlc3QpO1xuICAgICAgfVxuXG4gICAgICBHZW5lcmFsVXRpbC5jYWxsKGNvbmZpZy5hZnRlclN1Ym1pdCwgc3VibWl0RGF0YS5hY3Rpb24sIHN1Ym1pdERhdGEuZGF0YSwgY29uZmlnKTtcbiAgICB9O1xuXG4gICAgR2VuZXJhbFV0aWwuY2FsbChjb25maWcuYmVmb3JlU3VibWl0LCBzdWJtaXREYXRhLmFjdGlvbiwgc3VibWl0RGF0YS5kYXRhLCBjb25maWcpO1xuXG4gICAgaWYgKCd1bmRlZmluZWQnID09PSB0eXBlb2Ygc3VibWl0RGF0YS5kYXRhKSB7XG4gICAgICByZXF1ZXN0LnNlbmQoKTtcbiAgICB9IGVsc2Uge1xuICAgICAgc3VibWl0RGF0YS5kYXRhID0gQWpheFV0aWwucHJlcGFyZURhdGFGb3JTZW5kKHN1Ym1pdERhdGEuZGF0YSk7XG5cbiAgICAgIHJlcXVlc3Quc2VuZChzdWJtaXREYXRhLmRhdGEpO1xuICAgIH1cbiAgfVxuXG4gIHN0YXRpYyBwcmVwYXJlRGF0YUZvclNlbmQoZGF0YSkge1xuICAgIGlmICgnb2JqZWN0JyA9PT0gdHlwZW9mKGRhdGEpKVxuICAgIHtcbiAgICAgIGxldCBmb3JtRGF0YSA9IG5ldyBGb3JtRGF0YSgpO1xuXG4gICAgICBPYmplY3Qua2V5cyhkYXRhKS5mb3JFYWNoKGZpZWxkID0+IHtcbiAgICAgICAgZm9ybURhdGEuYXBwZW5kKGZpZWxkLCBkYXRhW2ZpZWxkXSk7XG4gICAgICB9KTtcblxuICAgICAgcmV0dXJuIGZvcm1EYXRhO1xuICAgIH1cblxuICAgIHJldHVybiBkYXRhO1xuICB9XG5cbiAgc3RhdGljIGluaXRpYWxpemVSZXF1ZXN0KG1ldGhvZCwgdXJsLCBjb25maWcpIHtcbiAgICBsZXQgcmVxdWVzdCA9IG5ldyBYTUxIdHRwUmVxdWVzdCgpO1xuXG4gICAgcmVxdWVzdC5vcGVuKG1ldGhvZCwgdXJsLCB0cnVlKTtcbiAgICByZXF1ZXN0ID0gQWpheFV0aWwuc2V0UmVxdWVzdEhlYWRlcnMocmVxdWVzdCwgY29uZmlnKTtcblxuICAgIHJldHVybiByZXF1ZXN0O1xuICB9XG5cbiAgc3RhdGljIHNldFJlcXVlc3RIZWFkZXJzKHJlcXVlc3QsIGNvbmZpZykge1xuICAgIGlmICgndW5kZWZpbmVkJyAhPT0gdHlwZW9mIGNvbmZpZy5oZWFkZXJzKSB7XG4gICAgICBPYmplY3Qua2V5cyhjb25maWcuaGVhZGVycykuZm9yRWFjaChrZXkgPT4ge1xuICAgICAgICByZXF1ZXN0LnNldFJlcXVlc3RIZWFkZXIoa2V5LCBjb25maWcuaGVhZGVyc1trZXldKTtcbiAgICAgIH0pO1xuICAgIH1cblxuICAgIHJldHVybiByZXF1ZXN0O1xuICB9XG5cbiAgc3RhdGljIHNldERlZmF1bHRzKGNvbmZpZykge1xuICAgIGlmICgndW5kZWZpbmVkJyA9PT0gdHlwZW9mIGNvbmZpZy5oZWFkZXJzKSB7XG4gICAgICBjb25maWcuaGVhZGVycyA9IHsnWC1SZXF1ZXN0ZWQtV2l0aCc6ICdYTUxIdHRwUmVxdWVzdCd9O1xuICAgIH1cblxuICAgIHJldHVybiBjb25maWc7XG4gIH1cbn1cblxuZXhwb3J0IGRlZmF1bHQgQWpheFV0aWw7IiwiY2xhc3MgQXJyYXlVdGlsIHtcbiAgICBzdGF0aWMgcmVtb3ZlRnJvbUFycmF5KHZhbHVlLCBhcnJheSkge1xuICAgICAgICBmb3IgKHZhciBpID0gMDsgaSA8IGFycmF5Lmxlbmd0aDsgaSsrKSB7XG4gICAgICAgICAgICBpZiAoSlNPTi5zdHJpbmdpZnkodmFsdWUpID09IEpTT04uc3RyaW5naWZ5KGFycmF5W2ldKSkge1xuICAgICAgICAgICAgICAgIGFycmF5LnNwbGljZShpLCAxKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgICAgICByZXR1cm4gYXJyYXk7XG4gICAgfVxufVxuXG5leHBvcnQgZGVmYXVsdCBBcnJheVV0aWwiLCJpbXBvcnQgJy4vcG9seWZpbGxzJztcbmltcG9ydCBBcnJheVV0aWwgZnJvbSAnLi9hcnJheS11dGlsJ1xuaW1wb3J0IERvbVV0aWwgZnJvbSAnLi9kb20tdXRpbCdcbmltcG9ydCBFdmVudFV0aWwgZnJvbSAnLi9ldmVudC11dGlsJ1xuaW1wb3J0IFVybFV0aWwgZnJvbSAnLi91cmwtdXRpbCdcbmltcG9ydCBHZW5lcmFsVXRpbCBmcm9tICcuL2dlbmVyYWwtdXRpbCdcbmltcG9ydCBBamF4VXRpbCBmcm9tICcuL2FqYXgtdXRpbCdcblxubGV0IHV0aWxzQnVuZGxlID0ge1xuICAgIGFqYXg6IEFqYXhVdGlsLFxuICAgIGFycmF5OiBBcnJheVV0aWwsXG4gICAgZG9tOiBEb21VdGlsLFxuICAgIGV2ZW50OiBFdmVudFV0aWwsXG4gICAgdXJsOiBVcmxVdGlsLFxuICAgIHV0aWw6IEdlbmVyYWxVdGlsXG59O1xuXG53aW5kb3cudXRpbHNCdW5kbGUgPSB1dGlsc0J1bmRsZTtcblxuZXhwb3J0IHtcbiAgICB1dGlsc0J1bmRsZSxcbiAgICBBamF4VXRpbCxcbiAgICBBcnJheVV0aWwsXG4gICAgRG9tVXRpbCxcbiAgICBFdmVudFV0aWwsXG4gICAgR2VuZXJhbFV0aWwsXG4gICAgVXJsVXRpbFxufVxuIiwiaW1wb3J0IEFycmF5VXRpbCBmcm9tICcuL2FycmF5LXV0aWwnO1xuXG5jbGFzcyBEb21VdGlsIHtcbiAgICBzdGF0aWMgZ2V0VGV4dFdpdGhvdXRDaGlsZHJlbihlbGVtZW50LCBub3RyaW0pIHtcbiAgICAgICAgbGV0IHJlc3VsdCA9IGVsZW1lbnQuY2xvbmUoKTtcbiAgICAgICAgcmVzdWx0LmNoaWxkcmVuKCkucmVtb3ZlKCk7XG5cbiAgICAgICAgaWYgKHR5cGVvZiBub3RyaW0gIT09ICd1bmRlZmluZWQnICYmIG5vdHJpbSA9PT0gdHJ1ZSkge1xuICAgICAgICAgICAgcmV0dXJuIHJlc3VsdC50ZXh0KCk7XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICByZXR1cm4gcmVzdWx0LnRleHQoKS50cmltKCk7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICBzdGF0aWMgc2Nyb2xsVG8oZWxlbWVudCwgb2Zmc2V0ID0gMCwgZGVsYXkgPSAwLCBmb3JjZSA9IGZhbHNlKSB7XG4gICAgICAgIGxldCByZWN0ID0gZWxlbWVudC5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKTtcbiAgICAgICAgbGV0IHNjcm9sbFBvc2l0aW9uID0gKHJlY3QudG9wICsgd2luZG93LnBhZ2VZT2Zmc2V0IC0gb2Zmc2V0KTtcbiAgICAgICAgc2V0VGltZW91dCgoKSA9PiB7XG4gICAgICAgICAgICBpZiAoIXRoaXMuZWxlbWVudEluVmlld3BvcnQoZWxlbWVudCkgfHwgZm9yY2UgPT09IHRydWUpXG4gICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgdmFyIGlzU21vb3RoU2Nyb2xsU3VwcG9ydGVkID0gJ3Njcm9sbEJlaGF2aW9yJyBpbiBkb2N1bWVudC5kb2N1bWVudEVsZW1lbnQuc3R5bGU7XG4gICAgICAgICAgICAgICAgaWYgKGlzU21vb3RoU2Nyb2xsU3VwcG9ydGVkKVxuICAgICAgICAgICAgICAgIHtcbiAgICAgICAgICAgICAgICAgICAgd2luZG93LnNjcm9sbFRvKHtcbiAgICAgICAgICAgICAgICAgICAgICAgICd0b3AnOiBzY3JvbGxQb3NpdGlvbixcbiAgICAgICAgICAgICAgICAgICAgICAgICdiZWhhdmlvcic6ICdzbW9vdGgnLFxuICAgICAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgZWxzZSB7XG4gICAgICAgICAgICAgICAgICAgIHdpbmRvdy5zY3JvbGxUbygwLCBzY3JvbGxQb3NpdGlvbik7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfVxuICAgICAgICB9LCBkZWxheSk7XG4gICAgfVxuXG4gICAgc3RhdGljIGVsZW1lbnRJblZpZXdwb3J0KGVsKSB7XG4gICAgICAgIGxldCB0b3AgPSBlbC5vZmZzZXRUb3A7XG4gICAgICAgIGxldCBsZWZ0ID0gZWwub2Zmc2V0TGVmdDtcbiAgICAgICAgbGV0IHdpZHRoID0gZWwub2Zmc2V0V2lkdGg7XG4gICAgICAgIGxldCBoZWlnaHQgPSBlbC5vZmZzZXRIZWlnaHQ7XG5cbiAgICAgICAgd2hpbGUgKGVsLm9mZnNldFBhcmVudCkge1xuICAgICAgICAgICAgZWwgPSBlbC5vZmZzZXRQYXJlbnQ7XG4gICAgICAgICAgICB0b3AgKz0gZWwub2Zmc2V0VG9wO1xuICAgICAgICAgICAgbGVmdCArPSBlbC5vZmZzZXRMZWZ0O1xuICAgICAgICB9XG5cbiAgICAgICAgcmV0dXJuIChcbiAgICAgICAgICAgIHRvcCA8ICh3aW5kb3cucGFnZVlPZmZzZXQgKyB3aW5kb3cuaW5uZXJIZWlnaHQpICYmXG4gICAgICAgICAgICBsZWZ0IDwgKHdpbmRvdy5wYWdlWE9mZnNldCArIHdpbmRvdy5pbm5lcldpZHRoKSAmJlxuICAgICAgICAgICAgKHRvcCArIGhlaWdodCkgPiB3aW5kb3cucGFnZVlPZmZzZXQgJiZcbiAgICAgICAgICAgIChsZWZ0ICsgd2lkdGgpID4gd2luZG93LnBhZ2VYT2Zmc2V0XG4gICAgICAgICk7XG4gICAgfVxuXG4gICAgc3RhdGljIGdldEFsbFBhcmVudE5vZGVzKG5vZGUpIHtcbiAgICAgICAgdmFyIHBhcmVudHMgPSBbXTtcblxuICAgICAgICB3aGlsZSAobm9kZSkge1xuICAgICAgICAgICAgcGFyZW50cy51bnNoaWZ0KG5vZGUpO1xuICAgICAgICAgICAgbm9kZSA9IG5vZGUucGFyZW50Tm9kZTtcbiAgICAgICAgfVxuXG4gICAgICAgIGZvciAodmFyIGkgPSAwOyBpIDwgcGFyZW50cy5sZW5ndGg7IGkrKykge1xuICAgICAgICAgICAgaWYgKHBhcmVudHNbaV0gPT09IGRvY3VtZW50KSB7XG4gICAgICAgICAgICAgICAgcGFyZW50cy5zcGxpY2UoaSwgMSk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gcGFyZW50cztcbiAgICB9XG59XG5cbmV4cG9ydCBkZWZhdWx0IERvbVV0aWwiLCJpbXBvcnQgRG9tVXRpbCBmcm9tICcuL2RvbS11dGlsJztcbmltcG9ydCBHZW5lcmFsVXRpbCBmcm9tICcuL2dlbmVyYWwtdXRpbCdcblxuY2xhc3MgRXZlbnRVdGlsIHtcbiAgICBzdGF0aWMgYWRkRHluYW1pY0V2ZW50TGlzdGVuZXIoZXZlbnROYW1lLCBzZWxlY3RvciwgY2FsbGJhY2ssIHNjb3BlLCBkaXNhYmxlQnViYmxpbmcpIHtcbiAgICAgICAgaWYgKHR5cGVvZiBzY29wZSA9PT0gJ3VuZGVmaW5lZCcpIHtcbiAgICAgICAgICAgIHNjb3BlID0gZG9jdW1lbnQ7XG4gICAgICAgIH1cblxuICAgICAgICBzY29wZS5hZGRFdmVudExpc3RlbmVyKGV2ZW50TmFtZSwgZnVuY3Rpb24gKGUpIHtcblxuICAgICAgICAgICAgbGV0IHBhcmVudHM7XG5cbiAgICAgICAgICAgIGlmIChHZW5lcmFsVXRpbC5pc1RydXRoeShkaXNhYmxlQnViYmxpbmcpKSB7XG4gICAgICAgICAgICAgICAgcGFyZW50cyA9IFtlLnRhcmdldF07XG4gICAgICAgICAgICB9IGVsc2UgaWYgKGUudGFyZ2V0ICE9PSBkb2N1bWVudCkge1xuICAgICAgICAgICAgICAgIHBhcmVudHMgPSBEb21VdGlsLmdldEFsbFBhcmVudE5vZGVzKGUudGFyZ2V0KTtcbiAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgLy8gZm9yIGluc3RhbmNlIHdpbmRvdyBsb2FkL3Jlc2l6ZSBldmVudFxuICAgICAgICAgICAgaWYgKCFBcnJheS5pc0FycmF5KHBhcmVudHMpKSB7XG4gICAgICAgICAgICAgICAgZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbChzZWxlY3RvcikuZm9yRWFjaChmdW5jdGlvbiAoaXRlbSkge1xuICAgICAgICAgICAgICAgICAgICBjYWxsYmFjayhpdGVtLCBlKTtcbiAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICByZXR1cm47XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIHBhcmVudHMucmV2ZXJzZSgpLmZvckVhY2goZnVuY3Rpb24gKGl0ZW0pIHtcbiAgICAgICAgICAgICAgICBpZiAoaXRlbSAmJiBpdGVtLm1hdGNoZXMoc2VsZWN0b3IpKSB7XG4gICAgICAgICAgICAgICAgICAgIGNhbGxiYWNrKGl0ZW0sIGUpO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9KTtcbiAgICB9XG59XG5cbmV4cG9ydCBkZWZhdWx0IEV2ZW50VXRpbFxuIiwiY2xhc3MgR2VuZXJhbFV0aWwge1xuICAgIHN0YXRpYyBpc1RydXRoeSh2YWx1ZSkge1xuICAgICAgICByZXR1cm4gdHlwZW9mIHZhbHVlICE9PSAndW5kZWZpbmVkJyAmJiB2YWx1ZSAhPT0gbnVsbDtcbiAgICB9XG5cbiAgICBzdGF0aWMgY2FsbChmdW5jKSB7XG4gICAgICAgIGlmICh0eXBlb2YgZnVuYyA9PT0gJ2Z1bmN0aW9uJykge1xuICAgICAgICAgICAgZnVuYy5hcHBseSh0aGlzLCBBcnJheS5wcm90b3R5cGUuc2xpY2UuY2FsbChhcmd1bWVudHMsIDEpKTtcbiAgICAgICAgfVxuICAgIH1cbn1cblxuZXhwb3J0IGRlZmF1bHQgR2VuZXJhbFV0aWwiLCIvLyBmb3JlYWNoIG9uIG5vZGVsaXN0c1xuaW1wb3J0ICdub2RlbGlzdC1mb3JlYWNoLXBvbHlmaWxsJztcblxuLy8gY2xvc2VzdCgpIGFuZCBtYXRjaGVzIHBvbHlmaWxsXG5pbXBvcnQgZWxlbWVudENsb3Nlc3QgZnJvbSAnZWxlbWVudC1jbG9zZXN0JztcblxuZWxlbWVudENsb3Nlc3Qod2luZG93KTtcblxuLy8gcmVwbGFjZVdpdGhcbmZ1bmN0aW9uIFJlcGxhY2VXaXRoKEVsZSkge1xuICAgICd1c2Utc3RyaWN0JzsgLy8gRm9yIHNhZmFyaSwgYW5kIElFID4gMTBcbiAgICB2YXIgcGFyZW50ID0gdGhpcy5wYXJlbnROb2RlLFxuICAgICAgICBpID0gYXJndW1lbnRzLmxlbmd0aCxcbiAgICAgICAgZmlyc3RJc05vZGUgPSArKHBhcmVudCAmJiB0eXBlb2YgRWxlID09PSAnb2JqZWN0Jyk7XG4gICAgaWYgKCFwYXJlbnQpIHJldHVybjtcblxuICAgIHdoaWxlIChpLS0gPiBmaXJzdElzTm9kZSkge1xuICAgICAgICBpZiAocGFyZW50ICYmIHR5cGVvZiBhcmd1bWVudHNbaV0gIT09ICdvYmplY3QnKSB7XG4gICAgICAgICAgICBhcmd1bWVudHNbaV0gPSBkb2N1bWVudC5jcmVhdGVUZXh0Tm9kZShhcmd1bWVudHNbaV0pO1xuICAgICAgICB9XG4gICAgICAgIGlmICghcGFyZW50ICYmIGFyZ3VtZW50c1tpXS5wYXJlbnROb2RlKSB7XG4gICAgICAgICAgICBhcmd1bWVudHNbaV0ucGFyZW50Tm9kZS5yZW1vdmVDaGlsZChhcmd1bWVudHNbaV0pO1xuICAgICAgICAgICAgY29udGludWU7XG4gICAgICAgIH1cbiAgICAgICAgcGFyZW50Lmluc2VydEJlZm9yZSh0aGlzLnByZXZpb3VzU2libGluZywgYXJndW1lbnRzW2ldKTtcbiAgICB9XG4gICAgaWYgKGZpcnN0SXNOb2RlKSBwYXJlbnQucmVwbGFjZUNoaWxkKEVsZSwgdGhpcyk7XG59XG5cbmlmICghRWxlbWVudC5wcm90b3R5cGUucmVwbGFjZVdpdGgpXG4gICAgRWxlbWVudC5wcm90b3R5cGUucmVwbGFjZVdpdGggPSBSZXBsYWNlV2l0aDtcbmlmICghQ2hhcmFjdGVyRGF0YS5wcm90b3R5cGUucmVwbGFjZVdpdGgpXG4gICAgQ2hhcmFjdGVyRGF0YS5wcm90b3R5cGUucmVwbGFjZVdpdGggPSBSZXBsYWNlV2l0aDtcbmlmICghRG9jdW1lbnRUeXBlLnByb3RvdHlwZS5yZXBsYWNlV2l0aClcbiAgICBDaGFyYWN0ZXJEYXRhLnByb3RvdHlwZS5yZXBsYWNlV2l0aCA9IFJlcGxhY2VXaXRoOyIsImNsYXNzIFVybFV0aWwge1xuICAgIHN0YXRpYyBnZXRQYXJhbWV0ZXJCeU5hbWUobmFtZSwgdXJsKVxuICAgIHtcbiAgICAgICAgaWYgKCF1cmwpXG4gICAgICAgIHtcbiAgICAgICAgICAgIHVybCA9IHdpbmRvdy5sb2NhdGlvbi5ocmVmO1xuICAgICAgICB9XG5cbiAgICAgICAgbmFtZSA9IG5hbWUucmVwbGFjZSgvW1xcW1xcXV0vZywgXCJcXFxcJCZcIik7XG5cbiAgICAgICAgbGV0IHJlZ2V4ID0gbmV3IFJlZ0V4cChcIls/Jl1cIiArIG5hbWUgKyBcIig9KFteJiNdKil8JnwjfCQpXCIpLFxuICAgICAgICAgICAgcmVzdWx0cyA9IHJlZ2V4LmV4ZWModXJsKTtcblxuICAgICAgICBpZiAoIXJlc3VsdHMpXG4gICAgICAgIHtcbiAgICAgICAgICAgIHJldHVybiBudWxsO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKCFyZXN1bHRzWzJdKVxuICAgICAgICB7XG4gICAgICAgICAgICByZXR1cm4gJyc7XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gZGVjb2RlVVJJQ29tcG9uZW50KHJlc3VsdHNbMl0ucmVwbGFjZSgvXFwrL2csIFwiIFwiKSk7XG4gICAgfVxuXG4gICAgc3RhdGljIGFkZFBhcmFtZXRlclRvVXJpKHVyaSwga2V5LCB2YWx1ZSlcbiAgICB7XG4gICAgICAgIGlmICghdXJpKVxuICAgICAgICB7XG4gICAgICAgICAgICB1cmkgPSB3aW5kb3cubG9jYXRpb24uaHJlZjtcbiAgICAgICAgfVxuXG4gICAgICAgIGxldCByZSA9IG5ldyBSZWdFeHAoXCIoWz8mXSlcIiArIGtleSArIFwiPS4qPygmfCN8JCkoLiopXCIsIFwiZ2lcIiksXG4gICAgICAgICAgICBoYXNoO1xuXG4gICAgICAgIGlmIChyZS50ZXN0KHVyaSkpXG4gICAgICAgIHtcbiAgICAgICAgICAgIGlmICh0eXBlb2YgdmFsdWUgIT09ICd1bmRlZmluZWQnICYmIHZhbHVlICE9PSBudWxsKVxuICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIHJldHVybiB1cmkucmVwbGFjZShyZSwgJyQxJyArIGtleSArIFwiPVwiICsgdmFsdWUgKyAnJDIkMycpO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgZWxzZVxuICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIGhhc2ggPSB1cmkuc3BsaXQoJyMnKTtcbiAgICAgICAgICAgICAgICB1cmkgPSBoYXNoWzBdLnJlcGxhY2UocmUsICckMSQzJykucmVwbGFjZSgvKCZ8XFw/KSQvLCAnJyk7XG5cbiAgICAgICAgICAgICAgICBpZiAodHlwZW9mIGhhc2hbMV0gIT09ICd1bmRlZmluZWQnICYmIGhhc2hbMV0gIT09IG51bGwpXG4gICAgICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgICAgICB1cmkgKz0gJyMnICsgaGFzaFsxXTtcbiAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICByZXR1cm4gdXJpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgICAgIGVsc2VcbiAgICAgICAge1xuICAgICAgICAgICAgaWYgKHR5cGVvZiB2YWx1ZSAhPT0gJ3VuZGVmaW5lZCcgJiYgdmFsdWUgIT09IG51bGwpXG4gICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgbGV0IHNlcGFyYXRvciA9IHVyaS5pbmRleE9mKCc/JykgIT09IC0xID8gJyYnIDogJz8nO1xuICAgICAgICAgICAgICAgIGhhc2ggPSB1cmkuc3BsaXQoJyMnKTtcbiAgICAgICAgICAgICAgICB1cmkgPSBoYXNoWzBdICsgc2VwYXJhdG9yICsga2V5ICsgJz0nICsgdmFsdWU7XG5cbiAgICAgICAgICAgICAgICBpZiAodHlwZW9mIGhhc2hbMV0gIT09ICd1bmRlZmluZWQnICYmIGhhc2hbMV0gIT09IG51bGwpXG4gICAgICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgICAgICB1cmkgKz0gJyMnICsgaGFzaFsxXTtcbiAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICByZXR1cm4gdXJpO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgZWxzZVxuICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIHJldHVybiB1cmk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICBzdGF0aWMgYWRkUGFyYW1ldGVyc1RvVXJpKHVyaSwgcGFyYW1ldGVycylcbiAgICB7XG4gICAgICAgIGZvciAobGV0IGtleSBpbiBwYXJhbWV0ZXJzKVxuICAgICAgICB7XG4gICAgICAgICAgICBpZiAocGFyYW1ldGVycy5oYXNPd25Qcm9wZXJ0eShrZXkpKVxuICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIHVyaSA9IHRoaXMuYWRkUGFyYW1ldGVyVG9VcmkodXJpLCBrZXksIHBhcmFtZXRlcnNba2V5XSk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gdXJpO1xuICAgIH1cblxuICAgIHN0YXRpYyByZW1vdmVQYXJhbWV0ZXJGcm9tVXJpKHVyaSwgcGFyYW1ldGVyKVxuICAgIHtcbiAgICAgICAgLy9wcmVmZXIgdG8gdXNlIGwuc2VhcmNoIGlmIHlvdSBoYXZlIGEgbG9jYXRpb24vbGluayBvYmplY3RcbiAgICAgICAgbGV0IHVyaXBhcnRzID0gdXJpLnNwbGl0KCc/Jyk7XG5cbiAgICAgICAgaWYgKHVyaXBhcnRzLmxlbmd0aCA+PSAyKVxuICAgICAgICB7XG5cbiAgICAgICAgICAgIGxldCBwcmVmaXggPSBlbmNvZGVVUklDb21wb25lbnQocGFyYW1ldGVyKSArICc9JztcbiAgICAgICAgICAgIGxldCBwYXJzID0gdXJpcGFydHNbMV0uc3BsaXQoL1smO10vZyk7XG5cbiAgICAgICAgICAgIC8vcmV2ZXJzZSBpdGVyYXRpb24gYXMgbWF5IGJlIGRlc3RydWN0aXZlXG4gICAgICAgICAgICBmb3IgKGxldCBpID0gcGFycy5sZW5ndGg7IGktLSA+IDA7KVxuICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIC8vaWRpb20gZm9yIHN0cmluZy5zdGFydHNXaXRoXG4gICAgICAgICAgICAgICAgaWYgKHBhcnNbaV0ubGFzdEluZGV4T2YocHJlZml4LCAwKSAhPT0gLTEpXG4gICAgICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgICAgICBwYXJzLnNwbGljZShpLCAxKTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIHVyaSA9IHVyaXBhcnRzWzBdICsgJz8nICsgcGFycy5qb2luKCcmJyk7XG4gICAgICAgICAgICByZXR1cm4gdXJpO1xuICAgICAgICB9XG4gICAgICAgIGVsc2VcbiAgICAgICAge1xuICAgICAgICAgICAgcmV0dXJuIHVyaTtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIHN0YXRpYyByZW1vdmVQYXJhbWV0ZXJzRnJvbVVyaSh1cmksIHBhcmFtZXRlcnMpXG4gICAge1xuICAgICAgICBmb3IgKGxldCBrZXkgaW4gcGFyYW1ldGVycylcbiAgICAgICAge1xuICAgICAgICAgICAgaWYgKHBhcmFtZXRlcnMuaGFzT3duUHJvcGVydHkoa2V5KSlcbiAgICAgICAgICAgIHtcbiAgICAgICAgICAgICAgICB1cmkgPSB0aGlzLnJlbW92ZVBhcmFtZXRlckZyb21VcmkodXJpLCBrZXkpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG5cbiAgICAgICAgcmV0dXJuIHVyaTtcbiAgICB9XG5cbiAgICBzdGF0aWMgcmVwbGFjZVBhcmFtZXRlckluVXJpKHVyaSwga2V5LCB2YWx1ZSlcbiAgICB7XG4gICAgICAgIHRoaXMuYWRkUGFyYW1ldGVyVG9VcmkodGhpcy5yZW1vdmVQYXJhbWV0ZXJGcm9tVXJpKHVyaSwga2V5KSwga2V5LCB2YWx1ZSk7XG4gICAgfVxuXG4gICAgc3RhdGljIHBhcnNlUXVlcnlTdHJpbmcocXVlcnlTdHJpbmcpIHtcbiAgICAgICAgcmV0dXJuIEpTT04ucGFyc2UoJ3tcIicgKyBkZWNvZGVVUkkocXVlcnlTdHJpbmcpLnJlcGxhY2UoL1wiL2csICdcXFxcXCInKS5yZXBsYWNlKC8mL2csICdcIixcIicpLnJlcGxhY2UoLz0vZywnXCI6XCInKSArICdcIn0nKVxuICAgIH1cblxuICAgIHN0YXRpYyBidWlsZFF1ZXJ5U3RyaW5nKHBhcmFtZXRlcnMpIHtcbiAgICAgICAgbGV0IHF1ZXJ5ID0gJyc7XG5cbiAgICAgICAgZm9yIChsZXQga2V5IGluIHBhcmFtZXRlcnMpIHtcbiAgICAgICAgICAgIGlmICgnJyAhPT0gcXVlcnkpIHtcbiAgICAgICAgICAgICAgICBxdWVyeSArPSAnJic7XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIHF1ZXJ5ICs9IGtleSArICc9JyArIHBhcmFtZXRlcnNba2V5XTtcbiAgICAgICAgfVxuXG4gICAgICAgIHJldHVybiBxdWVyeTtcbiAgICB9XG59XG5cbmV4cG9ydCBkZWZhdWx0IFVybFV0aWwiLCJmdW5jdGlvbiBwb2x5ZmlsbCh3aW5kb3cpIHtcbiAgY29uc3QgRWxlbWVudFByb3RvdHlwZSA9IHdpbmRvdy5FbGVtZW50LnByb3RvdHlwZTtcblxuICBpZiAodHlwZW9mIEVsZW1lbnRQcm90b3R5cGUubWF0Y2hlcyAhPT0gJ2Z1bmN0aW9uJykge1xuICAgIEVsZW1lbnRQcm90b3R5cGUubWF0Y2hlcyA9IEVsZW1lbnRQcm90b3R5cGUubXNNYXRjaGVzU2VsZWN0b3IgfHwgRWxlbWVudFByb3RvdHlwZS5tb3pNYXRjaGVzU2VsZWN0b3IgfHwgRWxlbWVudFByb3RvdHlwZS53ZWJraXRNYXRjaGVzU2VsZWN0b3IgfHwgZnVuY3Rpb24gbWF0Y2hlcyhzZWxlY3Rvcikge1xuICAgICAgbGV0IGVsZW1lbnQgPSB0aGlzO1xuICAgICAgY29uc3QgZWxlbWVudHMgPSAoZWxlbWVudC5kb2N1bWVudCB8fCBlbGVtZW50Lm93bmVyRG9jdW1lbnQpLnF1ZXJ5U2VsZWN0b3JBbGwoc2VsZWN0b3IpO1xuICAgICAgbGV0IGluZGV4ID0gMDtcblxuICAgICAgd2hpbGUgKGVsZW1lbnRzW2luZGV4XSAmJiBlbGVtZW50c1tpbmRleF0gIT09IGVsZW1lbnQpIHtcbiAgICAgICAgKytpbmRleDtcbiAgICAgIH1cblxuICAgICAgcmV0dXJuIEJvb2xlYW4oZWxlbWVudHNbaW5kZXhdKTtcbiAgICB9O1xuICB9XG5cbiAgaWYgKHR5cGVvZiBFbGVtZW50UHJvdG90eXBlLmNsb3Nlc3QgIT09ICdmdW5jdGlvbicpIHtcbiAgICBFbGVtZW50UHJvdG90eXBlLmNsb3Nlc3QgPSBmdW5jdGlvbiBjbG9zZXN0KHNlbGVjdG9yKSB7XG4gICAgICBsZXQgZWxlbWVudCA9IHRoaXM7XG5cbiAgICAgIHdoaWxlIChlbGVtZW50ICYmIGVsZW1lbnQubm9kZVR5cGUgPT09IDEpIHtcbiAgICAgICAgaWYgKGVsZW1lbnQubWF0Y2hlcyhzZWxlY3RvcikpIHtcbiAgICAgICAgICByZXR1cm4gZWxlbWVudDtcbiAgICAgICAgfVxuXG4gICAgICAgIGVsZW1lbnQgPSBlbGVtZW50LnBhcmVudE5vZGU7XG4gICAgICB9XG5cbiAgICAgIHJldHVybiBudWxsO1xuICAgIH07XG4gIH1cbn1cblxuZXhwb3J0IGRlZmF1bHQgcG9seWZpbGw7XG4vLyMgc291cmNlTWFwcGluZ1VSTD1pbmRleC5tanMubWFwXG4iLCJpZiAod2luZG93Lk5vZGVMaXN0ICYmICFOb2RlTGlzdC5wcm90b3R5cGUuZm9yRWFjaCkge1xyXG4gICAgTm9kZUxpc3QucHJvdG90eXBlLmZvckVhY2ggPSBmdW5jdGlvbiAoY2FsbGJhY2ssIHRoaXNBcmcpIHtcclxuICAgICAgICB0aGlzQXJnID0gdGhpc0FyZyB8fCB3aW5kb3c7XHJcbiAgICAgICAgZm9yICh2YXIgaSA9IDA7IGkgPCB0aGlzLmxlbmd0aDsgaSsrKSB7XHJcbiAgICAgICAgICAgIGNhbGxiYWNrLmNhbGwodGhpc0FyZywgdGhpc1tpXSwgaSwgdGhpcyk7XHJcbiAgICAgICAgfVxyXG4gICAgfTtcclxufVxyXG4iXSwic291cmNlUm9vdCI6IiJ9