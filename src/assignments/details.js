/*
  Assignment Detail Page + Discussion
 
  - Reads ?id=... from URL
  - Loads data from api/assignments.json and api/comments.json
  - Renders assignment details
  - Renders existing comments
  - Allows adding new comments (in-memory only)
*/
 
// --- Global Data Store ---
let currentAssignmentId = null;
let currentComments = [];
 
// --- Element Selections ---
const assignmentTitle = document.getElementById("assignment-title");
const assignmentDueDate = document.getElementById("assignment-due-date");
const assignmentDescription = document.getElementById("assignment-description");
const assignmentFilesList = document.getElementById("assignment-files-list");
 
const commentList = document.getElementById("comment-list");
const commentForm = document.getElementById("comment-form");
const newCommentText = document.getElementById("new-comment-text");
 
// --- Functions ---
 
/**
* Get assignment id from URL (?id=asg_1)
*/
function getAssignmentIdFromURL() {
  const params = new URLSearchParams(window.location.search);
  const id = params.get("id");
  return id;
}
 
/**
* Render assignment details into the page.
*/
function renderAssignmentDetails(assignment) {
  assignmentTitle.textContent = assignment.title;
  assignmentDueDate.textContent = `Due: ${assignment.dueDate || "-"}`;
  assignmentDescription.textContent = assignment.description;
 
  // Clear existing files
  assignmentFilesList.innerHTML = "";
 
  if (Array.isArray(assignment.files) && assignment.files.length > 0) {
    assignment.files.forEach((fileName) => {
      const li = document.createElement("li");
      const a = document.createElement("a");
      a.href = "#"; // Dummy link
      a.textContent = fileName;
      li.appendChild(a);
      assignmentFilesList.appendChild(li);
    });
  } else {
    const li = document.createElement("li");
    li.textContent = "No attached files.";
    assignmentFilesList.appendChild(li);
  }
}
 
/**
* Create one comment article.
* comment: { author, text }
*/
function createCommentArticle(comment) {
  const article = document.createElement("article");
  article.classList.add("comment-card");
 
  const textP = document.createElement("p");
  textP.textContent = comment.text;
 
  const footer = document.createElement("footer");
  footer.textContent = `Posted by: ${comment.author || "Student"}`;
 
  article.appendChild(textP);
  article.appendChild(footer);
 
  return article;
}
 
/**
* Render all comments.
*/
function renderComments() {
  commentList.innerHTML = "";
 
  if (!currentComments || currentComments.length === 0) {
    const p = document.createElement("p");
    p.textContent = "No comments yet. Be the first to ask a question!";
    commentList.appendChild(p);
    return;
  }
 
  currentComments.forEach((comment) => {
    const article = createCommentArticle(comment);
    commentList.appendChild(article);
  });
}
 
/**
* Handle posting a new comment.
*/
function handleAddComment(event) {
  event.preventDefault();
 
  const text = newCommentText.value.trim();
  if (!text) {
    return;
  }
 
  const newComment = {
    author: "Student",
    text,
  };
 
  currentComments.push(newComment);
  renderComments();
  newCommentText.value = "";
}
 
/**
* Initialize the detail page.
*/
async function initializePage() {
  currentAssignmentId = getAssignmentIdFromURL();
 
  if (!currentAssignmentId) {
    if (assignmentTitle) assignmentTitle.textContent = "Assignment not found";
    return;
  }
 
  
  try {
    // Adjust paths if needed
    const [assignmentsRes, commentsRes] = await Promise.all([
      fetch("../api/assignments.json"),
      fetch("../api/comments.json"),
    ]);
 
    const assignmentsData = await assignmentsRes.json();
    const commentsData = await commentsRes.json();
 
    const assignmentsArray = Array.isArray(assignmentsData)
      ? assignmentsData
      : [];
 
    const assignment = assignmentsArray.find(
      (a) => a.id === currentAssignmentId
    );
 
    if (!assignment) {
      assignmentTitle.textContent = "Assignment not found";
      assignmentDescription.textContent =
        "The requested assignment could not be located.";
      assignmentDueDate.textContent = "";
      assignmentFilesList.innerHTML = "";
      return;
    }
 
    // Render assignment info
    renderAssignmentDetails(assignment);
 
    // Load comments for this assignment
    currentComments = commentsData[currentAssignmentId] || [];
    renderComments();
 
    // Comment form listener
    if (commentForm) {
      commentForm.addEventListener("submit", handleAddComment);
    }
  } catch (error) {
    console.error("Error initializing assignment detail page:", error);
    if (assignmentTitle) {
      assignmentTitle.textContent = "Error loading assignment";
    }
  }
}
 
// Initial Page Load
initializePage();
