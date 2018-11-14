<?php
header('Content-type: text/css');
$ver = $_GET['assets_version'];
?>
@import url("colours.css?assets_version=<?=$ver?>");
@import url("common.css?assets_version=<?=$ver?>");
@import url("content.css?assets_version=<?=$ver?>");
@import url("fonts.css?assets_version=<?=$ver?>");
@import url("forms.css?assets_version=<?=$ver?>");
@import url("home.css?assets_version=<?=$ver?>");
@import url("imageset.css?assets_version=<?=$ver?>");
@import url("layout.css?assets_version=<?=$ver?>");
@import url("lightbox.css?assets_version=<?=$ver?>");
@import url("static.css?assets_version=<?=$ver?>");
@import url("responsive.css?assets_version=<?=$ver?>");
