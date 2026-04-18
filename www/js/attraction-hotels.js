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
// Fetch query params from URL
const params = new URLSearchParams(window.location.search);
const country = params.get('country');
const state = params.get('state');

document.getElementById('selectedCountry').textContent = `Exploring ${state}, ${country}`;

// Fetch and display hotels and attractions
async function fetchDetails() {
    try {
        const response = await fetch('server/attraction-hotels.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ country, state })
        });

        const data = await response.json();
        if (data.error) throw new Error(data.error);

        renderResults(data);
    } catch (error) {
        document.getElementById('results').textContent = `Error: ${error.message}`;
    }
}

function renderResults(data) {
const hotelsContainer = document.getElementById('hotels');
const attractionsContainer = document.getElementById('attractions');

// Render hotels
if (data[state].hotels && data[state].hotels.length > 0) {
data[state].hotels.forEach(hotel => {
    const card = createCard(hotel.hotel_name, hotel.image, hotel.hotel_id, "hotel");
    hotelsContainer.appendChild(card);
});
} else {
hotelsContainer.innerHTML += '<p>No hotels found.</p>';
}

// Render attractions
if (data[state].attractions && data[state].attractions.length > 0) {
data[state].attractions.forEach(attraction => {
    const card = createCard(attraction.attraction_name, attraction.imageD, attraction.attraction_id, "attraction");
    attractionsContainer.appendChild(card);
});
} else {
attractionsContainer.innerHTML += '<p>No attractions found.</p>';
}
}

function createCard(name, image, id, type) {
const card = document.createElement('div');
card.className = 'card';
card.style = 'margin: 10px; padding: 15px;';

card.innerHTML = `
<img src="${image}" alt="${name}" style="width: 100%; height: 150px; object-fit: cover;">
<h5>${name}</h5>
<a href="server/details.php?id=${id}&category=${type}" class="btn btn-primary btn-sm">View Details</a>
`;
return card;
}

fetchDetails();