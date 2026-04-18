document.addEventListener("DOMContentLoaded", function () {
    fetch("server/profile.php")
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
            } else {
                document.getElementById("username").textContent = data.username;
                document.getElementById("email").textContent = data.email;
                document.getElementById("birthday").textContent = data.birthday;
                document.getElementById("gender").textContent = data.gender;

                const profilePic = document.getElementById("profile-pic");
                if (data.gender === "Female") {
                    profilePic.src = "img/female.gif";
                } else if (data.gender === "Male") {
                    profilePic.src = "img/male.gif";
                } else {
                    profilePic.src = "img/other.gif";
                }
            }
        })
        .catch(error => console.error("Error fetching profile data:", error));
});

function editField(field) {
    const span = document.getElementById(field);
    const currentValue = span.textContent;

    let inputElement;

    if (field === "birthday") {
        inputElement = document.createElement("input");
        inputElement.type = "date";
    } else if (field === "gender") {
        inputElement = document.createElement("select");
        ["Male", "Female", "Other"].forEach(optionValue => {
            const option = document.createElement("option");
            option.value = optionValue;
            option.textContent = optionValue;
            if (optionValue === currentValue) {
                option.selected = true;
            }
            inputElement.appendChild(option);
        });
    } else {
        inputElement = document.createElement("input");
        inputElement.type = "text";
    }

    inputElement.value = currentValue;
    span.textContent = "";
    span.appendChild(inputElement);

    document.getElementById("save-btn").style.display = "block";
}

function saveProfile() {
    const username = document.querySelector("#username input")?.value || document.getElementById("username").textContent;
    const birthday = document.querySelector("#birthday input")?.value || document.getElementById("birthday").textContent;
    const gender = document.querySelector("#gender select")?.value || document.getElementById("gender").textContent;

    fetch("server/editProfile.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ username, birthday, gender })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Profile updated successfully!");
                location.reload();
            } else {
                alert("Error updating profile: " + data.error);
            }
        })
        .catch(error => console.error("Error:", error));
}

// Function to confirm logout
function confirmLogout() {
    const isConfirmed = confirm("Are you sure you want to logout?");
    if (isConfirmed) {
        // Send a request to the logout.php script
        fetch("server/logout.php")
            .then(response => {
                if (response.ok) {
                    // Redirect to the index page after logout
                    window.location.href = "index.html";
                } else {
                    alert("Logout failed. Please try again.");
                }
            })
            .catch(error => console.error("Error:", error));
    }
}


