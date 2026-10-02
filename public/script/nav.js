class Nav{
    constructor(element_id, href){
        this.element_id = element_id;
        this.href = href;
    };
    redirect(){
        const element = document.getElementById(this.element_id);
        if(element){
            element.addEventListener('click', ()=>{
                window.location.href = this.href;
            })
        }
    }

}


const home = new Nav("nav-title","/"); // home
const info = new Nav("nav-info","/info"); // info

document.addEventListener("DOMContentLoaded", ()=>{
    home.redirect();
    info.redirect();
})