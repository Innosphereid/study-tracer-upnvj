<template>
    <footer
        class="form-footer py-4 px-6 bg-white shadow-sm border-t border-gray-200"
    >
        <div
            class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between"
        >
            <!-- Copyright -->
            <div class="text-sm text-gray-500 mb-2 sm:mb-0">
                &copy; {{ currentYear }} TraceStudy UPNVJ. Hak Cipta Dilindungi.
            </div>

            <!-- Navigation/Actions -->
            <div class="flex items-center space-x-2">
                <slot></slot>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { computed } from "vue";

const currentYear = computed(() => new Date().getFullYear());
</script>
