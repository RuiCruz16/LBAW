// Utility Functions
const AjaxUtils = {
    encodeForAjax: (data) => {
      if (!data) return null;
      return Object.keys(data)
          .map((key) => `${encodeURIComponent(key)}=${encodeURIComponent(data[key])}`)
          .join("&");
    },
  
    sendRequest: (method, url, data, handler) => {
      const request = new XMLHttpRequest();
      request.open(method, url, true);
      request.setRequestHeader("X-CSRF-TOKEN", document.querySelector('meta[name="csrf-token"]').content);
      request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      request.addEventListener("load", handler);
      request.send(AjaxUtils.encodeForAjax(data));
    }
  };
  
  // Handle Pagination via AJAX
  document.addEventListener('DOMContentLoaded', () => {
      const paginationSections = ['invitations-received', 'invitations-sent', 'responses'];
  
      paginationSections.forEach(section => {
          const paginationContainer = document.getElementById(`pagination-${section}`);
          if (paginationContainer) {
              paginationContainer.addEventListener('click', function(e) {
                  if (e.target.tagName === 'A') {
                      e.preventDefault();
                      const url = e.target.getAttribute('href');
                      fetchPaginatedData(section, url);
                  }
              });
          }
      });
  
      function fetchPaginatedData(section, url) {
          fetch(url, {
              headers: {
                  'X-Requested-With': 'XMLHttpRequest'
              }
          })
          .then(response => response.text())
          .then(html => {
              const parser = new DOMParser();
              const doc = parser.parseFromString(html, 'text/html');
  
              // Update List
              const newList = doc.getElementById(`${section}-list`);
              if (newList) {
                  document.getElementById(`${section}-list`).innerHTML = newList.innerHTML;
              }
  
              // Update Pagination
              const newPagination = doc.getElementById(`pagination-${section}`);
              if (newPagination) {
                  document.getElementById(`pagination-${section}`).innerHTML = newPagination.innerHTML;
              }
          })
          .catch(error => console.error(`Error fetching ${section}:`, error));
      }
  });