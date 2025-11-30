/*
  Requirement: Make the "Manage Resources" page interactive.

  Instructions:
  1. Link this file to `admin.html` using:
     <script src="admin.js" defer></script>
  
  2. In `admin.html`, add an `id="resources-tbody"` to the <tbody> element
     inside your `resources-table`.
  
  3. Implement the TODOs below.
*/

// --- Global Data Store ---
// This will hold the resources loaded from the JSON file.
let resources = [];

// --- Element Selections ---
// TODO: Select the resource form ('#resource-form').
let form = document.getElementById('resource-form');
// TODO: Select the resources table body ('#resources-tbody').
let tbody = document.getElementById('resource-tbody');
// --- Functions ---

/**
 * TODO: Implement the createResourceRow function.
 * It takes one resource object {id, title, description}.
 * It should return a <tr> element with the following <td>s:
 * 1. A <td> for the `title`.
 * 2. A <td> for the `description`.
 * 3. A <td> containing two buttons:
 * - An "Edit" button with class "edit-btn" and `data-id="${id}"`.
 * - A "Delete" button with class "delete-btn" and `data-id="${id}"`.
 */
function createResourceRow(resource) {
  // ... your implementation here ...
  
  let tr = document.createElement('tr');
  let title = document.createElement('td');
  title.textContent= resource.title ;
  let desc = document.createElement('td');
  desc.textContent = resource.description ;
  let button = document.createElement('td');
  
  let edit = document.createElement('button');
  edit.textContent = 'Edit';
  edit.className = 'edit-btn' ;
  edit.setAttribute(`data-id="${id}"`);
  
  let delet = document.createElement('button');
  delet.textContent = 'Delete';
  delet.className = 'delete-btn' ;
  delet.setAttribute(`data-id="${id}"`);
 
  button.appendChild(edit);
  button.appendChild(delet);
 
  tr.appendChild(title);
  tr.appendChild(desc);
  tr.appendChild(button);

  return tr ;
}

/**
 * TODO: Implement the renderTable function.
 * It should:
 * 1. Clear the `resourcesTableBody`.
 * 2. Loop through the global `resources` array.
 * 3. For each resource, call `createResourceRow()`, and
 * append the resulting <tr> to `resourcesTableBody`.
 */
function renderTable() {
  // ... your implementation here ...
  tbody.innerHTML = '';
   resources.forEach(resource => {let row = createResourceRow(resource); 
    tbody.appendChild(row);
   } )
}

/**
 * TODO: Implement the handleAddResource function.
 * This is the event handler for the form's 'submit' event.
 * It should:
 * 1. Prevent the form's default submission.
 * 2. Get the values from the title, description, and link inputs.
 * 3. Create a new resource object with a unique ID (e.g., `id: \`res_${Date.now()}\``).
 * 4. Add this new resource object to the global `resources` array (in-memory only).
 * 5. Call `renderTable()` to refresh the list.
 * 6. Reset the form.
 */
function handleAddResource(event) {
  // ... your implementation here ...
  event.preventDefult();
  let title = document.getElementById('resource-title');
  let desc = document.getElementById('resource-description');
  let link = document.getElementById('resource-link');

  title= title.value.trim();
  desc= desc.value.trim();
  link= link.value.trim();

  let newres = { 
    id: `res_${Date.now()}` , 
    title: title , 
    description: desc ,
    link: link 
  };

  resources.push(newres);
  renderTable();
  form.reset();
}

/**
 * TODO: Implement the handleTableClick function.
 * This is an event listener on the `resourcesTableBody` (for delegation).
 * It should:
 * 1. Check if the clicked element (`event.target`) has the class "delete-btn".
 * 2. If it does, get the `data-id` attribute from the button.
 * 3. Update the global `resources` array by filtering out the resource
 * with the matching ID (in-memory only).
 * 4. Call `renderTable()` to refresh the list.
 */
function handleTableClick(event) {
  // ... your implementation here ...

}

/**
 * TODO: Implement the loadAndInitialize function.
 * This function needs to be 'async'.
 * It should:
 * 1. Use `fetch()` to get data from 'resources.json'.
 * 2. Parse the JSON response and store the result in the global `resources` array.
 * 3. Call `renderTable()` to populate the table for the first time.
 * 4. Add the 'submit' event listener to `resourceForm` (calls `handleAddResource`).
 * 5. Add the 'click' event listener to `resourcesTableBody` (calls `handleTableClick`).
 */
async function loadAndInitialize() {
  // ... your implementation here ...
}

// --- Initial Page Load ---
// Call the main async function to start the application.
loadAndInitialize();
