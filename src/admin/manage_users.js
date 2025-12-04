/*
  Requirement: Add interactivity and data management to the Admin Portal.

  Instructions:
  1. Link this file to your HTML using a <script> tag with the 'defer' attribute.
     Example: <script src="manage_users.js" defer></script>
  2. Implement the JavaScript functionality as described in the TODO comments.
  3. All data management will be done by manipulating the 'students' array
     and re-rendering the table.
*/

// --- Global Data Store ---
// This array will be populated with data fetched from 'students.json'.
let students = [];
let tbody = document.querySelector('#student-table tbody');
let addStudentForm=document.querySelector('#add-student-form');
let changePasswordForm= document.querySelector('#password-form');
let searchInput= document.querySelector('#search-input');
let th = document.querySelectorAll('#student-table thead th');
// --- Functions ---

/**
 * TODO: Implement the createStudentRow function.
 * This function should take a student object {name, id, email} and return a <tr> element.
 * The <tr> should contain:
 * 1. A <td> for the student's name.
 * 2. A <td> for the student's ID.
 * 3. A <td> for the student's email.
 * 4. A <td> containing two buttons:
 * - An "Edit" button with class "edit-btn" and a data-id attribute set to the student's ID.
 * - A "Delete" button with class "delete-btn" and a data-id attribute set to the student's ID.
 */
function createStudentRow(student) {
  let row =document.createElement('tr');
  //1
  let name= document.createElement('td');
  name.textContent=student.name;
  //2
  let ID = document.createElement('td');
  ID.textContent=student.id;
  //3
  let email=document.createElement('td');
  email.textContent=student.email;
  //4
  let actionCell= document.createElement('td');
  let editButton= document.createElement('button');
  editButton.textContent='edit';
  editButton.classList.add('edit-btn');
  editButton.setAttribute('data-id', student.id);
  
  let deleteButton = document.createElement("button");
  deleteButton.textContent = "Delete";
  deleteButton.classList.add("delete-btn");
  deleteButton.setAttribute("data-id", student.id);

  actionCell.appendChild(editButton);
  actionCell.appendChild(deleteButton);

  row.appendChild(name);
  row.appendChild(ID);
  row.appendChild(email);
  row.appendChild(actionCell);
  return row;
}

/**
 * TODO: Implement the renderTable function.
 * This function takes an array of student objects.
 * It should:
 * 1. Clear the current content of the `studentTableBody`.
 * 2. Loop through the provided array of students.
 * 3. For each student, call `createStudentRow` and append the returned <tr> to `studentTableBody`.
 */
function renderTable(studentArray) {
  tbody.innerHTML="";
  for (let student of studentArray) {
    let row=createStudentRow(student);
    tbody.appendChild(row);
    
  }
}

/**
 * TODO: Implement the handleChangePassword function.
 * This function will be called when the "Update Password" button is clicked.
 * It should:
 * 1. Prevent the form's default submission behavior.
 * 2. Get the values from "current-password", "new-password", and "confirm-password" inputs.
 * 3. Perform validation:
 * - If "new-password" and "confirm-password" do not match, show an alert: "Passwords do not match."
 * - If "new-password" is less than 8 characters, show an alert: "Password must be at least 8 characters."
 * 4. If validation passes, show an alert: "Password updated successfully!"
 * 5. Clear all three password input fields.
 */
function handleChangePassword(event) {
  //1
  event.preventDefault();
  //2
  let currentPassword = document.querySelector("#current-password").value.trim();
  let newPassword = document.querySelector("#new-password").value.trim();
  let confirmPassword = document.querySelector("#confirm-password").value.trim();
  //3
  if(newPassword !== confirmPassword){
    alert('Passwords do not match.');
    return;
  }
  if(newPassword.length <8){
    alert('Password must be at least 8 characters.');
    return;
  }
  //4
  alert('Password updated successfully!');
  document.querySelector('#current-password').value="";
  document.querySelector("#new-password").value = "";
  document.querySelector("#confirm-password").value = "";
}
changePasswordForm.addEventListener("submit", handleChangePassword);
/**
 * TODO: Implement the handleAddStudent function.
 * This function will be called when the "Add Student" button is clicked.
 * It should:
 * 1. Prevent the form's default submission behavior.
 * 2. Get the values from "student-name", "student-id", and "student-email".
 * 3. Perform validation:
 * - If any of the three fields are empty, show an alert: "Please fill out all required fields."
 * - (Optional) Check if a student with the same ID already exists in the 'students' array.
 * 4. If validation passes:
 * - Create a new student object: { name, id, email }.
 * - Add the new student object to the global 'students' array.
 * - Call `renderTable(students)` to update the view.
 * 5. Clear the "student-name", "student-id", "student-email", and "default-password" input fields.
 */
function handleAddStudent(event) {
  event.preventDefault();
  let name= document.querySelector('#student-name').value.trim();
  let id= document.querySelector('#student-id').value.trim();
  let email= document.querySelector('#student-email').value.trim();
  let password= document.querySelector('#default-password').value.trim();

  if(!name || !id || !email){
    alert('Please fill out all required fields.');
    return;
  }
  let newStudent={name, id, email};
  students.push(newStudent);
  renderTable(students);

  document.querySelector('#student-name').value='';
  document.querySelector('#student-id').value='';
  document.querySelector('#student-email').value='';
  document.querySelector('#default-password').value='password123';
}
addStudentForm.addEventListener("submit", handleAddStudent);


