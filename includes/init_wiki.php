<?php
$wiki_head = file_get_contents('https://clanquest.org/wiki/api.php?action=parse&page=Clan%20Quest&prop=headhtml&format=json');
$json = json_decode($wiki_head);
$doc = new DOMDocument();
$doc->loadHTML($json->parse->headhtml->{'*'});

$wiki_scripts = '';
$links = $doc->getElementsByTagName('link');
foreach ($links as $l) {
    $rel = $l->attributes->getNamedItem('rel')->nodeValue;
    $href = $l->attributes->getNamedItem('href')->nodeValue;
    if ($rel == 'stylesheet' && strpos($href, 'commonPrint') === false)
        $wiki_scripts .= '<link rel="stylesheet" href="' . $href . '">' . "\n";
}

$scripts = $doc->getElementsByTagName('script');
foreach ($scripts as $s) {
    if ($s->attributes->getNamedItem('src') !== NULL) {
        $src = $s->attributes->getNamedItem('src')->nodeValue;
        $wiki_scripts .= '<script async="" src="' . $src . '"></script>' . "\n";
    }
}
