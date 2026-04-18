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
    
    document.getElementById('backButton').addEventListener('click', () => {
window.history.back(); // Navigate to the previous page
});

    const apiKey = "01c348787bf83521c5c58da319e62121"; // API key for fetching weather data
    const params = new URLSearchParams(window.location.search);
    const collectionId = params.get("collection_id");

    if (collectionId) {
        fetchWeatherInfo(collectionId);
    } else {
        document.getElementById('weather-info').innerHTML =
            "<p class='error-message'>Collection ID is missing.</p>";
    }

    function fetchWeatherInfo(collectionId) {
        fetch(`server/weather.php?collection_id=${collectionId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    document.getElementById('weather-info').innerHTML =
                        `<p class="error-message">Error: ${data.error}</p>`;
                } else {
                    const weather = data.weather;
                    const stateName = data.state_name;
                    const currentDate = new Date().toLocaleDateString();

                    const weatherHTML = `
                        <h3>Current Weather in <span>${stateName}</span></h3>
                        <p>Temperature: <span>${weather.main.temp}°C</span></p>
                        <p>Weather: <span>${weather.weather[0].description}</span></p>
                        <p>Humidity: <span>${weather.main.humidity}%</span></p>
                        <p>Wind Speed: <span>${weather.wind.speed} m/s</span></p>
                    `;
                    document.getElementById('weather-info').innerHTML = weatherHTML;
                }
            })
            .catch(error => {
                console.error("Error fetching weather data:", error);
                document.getElementById('weather-info').innerHTML =
                    "<p class='error-message'>An error occurred while fetching weather data.</p>";
            });
    }