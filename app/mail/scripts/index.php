<?php
$alerts = $storage->fetchAll();

// trie les alertes par groupes
$alertsByGroup = array();
$groups = array();
foreach ($alerts AS $alert) {
    $group = $alert->group?$alert->group:"Sans groupe";
    $groups[] = $group;
    $alertsByGroup[$group][] = $alert;
}
$groups = array_unique($groups);
if (in_array("Sans groupe", $groups)) {
    // met les alertes sans groupe à la fin.
    unset($groups[array_search("Sans groupe", $groups)]);
    $groups[] = "Sans groupe";
}