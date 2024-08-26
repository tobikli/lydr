function isTouchDevice() {
  // Check for touch events
  const hasTouchEvents = 'ontouchstart' in window;

  // Check for touch points on the device
  const hasTouchPoints = (navigator.maxTouchPoints > 0) || (navigator.msMaxTouchPoints > 0);

  // Check if the user agent indicates an iOS device
  const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;

  // Check for macOS and Safari
  const isMacOS = navigator.platform.startsWith('Mac');
  const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);

  // Return true if it's an iOS device or a device with genuine touch capability
  return (isIOS || (hasTouchEvents || hasTouchPoints)) && !(isMacOS && isSafari);
}

var opac = '0';

if (!isTouchDevice()) {
    opac = '1';
}

// Create the custom cursor elements
const cursor = document.createElement('div');
cursor.classList.add('custom-cursor');
document.body.appendChild(cursor);

const cursor1 = document.createElement('div');
cursor1.classList.add('custom-cursor1');
document.body.appendChild(cursor1);

// Set the cursor's size
const cursorSize = 200;
const cursorSize1 = 10;

// Initially set the opacity to 0
cursor.style.opacity = '0';
cursor1.style.opacity = '0';

let firstMovement = true; // Flag to track first mouse movement

document.addEventListener('mousemove', (e) => {
    // On first mouse movement, start the opacity transition
    if (firstMovement) {
        firstMovement = false; // Reset flag to prevent re-triggering
        setTimeout(() => {
            cursor.style.transition = 'opacity 0.5s';
            cursor1.style.transition = 'opacity 0.5s, width 0.3s ease, height 0.3s ease';
            cursor.style.opacity = opac;
            cursor1.style.opacity = opac;
        }, 500); // Delay of 0.5 seconds
        cursor.style.transition = 'opacity 0s';
        cursor1.style.transition = 'opacity 0s';
    }
    
    cursor.style.opacity = opac;
    cursor1.style.opacity = opac;

    // Calculate offsets for centering the cursor
    const offsetX = cursorSize / 2;
    const offsetY = cursorSize / 2;

    const offsetX1 = cursorSize1 / 2;
    const offsetY1 = cursorSize1 / 2;

    // Calculate cursor positions
    let posX = e.clientX - offsetX;
    let posY = e.clientY - offsetY;

    let posX1 = e.clientX - offsetX1;
    let posY1 = e.clientY - offsetY1;
    
    const maxX = window.innerWidth - cursorSize;
    const maxY = window.innerHeight - cursorSize;
    
    const maxX1 = window.innerWidth - cursorSize1;
    const maxY1 = window.innerHeight - cursorSize1 - 5;

    // Adjust cursor position to stay within viewport bounds
    if (posX < 0) posX = 0;
    if (posX > maxX) posX = maxX;
    if (posY < 0) posY = 0;
    if (posY > maxY) posY = maxY;
    
    if (posX1 < 0) posX1 = 0;
    if (posX1 > maxX1) posX1 = maxX1;
    if (posY1 < 0) posY1 = 0;
    if (posY1 > maxY1) posY1 = maxY1;

    // Update cursor positions
    cursor.style.transform = `translate3d(${posX}px, ${posY}px, 0)`;
    cursor1.style.transform = `translate3d(${posX1}px, ${posY1}px, 0)`;
});

// Optional: Hide cursor when the mouse leaves the window
document.addEventListener('mouseleave', () => {
    cursor.style.opacity = opac;
    cursor1.style.opacity = '0';
});

// Add event listeners for hovering over links or grid items
// Flag to track if the cursor is in a special state
let isHoveringText = false;
let isHoveringLink = false;

// Create a helper function to apply the cursor styles based on the state
function updateCursorStyles() {
    if (isHoveringLink) {
        cursor1.style.width = '5px';
        cursor1.style.height = '5px';
        cursor1.style.border = '2px solid white';
        cursor1.style.borderRadius = '50%'; // Circular
        cursor1.style.transition = 'width 0.3s ease, height 0.3s ease, border-radius 0.3s ease';
    } else if (isHoveringText) {
        cursor1.style.width = '0px';
        cursor1.style.height = '15px';
        cursor1.style.border = '1px solid white';
        cursor1.style.borderRadius = '0'; // Straight line
        cursor1.style.transition = 'width 0.15s ease, height 0.15s ease, border-radius 0.15s ease';
    } else {
        cursor1.style.width = '10px'; // Default size
        cursor1.style.height = '10px'; // Default size
        cursor1.style.border = '2px solid white';
        cursor1.style.borderRadius = '50%'; // Default circular
        cursor1.style.transition = 'width 0.3s ease, height 0.3s ease, border-radius 0.3s ease';
    }
}

// Add event listeners for links
const hoverElements = document.querySelectorAll('a'); // Select links

hoverElements.forEach(element => {
    element.addEventListener('mouseenter', () => {
        isHoveringLink = true;
        updateCursorStyles();
    });

    element.addEventListener('mouseleave', () => {
        isHoveringLink = false;
        updateCursorStyles();
    });
});

// Add event listeners for text elements
const textElements = document.querySelectorAll('c');

textElements.forEach(element => {
    element.addEventListener('mouseenter', () => {
        isHoveringText = true;
        updateCursorStyles();
    });

    element.addEventListener('mouseleave', () => {
        isHoveringText = false;
        updateCursorStyles();
    });
});

// Add a general mouseleave event to reset if the cursor leaves both the text and link areas
document.addEventListener('mouseleave', () => {
    isHoveringText = false;
    isHoveringLink = false;
    updateCursorStyles();
});
