<template>
    <div class="flex flex-col h-full">
        <!-- Chat Header -->
        <div class="flex items-center justify-between px-4 py-3 bg-white border-b shadow-sm">
            <!-- Contact Info -->
            <div class="flex items-center cursor-pointer" @click="$emit('toggle-profile')">
                <div class="relative">
                    <img
                        :src="selectedContact.avatar || '/images/default-avatar.png'"
                        :alt="`${selectedContact.name}'s avatar`"
                        @error="handleImageError"
                        class="w-10 h-10 rounded-full object-cover border border-gray-200"
                    >
                    <span
                        v-if="onlineStatus"
                        class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"
                    ></span>
                </div>
                <div class="ml-3">
                    <h2 class="text-sm font-semibold text-gray-900">{{ selectedContact.name }}</h2>
                    <p v-if="onlineStatus" class="text-xs text-green-500 flex items-center">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1"></span>
                        Online
                    </p>
                    <p v-else class="text-xs text-gray-500">Last seen {{ lastSeen }}</p>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex items-center space-x-2">
                <!-- Info/Profile button -->
                <button
                    class="p-2 text-gray-500 hover:text-gray-700 focus:outline-none transition-colors duration-200"
                    @click="$emit('toggle-profile')"
                    aria-label="View Profile"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </button>

                <!-- More options button -->
                <button class="p-2 text-gray-500 hover:text-gray-700 focus:outline-none transition-colors duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Messages Container - uses more horizontal space -->
        <div
            ref="messagesContainer"
            class="flex-1 p-4 overflow-y-auto bg-gray-50 messages-container"
        >
            <div class="flex flex-col mx-auto space-y-4 w-full px-4 md:px-10 lg:px-16 xl:px-24">
                <!-- Date separator -->
                <div class="flex justify-center my-4">
                    <div class="px-3 py-1 text-xs text-gray-500 bg-gray-100 rounded-full">
                        Today
                    </div>
                </div>

                <!-- Messages -->
                <transition-group name="message" tag="div" class="space-y-3">
                    <MessageItem
                        v-for="message in formattedMessages"
                        :key="message.id"
                        :message="message"
                        :isSender="message.user.id === currentUser.id"
                        :showAvatar="shouldShowAvatar(message)"
                        class="max-w-3xl md:max-w-4xl lg:max-w-5xl"
                    />
                </transition-group>

                <!-- Typing indicator (optional) -->
                <div v-if="isTyping" class="flex items-center mt-3">
                    <div class="flex items-center">
                        <img
                            :src="selectedContact.avatar || '/images/default-avatar.png'"
                            alt="Avatar"
                            class="w-8 h-8 rounded-full mr-2"
                        >
                        <div class="px-3 py-2 bg-white rounded-lg shadow-sm">
                            <div class="flex space-x-1 items-center">
                                <div class="w-2 h-2 bg-gray-500 rounded-full animate-bounce"></div>
                                <div class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                                <div class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scroll to bottom button -->
                <button
                    v-if="showScrollToBottom"
                    @click="scrollToBottom"
                    class="fixed bottom-20 right-6 p-2 bg-blue-500 hover:bg-blue-600 text-white rounded-full shadow-lg transition-colors duration-200 focus:outline-none"
                    aria-label="Scroll to bottom"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted, onUpdated, watch } from 'vue';
import MessageItem from './MessageItem.vue';

export default {
    name: 'ChatWindow',
    components: {
        MessageItem
    },
    props: {
        messages: {
            type: Array,
            default: () => []
        },
        currentUser: {
            type: Object,
            required: true
        },
        selectedContact: {
            type: Object,
            required: true
        }
    },
    emits: ['toggle-profile'],
    setup(props) {
        const messagesContainer = ref(null);
        const isTyping = ref(false);
        const isNearBottom = ref(true);
        const showScrollToBottom = ref(false);

        // Enhanced messages with proper formatting
        const formattedMessages = computed(() => {
            return props.messages.map(message => ({
                ...message,
                // Add avatar for users if not present
                user: {
                    ...message.user,
                    avatar: message.user.id === props.currentUser.id
                        ? props.currentUser.avatar
                        : props.selectedContact.avatar
                },
                // Check if message contains emoji for special styling
                hasEmoji: /(\p{Emoji_Presentation}|\u200d)/u.test(message.text),
                // Format timestamp if needed
                formattedTime: message.time
            }));
        });

        // For demo purposes - would be from actual data
        const onlineStatus = computed(() => props.selectedContact.online || false);
        const lastSeen = computed(() => props.selectedContact.lastSeen || 'today');

        // Determine if we should show the avatar for a message
        // We only show it for the first message in a sequence from the same sender
        const shouldShowAvatar = (message) => {
            const index = props.messages.findIndex(m => m.id === message.id);
            if (index === 0) return true;

            const prevMessage = props.messages[index - 1];
            return prevMessage.user.id !== message.user.id;
        };

        // Check if user is near the bottom of the chat
        const checkIfNearBottom = () => {
            if (!messagesContainer.value) return;

            const { scrollTop, scrollHeight, clientHeight } = messagesContainer.value;
            const scrollPosition = scrollTop + clientHeight;
            const isNear = scrollHeight - scrollPosition < 100; // within 100px of bottom

            isNearBottom.value = isNear;
            showScrollToBottom.value = !isNear;
        };

        // Scroll to bottom when component is mounted
        onMounted(() => {
            scrollToBottom();

            // Add scroll event listener to check position
            if (messagesContainer.value) {
                messagesContainer.value.addEventListener('scroll', checkIfNearBottom);
            }
        });

        // Clean up event listener
        onUpdated(() => {
            // If near bottom, scroll to bottom when messages are updated
            if (isNearBottom.value) {
                scrollToBottom();
            }
        });

        // Watch for new messages
        watch(() => props.messages.length, (newLength, oldLength) => {
            if (newLength > oldLength && isNearBottom.value) {
                scrollToBottom();
            }
        });

        const scrollToBottom = () => {
            setTimeout(() => {
                if (messagesContainer.value) {
                    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
                    showScrollToBottom.value = false;
                }
            }, 50);
        };

        const handleImageError = (e) => {
            // Use a default avatar if image fails to load
            e.target.src = '/images/default-avatar.png';
        };

        return {
            messagesContainer,
            isTyping,
            onlineStatus,
            lastSeen,
            formattedMessages,
            shouldShowAvatar,
            showScrollToBottom,
            scrollToBottom,
            handleImageError
        };
    }
};
</script>

<style scoped>
.messages-container {
    scrollbar-width: thin;
    scrollbar-color: #d1d5db transparent;
    background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23f3f4f6' fill-opacity='0.4' fill-rule='evenodd'/%3E%3C/svg%3E");
    background-position: center;
    background-repeat: repeat;
}

/* Message animations */
.message-enter-active,
.message-leave-active {
    transition: all 0.3s ease;
}
.message-enter-from {
    opacity: 0;
    transform: translateY(20px);
}
.message-leave-to {
    opacity: 0;
}

/* Scroll to bottom button animation */
@keyframes bounce {
    0%, 100% {
        transform: translateY(-5%);
        animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
    }
    50% {
        transform: translateY(0);
        animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
    }
}
.animate-bounce {
    animation: bounce 1s infinite;
}
</style>
