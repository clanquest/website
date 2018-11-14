<?php
$wiki_contents = file_get_contents('https://clanquest.org/wiki/api.php?action=parse&page=Clan%20Quest&prop=text&disableeditsection=true&format=json');
$json = json_decode($wiki_contents);
echo $json->parse->text->{'*'};
