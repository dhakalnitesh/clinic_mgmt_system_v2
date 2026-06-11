<template>
    <footer class="mt-auto px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-gray-900 dark:text-gray-100">
            <!-- Copyright -->
            <p>© {{ currentYear }} Clinic Management System</p>

            <!-- Status -->
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                        <div class="w-2 h-2 rounded-full bg-emerald-500 absolute top-0 left-0 animate-ping"></div>
                    </div>
                    <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">All Systems Operational</span>
                </div>
                <div class="hidden lg:flex items-center gap-1">
                    <i class="fas fa-server text-xs text-gray-500 dark:text-gray-400"></i>
                    <span class="text-gray-700 dark:text-gray-300">{{ uptimePercentage }}% uptime</span>
                </div>
            </div>

            <!-- Links -->
            <div class="flex gap-4">
                <a href="#" class="text-gray-500 dark:text-gray-400 hover:text-teal-600 dark:hover:text-teal-400 transition-colors">Privacy</a>
                <a href="#" class="text-gray-500 dark:text-gray-400 hover:text-teal-600 dark:hover:text-teal-400 transition-colors">Terms</a>
                <a href="#" class="text-gray-500 dark:text-gray-400 hover:text-teal-600 dark:hover:text-teal-400 transition-colors">Cookies</a>
            </div>
        </div>


        <!-- Mobile Bottom Navigation -->
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 md:hidden">
            <div class="grid grid-cols-4 gap-2">
                <a href="/dashboard" class="flex flex-col items-center text-xs text-gray-600 dark:text-gray-400 hover:text-teal-600 dark:hover:text-teal-400">
                    <i class="fas fa-home mb-1"></i>
                    <span>Home</span>
                </a>
                 <a href="#" class="flex flex-col items-center text-xs text-gray-600 dark:text-gray-400 hover:text-teal-600 dark:hover:text-teal-400">
                    <i class="fas fa-chart-bar mb-1"></i>
                    <span>Reports</span>
                </a>
                <a href="#" class="flex flex-col items-center text-xs text-gray-600 dark:text-gray-400 hover:text-teal-600 dark:hover:text-teal-400">
                    <i class="fas fa-headset mb-1"></i>
                    <span>Support</span>
                </a>
                <a href="#" class="flex flex-col items-center text-xs text-gray-600 dark:text-gray-400 hover:text-teal-600 dark:hover:text-teal-400">
                    <i class="fas fa-cog mb-1"></i>
                    <span>Settings</span>
                </a>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const currentYear = computed(() => new Date().getFullYear());
const appVersion = ref('4.0');
const uptimePercentage = ref('99.9');

onMounted(() => {
    setTimeout(() => {
        uptimePercentage.value = '99.98';
    }, 2000);
});
</script>

<style scoped>
/* Status indicator animation */
@keyframes ping {
    75%, 100% {
        transform: scale(2);
        opacity: 0;
    }
}

.animate-ping {
    animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
}

/* Mobile grid optimization */
@media (max-width: 768px) {
    .grid-cols-4 {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}

/* Print optimization */
@media print {
    footer {
        display: none !important;
    }
}

/* Smooth hover effects */
a {
    transition: all 0.2s ease;
}
</style>
