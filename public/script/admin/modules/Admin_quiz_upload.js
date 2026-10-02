import {getCookie} from "../../modules/getCookie.js";

class Admin_quiz_upload {
    constructor() {
        this.div_id = "admindash-ans-n";
        this.ansid_num = 3; // 2 alap html oldalon
        this.answers = []; // hozzádott

        document.getElementById("admindash-ans-add").addEventListener("click", () => {
            this.add_ans();
        });
        document.getElementById("admindash-ans-rm").addEventListener("click", () => {
            this.remove_ans();
        });
        this.submit_quiz();
    }

    add_ans() { // válasz hozzáadása a HTML form felüllethez
        if (this.ansid_num === 10) { // max 10
            document.getElementById("admindash-ans-add").disabled = true;
        }

        const element_div = document.getElementById(this.div_id);
        if (element_div) {

            // div
            let ans_div = document.createElement("div");
            ans_div.classList.add("admindash-ans");

            // input-text
            let ans_input = document.createElement("input");
            ans_input.type = "text";
            ans_input.id = `ans-${this.ansid_num}`;
            ans_input.name = `ans-${this.ansid_num}`;
            ans_input.placeholder = `Válasz${this.ansid_num}`;

            // input-checkbox
            let ans_checkbox = document.createElement("input");
            ans_checkbox.type = "checkbox";
            ans_checkbox.classList.add("admindash-ans-sol");
            ans_checkbox.name = `isSol-${this.ansid_num}`;
            ans_checkbox.id = `isSol-${this.ansid_num}`;

            // checkbox-label
            let checkbox_label = document.createElement("label");
            checkbox_label.setAttribute("for",`isSol-${this.ansid_num}`);
            checkbox_label.textContent = "Megoldás";

            // append to div
            ans_div.appendChild(ans_input);
            ans_div.appendChild(ans_checkbox);
            ans_div.appendChild(checkbox_label);

            this.answers.push(ans_div);

            // append to div container
            element_div.appendChild(ans_div);

            this.ansid_num++;
        }
    }

    remove_ans() { // válasz eltávolítása a HTML form felülletről
        if (this.answers.length > 0) {
            const last_answer = this.answers.pop();
            last_answer.parentNode.removeChild(last_answer);
            this.ansid_num--;

            if (this.ansid_num < 11) {
                document.getElementById("admindash-ans-add").disabled = false;

            }
        }
    }
    Addloader(){
        const admindash_add = document.getElementById('admindash-add');
        let container = document.createElement('div');
        let spin_loader = document.createElement('div');


        // container.style.backgroundColor = 'rgba(0, 0, 0, 0.06)'
        spin_loader.classList.add('loader');
        spin_loader.style.transform = 'translateX(100)';

        container.id = 'loader-container-upload';
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
        admindash_add.appendChild(container);
    }
    Removeloader(){
        const admindash_add = document.getElementById('admindash-add');
        let spin_loader_container = document.getElementById('loader-container-upload');

        // spin_loader_container.style.backgroundColor = 'rgba(0, 0, 0, 0)'
        if(spin_loader_container){
            admindash_add.removeChild(spin_loader_container);
        }
    }

    submit_quiz(){
        // const submit = document.getElementById("admindash-submit");
        const form = document.getElementById("admindash-form");

        const admindash_upload_msg = document.getElementById("admindash-upload-msg");

        form.addEventListener("submit", async (event) => {
            event.preventDefault(); // prevents from sending the form

            const formData = new FormData(event.target); // form adat a HTML-oldalról
            // console.log([...formData.entries()]);

            try{
                this.Addloader();
                const response = await fetch("/actions/api/v1/admin/admin_quiz_upload.php",{
                    method: "POST",
                    body: formData,
                    headers: {
                        'X-Auth-Token': getCookie('auth_id'),
                    }
                });
                const data = await response.json();
                // console.log(data);

                if(data['error']){
                    admindash_upload_msg.innerHTML = data['error'];
                    admindash_upload_msg.style.marginTop = '10px';
                    admindash_upload_msg.style.color = 'red';
                }
                if(data['mysqli_error']){
                    console.log(data['mysqli_error']);
                }
                if(data['success']){
                    admindash_upload_msg.innerHTML = data['success'];
                    admindash_upload_msg.style.marginTop = '10px';
                    admindash_upload_msg.style.color = 'green';
            }


            }catch(e){
                console.log(e);
            }finally {
                this.Removeloader();
            }


        })
    }
}
export default Admin_quiz_upload;