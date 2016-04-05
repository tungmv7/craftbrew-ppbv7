To create a view file that overrides a module view file,
it needs to have the same path and name as the module view file.

Eg:

module view file: 
{ModuleFolder}/views/[module]/[controller]/[action].phtml

=> 
theme view file:
{ThemeFolder}/[module]/[controller]/[action].phtml


---

All themes will need to have a layout.phtml file
The "admin"  folder is reserved and will not be usable as a front-end theme.

---

the main themes folder can be renamed, in which case global.config.php will need to be altered as well


---

Each theme must have two text files:
name.txt - will hold the name of the theme
adverts.txt - will hold the available advert places for the theme (comma separated [id],[name])