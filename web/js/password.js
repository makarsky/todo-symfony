function toggleVisibility(element) {
    if (element.type === "password") {
        element.type = "text";
    } else {
        element.type = "password";
    }
}