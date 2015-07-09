# Ski Report
The Denver Post's Ski Report. Lots of legacy code, 2007-era.


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

Note: your php.ini must allow environment variables, i.e. `variables_order = "EGPCS"`

### How to's
#### Run an update of all the snow reports:
`./update.bash update`

#### Flush cache on the outputted flat files:
`php output.php skiarea`

## Front-end development
Most of the markup you need is in template/. The markup is assembled in output.php and output_functions.php.

## To Do's
[Now maintained on the issues page](https://github.com/denverpost/skireport/issues)

## Related
- On The Snow API: http://clientservice.onthesnow.com/docs/index.html
