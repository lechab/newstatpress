# Newstatpress development roadmap

**USELESS IDEA**
- add a page help somewhere (the help for the different %variable%,...)
- [ ] tab construction for overview page
- [ ] irihdate function in double with newstatpress_hdate => to clean
- [ ] Fix number of dot of navbar in Visitors page
- [ ] Add bot rss https://github.com/feedjira/feedjira/tree/master
- [ ] update options use of foreach
- [ ] changer le nom du widget dashboard
- [ ] Big problem on search function
- [ ] Export database > dump sql (bkp)
- [ ] add Select definitions to update
- add number on the websit in the overview page
- add jquery for credit page



## 0.9.7

User interface changes:
* [ ] Added New option : overview stats calculation method (global distinct ip OR sum of each day)
* [ ] Added New information 'Visitors RSS Feeds' in Overview page

## 0.9.6
Released date: 2015-02-21

User interface changes:
* Added Option page with tab navigation (use jQuery and idTabs)
* Fixed Search page link
* Updated Locale fr_FR, it_IT

Core changes:
* Various fixes (global definition, function, plugin page names with nsp_ prefix, code spliting)
* Various debug fixes (deprecated function, unset variable)
* Fixed %thistotalvisit% in API call


## 0.9.5

* Fixed PHP compatibility issue on old versions (tools page)

## 0.9.4

User interface changes:
* Added Tool page with tab navigation
* Added variable informations in Widget 'NewStatPress'
* Fix Overview Table (CSS)
* Updated Locale fr_FR, it_IT

Core changes:
- [x] Update of Widget 'NewStatPress' : code re-writed


## 0.9.3

- [x] Add Visits page with tab navigation
- [x] Add tab navigation in Crédits page
- [x] Add 'Donator' tab in Crédits page
- [x] Add 'visits' and 'options' links in Dashboard widget
- [x] Add CSS style to navbar in Visitors page
- [x] Add colored variation in overview table
- [x] Re-writed Overview function
- [x] Fix Duplicate INDEX when User database is updated (function rewrited)
- [x] Fix dashboard 'details' dead link
- [x] Fix navbar dead link in visitors page
- [x] Various code fixing
- [x] Api for variables (10x faster to load page with widget)
- [x] Changelog sort by last version
- [x] Update: locale fr_FR, it_IT

## 0.9.2

- [x] CSS fix, Overview fix and wp_enqueue_style compatibility fix

## 0.9.1

- [x] Activate changes of 0.8.9 in version 0.9.1 with PHP fixes

## 0.9.0

- [x] Revert to version 0.8.8 for problems with old PHP version

## 0.8.9

Development:
- [x] Add Ip2nation download function in option page
- [x] Add plugin homepage link, news feeds link, bouton donation in credit page
- [x] Add CSS style to stylesheet (./css/style.css), partially done
  - [x] remove page
  - [x] update page
  - [x] credit page
- [x] Optimization of the option page
- [x] Optimization of the credit page
- [x] Optimization of the export page
- [x] Optimization of the remove page
- [x] Optimization of the database update page
- [x] Fixed 'selected sub-menu' bug
- [x] Fixed wrong path to update IP2nation when database is updated (/includes)
- [x] Add variables %yvisits% (yesterday visits) %mvisits% (month visits)
- [x] Fix 5 bots, add 13 new bots


Translation Update:
- [x] fr_FR
- [x] it_IT
