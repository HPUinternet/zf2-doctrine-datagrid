# HPU DataGrid Module
Helps you select and display the data of an Doctrine Entity in a customizable and extensible DataGrid table.

The goal is to prevent duplication of code between pages or even projects by re-using one simple solution for displaying data.

Rather then using doctrine's lazy / eager loading to get association data this module builds its own set of Doctrine queries.
This gives us more control on the quantity of queries we shoot against our datasource (e.g. the mysql database) while doctrine still does all the heavy lifting of joining and generating the actual SQL.

## Supported features
- Lets users pick the columns/fields they want to see
    - Supports associations (Please note that the configured entity must contain information about the association to work)
    - You can hide columns/fields in the configuration array
- Pagination
- Filtering (configurable permanent where-clauses)
- Simple Searching (user can add simple filters)
- Turning features on/off in the view helper

## Installation
Please take a look at the [installation docs](docs/1. Installation.md).

## Feature roadmap
The following features are not yet supported by the module:

- publishing this in packagist
- Ordering the columns in a most basic way
- Frontend styling
- Many-To-Many associations where the joinTable holds data aswel (disguised as an OneToMany whereas the targetted entity has "joinable" (Many-to-One, One-to-One")fields
- Entities which consists of a multi column primary key
- Advanced filtering (an interface for the user to drag/drop their own search filters)
- Unit tests & Integration tests