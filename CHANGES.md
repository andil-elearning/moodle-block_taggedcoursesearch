### 1.1.1

* Remove legacy lib.php require to ensure moodle 4.5 compatibility
* Prevent SQL error on duplicated tag selection. Thanks to Andriy Semenets (@semteacher)

### 1.1.0

* Fix card display for Moodle 3.6 and onward
* Replace deprecated coursecat lib usage

### 1.0.2

* Move FR language pack to AMOS

### 1.0.1

* Add check for `$CFG->usetags`. Print an error message if tags are disabled
* Remove useless global variable `$OUTPUT` usage
* Add missing capability name strings 
* Fix a typo in English pack (Thanks Germán Valero)
