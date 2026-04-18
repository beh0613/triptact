//header and navigation function
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
// Fetch journal data from PHP
async function fetchJournalData() {
    try {
        const response = await fetch("server/displayJournal.php", { method: "GET" });
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const result = await response.json();
        if (result.success) {
            displayJournalData(result.data);
        } else {
            document.getElementById("responseContainer").innerHTML = `<div class="alert alert-danger">${result.error || 'Failed to retrieve data'}</div>`;
        }
    } catch (error) {
        console.error("Error fetching journal data:", error);
        document.getElementById("responseContainer").innerHTML = `<div class="alert alert-danger">Error fetching data: ${error.message}</div>`;
    }
}

// Display journal data inside white boxes with images and details
// Display journal data inside white boxes with images and details
// Display journal data with spending charts
// Display journal data with spending charts and total expenses
function displayJournalData(journals) {
    const journalDataContainer = document.getElementById("journalData");
    journalDataContainer.innerHTML = ''; // Clear existing content

    journals.forEach((journal, index) => {
        // Calculate total expenses
        const foodSpending = parseFloat(journal.food_spending) || 0;
        const transportSpending = parseFloat(journal.transport_spending) || 0;
        const otherSpending = parseFloat(journal.other_spending) || 0;
        const totalExpenses = foodSpending + transportSpending + otherSpending;

        const journalBox = document.createElement('div');
        journalBox.classList.add('journal-box');

        // Create a canvas element for the chart
        const chartContainer = document.createElement('div');
        chartContainer.style.width = '100%';
        chartContainer.style.maxWidth = '400px';
        chartContainer.style.margin = '20px auto';
        const canvas = document.createElement('canvas');
        canvas.id = `spendingChart-${index}`;
        chartContainer.appendChild(canvas);

        journalBox.innerHTML = `
            <div class="d-flex justify-content-center">
                ${journal.image_path ?
                `<img src="${journal.image_path}" alt="Image" class="journal-image">` :
                `<p>No Image</p>`}
            </div>
            <div><strong>Place:</strong> ${journal.place_name}</div>
            <div><strong>State:</strong> ${journal.state}</div>
            <div><strong>Country:</strong> ${journal.country}</div>
            <div><strong>Travel Date:</strong> ${journal.travel_date}</div>
            <div><strong>Feeling:</strong> ${journal.feeling}</div>
            <div><strong>Impression:</strong> ${journal.impression}</div>
            <div class="currency-info"><strong>Currency Use:</strong> ${journal.spending_currency}</div>
            
            <div class="spending-summary mt-3">
                <div class="total-box p-3 bg-light rounded">
                    <h5 class="text-center mb-2">Total Expenses Before Conversion</h5>
                    <div class="text-center font-weight-bold">
                        ${journal.spending_currency} ${totalExpenses.toFixed(2)}
                    </div>
                    <div class="spending-breakdown mt-2">
                        <div><strong>Food:</strong> ${journal.spending_currency} ${foodSpending.toFixed(2)}</div>
                        <div><strong>Transport:</strong> ${journal.spending_currency} ${transportSpending.toFixed(2)}</div>
                        <div><strong>Other:</strong> ${journal.spending_currency} ${otherSpending.toFixed(2)}</div>
                    </div>
                </div>
            </div>

            <h4 class="mt-4 mb-3 text-center">Spending Breakdown</h4>
        `;

        // Insert chart container after the basic info
        journalBox.appendChild(chartContainer);

        // Add the remaining journal information
        const remainingInfo = document.createElement('div');
        remainingInfo.innerHTML = `
            <div class="currency-info"><strong>Converted Currency Use:</strong> ${journal.converted_currency}</div>
            <div><strong>Converted Amount:</strong> ${journal.converted_amount}</div>
            <div class="actions-btns mt-3">
                <button class="btn btn-warning" onclick="editJournal(${journal.journal_id})">Edit</button>
                <button class="btn btn-danger" onclick="deleteJournal(${journal.journal_id})">Delete</button>
            </div>
        `;
        journalBox.appendChild(remainingInfo);
        journalDataContainer.appendChild(journalBox);

        // Create the pie chart
        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Food', 'Transport', 'Other'],
                datasets: [{
                    data: [foodSpending, transportSpending, otherSpending],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const value = context.raw;
                                const percentage = ((value / totalExpenses) * 100).toFixed(1);
                                return `${context.label}: ${journal.spending_currency} ${value.toFixed(2)} (${percentage}%)`;
                            }
                        }
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });
}

function deleteJournal(journalid) {
    // Show confirmation before deleting
    if (confirm("Are you sure you want to delete this journal?")) {
        // Call the force delete function directly
        forceDeleteJournal(journalid);
    } else {
        // If the user cancels, show a message and do nothing
        alert("Deletion canceled.");
    }
}

function forceDeleteJournal(journalid) {
    // Perform the forced delete using fetch
    fetch(`server/displayJournal.php?action=force_delete&journal_id=${journalid}`, {
        method: 'GET',
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Journal deleted successfully!");
                location.reload(); // Reload the page after successful deletion
            } else {
                alert("Failed to delete journal: " + (data.error || "Unknown error"));
            }
        })
        .catch(() => {
            location.reload(); // Reload the page regardless of any failure
        });
}


// Update the editJournal function to redirect to editJournal.html
function editJournal(journalId) {
    // Redirect to the editJournal page, passing the journal ID as a query parameter
    window.location.href = `editJournal.html?journal_id=${journalId}`;
}



// On page load, fetch journal data
window.onload = function () {
    fetchJournalData();
};

