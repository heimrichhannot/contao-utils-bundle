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
/*! exports provided: utilsBundle, ArrayUtil, DomUtil, EventUtil, GeneralUtil, UrlUtil */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "utilsBundle", function() { return utilsBundle; });
/* harmony import */ var _array_util__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./array-util */ "./node_modules/@hundh/contao-utils-bundle/js/array-util.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "ArrayUtil", function() { return _array_util__WEBPACK_IMPORTED_MODULE_0__["default"]; });

/* harmony import */ var _dom_util__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./dom-util */ "./node_modules/@hundh/contao-utils-bundle/js/dom-util.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "DomUtil", function() { return _dom_util__WEBPACK_IMPORTED_MODULE_1__["default"]; });

/* harmony import */ var _event_util__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./event-util */ "./node_modules/@hundh/contao-utils-bundle/js/event-util.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "EventUtil", function() { return _event_util__WEBPACK_IMPORTED_MODULE_2__["default"]; });

/* harmony import */ var _url_util__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./url-util */ "./node_modules/@hundh/contao-utils-bundle/js/url-util.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "UrlUtil", function() { return _url_util__WEBPACK_IMPORTED_MODULE_3__["default"]; });

/* harmony import */ var _general_util__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./general-util */ "./node_modules/@hundh/contao-utils-bundle/js/general-util.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "GeneralUtil", function() { return _general_util__WEBPACK_IMPORTED_MODULE_4__["default"]; });






var utilsBundle = {
  array: _array_util__WEBPACK_IMPORTED_MODULE_0__["default"],
  dom: _dom_util__WEBPACK_IMPORTED_MODULE_1__["default"],
  event: _event_util__WEBPACK_IMPORTED_MODULE_2__["default"],
  url: _url_util__WEBPACK_IMPORTED_MODULE_3__["default"],
  util: _general_util__WEBPACK_IMPORTED_MODULE_4__["default"]
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
    value: function addDynamicEventListener(eventName, selector, callback, disableBubbling) {
      document.addEventListener(eventName, function (e) {
        var parents = _general_util__WEBPACK_IMPORTED_MODULE_1__["default"].isTruthy(disableBubbling) ? [e.target] : _dom_util__WEBPACK_IMPORTED_MODULE_0__["default"].getAllParentNodes(e.target);

        if (!Array.isArray(parents)) {
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
  }]);

  return UrlUtil;
}();

/* harmony default export */ __webpack_exports__["default"] = (UrlUtil);

/***/ })

