<?php

   $query = XN_Query::create('Content')
                ->filter('owner','=')
                ->filter('type','eic','Comment')
                ->filter('id','=',$_GET['id']);
$items = $query->execute();
$item = $items[0];

XN_Content::delete($item);

?>
Sucessfully deleted comment ID#<?php echo $_GET['id']; ?>