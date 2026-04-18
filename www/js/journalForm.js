        //header &navigation function
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
    
    
            //collection id
        const params = new URLSearchParams(window.location.search);
    const collectionId = params.get("collection_id");
    
    if (collectionId) {
        journalForm(collectionId);
    } else {
    }
    
    function journalForm(collectionId) {
        fetch(`server/journalHandler.php?collection_id=${collectionId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.text(); // Temporarily handle as plain text for debugging
            })
            .then(text => {
                console.log("Raw response:", text); // Check raw response for debugging
                try {
                    return JSON.parse(text); // Parse JSON manually to catch any errors
                } catch (e) {
                    throw new Error("Failed to parse JSON. Invalid response format.");
                }
            })
            .then(data => {
                if (data.error) {
                    console.error("Error from server:", data.error);
                } else {
                    const place = data.attraction_name;
                    const place2 = data.hotel_name;
                    const stateName = data.state_name;
                    const country = data.country_name;
    
                    // Check if `place` is null or empty
                    if (place === null || place === "") {
                        document.getElementById('attraction_name').value = place2;
                    } else {
                        document.getElementById('attraction_name').value = place;
                    }
    
                    document.getElementById('state_name').value = stateName;  
                    document.getElementById('country_name').value = country; 
                }
            })
            .catch(error => {
                console.error("Error fetching data:", error);
            });
    }
    
         
    //Calculate
    // Function to calculate total spending from all categories
    function updateSpendingTotal() {
        // Get values from the spending fields, default to 0 if empty
        const foodSpending = parseFloat(document.getElementById('food_spending').value) || 0;
        const transportSpending = parseFloat(document.getElementById('transport_spending').value) || 0;
        const otherSpending = parseFloat(document.getElementById('other_spending').value) || 0;
    
        // Calculate the total spending
        const totalSpending = foodSpending + transportSpending + otherSpending;
    
        // Display the total amount in the 'spending_amount' field (readonly)
        document.getElementById('spending_amount').value = totalSpending.toFixed(2);
    
        // Trigger conversion when total spending is updated
    }
    
    
    
    //Currency
     // API key for Open Exchange Rates
        const apiKey = 'f7c3c37edfe0407983349539293b4558';
       const apiUrl = `https://openexchangerates.org/api/currencies.json?app_id=${apiKey}`;
       // Fetch and populate the currency options
        async function populateCurrencyOptions() {
            try {
                const response = await fetch(apiUrl);
                const data = await response.json();
    
                const currencySelect = document.getElementById('currency');
                const convertToCurrencySelect = document.getElementById('convert_to_currency');
    
                // Add an initial option
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Select Currency';
                currencySelect.appendChild(defaultOption);
                convertToCurrencySelect.appendChild(defaultOption.cloneNode(true));
    
                // Loop through the data and add currency options
                for (let currency in data) {
                    const option = document.createElement('option');
                    option.value = currency;
                    option.textContent = data[currency];
                    currencySelect.appendChild(option);
                    convertToCurrencySelect.appendChild(option.cloneNode(true));
                }
            } catch (error) {
            }
        }
    
        // Populate the currency options when the page loads
        window.onload = function() {
            populateCurrencyOptions();
        };
    
        document.getElementById("convertBtn").addEventListener("click", async function() {
            const spendingAmount = parseFloat(document.getElementById("spending_amount").value);
            const fromCurrency = document.getElementById("currency").value;
            const toCurrency = document.getElementById("convert_to_currency").value;
    
            if (isNaN(spendingAmount) || spendingAmount <= 0) {
                alert("Please enter a valid spending amount.");
                return;
            }
    
            try {
                const response = await fetch(`https://openexchangerates.org/api/latest.json?app_id=${apiKey}`);
                const data = await response.json();
    
                const rates = data.rates;
    
                // Get the conversion rates for the selected currencies
                const fromRate = rates[fromCurrency];
                const toRate = rates[toCurrency];
    
                // Convert the amount
                const convertedAmount = (spendingAmount * toRate) / fromRate;
    
                // Display the result
                document.getElementById("converted_amount").value = convertedAmount.toFixed(2);
            } catch (error) {
                console.error("Error fetching exchange rates:", error);
                alert("Could not retrieve exchange rates. Please try again.");
            }
        });
    
    
    
        //Camera
        // Function to start the camera
     const videoElement = document.getElementById('cameraFeed');
        const snapshotCanvas = document.createElement('canvas');
        const capturedImage = document.getElementById('capturedImage');
        const imageNameInput = document.getElementById('imageName');
        const errorMessageElement = document.getElementById('cameraError');
        const imageSaveContainer = document.getElementById('imageSaveContainer');
    
        let videoStream = null;
    
        // Start the camera
       document.getElementById('startCamera').addEventListener('click', async (event) => {
        event.preventDefault(); // Prevent form submission
    
        try {
            videoStream = await navigator.mediaDevices.getUserMedia({ video: true });
            videoElement.srcObject = videoStream;
            errorMessageElement.style.display = 'none'; // Hide error message
        } catch (error) {
            console.error('Error accessing camera:', error);
            errorMessageElement.style.display = 'block'; // Show error message
        }
    });
    
    document.getElementById('captureButton').addEventListener('click', (event) => {
        event.preventDefault();
    
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const video = document.getElementById('cameraFeed');
        
        const scaleFactor = 0.5;  // Adjust this value to control the image size reduction
        canvas.width = video.videoWidth * scaleFactor;
        canvas.height = video.videoHeight * scaleFactor;
    
        // Draw the video frame to the canvas
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    
        // Convert the canvas content to base64
        const compressedImage = canvas.toDataURL('image/jpeg', 0.7);  // Reduce quality to 70%
    
        // Proceed with uploading the compressed image
        capturedImage.src = compressedImage;
        imageSaveContainer.style.display = 'block';  // Display image saving options
    });
    
    
    document.getElementById("submitButton").addEventListener("click", async function () {
         event.preventDefault();
        const formData = new FormData();
    
        // Collect form data
        formData.append("place_name", document.getElementById("attraction_name").value);
        formData.append("state", document.getElementById("state_name").value);
        formData.append("country", document.getElementById("country_name").value);
        formData.append("travel_date", document.getElementById("travel_date").value || "");
        formData.append("feeling", document.getElementById("feeling").value);
        formData.append("impression", document.getElementById("impression").value || "");
        formData.append("spending_amount", document.getElementById("spending_amount").value || "");
        formData.append("spending_currency", document.getElementById("currency").value || "");
        formData.append("converted_amount", document.getElementById("converted_amount").value || "");
        formData.append("converted_currency", document.getElementById("convert_to_currency").value || "");
            formData.append("food_spending", document.getElementById("food_spending").value || "");
        formData.append("transport_spending", document.getElementById("transport_spending").value || "");
        formData.append("other_spending", document.getElementById("other_spending").value || "");
    
        formData.append("collection_id", collectionId);
    
        // Add captured image to FormData if available
        if (capturedImage.src && capturedImage.src !== "") {
            try {
                const response = await fetch(capturedImage.src);
                const imageBlob = await response.blob();
                formData.append("image_path", imageBlob, `${imageNameInput.value.trim()}.png`);
            } catch (error) {
                alert("Failed to include captured image. Please try again.");
                console.error(error);
                return;
            }
        }
    
        // Submit the form
    try {
        const response = await fetch("server/saveJournal.php", {
            method: "POST",
            body: formData,
        });
    
        if (!response.ok) {
            throw new Error(`HTTP error: ${response.status}`);
        }
    
        const result = await response.json();
        console.log("Server response:", result);
    
        if (result.success) {
            alert("Journal saved successfully!");
            window.location.href = result.redirect;
        } else {
            alert(`Error: ${result.error || "Unknown error occurred."}`);
        }
    } catch (error) {
        console.error("Error submitting the form:", error);
        alert(`An error occurred: ${error.message}`);
    }
    
    
    });
    