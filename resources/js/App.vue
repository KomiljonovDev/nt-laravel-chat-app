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
                    :messages="messages"
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


axios.defaults.baseURL = 'http://localhost:9000';
axios.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('auth_token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

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
        // Current user
        const currentUser = ref(null);

        // Messages data
        const messages = ref([]);
        const newMessage = ref('');

        // Contacts data
        const contacts = ref([]);

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
        const selectedContactId = ref(null);

        // Show/hide notifications modal
        const showNotifications = ref(false);

        // Show/hide profile panel
        const showProfilePanel = ref(false);

        // Login metodi (vaqtincha App.vue’da)
        const login = async (email, password) => {
            try {
                const response = await axios.post('/api/login', {
                    email,
                    password
                });
                const { token, user } = response.data;
                localStorage.setItem('auth_token', token); // Tokenni saqlash
                currentUser.value = user; // Foydalanuvchi ma'lumotlarini o‘rnatish
                return true;
            } catch (err) {
                console.error('Login error:', err.message);
                return false;
            }
        };

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
        };

        const toggleProfilePanel = () => {
            showProfilePanel.value = !showProfilePanel.value;
        };

        const getMe = async () => {
            try {
                const token = localStorage.getItem('auth_token');
                const response = await axios.get('/api/me', {
                    headers: {
                        Authorization: `Bearer ${token}`
                    }
                });
                currentUser.value = response.data;
            } catch (err) {
                console.error('Error fetching user:', err.message);
            }
        };


        const getMessages = async () => {
            try {
                const response = await axios.get('/api/messages');
                messages.value = response.data;
                scrollToBottom();
            } catch (err) {
                console.error('Error fetching messages:', err.message);
            }
        };

        const getRooms = async () => {
            try {
                const response = await axios.get('/api/rooms');
                contacts.value = response.data;
            } catch (err) {
                console.error('Error fetching rooms:', err.message);
            }
        };

        const sendMessage = async () => {
            if (newMessage.value.trim() === '') return;

            try {
                await axios.post('/api/message', {
                    text: newMessage.value.trim()
                });

                const message = {
                    id: Date.now(),
                    user: { id: currentUser.value.id, name: currentUser.value.name },
                    text: newMessage.value.trim(),
                    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                };

                messages.value.push(message);

                const contact = contacts.value.find(c => c.id === selectedContactId.value);
                if (contact) {
                    contact.lastMessage = newMessage.value.trim();
                    contact.timestamp = message.time;
                }

                newMessage.value = '';
                scrollToBottom();
            } catch (err) {
                console.error('Error sending message:', err.message);
            }
        };

        const makeSound = () => {
            const audio = new Audio('/sounds/tik.wav');
            audio.play().catch((e) => {
                console.warn('Autoplay prevented:', e.message);
            });
        }

        const scrollToBottom = () => {
            nextTick(() => {
                const messageList = document.getElementById('messagelist');
                if (messageList) {
                    messageList.scrollTop = messageList.scrollHeight;
                }
            });
        };

        const toggleNotifications = () => {
            showNotifications.value = !showNotifications.value;
        };

        const markNotificationAsRead = (id) => {
            const notification = notifications.value.find(n => n.id === id);
            if (notification) notification.read = true;
        };

        const markAllNotificationsAsRead = () => {
            notifications.value.forEach(notification => {
                notification.read = true;
            });
        };

        onMounted(() => {
            getMe();
            getRooms();
            getMessages();

            // Set up Echo for real-time updates
            if (window.Echo) {
                window.Echo.private("channel_for_everyone")
                    .listen('GotMessage', (e) => {
                        console.log('Received message:', e);
                        getMessages();
                        makeSound();

                        if (e.message && e.message.user_id !== selectedContactId.value) {
                            const contact = contacts.value.find(c => c.id === e.message.user_id);
                            if (contact) contact.unread = true;
                        }
                    });
            }
        });


        return {
            currentUser,
            contacts,
            filteredContacts,
            selectedContactId,
            messages,
            newMessage,
            notifications,
            showNotifications,
            showProfilePanel,
            selectContact,
            getSelectedContact,
            sendMessage,
            searchGlobal,
            toggleNotifications,
            toggleProfilePanel,
            markNotificationAsRead,
            markAllNotificationsAsRead
        };
    }
};
</script>

<style>
/* Base styles */
html, body {
    height: 100%;
    margin: 0;
    padding: 0;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    color: #333;
}

/* Scrollbar styling */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

/* Animations */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-right-enter-active,
.slide-right-leave-active {
    transition: transform 0.3s ease;
}

.slide-right-enter-from,
.slide-right-leave-to {
    transform: translateX(-20px);
    opacity: 0;
}
</style>
