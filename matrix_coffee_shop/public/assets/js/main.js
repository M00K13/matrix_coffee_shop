// assets/js/main.js
// Matrix Coffee Shop - Main JavaScript

// Wacht tot DOM is geladen
document.addEventListener('DOMContentLoaded', function() {
    // "Add to Cart" knoppen
    setupAddToCartButtons();
    
    // Matrix-stijl effect op tekst
    setupMatrixTextEffect();
});

// Winkelwagen functionaliteit
function setupAddToCartButtons() {
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productId = this.getAttribute('data-product-id');
            
            // AJAX-verzoek om product toe te voegen aan winkelwagen
            // Voor educatieve doeleinden - dit is een kwetsbare implementatie
            fetch('add_to_cart.php?product_id=' + productId, {
                method: 'GET',
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Product toegevoegd aan winkelwagen', 'success');
                    
                    // Update winkelwagenaantal
                    if (data.cart_count) {
                        const cartLink = document.querySelector('.cart-link');
                        if (cartLink) {
                            cartLink.textContent = `Winkelwagen (${data.cart_count})`;
                        }
                    }
                } else {
                    showNotification('Fout bij toevoegen aan winkelwagen', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Er is een fout opgetreden', 'error');
            });
        });
    });
}

// Matrix-stijl effect voor tekstelementen
function setupMatrixTextEffect() {
    const heroHeading = document.querySelector('.hero-content h2');
    
    if (heroHeading) {
        const originalText = heroHeading.textContent;
        const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        
        let interval = null;
        
        heroHeading.addEventListener('mouseover', event => {  
            let iteration = 0;
            
            clearInterval(interval);
            
            interval = setInterval(() => {
                heroHeading.innerText = heroHeading.innerText
                    .split("")
                    .map((letter, index) => {
                        if(index < iteration) {
                            return originalText[index];
                        }
                    
                        return letters[Math.floor(Math.random() * letters.length)];
                    })
                    .join("");
                
                if(iteration >= originalText.length){ 
                    clearInterval(interval);
                }
                
                iteration += 1 / 3;
            }, 30);
        });
    }
}

// Notificatie weergave
function showNotification(message, type = 'info') {
    // Maak een notificatie-element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    // Voeg toe aan body
    document.body.appendChild(notification);
    
    // Stijl toevoegen via JavaScript (voor het geval CSS niet geladen is)
    Object.assign(notification.style, {
        position: 'fixed',
        bottom: '20px',
        right: '20px',
        padding: '10px 20px',
        zIndex: '9999',
        borderRadius: '4px',
        color: '#000',
        backgroundColor: type === 'error' ? '#ff6666' : '#00ff00',
        boxShadow: '0 0 10px rgba(0, 255, 0, 0.5)',
        opacity: '0',
        transition: 'opacity 0.3s ease'
    });
    
    // Animatie
    setTimeout(() => {
        notification.style.opacity = '1';
    }, 10);
    
    // Verwijder na 3 seconden
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// XSS kwetsbare functie voor het verwerken van URL parameters
function getUrlParameter(name) {
    // KWETSBAAR: Geen juiste escaping van parameter waarden
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}

// Functie die XSS kwetsbaar is in DOM manipulatie
function displayWelcomeMessage() {
    const username = getUrlParameter('username');
    
    if (username) {
        // KWETSBAAR: Directe DOM manipulatie zonder escaping
        const welcomeDiv = document.createElement('div');
        welcomeDiv.className = 'welcome-message';
        welcomeDiv.innerHTML = `Welkom terug, ${username}!`; // XSS kwetsbaar
        
        const main = document.querySelector('main');
        if (main) {
            main.prepend(welcomeDiv);
        }
    }
}

// Voer de functie uit bij laden van de pagina
document.addEventListener('DOMContentLoaded', function() {
    displayWelcomeMessage();
});
