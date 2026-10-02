import {bypassHTMLtag} from "./modules/BypassHTML.js";
function load_div(){ // meghíváskor láthatóvá teszi a felületet
    const quiz_feladat = document.getElementById("quiz-feladat");
    if(quiz_feladat){
        quiz_feladat.hidden = false;
    }
}
function quiz_check(){ // event listener a válaszok divhez
    document.querySelectorAll('.quiz-ans').forEach(element => {
        element.addEventListener('click', quiz_check_listener);
    });
}
function quiz_check_listener(event) { // válaszok divre kattintva bejelöli a radio/checkbox input mezőt

            const input = this.querySelector('.quiz-ans-ch input');

            if (!input) return;

            if (input.type === "radio") {
                input.checked = true;
            } else if (input.type === "checkbox") {
                if (event.target !== input) {
                    input.checked = !input.checked;
                }
            }

            isChecked(); // ellenőrzi hogy be lett-e jelölve legalább egy input elem
}

function Addloader(){
    const feladat_div = document.getElementById('quiz-feladat-frame');
    let container = document.createElement('div');
    let spin_loader = document.createElement('div');


    container.style.backgroundColor = 'rgba(0, 0, 0, 0.06)'
    spin_loader.classList.add('loader');
    spin_loader.style.transform = 'translateX(100)';

    container.id = 'loader-container-quiz';
    container.style.position = 'absolute';
    container.style.top = '0';
    container.style.left = '0';
    container.style.width = '100%';
    container.style.height = '100%';
    container.style.display = 'flex';
    container.style.justifyContent = 'center';
    container.style.alignItems = 'center';
    container.style.zIndex = '100';


    container.appendChild(spin_loader);
    feladat_div.appendChild(container);
}
function Removeloader(){
    const feladat_div = document.getElementById('quiz-feladat-frame');
    let spin_loader_container = document.getElementById('loader-container-quiz');

    // spin_loader_container.style.backgroundColor = 'rgba(0, 0, 0, 0)'
    if(spin_loader_container){
        feladat_div.removeChild(spin_loader_container);
    }
}

function setProgressBar(currentQuiz, totalQuiz){
    const progressBar = document.querySelector('.progress-bar');
    const percentage = (currentQuiz/totalQuiz)*100; // százalék kiszámítása progressbar-hoz
    progressBar.style.width = String(percentage) + '%';
}

let NextQuiz = null; // következő quiz

async function getNext_quiz(answers){
    try {
        const form = new FormData();
        answers.forEach(a => form.append('answers[]', a));
        await fetch('/actions/api/v1/next_prev.php?action-next=true&i=1', {
            method: 'POST',
            body: form
        });

        const cat = new URLSearchParams(window.location.search).get('cat');
        const resp = await fetch(`/actions/api/v1/get_quiz.php?category=${cat}`, {
            method: 'GET'
        });
        return await resp.json();

    } catch(err){
        console.log(err);
        return null;
    }
}

