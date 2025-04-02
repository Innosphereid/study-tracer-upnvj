<template>
    <footer
        class="questionnaire-footer bg-white border-t border-gray-200 mt-auto"
    >
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <p class="text-sm text-gray-500">
                    &copy; {{ currentYear }} TraceStudy UPNVJ. Hak Cipta
                    Dilindungi.
                </p>

                <div class="mt-4 md:mt-0 flex space-x-6">
                    <a
                        href="#"
                        class="text-gray-500 hover:text-gray-700 transition-colors duration-200 text-sm"
                    >
                        Kebijakan Privasi
                    </a>
                    <a
                        href="#"
                        class="text-gray-500 hover:text-gray-700 transition-colors duration-200 text-sm"
                    >
                        Syarat dan Ketentuan
                    </a>
                    <a
                        href="#"
                        class="text-gray-500 hover:text-gray-700 transition-colors duration-200 text-sm"
                    >
                        Bantuan
                    </a>
                </div>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { computed } from "vue";

const currentYear = computed(() => new Date().getFullYear());
</script>

<style scoped>
.questionnaire-footer {
    margin-top: 3rem;
}
</style>
