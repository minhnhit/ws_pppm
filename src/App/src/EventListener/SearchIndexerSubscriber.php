<?php
/**
 * Created by PhpStorm.
 * User: jamesn
 * Date: 5/14/17
 * Time: 9:25 PM
 */
namespace App\EventListener;

use Doctrine\Common\EventSubscriber;


class SearchIndexerSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return array(
            'Gedmo\Timestampable\TimestampableListener',
            'Gedmo\SoftDeleteable\SoftDeleteableListener',
            'Gedmo\Translatable\TranslatableListener',
            'Gedmo\Blameable\BlameableListener',
            'Gedmo\Loggable\LoggableListener',
            'Gedmo\Sluggable\SluggableListener',
            'Gedmo\Sortable\SortableListener',
            'Gedmo\Tree\TreeListener',
        );
    }
}