async function get_quiz(){
    const cat_param = new URLSearchParams(window.location.search).get('cat');
    let data; // quiz JSON object

    try {
        Addloader();
        await new Promise(r => setTimeout(r, 50));

        if (NextQuiz) {
            data = await NextQuiz;
            NextQuiz = null; // előtöltött quiz törlése
        } else {
            const resp = await fetch(`/actions/api/v1/get_quiz.php?category=${cat_param}`);
            data = await resp.json();
        }

        const cat_title= document.getElementById('quiz-cat-title');
        const kerdes= document.getElementById('quiz-kerdes');
        const szamlalo= document.getElementById('quiz-szamlalo');
        const valaszok= document.getElementById('quiz-answers');
        const quiz_previous= document.getElementById('quiz-previous-container');
        const documentTitle= document.getElementById('documentTitle');

        if (data['error']) {
            kerdes.innerHTML = data['error'];
            kerdes.style.textAlign = 'center';
            if(data['kategoria']) documentTitle.innerHTML = data['kategoria'] + " Quiz";
            document.getElementById('quiz-check').hidden = true;
            document.querySelectorAll('#quiz-next-previous input').forEach(e => e.hidden = true);
            document.querySelector('.progress-bar-container').hidden = true;
            load_div();
            return;
        }
        if (data['end']) { // ha nincs több feladat akkor meghivja a függvényt, amely megjeleníti az elért eredményt
            await display_result();
            return;
        }

        cat_title.innerHTML = data['kategoria'] + " Quiz"; // cím
        documentTitle.innerHTML = data['kategoria'] + " Quiz"; // weboldal cím
        kerdes.innerHTML = bypassHTMLtag(data['kerdes']) + (data['tobb_megoldas'] ? "<br>(Több válasz lehetséges)" : ""); // több megoldás esetén
        szamlalo.innerHTML = data['szamlalo'];
        const [curr, total] = data['szamlalo'].split('/'); // [0, 1] <- index, 0 - jelenlegi, 1 - összes
        setProgressBar(+curr, +total);

        valaszok.innerHTML = "";
        data['valaszok'].forEach(v => {
            valaszok.innerHTML += `
        <div class="quiz-ans" id="quiz-d-${v.id}">
          <div class="quiz-ans-ch">
            <input type="${data['tobb_megoldas'] ?'checkbox':'radio'}"
                   name="ans" value="${v.id}">
          </div>
          <div class="quiz-ans-text">${bypassHTMLtag(v['valasz'])}</div>
        </div>`;
        });

        if (data['feladat_index'] > 1) {
            quiz_previous.innerHTML = '<input id="quiz-previous" type="submit" value="Előző">';
            next_prev();
        } else {
            quiz_previous.innerHTML = "";
        }

        if (String(data['seen']) === "true") {
            await getSol();
            disableInput();
        } else {
            quiz_check();
        }

        load_div();
        isCheckedListener();
        isChecked();

    } catch(e) {
        console.log(e);
    } finally {
        Removeloader();
    }
}

async function handleNextClick() {
    const btn = document.querySelector('#quiz-next');
    btn.disabled = true;
    const answers = Array.from(
        document.querySelectorAll('.quiz-ans input:checked')
    ).map(i=>i.value); // viszaadja a értéket

    NextQuiz = await getNext_quiz(answers); // következő kérédés

    await get_quiz();

    btn.disabled = false;
}

async function handlePrevClick() {
    const quiz_before = document.querySelector('#quiz-previous');
    quiz_before.disabled = true;
    try {
        const response = await fetch('/actions/api/v1/next_prev.php?action-before=true', { method: 'GET' });
        await response.json();
        await get_quiz();
    } catch (error) {
        console.log(error);
    } finally {
        quiz_before.disabled = false;
    }
}

function next_prev() { // tovább vissza gomb
    const quiz_next = document.querySelector('#quiz-next');
    const quiz_before = document.querySelector('#quiz-previous');

    // Remove existing listeners and add new ones
    if (quiz_next) {
        quiz_next.removeEventListener('click', handleNextClick);
        quiz_next.addEventListener('click', handleNextClick);
    }

    if (quiz_before) {
        quiz_before.removeEventListener('click', handlePrevClick);
        quiz_before.addEventListener('click', handlePrevClick);
    }
     check_answers();
}

function isChecked(){
    const quiz_check = document.querySelector('#quiz-check');
    const answers = document.querySelectorAll('.quiz-ans input[type="radio"], .quiz-ans input[type="checkbox"]');

    let anyChecked = false;
    answers.forEach((ans) => {
        if(ans.checked){
            anyChecked = true;
        }
    })
    if(quiz_check){
        quiz_check.disabled = !anyChecked;
    }

}
function disableInput(){ // válaszok ellenőrzése után input gombok letiltása
    const answers = document.querySelectorAll('.quiz-ans input');
    const checkButton = document.getElementById('quiz-check');

    answers.forEach((ans) => {
        ans.disabled = true;
    })
    checkButton.disabled = true;
    checkButton.style.backgroundColor = 'rgba(199,199,199,0.53)';
}

function isCheckedListener(){
    const answers = document.querySelectorAll('.quiz-ans input[type="radio"], .quiz-ans input[type="checkbox"]');

    answers.forEach((ans) => {
        ans.addEventListener('change', isChecked);
    })

}

