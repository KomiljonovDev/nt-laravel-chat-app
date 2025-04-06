<template>
    <div class="fixed inset-0 overflow-hidden z-50">
        <!-- Backdrop with blur effect -->
        <div
            class="fixed inset-0 bg-black bg-opacity-30 backdrop-filter backdrop-blur-sm transition-opacity duration-300"
            @click="$emit('close')"
        ></div>

        <!-- Notification Panel -->
        <div class="fixed inset-y-0 right-0 flex max-w-full">
            <div
                class="relative w-80 bg-white shadow-xl h-full flex flex-col transform transition-transform duration-300 ease-in-out"
                :class="{ 'translate-x-0': true, 'translate-x-full': false }"
            >
                <!-- Header -->
                <div class="flex items-center justify-between px-4 py-3 border-b">
                    <h2 class="text-lg font-medium text-gray-900">Notifications</h2>
                    <button
                        class="p-1 rounded-full text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200"
                        @click="$emit('close')"
                        aria-label="Close notifications"
                    >
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>

                <!-- Notification List -->
                <div class="flex-1 overflow-y-auto divide-y divide-gray-100">
                    <transition-group name="notification-list" tag="div">
                        <div
                            v-for="notification in sortedNotifications"
                            :key="notification.id"
                            class="p-4 hover:bg-gray-50 transition-colors duration-150"
                            :class="{ 'bg-blue-50': !notification.read }"
                        >
                            <!-- Notification header -->
                            <div class="flex justify-between items-start">
                                <h3
                                    class="text-sm font-medium"
                                    :class="{ 'text-gray-900': notification.read, 'text-blue-700': !notification.read }"
                                >
                                    {{ notification.title }}
                                </h3>
                                <span class="text-xs text-gray-500 whitespace-nowrap ml-2">{{ notification.timestamp }}</span>
                            </div>

                            <!-- Notification body -->
                            <p class="mt-1 text-sm text-gray-600">{{ notification.text }}</p>

                            <!-- Action buttons -->
                            <div class="mt-2 flex space-x-3">
                                <!-- Mark as read button (only for unread) -->
                                <button
                                    v-if="!notification.read"
                                    @click="$emit('mark-read', notification.id)"
                                    class="text-xs text-blue-600 hover:text-blue-800 transition-colors duration-200"
                                >
                                    Mark as read
                                </button>

                                <!-- Other action buttons -->
                                <button
                                    class="text-xs text-gray-600 hover:text-gray-800 transition-colors duration-200"
                                >
                                    View details
                                </button>
                            </div>
                        </div>
                    </transition-group>

                    <!-- Empty state if no notifications -->
                    <div v-if="notifications.length === 0" class="p-8 text-center">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <p class="text-gray-500">No notifications yet</p>
                        <p class="text-sm text-gray-400 mt-1">When you receive notifications, they'll appear here</p>
                    </div>
                </div>

                <!-- Footer -->
                <div v-if="hasUnreadNotifications" class="border-t p-4 bg-white">
                    <button
                        @click="$emit('mark-all-read')"
                        class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200"
                    >
                        Mark all as read
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { computed } from 'vue';

export default {
    name: 'NotificationModal',
    props: {
        notifications: {
            type: Array,
            default: () => []
        }
    },
    emits: ['close', 'mark-read', 'mark-all-read'],
    setup(props) {
        // Sort notifications: unread first, then by timestamp (recent first)
        const sortedNotifications = computed(() => {
            return [...props.notifications].sort((a, b) => {
                // Sort by read status
                if (!a.read && b.read) return -1;
                if (a.read && !b.read) return 1;

                // Then by timestamp (recent first)
                // Assuming timestamp is in format HH:MM or similar
                return b.timestamp.localeCompare(a.timestamp);
            });
        });

        // Check if there are any unread notifications
        const hasUnreadNotifications = computed(() => {
            return props.notifications.some(notification => !notification.read);
        });

        return {
            sortedNotifications,
            hasUnreadNotifications
        };
    }
};
</script>

<style scoped>
/* Animation for notifications list */
.notification-list-enter-active,
.notification-list-leave-active {
    transition: all 0.3s ease;
}

.notification-list-enter-from {
    opacity: 0;
    transform: translateX(30px);
}

.notification-list-leave-to {
    opacity: 0;
    transform: translateX(30px);
}

/* Custom scrollbar */
div {
    scrollbar-width: thin;
    scrollbar-color: #d1d5db transparent;
}

::-webkit-scrollbar {
    width: 4px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 2px;
}

::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

/* Slide-in animation */
@keyframes slide-in-right {
    from {
        transform: translateX(100%);
    }
    to {
        transform: translateX(0);
    }
}

.transform {
    animation: slide-in-right 0.3s ease-out;
}
</style>
