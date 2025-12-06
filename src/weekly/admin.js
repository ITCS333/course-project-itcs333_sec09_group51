/*
  Requirement: Make the "Manage Weekly Breakdown" page interactive.

  Instructions:
  1. Link this file to `admin.html` using:
     <script src="admin.js" defer></script>
  
  2. In `admin.html`, add an `id="weeks-tbody"` to the <tbody> element
     inside your `weeks-table`.
  
  3. Implement the TODOs below.
*/

// --- Global Data Store ---
// This will hold the weekly data loaded from the JSON file.
let weeks = [];

// --- Element Selections ---
// TODO: Select the week form ('#week-form').
const weekForm = document.getElementById('week-form');

// TODO: Select the weeks table body ('#weeks-tbody').
const weeksTableBody = document.getElementById('weeks-tbody');

// Select other elements we need
const formTitle = document.getElementById('form-title');
const submitWeekBtn = document.getElementById('submit-week');
const cancelEditBtn = document.getElementById('cancel-edit');
const clearFormBtn = document.getElementById('clear-form');

// --- Functions ---

/**
 * TODO: Implement the createWeekRow function.
 * It takes one week object {id, title, description}.
 * It should return a <tr> element with the following <td>s:
 * 1. A <td> for the `title`.
 * 2. A <td> for the `description`.
 * 3. A <td> containing two buttons:
 * - An "Edit" button with class "edit-btn" and `data-id="${id}"`.
 * - A "Delete" button with class "delete-btn" and `data-id="${id}"`.
 */
function createWeekRow(week) {
  // Create a table row
  const row = document.createElement('tr');
  
  // Extract week number from ID (e.g., "week_1" → 1)
  const weekNumber = week.id.split('_')[1] || '';
  
  // Create title cell
  const titleCell = document.createElement('td');
  titleCell.textContent = weekNumber;
  
  // Create description cell with title
  const descCell = document.createElement('td');
  descCell.textContent = week.title || '';
  
  // Create start date cell
  const dateCell = document.createElement('td');
  dateCell.textContent = week.startDate || '';
  
  // Create short description cell
  const shortDescCell = document.createElement('td');
  const shortDesc = week.description ? 
    (week.description.length > 100 ? week.description.substring(0, 100) + '...' : week.description) : 
    '';
  shortDescCell.textContent = shortDesc;
  
  // Create actions cell with buttons
  const actionsCell = document.createElement('td');
  actionsCell.className = 'actions-cell';
  
  // Create Edit button
  const editButton = document.createElement('button');
  editButton.textContent = 'Edit';
  editButton.className = 'edit-btn btn btn-edit';
  editButton.setAttribute('data-id', week.id);
  
  // Create Delete button
  const deleteButton = document.createElement('button');
  deleteButton.textContent = 'Delete';
  deleteButton.className = 'delete-btn btn btn-delete';
  deleteButton.setAttribute('data-id', week.id);
  
  // Add buttons to actions cell
  actionsCell.appendChild(editButton);
  actionsCell.appendChild(deleteButton);
  
  // Add all cells to the row
  row.appendChild(titleCell);
  row.appendChild(descCell);
  row.appendChild(dateCell);
  row.appendChild(shortDescCell);
  row.appendChild(actionsCell);
  
  return row;
}

/**
 * TODO: Implement the renderTable function.
 * It should:
 * 1. Clear the `weeksTableBody`.
 * 2. Loop through the global `weeks` array.
 * 3. For each week, call `createWeekRow()`, and
 * append the resulting <tr> to `weeksTableBody`.
 */
function renderTable() {
  // Clear the table body
  weeksTableBody.innerHTML = '';
  
  // Loop through weeks and create rows
  weeks.forEach(week => {
    const weekRow = createWeekRow(week);
    weeksTableBody.appendChild(weekRow);
  });
}

/**
 * Reset form to add mode
 */
function resetFormToAddMode() {
  weekForm.reset();
  formTitle.textContent = 'Add a New Week';
  submitWeekBtn.textContent = 'Add Week';
  cancelEditBtn.style.display = 'none';
  window.editingWeekId = null;
}

