/**
 * Created by Shen.L on 2017/4/19.
 */

var store = function () {
    return {
        get: function (key) {
            if (window.localStorage.getItem(key)) {
                return JSON.parse(window.localStorage.getItem(key));
            }
            return false;
        },
        set: function (key, value) {
            if (value === undefined) {
                window.localStorage.removeItem(key);
            } else {
                window.localStorage.setItem(key, JSON.stringify(value));
            }
            return window.localStorage.getItem(key);
        },
        setItem: function (goods) {
            if (typeof goods !== 'object' || undefined === goods.id) return;
            goods.count = parseInt(goods.count) ? parseInt(goods.count) : 1;
            var item = this.getItem(goods.id);
            if (item === null) item = {id: goods.id, count: 0};
            else this.removeItem(item.id);
            item.count = goods.count;
            var cart = this.getCart();
            cart.unshift(item);
            return this.set('cart', cart);
        },
        addItem: function (goods) {
            if (typeof goods !== 'object' || undefined === goods.id) return;
            goods.count = parseInt(goods.count) ? parseInt(goods.count) : 1;
            var item = this.getItem(goods.id);
            if (item === null) item = {id: goods.id, count: 0};
            else this.removeItem(item.id);
            item.count += goods.count;
            var cart = this.getCart();
            cart.unshift(item);
            return this.set('cart', cart);
        },
        /**
         * 获取购物车
         * @returns {Object}
         */
        getCart: function () {
            var cart = this.get('cart');
            if (cart === false) return [];
            return cart;
        },
        getItem: function (id) {
            var cart = this.get('cart');
            if (cart === false) return null;
            var l = cart.length;
            for (var i = 0; i < l; i++) {
                if (cart[i].id == id) {
                    return cart[i];
                }
            }
            return null;
        },
        removeItem: function (id) {
            var cart = this.get('cart');
            var l = cart.length;
            for (var i = 0; i < l; i++) {
                if (cart[i].id == id) {
                    cart.splice(i, 1);
                    this.set('cart', cart);
                    return true;
                }
            }
        },
        countItems: function () {
            var cart = this.get('cart'),
                l = cart.length,
                count = 0;
            for (var i = 0; i < l; i++) {
                count += cart[i].count
            }
            return count;
        },
        clearCart: function () {
            return this.set('cart', []);
        },
    }
}();

var Cart = function ($) {

    return {
        count: function () {
            return store.countItems();
        },
        add: function (item) {
            if (typeof item !== 'object') {
                item = {id: item, count: 1};
            }
            return store.addItem(item);
        },
        set: function (item) {
            if (typeof item !== 'object') {
                item = {id: item, count: 1};
            }
            return store.setItem(item);
        },
        get: function (id) {
            if (undefined === id) return store.getCart();
            return store.getItem(id);
        },
        remove: function (id) {
            return store.removeItem(id);
        },
        clear: function () {
            return store.clearCart();
        }

    }
}(jQuery);