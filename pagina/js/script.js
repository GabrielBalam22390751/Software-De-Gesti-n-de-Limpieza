// Obtener el popup
var popup = document.getElementById("popup");

// Obtener el botón que abre el popup
var btn = document.getElementById("addButton");

// Obtener el elemento <span> que cierra el popup
var span = document.getElementById("closeButton");

// Cuando el usuario hace clic en el botón, se abre el popup
btn.onclick = function() {
    popup.style.display = "block";
}

// Cuando el usuario hace clic en <span> (x), se cierra el popup
span.onclick = function() {
    popup.style.display = "none";
}

// Cuando el usuario hace clic en cualquier parte fuera del popup, se cierra
window.onclick = function(event) {
    if (event.target == popup) {
        popup.style.display = "none";
    }
}

// Manejar el envío del formulario
document.getElementById("registrationForm").onsubmit = function(event) {
    event.preventDefault(); // Prevenir el envío del formulario
    alert("Usuario registrado: " + document.getElementById("username").value);
    popup.style.display = "none"; // Cerrar el popup después del registro
}