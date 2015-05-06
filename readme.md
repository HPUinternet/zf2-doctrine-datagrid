# HPU Datagrid Module
Helps you select and display the data of an Doctrine Entity in a customizable and extensible DataGrid table.

## Supported features
- Pagination
- Associations (Please note that the configured entity must contain information about the association to work)
    - One-To-One
    - One-To-Many
    - Many-To-One
    - Many-To-Many

## Feature roadmap
The following features are not yet supported by the module:

- Many-To-Many associations where the joinTable holds data aswel (disguised as an OneToMany whereas the targetted entity has "joinable" (Many-to-One, One-to-One")fields
- Filtering / Searching
- Hydration to doctrine objects instead of array's
- Frontend styling
