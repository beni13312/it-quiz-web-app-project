import {bypassHTMLtag} from "../../modules/BypassHTML.js";
import {getCookie} from "../../modules/getCookie.js";

class Admin_quiz_manage {
    constructor() {
        this.catElement = document.querySelectorAll('.admin-quiz-manage-category-row'); // kategória div elemek
        this.currentCatName = "";
        this.setSite();
        this.getCategory();
        this.scrollGrab();
    }

    setSite(){
        this.catElement.forEach(element => {
            element.addEventListener('click', async (e) => {
                const url = new URL(window.location.href);
                url.searchParams.set('cat_id', e.target.id); // cat_id kategora id
                this.currentCatName = e.target.textContent; // kategória név

                window.history.pushState({}, '', url);
                await this.getCategory();
            })
        })
    }
    Addloader(){
        const admindash_manage = document.getElementById('admin-quiz-manage-table-wrapper');
        let container = document.createElement('div');
        let spin_loader = document.createElement('div');


        // container.style.backgroundColor = 'rgba(0, 0, 0, 0.06)'
        spin_loader.classList.add('loader');
        spin_loader.style.transform = 'translateX(100)';

        container.id = 'loader-container-manage';
        container.style.position = 'absolute';
        container.style.top = '0';
        container.style.left = '0';
        container.style.width = '100%';
        container.style.height = '100%';
        container.style.display = 'flex';
        container.style.justifyContent = 'center';
        container.style.alignItems = 'center';
        container.style.zIndex = '10000';


        container.appendChild(spin_loader);
        admindash_manage.appendChild(container);
    }
    Removeloader(){
        const admindash_manage = document.getElementById('admin-quiz-manage-table-wrapper');
        let spin_loader_container = document.getElementById('loader-container-manage');

        // spin_loader_container.style.backgroundColor = 'rgba(0, 0, 0, 0)'
        if(spin_loader_container){
            admindash_manage.removeChild(spin_loader_container);
        }
    }
    async getCategory(){
                const container = document.getElementById('admin-quiz-manage-table-body'); // tbody táblázat
                const admindash_manage_title = document.querySelector('#admindash-manage-title span');
                if (container) {
                    const url = new URL(window.location.href);
                    const cat_id = url.searchParams.get('cat_id') || 1; // alapérték 1 (HTML kategória)

                    try{
                        this.Addloader();
                        const response = await fetch(`/actions/api/v1/admin/admin_quiz_manage.php?cat_id=${cat_id}`,{
                            method: 'GET',
                            headers: {
                                'X-Auth-Token': getCookie('auth_id'),
                            }
                        });
                        const data = await response.json(); // válasz az API-tól

                        container.innerHTML = ''; // elöző törlése

                        admindash_manage_title.innerHTML = this.currentCatName || Array.from(this.catElement).find(e => e.id == cat_id).textContent; // kategória név megjelenítése címben

                        if(data['error']){
                            container.innerHTML = data['error'];

                        }

                        if (data['feladat']) {
                            data['feladat'].forEach(feladat => {
                                let tr = document.createElement('tr');
                                tr.id = feladat['id']; // feladat id
                                // elemek hozzáadása a táblához

                                let valaszok_str = "";
                                let megoldasok_str = "";
                                let unique_valaszok_str = [...new Set(JSON.parse(feladat['valaszok']))]; // JSON valaszok
                                let unique_megoldasok_str = [...new Set(JSON.parse(feladat['megoldasok']))]; // JSON megoldasok


                                unique_valaszok_str.forEach((e, i) =>{ // hozzáadás string-hez vesszővel
                                    if(i>0) valaszok_str += ', ';
                                    valaszok_str += bypassHTMLtag(e);
                                });
                                unique_megoldasok_str.forEach((e, i) =>{ // hozzáadás string-hez vesszővel
                                    if(i>0) megoldasok_str += ', ';
                                    megoldasok_str += bypassHTMLtag(e);

                                });

                                tr.innerHTML = `
                                    <td id="kerdes-${feladat['id']}">${bypassHTMLtag(feladat['kerdes'])}</td>
                                    <td id="valaszok-${feladat['id']}">${valaszok_str}</td>
                                    <td id="megoldasok-${feladat['id']}">${megoldasok_str}</td>
                                    <td><button class="admin-quiz-manage-table-button" id="${feladat['id']}">Törlés</button></td>
                                `;
                                container.appendChild(tr); // Append the row to the table
                                this.removeQuiz();
                            });
                        }
                    }catch (e){
                        console.log(e);
                    }finally {
                        this.Removeloader();
                    }
                    // echo "<tr>";
                    // echo "<td>".$row["kerdes"]."</td>";
                    // echo "<td>".$row["valaszok"]."</td>";
                    // echo "<td>".$row["megoldasok"]."</td>";
                    // echo "<td>"."<input class='admin-quiz-manage-table-button' type='button' name='feladat_id' value='Kezelés'>"."</td>";
                    // echo "</tr>";
                }
    }
    removeQuiz() { // adott quiz törlése
        const removeButtons = document.querySelectorAll('.admin-quiz-manage-table-button');
        const admin_manage_msg = document.getElementById('admin-quiz-manage-msg');

        removeButtons.forEach(button => {
            // előző event listener eltávolítása
            button.replaceWith(button.cloneNode(true));
        });

        // új event listener hozzáadása
        document.querySelectorAll('.admin-quiz-manage-table-button').forEach(button => {
            button.addEventListener('click', async (e) => {
                if (confirm("Biztos törölni akarja? A módosítások nem vonhatóak vissza!")) { // felugró ablak, ha a user elfogadja a kérés érvényesül
                    const formData = new FormData();
                    formData.append('feladat[feladat_id]', e.target.id); // feladat id
                    formData.append('feladat[torles]', 'true');

                    try{
                        const response = await fetch('/actions/api/v1/admin/admin_quiz_manage.php', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Auth-Token': getCookie('auth_id'),
                            }
                        });
                        const data = await response.json();
                        // console.log(data);

                        if(data['error_mod']) {
                            admin_manage_msg.innerHTML = "Hiba SQL parancs végrehajtása közben";
                            console.log("SQL error: ".data['error_mod']).color = 'red';
                        }


                        if(data['success_msg']){
                            admin_manage_msg.innerHTML = data['success_msg'];
                            admin_manage_msg.style.color = 'green';
                            admin_manage_msg.style.textAlign = 'center';
                            admin_manage_msg.style.marginTop = '20px';
                            await this.getCategory(); // ha sikeres a művelet, akkor újra meghívjuk a function-t, hogy a módosítás megjelenjen
                        }



                        }catch (e){
                        console.log(e);
                    }
                }
            });
        });
    }
    scrollGrab() {
        const el = document.getElementById('admin-quiz-manage-table-wrapper');
        const enableScrollDrag = () => {
            if (el.scrollWidth > el.clientWidth) {
                el.classList.add('scroll-drag');

                let isDown = false;
                let startX;
                let scrollLeft;

                el.addEventListener('mousedown', (e) => {
                    isDown = true;
                    el.classList.add('active');
                    startX = e.pageX - el.offsetLeft;
                    scrollLeft = el.scrollLeft;
                });

                el.addEventListener('mouseleave', () => {
                    isDown = false;
                    el.classList.remove('active');
                });

                el.addEventListener('mouseup', () => {
                    isDown = false;
                    el.classList.remove('active');
                });

                el.addEventListener('mousemove', (e) => {
                    if (!isDown) return;
                    e.preventDefault();
                    const x = e.pageX - el.offsetLeft;
                    const walk = (x - startX) * 1.5;
                    el.scrollLeft = scrollLeft - walk;
                });
            }else{
                if(el.classList.contains('scroll-drag')) { // grab cursor eltávolítása
                    el.classList.remove('scroll-drag');
                }
            }
        };

        enableScrollDrag();

        // Listen for window resize to re-evaluate
        window.addEventListener('resize', enableScrollDrag);
    }




}
export default Admin_quiz_manage;





