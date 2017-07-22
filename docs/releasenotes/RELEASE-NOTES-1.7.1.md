Released on March 5, 2012.

* Added basic support for OpenLink Virtuoso RDF database. In addition to
  $smwgDefaultStore = 'SMWSparqlStore', users should set
  $smwgSparqlDatabase = 'SMWSparqlDatabaseVirtuoso' and use Virtuoso's SPARQL
  endpoint at ./sparql/ for query and update alike. for further remarks and known
  limitations, see the file ./includes/sparql/SMW_SparqlDatabaseVirtuoso.php .
* Added ability to sort dates as dates in tables generated by SMW (bug 25768).
* Added "Last editor is" and "Is a new page" special properties (bug 34359).
* When there are only invalid query conditions, query answering is stopped (bug 33177).
* Fixed display of nearby values on Special:SearchByProperty (bug 34178).
* Fixed display of URL values (bug 34312, 34044).
* Fixed warning when browsing certain property pages (bug 34306).
* Fixed failure of SMW_setup --delete when using postgresql (bug 31153).
* Fixed division by 0 error when setting the "Corresponds to" property to 0 (bug 32594).
* Fixed accept header send with SPARQL query requests (bug 32280).
* Fixed unresolved prefixed name in SPARQL queries (bug 33687).
* Fixed issues with modification date property occurring when using SMWSparqlStore (bug 30989).
* Fixed erroneous SPARQL for property value comparison queries (bug 30993).
* Fixed broken +index=x for records (bug 30284).
* Fixed querying of subobjects using 4store as a datastore.
* Fixed issue with namespace internationalization (bug 34383).

Also see the [SMW 1.7 release notes](RELEASE-NOTES-1.7.md)