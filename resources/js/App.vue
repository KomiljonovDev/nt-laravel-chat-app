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
        const selectContact = async (Contact) => {
            try {
                // Store the contact temporarily
                const tempContact = Contact;

                if (!tempContact.room_id) {
                    // Get/create room using contact's id
                    const roomData = await getRoomByUser(tempContact.id);
                    if (!roomData || !roomData.roomId) {
                        console.error('Failed to get/create room for contact');
                        return;
                    }

                    // Update the contact in the store with the new room_id
                    const contact = contactsStore.contacts.find(c => String(c.id) === String(tempContact.id));
                    if (contact) {
                        contact.room_id = roomData.roomId;
                        selectedContactId.value = roomData.roomId;
                    }
                } else {
                    selectedContactId.value = tempContact.room_id;
                }

                // Mark as read (in real app, send API request)
                const contact = contactsStore.contacts.find(c => String(c.room_id) === String(selectedContactId.value));
                if (contact) {
                    contact.unread = false;
                }

                // Get messages for selected contact
                await getMessages();
            } catch (err) {
                console.error('Error in selectContact:', err.message);
            }
        };
        const getSelectedContact = () => {
            if (!selectedContactId.value) return null;

            return contactsStore.contacts.find(c => {
                if (c.room_id) {
                    return String(c.room_id) === String(selectedContactId.value);
                }
                // Only check id if we're in the process of creating a room
                return String(c.id) === String(selectedContactId.value);
            }) || null;
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
                if (!selectedContactId.value) {
                    messages.value = [];
                    return;
                }

                // Make sure we have a valid room_id
                const currentContact = getSelectedContact();
                if (!currentContact) {
                    console.error('No valid contact selected');
                    messages.value = [];
                    return;
                }

                // Use the room_id for fetching messages
                const response = await axios.get(`/messages/${selectedContactId.value}`);

                if (!response.data) {
                    console.error('No messages data received');
                    messages.value = [];
                    return;
                }

                messages.value = response.data;

                // Scroll to bottom after messages are loaded
                scrollToBottom();
            } catch (err) {
                console.error('Error fetching messages:', err.message);
                messages.value = [];
            }
        };
        const getRoomByUser = async (user_id) => {
            try {
                const response = await axios.get(`/users/${user_id}/room`);
                if (!response.data) {
                    console.error('No response data received from server');
                    return null;
                }

                if (!response.data.roomId) {
                    console.error('No roomId in response data');
                    return null;
                }

                // Just return the response data as is
                return response.data;
            } catch (err) {
                console.error('Error fetching room:', err.message);
                return null;
            }
        }
        const getRooms = async () => {
            try{
                const response = await axios.get('/rooms');
                contactsStore.getContacts(response.data);
            }catch (err){
                console.error(err);
            }
        };
        const sendMessage = async () => {
            if (newMessage.value.trim() === '') return;
            if (!selectedContactId.value) {
                console.error('No room selected');
                return;
            }

            const messageText = newMessage.value.trim();
            newMessage.value = ''; // Clear input early for better UX

            try {
                const currentContact = getSelectedContact();
                if (!currentContact) {
                    console.error('No valid contact selected');
                    newMessage.value = messageText; // Restore the message
                    return;
                }

                const response = await axios.post('/message', {
                    text: messageText,
                    room_id: selectedContactId.value,
                });

                if (response.data) {
                    // Fetch latest messages instead of manually adding
                    await getMessages(); // This will trigger scrollToBottom

                    // Update the last message in contacts
                    const contact = contactsStore.contacts.find(c =>
                        String(c.room_id) === String(selectedContactId.value)
                    );
                    if (contact) {
                        contact.lastMessage = messageText;
                        contact.timestamp = new Date().toLocaleTimeString([], {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    }
                } else {
                    console.error('Failed to send message');
                    newMessage.value = messageText; // Restore the message
                }
            } catch (err) {
                console.error('Error sending message:', err.message);
                newMessage.value = messageText; // Restore the message if sending failed
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
                const messageContainer = document.querySelector('.messages-container');
                if (messageContainer) {
                    setTimeout(() => {
                        messageContainer.scrollTop = messageContainer.scrollHeight;
                    }, 50); // Use same timeout as ChatWindow
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
            // Initialize user data
            getMe();
            getRooms();

            // Set up Echo for real-time updates
            if (window.Echo) {
                window.Echo.private("channel_for_everyone")
                    .listen('GotMessage', (e) => {
                        // Log raw event and debug the structure
                        console.log('Raw event:', e);
                        console.log('Event message structure:', {
                            direct: e.message,
                            nested: e.message?.message,
                            data: e.data
                        });

                        // In Laravel's broadcast format, the message should be in either e.message or e.data
                        let messageData = e;

                        // Try to get message from possible locations
                        if (e.message?.room_id) {
                            console.log('Found message data directly in e.message');
                            messageData = e.message;
                        } else if (e.message?.message?.room_id) {
                            console.log('Found message data in nested e.message.message');
                            messageData = e.message.message;
                        } else if (e.data?.room_id) {
                            console.log('Found message data in e.data');
                            messageData = e.data;
                        } else if (e.data?.message?.room_id) {
                            console.log('Found message data in e.data.message');
                            messageData = e.data.message;
                        }

                        // If we still don't have message data, log and return
                        if (!messageData || !messageData.room_id) {
                            console.error('Could not find valid message data with room_id. Event structure:', {
                                rawEvent: e,
                                message: e.message,
                                nestedMessage: e.message?.message,
                                data: e.data
                            });
                            return;
                        }

                        // Log the message data we're going to use
                        console.log('Using message data:', messageData);

                        // Handle the message
                        if (String(messageData.room_id) === String(selectedContactId.value)) {
                            console.log('Message is for current room, reloading messages');
                            getMessages();
                            makeSound();
                        } else {
                            const contact = contactsStore.contacts.find(c =>
                                String(c.room_id) === String(messageData.room_id)
                            );
                            if (contact) {
                                console.log('Updating contact with new message:', {
                                    contactId: contact.id,
                                    roomId: messageData.room_id,
                                    text: messageData.text
                                });
                                contact.unread = true;
                                contact.lastMessage = messageData.text;
                                contact.timestamp = messageData.time ||
                                    new Date().toLocaleTimeString([], {
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    });
                                makeSound();
                            } else {
                                console.warn('Could not find contact for room_id:', messageData.room_id);
                            }
                        }
                    });
            }
        });

        return {
            currentUser, // Add currentUser to the return object
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
    } // End of setup
} // End of component export
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