/******/ });
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vd2VicGFjay9ib290c3RyYXAiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL0BodW5kaC9jb250YW8tdXRpbHMtYnVuZGxlL2pzL2FycmF5LXV0aWwuanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL0BodW5kaC9jb250YW8tdXRpbHMtYnVuZGxlL2pzL2NvbnRhby11dGlscy1idW5kbGUuanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL0BodW5kaC9jb250YW8tdXRpbHMtYnVuZGxlL2pzL2RvbS11dGlsLmpzIiwid2VicGFjazovLy8uL25vZGVfbW9kdWxlcy9AaHVuZGgvY29udGFvLXV0aWxzLWJ1bmRsZS9qcy9ldmVudC11dGlsLmpzIiwid2VicGFjazovLy8uL25vZGVfbW9kdWxlcy9AaHVuZGgvY29udGFvLXV0aWxzLWJ1bmRsZS9qcy9nZW5lcmFsLXV0aWwuanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL0BodW5kaC9jb250YW8tdXRpbHMtYnVuZGxlL2pzL3VybC11dGlsLmpzIl0sIm5hbWVzIjpbIkFycmF5VXRpbCIsInZhbHVlIiwiYXJyYXkiLCJpIiwibGVuZ3RoIiwiSlNPTiIsInN0cmluZ2lmeSIsInNwbGljZSIsInV0aWxzQnVuZGxlIiwiZG9tIiwiRG9tVXRpbCIsImV2ZW50IiwiRXZlbnRVdGlsIiwidXJsIiwiVXJsVXRpbCIsInV0aWwiLCJHZW5lcmFsVXRpbCIsIndpbmRvdyIsImVsZW1lbnQiLCJub3RyaW0iLCJyZXN1bHQiLCJjbG9uZSIsImNoaWxkcmVuIiwicmVtb3ZlIiwidGV4dCIsInRyaW0iLCJvZmZzZXQiLCJkZWxheSIsImZvcmNlIiwicmVjdCIsImdldEJvdW5kaW5nQ2xpZW50UmVjdCIsInNjcm9sbFBvc2l0aW9uIiwidG9wIiwicGFnZVlPZmZzZXQiLCJzZXRUaW1lb3V0IiwiZWxlbWVudEluVmlld3BvcnQiLCJpc1Ntb290aFNjcm9sbFN1cHBvcnRlZCIsImRvY3VtZW50IiwiZG9jdW1lbnRFbGVtZW50Iiwic3R5bGUiLCJzY3JvbGxUbyIsImVsIiwib2Zmc2V0VG9wIiwibGVmdCIsIm9mZnNldExlZnQiLCJ3aWR0aCIsIm9mZnNldFdpZHRoIiwiaGVpZ2h0Iiwib2Zmc2V0SGVpZ2h0Iiwib2Zmc2V0UGFyZW50IiwiaW5uZXJIZWlnaHQiLCJwYWdlWE9mZnNldCIsImlubmVyV2lkdGgiLCJub2RlIiwicGFyZW50cyIsInVuc2hpZnQiLCJwYXJlbnROb2RlIiwiZXZlbnROYW1lIiwic2VsZWN0b3IiLCJjYWxsYmFjayIsImRpc2FibGVCdWJibGluZyIsImFkZEV2ZW50TGlzdGVuZXIiLCJlIiwiaXNUcnV0aHkiLCJ0YXJnZXQiLCJnZXRBbGxQYXJlbnROb2RlcyIsIkFycmF5IiwiaXNBcnJheSIsInJldmVyc2UiLCJmb3JFYWNoIiwiaXRlbSIsIm1hdGNoZXMiLCJmdW5jIiwiYXBwbHkiLCJwcm90b3R5cGUiLCJzbGljZSIsImNhbGwiLCJhcmd1bWVudHMiLCJuYW1lIiwibG9jYXRpb24iLCJocmVmIiwicmVwbGFjZSIsInJlZ2V4IiwiUmVnRXhwIiwicmVzdWx0cyIsImV4ZWMiLCJkZWNvZGVVUklDb21wb25lbnQiLCJ1cmkiLCJrZXkiLCJyZSIsImhhc2giLCJ0ZXN0Iiwic3BsaXQiLCJzZXBhcmF0b3IiLCJpbmRleE9mIiwicGFyYW1ldGVycyIsImhhc093blByb3BlcnR5IiwiYWRkUGFyYW1ldGVyVG9VcmkiLCJwYXJhbWV0ZXIiLCJ1cmlwYXJ0cyIsInByZWZpeCIsImVuY29kZVVSSUNvbXBvbmVudCIsInBhcnMiLCJsYXN0SW5kZXhPZiIsImpvaW4iLCJyZW1vdmVQYXJhbWV0ZXJGcm9tVXJpIiwicXVlcnlTdHJpbmciLCJwYXJzZSIsImRlY29kZVVSSSJdLCJtYXBwaW5ncyI6IjtBQUFBO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFD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QUFSLEdBQWMsR0FBZCxHQUFvQkcsSUFBSSxDQUFDRSxJQUFMLENBQVUsR0FBVixDQUExQjtBQUNBLGVBQU9qQixHQUFQO0FBQ0gsT0FsQkQsTUFvQkE7QUFDSSxlQUFPQSxHQUFQO0FBQ0g7QUFDSjs7OzRDQUU4QkEsRyxFQUFLUSxVLEVBQ3BDO0FBQ0ksV0FBSyxJQUFJUCxHQUFULElBQWdCTyxVQUFoQixFQUNBO0FBQ0ksWUFBSUEsVUFBVSxDQUFDQyxjQUFYLENBQTBCUixHQUExQixDQUFKLEVBQ0E7QUFDSUQsYUFBRyxHQUFHLEtBQUtrQixzQkFBTCxDQUE0QmxCLEdBQTVCLEVBQWlDQyxHQUFqQyxDQUFOO0FBQ0g7QUFDSjs7QUFFRCxhQUFPRCxHQUFQO0FBQ0g7OzswQ0FFNEJBLEcsRUFBS0MsRyxFQUFLdkYsSyxFQUN2QztBQUNJLFdBQUtnRyxpQkFBTCxDQUF1QixLQUFLUSxzQkFBTCxDQUE0QmxCLEdBQTVCLEVBQWlDQyxHQUFqQyxDQUF2QixFQUE4REEsR0FBOUQsRUFBbUV2RixLQUFuRTtBQUNIOzs7cUNBRXVCeUcsVyxFQUFhO0FBQ2pDLGFBQU9yRyxJQUFJLENBQUNzRyxLQUFMLENBQVcsT0FBT0MsU0FBUyxDQUFDRixXQUFELENBQVQsQ0FBdUJ6QixPQUF2QixDQUErQixJQUEvQixFQUFxQyxLQUFyQyxFQUE0Q0EsT0FBNUMsQ0FBb0QsSUFBcEQsRUFBMEQsS0FBMUQsRUFBaUVBLE9BQWpFLENBQXlFLElBQXpFLEVBQThFLEtBQTlFLENBQVAsR0FBOEYsSUFBekcsQ0FBUDtBQUNIOzs7Ozs7QUFHVW5FLHNFQUFmLEUiLCJmaWxlIjoiY29udGFvLXV0aWxzLWJ1bmRsZS5qcyIsInNvdXJjZXNDb250ZW50IjpbIiBcdC8vIFRoZSBtb2R1bGUgY2FjaGVcbiBcdHZhciBpbnN0YWxsZWRNb2R1bGVzID0ge307XG5cbiBcdC8vIFRoZSByZXF1aXJlIGZ1bmN0aW9uXG4gXHRmdW5jdGlvbiBfX3dlYnBhY2tfcmVxdWlyZV9fKG1vZHVsZUlkKSB7XG5cbiBcdFx0Ly8gQ2hlY2sgaWYgbW9kdWxlIGlzIGluIGNhY2hlXG4gXHRcdGlmKGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdKSB7XG4gXHRcdFx0cmV0dXJuIGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdLmV4cG9ydHM7XG4gXHRcdH1cbiBcdFx0Ly8gQ3JlYXRlIGEgbmV3IG1vZHVsZSAoYW5kIHB1dCBpdCBpbnRvIHRoZSBjYWNoZSlcbiBcdFx0dmFyIG1vZHVsZSA9IGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdID0ge1xuIFx0XHRcdGk6IG1vZHVsZUlkLFxuIFx0XHRcdGw6IGZhbHNlLFxuIFx0XHRcdGV4cG9ydHM6IHt9XG4gXHRcdH07XG5cbiBcdFx0Ly8gRXhlY3V0ZSB0aGUgbW9kdWxlIGZ1bmN0aW9uXG4gXHRcdG1vZHVsZXNbbW9kdWxlSWRdLmNhbGwobW9kdWxlLmV4cG9ydHMsIG1vZHVsZSwgbW9kdWxlLmV4cG9ydHMsIF9fd2VicGFja19yZXF1aXJlX18pO1xuXG4gXHRcdC8vIEZsYWcgdGhlIG1vZHVsZSBhcyBsb2FkZWRcbiBcdFx0bW9kdWxlLmwgPSB0cnVlO1xuXG4gXHRcdC8vIFJldHVybiB0aGUgZXhwb3J0cyBvZiB0aGUgbW9kdWxlXG4gXHRcdHJldHVybiBtb2R1bGUuZXhwb3J0cztcbiBcdH1cblxuXG4gXHQvLyBleHBvc2UgdGhlIG1vZHVsZXMgb2JqZWN0IChfX3dlYnBhY2tfbW9kdWxlc19fKVxuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5tID0gbW9kdWxlcztcblxuIFx0Ly8gZXhwb3NlIHRoZSBtb2R1bGUgY2FjaGVcbiBcdF9fd2VicGFja19yZXF1aXJlX18uYyA9IGluc3RhbGxlZE1vZHVsZXM7XG5cbiBcdC8vIGRlZmluZSBnZXR0ZXIgZnVuY3Rpb24gZm9yIGhhcm1vbnkgZXhwb3J0c1xuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5kID0gZnVuY3Rpb24oZXhwb3J0cywgbmFtZSwgZ2V0dGVyKSB7XG4gXHRcdGlmKCFfX3dlYnBhY2tfcmVxdWlyZV9fLm8oZXhwb3J0cywgbmFtZSkpIHtcbiBcdFx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgbmFtZSwgeyBlbnVtZXJhYmxlOiB0cnVlLCBnZXQ6IGdldHRlciB9KTtcbiBcdFx0fVxuIFx0fTtcblxuIFx0Ly8gZGVmaW5lIF9fZXNNb2R1bGUgb24gZXhwb3J0c1xuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5yID0gZnVuY3Rpb24oZXhwb3J0cykge1xuIFx0XHRpZih0eXBlb2YgU3ltYm9sICE9PSAndW5kZWZpbmVkJyAmJiBTeW1ib2wudG9TdHJpbmdUYWcpIHtcbiBcdFx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgU3ltYm9sLnRvU3RyaW5nVGFnLCB7IHZhbHVlOiAnTW9kdWxlJyB9KTtcbiBcdFx0fVxuIFx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgJ19fZXNNb2R1bGUnLCB7IHZhbHVlOiB0cnVlIH0pO1xuIFx0fTtcblxuIFx0Ly8gY3JlYXRlIGEgZmFrZSBuYW1lc3BhY2Ugb2JqZWN0XG4gXHQvLyBtb2RlICYgMTogdmFsdWUgaXMgYSBtb2R1bGUgaWQsIHJlcXVpcmUgaXRcbiBcdC8vIG1vZGUgJiAyOiBtZXJnZSBhbGwgcHJvcGVydGllcyBvZiB2YWx1ZSBpbnRvIHRoZSBuc1xuIFx0Ly8gbW9kZSAmIDQ6IHJldHVybiB2YWx1ZSB3aGVuIGFscmVhZHkgbnMgb2JqZWN0XG4gXHQvLyBtb2RlICYgOHwxOiBiZWhhdmUgbGlrZSByZXF1aXJlXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLnQgPSBmdW5jdGlvbih2YWx1ZSwgbW9kZSkge1xuIFx0XHRpZihtb2RlICYgMSkgdmFsdWUgPSBfX3dlYnBhY2tfcmVxdWlyZV9fKHZhbHVlKTtcbiBcdFx0aWYobW9kZSAmIDgpIHJldHVybiB2YWx1ZTtcbiBcdFx0aWYoKG1vZGUgJiA0KSAmJiB0eXBlb2YgdmFsdWUgPT09ICdvYmplY3QnICYmIHZhbHVlICYmIHZhbHVlLl9fZXNNb2R1bGUpIHJldHVybiB2YWx1ZTtcbiBcdFx0dmFyIG5zID0gT2JqZWN0LmNyZWF0ZShudWxsKTtcbiBcdFx0X193ZWJwYWNrX3JlcXVpcmVfXy5yKG5zKTtcbiBcdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KG5zLCAnZGVmYXVsdCcsIHsgZW51bWVyYWJsZTogdHJ1ZSwgdmFsdWU6IHZhbHVlIH0pO1xuIFx0XHRpZihtb2RlICYgMiAmJiB0eXBlb2YgdmFsdWUgIT0gJ3N0cmluZycpIGZvcih2YXIga2V5IGluIHZhbHVlKSBfX3dlYnBhY2tfcmVxdWlyZV9fLmQobnMsIGtleSwgZnVuY3Rpb24oa2V5KSB7IHJldHVybiB2YWx1ZVtrZXldOyB9LmJpbmQobnVsbCwga2V5KSk7XG4gXHRcdHJldHVybiBucztcbiBcdH07XG5cbiBcdC8vIGdldERlZmF1bHRFeHBvcnQgZnVuY3Rpb24gZm9yIGNvbXBhdGliaWxpdHkgd2l0aCBub24taGFybW9ueSBtb2R1bGVzXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLm4gPSBmdW5jdGlvbihtb2R1bGUpIHtcbiBcdFx0dmFyIGdldHRlciA9IG1vZHVsZSAmJiBtb2R1bGUuX19lc01vZHVsZSA/XG4gXHRcdFx0ZnVuY3Rpb24gZ2V0RGVmYXVsdCgpIHsgcmV0dXJuIG1vZHVsZVsnZGVmYXVsdCddOyB9IDpcbiBcdFx0XHRmdW5jdGlvbiBnZXRNb2R1bGVFeHBvcnRzKCkgeyByZXR1cm4gbW9kdWxlOyB9O1xuIFx0XHRfX3dlYnBhY2tfcmVxdWlyZV9fLmQoZ2V0dGVyLCAnYScsIGdldHRlcik7XG4gXHRcdHJldHVybiBnZXR0ZXI7XG4gXHR9O1xuXG4gXHQvLyBPYmplY3QucHJvdG90eXBlLmhhc093blByb3BlcnR5LmNhbGxcbiBcdF9fd2VicGFja19yZXF1aXJlX18ubyA9IGZ1bmN0aW9uKG9iamVjdCwgcHJvcGVydHkpIHsgcmV0dXJuIE9iamVjdC5wcm90b3R5cGUuaGFzT3duUHJvcGVydHkuY2FsbChvYmplY3QsIHByb3BlcnR5KTsgfTtcblxuIFx0Ly8gX193ZWJwYWNrX3B1YmxpY19wYXRoX19cbiBcdF9fd2VicGFja19yZXF1aXJlX18ucCA9IFwiL3B1YmxpYy9qcy9cIjtcblxuXG4gXHQvLyBMb2FkIGVudHJ5IG1vZHVsZSBhbmQgcmV0dXJuIGV4cG9ydHNcbiBcdHJldHVybiBfX3dlYnBhY2tfcmVxdWlyZV9fKF9fd2VicGFja19yZXF1aXJlX18ucyA9IFwiLi9ub2RlX21vZHVsZXMvQGh1bmRoL2NvbnRhby11dGlscy1idW5kbGUvanMvY29udGFvLXV0aWxzLWJ1bmRsZS5qc1wiKTtcbiIsImNsYXNzIEFycmF5VXRpbCB7XG4gICAgc3RhdGljIHJlbW92ZUZyb21BcnJheSh2YWx1ZSwgYXJyYXkpIHtcbiAgICAgICAgZm9yICh2YXIgaSA9IDA7IGkgPCBhcnJheS5sZW5ndGg7IGkrKykge1xuICAgICAgICAgICAgaWYgKEpTT04uc3RyaW5naWZ5KHZhbHVlKSA9PSBKU09OLnN0cmluZ2lmeShhcnJheVtpXSkpIHtcbiAgICAgICAgICAgICAgICBhcnJheS5zcGxpY2UoaSwgMSk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cbiAgICAgICAgcmV0dXJuIGFycmF5O1xuICAgIH1cbn1cblxuZXhwb3J0IGRlZmF1bHQgQXJyYXlVdGlsIiwiaW1wb3J0IEFycmF5VXRpbCBmcm9tICcuL2FycmF5LXV0aWwnXG5pbXBvcnQgRG9tVXRpbCBmcm9tICcuL2RvbS11dGlsJ1xuaW1wb3J0IEV2ZW50VXRpbCBmcm9tICcuL2V2ZW50LXV0aWwnXG5pbXBvcnQgVXJsVXRpbCBmcm9tICcuL3VybC11dGlsJ1xuaW1wb3J0IEdlbmVyYWxVdGlsIGZyb20gJy4vZ2VuZXJhbC11dGlsJ1xuXG5sZXQgdXRpbHNCdW5kbGUgPSB7XG4gICAgYXJyYXk6IEFycmF5VXRpbCxcbiAgICBkb206IERvbVV0aWwsXG4gICAgZXZlbnQ6IEV2ZW50VXRpbCxcbiAgICB1cmw6IFVybFV0aWwsXG4gICAgdXRpbDogR2VuZXJhbFV0aWxcbn07XG5cbndpbmRvdy51dGlsc0J1bmRsZSA9IHV0aWxzQnVuZGxlO1xuXG5leHBvcnQge1xuICAgIHV0aWxzQnVuZGxlLFxuICAgIEFycmF5VXRpbCxcbiAgICBEb21VdGlsLFxuICAgIEV2ZW50VXRpbCxcbiAgICBHZW5lcmFsVXRpbCxcbiAgICBVcmxVdGlsXG59XG4iLCJpbXBvcnQgQXJyYXlVdGlsIGZyb20gJy4vYXJyYXktdXRpbCc7XG5cbmNsYXNzIERvbVV0aWwge1xuICAgIHN0YXRpYyBnZXRUZXh0V2l0aG91dENoaWxkcmVuKGVsZW1lbnQsIG5vdHJpbSkge1xuICAgICAgICBsZXQgcmVzdWx0ID0gZWxlbWVudC5jbG9uZSgpO1xuICAgICAgICByZXN1bHQuY2hpbGRyZW4oKS5yZW1vdmUoKTtcblxuICAgICAgICBpZiAodHlwZW9mIG5vdHJpbSAhPT0gJ3VuZGVmaW5lZCcgJiYgbm90cmltID09PSB0cnVlKSB7XG4gICAgICAgICAgICByZXR1cm4gcmVzdWx0LnRleHQoKTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIHJldHVybiByZXN1bHQudGV4dCgpLnRyaW0oKTtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIHN0YXRpYyBzY3JvbGxUbyhlbGVtZW50LCBvZmZzZXQgPSAwLCBkZWxheSA9IDAsIGZvcmNlID0gZmFsc2UpIHtcbiAgICAgICAgbGV0IHJlY3QgPSBlbGVtZW50LmdldEJvdW5kaW5nQ2xpZW50UmVjdCgpO1xuICAgICAgICBsZXQgc2Nyb2xsUG9zaXRpb24gPSAocmVjdC50b3AgKyB3aW5kb3cucGFnZVlPZmZzZXQgLSBvZmZzZXQpO1xuICAgICAgICBzZXRUaW1lb3V0KCgpID0+IHtcbiAgICAgICAgICAgIGlmICghdGhpcy5lbGVtZW50SW5WaWV3cG9ydChlbGVtZW50KSB8fCBmb3JjZSA9PT0gdHJ1ZSlcbiAgICAgICAgICAgIHtcbiAgICAgICAgICAgICAgICB2YXIgaXNTbW9vdGhTY3JvbGxTdXBwb3J0ZWQgPSAnc2Nyb2xsQmVoYXZpb3InIGluIGRvY3VtZW50LmRvY3VtZW50RWxlbWVudC5zdHlsZTtcbiAgICAgICAgICAgICAgICBpZiAoaXNTbW9vdGhTY3JvbGxTdXBwb3J0ZWQpXG4gICAgICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgICAgICB3aW5kb3cuc2Nyb2xsVG8oe1xuICAgICAgICAgICAgICAgICAgICAgICAgJ3RvcCc6IHNjcm9sbFBvc2l0aW9uLFxuICAgICAgICAgICAgICAgICAgICAgICAgJ2JlaGF2aW9yJzogJ3Ntb290aCcsXG4gICAgICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICBlbHNlIHtcbiAgICAgICAgICAgICAgICAgICAgd2luZG93LnNjcm9sbFRvKDAsIHNjcm9sbFBvc2l0aW9uKTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9XG4gICAgICAgIH0sIGRlbGF5KTtcbiAgICB9XG5cbiAgICBzdGF0aWMgZWxlbWVudEluVmlld3BvcnQoZWwpIHtcbiAgICAgICAgbGV0IHRvcCA9IGVsLm9mZnNldFRvcDtcbiAgICAgICAgbGV0IGxlZnQgPSBlbC5vZmZzZXRMZWZ0O1xuICAgICAgICBsZXQgd2lkdGggPSBlbC5vZmZzZXRXaWR0aDtcbiAgICAgICAgbGV0IGhlaWdodCA9IGVsLm9mZnNldEhlaWdodDtcblxuICAgICAgICB3aGlsZSAoZWwub2Zmc2V0UGFyZW50KSB7XG4gICAgICAgICAgICBlbCA9IGVsLm9mZnNldFBhcmVudDtcbiAgICAgICAgICAgIHRvcCArPSBlbC5vZmZzZXRUb3A7XG4gICAgICAgICAgICBsZWZ0ICs9IGVsLm9mZnNldExlZnQ7XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gKFxuICAgICAgICAgICAgdG9wIDwgKHdpbmRvdy5wYWdlWU9mZnNldCArIHdpbmRvdy5pbm5lckhlaWdodCkgJiZcbiAgICAgICAgICAgIGxlZnQgPCAod2luZG93LnBhZ2VYT2Zmc2V0ICsgd2luZG93LmlubmVyV2lkdGgpICYmXG4gICAgICAgICAgICAodG9wICsgaGVpZ2h0KSA+IHdpbmRvdy5wYWdlWU9mZnNldCAmJlxuICAgICAgICAgICAgKGxlZnQgKyB3aWR0aCkgPiB3aW5kb3cucGFnZVhPZmZzZXRcbiAgICAgICAgKTtcbiAgICB9XG5cbiAgICBzdGF0aWMgZ2V0QWxsUGFyZW50Tm9kZXMobm9kZSkge1xuICAgICAgICB2YXIgcGFyZW50cyA9IFtdO1xuXG4gICAgICAgIHdoaWxlIChub2RlKSB7XG4gICAgICAgICAgICBwYXJlbnRzLnVuc2hpZnQobm9kZSk7XG4gICAgICAgICAgICBub2RlID0gbm9kZS5wYXJlbnROb2RlO1xuICAgICAgICB9XG5cbiAgICAgICAgZm9yICh2YXIgaSA9IDA7IGkgPCBwYXJlbnRzLmxlbmd0aDsgaSsrKSB7XG4gICAgICAgICAgICBpZiAocGFyZW50c1tpXSA9PT0gZG9jdW1lbnQpIHtcbiAgICAgICAgICAgICAgICBwYXJlbnRzLnNwbGljZShpLCAxKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuXG4gICAgICAgIHJldHVybiBwYXJlbnRzO1xuICAgIH1cbn1cblxuZXhwb3J0IGRlZmF1bHQgRG9tVXRpbCIsImltcG9ydCBEb21VdGlsIGZyb20gJy4vZG9tLXV0aWwnO1xuaW1wb3J0IEdlbmVyYWxVdGlsIGZyb20gJy4vZ2VuZXJhbC11dGlsJ1xuXG5jbGFzcyBFdmVudFV0aWwge1xuICAgIHN0YXRpYyBhZGREeW5hbWljRXZlbnRMaXN0ZW5lcihldmVudE5hbWUsIHNlbGVjdG9yLCBjYWxsYmFjaywgZGlzYWJsZUJ1YmJsaW5nKSB7XG4gICAgICAgIGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoZXZlbnROYW1lLCBmdW5jdGlvbihlKSB7XG4gICAgICAgICAgICB2YXIgcGFyZW50cyA9IChHZW5lcmFsVXRpbC5pc1RydXRoeShkaXNhYmxlQnViYmxpbmcpID8gW2UudGFyZ2V0XSA6IERvbVV0aWwuZ2V0QWxsUGFyZW50Tm9kZXMoZS50YXJnZXQpKTtcblxuICAgICAgICAgICAgaWYgKCFBcnJheS5pc0FycmF5KHBhcmVudHMpKVxuICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgcGFyZW50cy5yZXZlcnNlKCkuZm9yRWFjaChmdW5jdGlvbihpdGVtKSB7XG4gICAgICAgICAgICAgICAgaWYgKGl0ZW0gJiYgaXRlbS5tYXRjaGVzKHNlbGVjdG9yKSkge1xuICAgICAgICAgICAgICAgICAgICBjYWxsYmFjayhpdGVtLCBlKTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfSk7XG4gICAgfVxufVxuXG5leHBvcnQgZGVmYXVsdCBFdmVudFV0aWwiLCJjbGFzcyBHZW5lcmFsVXRpbCB7XG4gICAgc3RhdGljIGlzVHJ1dGh5KHZhbHVlKSB7XG4gICAgICAgIHJldHVybiB0eXBlb2YgdmFsdWUgIT09ICd1bmRlZmluZWQnICYmIHZhbHVlICE9PSBudWxsO1xuICAgIH1cblxuICAgIHN0YXRpYyBjYWxsKGZ1bmMpIHtcbiAgICAgICAgaWYgKHR5cGVvZiBmdW5jID09PSAnZnVuY3Rpb24nKSB7XG4gICAgICAgICAgICBmdW5jLmFwcGx5KHRoaXMsIEFycmF5LnByb3RvdHlwZS5zbGljZS5jYWxsKGFyZ3VtZW50cywgMSkpO1xuICAgICAgICB9XG4gICAgfVxufVxuXG5leHBvcnQgZGVmYXVsdCBHZW5lcmFsVXRpbCIsImNsYXNzIFVybFV0aWwge1xuICAgIHN0YXRpYyBnZXRQYXJhbWV0ZXJCeU5hbWUobmFtZSwgdXJsKVxuICAgIHtcbiAgICAgICAgaWYgKCF1cmwpXG4gICAgICAgIHtcbiAgICAgICAgICAgIHVybCA9IHdpbmRvdy5sb2NhdGlvbi5ocmVmO1xuICAgICAgICB9XG5cbiAgICAgICAgbmFtZSA9IG5hbWUucmVwbGFjZSgvW1xcW1xcXV0vZywgXCJcXFxcJCZcIik7XG5cbiAgICAgICAgbGV0IHJlZ2V4ID0gbmV3IFJlZ0V4cChcIls/Jl1cIiArIG5hbWUgKyBcIig9KFteJiNdKil8JnwjfCQpXCIpLFxuICAgICAgICAgICAgcmVzdWx0cyA9IHJlZ2V4LmV4ZWModXJsKTtcblxuICAgICAgICBpZiAoIXJlc3VsdHMpXG4gICAgICAgIHtcbiAgICAgICAgICAgIHJldHVybiBudWxsO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKCFyZXN1bHRzWzJdKVxuICAgICAgICB7XG4gICAgICAgICAgICByZXR1cm4gJyc7XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gZGVjb2RlVVJJQ29tcG9uZW50KHJlc3VsdHNbMl0ucmVwbGFjZSgvXFwrL2csIFwiIFwiKSk7XG4gICAgfVxuXG4gICAgc3RhdGljIGFkZFBhcmFtZXRlclRvVXJpKHVyaSwga2V5LCB2YWx1ZSlcbiAgICB7XG4gICAgICAgIGlmICghdXJpKVxuICAgICAgICB7XG4gICAgICAgICAgICB1cmkgPSB3aW5kb3cubG9jYXRpb24uaHJlZjtcbiAgICAgICAgfVxuXG4gICAgICAgIGxldCByZSA9IG5ldyBSZWdFeHAoXCIoWz8mXSlcIiArIGtleSArIFwiPS4qPygmfCN8JCkoLiopXCIsIFwiZ2lcIiksXG4gICAgICAgICAgICBoYXNoO1xuXG4gICAgICAgIGlmIChyZS50ZXN0KHVyaSkpXG4gICAgICAgIHtcbiAgICAgICAgICAgIGlmICh0eXBlb2YgdmFsdWUgIT09ICd1bmRlZmluZWQnICYmIHZhbHVlICE9PSBudWxsKVxuICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIHJldHVybiB1cmkucmVwbGFjZShyZSwgJyQxJyArIGtleSArIFwiPVwiICsgdmFsdWUgKyAnJDIkMycpO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgZWxzZVxuICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIGhhc2ggPSB1cmkuc3BsaXQoJyMnKTtcbiAgICAgICAgICAgICAgICB1cmkgPSBoYXNoWzBdLnJlcGxhY2UocmUsICckMSQzJykucmVwbGFjZSgvKCZ8XFw/KSQvLCAnJyk7XG5cbiAgICAgICAgICAgICAgICBpZiAodHlwZW9mIGhhc2hbMV0gIT09ICd1bmRlZmluZWQnICYmIGhhc2hbMV0gIT09IG51bGwpXG4gICAgICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgICAgICB1cmkgKz0gJyMnICsgaGFzaFsxXTtcbiAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICByZXR1cm4gdXJpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgICAgIGVsc2VcbiAgICAgICAge1xuICAgICAgICAgICAgaWYgKHR5cGVvZiB2YWx1ZSAhPT0gJ3VuZGVmaW5lZCcgJiYgdmFsdWUgIT09IG51bGwpXG4gICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgbGV0IHNlcGFyYXRvciA9IHVyaS5pbmRleE9mKCc/JykgIT09IC0xID8gJyYnIDogJz8nO1xuICAgICAgICAgICAgICAgIGhhc2ggPSB1cmkuc3BsaXQoJyMnKTtcbiAgICAgICAgICAgICAgICB1cmkgPSBoYXNoWzBdICsgc2VwYXJhdG9yICsga2V5ICsgJz0nICsgdmFsdWU7XG5cbiAgICAgICAgICAgICAgICBpZiAodHlwZW9mIGhhc2hbMV0gIT09ICd1bmRlZmluZWQnICYmIGhhc2hbMV0gIT09IG51bGwpXG4gICAgICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgICAgICB1cmkgKz0gJyMnICsgaGFzaFsxXTtcbiAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICByZXR1cm4gdXJpO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgZWxzZVxuICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIHJldHVybiB1cmk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICBzdGF0aWMgYWRkUGFyYW1ldGVyc1RvVXJpKHVyaSwgcGFyYW1ldGVycylcbiAgICB7XG4gICAgICAgIGZvciAobGV0IGtleSBpbiBwYXJhbWV0ZXJzKVxuICAgICAgICB7XG4gICAgICAgICAgICBpZiAocGFyYW1ldGVycy5oYXNPd25Qcm9wZXJ0eShrZXkpKVxuICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIHVyaSA9IHRoaXMuYWRkUGFyYW1ldGVyVG9VcmkodXJpLCBrZXksIHBhcmFtZXRlcnNba2V5XSk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gdXJpO1xuICAgIH1cblxuICAgIHN0YXRpYyByZW1vdmVQYXJhbWV0ZXJGcm9tVXJpKHVyaSwgcGFyYW1ldGVyKVxuICAgIHtcbiAgICAgICAgLy9wcmVmZXIgdG8gdXNlIGwuc2VhcmNoIGlmIHlvdSBoYXZlIGEgbG9jYXRpb24vbGluayBvYmplY3RcbiAgICAgICAgbGV0IHVyaXBhcnRzID0gdXJpLnNwbGl0KCc/Jyk7XG5cbiAgICAgICAgaWYgKHVyaXBhcnRzLmxlbmd0aCA+PSAyKVxuICAgICAgICB7XG5cbiAgICAgICAgICAgIGxldCBwcmVmaXggPSBlbmNvZGVVUklDb21wb25lbnQocGFyYW1ldGVyKSArICc9JztcbiAgICAgICAgICAgIGxldCBwYXJzID0gdXJpcGFydHNbMV0uc3BsaXQoL1smO10vZyk7XG5cbiAgICAgICAgICAgIC8vcmV2ZXJzZSBpdGVyYXRpb24gYXMgbWF5IGJlIGRlc3RydWN0aXZlXG4gICAgICAgICAgICBmb3IgKGxldCBpID0gcGFycy5sZW5ndGg7IGktLSA+IDA7KVxuICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIC8vaWRpb20gZm9yIHN0cmluZy5zdGFydHNXaXRoXG4gICAgICAgICAgICAgICAgaWYgKHBhcnNbaV0ubGFzdEluZGV4T2YocHJlZml4LCAwKSAhPT0gLTEpXG4gICAgICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgICAgICBwYXJzLnNwbGljZShpLCAxKTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIHVyaSA9IHVyaXBhcnRzWzBdICsgJz8nICsgcGFycy5qb2luKCcmJyk7XG4gICAgICAgICAgICByZXR1cm4gdXJpO1xuICAgICAgICB9XG4gICAgICAgIGVsc2VcbiAgICAgICAge1xuICAgICAgICAgICAgcmV0dXJuIHVyaTtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIHN0YXRpYyByZW1vdmVQYXJhbWV0ZXJzRnJvbVVyaSh1cmksIHBhcmFtZXRlcnMpXG4gICAge1xuICAgICAgICBmb3IgKGxldCBrZXkgaW4gcGFyYW1ldGVycylcbiAgICAgICAge1xuICAgICAgICAgICAgaWYgKHBhcmFtZXRlcnMuaGFzT3duUHJvcGVydHkoa2V5KSlcbiAgICAgICAgICAgIHtcbiAgICAgICAgICAgICAgICB1cmkgPSB0aGlzLnJlbW92ZVBhcmFtZXRlckZyb21VcmkodXJpLCBrZXkpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG5cbiAgICAgICAgcmV0dXJuIHVyaTtcbiAgICB9XG5cbiAgICBzdGF0aWMgcmVwbGFjZVBhcmFtZXRlckluVXJpKHVyaSwga2V5LCB2YWx1ZSlcbiAgICB7XG4gICAgICAgIHRoaXMuYWRkUGFyYW1ldGVyVG9VcmkodGhpcy5yZW1vdmVQYXJhbWV0ZXJGcm9tVXJpKHVyaSwga2V5KSwga2V5LCB2YWx1ZSk7XG4gICAgfVxuXG4gICAgc3RhdGljIHBhcnNlUXVlcnlTdHJpbmcocXVlcnlTdHJpbmcpIHtcbiAgICAgICAgcmV0dXJuIEpTT04ucGFyc2UoJ3tcIicgKyBkZWNvZGVVUkkocXVlcnlTdHJpbmcpLnJlcGxhY2UoL1wiL2csICdcXFxcXCInKS5yZXBsYWNlKC8mL2csICdcIixcIicpLnJlcGxhY2UoLz0vZywnXCI6XCInKSArICdcIn0nKVxuICAgIH1cbn1cblxuZXhwb3J0IGRlZmF1bHQgVXJsVXRpbCJdLCJzb3VyY2VSb290IjoiIn0=