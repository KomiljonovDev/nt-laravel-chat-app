<template>
    <div
        class="flex items-center px-4 py-3 cursor-pointer transition-colors duration-150"
        :class="{
      'bg-blue-50 hover:bg-blue-100': isSelected,
      'hover:bg-gray-50': !isSelected,
      'border-b border-gray-100': true
    }"
        @click="$emit('select', contact.id)"
    >
        <!-- Avatar -->
        <div class="relative flex-shrink-0">
            <img
                :src="contact.avatar || '/images/default-avatar.png'"
                :alt="`${contact.name}'s avatar`"
                @error="handleImageError"
                class="w-10 h-10 rounded-full object-cover border border-gray-200"
            >
            <!-- Online status indicator (if needed) -->
            <span
                v-if="contact.online"
                class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"
            ></span>
        </div>

        <!-- Contact Info -->
        <div class="ml-3 flex-1 min-w-0">
            <div class="flex justify-between items-start">
                <h3
                    class="text-sm font-medium truncate transition-colors duration-150"
                    :class="{
            'text-gray-900': !contact.unread,
            'text-gray-900 font-bold': contact.unread
          }"
                >
                    {{ contact.name }}
                </h3>
                <span
                    class="text-xs whitespace-nowrap ml-2 transition-colors duration-150"
                    :class="{
            'text-gray-500': !contact.unread,
            'text-blue-600 font-semibold': contact.unread
          }"
                >
          {{ contact.timestamp }}
        </span>
            </div>
            <p
                class="text-sm truncate mt-0.5 transition-colors duration-150"
                :class="{
          'text-gray-500': !contact.unread,
          'text-gray-700 font-medium': contact.unread
        }"
            >
                {{ contact.lastMessage }}
            </p>
        </div>

        <!-- Unread indicator (blue dot) -->
        <div
            v-if="contact.unread"
            class="w-2.5 h-2.5 rounded-full ml-2 flex-shrink-0 bg-blue-500"
        ></div>

        <!-- Favorite indicator (star icon) -->
        <div v-if="contact.favorite" class="ml-2 text-yellow-400 flex-shrink-0">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
            </svg>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ContactItem',
    props: {
        contact: {
            type: Object,
            required: true
        },
        isSelected: {
            type: Boolean,
            default: false
        }
    },
    emits: ['select'],
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
/* Avatar image hover effect */
img {
    transition: transform 0.2s ease;
}

div:hover img {
    transform: scale(1.05);
}

/* Selected state */
.bg-blue-50 {
    position: relative;
}

.bg-blue-50::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background-color: #3b82f6;
}

/* Truncation with proper ellipsis */
.truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 100%;
}
</style>
