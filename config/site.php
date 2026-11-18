<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Main navigation
    |--------------------------------------------------------------------------
    |
    | Shared by the desktop header and the mobile bottom bar. Items with
    | "enabled" => false are not rendered at all: switch them on as each
    | page ships. "bottom" => true also shows the item in the mobile bar
    | (keep it to 3 or 4 items). "icon" is a Heroicons outline name.
    | "submenu" => "services" opens the engineering/technology panel on
    | desktop instead of navigating (the mobile bar still links to the hub).
    |
    */

    'nav' => [
        ['label' => 'Início',    'route' => 'home', 'icon' => 'home', 'enabled' => true, 'bottom' => true],
        // "params" are passed to route(); "active" widens the highlight to the section.
        ['label' => 'Ebooks',    'route' => 'products.index', 'params' => ['type' => 'ebooks'],    'active' => 'products.*', 'match' => 'ebooks',    'icon' => 'book-open',   'enabled' => true, 'bottom' => true],
        ['label' => 'Planilhas', 'route' => 'products.index', 'params' => ['type' => 'planilhas'], 'active' => 'products.*', 'match' => 'planilhas', 'icon' => 'table-cells', 'enabled' => true, 'bottom' => true],
        ['label' => 'Serviços',  'route' => 'services.index',  'active' => 'services.*',  'icon' => 'briefcase', 'enabled' => true, 'bottom' => true, 'submenu' => 'services'],
        ['label' => 'Portfólio', 'route' => 'portfolio.index', 'active' => 'portfolio.*', 'icon' => 'photo',     'enabled' => false, 'bottom' => false], // hidden until there are projects
        ['label' => 'Calculadoras', 'route' => 'calculators.index', 'icon' => 'calculator', 'enabled' => false, 'bottom' => false], // next step
    ],

    /*
    |--------------------------------------------------------------------------
    | Announcement bar
    |--------------------------------------------------------------------------
    |
    | Thin bar above the header on every public page. Switch it off (or edit
    | the text in components/site/announcement.blade.php) when the launch
    | phase is over.
    |
    */

    'announcement' => [
        'enabled' => env('SITE_ANNOUNCEMENT', false),
    ],

];
