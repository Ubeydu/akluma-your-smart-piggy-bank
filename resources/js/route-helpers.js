import { route } from './ziggy';

export function getCurrentLocale() {
    return document.documentElement.lang || 'en';
}

export function localizedRoute(name, params = {}) {
    const locale = getCurrentLocale();
    return route(`localized.${name}`, { ...params, locale: locale });
}
