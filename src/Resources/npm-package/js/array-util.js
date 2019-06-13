class ArrayUtil {
    static removeFromArray(value, array) {
        for (var i = 0; i < array.length; i++) {
            if (JSON.stringify(value) == JSON.stringify(array[i])) {
                array.splice(i, 1);
            }
        }
        return array;
    }
}

export default ArrayUtil