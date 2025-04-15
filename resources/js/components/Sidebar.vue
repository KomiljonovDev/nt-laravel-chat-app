<template>
    <nav class="flex flex-col h-full bg-white border-r">
        <!-- Search Bar -->
        <div class="p-4">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20"
                         xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                              d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                              clip-rule="evenodd"></path>
                    </svg>
                </div>
                <input
                    type="text"
                    class="w-full py-2 pl-10 pr-4 text-sm text-gray-700 bg-gray-100 rounded-full focus:outline-none focus:bg-white focus:ring-1 focus:ring-blue-500 transition-colors duration-200"
                    placeholder="Search here..."
                    v-model="searchQuery"
                    @input="filterContacts"
                >
            </div>
        </div>

        <!-- Tabs for filtering -->
        <div class="flex px-4 border-b">
            <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="activeTab = tab.id"
                class="py-2 px-4 text-sm font-medium border-b-2 transition-colors duration-200 focus:outline-none"
                :class="[
          activeTab === tab.id
            ? 'border-blue-500 text-blue-600'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
        ]"
            >
                {{ tab.name }}
            </button>
        </div>

        <!-- Contact List -->
        <div class="flex-1 overflow-y-auto contacts-list">
            <transition-group name="contact-list" tag="div">
                <ContactItem
                    v-if="filteredContacts.length"
                    v-for="contact in filteredContacts"
                    :key="contact.id"
                    :contact="contact"
                    :isSelected="String(selectedContactId) === String(contact.room_id) || String(selectedContactId) === String(contact.id)"
                    @select="$emit('select-contact', {
                        id: contact.id,
                        room_id: contact.room_id
                    })"
                />
            </transition-group>

            <!-- Empty state if no contacts match the filter -->
            <div v-if="filteredContacts.length === 0" class="p-4 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                     xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <p>No contacts found</p>
                <button
                    @click="clearSearch"
                    class="mt-2 text-sm text-blue-500 hover:text-blue-700 focus:outline-none transition-colors duration-200"
                    v-if="searchQuery"
                >
                    Clear search
                </button>
            </div>
        </div>
    </nav>
</template>

<script>
import {ref, computed, watch} from 'vue';
import ContactItem from './ContactItem.vue';

export default {
    name: 'Sidebar',
    components: {
        ContactItem
    },
    props: {
        contacts: {
            type: Array,
            default: () => []
        },
        selectedContactId: {
            type: [Number, String], // Allow both number and string types
            default: null
        }
    },
    emits: ['select-contact'],
    setup(props, {emit}) {
        const searchQuery = ref('');
        const tabs = [
            {id: 'all', name: 'All'},
            {id: 'unread', name: 'Unread'},
            {id: 'favorites', name: 'Favorites'}
        ];
        const activeTab = ref('all');

        // Filter contacts based on search query and active tab
        const filteredContacts = computed(() => {
            let filtered = [...props.contacts];

            // Filter by search query
            if (searchQuery.value.trim()) {
                const query = searchQuery.value.toLowerCase();
                filtered = filtered.filter(contact =>
                    contact.name.toLowerCase().includes(query) ||
                    contact.lastMessage.toLowerCase().includes(query)
                );
            }

            // Filter by active tab
            if (activeTab.value === 'unread') {
                filtered = filtered.filter(contact => contact.unread);
            } else if (activeTab.value === 'favorites') {
                filtered = filtered.filter(contact => contact.favorite);
            }

            return filtered;
        });

        // Sort contacts: unread first, then by timestamp (most recent first)
        const sortedContacts = computed(() => {
            return [...filteredContacts.value].sort((a, b) => {
                // Unread contacts first
                if (a.unread && !b.unread) return -1;
                if (!a.unread && b.unread) return 1;

                // Then sort by timestamp (assuming format "HH:MM")
                return b.timestamp.localeCompare(a.timestamp);
            });
        });

        // Debounced filter function to prevent too many updates
        const filterContacts = () => {
            // Additional logic beyond the computed property
        };

        // Clear search and reset filters
        const clearSearch = () => {
            searchQuery.value = '';
            activeTab.value = 'all';
        };

        // Update selectedContactId if the current one is filtered out
        // Update selectedContactId if the current one is filtered out
        watch(sortedContacts, (newValue) => {
            if (props.selectedContactId) {
                const selectedId = String(props.selectedContactId);
                const contactExists = newValue.some(contact => 
                    String(contact.id) === selectedId || 
                    String(contact.room_id) === selectedId
                );
                
                if (!contactExists && newValue.length > 0) {
                    emit('select-contact', {
                        id: newValue[0].id,
                        room_id: newValue[0].room_id
                    });
                }
            }
        });
        // Add a new method to handle contact selection
        const handleContactSelect = (contact) => {
            emit('select-contact', {
                id: contact.id,
                room_id: contact.room_id
            });
        };

        return {
            searchQuery,
            tabs,
            activeTab,
            filteredContacts: sortedContacts,
            filterContacts,
            clearSearch,
            handleContactSelect
        };
    }
};
</script>

<style scoped>
.contacts-list {
    scrollbar-width: thin;
    scrollbar-color: #d1d5db transparent;
}

/* Contact list animations */
.contact-list-enter-active,
.contact-list-leave-active {
    transition: all 0.3s ease;
}

.contact-list-enter-from,
.contact-list-leave-to {
    opacity: 0;
    transform: translateX(-10px);
}

/* Tab indicator animation */
button {
    position: relative;
    overflow: hidden;
}

button::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 50%;
    width: 0;
    height: 2px;
    background-color: #3b82f6;
    transition: width 0.3s ease, left 0.3s ease;
}

button:focus::after,
button:hover::after {
    width: 100%;
    left: 0;
}

button.border-blue-500::after {
    width: 100%;
    left: 0;
}
</style>