/**
 * TODO: Implement the handleTableClick function.
 * This function will be an event listener on the `studentTableBody` (event delegation).
 * It should:
 * 1. Check if the clicked element (`event.target`) has the class "delete-btn".
 * 2. If it is a "delete-btn":
 * - Get the `data-id` attribute from the button.
 * - Update the global 'students' array by filtering out the student with the matching ID.
 * - Call `renderTable(students)` to update the view.
 * 3. (Optional) Check for "edit-btn" and implement edit logic.
 */
function handleTableClick(event) {
  //1
  if(event.target.classList.contains('delete-btn')){
   //2
    let studentID=event.target.getAttribute('data-id');
    students= students.filter(student => student.id !== studentID);
    renderTable(students);
  }
  //3
  if(event.target.classList.contains('edit-btn')){
   let studentID= event.target.getAttribute("data-id");
   alert("Edit logic dor sudent ID: "+ studentID);
  }
}
tbody.addEventListener("click", handleTableClick);

/**
 * TODO: Implement the handleSearch function.
 * This function will be called on the "input" event of the `searchInput`.
 * It should:
 * 1. Get the search term from `searchInput.value` and convert it to lowercase.
 * 2. If the search term is empty, call `renderTable(students)` to show all students.
 * 3. If the search term is not empty:
 * - Filter the global 'students' array to find students whose name (lowercase)
 * includes the search term.
 * - Call `renderTable` with the *filtered array*.
 */
function handleSearch(event) {
  let searchTerm= searchInput.value.trim().toLowerCase();
  if(searchTerm === ""){
    renderTable(students);
    return;
  }
  let filtered = students.filter(student => {
    return(
      student.name.toLowerCase().includes(searchTerm) || student.id.toLowerCase().includes(searchTerm)
    );
  })
  renderTable(filtered);
}
searchInput.addEventListener("input", handleSearch);

/**
 * TODO: Implement the handleSort function.
 * This function will be called when any `th` in the `thead` is clicked.
 * It should:
 * 1. Identify which column was clicked (e.g., `event.currentTarget.cellIndex`).
 * 2. Determine the property to sort by ('name', 'id', 'email') based on the index.
 * 3. Determine the sort direction. Use a data-attribute (e.g., `data-sort-dir="asc"`) on the `th`
 * to track the current direction. Toggle between "asc" and "desc".
 * 4. Sort the global 'students' array *in place* using `array.sort()`.
 * - For 'name' and 'email', use `localeCompare` for string comparison.
 * - For 'id', compare the values as numbers.
 * 5. Respect the sort direction (ascending or descending).
 * 6. After sorting, call `renderTable(students)` to update the view.
 */
function handleSort(event) {
  let columnIndex= event.currentTarget.cellIndex;
  let property;
  if(columnIndex===0) property='name';
  else if(columnIndex===1) property='id';
  else if(columnIndex===2) property='email';
  else return;
  let currentDir= event.currentTarget.getAttribute('data-sort-dir')||'asc';
  let newDir = currentDir === "asc" ? "desc" : "asc";
  event.currentTarget.setAttribute("data-sort-dir", newDir);
  
  students.sort((a,b)=>{
    let valA = a[property];
    let valB = b[property];

    if (property === "id") {
    
      valA = Number(valA);
      valB = Number(valB);
      return newDir === "asc" ? valA - valB : valB - valA;
    } else {
     
      return newDir === "asc"
        ? valA.localeCompare(valB)
        : valB.localeCompare(valA);
    }
  });
  renderTable(students);
}
  

/**
 * TODO: Implement the loadStudentsAndInitialize function.
 * This function needs to be 'async'.
 * It should:
 * 1. Use the `fetch()` API to get data from 'students.json'.
 * 2. Check if the response is 'ok'. If not, log an error.
 * 3. Parse the JSON response (e.g., `await response.json()`).
 * 4. Assign the resulting array to the global 'students' variable.
 * 5. Call `renderTable(students)` to populate the table for the first time.
 * 6. After data is loaded, set up all the event listeners:
 * - "submit" on `changePasswordForm` -> `handleChangePassword`
 * - "submit" on `addStudentForm` -> `handleAddStudent`
 * - "click" on `studentTableBody` -> `handleTableClick`
 * - "input" on `searchInput` -> `handleSearch`
 * - "click" on each header in `tableHeaders` -> `handleSort`
 */
async function loadStudentsAndInitialize() {
 try{
  //1
  let response= await fetch('students.json');
  //2
  if(!response.ok){
    console.error('Failed to load students.json');
    return;
  }
  //3
  let data = await response.json();
  //4
  students=data;
  //5
  renderTable(students);
  //6
  changePasswordForm.addEventListener("submit", handleChangePassword);
  addStudentForm.addEventListener("submit", handleAddStudent);
  tbody.addEventListener("click", handleTableClick);
  searchInput.addEventListener("input", handleSearch);

  th.forEach(header =>{
    header.addEventListener("click", handleSort);
  })

 }catch(error){
  console.error("Error loading students", error)
 }
}

// --- Initial Page Load ---
// Call the main async function to start the application.
loadStudentsAndInitialize();
