function linkify(inputText) {
    var markdownLinkPattern = /\[([^\]]+)]\((http[s]?:\/\/[^\s]+)\)/g;
    var replacedText = inputText.replace(markdownLinkPattern, '<a href="$2" target="_blank">$1</a>');
    var urlPattern = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])(?![^<]*>|[^<>]*<\/a>)/gim;
    replacedText = replacedText.replace(urlPattern, '<a href="$1" target="_blank">$1</a>');
    var pseudoUrlPattern = /(^|[^\/])(www\.[\S]+(\b|$))(?![^<]*>|[^<>]*<\/a>)/gim;
    replacedText = replacedText.replace(pseudoUrlPattern, '$1<a href="http://$2" target="_blank">$2</a>');
    var emailAddressPattern = /(\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,6})(?![^<]*>|[^<>]*<\/a>)/gim;
    replacedText = replacedText.replace(emailAddressPattern, '<a href="mailto:$1">$1</a>');
    var phoneNumberPattern = /(\+1|1)?(?:\W*\d{3}\W*)\d{3}\W*\d{4}(?![^<]*>|[^<>]*<\/a>)/gim;
    replacedText = replacedText.replace(phoneNumberPattern, '<a href="tel:$&">$&</a>');

    return replacedText;
}



// Function to sanitize HTML input
function sanitizeHtml(str) {
    var temp = document.createElement('div');
    temp.textContent = str;
    return temp.innerHTML;
}



jQuery(document).ready(function($) {
    var chatToggleSelector = '#AI24AI-chatbot-toggle';
    var chatContainerSelector = '#AI24AI-chatbot-container';
    var chatInputSelector = '#chat-input';
    var chatMessagesSelector = '#chat-messages';
    var chatMinimizeSelector = '#chatbox-minimize';
    var chatCloseSelector = '#chatbox-close';
    var confirmExitSelector = '#confirm-exit';
    var cancelExitSelector = '#cancel-exit';
    var isTransitioning = false;
    var isRequestPending = false;
    var typingAnimation;
    var typingIndicator;
    var userOpenedChat = false;
    var typingTimeouts = [];
    var welcomeMessageInProgress = false
    var displayMessagesInstantly = false;
    var currentMessageIndex = 0;
    var baseHeight = 50;

    /*RadicalK */
    // if (localStorage.getItem('AI24AI_chatThreadId') /*&& localStorage.getItem('AI24AI_chatOpen') === 'true'*/) {
    //     console.log('Existing chat found, and was previously open...');
    //     $(chatContainerSelector).show().addClass('active');
    //     userOpenedChat = true;
    //     sessionStorage.setItem('initialGreetingDisplayed', 'true');
    // }
    // original below::
    // sessionStorage.removeItem('initialGreetingDisplayed');
    // clearThreadId();


    /*RK here ends */

    // Function to fix input bar sizing
    $('#chat-input').on('input', function() {
        var baseHeight = 50; // This should be the height of the input field for one line of text, including padding.
        this.style.height = baseHeight + 'px'; // Reset to base height before calculating scrollHeight
        var newHeight = this.scrollHeight > baseHeight ? this.scrollHeight : baseHeight;
        this.style.height = newHeight + 'px'; // Set the new height
    }).on('keydown', function(e) {
        if (e.keyCode === 13 && e.shiftKey) { // Shift+Enter is pressed
            e.preventDefault();
            var start = this.selectionStart;
            var end = this.selectionEnd;

            // Insert the new line at the current cursor position
            this.value = this.value.substring(0, start) + '\n' + this.value.substring(end);

            // Move the cursor to the new position
            this.selectionStart = this.selectionEnd = start + 1;

            // Trigger the input event to resize the textarea
            $(this).trigger('input');
        }
    }).on('keyup', function(e) {
        if (e.keyCode == 13 && !e.shiftKey) { // Enter key is pressed without Shift or input is clear
            this.style.height = baseHeight + 'px'; // Reset to initial height
        }
    }); 
    
    // Function to move the typing indicator to the bottom of the chat
    function moveTypingIndicatorToBottom() {
        if (typingIndicator) {
            typingIndicator.detach(); // Detach the typing indicator from its current location
            $(chatMessagesSelector).append(typingIndicator); // Re-append it to move it to the bottom
        }
    }

    // Function to create the typing indicator element at the bottom
    function createTypingIndicator() {
        if (typingIndicator) {
            typingIndicator.remove(); // Remove any existing indicator
        }

        // Create a container for the typing indicator without message styles
        typingIndicator = $('<div class="typing-indicator">' +
                            '<div id="typing-animation" class="typing-animation-style"></div>' +
                            '</div>');

        // Append it to the chat messages container
        $(chatMessagesSelector).append(typingIndicator);

        // Initialize the Lottie animation within this container
        typingAnimation = lottie.loadAnimation({
            container: document.getElementById('typing-animation'), // Directly target the new div
            renderer: 'svg',
            loop: true,
            autoplay: false,
            path: AI24AI_params.typingAnimationPath // Use the localized path
        });

        moveTypingIndicatorToBottom(); // Move the indicator to the bottom after creation
    }

    // Call createTypingIndicator on script load to ensure it's ready when needed
    createTypingIndicator();

    // Function to show the typing indicator
    function showTypingIndicator() {
        if (!typingIndicator) {
            createTypingIndicator();
        }
        typingIndicator.show();
        typingAnimation.play();
        moveTypingIndicatorToBottom(); // Ensure it is at the bottom every time it is shown
        // Trigger a scroll after a slight delay to account for the space the indicator will occupy
        setTimeout(function() {
            $(chatMessagesSelector).scrollTop($(chatMessagesSelector)[0].scrollHeight);
        }, 100); // The delay can be adjusted if necessary
    }

    // Function to hide the typing indicator
    function hideTypingIndicator() {
        if (typingIndicator) {
            typingIndicator.hide();
            typingAnimation.stop();
        }
    }

    // Initially hide the typing indicator until needed
    hideTypingIndicator();

    //Initial Greeting
    function displayInitialGreeting() {
        /*Atharva changed this ---> */
        // Check localStorage instead of sessionStorage
        if (localStorage.getItem('initialGreetingDisplayed')) {
        return; 
        }
        localStorage.setItem('initialGreetingDisplayed', 'true');
        welcomeMessageInProgress = true;
        /*Atharva changed this ---> */
            sessionStorage.setItem('initialGreetingDisplayed', 'true'); // Flag the session right away
            welcomeMessageInProgress = true;
            
            // Function to display a single message with typing animation
            function displayMessageWithTypingAnimation(messages, index = 0) {
                if (displayMessagesInstantly) {
                    displayAllRemainingMessages(messages);
                    return; // Exit the function as we're done displaying messages.
                }
                if (index < messages.length) {
                    showTypingIndicator();
                    
                    // Clear any previous timeouts for safety
                    typingTimeouts.forEach(clearTimeout);
                    
                    // Create a new timeout for the typing indicator
                    var timeoutId = setTimeout(function() {
                        hideTypingIndicator();
                        var message = messages[index];
                        var currentTime = new Date();
                        var messageTimestamp = currentTime.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
                        
                        // Append message to chat area
                        $(chatMessagesSelector).append('<div class="chat-message assistant-message fade-in">' + 
                            message + 
                            '<span class="chat-timestamp-assistant">' + messageTimestamp + '</span>' + 
                            '</div>');
                        $(chatMessagesSelector).scrollTop($(chatMessagesSelector)[0].scrollHeight);
                        
                        currentMessageIndex = index + 1;
                        displayMessageWithTypingAnimation(messages, index + 1);
                    }, 1600); // Adjust this delay to manage the typing speed
                    
                    // Store the timeoutId to clear later if needed
                    typingTimeouts.push(timeoutId);
                } else {
                    welcomeMessageInProgress = false; // Sequence complete, allow re-entry if session cleared
                    // Clear all timeouts since the animation sequence is complete
                    typingTimeouts.forEach(clearTimeout);
                    typingTimeouts = []; // Clear the array for next use
                }
            }
            
            // Start the message display process after an initial delay
            var initialDelayId = setTimeout(() => {
                displayMessageWithTypingAnimation(AI24AI_params.contextMessages);
            }, 750); // Adjust this initial delay as needed
            
        typingTimeouts.push(initialDelayId); // Store the initial delay timeoutId as well
    }

    // Function to hide the toggle button when the chatbox is active, specifically for the sidebar option
    function handleSidebarToggle() {
        var widgetPosition = 'sidebar'; // Replace this with the actual condition to determine if the sidebar option is active
        var $chatToggle = $(chatToggleSelector);
        var $chatContainer = $(chatContainerSelector);
        
        if (widgetPosition === 'sidebar' && $chatContainer.hasClass('active')) {
            $chatToggle.addClass('hide-toggle'); // Hides the toggle button
        } else {
            $chatToggle.removeClass('hide-toggle'); // Shows the toggle button
        }
    }
    
    // Function to toggle chat
    // function toggleChatWidget() {
    //     if (isTransitioning) return;
    //     isTransitioning = true;

    //     var $chatContainer = $(chatContainerSelector);
    //     var $chatToggle = $(chatToggleSelector);

    //     // Check if we are on a mobile device
    //     var isMobile = window.matchMedia("only screen and (max-width: 540px)").matches;

    //     if ($chatContainer.hasClass('active')) {
    //         // If chat is currently active, hide it
    //         $chatContainer.removeClass('active').one('transitionend', function() {
    //             $(this).hide();
    //             isTransitioning = false; 
    //             if (isMobile) {
    //                 $chatToggle.addClass('toggle-visible').removeClass('hide-toggle'); // Make toggle reappear smoothly on mobile when chat is closed
    //                 $chatToggle.css('pointer-events', 'auto'); // Enable pointer events
    //             } else {
    //                 // Handle the toggle for the sidebar when it's not a mobile device
    //                 handleSidebarToggle(); // Call to hide or show the sidebar toggle
    //             }
    //             $chatToggle.prop('disabled', false);
    //         });
    //     } else {
    //         // If chat is not active, show it
    //         userOpenedChat = true;
    //         $chatContainer.show(0, function() {
    //             $(this).addClass('active');
    //             isTransitioning = false;
    //             if (isMobile) {
    //                 $chatToggle.addClass('hide-toggle').removeClass('toggle-visible'); // Hide the toggle button when chat is opened on mobile
    //                 $chatToggle.css('pointer-events', 'none'); // Disable pointer events
    //             } else {
    //                 // Handle the toggle for the sidebar when it's not a mobile device
    //                 handleSidebarToggle(); // Call to hide or show the sidebar toggle
    //             }
    //             $chatToggle.prop('disabled', false);
    //             if (!sessionStorage.getItem('initialGreetingDisplayed')) {
    //                 displayInitialGreeting();
    //             }

    //             // Focus the input field immediately
    //             // $(chatInputSelector).focus();
    //         });
    //     }
    // }

    function toggleChatWidget() {
        if (isTransitioning) return;
        isTransitioning = true;
    
        var $chatContainer = $(chatContainerSelector);
        var $chatToggle = $(chatToggleSelector);
    
        // Check if we are on a mobile device
        var isMobile = window.matchMedia("only screen and (max-width: 540px)").matches;
    
        if ($chatContainer.hasClass('active')) {
            // If chat is currently active, hide it
            $chatContainer.removeClass('active').one('transitionend', function() {
                $(this).hide();
                isTransitioning = false; 
                if (isMobile) {
                    $chatToggle.addClass('toggle-visible').removeClass('hide-toggle');
                    $chatToggle.css('pointer-events', 'auto');
                } else {
                    handleSidebarToggle();
                }
                $chatToggle.prop('disabled', false);
            });
        } else {
            // If chat is not active, show it
            userOpenedChat = true;
            $chatContainer.show(0, function() {
                $(this).addClass('active');
                isTransitioning = false;
                if (isMobile) {
                    $chatToggle.addClass('hide-toggle').removeClass('toggle-visible');
                    $chatToggle.css('pointer-events', 'none');
                } else {
                    handleSidebarToggle();
                }
                $chatToggle.prop('disabled', false);
                if (!sessionStorage.getItem('initialGreetingDisplayed')) {
                    displayInitialGreeting();
                }
            });
        }
    }


    $(chatToggleSelector).on('click', toggleChatWidget);

    function displayAllRemainingMessages(messages) {
        if (currentMessageIndex < messages.length) {
            messages.slice(currentMessageIndex).forEach(function(message) {
                var currentTime = new Date();
                var messageTimestamp = currentTime.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
        
                // Append the remaining messages to ensure they are in the correct position
                $(chatMessagesSelector).append('<div class="chat-message assistant-message fade-in">' + 
                    message + 
                    '<span class="chat-timestamp-assistant">' + messageTimestamp + '</span>' + 
                    '</div>');
            });
    
            // After displaying all remaining messages, update the currentMessageIndex
            currentMessageIndex = messages.length;
    
            // Reset the flag to indicate the welcome message sequence is complete
            welcomeMessageInProgress = false;
    
            // Scroll to the newest message
            $(chatMessagesSelector).scrollTop($(chatMessagesSelector)[0].scrollHeight);
        }
    }

    // Minimize chat functionality
    $(chatMinimizeSelector).on('click', function() {

        //radkang -- added -->
        // localStorage.setItem('AI24AI_chatOpen', 'false');
        //radkang -- added -->

        if (isTransitioning) return;
        isTransitioning = true;

        

        // Toggle the chat container's visibility.
        $(chatContainerSelector).toggleClass('active');

        if (window.matchMedia("(max-width: 768px)").matches) {
            // Mobile-specific behavior
            if ($(chatContainerSelector).hasClass('active')) {
                $(chatToggleSelector).addClass('hide-toggle').removeClass('toggle-visible');
            } else {
                $(chatToggleSelector).removeClass('hide-toggle').addClass('toggle-visible');
                // Ensure it is clickable again
                $(chatToggleSelector).css('pointer-events', 'auto');
            }
        } else {
            // Non-mobile behavior
            handleSidebarToggle(); // Update the toggle button visibility for sidebar
        }

        // Listen for the end of the transition to reset the isTransitioning flag.
        $(chatContainerSelector).one('transitionend', function() {
            isTransitioning = false;
        });
    });
    
    // Close button functionality
    $(chatCloseSelector).on('click', function() {
        $('#exit-confirmation-modal').addClass('active'); // Show the modal

        //radkang -- added -->
        // localStorage.setItem('AI24AI_chatOpen', 'false');
        //radkang -- added -->
    });

    // Cancel exit functionality
    $(cancelExitSelector).on('click', function() {
        $('#exit-confirmation-modal').removeClass('active'); // Hide the modal
    });

    function clearInitialMessageTimeouts() {
        typingTimeouts.forEach(clearTimeout); // Clear timeouts for pending messages
        typingTimeouts = [];
        welcomeMessageInProgress = false;
    }

    // Function to confirm exit
    $(confirmExitSelector).on('click', function() {
        if (isTransitioning) return;
        isTransitioning = true;
        clearThreadId();

        // Clear timeouts and messages
        clearInitialMessageTimeouts(); // Assuming this now also removes messages as per your update

        // Additional steps to reset the chat state fully
        hideTypingIndicator(); // Ensure typing indicator is hidden
        sessionStorage.removeItem('initialGreetingDisplayed'); // Allow initial greetings to re-trigger if chat is reopened
        welcomeMessageInProgress = false; // Reset this flag
        displayMessagesInstantly = false; // Reset this if you're using it to control message display logic
        userOpenedChat = false; 
        $('#chat-messages .chat-message').remove(); 

        // Handle UI transitions
        $(chatContainerSelector).removeClass('active').one('transitionend', function() {
            $(this).hide();
            isTransitioning = false;

            if (window.matchMedia("(max-width: 768px)").matches) {
                setTimeout(function() {
                    $(chatToggleSelector).removeClass('hide-toggle').addClass('toggle-visible').css({
                        'opacity': '1',
                        'pointer-events': 'auto'
                    });
                }, 20);
            } else {
                // Call handleSidebarToggle to slide the toggle back into view
                handleSidebarToggle(); // Update the toggle button visibility for sidebar
            }
        });

        $('#exit-confirmation-modal').removeClass('active');
        $(chatToggleSelector).prop('disabled', false);
    });

   /*ATHARVA CHANGED THIS:: */

    function storeThreadId(threadId) {
        // Store in both session and local storage
        sessionStorage.setItem('AI24AI_chatThreadId', threadId);
        localStorage.setItem('AI24AI_chatThreadId', threadId);
    }
    
    function retrieveThreadId() {
        // Check session first, then local
        return sessionStorage.getItem('AI24AI_chatThreadId') || 
               localStorage.getItem('AI24AI_chatThreadId') || 
               '';
    }
    
    /*ATHARVA CHANGED THIS:: */

function clearThreadId() {
    sessionStorage.removeItem('AI24AI_chatThreadId');
}

// Event listener for the Enter key press in the chat input
$(document).ready(function() {
    function sendMessage(user_input, inputElement) {
        if (user_input !== '') {
            if (welcomeMessageInProgress) {
                clearInitialMessageTimeouts();
                displayAllRemainingMessages(AI24AI_params.contextMessages);
            }

            inputElement.style.height = '50px';
            $(inputElement).val('');

                var currentTime = new Date();
                var userTimestamp = currentTime.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });

                // Sanitize and convert newlines to <br> for HTML rendering
                var sanitizedInput = sanitizeHtml(user_input).replace(/\n/g, '<br>');
                var formattedMessage = linkify(sanitizedInput);
            
                // Append user message and timestamp in the same div
                var messageHtml = '<div class="chat-message user-message fade-in">' +
                    formattedMessage +  // The actual message content
                    '<span class="chat-timestamp-user">' + userTimestamp + '</span>' + // The timestamp
                    '</div>';
            
                // Append the combined message and timestamp to the chat messages
                $(chatMessagesSelector).append(messageHtml);
                $(chatMessagesSelector).scrollTop($(chatMessagesSelector)[0].scrollHeight);
                $(this).val('');
            
                isRequestPending = true;

                // console.log("Nonce being sent in AJAX request:", AI24AI_params.nonce);
                
                var ajaxData = {
                    action: 'AI24AI_chat',
                    nonce: AI24AI_params.nonce,
                    user_input: user_input,
                    start_new_thread: !retrieveThreadId() 
                };

                if (retrieveThreadId()) {
                    ajaxData.thread_id = retrieveThreadId(); 
                }

                $.ajax({
                    url: AI24AI_params.ajax_url,
                    type: 'POST',
                    dataType: 'json',
                    data: ajaxData,
                    beforeSend: function() {
                        showTypingIndicator();
                    },
                    success: function(response) {
                        if (response.success) {
                            var assistantMessages = response.data.messages.filter(function(message) {
                                return message.role === 'assistant';
                            });
                            
                            // Check if there are any assistant messages
                            if (assistantMessages.length > 0) {
                                var latestAssistantMessage = assistantMessages[0];
                                var messageContentArray = latestAssistantMessage.content || latestAssistantMessage.clean_content;
                                if (Array.isArray(messageContentArray) && messageContentArray.length > 0) {
                                    var messageText = messageContentArray[0]['text'] && messageContentArray[0]['text']['value'] ? messageContentArray[0]['text']['value'] : 'Message content not available';
                                } else {
                                    console.log("No content available or content structure is incorrect", messageContentArray);
                                }

                                // Convert links in the message content
                                messageText = linkify(messageText);

                                var currentTime = new Date();
                                var assistantTimestamp = currentTime.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });

                                // Ensure HTML content is rendered correctly
                                var assistantMessageHtml = '<div class="chat-message assistant-message fade-in">' +
                                    '<span class="message-content">' + messageText + '</span>' + 
                                    '<span class="chat-timestamp-assistant">' + assistantTimestamp + '</span>' + 
                                    '</div>';
                                $(chatMessagesSelector).append(assistantMessageHtml);
                                $(chatMessagesSelector).scrollTop($(chatMessagesSelector)[0].scrollHeight);
                            }

                            // Store the thread ID if it's provided in the response
                            if (response.data.thread_id) {
                                storeThreadId(response.data.thread_id);
                            }
                        } else {
                           // Log removed: console.error('Error:', response.data.error);
                        }
                    },                    
                    error: function(xhr, status, error) {
                        // Log removed: console.error('AJAX error:', status, error);
                    },
                    complete: function() {
                        hideTypingIndicator();
                        isRequestPending = false;
                        $(chatInputSelector).focus(); // Make sure to remove focus from the input field
                    
                        // Scroll adjustment to encourage the browser to reset zoom
                        setTimeout(function() {
                            if(window.scrollY != 0) {
                                window.scrollTo({top: window.scrollY - 1});
                                window.scrollTo({top: window.scrollY + 1});
                            }
                        }, 50);
                        
                        // Ensure focus is maintained after assistant message is appended
                        setTimeout(function() {
                            $(chatInputSelector).focus();
                        }, 100);
                    }                    
                });
            }
        }

        // Event listener for the Enter key press in the chat input
        $(chatInputSelector).on('keypress', function(e) {
            if (e.which == 13 && e.shiftKey) {
                // Allow shift+enter to insert a newline
                var cursorPos = this.selectionStart;
                var value = $(this).val();
                $(this).val(value.substring(0, cursorPos) + "\n" + value.substring(cursorPos));
                this.selectionStart = this.selectionEnd = cursorPos + 1; // Move cursor to the next line
                e.stopPropagation(); // Prevent the default action
            } else if (e.which == 13 && !e.shiftKey && !isRequestPending) {
                e.preventDefault(); // Prevent the default action (new line)
                var user_input = $(this).val().trim();
                sendMessage(user_input, this); // Use 'this' to reference the input element
            }
        });

        $('#send-button').on('click touchstart', function(event) {
            event.preventDefault(); // Prevent native handling of touch events that might cause issues.
            if (!isRequestPending) {
                var $inputElement = $(chatInputSelector);
                var user_input = $inputElement.val().trim();
                if (user_input !== '') {
                    sendMessage(user_input, $inputElement[0]); // Use the existing sendMessage function
                }
            }
        });
    });
}); 