// File helper untuk validasi NIK Indonesia
// Dapat digunakan di client-side

function validateNIK(nik) {
    // NIK harus 16 digit
    if (nik.length !== 16) {
        return {
            valid: false,
            message: "NIK harus 16 digit",
        };
    }

    // NIK harus angka semua
    if (!/^\d+$/.test(nik)) {
        return {
            valid: false,
            message: "NIK hanya boleh berisi angka",
        };
    }

    // Validasi tanggal lahir dari NIK
    const tglLahir = parseInt(nik.substring(6, 8));
    const bulanLahir = parseInt(nik.substring(8, 10));
    const tahunLahir = parseInt(nik.substring(10, 12));

    // Untuk perempuan, tanggal ditambah 40
    const tanggal = tglLahir > 40 ? tglLahir - 40 : tglLahir;

    if (tanggal < 1 || tanggal > 31) {
        return {
            valid: false,
            message: "Format tanggal lahir di NIK tidak valid",
        };
    }

    if (bulanLahir < 1 || bulanLahir > 12) {
        return {
            valid: false,
            message: "Format bulan lahir di NIK tidak valid",
        };
    }

    return {
        valid: true,
        message: "NIK valid",
        gender: tglLahir > 40 ? "Perempuan" : "Laki-laki",
    };
}

// Export untuk digunakan
if (typeof module !== "undefined" && module.exports) {
    module.exports = { validateNIK };
}
