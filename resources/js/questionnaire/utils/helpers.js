/**
 * Menghasilkan slug dari string
 * @param {string} text - Text yang akan dijadikan slug
 * @returns {string} slug
 */
export function slugify(text) {
    return text
        .toString()
        .toLowerCase()
        .trim()
        .replace(/\s+/g, "-") // Ganti spasi dengan -
        .replace(/&/g, "-and-") // Ganti & dengan 'and'
        .replace(/[^\w\-]+/g, "") // Hapus semua karakter non-word
        .replace(/\-\-+/g, "-"); // Ganti beberapa - dengan single -
}

/**
 * Memformat tanggal ke format Indonesia
 * @param {string|Date} date - Tanggal yang akan diformat
 * @returns {string} tanggal yang diformat
 */
export function formatDate(date) {
    if (!date) return "";

    const options = {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    };

    return new Date(date).toLocaleDateString("id-ID", options);
}

/**
 * Menghasilkan warna berdasarkan string
 * Berguna untuk menghasilkan warna konsisten untuk kategori
 * @param {string} str - String input
 * @returns {string} Warna dalam format hex
 */
export function stringToColor(str) {
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
        hash = str.charCodeAt(i) + ((hash << 5) - hash);
    }

    let color = "#";
    for (let i = 0; i < 3; i++) {
        const value = (hash >> (i * 8)) & 0xff;
        color += ("00" + value.toString(16)).substr(-2);
    }

    return color;
}

/**
 * Menyalin objek dengan JSON stringify/parse untuk deep copy
 * @param {Object} obj - Objek yang akan di-clone
 * @returns {Object} Objek yang telah di-clone
 */
export function cloneDeep(obj) {
    return JSON.parse(JSON.stringify(obj));
}

/**
 * Membuat delay promise
 * @param {number} ms - Milliseconds untuk delay
 * @returns {Promise} Promise yang akan resolve setelah ms
 */
export function delay(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
}

/**
 * Memvalidasi apakah string email valid
 * @param {string} email - Email yang akan divalidasi
 * @returns {boolean} true jika email valid
 */
export function isValidEmail(email) {
    const re =
        /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

/**
 * Memformat nomor telepon ke format Indonesia
 * @param {string} phone - Nomor telepon
 * @returns {string} Nomor telepon yang diformat
 */
export function formatPhoneNumber(phone) {
    if (!phone) return "";

    // Hapus semua karakter non-digit
    let cleaned = ("" + phone).replace(/\D/g, "");

    // Cek awalan +62 atau 0
    if (cleaned.startsWith("62")) {
        cleaned = "+62" + cleaned.substring(2);
    } else if (cleaned.startsWith("0")) {
        cleaned = "+62" + cleaned.substring(1);
    } else if (!cleaned.startsWith("+")) {
        cleaned = "+62" + cleaned;
    }

    return cleaned;
}

/**
 * Menghasilkan nama ikon dari tipe pertanyaan
 * @param {string} questionType - Tipe pertanyaan
 * @returns {string} Nama icon
 */
export function getQuestionTypeIcon(questionType) {
    const iconMap = {
        "short-text": "format-text",
        "long-text": "format-paragraph",
        email: "mail",
        phone: "phone",
        number: "number",
        date: "calendar",
        radio: "radio-button-checked",
        checkbox: "check-box",
        dropdown: "arrow-drop-down-circle",
        rating: "star",
        likert: "linear-scale",
        "yes-no": "toggle-on",
        "file-upload": "file-upload",
        matrix: "grid-view",
        slider: "tune",
        ranking: "format-list-numbered",
    };

    return iconMap[questionType] || "help-circle";
}

/**
 * Menghasilkan pengkategorian hasil kuesioner
 * @param {number} value - Nilai (persentase)
 * @returns {string} Kategori (sangat baik, baik, cukup, kurang, sangat kurang)
 */
export function getCategoryFromPercentage(value) {
    if (value >= 90) return "Sangat Baik";
    if (value >= 70) return "Baik";
    if (value >= 50) return "Cukup";
    if (value >= 30) return "Kurang";
    return "Sangat Kurang";
}

/**
 * Menghasilkan warna untuk kategori
 * @param {string} category - Kategori
 * @returns {string} Kelas CSS Tailwind untuk warna
 */
export function getCategoryColor(category) {
    const colorMap = {
        "Sangat Baik": "bg-emerald-500",
        Baik: "bg-green-500",
        Cukup: "bg-yellow-500",
        Kurang: "bg-orange-500",
        "Sangat Kurang": "bg-red-500",
    };

    return colorMap[category] || "bg-gray-500";
}
