import './polyfills';
import ArrayUtil from './array-util'
import DomUtil from './dom-util'
import EventUtil from './event-util'
import UrlUtil from './url-util'
import GeneralUtil from './general-util'
import AjaxUtil from './ajax-util'

let utilsBundle = {
    ajax: AjaxUtil,
    array: ArrayUtil,
    dom: DomUtil,
    event: EventUtil,
    url: UrlUtil,
    util: GeneralUtil
};

window.utilsBundle = utilsBundle;

export {
    utilsBundle,
    AjaxUtil,
    ArrayUtil,
    DomUtil,
    EventUtil,
    GeneralUtil,
    UrlUtil
}
