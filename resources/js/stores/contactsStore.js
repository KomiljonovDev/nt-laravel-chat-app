import { defineStore } from 'pinia';
import axios from 'axios';

export const useContactsStore = defineStore('contacts', {
    state: () => ({
        contacts: [
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
            }
        ],
        originalContacts: [],
        searchQuery: '',
        isSearching: false
    }),
    getters: {
        filteredContacts: (state) => {
            if (!state.searchQuery || state.searchQuery.length <= 2) {
                return state.contacts;
            }
            
            const query = state.searchQuery.toLowerCase();
            return state.contacts.filter(contact => 
                contact.name.toLowerCase().includes(query) || 
                (contact.lastMessage && contact.lastMessage.toLowerCase().includes(query))
            );
        }
    },
    actions: {
        getContacts(contacts) {
            this.contacts = contacts;
            // Store original contacts for restoring later
            this.originalContacts = [...contacts];
        },
        
        setSearchQuery(query) {
            this.searchQuery = query;
            
            // If query is empty or too short, restore original contacts
            if (!query || query.length <= 2) {
                if (this.isSearching) {
                    this.contacts = [...this.originalContacts];
                    this.isSearching = false;
                }
            }
        },
        
        async fetchContacts(query) {
            try {
                this.setSearchQuery(query);
                
                // Only fetch from API if query is 3 or more characters
                if (query && query.length > 2) {
                    this.isSearching = true;
                    const response = await axios.get('search?q=' + query);
                    this.contacts = response.data;
                }
            } catch (error) {
                console.error('Error searching contacts:', error.message);
                // Restore original contacts on error
                this.contacts = [...this.originalContacts];
                this.isSearching = false;
            }
        }
    },
});
