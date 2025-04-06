<template>
    <div
        class="flex group"
        :class="{ 'justify-end': isSender, 'justify-start': !isSender }"
    >
        <!-- Avatar for received messages (only shown for first message in a sequence) -->
        <div v-if="!isSender && showAvatar" class="flex-shrink-0 self-end mr-2">
            <img
                :src="message.user.avatar || '/images/default-avatar.png'"
                :alt="`${message.user.name}'s avatar`"
                @error="handleImageError"
                class="w-8 h-8 rounded-full object-cover border border-gray-200"
            >
        </div>

        <!-- Space placeholder when avatar is hidden but alignment needed -->
        <div v-else-if="!isSender && !showAvatar" class="w-10"></div>

        <!-- Message Content -->
        <div
            class="group relative"
            :class="{
        'hover:shadow-md transition-shadow duration-200': true,
        'max-w-[70%] md:max-w-[65%] lg:max-w-[60%]': true
      }"
        >
            <!-- Message Bubble -->
            <div
                class="px-4 py-2 rounded-lg shadow-sm"
                :class="[
          isSender
            ? 'bg-blue-50 text-gray-800 rounded-br-none'
            : ' bg-white text-gray-800 rounded-bl-none',
          message.hasEmoji && message.text.length <= 5 ? 'text-3xl' : ''
        ]"
            >
                <!-- Message Text (with emoji detection and whitespace preservation) -->
                <div class="whitespace-pre-line break-words">
                    <!-- Add emoji styling if detected -->
                    <span v-if="message.hasEmoji && message.text.length <= 5">{{ message.text }}</span>
                    <span v-else>{{ message.text }}</span>
                </div>

                <!-- Message Metadata -->
                <div class="flex items-center justify-end mt-1 space-x-1">
                    <!-- Timestamp -->
                    <span class="text-xs text-gray-500">{{ message.time || message.formattedTime }}</span>

                    <!-- Read status (only for sent messages) -->
                    <span v-if="isSender">
            <svg
                v-if="message.read"
                class="inline-block w-4 h-4 text-blue-500"
                fill="currentColor"
                viewBox="0 0 20 20"
            >
              <path d="M2.5 8.5l3.5 3.5 8.5-8.5 1.5 1.5-10 10-5-5 1.5-1.5z"></path>
            </svg>
            <svg
                v-else
                class="inline-block w-4 h-4 text-gray-400"
                fill="currentColor"
                viewBox="0 0 20 20"
            >
              <path d="M2.5 8.5l3.5 3.5 8.5-8.5 1.5 1.5-10 10-5-5 1.5-1.5z"></path>
            </svg>
          </span>
                </div>
            </div>

            <!-- Message Options (visible on hover) -->
            <div
                class="absolute top-0 right-0 transform translate-x-full opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                :class="{ 'left-0 right-auto -translate-x-full': !isSender }"
            >
                <div class="flex items-center space-x-1 p-1">
                    <button class="p-1 text-gray-500 hover:text-gray-700 focus:outline-none rounded-full hover:bg-gray-100">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'MessageItem',
    props: {
        message: {
            type: Object,
            required: true
        },
        isSender: {
            type: Boolean,
            default: false
        },
        showAvatar: {
            type: Boolean,
            default: true
        }
    },
    setup() {
        const handleImageError = (e) => {
            // Use a default avatar if image fails to load
            e.target.src = '/images/default-avatar.png';
        };

        return {
            handleImageError
        };
    }
};
</script>

<style scoped>
/* Message bubble styles */
.bg-white {
    position: relative;
}

.bg-white::after {
    content: '';
    position: absolute;
    bottom: 0;
    right: -8px;
    width: 0;
    height: 0;
    border: 8px solid transparent;
    border-left-color: white;
    border-bottom-color: white;
}

.bg-blue-50 {
    position: relative;
}

.bg-blue-50::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: -8px;
    width: 0;
    height: 0;
    border: 8px solid transparent;
    border-right-color: #eff6ff; /* bg-blue-50 */
    border-bottom-color: #eff6ff; /* bg-blue-50 */
}

/* Link styling inside messages */
.whitespace-pre-line a {
    color: #3b82f6;
    text-decoration: underline;
}

/* Emoji styling */
.text-3xl {
    font-size: 1.875rem;
    line-height: 2.25rem;
    padding: 0.5rem 0.75rem;
}

/* Hover effect */
.group {
    transition: transform 0.2s ease;
}

.group:hover {
    transform: translateY(-1px);
}
</style>
