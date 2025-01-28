// const { useState, useEffect } = wp.element;

// const ChatStateManager = () => {
//     console.log('ChatStateManager initializing...');

//     // Setup broadcast channel for cross-tab communication
//     const channel = new BroadcastChannel('ai24_chat');

//     const [chatState, setChatState] = useState(() => {
//         const stored = localStorage.getItem('AI24AI_chatState');
//         console.log('Initial stored state:', stored);
        
//         if (stored) {
//             const parsedState = JSON.parse(stored);
//             if (parsedState.threadId) {
//                 sessionStorage.setItem('AI24AI_chatThreadId', parsedState.threadId);
//                 sessionStorage.setItem('initialGreetingDisplayed', 'true');
//                 sessionStorage.setItem('AI24AI_preserveThread', 'true');
//             }
//             return parsedState;
//         }   
        
//         return {
//             messages: [],
//             threadId: null,
//             lastUpdated: null
//         };
//     });

//     // Listen for changes from other tabs
//     useEffect(() => {
//         channel.onmessage = (event) => {
//             console.log('Received message from other tab:', event.data);
//             if (event.data.type === 'STATE_UPDATE') {
//                 setChatState(event.data.state);
//                 // Update session storage
//                 sessionStorage.setItem('AI24AI_chatThreadId', event.data.state.threadId);
//                 sessionStorage.setItem('initialGreetingDisplayed', 'true');
//             }
//         };

//         return () => channel.close();
//     }, []);

//     // Add this effect right after useState initialization
//     useEffect(() => {
//         console.log('Attempting to restore messages...');
//         const chatContainer = document.querySelector('#chat-messages');
//         if (!chatContainer || !chatState.messages.length) return;

//         console.log('Restoring messages:', chatState.messages);
        
//         // Clear existing messages
//         chatContainer.querySelectorAll('.chat-message').forEach(el => el.remove());

//         // Restore saved messages
//         chatState.messages.forEach(msg => {
//             const messageDiv = document.createElement('div');
//             messageDiv.className = `chat-message ${msg.role}-message fade-in`;
//             messageDiv.innerHTML = msg.content;
//             chatContainer.appendChild(messageDiv);
//         });
//     }, []); 

//     useEffect(() => {
//         const chatContainer = document.querySelector('#chat-messages');
//         if (!chatContainer) {
//             console.log('Chat container not found');
//             return;
//         }
//         console.log('Chat container found, setting up observer');
        
//         const observer = new MutationObserver((mutations) => {
//             console.log('Chat mutation detected');
            
//             const messages = Array.from(chatContainer.querySelectorAll('.chat-message'))
//                 .map(msg => ({
//                     content: msg.innerHTML,
//                     timestamp: msg.querySelector('.chat-timestamp-assistant, .chat-timestamp-user')?.textContent || '',
//                     role: msg.classList.contains('assistant-message') ? 'assistant' : 'user'
//                 }));

//             console.log('Current messages:', messages);
            
//             const threadId = sessionStorage.getItem('AI24AI_chatThreadId');
//             const newState = {
//                 messages,
//                 threadId,
//                 lastUpdated: new Date().toISOString()
//             };
            
//             console.log('Saving new state:', newState);
//             setChatState(newState);
//             localStorage.setItem('AI24AI_chatState', JSON.stringify(newState));
            
//             // Broadcast to other tabs
//             channel.postMessage({
//                 type: 'STATE_UPDATE',
//                 state: newState
//             });
//         });

//         observer.observe(chatContainer, {
//             childList: true,
//             subtree: true
//         });

//         return () => observer.disconnect();
//     }, []);

//     return null;
// };
// //Potentially add a separate file to load the react into wp-plugin --->
// document.addEventListener('DOMContentLoaded', () => {
//     console.log('DOM loaded, initializing ChatStateManager');
//     const root = document.createElement('div');
//     document.body.appendChild(root);
//     wp.element.render(wp.element.createElement(ChatStateManager), root);
// });


const { useState, useEffect } = wp.element;

const ChatStateManager = () => {
    console.log('ChatStateManager initializing...');

    const [chatState, setChatState] = useState(() => {
        const stored = localStorage.getItem('AI24AI_chatState');
        console.log('Initial stored state:', stored);
        
        if (stored) {
            const parsedState = JSON.parse(stored);
            // Sync with session storage for existing plugin
            if (parsedState.threadId) {
                sessionStorage.setItem('AI24AI_chatThreadId', parsedState.threadId);
                sessionStorage.setItem('initialGreetingDisplayed', 'true');
            }
            return {
                ...parsedState,
                isOpen: false 
            };
        }
        
        return {
            messages: [],
            threadId: null,
            isOpen: false,
            lastUpdated: null
        };
    });

    // Monitor chat visibility changes
    useEffect(() => {
        const chatContainer = document.querySelector('#AI24AI-chatbot-container');
        if (!chatContainer) return;

        console.log('Setting up visibility observers');

        // Monitor minimize button
        const minimizeBtn = document.querySelector('#chatbox-minimize');
        if (minimizeBtn) {
            minimizeBtn.addEventListener('click', () => {
                console.log('Chat minimized');
                setChatState(prev => ({ ...prev, isOpen: false }));
            });
        }

        // Monitor open button
        const toggleBtn = document.querySelector('#AI24AI-chatbot-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                console.log('Chat toggled');
                setChatState(prev => ({ ...prev, isOpen: !prev.isOpen }));
            });
        }

        return () => {
            // Cleanup listeners if needed
            if (minimizeBtn) {
                minimizeBtn.removeEventListener('click', () => {});
            }
            if (toggleBtn) {
                toggleBtn.removeEventListener('click', () => {});
            }
        };
    }, []);

    // Watch chat state changes
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

            console.log('Current messages:', messages);
            
            setChatState(prev => ({
                ...prev,
                messages,
                lastUpdated: new Date().toISOString()
            }));
        });

        observer.observe(chatContainer, {
            childList: true,
            subtree: true
        });

        return () => observer.disconnect();
    }, []);

    // Save state to localStorage whenever it changes
    useEffect(() => {
        console.log('Saving state:', chatState);
        localStorage.setItem('AI24AI_chatState', JSON.stringify(chatState));
    }, [chatState]);


    // Add this new effect to monitor chat container class changes
    useEffect(() => {
        const chatContainer = document.querySelector('#AI24AI-chatbot-container');
        if (!chatContainer) return;
        
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.target.classList.contains('active')) {
                    console.log('Chat container active - opening');
                    setChatState(prev => ({...prev, isOpen: true}));
                } else {
                    console.log('Chat container inactive - closing');
                    setChatState(prev => ({...prev, isOpen: false}));
                }
            });
        });

        observer.observe(chatContainer, {
            attributes: true,
            attributeFilter: ['class']
        });

        // Initial state check
        if (chatContainer.classList.contains('active')) {
            setChatState(prev => ({...prev, isOpen: true}));
        }

        return () => observer.disconnect();
    }, []);

    return null;
};

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    const root = document.createElement('div');
    document.body.appendChild(root);
    wp.element.render(wp.element.createElement(ChatStateManager), root);
});