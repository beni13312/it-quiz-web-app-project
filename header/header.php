<div id="header">
    <script src="/public/script/nav.js" type="text/javascript"></script>
    <nav id="navbar">
        <div id="nav-left">
            <h2 id="nav-title">Informatikai Feladatgyűjtemény</h2>
        </div>
        <div id="nav-right">
            <div class="nav-d" id="nav-info">Információ</div>

        </div>
    </nav>
</div>
<script>
    function updateTitle() {
        const titleElement = document.getElementById("nav-title");
        if (window.innerWidth < 624) {
            titleElement.textContent = "INFOGY";
        } else {
            titleElement.textContent = "Informatikai Feladatgyűjtemény";
        }
    }

    document.addEventListener("DOMContentLoaded", updateTitle);

    window.addEventListener("resize", updateTitle);

</script>