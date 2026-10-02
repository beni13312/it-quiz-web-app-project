import Admin_security from "./modules/Admin_security.js";
import Admin_quiz_upload from "./modules/Admin_quiz_upload.js";
import Admin_quiz_manage from "./modules/Admin_quiz_manage.js";
import Session_end_window from "./modules/Session_end_window.js";

import {getCookie} from "../modules/getCookie.js";

function loadSite() { // betölti a kiválasztott oldalt dinamikusan
    const admin_menu = document.getElementById('admin-flexbox-menu'); // Admin menu div
    const buttons = admin_menu.querySelectorAll('input[type=button]'); // Button array


    // oldal betöltéskor leelenőrzi hogy melyik érték van beállítva a site GET paraméternek, és azt a JS-t tölti be
    const page = new URL(window.location.href); // site GET param URL object
    const site = page.searchParams.get('site');  // string

    switch (site) { // meghívja a JS class-t az adott oldalnak megfelelően
        case "admin_quiz_upload":
            new Admin_quiz_upload();
            break;
        case "admin_quiz_manage":
            new Admin_quiz_manage();
            break;
        case "admin_security":
            new Admin_security();
            break;
        default:
            new Admin_quiz_upload(); // alapértelmezett, amikor nincsen vagy nincs beállítva a site GET paraméter
            break;
    }


    buttons.forEach( e => {
        e.addEventListener('click', async () => {
                // update URL with the GET param
                let name = e.name; // php file name
                const url = new URL(window.location.href);

                url.searchParams.set('site',name.split(".")[0]); // without PHP extension
                window.history.pushState({}, '', url);

           await fetch(name, {
               method: 'GET',
               headers: {
                   'X-Auth-Token': getCookie('auth_id'),
               }
           }) // PHP oldal kérése
                .then(response => response.text()) // HTML válasz
                .then(data => {

                    const container = document.getElementById('admin-menu-load'); // container ahova a dinamikus elem kerül
                    container.innerHTML = ''; // elöző HTML törlése
                    container.innerHTML = data;

                    const page = new URL(window.location.href); // site GET param URL object
                    const site = page.searchParams.get('site');  // string

                    switch (site) { // meghívja a JS class-t az adott oldalnak megfelelően
                        case "admin_quiz_upload":
                            new Admin_quiz_upload();
                            break;
                        case "admin_quiz_manage":
                            new Admin_quiz_manage();
                            break;
                        case "admin_security":
                            new Admin_security();
                            break;
                        default:
                            break;
                    }

                })
                .catch(error => {
                    console.error('Hiba történt betöltés közben: ', error);
                });
        });
    });
}

let sessionEndWindow = false;
function checkSession(){ // ellelenőrzi időnként hogy érvényes-e a session
    setInterval(async () => { // 60 másodprecenként lefuttatja a function-t
        if(sessionEndWindow) return; // ha egyszer már megjelent az ablak
        try{
            const response = await fetch('/actions/api/v1/admin/check_session.php',{
                method: 'GET',
                headers: {
                    'Connection': 'keep-alive',
                    'X-Auth-Token': getCookie('auth_id'),
                },

            });
            const data = await response.json();

            if(!data['active']){
                sessionEndWindow = true;

                const session_window = new Session_end_window().Addwindow();
                session_window.then( async result => {
                    if(result === 1){ // session törlése 1
                        try{
                            const response = await fetch('/actions/api/v1/admin/check_session.php',{
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-Auth-Token': getCookie('auth_id'),
                                },
                                body: JSON.stringify({"destroy": true})

                            });
                            window.location.reload(); // ha nem érvényes, újratölti az oldalt, ezzel kilépteti az admin user-t

                        }catch(e){
                            console.log(e)
                        }
                    }else{ // ha a user azt választja hogy marad 0
                        try{
                            const response = await fetch('/actions/api/v1/admin/check_session.php',{
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-Auth-Token': getCookie('auth_id'),
                                },
                                body: JSON.stringify({"regenerate": true})

                            });
                            window.location.reload(); // újratölti az oldalt, hogy az új session érvényes legyen a böngészőben is

                        }catch(e){
                            console.log(e)
                        }
                    }

                }).finally(()=>{
                    sessionEndWindow = false; // változó visszaállítása
                });
            }

        }catch (e){
            console.log(e);
        }


    },60000);// 60s
}


// oldalbetöltéskor
addEventListener("DOMContentLoaded", () => {
    loadSite();
    checkSession();
});

// ?site GET paraméterrel való betöltés
window.addEventListener('popstate', async (event) => {
    if (event.state?.site) {
        // Load content from state
        const container = document.getElementById('admin-menu-load');
        if (container) {
            await fetch(`${event.state.site}.php`, {
                method: 'GET',
                headers: {
                    'X-Auth-Token': getCookie('auth_id'),
                }
            })
                .then(response => response.text())
                .then(data => {
                    container.innerHTML = data;
                    loadSite();
                });
        }
    } else {
        // Clear content if no site parameter
        const container = document.getElementById('admin-menu-load');
        if (container) container.innerHTML = '';
        loadSite();
    }
});