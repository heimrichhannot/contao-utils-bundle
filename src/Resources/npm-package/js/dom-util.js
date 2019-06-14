import ArrayUtil from './array-util';

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
            {
                var isSmoothScrollSupported = 'scrollBehavior' in document.documentElement.style;
                if (isSmoothScrollSupported)
                {
                    window.scrollTo({
                        'top': scrollPosition,
                        'behavior': 'smooth',
                    });
                }
                else {
                    window.scrollTo(0, scrollPosition);
                }
            }
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

    static getAllParentNodes(node) {
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
}

export default DomUtil