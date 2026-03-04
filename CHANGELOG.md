# Changelog

All notable changes to this project will be documented in this file.

## [dev]

### Added
- `Bootstrap\WPAdminOptions` class for centralized CDN asset enqueuing (Vue.js 3.5.22, Select2 4.0.13)
- Auto-initialization of assets via `AbstractAdminOption` on first use

### Changed
- Upgraded from Vue.js 2 to Vue.js 3

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
