let assignments = [];

const assignmentForm = document.querySelector('#assignment-form');
const assignmentsTableBody = document.querySelector('#assignments-tbody');


function createAssignmentRow(assignment) {
    const tr = document.createElement('tr');

  
    const tdTitle = document.createElement('td');
    tdTitle.textContent = assignment.title;
    tr.appendChild(tdTitle);

   
    const tdDue = document.createElement('td');
    tdDue.textContent = assignment.dueDate;
    tr.appendChild(tdDue);

   
    const tdActions = document.createElement('td');

    const editBtn = document.createElement('button');
    editBtn.textContent = 'Edit';
    editBtn.classList.add('edit-btn');
    editBtn.dataset.id = assignment.id;

    const deleteBtn = document.createElement('button');
    deleteBtn.textContent = 'Delete';
    deleteBtn.classList.add('delete-btn');
    deleteBtn.dataset.id = assignment.id;

    tdActions.appendChild(editBtn);
    tdActions.appendChild(deleteBtn);
    tr.appendChild(tdActions);

    return tr;
}

// Render the table with current assignments
function renderTable() {
    // Clear table body
    assignmentsTableBody.innerHTML = '';

    // Loop through assignments and append rows
    assignments.forEach(assignment => {
        const row = createAssignmentRow(assignment);
        assignmentsTableBody.appendChild(row);
    });
}

// Handle adding a new assignment
function handleAddAssignment(event) {
    event.preventDefault();

    // Get input values
    const title = document.querySelector('#assignment-title').value.trim();
    const description = document.querySelector('#assignment-description').value.trim();
    const dueDate = document.querySelector('#assignment-due-date').value;
    const files = document.querySelector('#assignment-files').value.trim();

    if (!title || !dueDate) {
        alert('Please fill in the required fields.');
        return;
    }

    // Create new assignment object
    const newAssignment = {
        id: `asg_${Date.now()}`,
        title,
        description,
        dueDate,
        files
    };

    // Add to global array
    assignments.push(newAssignment);

    // Refresh table
    renderTable();

    // Reset form
    assignmentForm.reset();
}

// Handle Edit/Delete clicks using delegation
function handleTableClick(event) {
    const target = event.target;

    // Delete button clicked
    if (target.classList.contains('delete-btn')) {
        const id = target.dataset.id;
        assignments = assignments.filter(a => a.id !== id);
        renderTable();
    }

    // Edit button clicked (simple example: fill form with data)
    if (target.classList.contains('edit-btn')) {
        const id = target.dataset.id;
        const assignment = assignments.find(a => a.id === id);
        if (assignment) {
            document.querySelector('#assignment-title').value = assignment.title;
            document.querySelector('#assignment-description').value = assignment.description;
            document.querySelector('#assignment-due-date').value = assignment.dueDate;
            document.querySelector('#assignment-files').value = assignment.files;

            // Optional: remove the old assignment so submitting updates it
            assignments = assignments.filter(a => a.id !== id);
        }
    }
}

// Load assignments from JSON and initialize page
async function loadAndInitialize() {
    try {
        const response = await fetch('assignments.json');
        if (!response.ok) throw new Error('Failed to load assignments.json');
        const data = await response.json();
        assignments = data;

        renderTable();

        
        assignmentForm.addEventListener('submit', handleAddAssignment);
        assignmentsTableBody.addEventListener('click', handleTableClick);
    } catch (error) {
        console.error('Error loading assignments:', error);
        // Still attach event listeners even if fetch fails
        assignmentForm.addEventListener('submit', handleAddAssignment);
        assignmentsTableBody.addEventListener('click', handleTableClick);
    }
}


loadAndInitialize();
