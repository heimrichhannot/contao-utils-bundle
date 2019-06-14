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

export default GeneralUtil