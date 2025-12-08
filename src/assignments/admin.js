let assignments = [];

let editingAssignmentId = null;
 

const assignmentForm = document.getElementById("assignment-form");

const assignmentsTableBody = document.getElementById("assignments-tbody");

const titleInput = document.getElementById("assignment-title");

const descInput = document.getElementById("assignment-description");

const dueDateInput = document.getElementById("assignment-due-date");

const filesInput = document.getElementById("assignment-files");

const submitBtn = document.getElementById("add-assignment");
 
// Create row for one assignment

function createAssignmentRow(assignment) {

  const tr = document.createElement("tr");
 
  const titleTd = document.createElement("td");

  titleTd.textContent = assignment.title;
 
  const dueTd = document.createElement("td");

  dueTd.textContent = assignment.dueDate;
 
  const actionsTd = document.createElement("td");
 
  const editBtn = document.createElement("button");

  editBtn.textContent = "Edit";

  editBtn.classList.add("edit-btn");

  editBtn.dataset.id = assignment.id;
 
  const deleteBtn = document.createElement("button");

  deleteBtn.textContent = "Delete";

  deleteBtn.classList.add("delete-btn");

  deleteBtn.dataset.id = assignment.id;
 
  actionsTd.appendChild(editBtn);

  actionsTd.appendChild(deleteBtn);
 
  tr.appendChild(titleTd);

  tr.appendChild(dueTd);

  tr.appendChild(actionsTd);
 
  return tr;

}
 
// Render whole table

function renderTable() {

  assignmentsTableBody.innerHTML = "";

  assignments.forEach((assignment) => {

    const row = createAssignmentRow(assignment);

    assignmentsTableBody.appendChild(row);

  });

}
 
// Handle Add/Edit form submit

function handleAddAssignment(event) {

  event.preventDefault();
 
  const title = titleInput.value.trim();

  const description = descInput.value.trim();

  const dueDate = dueDateInput.value;

  const filesRaw = filesInput.value.trim();
 
  if (!title || !description || !dueDate) {

    alert("Please fill in title, description, and due date.");

    return;

  }
 

  const files = filesRaw

    ? filesRaw.split("\n").map((line) => line.trim()).filter((line) => line !== "")

    : [];
 
  if (editingAssignmentId === null) {

    // ADD MODE

    const newAssignment = {

      id: `asg_${Date.now()}`,

      title,

      description,

      dueDate,

      files,

    };

    assignments.push(newAssignment);

  } else {

    // EDIT MODE

    const index = assignments.findIndex((a) => a.id === editingAssignmentId);

    if (index !== -1) {

      assignments[index].title = title;

      assignments[index].description = description;

      assignments[index].dueDate = dueDate;

      assignments[index].files = files;

    }

    editingAssignmentId = null;

    submitBtn.textContent = "Add Assignment";

  }
 
  renderTable();

  assignmentForm.reset();

}
 
// Handle clicks in table (Edit / Delete)

function handleTableClick(event) {

  const target = event.target;
 
  if (target.classList.contains("delete-btn")) {

    const id = target.dataset.id;

    assignments = assignments.filter((a) => a.id !== id);

    renderTable();

  }
 
  if (target.classList.contains("edit-btn")) {

    const id = target.dataset.id;

    const assignment = assignments.find((a) => a.id === id);

    if (!assignment) return;
 
    // Fill form with current values

    titleInput.value = assignment.title;

    descInput.value = assignment.description;

    dueDateInput.value = assignment.dueDate;

    filesInput.value = (assignment.files || []).join("\n");
 
    editingAssignmentId = id;

    submitBtn.textContent = "Save Changes";
 
    assignmentForm.scrollIntoView({ behavior: "smooth" });

  }

}
 
// Load assignments from JSON and initialize

async function loadAndInitialize() {

  try {

    const response = await fetch("assignments.json");

    if (response.ok) {

      assignments = await response.json();

    } else {

      assignments = [];

    }

  } catch (error) {

    console.error("Failed to load assignments.json:", error);

    assignments = [];

  }
 
  renderTable();
 
  if (assignmentForm) {

    assignmentForm.addEventListener("submit", handleAddAssignment);

  }
 
  if (assignmentsTableBody) {

    assignmentsTableBody.addEventListener("click", handleTableClick);

  }

}
 
loadAndInitialize();

 
