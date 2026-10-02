import {getCookie} from "../../modules/getCookie.js";

class Admin_security{
    constructor() {
        this.current_password = document.getElementById('admindash-password-current');
        this.new_password = document.getElementById('admindash-password-new');
        this.re_new_password = document.getElementById('admindash-password-re-new');

        this.submit = document.getElementById('admindash-password-submit');
        this.password_msg = document.getElementById('admindash-password-msg');
        this.submit_msg = document.getElementById('admindash-password-submit-msg');


        this.canBeSent = false;
        this.submit.disabled = true;

        this.checkPasswordMatches();
        this.updatePassword();
    }
    isPasswordStrong(input){
        return input.length >= 8 &&
            /[A-Z]/.test(input) &&         // at least one uppercase
            /[a-z]/.test(input) &&         // at least one lowercase
            /[0-9]/.test(input) &&         // at least one digit
            /[!@#$%^&*()\[\]{}\-_=+\\|;:'",.<>\/?`~]/.test(input);    // at least one special char
    }




    checkPasswordMatches() {



        let timeout;
        const stoppedTyping_timout = 500; // ms

        const handleValidation = () =>{
            const new_password_value = this.new_password.value.trim();
            const re_new_password_value = this.re_new_password.value.trim();

            if (new_password_value.length === 0 && re_new_password_value.length === 0) { // ha mindkét mező űres
                this.password_msg.textContent = "";
                this.canBeSent = false;
                this.submit.disabled = true;
                return;
            }

            if(this.current_password.value === new_password_value) { // ha egyenlő a jelenleg beállított jelszó meg az új
                this.password_msg.textContent = "A jelszó nem lehet ugyanaz, mint a jelenleg beállított!";
                this.password_msg.style.color = "red";
                this.canBeSent = false;
                this.submit.disabled = true;
                return;
            }

            if(!this.isPasswordStrong(new_password_value)) { // ha nem egyezik a két jelszó
                this.password_msg.textContent = "A jelszó túl gyenge, minimum 8 karakter, tartalmaznia kell: a-z,A-Z,0-9,!*#@_...";
                this.password_msg.style.color = "red";
                this.canBeSent = false;
                this.submit.disabled = true;
                return;
            }
            if (re_new_password_value.length > 0 && new_password_value !== re_new_password_value) { // ha nem egyezik a kettő
                this.password_msg.textContent = "A két jelszó nem egyezik!";
                this.password_msg.style.color = "red";
                this.canBeSent = false;
                this.submit.disabled = true;
                return;
            }
            if(re_new_password_value.length === 0) {
                this.password_msg.textContent = "";
                this.canBeSent = false;
                this.submit.disabled = true;
                return;
            }

            this.password_msg.textContent = ""; // megfelelő a két jelszó
            this.canBeSent = true; // ha minden egyezik akkor a jelszavak küldhetőek az API backend-nek
            this.submit.disabled = false;

        }

        // Event listeners
        this.new_password.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(handleValidation,stoppedTyping_timout); // 500ms
        })
        this.re_new_password.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(handleValidation,stoppedTyping_timout); // 500ms
        })
    }
    updatePassword(){ // jelszó módosítása
            this.submit.addEventListener('click', async () => {
                const formData = new URLSearchParams(); // URL encoded form
                formData.append("current_password", this.current_password.value.trim());
                formData.append("new_password", this.new_password.value.trim());

                if(this.canBeSent){
                    try{
                        const response = await fetch('/actions/api/v1/admin/admin_security.php?change_passwd=true',{
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-Auth-Token': getCookie('auth_id'),
                            },
                            body: formData.toString()
                        });
                        const data = await response.json();

                        if(data['error']){
                            this.submit_msg.textContent = data['error'];
                            this.submit_msg.style.color = "red";
                        }

                        if(data['mysqli_error']){
                            console.log(data['mysqli_error']);
                        }
                        if(data['success']){
                            this.submit_msg.textContent = data['success'];
                            this.submit_msg.style.color = "green";
                        }
                    }catch(e){
                        console.log(e);
                    }
                }else {
                    this.submit_msg.textContent = "Nemlehet elküldeni az űrlapot";
                    this.submit_msg.style.color = "red";
                }
            })


    }

}
export default Admin_security;