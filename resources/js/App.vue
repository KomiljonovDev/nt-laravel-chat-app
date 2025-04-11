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
                :contacts="contactsStore.filteredContacts"
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
import { useContactsStore } from '@/stores/contactsStore';

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
        const contactsStore = useContactsStore();
        const currentUser = ref({
            id: 2,
            name: 'Dea Novita',
            role: 'Admin',
            avatar: '/images/default-avatar.png'
        });

        // Messages data
        const messages = ref([]);
        const newMessage = ref('');

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
        const selectedContactId = ref(); // Default to first contact

        // Show/hide notifications modal
        const showNotifications = ref(false);

        // Show/hide profile panel
        const showProfilePanel = ref(false);

        // Methods
        const selectContact = (contactId) => {
            selectedContactId.value = contactId;
            getRoomByUser();
            // Mark as read (in real app, send API request)
            const contact = contactsStore.contacts.find(c => c.id === contactId);
            if (contact) contact.unread = false;

            // Get messages for selected contact
            getMessages();
        };

        const getSelectedContact = () => {
            return contactsStore.contacts.find(c => c.id === selectedContactId.value) || null;
        };

        const searchGlobal = (query) => {
            // Use the store's fetchContacts action instead
            contactsStore.fetchContacts(query);
        };

        const toggleProfilePanel = () => {
            showProfilePanel.value = !showProfilePanel.value;
        };

        const getMe = async () => {
            try {
                const response = await axios.get(`/me/`);
                currentUser.value = response.data;
            } catch (err) {
                console.error('Error fetching user:', err.message);
            }
        };
        // Integrate with your existing message functions
        const getMessages = async () => {
            try {
                // In a real app, you'd include the selected contact ID in the request
                const response = await axios.get(`/rooms/${selectedContactId.value}/messages`);
                console.info(selectedContactId);
                messages.value = response.data;
                // Scroll to bottom after messages   are loaded
                scrollToBottom();
            } catch (err) {
                console.error('Error fetching messages:', err.message);
            }
        };
        const getRoomByUser = async () => {
            try {
                const response = await axios.get(`/users/${selectedContactId.value}/room`);
                messages.value = response.data;
            } catch (err) {
                console.error('Error fetching room:', err.message);
            }
        }
        // Fetch contacts (rooms) from the server
        const getRooms = async () => {
            try{
                const response = await axios.get('/rooms');
                contactsStore.getContacts(response.data);
            }catch (err){
                console.error(err);
            }
        };

        const getRooms = async () => {
            try{
                const response = await axios.get('/rooms');
                contacts.value = response.data;
            }catch (err){
                console.error(err);
            }
        };

        const sendMessage = async () => {
            if (newMessage.value.trim() === '') return;

            try {
                // In a real app, you'd send the message to the server
                await axios.post('/message', {
                    text: newMessage.value.trim()
                });

                // For demo purposes, we'll add the message locally
                const message = {
                    id: Date.now(),
                    user: { id: currentUser.value.id, name: currentUser.value.name },
                    text: newMessage.value.trim(),
                    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                };

                messages.value.push(message);

                // Update the last message in contacts
                // Update the last message in contacts
                const contact = contactsStore.contacts.find(c => c.id === selectedContactId.value);
                if (contact) {
                    contact.lastMessage = newMessage.value.trim();
                    contact.timestamp = message.time;
                }
                // Clear input
                newMessage.value = '';

                // Scroll to bottom
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

            // In a real app, you'd send an API request to update the server
        };

        const markAllNotificationsAsRead = () => {
            notifications.value.forEach(notification => {
                notification.read = true;
            });

            // In a real app, you'd send an API request to update the server
        };

        // Lifecycle hooks
        onMounted(() => {
            // Initialize messages for the default selected contact
            getMe();
            getRooms();
            getMessages();

            // Set up Echo for real-time updates
            if (window.Echo) {
                window.Echo.private("channel_for_everyone")
                    .listen('GotMessage', (e) => {
                        console.log('Received message:', e);
                        // Reload messages to include the new one
                        getMessages();
                        makeSound();

                        // If the message is from the currently selected contact,
                        // mark it as read. Otherwise, update unread state.
                        if (e.message && e.message.user_id !== selectedContactId.value) {
                            const contact = contactsStore.contacts.find(c => c.id === e.message.user_id);
                            if (contact) contact.unread = true;
                        }
                    });
            }
        });

        return {
            currentUser,
            contactsStore,
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
}

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
