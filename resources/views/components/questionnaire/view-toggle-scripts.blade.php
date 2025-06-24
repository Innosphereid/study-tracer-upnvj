{{-- 
/**
 * Questionnaire View Toggle Scripts Component
 * 
 * This component provides JavaScript functionality for:
 * 1. Toggling between grid and list views
 * 2. Managing view preferences using localStorage
 * 3. Form submission functionality for filters
 * 4. Clearing filters and search
 * 
 * It works in conjunction with the view-toggle component and filters component.
 */
--}}

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set initial view based on saved preference
    const savedView = localStorage.getItem('questionnaire_view') || 'grid';
    toggleView(savedView);
});

/**
 * Toggles between grid and list view
 * 
 * @param {string} viewType - The view type to display ('grid' or 'list')
 */
function toggleView(viewType) {
    const gridViewBtn = document.getElementById('grid-view-btn');
    const listViewBtn = document.getElementById('list-view-btn');
    const gridView = document.getElementById('grid-view');
    const listView = document.getElementById('list-view');

    if (viewType === 'grid') {
        gridViewBtn.classList.add('active-view', 'bg-gray-100');
        listViewBtn.classList.remove('active-view', 'bg-gray-100');
        gridView.classList.remove('hidden');
        listView.classList.add('hidden');
        // Save preference
        localStorage.setItem('questionnaire_view', 'grid');
    } else {
        gridViewBtn.classList.remove('active-view', 'bg-gray-100');
        listViewBtn.classList.add('active-view', 'bg-gray-100');
        gridView.classList.add('hidden');
        listView.classList.remove('hidden');
        // Save preference
        localStorage.setItem('questionnaire_view', 'list');
    }
}

/**
 * Submits the filter form
 */
function submitForm() {
    document.getElementById('submit-button').click();
}

/**
 * Clears all filters and resets to default values
 */
function clearFilters() {
    // Reset all form fields
    document.getElementById('status-filter').value = '';
    document.getElementById('period').value = '';
    document.getElementById('is_template').value = '';
    document.getElementById('search').value = '';
    document.getElementById('sort').value = 'newest';

    // Maintain per_page value if it exists
    const perPageSelect = document.getElementById('per_page');
    if (perPageSelect) {
        // Keep the current value or reset to 10 if not set
        perPageSelect.value = perPageSelect.value || '10';
    }

    // Submit the form to apply the cleared filters
    submitForm();
}

/**
 * Clears all filters except status
 */
function clearOtherFilters() {
    // Reset all filters except status
    document.getElementById('period').value = '';
    document.getElementById('search').value = '';
    document.getElementById('sort').value = 'newest';
}

/**
 * Clears the search field and submits the form
 */
function clearSearch() {
    document.getElementById('search').value = '';
    submitForm();
}
</script> 