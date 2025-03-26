import ShortTextPreview from "./ShortTextPreview.vue";
import RadioPreview from "./RadioPreview.vue";
import LongTextPreview from "./LongTextPreview.vue";
import CheckboxPreview from "./CheckboxPreview.vue";
import YesNoPreview from "./YesNoPreview.vue";
import DropdownPreview from "./DropdownPreview.vue";
import EmailPreview from "./EmailPreview.vue";
import NumberPreview from "./NumberPreview.vue";
import DatePreview from "./DatePreview.vue";
import RatingPreview from "./RatingPreview.vue";
import LikertPreview from "./LikertPreview.vue";
import FileUploadPreview from "./FileUploadPreview.vue";
import MatrixPreview from "./MatrixPreview.vue";
import SliderPreview from "./SliderPreview.vue";
import RankingPreview from "./RankingPreview.vue";

// Register semua komponen preview di sini
const previewComponents = {
    "short-text": ShortTextPreview,
    radio: RadioPreview,
    "long-text": LongTextPreview,
    checkbox: CheckboxPreview,
    "yes-no": YesNoPreview,
    dropdown: DropdownPreview,
    email: EmailPreview,
    number: NumberPreview,
    date: DatePreview,
    rating: RatingPreview,
    likert: LikertPreview,
    "file-upload": FileUploadPreview,
    matrix: MatrixPreview,
    slider: SliderPreview,
    ranking: RankingPreview,
};

// Fungsi untuk mendapatkan komponen preview berdasarkan tipe pertanyaan
export function getPreviewComponent(questionType) {
    return previewComponents[questionType] || null;
}

// Daftar semua tipe pertanyaan yang tersedia dan label yang sesuai
export const questionTypes = {
    "short-text": "Teks Pendek",
    "long-text": "Teks Panjang",
    email: "Email",
    phone: "Nomor Telepon",
    number: "Angka",
    date: "Tanggal",
    radio: "Pilihan Ganda",
    checkbox: "Kotak Centang",
    dropdown: "Dropdown",
    rating: "Rating Bintang",
    likert: "Skala Likert",
    "yes-no": "Ya/Tidak",
    "file-upload": "Upload File",
    matrix: "Matriks",
    slider: "Slider",
    ranking: "Rangking",
};

// Fungsi untuk mendapatkan label berdasarkan tipe
export function getTypeLabel(type) {
    return questionTypes[type] || type;
}

// Fungsi untuk mendapatkan class CSS berdasarkan tipe
export function getTypeClass(type) {
    const typeCategories = {
        dasar: ["short-text", "long-text", "email", "phone", "number", "date"],
        pilihan: [
            "radio",
            "checkbox",
            "dropdown",
            "rating",
            "likert",
            "yes-no",
        ],
        lanjutan: ["file-upload", "matrix", "slider", "ranking"],
    };

    // Determine category based on type
    let category = "dasar";
    Object.entries(typeCategories).forEach(([cat, types]) => {
        if (types.includes(type)) category = cat;
    });

    // Return appropriate class based on category
    switch (category) {
        case "dasar":
            return "bg-blue-100 text-blue-800";
        case "pilihan":
            return "bg-green-100 text-green-800";
        case "lanjutan":
            return "bg-purple-100 text-purple-800";
        default:
            return "bg-gray-100 text-gray-800";
    }
}
