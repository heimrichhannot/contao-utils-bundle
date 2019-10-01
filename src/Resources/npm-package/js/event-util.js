import DomUtil from './dom-util';
import GeneralUtil from './general-util'

class EventUtil {
    static addDynamicEventListener(eventName, selector, callback, scope, disableBubbling) {
        if (typeof scope === 'undefined') {
            scope = document;
        }

        scope.addEventListener(eventName, function(e) {

            let parents;

            if (GeneralUtil.isTruthy(disableBubbling)) {
                parents = [e.target];
            } else if (e.target !== document) {
                parents = DomUtil.getAllParentNodes(e.target);
            }

            // for instance window load/resize event
            if (!Array.isArray(parents)) {
                document.querySelectorAll(selector).forEach(function(item) {
                    callback(item, e);
                });
                return;
            }

            parents.reverse().forEach(function(item) {
                if (item && item.matches(selector)) {
                    callback(item, e);
                }
            });
        });
    }

    static createEventObject(type, bubbles = false, cancelable = false, composed = false) {
        if (typeof (Event) === 'function') {
            return new Event(type, {
                bubbles: bubbles,
                cancelable: cancelable,
                composed: composed
            });
        } else {
            let event = document.createEvent('Event');
            event.initEvent(type, bubbles, cancelable);

            return event;
        }
    }
}

export default EventUtil
