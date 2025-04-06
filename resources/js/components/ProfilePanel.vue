<template>
    <div class="h-full flex flex-col bg-white border-l overflow-y-auto" :class="{'w-0 hidden': !isOpen, 'w-72 lg:w-80': isOpen}">
        <div class="p-6 flex flex-col items-center border-b">
            <!-- Profile Image -->
            <div class="relative mb-4">
                <img
                    :src="contact.avatar || '/images/default-avatar.png'"
                    alt="Profile Picture"
                    class="w-24 h-24 rounded-full object-cover border-2 border-gray-200"
                    @error="handleImageError"
                >
                <div v-if="contact.online" class="absolute bottom-1 right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white"></div>
            </div>

            <!-- Name and Status -->
            <h2 class="text-xl font-semibold text-gray-900">{{ contact.name }}</h2>
            <p v-if="contact.online" class="text-sm text-green-500 mt-1 flex items-center">
                <span class="w-2 h-2 bg-green-500 rounded-full mr-1"></span>
                Online
            </p>
            <p v-else class="text-sm text-gray-500 mt-1">Last seen {{ contact.lastSeen || 'today' }}</p>
        </div>

        <!-- Contact Information -->
        <div class="p-6 border-b">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Contact Information</h3>

            <div class="space-y-4">
                <!-- Email -->
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-gray-500 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <div>
                        <p class="text-sm text-gray-900">{{ contact.email || 'user@example.com' }}</p>
                        <p class="text-xs text-gray-500">Email</p>
                    </div>
                </div>

                <!-- Phone -->
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-gray-500 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    <div>
                        <p class="text-sm text-gray-900">{{ contact.phone || '+1 (555) 123-4567' }}</p>
                        <p class="text-xs text-gray-500">Mobile</p>
                    </div>
                </div>

                <!-- Location -->
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-gray-500 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <div>
                        <p class="text-sm text-gray-900">{{ contact.location || 'San Francisco, CA' }}</p>
                        <p class="text-xs text-gray-500">Location</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="p-6 border-b">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Actions</h3>

            <div class="space-y-2">
                <button class="w-full py-2 px-3 flex items-center justify-center text-sm text-blue-600 bg-blue-50 hover:bg-blue-100 rounded focus:outline-none transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                    </svg>
                    Add to Favorites
                </button>

                <button class="w-full py-2 px-3 flex items-center justify-center text-sm text-gray-600 bg-gray-50 hover:bg-gray-100 rounded focus:outline-none transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                    </svg>
                    Block Contact
                </button>
            </div>
        </div>

        <!-- Shared Files -->
        <div class="p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Shared Files</h3>

            <div class="space-y-3">
                <div class="p-2 flex items-center bg-gray-50 rounded hover:bg-gray-100 transition-colors duration-150 cursor-pointer">
                    <svg class="w-8 h-8 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">Medical records.pdf</p>
                        <p class="text-xs text-gray-500">2.3 MB · March 21, 2025</p>
                    </div>
                </div>

                <div class="p-2 flex items-center bg-gray-50 rounded hover:bg-gray-100 transition-colors duration-150 cursor-pointer">
                    <svg class="w-8 h-8 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">Treatment plan.xlsx</p>
                        <p class="text-xs text-gray-500">1.5 MB · March 18, 2025</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ProfilePanel',
    props: {
        contact: {
            type: Object,
            required: true
        },
        isOpen: {
            type: Boolean,
            default: false
        }
    },
    setup() {
        const handleImageError = (e) => {
            e.target.src = '/images/default-avatar.png';
        };

        return {
            handleImageError
        };
    }
};
</script>

<style scoped>
/* Smooth transition for opening/closing panel */
.h-full {
    transition: width 0.3s ease-in-out;
}
</style>
