# HPU Datagrid Module - Data rendering
With data rendering, we mean the translation of a result set from the configured Doctrine datasource to HTML table cells. The DataGrid module uses a Strategy Pattern to make this process more extensible and flexible. What strategy is used to display the data is mostly determined by the datatype you've configured in your [Doctrine Entity](https://doctrine-dbal.readthedocs.org/en/latest/reference/types.html). Please note, not all Data Types might be supported by the DataGrid module. but feel free to add them with a pull request.

## Configuring a render
Just like the other configuration options from the DataGrid module, you can influence the behaviour of the renderings by configuring them in your array.  Take a look at this example below:
    
    'wms-datagrid' => array(
        'entityName' => 'Wms\Admin\MediaManager\Entity\MediaItem',
        'defaultColumns' => array(
            'id', 'title', 'caption', 'originalFile.mimetype', 'originalFile.size', 'thumbnailFile.imagepath'
        ),
         'renders' => array(
                'thumbnailFile.imagepath' => 'image'
        ),
    );
    
In the example above, the thumbnailFile.imagepath is rendered using the image strategy (which is supplied with the DataGrid module). Instead of a normal string output. 

## Building a custom render
Custom rendering is as easy as a.b.c. Just create a class that implements the `DataStrategyInterface` and make sure to configure them like below: 
    
    'wms-datagrid' => array(
        'entityName' => 'Wms\Admin\MediaManager\Entity\MediaItem',
        'defaultColumns' => array(
            'id', 'title', 'caption', 'originalFile.mimetype', 'originalFile.size', 'thumbnailFile.imagepath'
        ),
         'renders' => array(
                'thumbnailFile.imagepath' => '\Your\Custom\Rendering\StrategyClass'
        ),
    );
    
The DataGrid will recognise that you've configured a namespace for rendering and try to create a new instance of the configured class.