$(document).ready(function () {
    $('#carouselBackground').carousel({
        interval: 3000, // Change slide every 3 seconds
        ride: 'carousel'
    });
});

      function toggleNav() {
            var sideNav = document.getElementById("sideNav");
            if (sideNav.style.width === "250px") {
                sideNav.style.width = "0";
            } else {
                sideNav.style.width = "250px";
            }
        }

        function closeNav() {
            document.getElementById("sideNav").style.width = "0";
        }

        // Validate country using fetch and display messages
   async function validateCountry(event) {
    event.preventDefault();  // Prevent the form from submitting and reloading the page

    const country = document.getElementById('countryInput').value.trim();
    const messageDiv = document.getElementById('message');

    if (!country) {
        messageDiv.textContent = "Please enter a country name.";
        return;
    }

    try {
        const response = await fetch('server/travel.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ country })
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const data = await response.json();

        if (data.exists) {
            // If country exists, navigate to the relevant page
            window.location.href = `stateDisplay.html?country=${encodeURIComponent(country)}`;
        } else {
            
            alert("Country not found."); // Optional: alert the user too
        }
    } catch (error) {
        
        messageDiv.textContent = "An error occurred. Please try again.";
    }
}

// Ensure the form submission is captured for handling with JS
document.getElementById('countryForm').addEventListener('submit', validateCountry);
