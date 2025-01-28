// toggle-sidebar.js
jQuery(document).ready(function($) {
    function toggleVisibilityBasedOnWidgetPositionAndContentOption() {
        var widgetPosition = $('input[name="AI24AI_widget_position"]:checked').val();
        var contentOption = $('input[name="AI24AI_sidebar_content"]:checked').val();
        var sidebarContentSection = $('#sidebar-content-section');
        var textOptionContainer = $('#sidebar-content-text');
        var iconSVGContainer = $('#AI24AI_toggle_image_container');
        var textOption = $('#AI24AI_sidebar_text');
        var iconOption = $('#AI24AI_toggle_image');

        sidebarContentSection.toggle(widgetPosition === 'sidebar');
        textOptionContainer.toggle(contentOption === 'text' && widgetPosition === 'sidebar');
        iconSVGContainer.toggle(widgetPosition === 'sidebar');

        if (widgetPosition === 'corner') {
            textOption.val('').prop('disabled', true);
            textOptionContainer.hide();
            iconOption.prop('disabled', false);
        } else if (contentOption === 'text' && widgetPosition === 'sidebar') {
            textOption.prop('disabled', false);
            iconOption.prop('disabled', true);
        } else {
            textOption.prop('disabled', true);
            iconOption.prop('disabled', false);
        }
    }

    $('input[name="AI24AI_widget_position"], input[name="AI24AI_sidebar_content"]').change(toggleVisibilityBasedOnWidgetPositionAndContentOption);
    toggleVisibilityBasedOnWidgetPositionAndContentOption();
});
