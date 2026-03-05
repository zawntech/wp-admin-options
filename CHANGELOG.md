# Changelog

All notable changes to this project will be documented in this file.

## [dev]

### Added
- `Bootstrap\WPAdminOptions` class for centralized CDN asset enqueuing (Vue.js 3.5.22, Select2 4.0.13)
- Auto-initialization of assets via `AbstractAdminOption` on first use
- `assets/css/wp-admin-options.css` stylesheet with `wao-` prefixed utility classes and Tailwind UI-inspired styling
- `assets/js/wp-admin-options.js` with `window.WPAdminOptions` namespace for all option field scripts
- Shared JS helpers: `_dragMixin()`, `_itemMixin()`, `_select2Mount()`, `_merge()` for composable Vue option objects
- `_itemMixin` accepts configurable items property name for flexible list management (e.g. `'items'`, `'ids'`)
- `enable_copy` parameter on `InputOption` with clipboard copy button (auto-enabled for readonly inputs)
- Custom duration input mode on `DurationOption` with preset/custom toggle
- Drag-and-drop reordering with visual drop indicator on all sortable item lists
- Compact button groups (`.wao-action-group`) for action buttons
- Inline field labels (`.wao-field`, `.wao-field-label`) for ExampleJson option types
- `render_taxonomy_field()` implementations for BooleanCheckboxOption, ColorOption, EditorOption, HtmlOption
- `enable_test_mode()` for inline CSS and JS output via `<style>` and `<script>` tags
- Array value validation with red error banner (`render_array_error()`) for options requiring array input
- `.wao-error` CSS class for error banner styling

### Changed
- Upgraded from Vue.js 2 to Vue.js 3 (`new Vue()` → `Vue.createApp().mount()`)
- Migrated all option classes to Vue 3 compatible syntax: SelectOption, PostTypeSelectOption, TaxonomySelectOption, UserSelectOption, AttachmentOption, DateTimeOption, DurationOption, ExampleJsonOption, ExampleJsonMediaOption
- Fixed `v-for` destructuring to use parenthesized syntax (`v-for="(item, i) in items"`)
- Moved `before_admin_option` / `after_admin_option` hooks into `AbstractAdminOption::render()` dispatcher, removed from subclasses
- Extracted shared rendering into helper methods: `TextareaOption::render_textarea_content()`, `AttachmentOption::render_attachment_content()`
- Reduced code duplication between `render_admin_table()` and `render_taxonomy_field()` contexts
- Extracted all inline `<script>` blocks into `assets/js/wp-admin-options.js` with `WPAdminOptions` namespace
- Each PHP script call wrapped in IIFE with consolidated `json_encode($args)` for clean data passing
- ExampleJson options refactored as extension pattern examples using `WPAdminOptions._merge()` and `_dragMixin()`/`_itemMixin()`
- Disabled drag-and-drop attributes on singular AttachmentOption (non-multiple mode)

### Fixed
- Missing `</script>` closing tags in SelectOption and PostTypeSelectOption
- `.wao-vue-wrap { display: none !important }` preventing Vue multi-select components from rendering
- Missing `@click` handler on ExampleJsonMediaOption "Remove Image" button
- Uninitialized `$date` variable in DateTimeOption when value is empty

## [1.0.0]

### Added
- `AbstractAdminOption` base class with admin-table and taxonomy rendering contexts
- `InputOption` for text inputs
- `TextareaOption` with configurable rows
- `EditorOption` using WordPress WYSIWYG editor
- `HtmlOption` for raw HTML display
- `BooleanCheckboxOption` with 1/0 storage
- `ColorOption` with WordPress color picker and optional Spectrum.js driver
- `SelectOption` with single and multiple support via Vue.js
- `PostTypeSelectOption` for post selection by post type
- `TaxonomySelectOption` for term selection by taxonomy
- `UserSelectOption` with role filtering
- `AttachmentOption` for media library selection via Vue.js
- `DateTimeOption` for date and time inputs
- `DurationOption` for hours/minutes selection
- `ExampleJsonOption` for editable JSON arrays
- `ExampleJsonMediaOption` for JSON media arrays with reordering
- Help tooltip system with CSS injection
- `before_admin_option` and `after_admin_option` action hooks
- Taxonomy context rendering for all field types
- Select2 integration for enhanced dropdowns
- Placeholder attribute support
- Allow `'0'` string values in inputs
