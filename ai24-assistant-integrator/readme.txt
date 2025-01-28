=== AI24 Assistant Integrator ===
Contributors: Site24
Tags: AI, Virtual Assistant, OpenAI, ChatGPT, Chatbot
Requires at least: WordPress 5.0
Tested up to: 6.7.1
Requires PHP: 7.2
Version: 1.0.9
Stable tag: 1.0.9
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Copyright: 2024 AI24
Easily integrate OpenAI assistants into your WordPress site for enhanced user interaction and support.

== Description ==
The easiest to use plugin for OpenAI assistants. Empower your WordPress site with AI-driven chatbots for enhanced interactivity and support.

**AI24 Assistant Integrator** enables you to integrate OpenAI assistants into your WordPress site effortlessly. Enhance user interaction and provide instant support with the power of OpenAI's technology. 

All you need to do is set up the assistant on your OpenAI account, enter the API key and Assistant ID and it's ready to go. There are no other plugins that achieve this in such little time. 

== Features ==
- **Seamless OpenAI Integration:** Connect your WordPress site with OpenAI's powerful AI models with minimal setup.
- **Customizable Chatbot Widgets:** Tailor the appearance and behavior of your chatbots to match your site's design and user needs.
- **Future-Proof Technology:** Designed with scalability in mind, AI24 Chatbot Integrator is ready to evolve with the introduction of new AI technologies and APIs.
- **Intelligent Interaction:** Utilize advanced machine learning to provide users with smart, contextually relevant interactions.
- **"Powered by AI24" Branding:** While offering top-tier AI functionalities, also enjoy subtle branding that credits AI24 site for the enhanced user experience.

== Installation ==
1. Download the AI24 Assistant Integrator plugin from the WordPress Plugin Directory or from https://site24.com.au/ai24-assistant-integrator/
2. Upload the plugin files to your `/wp-content/plugins/ai24-assistant-integrator` directory, or install the plugin directly through the WordPress plugins screen.
3. Activate the plugin through the 'Plugins' screen in WordPress.
4. Navigate to the plugin settings within your WordPress dashboard to configure your OpenAI API key and customize your chatbot settings.

== Frequently Asked Questions ==
= Do I need an OpenAI account to use this plugin? =
Yes, an OpenAI API key is required to enable the chatbot functionalities provided by this plugin.

= Can I customize the chatbot's appearance? =
Absolutely! AI24 Assistant Integrator offers various customization options to ensure the chatbot fits seamlessly with your site's design.

