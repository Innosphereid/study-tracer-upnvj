{{-- 
/**
 * Questionnaire Styles Component
 * 
 * This component provides CSS styles for the questionnaire listing page.
 * It includes responsive grid styling for cards and table responsiveness.
 */
--}}

<style>
.card-grid {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 1rem;
}

@media (min-width: 640px) {
    .card-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (min-width: 1024px) {
    .card-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

.table-responsive {
    overflow-x: auto;
}

/* Line clamp utilities for multi-line text truncation */
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style> 