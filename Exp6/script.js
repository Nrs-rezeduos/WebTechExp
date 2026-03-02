function register() {
    var username = document.getElementById("regUsername").value;
    var email = document.getElementById("regEmail").value;
    var dob = document.getElementById("regDob").value;
    var password = document.getElementById("regPassword").value;

    if (username === "" || email === "" || dob === "" || password === "") {
        alert("Please fill all fields!");
    } else {
        // Store username in browser storage
        localStorage.setItem("username", username);
        alert("Registration Successful!");
        window.location.href = "login.html";
    }
}

function login() {
    var username = document.getElementById("loginUsername").value;
    var password = document.getElementById("loginPassword").value;

    var storedUser = localStorage.getItem("username");

    if (username === "" || password === "") {
        alert("Please fill all fields!");
    } else if (username !== storedUser) {
        alert("User not found! Please register first.");
    } else {
        window.location.href = "welcome.html?user=" + encodeURIComponent(username);
    }
}