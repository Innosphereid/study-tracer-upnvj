<template>
    <header
        class="questionnaire-header bg-white border-b border-gray-200 shadow-sm"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center">
                    <div class="logo flex-shrink-0">
                        <img
                            src="/logo.svg"
                            alt="Logo UPNVJ"
                            class="h-8 w-auto"
                        />
                    </div>
                    <div class="ml-4">
                        <h1 class="text-lg font-semibold text-gray-900">
                            {{ title }}
                        </h1>
                        <p v-if="subtitle" class="text-sm text-gray-600">
                            {{ subtitle }}
                        </p>
                    </div>
                </div>

                <div class="hidden md:flex space-x-4">
                    <a
                        v-if="showWebsiteLink"
                        href="https://upnvj.ac.id"
                        target="_blank"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="mr-2 h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        Website UPNVJ
                    </a>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        default: "TraceStudy UPNVJ",
    },
    subtitle: {
        type: String,
        default: "",
    },
    showWebsiteLink: {
        type: Boolean,
        default: true,
    },
});
</script>

<style scoped>
.questionnaire-header {
    position: sticky;
    top: 0;
    z-index: 10;
    backdrop-filter: blur(8px);
}
</style>
