/*
  Requirement: Populate the assignment detail page and discussion forum.
*/

// --- Global Data Store ---
let currentAssignmentId = null;
let currentComments = [];

// --- Element Selections ---
// Selecting all required elements by ID
const assignmentTitle = document.querySelector("#assignment-title");
const assignmentDueDate = document.querySelector("#assignment-due-date");
const assignmentDescription = document.querySelector("#assignment-description");
const assignmentFilesList = document.querySelector("#assignment-files-list");

const commentList = document.querySelector("#comment-list");
const commentForm = document.querySelector("#comment-form");
const newCommentText = document.querySelector("#new-comment-text");

// --- Functions ---

// 1. Get assignment ID from URL
function getAssignmentIdFromURL() {
  const params = new URLSearchParams(window.location.search);
  return params.get("id");
}

// 2. Render assignment details
function renderAssignmentDetails(assignment) {
  assignmentTitle.textContent = assignment.title;
  assignmentDueDate.textContent = "Due: " + assignment.dueDate;
  assignmentDescription.textContent = assignment.description;

  // Clear old files
  assignmentFilesList.innerHTML = "";

  // Add each file as <li><a></a></li>
  assignment.files.forEach(file => {
    const li = document.createElement("li");
    const a = document.createElement("a");
    a.href = "#";
    a.textContent = file;
    li.appendChild(a);
    assignmentFilesList.appendChild(li);
  });
}

// 3. Create a comment article
function createCommentArticle(comment) {
  const article = document.createElement("article");
  article.classList.add("comment");

  const p = document.createElement("p");
  p.textContent = comment.text;

  const footer = document.createElement("footer");
  footer.textContent = "Posted by: " + comment.author;

  article.appendChild(p);
  article.appendChild(footer);

  return article;
}

// 4. Render all comments
function renderComments() {
  commentList.innerHTML = ""; // Clear existing comments

  currentComments.forEach(comment => {
    const commentArticle = createCommentArticle(comment);
    commentList.appendChild(commentArticle);
  });
}

// 5. Add a new comment
function handleAddComment(event) {
  event.preventDefault();

  const text = newCommentText.value.trim();
  if (!text) return;

  const newComment = {
    author: "Student",
    text: text,
  };

  currentComments.push(newComment);

  renderComments();
  newCommentText.value = "";
}

// 6. Initialize page
async function initializePage() {
  currentAssignmentId = getAssignmentIdFromURL();

  if (!currentAssignmentId) {
    assignmentTitle.textContent = "Error: No assignment ID found.";
    return;
  }

  try {
    const [assignmentsRes, commentsRes] = await Promise.all([
      fetch("assignments.json"),
      fetch("comments.json"),
    ]);

    const assignments = await assignmentsRes.json();
    const commentsData = await commentsRes.json();

    const assignment = assignments.find(a => a.id === currentAssignmentId);
    currentComments = commentsData[currentAssignmentId] || [];

    if (!assignment) {
      assignmentTitle.textContent = "Error: Assignment not found.";
      return;
    }

    renderAssignmentDetails(assignment);
    renderComments();

    commentForm.addEventListener("submit", handleAddComment);

  } catch (error) {
    console.error("Error loading data:", error);
    assignmentTitle.textContent = "Error loading assignment.";
  }
}

// --- Initial Page Load ---
initializePage();
