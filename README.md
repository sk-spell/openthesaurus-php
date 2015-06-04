# OpenThesaurus - a PHP/MySQL-based web application for building a thesaurus

This is an fork of [http://sourceforge.net/projects/openthesaurus/](http://sourceforge.net/projects/openthesaurus/)
Copyright (C) 2003-2007 [Daniel Naber](http://www.danielnaber.de)

This README describes how to install OpenThesaurus (the web 
application, not the thesaurus data created by the application).
It is useful not for end users but for people who want to set up 
a server that can be used by users to build and/or maintain a 
thesaurus.

OpenThesaurus is stable software, but it doesn't have a release
in the sense that there's a ZIP file which you can download. You
will need to download the files from CVS at [Sourceforge](http://sourceforge.net/cvs/?group_id=80914).

### Requirements:

 * A web server
 * PHP4 with support for gettext
 * MySQL

### Installation:

1. Copy the contents of the 'www' directory to the server's webroot directory.
2. Copy the 'include' directory to the directory which is one level above the webroot directory (i.e. not inside the webroot -- that's important for security reasons). If the include() statements don't work for you, you will need to change the include_path option in php.ini.
3. Create a database 'thesaurus' and use thesaurus.sql to build up its structure (translate the German and English data values before you use that file).
4. Add a user 'admin' to the auth_user table: 'user_id', 'username' and 'perms' should all be set to 'admin'. The password should not be encrypted. This user will have special access rights on the pages, e.g. there will be links that only the admin can see.
5. Copy include/config-template.php to include/config.php and adapt it to your needs. Adapt www/OOo-Thesaurus/COPYING, and copy it to www/download.
7. Point your browser to the webserver, e.g. http://www.yourserver.org/ (if it fails, try a second time!)
8. Log in as 'admin' with the password you just added to the database. Now search for a word, there will be no matches unless you already imported data (see below). But you can add the word on the page that tells you that there are no matches. Follow the 'ADMIN' link on top of the page to the see the admin page (log of latest user actions etc.)  New users can register themselves on the first page now.

#### Optional:
9. If you want to translate the user-visible pages to your language, create a directory include/locale/xx/LC_MESSAGES/, with xx being your language code (fr for French, de for German etc). Use the standard tools (e.g. KBabel under KDE) to create the translations. Then, adapt include/top.php and include/phplib/prepend.php3 so that your new language is the default language. You may need to restart Apache so the new language becomes visible.
10. If you want to built OOo/KWord/text thesauri automatically, add cronjobs on the server that call the export scripts, e.g.:
```
   w3m -dump http://localhost/admin/kword_export.php >~/kword_export.log
   w3m -dump http://localhost/admin/text_export.php >~/text_export.log
   w3m -dump http://localhost/admin/ooo_export.php >~/ooo_export.log
```
   Please check the output files for errors. If there's a problem, it's probably permission related. You might need to give the "download" directory write permission for everyone (chmod a+rwx download). If the export still doesn't work or if it's incomplete, make sure your PHP memory and time limits (/etc/php.ini) are sufficient, e.g.:
```
   memory_limit = 32M
   max_execution_time = 180
```
   (the more words your database contains, the higher these limits need to be)
11. If you have a list of full form to base form mappings (English example: slept -> sleep, children -> child, etc.) you can import the words into the word_forms table and the mapping into the word_mapping table. This way people can search for a word's full forms and they will get the base form's synonyms as a result.
12. To change the layout, see include/top.php, include/bottom.php and www/themes/styles.css

**NOTE:** when you import data, the values in db_sequence will have to
be modified manually so they point to an unused ID for each table.
For example, if you imported 1000 words and the highest ID is 1000,
the 'words' column in the db_sequence table needs to contain 1001.

**NOTE:** the phplib version used for OpenThesaurus doesn't work with PHP5. You
will probably need to make the following settings in php.ini to use 
OpenThesaurus with PHP5:
```
register_long_arrays = On
allow_call_time_pass_reference = On
```
Also note that some pages contain links that are only useful 
for German words (e.g. index.php and suggestions.php). You 
might want to remove or replace these links.


License that refers to the software, not to the data built
with the software (see file LICENSE in this directory for full 
license text):

Copyright (C) 2003-2007 Daniel Naber (http://www.danielnaber.de)