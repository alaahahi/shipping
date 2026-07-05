/**
 * Full nav / المزيد access: admin (type_id = 1) or main owner account (id = 1).
 */
export function isAdminOrOwner(user) {
    if (!user) return false;
    return Number(user.type_id) === 1 || Number(user.id) === 1;
}
