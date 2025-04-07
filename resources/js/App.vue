<template>
    <div class="flex flex-col h-screen bg-gray-50">
        <!-- Header Component -->
        <Header
            :user="currentUser"
            :notifications="notifications"
            :selectedContact="getSelectedContact()"
            @toggle-notifications="toggleNotifications"
            @toggle-profile="toggleProfilePanel"
            @search="searchGlobal"
            class="shadow-sm z-10"
        />

        <!-- Main Content -->
        <div class="flex flex-1 overflow-hidden">
            <!-- Sidebar Component - smaller on larger screens -->
            <Sidebar
                :contacts="filteredContacts"
                :selectedContactId="selectedContactId"
                @select-contact="selectContact"
                class="w-1/4 md:w-1/5 lg:w-1/6 border-r bg-white overflow-y-auto"
            />

            <!-- Chat Window Component - adapt width based on profile panel -->
            <div :class="[
                'flex flex-col',
                showProfilePanel
                    ? 'w-1/2 md:w-3/5 lg:w-2/3'
                    : 'w-3/4 md:w-4/5 lg:w-5/6'
            ]">
                <ChatWindow
                    v-if="selectedContactId"
                    :messages="messages[selectedContactId]"
                    :currentUser="currentUser"
                    :selectedContact="getSelectedContact()"
                    @toggle-profile="toggleProfilePanel"
                    class="flex-1 overflow-hidden"
                    id="messagelist"
                />
                <div v-else class="flex-1 flex items-center justify-center bg-gray-50">
                    <p class="text-gray-500">Select a contact to start chatting</p>
                </div>

                <!-- Message Input Component -->
                <MessageInput
                    v-if="selectedContactId"
                    v-model="newMessage"
                    @send="sendMessage"
                    class="border-t"
                />
            </div>

            <!-- Profile Panel (right side) -->
            <ProfilePanel
                v-if="selectedContactId"
                :contact="getSelectedContact()"
                :isOpen="showProfilePanel"
            />
        </div>

        <!-- Notification Modal (Optional) -->
        <NotificationModal
            v-if="showNotifications"
            :notifications="notifications"
            @close="showNotifications = false"
            @mark-read="markNotificationAsRead"
            @mark-all-read="markAllNotificationsAsRead"
        />
    </div>
</template>

<script>
import axios from 'axios';
import { ref, computed, onMounted, nextTick } from 'vue';
import Header from './components/Header.vue';
import Sidebar from './components/Sidebar.vue';
import ChatWindow from './components/ChatWindow.vue';
import MessageInput from './components/MessageInput.vue';
import NotificationModal from './components/NotificationModal.vue';
import ProfilePanel from './components/ProfilePanel.vue';

