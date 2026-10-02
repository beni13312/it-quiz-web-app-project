class Session_end_window {
    constructor() {
        this.body = document.querySelector('.body'); // body div
    }

    Addwindow() {
        return new Promise((resolve) => { // várakozás a válaszra
            const popup_window = document.createElement('div'); // felugró ablak container div
            popup_window.id = 'session-popup';

            const overlay = document.createElement('div');
            Object.assign(overlay.style, {
                position: 'fixed',
                top: 0,
                left: 0,
                width: '100vw',
                height: '100vh',
                backgroundColor: 'rgba(0, 0, 0, 0.3)',
                zIndex: '9998', // közvetlenül a felugró ablak alatt
            });


            Object.assign(popup_window.style, {
                width: '300px',
                height: '100px',
                position: 'fixed',
                top: '-1000px',
                left: '50%',
                transform: 'translateX(-50%)',
                backgroundColor: '#fff',
                borderRadius: '8px',
                padding: '10px',
                boxShadow: '0 4px 10px rgba(0, 0, 0, 0.3)',
                transition: 'top 0.5s ease',
                zIndex: '9999',
            });

            const popupText = document.createElement('p');
            popupText.innerHTML = 'A rendszer hamarosan ki fogja léptetni! <span id="admindash-timeout"></span>';
            popupText.style.fontSize = '20px';

            const popupButton1 = document.createElement('input');
            popupButton1.type = 'button';
            popupButton1.id = 'input2';
            popupButton1.value = 'Maradás';
            popupButton1.style.margin = '10px';
            popupButton1.style.border = 'none';
            popupButton1.style.backgroundColor = 'rgba(175, 175, 175, 0.37)';
            popupButton1.style.padding = '5px';
            popupButton1.style.borderRadius = '5px';

            const popupButton2 = document.createElement('input');
            popupButton2.type = 'button';
            popupButton2.id = 'input3';
            popupButton2.value = 'Kilépés';
            popupButton2.style.margin = '10px';
            popupButton2.style.border = 'none';
            popupButton2.style.backgroundColor = 'rgba(175, 175, 175, 0.37)';
            popupButton2.style.padding = '5px';
            popupButton2.style.borderRadius = '5px';

            popup_window.appendChild(popupText);
            popup_window.appendChild(popupButton1);
            popup_window.appendChild(popupButton2);

            this.body.appendChild(overlay);
            this.body.appendChild(popup_window);

            // Show popup
            requestAnimationFrame(() => {
                popup_window.style.top = '50px';
            });

            let counter = 30;
            const interval = setInterval(() => { // 30s, ideje van a usernek arra, hogy válaszoljon, ha nem akkor ki lesz léptetve
                counter--;
                document.getElementById('admindash-timeout').innerHTML = String(counter); // pillanatnyi idő kiírása
                if (counter <= 0) {
                    clearInterval(interval);
                    overlay.remove();
                    popup_window.remove(); // ablak eltávolítása
                    resolve(1); // Kilépés
                }
            }, 1000);

            // Event Listeners
            popupButton1.addEventListener('click', () => {
                popup_window.style.top = '-1000px';
                overlay.remove();
                clearInterval(interval);
                popup_window.remove(); // ablak eltávolítása
                resolve(0); // Maradás
            });

            popupButton2.addEventListener('click', () => {
                popup_window.style.top = '-1000px';
                overlay.remove();
                clearInterval(interval);
                popup_window.remove(); // ablak eltávolítása
                resolve(1); // Kilépés
            });
        });
    }
}

export default Session_end_window;
