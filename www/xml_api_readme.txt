OTAPIphp - OpenThesaurus-API PHP code
(adds support for OpenThesaurus-API to the PHP version of OpenThesaurus)

API version: 0.1.3
OTAPIphp version: 0.1.3-2
Release date: 2010-08-17

Copyright and licensing information
---
Adapted by API specification copyright by Daniel Naber.
Written by Martin Srebotnjak.
Available under GNU LESSER GENERAL PUBLIC LICENSE Version 2.1.
---

Files included:
- xml_api.php (the main API-query page);
- api.php (API help page, English);
- include/synsets_api.php (API-adapted synset search);
- include/levensthein_api.php (API-adapted similar word search);
- include/substring_matches_api.php (API-adapted substring search);
- include/phplib/prepend_xml.php3 (API-adapted prepend subpage);

Instructions:
- copy xml_api.php and api.php to the root of your openthesaurus installation;
- copy files from the include folder (levensthein_api.php, substring_matches_api.php, synsets_api.php) to the ../include folder of your openthesaurus installation;
- copy file from the include/phplib folder (prepend_xml.php3) to the ../include/phplib folder of your openthesaurus installation;
- adapt api.php to your openthesuarus (change API links and examples, translate);
- test the UTF-8 display of special characters in your language; if needed adapt or drop conversion functions (utf_encode_xml and iso_encode_xml) in synsets_api.php;
