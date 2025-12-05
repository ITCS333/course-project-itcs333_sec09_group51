// --- Element Selections ---
const listSection = document.querySelector("#assignment-list-section");

// --- Functions ---

/**
 * Creates an <article> element for one assignment
 * assignment = { id, title, dueDate, description }
 */
function createAssignmentArticle(assignment) {
  // Create elements
  const article = document.createElement("article");

  const title = document.createElement("h2");
  title.textContent = assignment.title;

  const due = document.createElement("p");
  due.textContent = `Due: ${assignment.dueDate}`;

  const desc = document.createElement("p");
  desc.textContent = assignment.description;

  const link = document.createElement("a");
  link.href = `details.html?id=${assignment.id}`;
  link.textContent = "View Details & Discussion";

  // Append elements to article
  article.appendChild(title);
  article.appendChild(due);
  article.appendChild(desc);
  article.appendChild(link);

  return article;
}

/**
 * Loads assignments from assignments.json and displays them
 */
async function loadAssignments() {
  try {
    // 1. Fetch data
    const response = await fetch("assignments.json");

    // 2. Parse JSON
    const assignments = await response.json();

    // 3. Clear the section
    listSection.innerHTML = "";

    // 4. Loop and create articles
    assignments.forEach(assignment => {
      const article = createAssignmentArticle(assignment);
      listSection.appendChild(article);
    });

  } catch (error) {
    console.error("Error loading assignments:", error);
  }
}

// --- Initial Page Load ---
loadAssignments();
