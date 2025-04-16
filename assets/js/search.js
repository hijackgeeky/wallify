document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');
    const suggestionsDropdown = document.getElementById('searchSuggestions');
    let debounceTimer;

    if (!searchForm || !searchInput || !suggestionsDropdown) {
        console.error('Search elements not found');
        return;
    }

    // Handle search input
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();

        if (query.length < 2) {
            suggestionsDropdown.style.display = 'none';
            return;
        }

        // Show loading state
        suggestionsDropdown.style.display = 'block';
        suggestionsDropdown.innerHTML = `
            <div class="suggestion-item d-flex align-items-center">
                <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span>Searching...</span>
            </div>
        `;

        // Debounce the search request
        debounceTimer = setTimeout(() => {
            fetch(`search.php?q=${encodeURIComponent(query)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Search response:', data); // Debug log
                    if (data.success) {
                        displaySuggestions(data.results);
                    } else {
                        showError(data.message || 'An error occurred while searching');
                    }
                })
                .catch(error => {
                    console.error('Search error:', error);
                    showError('Failed to fetch search results');
                });
        }, 300);
    });

    // Display search suggestions
    function displaySuggestions(results) {
        if (!Array.isArray(results)) {
            console.error('Results is not an array:', results);
            showError('Invalid response format');
            return;
        }

        if (results.length === 0) {
            suggestionsDropdown.innerHTML = `
                <div class="suggestion-item d-flex align-items-center text-muted">
                    <i class="bi bi-search me-2"></i>
                    <span>No results found</span>
                </div>
            `;
            return;
        }

        suggestionsDropdown.innerHTML = results.map(result => `
            <div class="suggestion-item" data-id="${result.id}" style="display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #eee; cursor: pointer;">
                <img src="uploads/thumbnails/${result.thumbnail_path}" 
                     alt="${result.title}" 
                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; margin-right: 10px;">
                <div style="flex: 1;">
                    <div style="font-weight: 500;">${result.title}</div>
                    <div style="font-size: 0.875rem; color: #6c757d;">
                        <span class="badge bg-primary">${result.category}</span>
                        ${result.is_premium ? '<span class="badge bg-warning ms-1">Premium</span>' : ''}
                    </div>
                </div>
            </div>
        `).join('');

        // Add click handlers and hover effects to suggestions
        document.querySelectorAll('.suggestion-item').forEach(item => {
            if (item.dataset.id) {
                item.style.transition = 'background-color 0.2s';
                item.addEventListener('mouseover', () => {
                    item.style.backgroundColor = '#f8f9fa';
                });
                item.addEventListener('mouseout', () => {
                    item.style.backgroundColor = 'transparent';
                });
                item.addEventListener('click', function() {
                    window.location.href = `wallpaper.php?id=${this.dataset.id}`;
                });
            }
        });
    }

    // Show error message
    function showError(message) {
        suggestionsDropdown.innerHTML = `
            <div class="suggestion-item d-flex align-items-center text-danger">
                <i class="bi bi-exclamation-circle me-2"></i>
                <span>${message}</span>
            </div>
        `;
    }

    // Close suggestions when clicking outside
    document.addEventListener('click', function(event) {
        if (!searchForm.contains(event.target)) {
            suggestionsDropdown.style.display = 'none';
        }
    });

    // Handle form submission
    searchForm.addEventListener('submit', function(event) {
        const query = searchInput.value.trim();
        if (query.length < 2) {
            event.preventDefault();
            showError('Please enter at least 2 characters to search');
            suggestionsDropdown.style.display = 'block';
        }
    });

    // Handle focus on search input
    searchInput.addEventListener('focus', function() {
        if (this.value.trim().length >= 2) {
            suggestionsDropdown.style.display = 'block';
        }
    });
}); 