export default {
    components: {
        Header,
        Sidebar,
        ChatWindow,
        MessageInput,
        NotificationModal,
        ProfilePanel
    },
    setup() {
        // Current user (would be fetched from API in a real app)
        const currentUser = ref({
            id: 2,
            name: 'Dea Novita',
            role: 'Admin',
            avatar: '/images/default-avatar.png'
        });

        // Messages data (Now stored per contact)
        const messages = ref({}); // {contactId: [messages]}
        const newMessage = ref('');

        // Sample contacts data (would be fetched from API in a real app)
        const contacts = ref([
            {
                id: 1,
                name: 'Arya Wibawa',
                lastMessage: 'Yes, sure! I will fill it out now.',
                timestamp: '10:20',
                avatar: '/images/default-avatar.png',
                unread: false,
                email: 'arya.wibawa@example.com',
                phone: '+62 812-3456-7890',
                location: 'Jakarta, Indonesia',
                lastSeen: 'today'
            },
            {
                id: 2,
                name: 'Vita Darmawan',
                lastMessage: 'Yes, sure! I will fill it out now.',
                timestamp: '10:18',
                avatar: '/images/default-avatar.png',
                unread: true,
                email: 'vita.darmawan@example.com',
                phone: '+62 813-4567-8901',
                location: 'Surabaya, Indonesia',
                lastSeen: 'yesterday'
            },
            {
                id: 3,
                name: 'Purnami Aksa',
                lastMessage: 'Yes, sure! I will fill it out now.',
                timestamp: '10:17',
                avatar: '/images/default-avatar.png',
                unread: false,
                email: 'purnami.aksa@example.com',
                phone: '+62 814-5678-9012',
                location: 'Bali, Indonesia',
                lastSeen: '2 days ago'
            },
            {
                id: 4,
                name: 'Angel Mawar',
                lastMessage: 'Yes, sure! I will fill it out now.',
                timestamp: '10:15',
                avatar: '/images/default-avatar.png',
                unread: true,
                email: 'angel.mawar@example.com',
                phone: '+62 815-6789-0123',
                location: 'Bandung, Indonesia',
                lastSeen: 'today'
            },
            {
                id: 5,
                name: 'Lily Indrawan',
                lastMessage: 'Yes, sure! I will fill it out now.',
                timestamp: '10:12',
                avatar: '/images/default-avatar.png',
                unread: false,
                email: 'lily.indrawan@example.com',
                phone: '+62 816-7890-1234',
                location: 'Yogyakarta, Indonesia',
                lastSeen: '3 days ago'
            }
        ]);

        // Search query for filtering contacts
        const searchQuery = ref('');

        // Filtered contacts based on search query
        const filteredContacts = computed(() => {
            if (!searchQuery.value) return contacts.value;

            const query = searchQuery.value.toLowerCase();
            return contacts.value.filter(contact =>
                contact.name.toLowerCase().includes(query) ||
                contact.lastMessage.toLowerCase().includes(query)
            );
        });

        // Notifications
        const notifications = ref([
            {
                id: 1,
                title: 'New message',
                text: 'You have received a new message from Arya Wibawa',
                timestamp: '10:24',
                read: false
            },
            {
                id: 2,
                title: 'Appointment reminder',
                text: 'Your next appointment is scheduled for tomorrow at 2 PM',
                timestamp: '09:15',
                read: true
            }
        ]);

        // Track selected contact
        const selectedContactId = ref(1); // Default to first contact

        // Show/hide notifications modal
        const showNotifications = ref(false);

        // Show/hide profile panel
        const showProfilePanel = ref(false);

        // Methods
        const selectContact = (contactId) => {
            selectedContactId.value = contactId;

            // Mark as read (in real app, send API request)
            const contact = contacts.value.find(c => c.id === contactId);
            if (contact) contact.unread = false;

            // Get messages for selected contact
            getMessages();
        };

        const getSelectedContact = () => {
            return contacts.value.find(c => c.id === selectedContactId.value) || null;
        };

        const searchGlobal = (query) => {
            searchQuery.value = query;
            // In a real app, you might want to perform a more comprehensive search
        };

        const toggleProfilePanel = () => {
            showProfilePanel.value = !showProfilePanel.value;
        };

        const getMe = async () => {
            try {
                const response = await axios.get(`/me/`);
                currentUser.value = response.data;
                console.info(currentUser.value);
            } catch (err) {
                console.error('Error fetching user:', err.message);
            }
        };

        // Fetch messages for selected contact
        const getMessages = async () => {
            try {
                // Fetch messages based on selected contact ID
                const response = await axios.get(`/messages/${selectedContactId.value}`);
                messages.value[selectedContactId.value] = response.data;
                // Scroll to bottom after messages are loaded
                scrollToBottom();
            } catch (err) {
                console.error('Error fetching messages:', err.message);
            }
        };

        // Send a new message
        const sendMessage = async () => {
            if (newMessage.value.trim() === '') return;

            try {
                // Send message to the server
                await axios.post('/message', {
                    contact_id: selectedContactId.value,
                    text: newMessage.value.trim()
                });

                // Add message locally
                const message = {
                    id: Date.now(),
                    user: { id: currentUser.value.id, name: currentUser.value.name },
                    text: newMessage.value.trim(),
                    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                };

                // Ensure messages for selected contact exist
                if (!messages.value[selectedContactId.value]) {
                    messages.value[selectedContactId.value] = [];
                }

                // Add the new message to the list
                messages.value[selectedContactId.value].push(message);

                // Update the contact's last message
                const contact = contacts.value.find(c => c.id === selectedContactId.value);
                if (contact) {
                    contact.lastMessage = newMessage.value.trim();
                    contact.timestamp = message.time;
                }

                // Clear the input field
                newMessage.value = '';
                scrollToBottom();
            } catch (err) {
                console.error('Error sending message:', err.message);
            }
        };

        // Scroll to the bottom of the chat window
        const scrollToBottom = () => {
            nextTick(() => {
                const chatWindow = document.getElementById('messagelist');
                if (chatWindow) {
                    chatWindow.scrollTop = chatWindow.scrollHeight;
                }
            });
        };

        onMounted(getMe);

        return {
            currentUser,
            messages,
            newMessage,
            contacts,
            searchQuery,
            filteredContacts,
            selectedContactId,
            notifications,
            showNotifications,
            showProfilePanel,
            getMessages,
            sendMessage,
            selectContact,
            getSelectedContact,
            toggleProfilePanel,
            searchGlobal
        };
    }
};
</script>
