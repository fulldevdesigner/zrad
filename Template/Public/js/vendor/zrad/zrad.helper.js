if (!Object.keys) {
    Object.keys = (function () {
        var hasOwnProperty = Object.prototype.hasOwnProperty,
        hasDontEnumBug = !({
            toString: null
        }).propertyIsEnumerable('toString'),
        dontEnums = [
        'toString',
        'toLocaleString',
        'valueOf',
        'hasOwnProperty',
        'isPrototypeOf',
        'propertyIsEnumerable',
        'constructor'
        ],
        dontEnumsLength = dontEnums.length

        return function (obj) {
            if (typeof obj !== 'object' && typeof obj !== 'function' || obj === null) throw new TypeError('Object.keys called on non-object')

            var result = []

            for (var prop in obj) {
                if (hasOwnProperty.call(obj, prop)) result.push(prop)
            }

            if (hasDontEnumBug) {
                for (var i=0; i < dontEnumsLength; i++) {
                    if (hasOwnProperty.call(obj, dontEnums[i])) result.push(dontEnums[i])
                }
            }
            return result
        }
    })()
}

if (!Array.prototype.indexOf){
  Array.prototype.indexOf = function(elt /*, from*/) {
    var len = this.length >>> 0;
    var from = Number(arguments[1]) || 0;
    from = (from < 0)
         ? Math.ceil(from)
         : Math.floor(from);
    if (from < 0)
      from += len;

    for (; from < len; from++){
      if (from in this &&
          this[from] === elt)
        return from;
    }
    return -1;
  };
}

function isEmpty(object){
    return (
        ($.isPlainObject(object) && $.isEmptyObject(object)) ||
        ($.isArray(object) && object.length == 0) ||
        (typeof(object) == "string" && $.trim(object) === "") ||
        (!object)
        );
}

function compareObject(objA, objB){
    var i,a_type,b_type;

    // Compare if they are references to each other 
    if (objA === objB) {
        return true;
    }

    if (Object.keys(objA).length !== Object.keys(objB).length) {
        return false;
    }
    for (i in objA) {
        if (objA.hasOwnProperty(i)) {
            if (typeof objB[i] === 'undefined') {
                return false;
            }
            else {
                a_type = Object.prototype.toString.apply(objA[i]);
                b_type = Object.prototype.toString.apply(objB[i]);

                if (a_type !== b_type) {
                    return false; 
                }
            }
        }
        if (isEquals(objA[i],objB[i]) === false){
            return false;
        }
    }
    return true;
} 

function compareArray(arrayA, arrayB){
    var a,b,i,a_type,b_type;
    // References to each other?
    if (arrayA === arrayB) {
        return true;
    }

    if (arrayA.length != arrayB.length) {
        return false;
    }
    // sort modifies original array
    // (which are passed by reference to our method!)
    // so clone the arrays before sorting
    a = $.extend(true, [], arrayA);
    b = $.extend(true, [], arrayB);
    a.sort(); 
    b.sort();
    for (i = 0, l = a.length; i < l; i+=1) {
        a_type = Object.prototype.toString.apply(a[i]);
        b_type = Object.prototype.toString.apply(b[i]);

        if (a_type !== b_type) {
            return false;
        }

        if (isEquals(a[i],b[i]) === false) {
            return false;
        }
    }
    return true;
}

function isEquals(a, b){   
    var obj_str = '[object Object]',
    arr_str = '[object Array]',
    a_type  = Object.prototype.toString.apply(a),
    b_type  = Object.prototype.toString.apply(b);

    if ( a_type !== b_type) {
        return false;
    }
    else if (a_type === obj_str) {
        return compareObject(a,b);
    }
    else if (a_type === arr_str) {
        return compareArray(a,b);
    }
    return (a === b);
}

function redirect(url){
    location.href = url;
}

