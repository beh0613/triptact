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

// Function to get query parameter
function getQueryParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}

async function fetchStates() {
const country = getQueryParam('country');
document.getElementById('countryName').textContent = `States in ${country}`;

try {
    const response = await fetch('server/stateData.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ country })
    });

    const data = await response.json();

    if (data.states.length > 0) {
        const container = document.getElementById('stateContainer');
        data.states.forEach(state => {
            const card = document.createElement('div');
            card.className = 'state-card';
            card.setAttribute('data-state', state.name); // Add state name attribute
            card.innerHTML = `
                <img src="${state.image}" alt="${state.name}">
                <h5>${state.name}</h5>
            `;
            
            // Attach click event listener
            card.addEventListener('click', () => {
                // Navigate to new page with country and state in URL
                window.location.href = `attraction-hotels.html?country=${country}&state=${state.name}`;
            });

            container.appendChild(card);
        });
    } else {
        document.getElementById('stateContainer').textContent = 'No states found.';
    }
} catch (error) {
    document.getElementById('stateContainer').textContent = 'Error loading states.';
}
}
fetchStates();