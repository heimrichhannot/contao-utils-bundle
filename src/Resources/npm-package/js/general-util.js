class GeneralUtil {
    static isTruthy(value) {
        return typeof value !== 'undefined' && value !== null;
    }

    static call(func) {
        if (typeof func === 'function') {
            func.apply(this, Array.prototype.slice.call(arguments, 1));
        }
    }

    /**
     * Run a function recursively for a given set of arguments.
     *
     * function doLogic(argument, remainingArguments, callback) {
     *     // do your logic with argument
     *     utilsBundle.util.runRecursiveFunction(doLogic, remainingArguments, callback);
     * }
     *
     * utilsBundle.util.runRecursiveFunction(doLogic, [1, 2, 3, 4], () => {
     *     // do something after all is done
     * });
     *
     * @param func
     * @param args
     * @param callback
     * @param successIndex
     */
    static runRecursiveFunction(func, args, callback, successIndex) {
        if (args.length < 1) {
            if (GeneralUtil.isTruthy(callback) && Array.isArray(callback)) {
                GeneralUtil.call(callback[successIndex]);
            } else {
                GeneralUtil.call(callback);
            }

            return;
        }

        var argument = args[0],
            remainingArgs = args.slice(1, args.length);

        func(argument, remainingArgs, callback);
    }
}

export default GeneralUtil
