# Ski Report

Repo for the Denver Post's Ski Report. Lots of legacy code, 2007-era.


## Developing with the Ski Report
### Necessary Environment Variables:
* Set DEPLOY to localhost if testing
`export DEPLOY=localhost`
* Set DB_PASS to the database password
`export DB_PASS=whatever`
* Set DB_USER
`export DB_USER=whatever`
* Set API_TOKEN to On The Snow's API password
`export API_TOKEN=whatever`

### How to's
#### Run an update of all the snow reports:
`./update.bash update`

#### Flush cache on the outputted flat files:
`php output.php skiarea`


## To Do's
- [ ] Mobile-friendly
- [ ] Use new API(s)
- [X] Add deaths to ski slope data

## Related
- On The Snow API: http://clientservice.onthesnow.com/docs/index.html
