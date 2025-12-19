//Disable right-click context menu
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });
    //Disable F12 and Ctrl+Shift+I
    document.addEventListener('keydown', function(e) {
        if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key === 'I')) {
            e.preventDefault();
        }
    });
    //Disable Ctrl+U
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'u') {
            e.preventDefault();
        }
    });
    //Disable Ctrl+S
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
        }
    });
    //Disable Ctrl+P
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
        }
    });
    // Disable text selection
    document.addEventListener('selectstart', function(e) {
        e.preventDefault();
    });
    // Disable drag and drop
    document.addEventListener('dragstart', function(e) {
        e.preventDefault();
    });
    // Disable copy
    document.addEventListener('copy', function(e) {
        e.preventDefault();
    });
    // Disable paste
    document.addEventListener('paste', function(e) {
        e.preventDefault();
    });
    // Disable cut
    document.addEventListener('cut', function(e) {
        e.preventDefault();
    });
    // Disable right-click on images
    const images = document.querySelectorAll('img');
    images.forEach(function(image) {
        image.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });
    });
    // Disable right-click on links
    const links = document.querySelectorAll('a');
    links.forEach(function(link) {
        link.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });
    });
    // Disable right-click on buttons
    const buttons = document.querySelectorAll('button');
    buttons.forEach(function(button) {
        button.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });
    });
    // Disable right-click on input fields
    const inputs = document.querySelectorAll('input, textarea');
    inputs.forEach(function(input) {
        input.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });
    });
    // Disable right-click on the body
    document.body.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });
    // Disable right-click on the document
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });
    // Disable right-click on the window
    window.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });
    // Disable right-click on the document element
    document.documentElement.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });