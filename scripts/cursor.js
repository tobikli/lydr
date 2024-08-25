// Create the custom cursor element
const cursor = document.createElement('div');
cursor.classList.add('custom-cursor');
cursor.style.opacity = '0'; // Initially hidden
document.body.appendChild(cursor);

// Set the cursor's size
const cursorSize = 20; // Adjust this to match your cursor size

// Update cursor position based on mouse movements
document.addEventListener('mousemove', (e) => {
    const offsetX = cursorSize / 2; // Half of the cursor width
    const offsetY = cursorSize / 3; // Half of the cursor height

    cursor.style.opacity = '1'; // Show cursor when moving
    cursor.style.transform = `translate3d(${e.clientX - offsetX}px, ${e.clientY - offsetY}px, 0)`;
});

// Hide cursor when mouse leaves the window
document.addEventListener('mouseleave', () => {
    cursor.style.opacity = '0'; // Hide cursor when not moving
});

// Shrink cursor on hover over buttons or links
document.querySelectorAll('a, button').forEach((element) => {
    element.addEventListener('mouseenter', () => {
        cursor.classList.add('small');
    });
    element.addEventListener('mouseleave', () => {
        cursor.classList.remove('small');
    });
});
