$(document).ready(function() {
    // Simple pagination and search solution instead of DataTables
    $('.datatable').each(function() {
        // Remove the datatable class to prevent DataTables from initializing
        $(this).removeClass('datatable').addClass('simple-table');
        
        const table = $(this);
        const tableId = 'table-' + Math.floor(Math.random() * 10000);
        table.attr('id', tableId);
        
        // Create pagination and search controls
        const tableWrapper = $('<div class="simple-table-wrapper"></div>');
        table.wrap(tableWrapper);
        
        const controls = $(`
            <div class="simple-controls mb-3">
                <div class="row">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control search-input" placeholder="Search...">
                            <button class="btn btn-primary search-btn">Search</button>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-secondary prev-page-btn">Previous</button>
                            <span class="btn btn-light page-info">Page 1</span>
                            <button type="button" class="btn btn-secondary next-page-btn">Next</button>
                        </div>
                    </div>
                </div>
            </div>
        `);
        
        table.before(controls);
        
        // Initialize the table state
        const state = {
            currentPage: 1,
            rowsPerPage: 10,
            searchTerm: '',
            totalPages: Math.ceil(table.find('tbody tr').length / 10)
        };
        
        // Functions to update the table
        function updateTable() {
            const startIndex = (state.currentPage - 1) * state.rowsPerPage;
            const endIndex = startIndex + state.rowsPerPage;
            let visibleRows = table.find('tbody tr');
            
            // Apply search filter if a search term exists
            if (state.searchTerm) {
                visibleRows = visibleRows.filter(function() {
                    const text = $(this).text().toLowerCase();
                    return text.includes(state.searchTerm.toLowerCase());
                });
            }
            
            state.totalPages = Math.ceil(visibleRows.length / state.rowsPerPage);
            
            // Hide all rows first
            table.find('tbody tr').hide();
            
            // Show only the rows for the current page
            visibleRows.slice(startIndex, endIndex).show();
            
            // Update pagination info
            controls.find('.page-info').text(`Page ${state.currentPage} of ${state.totalPages || 1}`);
            
            // Enable/disable pagination buttons
            controls.find('.prev-page-btn').prop('disabled', state.currentPage === 1);
            controls.find('.next-page-btn').prop('disabled', state.currentPage >= state.totalPages || state.totalPages === 0);
        }
        
        // Initialize the table
        updateTable();
        
        // Event handlers
        controls.find('.search-btn').on('click', function() {
            state.searchTerm = controls.find('.search-input').val();
            state.currentPage = 1;
            updateTable();
        });
        
        controls.find('.search-input').on('keyup', function(e) {
            if (e.key === 'Enter') {
                state.searchTerm = $(this).val();
                state.currentPage = 1;
                updateTable();
            }
        });
        
        controls.find('.prev-page-btn').on('click', function() {
            if (state.currentPage > 1) {
                state.currentPage--;
                updateTable();
            }
        });
        
        controls.find('.next-page-btn').on('click', function() {
            if (state.currentPage < state.totalPages) {
                state.currentPage++;
                updateTable();
            }
        });
    });
    
    // Handle form submissions via AJAX
    $(document).on('submit', '.ajax-form', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const formData = new FormData(this);
        
        // Get the action from the form data or use a default
        const action = formData.get('action') || 'update_wallpaper';
        formData.set('action', action);
        
        // Show loading spinner if it exists
        const submitBtn = form.find('button[type="submit"]');
        if (submitBtn.length) {
            const originalText = submitBtn.html();
            submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
            submitBtn.prop('disabled', true);
            
            // Store original button state
            submitBtn.data('originalText', originalText);
        }
        
        $.ajax({
            url: 'process_ajax.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(data) {
                // Reset button state
                if (submitBtn.length) {
                    submitBtn.html(submitBtn.data('originalText'));
                    submitBtn.prop('disabled', false);
                }
                
                if (data.status === 'success') {
                    // Create success alert
                    const successAlert = $('<div>')
                        .addClass('alert alert-success alert-dismissible fade show')
                        .html(`
                            ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        `);
                    
                    // Find notification area or content area
                    const notificationArea = $('.notification-area');
                    const contentArea = $('.content-area');
                    
                    if (notificationArea.length) {
                        notificationArea.append(successAlert);
                    } else if (contentArea.length) {
                        contentArea.prepend(successAlert);
                    }
                    
                    // Reset form
                    form[0].reset();
                    
                    // Redirect after delay if URL is provided
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1500);
                    }
                } else {
                    // Create error alert
                    const errorAlert = $('<div>')
                        .addClass('alert alert-danger alert-dismissible fade show')
                        .html(`
                            ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        `);
                    
                    // Find notification area or content area
                    const notificationArea = $('.notification-area');
                    const contentArea = $('.content-area');
                    
                    if (notificationArea.length) {
                        notificationArea.append(errorAlert);
                    } else if (contentArea.length) {
                        contentArea.prepend(errorAlert);
                    }
                }
            },
            error: function() {
                // Reset button state
                if (submitBtn.length) {
                    submitBtn.html(submitBtn.data('originalText'));
                    submitBtn.prop('disabled', false);
                }
                
                // Create error alert
                const errorAlert = $('<div>')
                    .addClass('alert alert-danger alert-dismissible fade show')
                    .html(`
                        An error occurred. Please try again.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `);
                
                // Find notification area or content area
                const notificationArea = $('.notification-area');
                const contentArea = $('.content-area');
                
                if (notificationArea.length) {
                    notificationArea.append(errorAlert);
                } else if (contentArea.length) {
                    contentArea.prepend(errorAlert);
                }
            }
        });
    });
    
    // Image preview for file inputs
    $(document).on('change', '.image-upload', function() {
        const input = this;
        const preview = $(this).data('preview');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                $(preview).attr('src', e.target.result).show();
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    });
    
    // Load modal content dynamically
    $(document).on('click', '.load-modal', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        const title = $(this).data('title') || 'Details';
        
        $('#modalTitle').text(title);
        $('#modalBody').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');
        
        const modal = new bootstrap.Modal(document.getElementById('dynamicModal'));
        modal.show();
        
        $.get(url, function(data) {
            $('#modalBody').html(data);
        }).fail(function() {
            $('#modalBody').html('<div class="alert alert-danger">Failed to load content</div>');
        });
    });
    
    // Delete confirmation - simplified approach without AJAX
    $(document).on('click', '.confirm-delete', function(e) {
        e.preventDefault();
        const deleteUrl = $(this).attr('href');
        
        if (confirm('Are you sure you want to delete this item?')) {
            $('#loadingSpinner').show();
            
            // Instead of AJAX, simply navigate to the URL with a callback parameter
            const returnUrl = window.location.href;
            window.location.href = deleteUrl + '&return_url=' + encodeURIComponent(returnUrl);
        }
    });
    
    // Handle category delete
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-category')) {
            // Let the form handle the submission
            return;
        }
    });
});

function showAlert(type, message) {
    const alertHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>`;
    
    // Check if we have a notification area in the current page
    const notificationArea = $('#notification-area');
    if (notificationArea.length > 0) {
        // Display in the notification area
        notificationArea.html(alertHTML);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            notificationArea.find('.alert').alert('close');
        }, 5000);
    } else {
        // Fall back to the content area
        const alert = $(alertHTML);
        $('#content-area').prepend(alert);
        
        setTimeout(() => {
            alert.alert('close');
        }, 5000);
    }
}