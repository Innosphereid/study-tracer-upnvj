# Questionnaire Components Documentation

This directory contains a set of components used to display and manage questionnaires in the TraceStudy UPNVJ application.

## Components Overview

### `filters.blade.php`

**Purpose:** Renders the filtering and search UI for questionnaires.
**Props:**

-   `filters`: Array of current filter values.
    **Usage:**

```blade
<x-questionnaire.filters :filters="$filters" />
```

### `grid-view.blade.php`

**Purpose:** Displays questionnaires in a grid layout.
**Props:**

-   `questionnaires`: Collection of questionnaire models to display.
-   `activeTab`: String indicating the current active tab/filter (e.g., 'draft', 'published').
    **Usage:**

```blade
<x-questionnaire.grid-view :questionnaires="$questionnaires" :activeTab="$activeTab" />
```

### `list-view.blade.php`

**Purpose:** Displays questionnaires in a table layout.
**Props:**

-   `questionnaires`: Collection of questionnaire models to display.
-   `activeTab`: String indicating the current active tab/filter.
    **Usage:**

```blade
<x-questionnaire.list-view :questionnaires="$questionnaires" :activeTab="$activeTab" />
```

### `page-header.blade.php`

**Purpose:** Renders the page title, description, and action buttons.
**Props:**

-   `title`: (Optional) Page title. Default: 'Daftar Kuesioner'.
-   `description`: (Optional) Page description. Default: 'Mengelola dan melacak semua kuesioner dalam sistem TraceStudy UPNVJ.'
    **Usage:**

```blade
<x-questionnaire.page-header title="Custom Title" description="Custom description" />
```

### `stats.blade.php`

**Purpose:** Displays statistics cards with questionnaire metrics.
**Props:**

-   `totalQuestionnaires`: Total number of questionnaires.
-   `activeQuestionnaires`: Number of active questionnaires.
-   `totalResponses`: Total number of responses.
-   `overallResponseRate`: Response rate percentage.
    **Usage:**

```blade
<x-questionnaire.stats
    :totalQuestionnaires="$totalQuestionnaires"
    :activeQuestionnaires="$activeQuestionnaires"
    :totalResponses="$totalResponses"
    :overallResponseRate="$overallResponseRate"
/>
```

### `styles.blade.php`

**Purpose:** Provides CSS styles for the questionnaire components.
**Usage:**

```blade
<x-questionnaire.styles />
```

### `view-toggle.blade.php`

**Purpose:** Renders buttons to toggle between grid and list views.
**Props:**

-   `initialView`: (Optional) Initial view type. Default: 'grid'.
    **Usage:**

```blade
<x-questionnaire.view-toggle initialView="list" />
```

### `view-toggle-scripts.blade.php`

**Purpose:** Provides JavaScript functionality for the view toggle feature and filter operations.
**Usage:**

```blade
@push('scripts')
    <x-questionnaire.view-toggle-scripts />
@endpush
```

## Component Architecture

The questionnaire components follow a modular approach, adhering to the Single Responsibility Principle. Each component is responsible for one specific aspect of the UI:

1. The main page (`index.blade.php`) serves as a container that orchestrates components.
2. Components are designed to be reusable and maintain clear boundaries of responsibility.
3. Data is passed down to components via props.
4. JavaScript functionality is separated into dedicated script components.

## Best Practices

-   Use named props with clear types for better readability and maintainability.
-   Follow Laravel naming conventions and component structure.
-   Keep components focused on a single responsibility.
-   Use slot features when components need to contain dynamic content.
-   Properly document props and usage examples.
