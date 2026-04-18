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
    const journalId = params.get("journal_id");
    
    if (journalId) {
        fetchJournalData(journalId);
    }
    
    
    function fetchJournalData(journalId) {
        fetch(`server/editJournal.php?journal_id=${journalId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error("Error:", data.error);
                } else {
                    // Filling form fields with retrieved data
                    document.getElementById('attraction_name').value = data.place_name;
                    document.getElementById('state_name').value = data.state;
                    document.getElementById('country_name').value = data.country;
                    document.getElementById('travel_date').value = data.travel_date;
                    document.getElementById('feeling').value = data.feeling;
                    document.getElementById('impression').value = data.impression;
                    
                    // Set spending values (editable fields)
                    document.getElementById('food_spending').value = data.food_spending;
                    document.getElementById('transport_spending').value = data.transport_spending;
                    document.getElementById('other_spending').value = data.other_spending;
    
                    // Populate currency dropdowns
                    populateCurrencyOptions(data.spending_currency);
                    document.getElementById('convert_to_currency').value = data.converted_currency;
                    document.getElementById('converted_amount').value = data.converted_amount;
                    document.getElementById('currency').value = data.spending_currency;
    
                    // Display total spending field (readonly)
                    document.getElementById('spending_amount').value = data.spending_amount;
    
                    // Check for image_path and display it if available
                    if (data.image_path) {
                        document.getElementById('capturedImage').src = data.image_path; // Set image source
                        document.getElementById('imageSaveContainer').style.display = 'block'; // Show image save section
    
                        // Populate the image name input field with the image path
                        document.getElementById('imageName').value = data.image_path;
                    } else {
                        // If no image, hide the image save section
                        document.getElementById('imageSaveContainer').style.display = 'none';
                    }
                }
            })
            .catch(error => console.error('Error:', error));
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
     
    async function populateCurrencyOptions(currentCurrency) {
        try {
            const response = await fetch(apiUrl);  // Fetch currencies
            const data = await response.json();
    
            // Select the dropdowns
            const currencySelect = document.getElementById('currency');
            const convertToCurrencySelect = document.getElementById('convert_to_currency');
    
            // Clear existing options
            currencySelect.innerHTML = '';
            convertToCurrencySelect.innerHTML = '';
    
            // Add default option
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Select Currency';
            currencySelect.appendChild(defaultOption);
            convertToCurrencySelect.appendChild(defaultOption.cloneNode(true));
    
            // Loop through the data to create and append options
            for (let currency in data) {
                const option = document.createElement('option');
                option.value = currency;
                option.textContent = data[currency];
    
                // Add to both dropdowns
                currencySelect.appendChild(option);
                convertToCurrencySelect.appendChild(option.cloneNode(true));
            }
    
            // Set the selected currency (from database)
            if (currentCurrency) {
                currencySelect.value = currentCurrency;
            }
            
            // Ensure visibility after populating
            currencySelect.style.display = 'block';
            convertToCurrencySelect.style.display = 'block';
    
        } catch (error) {
            console.error('Error fetching currencies:', error);
        }
    }
    
    document.getElementById("convert_to_currency").addEventListener("change", () => {
        // Clear the 'converted_amount' field when currency selection changes
        document.getElementById("converted_amount").value = "";
    });
    
    
        document.getElementById("convertBtn").addEventListener("click", async function() {
        const spendingAmount = parseFloat(document.getElementById("spending_amount").value);
        const fromCurrency = document.getElementById("currency").value;
        const toCurrency = document.getElementById("convert_to_currency").value;
    
        if (isNaN(spendingAmount) || spendingAmount <= 0) {
            alert("Please enter a valid spending amount.");
            return;
        }
    
        // Ensure both currencies are selected
        if (!fromCurrency || !toCurrency) {
            alert("Please select both currencies.");
            return;
        }
    
        try {
            // Fetch exchange rates from Open Exchange Rates API
            const apiUrl = `https://openexchangerates.org/api/latest.json?app_id=${apiKey}`;
            const response = await fetch(apiUrl);
            const data = await response.json();
    
            const rates = data.rates;
    
            // Get the conversion rates for the selected currencies
            const fromRate = rates[fromCurrency];
            const toRate = rates[toCurrency];
    
            if (!fromRate || !toRate) {
                alert("Currency rates for the selected currencies are not available.");
                return;
            }
    
            // Convert the amount
            const convertedAmount = (spendingAmount * toRate) / fromRate;
    
            // Display the result in the 'converted_amount' field
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
    
    
    document.getElementById("updateButton").addEventListener("click", async function (event) {
        event.preventDefault();
        
        // Get the value of the spending currency
        const spendingCurrency = document.getElementById("currency").value;
        if (!spendingCurrency) {
            alert("Please select a spending currency before updating the journal.");
            return; // Stop further processing
        }
    
        const convertedAmount = document.getElementById("converted_amount").value;
    
        // Check if 'converted_amount' is empty or null
        if (!convertedAmount || convertedAmount.trim() === "") {
            alert("Please enter a converted amount before proceeding.");
            return; // Stop further execution
        }
    
        const formData = new FormData();
    
        formData.append("journal_id", journalId);  // Use journal_id to identify the entry for update
        formData.append("place_name", document.getElementById("attraction_name").value);
        formData.append("state", document.getElementById("state_name").value);
        formData.append("country", document.getElementById("country_name").value);
        formData.append("travel_date", document.getElementById("travel_date").value);
        formData.append("feeling", document.getElementById("feeling").value);
        formData.append("impression", document.getElementById("impression").value);
        formData.append("food_spending", document.getElementById("food_spending").value);
        formData.append("transport_spending", document.getElementById("transport_spending").value);
        formData.append("other_spending", document.getElementById("other_spending").value);
    formData.append("spending_currency",spendingCurrency );
    formData.append("converted_currency",document.getElementById("convert_to_currency").value);
        formData.append("converted_amount", document.getElementById("converted_amount").value);
    
        formData.append("spending_amount", document.getElementById("spending_amount").value);  // Total will be readonly
    
        // Handle the image if it was recaptured
        const capturedImage = document.getElementById("capturedImage");
        if (capturedImage.src && capturedImage.src !== "") {
            try {
                const response = await fetch(capturedImage.src);
                const imageBlob = await response.blob();
                formData.append("image_path", imageBlob, `${document.getElementById("imageName").value.trim()}.png`);
            } catch (error) {
                console.error('Error capturing image:', error);
            }
        }
    
        // Send the updated journal data to the server for saving
        try {
            const response = await fetch("server/editJournal.php", {
                method: "POST",
                body: formData
            });
    
            const result = await response.json();
            if (result.success) {
                alert("Journal updated successfully!");
                window.location.href = "displayJournal.html";
            } else {
                alert(`Error: ${result.error || "Unknown error occurred."}`);
            }
        } catch (error) {
            console.error("Error submitting the form:", error);
            alert("An error occurred. Please try again.");
        }
    });
    
    