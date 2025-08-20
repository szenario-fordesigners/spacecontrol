
// Remove novalidate attribute from form to ensure email validation works
// Only do this on our plugin's settings page
if (window.location.pathname.includes('/settings/plugins/spacecontrol')) {
    let mainForm = document.getElementById("main-form");
    if (mainForm) {
        mainForm.removeAttribute("novalidate");
    }
}