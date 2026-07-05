/**
 * Full nav / المزيد access: admin (type_id 1 or 6) or main owner account (id = 1).
 */
export function isAdminOrOwner(user) {
    if (!user) return false;
    const typeId = Number(user.type_id);
    const userId = Number(user.id);
    return typeId === 1 || typeId === 6 || userId === 1;
}
