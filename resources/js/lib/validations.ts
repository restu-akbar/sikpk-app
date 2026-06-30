export function validateNewPassword(
    password?: string,
    confirmPassword?: string,
    oldPassword?: string,
): string | null {
    if (!password) {
        return 'Password baru wajib diisi.';
    }

    if (password.length < 8) {
        return 'Password minimal 8 karakter.';
    }

    if (!/[A-Z]/.test(password)) {
        return 'Password harus mengandung minimal satu huruf kapital.';
    }

    if (!/[a-z]/.test(password)) {
        return 'Password harus mengandung minimal satu huruf kecil.';
    }

    if (!/[0-9]/.test(password)) {
        return 'Password harus mengandung minimal satu angka.';
    }

    if (password !== confirmPassword) {
        return 'Konfirmasi password tidak cocok.';
    }

    if (oldPassword && password === oldPassword) {
        return 'Password baru tidak boleh sama dengan password lama.';
    }

    return null;
}
