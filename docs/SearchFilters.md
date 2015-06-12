# HPU Datagrid Module - Search Filters
After configuring the DataGrid to display your entity, you might want to consider adding some filtering. The filters are divided into two different formats:

1. Filter
2. Search Filter

Whilst the (normal) filter is a permanent layer that can only be enabled or disabled by the programmer through the module configuration, the search filter allows the users of the datagrid to alter its values in the browser on a request basis.

## Configuring a search filter
Just like many other things in the datagrid module, searchfilters can be configured in the datagrid module options. Have a look at an example configuration below.
    
    
    'wms-datagrid' => array(
        'entityName' => 'Wms\Admin\Blog\Entity\BlogItem',
        'defaultColumns' => array(
            'id', 'title', 'content', 'categories.name', 'created'
        ),
        'searchFilters' => array(
            'Wms\Admin\Blog\SearchFilter\ListedonHomepage'
        ),
    );
    
    
### Your search filter class
In the above sample I've registered a filter class that is able to filter my BlogItems based on several predetermined parameters. In this scenario, a Blogpost is only listed on the homepage if: 1. Published is set to true, 2. The entity is within the last 10 posts, ordered by the created field.

@todo implmentatie hier