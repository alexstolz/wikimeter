## Knowledge Service for Wiki Pages ##

### What the project is about? ###

This project aims at automatically assessing the quality of Mediawiki articles.

For this purpose, it considers various readability indices, syntactical metrics, structural metrics, article lifecycle metrics, and reputation mechanisms.

### How to deploy it? ###

You as a private person or your enterprise is free to use this tool without any legal constraints (LGPL). Just copy the contents of this package somewhere onto your PHP-aware Web server, or deploy it as service for other stakeholders as well.

For the sake of simplicity, we subsequently assume that the Wikimeter was deployed at "hostname" in a subfolder "wikimeter", whereas the corresponding Mediawiki was installed on the same server within a folder "wiki". The knowledge service supports three eligible views:
  * Input form which allows a detailed view containing readability metrics and more: http://hostname/wikimeter/
  * Condensed view with condensed metrics and one summarized metric: http://hostname/wikimeter/condensed.php?uri=http://hostname/wiki/article
  * Comparison of the complete revision history over an article's lifecycle: http://hostname/wikimeter/comparison.php?uri=http://hostname/wiki/article