async function display_result(){ // eredmény megjelenítése, ha a legultolsó quizhez ér a user
    const feladat_frame = document.getElementById('quiz-feladat-frame');
    const title = document.getElementById('quiz-cat-title');



            try{
                const response = await fetch('/actions/api/v1/get_result.php',{method: 'GET'});
                const data = await response.json();
                let szazalek;
                if(data['percentage']){
                    szazalek = Math.round(data['percentage'])+'%';
                }else{
                    szazalek = '0%';
                }

                // eredmény kiíratása

            const titleClone = title.cloneNode(true); // cím klonozása, törlés elött

            [...feladat_frame.children].forEach(child => {
                if(child !== title){ // ami nem a kellő elem az el lessz távolítva
                    feladat_frame.removeChild(child);
                }
            });
            const result = document.createElement('div');
            result.id = 'quiz-feladat-result';

            const eredmeny_title = document.createElement('h2');
            const eredmeny_szazalek = document.createElement('h2');
            const backto_home_b = document.createElement('a');
            backto_home_b.id = 'backto_home';
            backto_home_b.href = '/';

            eredmeny_title.id = 'eredmeny_title';
            eredmeny_title.innerText = 'Eredmény:';
            backto_home_b.innerText = 'Vissza a kategóriákhoz';

            eredmeny_szazalek.innerText = szazalek;

            result.appendChild(titleClone); // cím megtartása
            result.appendChild(eredmeny_title); // eredmény title
            result.appendChild(eredmeny_szazalek); // eredmény százalék
            result.appendChild(backto_home_b); // vissza a kategóriákhoz
            feladat_frame.appendChild(result);

            }catch(e){
                console.log(e);
            }


}

async function getSol(){ // helyes és rossz válaszok megjelenítése, ha a user már bejelölte az adott feladatot
    try{

    const response = await fetch('/actions/api/v1/get_sol.php',{
        method: 'GET',
    });
    const data = await response.json();

    if(data['answers']){ // az előzöleg bejelölt válaszok a user által, visszakapott tömb a backendtől
        document.querySelectorAll(".quiz-ans input").forEach((ans) => {
            if(data['answers'].includes(parseInt(ans.value))){ // ha benne van a jelenlegi érték
                ans.checked = true; // bejelöli azokat a mezőket amelyek  benne vannal a tömbbe
            }
        })
    }

    if(data['error']){
        console.log(data['error']);
    }
    document.querySelectorAll(".quiz-ans").forEach(element => { // quiz-wrong class rossz válaszok stílus
        element.classList.add("quiz-wrong");
    })

     data["megoldas"].forEach((item) => {
             const solElem = document.getElementById(`quiz-d-${item}`);
             if (solElem) {
                     solElem.classList.add("quiz-good");
                 } else {
                     console.warn(`Nem található elem: quiz-d-${item}`);
                 }
         })
    }catch (e){
        console.log(e);
    }

}
function check_answers() { // válaszok ellenörzése
    const quiz_check = document.getElementById('quiz-check');
    
    const new_button = quiz_check.cloneNode(true); // clone the button to remove all listeners
    quiz_check.parentNode.replaceChild(new_button, quiz_check); // swap old for new

    new_button.addEventListener('click', checkAnswers, { once: true }); // attach once

    async function checkAnswers() {
        quiz_check.disabled = true;
        const checked_answers = Array.from(document.querySelectorAll('.quiz-ans input:checked'))
            .map(input => input.value); // bejelölt válaszok tömb
        const Formdata = new FormData();
        checked_answers.forEach((answer) => {
            Formdata.append('answers[]',answer); // válaszok hozzáadása tömbhöz
        })

       await fetch('/actions/api/v1/get_sol.php', {
            method: 'POST',
            body: Formdata
            })
            .then(response => response.json())
            .then(data => {
                // console.log(data);
                if(data['error']){
                    console.log(data['error']);
                }
                document.querySelectorAll(".quiz-ans").forEach(element => { // quiz-wrong class rossz válaszok stílus
                    element.classList.add("quiz-wrong");
                })

                data["megoldas"].forEach((item) => {
                    document.getElementById(`quiz-d-${item}`).classList.add("quiz-good"); // quiz-good class jó válaszok stílus
                })
                quiz_check.removeEventListener('click', checkAnswers); // eltávolítja az event listenert hogy csak egyszer lehessen ellenőrizni
                disableInput();
                document.querySelectorAll('.quiz-ans').forEach(element => {
                    element.removeEventListener('click', quiz_check_listener);
                });
            })
        .catch(error => console.log(error));
    }
    quiz_check.addEventListener('click', checkAnswers, {once: true});
}


document.addEventListener('DOMContentLoaded', async () => {
     await get_quiz();
     check_answers();
     quiz_check();
     next_prev();

});