/**
 * Clear form completely
 */
function clearForm() {
  weekForm.reset();
  
  // Also clear any values that might not be reset by form.reset()
  document.getElementById('week-title').value = '';
  document.getElementById('week-start-date').value = '';
  document.getElementById('week-description').value = '';
  document.getElementById('week-links').value = '';
  
  // Make sure we're in add mode
  resetFormToAddMode();
  
  // Show feedback (optional)
  console.log('Form cleared');
}

/**
 * TODO: Implement the handleAddWeek function.
 * This is the event handler for the form's 'submit' event.
 * It should:
 * 1. Prevent the form's default submission.
 * 2. Get the values from the title, start date, and description inputs.
 * 3. Get the value from the 'week-links' textarea. Split this value
 * by newlines (`\n`) to create an array of link strings.
 * 4. Create a new week object with a unique ID (e.g., `id: \`week_${Date.now()}\``).
 * 5. Add this new week object to the global `weeks` array (in-memory only).
 * 6. Call `renderTable()` to refresh the list.
 * 7. Reset the form.
 */
function handleAddWeek(event) {
  // 1. Prevent the form's default submission
  event.preventDefault();
  
  // 2. Get the values from the form inputs
  const titleInput = document.getElementById('week-title');
  const dateInput = document.getElementById('week-start-date');
  const descInput = document.getElementById('week-description');
  const linksInput = document.getElementById('week-links');
  
  const title = titleInput.value.trim();
  const startDate = dateInput.value;
  const description = descInput.value.trim();
  
  // 3. Get links and split by newlines
  const linksText = linksInput.value.trim();
  const links = linksText ? linksText.split('\n').map(link => link.trim()).filter(link => link !== '') : [];
  
  // Basic validation
  if (!title || !startDate || !description) {
    alert('Please fill in all required fields: Title, Start Date, and Description');
    return;
  }
  
  // Check if we're editing or adding
  if (window.editingWeekId) {
    // Update existing week
    const weekIndex = weeks.findIndex(w => w.id === window.editingWeekId);
    if (weekIndex !== -1) {
      weeks[weekIndex] = {
        ...weeks[weekIndex],
        title: title,
        startDate: startDate,
        description: description,
        links: links
      };
      showStatus('Week updated successfully!', 'success');
    }
  } else {
    // 4. Create a new week object with a unique ID
    const newId = `week_${weeks.length + 1}`;
    const newWeek = {
      id: newId,
      title: title,
      startDate: startDate,
      description: description,
      links: links
    };
    
    // 5. Add to the global weeks array
    weeks.push(newWeek);
    showStatus('Week added successfully!', 'success');
  }
  
  // 6. Refresh the table
  renderTable();
  
  // 7. Reset the form
  resetFormToAddMode();
}

/**
 * Show status message
 */
function showStatus(message, type = 'success') {
  const statusMessage = document.getElementById('status-message');
  if (statusMessage) {
    statusMessage.textContent = message;
    statusMessage.className = `status-message ${type}`;
    statusMessage.style.display = 'block';
    
    // Hide message after 3 seconds
    setTimeout(() => {
      statusMessage.style.display = 'none';
    }, 3000);
  }
}

/**
 * TODO: Implement the handleTableClick function.
 * This is an event listener on the `weeksTableBody` (for delegation).
 * It should:
 * 1. Check if the clicked element (`event.target`) has the class "delete-btn".
 * 2. If it does, get the `data-id` attribute from the button.
 * 3. Update the global `weeks` array by filtering out the week
 * with the matching ID (in-memory only).
 * 4. Call `renderTable()` to refresh the list.
 */
