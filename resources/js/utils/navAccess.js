/**
 * Full nav / المزيد access: admin (type_id 1 or 6) or main owner account (id = 1).
 */
export function isAdminOrOwner(user) {
    if (!user) return false;
    const typeId = Number(user.type_id);
    const userId = Number(user.id);
    return typeId === 1 || typeId === 6 || userId === 1;
}

export function canAccessRoute(routeName, allowedRoutes = []) {
    if (!routeName) return true;
    if (!Array.isArray(allowedRoutes) || allowedRoutes.length === 0) {
        return false;
    }
    return allowedRoutes.includes(routeName);
}

export function mainNavPages(navPages = []) {
    return navPages.filter((page) => page.nav_group === 'main');
}

export function moreNavPages(navPages = []) {
    return navPages.filter((page) => page.nav_group === 'more');
}
