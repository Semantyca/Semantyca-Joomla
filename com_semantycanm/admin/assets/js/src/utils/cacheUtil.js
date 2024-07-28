export function createCache(expiration = 60000) {
    return {
        map: new Map(),
        expiration,
        get(key) {
            const cached = this.map.get(key);
            if (cached && Date.now() - cached.cacheTime < this.expiration) {
                return cached.value;
            }
            return null;
        },
        set(key, value) {
            this.map.set(key, {value, cacheTime: Date.now()});
        },
        delete(key) {
            this.map.delete(key);
        },
        clear() {
            this.map.clear();
        }
    };
}
