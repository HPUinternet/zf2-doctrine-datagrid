# HPU Datagrid Module - Filters
After configuring the datagrid to display your entity, you might want to concider adding some filtering.
The filters are divided into two different formats:

1. Filter
2. Search Filter

Whilist the searchfilter is an manageble filter that can be altered by a user of the datagrid module (for example an input field to serach for a specific name), the normal Filter
represents a [doctrine Filter](https://doctrine-orm.readthedocs.org/en/latest/reference/filters.html) that will be active in every query the datagrid module fires.

## Configuring a filter
Filters should be configured in the datagrid module options. The targetted filter class should extend the doctrine SQL filter class as described [here]([Doctrine filters](https://doctrine-orm.readthedocs.org/en/latest/reference/filters.html#example-filter-class) (note that there is no need to configure or enable the filter, the datagrid module does this for you. An example of a datagrid with registered filter is shown below.
```PHP
'wms-datagrid' => array(
    'entityName' => 'Wms\Admin\MediaManager\Entity\MediaItem',
    'defaultColumns' => array(
        'id', 'title', 'caption', 'originalFile.mimetype', 'originalFile.size', 'thumbnailFile.imagepath'
    ),
    'filters' => array(
        'Wms\Admin\MediaManager\Filter\MimeTypeFilter'
    ),
);
```
### Adding parameters
In the above sample i registered a filter class that filters the data on specific mimetypes. But i'm only able to hardcode the mimetype in the filterclass itsself. seems a bit devious right? luckily you are able to configure the filter parameters also by adding aditional arguments to the filters key in the configuraton array. An example below:
```PHP
'wms-datagrid' => array(
    'entityName' => 'Wms\Admin\MediaManager\Entity\MediaItem',
    'defaultColumns' => array(
        'id', 'title', 'caption', 'originalFile.mimetype', 'originalFile.size', 'thumbnailFile.imagepath'
    ),
    'filters' => array(
        'Wms\Admin\MediaManager\Filter\MimeTypeFilter' => array (
            'mimeType' => 'image/jpeg'
        ),
    ),
);
```
### Dynamic parameters
We can imagine hardcoding the filter parameters can sometimes come up short. Sadly since doctrine initializes a new instance of your filterclass when adding it to the EntityManager you are not able to pass us a filterobject which you created in a factory. So we've designed a something around it in order to address this problem. Concider the following code:
``` PHP
'wms-datagrid' => array(
    'entityName' => 'Wms\Admin\MediaManager\Entity\MediaItem',
    'defaultColumns' => array(
        'id', 'title', 'caption', 'originalFile.mimetype', 'originalFile.size', 'thumbnailFile.imagepath'
    ),
    'filters' => array(
        'Wms\Admin\User\Filter\PersonalEntitiesFilter' => 'Wms\Admin\User\Filter\PersonalEntitiesFilterParams'
    ),
);
```
The above filter will filter entities that only belong to the logged in user (so you can imagine that this "user id" parameter differs). Instead of passing filter parameters in an array we refrence a single class that implements a specifc PHP interface. in that way, you'll have more possibilities configuring your parameters. Take a look at the implementation code below:
``` PHP
<?php namespace Wms\Admin\User\Filter;

use Doctrine\ORM\Mapping\ClassMetaData;
use Doctrine\ORM\Query\Filter\SQLFilter;

/**
 * The OnlyOwnEntitiesFilter is build as a demonstration filter for the datagrid module.
 * When configured to run, this filter will tell the datagrid module to only retrieve entities that have the creator_id
 * property matched with the current user identity.
 *
 * Class OnlyOwnEntitiesFilter
 * @package Wms\Admin\User\Filter
 */
class PersonalEntitiesFilter extends SQLFilter
{
    /**
     * Doctrine calls this function whenever a query is about to be generated, implement your logic here
     *
     * @param ClassMetaData $targetEntity
     * @param string $targetTableAlias
     * @return string
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if ($targetEntity->name == $this->cleanParameter($this->getParameter('entityName'))
            && $targetEntity->hasField('creator_id') && $this->getParameter('userId')) {
            return $targetTableAlias . '.creator_id = ' . $this->getParameter('userId');
        }
        return '';
    }

    /**
     * Some parameter might be encapsulated with string quotes or double namespace seperators when retrieving
     * values from the config, clean them up before comparison
     *
     * @param $parameter
     * @return mixed
     */
    private function cleanParameter($parameter) {
        $parameter = str_replace('\\\\', '\\', $parameter);
        $parameter = str_replace('"', "", $parameter);
        $parameter = str_replace("'", "", $parameter);
        return $parameter;
    }
}
```
``` PHP
<?php namespace Wms\Admin\User\Filter;

use Wms\Admin\DataGrid\Filter\FilterParameterProviderInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Wms\Admin\DataGrid\Options\ModuleOptions;

/**
 * Since our PersonalEntitiesFilter requires dynamic parameters (in this case, the id of a logged in user)
 * we need to provide a class that is able to resolve these parameters for us.
 *
 * Class PersonalEntitiesFilterParams
 * @package Wms\Admin\User\Filter
 */
class PersonalEntitiesFilterParams implements FilterParameterProviderInterface {

    public function resolveParameters(ServiceLocatorInterface $serviceLocator)
    {
        $entityName = $serviceLocator->get('Wms\Admin\DataGrid\Options\ModuleOptions')->getEntityName();
        $parameters = array('entityName' => $entityName);

        $auth = $serviceLocator->get('zfcuser_auth_service');
        if ($auth->hasIdentity()) {
            $parameters['userId'] = $auth->getIdentity()->getId();
        }

        return $parameters;
    }
}
```
Please note that these parameters are resolved on crating the datagrid module in his factories, so you will still have a hard time overwriting these filter parameters in a controller. If you still need to overwrite something on a requrest basis, concider implemeting a search filter (documented below).