import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                ink: "#0F1A14", // hitam kehijauan: teks utama dan tombol utama
                ground: "#F4F6F1", // latar halaman
                line: "#E3E8E2", // garis tepi
                muted: "#56635B", // teks pendukung
                court: {
                    DEFAULT: "#15803D", // hijau lapangan (warna utama)
                    deep: "#126B33",
                    dark: "#0E3B22",
                    soft: "#EAF6EE",
                },
                volt: {
                    DEFAULT: "#D4F45A", // hijau lime ala bola tenis (warna aksen)
                    soft: "#EEF8D0",
                    ink: "#2E4A05",
                },
            },
        },
    },

    plugins: [forms],
};