function handleTableClick(event) {
  // 1. Check if the clicked element is a delete button
  if (event.target.classList.contains('delete-btn')) {
    // 2. Get the data-id attribute
    const weekId = event.target.getAttribute('data-id');
    
    // Confirm deletion
    if (!confirm(`Are you sure you want to delete this week?`)) {
      return;
    }
    
    // 3. Filter out the week with the matching ID
    weeks = weeks.filter(week => week.id !== weekId);
    
    // 4. Refresh the table
    renderTable();
    
    // If we were editing the deleted week, reset form
    if (window.editingWeekId === weekId) {
      resetFormToAddMode();
    }
    
    showStatus('Week deleted successfully!', 'success');
  }
  
  // Handle edit button click
  if (event.target.classList.contains('edit-btn')) {
    const weekId = event.target.getAttribute('data-id');
    const weekToEdit = weeks.find(week => week.id === weekId);
    
    if (weekToEdit) {
      // Populate form with week data for editing
      document.getElementById('week-title').value = weekToEdit.title;
      document.getElementById('week-start-date').value = weekToEdit.startDate;
      document.getElementById('week-description').value = weekToEdit.description;
      document.getElementById('week-links').value = weekToEdit.links ? weekToEdit.links.join('\n') : '';
      
      // Change form to edit mode
      formTitle.textContent = 'Edit Week';
      submitWeekBtn.textContent = 'Update Week';
      cancelEditBtn.style.display = 'inline-block';
      
      // Store the ID being edited
      window.editingWeekId = weekId;
      
      // Scroll to form
      document.querySelector('.form-section').scrollIntoView({ behavior: 'smooth' });
      
      showStatus('Editing week. Make changes and click "Update Week".', 'info');
    }
  }
}

/**
 * TODO: Implement the loadAndInitialize function.
 * This function needs to be 'async'.
 * It should:
 * 1. Use `fetch()` to get data from 'weeks.json'.
 * 2. Parse the JSON response and store the result in the global `weeks` array.
 * 3. Call `renderTable()` to populate the table for the first time.
 * 4. Add the 'submit' event listener to `weekForm` (calls `handleAddWeek`).
 * 5. Add the 'click' event listener to `weeksTableBody` (calls `handleTableClick`).
 */
async function loadAndInitialize() {
  try {
    // 1. Fetch data from weeks.json
    const response = await fetch('weeks.json');
    
    if (!response.ok) {
      throw new Error(`HTTP error! Status: ${response.status}`);
    }
    
    // 2. Parse JSON and store in global weeks array
    weeks = await response.json();
    
    // 3. Populate the table
    renderTable();
    
    // 4. Add submit event listener to form
    weekForm.addEventListener('submit', handleAddWeek);
    
    // 5. Add click event listener to table body (event delegation)
    weeksTableBody.addEventListener('click', handleTableClick);
    
    // Set up cancel edit button
    if (cancelEditBtn) {
      cancelEditBtn.addEventListener('click', resetFormToAddMode);
    }
    
    // Set up clear form button - FIXED
    if (clearFormBtn) {
      clearFormBtn.addEventListener('click', clearForm);
      console.log('Clear button event listener added');
    } else {
      console.error('Clear button not found! Check HTML for id="clear-form"');
    }
    
    // Hide loading indicator
    const loadingDiv = document.getElementById('loading');
    if (loadingDiv) {
      loadingDiv.style.display = 'none';
    }
    
    console.log('Admin page initialized successfully');
    
  } catch (error) {
    console.error('Error loading weeks:', error);
    
    // Show error in table
    weeksTableBody.innerHTML = `
      <tr>
        <td colspan="5" style="text-align: center; color: red; padding: 20px;">
          Error loading weeks data: ${error.message}<br>
          Please check that weeks.json exists and has valid data.
        </td>
      </tr>
    `;
    
    // Still set up event listeners even if data fails to load
    weekForm.addEventListener('submit', handleAddWeek);
    weeksTableBody.addEventListener('click', handleTableClick);
    
    // Set up clear button even on error
    if (clearFormBtn) {
      clearFormBtn.addEventListener('click', clearForm);
    }
    
    // Hide loading indicator
    const loadingDiv = document.getElementById('loading');
    if (loadingDiv) {
      loadingDiv.style.display = 'none';
    }
  }
}

// --- Initial Page Load ---
// Call the main async function to start the application.
loadAndInitialize();