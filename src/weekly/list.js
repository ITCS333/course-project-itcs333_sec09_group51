/*
  Requirement: Populate the "Weekly Course Breakdown" list page.

  Instructions:
  1. Link this file to `list.html` using:
     <script src="list.js" defer></script>

  2. In `list.html`, add an `id="week-list-section"` to the
     <section> element that will contain the weekly articles.

  3. Implement the TODOs below.
*/

// --- Element Selections ---
// TODO: Select the section for the week list ('#week-list-section').
const listSection = document.getElementById('week-list-section');

// --- Functions ---

/**
 * TODO: Implement the createWeekArticle function.
 * It takes one week object {id, title, startDate, description}.
 * It should return an <article> element matching the structure in `list.html`.
 * - The "View Details & Discussion" link's `href` MUST be set to `details.html?id=${id}`.
 * (This is how the detail page will know which week to load).
 */
function createWeekArticle(week) {
  const article = document.createElement('article');
  article.className = 'week-card';
  
  // Week title
  const heading = document.createElement('h2');
  heading.textContent = week.title;
  
  // Start date
  const datePara = document.createElement('p');
  datePara.className = 'week-meta';
  datePara.innerHTML = `<strong>Starts on:</strong> ${week.startDate}`;
  
  // Description (truncate if too long)
  const descPara = document.createElement('p');
  descPara.className = 'week-description';
  descPara.textContent = week.description.length > 150 
    ? week.description.substring(0, 150) + '...' 
    : week.description;
  
  // Link to details page
  const link = document.createElement('a');
  link.href = `details.html?id=${week.id}`;
  link.className = 'btn';
  link.textContent = 'View Details & Discussion';
  
  // Append all elements
  article.appendChild(heading);
  article.appendChild(datePara);
  article.appendChild(descPara);
  article.appendChild(link);
  
  return article;
}

/**
 * TODO: Implement the loadWeeks function.
 * This function needs to be 'async'.
 * It should:
 * 1. Use `fetch()` to get data from 'weeks.json'.
 * 2. Parse the JSON response into an array.
 * 3. Clear any existing content from `listSection`.
 * 4. Loop through the weeks array. For each week:
 * - Call `createWeekArticle()`.
 * - Append the returned <article> element to `listSection`.
 */
async function loadWeeks() {
  try {
    // Fetch weeks data
    const response = await fetch('weeks.json');
    const weeks = await response.json();
    
    // Clear existing content
    listSection.innerHTML = '';
    
    // Add loading message if no weeks
    if (weeks.length === 0) {
      listSection.innerHTML = '<p class="no-weeks">No weeks available. Check back soon!</p>';
      return;
    }
    
    // Create and append week articles
    weeks.forEach(week => {
      const weekArticle = createWeekArticle(week);
      listSection.appendChild(weekArticle);
    });
    
  } catch (error) {
    console.error('Error loading weeks:', error);
    listSection.innerHTML = '<p class="error">Error loading weekly breakdown. Please try again later.</p>';
  }
}

// --- Initial Page Load ---
loadWeeks();
