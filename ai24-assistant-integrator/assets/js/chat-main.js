const { useState, useEffect } = wp.element;

const ChatStateManager = () => {
    // SECTION 1: Broadcast Channel Setup
    // Used for cross-tab communication, enabling state updates (e.g., open/close state) to sync across multiple tabs.
    const channel = new BroadcastChannel('ai24_chat');

    const [paused, setPaused] = useState(false);
    
    // SECTION 2: State Initialization
    // Initializes `chatState` with values from localStorage if available, otherwise sets default values.
    const [chatState, setChatState] = useState(() => {

        const stored = localStorage.getItem('AI24AI_chatState');
        
    
        if (stored) {
            const parsedState = JSON.parse(stored);
            if (parsedState.threadId) {
                sessionStorage.setItem('AI24AI_chatThreadId', parsedState.threadId);
                sessionStorage.setItem('initialGreetingDisplayed', 'true');
            }
            return {
                ...parsedState,
                isOpen: false,
                lastUpdated: new Date().toISOString()
            };
        }
    
        return {
            messages: [],
            threadId: null,
            isOpen: false,
            lastUpdated: new Date().toISOString()
        };
    });
    

    // Define functions for minimize and toggle button clicks
    const handleMinimizeClick = () => {
        const newState = { 
            ...chatState, 
            isOpen: false,
            lastUpdated: new Date().toISOString()
        };
        setChatState(newState);
        channel.postMessage({
            type: 'STATE_UPDATE',
            state: newState
        });
    };

    const handleToggleClick = () => {
        const newState = {
            ...chatState,
            isOpen: !chatState.isOpen,
            lastUpdated: new Date().toISOString()
        };
        setChatState(newState);
        channel.postMessage({
            type: 'STATE_UPDATE',
            state: newState
        });
    };

    // future for handling closes with react -- no need yet
    // const handleCloseClick = () => {
    //     console.log(' Close button clicked - initiating close across all tabs');
    //     channel.postMessage({
    //         type: 'STATE_UPDATE',
    //         action: 'CLOSE_ALL',
    //         state: {
    //             messages: [],
    //             threadId: null,
    //             isOpen: false,
    //             lastUpdated: new Date().toISOString()
    //         }
    //     });
    //     console.log(' Close message broadcast sent');
    // };


    // Function to reset the chat UI while keeping the toggle visible
    const resetChatUI = () => {

        // Hide chat container and remove any active state
        const chatContainer = document.querySelector('#AI24AI-chatbot-container');
        if (chatContainer) {
            chatContainer.classList.remove('active');
            chatContainer.style.display = 'none';
        }

        // Ensure the toggle remains visible and interactive
        const chatToggle = document.querySelector('#AI24AI-chatbot-toggle');
        if (chatToggle) {
            chatToggle.classList.add('toggle-visible');
            chatToggle.classList.remove('hide-toggle');
            chatToggle.style.display = 'block';
            chatToggle.style.pointerEvents = 'auto';
        }
    };

    // SECTION 3: Broadcaster Logic
    useEffect(() => {
        const handleBroadcast = (event) => {            
            switch(event.data.type) {
                case 'CHAT_RESET':
                    setChatState({
                        messages: [],
                        threadId: null,
                        isOpen: false,
                        lastUpdated: event.data.timestamp
                    });
                    break;
                // ... other cases
            }
        };

        channel.onmessage = handleBroadcast;
        return () => channel.close();
    }, [chatState.lastUpdated]);
    
    
    // SECTION 4: Sync chatState with localStorage
    useEffect(() => {
        const handleStorageChange = (event) => {
            if (event.key === 'AI24AI_chatState') {
                
                
                try {
                    const stored = localStorage.getItem('AI24AI_chatState');
                    if (stored) {
                        const parsedState = JSON.parse(stored);
                        if (parsedState.lastUpdated > chatState.lastUpdated) { 
                            setChatState(parsedState);

                        }
                    }
                } catch (error) {
                    console.log('[Storage] Error processing state:', error);
                }
            }
        };

        window.addEventListener('storage', handleStorageChange);
        return () => window.removeEventListener('storage', handleStorageChange);
    }, [chatState.lastUpdated]);

    // SECTION 4: Restore Open Chat State on Load
    // On component mount, restores the open state of the chat if `isOpen` was saved as true in localStorage.
    useEffect(() => {
        const chatContainer = document.querySelector('#AI24AI-chatbot-container');
        const chatToggle = document.querySelector('#AI24AI-chatbot-toggle');
        if (!chatContainer || !chatToggle) return;
    
        const stored = localStorage.getItem('AI24AI_chatState');
        if (stored) {
            const parsedState = JSON.parse(stored);
            if (parsedState.isOpen) {
                chatContainer.style.display = 'block';
                chatContainer.classList.add('active');
                chatToggle.classList.add('hide-toggle');
                chatToggle.classList.remove('toggle-visible');

                // If on mobile, disable pointer events for toggle
                if (window.matchMedia("only screen and (max-width: 540px)").matches) {
                    chatToggle.style.pointerEvents = 'none';
                }
            }
        }
    }, []);
    
    // SECTION 4a: Load Latest `localStorage` Data on Mount
    useEffect(() => {
        const stored = localStorage.getItem('AI24AI_chatState');
        if (stored) {
            const parsedState = JSON.parse(stored);
            if (parsedState.lastUpdated > chatState.lastUpdated) {
                console.log('Initializing with the latest state from localStorage:',);
                setChatState(parsedState);
            }
        }
    }, []);

    // SECTION 5: Restore Messages on Load
    // Restores messages stored in localStorage into the chat message container when the component loads.
    useEffect(() => {
        const chatContainer = document.querySelector('#chat-messages');
        if (!chatContainer) return;
    
        // Add only new messages by comparing existing DOM elements
        const currentMessages = Array.from(chatContainer.children).map(child => child.innerHTML);
        chatState.messages.forEach((msg) => {
            if (!currentMessages.includes(msg.content)) {
                const messageDiv = document.createElement('div');
                messageDiv.className = `chat-message ${msg.role}-message fade-in`;
                messageDiv.innerHTML = msg.content;
                chatContainer.appendChild(messageDiv);
            }
        });
    }, [chatState.messages]);


    // SECTION 6: Toggle Chat Visibility on Load
    // Ensures chat container visibility aligns with `isOpen` state on component mount.
    useEffect(() => {
        const chatContainer = document.querySelector('#AI24AI-chatbot-container');
        if (!chatContainer) return;

        const stored = localStorage.getItem('AI24AI_chatState');
        if (stored) {
            const parsedState = JSON.parse(stored);
            if (parsedState.isOpen) {
                chatContainer.style.display = 'block';
                chatContainer.classList.add('active');
            }
        }
    }, []);

    // SECTION 7: Handle Visibility Change Across Tabs
    // On tab visibility change, checks for the latest state in localStorage and updates `chatState` if a newer version is found.
    // SECTION 7: Handle Visibility Change Across Tabs
    useEffect(() => {
        const handleVisibilityChange = () => {
            if (paused) return;

            if (document.visibilityState === 'visible') {
                const stored = localStorage.getItem('AI24AI_chatState');
                if (!stored) return;

                const parsedState = JSON.parse(stored);
                if (parsedState.lastUpdated > chatState.lastUpdated) {
                    setChatState(parsedState);

                    const chatContainer = document.querySelector('#AI24AI-chatbot-container');
                    const chatToggle = document.querySelector('#AI24AI-chatbot-toggle');
                    if (chatContainer && chatToggle) {
                        if (parsedState.isOpen) {
                            chatContainer.style.display = 'block';
                            chatContainer.classList.add('active');
                            chatToggle.classList.add('hide-toggle');
                            chatToggle.classList.remove('toggle-visible');
                        } else {
                            chatContainer.style.display = 'none';
                            chatContainer.classList.remove('active');
                            chatToggle.classList.remove('hide-toggle');
                            chatToggle.classList.add('toggle-visible');
                        }
                    }
                }
            }
        };

        document.addEventListener('visibilitychange', handleVisibilityChange);
        return () => document.removeEventListener('visibilitychange', handleVisibilityChange);
        }, [chatState.lastUpdated, paused]);



    // SECTION 8: Monitor Button Clicks for Visibility Toggles
    // Sets up listeners on the minimize and toggle buttons to update `chatState.isOpen` and broadcast changes across tabs.
    useEffect(() => {
    const minimizeBtn = document.querySelector('#chatbox-minimize');
    const toggleBtn = document.querySelector('#AI24AI-chatbot-toggle');
    const closeBtn = document.querySelector('#confirm-exit');

    const handleCloseClick = () => {
        // Step 1: Broadcast close event across tabs
        localStorage.setItem('AI24AI_chatClosed', new Date().toISOString());
        localStorage.removeItem('AI24AI_chatState');  // Clear state immediately
        
        // Step 2: Update local state first
        setChatState({
            messages: [],
            threadId: null,
            isOpen: false,
            lastUpdated: new Date().toISOString()
        });

        // Step 3: Broadcast after state is cleared
        const closeEvent = new CustomEvent('chatClose', {
            detail: {
                action: 'CLOSE_ALL',
                timestamp: new Date().toISOString()
            }
        });
        document.dispatchEvent(closeEvent);
    };

    if (minimizeBtn) minimizeBtn.addEventListener('click', handleMinimizeClick);
    if (toggleBtn) toggleBtn.addEventListener('click', handleToggleClick);
    if (closeBtn) closeBtn.addEventListener('click', handleCloseClick);

    return () => {
        if (minimizeBtn) minimizeBtn.removeEventListener('click', handleMinimizeClick);
        if (toggleBtn) toggleBtn.removeEventListener('click', handleToggleClick);
        if (closeBtn) closeBtn.removeEventListener('click', handleCloseClick);
    };
    }, [chatState]);    

    // SECTION 9: Watch for Chat Message Updates
    // Uses a MutationObserver to monitor message additions in the chat container, then updates `chatState.messages`.
    useEffect(() => {
        const chatContainer = document.querySelector('#chat-messages');
        if (!chatContainer) return;
    
        const observer = new MutationObserver((mutations) => {
            const messages = Array.from(chatContainer.querySelectorAll('.chat-message'))
                .map(msg => ({
                    content: msg.innerHTML,
                    timestamp: msg.querySelector('.chat-timestamp-assistant, .chat-timestamp-user')?.textContent || '',
                    role: msg.classList.contains('assistant-message') ? 'assistant' : 'user'
                }));
    
            setChatState(prev => {
                const newState = {
                    ...prev,
                    messages,
                    lastUpdated: new Date().toISOString()
                };
                localStorage.setItem('AI24AI_chatState', JSON.stringify(newState));  // Save to `localStorage` on every change
                return newState;
            });
        });
    
        observer.observe(chatContainer, {
            childList: true,
            subtree: true
        });
    
        return () => observer.disconnect();
    }, []);
    

    // SECTION 10: Persist Chat State in LocalStorage
    // Stores `chatState` in localStorage each time it changes, ensuring state persistence across sessions.
    useEffect(() => {
        localStorage.setItem('AI24AI_chatState', JSON.stringify(chatState));
    }, [chatState]);

    // SECTION 11: Monitor Container Class Changes
    // Watches the chat container’s class for `active` to synchronize UI with `chatState.isOpen`.
    useEffect(() => {
        const chatContainer = document.querySelector('#AI24AI-chatbot-container');
        if (!chatContainer) return;

        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.target.classList.contains('active')) {
                    setChatState(prev => ({...prev, isOpen: true}));
                } else {
                    setChatState(prev => ({...prev, isOpen: false}));
                }
            });
        });

        observer.observe(chatContainer, {
            attributes: true,
            attributeFilter: ['class']
        });

        if (chatContainer.classList.contains('active')) {
            setChatState(prev => ({...prev, isOpen: true}));
        }

        return () => observer.disconnect();
    }, []);


    return null;
};

// Initialize ChatStateManager
document.addEventListener('DOMContentLoaded', () => {
    const root = document.createElement('div');
    document.body.appendChild(root);
    wp.element.render(wp.element.createElement(ChatStateManager), root);
});
