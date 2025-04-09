<template>
    <header class="flex items-center justify-between px-4 py-3 bg-white border-b">
        <!-- Logo/Brand -->
        <div class="text-xl font-bold text-gray-800">Chat</div>

        <!-- Search Bar -->
        <div class="relative flex-1 max-w-xl mx-6">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <input
                class="w-full py-2 pl-10 pr-4 text-sm text-gray-700 bg-gray-100 rounded-full focus:outline-none focus:bg-white focus:ring-1 focus:ring-blue-500 transition-colors duration-200"
                placeholder="Search for anything here..."
                v-model="contactsStore.searchQuery"
                @input="handleSearch"
            >
        </div>

        <!-- Right Section: Profile Toggle, Notifications and User Profile -->
        <div class="flex items-center space-x-3">
            <!-- Profile Info Toggle (only visible when a contact is selected) -->
            <button
                v-if="selectedContact"
                class="p-2 text-gray-500 hover:text-gray-700 focus:outline-none transition-colors duration-200"
                @click="$emit('toggle-profile')"
                aria-label="Toggle Profile Info"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </button>

            <!-- Notification Bell -->
            <button
                class="relative p-2 text-gray-500 hover:text-gray-700 focus:outline-none transition-colors duration-200"
                @click="$emit('toggle-notifications')"
                aria-label="Notifications"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>

                <!-- Notification Indicator (red dot) - shown if there are unread notifications -->
                <span v-if="hasUnreadNotifications" class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>

            <!-- User Profile -->
            <div class="flex items-center cursor-pointer" @click="toggleDropdown">
                <div class="relative">
                    <img
                        :src="user.avatar || '/images/default-avatar.png'"
                        alt="User avatar"
                        @error="handleImageError"
                        class="w-8 h-8 rounded-full object-cover border-2 border-gray-200"
                    >
                </div>
                <div class="ml-2">
                    <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                    <div class="text-xs text-gray-500">{{ user.role }}</div>
                </div>
                <button class="ml-2 text-gray-500 focus:outline-none transition-transform duration-200" :class="{ 'transform rotate-180': showDropdown }">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- User Dropdown Menu -->
        <div
            v-if="showDropdown"
            class="absolute right-4 top-14 mt-2 py-2 w-48 bg-white rounded-md shadow-lg z-20 transition-opacity duration-200"
            v-click-outside="closeDropdown"
        >
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">Your Profile</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">Settings</a>
            <div class="border-t my-1"></div>
            <a href="#" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100 transition-colors duration-150">Sign out</a>
        </div>
    </header>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useContactsStore } from '@/stores/contactsStore';

export default {
    name: 'Header',
    props: {
        user: {
            type: Object,
            default: () => ({
                name: 'Dea Novita',
                role: 'Admin',
                avatar: ''
            })
        },
        notifications: {
            type: Array,
            default: () => []
        },
        selectedContact: {
            type: Object,
            default: null
        }
    },
    emits: ['toggle-notifications', 'toggle-profile'],
    setup(props, { emit }) {
        const contactsStore = useContactsStore();
        const showDropdown = ref(false);

        // Check if there are any unread notifications
        const hasUnreadNotifications = computed(() => {
            return props.notifications && props.notifications.some(notification => !notification.read);
        });
        // Methods
        const handleSearch = () => {
            contactsStore.fetchContacts(contactsStore.searchQuery);
        };

        const toggleDropdown = () => {
            showDropdown.value = !showDropdown.value;
        };

        const closeDropdown = () => {
            showDropdown.value = false;
        };

        const handleImageError = (e) => {
            e.target.src = '/images/default-avatar.png';
        };

        // Click outside directive (simplified version)
        const clickOutsideEvent = (event) => {
            if (showDropdown.value && !event.target.closest('.dropdown-menu')) {
                closeDropdown();
            }
        };

        onMounted(() => {
            document.addEventListener('click', clickOutsideEvent);
        });

        onUnmounted(() => {
            document.removeEventListener('click', clickOutsideEvent);
        });

        // Custom directive for click outside
        const vClickOutside = {
            mounted(el, binding) {
                el.clickOutsideEvent = (event) => {
                    if (!(el === event.target || el.contains(event.target))) {
                        binding.value(event);
                    }
                };
                document.addEventListener('click', el.clickOutsideEvent);
            },
            unmounted(el) {
                document.removeEventListener('click', el.clickOutsideEvent);
            }
        };

        return {
            contactsStore,
            showDropdown,
            hasUnreadNotifications,
            handleSearch,
            toggleDropdown,
            closeDropdown,
            handleImageError,
            vClickOutside
        };
    }
};
</script>

<style scoped>
/* Additional custom styles */
.dropdown-menu {
    transform-origin: top right;
    animation: dropdown-appear 0.2s ease-out;
}

@keyframes dropdown-appear {
    from {
        transform: scale(0.95);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}
</style>
