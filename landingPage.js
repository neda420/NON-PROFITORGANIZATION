document.addEventListener('DOMContentLoaded', function () {
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();

            const target = document.querySelector(this.getAttribute('href'));

            window.scrollTo({
                top: target.offsetTop,
                behavior: 'smooth',
            });
        });
    });

    // Pop-up functionality
    const contactPopup = document.getElementById('contact-popup');
    const contactPopupTrigger = document.getElementById('contact-popup-trigger');
    const closePopup = document.getElementById('close-popup');

    // Open the pop-up when contact link is clicked
    contactPopupTrigger.addEventListener('click', function (e) {
        e.preventDefault();
        contactPopup.style.display = 'flex'; 
    });

    // Close the pop-up when close button is clicked
    closePopup.addEventListener('click', function () {
        contactPopup.style.display = 'none';
    });

    // Close the pop-up when clicked outside of it
    window.addEventListener('click', function (e) {
        if (e.target == contactPopup) {
            contactPopup.style.display = 'none';
        }
    });

    // Submit form functionality
    document.getElementById('contact-form').addEventListener('submit', function (e) {
        e.preventDefault();        
        const notification = document.createElement('div');
        notification.classList.add('notification');
        notification.textContent = 'Email sent successfully!';
        document.body.appendChild(notification);
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 1000);
        }, 3000);
        contactPopup.style.display = 'none';
    });
});