== Changelog ==
= 1.0.0 =
- Initial release: Introducing seamless integration of OpenAI-powered assistants into WordPress websites.
= 1.0.3 =
- Minor bug fixes and UI changes
= 1.0.4 =
- Fixed source tag not being removed
- Minor bug fixes
- Included an activate and deactivate upon plugin update
= 1.0.5 =
- Added support for functions
- Ability to integrate unlimited secret keys with custom identifiers
- Can choose between an assistant with functions or not (Non-function code includes an escape if the run enters requires_action but no functions enabled)
- View button added to keys
- Various security upgrades
- Changes settings slightly
= 1.0.5.1 = 
- Fixed issues with headers
= 1.0.5.2 =
- Minor CSS fixes to ensure consistency
= 1.0.6 =
- Major structural changes to code base
- Larger upgrades to security and nonce verification
- Cleaned up code 
- Move JS outside of php 
- Enqueues now only on correct admin page
- Functions/Names changes for more unique code
= 1.0.6.1 =
- Significant changes to how functions work, streamlining the integration process for users
- More updates structure getting ready for plugin publishing
= 1.0.6.2 =
- Major structure changes again to V2 capability 
- Functions integrated into both API files (V1 and V2, enables ability for vectors stores and GPT-4o)
- Fixed issue with sidebar not being able to be fully clicked once the widget had been opened
- Fixed show button in admin menu
- Fixed enable widget not doing anything when plugin first installed
- Fixed exit modal not sitting on top
- Header shade interaction with messages fixed
- Shift+enter no longer sends a message. JS to extend the chat size coming in next update
= 1.0.6.3 =
- Fixes to corner widget not working
- Fixes to CSS enqueues regarding the corner widget option
= 1.0.6.4 =
- Commented out lots of logging to not flood debug
- Fixes to queue of API versions in the functions file
= 1.0.7 =
- Large update push for repo release (Officialy listed)
- Added forced KumbhSans as default font
- Added ability to choose a font for the widget
- Fixed header issue on the main admin settings page
- Chat now accepts MD as a format
- Dynamically including V1 or V2 handler (Was causing crashes)
- New handler for "requires_action" run status
- Fixed description for repo listing preview card
- Added/changed tags for the plugin listing
- Added image/icon for the preview card
- Added banner for plugin repo page
- Changed and fixed formatting for MD in the chat widget
- Made settings page a little cleaner
- Added generate and regenerate buttons for the functions file (creates AI24AI-child plugin)
- Completely changed how the functions file works. Now is not overwritten when the file updates and ensures ease for admins
- Fixed the edit plugin file link
- Removed extra unnecessary logging
- Toggle now slides in for sidebar and fades in for corner on page load
- Removed nonce log for security reasons
- Removed save settings button on tutorials page
- Added if statements to AI24AI_add_thread_to_db and AI24AI_store_thread_id_in_session
- Give admins the ability to hook thread creation, storage and session storage with above handlers
- Shipped new generate and regenerate inline scripts into seperate .js files instead of sitting in live php
- Sped up the slide in animation for sidebar
= 1.0.7.2 =
- Fixed issues from previous versions 1.0.7+
= 1.0.7.3 =
- Add new handler for requires_action to include REST requests
- Shift+enter in the input field now adds a new line (Didn't before)
- Fixed some MD formats that weren't set correctly
- Changed Site24 link at the bottom of the widget container to the AI24AI page instead of the Site24 consults page
= 1.0.7.4 =
- Fixed issue with generate and regenerate buttons not working
- Removed debug line for button renders
= 1.0.7.5 =
- Fixed issue with multisite functions.php generation
= 1.0.7.6 =
- Uploaded new tutorial videos
- Changed titles for previous tutorials
- Changed old tutorials to new ones
= 1.0.7.61 =
- Remove log for checking position of toggle
- Fixed filetime issue. Paths not found for generate js, regenerate js and admin css files
- Updated the header to the functions.php file causing issues on saving
= 1.0.7.62 =
- Update to the requires_action status handler for best REST API handling and generic function handling
- Fixed contributors
- Fixed name of the plugin 
- Added short description
- Updated tags
= 1.0.8 =
- Fixed issue with lottie load causing chat not to work on mobile and desktop in some instances 
- Fixed contributor names for the plugin
- Add "settings" to the plugin preview card
- Small changes to p MD extra gap (Tested 2px potentially return?)
- After sending a message the input box is still selected until clicked out of 
- When first entering the chat the input box is already selected
- Fixed exit modal HTML sitting in middle of widget not entire screen
- Chat input refocused after assistant message sends
- Gaps in the input box are now pasted into the user message
- Fixed short description
- Update the "Powered by Site24" to "Powered by AI24"
- Changed the "Power by AI24" destination link
- Fixed minor discrepencies in the readme.txt
- Remove "ai24ai_add_plugin_settings_link called" log
- Add grow hover animation on the toggle
- Fixed chatbox header interacting with the controls incorrectly in some cases
- Made includes folder in the child-plugin folder
- Added functions.php to includes folder
- Created pluginmain.php file in child plugin
- Update edit functions file 
- Add a duplicate/SAVE functions file so that you can easily migrate afterwards
= 1.0.8.1 = 
- Commented extra logging from new functions.php file (missed in previous update)
- Fix <br> structure in the chat when adding spaces in the input field
- Fixed links not opening in another tab with parsedown target=blank
= 1.0.8.2 =
- Fixed readme.txt
- Removed auto focus on mobile devices for UX purposes
- Tested for WordPress version 6.6
- Reworked API handlers (more efficient and ready for future changes)
- Cleaned up and made main plugin file easier to read
- Added submenu for menu tab
- Added highlights for which tab is enabled in the submenu
- Fixed default lock of secret keys
- Added ability to change mode of select pages. Can now choose between show on selected pages or hide on selected pages (Still select no pages will display everywhere, by default)
- Added back MD support in the API handler
= 1.0.8.3 = 
- Updated V1 API handler to new structure
= 1.0.8.4 =
- Added option to customise the input field text
- Added option to customise the exit confirmation modal text
- Added option to customise the yes/no confirm exit button texts
- Added 4 new Typing fields in the “Assistant Styling” tab in settings for the above changes
- Basic preps for 1.0.9 (MAJOR UPGRADE)
= 1.0.9 =
- Added support for maintaining chatbot state across browser tabs
- Implemented a BroadcastChannel to synchronize chatbot states like open/close across tabs
- Enhanced the ChatStateManager to include new state handling logic for managing tab-specific interactions
- Adjusted AI24AI-script.js to support dynamic interaction states for better user experience in multi-tab scenarios.

== Support ==
For support queries, feature suggestions, or further assistance, please visit site24.com.au or email me directly info@site24.com.au

== Credits ==
AI24 Assistant Integrator is developed by Site24, a leader in web design, development, and AI integration solutions. For more information, visit our website at site24.com.au or reach out to our team @ info@site24.com.au

== 3rd Party or External Services ==

= OpenAI API = 
- We are leveraging the OpenAI ecosystem and thus API's with the plugin. You must be aware that when the chatbot is live on your site those APIs are being used when interacted with.
- OpenAI main page: https://openai.com/
- OpenAI Privacy Policies: https://openai.com/policies/privacy-policy
- OpenAI API: https://api.openai.com/v1

= Youtube Video/Tutorial Embeds =
- Tutorial videos embedded into the settings page have been uploaded to youtube by the following links.
- What is AI24?: https://youtu.be/blGeFZOvncY
- What are functions?: https://youtu.be/JIv3dnx7S30
- How to create OpenAI assistant functions?: https://youtu.be/pOIWTjgv9Sc
- How to create OpenAI assistants: https://youtu.be/Io_HckHRxow
