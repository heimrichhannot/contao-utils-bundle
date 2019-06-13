// foreach on nodelists
import 'nodelist-foreach-polyfill';

// closest() and matches polyfill
import elementClosest from 'element-closest';

elementClosest(window);

// replaceWith
function ReplaceWith(Ele) {
    'use-strict'; // For safari, and IE > 10
    var parent = this.parentNode,
        i = arguments.length,
        firstIsNode = +(parent && typeof Ele === 'object');
    if (!parent) return;

    while (i-- > firstIsNode) {
        if (parent && typeof arguments[i] !== 'object') {
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

if (!Element.prototype.replaceWith)
    Element.prototype.replaceWith = ReplaceWith;
if (!CharacterData.prototype.replaceWith)
    CharacterData.prototype.replaceWith = ReplaceWith;
if (!DocumentType.prototype.replaceWith)
    CharacterData.prototype.replaceWith = ReplaceWith;