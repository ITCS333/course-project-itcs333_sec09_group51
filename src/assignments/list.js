const listSection = document.getElementById("assignment-list-section");

function createAssignmentArticle(assignment) {
  const { id, title, dueDate, description } = assignment;

  const article = document.createElement("article");

  const h2 = document.createElement("h2");
  h2.textContent = title;

  const pDue = document.createElement("p");
  pDue.textContent = `Due: ${dueDate}`;

  const pDesc = document.createElement("p");
  pDesc.textContent = description;

  const link = document.createElement("a");
  link.href = `details.html?id=${encodeURIComponent(id)}`;
  link.textContent = "View Details & Discussion";

  article.append(h2, pDue, pDesc, link);
  return article;
}

// Load assignments from assignments.json
async function loadAssignments() {
  if (!listSection) return;

  listSection.textContent = "Loading assignments...";

  try {
    // Important: the file is inside the api folder
    const response = await fetch("../api/assignments.json");

    if (!response.ok) {
      throw new Error(`HTTP error: ${response.status}`);
    }

    const data = await response.json(); // This is an array

    listSection.innerHTML = "";

    if (!Array.isArray(data) || data.length === 0) {
      listSection.textContent = "No assignments found.";
      return;
    }

    data.forEach((assignment) => {
      const article = createAssignmentArticle(assignment);
      listSection.appendChild(article);
    });
  } catch (error) {
    console.error("Error loading assignments:", error);
    listSection.textContent = "Error loading assignments.";
  }
}

// Run on page load
loadAssignments();
