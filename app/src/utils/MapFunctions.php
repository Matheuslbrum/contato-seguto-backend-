<?php
function getObjectValues($object)
{
  $newValues = "$object->admin_name, $object->action, $object->timestamp</br>";
  return $newValues;
}
