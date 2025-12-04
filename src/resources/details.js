/*
  Requirement: Populate the resource detail page and discussion forum.

  Instructions:
  1. Link this file to `details.html` using:
     <script src="details.js" defer></script>

  2. In `details.html`, add the following IDs:
     - To the <h1>: `id="resource-title"`
     - To the description <p>: `id="resource-description"`
     - To the "Access Resource Material" <a> tag: `id="resource-link"`
     - To the <div> for comments: `id="comment-list"`
     - To the "Leave a Comment" <form>: `id="comment-form"`
     - To the <textarea>: `id="new-comment"`

  3. Implement the TODOs below.
*/

// --- Global Data Store ---
// These will hold the data related to *this* resource.
let currentResourceId = null;
let currentComments = [];

// --- Element Selections ---
// TODO: Select all the elements you added IDs for in step 2.
let title = document.getElementById("resource-title");
let desc = document.getElementById("resource-description");
let link = document.getElementById("resource-link");
let list = document.getElementById("comment-list");
let form = document.getElementById("comment-form");
let textarea = document.getElementById("new-comment");
// --- Functions ---

/**
 * TODO: Implement the getResourceIdFromURL function.
 * It should:
 * 1. Get the query string from `window.location.search`.
 * 2. Use the `URLSearchParams` object to get the value of the 'id' parameter.
 * 3. Return the id.
 */
function getResourceIdFromURL() {
  // ... your implementation here ...
  let urlpram = new URLSearchParams (window.location.search);
  return urlpram.get('id');
}

/**
 * TODO: Implement the renderResourceDetails function.
 * It takes one resource object.
 * It should:
 * 1. Set the `textContent` of `resourceTitle` to the resource's title.
 * 2. Set the `textContent` of `resourceDescription` to the resource's description.
 * 3. Set the `href` attribute of `resourceLink` to the resource's link.
 */
function renderResourceDetails(resource) {
  // ... your implementation here ...
  title.textContent = resource.title ;
  desc.textContent = resource.description ;
  link.herf = resource.link ;

}

/**
 * TODO: Implement the createCommentArticle function.
 * It takes one comment object {author, text}.
 * It should return an <article> element matching the structure in `details.html`.
 * (e.g., an <article> containing a <p> and a <footer>).
 */
function createCommentArticle(comment) {
  // ... your implementation here ...
  let artical = document.createElement('article');
  let p = document.createElement('p');
  p.textContent = comment.text ;
  let footer = document.createElement('footer');
  footer.textContent = 'Posted by:'+comment.author ;

  artical.appendChild(p);
  artical.appendChild(footer);

  return artical;
}

/**
 * TODO: Implement the renderComments function.
 * It should:
 * 1. Clear the `commentList`.
 * 2. Loop through the global `currentComments` array.
 * 3. For each comment, call `createCommentArticle()`, and
 * append the resulting <article> to `commentList`.
 */
function renderComments() {
  // ... your implementation here ...
  list.innerHTML = '';
  currentComments.forEach (comment => {
    let comm = createCommentArticle(comment);
    list.appendChild(comm);
  })
}

/**
 * TODO: Implement the handleAddComment function.
 * This is the event handler for the `commentForm` 'submit' event.
 * It should:
 * 1. Prevent the form's default submission.
 * 2. Get the text from `newComment.value`.
 * 3. If the text is empty, return.
 * 4. Create a new comment object: { author: 'Student', text: commentText }
 * (For this exercise, 'Student' is a fine hardcoded author).
 * 5. Add the new comment to the global `currentComments` array (in-memory only).
 * 6. Call `renderComments()` to refresh the list.
 * 7. Clear the `newComment` textarea.
 */
function handleAddComment(event) {
  // ... your implementation here ...
  event.preventDefault();
  let commentText = newComment.value.trim();
  if(!commentText)
    return;
  
  let commentObj = {
    author: 'Student', text: commentText};
  
  currentComments.push(commentObj);
  renderComments();
  newComment.value= '';
}

/**
 * TODO: Implement an `initializePage` function.
 * This function needs to be 'async'.
 * It should:
 * 1. Get the `currentResourceId` by calling `getResourceIdFromURL()`.
 * 2. If no ID is found, set `resourceTitle.textContent = "Resource not found."` and stop.
 * 3. `fetch` both 'resources.json' and 'resource-comments.json' (you can use `Promise.all`).
 * 4. Parse both JSON responses.
 * 5. Find the correct resource from the resources array using the `currentResourceId`.
 * 6. Get the correct comments array from the comments object using the `currentResourceId`.
 * Store this in the global `currentComments` variable. (If no comments exist, use an empty array).
 * 7. If the resource is found:
 * - Call `renderResourceDetails()` with the resource object.
 * - Call `renderComments()` to show the initial comments.
 * - Add the 'submit' event listener to `commentForm` (calls `handleAddComment`).
 * 8. If the resource is not found, display an error in `resourceTitle`.
 */
async function initializePage() {
  // ... your implementation here ...
  currentResourceId= getResourceIdFromURL();

  if(!currentResourceId){
    resourceTitle.textContent = "Resource not found." ;
    return;}
  
   try {
    const [resourcesResponse, commentsResponse] = await Promise.all([
      fetch('resources.json'),
      fetch('resource-comments.json')
    ]);
    
    const resources = await resourcesResponse.json();
    const commentsData = await commentsResponse.json();
    
    const resource = resources.find(r => r.id == currentResourceId);
    
    if (!resource) {
      resourceTitle.textContent = "Resource not found.";
      return;
    }
    
    currentComments = commentsData[currentResourceId] || [];
    
    renderResourceDetails(resource);
    renderComments();
    
    commentForm.addEventListener('submit', handleAddComment);
  } catch (error) {
    console.error('Error loading data:', error);
    resourceTitle.textContent = "Error loading resource. Please try again.";
  }
}

// --- Initial Page Load ---
initializePage();
