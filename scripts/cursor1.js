// Create the custom cursor elements


const cursor1 = document.createElement('div');
cursor1.classList.add('custom-cursor1');
document.body.appendChild(cursor1);

// Set the cursor's size
const cursorSize1 = 10;

// Initially set the opacity to 0

let firstMovement = true; // Flag to track first mouse movement

document.addEventListener('mousemove', (e) => {
    // On first mouse movement, start the opacity transition
    if (firstMovement) {
        firstMovement = false; // Reset flag to prevent re-triggering
        setTimeout(() => {
            cursor1.style.transition = 'opacity 0.5s';
            cursor1.style.opacity = '1';
        }, 500); // Delay of 0.5 seconds
        cursor1.style.transition = 'opacity 0s';
    }
    
    cursor1.style.opacity = '1';

    // Calculate offsets for centering the cursor
    
    const offsetX1 = cursorSize1 / 2;
    const offsetY1 = cursorSize1 / 2;

    // Calculate cursor positions
 
    let posX1 = e.clientX - offsetX1;
    let posY1 = e.clientY - offsetY1;
    
 
    
    const maxX1 = window.innerWidth - cursorSize1;
    const maxY1 = window.innerHeight - cursorSize1 - 5;


    // Adjust cursor position to stay within viewport bounds
    
    
    if (posX1 < 0) posX1 = 0;
    if (posX1 > maxX1) posX1 = maxX1;
    if (posY1 < 0) posY1 = 0;
    if (posY1 > maxY1) posY1 = maxY1;

    // Update cursor positions
    cursor1.style.transform = `translate3d(${posX1}px, ${posY1}px, 0)`;
});

// Optional: Hide cursor when the mouse leaves the window
document.addEventListener('mouseleave', () => {
    cursor1.style.opacity = '0';
});
